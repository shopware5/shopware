-- //
ALTER TABLE `s_core_countries` ADD `display_state_in_registration` INT( 1 ) NOT NULL ,
ADD `force_state_in_registration` INT( 1 ) NOT NULL;
-- //@UNDO
ALTER TABLE `s_core_countries` DROP `force_state_in_registration`;
ALTER TABLE `s_core_countries` DROP `display_state_in_registration`;
-- //