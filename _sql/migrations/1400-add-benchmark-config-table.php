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
        $this->connection->query('CREATE TABLE IF NOT EXISTS `s_benchmark_config` (
             `id` BINARY(16) UNIQUE NOT NULL,
             `active` TINYINT(1) NOT NULL DEFAULT 0,
             `last_sent` DATETIME NOT NULL,
             `last_received` DATETIME NOT NULL,
             `last_order_id` INT(11) NOT NULL,
             `orders_batch_size` INT(11) NOT NULL,
             `industry` INT(11) DEFAULT NULL,
             `response_token` VARCHAR(200) DEFAULT NULL,
             `cached_template` LONGTEXT DEFAULT NULL,
             PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
            ');
    }
}
