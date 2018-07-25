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
class Migrations_Migration1230 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Get Privacy formId
        $this->addSql("SET @formId = ( SELECT id FROM `s_core_config_forms` WHERE name = 'Privacy' LIMIT 1 );");
        $this->addSql('UPDATE s_core_config_elements SET position = 21 WHERE name = "show_cookie_note" AND form_id = @formId');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES ( @formId, 'cookie_note_mode', 'i:0;', 'Cookie-Hinweis-Modus', NULL, 'select', '0', '21', '0', 'a:2:{s:5:\"store\";s:35:\"Shopware.apps.Base.store.CookieMode\";s:9:\"queryMode\";s:5:\"local\";}')";
        $this->addSql($sql);

        // Translation
        $sql = "INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES ( LAST_INSERT_ID(), 2, 'Cookie notice mode', NULL )";
        $this->addSql($sql);
    }
}
