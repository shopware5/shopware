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

use Doctrine\ORM\AbstractQuery;

/**
 * Shopware Performance Controller
 */
class Shopware_Controllers_Backend_Performance extends Shopware_Controllers_Backend_ExtJs
{
    /**
     * Stores a list of all needed config data
     * @var array
     */
    protected $configData = array();

    protected function initAcl()
    {
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
     * Gets a list of id-name of all active shops
     */
    public function getActiveShopsAction()
    {
        $shops = Shopware()->Models()->getRepository(
            'Shopware\Models\Shop\Shop'
        )->getActiveShops(AbstractQuery::HYDRATE_ARRAY);
        $this->View()->assign(array(
            'success' => true,
            'data' => array_map(function($item) {return array('id' => $item['id'], 'name' => $item['name']);}, $shops)
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

        Shopware()->Cache()->remove('Shopware_Config');

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
     * @param $data
     */
    public function saveConfigData($data)
    {
        foreach ($data as $values) {
            foreach ($values as $configKey => $value) {
                $this->saveConfig($configKey, $value);
            }
        }
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
        $repo = Shopware()->Models()->getRepository(
            'Shopware\Models\Plugin\Plugin'
        );

        /** @var \Shopware\Models\Plugin\Plugin $plugin */
        $plugin = $repo->findOneBy(array('name' => 'HttpCache'));
        $plugin->setActive($data['enabled']);

        Shopware()->Models()->flush($plugin);

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

        unset($data['id']);

        return $data;
    }

    /**
     * Helper method to persist a given config value
     */
    public function saveConfig($name, $value)
    {
        $shopRepository    = Shopware()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository    = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

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

        foreach ($element->getValues() as $valueModel) {
            Shopware()->Models()->remove($valueModel);
        }

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
        Shopware()->Models()->flush($element);
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

        $elementRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Element');
        $formRepository = Shopware()->Models()->getRepository('Shopware\Models\Config\Form');

        $form = $formRepository->findOneBy(array('name' => $scope));

        if (!$form) {
            return $defaultValue;
        }

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
        return array(
            array(
                'id' => 1,
                'name' => 'APCu aktiviert',
                'value' => extension_loaded('apcu'),
                'valid' => extension_loaded('apcu') === true
            ),
            array(
                'id' => 3,
                'name' => 'Zend OPcache aktiviert',
                'value' => extension_loaded('Zend OPcache'),
                'valid' => extension_loaded('Zend OPcache') === true
            ),
            array(
                'id' => 4,
                'name' => 'PHP Version',
                'value' => phpversion(),
                'valid' => version_compare(phpversion(), '5.4.0', '>=')
            )
        );

    }

    /**
     * Reads all config data and prepares it for our models
     * @return array
     */
    protected function prepareConfigData()
    {
        return array(
            'check'     => $this->getPerformanceCheckData(),
            'httpCache' => $this->prepareHttpCacheConfig(),
            'topSeller' => $this->genericConfigLoader(
                array(
                    'topSellerActive',
                    'topSellerValidationTime',
                    'chartinterval',
                    'topSellerRefreshStrategy',
                    'topSellerPseudoSales'
                )
            ),
            'seo'       => $this->prepareSeoConfig(),
            'search'    => $this->genericConfigLoader(array('searchRefreshStrategy', 'cachesearch', 'traceSearch', 'fuzzysearchlastupdate')),
            'categories' => $this->genericConfigLoader(
                array(
                    'articlesperpage',
                    'orderbydefault',
                    'showSupplierInCategories',
                    'moveBatchModeEnabled'
                )
            ),
            'filters' => $this->genericConfigLoader(array(
                'propertySorting',
                'displayFiltersInListings',
                'displayFilterArticleCount',
                'displayFiltersOnDetailPage'
            )),
            'various' => $this->genericConfigLoader(
                array(
                    'disableShopwareStatistics',
                    'TagCloud:show',
                    'LastArticles:show',
                    'LastArticles:lastarticlestoshow',
                    'disableArticleNavigation'
                )
            ),
            'customer' => $this->genericConfigLoader(
                array('alsoBoughtShow', 'similarViewedShow', 'similarRefreshStrategy', 'similarValidationTime', 'similarActive')
            ),
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

        $repo = Shopware()->Models()->getRepository(
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
     */
    public function getConfigAction()
    {
        Shopware()->Cache()->remove('Shopware_Config');
        $this->View()->assign(array(
            'success' => true,
            'data' => $this->prepareConfigData()
        ));
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

        $component = Shopware()->CategoryDenormalization();

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
        $component = Shopware()->CategoryDenormalization();

        $component->removeOrphanedAssignments();

        $count = $component->rebuildAllAssignmentsCount();

        $this->View()->assign(array(
            'success' => true,
            'data' => array('count' => $count)
        ));
    }
}
