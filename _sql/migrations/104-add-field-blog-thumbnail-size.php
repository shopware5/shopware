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

class Migrations_Migration104 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-blog');
INSERT INTO  `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text` ,`store`, `display_field`, `value_field`, `default_value`, `allow_blank`)
VALUES (NULL ,  @parent,  'thumbnail_size',  'textfield', '',  'Thumbnail-Größe',  'Thumbnail-Nummer, die verwendet werden soll. Im Standard stehen Ihnen 0 bis 3 zur Verfügung.',  '',  '',  '',  '',  '',  '2',  1);
EOD;

        $this->addSql($sql);
    }
}
