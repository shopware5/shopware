
ALTER DATABASE `foo` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_articles_attributes` ENGINE = InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;


UPDATE s_articles_details SET shippingtime = NULL WHERE shippingtime = '' OR  shippingtime = 0;
UPDATE s_articles_details SET packunit = NULL WHERE packunit = '';
UPDATE s_articles_details SET purchasesteps = NULL WHERE purchasesteps = '';
UPDATE s_articles_details SET maxpurchase = NULL WHERE maxpurchase = '';
UPDATE s_articles_details SET minpurchase = NULL WHERE minpurchase = '';
UPDATE s_articles_details SET purchaseunit = NULL WHERE purchaseunit = '';
UPDATE s_articles_details SET referenceunit = NULL WHERE referenceunit = '';
