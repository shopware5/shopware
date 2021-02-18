<?php
class Migrations_Migration469 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addSql("SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'RelatedArticlesOnArticleNotFound' LIMIT 1);");

        $this->addSql("
            INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`)
            VALUES (@elementId, '2', 'Display related articles on \"Article not found\" page', 'If enabled, \"Article not found\" page will display related articles suggestions. Disable to use the standard \"Page not found\" page');
        ");
    }
}
