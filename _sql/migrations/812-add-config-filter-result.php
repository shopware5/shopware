<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration812 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<'SQL'
INSERT INTO s_core_config_elements (form_id, name, value, label, description, type, required, position, scope, options)
VALUES (0, 'instantFilterResult', 'i:0;', '', '', 'boolean', 1, 0, 0, NULL);
SQL;
        $this->addSql($sql);
    }
}
