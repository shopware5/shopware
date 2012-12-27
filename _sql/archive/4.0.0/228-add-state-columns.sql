-- //
ALTER TABLE `s_user_billingaddress` ADD `stateID` INT NOT NULL AFTER `countryID`;
ALTER TABLE `s_user_shippingaddress` ADD `stateID` INT NOT NULL AFTER `countryID`;
ALTER TABLE `s_order_billingaddress` ADD `stateID` INT NOT NULL AFTER `countryID`;
ALTER TABLE `s_order_shippingaddress` ADD `stateID` INT NOT NULL AFTER `countryID`;
-- //@UNDO
ALTER TABLE `s_user_billingaddress` DROP `stateID`;
ALTER TABLE `s_user_shippingaddress` DROP `stateID`;
ALTER TABLE `s_order_billingaddress` DROP `stateID`;
ALTER TABLE `s_order_shippingaddress` DROP `stateID`;
-- //