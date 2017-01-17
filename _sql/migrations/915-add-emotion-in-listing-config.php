<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration915 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $sql = <<<SQL
ALTER TABLE `s_emotion` ADD `listing_visibility` varchar(50) DEFAULT 'only_start' NOT NULL
SQL;

        $this->addSql($sql);
    }
}
