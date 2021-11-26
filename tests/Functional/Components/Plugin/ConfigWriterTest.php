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
use Shopware\Components\Model\ModelManager;
use Shopware\Components\Plugin\Configuration\Layers\DefaultLayer;
use Shopware\Components\Plugin\Configuration\Layers\SubShopLayer;
use Shopware\Components\Plugin\Configuration\ReaderInterface;
use Shopware\Components\Plugin\Configuration\WriterInterface;
use Shopware\Models\Plugin\Plugin;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class ConfigWriterTest extends TestCase
{
    use DatabaseTransactionBehaviour;

    private const PLUGIN_NAME = 'swConfigWriterPluginTest';

    private const NUMBER_CONFIGURATION_NAME = 'numberConfiguration';

    private const ELEMENT_DEFAULT_VALUE = 1;

    private Connection $connection;

    private ModelManager $modelManager;

    private int $installationShopId;

    private int $subShopId;

    private int $languageShopId;

    private WriterInterface $configWriter;

    private ReaderInterface $configReader;

    public function setUp(): void
    {
        $this->modelManager = Shopware()->Container()->get('models');
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
        $plugin = $this->modelManager->find(Plugin::class, $this->connection->lastInsertId());
        static::assertInstanceOf(Plugin::class, $plugin);

        // setup plugin configuration
        $parentFormId = $this->connection
            ->executeQuery('SELECT id FROM s_core_config_forms WHERE `name` = ?', ['Core'])
            ->fetchOne();

        $this->connection->insert('s_core_config_forms', [
            'name' => self::PLUGIN_NAME,
            'label' => 'This is a config reader test plugin',
            'position' => 0,
            'plugin_id' => $plugin->getId(),
            'parent_id' => $parentFormId,
        ]);
        $formId = $this->connection->lastInsertId();

        $this->connection->insert('s_core_config_elements', [
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
        $this->installationShopId = 1;

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
        ]);
        $this->subShopId = (int) $this->connection->lastInsertId();

        $this->connection->insert('s_core_shops', [
            'name' => 'Sub Shop',
            'position' => 0,
            'hosts' => '',
            'secure' => 1,
            'customer_scope' => 0,
            '`default`' => 0,
            'active' => 1,
            'main_id' => $this->subShopId,
        ]);
        $this->languageShopId = (int) $this->connection->lastInsertId();

        $this->configWriter = Shopware()->Container()->get(WriterInterface::class);
        $this->configReader = Shopware()->Container()->get(ReaderInterface::class);
    }

    public function testWriteValueForInstallation(): void
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->installationShopId
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId)
        );
    }

    public function testWriteValueForSubShop(): void
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->subShopId
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId)
        );
    }

    public function testWriteValueForLanguageShop(): void
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->languageShopId
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId)
        );
    }

    public function testWriteDefaultValueForSubShop(): void
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->installationShopId
        );

        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->subShopId
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId)
        );
    }

    public function testWriteDefaultValueForLanguageShop(): void
    {
        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->subShopId
        );

        $this->configWriter->setByPluginName(
            self::PLUGIN_NAME,
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->languageShopId
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->installationShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => 2],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->subShopId)
        );

        static::assertSame(
            [self::NUMBER_CONFIGURATION_NAME => self::ELEMENT_DEFAULT_VALUE],
            $this->configReader->getByPluginName(self::PLUGIN_NAME, $this->languageShopId)
        );
    }

    public function testSubshopLayerCallsParentForDefaultShopConfig(): void
    {
        $subShopLayer = $this->getMockBuilder(SubShopLayer::class)
            ->onlyMethods(['getParent'])
            ->setConstructorArgs([
                $this->connection,
                $this->modelManager,
                new DefaultLayer($this->connection),
            ])
            ->getMock();

        $subShopLayer->expects(static::atLeastOnce())->method('getParent');

        $subShopLayer->writeValues(self::PLUGIN_NAME, $this->installationShopId, []);
    }
}
