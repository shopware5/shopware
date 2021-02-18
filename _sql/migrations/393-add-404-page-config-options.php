<?php
class Migrations_Migration393 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @formId = (SELECT `id` FROM `s_core_config_forms` WHERE name = 'Frontend100');");
        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'PageNotFoundDestination' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@elementId, '2', '\"Page not found\" destination', 'When the user requests a non-existent page, he will be shown the following page.' );
        ");

        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'PageNotFoundCode' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@elementId, '2', '\"Page not found\" error code', 'HTTP code used in \"Page not found\" responses' );
        ");
    }
}
