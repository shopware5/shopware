-- //

ALTER TABLE `s_user_billingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_billingaddress` SET `stateID` = Null WHERE `stateID` = 0;

ALTER TABLE `s_user_shippingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_shippingaddress` SET `stateID` = Null WHERE `stateID` = 0;


-- //@UNDO


-- //
