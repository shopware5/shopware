-- //

ALTER TABLE `s_core_auth` ADD `apiKey` VARCHAR( 40 ) NULL DEFAULT NULL AFTER `password`;

-- //@UNDO

ALTER TABLE `s_core_auth` DROP `apiKey`;

-- //
