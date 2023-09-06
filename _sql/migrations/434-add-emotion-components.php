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

class Migrations_Migration434 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        // add banner element for new emotion module
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='banner-element' LIMIT 1);
EOD;
        $this->addSql($sql);

        // Add banner position configuration for the banner widget
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
VALUES (@parent, 'bannerPosition', 'hidden', '', '', '', '', '', '', '', '', 'center', '0', NULL);
EOD;
        $this->addSql($sql);

        // Add article element for new emotion module
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='article-element' LIMIT 1);
EOD;
        $this->addSql($sql);

        // Add configuration options for the article widget
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
VALUES (@parent, 'productImageOnly', 'checkboxfield', '', 'Nur Produktbild', 'Bei aktivierter Einstellung wird nur das Produktbild dargestellt.', '', '', '', 'label', 'key', '', '0', '10');
EOD;
        $this->addSql($sql);

        // Add new html 5 video element for new emotion module
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='emotion--element-video' LIMIT 1);
EOD;
        $this->addSql($sql);

        // Add configuration for the html 5 video element
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
VALUES (@parent, 'videoMode', 'emotion-components-fields-video-mode', '', 'Modus', 'Bestimmen Sie das Verhalten des Videos. Legen Sie fest, ob das Video skalierend, füllend oder gestreckt dargestellt werden soll.', '', '', '', 'label', 'key', '', '0', 40);
EOD;
        $this->addSql($sql);

        // Get blog component
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `cls`='blog-element' LIMIT 1);
EOD;
        $this->addSql($sql);

        // Add category selection for the blog entry widget
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_library_component_field` (`componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `position`)
VALUES (@parent, 'blog_entry_selection', 'emotion-components-fields-category-selection', '', 'Kategorie', '', '', '', '', 'label', 'key', '', '0', '10');
EOD;
        $this->addSql($sql);
    }
}
