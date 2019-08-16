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
    'menuitem_database-import' => 'Installation',
    'menuitem_edition' => 'Shopware-Lizenz',
    'menuitem_configuration' => 'Konfiguration',
    'menuitem_finish' => 'Fertig',
    'menuitem_license' => 'AGB',

    'license_incorrect' => 'Der eingegebene Lizenzschlüssel scheint nicht gültig zu sein',
    'license_does_not_match' => 'Der eingegebene Lizenzschlüssel passt zu keiner kommerziellen Shopware Version',
    'license_domain_error' => 'Der eingegebene Lizenzschlüssel ist nicht gültig für die Domain: ',

    'version_text' => '<strong>Version:</strong>',
    'back' => 'Zurück',
    'forward' => 'Weiter',
    'start' => 'Starten',
    'start_installation' => 'Installation starten',

    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',
    'select_language_nl' => 'Nederlands',
    'select_language_it' => 'Italiano',
    'select_language_fr' => 'Français',
    'select_language_es' => 'Español',
    'select_language_pt' => 'Português',
    'select_language_pl' => 'Polski',

    'language-selection_select_language' => 'Sprache Installationsassistent',
    'language-selection_header' => 'Deine Shopware-Installation',
    'language-selection_info_message' => 'Die hier zu wählende Sprache bezieht sich nur auf den Installationsassistenten, die Sprache Deines Shops kannst Du später definieren.',
    'language-selection_welcome_message' => <<<EOT
<p>
    Wir freuen uns, dass Du Teil unserer großartigen, weltweiten Shopware Community werden möchtest.
</p>
<p>
    Schritt für Schritt begleiten wir Dich jetzt durch Deinen Installationsprozess. Solltest Du Fragen haben, schaue einfach in unser <a href="https://forum.shopware.com" target="_blank">Forum</a> oder kontaktiere uns telefonisch unter <a href="tel:+492555928850">(+49) 2555 928850</a> oder per <a href="mailto:info@shopware.com">E-Mail</a>.
</p>
<p>
    <strong>Los geht's</strong>
</p>
EOT
    ,
    'requirements_header' => 'Systemvoraussetzungen',
    'requirements_header_files' => 'Dateien und Verzeichnisse',
    'requirements_header_system' => 'System',
    'requirements_files_info' => 'Die nachfolgenden Dateien und Verzeichnisse müssen vorhanden sein und Schreibrechte besitzen',
    'requirements_tablefiles_colcheck' => 'Datei/Verzeichnis',
    'requirements_tablefiles_colstatus' => 'Status',
    'requirements_error' => '<h3 class="alert-heading">Achtung!</h3>Es sind nicht alle Voraussetzungen für eine erfolgreiche Installation erfüllt',
    'requirements_success' => '<h3 class="alert-heading">Glückwunsch!</h3>Alle Voraussetzungen für eine erfolgreiche Installation sind erfüllt',
    'requirements_php_info' => 'Dein Server muss die folgenden Systemvoraussetzungen erfüllen, damit Shopware lauffähig ist',
    'requirements_php_max_compatible_version' => 'Diese Version von Shopware unterstützt PHP bis Version %s. Die vollständige Funktion mit neueren PHP Versionen kann nicht garantiert werden.',
    'requirements_system_colcheck' => 'Voraussetzung',
    'requirements_system_colrequired' => 'Erforderlich',
    'requirements_system_colfound' => 'Dein System',
    'requirements_system_colstatus' => 'Status',
    'requirements_show_all' => '(alles anzeigen)',
    'requirements_hide_all' => '(alles ausblenden)',

    'license_agreement_header' => 'Allgemeine Geschäftsbedingungen („AGB“)',
    'license_agreement_info' => 'Hier findest Du eine Aufstellung unserer allgemeinen Geschäftsbedingungen, die für eine erfolgreiche Installation bitte zu lesen und zu akzeptieren sind. Die Shopware Community Edition ist unter AGPL lizenziert, während Teile der Plugins und das Template unter der New BSD Lizenz stehen.',
    'license_agreement_error' => 'Du musst unseren AGB zustimmen',
    'license_agreement_checkbox' => 'Ich stimme den AGB zu',

    'database-configuration_header' => 'Datenbank konfigurieren',
    'database-configuration_field_host' => 'Datenbank Server:',
    'database-configuration_advanced_settings' => 'Erweiterte Einstellungen anzeigen',
    'database-configuration_field_port' => 'Datenbank Port:',
    'database-configuration_field_socket' => 'Datenbank Socket (optional):',
    'database-configuration_field_user' => 'Datenbank Benutzer:',
    'database-configuration_field_password' => 'Datenbank Passwort:',
    'database-configuration_field_database' => 'Datenbank Name:',
    'database-configuration_info' => 'Um Shopware auf Deinem System zu installieren, werden die Zugangsdaten zur Datenbank benötigt. Wenn Du Dir nicht sicher bist, was Du eintragen musst, kontaktiere Deinen Administrator / Hoster.',
    'database-configuration-create_new_database' => 'Neue Datenbank anlegen',

    'database-import_header' => 'Installation',
    'database-import_skip_import' => 'Überspringen',
    'database-import_progress' => 'Fortschritt: ',
    'database-import-hint' => '<strong>Hinweis: </strong> Falls in der konfigurierten Datenbank bereits Shopware Tabellen bestehen, werden diese durch die Installation / das Update entfernt!',
    'migration_counter_text_migrations' => 'Datenbank-Update wird durchgeführt',
    'migration_counter_text_snippets' => 'Textbausteine werden aktualisiert',
    'migration_update_success' => 'Datenbank erfolgreich importiert!',

    'edition_header' => 'Hast Du eine Shopware-Lizenz erworben?',
    'edition_info' => 'Shopware gibt es in einer kostenlosen <a href="https://de.shopware.com/versionen/" target="_blank">Community Edition </a> sowie in kostenpflichtigen <a href="https://de.shopware.com/versionen/" target="_blank">Professional, Professional Plus und Enterprise Editionen</a>.',
    'edition_ce' => 'Nein, ich möchte die kostenfreie <a href="https://de.shopware.com/versionen/" target="_blank">Community Edition</a> verwenden.',
    'edition_cm' => 'Ja, ich habe eine kostenpflichtige Shopware-Lizenz (<a href="https://de.shopware.com/versionen/" target="_blank">Professional, Professional Plus oder Enterprise</a>).',
    'edition_license' => 'Dann trage hier Deinen Lizenzschlüssel ein. Diesen findest Du in Deinem Shopware-Account unter "Shopbetreiberbereich" &rarr; "Shops" &rarr; [Wähle deine Domain] &rarr; "Lizenschlüssel kopieren":',
    'edition_license_error' => 'Für die Installation einer kostenpflichtigen Shopware-Version ist eine gültige Lizenz erforderlich.',

    'configuration_header' => 'Shop Grundeinrichtung',
    'configuration_sconfig_text' => 'Fast geschafft! Jetzt musst Du nur noch einige grundlegende Einstellungen für Deinen Shop festlegen, dann ist die Installation abgeschlossen. Alles was Du hier einträgst, kannst Du natürlich nachträglich wieder ändern!',
    'configuration_sconfig_name' => 'Name Deines Shops:',
    'configuration_sconfig_name_info' => 'Bitte gib den Namen Deines Shops ein',
    'configuration_sconfig_mail' => 'E-Mail-Adresse des Shops:',
    'configuration_sconfig_mail_info' => 'Bitte gib Deine E-Mail-Adresse für ausgehende E-Mails ein',
    'configuration_sconfig_domain' => 'Shop-Domain:',
    'configuration_sconfig_language' => 'Hauptsprache:',
    'configuration_sconfig_currency' => 'Standardwährung:',
    'configuration_sconfig_currency_info' => 'Diese Währung wird standardmäßig genutzt, wenn Artikelpreise definiert werden',
    'configuration_admin_currency_eur' => 'Euro',
    'configuration_admin_currency_usd' => 'Dollar (US)',
    'configuration_admin_currency_gbp' => 'Britisches Pfund (GB)',
    'configuration_admin_username' => 'Admin Login-Name:',
    'configuration_admin_mail' => 'Admin E-Mail:',
    'configuration_admin_name' => 'Admin Name:',

    'configuration_admin_language_de' => 'Deutsch',
    'configuration_admin_language_en' => 'Englisch',
    'configuration_admin_password' => 'Admin Passwort:',

    'finish_header' => 'Installation abgeschlossen',
    'finish_info' => 'Du hast Shopware erfolgreich installiert!',
    'finish_info_heading' => 'Juhu!',
    'finish_first_steps' => '"Erste Schritte" - Guide',
    'finish_frontend' => 'Zum Shop-Frontend',
    'finish_backend' => 'Zum Shop-Backend (Administration)',
    'finish_message' => '
<p>
    <strong>Herzlich Willkommen bei Shopware,</strong>
</p>
<p>
    wir freuen uns Dich in unserer Community begrüßen zu dürfen. Du hast Shopware erfolgreich installiert.
<p>Dein Shop ist jetzt einsatzbereit. Falls Du neu bei Shopware bist, empfehlen wir Dir den Guide <a href="https://docs.shopware.com/de/shopware-5-de/erste-schritte/erste-schritte-in-shopware" target="_blank">"Erste Schritte in Shopware"</a>. Wenn Du Dich zum ersten Mal im Shop-Backend anmeldest, wird Dich unser First Run Wizard durch die weitere grundlegende Einrichtung führen.</p>
<p>Viel Spaß mit Deinem neuen Onlineshop!</p>',
];
