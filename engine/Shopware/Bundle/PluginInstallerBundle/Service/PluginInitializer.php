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
     * @var callable|null
     */
    private $originalErrorHandler;

    /**
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
        $this->originalErrorHandler = set_error_handler([$this, 'errorHandler'], E_WARNING);

        $plugins = [];
        $shopwarePlugins = [];
        $pluginsAvailable = [];

        // @todo: Replace me in Shopware 5.7
        $classLoader = new Psr4ClassLoader();
        $classLoader->register(true);

        // Clearing the deprecation notice in the error stack from the deprecated classloader above
        error_clear_last();

        $stmt = $this->connection->query('SELECT `name`, `version`, `namespace` FROM s_core_plugins WHERE `active` = 1 AND `installation_date` IS NOT NULL;');
        $this->activePlugins = $stmt->fetchAll(PDO::FETCH_UNIQUE);

        foreach ($this->activePlugins as $pluginName => &$pluginData) {
            if (in_array($pluginData['namespace'], ['ShopwarePlugins', 'ProjectPlugins'], true)) {
                $shopwarePlugins[] = $pluginName;
            }
            $pluginData = $pluginData['version'];
        }
        unset($pluginData);

        // As first we register all plugin namespaces, to make sure to all namespaces are available on plugin construction
        foreach ($this->pluginDirectories as $pluginNamespace => $pluginDirectory) {
            foreach (new \DirectoryIterator($pluginDirectory) as $pluginDir) {
                if ($pluginDir->isFile() || strpos($pluginDir->getBasename(), '.') === 0) {
                    continue;
                }

                $pluginName = $pluginDir->getBasename();
                $pluginFile = $pluginDir->getPathname() . '/' . $pluginName . '.php';
                if (!is_file($pluginFile)) {
                    continue;
                }

                $classLoader->addPrefix($pluginName, $pluginDir->getPathname());

                $pluginsAvailable[$pluginName] = [
                    'className' => '\\' . $pluginName . '\\' . $pluginName,
                    'isActive' => in_array($pluginName, $shopwarePlugins, true),
                    'pluginFile' => $pluginFile,
                    'pluginNamespace' => $pluginNamespace,
                ];
            }
        }

        foreach ($pluginsAvailable as $pluginName => $pluginDetails) {
            if (!class_exists($pluginDetails['className'])) {
                throw new \RuntimeException(sprintf('Unable to load class %s for plugin %s in file %s', $pluginDetails['className'], $pluginName, $pluginDetails['pluginFile']));
            }

            /** @var Plugin $plugin */
            $plugin = new $pluginDetails['className']($pluginDetails['isActive'], $pluginDetails['pluginNamespace']);

            if (!$plugin instanceof Plugin) {
                throw new \RuntimeException(sprintf('Class %s must extend %s in file %s', get_class($plugin), Plugin::class, $pluginDetails['pluginFile']));
            }

            $plugins[$plugin->getName()] = $plugin;
        }

        restore_error_handler();

        return $plugins;
    }

    /**
     * @return array
     */
    public function getActivePlugins()
    {
        return $this->activePlugins;
    }

    /**
     * This early error handler help us to avoid plugin warnings, which still use old registerCommands method in Plugin.
     *
     * @deprecated Remove with Shopware 5.7
     *
     * @return mixed|void
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline, array $errcontext)
    {
        if (stripos($errstr, 'should be compatible with Shopware\Components\Plugin::registerCommands(Symfony\Component\Console\Application $application)') !== false) {
            return;
        }

        if ($this->originalErrorHandler) {
            return call_user_func($this->originalErrorHandler, $errno, $errstr, $errfile, $errline, $errcontext);
        }
    }
}
