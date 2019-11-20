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

class Migrations_Migration1648 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Get Privacy formId
        $this->addSql("SET @formId = ( SELECT id FROM `s_core_config_forms` WHERE name = 'Privacy' LIMIT 1 );");

        // Add Double-Opt-In optin in Backend
        $sql = "INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES (@formId, 'cookie_show_button', 'b:0;', '\"Alle akzeptieren\" Button in Cookie Hinweis anzeigen', 'Gilt nur für den Modus \"Technisch notwendige Cookies\". Bevor Du diese Einstellung änderst, solltest du die Verwendung vorab von Deiner Rechtsberatung prüfen lassen.', 'boolean', '0', '22', '0', NULL )";
        $this->addSql($sql);

        $sql = <<<SQL
            SET @lastInsertId = (SELECT LAST_INSERT_ID());
            SET @elementId = (SELECT IF(@lastInsertId, @lastInsertId, (SELECT id FROM s_core_config_elements element WHERE name='cookie_show_button')));
SQL;
        $this->addSql($sql);

        // Translation
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@elementId, 2, 'Show \"Accept all\" button in cookie hint', 'Only counts for the mode \"Technically necessary cookies\". Before you change this setting, you should first have your legal advisor check the use of this setting.')";
        $this->addSql($sql);
    }
}
