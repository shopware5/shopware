<?php

class Migrations_Migration610 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
DELETE FROM s_core_subscribes WHERE listener LIKE "Shopware_Plugins_Core_System_Bootstrap::onInitResourceAdodb"
EOD;

        $this->addSql($sql);
    }
}
