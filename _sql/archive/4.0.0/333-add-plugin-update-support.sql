--

ALTER TABLE `s_core_plugins` DROP `checkversion` ,
DROP `checkdate`;

ALTER TABLE `s_core_plugins` CHANGE `description` `description` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `description_long` `description_long` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

ALTER TABLE `s_core_plugins` CHANGE `copyright` `copyright` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `license` `license` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `support` `support` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `changes` `changes` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `link` `link` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

ALTER TABLE `s_core_plugins` CHANGE `autor` `author` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `s_core_plugins` ADD `store_version` VARCHAR( 255 ) NULL ,
ADD `store_date` DATETIME NULL;

ALTER TABLE `s_core_plugins` ADD `capability_update` INT( 1 ) NOT NULL ,
ADD `capability_install` INT( 1 ) NOT NULL ,
ADD `capability_enable` INT( 1 ) NOT NULL;

ALTER TABLE `s_core_plugins` ADD `refresh_date` DATETIME NULL AFTER `update_date`;

-- //@UNDO

ALTER TABLE `s_core_plugins` ADD `checkversion` VARCHAR( 255 ) NOT NULL ,
ADD `checkdate` DATE NOT NULL;

ALTER TABLE `s_core_plugins` CHANGE `author` `autor` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

ALTER TABLE `s_core_plugins` DROP `store_version` ,
DROP `store_date`;

ALTER TABLE `s_core_plugins` DROP `capability_update` ,
DROP `capability_install` ,
DROP `capability_enable`;

ALTER TABLE `s_core_plugins` DROP `refresh_date`;




-- //
