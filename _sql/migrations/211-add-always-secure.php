<?php
class Migrations_Migration211 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE  `s_core_shops` ADD  `always_secure` INT( 1 ) NOT NULL DEFAULT  '0';
EOD;
        $this->addSql($sql);
    }
}
