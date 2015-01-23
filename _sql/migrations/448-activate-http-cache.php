<?php
class Migrations_Migration448 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        if ($modus === self::MODUS_INSTALL) {
            $this->addSql("UPDATE s_core_plugins SET active = 1 WHERE name = 'HttpCache'");
        }
    }
}