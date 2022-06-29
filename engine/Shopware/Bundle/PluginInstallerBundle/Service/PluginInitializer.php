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

use Composer\Autoload\ClassLoader;
use DirectoryIterator;
use PDO;
use RuntimeException;
use Shopware\Components\Plugin;

class PluginInitializer
{
    private PDO $connection;

    /**
     * @var string[]
     */
    private array $pluginDirectories;

    /**
     * @var array<string, string>
     */
    private array $activePlugins = [];

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
     * @throws RuntimeException
     *
     * @return Plugin[]
     */
    public function initializePlugins()
    {
        $this->originalErrorHandler = set_error_handler([$this, 'errorHandler'], E_WARNING);

        $plugins = [];
        $shopwarePlugins = [];
        $pluginsAvailable = [];

        $classLoader = new ClassLoader();
        $classLoader->register(true);

        $activePlugins = $this->connection->query(
            'SELECT `name`, `version`, `namespace` FROM s_core_plugins WHERE `active` = 1 AND `installation_date` IS NOT NULL;'
        )->fetchAll(PDO::FETCH_UNIQUE);
        if (!\is_array($activePlugins)) {
            throw new RuntimeException('Could not load plugins from database');
        }

        foreach ($activePlugins as $pluginName => &$pluginData) {
            if (\in_array($pluginData['namespace'], ['ShopwarePlugins', 'ProjectPlugins'], true)) {
                $shopwarePlugins[] = $pluginName;
            }
            $pluginData = $pluginData['version'];
        }
        unset($pluginData);

        $this->activePlugins = $activePlugins;

        // At first, we register all plugin namespaces, to make sure to all namespaces are available on plugin construction
        foreach ($this->pluginDirectories as $pluginNamespace => $pluginDirectory) {
            foreach (new DirectoryIterator($pluginDirectory) as $pluginDir) {
                if ($pluginDir->isFile() || str_starts_with($pluginDir->getBasename(), '.')) {
                    continue;
                }

                $pluginName = $pluginDir->getBasename();
                $pluginFile = $pluginDir->getPathname() . '/' . $pluginName . '.php';
                if (!is_file($pluginFile)) {
                    continue;
                }

                $classLoader->addPsr4($pluginName . '\\', $pluginDir->getPathname());

                $pluginsAvailable[$pluginName] = [
                    'className' => '\\' . $pluginName . '\\' . $pluginName,
                    'isActive' => \in_array($pluginName, $shopwarePlugins, true),
                    'pluginFile' => $pluginFile,
                    'pluginNamespace' => $pluginNamespace,
                ];
            }
        }

        foreach ($pluginsAvailable as $pluginName => $pluginDetails) {
            if (!class_exists($pluginDetails['className'])) {
                throw new RuntimeException(sprintf('Unable to load class %s for plugin %s in file %s', $pluginDetails['className'], $pluginName, $pluginDetails['pluginFile']));
            }

            $plugin = new $pluginDetails['className']($pluginDetails['isActive'], $pluginDetails['pluginNamespace']);

            if (!$plugin instanceof Plugin) {
                throw new RuntimeException(sprintf('Class %s must extend %s in file %s', \get_class($plugin), Plugin::class, $pluginDetails['pluginFile']));
            }

            $plugins[$plugin->getName()] = $plugin;
        }

        restore_error_handler();

        return $plugins;
    }

    /**
     * @return array<string, string>
     */
    public function getActivePlugins()
    {
        return $this->activePlugins;
    }

    /**
     * This early error handler help us to avoid plugin warnings, which still use old registerCommands method in Plugin.
     *
     * @deprecated Remove with Shopware 5.8
     *
     * @return mixed|void
     */
    public function errorHandler(int $errno, string $errstr, string $errfile, int $errline)
    {
        if (stripos($errstr, 'should be compatible with Shopware\Components\Plugin::registerCommands(Symfony\Component\Console\Application $application)') !== false) {
            return;
        }

        if ($this->originalErrorHandler) {
            return \call_user_func($this->originalErrorHandler, $errno, $errstr, $errfile, $errline);
        }
    }
}
