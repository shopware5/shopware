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

use Enlight_Controller_Request_Request as Request;
use Shopware\Models\Shop\DetachedShop;
use Shopware\Models\Shop\Repository;
use Shopware\Models\Shop\Shop;

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
            || strpos($request->getPathInfo(), '/api/') === 0
        ) {
            return;
        }

        $shop = $this->getShopByRequest($request);

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

        // Read original base path for resources
        $request->getBasePath();

        if ($request->isSecure()) {
            $request->setBaseUrl($shop->getSecureBaseUrl());
        } else {
            $request->setBaseUrl($shop->getBaseUrl());
        }

        // Update path info
        $request->setPathInfo(
            $this->createPathInfo($request, $shop)
        );

        if (($host = $request->getHeader('X_FORWARDED_HOST')) !== null
            && $host === $shop->getSecureHost()
        ) {
            $request->setSecure();
            $request->setBasePath($shop->getSecureBasePath());
            $request->setBaseUrl($shop->getSecureBaseUrl());
            $request->setHttpHost($shop->getSecureHost());
        }

        $this->validateShop($shop);
        $shop->registerResources();
    }

    /**
     * @param Request $request
     * @param Shop $shop
     * @return null|string
     */
    private function createPathInfo(Request $request, Shop $shop)
    {
        $requestUri = $request->getRequestUri();
        if ($requestUri === null) {
            return null;
        }

        // Remove the query string from REQUEST_URI
        if ($pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        /** @var $repository Shopware\Models\Shop\Repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $requestShop = $repository->getActiveByRequest($request);

        if ($requestShop && $requestShop->getId() !== $shop->getId()) {
            $requestUri = $this->removeShopBaseUrl(
                $requestUri,
                $request,
                $requestShop
            );
        }

        $requestUri = $this->removeShopBaseUrl(
            $requestUri,
            $request,
            $shop
        );

        if (!$shop->getMain()) {
            return $requestUri;
        }

        return $this->removeShopBaseUrl(
            $requestUri,
            $request,
            $shop->getMain()
        );
    }

    /**
     * @param string $requestUri
     * @param Request $request
     * @param Shop $shop
     * @return string
     */
    private function removeShopBaseUrl($requestUri, Request $request, Shop $shop)
    {
        $url = $shop->getBaseUrl();
        $path = $shop->getBasePath();
        if ($request->isSecure()) {
            $url = $shop->getSecureBaseUrl();
            $path = $shop->getSecureBasePath();
        }
        $requestUri = $this->removePartOfUrl($requestUri, $url);
        $requestUri = $this->removePartOfUrl($requestUri, $path);

        return $requestUri;
    }

    /**
     * @param string $requestUri
     * @param string $url
     * @return string
     */
    private function removePartOfUrl($requestUri, $url)
    {
        $temp = rtrim($url, '/') . '/';
        switch (true) {
            case (strpos($requestUri, $temp) === 0):
                return substr($requestUri, strlen($url));
            case ($requestUri == $url):
                return substr($requestUri, strlen($url));
            default:
                return $requestUri;
        }
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

        if (Shopware()->Container()->initialized('Shop')) {
            /** @var DetachedShop $shop */
            $shop = $this->Application()->Shop();

            if ($request->isSecure() && $request->getHttpHost() !== $shop->getSecureHost()) {
                $newPath = 'https://' . $shop->getSecureHost() . $request->getRequestUri();
            } elseif (!$request->isSecure() && $request->getHttpHost() !== $shop->getHost()) {
                $newPath = 'http://' . $shop->getHost() . $shop->getBaseUrl();
            }

            // Strip /shopware.php/ from string and perform a redirect
            $preferBasePath = $this->Application()->Config()->preferBasePath;
            if ($preferBasePath && strpos($request->getPathInfo(), '/shopware.php/') === 0) {
                $removePath = $request->getBasePath() . '/shopware.php';
                $newPath = str_replace($removePath, $request->getBasePath(), $request->getRequestUri());
            }

            if (isset($newPath)) {
                // reset the cookie so only one valid cookie will be set IE11 fix
                $response->setCookie("session-" . $shop->getId(), '', 1);
                $response->setRedirect($newPath, 301);
            } else {
                $this->upgradeShop($request, $response);
                $this->initServiceMode($request);
            }

            Shopware()->Container()->get('shopware_storefront.context_service')->initializeShopContext();
        }
    }

    /**
     * @param Request $request
     */
    protected function initServiceMode($request)
    {
        $config = $this->Application()->Config();
        if (!empty($config->setOffline) && strpos($config->offlineIp, $request->getClientIp()) === false) {
            if ($request->getControllerName() != 'error') {
                $request->setControllerName('error')->setActionName('service')->setDispatched(false);
            }
        }
    }

    /**
     * @param Request $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     */
    protected function upgradeShop($request, $response)
    {
        $container = $this->Application()->Container();

        /** @var $shop DetachedShop */
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
        }

        if ($cookieKey === 'shop' && $this->shouldRedirect($request, $shop) == true) {
            /** @var $repository Shopware\Models\Shop\Repository */
            $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

            $newShop = $repository->getActiveById($cookieValue);

            if ($newShop !== null) {
                $redirectUrl = $this->getNewShopUrl($request, $newShop);
                $response->setRedirect($redirectUrl);

                if ($newShop->getBasePath()) {
                    $cookiePath = $newShop->getBasePath();
                } else {
                    $cookiePath = $request->getBasePath();
                }

                $cookiePath = rtrim($cookiePath, '/') . '/';

                // If shop is main, remove the cookie
                $cookieTime = $newShop->getMain() === null ? time() - 3600 : 0;

                $response->setCookie($cookieKey, $cookieValue, $cookieTime, $cookiePath);

                return;
            }
        }

        //currency switch
        if ($cookieKey == 'currency') {
            $path = rtrim($shop->getBasePath(), '/') . '/';
            $response->setCookie($cookieKey, $cookieValue, 0, $path);
            $url = sprintf('%s://%s%s',
                $request->getScheme(),
                $request->getHttpHost(),
                $request->getRequestUri()
            );
            $response->setRedirect($url);
            return;
        }

        // Upgrade currency
        if ($request->getCookie('currency') !== null) {
            $currencyValue = $request->getCookie('currency');
            foreach ($shop->getCurrencies() as $currency) {
                if ($currencyValue == $currency->getId()
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
        if (isset($session->sBasketCurrency) && $shop->getCurrency()->getId() != $session->sBasketCurrency) {
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }

        // Upgrade template
        if (isset($session->template) && !empty($session->Admin)) {
            $repository = 'Shopware\Models\Shop\Template';
            $repository = Shopware()->Models()->getRepository($repository);
            $template = $session->template;
            $template = $repository->findOneBy(array('template' => $template));

            $container->get('Template')->setTemplateDir(array());

            if ($template !== null) {
                $shop->setTemplate($template);
            } else {
                unset($session->template);
            }
        } else {
            unset($session->template);
        }

        // Save upgrades
        $shop->registerResources();

        if ($request->isSecure()) {
            $template = $container->get('Template');
            $template->setCompileId($template->getCompileId() . '_secure');
        }
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

    /**
     * @param Request $request
     * @return DetachedShop
     */
    protected function getShopByRequest(Request $request)
    {
        /** @var Repository $repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $shop = null;
        if ($request->getPost('__shop') !== null) {
            $shop = $repository->getActiveById($request->getPost('__shop'));
        }

        if ($shop === null && $request->getCookie('shop') !== null) {
            $shop = $repository->getActiveById($request->getCookie('shop'));
        }

        if ($shop && $request->getCookie('shop') !== null && $request->getPost('__shop') == null) {
            $requestShop = $repository->getActiveByRequest($request);
            if ($requestShop !== null && $shop->getId() !== $requestShop->getId() && $shop->getBaseUrl() !== $requestShop->getBaseUrl()) {
                $shop = $requestShop;
            }
        }

        if ($shop === null) {
            $shop = $repository->getActiveByRequest($request);
        }

        if ($shop === null) {
            $shop = $repository->getActiveDefault();
        }

        return $shop;
    }

    /**
     * @param Request $request
     * @param Shop $newShop
     * @return string
     */
    protected function getNewShopUrl(
        Request $request,
        Shop $newShop
    ) {
        /** @var Repository $repository */
        $repository = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $requestShop = $repository->getActiveByRequest($request);

        // Remove baseUrl from request url
        $url = $request->getRequestUri();

        if ($requestShop && strpos($url, $requestShop->getBaseUrl()) === 0) {
            $url = substr($url, strlen($requestShop->getBaseUrl()));
        }

        $baseUrl = $request->getBaseUrl();
        if (strpos($url, $baseUrl.'/') === 0) {
            $url = substr($url, strlen($baseUrl));
        }

        $basePath = $newShop->getBasePath();
        if (strpos($url, $basePath) === 0) {
            $url = substr($url, strlen($basePath));
        }

        $host = $newShop->getHost();
        $baseUrl = $newShop->getBaseUrl() ?: $request->getBasePath();

        if ($request->isSecure()) {
            $host = $newShop->getSecureHost() ?: $newShop->getHost();

            if ($newShop->getSecureBaseUrl()) {
                $baseUrl = $newShop->getSecureBaseUrl();
            } elseif ($newShop->getBaseUrl()) {
                $baseUrl = $newShop->getBaseUrl();
            } else {
                $baseUrl = $request->getBaseUrl();
            }
        }

        $host = trim($host, '/');
        $baseUrl = trim($baseUrl, '/');
        if (!empty($baseUrl)) {
            $baseUrl = '/' . $baseUrl;
        }

        $url = ltrim($url, '/');
        if (!empty($url)) {
            $url = '/' . $url;
        }

        //build full redirect url to allow host switches
        return sprintf(
            '%s://%s%s%s',
            $request->getScheme(),
            $host,
            $baseUrl,
            $url
        );
    }

    /**
     * @param Request $request
     * @param Shop $shop
     * @return bool
     */
    protected function shouldRedirect(Request $request, Shop $shop)
    {
        return (
            //for example: template preview, direct shop selection via url
            (
                $request->isGet()
                && $request->getQuery('__shop') !== null
                && $request->getQuery('__shop') != $shop->getId()
            )
            ||
            //for example: shop language switch
            (
                $request->isPost()
                && $request->getPost('__shop') !== null
                && $request->getPost('__redirect') !== null
            )
        );
    }

    /**
     * @param Shop $shop
     * @throws \RuntimeException
     */
    private function validateShop(Shop $shop)
    {
        if (!$shop->getCustomerGroup()) {
            throw new \RuntimeException(sprintf("Shop '%s (id: %s)' has no customer group.", $shop->getName(), $shop->getId()));
        }

        if (!$shop->getCurrency()) {
            throw new \RuntimeException(sprintf("Shop '%s (id: %s)' has no currency.", $shop->getName(), $shop->getId()));
        }

        if (!$shop->getLocale()) {
            throw new \RuntimeException(sprintf("Shop '%s (id: %s)' has no locale.", $shop->getName(), $shop->getId()));
        }

        $mainShop = $shop->getMain() !== null ? $shop->getMain() : $shop;
        if (!$mainShop->getTemplate()) {
            throw new \RuntimeException(sprintf("Shop '%s (id: %s)' has no template.", $shop->getName(), $shop->getId()));
        }

        if (!$mainShop->getDocumentTemplate()) {
            throw new \RuntimeException(sprintf("Shop '%s (id: %s)' has no document template.", $shop->getName(), $shop->getId()));
        }
    }
}
