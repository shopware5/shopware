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
class Migrations_Migration1400 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSQL('CREATE TABLE `s_benchmark_config` (
             `active` TINYINT(1) DEFAULT NULL,
             `last_sent` DATETIME NOT NULL,
             `last_received` DATETIME NOT NULL,
             `last_order_id` INT(11) NOT NULL,
             `orders_batch_size` INT(11) NOT NULL,
             `business` INT(11) DEFAULT NULL,
             `terms_accepted` TINYINT(1) NOT NULL,
             `response_token` VARCHAR(200) DEFAULT NULL,
             `cached_template` LONGTEXT DEFAULT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci
            ');

        // Only inserts this row, when table is empty - we have no unique key for 'Insert ignore'
        $this->addSql(
            'INSERT INTO `s_benchmark_config` (`active`, `last_sent`, `last_received`, `last_order_id`, `orders_batch_size`, `business`, `terms_accepted`, `cached_template`)
                SELECT NULL, "1990-01-01 00:00:00", "1990-01-01 00:00:00", 0, 1000, NULL, 0, NULL
                FROM dual
                WHERE NOT EXISTS (SELECT * FROM `s_benchmark_config`);'
        );
    }
}
