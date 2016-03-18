<?php

class Migrations_Migration709 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_user_billingaddress` DROP fax;");
        $this->addSql("ALTER TABLE `s_order_billingaddress` DROP fax;");
    }
}
