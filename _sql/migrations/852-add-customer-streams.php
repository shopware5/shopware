<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration852 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
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
