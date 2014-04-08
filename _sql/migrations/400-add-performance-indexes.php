<?php
class Migrations_Migration400 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
ALTER TABLE `s_articles_prices` ADD INDEX `all_product_prices_query`
(`articledetailsID`, `pricegroup`, `from`);
ALTER TABLE `s_articles_prices` ADD INDEX `cheapest_price_query`
(`articleID`, `pricegroup`, `price`);
EOD;

        $this->addSql($sql);
    }
}
