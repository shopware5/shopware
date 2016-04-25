<?php


class Migrations_Migration724 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = 'UPDATE s_core_menu SET onclick = "window.open(\'http://forum.shopware.com\',\'Shopware\',\'width=800,height=550,scrollbars=yes\')", controller = NULL WHERE name = "Zum Forum"';
        $this->addSql($sql);
    }
}
