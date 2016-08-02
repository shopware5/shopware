<?php

class Migrations_Migration782 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE `s_emarketing_partner` ADD INDEX `idcode` (`idcode`);");
    }
}
