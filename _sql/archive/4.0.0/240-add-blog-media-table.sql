-- //

CREATE TABLE IF NOT EXISTS `s_blog_media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blogID` int(11) unsigned NOT NULL,
  `mediaID` int(11) unsigned NOT NULL,
  `preview` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blogID` (`blogID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO

DROP TABLE IF EXISTS `s_blog_media`;

-- //
