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

use Doctrine\Common\EventArgs;
use Doctrine\ORM\Proxy\Proxy;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\ContainerAwareEventManager;
use Shopware\Components\HttpCache\CacheRouteGenerationService;
use Shopware\Components\HttpCache\CacheTimeServiceInterface;
use Shopware\Components\HttpCache\DefaultRouteService;
use Shopware\Components\HttpCache\Store;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\CachedConfigReader;
use Shopware\Models\Config\Form;
use Shopware\Models\Shop\Shop;
use ShopwarePlugins\HttpCache\CacheControl;
use ShopwarePlugins\HttpCache\CacheIdCollector;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;

class Shopware_Plugins_Core_HttpCache_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var Enlight_Controller_Action
     */
    private $action;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var array
     */
    private $cacheInvalidationBuffer = [];

    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Frontendcache (HttpCache)';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.1.0';
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * Install the plugin.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PreDispatch',
            'onPreDispatch'
        );

        $this->subscribeEvent(
            'Shopware_Plugins_HttpCache_InvalidateCacheId',
            'onInvalidateCacheId'
        );

        $this->subscribeEvent(
            'Shopware_Plugins_HttpCache_ClearCache',
            'onClearCache'
        );

        $this->createCronJob(
            'HTTP Cache löschen',
            'ClearHttpCache',
            86400,
            true
        );

        $this->subscribeEvent(
            'Shopware_CronJob_ClearHttpCache',
            'onClearHttpCache'
        );

        $this->subscribeEvent('Enlight_Bootstrap_InitResource_http_cache.cache_control', 'initCacheControl');
        $this->subscribeEvent('Enlight_Bootstrap_InitResource_http_cache.cache_id_collector', 'initCacheIdCollector');

        $this->subscribeEvent('Shopware\Models\Article\Price::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Price::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Price::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Article\Article::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Article::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Article::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Article\Detail::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Detail::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Detail::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Category\Category::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Banner\Banner::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Blog\Blog::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Blog\Blog::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Blog\Blog::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Emotion\Emotion::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Emotion\Emotion::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Emotion\Emotion::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Site\Site::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Site\Site::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Site\Site::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Form\Form::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Form\Form::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Form\Form::postRemove', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Form\Field::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Form\Field::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Form\Field::postRemove', 'onPostPersist');

        $this->installForm();

        return true;
    }

    /**
     * @return CacheControl
     */
    public function initCacheControl(Enlight_Event_EventArgs $args)
    {
        return new CacheControl(
            $this->get(SessionInterface::class),
            $this->get(CachedConfigReader::class)->getByPluginName('HttpCache'),
            $this->get(ContainerAwareEventManager::class),
            $this->get(DefaultRouteService::class),
            $this->get(CacheTimeServiceInterface::class),
            $this->get(CacheRouteGenerationService::class)
        );
    }

    /**
     * @return CacheIdCollector
     */
    public function initCacheIdCollector()
    {
        return new CacheIdCollector();
    }

    public function afterInit()
    {
        $this->get(Enlight_Loader::class)->registerNamespace('ShopwarePlugins\\HttpCache', __DIR__);
        parent::afterInit();
    }

    public function getCapabilities()
    {
        return [
            'install' => false,
            'enable' => false,
            'update' => true,
        ];
    }

    /**
     * Enable plugin method
     * Although plugin is flagged as capability: enable => false,
     * it can still be enabled/disabled programmatically
     */
    public function enable()
    {
        return true;
    }

    /**
     * Disable plugin method
     * Although plugin is flagged as capability: enable => false,
     * it can still be enabled/disabled programmatically
     */
    public function disable()
    {
        return true;
    }

    /**
     * Install config-form
     *
     * @return void
     */
    public function installForm()
    {
        $form = $this->Form();

        /** @var Form $parent */
        $parent = $this->Forms()->findOneBy(['name' => 'Core']);

        $form->setParent($parent);
        $form->setElement('textarea', 'cacheControllers', [
            'label' => 'Cache-Controller / Zeiten',
            'value' => "frontend/listing 3600\r\n" .
            "frontend/index 3600\r\n" .
            "frontend/detail 3600\r\n" .
            "frontend/campaign 14400\r\n" .
            "widgets/listing 14400\r\n" .
            "frontend/custom 14400\r\n" .
            "frontend/forms 14400\r\n" .
            "frontend/sitemap 14400\r\n" .
            "frontend/blog 14400\r\n" .
            "widgets/index 3600\r\n" .
            "widgets/checkout 3600\r\n" .
            "widgets/compare 3600\r\n" .
            "widgets/emotion 14400\r\n" .
            "widgets/recommendation 14400\r\n" .
            "widgets/lastArticles 3600\n" .
            "widgets/campaign 3600\n",
        ]);

        $form->setElement('textarea', 'noCacheControllers', [
            'label' => 'NoCache-Controller / Tags',
            'value' => "frontend/listing price\n" .
            "frontend/index price\n" .
            "frontend/detail price\n" .
            "widgets/lastArticles detail\n" .
            "widgets/checkout checkout\n" .
            "widgets/compare compare\n" .
            "widgets/emotion price\n",
        ]);

        $form->setElement('boolean', 'proxyPrune', [
            'label' => 'Proxy-Prune aktivieren',
            'description' => 'Das automatische Leeren des Caches aktivieren.',
            'value' => true,
        ]);

        $form->setElement('text', 'proxy', [
            'label' => 'Alternative Proxy-Url',
            'description' => 'Link zum Http-Proxy mit „http://“ am Anfang.',
            'value' => null,
        ]);

        $form->setElement('boolean', 'admin', [
            'label' => 'Admin-View',
            'description' => 'Cache bei Artikel-Vorschau und Schnellbestellung deaktivieren',
            'value' => false,
        ]);
    }

    /**
     * Returns the configured proxy-url.
     *
     * Fallback to auto-detection if proxy-url is not configured and $request is given.
     * Returns null if $request is not given or auto-detection fails.
     *
     * @param Request $request
     *
     * @return string|null
     */
    public function getProxyUrl(?Request $request = null)
    {
        $proxyUrl = trim((string) $this->Config()->get('proxy', ''));
        if ($proxyUrl !== '') {
            return $proxyUrl;
        }

        // If proxy url is not set fall back to host detection
        if ($request !== null && $request->getHttpHost()) {
            return $request->getScheme() . '://'
                   . $request->getHttpHost()
                   . $request->getBaseUrl() . '/';
        }

        $shop = $this->get(ModelManager::class)->getRepository(Shop::class)->findOneBy(['default' => true]);
        if (!$shop->getHost()) {
            return null;
        }

        return sprintf(
            '%s://%s%s/',
            $shop->getSecure() ? 'https' : 'http',
            $shop->getHost(),
            $shop->getBasePath()
        );
    }

    /**
     * Do http caching jobs
     *
     * @return void
     */
    public function onPreDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $this->action = $args->getSubject();
        $this->request = $args->getRequest();
        $this->response = $args->getResponse();

        $this->Application()->Events()->registerListener(
            new Enlight_Event_Handler_Default(
                'Enlight_Controller_Action_PostDispatch',
                [$this, 'onPostDispatch'],
                // Must be positioned before ViewRender Plugin so the ESI renderer can be registered  before the template is rendered
                399
            )
        );
    }

    public function renderEsiTag(Enlight_Controller_Request_RequestHttp $request, array $params): ?string
    {
        if (!$this->Plugin()->getActive()) {
            return null;
        }

        if ($this->request === null) {
            return null;
        }

        if (!$this->get('shop')->get('esi')) {
            return null;
        }

        if (!$this->hasSurrogateEsiCapability($this->request)) {
            return null;
        }

        if (!\in_array($this->request->getModuleName(), ['frontend', 'widgets'], true)) {
            return null;
        }

        $targetName = strtolower($params['module'] . '/' . $params['controller']);

        /** @var CacheControl $cacheControl */
        $cacheControl = $this->get('http_cache.cache_control');

        if ($cacheControl->useNoCacheParameterForEsi($request, $targetName)) {
            $params['nocache'] = 1;
        }

        $url = sprintf('%s/?%s', $request->getBaseUrl(), http_build_query($params, '', '&'));

        return '<esi:include src="' . $url . '" />';
    }

    /**
     * On post dispatch we try to find affected articleIds displayed during this request
     *
     * @return void
     */
    public function onPostDispatch(Enlight_Controller_ActionEventArgs $args)
    {
        $view = $args->getSubject()->View();
        if (!$this->request->isDispatched()
            || $this->response->isException()
            || !$view->hasTemplate()
        ) {
            return;
        }

        if (!$this->hasSurrogateEsiCapability($this->request)) {
            return;
        }

        if ($this->request->getModuleName() !== 'frontend' && $this->request->getModuleName() !== 'widgets') {
            return;
        }

        // Do not cache if shop(template) is not esi-enabled
        if (!Shopware()->Shop()->get('esi')) {
            return;
        }

        $this->addSurrogateControl($this->response);

        $this->addContextCookie($this->request, $this->response);

        $this->setNoCacheCookie();

        $this->setCacheHeaders();
    }

    /**
     * Callback for event Shopware_CronJob_ClearHttpCache
     *
     * Clears the file-based http-cache storage directory
     *
     * @return string
     */
    public function onClearHttpCache(Shopware_Components_Cron_CronJob $job)
    {
        if ($this->clearCache()) {
            return "Cleared HTTP-Cache\n";
        }

        return '';
    }

    /**
     * Callback for Shopware_Plugins_HttpCache_ClearCache-Event
     *
     * This events should be used to clear the http-cache without having
     * to check if the http-cache-plugin is installed and enabled.
     *
     * <code>
     * Shopware()->Events()->notify('Shopware_Plugins_HttpCache_ClearCache');
     * </code>
     *
     * @return void
     */
    public function onClearCache(Enlight_Event_EventArgs $args)
    {
        $result = $this->clearCache();

        $args->setReturn($result);
    }

    /**
     * Callback for Shopware_Plugins_HttpCache_InvalidateCacheId-Event
     *
     * This events should be used to invalidate cacheIds without having
     * to check if the http-cache-plugin is installed and enabled.
     *
     * <code>
     * Shopware()->Events()->notify(
     *     'Shopware_Plugins_HttpCache_InvalidateCacheId',
     *     array('cacheId' => 'a123')
     * );
     * </code>
     *
     * @return void
     */
    public function onInvalidateCacheId(Enlight_Event_EventArgs $args)
    {
        $cacheId = $args->get('cacheId');
        if (!$cacheId) {
            $args->setReturn(false);

            return;
        }

        $result = $this->invalidateCacheId($cacheId);
        $args->setReturn($result);
    }

    /**
     * Sets the Shopware cache headers
     *
     * @return bool
     */
    public function setCacheHeaders()
    {
        /** @var CacheControl $cacheControl */
        $cacheControl = $this->get('http_cache.cache_control');

        $shopId = $this->get('shop')->getId();
        if (!$cacheControl->isCacheableRoute($this->request)) {
            return false;
        }

        if ($cacheControl->useNoCacheControl($this->request, $this->response, $shopId)) {
            $this->response->headers->set('cache-control', 'private, no-cache', true);

            return false;
        }

        $cacheTime = (int) $cacheControl->getCacheTime($this->request);

        $this->request->setParam('__cache', $cacheTime);
        $this->response->headers->set('cache-control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime, true);

        $noCacheTags = $cacheControl->getNoCacheTagsForRequest($this->request, $shopId);
        if (!empty($noCacheTags)) {
            $this->response->headers->set('x-shopware-allow-nocache', implode(', ', $noCacheTags), true);
        }

        $cacheCollector = $this->get('http_cache.cache_id_collector');

        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        $this->setCacheIdHeader(
            $cacheCollector->getCacheIdsFromController($this->action, $context)
        );

        return true;
    }

    /**
     * This method sets the nocache-cookie if actions in the shop are triggered
     *
     * @return void
     */
    public function setNoCacheCookie()
    {
        /** @var CacheControl $cacheControl */
        $cacheControl = $this->get('http_cache.cache_control');

        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        $additions = array_keys(array_flip($cacheControl->getTagsForNoCacheCookie($this->request, $context)));
        foreach ($additions as $tag) {
            $this->setNoCacheTag($tag);
        }

        $removals = array_keys(array_flip($cacheControl->getRemovableCacheTags($this->request, $context)));
        foreach ($removals as $tag) {
            $this->setNoCacheTag($tag, true);
        }
    }

    /**
     * Set or remove given $noCacheTag from cookie
     *
     * @param string $newTag
     * @param bool   $remove
     *
     * @return void
     */
    public function setNoCacheTag($newTag, $remove = false)
    {
        if ($existingTags = $this->getResponseCookie($this->response)) {
            $existingTags = explode(', ', $existingTags);
        } elseif ($this->request->getCookie('nocache')) {
            $existingTags = $this->request->getCookie('nocache');
            $existingTags = explode(', ', $existingTags);
        } else {
            $existingTags = [];
        }

        $shopId = Shopware()->Shop()->getId();

        if (!empty($newTag) && $newTag !== 'slt') {
            $newTag .= '-' . $shopId;
        }

        if (empty($newTag)) {
            $newCacheTags = [];
        } elseif ($remove) {
            // Remove $noCacheTag from $newCacheTags
            $newCacheTags = array_diff($existingTags, [$newTag]);
        } elseif (!$remove && !\in_array($newTag, $existingTags)) {
            // Add $noCacheTag to $newCacheTags
            $newCacheTags = $existingTags;
            $newCacheTags[] = $newTag;
        }

        if (isset($newCacheTags)) {
            $this->response->headers->setCookie(
                new Cookie('nocache', implode(', ', $newCacheTags), 0, $this->request->getBasePath() . '/', null, $this->request->isSecure())
            );
        }
    }

    /**
     * Helper function to flag the request with cacheIds to invalidate the caching.
     *
     * @param array $cacheIds
     *
     * @return void
     */
    public function setCacheIdHeader($cacheIds = [])
    {
        $cacheIds = $this->Application()->Events()->filter(
            'Shopware_Plugins_HttpCache_GetCacheIds',
            $cacheIds,
            ['subject' => $this, 'action' => $this->action]
        );

        if (empty($cacheIds)) {
            return;
        }

        $cacheIds = ';' . implode(';', $cacheIds) . ';';
        $this->response->headers->set('x-shopware-cache-id', $cacheIds, true);
    }

    /**
     * Execute cache invalidation after Doctrine flush
     *
     * @return void
     */
    public function postFlush(EventArgs $eventArgs)
    {
        $cacheIds = array_keys($this->cacheInvalidationBuffer);
        foreach ($cacheIds as $cacheId) {
            $this->invalidateCacheId($cacheId);
        }
        $this->cacheInvalidationBuffer = [];
    }

    /**
     * Cache invalidation based on model events
     *
     * @return void
     */
    public function onPostPersist(Enlight_Event_EventArgs $eventArgs)
    {
        if (!$this->Config()->get('proxyPrune')) {
            return;
        }

        $entity = $eventArgs->get('entity');
        if ($entity instanceof Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = \get_class($entity);
        }

        if (Shopware()->Events()->notifyUntil(
            'Shopware_Plugins_HttpCache_ShouldNotInvalidateCache',
            [
                'entity' => $entity,
                'entityName' => $entityName,
            ]
        )) {
            return;
        }

        $cacheIds = [];

        switch ($entityName) {
            case Shopware\Models\Article\Price::class:
                $cacheIds[] = 'a' . $entity->getArticle()->getId();
                break;

            case Shopware\Models\Article\Article::class:
                $cacheIds[] = 'a' . $entity->getId();
                break;

            case Shopware\Models\Article\Detail::class:
                $cacheIds[] = 'a' . $entity->getArticleId();
                break;

            case Shopware\Models\Category\Category::class:
                $cacheIds[] = 'c' . $entity->getId();
                break;

            case Shopware\Models\Blog\Blog::class:
            case Shopware\Models\Banner\Banner::class:
                $cacheIds[] = 'c' . $entity->getCategoryId();
                break;

            case Shopware\Models\Emotion\Emotion::class:
                $cacheIds[] = 'e' . $entity->getId();
                break;

            case Shopware\Models\Site\Site::class:
                $cacheIds[] = 's' . $entity->getId();
                break;
            case Shopware\Models\Form\Form::class:
                $cacheIds[] = 'f' . $entity->getId();
                break;
            case Shopware\Models\Form\Field::class:
                $cacheIds[] = 'f' . $entity->getFormId();
                break;
        }

        foreach ($cacheIds as $cacheId) {
            $this->cacheInvalidationBuffer[$cacheId] = true;
        }

        $entityManager = Shopware()->Container()->get(ModelManager::class);
        $entityManager->getEventManager()->addEventListener(['postFlush'], $this);
    }

    /**
     * Helper function to enable the http cache for a single shopware controller.
     *
     * @param int   $cacheTime
     * @param array $cacheIds
     *
     * @return void
     */
    public function enableControllerCache($cacheTime = 3600, $cacheIds = [])
    {
        $this->response->headers->set('cache-control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime, true);
        $this->setCacheIdHeader($cacheIds);
    }

    /**
     * Helper function to disable the http cache for a single shopware controller
     *
     * @return void
     */
    public function disableControllerCache()
    {
        $this->response->headers->set('cache-control', 'private', true);
    }

    private function getResponseCookie(Response $response): ?string
    {
        $cookies = $response->getCookies();
        foreach ($cookies as $cookie) {
            if ($cookie['name'] === 'nocache') {
                return $cookie['value'];
            }
        }

        return null;
    }

    /**
     * Clears the cache
     */
    private function clearCache(): bool
    {
        return $this->invalidate();
    }

    /**
     * Invalidates a given $cacheId
     *
     * This sends a http-ban-request to the proxyUrl containing
     * the $cacheId in the x-shopware-invalidates http-header
     */
    private function invalidateCacheId(string $cacheId): bool
    {
        if (!$this->Config()->get('proxyPrune')) {
            return false;
        }

        return $this->invalidate($cacheId);
    }

    /**
     * Will send BAN requests to all configured reverse proxies. If cacheId is provided,
     * the corresponding headers will be set.
     *
     * @param string|null $cacheId If set, only pages including these cacheIds will be invalidated
     *
     * @return bool True will be returned, if *all* operations succeeded
     */
    private function invalidate(?string $cacheId = null): bool
    {
        $proxyUrl = trim((string) $this->Config()->get('proxy', ''));
        if ($proxyUrl !== '') {
            return $this->invalidateWithBANRequest($proxyUrl, $cacheId);
        }

        if ($this->get('service_container')->has('httpcache')) {
            return $this->invalidateWithStore($cacheId);
        }

        // If no explicit proxy was configured + no host is configured
        $proxyUrl = $this->getProxyUrl($this->request);
        if ($proxyUrl !== null) {
            return $this->invalidateWithBANRequest($proxyUrl, $cacheId);
        }

        return false;
    }

    /**
     * @param string $urls Comma separated URLs
     */
    private function invalidateWithBANRequest(string $urls, ?string $cacheId): bool
    {
        // Expand + trim proxies (comma separated)
        $urls = array_map(
            'trim',
            explode(',', $urls)
        );

        $success = true;
        foreach ($urls as $url) {
            try {
                $client = new Zend_Http_Client($url, [
                    'useragent' => 'Shopware/' . Shopware()->Config()->get('version'),
                    'timeout' => 3,
                ]);

                if ($cacheId) {
                    $client->setHeaders('x-shopware-invalidates', $cacheId);
                }

                $response = $client->request('BAN');
                if ($response->getStatus() < 200 || $response->getStatus() >= 300) {
                    $this->get('corelogger')->error(
                        'Reverse proxy returned invalid status code',
                        ['response' => $response->getRawBody(), 'code' => $response->getStatus()]
                    );
                }
            } catch (Exception $e) {
                $this->get('corelogger')->error($e->getMessage(), ['exception' => $e]);
                $success = false;
            }
        }

        return $success;
    }

    private function invalidateWithStore(?string $cacheId = null): bool
    {
        /** @var HttpCache $httpCache */
        $httpCache = $this->get('httpcache');

        /** @var Store $store */
        $store = $httpCache->getStore();

        if (!$cacheId) {
            return $store->purgeAll();
        }

        return $store->purgeByHeader('x-shopware-cache-id', $cacheId);
    }

    /**
     * Adds HTTP headers to specify that the Response needs to be parsed for ESI.
     *
     * This method only adds an ESI HTTP header if the Response has some ESI tags.
     *
     * @param Response $response A Response instance
     */
    private function addSurrogateControl(Response $response): void
    {
        $response->headers->set('Surrogate-Control', 'content="ESI/1.0"', true);
    }

    /**
     * Checks that at least one surrogate has ESI/1.0 capability.
     *
     * @param Request $request A Request instance
     *
     * @return bool true if one surrogate has ESI/1.0 capability, false otherwise
     */
    private function hasSurrogateEsiCapability(Request $request): bool
    {
        $value = $request->getHeader('Surrogate-Capability');
        if (empty($value)) {
            return false;
        }

        return str_contains($value, 'ESI/1.0');
    }

    /**
     * Add context cookie
     */
    private function addContextCookie(Request $request, Response $response): void
    {
        /** @var CacheControl $cacheControl */
        $cacheControl = $this->get('http_cache.cache_control');

        $context = $this->get(ContextServiceInterface::class)->getShopContext();

        $cacheControl->setContextCacheKey($request, $context, $response);
    }
}
