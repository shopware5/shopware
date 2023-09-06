<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace SwagTest\Migrations;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Comparator;
use Doctrine\DBAL\Schema\Schema;
use Shopware\Components\Migrations\AbstractPluginMigration;

class Migration2 extends AbstractPluginMigration
{
    public function up($modus): void
    {
        $this->addSql(implode(';', (new Comparator())->compare($this->getOldSchema(), $this->getSchema())->toSql(new MySqlPlatform())));
    }

    public function down(bool $keepUserData): void
    {
        if ($keepUserData) {
            return;
        }

        $this->addSql(implode(';', (new Comparator())->compare($this->getSchema(), $this->getOldSchema())->toSql(new MySqlPlatform())));
    }

    private function getOldSchema(): Schema
    {
        $schema = new Schema();
        $migration = $schema->createTable('s_test_table');
        $migration->addColumn('name', 'string', ['length' => 255]);

        return $schema;
    }

    private function getSchema(): Schema
    {
        $schema = new Schema();
        $migration = $schema->createTable('s_test_table');
        $migration->addColumn('name', 'string', ['length' => 255]);
        $migration->addColumn('newcolumn', 'string', ['length' => 255]);

        return $schema;
    }
}
