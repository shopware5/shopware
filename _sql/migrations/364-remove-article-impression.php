<?php
class Migrations_Migration364 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        ALTER TABLE `s_articles_details` DROP `impressions`;
EOD;
        $this->addSql($sql);
    }
}



