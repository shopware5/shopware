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
class Migrations_Migration1401 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSQL("INSERT IGNORE INTO `s_core_menu` (`parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`) VALUES
            (69, 'Shopware BI', NULL, 'sprite-chart marketing--analyses', 1, 1, NULL, 'BenchmarkMenu', NULL, NULL);");

        $this->addSQL('SET @parentId = (SELECT `id` FROM `s_core_menu` WHERE name="Shopware BI");');

        $this->addSQL("INSERT IGNORE INTO `s_core_menu` (`parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`) VALUES
            (@parentId, 'Einstellungen', NULL, 'sprite-wrench-screwdriver settings--basic-settings', 1, 0, NULL, 'Benchmark', NULL, 'Settings'),
            (@parentId, 'Übersicht', NULL, 'sprite-report-paper marketing--analyses--overview', 0, 1, NULL, 'Benchmark', NULL, 'Index');");
    }
}
