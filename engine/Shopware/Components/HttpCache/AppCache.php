<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Components\HttpCache;

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
 *
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * @var array
     */
    protected $options = [];

    /**
     * Constructor.
     *
     * @param HttpKernelInterface $kernel  An HttpKernelInterface instance
     * @param array               $options
     */
    public function __construct(HttpKernelInterface $kernel, $options)
    {
        $this->kernel = $kernel;

        if (isset($options['cache_dir'])) {
            $this->cacheDir = $options['cache_dir'];
        }

        $this->options = array_merge([
            'purge_allowed_ips' => ['127.0.0.1', '::1'],
            'debug' => false,
            'cache_cookies' => ['shop', 'currency', 'x-cache-context-hash'],
        ], $options);

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
        $request->headers->set('Surrogate-Capability', 'shopware="ESI/1.0"');

        if (strpos($request->getPathInfo(), '/backend/') === 0) {
            return $this->pass($request, $catch);
        }

        if (strpos($request->getPathInfo(), '/widgets/index/refreshStatistic') === 0) {
            return $this->pass($request, $catch);
        }

        if (strpos($request->getPathInfo(), '/captcha/index/rand/') === 0) {
            return $this->pass($request, $catch);
        }

        $response = parent::handle($request, $type, $catch);

        $response->headers->remove('cache-control');
        $response->headers->addCacheControlDirective('no-cache');

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
     * @param Request $request
     * @param bool    $catch   Whether to process exceptions
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
                $response->setStatusCode(200, 'Banned');
            } else {
                $response->setStatusCode(200, 'Not Banned');
            }
        } elseif ($request->getMethod() === 'PURGE') {
            if ($this->getStore()->purge($request->getUri())) {
                $response->setStatusCode(200, 'Purged');
            } else {
                $response->setStatusCode(200, 'Not purged');
            }
        }

        return $response;
    }

    /**
     * Lookups a Response from the cache for the given Request.
     *
     * {@inheritdoc}
     *
     * @param Request $request
     * @param bool    $catch
     *
     * @return Response
     */
    protected function lookup(Request $request, $catch = false)
    {
        $response = parent::lookup($request, $catch);

        // If Response is not fresh age > 0 AND contains a mathing no cache tag
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
     * @param Request  $request
     * @param Response $response
     *
     * @throws \Exception
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
     * @param Request  $request
     * @param Response $response
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

        $cacheTag = $response->headers->get('x-shopware-allow-nocache');
        $cacheTag = explode(', ', $cacheTag);
        $noCacheCookie = $request->cookies->get('nocache');

        foreach ($cacheTag as $cacheTagValue) {
            if (strpos($noCacheCookie, $cacheTagValue) !== false) {
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
    protected function forward(Request $request, $raw = false, Response $entry = null)
    {
        $this->getKernel()->boot();

        /** @var $container \Shopware\Components\DependencyInjection\Container */
        $container = $this->getKernel()->getContainer();
        $container->set('HttpCache', $this);

        return parent::forward($request, $raw, $entry);
    }

    /**
     * @return \Symfony\Component\HttpKernel\HttpCache\Esi
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
        return new Store($this->cacheDir ? $this->cacheDir : $this->kernel->getCacheDir() . '/http_cache', $this->options['cache_cookies'], $this->options['lookup_optimization']);
    }

    /**
     * Checks if current purge request is allowed.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return bool
     */
    protected function isPurgeRequestAllowed(Request $request)
    {
        if ($request->server->has('SERVER_ADDR')) {
            if ($request->server->get('SERVER_ADDR') == $request->getClientIp()) {
                return true;
            }
        }

        return $this->isPurgeIPAllowed($request->getClientIp());
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
     * @return array
     */
    protected function getPurgeAllowedIPs()
    {
        $allowedIps = $this->options['purge_allowed_ips'];

        return $allowedIps;
    }
}
