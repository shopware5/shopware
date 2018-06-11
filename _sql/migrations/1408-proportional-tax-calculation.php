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
class Migrations_Migration1408 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('SET @formId = (SELECT id FROM s_core_config_forms WHERE name = "Frontend79")');

        $sql = "INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
                    VALUES (@formId, 'proportionalTaxCalculation', 'b:0;', 'Anteilige Berechnung der Steuer-Positionen', '', 'boolean', '0', '0', '0', NULL);";
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $this->addSql('INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`) VALUES (@elementId, \'2\', \'Proportional calculation of tax positions\');');

        $this->addSql('ALTER TABLE `s_order` ADD COLUMN `is_proportional_calculation` TINYINT NOT NULL DEFAULT \'0\' AFTER `deviceType`;');
    }
}
