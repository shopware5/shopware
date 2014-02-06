<?php
class Migrations_Migration225 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
            ALTER TABLE s_core_templates ADD  parent_id INT NULL DEFAULT NULL ;
EOD;
        $this->addSql($sql);
    }
}



