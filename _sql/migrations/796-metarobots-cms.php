<?php

class Migrations_Migration796 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_cms_static` ADD `meta_robots` VARCHAR(255) NOT NULL AFTER `meta_description`;');
    }
}
