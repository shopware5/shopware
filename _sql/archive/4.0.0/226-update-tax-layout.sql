-- //

DROP TABLE IF EXISTS `s_core_tax_groups`;

DELETE FROM `s_core_tax_rules`;

ALTER TABLE `s_core_tax` CHANGE `tax` `tax` DECIMAL( 10, 2 ) NOT NULL;

ALTER TABLE `s_core_tax_rules` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `areaID` `areaID` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `countryID` `countryID` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `stateID` `stateID` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `groupID` `groupID` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `tax` `tax` DECIMAL( 10, 2 ) NOT NULL ,
CHANGE `name` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `active` `active` INT( 1 ) UNSIGNED NOT NULL;

ALTER TABLE `s_core_tax_rules` ADD `customer_groupID` INT( 11 ) UNSIGNED NOT NULL AFTER `groupID`;

-- //@UNDO

ALTER TABLE `s_core_tax_rules` DROP `customer_groupID`;

-- //
