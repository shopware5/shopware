-- Remove export_settings database tables from default
DROP TABLE s_export_settings;

-- //@UNDO
--

CREATE TABLE IF NOT EXISTS `s_export_settings` (
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `feedID` int(11) NOT NULL,
  PRIMARY KEY (`name`,`feedID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
