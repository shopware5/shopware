ALTER TABLE `s_emarketing_vouchers` CHANGE `valid_from` `valid_from` DATE NULL DEFAULT NULL ,
CHANGE `valid_to` `valid_to` DATE NULL DEFAULT NULL;
-- //@UNDO
ALTER TABLE `s_emarketing_vouchers` CHANGE `valid_from` `valid_from` DATE NOT NULL DEFAULT '0000-00-00',
CHANGE `valid_to` `valid_to` DATE NOT NULL DEFAULT '0000-00-00';