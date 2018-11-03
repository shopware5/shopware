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

use Shopware\Components\Migrations\AbstractMigration;

class Migrations_Migration944 extends AbstractMigration
{
    public function up($modus)
    {
        $this->replaceTranslations();
    }

    private function replaceTranslations()
    {
        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Wenn keine ähnlichen Produkte gefunden wurden, kann Shopware automatisch alternative Vorschläge generieren. Du kannst die automatischen Vorschläge aktivieren, indem du einen Wert größer als 0 einträgst. Das Aktivieren kann sich negativ auf die Performance des Shops auswirken.'
WHERE s_core_config_elements.name = 'similarlimit' AND s_core_config_forms.name = 'Frontend77';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Definiere hier den Zahlstatus bei dem ein Download des ESD-Artikels möglich ist.'
WHERE s_core_config_elements.name = 'downloadAvailablePaymentStatus' AND s_core_config_forms.name = 'Esd';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Beachte, dass du die Sternchenangabe über den Textbaustein RegisterLabelPhone konfigurieren musst'
WHERE s_core_config_elements.name = 'requirePhoneField' AND s_core_config_forms.name = 'Frontend33';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = '<b>Achtung</b>: Diese Einstellung könnte die Funktionalität der ESD Downloads beeinträchtigen. Ändere hier nur die Einstellung falls du weißt, was du tust.<br><br>Downloadstrategie für ESD Dateien.<br><b>Link</b>: Unter umständen Unsicher, da der Link von Außen eingesehen werden kann.<br><b>PHP</b>: Der Link kann nicht eingesehen werden. PHP liefert die Datei aus. Dies kann zu Problemen bei größeren Dateien führen.<br><b>X-Sendfile</b>: Unterstütz größere Dateien und ist sicher. Benötigt das X-Sendfile Apache Module. <br><b>X-Accel</b>: Äquivalent zum X-Sendfile. Benötigt das Nginx Modul X-Accel.'
WHERE s_core_config_elements.name = 'esdDownloadStrategy' AND s_core_config_forms.name = 'Esd';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Mit Hilfe des Produkt Layouts kannst du entscheiden, wie deine Produkte auf der Suchergebnis-Seite dargestellt werden sollen. Wähle eines der drei unterschiedlichen Layouts um die Ansicht perfekt auf dein Produktsortiment abzustimmen.'
WHERE s_core_config_elements.name = 'searchProductBoxLayout' AND s_core_config_forms.name = 'Search';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Wenn aktiviert, zeigt die "Artikel nicht gefunden" Seite die ähnlichen Artikel Vorschläge an. Deaktiviere diese Einstellung um die Standard "Seite nicht gefunden" Seite darzustellen.'
WHERE s_core_config_elements.name = 'RelatedArticlesOnArticleNotFound' AND s_core_config_forms.name = 'Frontend100';
SQL;
        $this->addSql($sql);

        $sql = <<<SQL
UPDATE s_core_config_elements
LEFT JOIN s_core_config_forms ON s_core_config_elements.form_id = s_core_config_forms.id
SET s_core_config_elements.description = 'Wähle hier eine Methode aus, wie die Formulare gegen Spam-Bots geschützt werden sollen'
WHERE s_core_config_elements.name = 'captchaMethod' AND s_core_config_forms.name = 'Captcha';
SQL;
        $this->addSql($sql);
    }
}
