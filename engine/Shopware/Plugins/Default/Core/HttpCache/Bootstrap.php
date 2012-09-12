<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage HttpCache
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

/**
 * Shopware Application
 *
 * todo@all: Documentation
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
        $this->subscribeEvent('Shopware\Models\Article\Article::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postPersist', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Article\Article::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Category\Category::postUpdate', 'onPostPersist');
        $this->subscribeEvent('Shopware\Models\Banner\Banner::postUpdate', 'onPostPersist');

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
        $form->setElement('boolean', 'proxyBan', array(
            'label' => 'Proxy-BAN aktivieren',
            'description' => 'Das automatische Leeren des Caches aktivieren.',
            'value' => false
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

        return true;
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
        return '1.0.4';
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return array(
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
            'description' => 'Achtung! Diese Erweiterung ist derzeit noch in der Beta-Phase. Installation und Einsatz ohne Gewährleistung.'
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
        'frontend/note' => 'checkout',
        'frontend/detail' => 'detail',
        'frontend/compare' => 'compare',
    );

    /**
     * @var string[]
     */
    protected $allowNoCacheControllers;
    /**
     * @var string[]
     */
    protected $controllerOptions = array(
        'frontend/listing' => array(
            'sSort' => true,
            'sPerPage' => true,
            'sTemplate' => true,
            'sSupplier' => true,
            'sFilterProperties' => true
       )
    );

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
     *
     */
    protected function initConfig()
    {
        $controllers = $this->Config()->cacheControllers;
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            $this->cacheControllers = array();
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $this->cacheControllers[$controller] = $cacheTime;
            }
        }

        $controllers = $this->Config()->noCacheControllers;
        if(!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            $this->allowNoCacheControllers = array();
            foreach($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $this->allowNoCacheControllers[$controller] = $cacheTime;
            }
        }

        $proxy = $this->Config()->proxy;
        if(!empty($proxy)) {
            $this->proxyUrl = $proxy;
        }

        if($this->proxyUrl === null) {
            if($this->request->getHttpHost()) {
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
        $this->action = $action = $args->getSubject();
        $this->request = $request = $args->getRequest();
        $this->response = $response = $args->getResponse();

        if ($request->getHeader('Surrogate-Capability') === false) {
            return;
        }

        $this->initConfig();

        if ($request->getModuleName() != 'frontend' && $request->getModuleName() != 'widgets') {
            return;
        }
        if (!Shopware()->Shop()->get('esi')) {
            return;
        }

        // Allow esi tags
        $this->response->setHeader('Surrogate-Control', 'content="ESI/1.0"');

        $this->setControllerOptions();
        $this->setCacheHeaders();
        $this->setNoCacheCookie();
        $this->setCacheIdHeader();
    }

    /**
     * Sets the shopware cache headers
     */
    public function setControllerOptions()
    {
        $controllerName = $this->request->getModuleName() . '/' . $this->request->getControllerName();
        if(isset($this->controllerOptions[$controllerName]) && $this->request->getParam('rewriteUrl')) {
            $options = $this->controllerOptions[$controllerName];
            $query = $this->request->getQuery();
            $result = array_intersect_key($query, $options);
            $cookie = 'controller-options-'
                . $this->request->getBaseUrl()
                . $this->request->getPathInfo();
            if(count($result) > 0) {
                $options = $this->request->getCookie($cookie);
                if($options !== null) {
                    parse_str($options, $options);
                } else {
                    $options = array();
                }
                $options = array_merge($options, $result);
                ksort($options);
                $options = http_build_query($options, '', '&');
                $this->response->setCookie(
                    $cookie, $options, 0,
                    null, //$this->request->getBasePath() . '/',
                    $this->request->getHttpHost()
                );
                $location = array_diff($query, $result);
                $location = $this->action->Front()->Router()->assemble($location);
                $this->action->redirect($location);
            } else {
                $options = $this->request->getCookie($cookie);
                if($options !== null) {
                    parse_str($options, $options);
                    $this->request->setQuery($options);
                }
            }
        }
    }

    /**
     * Sets the shopware cache headers
     */
    public function setCacheHeaders()
    {
        $controllerName = $this->request->getModuleName() . '/' . $this->request->getControllerName();

        if(isset($this->cacheControllers[$controllerName])) {
            // Enable esi tag output
            $this->registerEsiRenderer();
        }

        if ((isset($this->allowNoCacheControllers[$controllerName])
            && $this->request->getQuery('nocache') !== null)
            || $this->response->isRedirect()
            || (!$this->request->getParam('rewriteUrl') && isset($this->controllerOptions[$controllerName]))
        ) {
            $this->response->setHeader('Cache-Control', 'private, no-cache');
        } elseif (isset($this->cacheControllers[$controllerName])) {
            $cacheTime = (int)$this->cacheControllers[$controllerName];
            $this->request->setParam('__cache', $cacheTime);
            $this->response->setHeader('Cache-Control', 'public, max-age=' . $cacheTime . ', s-maxage=' . $cacheTime);
        }

        $shopId = Shopware()->Shop()->getId();
        $allowNoCache = array();
        if(!empty($this->Config()->admin)) {
            $allowNoCache[] = 'admin-' . $shopId;
        }
        if (isset($this->allowNoCacheControllers[$controllerName])) {
            $allowNoCache[] = $this->allowNoCacheControllers[$controllerName] . '-' . $shopId;
        }
        if(!empty($allowNoCache)) {
            $this->response->setHeader('x-shopware-allow-nocache', implode(', ', $allowNoCache));
        }
    }

    /**
     *
     */
    public function setCacheIdHeader()
    {
        $controllerName = $this->request->getModuleName() . '/' . $this->request->getControllerName();

        $cacheIds = array();

        switch ($controllerName) {
            case 'widgets/listing':
                $categoryId = (int)$this->request->getParam('sCategory');
                if (empty($categoryId)) {
                    $categoryId = (int)Shopware()->Shop()->get('parentID');
                }
                $cacheIds[] = 'c-' . $categoryId;
                break;
            case 'frontend/index':
                $categoryId = (int)Shopware()->Shop()->get('parentID');
                $cacheIds[] = 'c-' . $categoryId;
                break;
            case 'frontend/detail':
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

        if (!empty($cacheIds)) {
            $this->response->setHeader('x-shopware-cache-id', implode(', ', $cacheIds));
        }
    }

    /**
     *
     */
    public function setNoCacheCookie()
    {
        $controllerName = $this->request->getModuleName() . '/' . $this->request->getControllerName();
        if (isset($this->autoNoCacheControllers[$controllerName])) {
            $noCacheTag = $this->autoNoCacheControllers[$controllerName];
            $this->setNoCacheTag($noCacheTag);
        }

        if (Shopware()->Shop()->get('defaultcustomergroup') != Shopware()->System()->sUSERGROUP) {
            $this->setNoCacheTag('price');
        }

        if ($controllerName == 'frontend/checkout' || $controllerName == 'frontend/note') {
            if(empty(Shopware()->Session()->sBasketQuantity) && empty(Shopware()->Session()->sNotesQuantity)) {
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

        if($controllerName == 'frontend/account') {
            if(in_array($this->request->getActionName(), array('ajax_logout', 'logout'))) {
                $this->setNoCacheTag('');
            }
        }
    }

    /**
     * @param $noCacheTag
     * @param bool $remove
     * @return void
     */
    public function setNoCacheTag($noCacheTag, $remove = false)
    {
        static $noCacheTags, $shopId;
        if(!isset($noCacheTags)) {
            if($this->request->getCookie('nocache')) {
                $noCacheTags = $this->request->getCookie('nocache');
                $noCacheTags = explode(', ', $noCacheTags);
            } else {
                $noCacheTags = array();
            }
            $shopId = Shopware()->Shop()->getId();
        }
        if(!empty($noCacheTag)) {
            $noCacheTag .= '-' . $shopId;
        }
        if(empty($noCacheTag)) {
            $newCacheTags = array();
        } elseif($remove && in_array($noCacheTag, $noCacheTags)) {
            $newCacheTags = array_diff($noCacheTags, array($noCacheTag));
        } elseif(!$remove && !in_array($noCacheTag, $noCacheTags)) {
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
     * @param Enlight_Event_EventArgs $eventArgs
     */
    public function onPostPersist(Enlight_Event_EventArgs $eventArgs)
    {
        if(empty($this->Config()->proxyBan)) {
            return;
        }
        if($this->proxyUrl === null || $this->request->getHeader('Surrogate-Capability') === false) {
            return;
        }

        $entity = $eventArgs->get('entity');
        if ($entity instanceof \Doctrine\ORM\Proxy\Proxy) {
            $entityName = get_parent_class($entity);
        } else {
            $entityName = get_class($eventArgs->getEntity());
        }

        $categoryIds = array();
        $articleIds = array();

        switch ($entityName) {
            case 'Shopware\Models\Article\Article':
                $articleIds[] = $entity->getId();
                foreach ($entity->getCategories() as $category) {
                    $categoryIds[] = $category->getId();
                }
                break;
            case 'Shopware\Models\Category\Category':
                $categoryIds[] = $entity->getId();
                break;
            case 'Shopware\Models\Banner\Banner':
                $categoryIds[] = $entity->getCategoryId();
                break;
        }

        $client = new Zend_Http_Client(null, array(
            'useragent' => 'Shopware/' . Shopware()->Config()->version,
            'timeout' => 5,
        ));

        try {
            foreach ($categoryIds as $categoryId) {
                $client->setUri(
                    $this->proxyUrl . urlencode('c-' . $categoryId)
                )->request('BAN');
            }
            foreach ($articleIds as $articleId) {
                $client->setUri(
                    $this->proxyUrl . urlencode('a-' . $articleId)
                )->request('BAN');
            }
        } catch(Exception $e) { }
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
     * @param   array $params
     * @return  string
     */
    public function renderEsiTag($params)
    {
        $request = $this->action->Request();

        if (isset($params['params'])) {
            $params = array_merge((array)$params['params'], $params);
            unset($params['params']);
        }

        if (!isset($params['module'])) {
            $params['module'] = $request->getModuleName();
            if (!isset($params['controller'])) {
                $params['controller'] = $request->getControllerName();
            }
        }

        $targetName = $params['module'] . '/' . $params['controller'];
        $controllerName = $request->getModuleName() . '/' . $request->getControllerName();

        if (isset($this->autoNoCacheControllers[$controllerName])
            && isset($this->allowNoCacheControllers[$targetName])
            && $this->autoNoCacheControllers[$controllerName]
                == $this->allowNoCacheControllers[$targetName]
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
}
