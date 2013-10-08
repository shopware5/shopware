<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shopware Application
 *
 * <code>
 * $httpCacheApp = new Shopware\Components\HttpCache\AppCache($kernel);
 * $httpCacheApp->invalidate($request);
 * </code>
 *
 * @category  Shopware
 * @package   Shopware\Components\HttpCache
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class AppCache extends HttpCache
{
    /**
     * @var
     */
    protected $cacheDir;

    /**
     * @var \Symfony\Component\HttpKernel\HttpKernelInterface
     */
    protected $kernel;

    /**
     * @var \Symfony\Component\HttpKernel\HttpCache\Store
     */
    private $store;

    /**
     * @var \Symfony\Component\HttpKernel\HttpCache\Esi
     */
    private $esi;

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

        parent::__construct(
            $kernel,
            $this->store = $this->createStore(),
            $this->esi = $this->createEsi(),
            $options
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
        if (strpos($request->getPathInfo(), '/backend/') === 0) {
            return $this->pass($request, $catch);
        }

        if (strpos($request->getPathInfo(), '/widgets/index/refreshStatistic') === 0) {
            return $this->pass($request, $catch);
        }

        if (strpos($request->getPathInfo(), '/captcha/index/rand/') === 0) {
            return $this->pass($request, $catch);
        }

        return parent::handle($request, $type, $catch);
    }

    /**
     * Invalidates non-safe methods (like POST, PUT, and DELETE).
     *
     * @param Request $request
     * @param Boolean $catch   Whether to process exceptions
     *
     * @return Response A Response instance
     *
     * @see RFC2616 13.10
     */
    protected function invalidate(Request $request, $catch = false)
    {
        if ($_SERVER['SERVER_ADDR'] !== $request->getClientIp()) {
            return parent::invalidate($request);
        }

        if ($request->getMethod() === 'BAN') {
            $response = new Response();
            $this->getStore()->purgeAll();
            $response->setStatusCode(200, 'Banned');
        } elseif ($request->getMethod() === 'PURGE') {
            $response = new Response();
            if ($this->getStore()->purge($request->getUri())) {
                $response->setStatusCode(200, 'Purged');
            } else {
                $response->setStatusCode(404, 'Not purged');
            }
        } else {
            $response = parent::invalidate($request);
        }

        return $response;
    }

    /**
     * Lookups a Response from the cache for the given Request.
     *
     * {@inheritDoc}
     *
     * @param  Request  $request
     * @param  bool     $catch
     * @return Response
     */
    protected function lookup(Request $request, $catch = false)
    {
        $response = parent::lookup($request, $catch);

        // If Response is not fresh age > 0 AND contains a mathing no cache tag
        if ($response->getAge() > 0 && $this->containsNoCacheTag($request, $response)) {
            $this->record($request, 'no-cache-tag');
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
     * @param  Request  $request
     * @param  Response $response
     * @throws \Exception
     */
    protected function store(Request $request, Response $response)
    {
        // Not cache sites with nocache header
        if ($this->containsNoCacheTag($request, $response)) {
            return;
        }

        return parent::store($request, $response);
    }

    /**
     * Checks whether or not the response header contains
     * a no-cache header that matches one in the request cookie
     *
     * @param Request  $request
     * @param Response $response
     * @return bool
     */
    protected function containsNoCacheTag(Request $request, Response $response)
    {
        // Not cache sites with nocache header
        if ($response->headers->has('x-shopware-allow-nocache')
            && $request->cookies->has('nocache')
        ) {
            $cacheTag = $response->headers->get('x-shopware-allow-nocache');
            $cacheTag = explode(', ', $cacheTag);
            foreach ($cacheTag as $cacheTagValue) {
                if (strpos($request->cookies->get('nocache'), $cacheTagValue) !== false) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Forwards the Request to the backend and returns the Response.
     *
     * @param Request  $request A Request instance
     * @param Boolean  $raw     Whether to catch exceptions or not
     * @param Response $entry   A Response instance (the stale entry if present, null otherwise)
     *
     * @return Response A Response instance
     */
    protected function forward(Request $request, $raw = false, Response $entry = null)
    {
        $this->getKernel()->boot();
        /** @var $bootstrap \Shopware_Bootstrap */
        $bootstrap = $this->getKernel()->getApp()->Bootstrap();

        $bootstrap->registerResource('HttpCache', $this);
        $bootstrap->registerResource('Esi', $this->esi);

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
     * @return \Symfony\Component\HttpKernel\HttpCache\Store
     */
    protected function createStore()
    {
        return new Store($this->cacheDir? $this->cacheDir : $this->kernel->getCacheDir().'/http_cache');
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }
}
