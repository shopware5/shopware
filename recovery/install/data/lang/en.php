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
    'menuitem_database-import' => 'Installation',
    'menuitem_edition' => 'Shopware licence',
    'menuitem_configuration' => 'Configuration',
    'menuitem_finish' => 'Done',
    'menuitem_license' => 'Terms of service',

    'license_incorrect' => 'The licence key entered does not appear to be valid',
    'license_does_not_match' => 'The licence key entered does not match a commercial Shopware version',
    'license_domain_error' => 'The licence key entered is not valid for the domain: ',

    'version_text' => '<strong>Version:</strong>',
    'back' => 'Back',
    'forward' => 'Next',
    'start' => 'Start',
    'start_installation' => 'Start installation',

    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',
    'select_language_nl' => 'Nederlands',
    'select_language_it' => 'Italiano',
    'select_language_fr' => 'Français',
    'select_language_es' => 'Español',
    'select_language_pt' => 'Português',
    'select_language_pl' => 'Polski',

    'language-selection_select_language' => 'Language installation wizard',
    'language-selection_header' => 'Your Shopware installation',
    'language-selection_info_message' => 'The language you select here only applies to the installation wizard. You can specify the language for your shop later.',
    'language-selection_welcome_message' => <<<'EOT'
<p>
    We are delighted you want to join our fantastic, global Shopware community.
</p>
<p>
    We will now take you through the installation process step by step. If you have any queries, simply take a look in our <a href="https://forum.shopware.com" target="_blank">forum</a>, give us a call on <a href="tel:0080074676260">00 800 746 7626 0 (free of charge)</a> or send us an <a href="mailto:info@shopware.com">e-mail</a>.
</p>
<p>
    <strong>Let's get started</strong>
</p>
EOT
    ,
    'requirements_header' => 'System requirements',
    'requirements_header_files' => 'Files and directories',
    'requirements_header_system' => 'System',
    'requirements_files_info' => 'The following files and directories must be present and have write permissions',
    'requirements_tablefiles_colcheck' => 'File/directory',
    'requirements_tablefiles_colstatus' => 'Status',
    'requirements_error' => '<h3 class="alert-heading">Warning!</h3>Not all of the requirements for successful installation have been met',
    'requirements_success' => '<h3 class="alert-heading">Congratulations!</h3>All of the requirements for successful installation have been met',
    'requirements_php_info' => 'Your server must meet the following system requirements in order to run Shopware',
    'requirements_php_max_compatible_version' => 'This Shopware version supports PHP up to version %s. The complete functionality with newer PHP versions cannot be guaranteed.',
    'requirements_system_colcheck' => 'Requirement',
    'requirements_system_colrequired' => 'Required',
    'requirements_system_colfound' => 'Your system',
    'requirements_system_colstatus' => 'Status',
    'requirements_show_all' => '(show all)',
    'requirements_hide_all' => '(hide all)',

    'license_agreement_header' => 'Terms of service ("TOS")',
    'license_agreement_info' => 'Here you will find a summary of our terms of service, which you must read and accept for successful installation. The Shopware Community Edition is licensed under AGPL, while parts of the plug-ins and the template are under the New BSD licence.',
    'license_agreement_error' => 'You must agree to our terms of service',
    'license_agreement_checkbox' => 'I agree to the terms of service',

    'database-configuration_header' => 'Configure database',
    'database-configuration_field_host' => 'Database server:',
    'database-configuration_advanced_settings' => 'Show advanced settings',
    'database-configuration_field_port' => 'Database port:',
    'database-configuration_field_socket' => 'Database socket (optional):',
    'database-configuration_field_user' => 'Database user:',
    'database-configuration_field_password' => 'Database password:',
    'database-configuration_field_database' => 'Database name:',
    'database-configuration_info' => 'The access details for the database are required in order to install Shopware on your system. If you are not sure what to enter, contact your administrator / hosting service.',
    'database-configuration-create_new_database' => 'Create new database',

    'database-import_header' => 'Installation',
    'database-import_skip_import' => 'Skip',
    'database-import_progress' => 'Progress: ',
    'database-import-hint' => '<strong>Note: </strong> If Shopware tables already exist in the configured database, these will be removed by the installation/update!',
    'migration_counter_text_migrations' => 'Updating database',
    'migration_counter_text_snippets' => 'Updating text modules',
    'migration_update_success' => 'Database successfully imported!',

    'edition_header' => 'Have you purchased a Shopware licence?',
    'edition_info' => 'Shopware is available as a free <a href="https://en.shopware.com/pricing/" target="_blank">Community Edition</a>, and is also available as <a href="https://en.shopware.com/pricing/" target="_blank">Professional, Professional Plus or Enterprise Edition</a>, for a one-time fee.',
    'edition_ce' => 'No, I would like to use the free <a href="https://en.shopware.com/pricing/" target="_blank">Community Edition</a>.',
    'edition_cm' => 'Yes, I have purchased a Shopware licence (<a href="https://en.shopware.com/pricing/" target="_blank">Professional, Professional Plus or Enterprise</a>).',
    'edition_license' => 'Please enter your licence key here. You can find it in your Shopware account under "Merchant area" &rarr; "Shops" &rarr; [Click on your domain] &rarr; "Copy license key":',
    'edition_license_error' => 'A valid licence is required in order to install a fee-based Shopware version.',

    'configuration_header' => 'Basic shop set-up',
    'configuration_sconfig_text' => 'Almost done! Now you just need to make a few basic settings for your shop and the installation will be complete. Anything that you enter here can be changed later on.',
    'configuration_sconfig_name' => 'Name of your shop:',
    'configuration_sconfig_name_info' => 'Please enter the name of your shop',
    'configuration_sconfig_mail' => 'E-mail address of the shop:',
    'configuration_sconfig_mail_info' => 'Please enter your email address for outgoing e-mails',
    'configuration_sconfig_domain' => 'Shop domain:',
    'configuration_sconfig_language' => 'Main language:',
    'configuration_sconfig_currency' => 'Default currency:',
    'configuration_sconfig_currency_info' => 'This currency will be used as standard when setting item prices',
    'configuration_admin_currency_eur' => 'Euro',
    'configuration_admin_currency_usd' => 'Dollar (US)',
    'configuration_admin_currency_gbp' => 'Sterling (UK)',
    'configuration_admin_username' => 'Admin login name:',
    'configuration_admin_mail' => 'Admin e-mail:',
    'configuration_admin_name' => 'Admin name:',

    'configuration_admin_language_de' => 'Deutsch',
    'configuration_admin_language_en' => 'English',
    'configuration_admin_password' => 'Admin password:',

    'finish_header' => 'Installation complete',
    'finish_info' => 'You have successfully installed Shopware!',
    'finish_info_heading' => 'Hooray!',
    'finish_first_steps' => '"First steps" guide',
    'finish_frontend' => 'Go to shop frontend',
    'finish_backend' => 'Go to shop backend (administration)',
    'finish_message' => '
<p>
    <strong>Welcome to Shopware,</strong>
</p>
<p>
    We are delighted to welcome you to our community. You have successfully installed Shopware.
<p>Your shop is now ready to use. If you are new to Shopware, we recommend that you take a look at the guide <a href="https://docs.shopware.com/en/shopware-5-en/first-steps/first-steps-in-shopware" target="_blank">"First steps in Shopware"</a>. When you log in to the shop backend for the first time, our "First Run Wizard" will take you through some further basic settings.</p>
<p>Enjoy your new online shop!</p>',
];
