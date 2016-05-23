<?php

class Migrations_Migration715 extends Shopware\Components\Migrations\AbstractMigration
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

        $sql = <<<SQL
SET foreign_key_checks=0;

INSERT IGNORE INTO s_user_addresses_attributes (address_id, text1, text2, text3, text4, text5, text6)
(
  SELECT adr.id, migration.text1, migration.text2, migration.text3, migration.text4, migration.text5, migration.text6
  FROM s_user_addresses AS adr
  INNER JOIN s_user_addresses_migration AS migration ON adr.migration_id = migration.id
);

SET foreign_key_checks=1;
SQL;

        $this->addSql($sql);
    }
}
