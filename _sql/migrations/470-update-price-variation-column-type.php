<?php
class Migrations_Migration470 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
            ALTER TABLE `s_article_configurator_price_variations`
            CHANGE `options` `options` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);
    }
}
