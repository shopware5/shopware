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
class Migrations_Migration1415 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add new 'last id' columns, e.g. for products and customers
        // Rename "orders_batch_size" to "batch_size" so it works with all entities
        // Add 'locked' and 'type' column for locking a shop and saving the business model (e.g. 'b2b')
        // Add 'shopId' to unique keys
        $sql = <<<SQL
            ALTER TABLE `s_benchmark_config`
                ADD `last_customer_id` int(11) NOT NULL AFTER `last_order_id`,
                ADD `last_product_id` int(11) NOT NULL AFTER `last_customer_id`,ADD `shop_id` int(11) NOT NULL AFTER `id`,
                ADD `type` varchar(20) NULL AFTER `industry`,
                ADD `locked` DATETIME NULL AFTER `cached_template`,
                CHANGE `orders_batch_size` `batch_size` int(11) NOT NULL AFTER `last_product_id`,
                ADD UNIQUE `shop_id` (`shop_id`);
SQL;
        $this->addSql($sql);
    }
}
