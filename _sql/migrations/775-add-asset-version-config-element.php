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

class Migrations_Migration775 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->createUniqueIndex();

        $sql = <<<SQL
INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES (NULL, '0', 'assetTimestamp', 'i:0;', '', 'Cache invalidation timestamp for assets', '', '0', '0', '1')
SQL;
        $this->addSql($sql);
    }

    private function createUniqueIndex()
    {
        $this->addSql('CREATE TABLE `s_core_config_values_unique` LIKE `s_core_config_values`');
        $this->addSql('ALTER TABLE `s_core_config_values_unique` ADD UNIQUE `element_id_shop_id` (`element_id`, `shop_id`)');
        $this->addSql('INSERT IGNORE INTO `s_core_config_values_unique` SELECT * FROM `s_core_config_values`');
        $this->addSql('DROP TABLE `s_core_config_values`');
        $this->addSql('RENAME TABLE `s_core_config_values_unique` TO `s_core_config_values`');
    }
}
