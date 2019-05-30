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

class Migrations_Migration1218 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add privacy-category
        $this->addSql("INSERT INTO `s_core_config_forms` ( `parent_id`, `name`, `label` ) VALUES ( '92', 'Privacy', 'Datenschutz' );");

        $this->addSql('SET @formId = LAST_INSERT_ID();');

        // Translation for the privacy-category
        $sql = "INSERT INTO `s_core_config_form_translations` (`form_id`, `locale_id`, `label`)
                VALUES (@formId, '2', 'Privacy');";
        $this->addSql($sql);

        // Change several element categories to the new privacy-category
        $sql = "UPDATE `s_core_config_elements`
                SET form_id = @formId, position = '10'
                WHERE name IN ('ignoreagb', 'actdprcheck', 'newsletterextendedfields', 'optinnewsletter', 'optinvote', 'sendRegisterConfirmation', 'show_cookie_note', 'data_privacy_statement_link')";
        $this->addSql($sql);

        // Renaming labels
        $sql = "UPDATE `s_core_config_elements`
                SET label = 'Datenschutzhinweise müssen über Checkbox akzeptiert werden'
                WHERE name = 'actdprcheck'";
        $this->addSql($sql);

        $sql = "UPDATE `s_core_config_elements`
                SET label = 'Link zur Datenschutzerklärung für Cookies'
                WHERE name = 'data_privacy_statement_link'";
        $this->addSql($sql);

        $sql = "UPDATE `s_core_config_element_translations`
                SET label = 'Link to the data privacy statement for cookies'
                WHERE element_id = ( SELECT id FROM `s_core_config_elements` WHERE name = 'data_privacy_statement_link' LIMIT 1 ) and locale_id = 2";
        $this->addSql($sql);

        // Move the ACTDPR-Elements together
        $this->addSql("UPDATE `s_core_config_elements` SET position = '1' WHERE name = 'actdprcheck'");

        // Pre-Sort of the last elements to keep them at the end
        $this->addSql("UPDATE `s_core_config_elements` SET position = '20' WHERE name IN ( 'sendRegisterConfirmation', 'show_cookie_note', 'data_privacy_statement_link' )");

        // Inserts new option to show the Privacy-Texts
        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                    VALUES (@formId, 'actdprtext', 'b:1;', 'Datenschutzhinweise anzeigen', 'Betrifft die Formulare der Registrierung, Blog- & Artikelkommentare, Newsletter, Produkt-Verfügbarkeitsbenachrichtigung (Notification-Plugin) sowie die eigenen Formulare', 'boolean', '0', '0', '0', NULL);";
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        // Inserts old Values on Update
        if ($modus === self::MODUS_UPDATE) {
            $this->addSql("SET @checkboxId = ( SELECT id FROM `s_core_config_elements` WHERE name = 'actdprcheck' LIMIT 1 );");
            $this->addSql("SET @privacyValue = ( SELECT IFNULL((SELECT value FROM `s_core_config_values` WHERE element_id = @checkboxId), 'b:0;') );");
            $this->addSql('INSERT INTO `s_core_config_values` ( element_id, shop_id, value ) VALUES ( @elementId, 1, @privacyValue ) ');
        }

        // Translation for the new menu element
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@elementId, '2', 'Data protection information will be shown', 'Affects the registration, blog & product comments, newsletters and the product notification plugin form, but also your own forms');";
        $this->addSql($sql);

        // Delete now empty "Cookie hint"-Category
        $this->addSql("SET @cookieId = ( SELECT id FROM `s_core_config_forms` WHERE name = 'CookiePermission' LIMIT 1 );");

        $this->addSql('DELETE FROM `s_core_config_forms` WHERE id = @cookieId');
        $this->addSql('DELETE FROM `s_core_config_form_translations` WHERE form_id = @cookieId');
    }
}
