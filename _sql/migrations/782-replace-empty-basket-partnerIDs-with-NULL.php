<?php

class Migrations_Migration782 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Replace all empty 's_order_basket.partnerID's with NULL
        $sql = <<<SQL
UPDATE `s_order_basket`
SET `partnerID` = NULL
WHERE `partnerID` = '';
SQL;
        $this->addSql($sql);
    }
}
