<?php
class Migrations_Migration1464 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_element_translations SET label='Minimum search term length' WHERE label='Maximum search term length';
EOD;
        $this->addSql($sql);
    }
}
