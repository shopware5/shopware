-- //
ALTER TABLE `s_media_album_settings` CHANGE `icon` `icon` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_media_album_settings` CHANGE `thumbnail_size` `thumbnail_size` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
-- //@UNDO
ALTER TABLE `s_media_album_settings` CHANGE `thumbnail_size` `thumbnail_size` VARCHAR( 50 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_media_album_settings` CHANGE `icon` `icon` VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
-- //