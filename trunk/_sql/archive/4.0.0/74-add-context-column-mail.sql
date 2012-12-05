ALTER TABLE `s_core_config_mails` ADD `context` TEXT NULL;
-- //@UNDO
ALTER TABLE `s_core_config_mails` DROP `context`;
