<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface;
use Shopware\Components\Model\ModelManager;
use Enlight_Controller_Request_Request as Request;
use Enlight_Controller_Response_ResponseHttp as Response;

/**
 * @category  Shopware
 * @package   Shopware\Plugins\Core\HttpCache
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_HttpCache_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var array
     */
    protected $autoNoCacheControllers = array(
        'frontend/checkout' => 'checkout',
        'frontend/note'     => 'checkout',
        'frontend/detail'   => 'detail',
        'frontend/compare'  => 'compare',
    );

    /**
     * @var \Enlight_Controller_Action
     */
    protected $action;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

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
        return array(
            'version' => $this->getVersion(),
            'label'   => $this->getLabel(),
        );
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

        $this->subscribeEvent('Shopware\Models\Article\Article::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Article::postPersist', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Article\Detail::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Detail::postPersist', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Category\Category::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postUpdate', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Banner\Banner::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postUpdate', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Blog\Blog::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Blog\Blog::postUpdate', 'onPostPersist');

        $this->subscribeEvent('Shopware\Models\Emotion\Emotion::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Emotion\Emotion::postUpdate', 'onPostPersist');

        $this->installForm();

        return true;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return array(
            'install' => false,
            'enable' => false,
            'update' => true
        );
    }

    /**
     * Enable plugin method
     * Although plugin is flagged as capability: enable => false,
     * it can still be enabled/disabled programmatically
     *
     * @return bool
     */
    public function enable()
    {
        return true;
    }

    /**
     * Disable plugin method
     * Although plugin is flagged as capability: enable => false,
     * it can still be enabled/disabled programmatically
     *
     * @return bool
     */
    public function disable()
    {
        return true;
    }

    /**
     * Install config-form
     */
    public function installForm()
    {
        $form = $this->Form();

        /** @var $parent \Shopware\Models\Config\Form */
        $parent = $this->Forms()->findOneBy(array('name' => 'Core'));

        $form->setParent($parent);
        $form->setElement('textarea', 'cacheControllers', array(
            'label' => 'Cache-Controller / Zeiten',
            'value' =>
            "frontend/listing 3600\r\n" .
            "frontend/index 3600\r\n" .
            "frontend/detail 3600\r\n" .
            "frontend/campaign 14400\r\n" .
            "widgets/listing 14400\r\n" .
            "frontend/custom 14400\r\n" .
            "frontend/sitemap 14400\r\n" .
            "frontend/blog 14400\r\n" .
            "widgets/index 3600\r\n" .
            "widgets/checkout 3600\r\n" .
            "widgets/compare 3600\r\n" .
            "widgets/emotion 14400\r\n" .
            "widgets/recommendation 14400\r\n" .
            "widgets/lastArticles 3600\n" .
            "widgets/campaign 3600\n"
        ));

        $form->setElement('textarea', 'noCacheControllers', array(
            'label' => 'NoCache-Controller / Tags',
            'value' =>
            "frontend/listing price\n" .
            "frontend/index price\n" .
            "frontend/detail price\n" .
            "widgets/lastArticles detail\n" .
            "widgets/checkout checkout\n" .
            "widgets/compare compare\n" .
            "widgets/emotion price\n"
        ));

        $form->setElement('boolean', 'proxyPrune', array(
            'label' => 'Proxy-Prune aktivieren',
            'description' => 'Das automatische Leeren des Caches aktivieren.',
            'value' => true
        ));

        $form->setElement('text', 'proxy', array(
            'label' => 'Alternative Proxy-Url',
            'description' => 'Link zum Http-Proxy mit „http://“ am Anfang.',
            'value' => null
        ));

        $form->setElement('boolean', 'admin', array(
            'label' => 'Admin-View',
            'description' => 'Cache bei Artikel-Vorschau und Schnellbestellung deaktivieren',
            'value' => false
        ));
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
    protected function getCacheControllers()
    {
        $controllers = $this->Config()->get('cacheControllers');
        if (empty($controllers)) {
            return array();
        }

        $result = array();
        $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
        $controllers = explode("\n", trim($controllers));
        foreach ($controllers as $controller) {
            list($controller, $cacheTime) = explode(" ", $controller);
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
     *    'frontend/detail'  => 'price',
     *    'widgets/checkout' => 'checkout',
     *    'widgets/compare'  => 'compare',
     * )
     * </code>
     *
     * @return array
     */
    protected function getAllowNoCacheControllers()
    {
        $controllers = $this->Config()->get('noCacheControllers');
        if (empty($controllers)) {
            return array();
        }

        $result = array();
        $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
        $controllers = explode("\n", trim($controllers));
        foreach ($controllers as $controller) {
            list($controller, $tag) = explode(" ", $controller);
            $result[strtolower($controller)] = $tag;
        }

        return $result;
    }

    /**
     * Returns the configured proxy-url.
     *
     * Fallbacks to autodetection if proxy-url is not configured and $request is given.
     * Returns null if $request is not given or autodetection fails.
     *
     * @param Request $request
     * @return string|null
     */
    public function getProxyUrl(Request $request = null)
    {
        $proxyUrl = trim($this->Config()->get('proxy'));
        if (!empty($proxyUrl)) {
            return $proxyUrl;
        };

        // if proxy url is not set fall back to host detection
        if ($request !== null && $request->getHttpHost()) {
            return $request->getScheme() . '://'
                   . $request->getHttpHost()
                   . $request->getBaseUrl() . '/';
        }


        /** @var ModelManager $em */
        $em = $this->get('models');
        $repository = $em->getRepository('Shopware\Models\Shop\Shop');

        /** @var Shopware\Models\Shop\Shop $shop */
        $shop = $repository->findOneBy(array('default' => true));

        if (!$shop->getHost()) {
            return null;
        }

        $url = sprintf(
            '%s://%s%s/',
            'http',
            $shop->getHost(),
            $shop->getBasePath()
        );

        return $url;
    }

    /**
     * Do http caching jobs
     *
     * @param Enlight_Controller_ActionEventArgs $args
     */
    public function onPreDispatch(\Enlight_Controller_ActionEventArgs $args)
    {
        $this->action   = $args->getSubject();
        $this->request  = $args->getRequest();
        $this->response = $args->getResponse();

        $this->Application()->Events()->registerListener(
            new Enlight_Event_Handler_Default(
                'Enlight_Controller_Action_PostDispatch',
                array($this, 'onPostDispatch'),
                // must be positioned before ViewRender Plugin
                // so the ESI renderer can be registered
                // before the template is rendered
                399
            )
        );
    }

    /**
     * On post dispatch we try to find affected articleIds displayed during this request
     *
     * @param \Enlight_Controller_ActionEventArgs $args
     */
    public function onPostDispatch(\Enlight_Controller_ActionEventArgs $args)
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

        if ($this->request->getModuleName() != 'frontend' && $this->request->getModuleName() != 'widgets') {
            return;
        }

        // Do not cache if shop(template) is not esi-enabled
        if (!Shopware()->Shop()->get('esi')) {
            return;
        }

        // Enable esi tag output
        $this->registerEsiRenderer();

        $this->addSurrogateControl($this->response);

        $this->addContextCookie($this->request, $this->response);

        $this->setNoCacheCookie();

        /**
         * Emits Shopware_Plugins_HttpCache_ShouldNotCache Event
         */
        if (Enlight()->Events()->notifyUntil(
            // deprecated since SW 4.3, will be removed in SW 5.0
            'Shopware_Plugins_HttpCache_ShouldNotCache',
            array(
                'subject' => $this,
                'action'  => $this->action
            )
        )) {
            return;
        }

        $this->setCacheHeaders();
    }

    /**
     * Callback for event Shopware_CronJob_ClearHttpCache
     *
     * Clears the file-based http-cache storage directory
     *
     * @param Shopware_Components_Cron_CronJob $job
     * @return string
     */
    public function onClearHttpCache(\Shopware_Components_Cron_CronJob $job)
    {
        if ($this->clearCache()) {
            return "Cleared HTTP-Cache\n";
        }
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
     * @param \Enlight_Event_EventArgs $args
     */
    public function onClearCache(\Enlight_Event_EventArgs $args)
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
     * @param \Enlight_Event_EventArgs $args
     */
    public function onInvalidateCacheId(\Enlight_Event_EventArgs $args)
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
     * Sets the shopware cache headers
     */
    public function setCacheHeaders()
    {
        $controllerName = $this->buildControllerName($this->request);

        $cacheControllers = $this->getCacheControllers();
        if (!isset($cacheControllers[$controllerName])) {
            return false;
        }

        if (strpos($this->request->getPathInfo(), '/widgets/index/refreshStatistic') !== false) {
            return false;
        }

        if (strpos($this->request->getPathInfo(), '/captcha/index/rand/') !== false) {
            return false;
        }

        $allowNoCache             = $this->getNoCacheTagsForController($controllerName);
        $noCacheCookies           = $this->getNoCacheTagsFromCookie($this->request);
        $hasMatchingNoCacheCookie = $this->hasArrayIntersection($allowNoCache, $noCacheCookies);

        if ($this->response->isRedirect()) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
            return false;
        }

        if ($hasMatchingNoCacheCookie) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
            return false;
        }

        $allowNoCacheControllers = $this->getAllowNoCacheControllers();
        if (isset($allowNoCacheControllers[$controllerName]) && $this->request->getQuery('nocache') !== null) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
            return false;
        }

        // Don't cache when using admin session
        if (Shopware()->Session()->Admin) {
            return false;
        }

        // Don't cache filled basket or wishlist
        if ($controllerName == 'widgets/checkout' && (!empty(Shopware()->Session()->sBasketQuantity) || !empty(Shopware()->Session()->sNotesQuantity))) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
            return false;
        }

        $cacheTime = (int) $cacheControllers[$controllerName];
        $this->request->setParam('__cache', $cacheTime);
        $this->response->setHeader('Cache-Control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime);

        if (!empty($allowNoCache)) {
            $this->response->setHeader('x-shopware-allow-nocache', implode(', ', $allowNoCache));
        }

        $cacheIds = $this->getCacheIdsFromController($this->action);
        $this->setCacheIdHeader($cacheIds);

        return true;
    }

    /**
     * This methods sets the nocache-cookie if actions in the shop are triggerd
     */
    public function setNoCacheCookie()
    {
        $controllerName = $this->buildControllerName($this->request);

        if (isset($this->autoNoCacheControllers[$controllerName])) {
            $noCacheTag = $this->autoNoCacheControllers[$controllerName];
            $this->setNoCacheTag($noCacheTag);
        }

        if ($controllerName == 'frontend/checkout' || $controllerName == 'frontend/note') {
            if (empty(Shopware()->Session()->sBasketQuantity) && empty(Shopware()->Session()->sNotesQuantity)) {
                // remove checkout-cookie
                $this->setNoCacheTag('checkout', true);
            }
        }

        if ($controllerName == 'frontend/compare' && $this->request->getActionName() == 'delete_all') {
            // remove compare cookie
            $this->setNoCacheTag('compare', true);
        }

        if (!empty(Shopware()->Session()->sNotesQuantity)) {
            // set checkout-cookie
            $this->setNoCacheTag('checkout');
        }

        if ($this->request->getModuleName() == 'frontend' && !empty(Shopware()->Session()->Admin)) {
            // set admin-cookie if admin session is present
            $this->setNoCacheTag('admin');
        }

        if ($controllerName == 'frontend/account') {
            if (in_array($this->request->getActionName(), array('ajax_logout', 'logout'))) {
                $this->setNoCacheTag('');
            }
        }
    }

    /**
     * Set or remove given $noCacheTag from cookie
     *
     * @param string $noCacheTag
     * @param  bool $remove
     */
    public function setNoCacheTag($noCacheTag, $remove = false)
    {
        static $noCacheTags, $shopId;

        if (!isset($noCacheTags)) {
            if ($this->request->getCookie('nocache')) {
                $noCacheTags = $this->request->getCookie('nocache');
                $noCacheTags = explode(', ', $noCacheTags);
            } else {
                $noCacheTags = array();
            }
            $shopId = Shopware()->Shop()->getId();
        }

        if (!empty($noCacheTag)) {
            $noCacheTag .= '-' . $shopId;
        }

        if (empty($noCacheTag)) {
            $newCacheTags = array();
        } elseif ($remove && in_array($noCacheTag, $noCacheTags)) {
            // remove $noCacheTag from $newCacheTags
            $newCacheTags = array_diff($noCacheTags, array($noCacheTag));
        } elseif (!$remove && !in_array($noCacheTag, $noCacheTags)) {
            // add $noCacheTag to $newCacheTags
            $newCacheTags = $noCacheTags;
            $newCacheTags[] = $noCacheTag;
        }

        if (isset($newCacheTags)) {
            $this->response->setCookie(
                'nocache',
                implode(', ', $newCacheTags),
                0,
                $this->request->getBasePath() . '/',
                ($this->request->getHttpHost() == 'localhost') ? null : $this->request->getHttpHost()
            );
        }
    }

    /**
     * Register the action plugin override
     */
    public function registerEsiRenderer()
    {
        $engine = $this->action->View()->Engine();

        $engine->unregisterPlugin(
            Smarty::PLUGIN_FUNCTION,
            'action'
        );
        $engine->registerPlugin(
            Smarty::PLUGIN_FUNCTION,
            'action',
            array($this, 'renderEsiTag')
        );

        if (strpos($engine->getCompileId(), '_esi') === false) {
            $engine->setCompileId($engine->getCompileId() . '_esi');
        }
    }

    /**
     * @param  array  $params
     * @return string
     */
    public function renderEsiTag($params)
    {
        $request = $this->action->Request();

        // Alias from "name" to "action" to be compatible with non-http-cache implementation
        // @see engine/Library/Enlight/Template/Plugins/function.action.php
        if (isset($params['name'])) {
            $params['action'] = $params['name'];
            unset($params['name']);
        }

        if (isset($params['params'])) {
            $params = array_merge((array) $params['params'], $params);
            unset($params['params']);
        }

        if (!isset($params['module'])) {
            $params['module'] = $request->getModuleName();
            if (!isset($params['controller'])) {
                $params['controller'] = $request->getControllerName();
            }
        }

        $targetName = $params['module'] . '/' . $params['controller'];

        $controllerName = $this->buildControllerName($request);

        $allowNoCacheControllers = $this->getAllowNoCacheControllers();

        if (isset($this->autoNoCacheControllers[$controllerName])
            && isset($allowNoCacheControllers[$targetName])
            && $this->autoNoCacheControllers[$controllerName] == $allowNoCacheControllers[$targetName]
        ) {
            $params['nocache'] = 1;
        }

        $url = sprintf(
            '%s/?%s',
            $request->getBaseUrl(),
            http_build_query($params, null, '&')
        );

        return '<esi:include src="' . $url . '" />';
    }

    /**
     * Returns an array of affected cacheids for this $controller
     *
     * @param \Enlight_Controller_Action $controller
     * @return array
     */
    protected function getCacheIdsFromController(\Enlight_Controller_Action $controller)
    {
        $request        = $controller->Request();
        $view           = $controller->View();
        $controllerName = $this->buildControllerName($request);
        $cacheIds       = array();
        $articleIds     = array();

        switch ($controllerName) {
            case 'frontend/blog':
                $categoryId = (int) $request->getParam('sCategory');
                $cacheIds[] = 'c' . $categoryId;

                $blogPost = $view->getAssign('sArticle');
                foreach ($blogPost['assignedArticles'] as $article) {
                    $articleIds[] = $article['id'];
                }

                break;
            case 'widgets/listing':
                $categoryId = (int) $request->getParam('sCategory');
                if (empty($categoryId)) {
                    $categoryId = (int) Shopware()->Shop()->get('parentID');
                }
                $cacheIds[] = 'c' . $categoryId;

                foreach ($view->getAssign('sArticles') as $article) {
                    $articleIds[] = $article['articleID'];
                }

                foreach ($view->getAssign('sCharts') as $article) {
                    $articleIds[] = $article['articleID'];
                }
                break;
            case 'frontend/index':
                $categoryId = (int) Shopware()->Shop()->get('parentID');
                $cacheIds[] = 'c' . $categoryId;

                break;
            case 'widgets/recommendation':
                $article = $view->getAssign('sArticle');

                foreach ($article['sRelatedArticles'] as $article) {
                    $articleIds[] = $article['articleID'];
                }
                foreach ($article['sSimilarArticles'] as $article) {
                    $articleIds[] = $article['articleID'];
                }

                break;
            case 'frontend/detail':
                $articleId = $request->getParam('sArticle', 0);
                $articleIds[] = $articleId;

                break;
            case 'widgets/emotion':
                foreach ($view->getAssign('sEmotions') as $emotion) {
                    $cacheIds[] = 'e' . $emotion['id'];
                    foreach ($emotion['elements'] as $element) {
                        if ($element['component']['name'] == 'Artikel') {
                            $articleIds[] = $element['data']['articleID'];
                            $articleIds[] = $element['data']['articleDetailsID'];
                        } elseif ($element['component']['name'] == 'Artikel-Slider') {
                            foreach ($element['data']['values'] as $value) {
                                $articleIds[] = $value['articleID'];
                                $articleIds[] = $value['articleDetailsID'];
                            }
                        } elseif ($element['component']['name'] == 'Sideview-Element') {
                            foreach ($element['data']['product_data'] as $value) {
                                $articleIds[] = $value['articleID'];
                            }
                        }
                    }
                }

                break;
            case 'frontend/listing':
                foreach ($view->getAssign('sArticles') as $article) {
                    $articleIds[] = $article['articleID'];
                }

                break;
        }

        array_walk($articleIds, function (&$value) {
            $value = 'a' . $value;
        });

        $cacheIds = array_merge($cacheIds, $articleIds);

        return $cacheIds;
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
     * @return array
     */
    protected function getNoCacheTagsFromCookie(Request $request)
    {
        $noCacheCookie = $request->getCookie('nocache', false);

        if (false === $noCacheCookie) {
            return array();
        }

        $noCacheTags = explode(',', $noCacheCookie);
        $noCacheTags = array_map('trim', $noCacheTags);

        return $noCacheTags;
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
     * @param string $controllerName
     * @return array
     */
    protected function getNoCacheTagsForController($controllerName)
    {
        $shopId       = Shopware()->Shop()->getId();
        $allowNoCache = array();
        $autoAdmin    = $this->Config()->get('admin');

        if (!empty($autoAdmin)) {
            $allowNoCache[] = 'admin-' . $shopId;
        }

        $allowNoCacheControllers = $this->getAllowNoCacheControllers();
        if (isset($allowNoCacheControllers[$controllerName])) {
            $allowNoCache[] = $allowNoCacheControllers[$controllerName] . '-' . $shopId;
        }

        return $allowNoCache;
    }

    /**
     * @param $array1
     * @param $array2
     * @return bool
     */
    protected function hasArrayIntersection($array1, $array2)
    {
        $intersection = array_intersect($array1, $array2);

        return !empty($intersection);
    }

    /**
     * Helper function to flag the request with cacheIds
     * to invalidate the caching.
     *
     * @param array $cacheIds
     */
    public function setCacheIdHeader($cacheIds = array())
    {
        $cacheIds = $this->Application()->Events()->filter(
            'Shopware_Plugins_HttpCache_GetCacheIds',
            $cacheIds,
            array('subject' => $this, 'action' => $this->action)
        );

        if (empty($cacheIds)) {
            return;
        }

        $cacheIds = ';' . implode(';', $cacheIds) . ';';
        $this->response->setHeader('x-shopware-cache-id', $cacheIds);
    }

    /**
     * Cache invalidation based on model events
     *
     * @param Enlight_Event_EventArgs $eventArgs
     */
    public function onPostPersist(Enlight_Event_EventArgs $eventArgs)
    {
        if (!$this->Config()->get('proxyPrune')) {
            return;
        }

        $entity = $eventArgs->get('entity');
        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = get_class($entity);
        }

        $cacheIds = array();

        switch ($entityName) {
            case 'Shopware\Models\Article\Article':
                $cacheIds[] = 'a' . $entity->getId();
                break;
            case 'Shopware\Models\Article\Detail':
                $cacheIds[] = 'a' . $entity->getArticleId();
                break;
            case 'Shopware\Models\Category\Category':
                $cacheIds[] = 'c' . $entity->getId();
                break;
            case 'Shopware\Models\Banner\Banner':
                $cacheIds[] = 'c' . $entity->getCategoryId();
                break;
            case 'Shopware\Models\Blog\Blog':
                $cacheIds[] = 'c' . $entity->getCategoryId();
                break;
            case 'Shopware\Models\Emotion\Emotion':
                $cacheIds[] = 'e' . $entity->getId();
                break;
        }

        foreach ($cacheIds as $cacheId) {
            $this->invalidateCacheId($cacheId);
        }
    }

    /**
     * Clears the cache
     *
     * @return bool
     */
    protected function clearCache()
    {
        if ($this->request) {
            $proxyUrl = $this->getProxyUrl($this->request);
        } else {
            $proxyUrl = $this->getProxyUrl();
        }

        if ($proxyUrl === null) {
            return false;
        }

        try {
            $client = new Zend_Http_Client($proxyUrl, array(
                'useragent' => 'Shopware/' . Shopware()->Config()->get('version'),
                'timeout'   => 3,
            ));
            $client->request('BAN');
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Invalidates a given $cacheId
     *
     * This sends a http-ban-request to the proxyUrl containing
     * the $cacheId in the x-shopware-invalidates http-header
     *
     * @param string $cacheId
     * @return bool
     */
    protected function invalidateCacheId($cacheId)
    {
        if (!$this->Config()->get('proxyPrune')) {
            return false;
        }

        $proxyUrl = $this->getProxyUrl($this->request);
        if ($proxyUrl === null) {
            return false;
        }

        if (!$this->request || $this->request->getHeader('Surrogate-Capability') === false) {
            return false;
        }

        try {
            $client = new Zend_Http_Client($proxyUrl, array(
                'useragent' => 'Shopware/' . Shopware()->Config()->get('version'),
                'timeout'   => 5,
            ));

            $client->setHeaders('x-shopware-invalidates', $cacheId)
                   ->request('BAN');
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Helper function to enable the http cache for a single shopware controller.
     *
     * @param int $cacheTime
     * @param array $cacheIds
     */
    public function enableControllerCache($cacheTime = 3600, $cacheIds = array())
    {
        $this->response->setHeader('Cache-Control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime, true);
        $this->setCacheIdHeader($cacheIds);
    }

    /**
     * Helper function to disable the http cache for a single shopware controller
     */
    public function disableControllerCache()
    {
        $this->response->setHeader('Cache-Control', 'private', true);
    }

    /**
     * Adds HTTP headers to specify that the Response needs to be parsed for ESI.
     *
     * This method only adds an ESI HTTP header if the Response has some ESI tags.
     *
     * @param Response $response A Response instance
     */
    private function addSurrogateControl(Response $response)
    {
        $response->setHeader('Surrogate-Control', 'content="ESI/1.0"');
    }

    /**
     * Checks that at least one surrogate has ESI/1.0 capability.
     *
     * @param Request $request A Request instance
     *
     * @return bool    true if one surrogate has ESI/1.0 capability, false otherwise
     */
    private function hasSurrogateEsiCapability(Request $request)
    {
        if (null === $value = $request->getHeader('Surrogate-Capability')) {
            return false;
        }

        return false !== strpos($value, 'ESI/1.0');
    }

    /**
     * @param Request $request
     * @return string
     */
    private function buildControllerName(Request $request)
    {
        $controllerName = strtolower($request->getModuleName() . '/' . $request->getControllerName());

        return $controllerName;
    }

    /**
     * Add context cookie
     *
     * @param Request $request
     * @param Response $response
     */
    private function addContextCookie(Request $request, Response $response)
    {
        /** @var $session Enlight_Components_Session_Namespace */
        $session = $this->get('session');

        if ($session->offsetGet('sCountry')) {
            /** @var ProductContextInterface $productContext */
            $productContext = $this->get('shopware_storefront.context_service')->getProductContext();
            $userContext = sha1(
                json_encode($productContext->getTaxRules()) .
                json_encode($productContext->getCurrentCustomerGroup())
            );
            $response->setCookie(
                'x-cache-context-hash',
                $userContext,
                0,
                $request->getBasePath() . '/',
                ($request->getHttpHost() == 'localhost') ? null : $request->getHttpHost()
            );
        } else {
            if ($request->getCookie('x-cache-context-hash')) {
                $response->setCookie(
                    'x-cache-context-hash',
                    null,
                    strtotime('-1 Year', time()),
                    $request->getBasePath() . '/',
                    ($request->getHttpHost() == 'localhost') ? null : $request->getHttpHost()
                );
            }
        }
    }
}
