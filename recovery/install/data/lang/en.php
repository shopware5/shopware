<?php

/** Installer locale - en_GB */
return array(
    'locale' => 'en_GB',
    'meta_text'     => '<strong>Shopware version:</strong> ___VERSION___',
    "start_install" => "Start install",
    "system_requirements" => "System requirements",
    "configure_db" => "Database configuration",
    "import_db" => "Database import",
    "licence" => "License",
    "configuration" => "Configuration",
    "done" => "Done",
    "thank_you_message" => "Congratulations and thank you for choosing Shopware 4. This installer will perform the installation and basic configuration. Afterwards, you can start setting up your shop. Note: If you proceed with the English variant of installation assistant, Shopware will be installed with the English language package.",
    "select_language" => "Select language",
    "select_language_choose" => "Please choose",
    "select_language_de" => "Deutsch",
    "select_language_en" => "English",
    "your_name" => "Your name",
    "back" => "Back",
    "forward" => "Forward",
    "check_system_requirements" => "Check system requirements",
    'system_requirements_header' => "System requirements",
    // Step 2 # New #
    'step2_header_files' => "File & directory permissions",    // File & directory permissions
    'step2_files_info' => "The following files and directories must exist and be writable",   // The following files and directories must exist and be writable
    'step2_tablefiles_colcheck' => 'Check', // Check
    'step2_tablefiles_colstatus' => 'Status', // Status
    'step2_error' => 'Some system requirements have not been met.', //Some system requirements are not met
    'step2_php_info' => "Your server must meet the following requirements in order to run Shopware.", // Your server must meet the following requirements in order to run shopware
    'step2_system_colcheck' => 'Check',  // Check
    'step2_system_colrequired' => 'Required',   // Required
    'step2_system_colfound' => 'Found',  // Found
    'step2_system_colstatus' => 'Status', // Status
    // Step 3
    'step3_header' => 'Configure database', // Database
    'step3_field_host' => 'Database host:', // Database host:
    'step3_field_port' => 'Database port:', // Database port:
    'step3_field_socket' => 'Database socket (optional):', // Database socket:
    'step3_field_user' => 'Database user:', // Database user
    'step3_field_password' => 'Database password:', // Database password
    'step3_field_database' => 'Database name:', // Database
    'step3_info' => 'Enter your database connection - if you are not sure what data must be entered here, please contact your hosting provider.', // Enter your database connection - if you are not sure what data must be entered here, please contact your provider.
    'step_3_loading' => 'Importing database...',
    // Step 4
    'step4_header'      => 'Database import', // Database import
    'step4_skip_import' => 'Skip database import', // Skip database import
    // Step 5
    'step5_header' => 'Choose your license', // Choose your license
    'step5_ce' => 'Shopware Community Edition (License: AGPL)', // Shopware Community Edition (License: AGPL)
    'step5_pe' => 'Shopware Professional Edition (License: Commercial / License key required)', // Shopware Professional Edition (License: Commercial / License key required)
    'step5_ee' => 'Shopware Enterprise Basic Edition (License: Commercial / License key required)', // Shopware Enterprise Basic Edition (License: Commercial / License key required)
    'step5_ec' => 'Shopware Enterprise Premium Edition (License: Commercial / License key required)', //  Shopware Enterprise Premium Edition (License: Commercial / License key required)
    'step5_license' => 'License key', // License-Key:
    'step5_info' => 'If you have purchased a commercial Shopware version, select the appropriate edition in the list. Then enter the license key that you received upon purchasing.', //  If you have purchased a commercial shopware version, select the appropriate edition in the list. Then, enter the license key that you received after the purchase.
    'step5_license_error' => 'It is required that you enter a valid to install a commercial shopware edition.',

    // Step 6
    'step6_header' => 'Shop configuration',   // Shop configuration
    'step6_sconfig_header' => 'Frontend configuration',   // Frontend configuration
    'step6_sconfig_name' => 'Shop name:',   // Shop name
    'step6_sconfig_name_info' => 'Please enter the name of your shop',   //  Please enter the title of your shop
    'step6_sconfig_mail' => 'Your email address:',   // Your mail address
    'step6_sconfig_mail_info' => 'Please enter your email address', // Please enter your email-address
    'step6_sconfig_domain' => 'Shop domain:', // Shop domain
    'step6_sconfig_language' => 'Default shop  language:', // Shop default language
    'step6_sconfig_currency' => 'Default shop currency:', // Shop default currency
    'step6_admin_title' => 'Configure admin user', // Configure admin user
    'step6_admin_login' => 'Admin login:', // Admin login
    'step6_admin_mail' => 'Admin email:', // Admin email
    'step6_admin_name' => 'Admin name:', // Admin name
    'step6_admin_language' => 'Admin backend language:', // Admin backend language
    'step6_admin_language_de' => 'German', // German
    'step6_admin_language_en' => 'English', // English
    'step6_admin_password' => 'Admin password (default: demo):', // Admin password
    'step6_admin_password_repeat' => 'Repeat admin password (default: demo):', // Admin password repeat
    // Step 7
    'step7_title' => 'Finished', // Finished
    'step7_info' => 'The installation has been completed successfully.', // The installation was finished successful
    'step7_frontend' => 'Open shop frontend', // Open shop frontend
    'step7_backend' => 'Open shop backend', // Open shop backend

    'migration_progress_text'           => 'Please start the database import by clicking the "Start" button.<br> <strong>Any existing Shopware tables may be removed.</strong>',
    'migration_counter_text_migrations' => 'Database import in progress',
    'migration_update_success'          => 'Database import complete',
    "start"                             => "Start",
);
