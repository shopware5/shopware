ALTER TABLE `s_core_templates` DROP `created`, DROP `updated`;
ALTER TABLE `s_core_templates` CHANGE `author` `author` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //@UNDO