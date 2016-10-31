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

use Doctrine\ORM\AbstractQuery;
use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;
use Shopware\Models\Config\Element;
use Shopware\Models\Config\Form;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

/**
 * Shopware Performance Controller
 */
class Shopware_Controllers_Backend_Performance extends Shopware_Controllers_Backend_ExtJs
{
    const PHP_RECOMMENDED_VERSION = '7.0.0';
    const PHP_MINIMUM_VERSION     = '5.6.4';

    const PERFORMANCE_VALID       = 1;
    const PERFORMANCE_WARNING     = 2;
    const PERFORMANCE_INVALID     = 0;

    /**
     * Stores a list of all needed config data
     * @var array
     */
    protected $configData = array();

    protected function initAcl()
    {
    }

    /**
     * get productive mode
     */
    public function getProductiveModeAction()
    {
        $httpCache = $this->getPluginByName('HttpCache');

        $active = ($httpCache->getActive() && $httpCache->getInstalled() != null);

        $this->View()->assign([
            'success' => true,
            'productiveMode' => $active
        ]);
    }

    /**
     * set productive mode true or false
     * enable and disable caching by enable or disable plugin 'HttpCache'
     */
    public function toggleProductiveModeAction()
    {
        /** @var Plugin $httpCache */
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
     * activate httpCache-Plugin
     * @param Plugin $httpCache
     */
    private function activeHttpCache($httpCache)
    {
        /**@var $service InstallerService*/
        $service = $this->get('shopware.plugin_manager');

        if (!$httpCache->getInstalled()) {
            $service->installPlugin($httpCache);
        }
        if (!$httpCache->getActive()) {
            $service->activatePlugin($httpCache);
        }
    }

    /**
     * deactivate httpCache-Plugin
     * @param Plugin $httpCache
     */
    private function deactivateHttpCache($httpCache)
    {
        if (!$httpCache->getActive()) {
            return;
        }

        /**@var $service InstallerService*/
        $service = $this->get('shopware.plugin_manager');
        $service->deactivatePlugin($httpCache);
    }

    /**
     * Get plugin orm-model by name
     * @param $name
     * @return null|Plugin
     */
    private function getPluginByName($name)
    {
        $repo = $this->get('models')->getRepository('Shopware\Models\Plugin\Plugin');

        return $repo->findOneBy(['name' => $name]);
    }

    /**
     * Gets a list of id-name of all existing shops
     */
    public function getShopsAction()
    {
        $shops = Shopware()->Db()->fetchAll('SELECT id, name FROM s_core_shops');
        $this->View()->assign(array(
            'success' => true,
            'data' => $shops
        ));
    }

    /**
     *
     */
    public function getConfigAction()
    {
        $this->get('cache')->remove('Shopware_Config');
        $this->View()->assign(array(
            'success' => true,
            'data' => $this->prepareConfigData()
        ));
    }

    public function getListingSortingsAction()
    {
        /**@var $namespace Enlight_Components_Snippet_Namespace*/
        $namespace = $this->get('snippets')->getNamespace('frontend/listing/listing_actions');

        $coreSortings = array(
            array('id' => 1, 'name' => $namespace->get('ListingSortRelease')),
            array('id' => 2, 'name' => $namespace->get('ListingSortRating')),
            array('id' => 3, 'name' => $namespace->get('ListingSortPriceLowest')),
            array('id' => 4, 'name' => $namespace->get('ListingSortPriceHighest')),
            array('id' => 5, 'name' => $namespace->get('ListingSortName')),
        );

        $this->View()->assign(array(
            'success' => true,
            'data' => $coreSortings
        ));
    }

    /**
     * Gets a list of id-name of all active shops
     */
    public function getActiveShopsAction()
    {
        $shops = $this->get('models')->getRepository(
            'Shopware\Models\Shop\Shop'
        )->getActiveShops(AbstractQuery::HYDRATE_ARRAY);
        $this->View()->assign(array(
            'success' => true,
            'data' => array_map(function ($item) {
                return array('id' => $item['id'], 'name' => $item['name']);
            }, $shops)
        ));
    }

    /**
     * This action creates/updates the configuration
     */
    public function saveConfigAction()
    {
        $data = $this->Request()->getParams();

        // Save the config
        $data = $this->prepareDataForSaving($data);
        $this->saveConfigData($data);

        $this->get('cache')->remove('Shopware_Config');

        // Reload config, so that the actual config from the
        // db is returned
        $this->configData = $this->prepareConfigData();

        $this->View()->assign(array(
            'success' => true,
            'data' => $this->configData
        ));
    }

    /**
     * Iterates the given data array and persists all config variables
     * @param array $data
     */
    public function saveConfigData(array $data)
    {
        foreach ($data as $values) {
            foreach ($values as $configKey => $value) {
                $this->saveConfig($configKey, $value);
            }
        }
    }

    /**
     * Reads all config data and prepares it for our models
     * @return array
     */
    protected function prepareConfigData()
    {
        return [
            'check'     => $this->getPerformanceCheckData(),
            'httpCache' => $this->prepareHttpCacheConfig(),
            'topSeller' => $this->genericConfigLoader([
                'topSellerActive',
                'topSellerValidationTime',
                'chartinterval',
                'topSellerRefreshStrategy',
                'topSellerPseudoSales'
            ]),
            'seo'       => $this->prepareSeoConfig(),
            'search'    => $this->prepareSearchConfig(),
            'categories' => $this->genericConfigLoader([
                'moveBatchModeEnabled',
                'articlesperpage',
                'defaultListingSorting'
            ]),
            'filters' => $this->genericConfigLoader([
                'showSupplierInCategories',
                'showImmediateDeliveryFacet',
                'showShippingFreeFacet',
                'showPriceFacet',
                'showVoteAverageFacet',
                'displayFiltersInListings',
                'defaultListingSorting',
            ]),
            'various' => $this->genericConfigLoader([
                'disableShopwareStatistics',
                'LastArticles:show',
                'LastArticles:lastarticlestoshow',
                'disableArticleNavigation'
            ]),
            'customer' => $this->genericConfigLoader([
                'alsoBoughtShow',
                'similarViewedShow',
                'similarRefreshStrategy',
                'similarValidationTime',
                'similarActive'
            ])
        ];
    }

    /**
     * General helper method which triggers the prepare...ConfigForSaving methods
     *
     * @param $data
     * @return array
     */
    public function prepareDataForSaving($data)
    {
        $output = array();
        $output['httpCache']  = $this->prepareHttpCacheConfigForSaving($data['httpCache'][0]);
        $output['topSeller']  = $this->prepareForSavingDefault($data['topSeller'][0]);
        $output['seo']        = $this->prepareSeoConfigForSaving($data['seo'][0]);
        $output['search']     = $this->prepareForSavingDefault($data['search'][0]);
        $output['filters']    = $this->prepareForSavingDefault($data['filters'][0]);
        $output['categories'] = $this->prepareForSavingDefault($data['categories'][0]);
        $output['various']    = $this->prepareForSavingDefault($data['various'][0]);
        $output['customer']   = $this->prepareForSavingDefault($data['customer'][0]);

        return $output;
    }

    /**
     * Generic helper method which prepares a given array for saving
     * @param $data
     * @return Array
     */
    public function prepareForSavingDefault($data)
    {
        unset($data['id']);

        return $data;
    }

    /**
     * Prepare seo array for saving
     *
     * @param $data
     * @return Array
     */
    public function prepareSeoConfigForSaving($data)
    {
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
     * Prepare the http config array so that it can easily be saved
     *
     * @param $data
     * @return Array
     */
    public function prepareHttpCacheConfigForSaving($data)
    {
        $modelManager = $this->get('models');
        $repo = $modelManager->getRepository(
            'Shopware\Models\Plugin\Plugin'
        );

        /** @var \Shopware\Models\Plugin\Plugin $plugin */
        $plugin = $repo->findOneBy(array('name' => 'HttpCache'));
        $plugin->setActive($data['enabled']);

        $modelManager->flush($plugin);

        $lines = array();
        foreach ($data['cacheControllers'] as $entry) {
            $lines[] = $entry['key'] . ' ' . $entry['value'];
        }
        $data['cacheControllers'] = implode("\n", $lines);

        $lines = array();
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
     * Helper method to persist a given config value
     *
     * @param string $name
     * @param mixed $value
     */
    public function saveConfig($name, $value)
    {
        $modelManager = $this->get('models');
        $shopRepository    = $modelManager->getRepository(Shop::class);
        $elementRepository = $modelManager->getRepository(Element::class);
        $formRepository    = $modelManager->getRepository(Form::class);

        $shop = $shopRepository->find($shopRepository->getActiveDefault()->getId());

        if (strpos($name, ':') !== false) {
            list($formName, $name) = explode(':', $name, 2);
        }

        $findBy = array('name' => $name);
        if (isset($formName)) {
            $form = $formRepository->findOneBy(array('name' => $formName));
            $findBy['form'] = $form;
        }

        /** @var $element Shopware\Models\Config\Element */
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

        $values = array();
        // Do not save default value
        if ($value !== $element->getValue()) {
            $valueModel = new Shopware\Models\Config\Value();
            $valueModel->setElement($element);
            $valueModel->setShop($shop);
            $valueModel->setValue($value);
            $values[$shop->getId()] = $valueModel;
        }

        $element->setValues($values);
        $modelManager->flush($element);
    }

    /**
     * Read a given config by name
     *
     * @param $configName
     * @param  string      $defaultValue
     * @return null|string
     */
    public function readConfig($configName, $defaultValue = '')
    {
        // If we have a simple config item, we can return it by using Shopware()->Config()
        if (strpos($configName, ':') === false) {
            return Shopware()->Config()->get($configName);
        }

        // The colon separates formName and elementName
        list($scope, $config) = explode(':', $configName, 2);

        $elementRepository = $this->get('models')->getRepository('Shopware\Models\Config\Element');
        $formRepository = $this->get('models')->getRepository('Shopware\Models\Config\Form');

        $form = $formRepository->findOneBy(array('name' => $scope));

        if (!$form) {
            return $defaultValue;
        }

        /** @var \Shopware\Models\Config\Element $element */
        $element = $elementRepository->findOneBy(array('name' => $config, 'form' => $form));

        if (!$element) {
            return $defaultValue;
        }

        $values = $element->getValues();
        if (empty($values) || empty($values[0])) {
            return $element->getValue();
        }

        $firstValue = $values[0];

        return $firstValue->getValue();
    }

    /**
     * Helper function to check some performance configurations.
     */
    protected function getPerformanceCheckData()
    {
        $descriptionPHPVersion = '';
        if (version_compare(phpversion(), self::PHP_RECOMMENDED_VERSION, '>=')) {
            $validPHPVersion = self::PERFORMANCE_VALID;
        } elseif (version_compare(phpversion(), self::PHP_MINIMUM_VERSION, '>=')) {
            $validPHPVersion = self::PERFORMANCE_WARNING;
            $descriptionPHPVersion = Shopware()->Snippets()->getNamespace('backend/performance/main')
                ->get('cache/php_version/description_eol');
        } else {
            $validPHPVersion = self::PERFORMANCE_INVALID;
        }

        return array(
            array(
                'id' => 1,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/apc'),
                'value' => extension_loaded('apcu'),
                'valid' => extension_loaded('apcu') === true ? self::PERFORMANCE_VALID : self::PERFORMANCE_INVALID
            ),
            array(
                'id' => 3,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/zend'),
                'value' => extension_loaded('Zend OPcache'),
                'valid' => extension_loaded('Zend OPcache') === true ? self::PERFORMANCE_VALID : self::PERFORMANCE_INVALID
            ),
            array(
                'id' => 4,
                'name' => Shopware()->Snippets()->getNamespace('backend/performance/main')->get('cache/php_version'),
                'value' => phpversion(),
                'valid' => $validPHPVersion,
                'description' => $descriptionPHPVersion
            )
        );
    }

    /**
     * Generic helper method to build an array of config which needs to be loaded
     * @param $config
     * @return array
     */
    protected function genericConfigLoader($config)
    {
        $data = array();

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
        $formatted = trim(str_replace('T', ' ', Shopware()->Config()->routerlastupdate));
        $datetime = date_create_from_format('Y-m-d H:i:s', $formatted);

        if ($datetime) {
            $date = $datetime ->format('d.m.Y');
            $time = $datetime ->format('H:i:s');
        } else {
            $date = null;
            $time = null;
        }

        return array(
            'routercache'          => (int) Shopware()->Config()->routercache,
            'routerlastupdateDate' => $date,
            'routerlastupdateTime' => $time,
            'seoRefreshStrategy'   => Shopware()->Config()->seoRefreshStrategy
        );
    }

    /**
     * Special treatment for HTTPCache config needed
     *
     * @return array
     */
    protected function prepareHttpCacheConfig()
    {
        $controllers = Shopware()->Config()->cacheControllers;
        $cacheControllers = array();
        if (!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $cacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        $controllers = Shopware()->Config()->noCacheControllers;
        $noCacheControllers = array();
        if (!empty($controllers)) {
            $controllers = str_replace(array("\r\n", "\r"), "\n", $controllers);
            $controllers = explode("\n", trim($controllers));
            foreach ($controllers as $controller) {
                list($controller, $cacheTime) = explode(" ", $controller);
                $noCacheControllers[] = array('key' => $controller, 'value' => $cacheTime);
            }
        }

        $repo = $this->get('models')->getRepository(
            'Shopware\Models\Plugin\Plugin'
        );

        /** @var \Shopware\Models\Plugin\Plugin $plugin */
        $plugin = $repo->findOneBy(array('name' => 'HttpCache'));

        return array(
            'enabled'            => $plugin->getActive(),
            'cacheControllers'   => $cacheControllers,
            'noCacheControllers' => $noCacheControllers,
            'HttpCache:proxyPrune' => $this->readConfig('HttpCache:proxyPrune'),
            'HttpCache:admin'    => $this->readConfig('HttpCache:admin'),
            'HttpCache:proxy'    => $this->readConfig('HttpCache:proxy')
        );
    }

    /**
     *
     * Helpers to fix categories
     *
     */

    /**
     * Fixes categorie tree
     */
    public function fixCategoriesAction()
    {
        $offset = $this->Request()->getParam('offset', 0);
        $limit  = $this->Request()->getParam('limit', null);

        $component = $this->get('CategoryDenormalization');

        if ($offset == 0) {
            $component->rebuildCategoryPath();
            $component->removeAllAssignments();
        }

        $count = $component->rebuildAllAssignments($limit, $offset);

        $this->View()->assign(array(
            'success' => true,
            'total'   => $count,
        ));
    }

    public function prepareTreeAction()
    {
        $component = $this->get('CategoryDenormalization');

        $component->removeOrphanedAssignments();

        $count = $component->rebuildAllAssignmentsCount();

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }

    /**
     * calculates the number of all urls to create a cache entry for
     */
    public function getHttpURLsAction()
    {
        $shopId = (int)$this->Request()->getParam('shopId', 1);

        /**@var $cacheWarmer \Shopware\Components\HttpCache\CacheWarmer*/
        $cacheWarmer = $this->get('http_cache_warmer');

        $this->View()->assign(
            array(
                'success' => true,
                'data' => array(
                    'counts' => array(
                        'category' => $cacheWarmer->getSEOURLByViewPortCount($cacheWarmer::CATEGORY_PATH, $shopId),
                        'article' => $cacheWarmer->getSEOURLByViewPortCount($cacheWarmer::ARTICLE_PATH, $shopId),
                        'blog' => $cacheWarmer->getSEOURLByViewPortCount($cacheWarmer::BlOG_PATH, $shopId),
                        'static' => $cacheWarmer->getSEOURLByViewPortCount($cacheWarmer::CUSTOM_PATH, $shopId),
                        'supplier' => $cacheWarmer->getSEOURLByViewPortCount($cacheWarmer::SUPPLIER_PATH, $shopId)
                    )
                )
            )
        );
    }

    /**
     * calculates and call every url depending on the shopId and the resource
     */
    public function warmUpCacheAction()
    {
        $shopId = (int)$this->Request()->getParam('shopId', 1);
        $limit = $this->Request()->get('limit');
        $offset = $this->Request()->get('offset');
        $resource = $this->Request()->get('resource');

        /** @var Shopware\Components\HttpCache\CacheWarmer $cacheWarmer */
        $cacheWarmer = $this->get('http_cache_warmer');

        $viewPorts = [];
        switch ($resource) {
            case 'article':
                $viewPorts[] = $cacheWarmer::ARTICLE_PATH;
                break;
            case 'category':
                $viewPorts[] = $cacheWarmer::CATEGORY_PATH;
                break;
            case 'blog':
                $viewPorts[] = $cacheWarmer::BlOG_PATH;
                break;
            case 'static':
                $viewPorts[] = $cacheWarmer::CUSTOM_PATH;
                $viewPorts[] = $cacheWarmer::EMOTION_LANDING_PAGE_PATH;
                break;
            case 'supplier':
                $viewPorts[] = $cacheWarmer::SUPPLIER_PATH;
                break;
            default:
                $this->View()->assign(array('success' => false));
                return;
        }

        $urls = $cacheWarmer->getSEOUrlByViewPort($viewPorts, $shopId, $limit, $offset);
        $cacheWarmer->callUrls($urls, $shopId);

        $this->View()->assign(
            array(
                'success' => true,
                'data' => array('count' => count($urls))
            )
        );
    }

    /**
     * Converts the fuzzysearchlastupdate to a DateTime object
     * @return array
     */
    private function prepareSearchConfig()
    {
        $data = $this->genericConfigLoader(['searchRefreshStrategy', 'cachesearch', 'traceSearch', 'fuzzysearchlastupdate']);
        $data['fuzzysearchlastupdate'] = new DateTime($data['fuzzysearchlastupdate']);
        return $data;
    }
}
