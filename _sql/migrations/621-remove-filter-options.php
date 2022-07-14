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

class Migrations_Migration621 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<EOD
DELETE s_core_config_elements, s_core_config_element_translations, s_core_config_values
FROM s_core_config_elements
LEFT JOIN s_core_config_element_translations ON s_core_config_element_translations.element_id = s_core_config_elements.id
LEFT JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id
WHERE s_core_config_elements.form_id = 0 AND name = "propertySorting"
EOD;
        $this->addSql($sql);

        $sql = <<<EOD
DELETE s_core_config_elements, s_core_config_element_translations, s_core_config_values
FROM s_core_config_elements
LEFT JOIN s_core_config_element_translations ON s_core_config_element_translations.element_id = s_core_config_elements.id
LEFT JOIN s_core_config_values ON s_core_config_values.element_id = s_core_config_elements.id
WHERE s_core_config_elements.form_id = 0 AND name = "displayFiltersOnDetailPage"
EOD;
        $this->addSql($sql);
    }
}
