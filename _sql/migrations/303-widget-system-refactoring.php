<?php
class Migrations_Migration303 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
            SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'Widget' LIMIT 1);

            DELETE FROM s_core_config_element_translations
            WHERE element_id IN (
                SELECT id FROM s_core_config_elements WHERE form_id = @formId
            );

            DELETE FROM s_core_config_elements
            WHERE form_id = @formId;

            DELETE FROM s_core_config_form_translations
            WHERE form_id = @formId;

            DELETE FROM s_core_config_forms
            WHERE id = @formId;

            ALTER TABLE `s_core_widget_views` DROP `label`;

            ALTER TABLE `s_core_widgets` ADD `plugin_id` INT( 11 ) NULL ;

            ALTER TABLE `s_core_widgets` CHANGE `label` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ;
EOD;

        $this->addSql($sql);
    }
}
