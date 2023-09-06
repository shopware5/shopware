<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\HttpCache;

use Exception;
use Shopware\Bundle\CookieBundle\CookieCollection;
use Shopware\Bundle\CookieBundle\CookieGroupCollection;
use Shopware\Bundle\CookieBundle\Services\CookieRemoveHandler;
use Shopware\Bundle\CookieBundle\Structs\CookieGroupStruct;
use Shopware\Bundle\CookieBundle\Structs\CookieStruct;
use Shopware\Components\Privacy\CookieRemoveSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\StoreInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Shopware Application
 *
 * <code>
 * $httpCacheApp = new Shopware\Components\HttpCache\AppCache($kernel);
 * $httpCacheApp->invalidate($request);
 * </code>
 */
class AppCache extends HttpCache
{
    /**
     * @var HttpKernelInterface
     */
    protected $kernel;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var array<string, mixed>
     */
    protected $options = [
        'purge_allowed_ips' => ['127.0.0.1', '::1'],
        'debug' => false,
        'cache_cookies' => ['shop', 'currency', 'x-cache-context-hash'],
    ];

    /**
     * @param HttpKernelInterface  $kernel  An HttpKernelInterface instance
     * @param array<string, mixed> $options
     */
    public function __construct(HttpKernelInterface $kernel, $options)
    {
        $this->kernel = $kernel;

        if (isset($options['cache_dir'])) {
            $this->cacheDir = $options['cache_dir'];
        }

        $this->options = array_merge($this->options, $options);

        parent::__construct(
            $kernel,
            $this->createStore(),
            $this->createEsi(),
            $this->options
        );
    }

    /**
     * Short circuit some URLs to early pass
     *
     * {@inheritdoc}
     *
     * @api
     */
    public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true)
    {
        $this->checkSltCookie($request);

        $request->headers->set('Surrogate-Capability', 'shopware="ESI/1.0"');

        if (str_starts_with($request->getPathInfo(), '/backend/')) {
            return $this->pass($request, $catch);
        }

        if (stripos($request->getPathInfo(), '/widgets/index/refreshStatistic') === 0) {
            return $this->handleCookies($request, $this->pass($request, $catch));
        }

        if (str_starts_with($request->getPathInfo(), '/captcha/index/rand/')) {
            return $this->pass($request, $catch);
        }

        $response = parent::handle($request, $type, $catch);

        $response->headers->remove('cache-control');
        $response->headers->addCacheControlDirective('no-cache');

        if (str_starts_with($request->getPathInfo(), '/account')) {
            $response->headers->addCacheControlDirective('no-store');
        }

        $response = $this->handleCookies($request, $response);

        $this->filterHttp2ServerPushHeader($request, $response);

        return $response;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Invalidates non-safe methods (like POST, PUT, and DELETE).
     *
     * @param bool $catch Whether to process exceptions
     *
     * @return Response A Response instance
     */
    protected function invalidate(Request $request, $catch = false)
    {
        if ($request->getMethod() !== 'BAN' && $request->getMethod() !== 'PURGE') {
            return parent::invalidate($request, $catch);
        }

        // Reject all non-authorized clients
        if (!$this->isPurgeRequestAllowed($request)) {
            return new Response('', 405);
        }

        $response = new Response();

        if ($request->getMethod() === 'BAN') {
            if ($request->headers->has('x-shopware-invalidates')) {
                $cacheId = $request->headers->get('x-shopware-invalidates');
                $result = $this->getStore()->purgeByHeader('x-shopware-cache-id', $cacheId);
            } else {
                $result = $this->getStore()->purgeAll();
            }

            if ($result) {
                $response->setStatusCode(Response::HTTP_OK, 'Banned');
            } else {
                $response->setStatusCode(Response::HTTP_OK, 'Not Banned');
            }
        } elseif ($request->getMethod() === 'PURGE') {
            if ($this->getStore()->purge($request->getUri())) {
                $response->setStatusCode(Response::HTTP_OK, 'Purged');
            } else {
                $response->setStatusCode(Response::HTTP_OK, 'Not purged');
            }
        }

        return $response;
    }

    /**
     * Lookups a Response from the cache for the given Request.
     *
     * {@inheritdoc}
     *
     * @param bool $catch
     *
     * @return Response
     */
    protected function lookup(Request $request, $catch = false)
    {
        $response = parent::lookup($request, $catch);

        // If Response is not fresh age > 0 AND contains a matching no cache tag
        if ($response->getAge() > 0 && $this->containsNoCacheTag($request, $response)) {
            $response = $this->fetch($request);
        }

        if (!$this->options['debug']) {
            // Hide headers from client
            $response->headers->remove('x-shopware-allow-nocache');
            $response->headers->remove('x-shopware-cache-id');
        }

        return $response;
    }

    /**
     * @throws Exception
     *
     * @return void
     */
    protected function store(Request $request, Response $response)
    {
        // Not cache sites with nocache header
        if ($this->containsNoCacheTag($request, $response)) {
            return;
        }

        parent::store($request, $response);
    }

    /**
     * Checks whether or not the response header contains
     * a no-cache header that matches one in the request cookie
     *
     * @return bool
     */
    protected function containsNoCacheTag(Request $request, Response $response)
    {
        // Not cache sites with nocache header
        if (!$response->headers->has('x-shopware-allow-nocache')
            || !$request->cookies->has('nocache')) {
            return false;
        }

        $cacheTag = $response->headers->get('x-shopware-allow-nocache') ?? '';
        $cacheTag = explode(', ', $cacheTag);
        $noCacheCookie = $request->cookies->get('nocache');

        foreach ($cacheTag as $cacheTagValue) {
            if (str_contains($noCacheCookie, $cacheTagValue)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Forwards the Request to the backend and returns the Response.
     *
     * @param Request  $request A Request instance
     * @param bool     $raw     Whether to catch exceptions or not
     * @param Response $entry   A Response instance (the stale entry if present, null otherwise)
     *
     * @return Response A Response instance
     */
    protected function forward(Request $request, $raw = false, ?Response $entry = null)
    {
        $this->getKernel()->boot();

        $container = $this->getKernel()->getContainer();
        $container->set('httpcache', $this);

        return parent::forward($request, $raw, $entry);
    }

    /**
     * @return Esi
     */
    protected function createEsi()
    {
        return new Esi();
    }

    /**
     * @return StoreInterface
     */
    protected function createStore()
    {
        if (isset($this->options['storeClass'])) {
            /** @var class-string<StoreInterface> $class */
            $class = $this->options['storeClass'];

            return new $class($this->options, $this->kernel);
        }

        return new Store(
            $this->cacheDir ?: ($this->kernel->getCacheDir() . '/http_cache'),
            $this->options['cache_cookies'],
            $this->options['lookup_optimization'],
            $this->options['ignored_url_parameters']
        );
    }

    /**
     * Checks if current purge request is allowed.
     *
     * @return bool
     */
    protected function isPurgeRequestAllowed(Request $request)
    {
        if ($request->server->has('SERVER_ADDR') && $request->server->get('SERVER_ADDR') === $request->getClientIp()) {
            return true;
        }

        $clientIp = $request->getClientIp();
        if (!\is_string($clientIp)) {
            return true;
        }

        return $this->isPurgeIPAllowed($clientIp);
    }

    /**
     * Checks if $ip is allowed for Http PURGE requests
     *
     * @param string $ip
     *
     * @return bool
     */
    protected function isPurgeIPAllowed($ip)
    {
        $allowedIps = array_fill_keys($this->getPurgeAllowedIPs(), true);

        return isset($allowedIps[$ip]);
    }

    /**
     * Returns an array of allowed IPs for Http PURGE requests.
     *
     * @return array<string>
     */
    protected function getPurgeAllowedIPs()
    {
        return $this->options['purge_allowed_ips'];
    }

    private function checkSltCookie(Request $request): void
    {
        if (!$request->cookies->has('slt')) {
            return;
        }

        $noCache = $request->cookies->get('nocache', '');

        $noCache = array_filter(explode(', ', $noCache));
        if (\in_array('slt', $noCache, true)) {
            return;
        }

        $noCache[] = 'slt';
        $request->cookies->set('nocache', implode(', ', $noCache));
    }

    private function filterHttp2ServerPushHeader(Request $request, Response $response): void
    {
        /* We do not want to push the assets with every request, only for new visitors. We therefore check
           for an existing session-cookie, which would indicate that this isn't the first client request. */
        foreach ($request->cookies->keys() as $cookieName) {
            if (str_starts_with($cookieName, 'session-')) {
                $response->headers->remove('link');

                return;
            }
        }
    }

    private function handleCookies(Request $request, Response $response): Response
    {
        $response = $this->removeCookies($request, $response);

        $response->headers->remove(CookieRemoveHandler::COOKIE_CONFIG_KEY);
        $response->headers->remove(CookieRemoveHandler::COOKIE_GROUP_COLLECTION_KEY);

        return $response;
    }

    private function removeCookies(Request $request, Response $response): Response
    {
        $allowCookie = $request->cookies->getInt('allowCookie');

        if ($allowCookie === 1) {
            return $response;
        }

        $responseHeaders = $response->headers;
        $cookieResponseHeader = $responseHeaders->get(CookieRemoveHandler::COOKIE_CONFIG_KEY);
        if (!\is_string($cookieResponseHeader)) {
            return $response;
        }

        $cookieConfig = json_decode($cookieResponseHeader, true);

        if ($cookieConfig['cookieNoteMode'] === CookieRemoveSubscriber::COOKIE_MODE_ALL) {
            return $response;
        }

        $cookieGroupResponseHeader = $responseHeaders->get(CookieRemoveHandler::COOKIE_GROUP_COLLECTION_KEY);
        if (!\is_string($cookieGroupResponseHeader)) {
            return $response;
        }

        $cookieGroupCollection = unserialize(base64_decode($cookieGroupResponseHeader),
            [
                'allowed_classes' => [
                    CookieGroupCollection::class,
                    CookieCollection::class,
                    CookieGroupStruct::class,
                    CookieStruct::class,
                ],
            ]
        );

        $cookieRemoveHandler = new CookieRemoveHandler($cookieGroupCollection);
        $cookieRemoveHandler->removeCookiesFromPreferences($request, $response);

        return $response;
    }
}
