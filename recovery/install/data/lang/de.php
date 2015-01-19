<?php
return [
    'menuitem_language-selection'      => 'Start',
    'menuitem_requirements'            => 'Systemvoraussetzungen',
    'menuitem_database-configuration'  => 'Datenbank Konfiguration',
    'menuitem_database-import'         => 'Datenbank Import',
    'menuitem_edition'                 => 'License',
    'menuitem_configuration'           => 'Konfiguration',
    'menuitem_finish'                  => 'Fertig',
    'menuitem_license'                 => 'Lizenz',

    'locale' => 'de_DE',
    'version_text'     => '<strong>Version:</strong>',
    "system_requirements" => "Systemvoraussetzungen",
    "configure_db" => "Datenbank konfigurieren",
    "import_db" => "DB-Import",
    "licence" => "Lizenz",
    "configuration" => "Konfiguration",
    "done" => "Fertig",
    "thank_you_message" => "<strong>Danke</strong>, dass Sie sich für Shopware 5 entschieden haben. Mit diesem Installer wird die Installation und Basis-Konfiguration von Shopware vorgenommen, so dass Sie nach der Installation direkt mit der Einrichtung Ihres Shops beginnen können.",
    "select_language" => "Sprache wählen",
    "select_language_choose" => "Bitte wählen",
    "select_language_de" => "Deutsch",
    "select_language_en" => "English",
    "back" => "Zurück",
    "forward" => "Weiter",
    "check_system_requirements" => "Systemvorraussetzungen prüfen",
    'system_requirements_header' => "Systemvoraussetzungen",
    "language-selection_header" => "Installation starten",
    'language-selection_welcome_message' => <<<EOT
<p>
    Sie sind nur noch wenige Klicks davon entfernt, Teil der großen Shopware Community zu werden, welche aktuell aus über 30.000 Onlinehändlern weltweit besteht!
</p>
<p>
    Die Installation eines der leistungsfähigsten Shopsysteme auf dem Markt verläuft schnell und unkompliziert, eine entsprechende Installationsanleitung finden Sie <a href="#">hier</a>.
</p>
<p>
    Darüber hinaus steht Ihnen unser Support-Team selbstverständlich jederzeit für Fragen zur Verfügung. Kontaktieren Sie uns einfach unter Tel. <a href="tel:+492555928850">(+49) 2555 92 8850</a> oder eMail <a href="mailto:info@shopware.com">info@shopware.com</a> .
</p>
EOT
,
    'language-selection_info_message' => 'Diese Sprachauswahl bezieht sich lediglich auf den Installationsassistenten. Im Anschluss an die Installation können Sie Ihren Shop natürlich wahlweise in vielen anderen Sprachen betreiben.',

    'license_agreement_header'   => 'Endnutzer-Lizenzbestimmungen („EULA“)',
    'license_agreement_info'     => 'Hier finden Sie eine Aufstellung unserer Lizenzbestimmungen, welche Sie bitte zur erfolgreichen Installation lesen und akzeptieren. Die Shopware Community Edition ist unter AGPL lizensiert, während Teile der Plugins und das Template unter der New BSD Lizenz stehen.',
    'license_agreement_error'    => 'Sie müssen unseren Lizenzbestimmungen zustimmen',
    'license_agreement_checkbox' => 'Ich stimme den Lizenzbestimmungen zu',

    'requirements_header_files' => "Dateien und Verzeichnisse",    // File & Directory permissions
    'requirements_files_info' => "Die nachfolgenden Dateien und Verzeichnisse müssen vorhanden sein und Schreibrechte besitzen",   // The following files and directories must exist and be writable
    'requirements_tablefiles_colcheck' => 'Datei/Verzeichnis', // Check
    'requirements_tablefiles_colstatus' => 'Status', // Status
    'requirements_error' => 'Einige Voraussetzungen werden nicht erfüllt', //Some system requirements are not met
    'requirements_php_info' => "Ihr Server muss die folgenden Systemvoraussetzungen erfüllen, damit Shopware lauffähig ist", // Your server must meet the following requirements in order to run shopware
    'requirements_system_colcheck' => 'Voraussetzung',  // Check
    'requirements_system_colrequired' => 'Erforderlich',   // Required
    'requirements_system_colfound' => 'Ihr System',  // Found
    'requirements_system_colstatus' => 'Status', // Status

    'database-configuration_header' => 'Datenbank konfigurieren', // Database
    'database-configuration_field_host' => 'Datenbank Server:', // Database host:
    'database-configuration_field_port' => 'Datenbank Port:', // Database port:
    'database-configuration_field_socket' => 'Datenbank Socket (optional):', // Database socket:
    'database-configuration_field_user' => 'Datenbank Benutzer:', // Database user
    'database-configuration_field_password' => 'Datenbank Passwort:', // Database password
    'database-configuration_field_database' => 'Datenbank Name:', // Database
    'database-configuration_info' => 'Geben Sie hier Ihre Datenbank-Zugangsdaten ein, wenn Sie sich nicht sicher sind, welche Dateien Sie eintragen müssen, kontaktieren Sie Ihren Provider.', // Enter your database connection - if you are not sure what data must be entered here, please contact your provider.
    'database-configuration-create_new_database' => "Neue Datenbank anlegen",

    'database-import_header'      => 'Datenbank importieren', // Database import
    'database-import_skip_import' => 'Überspringen', // Skip database import
    'migration_progress_text'           => 'Bitte starten Sie das Datenbank-Update mit einen Klick auf den Button "Starten".<br> <strong>Bestehende Shopware Tabellen werden enfernt.</strong>',
    'migration_counter_text_migrations' => 'Datenbank-Update wird durchgeführt',
    'migration_update_success'          => 'Datenbank Update wurde erfolgreich durchgeführt',
    "start"                             => "Starten",

    'edition_header' => 'Wählen Sie die Lizenz unter der Sie Shopware verwenden möchten', // Choose your license
    'edition_ce' => 'Shopware Community Version (Lizenz: Open-Source AGPL)', // Shopware Community Edition (License: AGPL)
    'edition_pe' => 'Shopware Professional (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', // Shopware Professional Edition (License: Commercial / License key required)
    'edition_ee' => 'Shopware Enterprise Basic (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', // Shopware Enterprise Basic Edition (License: Commercial / License key required)
    'edition_ec' => 'Shopware Enterprise Premium (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', //  Shopware Enterprise Premium Edition (License: Commercial / License key required)
    'edition_license' => 'Lizenz-Schlüssel:', // License-Key:
    'edition_info' => 'Wenn Sie eine kommerzielle Shopware Lizenz erworben haben ,bitte wählen Sie die zutreffene Edition aus der Liste und geben Sie Ihren Lizenzschlüssel ein!', //  If you have purchased a commercial shopware version, select the appropriate edition in the list. Then, enter the license key that you received after the purchase.
    'edition_license_error' => 'Für die Installation einer kommerziellen Shopware Version ist eine gültige Lizenz erfordelich.',

    'configuration_header' => 'Shop Basis-Konfiguration',   // Shop configuration
    'configuration_sconfig_header' => 'Frontend Einstellungen',   // Frontend configuration
    'configuration_sconfig_name' => 'Name des Shops:',   // Shop name
    'configuration_sconfig_name_info' => 'Bitte geben Sie den Namen Ihres Shops ein',   //  Please enter the title of your shop
    'configuration_sconfig_mail' => 'Ihre eMail-Adresse:',   // Your mail address
    'configuration_sconfig_mail_info' => 'Bitte geben Sie Ihre eMail-Adresse für ausgehende eMails ein', // Please enter your email-address
    'configuration_sconfig_admin_mail_info' => 'Bitte geben Sie Ihre eMail-Adresse für ausgehende eMails ein', // Please enter your email-address
    'configuration_sconfig_domain' => 'Shop-Domain:', // Shop domain
    'configuration_sconfig_language' => 'Shop-Default-Sprache:', // Shop default language
    'configuration_sconfig_currency' => 'Shop-Default-Währung:', // Shop default currency
    'configuration_admin_title' => 'Admin Benutzer einrichten', // Configure admin user
    'configuration_admin_username' => 'Admin Login-Name:', // Admin login
    'configuration_admin_mail' => 'Admin eMail:', // Admin email
    'configuration_admin_name' => 'Admin Name:', // Admin name
    'configuration_admin_language' => 'Admin Backend-Sprache:', // Admin backend language
    'configuration_admin_language_de' => 'Deutsch', // German
    'configuration_admin_language_en' => 'Englisch', // English
    'configuration_admin_password' => 'Admin Passwort:', // Admin password

    'finish_header' => 'Basis-Einrichtung abgeschlossen', // Finished
    'finish_info' => 'Die Installation wurde erfolgreich abgeschlossen.', // The installation was finished successful
    'finish_frontend' => 'Zum Shop-Frontend', // Open shop frontend
    'finish_backend' => 'Zum Shop-Backend (Administration)', // Open shop backend
    'finish_message' => '
<p>
    Herzlich Willkommen bei Shopware.
</p>
<p>
    Wir freuen uns Sie in unserer Community begrüßen zu dürfen. Shopware ist nun erfolgreich installiert und ab sofort einsatzbereit. Gerne unterstützt Sie unser Assistent, welcher sich automatisch beim Aufruf Ihrer Shopware Administrationsoberfläche öffnet, bei der weiteren Einrichtung.
</p>
<p>
    Darüber hinaus hilft Ihnen unser <a target="_blank" href="http://wiki.shopware.com/_detail_930.html">Guide</a> bei Ihren „ersten Schritten".
</p>
    ',
];
