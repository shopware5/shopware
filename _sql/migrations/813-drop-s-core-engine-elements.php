<?php

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration813 extends AbstractMigration
{
    /**
     * @inheritdoc
     */
    public function up($modus)
    {
        $this->addSql('DROP TABLE IF EXISTS `s_core_engine_elements`;');
    }
}
