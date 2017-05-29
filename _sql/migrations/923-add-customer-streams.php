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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration923 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
CREATE TABLE `s_customer_streams` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `conditions` LONGTEXT COLLATE utf8_unicode_ci,
    `description` text COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);

        $sql = <<<'SQL'
CREATE TABLE `s_customer_search_index` (
    id int(11),
    email varchar(70) NULL,
    active int(1),
    accountmode int(11),
    firstlogin date,
    newsletter int(1),
    shop_id int(11),
    default_billing_address_id int(11),
    title varchar(100) NULL,
    salutation varchar(30) NULL,
    firstname varchar(255) NULL,
    lastname varchar(255) NULL,
    birthday date NULL,
    customernumber varchar(30) NULL,
    customer_group_id int(11) NULL,
    customer_group_name varchar(255) NULL,
    payment_id int(11),
    company varchar(255) NULL,
    department varchar(255) NULL,
    street varchar(255) NULL,
    zipcode varchar(255) NULL,
    city varchar(255) NULL,
    phone varchar(255) NULL,
    additional_address_line1 varchar(255) NULL,
    additional_address_line2 varchar(255) NULL,
    country_id int(11) NULL,
    country_name varchar(255) NULL,
    state_id int(11),
    age int(11) NULL,
    count_orders  int(11) NULL,
    invoice_amount_sum double NULL,
    invoice_amount_avg double NULL,
    invoice_amount_min double NULL,
    invoice_amount_max double NULL,
    first_order_time date NULL,
    last_order_time date NULL,
    has_canceled_orders int(1) NULL,
    product_avg double NULL,
    ordered_at_weekdays TEXT NULL,
    ordered_in_shops TEXT NULL,
    ordered_on_devices TEXT NULL,
    ordered_with_deliveries TEXT NULL,
    ordered_with_payments TEXT NULL,
    ordered_products LONGTEXT NULL,
    ordered_products_of_categories LONGTEXT NULL,
    ordered_products_of_manufacturer LONGTEXT NULL,
    index_time datetime NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->addSql($sql);

        $this->addSql('
            ALTER TABLE `s_emotion` ADD `customer_stream_ids` LONGTEXT NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `s_emotion` ADD `replacement` text COLLATE utf8_unicode_ci NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `s_emarketing_vouchers` ADD `customer_stream_ids` LONGTEXT NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `s_user` ADD `login_token` VARCHAR(250) NULL DEFAULT NULL;
        ');

        $this->addSql('
CREATE TABLE `s_customer_streams_mapping` (
  `stream_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  UNIQUE KEY `stream_id` (`stream_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');

        $this->addSql("
INSERT INTO `s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `disable_on_error`, `end`, `inform_template`, `inform_mail`, `pluginID`) VALUES
(NULL, 'Customer Stream refresh', 'RefreshCustomerStreams',	NULL, '', '2016-01-01 01:00:00', NULL, 7200, 1, 0, '2016-01-01 01:00:01', '', '', NULL);
");
    }
}
