<?php

class Migrations_Migration816 extends Shopware\Components\Migrations\AbstractMigration
{
    private $mailer = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": "mail"},{"name": "smtp"},{"name": "file"}]});',
        'queryMode' => 'local'
    ];

    private $mailerSecure = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": ""},{"name": "ssl"},{"name": "tls"}]});',
        'queryMode' => 'local'
    ];

    private $mailerAuth = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": ""},{"name": "login"},{"name": "md5"},{"name": "crammd5"}]});',
        'queryMode' => 'local'
    ];

    public function up($modus)
    {
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_mailer"', serialize($this->mailer)));
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_smtpsecure"', serialize($this->mailerSecure)));
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_auth"', serialize($this->mailerAuth)));
    }
}
