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

namespace Shopware\Recovery\Update;

class PluginCheck
{
    /**
     * @var StoreApi
     */
    private $storeApi;

    /**
     * @var \PDO
     */
    private $conn;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @param string $shopwareVersion
     */
    public function __construct(StoreApi $storeApi, \PDO $conn, $shopwareVersion)
    {
        $this->storeApi = $storeApi;
        $this->conn = $conn;
        $this->shopwareVersion = $shopwareVersion;
    }

    public function checkPlugins()
    {
        $localPluginNames = $this->getCustomPluginNames();

        $localPluginNames = [
            'Shizzle',
            'SwagUpdateCheck',
            'SwagAboCommerce',
            'SwagFooBar',
        ];

        $version = $this->getNumericShopwareVersion($this->shopwareVersion);

        $result1 = $this->storeApi->getProductsByNamesAndVersion($localPluginNames, $version);
        $compatiblePlugins = $this->getCompatiblePlugins($result1, $localPluginNames);

        $result2 = $this->storeApi->getProductsByNames($localPluginNames);
        $inCompatiblePlugins = $this->getInCompatiblePlugins($result2, $localPluginNames);

        $result = [];

        foreach ($compatiblePlugins as $pluginName => $info) {
            $result[] = [
                'plugin_name' => $pluginName,
                'in_store' => true,
                'compatible' => true,
                'link' => $info['store_url'],
                'shopware_compatible' => $info['shopware_compatible'],
            ];
        }

        foreach ($inCompatiblePlugins as $pluginName => $info) {
            $result[] = [
                'plugin_name' => $pluginName,
                'in_store' => true,
                'compatible' => false,
                'link' => $info['store_url'],
                'shopware_compatible' => $info['shopware_compatible'],
            ];
        }

        foreach ($localPluginNames as $pluginName) {
            $result[] = [
                'plugin_name' => $pluginName,
                'in_store' => false,
                'compatible' => false,
                'link' => null,
                'shopware_compatible' => null,
            ];
        }

        return $result;
    }

    /**
     * Internal helper function to get the passed shopware version as a numeric value with four positions.
     *
     * @param string $version
     *
     * @return int
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
     * Returns a array of all custom plugin names installed
     *
     * @return string[]
     */
    private function getCustomPluginNames()
    {
        $sql = <<<'EOT'
SELECT name FROM s_core_plugins
WHERE source != "Default"
AND name != "PluginManager"
AND name != "StoreAPI"
EOT;
        $result = $this->conn->query($sql)->fetchAll(\PDO::FETCH_COLUMN);

        return $result;
    }

    /**
     * @param array $results
     * @param array $localPluginNames
     */
    private function getCompatiblePlugins($results, &$localPluginNames)
    {
        if (empty($localPluginNames)) {
            return [];
        }

        $compatiblePlugins = [];

        foreach ($results as $result) {
            foreach ($result['plugin_names'] as $pluginName) {
                if (!in_array($pluginName, $localPluginNames)) {
                    continue;
                }

                $compatiblePlugins[$pluginName] = [
                    'store_url' => $result['attributes']['store_url'],
                    'shopware_compatible' => $result['attributes']['shopware_compatible'],
                ];

                unset($localPluginNames[array_search($pluginName, $localPluginNames)]);
            }
        }

        return $compatiblePlugins;
    }

    /**
     * @param array $results
     * @param array $localPluginNames
     */
    private function getInCompatiblePlugins($results, &$localPluginNames)
    {
        $compatiblePlugins = [];

        foreach ($results as $result) {
            foreach ($result['plugin_names'] as $pluginName) {
                if (!in_array($pluginName, $localPluginNames)) {
                    continue;
                }

                $compatiblePlugins[$pluginName] = [
                    'store_url' => $result['attributes']['store_url'],
                    'shopware_compatible' => $result['attributes']['shopware_compatible'],
                ];

                unset($localPluginNames[array_search($pluginName, $localPluginNames)]);
            }
        }

        return $compatiblePlugins;
    }
}
