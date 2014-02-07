<?php
class Migrations_Migration225 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'banner_slider_title' AND componentID = 7;
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'manufacturer_slider_title' AND componentID = 10;
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'cms_title' AND componentID = 2;
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'link' AND componentID = 3;
UPDATE s_library_component_field SET allow_blank = '1' WHERE name = 'article_slider_title' AND componentID = 11;

EOD;
        $this->addSql($sql);
    }
}
