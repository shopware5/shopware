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

class Migrations_Migration113 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements`
  (`name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
('topSellerActive', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerValidationTime', 'i:100;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerRefreshStrategy', 'i:3;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerPseudoSales', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('seoRefreshStrategy', 'i:3;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('searchRefreshStrategy', 'i:3;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('showSupplierInCategories', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('propertySorting', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('disableShopwareStatistics', 'i:0;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('disableArticleNavigation', 'i:0;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('similarRefreshStrategy', 'i:3;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('similarActive', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('similarValidationTime', 'i:100;', '', '', '', 1, 0, 0, NULL, NULL, '');
EOD;

        $this->addSql($sql);
    }
}
