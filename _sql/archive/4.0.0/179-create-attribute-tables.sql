ALTER TABLE  `s_emotion` ENGINE = INNODB;
ALTER TABLE  `s_emotion_element` ENGINE = INNODB;
ALTER TABLE  `s_emotion_element_value` ENGINE = INNODB;
ALTER TABLE  `s_emotion_grid` ENGINE = INNODB;
ALTER TABLE  `s_library_component` ENGINE = INNODB;
ALTER TABLE  `s_library_component_field` ENGINE = INNODB;

ALTER TABLE  `s_articles_attributes` CHANGE  `articleID`  `articleID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `s_articles_attributes` CHANGE  `articledetailsID`  `articledetailsID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `s_articles_attributes` ADD FOREIGN KEY (  `articleID` ) REFERENCES  `s_articles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION ;
ALTER TABLE  `s_articles_attributes` ADD FOREIGN KEY (  `articledetailsID` ) REFERENCES  `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION ;

ALTER TABLE `s_articles_attributes`
	CHANGE `attr1` `attr1` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0',
	CHANGE `attr2` `attr2` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0',
	CHANGE `attr3` `attr3` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0',
	CHANGE `attr4` `attr4` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr5` `attr5` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr6` `attr6` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr7` `attr7` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr8` `attr8` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0',
	CHANGE `attr9` `attr9` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr10` `attr10` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr11` `attr11` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr12` `attr12` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr13` `attr13` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0',
	CHANGE `attr14` `attr14` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr15` `attr15` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr16` `attr16` VARCHAR(30) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr17` `attr17` DATE NULL DEFAULT NULL,
	CHANGE `attr18` `attr18` MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr19` `attr19` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
	CHANGE `attr20` `attr20` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;

CREATE TABLE IF NOT EXISTS `s_articles_downloads_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `downloadID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloadID` (`downloadID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_esd_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `esdID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `esdID` (`esdID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_img_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imageID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imageID` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_information_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `informationID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `informationID` (`informationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_prices_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priceID` (`priceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_supplier_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplierID` (`supplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_categories_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) unsigned DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `testIT` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID` (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_cms_static_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsStaticID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsStaticID` (`cmsStaticID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_cms_support_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsSupportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsSupportID` (`cmsSupportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_core_auth_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authID` (`authID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `s_core_config_mails_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `s_core_countries_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countryID` (`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_core_countries_states_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stateID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stateID` (`stateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_core_customergroups_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerGroupID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customerGroupID` (`customerGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentmeanID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paymentmeanID` (`paymentmeanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_emarketing_banners_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bannerID` (`bannerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID` (`voucherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_emotion_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emotionID` (`emotionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_export_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exportID` (`exportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_filter_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filterID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filterID` (`filterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_media_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mediaID` (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `s_order_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `s_order_basket_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basketID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basketID` (`basketID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_order_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_order_details_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detailID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detailID` (`detailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_order_documents_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentID` (`documentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_order_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_premium_dispatch_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatchID` (`dispatchID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_user_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;



CREATE TABLE IF NOT EXISTS `s_user_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_user_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


ALTER TABLE `s_articles_downloads_attributes`
  ADD CONSTRAINT `s_articles_downloads_attributes_ibfk_1` FOREIGN KEY (`downloadID`) REFERENCES `s_articles_downloads` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_articles_esd_attributes`
  ADD CONSTRAINT `s_articles_esd_attributes_ibfk_1` FOREIGN KEY (`esdID`) REFERENCES `s_articles_esd` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_articles_img_attributes`
  ADD CONSTRAINT `s_articles_img_attributes_ibfk_1` FOREIGN KEY (`imageID`) REFERENCES `s_articles_img` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_articles_information_attributes`
  ADD CONSTRAINT `s_articles_information_attributes_ibfk_1` FOREIGN KEY (`informationID`) REFERENCES `s_articles_information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_articles_prices_attributes`
  ADD CONSTRAINT `s_articles_prices_attributes_ibfk_1` FOREIGN KEY (`priceID`) REFERENCES `s_articles_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_articles_supplier_attributes`
  ADD CONSTRAINT `s_articles_supplier_attributes_ibfk_1` FOREIGN KEY (`supplierID`) REFERENCES `s_articles_supplier` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_categories_attributes`
  ADD CONSTRAINT `s_categories_attributes_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `s_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_cms_static_attributes`
  ADD CONSTRAINT `s_cms_static_attributes_ibfk_1` FOREIGN KEY (`cmsStaticID`) REFERENCES `s_cms_static` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_cms_support_attributes`
  ADD CONSTRAINT `s_cms_support_attributes_ibfk_1` FOREIGN KEY (`cmsSupportID`) REFERENCES `s_cms_support` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_auth_attributes`
  ADD CONSTRAINT `s_core_auth_attributes_ibfk_1` FOREIGN KEY (`authID`) REFERENCES `s_core_auth` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_config_mails_attributes`
  ADD CONSTRAINT `s_core_config_mails_attributes_ibfk_1` FOREIGN KEY (`mailID`) REFERENCES `s_core_config_mails` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_countries_attributes`
  ADD CONSTRAINT `s_core_countries_attributes_ibfk_1` FOREIGN KEY (`countryID`) REFERENCES `s_core_countries` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_countries_states_attributes`
  ADD CONSTRAINT `s_core_countries_states_attributes_ibfk_1` FOREIGN KEY (`stateID`) REFERENCES `s_core_countries_states` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_customergroups_attributes`
  ADD CONSTRAINT `s_core_customergroups_attributes_ibfk_1` FOREIGN KEY (`customerGroupID`) REFERENCES `s_core_customergroups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_core_paymentmeans_attributes`
  ADD CONSTRAINT `s_core_paymentmeans_attributes_ibfk_1` FOREIGN KEY (`paymentmeanID`) REFERENCES `s_core_paymentmeans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_emarketing_banners_attributes`
  ADD CONSTRAINT `s_emarketing_banners_attributes_ibfk_1` FOREIGN KEY (`bannerID`) REFERENCES `s_emarketing_banners` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_emarketing_vouchers_attributes`
  ADD CONSTRAINT `s_emarketing_vouchers_attributes_ibfk_1` FOREIGN KEY (`voucherID`) REFERENCES `s_emarketing_vouchers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_emotion_attributes`
  ADD CONSTRAINT `s_emotion_attributes_ibfk_1` FOREIGN KEY (`emotionID`) REFERENCES `s_emotion` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_export_attributes`
  ADD CONSTRAINT `s_export_attributes_ibfk_1` FOREIGN KEY (`exportID`) REFERENCES `s_export` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_filter_attributes`
  ADD CONSTRAINT `s_filter_attributes_ibfk_1` FOREIGN KEY (`filterID`) REFERENCES `s_filter` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_media_attributes`
  ADD CONSTRAINT `s_media_attributes_ibfk_1` FOREIGN KEY (`mediaID`) REFERENCES `s_media` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_attributes`
  ADD CONSTRAINT `s_order_attributes_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `s_order` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_basket_attributes`
  ADD CONSTRAINT `s_order_basket_attributes_ibfk_2` FOREIGN KEY (`basketID`) REFERENCES `s_order_basket` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_billingaddress_attributes`
  ADD CONSTRAINT `s_order_billingaddress_attributes_ibfk_2` FOREIGN KEY (`billingID`) REFERENCES `s_order_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_details_attributes`
  ADD CONSTRAINT `s_order_details_attributes_ibfk_1` FOREIGN KEY (`detailID`) REFERENCES `s_order_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_documents_attributes`
  ADD CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_order_shippingaddress_attributes`
  ADD CONSTRAINT `s_order_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_order_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_premium_dispatch_attributes`
  ADD CONSTRAINT `s_premium_dispatch_attributes_ibfk_1` FOREIGN KEY (`dispatchID`) REFERENCES `s_premium_dispatch` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_user_attributes`
  ADD CONSTRAINT `s_user_attributes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_user_billingaddress_attributes`
  ADD CONSTRAINT `s_user_billingaddress_attributes_ibfk_1` FOREIGN KEY (`billingID`) REFERENCES `s_user_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

ALTER TABLE `s_user_shippingaddress_attributes`
  ADD CONSTRAINT `s_user_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_user_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;



INSERT INTO s_categories_attributes (categoryID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
	SELECT id, ac_attr1, ac_attr2, ac_attr3, ac_attr4, ac_attr5, ac_attr6
	FROM s_categories;

INSERT INTO s_order_basket_attributes (basketID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
	SELECT id, ob_attr1, ob_attr2, ob_attr3, ob_attr4, ob_attr5, ob_attr6
	FROM s_order_basket;

INSERT INTO s_user_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6)
	SELECT id, text1, text2, text3, text4, text5, text6
	FROM s_user_billingaddress;

UPDATE s_user_billingaddress_attributes SET text1 = NULL WHERE text1 = '';
UPDATE s_user_billingaddress_attributes SET text2 = NULL WHERE text2 = '';
UPDATE s_user_billingaddress_attributes SET text3 = NULL WHERE text3 = '';
UPDATE s_user_billingaddress_attributes SET text4 = NULL WHERE text4 = '';
UPDATE s_user_billingaddress_attributes SET text5 = NULL WHERE text5 = '';
UPDATE s_user_billingaddress_attributes SET text6 = NULL WHERE text6 = '';

INSERT INTO s_user_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6)
	SELECT id, text1, text2, text3, text4, text5, text6
	FROM s_user_shippingaddress;

UPDATE s_user_shippingaddress_attributes SET text1 = NULL WHERE text1 = '';
UPDATE s_user_shippingaddress_attributes SET text2 = NULL WHERE text2 = '';
UPDATE s_user_shippingaddress_attributes SET text3 = NULL WHERE text3 = '';
UPDATE s_user_shippingaddress_attributes SET text4 = NULL WHERE text4 = '';
UPDATE s_user_shippingaddress_attributes SET text5 = NULL WHERE text5 = '';
UPDATE s_user_shippingaddress_attributes SET text6 = NULL WHERE text6 = '';

INSERT INTO s_order_shippingaddress_attributes (shippingID, text1, text2, text3, text4, text5, text6)
	SELECT id, text1, text2, text3, text4, text5, text6
	FROM s_order_shippingaddress;

UPDATE s_order_shippingaddress_attributes SET text1 = NULL WHERE text1 = '';
UPDATE s_order_shippingaddress_attributes SET text2 = NULL WHERE text2 = '';
UPDATE s_order_shippingaddress_attributes SET text3 = NULL WHERE text3 = '';
UPDATE s_order_shippingaddress_attributes SET text4 = NULL WHERE text4 = '';
UPDATE s_order_shippingaddress_attributes SET text5 = NULL WHERE text5 = '';
UPDATE s_order_shippingaddress_attributes SET text6 = NULL WHERE text6 = '';

INSERT INTO s_order_billingaddress_attributes (billingID, text1, text2, text3, text4, text5, text6)
	SELECT id, text1, text2, text3, text4, text5, text6
	FROM s_order_billingaddress;

UPDATE s_order_billingaddress_attributes SET text1 = NULL WHERE text1 = '';
UPDATE s_order_billingaddress_attributes SET text2 = NULL WHERE text2 = '';
UPDATE s_order_billingaddress_attributes SET text3 = NULL WHERE text3 = '';
UPDATE s_order_billingaddress_attributes SET text4 = NULL WHERE text4 = '';
UPDATE s_order_billingaddress_attributes SET text5 = NULL WHERE text5 = '';
UPDATE s_order_billingaddress_attributes SET text6 = NULL WHERE text6 = '';

INSERT INTO s_order_attributes (orderID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
	SELECT id, o_attr1, o_attr2, o_attr3, o_attr4, o_attr5, o_attr6
	FROM s_order;

UPDATE s_order_attributes SET attribute1 = NULL WHERE attribute1 = '';
UPDATE s_order_attributes SET attribute2 = NULL WHERE attribute2 = '';
UPDATE s_order_attributes SET attribute3 = NULL WHERE attribute3 = '';
UPDATE s_order_attributes SET attribute4 = NULL WHERE attribute4 = '';
UPDATE s_order_attributes SET attribute5 = NULL WHERE attribute5 = '';
UPDATE s_order_attributes SET attribute6 = NULL WHERE attribute6 = '';

INSERT INTO s_order_details_attributes (detailID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
	SELECT id, od_attr1, od_attr2, od_attr3, od_attr4, od_attr5, od_attr6
	FROM s_order_details;

UPDATE s_order_details_attributes SET attribute1 = NULL WHERE attribute1 = '';
UPDATE s_order_details_attributes SET attribute2 = NULL WHERE attribute2 = '';
UPDATE s_order_details_attributes SET attribute3 = NULL WHERE attribute3 = '';
UPDATE s_order_details_attributes SET attribute4 = NULL WHERE attribute4 = '';
UPDATE s_order_details_attributes SET attribute5 = NULL WHERE attribute5 = '';
UPDATE s_order_details_attributes SET attribute6 = NULL WHERE attribute6 = '';


ALTER TABLE `s_categories`
  DROP `ac_attr1`,
  DROP `ac_attr2`,
  DROP `ac_attr3`,
  DROP `ac_attr4`,
  DROP `ac_attr5`,
  DROP `ac_attr6`;

ALTER TABLE `s_order_basket`
  DROP `ob_attr1`,
  DROP `ob_attr2`,
  DROP `ob_attr3`,
  DROP `ob_attr4`,
  DROP `ob_attr5`,
  DROP `ob_attr6`;

ALTER TABLE `s_user_billingaddress`
  DROP `text1`,
  DROP `text2`,
  DROP `text3`,
  DROP `text4`,
  DROP `text5`,
  DROP `text6`;

ALTER TABLE `s_user_shippingaddress`
  DROP `text1`,
  DROP `text2`,
  DROP `text3`,
  DROP `text4`,
  DROP `text5`,
  DROP `text6`;

ALTER TABLE `s_order_shippingaddress`
  DROP `text1`,
  DROP `text2`,
  DROP `text3`,
  DROP `text4`,
  DROP `text5`,
  DROP `text6`;

ALTER TABLE `s_order_billingaddress`
  DROP `text1`,
  DROP `text2`,
  DROP `text3`,
  DROP `text4`,
  DROP `text5`,
  DROP `text6`;

ALTER TABLE `s_order`
  DROP `o_attr1`,
  DROP `o_attr2`,
  DROP `o_attr3`,
  DROP `o_attr4`,
  DROP `o_attr5`,
  DROP `o_attr6`;

ALTER TABLE `s_order_details`
  DROP `od_attr1`,
  DROP `od_attr2`,
  DROP `od_attr3`,
  DROP `od_attr4`,
  DROP `od_attr5`,
  DROP `od_attr6`;

-- //@UNDO

ALTER TABLE  `s_articles_attributes` DROP FOREIGN KEY  `s_articles_attributes_ibfk_1` ;
ALTER TABLE  `s_articles_attributes` DROP FOREIGN KEY  `s_articles_attributes_ibfk_2` ;

ALTER TABLE  `s_categories` ADD  `ac_attr1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_categories` ADD  `ac_attr2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_categories` ADD  `ac_attr3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_categories` ADD  `ac_attr4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_categories` ADD  `ac_attr5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_categories` ADD  `ac_attr6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_order_basket` ADD  `ob_attr1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_basket` ADD  `ob_attr2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_basket` ADD  `ob_attr3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_basket` ADD  `ob_attr4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_basket` ADD  `ob_attr5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_basket` ADD  `ob_attr6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_user_billingaddress` ADD  `text1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_billingaddress` ADD  `text2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_billingaddress` ADD  `text3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_billingaddress` ADD  `text4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_billingaddress` ADD  `text5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_billingaddress` ADD  `text6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_user_shippingaddress` ADD  `text1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_shippingaddress` ADD  `text2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_shippingaddress` ADD  `text3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_shippingaddress` ADD  `text4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_shippingaddress` ADD  `text5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_user_shippingaddress` ADD  `text6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_order_shippingaddress` ADD  `text1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_shippingaddress` ADD  `text2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_shippingaddress` ADD  `text3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_shippingaddress` ADD  `text4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_shippingaddress` ADD  `text5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_shippingaddress` ADD  `text6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_order_billingaddress` ADD  `text1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_billingaddress` ADD  `text2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_billingaddress` ADD  `text3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_billingaddress` ADD  `text4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_billingaddress` ADD  `text5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_billingaddress` ADD  `text6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_order` ADD  `o_attr1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order` ADD  `o_attr2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order` ADD  `o_attr3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order` ADD  `o_attr4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order` ADD  `o_attr5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order` ADD  `o_attr6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE  `s_order_details` ADD  `od_attr1` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_details` ADD  `od_attr2` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_details` ADD  `od_attr3` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_details` ADD  `od_attr4` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_details` ADD  `od_attr5` VARCHAR( 255 ) NOT NULL;
ALTER TABLE  `s_order_details` ADD  `od_attr6` VARCHAR( 255 ) NOT NULL;


DROP TABLE `s_articles_downloads_attributes`,
	`s_articles_esd_attributes`,
	`s_articles_img_attributes`,
	`s_articles_information_attributes`,
	`s_articles_prices_attributes`,
	`s_articles_supplier_attributes`,
	`s_categories_attributes`,
	`s_cms_static_attributes`,
	`s_cms_support_attributes`,
	`s_core_auth_attributes`,
	`s_core_config_mails_attributes`,
	`s_core_countries_attributes`,
	`s_core_countries_states_attributes`,
	`s_core_customergroups_attributes`,
	`s_core_paymentmeans_attributes`,
	`s_emarketing_banners_attributes`,
	`s_emarketing_vouchers_attributes`,
	`s_emotion_attributes`,
	`s_export_attributes`,
	`s_filter_attributes`,
	`s_media_attributes`,
	`s_order_attributes`,
	`s_order_basket_attributes`,
	`s_order_billingaddress_attributes`,
	`s_order_details_attributes`,
	`s_order_documents_attributes`,
	`s_order_shippingaddress_attributes`,
	`s_premium_dispatch_attributes`,
	`s_user_attributes`,
	`s_user_billingaddress_attributes`,
	`s_user_shippingaddress_attributes`;
