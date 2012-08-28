-- //
CREATE TABLE IF NOT EXISTS `s_core_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author` text COLLATE utf8_unicode_ci NOT NULL,
  `license` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `esi_compatible` tinyint(1) unsigned NOT NULL,
  `style_assist_compatible` tinyint(1) unsigned NOT NULL,
  `emotions_compatible` tinyint(1) unsigned NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`basename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- //@UNDO

DROP TABLE s_core_templates;

--
