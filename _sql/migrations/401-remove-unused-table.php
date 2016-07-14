<?php
class Migrations_Migration401 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            DROP TABLE IF EXISTS `s_cms_groups`;
EOD;

        $this->addSql($sql);
    }
}
