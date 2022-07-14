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

class Migrations_Migration506 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('ALTER TABLE  `s_core_optin` ADD  `type` VARCHAR( 255 ) NULL DEFAULT NULL AFTER  `id`;');

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_mails` (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`, `dirty`) VALUES (NULL, NULL, 'sCONFIRMPASSWORDCHANGE', '{config name=mail}', '{config name=shopName}', 'Passwort vergessen - Passwort zurücksetzen', '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHallo,

im Shop {sShopURL} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen.

Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.

{sUrlReset}

Dieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden.

Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.

{config name=address}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', '', '0', '', '2', '', '0');
EOD;
        $this->addSql($sql);

        $this->addSql("DELETE FROM `s_core_config_mails` WHERE `name` = 'sPASSWORD';");
    }
}
