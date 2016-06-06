<?php

class Migrations_Migration722 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        if ($modus !== self::MODUS_INSTALL) {
            return;
        }
        $this->addSql('DELETE FROM `s_core_plugins` WHERE name = "License"');
    }
}
