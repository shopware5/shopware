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

class Migrations_Migration225 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider' AND template = 'component_banner_slider' AND pluginID IS NULL LIMIT 1);
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'banner_slider_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-manufacturer-slider' AND template = 'component_manufacturer_slider' AND pluginID IS NULL LIMIT 1);
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'manufacturer_slider_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-html-element' AND template = 'component_html' AND pluginID IS NULL LIMIT 1);
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'cms_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner' AND template = 'component_banner' AND pluginID IS NULL LIMIT 1);
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'link' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-article-slider' AND template = 'component_article_slider' AND pluginID IS NULL LIMIT 1);
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'article_slider_title' AND componentID = @parent;

EOD;
        $this->addSql($sql);
    }
}
