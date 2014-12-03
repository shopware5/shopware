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

use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\OrderRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginLicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\BasketStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\LicenceStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use ShopwarePlugins\PluginManager\Components\PluginCategoryService;

class Shopware_Controllers_Backend_PluginManager
    extends Shopware_Controllers_Backend_ExtJs
{
    public function preDispatch()
    {
        if (strtolower($this->Request()->getActionName()) == 'index') {
            $available = $this->checkStoreApi();

            if ($available) {
                $this->getCategoryService()->synchronize();
            }

            $this->get('shopware.plugin_manager')->refreshPluginList();
        }

        parent::preDispatch();
    }

    public function pingStoreAction()
    {
        $available = $this->checkStoreApi();

        $this->View()->assign('success', $available);
    }

    public function checkIonCubeLoaderAction()
    {
        $this->View()->assign(array('success' => extension_loaded('ionCube Loader')));
    }

    public function getCategoriesAction()
    {
        $categories = $this->getCategoryService()->get(
            $this->getLocale()
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $categories
        ));
    }

    public function storeListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign(array(
                'success' => false,
                'data' => array()
            ));
            return;
        }

        $categoryId = $this->Request()->getParam('categoryId', null);

        $filter = $this->Request()->getParam('filter', array());

        $sort = $this->Request()->getParam('sort',
            [['property' => 'release']]
        );

        if ($categoryId) {
            switch ($categoryId) {
                case PluginCategoryService::CATEGORY_HIGHLIGHTS:
                    $filter[] = ['property' => 'topseller', 'value' => true];
                    break;
                case PluginCategoryService::CATEGORY_NEWCOMER:
                    $filter[] = ['property' => 'newcomer', 'value' => true];
                    break;
                case PluginCategoryService::CATEGORY_RECOMMENDATION:
                    $filter[] = ['property' => 'recommendation', 'value' => true];
                    break;
                default:
                    $filter[] = ['property' => 'categoryId', 'value' => $categoryId];
            }
        }

        $context = new ListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->Request()->getParam('start', 0),
            $this->Request()->getParam('limit', 30),
            $filter,
            $sort
        );

        /**@var $listingResult ListingResultStruct*/
        $listingResult = $this->get('plugin_service_view')
            ->getStoreListing($context);

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($listingResult->getPlugins()),
            'total' => $listingResult->getTotalCount()
        ));
    }

    public function localListingAction()
    {
        $this->get('shopware.plugin_manager')->refreshPluginList();

        $sort = [];
        foreach ($this->Request()->getParam('sort', []) as $sortData) {
            if ($sortData['property'] == 'groupingState') {
                continue;
            }
            $sort[] = $sortData;
        }

        $context = new ListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->Request()->getParam('offset', null),
            $this->Request()->getParam('limit', null),
            $this->Request()->getParam('filter', []),
            $sort
        );

        if ($this->isApiAvailable()) {
            $plugins = $this->get('plugin_service_view')
                ->getLocalListing($context);
        } else {
            $plugins = $this->get('plugin_service_local')
                ->getListing($context)->getPlugins();
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($plugins)
        ));
    }

    public function detailAction()
    {
        $technicalName = $this->Request()->getParam('technicalName', null);

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            [$technicalName]
        );

        $plugin = $this->get('plugin_service_local')->getPlugin($context);

        $this->View()->assign(array('success' => true, 'data' => $plugin));
    }

    public function licenceListAction()
    {
        $context = new LicenceRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->getDomain(),
            $this->getAccessToken()
        );

        $licences = $this->get('plugin_service_store_production')
            ->getLicences($context);

        $this->View()->assign(array(
            'success' => true,
            'data' => array_values($licences)
        ));
    }

    public function updateListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign('success', false);
            return;
        }

        $plugins = $this->get('plugin_service_local')->getPluginsForUpdateCheck();

        $context = new UpdateListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->getDomain(),
            $plugins
        );

        $updates = $this->get('plugin_service_view')->getUpdates($context);

        $this->View()->assign(array('data' => array_values($updates)));
    }

    public function checkLicencePluginAction()
    {
        $plugin = $this->getPluginModel('SwagLicense');

        switch (true) {
            case (!$plugin instanceof Shopware\Models\Plugin\Plugin):
                $state = 'download';
                break;

            case ($plugin->getInstalled() == null):
                $state = 'install';
                break;

            case (!$plugin->getActive()):
                $state = 'activate';
                break;

            default:
                $this->View()->assign('success', true);
                return;
        }

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            ['SwagLicense']
        );

        $data = $this->get('plugin_service_view')->getPlugin($context);

        $this->View()->assign(array(
            'success' => false,
            'data' => $data,
            'state' => $state
        ));
    }

    public function purchasePluginAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');

        $price = $this->Request()->getParam('price');

        $type = $this->Request()->getParam('priceType');

        $token = $this->getAccessToken();

        $context = new OrderRequest(
            $this->getDomain(),
            $this->getDomain(),
            $orderNumber,
            $price,
            $type
        );

        try {
            $this->get('store_order_service')
                ->orderPlugin($token, $context);
        } catch (StoreException $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $this->getExceptionMessage($e)
            ));
            return;
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign(array('success' => true));
    }

    public function checkoutAction()
    {
        $positions = $this->Request()->getParam('positions');
        $positions = json_decode($positions, true);

        $token = $this->getAccessToken();

        $context = new OrderRequest(
            $this->getDomain(),
            $this->getDomain(),
            $positions[0]['orderNumber'],
            $positions[0]['price'],
            $positions[0]['type']
        );

        try {
            $basket = $this->get('store_order_service')
                ->getCheckout($token, $context);

            $this->loadBasketPlugins($basket, $positions);
        } catch (StoreException $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $this->getExceptionMessage($e)
            ));
            return;
        }

        $this->View()->assign(array(
            'success' => true,
            'data' => $basket
        ));
    }

    public function downloadLicenceDirectAction()
    {
        $link = $this->Request()->getParam('binaryLink');

        $licence = $this->Request()->getParam('licenceKey');

        $struct = new LicenceStruct();
        $struct->setBinaryLink($link);
        $struct->setLicenseKey($licence);

        try {
            $this->downloadPluginLicence($struct);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign('success', true);
    }

    /**
     * @throws Exception
     */
    public function downloadPluginLicenceAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');

        $context = new PluginLicenceRequest(
            $this->getAccessToken(),
            $this->getDomain(),
            $this->getVersion(),
            $technicalName
        );

        $licence = $this->get('plugin_service_store_production')
            ->getPluginLicence($context);

        try {
            $this->downloadPluginLicence($licence);
        } catch (Exception $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $e->getMessage()
            ));
            return;
        }

        $this->View()->assign('success', true);
    }

    public function importPluginLicenceAction()
    {
        $key = $this->Request()->getParam('licenceKey');

        $this->importLicence($key);

        $this->View()->assign('success', true);
    }

    public function updateDummyPluginAction()
    {
        $technicalName = $this->Request()->getParam('technicalName', null);

        $this->get('plugin_download_service')->downloadDummy(
            $technicalName,
            $this->getVersion()
        );

        $this->View()->assign('success', true);
    }

    public function downloadUpdateAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');

        $context = new UpdateRequest(
            $this->getAccessToken(),
            $technicalName,
            $this->getDomain(),
            $this->getVersion()
        );

        $this->get('plugin_download_service')
            ->downloadUpdate($context);

        $this->View()->assign('success', true);
    }

    public function getAccessTokenAction()
    {
        $token = $this->getAccessToken();

        if ($token == null) {
            $this->View()->assign('success', false);
        } else {
            $this->View()->assign(array(
                'success' => true,
                'shopwareId' => $token->getShopwareId()
            ));
        }
    }

    public function loginAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign('success', false);
            return;
        }

        $shopwareId = $this->Request()->getParam('shopwareId');
        $password = $this->Request()->getParam('password');

        try {
            $token = $this->get('store_client')->getAccessToken(
                $shopwareId,
                $password
            );
        } catch (StoreException $e) {
            $this->View()->assign(array(
                'success' => false,
                'message' => $this->getExceptionMessage($e)
            ));
            return;
        }

        $this->get('BackendSession')->offsetSet('store_token', serialize($token));

        $this->View()->clearAssign();
        $this->View()->assign('success', true);
    }

    /**
     * @return null|AccessTokenStruct
     */
    private function getAccessToken()
    {
        if (!$this->get('BackendSession')->offsetExists('store_token')) {
            return null;
        }

        if (!$this->isApiAvailable()) {
            return null;
        }

        /**@var $token AccessTokenStruct*/
        $token = $this->get('BackendSession')->offsetGet('store_token');
        $token = unserialize($token);

        return $token;
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        return Shopware()->Auth()->getIdentity()->locale->getLocale();
    }

    /**
     * @return string
     */
    private function getDomain()
    {
        $repo = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');

        $default = $repo->getActiveDefault();

        return $default->getHost();
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        $version = Shopware::VERSION;

        if ($version == '___VERSION___') {
            return '4.2.0';
        } else {
            return $version;
        }
    }

    /**
     * @param $technicalName
     * @return \Shopware\Models\Plugin\Plugin
     */
    private function getPluginModel($technicalName)
    {
        $repo = Shopware()->Models()->getRepository('Shopware\Models\Plugin\Plugin');
        $plugin = $repo->findOneBy(array('name' => $technicalName));
        return $plugin;
    }

    /**
     * @param StoreException $exception
     * @return mixed|string
     * @throws Exception
     */
    private function getExceptionMessage(StoreException $exception)
    {
        $namespace = $this->get('snippets')
            ->getNamespace('backend/plugin_manager/exceptions');

        if ($namespace->offsetExists($exception->getMessage())) {
            $snippet = $namespace->get($exception->getMessage());
        } else {
            $snippet = $exception->getMessage();
        }

        $snippet .= '<br><br>Error code: ' . $exception->getSbpCode();

        return $snippet;
    }

    /**
     * @return bool
     */
    private function isApiAvailable()
    {
        if ($this->get('BackendSession')->offsetExists('sbp_available')) {
            return (bool) $this->get('BackendSession')->offsetGet('sbp_available');
        }

        return $this->checkStoreApi();
    }

    /**
     * @return bool
     */
    private function checkStoreApi()
    {
        try {
            $this->get('account_manager_service')->pingServer();
            $this->get('BackendSession')->offsetSet('sbp_available', 1);
        } catch (Exception $e) {
            $this->get('BackendSession')->offsetSet('sbp_available', 0);
        }

        return (bool) $this->get('BackendSession')->offsetGet('sbp_available');
    }

    /**
     * @return PluginCategoryService
     * @throws Exception
     */
    private function getCategoryService()
    {
        return new PluginCategoryService(
            $this->get('plugin_service_store'),
            $this->get('dbal_connection'),
            $this->get('plugin_installer_struct_hydrator')
        );
    }

    /**
     * @param $licenceKey
     * @return int
     */
    private function importLicence($licenceKey)
    {
        $persister = new \Shopware_Components_LicensePersister(
            $this->get('dbal_connection')
        );

        $info = \Shopware_Components_License::readLicenseInfo($licenceKey);

        if ($info == false) {
            throw new RuntimeException();
        }

        return $persister->saveLicense($info, true);
    }

    /**
     * @param LicenceStruct $licence
     * @throws Exception
     */
    private function downloadPluginLicence(LicenceStruct $licence)
    {
        $success = $this->get('plugin_download_service')
            ->downloadPlugin($this->getAccessToken(), $licence);

        if ($success && strlen($licence->getLicenseKey()) > 0) {
            $this->importLicence($licence->getLicenseKey());
        }

        $this->get('shopware.plugin_manager')->refreshPluginList();
    }

    private function loadBasketPlugins(BasketStruct $basket, $positions)
    {
        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            array_column($positions, 'technicalName')
        );

        $plugins = $this->get('plugin_service_store_production')
            ->getPlugins($context);

        foreach ($basket->getPositions() as $position) {
            $name = $this->getTechnicalNameOfOrderNumber($position->getOrderNumber(), $positions);

            if ($name == null) {
                continue;
            }

            $key = strtolower($name);
            $position->setPlugin($plugins[$key]);
        }
    }

    private function getTechnicalNameOfOrderNumber($orderNumber, $positions)
    {
        foreach ($positions as $requestPosition) {
            if ($requestPosition['orderNumber'] != $orderNumber) {
                continue;
            }

            return $requestPosition['technicalName'];
        }

        return null;
    }
}
