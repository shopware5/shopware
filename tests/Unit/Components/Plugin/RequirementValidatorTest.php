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

namespace Shopware\Tests\Unit\Components\Plugin;

use PHPUnit\Framework\TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\Plugin\XmlReader\XmlPluginReader;
use Shopware\Models\Plugin\Plugin;

class RequirementValidatorTest extends TestCase
{
    /**
     * @var Plugin[]
     */
    private $plugins;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plugins = [];
    }

    public function testMinimumShopwareVersionShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Plugin requires at least Shopware version 5.1.0');
        $validator = $this->getValidator([]);
        $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '4.0.0');
    }

    public function testMinimumShopwareVersionShouldBeSuccessful()
    {
        $validator = $this->getValidator([]);
        $e = null;
        try {
            $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '5.1.0');
        } catch (\Exception $e) {
        }

        static::assertNull($e);
    }

    public function testMaximumShopwareVersionShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Plugin is only compatible with Shopware version <= 5.2');
        $validator = $this->getValidator([]);
        $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '5.3');
    }

    public function testMaximumShopwareVersionShouldBeSuccessful()
    {
        $validator = $this->getValidator([]);
        $e = null;
        try {
            $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '5.1.0');
        } catch (\Exception $e) {
        }
        static::assertNull($e);
    }

    public function testBlackListedShopwareVersionShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Shopware version 5.1.2 is blacklisted by the plugin');
        $validator = $this->getValidator([]);
        $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '5.1.2');
    }

    public function testBlackListedShopwareVersionShouldSuccessful()
    {
        $validator = $this->getValidator([]);
        $e = null;
        try {
            $validator->validate(__DIR__ . '/examples/shopware_version_requirement.xml', '5.1.3');
        } catch (\Exception $e) {
        }
        static::assertNull($e);
    }

    public function testRequiredPluginNotExists()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Required plugin SwagBundle was not found');
        $validator = $this->getValidator([]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testSecondRequiredPluginNotExists()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Required plugin SwagLiveShopping was not found');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.5', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginInstalledShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Required plugin SwagBundle is not installed');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '1.0', 'active' => false, 'installed' => null],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginActiveShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Required plugin SwagBundle is not active');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'active' => false, 'version' => '1.0', 'installed' => '2016-01-01 11:00:00'],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginMinimumVersionShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Version 2.0 of plugin SwagBundle is required.');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '1.0', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginMaximumVersionShouldFail()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Plugin is only compatible with Plugin SwagBundle version <= 3.0');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '10.0', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginVersionIsBlackListed()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Required plugin SwagBundle with version 2.1 is blacklist');
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.1', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginsShouldBeSuccessful()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.1.1', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
            ['name' => 'SwagLiveShopping', 'version' => '2.1.1', 'active' => true, 'installed' => '2016-01-01 11:00:00'],
        ]);

        $e = null;
        try {
            $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
        } catch (\Exception $e) {
        }
        static::assertNull($e);
    }

    /**
     * @param array $args
     *
     * @return Plugin|null
     */
    public function findPluginByName($args)
    {
        $name = $args['name'];
        if (isset($this->plugins[$name])) {
            return $this->plugins[$name];
        }

        return null;
    }

    private function getValidator(array $plugins)
    {
        $repo = $this->createMock(ModelRepository::class);
        $defaults = ['active' => false, 'installed' => null];

        foreach ($plugins as $pluginInfo) {
            $pluginInfo = array_merge($defaults, $pluginInfo);

            $plugin = $this->createConfiguredMock(Plugin::class, [
                'getVersion' => $pluginInfo['version'],
                'getName' => $pluginInfo['name'],
                'getActive' => $pluginInfo['active'],
                'getInstalled' => $pluginInfo['installed'],
            ]);

            $this->plugins[$pluginInfo['name']] = $plugin;
        }

        if ($plugins) {
            $repo->method('findOneBy')
                ->will(static::returnCallback([$this, 'findPluginByName']));
        }

        $em = $this->createConfiguredMock(ModelManager::class, ['getRepository' => $repo]);

        return new RequirementValidator($em, new XmlPluginReader(), $this->createSnippetManager());
    }

    /**
     * @return \Enlight_Components_Snippet_Manager
     */
    private function createSnippetManager()
    {
        $snippetNamespace = $this->createMock(\Enlight_Components_Snippet_Namespace::class);

        $snippetNamespace
            ->expects(static::any())
            ->method('get')
            ->willReturnCallback(function ($arg) {
                switch ($arg) {
                    case 'plugin_min_shopware_version':
                        return 'Plugin requires at least Shopware version %s';
                    case 'plugin_max_shopware_version':
                        return 'Plugin is only compatible with Shopware version <= %s';
                    case 'shopware_version_blacklisted':
                        return 'Shopware version %s is blacklisted by the plugin';
                    case 'required_plugin_not_found':
                        return 'Required plugin %s was not found';
                    case 'required_plugin_not_installed':
                        return 'Required plugin %s is not installed';
                    case 'required_plugin_not_active':
                        return 'Required plugin %s is not active';
                    case 'plugin_version_required':
                        return 'Version %s of plugin %s is required.';
                    case 'plugin_version_max':
                        return 'Plugin is only compatible with Plugin %s version <= %s';
                    case 'required_plugin_blacklisted':
                        return 'Required plugin %s with version %s is blacklisted';
                }
            });

        $snippetManager = $this->createMock(\Enlight_Components_Snippet_Manager::class);

        $snippetManager->expects(static::any())
            ->method('getNamespace')
            ->willReturn($snippetNamespace);

        return $snippetManager;
    }
}
