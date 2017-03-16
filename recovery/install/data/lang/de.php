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

return [
    'menuitem_language-selection' => 'Start',
    'menuitem_requirements' => 'Systemvoraussetzungen',
    'menuitem_database-configuration' => 'Datenbank Konfiguration',
    'menuitem_database-import' => 'Datenbank Import',
    'menuitem_edition' => 'Shopware-Lizenz',
    'menuitem_configuration' => 'Konfiguration',
    'menuitem_finish' => 'Fertig',
    'menuitem_license' => 'Endnutzer-Lizenz',

    'version_text' => '<strong>Version:</strong>',
    'back' => 'Zurück',
    'forward' => 'Weiter',
    'start' => 'Starten',

    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',
    'select_language_nl' => 'Nederlands',

    'language-selection_select_language' => 'Sprache Installationsassistent',
    'language-selection_header' => 'Deine Shopware-Installation',
    'language-selection_info_message' => 'Die hier zu wählende Sprache bezieht sich nur auf den Installationsassistenten, die Sprache Deines Shops kannst Du später definieren.',
    'language-selection_welcome_message' => <<<EOT
<p>
    Wir freuen uns, dass Du Teil unserer unserer großartigen, weltweiten Shopware Community werden möchtest.
</p>
<p>
    Schritt für Schritt begleiten wir Dich jetzt durch Deinen Installationsprozess. Solltest Du Fragen haben, schaue einfach in unser <a href="https://forum.shopware.com" target="_blank">Forum</a> oder kontaktiere uns telefonisch unter <a href="tel:+492555928850">(+49) 2555 928850</a> oder per <a href="mailto:info@shopware.com">Mail</a>.
</p>
<p>
    <strong>Los geht's</strong>
</p>
EOT
    ,
    'requirements_header' => 'Systemvoraussetzungen',
    'requirements_header_files' => 'Dateien und Verzeichnisse',
    'requirements_header_system' => 'System',
    'requirements_files_info' => 'Die nachfolgenden Dateien und Verzeichnisse müssen vorhanden sein und Schreibrechte besitzen',   // The following files and directories must exist and be writable
    'requirements_tablefiles_colcheck' => 'Datei/Verzeichnis',
    'requirements_tablefiles_colstatus' => 'Status',
    'requirements_error' => 'Einige Voraussetzungen werden nicht erfüllt',
    'requirements_success'              => '<h3 class="alert-heading">Glückwunsch!</h3>Alle Voraussetzungen für eine erfolgreiche Installation erfüllt',
    'requirements_ioncube'              => '<small><strong>* Hinweis:</strong> Auf Deinem System ist die Codierungs-Software ionCube nicht installiert. Diese wird nur benötigt, wenn Du Dir später über unseren <a href="https://store.shopware.com" target="_blank">Shopware Community Store</a> Erweiterungen installieren möchtest, die mit ionCube verschlüsselt sind. Du kannst die Installation von ionCube auch jederzeit nachholen.</small>',
    'requirements_php_info' => 'Ihr Server muss die folgenden Systemvoraussetzungen erfüllen, damit Shopware lauffähig ist', // Your server must meet the following requirements in order to run shopware
    'requirements_system_colcheck' => 'Voraussetzung',
    'requirements_system_colrequired' => 'Erforderlich',
    'requirements_system_colfound' => 'Ihr System',
    'requirements_system_colstatus' => 'Status',
    'requirements_show_all'             => '(alles anzeigen)',
    'requirements_hide_all'             => '(alles ausblenden)',

    'license_agreement_header' => 'Endnutzer-Lizenzbestimmungen („EULA“)',
    'license_agreement_info' => 'Hier finden Sie eine Aufstellung unserer Lizenzbestimmungen, welche Sie bitte zur erfolgreichen Installation lesen und akzeptieren. Die Shopware Community Edition ist unter AGPL lizensiert, während Teile der Plugins und das Template unter der New BSD Lizenz stehen.',
    'license_agreement_error' => 'Sie müssen unseren Lizenzbestimmungen zustimmen',
    'license_agreement_checkbox' => 'Ich stimme den Lizenzbestimmungen zu',

    'database-configuration_header' => 'Datenbank konfigurieren',
    'database-configuration_field_host' => 'Datenbank Server:',
    'database-configuration_field_port' => 'Datenbank Port:',
    'database-configuration_field_socket' => 'Datenbank Socket (optional):',
    'database-configuration_field_user' => 'Datenbank Benutzer:',
    'database-configuration_field_password' => 'Datenbank Passwort:',
    'database-configuration_field_database' => 'Datenbank Name:',
    'database-configuration_info' => 'Geben Sie hier Ihre Datenbank-Zugangsdaten ein. Wenn Sie sich nicht sicher sind, welche Daten Sie eintragen müssen, kontaktieren Sie Ihren Provider.', // Enter your database connection - if you are not sure what data must be entered here, please contact your provider.
    'database-configuration-create_new_database' => 'Neue Datenbank anlegen',

    'database-import_header' => 'Datenbank importieren',
    'database-import_skip_import' => 'Überspringen',
    'database-import_progress_text' => 'Bitte starten Sie das Datenbank-Update mit einen Klick auf den Button "Starten".<br> <strong>Bestehende Shopware Tabellen werden entfernt.</strong>',

    'migration_counter_text_migrations' => 'Datenbank-Update wird durchgeführt',
    'migration_counter_text_snippets' => 'Textbausteine werden aktualisiert',
    'migration_update_success' => 'Datenbank Update wurde erfolgreich durchgeführt',

    'edition_header' => 'Wählen Sie die Lizenz unter der Sie Shopware verwenden möchten',
    'edition_info' => 'Wenn Sie eine kommerzielle Shopware Lizenz erworben haben, so wählen Sie bitte die zutreffene Edition aus der Liste und geben Sie Ihren Lizenzschlüssel ein.',
    'edition_ce' => 'Shopware Community Edition (Lizenz: Open-Source AGPL)',
    'edition_cm' => 'Shopware kommerzielle Version (Lizenz: Kommerziell, Lizenzschlüssel erforderlich) Bspw. Professional, Professional Plus, Enterprise',
    'edition_license' => 'Lizenz-Schlüssel:',
    'edition_license_error' => 'Für die Installation einer kommerziellen Shopware Version ist eine gültige Lizenz erfordelich.',

    'configuration_header' => 'Shop Basis-Konfiguration',
    'configuration_sconfig_header' => 'Frontend Einstellungen',
    'configuration_sconfig_name' => 'Name des Shops:',
    'configuration_sconfig_name_info' => 'Bitte geben Sie den Namen Ihres Shops ein',
    'configuration_sconfig_mail' => 'Ihre E-Mail-Adresse:',
    'configuration_sconfig_mail_info' => 'Bitte geben Sie Ihre E-Mail-Adresse für ausgehende E-Mails ein',
    'configuration_sconfig_domain' => 'Shop-Domain:',
    'configuration_sconfig_language' => 'Shop-Default-Sprache:',
    'configuration_sconfig_currency' => 'Shop-Default-Währung:',
    'configuration_sconfig_currency_info' => 'Diese Währung wird standardmäßig genutzt, wenn Artikel Preise definiert werden',
    'configuration_admin_currency_eur' => 'Euro',
    'configuration_admin_currency_usd' => 'Dollar (US)',
    'configuration_admin_currency_gbp' => 'Britisches Pfund (GB)',
    'configuration_admin_title' => 'Admin Benutzer einrichten',
    'configuration_admin_username' => 'Admin Login-Name:',
    'configuration_admin_mail' => 'Admin E-Mail:',
    'configuration_admin_name' => 'Admin Name:',
    'configuration_admin_language' => 'Admin Backend-Sprache:',
    'configuration_admin_language_de' => 'Deutsch',
    'configuration_admin_language_en' => 'Englisch',
    'configuration_admin_password' => 'Admin Passwort:',

    'finish_header' => 'Basis-Einrichtung abgeschlossen',
    'finish_info' => 'Die Installation wurde erfolgreich abgeschlossen.',
    'finish_frontend' => 'Zum Shop-Frontend',
    'finish_backend' => 'Zum Shop-Backend (Administration)',
    'finish_message' => '
<p>
    Herzlich Willkommen bei Shopware.
</p>
<p>
    Wir freuen uns Sie in unserer Community begrüßen zu dürfen. Shopware ist nun erfolgreich installiert und ab sofort einsatzbereit. Gerne unterstützt Sie unser Assistent, welcher sich automatisch beim Aufruf Ihrer Shopware Administrationsoberfläche öffnet, bei der weiteren Einrichtung.
</p>
<p>
    Darüber hinaus hilft Ihnen unser <a target="_blank" href="http://wiki.shopware.com/_detail_930.html">Guide</a> bei Ihren „ersten Schritten".
</p>',
];
