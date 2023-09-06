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

class Migrations_Migration409 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<EOD
ALTER TABLE `s_filter_values` ADD `media_id` INT NULL DEFAULT NULL , ADD INDEX (`media_id`) ;
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, 0, 'showImmediateDeliveryFacet', 'i:0;', '', '', 'boolean', 1, 0, 0, NULL, NULL, NULL),
(NULL, 0, 'showShippingFreeFacet',      'i:0;', '', '', 'boolean', 1, 0, 0, NULL, NULL, NULL),
(NULL, 0, 'showPriceFacet',             'i:0;', '', '', 'boolean', 1, 0, 0, NULL, NULL, NULL),
(NULL, 0, 'showVoteAverageFacet',       'i:0;', '', '', 'boolean', 1, 0, 0, NULL, NULL, NULL),
(NULL, 0, 'defaultListingSorting',      'i:1;', '', '', '', 1, 0, 0, NULL, NULL, NULL);
EOD;

        $this->addSql($sql);

        $sql = "DELETE FROM s_core_config_elements WHERE name = 'orderbydefault'";
        $this->addSql($sql);

        $sql = 'UPDATE s_filter SET sortmode = 0 WHERE sortmode = 2;';
        $this->addSql($sql);
    }
}
