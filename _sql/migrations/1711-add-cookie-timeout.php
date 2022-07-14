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

class Migrations_Migration1711 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // Add configuration
        $sql = "SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Privacy' LIMIT 1);";
        $this->addSql($sql);

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(@formId, 'cookieTimeout', 'i:60;', 'Cookie nach X Tagen invalidieren', 'Invalidiert Cookies nach festgelegter Zeit', 'number', 1, 31, 0, NULL);
EOD;
        $this->addSql($sql);

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_element_translations`
    (`element_id`, `locale_id`, `label`, `description`)
VALUES
    (@elementId, '2', 'Invalidate Cookies after X days', 'Invalidates Cookies after set time');
EOD;
        $this->addSql($sql);
    }
}
