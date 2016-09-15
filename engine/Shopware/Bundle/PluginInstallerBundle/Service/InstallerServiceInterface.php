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

use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

interface InstallerServiceInterface
{
    /**
     * @param string $pluginName
     *
     * @return string
     * @throws \Exception
     */
    public function getPluginPath($pluginName);

    /**
     * @param string $pluginName
     *
     * @throws \Exception
     * @return Plugin
     */
    public function getPluginByName($pluginName);

    /**
     * Returns a certain plugin by plugin id.
     *
     * @param Plugin $plugin
     *
     * @return \Shopware_Components_Plugin_Bootstrap|null
     */
    public function getPluginBootstrap(Plugin $plugin);

    /**
     * @param Plugin $plugin
     *
     * @return InstallContext
     * @throws \Exception
     */
    public function installPlugin(Plugin $plugin);

    /**
     * @param Plugin $plugin
     * @param bool   $removeData
     *
     * @return UninstallContext
     * @throws \Exception
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true);

    /**
     * @param Plugin $plugin
     *
     * @return UpdateContext
     * @throws \Exception
     */
    public function updatePlugin(Plugin $plugin);

    /**
     * @param Plugin $plugin
     *
     * @return ActivateContext
     * @throws \Exception
     */
    public function activatePlugin(Plugin $plugin);

    /**
     * @param Plugin $plugin
     *
     * @return DeactivateContext
     * @throws \Exception
     */
    public function deactivatePlugin(Plugin $plugin);

    /**
     * @param Plugin $plugin
     * @param Shop   $shop
     *
     * @return array
     */
    public function getPluginConfig(Plugin $plugin, Shop $shop = null);

    /**
     * @param Plugin $plugin
     * @param array  $elements
     * @param Shop   $shop
     */
    public function savePluginConfig(Plugin $plugin, $elements, Shop $shop = null);

    /**
     * @param Plugin $plugin
     * @param string $name
     * @param mixed  $value
     * @param Shop   $shop
     *
     * @throws \Exception
     */
    public function saveConfigElement(Plugin $plugin, $name, $value, Shop $shop = null);

    public function refreshPluginList();
}
