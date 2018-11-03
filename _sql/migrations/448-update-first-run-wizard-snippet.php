<?php
class Migrations_Migration448 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            UPDATE `s_core_config_elements` SET label = '\'First Run Wizard\' beim Aufruf des Backends starten'
            WHERE name = 'firstRunWizardEnabled';
EOD;
        $this->addSql($sql);
    }
}
