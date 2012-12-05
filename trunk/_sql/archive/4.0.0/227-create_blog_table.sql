-- //
CREATE TABLE IF NOT EXISTS `s_blog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `authorID` int(11) DEFAULT NULL,
  `active` int(1) NOT NULL,
  `short_description` text COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `views` int(11) unsigned DEFAULT NULL,
  `display_date` datetime NOT NULL,
  `categoryID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO

DROP TABLE IF EXISTS `s_blog`;

-- //
