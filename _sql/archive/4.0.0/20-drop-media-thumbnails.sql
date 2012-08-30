-- //
DROP TABLE s_media_thumbnails;
-- //@UNDO
CREATE TABLE IF NOT EXISTS `s_media_thumbnails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) NOT NULL,
  `path` text COLLATE utf8_unicode_ci NOT NULL,
  `size` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mediaID` (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
-- //
