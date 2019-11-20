<?php

class Migrations_Migration1646 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        DELETE FROM `s_core_config_forms` WHERE `name` = 'sitemap'
SQL;
        $this->addSql($sql);
    }
}
