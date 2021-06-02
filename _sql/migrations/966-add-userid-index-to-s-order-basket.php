<?php
class Migrations_Migration966 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'SQL'
ALTER TABLE `s_order_basket` ADD INDEX(`userID`);
SQL;

        $this->addSql($sql);
    }
}
