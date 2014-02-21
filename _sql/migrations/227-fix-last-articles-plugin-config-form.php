<?php
class Migrations_Migration227 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up()
    {
        $sql = <<<'EOD'
SET @parent_form = (SELECT id FROM `s_core_config_forms` WHERE `name`='LastArticles');
SET @thumb_element = (SELECT id FROM s_core_config_elements WHERE form_id = @parent_form and name='thumb');
SET @localeID = (SELECT id FROM s_core_locales WHERE locale='en_GB');

UPDATE s_core_config_element_translations SET label = 'Thumbnail size', description = 'Index of the thumbnail size of the associated album to use. Starts at 0'
WHERE element_id = @thumb_element AND locale_id = @localeID AND label = 'Size of display' AND description IS NULL;

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent_form, 'time', 'i:15;', 'Speicherfrist in Tagen', NULL, 'number', 0, 0, 0, NULL, NULL, 'a:0:{}');

SET @time_element = (SELECT id FROM s_core_config_elements WHERE form_id = @parent_form and name='time');

INSERT IGNORE INTO `s_core_config_element_translations` (`element_id`, `locale_id`, `label`, `description`) VALUES
(@time_element, @localeID, 'Storage period in days', NULL);
EOD;
        $this->addSql($sql);
    }
}
