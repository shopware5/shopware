ALTER TABLE `s_core_countries_states` ADD `shortcode` VARCHAR( 255 ) NOT NULL AFTER `name`;
-- //@UNDO
ALTER TABLE `s_core_countries_states` DROP `shortcode;
