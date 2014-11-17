<?php
class Migrations_Migration318 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $this->addSql("
            UPDATE `s_core_plugins`
            SET version = '1.0.1'
            WHERE name = 'PaymentMethods' AND author = 'shopware AG'
        ");
    }
}
