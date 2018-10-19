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

use PHPUnit\Framework\TestCase;

class NamespaceTest extends TestCase
{
    /**
     * @var \Shopware_Components_Plugin_Namespace
     */
    private $namespace;

    /**
     * @var string
     */
    private $testNamespaceName = 'Core';

    /**
     * @var \Enlight_Config
     */
    private $storage;

    protected function setUp()
    {
        // Build Namespace Test Object and Set Manager
        $pluginManager = new \Enlight_Plugin_PluginManager(Shopware());
        $this->namespace = new \Shopware_Components_Plugin_Namespace(
            $this->testNamespaceName,
            null,
            [],
            Shopware()->Container()->get('shopware.plugin.cached_config_reader')
        );
        $this->namespace->setManager($pluginManager);

        // Execute Storage (with init)
        $this->storage = $this->namespace->Storage();
    }

    public function testListenerCache()
    {
        // And Check correctly set cache keys
        $cache = Shopware()->Container()->get('cache');
        $result = $cache->load('globalListenersPlugins' . $this->testNamespaceName);

        $this->assertEquals(['name', 'listener', 'position', 'plugin'], array_keys($result[0]));
    }

    public function testPluginListCache()
    {
        // And Check correctly set cache keys
        $cache = Shopware()->Container()->get('cache');
        $result = $cache->load('globalAllPlugins' . $this->testNamespaceName);

        $this->assertEquals(['name', 'id', 'label', 'description', 'source', 'active', 'installationDate', 'updateDate', 'version'], array_keys($result[0]));
    }

    public function testPluginStorage()
    {
        $this->assertGreaterThan(0, $this->storage->get('plugins')->count());
    }

    public function testListenerStorage()
    {
        $this->assertGreaterThan(0, $this->storage->get('listeners')->count());
    }
}
