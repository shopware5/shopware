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

/**
 * Shopware Router Plugin
 *
 * @category   Shopware
 * @package    Shopware_Plugins
 * @subpackage Core
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 */
class Shopware_Plugins_Core_Router_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Router config
     *
     * @var mixed
     */
    protected $removeCategory = false,
        $baseFile = null,
        $basePath = null,
        $secureBasePath = null,
        $secureControllers = array('account', 'checkout', 'register', 'ticket', 'note', 'compare'),
        $shop,
        $secure = false;

    /**
     * Init plugin method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteStartup',
            'onRouteStartup'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_RouteShutdown',
            'onRouteShutdown'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Router_FilterAssembleParams',
            'onFilterAssemble'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Router_FilterUrl',
            'onFilterUrl'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Router_Assemble',
            'onAssemble',
            100
        );
        return true;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        if (strpos($request->getPathInfo(), '/backend') === 0
            || strpos($request->getPathInfo(), '/api') === 0
        ) {
            return;
        }

        try {
            /** @var $repository Shopware\Models\Shop\Repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            if (($shop = $request->getQuery('__shop')) !== null) {
                $shop = $repository->getActiveById($shop);
            } elseif (($shop = $request->getCookie('shop')) !== null) {
                $shop = $repository->getActiveById($shop);
            } if($shop === null) {
                $shop = $repository->getActiveByRequest($request);
            } if($shop === null) {
                $shop = $repository->getActiveDefault();
            }
        } catch (Exception $e) {
            $args->getResponse()->setException($e);
            return;
        }

        if (!$shop->getHost()) {
            $shop->setHost($request->getHttpHost());
        }
        if (!$shop->getBaseUrl()) {
            $preferBasePath = $this->Application()->Config()->preferBasePath;
            $shop->setBaseUrl($preferBasePath ? $request->getBasePath() : $request->getBaseUrl());
        }
        if (!$shop->getBasePath()) {
            $shop->setBasePath($request->getBasePath());
        }
        if (!$shop->getSecureBasePath()) {
            $shop->setSecureBasePath($shop->getBasePath());
        }
        if (!$shop->getSecureHost()) {
            $shop->setSecureHost($shop->getHost());
        }

        $main = $shop->getMain() !== null ? $shop->getMain() : $shop;
        if(!$main->getDefault()) {
            $main = $repository->getActiveDefault();
            $shop->setTemplate($main->getTemplate());
            $shop->setHost($main->getHost());
            $shop->setSecureHost($main->getSecureHost() ?: $main->getHost());
        }

        // Read original base path for resources
        $request->getBasePath();

        if ($request->isSecure()) {
            $request->setBaseUrl($shop->getSecureBaseUrl());
        } else {
            $request->setBaseUrl($shop->getBaseUrl());
        }

        // Update path info
        $request->setPathInfo();

        if (($host = $request->getHeader('X_FORWARDED_HOST')) !== null
            && $host === $shop->getSecureHost()
        ) {
            $request->setSecure();
            $request->setBasePath($shop->getSecureBasePath());
            $request->setBaseUrl($shop->getSecureBaseUrl());
            $request->setHttpHost($shop->getSecureHost());
        }

        $shop->registerResources(Shopware()->Bootstrap());
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_EventArgs $args
     */
    public function onRouteShutdown(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        $bootstrap = $this->Application()->Bootstrap();
        if ($bootstrap->issetResource('Shop')) {
            $shop = $this->Application()->Shop();

            if ($request->isSecure() && $request->getHttpHost() !== $shop->getSecureHost()) {
                $newPath = $request::SCHEME_HTTPS . '://' . $shop->getSecureHost() . $shop->getBasePath();
            } elseif (!$request->isSecure() && $request->getHttpHost() !== $shop->getHost()) {
                $newPath = $request::SCHEME_HTTP . '://' . $shop->getHost() . $shop->getBasePath();
            }

            // Strip /shopware.php/ from string and perform a redirect
            $preferBasePath = $this->Application()->Config()->preferBasePath;
            if ($preferBasePath && strpos($request->getPathInfo(), '/shopware.php/') === 0) {
                $removePath = $request->getBasePath() . '/shopware.php';
                $newPath = str_replace($removePath, $request->getBasePath(), $request->getRequestUri());
            }

            if(isset($newPath)) {
                $response->setRedirect($newPath, 301);
            } else {
                $this->upgradeShop($request, $response);
                $this->initServiceMode($request);
            }
        }

        $this->fixRequest($request);
        $this->initConfig($request);
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     */
    protected function initServiceMode($request)
    {
        $config = $this->Application()->Config();
        if (!empty($config->setOffline) && strpos($config->offlineIp, $request->getClientIp(false)) === false) {
            if ($request->getControllerName() != 'error') {
                $request->setControllerName('error')->setActionName('service')->setDispatched(false);
            }
        }
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     */
    protected function upgradeShop($request, $response)
    {
        $bootstrap = $this->Application()->Bootstrap();
        $shop = $this->Application()->Shop();

        $cookieKey = null;
        $cookieValue = null;

        switch (true) {
            case $request->getPost('sLanguage') !== null:
                $cookieKey = 'shop';
                $cookieValue = $request->getPost('sLanguage');
                break;
            case $request->getPost('sCurrency') !== null:
                $cookieKey = 'currency';
                $cookieValue = $request->getPost('sCurrency');
                break;
            case $request->getPost('__shop') !== null:
                $cookieKey = 'shop';
                $cookieValue = $request->getPost('__shop');
                break;
            case $request->getQuery('__shop') !== null:
                $cookieKey = 'shop';
                $cookieValue = $request->getQuery('__shop');
                break;
            case $request->getPost('__currency') !== null:
                $cookieKey = 'currency';
                $cookieValue = $request->getPost('__currency');
                break;
            case $request->getQuery('__template') !== null:
                $cookieKey = 'template';
                $cookieValue = $request->getQuery('__template');
                break;
        }

        // Redirect on shop change
        if ($cookieKey === 'shop') {
            /** @var $repository Shopware\Models\Shop\Repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
            $newShop = $repository->getActiveById($cookieValue);
            if ($newShop !== null) {
                if (($newShop->getHost() !== null && $newShop->getHost() !== $shop->getHost())
                    || ($newShop->getBaseUrl() !== null && $newShop->getBaseUrl() !== $shop->getBaseUrl())
                ) {
                    $url = sprintf('%s://%s%s%s',
                        $request::SCHEME_HTTP,
                        $newShop->getHost(),
                        $newShop->getBaseUrl(),
                        '/'
                    );
                    $path = rtrim($newShop->getBasePath(), '/') . '/';
                    $response->setCookie($cookieKey, $cookieValue, 0, $path);
                    $response->setRedirect($url);
                    return;
                }
            }
        }

        // Refresh on shop change
        if ($cookieKey !== null && $cookieKey != 'template') {
            $path = rtrim($shop->getBasePath(), '/') . '/';
            $response->setCookie($cookieKey, $cookieValue, 0, $path);
            if($request->isPost() && $request->getQuery('__shop') === null) {
                $url = sprintf('%s://%s%s',
                    $request->getScheme(),
                    $request->getHttpHost(),
                    $request->getRequestUri()
                );
                $response->setRedirect($url);
                return;
            }
        }

        // Upgrade currency
        if($request->getCookie('currency') !== null) {
            $currencyValue = $request->getCookie('currency');
            foreach($shop->getCurrencies() as $currency) {
                if($currencyValue == $currency->getId()
                    || $currencyValue == $currency->getCurrency()) {
                    $shop->setCurrency($currency);
                    break;
                }
            }
        }

        // Start session in frontend controllers
        $session = Shopware()->Session();

        if ($cookieKey !== null) {
            $session->$cookieKey = $cookieValue;
        }

        // Refresh basket on currency change
        if(isset($session->sBasketCurrency) && $shop->getCurrency()->getId() != $session->sBasketCurrency) {
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }

        // Upgrade template
        if(isset($session->template) && !empty($session->Admin)) {
            $repository = 'Shopware\Models\Shop\Template';
            $repository = Shopware()->Models()->getRepository($repository);
            $template = $session->template;
            $template = $repository->findOneBy(array('template' => $template));
            if($template !== null) {
                $shop->setTemplate($template);
            } else {
                unset($session->template);
            }
        } else {
            unset($session->template);
        }

        // Save upgrades
        $shop->registerResources($bootstrap);

        if($request->isSecure()) {
            $template = $bootstrap->getResource('Template');
            $template->setCompileId($template->getCompileId() . '_secure');
        }
    }

    /**
     * @param $request
     */
    protected function initConfig($request)
    {
        $this->basePath = $request->getHttpHost() . $request->getBaseUrl();
        $this->secureBasePath = $this->basePath;
    }

    /**
     * Init router shop config
     */
    protected function initShopConfig()
    {
        $bootstrap = $this->Application()->Bootstrap();

        if(!$bootstrap->hasResource('Shop')) {
            return;
        }

        /** @var $shop \Shopware\Models\Shop\Shop */
        $this->shop = $shop = $bootstrap->getResource('Shop');
        $this->secure = $shop->getSecure();
        $this->basePath = $shop->getHost() . $shop->getBaseUrl();
        if ($shop->getSecure()) {
            $this->secureBasePath = $shop->getSecureHost() . $shop->getSecureBaseUrl();
        } else {
            $this->secureBasePath = $this->basePath;
        }

        /** @var $config Shopware_Components_Config */
        $config = $bootstrap->getResource('Config');
        $this->removeCategory = $config->routerRemoveCategory;
        $this->baseFile = $config->baseFile;
    }

    /**
     * @param Enlight_Controller_Request_RequestHttp $request
     */
    protected function fixRequest($request)
    {
        $aliases = array(
            'sViewport' => 'controller',
            'sAction' => 'action',
        );
        foreach ($aliases as $key => $alias) {
            if (($value = $request->getParam($key)) !== null) {
                $request->setParam($alias, $value);
                $request->setAlias($key, $alias);
            }
        }
        $request->setQuery($request->getUserParams() + $request->getQuery());
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_Router_EventArgs $args
     * @return array|mixed
     */
    public function onFilterAssemble(Enlight_Controller_Router_EventArgs $args)
    {
        $params = $args->getReturn();
        $request = $args->getRequest();

        $aliases = array(
            'sDetails' => 'sArticle',
            'cCUSTOM' => 'sCustom',
            'controller' => 'sViewport',
            'action' => 'sAction',
        );
        foreach ($aliases as $key => $alias) {
            if (isset($params[$key])) {
                $params[$alias] = $params[$key];
                unset ($params[$key]);
            }
        }

        if (!empty($params['sDetails']) && !empty($params['sViewport']) && $params['sViewport'] == 'detail') {
            $params['sArticle'] = $params['sDetails'];
            unset($params['sDetails']);
        }

        if (empty($params['module'])) {
            $params['module'] = $request->getModuleName() ? : '';
            if ($params['module'] == 'widgets') {
                $params['module'] = 'frontend';
            } elseif (empty($params['sViewport'])) {
                $params['sViewport'] = $request->getControllerName() ? : 'index';
                if (empty($params['sAction'])) {
                    $params['sAction'] = $request->getActionName() ? : 'index';
                }
            }
        }

        if (isset($params['sAction'])) {
            $params = array_merge(array('sAction' => null), $params);
        }
        if (isset($params['sViewport'])) {
            $params = array_merge(array('sViewport' => null), $params);
        }

        unset($params['sUseSSL'], $params['fullPath'], $params['appendSession'], $params['forceSecure'], $params['sCoreId']);
        unset($params['rewriteOld'], $params['rewriteAlias'], $params['rewriteUrl']);

        if (!empty($params['sViewport']) && $params['sViewport'] == 'detail' && !empty($this->removeCategory)) {
            unset($params['sCategory']);
        }

        return $params;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_Router_EventArgs $args
     * @return mixed|string
     */
    public function onFilterUrl(Enlight_Controller_Router_EventArgs $args)
    {
        $params = $args->getParams();
        $userParams = $args->getUserParams();

        if (!empty($params['module']) && $params['module'] != 'frontend'
            && empty($userParams['forceSecure'])
            && empty($userParams['fullPath'])
        ) {
            return $args->getReturn();
        }

        if($this->shop === null && $params['module'] == 'frontend') {
            $this->initShopConfig();
        }

        if (empty($this->secure)) {
            $secure = false;
        } elseif (!empty($userParams['sUseSSL']) || !empty($userParams['forceSecure'])) {
            $secure = true;
        } elseif (!empty($params['sViewport']) &&
            in_array($params['sViewport'], $this->secureControllers)
        ) {
            $secure = true;
        } else {
            $secure = false;
        }

        $url = '';

        if (!isset($userParams['fullPath']) || !empty($userParams['fullPath'])) {
            $url = $secure ? 'https://' : 'http://';
            $url .= $secure ? $this->secureBasePath : $this->basePath;
            $url .= '/';
        }

        $url .= $args->getReturn();

        if (!empty($userParams['appendSession'])) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= session_name() . '=' . session_id();
            $url .= '&__shop=' . $this->shop->getId();
        }

        return $url;
    }

    /**
     * Event listener method
     *
     * @param Enlight_Controller_Router_EventArgs $args
     * @return array
     */
    public function onAssemble(Enlight_Controller_Router_EventArgs $args)
    {
        $params = $args->getParams();
        if (isset($params['sViewport'])) {
            $params['controller'] = $params['sViewport'];
        }
        if (isset($params['sAction'])) {
            $params['action'] = $params['sAction'];
        }
        unset($params['title'], $params['sViewport'], $params['sAction']);
        return $args->getSubject()->assembleDefault($params);
    }

    /**
     * Returns capabilities
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
}
