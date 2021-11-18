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

namespace Shopware\Controllers\Backend;

use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\AbstractQuery;
use Exception;
use PDO;
use RuntimeException;
use Shopware\Bundle\PluginInstallerBundle\Context\LicenceRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\OrderRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\UpdateListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Exception\AuthenticationException;
use Shopware\Bundle\PluginInstallerBundle\Exception\StoreException;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginCategoryService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLicenceService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginLocalService;
use Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService;
use Shopware\Bundle\PluginInstallerBundle\Service\SubscriptionService;
use Shopware\Bundle\PluginInstallerBundle\StoreClient;
use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\BasketStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationResultStruct;
use Shopware\Components\HttpClient\RequestException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Models\Plugin\Plugin;
use Shopware_Controllers_Backend_ExtJs;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;

class PluginManager extends Shopware_Controllers_Backend_ExtJs
{
    public const FALLBACK_LOCALE = 'en_GB';

    /**
     * @return void
     */
    public function preDispatch()
    {
        if (strtolower($this->Request()->getActionName()) === 'index' && $this->checkStoreApi()) {
            $this->getCategoryService()->synchronize();
        }
        parent::preDispatch();
    }

    /**
     * @return void
     */
    public function metaDownloadAction()
    {
        try {
            $request = new MetaRequest(
                $this->Request()->getParam('technicalName'),
                $this->getVersion(),
                $this->getDomain(),
                $this->getAccessToken()
            );

            $result = $this->get('shopware_plugininstaller.plugin_download_service')->getMetaInformation($request);
            $this->get('backendsession')->offsetSet('plugin_manager_meta_download', $result);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign(['success' => true, 'data' => $result]);
    }

    /**
     * @return void
     */
    public function rangeDownloadAction()
    {
        $metaStruct = $this->get('backendsession')->offsetGet('plugin_manager_meta_download');
        if (!$metaStruct) {
            $this->View()->assign(['success' => false, 'message' => 'Unable to retrieve meta information']);

            return;
        }

        $offset = (int) $this->Request()->getParam('offset');

        $downloadsDir = $this->container->getParameter('shopware.app.downloadsDir');
        if (!\is_string($downloadsDir)) {
            throw new RuntimeException('Parameter shopware.app.downloadsDir has to be a string');
        }

        $destination = rtrim($downloadsDir, '/') . DIRECTORY_SEPARATOR . $metaStruct->getFileName();
        if ($offset === 0) {
            unlink($destination);
        }

        $request = new RangeDownloadRequest(
            $metaStruct->getUri(),
            $offset,
            (int) $metaStruct->getSize(),
            $metaStruct->getSha1(),
            $destination
        );

        try {
            $result = $this->get('shopware_plugininstaller.plugin_download_service')->downloadRange($request);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        if ($result instanceof FinishResult) {
            $this->View()->assign(['success' => true, 'finish' => true, 'destination' => $destination]);
        } else {
            $this->View()->assign(['success' => true, 'finish' => false, 'offset' => $result->getOffset()]);
        }
    }

    /**
     * @return void
     */
    public function extractAction()
    {
        $metaStruct = $this->get('backendsession')->offsetGet('plugin_manager_meta_download');
        if (!$metaStruct) {
            $this->View()->assign(['success' => false, 'message' => 'Unable to retrieve meta information']);

            return;
        }

        $downloadsDir = $this->container->getParameter('shopware.app.downloadsDir');
        if (!\is_string($downloadsDir)) {
            throw new RuntimeException('Parameter shopware.app.downloadsDir has to be a string');
        }

        $filePath = rtrim($downloadsDir, '/') . DIRECTORY_SEPARATOR . $metaStruct->getFileName();
        $service = Shopware()->Container()->get('shopware_plugininstaller.plugin_download_service');

        try {
            $service->extractPluginZip($filePath, $metaStruct->getTechnicalName());

            $pluginManager = $this->get(InstallerService::class);
            $pluginManager->refreshPluginList();
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $this->View()->assign('success', true);
    }

    /**
     * @return void
     */
    public function pingStoreAction()
    {
        $available = $this->checkStoreApi();

        $this->View()->assign('success', $available);
    }

    /**
     * @return void
     */
    public function checkIonCubeLoaderAction()
    {
        $this->View()->assign(['success' => \extension_loaded('ionCube Loader')]);
    }

    /**
     * @return void
     */
    public function getCategoriesAction()
    {
        $categories = $this->getCategoryService()->get(
            $this->getLocale(),
            self::FALLBACK_LOCALE
        );

        $this->View()->assign([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * check if secret is set and set if it isn't
     * return true if secret finally exists
     *
     * @return void
     */
    public function checkSecretAction()
    {
        $subscriptionService = $this->container->get(SubscriptionService::class);
        $secret = $subscriptionService->getShopSecret();

        if (empty($secret)) {
            try {
                $subscriptionService->setShopSecret();
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'error' => $e->getMessage()]);

                return;
            }
        }

        $this->View()->assign('success', true);
    }

    /**
     * Returns not upgraded plugins, "hacked" plugins, plugins which loose subscription to json-view
     *
     * @return void
     */
    public function getPluginInformationAction()
    {
        $subscriptionService = $this->container->get(SubscriptionService::class);
        $pluginLicenseService = $this->container->get(PluginLicenceService::class);
        $pluginInformation = $subscriptionService->getPluginInformation($this->Response(), $this->Request());

        if ($pluginInformation === false) {
            try {
                $pluginInformationStructs = $pluginLicenseService->getExpiringLicenses();
                $pluginInformation = new PluginInformationResultStruct($pluginInformationStructs);
            } catch (Exception $e) {
                $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

                return;
            }

            $data = $pluginInformation->jsonSerialize();

            $data['shopSecretMissing'] = $subscriptionService->getException() instanceof StoreException;
            $data['live'] = false;
        } else {
            $data = $pluginInformation->jsonSerialize();
            $data['live'] = true;
        }

        $this->View()->assign(['success' => true, 'data' => $data]);
    }

    /**
     * @return void
     */
    public function storeListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign(['success' => false, 'data' => []]);

            return;
        }

        $categoryId = $this->Request()->getParam('categoryId');

        $filter = $this->Request()->getParam('filter', []);

        $sort = $this->Request()->getParam('sort', [['property' => 'release']]);

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
            (int) $this->Request()->getParam('start', 0),
            (int) $this->Request()->getParam('limit', 30),
            $filter,
            $sort
        );

        try {
            $listingResult = $this->get('shopware_plugininstaller.plugin_service_view')->getStoreListing($context);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($listingResult->getPlugins()),
            'total' => $listingResult->getTotalCount(),
        ]);
    }

    /**
     * @return void
     */
    public function refreshPluginListAction()
    {
        $pluginManager = $this->get(InstallerService::class);
        $pluginManager->refreshPluginList();
        $this->View()->assign([
            'success' => true,
        ]);
    }

    /**
     * @return void
     */
    public function localListingAction()
    {
        $this->registerShutdown();

        $error = null;
        try {
            $pluginManager = $this->get(InstallerService::class);
            $pluginManager->refreshPluginList();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $context = new ListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            (int) $this->Request()->getParam('offset', 0),
            (int) $this->Request()->getParam('limit', 30),
            $this->Request()->getParam('filter', []),
            $this->getListingSorting()
        );

        if ($this->isApiAvailable()) {
            $plugins = $this->get('shopware_plugininstaller.plugin_service_view')->getLocalListing($context);
        } else {
            $plugins = $this->get(PluginLocalService::class)->getListing($context)->getPlugins();
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($plugins),
            'error' => $error,
        ]);
    }

    /**
     * @return void
     */
    public function toggleSafeModeAction()
    {
        $plugins = $this->getThirdPartyPluginsQuery()->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        $pluginsInSafeMode = $this->getPluginsInSafeMode($plugins);

        $installer = $this->container->get(InstallerService::class);

        if ($pluginsInSafeMode) {
            foreach ($pluginsInSafeMode as $plugin) {
                $plugin->setInSafeMode(false);

                if (!$plugin->getActive()) {
                    $installer->activatePlugin($plugin);
                }
            }
            $this->container->get(ModelManager::class)->flush();
            $this->View()->assign(['success' => true, 'inSafeMode' => false]);

            return;
        }

        foreach ($plugins as $plugin) {
            if (!$plugin->getActive()) {
                continue;
            }
            $plugin->setInSafeMode(true);
            $installer->deactivatePlugin($plugin);
        }
        $this->container->get(ModelManager::class)->flush();

        $this->View()->assign(['success' => true, 'inSafeMode' => true]);
    }

    /**
     * @return void
     */
    public function isInSafeModeAction()
    {
        $query = $this->getThirdPartyPluginsQuery();
        $query->andWhere('plugin.inSafeMode = true');
        $query->andWhere('plugin.active = false');

        $plugins = $query->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        $inSafeMode = !empty($plugins);

        $query = $this->getThirdPartyPluginsQuery();
        $query->andWhere('plugin.active = true');

        $plugins = $query->getQuery()->getResult(AbstractQuery::HYDRATE_OBJECT);

        $this->View()->assign([
            'success' => true,
            'inSafeMode' => $inSafeMode,
            'hasActiveThirdPartyPlugins' => !empty($plugins),
        ]);
    }

    /**
     * @return void
     */
    public function getAllCachesAction()
    {
        $this->View()->assign([
            'caches' => InstallContext::CACHE_LIST_ALL,
        ]);
    }

    /**
     * @return void
     */
    public function detailAction()
    {
        $technicalName = $this->Request()->getParam('technicalName');

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            [$technicalName]
        );

        $plugin = $this->get(PluginLocalService::class)->getPlugin($context);

        $this->View()->assign(['success' => true, 'data' => $plugin]);
    }

    /**
     * @return void
     */
    public function licenceListAction()
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken instanceof AccessTokenStruct) {
            $this->View()->assign(['success' => false, 'message' => 'Access token is not available']);

            return;
        }

        $context = new LicenceRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->getDomain(),
            $accessToken
        );

        try {
            $licences = $this->get(PluginStoreService::class)->getLicences($context);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($licences),
        ]);
    }

    /**
     * @return void
     */
    public function updateListingAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign('success', false);

            return;
        }
        $secret = $this->container->get(SubscriptionService::class)->getShopSecret();

        $plugins = $this->get(PluginLocalService::class)->getPluginsForUpdateCheck();

        $context = new UpdateListingRequest(
            $this->getLocale(),
            $this->getVersion(),
            $this->getDomain(),
            $plugins
        );

        try {
            $updates = $this->get('shopware_plugininstaller.plugin_service_view')->getUpdates($context);
        } catch (Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => array_values($updates->getPlugins()),
            'loginRecommended' => empty($secret) && $updates->isGtcAcceptanceRequired(),
        ]);
    }

    /**
     * @return void
     */
    public function expiredListingAction()
    {
        $pluginInformationStructs = $this->container->get(PluginLicenceService::class)->getExpiredLicenses();
        $expiredPlugins = [];
        foreach ($pluginInformationStructs as $pluginInformationStruct) {
            $expiredPlugins[] = $pluginInformationStruct->getTechnicalName();
        }

        $installDates = $this->get(Connection::class)->createQueryBuilder()->from('s_core_plugins', 'plugins')
            ->addSelect('plugins.name, plugins.installation_date, plugins.capability_secure_uninstall')
            ->andWhere('name IN (:names)')
            ->setParameter('names', $expiredPlugins, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP | PDO::FETCH_UNIQUE);

        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            $expiredPlugins
        );

        try {
            $plugins = $this->get(PluginStoreService::class)->getPlugins($context);

            foreach ($plugins as $plugin) {
                if (isset($installDates[$plugin->getTechnicalName()])) {
                    $date = $installDates[$plugin->getTechnicalName()]['installation_date'];

                    if ($date) {
                        $plugin->setInstallationDate(new DateTime($date));
                    }
                    $plugin->setCapabilitySecureUninstall($installDates[$plugin->getTechnicalName()]['capability_secure_uninstall']);
                }
            }

            $this->View()->assign(['success' => true, 'data' => array_values($plugins)]);
        } catch (Exception $e) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * @return void
     */
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
            $storeOrderService = $this->get('shopware_plugininstaller.store_order_service');
            $storeOrderService->orderPlugin($token, $context);
        } catch (StoreException|Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * @return void
     */
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
            $basket = $this->get('shopware_plugininstaller.store_order_service')->getCheckout($token, $context);

            $this->loadBasketPlugins($basket, $positions);
        } catch (StoreException|Exception $e) {
            $this->handleException($e);

            return;
        }

        $this->View()->assign([
            'success' => true,
            'data' => $basket,
        ]);
    }

    /**
     * @return void
     */
    public function getAccessTokenAction()
    {
        $token = $this->getAccessToken();

        if (!$token instanceof AccessTokenStruct) {
            $this->View()->assign('success', false);
        } else {
            $this->View()->assign(['success' => true, 'shopwareId' => $token->getShopwareId()]);
        }
    }

    /**
     * @return void
     */
    public function loginAction()
    {
        if (!$this->isApiAvailable()) {
            $this->View()->assign('success', false);

            return;
        }

        $shopwareId = $this->Request()->getParam('shopwareId');
        $password = $this->Request()->getParam('password');

        try {
            $token = $this->get(StoreClient::class)->getAccessToken($shopwareId, $password);
        } catch (StoreException $e) {
            $this->handleException($e);

            return;
        }

        $this->get('backendsession')->offsetSet('store_token', serialize($token));

        $this->View()->clearAssign();
        $this->View()->assign('success', true);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getThirdPartyPluginsQuery()
    {
        return $this->container->get(ModelManager::class)->createQueryBuilder()
            ->select(['plugin'])
            ->from(Plugin::class, 'plugin')
            ->where('plugin.source != :source')
            ->andWhere('plugin.name NOT LIKE :name')
            ->setParameter(':source', 'Default')
            ->setParameter(':name', 'Swag%');
    }

    /**
     * Returns the sorting criteria for the plugin listing
     * Shows installed plugins, then inactive, then uninstalled.
     * Afterwards applies the custom sorting from the request,
     * and then 'installation_date DESC' as fallback.
     *
     * @return array<array{property: string, direction: string}>
     */
    private function getListingSorting(): array
    {
        $prioritySorting = [
            ['property' => 'active', 'direction' => 'DESC'],
            ['property' => 'installation_date IS NULL', 'direction' => 'ASC'],
        ];

        $fallbackSorting = [
            ['property' => 'installation_date', 'direction' => 'DESC'],
        ];

        $customSorting = [];
        foreach ($this->Request()->getParam('sort', []) as $sortData) {
            if ($sortData['property'] === 'groupingState') {
                continue;
            }
            $sortData['property'] = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $sortData['property']));
            $customSorting[] = $sortData;
        }

        return array_merge($prioritySorting, $customSorting, $fallbackSorting);
    }

    private function getAccessToken(): ?AccessTokenStruct
    {
        if (!$this->get('backendsession')->offsetExists('store_token')) {
            return null;
        }

        if (!$this->isApiAvailable()) {
            return null;
        }

        $allowedClassList = [
            AccessTokenStruct::class,
        ];

        return unserialize(
            $this->get('backendsession')->offsetGet('store_token'),
            ['allowed_classes' => $allowedClassList]
        );
    }

    private function getLocale(): string
    {
        return Shopware()->Container()->get('auth')->getIdentity()->locale->getLocale();
    }

    private function getDomain(): string
    {
        return $this->container->get('shopware_plugininstaller.account_manager_service')->getDomain();
    }

    private function getVersion(): string
    {
        $version = $this->container->getParameter('shopware.release.version');

        if (!\is_string($version)) {
            throw new RuntimeException('Parameter shopware.release.version has to be a string');
        }

        return $version;
    }

    private function getExceptionMessage(StoreException $exception): string
    {
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

        if (!($prev instanceof RequestException)) {
            return $snippet;
        }

        $data = json_decode($prev->getBody(), true);
        if (isset($data['reason'])) {
            $snippet .= '<br>' . $data['reason'];
        }

        return $snippet;
    }

    private function isApiAvailable(): bool
    {
        if ($this->get('backendsession')->offsetExists('sbp_available')) {
            return (bool) $this->get('backendsession')->offsetGet('sbp_available');
        }

        return $this->checkStoreApi();
    }

    private function checkStoreApi(): bool
    {
        try {
            $this->get('shopware_plugininstaller.account_manager_service')->pingServer();
            $this->get('backendsession')->offsetSet('sbp_available', 1);
        } catch (Exception $e) {
            $this->get('backendsession')->offsetSet('sbp_available', 0);
        }

        return (bool) $this->get('backendsession')->offsetGet('sbp_available');
    }

    private function getCategoryService(): PluginCategoryService
    {
        return $this->get(PluginCategoryService::class);
    }

    /**
     * @param array<array<string, mixed>> $positions
     */
    private function loadBasketPlugins(BasketStruct $basket, array $positions): void
    {
        $context = new PluginsByTechnicalNameRequest(
            $this->getLocale(),
            $this->getVersion(),
            array_column($positions, 'technicalName')
        );

        $plugins = $this->get(PluginStoreService::class)->getPlugins($context);

        foreach ($basket->getPositions() as $position) {
            $name = $this->getTechnicalNameOfOrderNumber($position->getOrderNumber(), $positions);

            if ($name === null) {
                continue;
            }

            $key = strtolower($name);
            $position->setPlugin($plugins[$key]);
        }
    }

    /**
     * @param array<array> $positions
     */
    private function getTechnicalNameOfOrderNumber(string $orderNumber, array $positions): ?string
    {
        foreach ($positions as $requestPosition) {
            if ($requestPosition['orderNumber'] !== $orderNumber) {
                continue;
            }

            return $requestPosition['technicalName'];
        }

        return null;
    }

    private function handleException(Exception $e): void
    {
        if (!($e instanceof StoreException)) {
            $this->View()->assign(['success' => false, 'message' => $e->getMessage()]);

            return;
        }

        $message = $this->getExceptionMessage($e);

        $this->View()->assign([
            'success' => false,
            'message' => $message,
            'authentication' => $e instanceof AuthenticationException,
        ]);
    }

    /**
     * Registers php shutdown function to catch fatal and parse errors which thrown in refreshPluginList
     */
    private function registerShutdown(): void
    {
        register_shutdown_function(function (): void {
            $lastError = error_get_last();
            if (!\is_array($lastError)) {
                return;
            }

            switch ($lastError['type']) {
                case E_ERROR:
                case E_PARSE:
                case E_CORE_ERROR:
                    ob_clean();
                    ob_flush();
                    http_response_code(200);
                    $message = 'Error<br><br>' . $lastError['message'] . '<br><br>File:' . str_replace('/', '/ ', $lastError['file']);
                    echo json_encode(['success' => false, 'error' => $message]);
            }
        });
    }

    /**
     * Gets an array of plugins that are in Safe Mode
     *
     * @param array<Plugin> $plugins
     *
     * @return array<Plugin>
     */
    private function getPluginsInSafeMode(array $plugins): array
    {
        $pluginsInSafeMode = [];
        foreach ($plugins as $plugin) {
            if ($plugin->isInSafeMode()) {
                $pluginsInSafeMode[] = $plugin;
            }
        }

        return $pluginsInSafeMode;
    }
}
