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

class Migrations_Migration605 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->insertConfigElements();
        $this->insertConfigElementTranslations();
    }

    /**
     * inserts the config elements
     */
    private function insertConfigElements()
    {
        $this->addSql("SET @formId = (SELECT id FROM `s_core_config_forms` WHERE `name` = 'Search' LIMIT 1);");
        $sql = <<<SQL
INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
VALUES (NULL, @formId, 'enableAndSearchLogic', 'b:0;', '"Und" Suchlogik verwenden', 'Die Suche zeigt nur Treffer an, in denen alle Suchbegriffe vorkommen.', 'checkbox', '0', '0', '1', NULL, NULL);
SQL;
        $this->addSql($sql);
    }

    /**
     * inserts the config element translations
     */
    private function insertConfigElementTranslations()
    {
        $this->addSql("SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'enableAndSearchLogic' LIMIT 1);");
        $sql = <<<SQL
INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
VALUES (NULL, @elementId, '2', 'Use "and" search logic', 'The search will only return results that match all the search terms.');
SQL;
        $this->addSql($sql);
    }
}
