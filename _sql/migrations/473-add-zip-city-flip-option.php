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

class Migrations_Migration473 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        SET @form = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
            INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
            (@form, 'showZipBeforeCity', 'b:1;', 'PLZ vor dem Stadtfeld anzeigen', 'Legt fest ob die PLZ vor oder nach der Stadt angezeigt werden soll. Nur für Shopware 5 Themes.', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showZipBeforeCity' LIMIT 1);
EOD;
        $this->addSql($sql);

        $sql = <<<'EOD'
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Show zip code field before city field', 'Determines if the zip code field should be shown before or after the the city field. Only applicable for Shopware 5 themes');
EOD;
        $this->addSql($sql);
    }
}
