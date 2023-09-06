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

class Migrations_Migration125 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
ALTER TABLE  `s_filter_values` ADD  `value_numeric`  DECIMAL( 10, 2 ) DEFAULT 0 NOT NULL;
UPDATE s_filter_values SET value_numeric = TRIM(REPLACE(value,',','.'))+0;

ALTER TABLE  `s_articles` ADD INDEX  `get_category_filters` (  `active` ,  `filtergroupID` );
ALTER TABLE  `s_filter_articles` ADD INDEX (  `valueID` );
ALTER TABLE  `s_filter_articles` ADD INDEX (  `articleID` );

ALTER TABLE  `s_filter_relations` ADD INDEX (  `groupID` );
ALTER TABLE  `s_filter_relations` ADD INDEX (  `optionID` );
ALTER TABLE  `s_filter_values` ADD INDEX (  `optionID` );

ALTER TABLE `s_filter_values` ADD INDEX  `filters_order_by_position` (  `optionID` ,  `position`, `id`  );
ALTER TABLE `s_filter_values` ADD INDEX  `filters_order_by_numeric` (  `optionID` ,  `value_numeric` ,  `id` );
ALTER TABLE `s_filter_values` ADD INDEX  `filters_order_by_alphanumeric` (  `optionID` ,  `value` ,  `id` );

INSERT IGNORE INTO `s_core_config_elements`
  (`name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
('displayFiltersInListings', 'i:1;', '', '', 'boolean', 1, 0, 0, NULL, NULL, ''),
('displayFilterArticleCount', 'i:1;', '', '', 'boolean', 1, 0, 0, NULL, NULL, ''),
('displayFiltersOnDetailPage', 'i:1;', '', '', 'boolean', 1, 0, 0, NULL, NULL, '');
EOD;

        $this->addSql($sql);
    }
}
