-- Remove export deprecated columns
ALTER TABLE `s_export` DROP `image`;
ALTER TABLE `s_export` DROP `link`;
ALTER TABLE `s_export` DROP `inform_template`;
ALTER TABLE `s_export` DROP `inform_mail`;
-- //@UNDO
--

ALTER TABLE `s_export` CHANGE `image` `image` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_export` CHANGE `link` `link` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_export` CHANGE `inform_template` `inform_template` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `s_export` CHANGE `inform_mail` `inform_mail` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;