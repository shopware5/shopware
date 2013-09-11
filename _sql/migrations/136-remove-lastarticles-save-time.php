<?php
class Migrations_Migration136 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'time' LIMIT 1);

DELETE FROM s_core_config_element_translations WHERE element_id = @elementId;
DELETE FROM s_core_config_values WHERE element_id = @elementId;
DELETE FROM s_core_config_elements WHERE id = @elementId;
EOD;

        $this->addSql($sql);
    }
}
