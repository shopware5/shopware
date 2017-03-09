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
    'menuitem_requirements' => 'System requirements',
    'menuitem_database-configuration' => 'Database configuration',
    'menuitem_database-import' => 'Database creation',
    'menuitem_edition' => 'Shopware Edition',
    'menuitem_configuration' => 'Configuration',
    'menuitem_finish' => 'Finish',
    'menuitem_license' => 'License agreement',

    'version_text' => '<strong>Version:</strong>',
    'back' => 'Back',
    'forward' => 'Forward',
    'start' => 'Start',

    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',
    'select_language_nl' => 'Nederlands',

    'language-selection_header' => 'Start installation',
    'language-selection_select_language' => 'Choose installer language',
    'language-selection_info_message' => 'This language selection applies only to the installation assistant. Once you finish installing Shopware, you will be able to configure multiple shop in different languages.',
    'language-selection_welcome_message' => <<<'EOT'
<p>
    You are only a few clicks away from being part of the big Shopware community,
    which consists of over 54000 online merchants worldwide.
</p>
<p>
    Our Support team is available to answer your questions at any time.
    Please contact us via telephone <a href="tel:+492555928850">(+49) 2555 92 8850</a> or via email <a href="mailto:info@shopware.com">info@shopware.com</a>.
</p>
EOT
    ,

    'requirements_header' => 'System requirements',
    'requirements_header_files' => 'File & directory permissions',
    'requirements_header_system' => 'System requirements',
    'requirements_files_info' => 'The following files and directories must exist and be writable',
    'requirements_tablefiles_colcheck' => 'Check',
    'requirements_tablefiles_colstatus' => 'Status',
    'requirements_error' => 'Some system requirements have not been met.',
    'requirements_php_info' => 'Your server must meet the following requirements in order to run Shopware.',
    'requirements_system_colcheck' => 'Check',
    'requirements_system_colrequired' => 'Required',
    'requirements_system_colfound' => 'Found',
    'requirements_system_colstatus' => 'Status',

    'license_agreement_header' => 'License agreement (EULA)',
    'license_agreement_info' => 'The Shopware Community Edition is AGPL licensed, whereas parts of the plugins and the theme are MIT licensed.',
    'license_agreement_error' => 'You have to agree to our license',
    'license_agreement_checkbox' => 'I agree to the above terms and conditions',

    'database-configuration_header' => 'Database configuration',
    'database-configuration_field_host' => 'Database host:',
    'database-configuration_field_port' => 'Database port:',
    'database-configuration_field_socket' => 'Database socket (optional):',
    'database-configuration_field_user' => 'Database user:',
    'database-configuration_field_password' => 'Database password:',
    'database-configuration_field_database' => 'Database name:',
    'database-configuration_info' => 'Enter your database connection - if you are not sure what data must be entered here, please contact your hosting provider.',

    'database-import_header' => 'Database creation',
    'database-import_skip_import' => 'Skip database creation',
    'database-import_progress_text' => 'Please start the database creation process by clicking the "Start" button <br> <strong>Existing Shopware tables will be deleted.</strong>',
    'migration_counter_text_migrations' => 'Creating database structure',
    'migration_counter_text_snippets' => 'Update snippets',
    'migration_update_success' => 'Process complete',

    'edition_header' => 'Choose your license',
    'edition_ce' => 'Shopware Community Edition (License: AGPL)',
    'edition_cm' => 'Shopware Commercial Version (License: Commercial / License key required) e.g. Professional, Professional Plus, Enterprise',
    'edition_license' => 'License key:',
    'edition_info' => 'If you have purchased a commercial Shopware version, select the appropriate edition in the list. Then enter the license key that you received upon purchasing.',
    'edition_license_error' => 'It is required that you enter a valid to install a commercial shopware edition.',

    'configuration_header' => 'Shop configuration',
    'configuration_sconfig_header' => 'Frontend configuration',
    'configuration_sconfig_name' => 'Shop name:',
    'configuration_sconfig_name_info' => 'Please enter the name of your shop',
    'configuration_sconfig_mail' => 'Your email address:',
    'configuration_sconfig_mail_info' => 'Please enter your email address used for outgoing email',
    'configuration_sconfig_domain' => 'Shop domain:',
    'configuration_sconfig_language' => 'Default shop language:',
    'configuration_sconfig_currency' => 'Default shop currency:',
    'configuration_sconfig_currency_info' => 'Currency used by default when defining article prices',
    'configuration_admin_currency_eur' => 'Euro',
    'configuration_admin_currency_usd' => 'Dollar (US)',
    'configuration_admin_currency_gbp' => 'Pound (GB)',
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
    Welcome to Shopware.
</p>
<p>
    We are happy to welcome you to our Community.
    Shopware is successfully installed and ready to use.
</p>
',
];
