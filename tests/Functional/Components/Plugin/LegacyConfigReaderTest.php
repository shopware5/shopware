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
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Shopware\Components\Plugin\Configuration\ReaderInterface;
use Shopware\Components\Plugin\DBALConfigReader;
use Shopware\Models\Shop\Shop;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class LegacyConfigReaderTest extends TestCase
{
    use DatabaseTransactionBehaviour;
    private const PLUGIN_NAME = 'swConfigReaderPluginTest';

    private const NUMBER_CONFIGURATION_NAME = 'numberConfiguration';

    private Connection $connection;

    private DBALConfigReader $configReader;

    private int $configElementId;

    private Shop $installationShop;

    private Shop $subShop;

    private Shop $languageShop;

    public function setUp(): void
    {
        $modelManager = Shopware()->Container()->get('models');
        $this->connection = Shopware()->Container()->get('dbal_connection');

        // setup plugin
        $this->connection->insert('s_core_plugins', [
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
        $pluginId = $this->connection->lastInsertId();

        // setup plugin configuration
        $parentFormId = $this->connection
            ->executeQuery('SELECT id FROM s_core_config_forms WHERE `name` = ?', ['Core'])
            ->fetchColumn();

        $this->connection->insert('s_core_config_forms', [
            'name' => self::PLUGIN_NAME,
            'label' => 'This is a config reader test plugin',
            'position' => 0,
            'plugin_id' => $pluginId,
            'parent_id' => $parentFormId,
        ]);
        $formId = $this->connection->lastInsertId();

        $this->connection->insert('s_core_config_elements', [
            'form_id' => $formId,
            'name' => self::NUMBER_CONFIGURATION_NAME,
            'value' => serialize(1),
            'type' => 'number',
            'required' => 0,
            'position' => 0,
            'scope' => 1,
        ]);
        $this->configElementId = (int) $this->connection->lastInsertId();

        // setup shops
        // assume shop by id 1 exists
        $shop = $modelManager->find(Shop::class, 1);
        static::assertInstanceOf(Shop::class, $shop);
        $this->installationShop = $shop;

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
        ]);
        $subShop = $modelManager->find(Shop::class, $this->connection->lastInsertId());
        static::assertInstanceOf(Shop::class, $subShop);
        $this->subShop = $subShop;

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
            'main_id' => $this->subShop->getId(),
        ]);
        $languageShop = $modelManager->find(Shop::class, $this->connection->lastInsertId());
        static::assertInstanceOf(Shop::class, $languageShop);
        $this->languageShop = $languageShop;

        $this->configReader = new DBALConfigReader(Shopware()->Container()->get(ReaderInterface::class));
    }

    public function testReadElementDefault(): void
    {
        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 1],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 1],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );
    }

    public function testReadValueForInstallation(): void
    {
        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(2),
            'shop_id' => $this->installationShop->getId(),
        ]);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }

    public function testReadValueForSubShop(): void
    {
        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(2),
            'shop_id' => $this->installationShop->getId(),
        ]);

        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(3),
            'shop_id' => $this->subShop->getId(),
        ]);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 3],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 3],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }

    public function testReadValueForLanguageShop(): void
    {
        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(2),
            'shop_id' => $this->installationShop->getId(),
        ]);

        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(3),
            'shop_id' => $this->subShop->getId(),
        ]);

        $this->connection->insert('s_core_config_values', [
            'element_id' => $this->configElementId,
            'value' => serialize(4),
            'shop_id' => $this->languageShop->getId(),
        ]);

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 3],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShop)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 4],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShop)
        );
    }
}
