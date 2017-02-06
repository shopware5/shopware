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

namespace Shopware\Components\Test\Plugin;

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
 * @category  Shopware
 * @package   Shopware\Components\Test\Plugin
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InstallerService
     */
    private static $pluginManager;

    /**
     * @var array
     */
    protected static $ensureLoadedPlugins = array();

    /**
     * @var array
     */
    private static $pluginStates = array();

    public static function setUpBeforeClass()
    {
        self::$pluginManager = Shopware()->Container()->get('shopware_plugininstaller.plugin_manager');
        $loadedPlugins = static::$ensureLoadedPlugins;

        foreach ($loadedPlugins as $key => $value) {
            if (is_array($value)) {
                $pluginName = $key;
                $config = $value;
            } else {
                $pluginName = $value;
                $config = array();
            }

            self::ensurePluginAvailable($pluginName, $config);
        }
    }

    public static function tearDownAfterClass()
    {
        self::restorePluginStates();
        self::$pluginManager = null;
        Shopware()->Models()->clear();
    }

    /**
     * Ensures given $pluginName is installed and activated.
     *
     * @param $pluginName
     * @param array $config
     */
    private static function ensurePluginAvailable($pluginName, $config = array())
    {
        $plugin = self::$pluginManager->getPluginByName($pluginName);

        self::$pluginStates[$pluginName] = array(
            'isInstalled' => (bool) $plugin->getInstalled(),
            'isActive'    => (bool) $plugin->getActive(),
        );

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
     * @param $pluginName
     * @param $status
     * @return int
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
