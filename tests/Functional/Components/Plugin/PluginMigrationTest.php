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

use Shopware\Bundle\PluginInstallerBundle\Service\PluginInitializer;
use Shopware\Components\Migrations\AbstractPluginMigration;
use Shopware\Components\Migrations\PluginMigrationManager;

class PluginMigrationTest extends TestCase
{
    /**
     * @var array
     */
    private $plugins;

    protected function setUp(): void
    {
        $pdo = Shopware()->Container()->get('db_connection');
        $initializer = new PluginInitializer($pdo, [
            'ShopwarePlugins' => __DIR__ . '/fixtures/',
        ]);
        $this->plugins = $initializer->initializePlugins();
    }

    public function testMigrationWithDown(): void
    {
        $manager = new PluginMigrationManager(
            Shopware()->Container()->get('db_connection'),
            $this->plugins['SwagTest'],
            Shopware()->Container()->get('pluginlogger')
        );

        $manager->run(AbstractPluginMigration::MODUS_INSTALL);

        $this->assertTableExists('s_test_table');
        $this->assertMigrationExecuted('SwagTest', 1);

        $manager->run(AbstractPluginMigration::MODUS_UNINSTALL);

        $this->assertMigrationNotExecuted('SwagTest', 1);
        $this->assertTableNotExists('s_test_table');
    }

    public function testMigrationWithDownWithKeepUserData(): void
    {
        $manager = new PluginMigrationManager(
            Shopware()->Container()->get('db_connection'),
            $this->plugins['SwagTest'],
            Shopware()->Container()->get('pluginlogger')
        );

        $manager->run(AbstractPluginMigration::MODUS_INSTALL);

        $this->assertTableExists('s_test_table');
        $this->assertMigrationExecuted('SwagTest', 1);

        $manager->run(AbstractPluginMigration::MODUS_UNINSTALL, true);

        $this->assertTableExists('s_test_table');
        $this->assertMigrationNotExecuted('SwagTest', 1);

        Shopware()->Db()->exec('DROP TABLE s_test_table');
    }

    public function testMigrationWithDownWithUpdate(): void
    {
        $manager = new PluginMigrationManager(
            Shopware()->Container()->get('db_connection'),
            $this->plugins['SwagTest'],
            Shopware()->Container()->get('pluginlogger')
        );

        $manager->run(AbstractPluginMigration::MODUS_INSTALL);

        $this->assertTableExists('s_test_table');
        $this->assertMigrationExecuted('SwagTest', 1);

        copy(__DIR__ . '/fixtures/2-rename-foo.php', __DIR__ . '/fixtures/SwagTest/Resources/migrations/2-rename-foo.php');

        $manager->run(AbstractPluginMigration::MODUS_UPDATE);
        $this->assertTableExists('s_test_table');
        $this->assertTableColumnExists('s_test_table', 'name');
        $this->assertTableColumnExists('s_test_table', 'newcolumn');

        $this->assertMigrationExecuted('SwagTest', 2);

        $manager->run(AbstractPluginMigration::MODUS_UNINSTALL);

        unlink(__DIR__ . '/fixtures/SwagTest/Resources/migrations/2-rename-foo.php');

        $this->assertTableNotExists('s_test_table');
        $this->assertMigrationNotExecuted('SwagTest', 1);
        $this->assertMigrationNotExecuted('SwagTest', 2);
    }

    private function assertMigrationExecuted(string $pluginName, int $version): void
    {
        static::assertTrue((bool) Shopware()->Db()->fetchOne('SELECT 1 FROM s_plugin_schema_version WHERE plugin_name = ? AND version = ? AND complete_date IS NOT NULL', [$pluginName, $version]));
    }

    private function assertMigrationNotExecuted(string $pluginName, int $version): void
    {
        static::assertFalse((bool) Shopware()->Db()->fetchOne('SELECT 1 FROM s_plugin_schema_version WHERE plugin_name = ? AND version = ? AND complete_date IS NOT NULL', [$pluginName, $version]));
    }

    private function assertTableExists(string $table): void
    {
        static::assertTrue((bool) Shopware()->Db()->fetchOne('SHOW TABLES like ?', [$table]));
    }

    private function assertTableNotExists(string $table): void
    {
        static::assertFalse((bool) Shopware()->Db()->fetchOne('SHOW TABLES like ?', [$table]));
    }

    private function assertTableColumnExists(string $table, string $column)
    {
        static::assertTrue((bool) Shopware()->Db()->fetchOne(sprintf('show columns FROM %s WHERE `Field` = ?', $table), [$column]));
    }
}
