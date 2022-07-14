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

class Migrations_Migration776 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $rows = $this->getConnection()->query("show index from s_core_payment_data WHERE Non_unique = 0 AND Column_name IN ('payment_mean_id','user_id')")->rowCount();
        if ($rows === 2) {
            return;
        }

        $this->addSql('CREATE TABLE `s_core_payment_data_unique` LIKE `s_core_payment_data`');
        $this->addSql('ALTER TABLE `s_core_payment_data_unique` ADD UNIQUE (`payment_mean_id`, `user_id`)');
        $this->addSql('INSERT IGNORE INTO `s_core_payment_data_unique` SELECT * FROM `s_core_payment_data`');
        $this->addSql('DROP TABLE `s_core_payment_data`');
        $this->addSql('RENAME TABLE `s_core_payment_data_unique` TO `s_core_payment_data`');
    }
}
