<?php
class Migrations_Migration376 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'Frontend79');

INSERT IGNORE INTO `s_core_config_elements`
  (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
VALUES
(@formId, 'dynamicAdditionalTextValues', 'b:1;', 'Generiere Variantentexte dynamisch', 'Wenn diese Option gewählt ist, werden leere Variantentexte automatisch durch die Informationen der "Konfigurator Gruppen" erzeugt.', 'boolean', 0, 0, 0, NULL, NULL);


SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'dynamicAdditionalTextValues' LIMIT 1);

INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@elementId, '2', 'Dynamically generate variant texts', 'If active, empty variant texts will be automatically filled with data from the configurator groups' );

EOD;

        $this->addSql($sql);
    }
}
