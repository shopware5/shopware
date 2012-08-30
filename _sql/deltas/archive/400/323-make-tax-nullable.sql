-- //

ALTER TABLE `s_order_details` CHANGE `taxID` `taxID` INT(11) NULL DEFAULT NULL;
UPDATE `s_order_details` SET `taxID`=NULL WHERE `taxID`=0;

-- //@UNDO

--
