<?php

/** Installer locale - de_DE */
return array(
    'locale' => 'de_DE',
    'meta_text'     => '<strong>Shopware-Version:</strong> ___VERSION___',
    "start_install" => "Installation starten",
    "system_requirements" => "Systemvoraussetzungen",
    "configure_db" => "Datenbank konfigurieren",
    "import_db" => "DB-Import",
    "licence" => "Lizenz",
    "configuration" => "Konfiguration",
    "done" => "Fertig",
    "thank_you_message" => "<strong>Danke</strong>, dass Sie sich für Shopware 4 entschieden haben. Mit diesem Installer wird die Installation und Basis-Konfiguration von Shopware vorgenommen, so dass Sie nach der Installation direkt mit der Einrichtung Ihres Shops beginnen können.",
    "select_language" => "Sprache wählen",
    "select_language_choose" => "Bitte wählen",
    "select_language_de" => "Deutsch",
    "select_language_en" => "English",
    "your_name" => "Ihr Name",
    "back" => "Zurück",
    "forward" => "Weiter",
    "check_system_requirements" => "Systemvorraussetzungen prüfen",
    'system_requirements_header' => "Systemvoraussetzungen",
    // Step 2 # New #
    'step2_header_files' => "Dateien und Verzeichnisse",    // File & Directory permissions
    'step2_files_info' => "Die nachfolgenden Dateien und Verzeichnisse müssen vorhanden sein und Schreibrechte besitzen",   // The following files and directories must exist and be writable
    'step2_tablefiles_colcheck' => 'Datei/Verzeichnis', // Check
    'step2_tablefiles_colstatus' => 'Status', // Status
    'step2_error' => 'Einige Voraussetzungen werden nicht erfüllt', //Some system requirements are not met
    'step2_php_info' => "Ihr Server muss die folgenden Systemvoraussetzungen erfüllen, damit Shopware lauffähig ist", // Your server must meet the following requirements in order to run shopware
    'step2_system_colcheck' => 'Voraussetzung',  // Check
    'step2_system_colrequired' => 'Erforderlich',   // Required
    'step2_system_colfound' => 'Ihr System',  // Found
    'step2_system_colstatus' => 'Status', // Status
    // Step 3
    'step3_header' => 'Datenbank konfigurieren', // Database
    'step3_field_host' => 'Datenbank Server:', // Database host:
    'step3_field_port' => 'Datenbank Port:', // Database port:
    'step3_field_socket' => 'Datenbank Socket (optional):', // Database socket:
    'step3_field_user' => 'Datenbank Benutzer:', // Database user
    'step3_field_password' => 'Datenbank Passwort:', // Database password
    'step3_field_database' => 'Datenbank Name:', // Database
    'step3_info' => 'Geben Sie hier Ihre Datenbank-Zugangsdaten ein, wenn Sie sich nicht sicher sind, welche Dateien Sie eintragen müssen, kontaktieren Sie Ihren Provider.', // Enter your database connection - if you are not sure what data must be entered here, please contact your provider.
    'step_3_loading' => 'Datenbank wird importiert...',

    // Step 4
    'step4_header'      => 'Datenbank importieren', // Database import
    'step4_skip_import' => 'Überspringen', // Skip database import

    // Step 5
    'step5_header' => 'Wählen Sie die Lizenz unter der Sie Shopware verwenden möchten', // Choose your license
    'step5_ce' => 'Shopware Community Version(Lizenz: Open-Source AGPL)', // Shopware Community Edition (License: AGPL)
    'step5_pe' => 'Shopware Professional (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', // Shopware Professional Edition (License: Commercial / License key required)
    'step5_ee' => 'Shopware Enterprise Basic (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', // Shopware Enterprise Basic Edition (License: Commercial / License key required)
    'step5_ec' => 'Shopware Enterprise Premium (Lizenz: Kommerziell, Lizenzschlüssel erforderlich)', //  Shopware Enterprise Premium Edition (License: Commercial / License key required)
    'step5_license' => 'Lizenz', // License-Key:
    'step5_info' => 'Wenn Sie eine kommerzielle Shopware Lizenz erworben haben ,bitte wählen Sie die zutreffene Edition aus der Liste und geben Sie Ihren Lizenzschlüssel ein!', //  If you have purchased a commercial shopware version, select the appropriate edition in the list. Then, enter the license key that you received after the purchase.
    // Step 6
    'step6_header' => 'Shop Basis-Konfiguration',   // Shop configuration
    'step6_sconfig_header' => 'Frontend Einstellungen',   // Frontend configuration
    'step6_sconfig_name' => 'Name des Shops:',   // Shop name
    'step6_sconfig_name_info' => 'Bitte geben Sie den Namen Ihres Shops ein',   //  Please enter the title of your shop
    'step6_sconfig_mail' => 'Ihre eMail-Adresse:',   // Your mail address
    'step6_sconfig_mail_info' => 'Bitte geben Sie Ihre eMail-Adresse ein', // Please enter your email-address
    'step6_sconfig_domain' => 'Shop-Domain:', // Shop domain
    'step6_sconfig_language' => 'Shop-Default-Sprache:', // Shop default language
    'step6_sconfig_currency' => 'Shop-Default-Währung:', // Shop default currency
    'step6_admin_title' => 'Admin Benutzer einrichten', // Configure admin user
    'step6_admin_login' => 'Admin Login-Name:', // Admin login
    'step6_admin_mail' => 'Admin eMail:', // Admin email
    'step6_admin_name' => 'Admin Name:', // Admin name
    'step6_admin_language' => 'Admin Backend-Sprache:', // Admin backend language
    'step6_admin_language_de' => 'Deutsch', // German
    'step6_admin_language_en' => 'Englisch', // English
    'step6_admin_password' => 'Admin Passwort (Standard: demo):', // Admin password
    'step6_admin_password_repeat' => 'Admin Passwort Wdh. (Standard: demo):', // Admin password repeat
    // Step 7
    'step7_title' => 'Basis-Einrichtung abgeschlossen', // Finished
    'step7_info' => 'Die Installation wurde erfolgreich abgeschlossen.<br /><br />Aus Sicherheitsgründen sollten Sie den Installer (/install) nun via FTP vom Server löschen.', // The installation was finished successful
    'step7_frontend' => 'Zum Shop-Frontend', // Open shop frontend
    'step7_backend' => 'Zum Shop-Backend (Administration)', // Open shop backend

    'migration_progress_text'           => 'Bitte starten Sie das Datenbank-Update mit einen Klick auf den Button "Starten".<br> <strong>Bestehende Shopware Tabellen werden enfernt.</strong>',
    'migration_counter_text_migrations' => 'Datenbank-Update wird durchgeführt',
    'migration_update_success'          => 'Datenbank Update wurde erfolgreich durchgeführt',
    "start"                             => "Starten",
);
