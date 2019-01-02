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
class Migrations_Migration1413 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Enable or Disable Hreflang
        $this->addSql('SET @formId = (SELECT id FROM s_core_config_forms WHERE name = "Frontend100")');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                    VALUES (@formId, 'hrefLangEnabled', 'b:1;', 'href-lang in den Meta-Tags ausgeben', 'Wenn aktiv, werden in den Meta Tags alle Sprachen einer Seite ausgegeben', 'boolean', '0', '0', '0', NULL);";
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $this->addSql('INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES (@elementId, \'2\', \'Output href-lang in the meta tags\', \'If active, all languages of a page are displayed in the the meta tags\');');

        // Show only language (de) instead "de-de"

        $this->addSql('SET @formId = (SELECT id FROM s_core_config_forms WHERE name = "Frontend100")');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                    VALUES (@formId, 'hrefLangCountry', 'b:1;', 'Im href-lang Sprache und Land verwenden', 'Wenn diese Option aktiviert ist, wird zusätzlich zur Sprache auch das Land ausgegeben, z.B. \"de-DE\" anstatt \"de\"', 'boolean', '0', '0', '0', NULL);";
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $this->addSql('INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES (@elementId, \'2\', \'Use language and country in href-lang\', \'If this option is activated, the country is output in addition to the language, e.g. "en-GB" instead of "en"\');');
    }
}
