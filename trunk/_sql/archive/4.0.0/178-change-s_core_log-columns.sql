ALTER TABLE `s_core_log` CHANGE `datum` `date` DATETIME NOT NULL ,
CHANGE `value1` `user` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
CHANGE `value2` `ip_address` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
CHANGE `value3` `user_agent` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- //@UNDO

ALTER TABLE `s_core_log` CHANGE `date` `datum` DATETIME NOT NULL ,
CHANGE `user` `value1` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `ip_address` `value2` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `user_agent` `value3` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;