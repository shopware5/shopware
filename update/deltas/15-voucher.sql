UPDATE s_emarketing_vouchers SET `valid_from` = NULL WHERE valid_from = '0000-00-00';
UPDATE s_emarketing_vouchers SET `valid_to` = NULL WHERE valid_to = '0000-00-00';
UPDATE s_emarketing_vouchers SET `subshopID` = NULL WHERE subshopID = 0;
UPDATE s_emarketing_vouchers SET `bindtosupplier` = NULL WHERE bindtosupplier = 0;
UPDATE s_emarketing_vouchers SET `customergroup` = NULL WHERE customergroup = 0;
