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
class Migrations_Migration1628 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('UPDATE `s_core_config_elements` 
SET `position` = `id`
WHERE `form_id` = (
    SELECT `id` FROM `s_core_config_forms` WHERE `name` = \'Frontend33\' LIMIT 1
)');

        $this->addSql('SET @parentId = (SELECT id FROM `s_core_config_forms` WHERE name = \'Frontend33\' LIMIT 1)');
        
        $this->addSql('INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`)
VALUES (@parentId, \'birthdaySingleField\', \'b:0;\', \'Geburtstag als Datumsfeld anzeigen\', \'Wenn aktiv, wird das Geburtsdatum als einzelnes Datumsfeld dargestellt, statt drei einzelnen Feldern.\', \'boolean\', 1, 0, 0);
');

        $this->addSql('SET @configId = LAST_INSERT_ID();');
        $this->addSql('SET @positionId = (SELECT id FROM `s_core_config_elements` WHERE name = \'requirebirthdayfield\' LIMIT 1);');

        $this->addSql('UPDATE `s_core_config_elements` 
SET position = @positionId
WHERE `id` = @configId
');

        $this->addSql('INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@configId, 1, \'Geburtstag als Datumsfeld anzeigen\', \'Wenn aktiv, wird das Geburtsdatum als einzelnes Datumsfeld dargestellt, statt drei einzelnen Feldern\');
');

        $this->addSql('INSERT INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
VALUES (@configId, 2, \'Display birthday as a date field\', \'If active, the birthdate will be displayed as a date field, rather than three single fields.\');
');
    }
}
