<?php

class Migrations_Migration746 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_order_billingaddress` CHANGE `ustid` `ustid` VARCHAR(50) NULL;');
        $this->addSql('ALTER TABLE `s_user_billingaddress` CHANGE `ustid` `ustid` VARCHAR(50) NULL;');
    }
}
