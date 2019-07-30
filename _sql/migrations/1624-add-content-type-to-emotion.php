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

class Migrations_Migration1624 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql('INSERT INTO `s_library_component` (`name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`)
VALUES (\'Content Type\', \'emotion-components-content-type\', NULL, \'\', \'component_content_type\', \'content-type-element\', NULL);
');

        $this->addSql('SET @elementId = LAST_INSERT_ID();');

        $this->addSql('INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `translatable`, `position`) VALUES
(NULL, @elementId, \'content_type\', \'shopware-form-field-content-type-selection\', \'\', \'Content Type Selection\', \'\', \'\', \'\', \'\', \'name\', \'internalName\', \'\', 0, 0, NULL),
(NULL, @elementId, \'ids\', \'hidden\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'1\', \'0\', NULL),
(NULL, @elementId, \'mode\', \'combobox\', \'\', \'Modus\', \'\', \'\', \'\', \'Shopware.apps.Emotion.store.ContentTypeMode\', \'name\', \'id\', \'\', 0, 0, NULL);');
    }
}
