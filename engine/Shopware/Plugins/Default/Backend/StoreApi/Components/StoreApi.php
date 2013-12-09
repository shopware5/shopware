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

class Shopware_Components_StoreApi extends Enlight_Class
{
    /**
     * Holds the rest client
     *
     * @var Shopware_StoreApi_Core_Rest_Client
     */
    private $client = null;
    private $config = null;

    private $productService = null;
    private $categoryService = null;
    private $vendorService = null;
    private $authService = null;
    private $accountService = null;
    private $orderService = null;

    public function init()
    {
        $this->client = new Shopware_StoreApi_Core_Rest_Client();
        $this->startClient();
    }

    public function getClient()
    {
        return $this->client;
    }

    /**
     * Starts the client
     */
    protected function startClient()
    {
        $storeApiUrl = Shopware()->Plugins()->Backend()->StoreApi()->Config()->StoreApiUrl;
        if (empty($storeApiUrl)) {
            throw new Shopware_StoreApi_Exception_Exception('there is no store api url configured');
        }

        $this->client->setConfig($this->Config());
        $this->client->startClient($storeApiUrl);
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Product
     */
    public function getProductService()
    {
        if($this->productService === null) {
            $this->productService = new Shopware_StoreApi_Core_Service_Product();
        }

        return $this->productService;
    }

    /**
     * @return Shopware_StoreApi_Core_Service_Category
     */
    public function getCategoryService()
    {
        if($this->categoryService === null) {
            $this->categoryService = new Shopware_StoreApi_Core_Service_Category();
        }

        return $this->categoryService;
    }

    public function getVendorService()
    {
        if($this->vendorService === null) {
            $this->vendorService = new Shopware_StoreApi_Core_Service_Vendor();
        }

        return $this->vendorService;
    }

    public function getAuthService()
    {
        if($this->authService === null) {
            $this->authService = new Shopware_StoreApi_Core_Service_Auth();
        }

        return $this->authService;
    }

    public function getAccountService()
    {
        if($this->accountService === null) {
            $this->accountService = new Shopware_StoreApi_Core_Service_Account();
        }

        return $this->accountService;
    }

    public function getOrderService()
    {
        if($this->orderService === null) {
            $this->orderService = new Shopware_StoreApi_Core_Service_Order();
        }

        return $this->orderService;
    }

    public function Config()
    {
        if ($this->config === null) {
            $this->config = new Shopware_Components_StoreConfig();
        }

        return $this->config;
    }
}
