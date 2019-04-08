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
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_Response as Response;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\HttpCache\CacheRouteGenerationService;
use Shopware\Components\HttpCache\CacheTimeServiceInterface;
use Shopware\Components\HttpCache\DefaultRouteService;
use Symfony\Component\HttpFoundation\Cookie;

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
     * @var array
     */
    private $config;

    /**
     * @var \Enlight_Event_EventManager
     */
    private $eventManager;

    /**
     * @var CacheTimeServiceInterface
     */
    private $cacheTimeService;

    /**
     * @var DefaultRouteService
     */
    private $defaultRouteService;

    /**
     * @var CacheRouteGenerationService
     */
    private $cacheRouteGeneration;

    public function __construct(
        Session $session,
        array $config,
        \Enlight_Event_EventManager $eventManager,
        DefaultRouteService $defaultRouteService,
        CacheTimeServiceInterface $cacheTimeService,
        CacheRouteGenerationService $cacheRouteGeneration
    ) {
        $this->session = $session;
        $this->config = $config;
        $this->eventManager = $eventManager;
        $this->cacheTimeService = $cacheTimeService;
        $this->defaultRouteService = $defaultRouteService;
        $this->cacheRouteGeneration = $cacheRouteGeneration;
    }

    /**
     * Validates if the provided route should be cached
     *
     * @return bool
     */
    public function isCacheableRoute(Request $request)
    {
        $cacheTime = $this->cacheTimeService->getCacheTime($request);

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
     * Returns the cache time for the provided request route
     *
     * @return int|null
     */
    public function getCacheTime(Request $request)
    {
        return $this->cacheTimeService->getCacheTime($request);
    }

    /**
     * Validates if the provided route should get the `private, no-cache` header
     *
     * @param int $shopId
     *
     * @return bool
     */
    public function useNoCacheControl(Request $request, Response $response, $shopId)
    {
        $cacheTime = $this->cacheTimeService->getCacheTime($request);

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

        $controller = $this->cacheRouteGeneration->getControllerRoute($request);

        return $controller === 'widgets/checkout' && (!empty($this->session->offsetGet('sBasketQuantity')) || !empty($this->session->offsetGet('sNotesQuantity')));
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
     * @param int $shopId
     *
     * @return array
     */
    public function getNoCacheTagsForRequest(Request $request, $shopId)
    {
        $tags = [];
        $autoAdmin = $this->config['admin'];

        if (!empty($autoAdmin)) {
            $tags[] = 'admin-' . $shopId;
        }

        $configuredNoCacheTags = $this->defaultRouteService->getDefaultNoCacheTags();
        $routeTags = $this->defaultRouteService->findRouteValue($request, $configuredNoCacheTags);

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
     * @return \string[]
     */
    public function getTagsForNoCacheCookie(Request $request, ShopContextInterface $context)
    {
        $auto = $this->defaultRouteService->findRouteValue($request, self::AUTO_NO_CACHE_CONTROLLERS);

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
            // Set admin-cookie if admin session is present
            $tags[] = 'admin';
        }

        $action = $this->cacheRouteGeneration->getActionRoute($request);
        if ($action === 'frontend/account/logout') {
            $tags[] = '';
        }

        return $tags;
    }

    /**
     * Returns a list of tags which has to be deleted from the no cache cookie
     *
     * @return \string[]
     */
    public function getRemovableCacheTags(Request $request, ShopContextInterface $context)
    {
        $action = $this->cacheRouteGeneration->getActionRoute($request);

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
     * Defines if the provided route should add the nocache parameter for the generated esi url
     *
     * @param string $targetName
     *
     * @return bool
     */
    public function useNoCacheParameterForEsi(Request $request, $targetName)
    {
        $tags = $this->defaultRouteService->getDefaultNoCacheTags();

        $autoNoCacheControls = $this->defaultRouteService->findRouteValue($request, self::AUTO_NO_CACHE_CONTROLLERS);

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
     * @param int $shopId
     *
     * @return bool
     */
    private function hasMatchingNoCacheCookie(Request $request, $shopId)
    {
        $routeTags = $this->getNoCacheTagsForRequest($request, $shopId);

        $cookieTags = $this->getNoCacheTagsFromCookie($request);

        // Has cookie tag?
        return !empty(array_intersect($routeTags, $cookieTags));
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
     * @return array
     */
    private function getNoCacheTagsFromCookie(Request $request)
    {
        $noCacheCookie = $request->getCookie('nocache', false);

        if ($noCacheCookie === false) {
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
     * @return bool
     */
    private function hasAllowedNoCacheParameter(Request $request)
    {
        $configuredRoutes = $this->defaultRouteService->getDefaultNoCacheTags();
        $tag = $this->defaultRouteService->findRouteValue($request, $configuredRoutes);

        return isset($tag) && $request->getQuery('nocache') !== null;
    }

    private function resetCookies(Request $request, Response $response)
    {
        $response->headers->setCookie(new Cookie('x-cache-context-hash', null, strtotime('-1 Year'), $request->getBasePath() . '/'));
    }

    private function setContextCookie(Request $request, ShopContextInterface $context, Response $response)
    {
        $hash = json_encode($context->getTaxRules()) . json_encode($context->getCurrentCustomerGroup());

        $hash = $this->eventManager->filter('Shopware_Plugins_HttpCache_ContextCookieValue', $hash, [
            'shopContext' => $context,
            'session' => $this->session,
            'request' => $request,
            'response' => $response,
        ]);

        $response->headers->setCookie(new Cookie('x-cache-context-hash', sha1($hash), 0, $request->getBasePath() . '/'));
    }
}
