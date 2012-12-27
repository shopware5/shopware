ALTER TABLE `s_user_shippingaddress` CHANGE `countryID` `countryID` INT( 11 ) NULL DEFAULT NULL;
UPDATE s_user_shippingaddress SET countryID = NULL WHERE countryID = 0;
-- //@UNDO
ALTER TABLE `s_user_shippingaddress` CHANGE `countryID` `countryID` INT( 11 ) NOT NULL;
UPDATE s_user_shippingaddress SET countryID = 0 WHERE countryID IS NULL;
