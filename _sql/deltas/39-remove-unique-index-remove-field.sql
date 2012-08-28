--  //
ALTER TABLE s_cms_support DROP INDEX name;
ALTER TABLE `s_cms_support_fields` DROP `vtyp`;
-- //@UNDO
ALTER TABLE `s_cms_support` ADD UNIQUE (`name`);
ALTER TABLE `s_cms_support_fields` ADD `vtyp` VARCHAR( 255 ) NULL AFTER `value`;
-- //