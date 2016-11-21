<?php

class Migrations_Migration795 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("UPDATE s_user SET encoder = 'StripTags';");
    }
}
