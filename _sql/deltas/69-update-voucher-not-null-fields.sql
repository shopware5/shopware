ALTER TABLE `s_emarketing_vouchers` CHANGE `subshopID` `subshopID` INT( 1 ) NULL DEFAULT NULL;
UPDATE s_emarketing_vouchers SET `subshopID` = NULL WHERE subshopID = 0;

ALTER TABLE `s_emarketing_vouchers` CHANGE `bindtosupplier` `bindtosupplier` INT( 11 ) NULL DEFAULT NULL;
UPDATE s_emarketing_vouchers SET `bindtosupplier` = NULL WHERE bindtosupplier = 0;

ALTER TABLE `s_emarketing_vouchers` CHANGE `customergroup` `customergroup` INT( 11 ) NULL DEFAULT NULL;
UPDATE s_emarketing_vouchers SET `customergroup` = NULL WHERE customergroup = 0;

-- //@UNDO
ALTER TABLE `s_emarketing_vouchers` CHANGE `subshopID` `subshopID` INT( 1 ) NOT NULL;
UPDATE s_emarketing_vouchers SET `subshopID` = 0 WHERE subshopID IS NULL;

UPDATE s_emarketing_vouchers SET `bindtosupplier` = 0 WHERE bindtosupplier IS NULL;
ALTER TABLE `s_emarketing_vouchers` CHANGE `bindtosupplier` `bindtosupplier` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `s_emarketing_vouchers` CHANGE `customergroup` `customergroup` VARCHAR( 15 ) NOT NULL;