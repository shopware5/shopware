-- //

CREATE TABLE `s_core_error_reporter` (
`id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`message` VARCHAR( 255 ) NOT NULL ,
`filename` VARCHAR( 255 ) NOT NULL ,
`linenumber` INT( 11 ) NOT NULL ,
`created` DATETIME NOT NULL
) ENGINE = MYISAM CHARACTER SET latin1 COLLATE latin1_bin;

-- //@UNDO

DROP TABLE IF EXISTS `s_core_error_reporter`;

-- //