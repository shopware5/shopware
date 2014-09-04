<?php
class Migrations_Migration399 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            DROP TABLE IF EXISTS `s_core_rewrite`;
EOD;

        $this->addSql($sql);
    }
}
