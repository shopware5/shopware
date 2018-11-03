<?php
class Migrations_Migration430 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_cms_static` ADD `shop_ids` VARCHAR(255) NULL;");

        $this->addSql("ALTER TABLE `s_cms_support` ADD `shop_ids` VARCHAR(255) NULL;");
    }
}
