-- //
DROP TABLE `s_core_auth_files`;
-- //@UNDO
CREATE TABLE IF NOT EXISTS `s_core_auth_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `modID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;
-- //
