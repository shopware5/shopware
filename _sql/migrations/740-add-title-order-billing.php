<?php

class Migrations_Migration740 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_order_billingaddress` ADD `title` VARCHAR(100) NULL DEFAULT NULL AFTER `additional_address_line2`;');
    }
}
