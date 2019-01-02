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
class Migrations_Migration1414 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add new ACL resource "benchmark"
        $this->addSql("INSERT IGNORE INTO `s_core_acl_resources` (name) VALUES ('benchmark');");

        // Fetch id for later use
        $this->addSql(
            sprintf(
                'SET @resourceId = (%s);',
                "SELECT id FROM `s_core_acl_resources` WHERE name = 'benchmark' LIMIT 1"
            )
        );

        // Add privileges corresponding to the benchmark resource
        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'read');");
        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'submit');");
        $this->addSql("INSERT IGNORE INTO `s_core_acl_privileges` (resourceID,name) VALUES (@resourceId, 'manage');");

        // Change s_core_menu controller definition, so the main menu entry won't be displayed when the user doesn't have read permission
        $this->addSql("UPDATE `s_core_menu` SET `controller` = 'Benchmark' WHERE `controller` = 'BenchmarkMenu';");
        // Change index action name to lowercase, so it can be addressed separately when reading menu snippets
        $this->addSql("UPDATE `s_core_menu` SET `action` = 'index' WHERE `controller` = 'Benchmark' AND `action` = 'Index';");
    }
}
