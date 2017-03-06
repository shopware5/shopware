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

class Migrations_Migration1000 extends AbstractMigration
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
    `conditions` text COLLATE utf8_unicode_ci,
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
    shopId int(11),
    default_billing_address_id int(11),
    title varchar(100) NULL,
    salutation varchar(30) NULL,
    firstname varchar(255) NULL,
    lastname varchar(255) NULL,
    birthday date NULL,
    customernumber varchar(30) NULL,
    customerGroupId int(11),
    customerGroup varchar(255) NULL,
    paymentId int(11),
    payment varchar(255) NULL,
    shop varchar(255) NULL,
    company varchar(255) NULL,
    department varchar(255) NULL,
    street varchar(255) NULL,
    zipcode varchar(255) NULL,
    city varchar(255) NULL,
    phone varchar(255) NULL,
    additional_address_line1 varchar(255) NULL,
    additional_address_line2 varchar(255) NULL,
    countryId int(11),
    country varchar(255) NULL,
    stateId int(11),
    state varchar(255) NULL,
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
    weekdays TEXT NULL,
    shops TEXT NULL,
    devices TEXT NULL,
    deliveries TEXT NULL,
    payments TEXT NULL,
    products LONGTEXT NULL,
    categories LONGTEXT NULL,
    manufacturers LONGTEXT NULL,
    interests LONGTEXT NULL,
    index_time datetime DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->addSql($sql);

        $this->addSql("SET @menuId = (SELECT id FROM s_core_menu WHERE name = 'Kunden' LIMIT 1)");

        $this->addSql("
INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (NULL, @menuId, 'Customer Stream', NULL, 'sprite-product-streams', '0', '1', NULL, 'CustomerStream', '', 'Index');
        ");

        $this->addSql('
            ALTER TABLE `s_emotion` ADD `customer_stream_id` int(11) unsigned NULL DEFAULT NULL;
        ');

        $this->addSql('
            ALTER TABLE `s_emotion` ADD `replacement` text COLLATE utf8_unicode_ci NULL DEFAULT NULL;
        ');

        $this->addSql('
CREATE TABLE `s_customer_streams_mapping` (
  `stream_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  UNIQUE KEY `stream_id` (`stream_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ');
    }
}
