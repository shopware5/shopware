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

namespace ShopwarePlugins\HttpCache;

use Enlight_Components_Session_Namespace as Session;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_Response as Response;
use Enlight_Event_EventManager;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\HttpCache\CacheRouteGenerationService;
use Shopware\Components\HttpCache\CacheTimeServiceInterface;
use Shopware\Components\HttpCache\DefaultRouteService;

class CacheControl
{
    public const AUTO_NO_CACHE_CONTROLLERS = [
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
     * @var array<string, mixed>
     */
    private $config;

    /**
     * @var Enlight_Event_EventManager
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

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        Session $session,
        array $config,
        Enlight_Event_EventManager $eventManager,
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

        if (stripos($request->getPathInfo(), '/widgets/index/refreshStatistic') !== false) {
            return false;
        }

        if (strpos($request->getPathInfo(), '/captcha/index/rand/') !== false) {
            return false;
        }

        if ($this->session->get('Admin')) {
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
     * @return array<string>
     */
    public function getNoCacheTagsForRequest(Request $request, $shopId)
    {
        $tags = [];
        $autoAdmin = $this->config['admin'] ?? null;

        if (!empty($autoAdmin)) {
            $tags[] = 'admin-' . $shopId;
        }

        $configuredNoCacheTags = $this->defaultRouteService->getDefaultNoCacheTags();
        $routeTags = $this->defaultRouteService->findRouteValue($request, $configuredNoCacheTags);

        if (!\is_array($routeTags)) {
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
     * @return string[]
     */
    public function getTagsForNoCacheCookie(Request $request, ShopContextInterface $context)
    {
        $auto = $this->defaultRouteService->findRouteValue($request, self::AUTO_NO_CACHE_CONTROLLERS);

        $tags = [];
        if (\is_array($auto)) {
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
     * @return string[]
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

        return isset($autoNoCacheControls, $tags[$targetName])
            && \is_array($autoNoCacheControls) && !empty(array_intersect($autoNoCacheControls, $tags[$targetName]));
    }

    /**
     * @return void
     */
    public function setContextCacheKey(Request $request, ShopContextInterface $context, Response $response)
    {
        // not logged in => reset global context cookie
        if (!$this->session->offsetGet('sUserGroup')) {
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
     * @return array<string>
     */
    private function getNoCacheTagsFromCookie(Request $request)
    {
        $noCacheCookie = $request->getCookie('nocache', false);

        if ($noCacheCookie === false) {
            return [];
        }

        $noCacheTags = explode(',', $noCacheCookie);

        return array_map('trim', $noCacheTags);
    }

    /**
     * Validates if the provided request is a cacheable route which should not be cached if a specify tag is set
     * and the request contains the nocache parameter as get parameter
     */
    private function hasAllowedNoCacheParameter(Request $request): bool
    {
        $configuredRoutes = $this->defaultRouteService->getDefaultNoCacheTags();
        $tag = $this->defaultRouteService->findRouteValue($request, $configuredRoutes);

        return isset($tag) && $request->getQuery('nocache') !== null;
    }

    private function resetCookies(Request $request, Response $response): void
    {
        $response->setCookie('x-cache-context-hash', null, strtotime('-1 Year'), $request->getBasePath() . '/');
    }

    private function setContextCookie(Request $request, ShopContextInterface $context, Response $response): void
    {
        $hash = json_encode($context->getTaxRules()) . json_encode($context->getCurrentCustomerGroup());

        $hash = $this->eventManager->filter('Shopware_Plugins_HttpCache_ContextCookieValue', $hash, [
            'shopContext' => $context,
            'session' => $this->session,
            'request' => $request,
            'response' => $response,
        ]);

        $response->setCookie('x-cache-context-hash', sha1($hash), 0, $request->getBasePath() . '/', null, $request->isSecure());
    }
}
