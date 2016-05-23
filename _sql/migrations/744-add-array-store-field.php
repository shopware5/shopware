<?php

class Migrations_Migration744 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_attribute_configuration` ADD `array_store` MEDIUMTEXT NULL DEFAULT NULL AFTER `plugin_id`;");
    }
}
