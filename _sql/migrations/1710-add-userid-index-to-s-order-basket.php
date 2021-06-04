<?php
class Migrations_Migration1710 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus): void
    {
        $sql = <<<'SQL'
ALTER TABLE `s_order_basket` ADD INDEX(`userID`);
SQL;

        $this->addSql($sql);
    }
}
