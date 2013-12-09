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
class CommunityStore
{
    /**
     * @var Shopware_Api_Core_Service_Product
     */
    protected $api = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Product
     */
    protected $productService = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Category
     */
    protected $categoryService = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Vendor
     */
    protected $vendorService = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Auth
     */
    protected $authService = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Account
     */
    protected $accountService = null;

    /**
     * @var Shopware_StoreApi_Core_Service_Order
     */
    protected $orderService = null;

    /**
     * Contains the Shopware account identity.
     * @var Shopware_StoreApi_Models_Auth
     */
    protected $identity = null;

    /**
     * Internal helper function to get the shopware store api object
     * @return Shopware_Components_StoreApi
     */
    public function getApi()
    {
        if ($this->api === null) {
            $this->api = Shopware()->StoreApi();
            $this->api->Config()->setVersion(4000);
            $this->api->Config()->setLanguage('DE');
        }
        return $this->api;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Product
     */
    public function getProductService()
    {
        if ($this->productService === null) {
            $this->productService = $this->getApi()->getProductService();
        }
        return $this->productService;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Category
     */
    public function getCategoryService()
    {
        if ($this->categoryService === null) {
            $this->categoryService = $this->getApi()->getCategoryService();
        }
        return $this->categoryService;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Vendor
     */
    public function getVendorService()
    {
        if ($this->vendorService === null) {
            $this->vendorService = $this->getApi()->getVendorService();
        }
        return $this->vendorService;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Auth
     */
    public function getAuthService()
    {
        if ($this->authService === null) {
            $this->authService = $this->getApi()->getAuthService();
        }
        return $this->authService;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Account
     */
    public function getAccountService()
    {
        if ($this->accountService === null) {
            $this->accountService  = $this->getApi()->getAccountService();
        }
        return $this->accountService ;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Order
     */
    public function getOrderService()
    {
        if ($this->orderService === null) {
            $this->orderService = $this->getApi()->getOrderService();
        }
        return $this->orderService;
    }

    /**
     * Internal helper function to check if the user is logged in
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($this->getIdentity() instanceof Shopware_StoreApi_Models_Auth) {
            return $this->getAuthService()->isTokenValid($this->getIdentity());
        } else {
            return false;
        }
    }

    /**
     * @return null|Shopware_StoreApi_Models_Auth
     */
    public function getIdentity()
    {
        if (!$this->identity instanceof Shopware_StoreApi_Models_Auth) {
            $this->identity = new Shopware_StoreApi_Models_Auth();
            $this->identity->setShopwareId(Shopware()->BackendSession()->pluginManagerShopwareId);
            $this->identity->setToken(Shopware()->BackendSession()->pluginManagerAccountToken);
            $this->identity->setAccountUrl(Shopware()->BackendSession()->pluginManagerAccountUrl);
        }
        return $this->identity;
    }

    /**
     * Internal helper function to login in the shopware account
     * @param $shopwareId
     * @param $password
     * @return mixed|Shopware_StoreApi_Exception_Response
     */
    public function login($shopwareId, $password)
    {
        $resultSet = $this->getAuthService()->login($shopwareId, $password);
        /**@var $resultSet Shopware_StoreApi_Models_Auth*/
        if (!$resultSet instanceof Shopware_StoreApi_Exception_Response) {
            Shopware()->BackendSession()->pluginManagerShopwareId = $resultSet->getShopwareId();
            Shopware()->BackendSession()->pluginManagerAccountToken = $resultSet->getToken();
            Shopware()->BackendSession()->pluginManagerAccountUrl = $resultSet->getAccountUrl();
        }
        $this->identity = $resultSet;
        return $resultSet;
    }

    /**
     * Internal helper function to get the current shopware version as a numeric value with four positions.
     *
     * @return string
     */
    public function getNumericShopwareVersion()
    {
        $version = Shopware()->Config()->get('version');
        $paths = explode('.', $version);
        if (count($paths) === 3) {
            $paths[] = 0;
        }
        return (int) implode('', $paths);
    }


    /**
     * Helper method which checks for an update of a given plugin
     *
     * Will return false if no update is available or the available update version if an update is available
     *
     * @param string $name      Name of the plugin to check for an update
     * @return bool|string      Returns false, if not update was found or the new available plugin version
     * @throws Exception        If plugin was not found or the store returns an error
     */
    public function isPluginUpdateAvailable($name)
    {
        $pluginModel = Shopware()->Models()->getRepository('Shopware\Models\Plugin\Plugin')->findOneBy(array('name' => $name));
        if (!$pluginModel) {
            throw new \Exception("Plugin {$name} was not found");
        }

        $version = $pluginModel->getVersion();

        $result = $this->getUpdateablePlugins(array(
            $name => array(
                'name' => $name,
                'version' => $version,
                'shopwareVersion' => $this->getNumericShopwareVersion(),
                '1'
            )
        ));

        if ($result['success'] != true) {
            throw new \Exception($result['message']);
        }

        // As we only check for one plugin, we can pop the element
        $data = array_pop($result['data']);
        if (!$data) {
            return false;
        }

        return $data['availableVersion'];
    }

    /**
     * Helper function to download the zip file from the passed url.
     *
     * @param        $url
     *
     * @param string $source
     *
     * @return array
     */
    public function downloadPlugin($url, $source = 'Community')
    {
        $name = 'plugin' . md5($url) . '.zip';
        $tmp = Shopware()->DocPath() . 'files/downloads/' . $name;
        $message = '';

        try {
            $client = new Zend_Http_Client($url, array(
                'timeout' => 30,
                'useragent' => 'Shopware/' . Shopware()->Config()->Version
            ));
            $client->setStream($tmp);
            $client->request('GET');
            $this->decompressFile($tmp, $source);
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        @unlink($tmp);

        return array(
            'success' => empty($message),
            'message' => $message,
            'url' => $url
        );
    }

    /**
     * Decompress a given plugin zip file.
     *
     * @param        $file
     * @param string $source
     * @throws Enlight_Exception
     */
    public function decompressFile($file, $source = 'Community')
    {
        $target = Shopware()->AppPath('Plugins_' . $source);

        if (!$this->isPluginDirectoryWritable($target, true)) {
            throw new Enlight_Exception("A directory or a file in ". $target ." is not writable, please change the permissions recursively");
        }
        $filter = new Zend_Filter_Decompress(array(
            'adapter' => 'Zip',
            'options' => array(
                'target' => $target
            )
        ));
        $filter->filter($file);
    }

    /**
     * @return Shopware_StoreApi_Models_Product
     */
    public function getLicensePlugin()
    {
        $query = new Shopware_StoreApi_Models_Query_Product();
        $query->setLimit(1);

        $query->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_PluginName('SwagLicense')
        );

        $resultSet = $this->getProductService()->getProducts($query);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return $resultSet;
        } else {
            return $resultSet->getIterator()->current();
        }
    }

    /**
     * @param $productId
     * @return mixed|\Shopware_StoreApi_Exception_Response
     */
    public function getProductFeedback($productId)
    {
        $product = $this->getProductService()->getProductById($productId);
        $feedback = $this->getProductService()->getProductFeedback($product);
        if ($feedback instanceof Shopware_StoreApi_Exception_Response) {
            return array('success' => false, 'data' => array(), 'message' => $feedback->getMessage(), 'code' => $feedback->getCode());
        }
        /**@var $feedback Shopware_StoreApi_Core_Response_SearchResult*/
        $iterator = $feedback->getIterator();
        $votes = array();
        foreach($iterator as $data) {
            $votes[] = $data->getRawData();
        }
        return array('success' => true, 'data' => $votes);
    }



    /**
     * The getLicencedProducts is an internal helper function to hold the action function getLicencedProductsActions
     * small. This function executes the community store requests.
     * @param $httpHost
     * @param $shopwareVersion
     * @return mixed|Shopware_StoreApi_Core_Response_SearchResult|Shopware_StoreApi_Exception_Response
     */
    public function getLicencedProducts($httpHost, $shopwareVersion)
    {
        $domain = $this->getAccountService()->getDomain($this->identity, $httpHost);

        //check if the request was successfully, in case of an error we have an instance of
        //Shopware_StoreApi_Exception_Response in our result set.
        if ($domain instanceof Shopware_StoreApi_Exception_Response) {
            return $domain;
        } else {
            $resultSet = $this->getAccountService()->getLicencedProducts(
                $this->identity,
                $domain,
                $shopwareVersion
            );
        }

        //check if the request was successfully, in case of an error we have an instance of
        //Shopware_StoreApi_Exception_Response in our result set.
        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return $resultSet;
        } else {
            $products = array();

            /**@var $product Shopware_StoreApi_Models_Licence */
            foreach($resultSet->getIterator() as $product) {
                $data = $product->getRawData();

                $payed = (int) $data['payed'];
                if ($payed === 1) {
                    $products[] = array(
                        'id' => $data['id'],
                        'ordernumber' => $data['ordernumber'],
                        'download' => $data['downloads']['download']['url'],
                        'plugin_version' => $data['downloads']['plugin_version'],
                        'download_type' => $data['downloads']['type'],
                        'licence' => $data['licence'],
                        'plugin' => $data['plugin']
                    );
                }
            }

            return $products;
        }
    }

    /**
     * The getDefaultCategory returns a phantom category for "All extensions" in the plugin manager
     * navigation.
     * @return array
     */
    public function getDefaultCategory()
    {
        return array(array(
            'id' => null,
            'parent' => null,
            'description' => 'Alle Erweiterungen',
            'position' => 0,
            'children' => 0,
            'selected' => true
        ));
    }

    /**
     * The getCategories function returns an array with all defined categories
     * of the community store.
     * @return array|Shopware_StoreApi_Core_Response_SearchResult
     */
    public function getCategories()
    {
        $query = new Shopware_StoreApi_Models_Query_Category();
        $query->setOrderBy(Shopware_StoreApi_Models_Query_Category::ORDER_BY_DESCRIPTION);
        $query->setOrderDirection(Shopware_StoreApi_Models_Query_Category::ORDER_DIRECTION_ASC);

        /**@var $resultSet Shopware_StoreApi_Core_Response_SearchResult*/
        $resultSet = $this->getCategoryService()->getCategories($query);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return $resultSet;
        }
        $iterator = $resultSet->getIterator();
        $categories = array();

        /**@var $categoryModel Shopware_StoreApi_Models_Category */
        foreach($iterator as $categoryModel) {
            $categories[] = $categoryModel->getRawData();
        }
        return $categories;
    }

    /**
     * The getTopSellerCategory function is an internal helper function to create
     * a phantom community store category with the both top seller plugins.
     * This phantom category has the flag isTopSeller with true, all other categories
     * have this flag set to false.
     * @param null $page
     * @param null $limit
     * @param      $shopwareVersion
     * @internal param null $offset
     * @return array|Shopware_StoreApi_Core_Response_SearchResult
     */
    public function getTopSellerCategory($page = null, $limit = null, $shopwareVersion)
    {
        //todo@dr: shopware version mit rein geben.
        $resultSet = $this->getProductService()->getBannerHighlights();

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return array('success' => false, 'message' => $resultSet->getMessage(), 'code' => $resultSet->getCode());
        }
        $iterator = $resultSet->getIterator();
        $products = array();

        /**@var $product Shopware_StoreApi_Models_Product */
        foreach($iterator as $product) {
            $data  = $product->getRawData();
            $data['details'] = $product->getDetails();
            $products[] = $data;
        }

        $pages = array_chunk($products, $limit);
        return array(
            'success' => true,
            'data' => array(
                array(
                    'id' => null,
                    'parent' => null,
                    'isTopSeller' => true,
                    'description' => 'Die beliebtesten Plugins im Community Store',
                    'children' => 0,
                    'position' => -1,
                    'products' => $pages[$page]
                ),
            ),
            'total' => $resultSet->getTotal()
        );
    }

    /**
     * The getCategoriesWithProducts function returns all defined community store categories,
     * with the first six products of the category.
     * The function selects first all categories and calls for each category the internal helper function
     * "getCategoryProducts" which selects all products for a specify store category.
     *
     * @param      $offset
     * @param      $limit
     * @param null $orderBy
     * @param null $filters
     * @param      $shopwareVersion
     * @return array|Shopware_StoreApi_Core_Response_SearchResult
     */
    public function getCategoriesWithProducts($offset = null, $limit = null, $orderBy = null, $filters = null, $shopwareVersion)
    {
        $categoryQuery = new Shopware_StoreApi_Models_Query_Category();
        $categoryQuery->setOrderBy(Shopware_StoreApi_Models_Query_Category::ORDER_BY_DESCRIPTION);
        $categoryQuery->setOrderDirection(Shopware_StoreApi_Models_Query_Category::ORDER_DIRECTION_ASC);
        $productQuery = $this->getProductQueryForListing($offset, $limit, $orderBy, $filters);
        $productQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Version($shopwareVersion)
        );

        $resultSet = $this->getProductService()->getProductsGroupByCategories($productQuery, $categoryQuery);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return $resultSet;
        }
        $iterator = $resultSet->getIterator();
        $categories = array();

        /**@var $model Shopware_StoreApi_Models_Category */
        foreach($iterator as $model) {
            $category = $model->getRawData();
            $productResult = $model->getProducts();
            if ($productResult instanceof Shopware_StoreApi_Exception_Response) {
                $products = array('message' => $productResult->getMessage(), 'code' => $productResult->getCode());
            } else {
                $products = array();
                /**@var $productModel Shopware_StoreApi_Models_Product*/
                foreach($productResult as $productModel) {
                    $product = $productModel->getRawData();
                    $product['details'] = $productModel->getDetails();
                    $products[] = $product;
                }
            }
            unset($category['_products']);
            $category['products'] = $products;
            $categories[] = $category;
        }

        return $categories;
    }

    /**
     * The getProductQueryForListing function return an instance of Shopware_StoreApi_Models_Query_Product
     * which is used in the overview listing of the plugin manager backend module.
     *
     * @param $offset
     * @param $limit
     * @param $orderBy
     * @param $filters
     * @return Shopware_StoreApi_Models_Query_Product
     */
    public function getProductQueryForListing($offset, $limit, $orderBy, $filters)
    {
        $productQuery = new Shopware_StoreApi_Models_Query_Product();

        $productQuery->setStart($offset)
                     ->setLimit($limit);

        $productQuery->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_PLUGIN_NAME);
        $productQuery->setOrderDirection(Shopware_StoreApi_Models_Query_Product::ORDER_DIRECTION_ASC);
        if (!empty($orderBy)) {
            switch(strtolower($orderBy['property'])) {
                case "datum":
                    $productQuery->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_CREATION_DATE);
                    $productQuery->setOrderDirection($orderBy['direction']);
                    break;
                case "sales":
                    $productQuery->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_SALES);
                    $productQuery->setOrderDirection($orderBy['direction']);
                    break;
                default:
                    $productQuery->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_PLUGIN_NAME);
                    $productQuery->setOrderDirection($orderBy['direction']);
            }
        }

        if (!empty($filters)) {
            $values = array();
            foreach($filters as $filter) {
                $values[] = $filter['value'];
            }
            $productQuery->addCriterion(
                new Shopware_StoreApi_Models_Query_Criterion_Search($values)
            );
        }
        return $productQuery;
    }

    public function getDomainMessage() {
        $url = 'store.shopware.de';
        if ($this->getIdentity()) {
            $url = $this->getIdentity()->getAccountUrl();
        }
        $namespace = Shopware()->Snippets()->getNamespace('backend/plugin_manager/main');
        $message = $namespace->get('domain_failed', "Your currently used shop domain isn't associated with your shopware account.");
        $link = ' <a target="_blank" href="' . $url . '">' . $namespace->get('account_link', 'Open account configuration.') . '</a>';
        $message = $message . $link;
        return $message;
    }

    /**
     * The getCategoryProducts function selects all products of the community store
     * for the passed category (have to been an instance of Shopware_StoreApi_Models_Category).
     * The offset and limit parameter can be used for a paging.
     * The orderBy and filter parameter can be used for sorting and filtering.
     *
     * @param      $category  Shopware_StoreApi_Models_Category
     * @param      $offset    int
     * @param      $limit     int
     * @param null $orderBy   array|null
     * @param null $filters
     * @param      $shopwareVersion
     * @return array
     */
    public function getCategoryProducts($category, $offset, $limit, $orderBy = null, $filters = null, $shopwareVersion)
    {
        $query = new Shopware_StoreApi_Models_Query_Product();
        $query->setStart($offset)
              ->setLimit($limit);

        $query->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Category($category)
        );
        $query->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Version($shopwareVersion)
        );


        $query->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_PLUGIN_NAME);
        $query->setOrderDirection(Shopware_StoreApi_Models_Query_Product::ORDER_DIRECTION_ASC);
        if (!empty($orderBy)) {
            switch(strtolower($orderBy['property'])) {
                case "datum":
                    $query->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_CREATION_DATE);
                    $query->setOrderDirection($orderBy['direction']);
                    break;
                case "sales":
                    $query->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_SALES);
                    $query->setOrderDirection($orderBy['direction']);
                    break;
                default:
                    $query->setOrderBy(Shopware_StoreApi_Models_Query_Product::ORDER_BY_PLUGIN_NAME);
                    $query->setOrderDirection($orderBy['direction']);
            }
        }

        if (!empty($filters)) {
            $values = array();
            foreach($filters as $filter) {
                $values[] = $filter['value'];
            }
            $query->addCriterion(
                new Shopware_StoreApi_Models_Query_Criterion_Search($values)
            );
        }

        $resultSet = $this->getProductService()->getProducts($query);
        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return $resultSet;
        }
        $iterator = $resultSet->getIterator();
        $products = array();

        /**@var $product Shopware_StoreApi_Models_Product */
        foreach($iterator as $product) {
            $data  = $product->getRawData();
            $data['details'] = $product->getDetails();
            $products[] = $data;
        }

        return $products;
    }

    /**
     * The getCategoryWithProducts function returns the category data with their products for the passed category id.
     * The offset and limit parameter is used for the listing of the category products.
     * The orderBy and filter is used to filter and sorting the category products.
     *
     * @param      $categoryId
     * @param      $offset
     * @param      $limit
     * @param null $orderBy
     * @param null $filter
     * @param      $shopwareVersion
     * @return array
     */
    public function getCategoryWithProducts($categoryId, $offset, $limit, $orderBy = null, $filter = null, $shopwareVersion)
    {
        /**@var $categoryModel Shopware_StoreApi_Models_Category */
        $categoryModel = $this->getCategoryService()->getCategoryById($categoryId);
        $products = $this->getCategoryProducts($categoryModel, $offset, $limit, $orderBy, $filter, $shopwareVersion);

        $category = $categoryModel->getRawData();
        if ($products instanceof Shopware_StoreApi_Exception_Response) {
            $category['products'] = array('message' => $products->getMessage(), 'code' => $products->getCode());
        } else {
            $category['products'] = $products;
        }
        return $category;
    }

    /**
     * The getPluginCommunityData function returns the community plugin data for the passed Shopware\Models\Plugin\Plugin
     * object.
     *
     * @param $plugin \Shopware\Models\Plugin\Plugin
     * @return array|\Shopware_StoreApi_Core_Response_SearchResult
     */
    public function getPluginCommunityData($plugin)
    {
        $query = new Shopware_StoreApi_Models_Query_Product();
        $query->setLimit(1);

        $query->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_PluginName($plugin->getName())
        );

        $resultSet = $this->getProductService()->getProducts($query);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            return array('message' => $resultSet->getMessage(), 'code' => $resultSet->getCode());
        } else {
            return $resultSet->getIterator()->current()->getRawData();
        }
    }

    /**
     * The getUpdateablePlugins function checks if for the passed plugins updates available and returns
     * the updateable plugins.
     * @param array $plugins
     * @return Shopware_StoreApi_Core_Response_SearchResult
     */
    public function getUpdateablePlugins($plugins)
    {
        $resultSet = $this->getProductService()->getProductUpdates($plugins);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            if ($resultSet->getCode() == 200) {
                return array(
                    'success' => true,
                    'data' => array(),
                    'total' => 0
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $resultSet->getMessage(),
                    'code' => $resultSet->getCode()
                );
            }
        } else {
            foreach($resultSet as $key => &$plugin) {
                if (array_key_exists($key, $plugins)) {
                    $plugin['pluginId'] = $plugins[$key]['pluginId'];
                }
            }

            return array(
                'success' => true,
                'data' => array_values($resultSet),
                'total' => count($resultSet)
            );
        }
    }


    /**
     * The getPluginsAvailableFor method checks if a list of given plugins
     * is available for a given version of shopware
     *
     * @param array $plugins
     * @param string $version
     * @return Array
     */
    public function getPluginsAvailableFor($plugins, $version)
    {
        // Construct API query
        $productService = $this->getApi()->getProductService();
        $productQuery = new Shopware_StoreApi_Models_Query_Product();

        $productQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_PluginName($plugins)
        );
        $productQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_Version($version)
        );

        $resultSet = $productService->getProducts($productQuery);

        // First mark all plugins as incompatible
        $results = array();
        foreach ($plugins as $name) {
            $results[$name] = false;
        }

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            // If an empty result is returned, non of the passed plugins was compatible
            if ($resultSet->getCode() == 200) {
                return array(
                    'success' => true,
                    'data' => $results,
                    'total' => count($results)
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $resultSet->getMessage(),
                    'code' => $resultSet->getCode()
                );
            }
        } else {
            // mark returned plugins as compatible
            foreach($resultSet as  $productModel) {
                $names  = $productModel->getPluginNames();
                foreach ($names as $name) {
                    $results[$name] = true;
                }
            }

            return array(
                'success' => true,
                'data' => $results,
                'total' => count($results)
            );
        }
    }


    /**
     * Get plugin infos for a list of plugin names
     *
     * @param array $plugins
     * @return array
     */
    public function getPluginInfos($plugins)
    {
        // Construct API query
        $productService = $this->getApi()->getProductService();
        $productQuery = new Shopware_StoreApi_Models_Query_Product();

        $productQuery->addCriterion(
            new Shopware_StoreApi_Models_Query_Criterion_PluginName($plugins)
        );

        $resultSet = $productService->getProducts($productQuery);

        if ($resultSet instanceof Shopware_StoreApi_Exception_Response) {
            // If an empty result is returned, non of the passed plugins was compatible
            if ($resultSet->getCode() == 200) {
                return array(
                    'success' => true,
                    'data' => array(),
                    'total' => 0
                );
            } else {
                return array(
                    'success' => false,
                    'message' => $resultSet->getMessage(),
                    'code' => $resultSet->getCode()
                );
            }
        } else {

            return array(
                'success' => true,
                'data' => $resultSet,
                'total' => count($resultSet)
            );
        }
    }

    /**
     * helper method to check if the directory is writable
     * Used to check if a plugin can be extracted in this directory
     *
     * @param $directory | the directory in which the permissions are checked
     * @param bool $recursive | if true, the directory will be checked recursively
     *
     * @return bool
     */
    protected function isPluginDirectoryWritable($directory, $recursive = false)
    {
        if (!$recursive) {
            return is_writable($directory);
        } else {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory),
                RecursiveIteratorIterator::CHILD_FIRST
            );

            foreach ($iterator as $path) {
                if (!is_writable($path->__toString())) {
                    return false;
                }
            }
            return true;
        }
    }
}
