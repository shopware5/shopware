<?php

class Migrations_Migration2000 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_core_shops` DROP `secure_host`, DROP `secure_base_path`, DROP `always_secure`');
    }
}
