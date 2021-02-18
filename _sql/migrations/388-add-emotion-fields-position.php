<?php
class Migrations_Migration388 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("ALTER TABLE s_library_component_field ADD position INT NULL;");
        $this->addSql("UPDATE s_library_component_field SET position = id;");
        $this->addSql("SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-article-slider' AND template = 'component_article_slider' AND pluginID IS NULL LIMIT 1);");
        $this->addSql("SET @maxNumberPosition = (SELECT id FROM `s_library_component_field` WHERE `name`='article_slider_max_number' AND componentID = @parent LIMIT 1);");
        $this->addSql("UPDATE s_library_component_field SET position = position+1 WHERE componentID = @parent AND id >= @maxNumberPosition;");
    }
}
