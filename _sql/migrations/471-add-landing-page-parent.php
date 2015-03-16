<?php
class Migrations_Migration471 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
        ALTER TABLE `s_emotion` ADD `parent_id` INT NULL DEFAULT NULL ;
SQL;
        $this->addSql($sql);
    }
}
