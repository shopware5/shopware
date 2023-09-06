<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Bundle\SitemapBundle\Service\ConfigHandler;
use Shopware\Bundle\SitemapBundle\Service\SitemapExporter;
use Shopware\Components\CacheManager;
use Shopware\Components\HttpCache\CacheWarmer;
use Shopware\Components\HttpCache\UrlProviderFactoryInterface;
use Shopware\Components\Model\Exception\ModelNotFoundException;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Routing\Context;
use Shopware\Models\Article\Article as ProductModel;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Category\Category;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Emotion\Emotion;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Shopware\Models\Site\Site;

class Shopware_Controllers_Backend_Performance extends Shopware_Controllers_Backend_ExtJs
{
    public const PHP_RECOMMENDED_VERSION = '8.0.0';
    public const PHP_MINIMUM_VERSION = '7.4.0';

    public const PERFORMANCE_VALID = 1;
    public const PERFORMANCE_WARNING = 2;
    public const PERFORMANCE_INVALID = 0;

    /**
     * Stores a list of all needed config data
     *
     * @var array
     */
    protected $configData = [];

    /**
     * get productive mode
     */
    public function getProductiveModeAction()
    {
        $httpCache = $this->getPluginByName('HttpCache');

        $active = $httpCache->getActive() && $httpCache->getInstalled() != null;

        $this->View()->assign([
            'success' => true,
            'productiveMode' => $active,
        ]);
    }

    /**
     * Set productive mode true or false
     * Enable and disable caching by enable or disable plugin 'HttpCache'
     */
    public function toggleProductiveModeAction()
    {
        /** @var Plugin|null $httpCache */
        $httpCache = $this->getPluginByName('HttpCache');

        if (!$httpCache) {
            $this->View()->assign(['success' => false, 'state' => 'not_found']);

            return;
        }

        switch ($httpCache->getActive()) {
            case true:
                $this->deactivateHttpCache($httpCache);
                break;
            case false:
                $this->activeHttpCache($httpCache);
                break;
        }

        $this->View()->assign(['success' => true]);
    }

    /**
     * Gets a list of id-name of all existing shops
     */
    public function getShopsAction()
    {
        $shops = Shopware()->Db()->fetchAll('SELECT id, name FROM s_core_shops');
        $this->View()->assign([
            'success' => true,
            'data' => $shops,
        ]);
    }

    public function getConfigAction()
    {
        Shopware()->Container()->get(Zend_Cache_Core::class)->remove(CacheManager::ITEM_TAG_CONFIG);
        $this->View()->assign([
            'success' => true,
            'data' => $this->prepareConfigData(),
        ]);
    }

    /**
     * Gets a list of id-name of all active shops
     */
    public function getActiveShopsAction()
    {
        $shopRepo = $this->container->get(ModelManager::class)->getRepository(Shop::class);
        $shops = $shopRepo->getActiveShops(AbstractQuery::HYDRATE_ARRAY);
        $this->View()->assign([
            'success' => true,
            'data' => array_map(function ($item) {
                return ['id' => $item['id'], 'name' => $item['name']];
            }, $shops),
        ]);
    }

    /**
     * This action creates/updates the configuration
     */
    public function saveConfigAction()
    {
        $data = $this->Request()->getParams();

        // Save the config
        $sitemapData = $data['sitemap'][0];
        $data = $this->prepareDataForSaving($data);
        $this->saveConfigData($data);
        $this->saveSitemapData($sitemapData);

        Shopware()->Container()->get(Zend_Cache_Core::class)->remove(CacheManager::ITEM_TAG_CONFIG);

        // Reload config, so that the actual config from the
        // db is returned
        $this->configData = $this->prepareConfigData();

        $this->View()->assign([
            'success' => true,
            'data' => $this->configData,
        ]);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Iterates the given data array and persists all config variables
     */
    public function saveConfigData(array $data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        foreach ($data as $values) {
            foreach ($values as $configKey => $value) {
                $this->saveConfig($configKey, $value);
            }
        }
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * General helper method which triggers the prepare...ConfigForSaving methods
     *
     * @param array $data
     *
     * @return array
     */
    public function prepareDataForSaving($data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $output = [];
        $output['httpCache'] = $this->prepareHttpCacheConfigForSaving($data['httpCache'][0]);
        $output['topSeller'] = $this->prepareForSavingDefault($data['topSeller'][0]);
        $output['seo'] = $this->prepareSeoConfigForSaving($data['seo'][0]);
        $output['search'] = $this->prepareForSavingDefault($data['search'][0]);
        $output['filters'] = $this->prepareForSavingDefault($data['filters'][0]);
        $output['categories'] = $this->prepareForSavingDefault($data['categories'][0]);
        $output['various'] = $this->prepareForSavingDefault($data['various'][0]);
        $output['customer'] = $this->prepareForSavingDefault($data['customer'][0]);
        $output['sitemap'] = $this->prepareSitemapConfigForSaving($data['sitemap'][0]);

        return $output;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Generic helper method which prepares a given array for saving
     *
     * @return array
     */
    public function prepareForSavingDefault(array $data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        unset($data['id']);

        return $data;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Prepare seo array for saving
     *
     * @return array
     */
    public function prepareSeoConfigForSaving(array $data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        unset($data['id']);

        $date = date_create($data['routerlastupdateDate'])->format('Y-m-d');

        $time = $data['routerlastupdateTime'];

        $datetime = $date . 'T' . $time;

        $data['routerlastupdate'] = $datetime;

        unset($data['routerlastupdateDate']);
        unset($data['routerlastupdateTime']);

        return $data;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Prepare the http config array so that it can easily be saved
     *
     * @return array
     */
    public function prepareHttpCacheConfigForSaving(array $data)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $modelManager = $this->container->get(ModelManager::class);
        $repo = $modelManager->getRepository(Plugin::class);

        /** @var Plugin $plugin */
        $plugin = $repo->findOneBy(['name' => 'HttpCache']);
        $plugin->setActive($data['enabled']);

        $modelManager->flush($plugin);

        $lines = [];
        foreach ($data['cacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['cacheControllers'] = implode("\n", $lines);

        $lines = [];
        foreach ($data['noCacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['noCacheControllers'] = implode("\n", $lines);

        $data['HttpCache:proxy'] = implode(
            ',',
            array_map(
                function ($url) {
                    $url = trim($url);
                    if (empty($url) || strpos($url, '://') !== false) {
                        return $url;
                    }

                    return 'http://' . $url;
                },
                explode(',', $data['HttpCache:proxy'])
            )
        );

        unset($data['id']);

        return $data;
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Helper method to persist a given config value
     *
     * @param string $name
     */
    public function saveConfig($name, $value)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $modelManager = $this->container->get(ModelManager::class);
        $shopRepository = $modelManager->getRepository(Shop::class);
        $elementRepository = $modelManager->getRepository(Element::class);
        $formRepository = $modelManager->getRepository(Form::class);

        /** @var Shop $shop */
        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = ['name' => $name];
        if (isset($formName)) {
            $form = $formRepository->findOneBy(['name' => $formName]);
            $findBy['form'] = $form;
        }

        $element = $elementRepository->findOneBy($findBy);

        // If the element is empty, the given setting does not exists. This might be the case for some plugins
        // Skip those values
        if (empty($element)) {
            return;
        }

        $removedValues = [];
        foreach ($element->getValues() as $valueModel) {
            $removedValues[] = $valueModel;
            $modelManager->remove($valueModel);
        }
        $modelManager->flush($removedValues);

        $values = [];
        // Do not save default value
        if ($value !== $element->getValue()) {
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues(new ArrayCollection($values));
        $modelManager->flush($element);
    }

    /**
     * @deprecated in 5.6, will be private in 5.8
     *
     * Read a given config by name
     *
     * @param string $configName
     * @param string $defaultValue
     *
     * @return array<array-key, mixed>|bool|float|int|string|null
     */
    public function readConfig($configName, $defaultValue = '')
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be private with 5.8.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        // If we have a simple config item, we can return it by using Shopware()->Config()
        if (strpos($configName, ':') === false) {
            return Shopware()->Config()->get($configName);
        }

        // The colon separates formName and elementName
        list($scope, $config) = explode(':', $configName, 2);

        $elementRepository = $this->container->get(ModelManager::class)->getRepository(Element::class);
        $formRepository = $this->container->get(ModelManager::class)->getRepository(Form::class);

        $form = $formRepository->findOneBy(['name' => $scope]);

        if (!$form) {
            return $defaultValue;
        }

        $element = $elementRepository->findOneBy(['name' => $config, 'form' => $form]);

        if (!$element) {
            return $defaultValue;
        }

        $values = $element->getValues();
        if ($values->count() === 0 || empty($values[0])) {
            return $element->getValue();
        }

        $firstValue = $values[0];

        return $firstValue->getValue();
    }

    /**
     * Helpers to fix categories
     */

    /**
     * Fixes category tree
     */
    public function fixCategoriesAction()
    {
        $offset = (int) $this->Request()->getParam('offset', 0);
        $limit = (int) $this->Request()->getParam('limit');

        $component = Shopware()->Container()->get('categorydenormalization');

        if ($offset === 0) {
            $component->rebuildCategoryPath();
            $component->removeAllAssignments();
        }

        $count = $component->rebuildAllAssignments($limit, $offset);

        $this->View()->assign([
            'success' => true,
            'total' => $count,
        ]);
    }

    public function prepareTreeAction()
    {
        $component = Shopware()->Container()->get('categorydenormalization');

        $component->removeOrphanedAssignments();

        $count = $component->rebuildAllAssignmentsCount();

        $this->View()->assign([
            'success' => true,
            'data' => ['count' => $count],
        ]);
    }

    /**
     * Calculates the number of all urls to create a cache entry for
     */
    public function getHttpURLsAction()
    {
        $shopId = (int) $this->Request()->getParam('shopId', 1);

        $shopModel = $this->container->get(ModelManager::class)->getRepository(Shop::class)->getById($shopId);
        if ($shopModel === null) {
            throw new ModelNotFoundException(Shop::class, $shopId);
        }

        $context = Context::createFromShop(
            $shopModel,
            $this->container->get(Shopware_Components_Config::class)
        );

        $providers = $this->get('shopware_cache_warmer.url_provider_factory')->getAllProviders();

        // Count for each provider, if enabled
        $config = json_decode($this->Request()->getParam('config', '{}'), true);
        $counts = [];
        foreach ($providers as $provider) {
            if ($config[$provider->getName()]) {
                $counts[$provider->getName()] = (int) $provider->getCount($context);
            } else {
                $counts[$provider->getName()] = 0;
            }
        }
        $counts['all'] = array_sum($counts);

        $counts = $this->get('events')->filter(
            'Shopware_Controllers_Performance_filterCounts',
            $counts
        );

        $this->View()->assign([
            'success' => true,
            'data' => [
                'counts' => $counts,
            ],
        ]);
    }

    /**
     * Calculates and calls every url depending on the shopId and the resource
     */
    public function warmUpCacheAction()
    {
        /** @var UrlProviderFactoryInterface $urlProviderFactory */
        $urlProviderFactory = $this->get('shopware_cache_warmer.url_provider_factory');

        /** @var CacheWarmer $cacheWarmer */
        $cacheWarmer = $this->get('http_cache_warmer');

        $shopId = (int) $this->Request()->getParam('shopId', 1);
        $shopModel = $this->container->get(ModelManager::class)->getRepository(Shop::class)->getById($shopId);
        if ($shopModel === null) {
            throw new ModelNotFoundException(Shop::class, $shopId);
        }

        $context = Context::createFromShop($shopModel, Shopware()->Config());

        $limit = (int) $this->Request()->get('limit');
        $offset = (int) $this->Request()->get('offset');
        $concurrentRequests = (int) $this->Request()->getParam('concurrent', 1);

        $resource = $this->Request()->get('resource');
        $provider = $urlProviderFactory->getProvider($resource);

        $urls = $provider->getUrls($context, $limit, $offset);

        $view = $this->View();

        $this->get('events')->addListener('Shopware_Components_CacheWarmer_ErrorOccured', function () use ($view) {
            $view->assign('requestFailed', true);
        });

        $cacheWarmer->warmUpUrls($urls, $context, $concurrentRequests);

        $this->View()->assign([
            'success' => true,
            'data' => ['count' => \count($urls)],
        ]);
    }

    /**
     * Regenerate sitemap cache
     */
    public function buildSitemapCacheAction()
    {
        $shops = $this->getModelManager()->getRepository(Shop::class)->getActiveShopsFixed();

        foreach ($shops as $shop) {
            $this->container->get(SitemapExporter::class)->generate($shop);
        }

        $this->View()->assign('success', true);
    }

    protected function initAcl()
    {
    }

    /**
     * Reads all config data and prepares it for our models
     *
     * @return array
     */
    protected function prepareConfigData()
    {
        return [
            'check' => $this->getPerformanceCheckData(),
            'httpCache' => $this->prepareHttpCacheConfig(),
            'topSeller' => $this->genericConfigLoader([
                'topSellerActive',
                'topSellerValidationTime',
                'chartinterval',
                'topSellerRefreshStrategy',
                'topSellerPseudoSales',
            ]),
            'seo' => $this->prepareSeoConfig(),
            'search' => $this->prepareSearchConfig(),
            'categories' => $this->genericConfigLoader([
                'moveBatchModeEnabled',
                'articlesperpage',
            ]),
            'filters' => $this->genericConfigLoader([
                'listingMode',
            ]),
            'various' => $this->genericConfigLoader([
                'disableShopwareStatistics',
                'LastArticles:lastarticles_show',
                'LastArticles:lastarticlestoshow',
                'disableArticleNavigation',
                'http2Push',
                'minifyHtml',
            ]),
            'customer' => $this->genericConfigLoader([
                'alsoBoughtShow',
                'similarViewedShow',
                'similarRefreshStrategy',
                'similarValidationTime',
                'similarActive',
            ]),
            'sitemap' => $this->prepareSitemapConfig(),
        ];
    }

    /**
     * Helper function to check some performance configurations.
     *
     * @return array
     */
    protected function getPerformanceCheckData()
    {
        $descriptionPHPVersion = '';
        if (version_compare(PHP_VERSION, self::PHP_RECOMMENDED_VERSION, '>=')) {
            $validPHPVersion = self::PERFORMANCE_VALID;
        } elseif (version_compare(PHP_VERSION, self::PHP_MINIMUM_VERSION, '>=')) {
            $validPHPVersion = self::PERFORMANCE_WARNING;
            $descriptionPHPVersion = Shopware()->Snippets()->getNamespace('backend/performance/main')
                ->get('cache/php_version/description_eol');
        } else {
            $validPHPVersion = self::PERFORMANCE_INVALID;
        }

        return [
            [
                'id' => 1,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/apc'),
                'value' => \extension_loaded('apcu'),
                'valid' => \extension_loaded('apcu') === true && \ini_get('apc.enabled') ? self::PERFORMANCE_VALID : self::PERFORMANCE_INVALID,
            ],
            [
                'id' => 3,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/zend'),
                'value' => \extension_loaded('Zend OPcache'),
                'valid' => \extension_loaded('Zend OPcache') === true && \ini_get('opcache.enable') ? self::PERFORMANCE_VALID : self::PERFORMANCE_INVALID,
            ],
            [
                'id' => 4,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/php_version'),
                'value' => PHP_VERSION,
                'valid' => $validPHPVersion,
                'description' => $descriptionPHPVersion,
            ],
        ];
    }

    /**
     * Generic helper method to build an array of config which needs to be loaded
     *
     * @return array
     */
    protected function genericConfigLoader(array $config)
    {
        $data = [];

        foreach ($config as $configName) {
            $data[$configName] = $this->readConfig($configName);
        }

        return $data;
    }

    /**
     * Special treatment for SEO config needed
     *
     * @return array
     */
    protected function prepareSeoConfig()
    {
        $formatted = trim(str_replace('T', ' ', Shopware()->Config()->get('routerlastupdate')));
        $datetime = date_create_from_format('Y-m-d H:i:s', $formatted);

        if ($datetime) {
            $date = $datetime->format('d.m.Y');
            $time = $datetime->format('H:i:s');
        } else {
            $date = null;
            $time = null;
        }

        return [
            'routercache' => (int) Shopware()->Config()->get('routercache'),
            'routerlastupdateDate' => $date,
            'routerlastupdateTime' => $time,
            'seoRefreshStrategy' => Shopware()->Config()->get('seoRefreshStrategy'),
        ];
    }

    /**
     * Special treatment for HTTPCache config needed
     *
     * @return array
     */
    protected function prepareHttpCacheConfig()
    {
        $controllers = $this->get('config')->get('cacheControllers');
        $cacheControllers = [];
        if (!empty($controllers)) {
            $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(' ', $controller);
                $cacheControllers[] = ['key' => $controller, 'value' => $cacheTime];
            }
        }

        $controllers = $this->get('config')->get('noCacheControllers');
        $noCacheControllers = [];
        if (!empty($controllers)) {
            $controllers = str_replace(["\r\n", "\r"], "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(' ', $controller);
                $noCacheControllers[] = ['key' => $controller, 'value' => $cacheTime];
            }
        }

        $repo = $this->get(ModelManager::class)->getRepository(Plugin::class);

        /** @var Plugin $plugin */
        $plugin = $repo->findOneBy(['name' => 'HttpCache']);

        return [
            'enabled' => $plugin->getActive(),
            'cacheControllers' => $cacheControllers,
            'noCacheControllers' => $noCacheControllers,
            'HttpCache:proxyPrune' => $this->readConfig('HttpCache:proxyPrune'),
            'HttpCache:admin' => $this->readConfig('HttpCache:admin'),
            'HttpCache:proxy' => $this->readConfig('HttpCache:proxy'),
        ];
    }

    private function activeHttpCache(Plugin $httpCache): void
    {
        /** @var InstallerService $service */
        $service = $this->get('shopware.plugin_manager');

        if (!$httpCache->getInstalled()) {
            $service->installPlugin($httpCache);
        }

        if (!$httpCache->getActive()) {
            $service->activatePlugin($httpCache);
        }
    }

    private function deactivateHttpCache(Plugin $httpCache): void
    {
        if (!$httpCache->getActive()) {
            return;
        }

        /** @var InstallerService $service */
        $service = $this->get('shopware.plugin_manager');
        $service->deactivatePlugin($httpCache);
    }

    private function getPluginByName(string $name): ?Plugin
    {
        /** @var Plugin|null $return */
        $return = $this->get(ModelManager::class)
            ->getRepository(Plugin::class)
            ->findOneBy(['name' => $name]);

        return $return;
    }

    /**
     * Converts the fuzzysearchlastupdate to a DateTime object
     */
    private function prepareSearchConfig(): array
    {
        $data = $this->genericConfigLoader(['searchRefreshStrategy', 'cachesearch', 'traceSearch', 'fuzzysearchlastupdate']);
        $data['fuzzysearchlastupdate'] = new DateTime($data['fuzzysearchlastupdate']);

        return $data;
    }

    private function prepareSitemapConfig(): array
    {
        $sitemapConfig = $this->genericConfigLoader([
            'sitemapRefreshStrategy',
            'sitemapRefreshTime',
            'sitemapLastRefresh',
        ]);

        $sitemapConfig['customUrls'] = $this->get(ConfigHandler::class)->get(Confighandler::CUSTOM_URLS_KEY);
        $sitemapConfig['excludedUrls'] = $this->get(ConfigHandler::class)->get(Confighandler::EXCLUDED_URLS_KEY);

        $sitemapConfig['excludedUrls'] = array_map(function ($excludeUrl) {
            $excludeUrl['resource'] = $this->convertModelToResource($excludeUrl['resource']);

            return $excludeUrl;
        }, $sitemapConfig['excludedUrls']);

        return $sitemapConfig;
    }

    private function prepareSitemapConfigForSaving(array $sitemapData): array
    {
        unset($sitemapData['id'], $sitemapData['excludedUrls'], $sitemapData['customUrls']);

        return $sitemapData;
    }

    private function saveSitemapData(array $sitemapData): void
    {
        $sitemapData['excludedUrls'] = array_map(function ($excludeUrl) {
            $excludeUrl['resource'] = $this->convertResourceToModel($excludeUrl['resource']);

            return $excludeUrl;
        }, $sitemapData['excludedUrls']);

        $this->get(ConfigHandler::class)->save($sitemapData);
    }

    private function convertModelToResource(string $model): string
    {
        return array_flip($this->getResourceMapping())[$model];
    }

    private function convertResourceToModel(string $resource): string
    {
        return $this->getResourceMapping()[$resource];
    }

    private function getResourceMapping(): array
    {
        return [
            'product' => ProductModel::class,
            'blog' => Blog::class,
            'manufacturer' => Supplier::class,
            'category' => Category::class,
            'landing_page' => Emotion::class,
            'static' => Site::class,
        ];
    }
}
