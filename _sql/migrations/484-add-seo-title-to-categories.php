<?php

class Migrations_Migration484 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE `s_categories` ADD `meta_title` VARCHAR(255) NULL DEFAULT NULL ;
SQL;
        $this->addSql($sql);
    }
}
