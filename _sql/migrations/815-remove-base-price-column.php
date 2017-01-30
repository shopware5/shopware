<?php

class Migrations_Migration815 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $statement = $this->connection->prepare('SHOW COLUMNS FROM s_articles_prices LIKE :fieldname');
        $statement->execute([':fieldname' => 'baseprice']);

        $columnExists = $statement->fetch(PDO::FETCH_ASSOC);

        if ($columnExists) {
            $sql = 'ALTER TABLE `s_articles_prices` DROP `baseprice`;';
            $this->addSql($sql);
        }
    }
}
