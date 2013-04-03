-- //

CREATE TABLE IF NOT EXISTS `s_core_sessions_backend` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `expireref` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `expiry` (`expiry`),
  KEY `expireref` (`expireref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- //@UNDO
  DROP TABLE IF EXISTS s_core_sessions_backend;
-- //
