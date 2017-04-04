<?php

class Migrations_Migration811 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = "ALTER TABLE `s_order_shippingaddress` ADD `phone` VARCHAR(40) NULL DEFAULT NULL AFTER `city`;";
        $this->addSql($sql);
    }
}
