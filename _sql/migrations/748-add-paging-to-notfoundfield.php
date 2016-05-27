<?php

class Migrations_Migration748 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('UPDATE s_core_config_elements SET `options` = \'a:5:{s:5:"store";s:35:"base.PageNotFoundDestinationOptions";s:12:"displayField";s:4:"name";s:10:"valueField";s:2:"id";s:10:"allowBlank";s:5:"false";s:8:"pageSize";i:25;}\' WHERE `name` = "PageNotFoundDestination"');
    }
}
