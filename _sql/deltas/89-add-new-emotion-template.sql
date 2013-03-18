
ALTER TABLE `s_emotion` DROP `template`;
ALTER TABLE  `s_emotion` ADD  `template_id` INT NULL;

UPDATE s_emotion SET template_id = 1;

CREATE TABLE IF NOT EXISTS `s_emotion_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emotion_templates`
--

INSERT INTO `s_emotion_templates` (`id`, `name`, `file`) VALUES
(1, 'Standard', 'index.tpl');

