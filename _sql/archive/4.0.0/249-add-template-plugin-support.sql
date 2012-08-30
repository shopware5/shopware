-- //

ALTER TABLE `s_core_templates` ADD `plugin_id` INT( 11 ) UNSIGNED NULL AFTER `emotion`;

-- //@UNDO

ALTER TABLE `s_core_templates` DROP `plugin_id`;

-- //
