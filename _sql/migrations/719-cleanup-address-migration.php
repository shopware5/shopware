<?php

class Migrations_Migration719 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * @param string $modus
     * @return void
     */
    public function up($modus)
    {
        if ($modus == self::MODUS_INSTALL) {
            return;
        }

        $this->addSql("DROP TABLE IF EXISTS `s_user_addresses_migration`");
        $this->addSql("ALTER TABLE `s_user_addresses` DROP `migration_id`");
    }
}
