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

namespace Shopware\Tests\Functional\Components\Plugin;

use Shopware\Bundle\PluginInstallerBundle\Service\InstallerService;

/**
 * Ensures a given plugin is installed and sets configuration.
 * After the test is run the initial state is restored
 *
 * protected static $ensureLoadedPlugins = array(
 *     'AdvancedMenu' => array(
 *         'show'    => 1,
 *         'levels'  => 3,
 *         'caching' => 0
 *     )
 * );
 *
 * @runInSeparateProcess
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var array
     */
    protected static $ensureLoadedPlugins = [];

    /**
     * @var InstallerService|null
     */
    private static $pluginManager;

    /**
     * @var array
     */
    private static $pluginStates = [];

    public static function setUpBeforeClass(): void
    {
        self::$pluginManager = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager');
        $loadedPlugins = static::$ensureLoadedPlugins;

        foreach ($loadedPlugins as $key => $value) {
            if (is_array($value)) {
                $pluginName = $key;
                $config = $value;
            } else {
                $pluginName = $value;
                $config = [];
            }

            self::ensurePluginAvailable($pluginName, $config);
        }
    }

    public static function tearDownAfterClass(): void
    {
        self::restorePluginStates();
        self::$pluginManager = null;
        Shopware()->Models()->clear();
    }

    /**
     * Ensures given $pluginName is installed and activated.
     *
     * @param string $pluginName
     * @param array  $config
     */
    private static function ensurePluginAvailable($pluginName, $config = [])
    {
        $plugin = self::$pluginManager->getPluginByName($pluginName);

        self::$pluginStates[$pluginName] = [
            'isInstalled' => (bool) $plugin->getInstalled(),
            'isActive' => (bool) $plugin->getActive(),
        ];

        self::$pluginManager->installPlugin($plugin);
        self::$pluginManager->activatePlugin($plugin);

        foreach ($config as $element => $value) {
            self::$pluginManager->saveConfigElement($plugin, $element, $value);
        }
    }

    /**
     * Restores initial plugin state
     */
    private static function restorePluginStates()
    {
        Shopware()->Models()->clear();
        foreach (self::$pluginStates as $pluginName => $status) {
            self::restorePluginState($pluginName, $status);
        }
    }

    /**
     * @param string $pluginName
     * @param array  $status
     */
    private static function restorePluginState($pluginName, $status)
    {
        $plugin = self::$pluginManager->getPluginByName($pluginName);

        if ($plugin->getInstalled() && !$status['isInstalled']) {
            self::$pluginManager->uninstallPlugin($plugin);

            return;
        }

        if ($plugin->getActive() && !$status['isActive']) {
            self::$pluginManager->deactivatePlugin($plugin);
        }
    }
}
