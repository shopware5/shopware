-- //

/**
 * @author Jens Schwehn
 * @since 4.0.0 - 2012/02/13
 */
ALTER TABLE `s_emarketing_banners` CHANGE `valid_to` `valid_to` DATETIME NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `s_emarketing_banners` CHANGE `valid_from` `valid_from` DATETIME NULL DEFAULT '0000-00-00 00:00:00';
-- //@UNDO
ALTER TABLE `s_emarketing_banners` CHANGE `valid_to` `valid_to` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `s_emarketing_banners` CHANGE `valid_from` `valid_from` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
-- //