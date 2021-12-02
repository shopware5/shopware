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

namespace Shopware\Tests\Functional\Components\Plugin;

use DateTime;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\Configuration\ReaderInterface;
use Shopware\Components\Plugin\Configuration\WriterInterface;
use Shopware\Components\Plugin\ConfigWriter;
use Shopware\Components\Plugin\DBALConfigReader;
use Shopware\Models\Plugin\Plugin;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class LegacyConfigWriterTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const PLUGIN_NAME = 'swConfigWriterPluginTest';

    private const NUMBER_CONFIGURATION_NAME = 'numberConfiguration';

    private const ELEMENT_DEFAULT_VALUE = 1;

    private Plugin $plugin;

    private Shop $installationShop;

    private Shop $subShop;

    private Shop $languageShop;

    private ConfigWriter $configWriter;

    private DBALConfigReader $configReader;

    public function setUp(): void
    {
        $connection = Shopware()->Container()->get('dbal_connection');
        $modelManager = Shopware()->Container()->get('models');

        // setup plugin
        $connection->insert('s_core_plugins', [
            'namespace' => 'Core',
            'name' => self::PLUGIN_NAME,
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
        $plugin = $modelManager->find(Plugin::class, $connection->lastInsertId());
        static::assertInstanceOf(Plugin::class, $plugin);
        $this->plugin = $plugin;

        // setup plugin configuration
        $parentFormId = $connection
            ->executeQuery('SELECT id FROM s_core_config_forms WHERE `name` = ?', ['Core'])
            ->fetchColumn();

        $connection->insert('s_core_config_forms', [
            'name' => self::PLUGIN_NAME,
            'label' => 'This is a config reader test plugin',
            'position' => 0,
            'plugin_id' => $this->plugin->getId(),
            'parent_id' => $parentFormId,
        ]);
        $formId = $connection->lastInsertId();

        $connection->insert('s_core_config_elements', [
            'form_id' => $formId,
            'name' => self::NUMBER_CONFIGURATION_NAME,
            'value' => serialize(self::ELEMENT_DEFAULT_VALUE),
            'type' => 'number',
            'required' => 0,
            'position' => 0,
            'scope' => 1,
        ]);

        // setup shops
        // assume shop by id 1 exists
        $shop = $modelManager->find(Shop::class, 1);
        static::assertInstanceOf(Shop::class, $shop);
        $this->installationShop = $shop;

        $connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
        ]);
        $subShop = $modelManager->find(Shop::class, $connection->lastInsertId());
        static::assertInstanceOf(Shop::class, $subShop);
        $this->subShop = $subShop;

        $connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
            'main_id' => $this->subShop->getId(),
        ]);
        $languageShop = $modelManager->find(Shop::class, $connection->lastInsertId());
        static::assertInstanceOf(Shop::class, $languageShop);
        $this->languageShop = $languageShop;

        $this->configWriter = new ConfigWriter(Shopware()->Container()->get(WriterInterface::class));
        $this->configReader = new DBALConfigReader(Shopware()->Container()->get(ReaderInterface::class));
    }

    public function testWriteValueForInstallation(): void
    {
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, 2, $this->installationShop);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );
    }

    public function testWriteValueForSubShop(): void
    {
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, 2, $this->subShop);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );
    }

    public function testWriteValueForLanguageShop(): void
    {
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, 2, $this->languageShop);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }

    public function testWriteDefaultValueForSubShop(): void
    {
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, 2, $this->installationShop);
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, self::ELEMENT_DEFAULT_VALUE, $this->subShop);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }

    public function testWriteDefaultValueForLanguageShop(): void
    {
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, 2, $this->subShop);
        $this->configWriter->saveConfigElement($this->plugin, self::NUMBER_CONFIGURATION_NAME, self::ELEMENT_DEFAULT_VALUE, $this->languageShop);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }
}
