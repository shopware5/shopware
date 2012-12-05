ALTER TABLE `s_core_config_mails` DROP `htmlable`;
-- //@UNDO
ALTER TABLE `s_core_config_mails` ADD `htmlable` INT( 1 ) NOT NULL AFTER `ishtml`;
