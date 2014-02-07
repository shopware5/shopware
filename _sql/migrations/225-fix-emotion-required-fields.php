<?php
class Migrations_Migration225 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
UPDATE s_library_component_field
SET allow_blank = '1'
WHERE name IN (
    'banner_slider_title',
    'manufacturer_slider_title',
    'article_slider_title'
);
EOD;
        $this->addSql($sql);
    }
}
