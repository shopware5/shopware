<?php

class Migrations_Migration756 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE s_categories DROP noviewselect");
    }
}
