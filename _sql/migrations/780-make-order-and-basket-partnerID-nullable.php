<?php

class Migrations_Migration780 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Make 's_order.partnerID' field nullable
        $sql = <<<SQL
ALTER TABLE `s_order`
    CHANGE `partnerID` `partnerID` VARCHAR(255) NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        // Make 's_order_basket.partnerID' field nullable and increase its field length
        // to match 's_order.partnerID'
        $sql = <<<SQL
ALTER TABLE `s_order_basket`
    CHANGE `partnerID` `partnerID` VARCHAR(255) NULL DEFAULT NULL;
SQL;
        $this->addSql($sql);

        // Replace all empty 'partnerID's with NULL
        $sql = <<<SQL
UPDATE `s_order`
SET `partnerID` = NULL
WHERE `partnerID` = '';
SQL;
        $this->addSql($sql);
    }
}
