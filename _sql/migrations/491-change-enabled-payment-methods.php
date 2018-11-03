<?php
class Migrations_Migration491 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus !== self::MODUS_INSTALL) {
            return;
        }

        $sql = "UPDATE s_core_paymentmeans SET `active` = 0 WHERE `name` != 'prepayment';";
        $this->addSql($sql);
    }
}
