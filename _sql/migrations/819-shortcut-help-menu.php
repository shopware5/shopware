<?php

class Migrations_Migration819 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
UPDATE s_core_menu SET shortcut = "STRG + ALT + H" WHERE controller = "ShortCutMenu"
SQL;
        $this->addSql($sql);
    }
}
