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

class Migrations_Migration387 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
            SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend79' LIMIT 1);

            INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`) VALUES
                (@formId, 'basketShowCalculation', 'b:1;', 'Versandkostenberechnung im Warenkob anzeigen', 'Bei aktivierter Einstellung wird ein Versandkostenrechner auf der Warenkorbseite dargestellt. Diese Funktion ist nur für nicht angemeldete Kunden verfügbar.', 'boolean', 0, 0, 1, NULL, NULL);

            SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'basketShowCalculation' LIMIT 1);

            INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
                VALUES (@elementId, 2, 'Show shipping fee calculation in shopping cart', 'If enabled, a shipping cost calculator will be displayed in the cart page. This is only available for customers who haven''t logged in');
EOD;
        $this->addSql($sql);
    }
}
