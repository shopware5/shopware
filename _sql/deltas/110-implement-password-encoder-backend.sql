-- //

ALTER TABLE  `s_core_auth` ADD  `encoder` VARCHAR( 255 ) NOT NULL DEFAULT  'LegacyBackendMd5' AFTER  `password`;
ALTER TABLE  `s_core_auth` CHANGE  `password`  `password` VARCHAR( 255 ) NOT NULL;

-- //@UNDO

-- //


