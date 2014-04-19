<?php

class Migrations_Migration401 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
ALTER TABLE `s_articles_vote`ADD INDEX `vote_average` (`points`);
ALTER TABLE `s_articles_prices` ADD INDEX `product_prices` (`articledetailsID`, `from`);
EOD;
        $this->addSql($sql);
    }
}
