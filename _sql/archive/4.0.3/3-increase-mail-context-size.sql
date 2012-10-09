-- depends on SW-3940 - Increase size of mailcontext field in database

-- //

ALTER TABLE `s_core_config_mails` CHANGE `context` `context` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

-- //@UNDO

-- //
