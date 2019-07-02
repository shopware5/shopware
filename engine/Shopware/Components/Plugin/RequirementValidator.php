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

namespace Shopware\Components\Plugin;

use Enlight_Components_Snippet_Manager as SnippetManager;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\XmlReader\XmlPluginReader;
use Shopware\Models\Plugin\Plugin;

class RequirementValidator
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var XmlPluginReader
     */
    private $infoReader;

    /**
     * @var \Enlight_Components_Snippet_Namespace
     */
    private $namespace;

    public function __construct(ModelManager $em, XmlPluginReader $infoReader, SnippetManager $snippetManager)
    {
        $this->em = $em;
        $this->infoReader = $infoReader;
        $this->namespace = $snippetManager->getNamespace('backend/plugin_manager/exceptions');
    }

    /**
     * @param string $pluginXmlFile   File path to the plugin.xml
     * @param string $shopwareVersion current shopware version
     *
     * @throws \Exception
     */
    public function validate($pluginXmlFile, $shopwareVersion)
    {
        if (!is_file($pluginXmlFile)) {
            return;
        }

        $info = $this->infoReader->read($pluginXmlFile);

        if (isset($info['compatibility'])) {
            $this->assertShopwareVersion($info['compatibility'], $shopwareVersion);
        }

        if (isset($info['requiredPlugins'])) {
            $this->assertRequiredPlugins($info['requiredPlugins']);
        }
    }

    /**
     * @param string $version
     * @param string $required
     * @param string $operator
     *
     * @return bool
     */
    private function assertVersion($version, $required, $operator)
    {
        if ($version === '___VERSION___') {
            return true;
        }

        return version_compare($version, $required, $operator);
    }

    private function assertShopwareVersion(array $compatibility, string $shopwareVersion): void
    {
        if (isset($compatibility['blacklist']) && in_array($shopwareVersion, $compatibility['blacklist'])) {
            throw new \Exception(sprintf($this->namespace->get('shopware_version_blacklisted'), $shopwareVersion));
        }

        if (isset($compatibility['minVersion'])) {
            $min = $compatibility['minVersion'];
            if (strlen($min) > 0 && !$this->assertVersion($shopwareVersion, $min, '>=')) {
                throw new \Exception(sprintf($this->namespace->get('plugin_min_shopware_version'), $min));
            }
        }

        if (isset($compatibility['maxVersion'])) {
            $max = $compatibility['maxVersion'];
            if (strlen($max) > 0 && !$this->assertVersion($shopwareVersion, $max, '<=')) {
                throw new \Exception(sprintf($this->namespace->get('plugin_max_shopware_version'), $max));
            }
        }
    }

    private function assertRequiredPlugins(array $requiredPlugins): void
    {
        $pluginRepository = $this->em->getRepository(Plugin::class);

        foreach ($requiredPlugins as $requiredPlugin) {
            $plugin = $pluginRepository->findOneBy([
                'name' => $requiredPlugin['pluginName'],
            ]);

            if (!$plugin) {
                throw new \Exception(sprintf($this->namespace->get('required_plugin_not_found'), $requiredPlugin['pluginName']));
            }

            if ($plugin->getInstalled() === null) {
                throw new \Exception(sprintf($this->namespace->get('required_plugin_not_installed'), $requiredPlugin['pluginName']));
            }

            if (!$plugin->getActive()) {
                throw new \Exception(sprintf($this->namespace->get('required_plugin_not_active'), $requiredPlugin['pluginName']));
            }

            if (isset($requiredPlugin['blacklist']) && in_array($plugin->getVersion(), $requiredPlugin['blacklist'])) {
                throw new \Exception(sprintf($this->namespace->get('required_plugin_blacklisted'), $plugin->getName(), $plugin->getVersion()));
            }

            if (isset($requiredPlugin['minVersion'])) {
                $min = $requiredPlugin['minVersion'];
                if (strlen($min) > 0 && !$this->assertVersion($plugin->getVersion(), $min, '>=')) {
                    throw new \Exception(sprintf($this->namespace->get('plugin_version_required'), $min, $plugin->getName()));
                }
            }

            if (isset($requiredPlugin['maxVersion'])) {
                $max = $requiredPlugin['maxVersion'];
                if (strlen($max) > 0 && !$this->assertVersion($plugin->getVersion(), $max, '<=')) {
                    throw new \Exception(sprintf($this->namespace->get('plugin_version_max'), $plugin->getName(), $max));
                }
            }
        }
    }
}
