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

namespace ShopwarePlugins\SwagUpdate\Components;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Components\DependencyInjection\Container;

class PluginCheck
{
    /**
     * @var \Shopware\Components\DependencyInjection\Container
     */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Check if the currently installed plugin where marked to be compatible with the selected sw version
     *
     * @param string $version
     *
     * @return array
     */
    public function checkInstalledPluginsAvailableForNewVersion($version)
    {
        $service = $this->container->get('shopware_plugininstaller.plugin_service_store_production');
        $installedPlugins = $this->getUserInstalledPlugins();
        $technicalNames = array_column($installedPlugins, 'name');
        $locale = $this->getLocale();

        $shopwareVersion = $this->container->getParameter('shopware.release.version');

        $request = new PluginsByTechnicalNameRequest($locale, $shopwareVersion, $technicalNames);
        $storePlugins = $service->getPlugins($request);

        $request = new PluginsByTechnicalNameRequest($locale, $version, $technicalNames);
        $updatesAvailable = $service->getPlugins($request);

        try {
            $results = [];
            foreach ($installedPlugins as $plugin) {
                $technicalName = $plugin['name'];
                $key = strtolower($plugin['name']);
                $name = $plugin['label'];

                $inStore = array_key_exists($key, $storePlugins);
                $targetVersionUpdateAvailable = array_key_exists($key, $updatesAvailable);
                $description = $this->getPluginStateDescription($inStore, $targetVersionUpdateAvailable);

                $results[] = [
                    'inStore' => $inStore,
                    'name' => $name,
                    'message' => $description,
                    'updatable' => $inStore && version_compare($plugin['version'], $storePlugins[$key]->getVersion(), '<'),
                    'updatableAfterUpgrade' => $inStore && $targetVersionUpdateAvailable && $storePlugins[$key]->getVersion() !== $updatesAvailable[$key]->getVersion(),
                    'id' => sprintf('plugin_incompatible-%s', $name),
                    'technicalName' => $technicalName,
                    'errorLevel' => ($targetVersionUpdateAvailable) ? Validation::REQUIREMENT_VALID : Validation::REQUIREMENT_WARNING,
                ];
            }
        } catch (\Exception $e) {
            $results[] = [
                'name' => 'Error',
                'message' => 'Could not query plugins which are available for your shopware version',
                'details' => $e->getCode() . ': ' . $e->getMessage(),
                'errorLevel' => Validation::REQUIREMENT_WARNING,
                'id' => 'plugin_available_error',
            ];
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
     * Helper which returns an snippet-instance
     *
     * @return \Enlight_Components_Snippet_Namespace
     */
    public function getSnippetNamespace()
    {
        return $this->container->get('snippets')->getNamespace('backend/swag_update/main');
    }

    /**
     * @return string|null
     */
    private function getLocale()
    {
        try {
            return $this->container->get('auth')->getIdentity()->locale->getLocale();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Returns a list of all plugins installed by the user
     *
     * @return array
     */
    private function getUserInstalledPlugins()
    {
        $query = $this->container->get('dbal_connection')->createQueryBuilder();
        $query->select(['plugin.name', 'plugin.label', 'plugin.version'])
            ->from('s_core_plugins', 'plugin')
            ->where('plugin.name NOT IN (:names)')
            ->andWhere('plugin.source != :source')
            ->setParameter(':source', 'Default')
            ->setParameter(':names', ['PluginManager', 'StoreApi'], Connection::PARAM_STR_ARRAY);

        /** @var \PDOStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @param bool $inStore
     * @param bool $available
     *
     * @return string
     */
    private function getPluginStateDescription($inStore, $available)
    {
        switch (true) {
            case $inStore && $available:
                return $this->getSnippetNamespace()->get('controller/plugin_compatible', 'The author of the plugin marked the plugin as compatible.');
            case $inStore && !$available:
                return $this->getSnippetNamespace()->get('controller/plugin_not_compatible', 'The author of the plugin did not mark the plugin as compatible with the shopware version');
            default:
                return $this->getSnippetNamespace()->get('controller/plugin_not_in_store', 'The plugin is not available in the store.');
        }
    }
}
