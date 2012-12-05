-- //

ALTER TABLE `s_media` CHANGE `path` `path` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_media` ADD INDEX `path` ( `path` );

-- //@UNDO

ALTER TABLE s_media DROP INDEX path;
ALTER TABLE `s_media` CHANGE `path` `path` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //