<?php

class Migrations_Migration781 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Replace all empty 's_order.partnerID's with NULL
        $sql = <<<SQL
UPDATE `s_order`
SET `partnerID` = NULL
WHERE `partnerID` = '';
SQL;
        $this->addSql($sql);
    }
}
