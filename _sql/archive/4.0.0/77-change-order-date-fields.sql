ALTER TABLE s_order_details CHANGE releasedate releasedate DATE NULL DEFAULT NULL;

-- //@UNDO

ALTER TABLE `s_order_details` CHANGE `releasedate` `releasedate` DATE NOT NULL DEFAULT '0000-00-00';