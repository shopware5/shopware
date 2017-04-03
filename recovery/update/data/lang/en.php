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
    'title' => 'Shopware 5 - Update Script',
    'meta_text' => '<strong>Shopware update:</strong>',

    'tab_start' => 'Start update',
    'tab_check' => 'System requirements',
    'tab_migration' => 'Database migration',
    'tab_cleanup' => 'Cleanup',
    'tab_done' => 'Done',

    'start_update' => 'Start update',
    'configuration' => 'Configuration',

    'back' => 'Back',
    'forward' => 'Forward',
    'start' => 'Start',

    'select_language' => 'Select language',
    'select_language_choose' => 'Please choose',
    'select_language_de' => 'Deutsch',
    'select_language_en' => 'English',

    'noaccess_title' => 'Access denied',
    'noaccess_info' => 'Please add your IP address "<strong>%s</strong>" to the <strong>%s</strong> file to enable access.',

    'step2_header_files' => 'File & directory permissions',
    'step2_files_info' => 'The following files and directories must exist and be writable',
    'step2_files_delete_info' => 'The following directories have to be <strong>deleted</strong>',
    'step2_error' => 'Some system requirements are not met',
    'step2_php_info' => 'Your server must meet the following requirements in order to run Shopware',
    'step2_system_colcheck' => 'Check',
    'step2_system_colrequired' => 'Required',
    'step2_system_colfound' => 'Found',
    'step2_system_colstatus' => 'Status',

    'migration_progress_text' => 'Please start the database migration process by clicking the "Start" button',
    'migration_header' => 'Database migration',
    'migration_counter_text_migrations' => 'Database migration in progress',
    'migration_counter_text_snippets' => 'Update snippets',
    'migration_counter_text_unpack' => 'Update files',
    'migration_update_success' => 'Update complete',

    'cleanup_header' => 'File cleanup',
    'cleanup_disclaimer' => 'The following files belong to your previous Shopware version, but are no longer necessary after this update. Press "Forward" to remove them automatically and finish the update process. We recommend performing a backup before proceeding. <br /><strong>Depending on the amount of files this process may take some time.</strong>',
    'cleanup_error' => 'The following files could not be deleted. Please delete them manually or ensure that your web server user has enough permissions to do so, and press "Forward" to resume the update process.',

    'done_title' => 'Finished',
    'done_info' => 'The update has been finished successfully',
    'done_delete' => 'Your shop is currently in maintenance mode.<br/>Please delete the updater (/update-assets) from your server via FTP.',
    'done_frontend' => 'Open shop frontend',
    'done_backend' => 'Open shop backend',
    'done_template_changed' => '<b>Emotion Template has been deactivated!</b><br/>At least one of your shops is currently using an emotion based (document-) template, which is no longer supported in Shopware 5.2. Shops that were using the emotion template have been automatically configured to use the new responsive theme.',
    'deleted_files' => '&nbsp;deleted files',
    'cache_clear_error' => 'An error has occurred. Please delete the cache after update manually.',
];
