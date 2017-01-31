<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration919 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE `s_core_payment_instance`
  CHANGE `created_at` `created_at` DATETIME NOT NULL;
SQL;

        $this->addSql($sql);
    }
}
