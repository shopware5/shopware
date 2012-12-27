-- //
ALTER TABLE `s_order_billingaddress` ADD UNIQUE (
	`orderID`
);
ALTER TABLE `s_order_shippingaddress` ADD UNIQUE (
	`orderID`
);
ALTER TABLE `s_emarketing_lastarticles` ADD INDEX ( `sessionID` );
-- //@UNDO
ALTER TABLE s_order_billingaddress DROP INDEX orderID;

ALTER TABLE s_order_shippingaddress DROP INDEX orderID;

ALTER TABLE s_emarketing_lastarticles DROP INDEX sessionID;
-- //
