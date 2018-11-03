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
    'meta_text' => '<strong>Shopware-Update:</strong>',

    'tab_start' => 'Actualisatie starten',
    'tab_check' => 'Systeemvereisten',
    'tab_migration' => 'Database migratie',
    'tab_cleanup' => 'Opruimen',
    'tab_done' => 'Gereed',

    'start_update' => 'Actualisatie uitvoeren',
    'configuration' => 'Configuratie',

    'back' => 'Terug',
    'forward' => 'Verder',
    'start' => 'Starten',

    'select_language' => 'Selecteer taal',
    'select_language_choose' => 'Kies taal',
    'select_language_de' => 'Duits',
    'select_language_en' => 'Engels',

    'noaccess_title' => 'Toegang geweigerd',
    'noaccess_info' => 'Voeg alstublieft uw IP adres toe "<strong>%s</strong>" de data <strong>%s</strong>',

    'step2_header_files' => 'Bestanden en Folders',
    'step2_files_info' => 'De volgende Bestanden en Folders moeten beschikbaar zijn en de juiste rechten bezitten',
    'step2_files_delete_info' => 'De volgende Folders moeten <strong>verwijderd</strong> worden.',
    'step2_error' => 'Sommige vereisten zijn niet vervuld',
    'step2_php_info' => 'Uw server moet de volgende systeemvereisten vervullen, voordat Shopware uit te voeren is.',
    'step2_system_colcheck' => 'Voorwaarde',
    'step2_system_colrequired' => 'Vereiste',
    'step2_system_colfound' => 'Uw Systeem',
    'step2_system_colstatus' => 'Status',

    'migration_progress_text' => 'Start u alstublieft uw Database-update met een klik op de knop "Starten"',
    'migration_header' => 'Database Update uitvoeren',
    'migration_counter_text_migrations' => 'Database Update word uitgevoerd',
    'migration_counter_text_snippets' => 'Tekstblokken worden geactualiseerd',
    'migration_counter_text_unpack' => 'Bestanden worden uitgepakt',
    'migration_update_success' => 'De update wordt succesvol uitgevoerd',

    'cleanup_header' => 'Opruimen',
    'cleanup_disclaimer' => 'De volgende bestanden behoren tot een oudere Shopware versie en worden na deze update niet langer geupdate. Drukt u op Verder om de bestanden automatisch te verwijderen en de update te beeindigen. Wij raden aan om een backup te creëren. <br /><strong>Afhankelijk van de hoeveelheid data, kan dit proces een bepaalde extra tijd duren</strong>',
    'cleanup_error' => 'De volgende bestanden kunnen niet verwijderd worden. U kunt deze manueel verwijderen, of zorg ervoor dat uw webserver genoeg rechten bezit om deze bestanden te verwijderen. Klik op de knop "Verder" om de update voort te zetten.',

    'done_title' => 'Actualisatie uitgevoerd ',
    'done_info' => 'De actualisatie werd succesvol afgesloten.',
    'done_delete' => 'Uw shop bevind zich momenteel in onderhoudsmodus. <br/>Verwijder de updater (/update-assets) alleen via FTP van de server.',
    'done_frontend' => 'Naar het Shop-Frontend',
    'done_backend' => 'Naar het Shop-Backend (Administratie)',
    'done_template_changed' => 'Men heeft ontdekt, dat je een (document-) template op basis van emotie gebruikt, die niet langer verenigbaar is sinds Shopware 5.2. Dus worden je winkels, die nog het oude template gebruiken, aangepast aan de nieuwe responsieve thema.',
    'deleted_files' => '&nbsp;verwijderd bestanden',
    'cache_clear_error' => 'Fout opgetreden. De cache moet na het update handmatig worden vernieuwd.',
];
