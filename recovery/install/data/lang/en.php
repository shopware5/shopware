<?php
return [
    'menuitem_language-selection'      => 'Start',
    'menuitem_requirements'            => 'System requirements',
    'menuitem_database-configuration'  => 'Database configuration',
    'menuitem_database-import'         => 'Database import',
    'menuitem_edition'                 => 'Edition',
    'menuitem_configuration'           => 'Configuration',
    'menuitem_finish'                  => 'Finish',
    'menuitem_license'                 => 'License',

    'locale' => 'en_GB',
    'version_text' => '<strong>Version:</strong>',
    "system_requirements" => "System requirements",
    "configure_db" => "Database configuration",
    "import_db" => "Database import",
    "licence" => "License",
    "configuration" => "Configuration",
    "done" => "Done",
    'skip' => "Skip",
    "back" => "Back",
    "forward" => "Forward",

    'language-selection_header' => 'Start install',
    'language-selection_thank_you_message' => "Congratulations and thank you for choosing Shopware 5. This installer will perform the installation and basic configuration. Afterwards, you can start setting up your shop. Note: If you proceed with the English variant of installation assistant, Shopware will be installed with the English language package.",

    'license_agreement_header' => 'License agreement',
    'license_agreement_info' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin sed ullamcorper metus, eget suscipit sapien. Curabitur at ipsum ut eros pulvinar semper. Integer non augue at dolor vestibulum mattis eget eu urna. Fusce convallis mattis consectetur. Nulla vulputate viverra dolor, ac laoreet nisl convallis nec. Donec odio magna, rutrum condimentum consectetur at, tempus eget libero. In dictum nulla mi, in egestas nunc accumsan in. Nullam nec aliquet diam, eget placerat dui.',
    'license_agreement_error' => 'You have to agree to our license',
    'license_agreement_checkbox' => 'I agree to the above terms and conditions',

    'requirements_header' => "System requirements",
    'requirements_header_files' => "File & directory permissions",
    'requirements_header_system' => "System requirements",
    'requirements_files_info' => "The following files and directories must exist and be writable",
    'requirements_tablefiles_colcheck' => 'Check',
    'requirements_tablefiles_colstatus' => 'Status',
    'requirements_error' => 'Some system requirements have not been met.',
    'requirements_php_info' => "Your server must meet the following requirements in order to run Shopware.",
    'requirements_system_colcheck' => 'Check',
    'requirements_system_colrequired' => 'Required',
    'requirements_system_colfound' => 'Found',
    'requirements_system_colstatus' => 'Status',

    'database-configuration_header' => 'Configure database',
    'database-configuration_field_host' => 'Database host:',
    'database-configuration_field_port' => 'Database port:',
    'database-configuration_field_socket' => 'Database socket (optional):',
    'database-configuration_field_user' => 'Database user:',
    'database-configuration_field_password' => 'Database password:',
    'database-configuration_field_database' => 'Database name:',
    'database-configuration_info' => 'Enter your database connection - if you are not sure what data must be entered here, please contact your hosting provider.',

    'database-import_header'      => 'Database import',
    'database-import_skip_import' => 'Skip database import',

    'edition_header'        => 'Choose your license',
    'edition_ce'            => 'Shopware Community Edition (License: AGPL)',
    'edition_pe'            => 'Shopware Professional Edition (License: Commercial / License key required)',
    'edition_ee'            => 'Shopware Enterprise Basic Edition (License: Commercial / License key required)',
    'edition_ec'            => 'Shopware Enterprise Premium Edition (License: Commercial / License key required)',
    'edition_license'       => 'License key:',
    'edition_info'          => 'If you have purchased a commercial Shopware version, select the appropriate edition in the list. Then enter the license key that you received upon purchasing.',
    'edition_license_error' => 'It is required that you enter a valid to install a commercial shopware edition.',

    'configuration_header' => 'Shop configuration',
    'configuration_sconfig_header' => 'Frontend configuration',
    'configuration_sconfig_name' => 'Shop name:',
    'configuration_sconfig_name_info' => 'Please enter the name of your shop',
    'configuration_sconfig_mail' => 'Your email address:',
    'configuration_sconfig_mail_info' => 'Please enter your email address used for outgoing mail',
    'configuration_sconfig_admin_mail_info' => 'This email address is used as backend login',
    'configuration_sconfig_domain' => 'Shop domain:',
    'configuration_sconfig_language' => 'Default shop  language:',
    'configuration_sconfig_currency' => 'Default shop currency:',
    'configuration_admin_title' => 'Configure admin user',
    'configuration_admin_username' => 'Admin login:',
    'configuration_admin_mail' => 'Admin email:',
    'configuration_admin_name' => 'Admin name:',
    'configuration_admin_language' => 'Admin backend language:',
    'configuration_admin_language_de' => 'German',
    'configuration_admin_language_en' => 'English',
    'configuration_admin_password' => 'Admin password:',

    'finish_header' => 'Finished',
    'finish_info' => 'The installation has been completed successfully.',
    'finish_frontend' => 'Open shop frontend',
    'finish_backend' => 'Open shop backend',
    'finish_message' => '
<p>
    Herzlich Willkommen bei Shopware.
</p>
<p>
    Wir freuen uns Sie in unserer Community begrüßen zu dürfen. Shopware ist nun erfolgreich installiert und ab sofort einsatzbereit. Gerne unterstützt Sie unser Assistent, welcher sich automatisch beim Aufruf Ihrer Shopware Administrationsoberfläche öffnet, bei der weiteren Einrichtung.
</p>
<p>
    Darüber hinaus hilft Ihnen unser <a target="_blank" href="http://en.wiki.shopware.com/_detail_1195.html">Guide</a> bei Ihren „ersten Schritten".
</p>
    ',

    'migration_progress_text'           => 'Please start the database import by clicking the "Start" button.<br> <strong>Any existing Shopware tables may be removed.</strong>',
    'migration_counter_text_migrations' => 'Database import in progress',
    'migration_update_success'          => 'Database import complete',
    "start"                             => "Start",
];
