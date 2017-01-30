<?php

class Migrations_Migration814 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = 'ALTER TABLE `s_articles_prices` DROP `baseprice`;';
        $this->addSql($sql);
    }
}
