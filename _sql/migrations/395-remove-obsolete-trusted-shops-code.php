<?php
class Migrations_Migration395 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = "SET @formId = (SELECT id FROM s_core_config_forms WHERE name LIKE 'TrustedShop' LIMI 1)";
        $this->addSql($sql);

        $sql = "SET @elementId = (SELECT id FROM s_core_config_elements WHERE form_id = @formId LIMI 1)";
        $this->addSql($sql);

        $sql = "DELETE FROM s_core_config_elements WHERE id = @elementId";
        $this->addSql($sql);

        $sql = "DELETE FROM s_core_config_element_translations WHERE element_id = @elementId";
        $this->addSql($sql);

        $sql = "DELETE FROM s_core_config_forms WHERE id = @formId";
        $this->addSql($sql);

        $sql = "DELETE FROM s_core_config_form_translations WHERE form_id = @formId";
        $this->addSql($sql);
    }
}
