CREATE TABLE IF NOT EXISTS `s_core_detail_states` (
  `id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_core_detail_states`
--

INSERT INTO `s_core_detail_states` (`id`, `description`, `position`, `mail`) VALUES
(0, 'Offen', 1, 0),
(1, 'In Bearbeitung', 2, 0),
(2, 'Storniert', 3, 0),
(3, 'Abgeschlossen', 4, 0);

-- //@UNDO

DROP TABLE s_core_detail_states;