<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

class Migrations_Migration1434 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $settings = [
            'valueField' => 'id',
            'displayValue' => 'name',
            'store' => 'base.ShopLanguage',
            'queryMode' => 'remote',
        ];

        $sql = <<<'EOD'
SET @formId = (SELECT id FROM s_core_config_forms WHERE name = "Frontend100");
INSERT IGNORE INTO `s_core_config_elements`
(`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`)
VALUES
(@formId, 'hrefLangDefaultShop', 's:0:"";', 'href-lang Standardsprache', 'Gibt für diesen Shop "x-default" im href-lang-Tag aus und definiert damit die Sprache dieses Shops als Standardsprache.', 'combo', 0, 0, 0, '%s');
EOD;
        $this->addSql(sprintf($sql, serialize($settings)));
    }
}
