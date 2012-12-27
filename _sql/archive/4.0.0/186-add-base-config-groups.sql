
-- Add form scope field
ALTER TABLE `s_core_config_forms` ADD `scope` INT( 11 ) UNSIGNED NOT NULL AFTER `position`;
UPDATE s_core_config_forms f SET f.scope = (SELECT MAX(scope) FROM s_core_config_elements WHERE form_id=f.id);

-- //@UNDO

ALTER TABLE `s_core_config_forms` DROP `scope`;