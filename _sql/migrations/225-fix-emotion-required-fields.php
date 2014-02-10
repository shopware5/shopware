<?php
class Migrations_Migration225 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider');
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'banner_slider_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-manufacturer-slider');
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'manufacturer_slider_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-html-element');
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'cms_title' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner');
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'link' AND componentID = @parent;

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-article-slider');
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'article_slider_title' AND componentID = @parent;

EOD;
        $this->addSql($sql);
    }
}
