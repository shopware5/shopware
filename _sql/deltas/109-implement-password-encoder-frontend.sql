-- //

ALTER TABLE  `s_user` ADD  `encoder` VARCHAR( 255 ) NOT NULL DEFAULT  'md5' AFTER  `password`;
ALTER TABLE  `s_user` CHANGE  `password`  `password` VARCHAR( 255 ) NOT NULL;

-- //@UNDO

-- //


