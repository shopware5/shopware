-- //

ALTER TABLE `s_categories` ADD INDEX ( `left` , `right` ) ;
ALTER TABLE `s_categories` ADD INDEX ( `level` ) ;
ALTER TABLE `s_articles_categories` DROP `categoryparentID`;
ALTER TABLE `s_articles_categories` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `articleID` `articleID` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `categoryID` `categoryID` INT( 11 ) UNSIGNED NOT NULL;

UPDATE `s_categories` SET `metakeywords` = NULL WHERE `metakeywords` = '';
UPDATE `s_categories` SET `metadescription` = NULL WHERE `metadescription` = '';
UPDATE `s_categories` SET `cmsheadline` = NULL WHERE `cmsheadline` = '';
UPDATE `s_categories` SET `cmstext` = NULL WHERE `cmstext` = '';
UPDATE `s_categories` SET `template` = NULL WHERE `template` = '';
UPDATE `s_categories` SET `ac_attr1` = NULL WHERE `ac_attr1` = '';
UPDATE `s_categories` SET `ac_attr2` = NULL WHERE `ac_attr2` = '';
UPDATE `s_categories` SET `ac_attr3` = NULL WHERE `ac_attr3` = '';
UPDATE `s_categories` SET `ac_attr4` = NULL WHERE `ac_attr4` = '';
UPDATE `s_categories` SET `ac_attr5` = NULL WHERE `ac_attr5` = '';
UPDATE `s_categories` SET `ac_attr6` = NULL WHERE `ac_attr6` = '';
UPDATE `s_categories` SET `external` = NULL WHERE `external` = '';

UPDATE `s_categories` SET `added` = NOW(), `changed` = NOW();

-- //@UNDO

ALTER TABLE s_categories DROP INDEX `left`;
ALTER TABLE s_categories DROP INDEX `level`;
ALTER TABLE `s_articles_categories` ADD `categoryparentID` INT( 11 ) UNSIGNED NOT NULL;

--