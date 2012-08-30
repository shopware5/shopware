-- //

ALTER TABLE `s_categories` ADD `left` INT( 11 ) UNSIGNED NOT NULL AFTER `position` ,
ADD `right` INT( 11 ) UNSIGNED NOT NULL AFTER `left`;
ALTER TABLE `s_categories` ADD `level` INT( 11 ) UNSIGNED NOT NULL AFTER `right`;

ALTER TABLE `s_categories` CHANGE `parent` `parent` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `s_categories` CHANGE `alias` `alias` INT(11) NULL DEFAULT '0',
CHANGE `metakeywords` `metakeywords` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `metadescription` `metadescription` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `cmsheadline` `cmsheadline` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `cmstext` `cmstext` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `template` `template` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `noviewselect` `noviewselect` INT(1) UNSIGNED NULL,
CHANGE `aliassql` `aliassql` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr1` `ac_attr1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr2` `ac_attr2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr3` `ac_attr3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr4` `ac_attr4` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr5` `ac_attr5` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `ac_attr6` `ac_attr6` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
CHANGE `external` `external` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

ALTER TABLE `s_categories` DROP `alias`, DROP `aliassql`;
ALTER TABLE `s_categories` ADD `changed` DATETIME NOT NULL AFTER `level`;

ALTER TABLE `s_categories` ADD `added` DATETIME NOT NULL AFTER `level`;
UPDATE `s_categories` SET `added` = NOW();

INSERT INTO `s_categories` (
  `id`, `parent`, `description`, `position`, `active`, `left`, `right`
)
VALUES (
  1, NULL, 'Root', '0', 1, 1, 2
);

-- //@UNDO

DELETE FROM `s_categories` WHERE `s_categories`.`id` = 1;

ALTER TABLE `s_categories`
  DROP `left`,
  DROP `right`,
  DROP `level`,
  DROP `added`,
  DROP `changed`;

ALTER TABLE `s_categories`
  ADD `alias` VARCHAR( 255 ) NOT NULL ,
  ADD `aliassql` VARCHAR( 255 ) NOT NULL;

--