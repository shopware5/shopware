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

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\ConfigReader;
use Shopware\Components\Plugin\ConfigWriter;
use Shopware\Components\Plugin\Context\ActivateContext;
use Shopware\Components\Plugin\Context\DeactivateContext;
use Shopware\Components\Plugin\Context\InstallContext;
use Shopware\Components\Plugin\Context\UninstallContext;
use Shopware\Components\Plugin\Context\UpdateContext;
use Shopware\Components\ShopwareReleaseStruct;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;

class InstallerService
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var \Shopware\Components\Model\ModelRepository
     */
    private $pluginRepository;

    /**
     * @var \Shopware\Models\Shop\Repository
     */
    private $shopRepository;

    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @var ConfigWriter
     */
    private $configWriter;

    /**
     * @var LegacyPluginInstaller
     */
    private $legacyPluginInstaller;

    /**
     * @var PluginInstaller
     */
    private $pluginInstaller;

    /**
     * @var ShopwareReleaseStruct
     */
    private $release;

    public function __construct(
        ModelManager $em,
        PluginInstaller $pluginInstaller,
        LegacyPluginInstaller $legacyPluginInstaller,
        ConfigWriter $configWriter,
        ConfigReader $configReader,
        ShopwareReleaseStruct $release
    ) {
        $this->em = $em;
        $this->pluginInstaller = $pluginInstaller;
        $this->legacyPluginInstaller = $legacyPluginInstaller;
        $this->configWriter = $configWriter;
        $this->configReader = $configReader;
        $this->pluginRepository = $this->em->getRepository(Plugin::class);
        $this->shopRepository = $this->em->getRepository(Shop::class);
        $this->release = $release;
    }

    /**
     * @param string $pluginName
     *
     * @throws \Exception
     *
     * @return string
     */
    public function getPluginPath($pluginName)
    {
        $plugin = $this->getPluginByName($pluginName);

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->getPluginPath($plugin);
        }

        return $this->legacyPluginInstaller->getPluginPath($plugin);
    }

    /**
     * @param string $pluginName
     *
     * @throws \Exception
     *
     * @return Plugin
     */
    public function getPluginByName($pluginName)
    {
        /** @var Plugin|null $plugin */
        $plugin = $this->pluginRepository->findOneBy([
            'name' => $pluginName,
            'capabilityEnable' => 1,
        ]);

        if ($plugin === null) {
            throw new \Exception(sprintf('Unknown plugin "%s".', $pluginName));
        }

        return $plugin;
    }

    /**
     * Returns a certain plugin by plugin id.
     *
     * @return \Shopware_Components_Plugin_Bootstrap|null
     */
    public function getPluginBootstrap(Plugin $plugin)
    {
        return $this->legacyPluginInstaller->getPluginBootstrap($plugin);
    }

    /**
     * @throws \Exception
     *
     * @return InstallContext
     */
    public function installPlugin(Plugin $plugin)
    {
        $context = new InstallContext($plugin, $this->release->getVersion(), $plugin->getVersion());
        if ($plugin->getInstalled()) {
            return $context;
        }

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->installPlugin($plugin);
        }

        $result = $this->legacyPluginInstaller->installPlugin($plugin);
        $this->applyLegacyResultToContext($result, $context);

        return $context;
    }

    /**
     * @param bool $removeData
     *
     * @throws \Exception
     *
     * @return UninstallContext
     */
    public function uninstallPlugin(Plugin $plugin, $removeData = true)
    {
        $context = new UninstallContext($plugin, $this->release->getVersion(), $plugin->getVersion(), !$removeData);
        if (!$plugin->getInstalled()) {
            return $context;
        }

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->uninstallPlugin($plugin, $removeData);
        }

        $result = $this->legacyPluginInstaller->uninstallPlugin($plugin, $removeData);
        $this->applyLegacyResultToContext($result, $context);

        return $context;
    }

    /**
     * @throws \Exception
     *
     * @return UpdateContext
     */
    public function updatePlugin(Plugin $plugin)
    {
        $context = new UpdateContext($plugin, $this->release->getVersion(), $plugin->getVersion(), $plugin->getUpdateVersion());
        if (!$plugin->getUpdateVersion()) {
            return $context;
        }

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->updatePlugin($plugin);
        }

        $result = $this->legacyPluginInstaller->updatePlugin($plugin);
        $this->applyLegacyResultToContext($result, $context);

        return $context;
    }

    /**
     * @throws \Exception
     *
     * @return ActivateContext
     */
    public function activatePlugin(Plugin $plugin)
    {
        $context = new ActivateContext($plugin, $this->release->getVersion(), $plugin->getVersion());
        if ($plugin->getActive()) {
            return $context;
        }

        if (!$plugin->getInstalled()) {
            throw new \Exception(sprintf('Plugin "%s" has to be installed first before it can be activated.', $plugin->getName()));
        }

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->activatePlugin($plugin);
        }

        $result = $this->legacyPluginInstaller->activatePlugin($plugin);
        $this->applyLegacyResultToContext($result, $context);

        return $context;
    }

    /**
     * @throws \Exception
     *
     * @return DeactivateContext
     */
    public function deactivatePlugin(Plugin $plugin)
    {
        $context = new DeactivateContext($plugin, $this->release->getVersion(), $plugin->getVersion());
        if (!$plugin->getActive()) {
            return $context;
        }

        if (!$plugin->isLegacyPlugin()) {
            return $this->pluginInstaller->deactivatePlugin($plugin);
        }

        $result = $this->legacyPluginInstaller->deactivatePlugin($plugin);
        $this->applyLegacyResultToContext($result, $context);

        return $context;
    }

    /**
     * @param Shop $shop
     *
     * @return array
     */
    public function getPluginConfig(Plugin $plugin, Shop $shop = null)
    {
        return $this->configReader->getByPluginName($plugin->getName(), $shop);
    }

    /**
     * @param array $elements
     * @param Shop  $shop
     */
    public function savePluginConfig(Plugin $plugin, $elements, Shop $shop = null)
    {
        if ($shop === null) {
            /** @var Shop $shop */
            $shop = $this->shopRepository->find($this->shopRepository->getActiveDefault()->getId());
        }

        $this->configWriter->savePluginConfig($plugin, $elements, $shop);
    }

    /**
     * @param string $name
     * @param Shop   $shop
     *
     * @throws \Exception
     */
    public function saveConfigElement(Plugin $plugin, $name, $value, Shop $shop = null)
    {
        if ($shop === null) {
            /** @var Shop $shop */
            $shop = $this->shopRepository->find($this->shopRepository->getActiveDefault()->getId());
        }

        $this->configWriter->saveConfigElement($plugin, $name, $value, $shop);
    }

    public function refreshPluginList()
    {
        $refreshDate = new \DateTimeImmutable();

        $this->pluginInstaller->refreshPluginList($refreshDate);
        $this->legacyPluginInstaller->refreshPluginList($refreshDate);
    }

    /**
     * @param bool|array                                                                      $result
     * @param InstallContext|ActivateContext|DeactivateContext|UninstallContext|UpdateContext $context
     */
    private function applyLegacyResultToContext($result, InstallContext $context)
    {
        if (is_bool($result)) {
            return;
        }

        if (array_key_exists('invalidateCache', $result)) {
            $context->scheduleClearCache($result['invalidateCache']);
        }

        if (array_key_exists('message', $result)) {
            $context->scheduleMessage($result['message']);
        }
    }
}
