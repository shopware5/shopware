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

use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\OrderRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginLicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Exception\AuthenticationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Service\DownloadService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLicenceService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLocalService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginViewService;
use Shopware\Bundle\PluginInstallerBundle\Service\StoreOrderService;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\BasketStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationStruct;
use Shopware\Models\Menu\Menu;
use Shopware\Models\Plugin\Plugin;
use ShopwarePlugins\PluginManager\Components\PluginCategoryService;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;

class Shopware_Controllers_Backend_PluginManager extends Shopware_Controllers_Backend_ExtJs
{
    const FALLBACK_LOCALE = 'en_GB';

    public function preDispatch()
    {
        if (strtolower($this->Request()->getActionName()) == 'index' && $this->checkStoreApi()) {
            $this->getCategoryService()->synchronize();
        }
        parent::preDispatch();
    }

    public function metaDownloadAction()
    {
        try {
            $request = new MetaRequest(
                $this->Request()->getParam('technicalName'),
                $this->getVersion(),
                $this->getDomain(),
                $this->getAccessToken()
            );

            /**@var $service DownloadService */
            $service = $this->get('shopware_plugininstaller.plugin_download_service');
            $result = $service->getMetaInformation($request);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    public function rangeDownloadAction()
    {
        $url    = $this->Request()->getParam('uri');
        $offset = $this->Request()->getParam('offset');
        $size   = $this->Request()->getParam('size');
        $sha1   = $this->Request()->getParam('sha1');
        $name   = $this->Request()->getParam('fileName', 'download.zip');

        $destination = Shopware()->Container()->getParameter('kernel.root_dir') . '/files/downloads/' . $name;
        if ($offset == 0) {
            unlink($destination);
        }

        $request = new RangeDownloadRequest($url, $offset, $size, $sha1, $destination);

        try {
            /**@var $service DownloadService */
            $service = $this->get('shopware_plugininstaller.plugin_download_service');
            $result = $service->downloadRange($request);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        if ($result instanceof FinishResult) {
            $this->View()->assign(['success' => true, 'finish' => true, 'destination' => $destination]);
        } else {
            /**@var $result \Shopware\Recovery\Update\Steps\ValidResult*/
            $this->View()->assign(['success' => true, 'finish' => false, 'offset' => $result->getOffset()]);
        }
    }

    public function extractAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');
        $fileName      = $this->Request()->getParam('fileName');
        $service       = Shopware()->Container()->get('shopware_plugininstaller.plugin_download_service');

        try {
            $service->extractPluginZip($fileName, $technicalName);

            /** @var InstallerService $pluginManager */
            $pluginManager  = $this->get('shopware_plugininstaller.plugin_manager');
            $pluginManager->refreshPluginList();
        } catch (Exception $e) {
            $this->View()->assign(['succees' => false, 'message' => $e->getMessage()]);
        }

        $this->View()->assign('success', true);
    }

    public function pingStoreAction()
    {
        $available = $this->checkStoreApi();

        $this->View()->assign('success', $available);
    }

    public function checkIonCubeLoaderAction()
    {
        $this->View()->assign(['success' => extension_loaded('ionCube Loader')]);
    }

    public function getCategoriesAction()
    {
        $categories = $this->getCategoryService()->get(
            $this->getLocale(),
            self::FALLBACK_LOCALE
        );

        $this->View()->assign([
            'success' => true,
            'data' => $categories
        ]);
    }

    /**
     * check if secret is set and set if it isn't
     * return true if secret finally exists
     */
    public function checkSecretAction()
    {
        $subscriptionService = $this->container->get('shopware_plugininstaller.subscription_service');
        $secret = $subscriptionService->getShopSecret();

        if (empty($secret)) {
            try {
                $subscriptionService->setShopSecret();
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'error' => $e->getMessage()]);
            }
        } else {
            $this->View()->assign('success', true);
        }
    }

    /**
     * Returns not upgraded plugins, "hacked" plugins, plugins which loose subscription to json-view
     */
    public function getPluginInformationAction()
    {
        $subscriptionService = $this->container->get('shopware_plugininstaller.subscription_service');
        $pluginLicenseService = $this->container->get('shopware_plugininstaller.plugin_licence_service');
        $pluginInformation = $subscriptionService->getPluginInformation($this->Response(), $this->Request());

        if ($pluginInformation === false) {
            try {
                $pluginInformationStructs = $pluginLicenseService->getExpiringLicenses();
                $pluginInformation = new PluginInformationResultStruct($pluginInformationStructs);
            } catch (\Exception $e) {
                $this->View()->assign(['succes' => false, 'message' => $e->getMessage()]);
                return;
            }
        }
        $this->View()->assign(['success' => true, 'data' => $pluginInformation]);
    }

    public function storeListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign(['success' => false, 'data' => []]);
            return;
        }

        $categoryId = $this->Request()->getParam('categoryId', null);

        $filter = $this->Request()->getParam('filter', []);

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

        try {
            /** @var PluginViewService $pluginViewService */
            $pluginViewService = $this->get('shopware_plugininstaller.plugin_service_view');
            $listingResult = $pluginViewService->getStoreListing($context);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($listingResult->getPlugins()),
            'total' => $listingResult->getTotalCount()
        ]);
    }

    public function refreshPluginListAction()
    {
        /** @var InstallerService $pluginManager */
        $pluginManager = $this->get('shopware_plugininstaller.plugin_manager');
        $pluginManager->refreshPluginList();
        $this->View()->assign([
            'success' => true
        ]);
    }

    public function localListingAction()
    {
        $this->registerShutdown();

        $error = null;
        try {
            /** @var InstallerService $pluginManager */
            $pluginManager = $this->get('shopware_plugininstaller.plugin_manager');
            $pluginManager->refreshPluginList();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $context = new ListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->Request()->getParam('offset', null),
            $this->Request()->getParam('limit', null),
            $this->Request()->getParam('filter', []),
            $this->getListingSorting()
        );

        if ($this->isApiAvailable()) {
            /** @var PluginViewService $pluginViewService */
            $pluginViewService = $this->get('shopware_plugininstaller.plugin_service_view');
            $plugins = $pluginViewService->getLocalListing($context);
        } else {
            /** @var PluginLocalService $localService */
            $localService = $this->get('shopware_plugininstaller.plugin_service_local');
            $plugins = $localService->getListing($context)->getPlugins();
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
            'error' => $error
        ]);
    }

    /**
     * Returns the sorting criteria for the plugin listing
     * Shows installed plugins, then inactive, then uninstalled.
     * Afterwards applies the custom sorting from the request,
     * and then 'installation_date DESC' as fallback.
     *
     * @return array
     */
    private function getListingSorting()
    {
        $prioritySorting = [
            ['property' => 'active', 'direction' => 'DESC'],
            ['property' => 'installation_date IS NULL', 'direction' => 'ASC']
        ];

        $fallbackSorting = [
            ['property' => 'installation_date', 'direction' => 'DESC']
        ];

        $customSorting = [];
        foreach ($this->Request()->getParam('sort', []) as $sortData) {
            if ($sortData['property'] == 'groupingState') {
                continue;
            }
            $sortData['property'] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sortData['property']));
            $customSorting[] = $sortData;
        }

        return array_merge($prioritySorting, $customSorting, $fallbackSorting);
    }

    public function detailAction()
    {
        $technicalName = $this->Request()->getParam('technicalName', null);

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            [$technicalName]
        );

        /** @var PluginLocalService $localService */
        $localService = $this->get('shopware_plugininstaller.plugin_service_local');
        $plugin = $localService->getPlugin($context);

        $this->View()->assign(['success' => true, 'data' => $plugin]);
    }

    public function licenceListAction()
    {
        $context = new LicenceRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->getDomain(),
            $this->getAccessToken()
        );

        try {
            /** @var PluginStoreService $pluginStoreService */
            $pluginStoreService = $this->get('shopware_plugininstaller.plugin_service_store_production');
            $licences = $pluginStoreService->getLicences($context);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($licences)
        ]);
    }

    public function updateListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign('success', false);
            return;
        }
        $subscriptionService = $this->container->get('shopware_plugininstaller.subscription_service');
        $secret = $subscriptionService->getShopSecret();
        $domain = empty($secret) ? null : $this->getDomain();

        /** @var PluginLocalService $localService */
        $localService = $this->get('shopware_plugininstaller.plugin_service_local');
        $plugins = $localService->getPluginsForUpdateCheck();

        $context = new UpdateListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $domain,
            $plugins
        );

        try {
            /** @var PluginViewService $pluginViewService */
            $pluginViewService = $this->get('shopware_plugininstaller.plugin_service_view');
            $updates = $pluginViewService->getUpdates($context);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($updates->getPlugins()),
            'loginRecommended' => empty($secret) && $updates->isGtcAcceptanceRequired()
        ]);
    }

    public function expiredListingAction()
    {
        $pluginLicenseService = $this->container->get('shopware_plugininstaller.plugin_licence_service');
        /** @var PluginInformationStruct[] $pluginInformationStructs */
        $pluginInformationStructs = $pluginLicenseService->getExpiredLicenses();
        $expiredPlugins = [];
        foreach ($pluginInformationStructs as $pluginInformationStruct) {
            $expiredPlugins[] = $pluginInformationStruct->getTechnicalName();
        }

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            $expiredPlugins
        );

        try {
            /** @var PluginStoreService $pluginStoreService */
            $pluginStoreService = $this->get('shopware_plugininstaller.plugin_service_store_production');
            $plugins = $pluginStoreService->getPlugins($context);

            $this->View()->assign(['success' => true, 'data' => array_values($plugins)]);
        } catch (\Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
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

        try {
            /** @var PluginViewService $pluginViewService */
            $pluginViewService = $this->get('shopware_plugininstaller.plugin_service_view');
            $data = $pluginViewService->getPlugin($context);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign([
            'success' => false,
            'data' => $data,
            'state' => $state
        ]);
    }

    public function purchasePluginAction()
    {
        $orderNumber = $this->Request()->getParam('orderNumber');

        $price = $this->Request()->getParam('price');

        $type = $this->Request()->getParam('priceType');

        $domain = $this->Request()->getParam('bookingDomain');

        $token = $this->getAccessToken();

        $context = new OrderRequest(
            $this->getDomain(),
            $domain,
            $orderNumber,
            $price,
            $type
        );

        try {
            /** @var StoreOrderService $storeOrderService */
            $storeOrderService = $this->get('shopware_plugininstaller.store_order_service');
            $storeOrderService->orderPlugin($token, $context);
        } catch (StoreException $e) {
            $this->handleException($e);
            return;
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign(['success' => true]);
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
            /** @var StoreOrderService $storeOrderService */
            $storeOrderService = $this->get('shopware_plugininstaller.store_order_service');
            $basket = $storeOrderService->getCheckout($token, $context);

            $this->loadBasketPlugins($basket, $positions);
        } catch (StoreException $e) {
            $this->handleException($e);
            return;
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $basket
        ]);
    }

    public function importLicenceKeyAction()
    {
        $key = $this->Request()->getParam('licenceKey');

        try {
            /**@var $service PluginLicenceService*/
            $service = $this->get('shopware_plugininstaller.plugin_licence_service');
            $service->importLicence($key);
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        $this->View()->assign('success', true);
    }

    /**
     * @throws Exception
     */
    public function importPluginLicenceAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');

        $context = new PluginLicenceRequest(
            $this->getAccessToken(),
            $this->getDomain(),
            $this->getVersion(),
            $technicalName
        );

        /** @var PluginStoreService $pluginStoreService */
        $pluginStoreService = $this->get('shopware_plugininstaller.plugin_service_store_production');
        $licence = $pluginStoreService->getPluginLicence($context);

        if (!$licence) {
            return $this->View()->assign([
                'success' => false,
                'message' => sprintf('Licence for plugin %s not found', $technicalName)
            ]);
        }

        if (!$licence->getLicenseKey()) {
            return $this->View()->assign('success', true);
        }

        try {
            /**@var $service PluginLicenceService*/
            $service = $this->get('shopware_plugininstaller.plugin_licence_service');
            $service->importLicence($licence->getLicenseKey());
        } catch (Exception $e) {
            $this->handleException($e);
            return;
        }

        return $this->View()->assign('success', true);
    }

    public function getAccessTokenAction()
    {
        $token = $this->getAccessToken();

        if ($token == null) {
            $this->View()->assign('success', false);
        } else {
            $this->View()->assign(['success' => true, 'shopwareId' => $token->getShopwareId()]);
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
            /** @var StoreClient $storeClient */
            $storeClient = $this->get('shopware_plugininstaller.store_client');
            $token = $storeClient->getAccessToken($shopwareId, $password);
        } catch (StoreException $e) {
            $this->handleException($e);
            return;
        }

        $this->get('BackendSession')->offsetSet('store_token', serialize($token));

        $this->View()->clearAssign();
        $this->View()->assign('success', true);
    }

    public function disableConnectMenuAction()
    {
        $em = $this->container->get('models');
        $repo = $em->getRepository(Menu::class);

        /** @var Menu $menuEntry */
        $menuEntry = $repo->findOneBy(array('label' => 'Connect'));
        if ($menuEntry) {
            $menuEntry->setActive(false);
            $em->persist($menuEntry);
            $em->flush();
        }

        $this->View()->assign([
            'success' => true
        ]);
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
        return Shopware()->Container()->get('Auth')->getIdentity()->locale->getLocale();
    }

    /**
     * @return string
     */
    private function getDomain()
    {
        return $this->container->get('shopware_plugininstaller.account_manager_service')->getDomain();
    }

    /**
     * @return string
     */
    private function getVersion()
    {
        return Shopware::VERSION;
    }

    /**
     * @param string $technicalName
     * @return Plugin
     */
    private function getPluginModel($technicalName)
    {
        $repo = Shopware()->Models()->getRepository(Plugin::class);
        $plugin = $repo->findOneBy(['name' => $technicalName]);
        return $plugin;
    }

    /**
     * @param StoreException $exception
     * @return mixed|string
     * @throws Exception
     */
    private function getExceptionMessage(StoreException $exception)
    {
        /** @var \Enlight_Components_Snippet_Namespace $namespace */
        $namespace = $this->get('snippets')
            ->getNamespace('backend/plugin_manager/exceptions');

        if ($namespace->offsetExists($exception->getMessage())) {
            $snippet = $namespace->get($exception->getMessage());
        } else {
            $snippet = $exception->getMessage();
        }

        $snippet .= '<br><br>Error code: ' . $exception->getSbpCode();

        if (!($prev = $exception->getPrevious())) {
            return $snippet;
        }

        /** @var \Shopware\Components\HttpClient\RequestException $prev */
        if (!($prev instanceof \Shopware\Components\HttpClient\RequestException)) {
            return $snippet;
        }

        $data = json_decode($prev->getBody(), true);
        if (isset($data['reason'])) {
            $snippet .= '<br>' . $data['reason'];
        }

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
            $this->get('shopware_plugininstaller.account_manager_service')->pingServer();
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
            $this->get('shopware_plugininstaller.plugin_service_store'),
            $this->get('dbal_connection'),
            $this->get('shopware_plugininstaller.plugin_installer_struct_hydrator')
        );
    }

    /**
     * @param BasketStruct $basket
     * @param $positions
     */
    private function loadBasketPlugins(BasketStruct $basket, $positions)
    {
        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            array_column($positions, 'technicalName')
        );

        /** @var PluginStoreService $pluginStoreService */
        $pluginStoreService = $this->get('shopware_plugininstaller.plugin_service_store_production');
        $plugins = $pluginStoreService->getPlugins($context);

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

    private function handleException(Exception $e)
    {
        if (!($e instanceof StoreException)) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
            return;
        }

        $message = $this->getExceptionMessage($e);


        $this->View()->assign([
            'success' => false,
            'message' => $message,
            'authentication' => ($e instanceof AuthenticationException)
        ]);
    }

    /**
     * Registers php shutdown function to catch fatal and parse errors which thrown in refreshPluginList
     */
    private function registerShutdown()
    {
        register_shutdown_function(function () {
            $lasterror = error_get_last();
            if (!$lasterror) {
                return;
            }

            switch ($lasterror['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                    ob_clean();
                    ob_flush();
                    http_response_code(200);
                    $message = 'Error<br><br>' . $lasterror['message'] . '<br><br>File:' . str_replace('/', '/ ', $lasterror['file']);
                    echo json_encode(['success' => false, 'error' => $message]);
            }
        });
    }
}
