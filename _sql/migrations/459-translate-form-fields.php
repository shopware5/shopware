<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Migrations_Migration459 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<SQL
            UPDATE s_cms_support SET text = '<p>Sie erhalten von uns nach dem Absenden dieses Formulars innerhalb kurzer Zeit eine R&uuml;ckantwort mit einer RMA-Nummer und weiterer Vorgehensweise.</p>\r\n<p>Bitte f&uuml;llen Sie die Fehlerbeschreibung ausf&uuml;hrlich aus, Sie m&uuml;ssen diese dann nicht mehr dem Paket beilegen.</p>'
            WHERE text = '<h1>Defektes Produkt - f&uuml;r Endkunden und H&auml;ndler</h1>\r\n<p>Sie erhalten von uns nach dem Absenden dieses Formulars innerhalb kurzer Zeit eine R&uuml;ckantwort mit einer RMA-Nummer und weiterer Vorgehensweise.</p>\r\n<p>Bitte f&uuml;llen Sie die Fehlerbeschreibung ausf&uuml;hrlich aus, Sie m&uuml;ssen diese dann nicht mehr dem Paket beilegen.</p>'
          AND name = 'Defektes Produkt';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
        SET @formId = (SELECT id FROM s_cms_support WHERE name = 'Defective product' LIMIT 1);
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
            UPDATE s_cms_support SET text = '<p>After submitting this form you will receive an answer from us with a RMA number and additional instruction</p><p>Please fill out the form with all necessary details. You will not need to include the error description in your package.</p>'
            WHERE text = '<p>&nbsp;</p>&nbsp;<h1>Defective product - for customers and traders</h1><p>You will receive an answer&nbsp;from us&nbsp;with an RMA number an other approach&nbsp;after sending this form.&nbsp;</p><p>Please fill out the error description, so you must not add this any more to the package.</p>'
          AND id = @formId;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
            UPDATE s_cms_support_fields SET label = 'Article number(s)'
            WHERE name = 'artikel' AND label = 'Articlenumber(s)' AND supportID = @formId;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
            UPDATE s_cms_support_fields SET label = 'Email address'
            WHERE name = 'email' AND label = 'eMail-Adress' AND supportID = @formId;
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
            UPDATE s_cms_support_fields SET label = 'How does the problem occur?'
            WHERE name = 'wie' AND label = 'How doeas the problem occur?' AND supportID = @formId;
SQL;
        $this->addSql($sql);
    }
}
