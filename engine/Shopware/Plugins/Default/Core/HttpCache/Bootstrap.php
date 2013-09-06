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

/**
 * @category  Shopware
 * @package   Shopware\Plugins\Core\HttpCache
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Plugins_Core_HttpCache_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Installed the plugin.
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Action_PreDispatch',
            'onPreDispatch'
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

        $this->subscribeEvent('Shopware\Models\Article\Article::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Article::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postUpdate', 'onPostPersist');

        $this->installForm();

        return true;
    }

    public function installForm()
    {
        $form = $this->Form();
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
            "widgets/lastArticles 3600\n"
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
            'version'     => $this->getVersion(),
            'label'       => $this->getLabel(),
        );
    }

    /**
     * @var int[]
     */
    protected $cacheControllers;

    /**
     * @var string[]
     */
    protected $autoNoCacheControllers = array(
        'frontend/checkout' => 'checkout',
        'frontend/note'     => 'checkout',
        'frontend/detail'   => 'detail',
        'frontend/compare'  => 'compare',
    );

    /**
     * @var string[]
     */
    protected $allowNoCacheControllers;

    /**
     * @var string
     */
    protected $proxyUrl;

    /**
     * @var Enlight_Controller_Action
     */
    protected $action;

    /**
     * @var Enlight_Controller_Request_RequestHttp
     */
    protected $request;

    /**
     * @var Enlight_Controller_Response_ResponseHttp
     */
    protected $response;

    /**
     * Ready plugin configuration and transforms config
     */
    protected function initConfig()
    {
        $controllers = $this->Config()->cacheControllers;
        if (!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            $this->cacheControllers = array();
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $this->cacheControllers[strtolower($controller)] = $cacheTime;
            }
        }

        $controllers = $this->Config()->noCacheControllers;
        if (!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            $this->allowNoCacheControllers = array();
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $this->allowNoCacheControllers[strtolower($controller)] = $cacheTime;
            }
        }

        $proxy = $this->Config()->proxy;
        if (!empty($proxy)) {
            $this->proxyUrl = $proxy;
        }

        if ($this->proxyUrl === null) {
            if ($this->request->getHttpHost()) {
                $this->proxyUrl = $this->request->getScheme() . '://'
                    . $this->request->getHttpHost()
                    . $this->request->getBaseUrl() . '/';
            }
        }
    }

    /**
     * Do http caching jobs
     *
     * @param \Enlight_Controller_EventArgs $args
     */
    public function onPreDispatch($args)
    {
        $this->action   = $args->getSubject();
        $this->request  = $args->getRequest();
        $this->response = $args->getResponse();

        if ($this->request->getHeader('Surrogate-Capability') === false) {
            return;
        }

        $this->initConfig();

        if ($this->request->getModuleName() != 'frontend' && $this->request->getModuleName() != 'widgets') {
            return;
        }

        if (!Shopware()->Shop()->get('esi')) {
            return;
        }

        // Allow ESI tags
        $this->response->setHeader('Surrogate-Control', 'content="ESI/1.0"');

        $this->setCacheHeaders();
        $this->setNoCacheCookie();
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
        // If local file-based proxy is used delete cache files from filesystem
        $cacheOptions = Shopware()->getOption('HttpCache');
        if (isset($cacheOptions['cache_dir']) && is_dir($cacheOptions['cache_dir'])) {
            $cacheDir = $cacheOptions['cache_dir'];
            $counter  = 0;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($cacheDir),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $path) {
                if ($path->isDir()) {
                    rmdir($path->__toString());
                } else {
                    $counter++;
                    unlink($path->__toString());
                }
            }

            return "Removed $counter files from $cacheDir\n";
        }

        return 'HTTP-Cache Directory not set.';
    }


    /**
     * Sets the shopware cache headers
     */
    public function setCacheHeaders()
    {
        $controllerName = strtolower($this->request->getModuleName()) . '/' . strtolower($this->request->getControllerName());

        if (isset($this->cacheControllers[$controllerName])) {
            // Enable esi tag output
            $this->registerEsiRenderer();
        }

        if ($this->response->isRedirect()) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
        } elseif (isset($this->allowNoCacheControllers[$controllerName]) && $this->request->getQuery('nocache') !== null) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
        } elseif (isset($this->cacheControllers[$controllerName])) {
            $this->setCacheIdHeader();

            $cacheTime = (int) $this->cacheControllers[$controllerName];
            $this->request->setParam('__cache', $cacheTime);
            $this->response->setHeader('Cache-Control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime, true);
        }

        $shopId = Shopware()->Shop()->getId();
        $allowNoCache = array();
        if (!empty($this->Config()->admin)) {
            $allowNoCache[] = 'admin-' . $shopId;
        }

        if (isset($this->allowNoCacheControllers[$controllerName])) {
            $allowNoCache[] = $this->allowNoCacheControllers[$controllerName] . '-' . $shopId;
        }

        if (!empty($allowNoCache)) {
            $this->response->setHeader('x-shopware-allow-nocache', implode(', ', $allowNoCache));
        }
    }

    /**
     *
     */
    public function setNoCacheCookie()
    {
        $controllerName = strtolower($this->request->getModuleName()) . '/' . strtolower($this->request->getControllerName());

        if (isset($this->autoNoCacheControllers[$controllerName])) {
            $noCacheTag = $this->autoNoCacheControllers[$controllerName];
            $this->setNoCacheTag($noCacheTag);
        }

        if (Shopware()->Shop()->get('defaultcustomergroup') != Shopware()->System()->sUSERGROUP) {
            $this->setNoCacheTag('price');
        }

        if ($controllerName == 'frontend/checkout' || $controllerName == 'frontend/note') {
            if (empty(Shopware()->Session()->sBasketQuantity) && empty(Shopware()->Session()->sNotesQuantity)) {
                $this->setNoCacheTag('checkout', true);
            }
        }

        if ($controllerName == 'frontend/compare' && $this->request->getActionName() == 'delete_all') {
            $this->setNoCacheTag('compare', true);
        }

        if (!empty(Shopware()->Session()->sNotesQuantity)) {
            $this->setNoCacheTag('checkout');
        }

        if ($this->request->getModuleName() == 'frontend' && !empty(Shopware()->Session()->Admin)) {
            $this->setNoCacheTag('admin');
        }

        if ($controllerName == 'frontend/account') {
            if (in_array($this->request->getActionName(), array('ajax_logout', 'logout'))) {
                $this->setNoCacheTag('');
            }
        }
    }

    /**
     * @param $noCacheTag
     * @param  bool $remove
     * @return void
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
            $newCacheTags = array_diff($noCacheTags, array($noCacheTag));
        } elseif (!$remove && !in_array($noCacheTag, $noCacheTags)) {
            $newCacheTags = $noCacheTags;
            $newCacheTags[] = $noCacheTag;
        }

        if (isset($newCacheTags)) {
            $this->response->setCookie(
                'nocache',
                implode(', ', $newCacheTags),
                0,
                $this->request->getBasePath() . '/',
                $this->request->getHttpHost()
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
        $controllerName = strtolower($request->getModuleName()) . '/' . strtolower($request->getControllerName());

        if (isset($this->autoNoCacheControllers[$controllerName])
            && isset($this->allowNoCacheControllers[$targetName])
            && $this->autoNoCacheControllers[$controllerName] == $this->allowNoCacheControllers[$targetName]
        ) {
            $params['nocache'] = 1;
        }

        $url = sprintf('%s://%s%s/?%s',
            $request->getScheme(),
            $request->getHttpHost(),
            $request->getBaseUrl(),
            http_build_query($params, null, '&')
        );

        return '<esi:include src="' . $url . '" />';
    }

    /**
     * Invalidation
     *
     * Set x-shopware-cache-id user for cache invalidation on proxy side
     */
    public function setCacheIdHeader()
    {
        $controllerName = strtolower($this->request->getModuleName()) . '/' . strtolower($this->request->getControllerName());

        $cacheIds = array();

        switch ($controllerName) {
            case 'widgets/listing':
                $categoryId = (int) $this->request->getParam('sCategory');
                if (empty($categoryId)) {
                    $categoryId = (int) Shopware()->Shop()->get('parentID');
                }
                $cacheIds[] = 'c-' . $categoryId;
                break;
            case 'frontend/index':
                $categoryId = (int) Shopware()->Shop()->get('parentID');
                $cacheIds[] = 'c-' . $categoryId;
                break;
            case 'frontend/detail':
                $articleId = $this->request->getParam('sArticle', 0);
                $cacheIds[] = 'a-' . $articleId;
            case 'frontend/listing':
                $categoryId = $this->request->getParam('sCategory', 0);

                while ($categoryId > 1) {
                    $category = Shopware()->Models()->find(
                        'Shopware\Models\Category\Category', $categoryId
                    );
                    if ($category === null) {
                        break;
                    }

                    $cacheIds[] = 'c-' . $category->getId();
                    $categoryId = $category->getParentId();
                }
                break;
        }

        $this->setCacheIds($cacheIds);
    }

    /**
     * Helper function to flag the request with affected article or category ids
     * to invalidate the caching.
     *
     * @param array $cacheIds
     */
    public function setCacheIds($cacheIds = array())
    {
        if (empty($cacheIds)) {
            return;
        }

        $request = $this->request;

        $uri = sprintf('%s://%s%s',
            $request->getScheme(),
            $request->getHttpHost(),
            $request->getRequestUri()
        );

        if ($request->getCookie('shop', false)) {
            $uri .= '&__shop=' . $request->getCookie('shop');
        }

        if ($request->getCookie('currency', false)) {
            $uri .= '&__currency=' . $request->getCookie('currency');
        }

        $cacheIds = '|' . implode('|', $cacheIds) . '|';

        Shopware()->Db()->query(
            'INSERT IGNORE INTO s_cache_log (url, cache_keys) VALUES (?, ?)',
            array($uri, $cacheIds)
        );

        $this->response->setHeader('x-shopware-cache-id', $cacheIds);
    }

    /**
     * Cache invalidation based on model events
     *
     * @param Enlight_Event_EventArgs $eventArgs
     */
    public function onPostPersist(Enlight_Event_EventArgs $eventArgs)
    {
        if (empty($this->Config()->proxyPrune)) {
            return;
        }

        if ($this->proxyUrl === null || $this->request->getHeader('Surrogate-Capability') === false) {
            return;
        }

        $entity = $eventArgs->get('entity');
        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = get_class($eventArgs->getEntity());
        }

        $cacheIds = array();

        switch ($entityName) {
            case 'Shopware\Models\Article\Article':
                $cacheIds[] = 'a-' . $entity->getId();
                break;
            case 'Shopware\Models\Category\Category':
                $cacheIds[] = 'c-' . $entity->getId();
                break;
            case 'Shopware\Models\Banner\Banner':
                $cacheIds[] = 'c-' . $entity->getCategoryId();
                break;
        }

        $client = new Zend_Http_Client(null, array(
            'useragent' => 'Shopware/' . Shopware()->Config()->version,
            'timeout' => 5,
        ));

        try {
            foreach ($cacheIds as $cacheId) {
                $cacheId = '%|' . $cacheId . '|%';

                $urls = Shopware()->Db()->fetchAll('SELECT url FROM s_cache_log WHERE cache_keys LIKE ?', $cacheId);
                foreach ($urls as $url) {
                    $purgeUrl = $url['url'];
                    $client->setUri($purgeUrl)->request('PURGE');
                }

                Shopware()->Db()->query('DELETE FROM s_cache_log WHERE cache_keys LIKE ?', $cacheId);
            }
        } catch (Exception $e) { }
    }


    /**
     * Helper function to enable the http cache for a single shopware controller.
     * @param int $cacheTime
     * @param array $cacheIds
     */
    public function enableControllerCache($cacheTime = 3600, $cacheIds = array())
    {
        $this->response->setHeader('Cache-Control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime, true);
        $this->registerEsiRenderer();
        $this->setCacheIds($cacheIds);
    }


    /**
     * Helper function to disable the http cache for a single shopware controller
     */
    public function disableControllerCache()
    {
        $this->response->setHeader('Cache-Control', 'private', true);
    }

}
