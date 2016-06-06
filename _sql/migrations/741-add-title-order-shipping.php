<?php

class Migrations_Migration741 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_order_shippingaddress` ADD `title` VARCHAR(100) NULL DEFAULT NULL AFTER `additional_address_line2`;');
    }
}
