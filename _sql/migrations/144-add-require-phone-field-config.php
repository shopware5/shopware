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

class Migrations_Migration144 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'requirePhoneField', 'b:1;', 'Telefon als Pflichtfeld behandeln', 'Beachten Sie, dass Sie die Sternchenangabe über den Textbaustein RegisterLabelPhone konfigurieren müssen', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'requirePhoneField' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Treat phone field as required', 'Note that you must configure the asterisk indication in the snippet RegisterLabelPhone' );
EOD;
        $this->addSql($sql);
    }
}
