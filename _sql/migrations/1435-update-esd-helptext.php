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
class Migrations_Migration1435 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
UPDATE s_core_config_elements SET description = "<b>Achtung</b>: Diese Einstellung könnte die Funktionalität der ESD Downloads beeinträchtigen. Sobald die Dateien nicht mehr lokal sind, wird aus Sicherheitsgründen nur noch 'PHP' verwendet. <br><br>Downloadstrategie für ESD Dateien.<br><b>Link</b>: Unter Umständen unsicher, da der Link von außen eingesehen werden kann.<br><b>PHP</b>: Der Link kann nicht eingesehen werden. PHP liefert die Datei aus. Dies kann zu Problemen bei größeren Dateien führen.<br><b>X-Sendfile</b>: Unterstützt größere Dateien und ist sicher. Benötigt das X-Sendfile Apache Module. <br><b>X-Accel</b>: Äquivalent zum X-Sendfile. Benötigt das Nginx Modul X-Accel." WHERE name = "esdDownloadStrategy";
EOD;

        $this->addSql($sql);
        $this->addSql('UPDATE s_core_config_element_translations SET  `description` = \'<b>Warning</b>: Changing this setting might break ESD downloads. If not sure, use default (PHP)<br><br>Strategy to generate the download links for ESD files. If you use an external storage, the method PHP will always be used for security reasons.<br><b>Link</b>: Better performance, but possibly insecure <br><b>PHP</b>: More secure, but memory consuming, especially for bigger files <br><b>X-Sendfile</b>: Secure and lightweight, but requires X-Sendfile module and Apache2 web server <br><b>X-Accel</b>: Equivalent to X-Sendfile, but requires Nginx web server instead\' WHERE label = "Download strategy for ESD files"');
    }
}
