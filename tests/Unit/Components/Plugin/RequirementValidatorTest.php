<?php

namespace Shopware\Components\Test\Plugin;

use Shopware\Components\Model\ModelManager;
use Shopware\Components\Model\ModelRepository;
use Shopware\Components\Plugin;
use Shopware\Components\Plugin\RequirementValidator;
use Shopware\Components\Plugin\XmlPluginInfoReader;

class RequirementValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Shopware\Models\Plugin\Plugin[]
     */
    private $plugins;

    protected function setUp()
    {
        parent::setUp();
        $this->plugins = [];
    }


    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Plugin requires at least Shopware version 5.1.0
     */
    public function testMinimumShopwareVersionShouldFail()
    {
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

        $this->assertNull($e);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Plugin is only compatible with Shopware version <= 5.2
     */
    public function testMaximumShopwareVersionShouldFail()
    {
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
        $this->assertNull($e);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Shopware version 5.1.2 is blacklisted by the plugin
     */
    public function testBlackListedShopwareVersionShouldFail()
    {
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
        $this->assertNull($e);
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Required plugin SwagBundle was not found
     */
    public function testRequiredPluginNotExists()
    {
        $validator = $this->getValidator([]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Required plugin SwagLiveShopping was not found
     */
    public function testSecondRequiredPluginNotExists()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.5']
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Version 2.0 of plugin SwagBundle is required.
     */
    public function testRequiredPluginMinimumVersionShouldFail()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '1.0']
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Plugin is only compatible with Plugin SwagBundle version <= 3.0
     */
    public function testRequiredPluginMaximumVersionShouldFail()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '10.0']
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Required plugin SwagBundle with version 2.1 is blacklist
     */
    public function testRequiredPluginVersionIsBlackListed()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.1']
        ]);
        $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
    }

    public function testRequiredPluginsShouldBeSuccessful()
    {
        $validator = $this->getValidator([
            ['name' => 'SwagBundle', 'version' => '2.1.1'],
            ['name' => 'SwagLiveShopping', 'version' => '2.1.1']
        ]);

        $e = null;
        try {
            $validator->validate(__DIR__ . '/examples/shopware_required_plugin.xml', '5.2');
        } catch (\Exception $e) {
        }
        $this->assertNull($e);
    }

    private function getValidator($plugins)
    {
        $em = $this->getMockBuilder(ModelManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repo = $this->getMockBuilder(ModelRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        foreach ($plugins as $pluginInfo) {
            $plugin = $this->getMockBuilder(\Shopware\Models\Plugin\Plugin::class)
                ->disableOriginalConstructor()
                ->getMock();

            $plugin->method('getVersion')
                ->willReturn($pluginInfo['version']);

            $plugin->method('getName')
                ->willReturn($pluginInfo['name']);

            $this->plugins[$pluginInfo['name']] = $plugin;
        }

        if ($plugins) {
            $repo->method('findOneBy')
                ->will($this->returnCallback([$this, 'findPluginByName']));
        }

        $em->method('getRepository')
           ->willReturn($repo);

        return new RequirementValidator($em, new XmlPluginInfoReader());
    }

    /**
     * @param $args
     * @return null|\Shopware\Models\Plugin\Plugin
     */
    public function findPluginByName($args)
    {
        $name = $args['name'];
        if (isset($this->plugins[$name])) {
            return $this->plugins[$name];
        }
        return null;
    }
}
