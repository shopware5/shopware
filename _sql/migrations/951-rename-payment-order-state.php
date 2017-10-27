<?php
class Migrations_Migration951 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("UPDATE s_core_states SET `name` = 'the_payment_has_been_ordered', `description` = 'Die Zahlung wurde angewiesen.' WHERE `name` = 'the_payment_has_been_ordered_by_hanseatic_bank' AND `group` LIKE 'payment';");
    }
}
