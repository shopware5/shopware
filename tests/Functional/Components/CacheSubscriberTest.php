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

namespace Shopware\Tests\Functional\Components;

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

    private const PLUGIN_ID = 10;

    protected function setUp(): void
    {
        /** @var CacheSubscriber $cacheSubscriber */
        $cacheSubscriber = Shopware()->Container()->get(CacheSubscriber::class);

        Utils::hijackProperty($cacheSubscriber, 'clearTags', []);
    }

    public function testConfigClearOnChange(): void
    {
        $this->createConfig('testConfigOnChange', 'test');
        $this->resetTags();

        $plugin = new Plugin();
        Utils::hijackProperty($plugin, 'id', self::PLUGIN_ID);

        $shop = Shopware()->Models()->getRepository(Shop::class)->getDefault();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'foo', $shop);

        static::assertNotEmpty(Utils::hijackAndReadProperty(Shopware()->Container()->get(CacheSubscriber::class), 'clearTags'));
    }

    public function testConfigOnChangeToDefault(): void
    {
        $this->createConfig('testConfigOnChange', 'test');
        $this->resetTags();

        $plugin = new Plugin();
        Utils::hijackProperty($plugin, 'id', self::PLUGIN_ID);

        $shop = Shopware()->Models()->getRepository(Shop::class)->getDefault();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'foo', $shop);

        $this->resetTags();
        Shopware()->Container()->get('shopware.plugin.config_writer')->saveConfigElement($plugin, 'testConfigOnChange', 'test', $shop);

        static::assertNotEmpty(Utils::hijackAndReadProperty(Shopware()->Container()->get(CacheSubscriber::class), 'clearTags'));
    }

    protected function createConfig(string $elementName, string $value): void
    {
        $form = new Form();
        $form->setPluginId(self::PLUGIN_ID);
        $form->setName(__CLASS__ . __FUNCTION__);
        $form->setLabel(__CLASS__ . __FUNCTION__);
        $form->setDescription('');

        /** @var Form $parent */
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
        /** @var CacheSubscriber $cacheSubscriber */
        $cacheSubscriber = Shopware()->Container()->get(CacheSubscriber::class);

        Utils::hijackProperty($cacheSubscriber, 'clearTags', []);
    }
}
