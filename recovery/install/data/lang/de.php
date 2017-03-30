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
    'menuitem_license' => 'Endnutzer-Lizenz',

    'version_text' => '<strong>Version:</strong>',
    'back'         => 'Zurück',
    'forward'      => 'Weiter',
    'start'        => 'Starten','start_installation' => 'Installation starten',

    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',
    'select_language_nl' => 'Nederlands',

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
    'requirements_success'              => '<h3 class="alert-heading">Glückwunsch!</h3>Alle Voraussetzungen für eine erfolgreiche Installation sind erfüllt',
    'requirements_ioncube'              => '<small><strong>* Hinweis:</strong> Auf Deinem System ist die Codierungs-Software ionCube nicht installiert. Diese wird nur benötigt, wenn Du Dir später über unseren <a href="https://store.shopware.com" target="_blank">Shopware Community Store</a> Erweiterungen installieren möchtest, die mit ionCube verschlüsselt sind. Du kannst die Installation von ionCube auch jederzeit nachholen.</small>',
    'requirements_php_info' => 'Ihr Server muss die folgenden Systemvoraussetzungen erfüllen, damit Shopware lauffähig ist',
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
    'database-configuration_advanced_settings'   => 'Erweiterte Einstellungen anzeigen',
    'database-configuration_field_port' => 'Datenbank Port:',
    'database-configuration_field_socket' => 'Datenbank Socket (optional):',
    'database-configuration_field_user' => 'Datenbank Benutzer:',
    'database-configuration_field_password' => 'Datenbank Passwort:',
    'database-configuration_field_database' => 'Datenbank Name:',
    'database-configuration_info' => 'Um Shopware auf Deinem System zu installieren, werden die Zugangsdaten zur Datenbank benötigt. Wenn Du Dir nicht sicher bist, was Du eintragen musst, kontaktiere Deinen Administrator / Hoster.',
    'database-configuration-create_new_database' => 'Neue Datenbank anlegen',

    'database-import_header'            => 'Installation',
    'database-import_skip_import'       => 'Überspringen',
    'database-import_progress'          => 'Fortschritt: ',
    'database-import-hint'              => '<strong>Hinweis: </strong> Falls in der konfigurierten Datenbank bereits Shopware Tabellen bestehen,werden diese durch die Installation / Updateentfernt!',
    'migration_counter_text_migrations' => 'Datenbank-Update wird durchgeführt',
    'migration_counter_text_snippets' => 'Textbausteine werden aktualisiert',
    'migration_update_success' => 'Datenbank erfolgreich importiert!',

    'edition_header'        => 'Hast Du eine  Shopware -Lizenz erworben ?',
    'edition_info'          => 'Shopware gibt es in einer kostenlosen <a href="https://de.shopware.com/versionen/" target="_blank">Community Edition </a> sowie in kostenpflichtigen <a href="https://de.shopware.com/versionen/" target="_blank">Professional, Professional Plus und Enterprise Editionen</a>.',
    'edition_ce'            => 'Nein, ich möchte die kostenfreie <a href="https://de.shopware.com/versionen/" target="_blank"> Community Edition </a> verwenden.',
    'edition_cm'            => 'Ja, ich habe eine kostenpflichtigeShopware -Lizenz(<a href="https://de.shopware.com/versionen/" target="_blank"> Professional, Professional Plusoder Enterprise</a>).',
    'edition_license'       => 'Dann trage hier Deinen Lizenzschlüssel ein. Diesen findest Du in Deinem Shopware Account unter "Lizenzen" -> "Produktlizenzen" -> "Details / Download":',
    'edition_license_error' => 'Für die Installation einer kostenpflichtigen Shopware Version ist eine gültige Lizenz erfordelich.',

    'configuration_header'                  => 'Shop Grundeinrichtung',
    'configuration_sconfig_text'            => 'Fast geschafft! Jetzt musst Du nur noch einige grundlegende Einstellungenfür Deinen Shop festlegen, dann ist die Installation abgeschlossen. Alles was Du hier einträgst, kannst Du natürlich nachträglich wieder ändern!',
    'configuration_sconfig_name'            => 'Name Deines Shops:',
    'configuration_sconfig_name_info'       => 'Bitte gib den Namen Deines Shops ein',
    'configuration_sconfig_mail'            => 'E-Mail-Adresse des Shops:',
    'configuration_sconfig_mail_info'       => 'Bitte gib Deine E-Mail-Adresse für ausgehende E-Mails ein',
    'configuration_sconfig_domain'          => 'Shop-Domain:',
    'configuration_sconfig_language'        => 'Hauptsprache:',
    'configuration_sconfig_currency'        => 'Standardwährung:',
    'configuration_sconfig_currency_info'   => 'Diese Währung wird standardmäßig genutzt, wenn Artikel Preise definiert werden',
    'configuration_admin_currency_eur'      => 'Euro',
    'configuration_admin_currency_usd'      => 'Dollar (US)',
    'configuration_admin_currency_gbp'      => 'Britisches Pfund (GB)',
    'configuration_admin_username'          => 'Admin Login-Name:',
    'configuration_admin_mail'              => 'Admin E-Mail:',
    'configuration_admin_name'              => 'Admin Name:',

    'configuration_admin_language_de'       => 'Deutsch',
    'configuration_admin_language_en'       => 'Englisch',
    'configuration_admin_password'          => 'Admin Passwort:',

    'finish_header' => 'Basis-Einrichtung abgeschlossen',
    'finish_info' => 'Die Installation wurde erfolgreich abgeschlossen.',
    'finish_frontend' => 'Zum Shop-Frontend',
    'finish_backend' => 'Zum Shop-Backend (Administration)',
    'finish_message' => '
<p>
    Herzlich Willkommen bei Shopware.
</p>
<p>
    Wir freuen uns Dich in unserer Community begrüßen zu dürfen. Shopware ist nun erfolgreich installiert und ab sofort einsatzbereit. Gerne unterstützt Dich unser Assistent, welcher sich automatisch beim Aufruf Ihrer Shopware Administrationsoberfläche öffnet, bei der weiteren Einrichtung.
</p>
<p>
    Darüber hinaus hilft Dir unser <a target="_blank" href="http://wiki.shopware.com/_detail_930.html">Guide</a> bei Deinen „ersten Schritten".
</p>',
];
