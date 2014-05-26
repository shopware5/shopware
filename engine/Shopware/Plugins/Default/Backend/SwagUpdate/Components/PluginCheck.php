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

namespace ShopwarePlugins\SwagUpdate\Components;

use Shopware\Components\DependencyInjection\Container;

/**
 * @category  Shopware
 * @package   ShopwarePlugins\SwagUpdate\Components;
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
Class PluginCheck
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Internal helper function to get the passed shopware version as a numeric value with four positions.
     *
     * @param version
     * @return string
     */
    private function getNumericShopwareVersion($version)
    {
        $paths = explode('.', $version);
        $paths = array_map('intval', $paths);
        if (count($paths) === 3) {
            $paths[] = 0;
        }

        return (int) implode('', $paths);
    }

    /**
     * Check if the currently installed plugin where marked to be compatible with the selected sw version
     *
     * @param $version
     * @return array
     */
    public function checkInstalledPluginsAvailableForNewVersion($version)
    {
        $version = $this->getNumericShopwareVersion($version);

        $results = array();
        $communityStore = $this->getCommunityStore();
        try {
            $plugins = $this->queryInstalledPluginsAvailableForNewVersion($version);
            if (empty($plugins)) {
                return array();
            }

            // Build array with plugin info and the plugin's name as key
            $pluginInfos = array();
            $response = $communityStore->getPluginInfos(array_keys($plugins));
            foreach ($response as $productModel) {
                foreach ($productModel->getPluginNames() as $name) {
                    $pluginInfos[$name] = $productModel;
                }
            }

            foreach ($plugins as $name => $available) {
                $inStore = false;
                $link    = '';
                // Check if a links is available
                if (isset($pluginInfos[$name])) {
                    $inStore = true;
                    $productModel = $pluginInfos[$name];
                    $link = $productModel->getStoreUrl();
                }

                if ($inStore) {
                    if ($available) {
                        $description = $this->getSnippetNamespace()->get('controller/plugin_compatible', 'The author of the plugin marked the plugin as compatible.');
                    } else {
                        $description = $this->getSnippetNamespace()->get('controller/plugin_not_compatible', 'The author of the plugin did not mark the plugin as compatible with the shopware version');
                    }
                } else {
                    $description = $this->getSnippetNamespace()->get('controller/plugin_not_in_store', 'The plugin is not available in the store.');
                }

                $results[] = array(
                    'inStore'    => $inStore,
                    'name'       => $name,
                    'message'    => $description,
                    'link'       => $link,
                    'success'    => $available,
                    'id'         => sprintf('plugin_incompatible-%s', $name),
                    'errorLevel' => ($available) ? Validation::REQUIREMENT_VALID : Validation::REQUIREMENT_WARNING
                );
            }
        } catch (\Exception $e) {
            $results[] = array(
                'name'       => 'Error',
                'message'    => 'Could not query plugins which are available for your shopware version',
                'details'    => $e->getCode() . ': ' . $e->getMessage(),
                'errorLevel' => Validation::REQUIREMENT_WARNING,
                'id'         => 'plugin_available_error'
            );
        }

        usort($results, function ($a, $b) {
            if ($a['inStore'] < $b['inStore']) {
                return -1;
            } elseif ($a['inStore'] > $b['inStore']) {
                return 1;
            }

            return $a['success'] > $b['success'];
        });

        return $results;
    }

    /**
     * Queries the communityStore for plugins which have explicitly
     * been marked as compatible with the checked SW-Version
     *
     * @param $version
     * @return array
     */
    private function queryInstalledPluginsAvailableForNewVersion($version)
    {
        $plugins = $this->getUserInstalledPlugins();

        if (empty($plugins)) {
            return array();
        }

        $communityStore = $this->getCommunityStore();
        $plugins = $communityStore->getPluginsAvailableFor($plugins, $version);

        return $plugins;
    }

    /**
     * @return \ShopwarePlugins\SwagUpdate\Components\CommunityStore
     */
    public function getCommunityStore()
    {
        $productService = $this->getProductService();
        $communityStore = new \ShopwarePlugins\SwagUpdate\Components\CommunityStore($productService);

        return $communityStore;
    }

    /**
     * Returns a list of all plugins installed by the user
     *
     * @return array
     */
    private function getUserInstalledPlugins()
    {
        $builder = $this->container->get('models')->createQueryBuilder();
        $builder->select(array('plugin.name'))
                ->from('Shopware\Models\Plugin\Plugin', 'plugin', 'plugin.name')
                ->where('plugin.name != :pluginManager')
                ->andWhere('plugin.source != :source')
                ->andWhere('plugin.name != :storeApi')
                ->setParameter('pluginManager', 'PluginManager')
                ->setParameter('storeApi', 'StoreApi')
                ->setParameter('source', 'Default');

        $plugins = array_keys($builder->getQuery()->getArrayResult());

        return $plugins;
    }


    /**
     * Helper which returns an snippet-instance
     *
     * @return \Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        return $this->container->get('snippets')->getNamespace('backend/swag_update/main');
    }

    /**
     * Get and setup ProductService Component
     *
     * This method also does the setup like autoloading.
     *
     * @return \Shopware_StoreApi_Core_Service_Product
     */
    private function getProductService()
    {
        /** @var $communityStore CommunityStore */
        $communityStore = $this->container->get('CommunityStore');

        $storeApi = $communityStore->getApi();

        $productService = $storeApi->getProductService();

        return $productService;
    }
}
