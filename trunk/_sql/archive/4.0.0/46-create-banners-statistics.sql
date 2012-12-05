-- //
CREATE TABLE IF NOT EXISTS `s_emarketing_banners_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) NOT NULL,
  `display_date` date NOT NULL,
  `clicks` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_date` (`bannerID`,`display_date`),
  KEY `bannerID` (`bannerID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO
DROP TABLE `s_emarketing_banners_statistics`;

-- //
