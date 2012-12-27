-- //
DROP TABLE IF EXISTS s_statistics_search_backup;

CREATE TABLE IF NOT EXISTS `s_statistics_search_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) CHARACTER SET latin1 NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `searchterm` (`searchterm`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;


RENAME TABLE s_statistics_search TO s_statistics_search_backup;
INSERT INTO s_statistics_search_new
(SELECT * FROM s_statistics_search_backup);
RENAME TABLE s_statistics_search_new TO s_statistics_search;
DROP TABLE s_statistics_search_backup;
-- //@UNDO

-- //

