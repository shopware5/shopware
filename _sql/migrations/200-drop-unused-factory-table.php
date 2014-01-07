<?php
class Migrations_Migration200 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
DROP TABLE IF EXISTS s_core_factory;
EOD;

        $this->addSql($sql);
    }
}
