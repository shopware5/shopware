<?php

class Migrations_Migration764 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // s_campaigns_articles
        $sql = <<<'EOD'
            ALTER TABLE `s_campaigns_articles`
            MODIFY COLUMN `articleordernumber` varchar(255) NOT NULL DEFAULT '0';
EOD;
        $this->addSql($sql);
    }
}
