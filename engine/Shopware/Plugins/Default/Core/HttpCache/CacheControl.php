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

namespace ShopwarePlugins\HttpCache;

use Enlight_Components_Session_Namespace as Session;
use Enlight_Config as HttpCacheConfig;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_Response as Response;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class CacheControl
{
    const AUTO_NO_CACHE_CONTROLLERS = [
        'frontend/checkout' => ['checkout'],
        'frontend/note' => ['checkout'],
        'frontend/detail' => ['detail'],
        'frontend/compare' => ['compare'],
    ];

    /**
     * @var Session
     */
    private $session;

    /**
     * @var HttpCacheConfig
     */
    private $config;

    /**
     * @param Session         $session
     * @param HttpCacheConfig $config
     */
    public function __construct(Session $session, HttpCacheConfig $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Validates if the provided route should be cached
     *
     * @return bool
     */
    public function isCacheableRoute(Request $request)
    {
        $cacheTime = $this->getCacheTime($request);

        if ($cacheTime === null) {
            return false;
        }

        if (strpos($request->getPathInfo(), '/widgets/index/refreshStatistic') !== false) {
            return false;
        }

        if (strpos($request->getPathInfo(), '/captcha/index/rand/') !== false) {
            return false;
        }

        if ($this->session->Admin) {
            return false;
        }

        return true;
    }

    /**
     * Validates if the provided route should get the `private, no-cache` header
     *
     * @param Request  $request
     * @param Response $response
     * @param $shopId
     *
     * @return bool
     */
    public function useNoCacheControl(Request $request, Response $response, $shopId)
    {
        $cacheTime = $this->getCacheTime($request);

        if ($cacheTime === null) {
            return true;
        }

        if ($response->isRedirect()) {
            return true;
        }

        if ($this->hasMatchingNoCacheCookie($request, $shopId)) {
            return true;
        }

        if ($this->hasAllowedNoCacheParameter($request)) {
            return true;
        }

        $controller = $this->getControllerRoute($request);
        if ($controller === 'widgets/checkout' && (!empty($this->session->offsetGet('sBasketQuantity')) || !empty($this->session->offsetGet('sNotesQuantity')))) {
            return true;
        }

        return false;
    }

    /**
     * Returns array of nocache-tags for given $controllerName
     *
     * <code>
     * array (
     *     0 => 'detail-1',
     *     1 => 'checkout-1',
     * )
     * </code>
     *
     * @param Request $request
     * @param int     $shopId
     *
     * @return array
     */
    public function getNoCacheTagsForRequest(Request $request, $shopId)
    {
        $tags = [];
        $autoAdmin = $this->config->get('admin');

        if (!empty($autoAdmin)) {
            $tags[] = 'admin-' . $shopId;
        }

        $configuredNoCacheTags = $this->getConfiguredNoCacheTags();

        $routeTags = $this->findRouteValue($configuredNoCacheTags, $request);

        if (!$routeTags) {
            return $tags;
        }

        foreach ($routeTags as $tag) {
            if ($tag === 'slt') {
                $tags[] = 'slt';
            } else {
                $tags[] = $tag . '-' . $shopId;
            }
        }

        return $tags;
    }

    /**
     * Returns a list of tags which has to be added to the no cache cookie
     *
     * @param Request              $request
     * @param ShopContextInterface $context
     *
     * @return \string[]
     */
    public function getTagsForNoCacheCookie(Request $request, ShopContextInterface $context)
    {
        $auto = $this->findRouteValue(self::AUTO_NO_CACHE_CONTROLLERS, $request);

        $tags = [];
        if ($auto !== null) {
            $tags = $auto;
        }

        if (!empty($this->session->offsetGet('sBasketQuantity')) || !empty($this->session->offsetGet('sNotesQuantity'))) {
            $tags[] = 'checkout';
        }

        if ($request->getCookie('slt')) {
            $tags[] = 'slt';
        }

        if (strtolower($request->getModuleName()) === 'frontend' && !empty($this->session->Admin)) {
            // set admin-cookie if admin session is present
            $tags[] = 'admin';
        }

        $action = $this->getActionRoute($request);
        if ($action === 'frontend/account/logout') {
            $tags[] = '';
        }

        return $tags;
    }

    /**
     * Returns a list of tags which has to be deleted from the no cache cookie
     *
     * @param Request              $request
     * @param ShopContextInterface $context
     *
     * @return \string[]
     */
    public function getRemovableCacheTags(Request $request, ShopContextInterface $context)
    {
        $action = $this->getActionRoute($request);

        $tags = [];
        if (empty($this->session->offsetGet('sBasketQuantity')) && empty($this->session->offsetGet('sNotesQuantity'))) {
            $tags[] = 'checkout';
        }

        if ($action === 'frontend/compare/delete_all') {
            $tags[] = 'compare';
        }

        if (!$request->getCookie('slt')) {
            $tags[] = 'slt';
        }

        return $tags;
    }

    /**
     * Returns the cache time for the provided request route
     *
     * @param Request $request
     *
     * @return int|null
     */
    public function getCacheTime(Request $request)
    {
        $routes = $this->getCacheableRoutes();

        return $this->findRouteValue($routes, $request);
    }

    /**
     * Defines if the provided route should add the nocache parameter for the generated esi url
     *
     * @param Request $request
     *
     * @return bool
     */
    public function useNoCacheParameterForEsi(Request $request, $targetName)
    {
        $tags = $this->getConfiguredNoCacheTags();

        $autoNoCacheControls = $this->findRouteValue(self::AUTO_NO_CACHE_CONTROLLERS, $request);

        return isset($autoNoCacheControls) && isset($tags[$targetName]) && !empty(array_intersect($autoNoCacheControls, $tags[$targetName]));
    }

    public function setContextCacheKey(Request $request, ShopContextInterface $context, Response $response)
    {
        $session = $this->session;

        $customerGroup = $session->offsetGet('sUserGroup');

        //not logged in => reset global context cookie
        if (!$customerGroup) {
            $this->resetCookies($request, $response);

            return;
        }

        $this->setContextCookie($request, $context, $response);
    }

    /**
     * @param array   $values
     * @param Request $request
     *
     * @return mixed
     */
    private function findRouteValue(array $values, Request $request)
    {
        $route = $this->getActionRoute($request);

        if (isset($values[$route])) {
            return $values[$route];
        }

        $route = $this->getControllerRoute($request);
        if (isset($values[$route])) {
            return $values[$route];
        }

        return null;
    }

    private function getActionRoute(Request $request)
    {
        return implode('/', [
            strtolower($request->getModuleName()),
            strtolower($request->getControllerName()),
            strtolower($request->getActionName()),
        ]);
    }

    private function getControllerRoute(Request $request)
    {
        return implode('/', [
            strtolower($request->getModuleName()),
            strtolower($request->getControllerName()),
        ]);
    }

    /**
     * @param Request $request
     * @param int     $shopId
     *
     * @return bool
     */
    private function hasMatchingNoCacheCookie(Request $request, $shopId)
    {
        $routeTags = $this->getNoCacheTagsForRequest($request, $shopId);

        $cookieTags = $this->getNoCacheTagsFromCookie($request);

        //has cookie tag?
        return !empty(array_intersect($routeTags, $cookieTags));
    }

    /**
     * Returns an array with cachable controllernames.
     *
     * Array-Key is controllername
     * Array-Value is ttl
     *
     * <code>
     * array (
     *     'frontend/listing'       => '3600',
     *     'frontend/index'         => '3600',
     *     'widgets/recommendation' => '14400',
     * )
     * </code>
     *
     * @return array
     */
    private function getCacheableRoutes()
    {
        $controllers = $this->config->get('cacheControllers');
        if (empty($controllers)) {
            return [];
        }

        $result = [];
        $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
        $controllers = explode("\n", trim($controllers));
        foreach ($controllers as $controller) {
            list($controller, $cacheTime) = explode(' ', $controller);
            $result[strtolower($controller)] = (int) $cacheTime;
        }

        return $result;
    }

    /**
     * Returns an mapping array with nocache-tags to controllernames
     *
     * Array-Key is controllername
     * Array-Value is cache tag
     *
     * <code>
     * array (
     *    'frontend/detail'  => ['price'],
     *    'widgets/checkout' => ['checkout'],
     *    'widgets/compare'  => ['compare'],
     * )
     * </code>
     *
     * @return array
     */
    private function getConfiguredNoCacheTags()
    {
        $controllers = $this->config->get('noCacheControllers');
        if (empty($controllers)) {
            return [];
        }

        $result = [];
        $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
        $controllers = explode("\n", trim($controllers));
        foreach ($controllers as $controller) {
            list($controller, $tag) = explode(' ', $controller);
            $result[strtolower($controller)] = explode(',', $tag);
        }

        return $result;
    }

    /**
     * Returns array of nocache-tags in the request cookie
     *
     * <code>
     * array (
     *     0 => 'detail-1',
     *     1 => 'checkout-1',
     * )
     * </code>
     *
     * @param Request $request
     *
     * @return array
     */
    private function getNoCacheTagsFromCookie(Request $request)
    {
        $noCacheCookie = $request->getCookie('nocache', false);

        if (false === $noCacheCookie) {
            return [];
        }

        $noCacheTags = explode(',', $noCacheCookie);
        $noCacheTags = array_map('trim', $noCacheTags);

        return $noCacheTags;
    }

    /**
     * Validates if the provided request is a cacheable route which should not be cached if a specify tag is set
     * and the request contains the nocache parameter as get parameter
     *
     * @param Request $request
     *
     * @return bool
     */
    private function hasAllowedNoCacheParameter(Request $request)
    {
        $configuredRoutes = $this->getConfiguredNoCacheTags();

        $tag = $this->findRouteValue($configuredRoutes, $request);

        return isset($tag) && $request->getQuery('nocache') !== null;
    }

    /**
     * @param Request  $request
     * @param Response $response
     */
    private function resetCookies(Request $request, Response $response)
    {
        $response->setCookie(
            'x-cache-context-hash',
            null,
            strtotime('-1 Year'),
            $request->getBasePath() . '/',
            ($request->getHttpHost() === 'localhost') ? null : $request->getHttpHost()
        );
    }

    /**
     * @param Request              $request
     * @param ShopContextInterface $context
     * @param Response             $response
     */
    private function setContextCookie(Request $request, ShopContextInterface $context, Response $response)
    {
        $hash = sha1(
            json_encode($context->getTaxRules()) .
            json_encode($context->getCurrentCustomerGroup())
        );

        $response->setCookie(
            'x-cache-context-hash',
            $hash,
            0,
            $request->getBasePath() . '/',
            ($request->getHttpHost() === 'localhost') ? null : $request->getHttpHost()
        );
    }
}
