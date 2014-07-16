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
 * Shopware Plugin Manager
 */
class Shopware_Controllers_Backend_Store extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * @var $communityStore CommunityStore
     */
    protected $communityStore = null;

    /**
     * @return CommunityStore|null
     */
    private function getCommunityStore()
    {
        if ($this->communityStore === null) {
            $this->communityStore = new CommunityStore();
        }
        return $this->communityStore;
    }


    public function getLoginAction()
    {
        if (!$this->getCommunityStore()->isLoggedIn()) {
            $this->View()->assign(array(
                'success' => false,
                'data' => array()
            ));
        }

        /**@var $domain Shopware_StoreApi_Models_Domain */
        $domain = $this->getCommunityStore()->getAccountService()->getDomain(
            $this->getCommunityStore()->getIdentity(),
            $this->Request()->getHttpHost()
        );

        if ($domain instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
                'success' => false,
                'code' => $domain->getCode(),
                'message' => $this->getCommunityStore()->getDomainMessage()
            ));
            return;
        }

        $data = array_merge(
            $this->getCommunityStore()->getIdentity()->getRawData(),
            $domain->getRawData()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));

    }

    /**
     * The loginAction function is a controller function which can be called for example
     * over the url plugin.
     * The function expects the request parameters shopwareId and password to login
     * in the shopware account.
     */
    public function loginAction()
    {
        $shopwareId = $this->Request()->getParam('shopwareID', null);
        $password = $this->Request()->getParam('password', null);

        if (!$this->getCommunityStore()->isLoggedIn()) {
            $auth = $this->getCommunityStore()->login($shopwareId, $password);
        } else {
            $auth = $this->getCommunityStore()->getIdentity();
        }

        if ($auth instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
                'success' => false,
                'source' => 'auth',
                'code' => $auth->getCode(),
                'message' => $auth->getMessage()
            ));
            return;
        }

        /**@var $domain Shopware_StoreApi_Models_Domain */
        $domain = $this->getCommunityStore()->getAccountService()->getDomain(
            $auth,
            $this->Request()->getHttpHost()
        );

        if ($domain instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
                'success' => false,
                'code' => $domain->getCode(),
                'message' => $this->getCommunityStore()->getDomainMessage()
            ));
            return;
        }

        Shopware()->BackendSession()->pluginManagerAccountId = $domain->getAccountId();

        $data = array_merge($auth->getRawData(), $domain->getRawData());

        $this->View()->assign(array(
            'success' => true,
            'data' => $data
        ));
    }



    public function votesAction()
    {
        $productId = $this->Request()->getParam('productId', null);

        if (empty($productId)) {
            $this->View()->assign(array(
                'success' => false,
                'data' => array(),
                'total' => 0
            ));
            return;
        }
        $result = $this->getCommunityStore()->getProductFeedback($productId);
        $this->View()->assign($result);
    }

    /**
     *
     */
    public function buyAction()
    {
        $rentVersion = $this->Request()->getParam('rentVersion', false);
        if ($rentVersion === 'false') {
            $rentVersion = false;
        }
        $pluginNames = $this->Request()->getParam('plugin_names', array());
        if (!is_array($pluginNames)) {
            $pluginNames = array($pluginNames);
        }


        $result = $this->buyProduct(
            $this->Request()->getParam('productId', null),
            $rentVersion,
            $pluginNames,
            $this->Request()->getParam('licenceKey', null)
        );

        $this->View()->assign($result);
    }

    public function taxAction()
    {
        if (!$this->getCommunityStore()->isLoggedIn()) {
            $this->View()->assign(array(
                'success' => false,
                'loginRequired' => true
            ));
            return;
        }
        $productId = $this->Request()->getParam('productId', null);
        if (empty($productId)) {
            $this->View()->assign(array(
                'success' => false,
                'noId' => true
            ));
            return;
        }

        $tax = $this->getCommunityStore()->getAccountService()->getTax(
            $this->getCommunityStore()->getIdentity()
        );

        if ($tax instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
                'success' => false,
                'code' => $tax->getCode(),
                'message' => $tax->getMessage()
            ));
            return;
        }

        $product = $this->getCommunityStore()->getProductService()->getProductById($productId);
        if ($product instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
                'success' => false,
                'code' => $product->getCode(),
                'message' => $product->getMessage()
            ));
            return;
        }


        /**@var $product Shopware_StoreApi_Models_Product*/
        $detailId = $this->Request()->getParam('detail', null);
        $detail = null;

        foreach ($product->getDetails() as $productDetail) {
            if ($productDetail['id'] === $detailId) {
                $detail = $productDetail;
                break;
            }
        }

        if (empty($detail)) {
            $this->View()->assign(array(
                'success' => false,
                'noDetail' => true
            ));
            return;
        }

        $taxValue = $tax->getTax() + 100;

        /**@var $tax Shopware_StoreApi_Models_Tax*/
        if ($tax->isNet()) {
            $this->View()->assign(array(
                'success' => true,
                'price' => $detail['price']
            ));
        } else {
            $this->View()->assign(array(
                'success' => true,
                'price' => $detail['price'] / 100 * $taxValue
            ));
        }

    }


    public function buyLicensePluginAction()
    {
        $licensePlugin = $this->getCommunityStore()->getLicensePlugin();
        if ($licensePlugin instanceof Shopware_StoreApi_Exception_Response) {
            return array(
                'success' => false,
                'code' => $licensePlugin->getCode(),
                'message' => $licensePlugin->getMessage()
            );
        }
        $result = $this->buyProduct($licensePlugin->getId(), false, array('SwagLicense'), null);
        $this->View()->assign($result);
    }

    /**
     * @param      $productId
     * @param bool $rentVersion
     * @param      $pluginNames
     * @param      $licence
     * @internal param $license
     * @return array
     */
    private function buyProduct($productId, $rentVersion = false, $pluginNames, $licence)
    {
        //check if the user is logged in the community store and the token is valid
        if (!$this->getCommunityStore()->isLoggedIn()) {
            return array(
                'success' => false,
                'loginRequired' => true
            );
        }

        //check if a valid product id is passed.
        if (empty($productId)) {
            return array(
                'success' => false,
                'noId' => true
            );
        }

        $product = new Shopware_StoreApi_Models_Product(array('id' => $productId));
        $licence = $licence . '';

        //licence required and isn't licence plugin?
        if (!empty($licence) && strlen($licence) > 0 && !in_array('SwagLicense', $pluginNames) ) {
            //the licence plugin requires the ionCubeLoader
            if (!$this->isIonCubeLoaderLoaded()) {
                return array(
                    'success' => false,
                    'noDecoder' => true
                );
            }

            $localeLicensePlugin = $this->getLocaleLicensePlugin();

            //license plugin exist on the shopware shop?
            if (!$localeLicensePlugin instanceof \Shopware\Models\Plugin\Plugin) {
                //return licensePluginRequired to send a new ajax request to buy the license plugin
                return array(
                    'success' => false,
                    'licensePluginRequired' => true
                );
            } else {
                /**@var $localeLicensePlugin \Shopware\Models\Plugin\Plugin*/
                if ($localeLicensePlugin->getInstalled() === null) {
                    $this->installPlugin($localeLicensePlugin);
                }
                if (!$localeLicensePlugin->getActive()) {
                    $this->activatePlugin($localeLicensePlugin);
                }
            }
        }

        $domain  = new Shopware_StoreApi_Models_Domain(array(
            'domain' => $this->Request()->getHttpHost(),
            'account_id' =>  Shopware()->BackendSession()->pluginManagerAccountId
        ));

        //after the domain resolved, we can perform the order
        $orderModel = $this->getCommunityStore()->getOrderService()->orderProduct(
            $this->getCommunityStore()->getIdentity(),
            $domain,
            $product,
            $rentVersion
        );

        //first we have to check if an request error occurred. This errors will be displayed in a growl message
        if ($orderModel instanceof Shopware_StoreApi_Exception_Response) {
            return array(
                'success' => false,
                'source' => 'order',
                'code' => $orderModel->getCode(),
                'message' => $this->getOrderExceptionMessage($orderModel->getCode())
            );
        }

        /**@var $orderModel Shopware_StoreApi_Models_Order*/
        //if the request was successfully but the order process wasn't successfully, the account data are not completed
        //for example: The user hasn't enough credits or the user bought the plugin already.
        if (!$orderModel->wasSuccessful()) {
            return array(
                'success' => false,
                'displayInWindow' => true,
                'code' => $orderModel->getErrorType(),
                'message' => $this->getOrderExceptionMessage($orderModel->getErrorType(),  $orderModel->getErrorData())
            );
        }

        //if the order process was successful we have to get the licenced product to get the available downloads.
        $licencedProduct = $this->getCommunityStore()->getAccountService()->getLicencedProductById(
            $this->getCommunityStore()->getIdentity(),
            $domain,
            $productId,
            $this->getNumericShopwareVersion()
        );

        //check if the product was founded
        if ($licencedProduct instanceof Shopware_StoreApi_Exception_Response) {
            return array(
                'success' => false,
                'source' => 'licencedProduct',
                'code' => $licencedProduct->getCode(),
                'message' => $licencedProduct->getMessage()
            );
        }

        /**@var $product Shopware_StoreApi_Models_Product*/
        /**@var $licencedProduct Shopware_StoreApi_Models_Licence*/
        $downloads = $licencedProduct->getDownloads();
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        if (empty($downloads)) {
            return array(
                'success' => false,
                'message' => $namespace->get('no_download', 'No download available')
            );
        }

        $url = $downloads['download']['url'];

        if ($downloads['type'] === 'plain') {
            $result = $this->getCommunityStore()->downloadPlugin($url);
            if ($result['success']) {
                return array(
                    'success' => true,
                    'data' => $orderModel->getRawData(),
                    'license' => $licencedProduct->getLicence()
                );
            } else {
                return $result;
            }
        } else {
            if ($this->isIonCubeLoaderLoaded()) {
                $result = $this->getCommunityStore()->downloadPlugin($url);
                if ($result['success']) {
                    return array(
                        'success' => true,
                        'isMultiShopPlugin' => in_array('SwagMultiShop', $pluginNames),
                        'data' => $orderModel->getRawData(),
                        'license' => $licencedProduct->getLicence()
                    );
                } else {
                    return $result;
                }
            } else {
                return array(
                    'success' => false,
                    'noDecoder' => true
                );
            }
        }
    }

    /**
     * Download action of a licensed product.
     */
    public function downloadAction()
    {
        $url = $this->Request()->getParam('url', null);
        if (empty($url)) {
            $this->View()->assign(array('success' => false, 'noUrl' => true));
            return;
        }

        $result = $this->getCommunityStore()->downloadPlugin($url);
        $this->View()->assign($result);
    }


    /**
     * Returns a certain plugin by plugin id.
     *
     * @param \Shopware\Models\Plugin\Plugin $plugin
     * @return Shopware_Components_Plugin_Bootstrap|null
     */
    private function getPluginBootstrap($plugin)
    {
        $namespace = Shopware()->Plugins()->get($plugin->getNamespace());
        if ($namespace === null) {
            return null;
        }
        $plugin = $namespace->get($plugin->getName());
        return $plugin;
    }

    /**
     * @param $plugin \Shopware\Models\Plugin\Plugin
     */
    private function activatePlugin($plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);
        $result = $bootstrap->enable();
        if ($result) {
            $plugin->setActive(true);
            Shopware()->Models()->persist($plugin);
            Shopware()->Models()->flush($plugin);
        }
        return $result;
    }

    private function installPlugin($plugin)
    {
        $bootstrap = $this->getPluginBootstrap($plugin);
        return $bootstrap->Collection()->installPlugin($bootstrap);
    }


    /**
     * Internal helper function to check if the license plugin exist on the system.
     * @return mixed
     */
    private function getLocaleLicensePlugin()
    {
        $builder = Shopware()->Models()->createQueryBuilder();
        return $builder->select(array('plugin'))
                       ->from('Shopware\Models\Plugin\Plugin', 'plugin')
                       ->where('plugin.name = :name')
                       ->setParameter('name', 'SwagLicense')
                       ->setFirstResult(0)
                       ->setMaxResults(1)
                       ->getQuery()
                       ->getOneOrNullResult(\Doctrine\ORM\AbstractQuery::HYDRATE_OBJECT);
    }


    /**
     * Helper function to check if the ion cube loader is loaded
     * @return bool
     */
    private function isIonCubeLoaderLoaded()
    {
        return extension_loaded('ionCube Loader');
    }


    /**
     * Internal helper function to map the StoreApi error codes to an helpfully error message.
     * @param      $code
     * @param null $errorData
     * @return string
     */
    private function getOrderExceptionMessage($code, $errorData = null)
    {
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');

        switch ($code) {
            case Shopware_StoreApi_Exception_Response::ACCESS_FORBIDDEN:
                $message = $namespace->get('access_forbidden', 'Access prohibited – Token expired or insufficient rights.', true);
                break;
            case Shopware_StoreApi_Exception_Response::DOMAIN_ACCESS_FORBIDDEN:
                $message = $namespace->get('domain_access_forbidden', 'Access to transferred domain denied.', true);
                break;
            case Shopware_StoreApi_Exception_Response::PRODUCT_NOT_FOUND:
                $message = $namespace->get('product_not_found', 'Article could not be found.', true);
                break;
            case Shopware_StoreApi_Exception_Response::NO_RENT_VERSION_AVAILABLE:
                $message = $namespace->get('no_rent_version_available', 'No rental version of this article existing.', true);
                break;
            case Shopware_StoreApi_Exception_Response::PRODUCT_COULD_NOT_ADDED:
                $message = $namespace->get('product_could_not_added', 'Article could not be added to store shopping basket.', true);
                break;
            case Shopware_StoreApi_Models_Order::BILLING_ADDRESS_INCOMPLETE:
                $link = "http://account.shopware.de";
                $message = $namespace->get('billing_address_incomplete', "Please complete your billing information and contact details under <a href='http://account.shopware.de' target='_blank'>account.shopware.de</a> first.", true);
                $message = array(
                    'link' => $link,
                    'message' => $message
                );
                break;
            case Shopware_StoreApi_Models_Order::TRADE_TERMS_NOT_ACCEPTED:
                $link = "http://account.shopware.de";
                $message =  $namespace->get('trade_terms_not_accepted', "Please accept the terms and conditions under <a href='http://account.shopware.de' target='_blank'>account.shopware.de</a> first.", true);
                $message = array(
                    'link' => $link,
                    'message' => $message
                );
                break;
            case Shopware_StoreApi_Models_Order::PRODUCT_ALREADY_BOUGHT:
                $message = $namespace->get('product_already_bought', 'You have already purchased this module!');
                $message = array(
                    'link' => null,
                    'message' => $message
                );
                break;
            case Shopware_StoreApi_Models_Order::CREDITS_NOT_ENOUGH:
                $message = $namespace->get('credits_not_enough', "The order value is %s EUR. Please charge %s EUR to purchase the article.");
                $message = sprintf($message, str_replace('.', ',', $errorData['basket_amount']), str_replace('.', ',', $errorData['amount_difference']));
                $message = array(
                    'link' => $errorData['charge_link'],
                    'message' => $message
                );
                break;
            default:
                $message = $namespace->get('unknown_error', "Unknown error");
                break;
        }

        return $message;
    }

    /**
     * The licencedProductsAction function is a controller action function which can be called for example
     * over the action|url plugin.
     * The function selects all licenced products for the current shopware account.
     */
    public function licencedProductsAction()
    {
        if (!$this->getCommunityStore()->isLoggedIn()) {
            $this->View()->assign(array(
                'success' => false,
                'loginRequired' => true
            ));
            return;
        }

        $products = $this->getCommunityStore()->getLicencedProducts(
            $this->Request()->getHttpHost(),
            $this->getNumericShopwareVersion()
        );

        //check if the request was successfully, in case of an error we have an instance of
        //Shopware_StoreApi_Exception_Response in our result set.
        if ($products instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
               'success' => false,
               'message' => $products->getMessage(),
               'code' => $products->getCode()
            ));
            return;
        } else {
            $this->View()->assign(array(
                'success' => true,
                'data' => $products
            ));
        }
    }

    /**
     * The topSellerListAction function is a controller action function which can be called for example
     * over the url plugin.
     * It is used for the plugin manager backend module for the community store overview.
     */
    public function topSellerListAction()
    {
        $topSeller = $this->getCommunityStore()->getTopSellerCategory(
            $this->Request()->getParam('page', 0),
            $this->Request()->getParam('limit', 2),
            $this->getNumericShopwareVersion()
        );
        $this->View()->assign($topSeller);
    }

    /**
     * Internal helper function to get the current shopware version as a numeric value with four positions.
     * @return string
     */
    private function getNumericShopwareVersion()
    {
        $version = Shopware()->Config()->get('version');
        $paths = explode('.', $version);
        if (count($paths) === 3) {
            $paths[] = 0;
        }
        return (int) implode('', $paths);
    }


    /**
     * The "communityListAction" function is a controller action function,
     * which is used for the community store of the plugin manager backend module.
     */
    public function communityListAction()
    {
        $categoryId = (int) $this->Request()->getParam('categoryId', null);
        if ($categoryId > 0) {
            $categories = $this->getCommunityStore()->getCategoryWithProducts(
                $categoryId,
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null),
                $this->Request()->getParam('sort', null),
                $this->Request()->getParam('filter', null),
                $this->getNumericShopwareVersion()
            );
        } else {
            $categories = $this->getCommunityStore()->getCategoriesWithProducts(
                $this->Request()->getParam('start', null),
                $this->Request()->getParam('limit', null),
                $this->Request()->getParam('sort', null),
                $this->Request()->getParam('filter', null),
                $this->getNumericShopwareVersion()
            );
        }

        if ($categories instanceof Shopware_StoreApi_Exception_Response) {
            $this->View()->assign(array(
               'success' => false,
               'message' => $categories->getMessage(),
               'code' => $categories->getCode()
            ));
            return;
        } else {
            $this->View()->assign(array(
                'success' => true,
                'data' => $categories
            ));
            return;
        }
    }

    /**
     * The categoryListAction function is a controller action function which is used from
     * the plugin manager backend module to load all store categories.
     */
    public function categoryListAction()
    {
        $categories = $this->getCommunityStore()->getCategories();
        $categories = array_merge($this->getCommunityStore()->getDefaultCategory(), $categories);
        $this->View()->assign(array('success' => true, 'data' => $categories));
    }

}
