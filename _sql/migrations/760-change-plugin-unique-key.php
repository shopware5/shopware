<?php

class Migrations_Migration760 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_core_plugins` ADD UNIQUE (`name`), DROP INDEX `namespace`;");
    }
}
