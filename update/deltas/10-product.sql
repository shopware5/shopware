UPDATE s_articles SET supplierID = NULL WHERE supplierID = 0;
UPDATE s_articles SET taxID = NULL WHERE taxID = 0;
UPDATE s_articles SET pricegroupID = NULL WHERE pricegroupID = 0;
UPDATE s_articles SET filtergroupID = NULL WHERE filtergroupID = 0;
UPDATE s_articles SET keywords = NULL WHERE keywords = '';
UPDATE s_articles SET description = NULL WHERE description = '';
UPDATE s_articles SET datum = NOW() WHERE datum = '0000-00-00';
UPDATE s_articles SET shippingtime = NULL WHERE shippingtime = '' OR shippingtime = 0;
UPDATE s_articles a, s_articles_details d SET main_detail_id = d.id WHERE a.id = d.articleID AND d.kind = 1;

UPDATE s_articles_prices SET pseudoprice = NULL WHERE pseudoprice = 0;
UPDATE s_articles_prices SET baseprice = NULL WHERE baseprice = 0;
UPDATE s_articles_prices SET percent = NULL WHERE percent = 0;

DELETE s FROM s_articles_relationships s, s_articles_details d
WHERE s.relatedarticle = d.ordernumber
AND d.kind != 1;

UPDATE s_articles_relationships s, s_articles_details d
SET s.relatedarticle = d.articleID
WHERE s.relatedarticle = d.ordernumber;

DELETE s FROM s_articles_similar s, s_articles_details d
WHERE s.relatedarticle = d.ordernumber
AND d.kind != 1;

UPDATE s_articles_similar s, s_articles_details d
SET s.relatedarticle = d.articleID
WHERE s.relatedarticle = d.ordernumber;

UPDATE s_articles_details d, backup_s_articles a SET d.unitID = a.unitID,
    d.purchasesteps = IF(a.purchasesteps = 0, NULL, a.purchasesteps),
    d.maxpurchase = IF(a.maxpurchase = 0, NULL, a.maxpurchase),
    d.minpurchase = IF(a.minpurchase = 0, NULL, a.minpurchase),
    d.purchaseunit = IF(a.purchaseunit = 0, NULL, a.purchaseunit),
    d.referenceunit = IF(a.referenceunit = 0, NULL, a.referenceunit),
    d.packunit = IF(a.packunit = '', NULL, a.packunit),
    d.releasedate = IF(a.releasedate = '0000-00-00', NULL, a.releasedate),
    d.shippingfree = IF(a.shippingfree = '', NULL, a.shippingfree),
    d.shippingtime = IF(a.shippingtime = '' OR a.shippingtime = 0, NULL, a.shippingtime)
WHERE d.articleID = a.id;
UPDATE s_articles_details SET suppliernumber = NULL WHERE suppliernumber = '';
UPDATE s_articles_details SET additionaltext = NULL WHERE additionaltext = '';
UPDATE s_articles_details SET weight = NULL WHERE weight = 0;
UPDATE s_articles_details SET instock = NULL WHERE instock = 0;
UPDATE s_articles_details SET stockmin = NULL WHERE stockmin = 0;

DELETE FROM `s_core_engine_elements`
WHERE `domname` NOT LIKE 'attr%';

CREATE TABLE IF NOT EXISTS `s_core_customerpricegroups_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pricegroup` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `from` int(10) unsigned NOT NULL,
  `to` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articledetailsID` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `pseudoprice` double DEFAULT NULL,
  `baseprice` double DEFAULT NULL,
  `percent` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `articledetailsID` (`articledetailsID`),
  KEY `pricegroup_2` (`pricegroup`,`from`,`articledetailsID`),
  KEY `pricegroup` (`pricegroup`,`to`,`articledetailsID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
INSERT IGNORE INTO `s_core_customerpricegroups_prices` (
  `pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`
)
SELECT `pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`, `pseudoprice`, `baseprice`, `percent`
FROM `s_articles_prices`
WHERE `pricegroup` LIKE 'PG%';
DELETE FROM `s_articles_prices` WHERE `pricegroup` LIKE 'PG%';

UPDATE `s_articles_attributes` SET `attr17` = null WHERE `attr17` = '0000-00-00';
