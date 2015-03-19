<?php

class Migrations_Migration475 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE `s_core_templates_config_elements` ADD `less_compatible` INT(1) NOT NULL DEFAULT '1' ;
SQL;
        $this->addSql($sql);
    }
}
