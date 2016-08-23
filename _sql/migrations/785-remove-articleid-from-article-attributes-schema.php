<?php

class Migrations_Migration785 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_articles_attributes` DROP FOREIGN KEY `s_articles_attributes_ibfk_1`;");
        $this->addSql("ALTER TABLE `s_articles_attributes` DROP COLUMN `articleID`;");
    }
}
