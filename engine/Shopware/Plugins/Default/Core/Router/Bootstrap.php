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
use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\ShopRegistrationServiceInterface;
use Shopware\Models\Customer\Group as CustomerGroup;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Shop\Template;
use Symfony\Component\HttpFoundation\Cookie;

class Shopware_Plugins_Core_Router_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
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
     * @return void
     */
    public function onRouteStartup(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();

        if (str_starts_with($request->getPathInfo(), '/backend')
            || str_starts_with($request->getPathInfo(), '/api/')
        ) {
            return;
        }

        $shop = $this->getShopByRequest($request);

        if (!$shop->getHost()) {
            $shop->setHost($request->getHttpHost());
        }
        if (!$shop->getBaseUrl()) {
            $preferBasePath = $this->get(Shopware_Components_Config::class)->get('preferBasePath');
            $shop->setBaseUrl($preferBasePath ? $request->getBasePath() : $request->getBaseUrl());
        }
        if (!$shop->getBasePath()) {
            $shop->setBasePath($request->getBasePath());
        }

        // Read original base path for resources
        $request->getBasePath();
        $request->setBaseUrl($shop->getBaseUrl());

        // Update path info
        $request->setPathInfo($this->createPathInfo($request, $shop));

        $host = $request->getHeader('X_FORWARDED_HOST');
        if ($host !== null && $host === $shop->getHost()) {
            // If the base path is null, set it to empty string. Otherwise the request will try to assemble the base path. On a reverse proxy setup with varnish this will fail on virtual URLs like /en
            // The X-Forwarded-Host header is only set in such environments
            if ($shop->getBasePath() === null) {
                $shop->setBasePath('');
            }

            $request->setSecure();
            $request->setBasePath($shop->getBasePath());
            $request->setBaseUrl($shop->getBaseUrl());
            $request->setHttpHost($shop->getHost());
        }

        $this->validateShop($shop);
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);
    }

    /**
     * @return void
     */
    public function onRouteShutdown(Enlight_Controller_EventArgs $args)
    {
        $request = $args->getRequest();
        $response = $args->getResponse();

        if (!Shopware()->Container()->initialized('shop')) {
            return;
        }

        $shop = $this->get('shop');
        if ($request->getHttpHost() !== $shop->getHost()) {
            if ($request->isSecure()) {
                $newPath = 'https://' . $shop->getHost() . $request->getRequestUri();
            } else {
                $newPath = 'http://' . $shop->getHost() . $shop->getBaseUrl();
            }
        }

        // Strip /shopware.php/ from string and perform a redirect
        $preferBasePath = $this->get(Shopware_Components_Config::class)->get('preferBasePath');
        if ($preferBasePath && str_starts_with($request->getPathInfo(), '/shopware.php/')) {
            $removePath = $request->getBasePath() . '/shopware.php';
            $newPath = str_replace($removePath, $request->getBasePath(), $request->getRequestUri());
            $newPath = preg_replace('/\/{2,}/', '/', $newPath);
        }

        if (isset($newPath)) {
            // reset the cookie so only one valid cookie will be set IE11 fix
            $basePath = $shop->getBasePath();
            if ($basePath === null || $basePath === '') {
                $basePath = '/';
            }
            $response->headers->setCookie(new Cookie('session-' . $shop->getId(), '', 1, $basePath));
            $response->setRedirect($newPath, 301);
        } else {
            $this->upgradeShop($request, $response);
            $this->initServiceMode($request);
        }

        $this->get(ContextServiceInterface::class)->initializeShopContext();
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
     * @deprecated - Will be private with Shopware 5.8
     *
     * @param Request $request
     *
     * @return void
     */
    protected function initServiceMode($request)
    {
        $config = $this->get(Shopware_Components_Config::class);
        if (!empty($config->get('setOffline'))
            && !str_contains($config->get('offlineIp'), $request->getClientIp())
            && $request->getControllerName() !== 'error'
        ) {
            $request->setControllerName('error')->setActionName('service')->setDispatched(false);
        }
    }

    /**
     * @deprecated - Will be private with Shopware 5.8
     *
     * @param Request                                  $request
     * @param Enlight_Controller_Response_ResponseHttp $response
     *
     * @return void
     */
    protected function upgradeShop($request, $response)
    {
        $shop = $this->get('shop');

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

        if ($cookieKey === 'shop' && $this->shouldRedirect($request, $shop)) {
            $repository = $this->get(ModelManager::class)->getRepository(Shop::class);

            $newShop = $repository->getActiveById($cookieValue);

            if ($newShop !== null) {
                $redirectUrl = $this->getNewShopUrl($request, $newShop);
                $response->setRedirect($redirectUrl);

                if ($newShop->getBasePath()) {
                    $cookiePath = $newShop->getBasePath();
                } else {
                    $cookiePath = $request->getBasePath();
                }

                $cookiePath = rtrim((string) $cookiePath, '/') . '/';

                // If shop is main, remove the cookie
                $cookieTime = $newShop->getMain() === null ? time() - 3600 : 0;

                $response->headers->setCookie(new Cookie($cookieKey, $cookieValue, $cookieTime, $cookiePath, null, $request->isSecure()));

                $this->refreshCart($newShop);

                return;
            }
        }

        // currency switch
        if ($cookieKey === 'currency') {
            $path = rtrim((string) $shop->getBasePath(), '/') . '/';
            $response->headers->setCookie(new Cookie($cookieKey, $cookieValue, 0, $path, null, $request->isSecure()));
            $url = sprintf(
                '%s://%s%s',
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
        $session = $this->get('session');

        if ($cookieKey !== null) {
            $session->set($cookieKey, $cookieValue);
        }

        // Refresh basket on currency change
        if ($session->has('sBasketCurrency') && $shop->getCurrency()->getId() != $session->get('sBasketCurrency')) {
            Shopware()->Modules()->Basket()->sRefreshBasket();
        }

        // Upgrade template
        if ($session->has('template') && !empty($session->get('Admin'))) {
            $repository = $this->get(ModelManager::class)->getRepository(Template::class);
            $template = $session->get('template');
            $template = $repository->findOneBy(['template' => $template]);

            $this->get(Enlight_Template_Manager::class)->setTemplateDir([]);

            if ($template !== null) {
                $shop->setTemplate($template);
            } else {
                unset($session->template);
            }
        } else {
            unset($session->template);
        }

        // Save upgrades
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        if ($request->isSecure()) {
            $template = $this->get(Enlight_Template_Manager::class);
            $template->setCompileId($template->getCompileId() . '_secure');
        }
    }

    /**
     * @deprecated - Will be private with Shopware 5.8
     *
     * @return Shop
     */
    protected function getShopByRequest(Request $request)
    {
        $repository = $this->get(ModelManager::class)->getRepository(Shop::class);

        $shop = null;
        if ($request->getPost('__shop') !== null) {
            $shop = $repository->getActiveById($request->getPost('__shop'));
        }

        if ($shop === null && $request->getCookie('shop') !== null) {
            $shop = $repository->getActiveById($request->getCookie('shop'));
        }

        if ($shop && $request->getCookie('shop') !== null && $request->getPost('__shop') === null) {
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
     * @deprecated - Will be private with Shopware 5.8
     *
     * @return string
     */
    protected function getNewShopUrl(Request $request, Shop $newShop)
    {
        // Remove baseUrl from request url
        $url = $request->getRequestUri();

        $requestShop = $this->get(ModelManager::class)->getRepository(Shop::class)->getActiveShopByRequestAsArray($request);
        if ($requestShop && str_starts_with($url, $requestShop['base_url'])) {
            $url = substr($url, \strlen($requestShop['base_url']));
        }

        $baseUrl = $request->getBaseUrl();
        if (str_starts_with($url, $baseUrl . '/')) {
            $url = substr($url, \strlen($baseUrl));
        }

        $basePath = (string) $newShop->getBasePath();
        if (str_starts_with($url, $basePath)) {
            $url = substr($url, \strlen($basePath));
        }

        $host = $newShop->getHost();
        $baseUrl = $newShop->getBaseUrl() ?: $request->getBasePath();

        if ($request->isSecure()) {
            if ($newShop->getBaseUrl()) {
                $baseUrl = $newShop->getBaseUrl();
            } else {
                $baseUrl = $request->getBaseUrl();
            }
        }

        if (\is_string($host)) {
            $host = trim($host, '/');
        }
        if (\is_string($baseUrl)) {
            $baseUrl = trim($baseUrl, '/');
        }
        if (!empty($baseUrl)) {
            $baseUrl = '/' . $baseUrl;
        }

        $url = ltrim($url, '/');
        if (!empty($url)) {
            $url = '/' . $url;
        }

        // build full redirect url to allow host switches
        return sprintf(
            '%s://%s%s%s',
            $request->getScheme(),
            $host,
            $baseUrl,
            $url
        );
    }

    /**
     * @deprecated - Will be private with Shopware 5.8
     *
     * @return bool
     */
    protected function shouldRedirect(Request $request, Shop $shop)
    {
        return // for example: template preview, direct shop selection via url
            (
                $request->isGet()
                && $request->getQuery('__shop') !== null
                && (int) $request->getQuery('__shop') !== (int) $shop->getId()
            )
            // for example: shop language switch
            || (
                $request->isPost()
                && $request->getPost('__shop') !== null
                && $request->getPost('__redirect') !== null
            )
        ;
    }

    private function createPathInfo(Request $request, Shop $shop): string
    {
        $requestUri = $request->getRequestUri();

        // Remove the query string from REQUEST_URI
        $pos = strpos($requestUri, '?');
        if ($pos !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        $requestShop = $this->get(ModelManager::class)->getRepository(Shop::class)->getActiveShopByRequestAsArray($request);

        if ($requestShop && $requestShop['id'] !== $shop->getId()) {
            $requestUri = $this->removePartOfUrl($requestUri, (string) $requestShop['base_url']);
            $requestUri = $this->removePartOfUrl($requestUri, (string) $requestShop['base_path']);
        }

        $requestUri = $this->removeShopBaseUrl(
            $requestUri,
            $shop
        );

        if (!$shop->getMain() instanceof Shop) {
            return $requestUri;
        }

        return $this->removeShopBaseUrl($requestUri, $shop->getMain());
    }

    private function removeShopBaseUrl(string $requestUri, Shop $shop): string
    {
        $requestUri = $this->removePartOfUrl($requestUri, (string) $shop->getBaseUrl());

        return $this->removePartOfUrl($requestUri, (string) $shop->getBasePath());
    }

    private function removePartOfUrl(string $requestUri, string $url): string
    {
        $temp = rtrim($url, '/') . '/';
        switch (true) {
            case str_starts_with($requestUri, $temp):
                return substr($requestUri, \strlen($url));
            case $requestUri === $url:
                return substr($requestUri, \strlen($url));
            default:
                return $requestUri;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function validateShop(Shop $shop): void
    {
        if (!$shop->getCustomerGroup() instanceof CustomerGroup) {
            throw new RuntimeException(sprintf("Shop '%s (id: %s)' has no customer group.", $shop->getName(), $shop->getId()));
        }

        $shop->getCurrency();
        $shop->getLocale();

        $mainShop = $shop->getMain() ?? $shop;
        if (!$mainShop->getTemplate()) {
            throw new RuntimeException(sprintf("Shop '%s (id: %s)' has no template.", $shop->getName(), $shop->getId()));
        }

        if (!$mainShop->getDocumentTemplate()) {
            throw new RuntimeException(sprintf("Shop '%s (id: %s)' has no document template.", $shop->getName(), $shop->getId()));
        }
    }

    private function refreshCart(Shop $shop): void
    {
        $this->get(ShopRegistrationServiceInterface::class)->registerShop($shop);

        $modules = $this->get('modules');

        if (empty($this->get('session')->get('sUserId'))) {
            $modules->System()->sUSERGROUP = $shop->getCustomerGroup()->getKey();
            $modules->System()->sUSERGROUPDATA = $shop->getCustomerGroup()->toArray();
            $modules->System()->sCurrency = $shop->getCurrency()->toArray();
        }

        $this->get(ContextServiceInterface::class)->initializeContext();

        $shopContext = $this->get(ContextServiceInterface::class)->getShopContext();

        $modules->Basket()->sRefreshBasket();
        $modules->Admin()->sGetPremiumShippingcosts($shopContext->getCountry() ? $shopContext->getCountry()->jsonSerialize() : null);

        $amount = $modules->Basket()->sGetAmount();
        $this->get('session')->offsetSet('sBasketAmount', empty($amount) ? 0 : array_shift($amount));
    }
}
