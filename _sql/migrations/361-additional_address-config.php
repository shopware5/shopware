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

class Migrations_Migration361 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
        SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Frontend33' LIMIT 1);

        /* add show additional address lines config */
        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'showAdditionAddressLine1', 'b:0;', 'Adresszusatzzeile 1 anzeigen', '', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'showAdditionAddressLine2', 'b:0;', 'Adresszusatzzeile 2 anzeigen', '', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showAdditionAddressLine1' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Show additional address line 1', '' );

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'showAdditionAddressLine2' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Show additional address line 2', '' );


        /* add require additional address lines config */
        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'requireAdditionAddressLine1', 'b:0;', 'Adresszusatzzeile 1 als Pflichtfeld behandeln', '', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
        (NULL, @parent, 'requireAdditionAddressLine2', 'b:0;', 'Adresszusatzzeile 2 als Pflichtfeld behandeln', '', 'checkbox', 0, 0, 1, NULL, NULL, 'a:0:{}');

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'requireAdditionAddressLine1' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Treat additional address line 1 as required', '' );

        SET @elementId = (SELECT id FROM `s_core_config_elements` WHERE `name` = 'requireAdditionAddressLine2' LIMIT 1);
        INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
        VALUES (@elementId, '2', 'Treat additional address line 2 as required', '' );
EOD;
        $this->addSql($sql);
    }
}
