-- depends on SW-3990 - Change decimal precision of purchaseunit to 4

-- //

ALTER TABLE `s_articles_details` CHANGE `purchaseunit` `purchaseunit` DECIMAL( 11, 4 ) UNSIGNED NULL DEFAULT NULL;

-- //@UNDO

-- //



