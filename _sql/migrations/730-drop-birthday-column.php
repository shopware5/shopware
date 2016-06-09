<?php

class Migrations_Migration730 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // finally remove the old field
        $this->addSql("ALTER TABLE `s_user_billingaddress` DROP `birthday`");
    }
}
