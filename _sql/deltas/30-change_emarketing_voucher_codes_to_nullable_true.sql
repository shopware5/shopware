ALTER TABLE `s_emarketing_voucher_codes` CHANGE `userID` `userID` INT(11) NULL DEFAULT '0';
UPDATE s_emarketing_voucher_codes SET userID = null WHERE userID = 0;

-- //@UNDO
UPDATE s_emarketing_voucher_codes SET userID = 0 WHERE userID = null;
ALTER TABLE `s_emarketing_voucher_codes` CHANGE `userID` `userID` INT( 11 ) NOT NULL ;