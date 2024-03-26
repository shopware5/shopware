<?php
declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Plugin;

use Enlight_Components_Snippet_Manager as SnippetManager;
use Enlight_Components_Snippet_Namespace;
use Exception;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\XmlReader\XmlPluginReader;
use Shopware\Models\Plugin\Plugin;

class RequirementValidator
{
    private ModelManager $em;

    private XmlPluginReader $infoReader;

    private Enlight_Components_Snippet_Namespace $namespace;

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
     * @throws Exception
     * @return void
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

    private function assertVersion(string $version, string $required, string $operator): bool
    {
        if ($version === '___VERSION___') {
            return true;
        }

        return version_compare($version, $required, $operator);
    }

    /**
     * @param array{minVersion?: string, maxVersion?: string, blacklist?: list<string>} $compatibility
     */
    private function assertShopwareVersion(array $compatibility, string $shopwareVersion): void
    {
        if (isset($compatibility['blacklist']) && \in_array($shopwareVersion, $compatibility['blacklist'], true)) {
            throw new Exception(sprintf($this->namespace->get('shopware_version_blacklisted'), $shopwareVersion));
        }

        if (isset($compatibility['minVersion'])) {
            $min = $compatibility['minVersion'];
            if ($min !== '' && !$this->assertVersion($shopwareVersion, $min, '>=')) {
                throw new Exception(sprintf($this->namespace->get('plugin_min_shopware_version'), $min));
            }
        }

        if (isset($compatibility['maxVersion'])) {
            $max = $compatibility['maxVersion'];
            if ($max !== '' && !$this->assertVersion($shopwareVersion, $max, '<=')) {
                throw new Exception(sprintf($this->namespace->get('plugin_max_shopware_version'), $max));
            }
        }
    }

    /**
     * @param array<array{pluginName: string, minVersion?: string, maxVersion?: string, blacklist?: list<string>}> $requiredPlugins
     */
    private function assertRequiredPlugins(array $requiredPlugins): void
    {
        $pluginRepository = $this->em->getRepository(Plugin::class);

        foreach ($requiredPlugins as $requiredPlugin) {
            $plugin = $pluginRepository->findOneBy([
                'name' => $requiredPlugin['pluginName'],
            ]);

            if (!$plugin) {
                throw new Exception(sprintf($this->namespace->get('required_plugin_not_found'), $requiredPlugin['pluginName']));
            }

            if ($plugin->getInstalled() === null) {
                throw new Exception(sprintf($this->namespace->get('required_plugin_not_installed'), $requiredPlugin['pluginName']));
            }

            if (!$plugin->getActive()) {
                throw new Exception(sprintf($this->namespace->get('required_plugin_not_active'), $requiredPlugin['pluginName']));
            }

            if (isset($requiredPlugin['blacklist']) && \in_array($plugin->getVersion(), $requiredPlugin['blacklist'], true)) {
                throw new Exception(sprintf($this->namespace->get('required_plugin_blacklisted'), $plugin->getName(), $plugin->getVersion()));
            }

            if (isset($requiredPlugin['minVersion'])) {
                $min = $requiredPlugin['minVersion'];
                if ($min !== '' && !$this->assertVersion($plugin->getVersion(), $min, '>=')) {
                    throw new Exception(sprintf($this->namespace->get('plugin_version_required'), $min, $plugin->getName()));
                }
            }

            if (isset($requiredPlugin['maxVersion'])) {
                $max = $requiredPlugin['maxVersion'];
                if ($max !== '' && !$this->assertVersion($plugin->getVersion(), $max, '<=')) {
                    throw new Exception(sprintf($this->namespace->get('plugin_version_max'), $plugin->getName(), $max));
                }
            }
        }
    }
}
