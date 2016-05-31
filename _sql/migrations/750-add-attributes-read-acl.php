<?php

class Migrations_Migration750 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'attributes' LIMIT 1);");
        $this->addSql("INSERT INTO s_core_acl_privileges (resourceID, name) VALUES (@resourceID, 'read');");
    }
}
