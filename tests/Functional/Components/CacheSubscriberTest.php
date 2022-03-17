<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Components;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopware\Components\CacheSubscriber;
use Shopware\Models\Config\Form;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Helper\Utils;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class CacheSubscriberTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private int $pluginId = 0;

    protected function setUp(): void
    {
        $connection = Shopware()->Container()->get('dbal_connection');

        // setup plugin
        $connection->insert('s_core_plugins', [
            'namespace' => 'Core',
            'name' => 'CacheSubscriberTest',
            'label' => 'This is a config reader test plugin',
            'source' => 'php unit',
            'active' => 0,
            'added' => new DateTime(),
            'version' => '1.0.0',
            'capability_update' => 0,
            'capability_install' => 0,
            'capability_enable' => 1,
            'capability_secure_uninstall' => 1,
        ], [
            'added' => 'datetime',
        ]);
        $this->pluginId = (int) $connection->lastInsertId();

        $cacheSubscriber = Shopware()->Container()->get(CacheSubscriber::class);

        Utils::hijackProperty($cacheSubscriber, 'clearTags', []);
    }

    public function testConfigClearOnChange(): void
    {
        $this->createConfig('testConfigOnChange', 'test');
        $this->resetTags();

        $plugin = Shopware()->Models()->getRepository(Plugin::class)->find($this->pluginId);
        static::assertInstanceOf(Plugin::class, $plugin);
        $shop = Shopware()->Models()->getRepository(Shop::class)->getDefault();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'foo', $shop);

        static::assertNotEmpty(Utils::hijackAndReadProperty(Shopware()->Container()->get(CacheSubscriber::class), 'clearTags'));
    }

    public function testConfigOnChangeToDefault(): void
    {
        $this->createConfig('testConfigOnChange', 'test');
        $this->resetTags();

        $plugin = Shopware()->Models()->getRepository(Plugin::class)->find($this->pluginId);
        static::assertInstanceOf(Plugin::class, $plugin);

        $shop = Shopware()->Models()->getRepository(Shop::class)->getDefault();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'foo', $shop);

        $this->resetTags();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'test', $shop);

        static::assertNotEmpty(Utils::hijackAndReadProperty(Shopware()->Container()->get(CacheSubscriber::class), 'clearTags'));
    }

    protected function createConfig(string $elementName, string $value): void
    {
        $form = new Form();
        $form->setPluginId($this->pluginId);
        $form->setName(__CLASS__ . __FUNCTION__);
        $form->setLabel(__CLASS__ . __FUNCTION__);
        $form->setDescription('');

        $parent = Shopware()->Models()->getRepository(Form::class)->findOneBy([
            'name' => 'Other',
        ]);
        $form->setParent($parent);

        Shopware()->Models()->persist($form);
        $form->setElement('text', $elementName, ['value' => $value]);

        Shopware()->Models()->flush();
        Shopware()->Models()->clear();
    }

    private function resetTags(): void
    {
        $cacheSubscriber = Shopware()->Container()->get(CacheSubscriber::class);

        Utils::hijackProperty($cacheSubscriber, 'clearTags', []);
    }
}
