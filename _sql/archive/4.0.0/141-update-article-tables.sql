
ALTER TABLE `s_articles_details`
ADD `width` DECIMAL( 10, 3 ) UNSIGNED NULL DEFAULT NULL,
ADD `height` DECIMAL( 10, 3 ) UNSIGNED NULL DEFAULT NULL,
ADD `length` DECIMAL( 10, 3 ) UNSIGNED NULL DEFAULT NULL,
ADD `ean` DECIMAL( 13, 0 ) UNSIGNED NULL DEFAULT NULL;


ALTER TABLE `s_articles_prices` CHANGE `pseudoprice` `pseudoprice` DECIMAL( 10, 2 ) NULL,
CHANGE `baseprice` `baseprice` DECIMAL( 10, 2 ) NULL,
CHANGE `percent` `percent` DECIMAL( 10, 2 ) NULL;

UPDATE s_articles_prices SET pseudoprice = NULL WHERE pseudoprice = 0;
UPDATE s_articles_prices SET baseprice = NULL WHERE baseprice = 0;
UPDATE s_articles_prices SET percent = NULL WHERE percent = 0;


ALTER TABLE `s_articles`
 CHANGE `supplierID` `supplierID` INT(11) UNSIGNED NULL DEFAULT NULL,
 CHANGE `description` `description` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
 CHANGE `description_long` `description_long` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
 CHANGE `shippingtime` `shippingtime` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
 CHANGE `releasedate` `releasedate` DATE NULL DEFAULT NULL,
 CHANGE `pseudosales` `pseudosales` INT(11) NOT NULL DEFAULT '0',
 CHANGE `keywords` `keywords` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
 CHANGE `minpurchase` `minpurchase` INT(11) UNSIGNED NULL DEFAULT NULL,
 CHANGE `purchasesteps` `purchasesteps` INT(11) UNSIGNED NULL,
 CHANGE `maxpurchase` `maxpurchase` INT(11) UNSIGNED NULL DEFAULT NULL,
 CHANGE `purchaseunit` `purchaseunit` DECIMAL(10,3) UNSIGNED NULL DEFAULT NULL,
 CHANGE `referenceunit` `referenceunit` DECIMAL(10,3) UNSIGNED NULL DEFAULT NULL,
 CHANGE `packunit` `packunit` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
 CHANGE `unitID` `unitID` INT(11) UNSIGNED NULL DEFAULT NULL, CHANGE `pricegroupID` `pricegroupID` INT(11) UNSIGNED NULL DEFAULT NULL,
 CHANGE `filtergroupID` `filtergroupID` INT(11) UNSIGNED NULL DEFAULT NULL;

ALTER TABLE `s_articles` CHANGE `main_detail_id` `main_detail_id` INT( 11 ) UNSIGNED NULL;
ALTER TABLE `s_articles_details` CHANGE `instock` `instock` INT( 11 ) NULL DEFAULT NULL;


-- //@UNDO


ALTER TABLE `s_articles_details` DROP `width` ,
DROP `height` ,
DROP `length` ,
DROP `ean` ;

ALTER TABLE `s_articles_prices` CHANGE `pseudoprice` `pseudoprice` DOUBLE NOT NULL DEFAULT '0',
CHANGE `baseprice` `baseprice` DOUBLE NOT NULL DEFAULT '0',
CHANGE `percent` `percent` DOUBLE NOT NULL DEFAULT '0';

UPDATE s_articles_prices SET pseudoprice = NULL WHERE pseudoprice = 0;
UPDATE s_articles_prices SET baseprice = NULL WHERE baseprice = 0;
UPDATE s_articles_prices SET percent = NULL WHERE percent = 0;

ALTER TABLE `s_articles` CHANGE `main_detail_id` `main_detail_id` INT( 11 ) UNSIGNED NOT NULL ;
ALTER TABLE `s_articles_details` CHANGE `instock` `instock` INT( 11 ) NOT NULL ;

ALTER TABLE `s_articles`
 CHANGE `supplierID` `supplierID` INT(11) UNSIGNED NOT NULL,
 CHANGE `description` `description` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 CHANGE `description_long` `description_long` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 CHANGE `shippingtime` `shippingtime` VARCHAR(11) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 CHANGE `releasedate` `releasedate` DATE NOT NULL,
 CHANGE `pseudosales` `pseudosales` INT(11) NOT NULL DEFAULT '0',
 CHANGE `keywords` `keywords` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 CHANGE `minpurchase` `minpurchase` INT(11) UNSIGNED NOT NULL,
 CHANGE `purchasesteps` `purchasesteps` INT(11) UNSIGNED NOT NULL,
 CHANGE `maxpurchase` `maxpurchase` INT(11) UNSIGNED NOT NULL,
 CHANGE `purchaseunit` `purchaseunit` DECIMAL(10,3) UNSIGNED NOT NULL,
 CHANGE `referenceunit` `referenceunit` DECIMAL(10,3) UNSIGNED NOT NULL,
 CHANGE `packunit` `packunit` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
 CHANGE `unitID` `unitID` INT(11) UNSIGNED NOT NULL,
 CHANGE `pricegroupID` `pricegroupID` INT(11) UNSIGNED NOT NULL,
 CHANGE `filtergroupID` `filtergroupID` INT(11) UNSIGNED NOT NULL;
