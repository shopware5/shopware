<?php

class Migrations_Migration755 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("
UPDATE s_user user, s_user_billingaddress billing
SET user.customernumber = billing.customernumber
WHERE user.id = billing.userID;
        ");
    }
}
