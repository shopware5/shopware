<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */
class Migrations_Migration1444 extends Shopware\Components\Migrations\AbstractMigration
{
    private $mailer = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": "mail"},{"name": "smtp"},{"name": "file"}]});',
        'queryMode' => 'local',
    ];

    private $mailerSecure = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": "ssl"},{"name": "tls"}]});',
        'queryMode' => 'local',
    ];

    private $mailerAuth = [
        'displayValue' => 'name',
        'valueField' => 'name',
        'store' => 'new Ext.create("Ext.data.Store",{fields: [{name: "name", type: "string"}], data:[{"name": "login"},{"name": "plain"},{"name": "crammd5"}]});',
        'queryMode' => 'local',
    ];

    public function up($modus)
    {
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_mailer"', serialize($this->mailer)));
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_smtpsecure"', serialize($this->mailerSecure)));
        $this->addSql(sprintf('UPDATE s_core_config_elements SET options = \'%s\', type = "combo", description = NULL WHERE `name` = "mailer_auth"', serialize($this->mailerAuth)));
    }
}
