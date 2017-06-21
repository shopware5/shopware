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

use Shopware\Components\Model\ModelManager;
use Shopware\Models\Plugin\Plugin;

class RequirementValidator
{
    /**
     * @var ModelManager
     */
    private $em;

    /**
     * @var XmlPluginInfoReader
     */
    private $infoReader;

    /**
     * RequirementValidator constructor.
     *
     * @param ModelManager        $em
     * @param XmlPluginInfoReader $infoReader
     */
    public function __construct(ModelManager $em, XmlPluginInfoReader $infoReader)
    {
        $this->em = $em;
        $this->infoReader = $infoReader;
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
        if ($version == '___VERSION___') {
            return true;
        }

        return version_compare($version, $required, $operator);
    }

    /**
     * @param array  $compatibility
     * @param string $shopwareVersion
     *
     * @throws \Exception
     */
    private function assertShopwareVersion($compatibility, $shopwareVersion)
    {
        if (in_array($shopwareVersion, $compatibility['blacklist'])) {
            throw new \Exception(sprintf('Shopware version %s is blacklisted by the plugin', $shopwareVersion));
        }

        $min = $compatibility['minVersion'];
        if (strlen($min) > 0 && !$this->assertVersion($shopwareVersion, $min, '>=')) {
            throw new \Exception(sprintf('Plugin requires at least Shopware version %s', $min));
        }

        $max = $compatibility['maxVersion'];
        if (strlen($max) > 0 && !$this->assertVersion($shopwareVersion, $max, '<=')) {
            throw new \Exception(sprintf('Plugin is only compatible with Shopware version <= %s', $max));
        }
    }

    /**
     * @param array[] $requiredPlugins
     *
     * @throws \Exception
     */
    private function assertRequiredPlugins($requiredPlugins)
    {
        $repo = $this->em->getRepository(Plugin::class);

        foreach ($requiredPlugins as $requiredPlugin) {
            $plugin = $repo->findOneBy(['name' => $requiredPlugin['pluginName']]);

            if (!$plugin) {
                throw new \Exception(sprintf('Required plugin %s was not found', $requiredPlugin['pluginName']));
            }

            if ($plugin->getInstalled() === null) {
                throw  new \Exception(sprintf('Required plugin %s is not installed', $requiredPlugin['pluginName']));
            }

            if (!$plugin->getActive()) {
                throw  new \Exception(sprintf('Required plugin %s is not active', $requiredPlugin['pluginName']));
            }

            if (in_array($plugin->getVersion(), $requiredPlugin['blacklist'])) {
                throw new \Exception(sprintf('Required plugin %s with version %s is blacklisted', $plugin->getName(), $plugin->getVersion()));
            }

            $min = $requiredPlugin['minVersion'];
            if (strlen($min) > 0 && !$this->assertVersion($plugin->getVersion(), $min, '>=')) {
                throw new \Exception(sprintf('Version %s of plugin %s is required.', $min, $plugin->getName()));
            }

            $max = $requiredPlugin['maxVersion'];
            if (strlen($max) > 0 && !$this->assertVersion($plugin->getVersion(), $max, '<=')) {
                throw new \Exception(sprintf('Plugin is only compatible with Plugin %s version <= %s', $plugin->getName(), $max));
            }
        }
    }
}
