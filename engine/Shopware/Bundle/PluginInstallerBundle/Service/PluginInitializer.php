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

namespace Shopware\Bundle\PluginInstallerBundle\Service;

use PDO;
use Shopware\Components\Plugin;
use Symfony\Component\ClassLoader\Psr4ClassLoader;

class PluginInitializer
{
    /**
     * @var PDO
     */
    private $connection;

    /**
     * @var string[]
     */
    private $pluginDirectories;

    /**
     * @var array[]
     */
    private $activePlugins = [];

    /**
     * @param PDO             $connection
     * @param string|string[] $pluginDirectories
     */
    public function __construct(PDO $connection, $pluginDirectories)
    {
        $this->connection = $connection;
        $this->pluginDirectories = (array) $pluginDirectories;
    }

    /**
     * @throws \RuntimeException
     *
     * @return Plugin[]
     */
    public function initializePlugins()
    {
        $plugins = [];
        $shopwarePlugins = [];

        $classLoader = new Psr4ClassLoader();
        $classLoader->register(true);

        $stmt = $this->connection->query('SELECT name, version, namespace FROM s_core_plugins WHERE active = 1 AND installation_date IS NOT NULL;');
        $this->activePlugins = $stmt->fetchAll(PDO::FETCH_UNIQUE);

        foreach ($this->activePlugins as $pluginName => &$pluginData) {
            if (in_array($pluginData['namespace'], ['ShopwarePlugins', 'ProjectPlugins'], true)) {
                $shopwarePlugins[] = $pluginName;
            }
            $pluginData = $pluginData['version'];
        }
        unset($pluginData);

        foreach ($this->pluginDirectories as $pluginDirectory) {
            foreach (new \DirectoryIterator($pluginDirectory) as $pluginDir) {
                if ($pluginDir->isFile() || $pluginDir->getBasename()[0] === '.') {
                    continue;
                }

                $pluginName = $pluginDir->getBasename();
                $pluginFile = $pluginDir->getPathname() . '/' . $pluginName . '.php';
                if (!is_file($pluginFile)) {
                    continue;
                }

                $namespace = $pluginName;
                $className = '\\' . $namespace . '\\' . $pluginName;
                $classLoader->addPrefix($namespace, $pluginDir->getPathname());

                if (!class_exists($className)) {
                    throw new \RuntimeException(sprintf('Unable to load class %s for plugin %s in file %s', $className, $pluginName, $pluginFile));
                }

                $isActive = in_array($pluginName, $shopwarePlugins, true);

                /** @var Plugin $plugin */
                $plugin = new $className($isActive);

                if (!$plugin instanceof Plugin) {
                    throw new \RuntimeException(sprintf('Class %s must extend %s in file %s', get_class($plugin), Plugin::class, $pluginFile));
                }
                $plugins[$plugin->getName()] = $plugin;
            }
        }

        return $plugins;
    }

    /**
     * @return array
     */
    public function getActivePlugins()
    {
        return $this->activePlugins;
    }
}
