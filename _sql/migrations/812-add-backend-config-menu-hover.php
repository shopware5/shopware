<?php

class Migrations_Migration812 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `label` = \'Backend\');');

        $this->addSql('INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`) VALUES
            (@formId, \'useBackendMenuHoverButton\', \'b:1;\', \'Hover im Menü aktiv?\', null, \'checkbox\', 0, 0, 0);');

        $this->addSql('SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = \'useBackendMenuHoverButton\' LIMIT 1)');

        $this->addSql('INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`)
        VALUES (@elementId, \'2\', \'Enable Hover Menu items?\');');
    }
}