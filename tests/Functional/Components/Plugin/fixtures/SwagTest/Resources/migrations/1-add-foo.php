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

namespace SwagTest\Migrations;

use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Schema\Schema;
use Shopware\Components\Migrations\AbstractPluginMigration;

class Migration1 extends AbstractPluginMigration
{
    public function up($modus): void
    {
        $this->addSql(implode(';', $this->getSchema()->toSql(new MySqlPlatform())));
    }

    public function down(bool $keepUserData): void
    {
        if ($keepUserData) {
            return;
        }

        $this->addSql(implode(';', $this->getSchema()->toDropSql(new MySqlPlatform())));
    }

    private function getSchema(): Schema
    {
        $schema = new Schema();
        $migration = $schema->createTable('s_test_table');
        $migration->addColumn('name', 'string', ['length' => 255]);

        return $schema;
    }
}
