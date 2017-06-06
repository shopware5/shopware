<?php

class Migrations_Migration815 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
          UPDATE s_core_menu SET shortcut = "STRG + ALT + P" WHERE controller = "PluginManager" AND action = "Index"
SQL;
        $this->addSql($sql);
    }
}
