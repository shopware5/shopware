<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration852 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $this->addSql("
CREATE TABLE multiplier ( `id` int(11) unsigned NOT NULL AUTO_INCREMENT, PRIMARY KEY (`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `multiplier` (`id`) VALUES (1), (2), (3), (4);
        ");

        $sql = <<<SQL
CREATE TABLE `s_customer_streams` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
    `conditions` text COLLATE utf8_unicode_ci,
    `description` text COLLATE utf8_unicode_ci,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $this->addSql($sql);


        $sql = <<<SQL
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
    product_avg double NULL,
    products LONGTEXT NULL,
    categories LONGTEXT NULL,
    manufacturers LONGTEXT NULL,
    interests LONGTEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;

        $this->addSql($sql);


        $this->addSql("SET @menuId = (SELECT id FROM s_core_menu WHERE name = 'Kunden' LIMIT 1)");

        $this->addSql("
INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`)
VALUES (NULL, @menuId, 'Customer Stream', NULL, 'sprite-product-streams', '0', '1', NULL, 'CustomerStream', '', 'Index');
        ");

        $this->addSql("
            ALTER TABLE `s_emotion` ADD `customer_stream_id` int(11) unsigned NULL DEFAULT NULL;
        ");

        $this->addSql("
            ALTER TABLE `s_emotion` ADD `replacement` text COLLATE utf8_unicode_ci NULL DEFAULT NULL;
        ");

        $this->addSql("
CREATE TABLE `s_customer_streams_mapping` (
  `stream_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  UNIQUE KEY `stream_id` (`stream_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
        ");
    }
}
