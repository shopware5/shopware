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
class Migrations_Migration1708 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @formId = ( SELECT id FROM `s_core_config_forms` WHERE name = 'Frontend30' LIMIT 1 );");

        $sql = "INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
            VALUES (@formId, 'hide_categories_in_offcanvas', 'b:0;', 'Kategorien auch im Offcanvas-Menü ausblenden', 'Wird eine Kategorie nicht in der Top-Navigation angezeigt, wird sie mit dieser Option auch nicht im Offcanvas-Menü gezeigt.', 'boolean', '0', '0', '0', NULL)";
        $this->addSql($sql);

        $sql = <<<SQL
            SET @lastInsertId = (SELECT LAST_INSERT_ID());
            SET @elementId = (SELECT IF(@lastInsertId, @lastInsertId, (SELECT id FROM s_core_config_elements element WHERE name='hide_categories_in_offcanvas')));
SQL;
        $this->addSql($sql);

        // Translation
        $sql = "INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@elementId, 2, 'Hide categories in off-canvas menu aswell', 'If a category is hidden from the top navigation, it won\'t be displayed in the off-canvas menu aswell, if this option is active.')";
        $this->addSql($sql);
    }
}
