ALTER TABLE s_order_billingaddress CHANGE userID userID INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE s_order_shippingaddress CHANGE userID userID INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_order_shippingaddress` SET userID = NULL WHERE userID = 0;
UPDATE `s_order_billingaddress` SET userID = NULL WHERE userID = 0;


-- //@UNDO

ALTER TABLE s_order_billingaddress CHANGE userID userID INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE s_order_shippingaddress CHANGE userID userID INT( 11 ) NOT NULL DEFAULT '0';
UPDATE `s_order_billingaddress` SET userID = 0 WHERE userID IS NULL;
UPDATE `s_order_shippingaddress` SET userID = 0 WHERE userID IS NULL;