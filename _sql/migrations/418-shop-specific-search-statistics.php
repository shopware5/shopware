<?php
class Migrations_Migration418 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_statistics_search` ADD `shop_id` INT NULL DEFAULT NULL");
    }
}
