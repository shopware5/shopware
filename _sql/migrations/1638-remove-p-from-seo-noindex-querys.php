<?php

class Migrations_Migration1638 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_UPDATE){
            return;
        }

        $sql = <<<SQL
        UPDATE s_core_config_elements
        SET value = 's:42:"sPerPage,sSupplier,sFilterProperties,n,s,f";'
        WHERE name = 'seoqueryblacklist';
SQL;

        $this->addSql($sql);
    }
}
