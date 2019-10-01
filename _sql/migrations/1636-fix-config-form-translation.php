<?php

class Migrations_Migration1636 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
        SET @elementId = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'Service');
        
        UPDATE s_core_config_form_translations
        SET label = 'Maintenance'
        WHERE form_id = @elementId;
SQL;
        $this->addSql($sql);
    }
}