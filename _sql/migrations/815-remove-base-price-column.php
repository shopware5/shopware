<?php

class Migrations_Migration815 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->connection->query('SELECT * FROM s_articles_prices');
        $price = $statement->fetch(PDO::FETCH_ASSOC);

        if (array_key_exists('baseprice', $price)) {
            $sql = 'ALTER TABLE `s_articles_prices` DROP `baseprice`;';
            $this->addSql($sql);
        }
    }
}
