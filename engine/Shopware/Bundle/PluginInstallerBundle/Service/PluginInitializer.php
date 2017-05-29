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
     * @var string
     */
    private $pluginDirectory;

    /**
     * @param PDO $connection
     * @param $pluginDirectory
     */
    public function __construct(PDO $connection, $pluginDirectory)
    {
        $this->connection = $connection;
        $this->pluginDirectory = $pluginDirectory;
    }

    /**
     * @throws \RuntimeException
     *
     * @return Plugin[]
     */
    public function initializePlugins()
    {
        $plugins = [];

        $classLoader = new Psr4ClassLoader();
        $classLoader->register(true);

        $stmt = $this->connection->query('SELECT name FROM s_core_plugins WHERE namespace LIKE "ShopwarePlugins" AND active = 1 AND installation_date IS NOT NULL;');
        $activePlugins = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach (new \DirectoryIterator($this->pluginDirectory) as $pluginDir) {
            if ($pluginDir->isFile() || $pluginDir->getBasename()[0] === '.') {
                continue;
            }

            $pluginName = $pluginDir->getBasename();
            $pluginFile = $pluginDir->getPathname() . '/' . $pluginName . '.php';
            if (!is_file($pluginFile)) {
                continue;
            }

            $isActive = in_array($pluginName, $activePlugins, true);
            if (!$isActive) {
                continue;
            }

            $namespace = $pluginName;
            $className = '\\' . $namespace . '\\' . $pluginName;
            $classLoader->addPrefix($namespace, $pluginDir->getPathname());

            if (!class_exists($className)) {
                throw new \RuntimeException(sprintf('Unable to load class %s for plugin %s in file %s', $className, $pluginName, $pluginFile));
            }

            /** @var Plugin $plugin */
            $plugin = new $className($isActive);

            if (!$plugin instanceof Plugin) {
                throw new \RuntimeException(sprintf('Class %s must extend %s in file %s', get_class($plugin), Plugin::class, $pluginFile));
            }
            $plugins[$plugin->getName()] = $plugin;
        }

        return $plugins;
    }
}
