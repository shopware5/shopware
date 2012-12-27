ALTER TABLE `s_cms_static` ADD `parentID` INT( 11 ) NOT NULL DEFAULT '0';
-- //@UNDO
ALTER TABLE `s_cms_static` DROP `parentID`;