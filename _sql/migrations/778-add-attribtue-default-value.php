<?php

class Migrations_Migration778 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE `s_attribute_configuration` ADD `default_value` VARCHAR(500) NULL DEFAULT NULL AFTER `column_type`;');
    }
}
