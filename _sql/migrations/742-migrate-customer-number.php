<?php

class Migrations_Migration742 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_user` ADD `customernumber` VARCHAR(30) NULL DEFAULT NULL;");
        $this->addSql("ALTER TABLE `s_order_billingaddress` CHANGE `customernumber` `customernumber` VARCHAR(30) NULL DEFAULT NULL;");

        $this->addSql("
UPDATE s_user user,
		s_user_billingaddress billing
SET user.customernumber = billing.customernumber
WHERE user.id = billing.userID;
        ");
    }
}
