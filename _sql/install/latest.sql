SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `s_addon_premiums`;
CREATE TABLE `s_addon_premiums` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `startprice` double NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ordernumber_export` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subshopID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles`;
CREATE TABLE `s_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) unsigned DEFAULT NULL,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `description_long` mediumtext COLLATE utf8_unicode_ci,
  `shippingtime` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datum` date DEFAULT NULL,
  `active` int(1) unsigned NOT NULL DEFAULT '0',
  `taxID` int(11) unsigned DEFAULT NULL,
  `pseudosales` int(11) NOT NULL DEFAULT '0',
  `topseller` int(1) unsigned NOT NULL DEFAULT '0',
  `metaTitle` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changetime` datetime NOT NULL,
  `pricegroupID` int(11) unsigned DEFAULT NULL,
  `pricegroupActive` int(1) unsigned NOT NULL,
  `filtergroupID` int(11) unsigned DEFAULT NULL,
  `laststock` int(1) NOT NULL,
  `crossbundlelook` int(1) unsigned NOT NULL,
  `notification` int(1) unsigned NOT NULL COMMENT 'send notification',
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mode` int(11) NOT NULL,
  `main_detail_id` int(11) unsigned DEFAULT NULL,
  `available_from` datetime DEFAULT NULL,
  `available_to` datetime DEFAULT NULL,
  `configurator_set_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `main_detailID` (`main_detail_id`),
  KEY `datum` (`datum`),
  KEY `name` (`name`),
  KEY `supplierID` (`supplierID`),
  KEY `shippingtime` (`shippingtime`),
  KEY `changetime` (`changetime`),
  KEY `configurator_set_id` (`configurator_set_id`),
  KEY `articles_by_category_sort_release` (`datum`,`id`),
  KEY `articles_by_category_sort_name` (`name`,`id`),
  KEY `product_newcomer` (`active`,`datum`),
  KEY `get_category_filters` (`active`,`filtergroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_also_bought_ro`;
CREATE TABLE `s_articles_also_bought_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `related_article_id` int(11) NOT NULL,
  `sales` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `bought_combination` (`article_id`,`related_article_id`),
  KEY `related_article_id` (`related_article_id`),
  KEY `article_id` (`article_id`),
  KEY `get_also_bought_articles` (`article_id`,`sales`,`related_article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_attributes`;
CREATE TABLE `s_articles_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned DEFAULT NULL,
  `articledetailsID` int(11) unsigned DEFAULT NULL,
  `attr1` text COLLATE utf8_unicode_ci,
  `attr2` text COLLATE utf8_unicode_ci,
  `attr3` text COLLATE utf8_unicode_ci,
  `attr4` text COLLATE utf8_unicode_ci,
  `attr5` text COLLATE utf8_unicode_ci,
  `attr6` text COLLATE utf8_unicode_ci,
  `attr7` text COLLATE utf8_unicode_ci,
  `attr8` text COLLATE utf8_unicode_ci,
  `attr9` text COLLATE utf8_unicode_ci,
  `attr10` text COLLATE utf8_unicode_ci,
  `attr11` text COLLATE utf8_unicode_ci,
  `attr12` text COLLATE utf8_unicode_ci,
  `attr13` text COLLATE utf8_unicode_ci,
  `attr14` text COLLATE utf8_unicode_ci,
  `attr15` text COLLATE utf8_unicode_ci,
  `attr16` text COLLATE utf8_unicode_ci,
  `attr17` text COLLATE utf8_unicode_ci,
  `attr18` text COLLATE utf8_unicode_ci,
  `attr19` text COLLATE utf8_unicode_ci,
  `attr20` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articledetailsID` (`articledetailsID`),
  KEY `articleID` (`articleID`),
  CONSTRAINT `s_articles_attributes_ibfk_1` FOREIGN KEY (`articleID`) REFERENCES `s_articles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `s_articles_attributes_ibfk_2` FOREIGN KEY (`articledetailsID`) REFERENCES `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_avoid_customergroups`;
CREATE TABLE `s_articles_avoid_customergroups` (
  `articleID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  PRIMARY KEY `articleID` (`articleID`,`customergroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_categories`;
CREATE TABLE `s_articles_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`categoryID`),
  KEY `categoryID` (`categoryID`),
  KEY `articleID_2` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_categories_ro`;
CREATE TABLE `s_articles_categories_ro` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  `parentCategoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`categoryID`,`parentCategoryID`),
  KEY `categoryID` (`categoryID`),
  KEY `articleID_2` (`articleID`),
  KEY `categoryID_2` (`categoryID`,`parentCategoryID`),
  KEY `category_id_by_article_id` (`articleID`,`id`),
  KEY `elastic_search` (`categoryID`,`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_categories_seo`;
CREATE TABLE `s_articles_categories_seo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_article` (`shop_id`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_details`;
CREATE TABLE `s_articles_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `suppliernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kind` int(1) NOT NULL DEFAULT '0',
  `additionaltext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sales` int(11) NOT NULL DEFAULT '0',
  `active` int(11) unsigned NOT NULL DEFAULT '0',
  `instock` int(11) NOT NULL DEFAULT '0',
  `stockmin` int(11) unsigned DEFAULT NULL,
  `laststock` int(1) NOT NULL DEFAULT '0',
  `weight` decimal(10,3) unsigned DEFAULT NULL,
  `position` int(11) unsigned NOT NULL,
  `width` decimal(10,3) unsigned DEFAULT NULL,
  `height` decimal(10,3) unsigned DEFAULT NULL,
  `length` decimal(10,3) unsigned DEFAULT NULL,
  `ean` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unitID` int(11) unsigned DEFAULT NULL,
  `purchasesteps` int(11) unsigned DEFAULT NULL,
  `maxpurchase` int(11) unsigned DEFAULT NULL,
  `minpurchase` int(11) unsigned NOT NULL DEFAULT '1',
  `purchaseunit` decimal(11,4) unsigned DEFAULT NULL,
  `referenceunit` decimal(10,3) unsigned DEFAULT NULL,
  `packunit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `releasedate` date DEFAULT NULL,
  `shippingfree` int(1) unsigned NOT NULL DEFAULT '0',
  `shippingtime` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `purchaseprice` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordernumber` (`ordernumber`),
  KEY `articleID` (`articleID`),
  KEY `releasedate` (`releasedate`),
  KEY `articles_by_category_sort_popularity` (`sales`,`articleID`),
  KEY `get_similar_articles` (`kind`,`sales`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_downloads`;
CREATE TABLE `s_articles_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_downloads_attributes`;
CREATE TABLE `s_articles_downloads_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `downloadID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloadID` (`downloadID`),
  CONSTRAINT `s_articles_downloads_attributes_ibfk_1` FOREIGN KEY (`downloadID`) REFERENCES `s_articles_downloads` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_esd`;
CREATE TABLE `s_articles_esd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articledetailsID` int(11) NOT NULL DEFAULT '0',
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `serials` int(1) NOT NULL DEFAULT '0',
  `notification` int(1) NOT NULL DEFAULT '0',
  `maxdownloads` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `articledetailsID` (`articledetailsID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_esd_attributes`;
CREATE TABLE `s_articles_esd_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `esdID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `esdID` (`esdID`),
  CONSTRAINT `s_articles_esd_attributes_ibfk_1` FOREIGN KEY (`esdID`) REFERENCES `s_articles_esd` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_esd_serials`;
CREATE TABLE `s_articles_esd_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `esdID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `esdID` (`esdID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_img`;
CREATE TABLE `s_articles_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) DEFAULT NULL,
  `img` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `main` int(1) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `relations` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `article_detail_id` int(10) unsigned DEFAULT NULL,
  `media_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `artikel_id` (`articleID`),
  KEY `article_detail_id` (`article_detail_id`),
  KEY `parent_id` (`parent_id`),
  KEY `media_id` (`media_id`),
  KEY `article_images_query` (`articleID`,`position`),
  KEY `article_cover_image_query` (`articleID`,`main`,`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_img_attributes`;
CREATE TABLE `s_articles_img_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imageID` int(11) DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imageID` (`imageID`),
  CONSTRAINT `s_articles_img_attributes_ibfk_1` FOREIGN KEY (`imageID`) REFERENCES `s_articles_img` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_information`;
CREATE TABLE `s_articles_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hauptid` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_information_attributes`;
CREATE TABLE `s_articles_information_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `informationID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `informationID` (`informationID`),
  CONSTRAINT `s_articles_information_attributes_ibfk_1` FOREIGN KEY (`informationID`) REFERENCES `s_articles_information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_notification`;
CREATE TABLE `s_articles_notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send` int(1) unsigned NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopLink` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_prices`;
CREATE TABLE `s_articles_prices` (
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
  KEY `pricegroup` (`pricegroup`,`to`,`articledetailsID`),
  KEY `product_prices` (`articledetailsID`,`from`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_prices_attributes`;
CREATE TABLE `s_articles_prices_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priceID` (`priceID`),
  CONSTRAINT `s_articles_prices_attributes_ibfk_1` FOREIGN KEY (`priceID`) REFERENCES `s_articles_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_relationships`;
CREATE TABLE `s_articles_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_similar`;
CREATE TABLE `s_articles_similar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_similar_shown_ro`;
CREATE TABLE `s_articles_similar_shown_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `related_article_id` int(11) NOT NULL,
  `viewed` int(11) unsigned NOT NULL DEFAULT '0',
  `init_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `viewed_combination` (`article_id`,`related_article_id`),
  KEY `viewed` (`viewed`,`related_article_id`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_supplier`;
CREATE TABLE `s_articles_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changed` datetime NOT NULL DEFAULT '2019-12-06 10:19:52',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_supplier_attributes`;
CREATE TABLE `s_articles_supplier_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplierID` (`supplierID`),
  CONSTRAINT `s_articles_supplier_attributes_ibfk_1` FOREIGN KEY (`supplierID`) REFERENCES `s_articles_supplier` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_top_seller_ro`;
CREATE TABLE `s_articles_top_seller_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `sales` int(11) unsigned NOT NULL DEFAULT '0',
  `last_cleared` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`article_id`),
  KEY `sales` (`sales`),
  KEY `listing_query` (`sales`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_translations`;
CREATE TABLE `s_articles_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL,
  `languageID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description_long` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description_clear` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `shippingtime` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `attr1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr5` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`languageID`)
) ENGINE=InnoDB AUTO_INCREMENT=13928 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_articles_vote`;
CREATE TABLE `s_articles_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `points` double NOT NULL,
  `datum` datetime NOT NULL,
  `active` int(11) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `answer` text COLLATE utf8_unicode_ci NOT NULL,
  `answer_date` datetime DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `get_articles_votes` (`articleID`,`active`,`datum`),
  KEY `vote_average` (`points`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_dependencies`;
CREATE TABLE `s_article_configurator_dependencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `configurator_set_id` int(10) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `child_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_groups`;
CREATE TABLE `s_article_configurator_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_groups_attributes`;
CREATE TABLE `s_article_configurator_groups_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  CONSTRAINT `s_article_configurator_groups_attributes_ibfk_1` FOREIGN KEY (`groupID`) REFERENCES `s_article_configurator_groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_options`;
CREATE TABLE `s_article_configurator_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `media_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_options_attributes`;
CREATE TABLE `s_article_configurator_options_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `optionID` (`optionID`),
  CONSTRAINT `s_article_configurator_options_attributes_ibfk_1` FOREIGN KEY (`optionID`) REFERENCES `s_article_configurator_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_option_relations`;
CREATE TABLE `s_article_configurator_option_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`article_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_price_variations`;
CREATE TABLE `s_article_configurator_price_variations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `configurator_set_id` int(10) unsigned NOT NULL,
  `variation` decimal(10,3) NOT NULL,
  `options` text COLLATE utf8_unicode_ci,
  `is_gross` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_sets`;
CREATE TABLE `s_article_configurator_sets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_set_group_relations`;
CREATE TABLE `s_article_configurator_set_group_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_set_option_relations`;
CREATE TABLE `s_article_configurator_set_option_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_templates`;
CREATE TABLE `s_article_configurator_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_number` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `suppliernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additionaltext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `active` int(11) unsigned NOT NULL DEFAULT '0',
  `instock` int(11) DEFAULT NULL,
  `stockmin` int(11) unsigned DEFAULT NULL,
  `laststock` tinyint(4) NOT NULL DEFAULT '0',
  `weight` decimal(10,3) unsigned DEFAULT NULL,
  `position` int(11) unsigned NOT NULL,
  `width` decimal(10,3) unsigned DEFAULT NULL,
  `height` decimal(10,3) unsigned DEFAULT NULL,
  `length` decimal(10,3) unsigned DEFAULT NULL,
  `ean` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit_id` int(11) unsigned DEFAULT NULL,
  `purchasesteps` int(11) unsigned DEFAULT NULL,
  `maxpurchase` int(11) unsigned DEFAULT NULL,
  `minpurchase` int(11) unsigned DEFAULT NULL,
  `purchaseunit` decimal(11,4) unsigned DEFAULT NULL,
  `referenceunit` decimal(10,3) unsigned DEFAULT NULL,
  `packunit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `releasedate` date DEFAULT NULL,
  `shippingfree` int(1) unsigned NOT NULL DEFAULT '0',
  `shippingtime` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  `purchaseprice` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `articleID` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_templates_attributes`;
CREATE TABLE `s_article_configurator_templates_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) unsigned DEFAULT NULL,
  `attr1` text COLLATE utf8_unicode_ci,
  `attr2` text COLLATE utf8_unicode_ci,
  `attr3` text COLLATE utf8_unicode_ci,
  `attr4` text COLLATE utf8_unicode_ci,
  `attr5` text COLLATE utf8_unicode_ci,
  `attr6` text COLLATE utf8_unicode_ci,
  `attr7` text COLLATE utf8_unicode_ci,
  `attr8` text COLLATE utf8_unicode_ci,
  `attr9` text COLLATE utf8_unicode_ci,
  `attr10` text COLLATE utf8_unicode_ci,
  `attr11` text COLLATE utf8_unicode_ci,
  `attr12` text COLLATE utf8_unicode_ci,
  `attr13` text COLLATE utf8_unicode_ci,
  `attr14` text COLLATE utf8_unicode_ci,
  `attr15` text COLLATE utf8_unicode_ci,
  `attr16` text COLLATE utf8_unicode_ci,
  `attr17` text COLLATE utf8_unicode_ci,
  `attr18` text COLLATE utf8_unicode_ci,
  `attr19` text COLLATE utf8_unicode_ci,
  `attr20` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `templateID` (`template_id`),
  CONSTRAINT `s_article_configurator_templates_attributes_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `s_article_configurator_templates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_template_prices`;
CREATE TABLE `s_article_configurator_template_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned DEFAULT NULL,
  `customer_group_key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `from` int(10) unsigned NOT NULL,
  `to` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `pseudoprice` double DEFAULT NULL,
  `percent` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pricegroup_2` (`customer_group_key`,`from`),
  KEY `pricegroup` (`customer_group_key`,`to`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_configurator_template_prices_attributes`;
CREATE TABLE `s_article_configurator_template_prices_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_price_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priceID` (`template_price_id`),
  CONSTRAINT `s_article_configurator_template_prices_attributes_ibfk_1` FOREIGN KEY (`template_price_id`) REFERENCES `s_article_configurator_template_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_img_mappings`;
CREATE TABLE `s_article_img_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_article_img_mapping_rules`;
CREATE TABLE `s_article_img_mapping_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapping_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `mapping_id` (`mapping_id`),
  KEY `option_id` (`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_attribute_configuration`;
CREATE TABLE `s_attribute_configuration` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_value` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `translatable` int(1) NOT NULL,
  `display_in_backend` int(1) NOT NULL,
  `custom` int(1) NOT NULL,
  `help_text` text COLLATE utf8_unicode_ci,
  `support_text` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `entity` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `array_store` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `table_name` (`table_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_attribute_configuration` (`id`, `table_name`, `column_name`, `column_type`, `default_value`, `position`, `translatable`, `display_in_backend`, `custom`, `help_text`, `support_text`, `label`, `entity`, `array_store`) VALUES
(1,	's_articles_attributes',	'attr3',	'text',	NULL,	3,	1,	1,	0,	'Optionaler Kommentar',	'',	'Kommentar',	'NULL',	NULL),
(2,	's_articles_attributes',	'attr1',	'text',	NULL,	1,	1,	1,	0,	'Freitext zur Anzeige auf der Detailseite',	'',	'Freitext-1',	'NULL',	NULL),
(3,	's_articles_attributes',	'attr2',	'text',	NULL,	2,	1,	1,	0,	'Freitext zur Anzeige auf der Detailseite',	'',	'Freitext-2',	'NULL',	NULL);

DROP TABLE IF EXISTS `s_benchmark_config`;
CREATE TABLE `s_benchmark_config` (
  `id` binary(16) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `last_sent` datetime NOT NULL,
  `last_received` datetime NOT NULL,
  `last_order_id` int(11) NOT NULL,
  `last_customer_id` int(11) NOT NULL,
  `last_product_id` int(11) NOT NULL,
  `last_analytics_id` int(11) NOT NULL,
  `last_updated_orders_date` datetime DEFAULT NULL,
  `batch_size` int(11) NOT NULL,
  `industry` int(11) DEFAULT NULL,
  `type` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `response_token` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cached_template` longtext COLLATE utf8_unicode_ci,
  `locked` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`),
  UNIQUE KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_billing_template`;
CREATE TABLE `s_billing_template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `typ` mediumint(11) NOT NULL,
  `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `show` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_billing_template` (`ID`, `name`, `value`, `typ`, `group`, `desc`, `position`, `show`) VALUES
(1,	'top',	'1cm',	2,	'margin',	'Seitenabstand oben',	0,	1),
(2,	'right',	'0.81cm',	2,	'margin',	'Seitenrand rechts',	0,	1),
(3,	'bottom',	'0cm',	2,	'margin',	'Seitenabstand unten',	0,	1),
(4,	'left',	'2.41cm',	2,	'margin',	'Seitenabstand links',	0,	1),
(5,	'top2',	'5cm',	2,	'header',	'Logohöhe',	6,	1),
(7,	'margin',	'1cm',	2,	'headline',	'Überschrift Abstand zur Anschrift',	0,	1),
(8,	'left',	'0cm',	2,	'sender',	'Abstand links (negativ Wert möglich)',	0,	1),
(9,	'footer',	'<table style=\"height: 90px;\" border=\"0\" width=\"100%\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Demo GmbH</span></p>\r\n<p><span style=\"font-size: xx-small;\">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style=\"font-size: xx-small;\">Musterstadt</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Bankverbindung</span></p>\r\n<p><span style=\"font-size: xx-small;\">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style=\"font-size: xx-small;\">aaaa<br /></span></td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">AGB<br /></span></p>\r\n<p><span style=\"font-size: xx-small;\">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style=\"font-size: xx-small;\">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>',	1,	'footer',	'Fusszeile',	2,	1),
(13,	'right',	'<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 56780<br />info@demo.de<br />www.demo.de</p>',	1,	'header',	'Briefkopf rechts',	9,	1),
(14,	'sender',	'Demo GmbH - Straße 3 - 00000 Musterstadt',	2,	'sender',	'Absender',	0,	1),
(15,	'left',	'100px',	2,	'footer',	'Abstand links',	0,	1),
(16,	'bottom',	'100px',	2,	'footer',	'Abstand unten',	1,	1),
(17,	'number',	'10',	2,	'content_middle',	'Anzahl angezeigter Postionen',	2,	1),
(18,	'text',	'',	1,	'content_middle',	'Freitext',	4,	1),
(19,	'height',	'12cm',	2,	'content_middle',	'Inhaltsabstand zum obigen Seitenrand',	0,	1),
(20,	'top',	'<p><img src=\"http://www.shopwaredemo.de/eMail_logo.jpg\" alt=\"\" width=\"393\" height=\"78\" /></p>',	1,	'header',	'Logo oben',	7,	1),
(21,	'top',	'1cm',	2,	'sender',	'Abstand unten zum Logo (negativ Wert möglich)',	0,	1),
(22,	'margin',	'2.2cm',	2,	'header',	'Abstand rechts (negativ Wert möglich)',	8,	1);

DROP TABLE IF EXISTS `s_blog`;
CREATE TABLE `s_blog` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `active` int(1) NOT NULL,
  `short_description` text COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `views` int(11) unsigned DEFAULT NULL,
  `display_date` datetime NOT NULL,
  `category_id` int(11) unsigned DEFAULT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emotion_get_blog_entry` (`display_date`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_blog_assigned_articles`;
CREATE TABLE `s_blog_assigned_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_blog_attributes`;
CREATE TABLE `s_blog_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  `attribute4` text COLLATE utf8_unicode_ci,
  `attribute5` text COLLATE utf8_unicode_ci,
  `attribute6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`),
  CONSTRAINT `s_blog_attributes_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `s_blog` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_blog_comments`;
CREATE TABLE `s_blog_comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `active` int(1) NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `points` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_blog_media`;
CREATE TABLE `s_blog_media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `media_id` int(11) unsigned NOT NULL,
  `preview` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blogID` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_blog_tags`;
CREATE TABLE `s_blog_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blogID` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_articles`;
CREATE TABLE `s_campaigns_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_banner`;
CREATE TABLE `s_campaigns_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linkTarget` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_containers`;
CREATE TABLE `s_campaigns_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_groups`;
CREATE TABLE `s_campaigns_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_campaigns_groups` (`id`, `name`) VALUES
(1,	'Newsletter-Empfänger');

DROP TABLE IF EXISTS `s_campaigns_html`;
CREATE TABLE `s_campaigns_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alignment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_links`;
CREATE TABLE `s_campaigns_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_logs`;
CREATE TABLE `s_campaigns_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mailingID` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_mailaddresses`;
CREATE TABLE `s_campaigns_mailaddresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer` int(1) NOT NULL,
  `groupID` int(11) NOT NULL,
  `email` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `lastmailing` int(11) NOT NULL,
  `lastread` int(11) NOT NULL,
  `added` datetime DEFAULT NULL,
  `double_optin_confirmed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `email` (`email`),
  KEY `lastmailing` (`lastmailing`),
  KEY `lastread` (`lastread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_maildata`;
CREATE TABLE `s_campaigns_maildata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `salutation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `double_optin_confirmed` datetime DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_mailings`;
CREATE TABLE `s_campaigns_mailings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date DEFAULT NULL,
  `groups` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `sendermail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sendername` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `plaintext` int(1) NOT NULL,
  `templateID` int(11) NOT NULL DEFAULT '0',
  `languageID` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `locked` datetime DEFAULT NULL,
  `recipients` int(11) NOT NULL,
  `read` int(11) NOT NULL DEFAULT '0',
  `clicked` int(11) NOT NULL DEFAULT '0',
  `customergroup` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `publish` int(1) unsigned NOT NULL,
  `timed_delivery` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_positions`;
CREATE TABLE `s_campaigns_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `containerID` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_campaigns_sender`;
CREATE TABLE `s_campaigns_sender` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_campaigns_sender` (`id`, `email`, `name`) VALUES
(1,	'info@example.com',	'Newsletter Absender');

DROP TABLE IF EXISTS `s_campaigns_templates`;
CREATE TABLE `s_campaigns_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_campaigns_templates` (`id`, `path`, `description`) VALUES
(1,	'index.tpl',	'Standardtemplate'),
(2,	'indexh.tpl',	'Händler');

DROP TABLE IF EXISTS `s_categories`;
CREATE TABLE `s_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned DEFAULT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) unsigned DEFAULT '0',
  `left` int(11) unsigned NOT NULL,
  `right` int(11) unsigned NOT NULL,
  `level` int(11) unsigned NOT NULL,
  `added` datetime NOT NULL,
  `changed` datetime NOT NULL,
  `metakeywords` mediumtext COLLATE utf8_unicode_ci,
  `metadescription` mediumtext COLLATE utf8_unicode_ci,
  `cmsheadline` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cmstext` mediumtext COLLATE utf8_unicode_ci,
  `template` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` int(1) NOT NULL,
  `blog` int(11) NOT NULL,
  `external` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hidefilter` int(1) NOT NULL,
  `hidetop` int(1) NOT NULL,
  `mediaID` int(11) unsigned DEFAULT NULL,
  `product_box_layout` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `stream_id` int(11) unsigned DEFAULT NULL,
  `hide_sortings` int(1) NOT NULL DEFAULT '0',
  `sorting_ids` text COLLATE utf8_unicode_ci,
  `facet_ids` text COLLATE utf8_unicode_ci,
  `external_target` varchar(32) COLLATE utf8_unicode_ci DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `description` (`description`),
  KEY `position` (`position`),
  KEY `left` (`left`,`right`),
  KEY `level` (`level`),
  KEY `active_query_builder` (`parent`,`position`,`id`),
  KEY `stream_id` (`stream_id`),
  CONSTRAINT `s_categories_fk_stream_id` FOREIGN KEY (`stream_id`) REFERENCES `s_product_streams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`) VALUES
(1,	NULL,	NULL,	'Root',	0,	1,	6,	0,	'2012-08-27 22:28:52',	'2012-08-27 22:28:52',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	0,	NULL,	0,	0,	0,	NULL,	NULL,	NULL,	0,	NULL,	NULL,	''),
(3,	1,	NULL,	'Deutsch',	0,	2,	3,	1,	'2012-08-27 22:28:52',	'2012-08-27 22:28:52',	NULL,	'',	'',	'',	NULL,	1,	0,	'',	0,	0,	NULL,	NULL,	NULL,	NULL,	0,	NULL,	NULL,	'');

DROP TABLE IF EXISTS `s_categories_attributes`;
CREATE TABLE `s_categories_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) unsigned DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  `attribute4` text COLLATE utf8_unicode_ci,
  `attribute5` text COLLATE utf8_unicode_ci,
  `attribute6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID` (`categoryID`),
  CONSTRAINT `s_categories_attributes_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `s_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_categories_avoid_customergroups`;
CREATE TABLE `s_categories_avoid_customergroups` (
  `categoryID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  PRIMARY KEY `articleID` (`categoryID`,`customergroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_cms_static`;
CREATE TABLE `s_cms_static` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `tpl1variable` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tpl1path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tpl2variable` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tpl2path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tpl3variable` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tpl3path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `grouping` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `page_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `meta_description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `changed` datetime NOT NULL DEFAULT '2019-12-06 10:19:52',
  `shop_ids` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `get_menu` (`position`,`description`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_cms_static` (`id`, `active`, `tpl1variable`, `tpl1path`, `tpl2variable`, `tpl2path`, `tpl3variable`, `tpl3path`, `description`, `html`, `grouping`, `position`, `link`, `target`, `parentID`, `page_title`, `meta_keywords`, `meta_description`, `changed`, `shop_ids`) VALUES
(1,	1,	'',	'',	'',	'',	'',	'',	'Kontakt',	'<p>F&uuml;gen Sie hier Ihre Kontaktdaten ein</p>',	'left|bottom',	1,	'shopware.php?sViewport=ticket&sFid=5',	'_self',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(2,	1,	'',	'',	'',	'',	'',	'',	'Hilfe / Support',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left',	1,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(3,	1,	'',	'',	'',	'',	'',	'',	'Impressum',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom2',	20,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(4,	1,	'',	'',	'',	'',	'',	'',	'AGB',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom',	18,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(6,	1,	'',	'',	'',	'',	'',	'',	'Versand und Zahlungsbedingungen',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom',	3,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(7,	1,	'',	'',	'',	'',	'',	'',	'Datenschutz',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom2',	6,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(8,	1,	'',	'',	'',	'',	'',	'',	'Widerrufsrecht',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom',	5,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(9,	1,	'',	'',	'',	'',	'',	'',	'Über uns',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom2',	0,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(21,	1,	'',	'',	'',	'',	'',	'',	'Händler-Login',	'',	'left',	0,	'shopware.php?sViewport=registerFC&sUseSSL=1&sValidation=H',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(26,	1,	'',	'',	'',	'',	'',	'',	'Newsletter',	'',	'bottom2',	0,	'shopware.php?sViewport=newsletter',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(37,	1,	'',	'',	'',	'',	'',	'',	'Partnerprogramm',	'<h1>Jetzt Partner werden</h1>\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'bottom',	0,	'shopware.php?sViewport=ticket&sFid=8',	'_self',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(39,	1,	'',	'',	'',	'',	'',	'',	'Defektes Produkt',	'<p>Defektes Produkt.</p>',	'bottom',	0,	'shopware.php?sViewport=ticket&sFid=9',	'_self',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(41,	1,	'',	'',	'',	'',	'',	'',	'Rückgabe',	'<p>R&uuml;ckgabe.</p>',	'bottom',	4,	'shopware.php?sViewport=ticket&sFid=10',	'_self',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(43,	1,	'',	'',	'',	'',	'',	'',	'rechtliche Vorabinformationen',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'left|bottom',	0,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL),
(45,	1,	'',	'',	'',	'',	'',	'',	'Widerrufsformular',	'<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>',	'bottom',	8,	'',	'',	0,	'',	'',	'',	'2019-12-06 10:19:52',	NULL);

DROP TABLE IF EXISTS `s_cms_static_attributes`;
CREATE TABLE `s_cms_static_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsStaticID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsStaticID` (`cmsStaticID`),
  CONSTRAINT `s_cms_static_attributes_ibfk_1` FOREIGN KEY (`cmsStaticID`) REFERENCES `s_cms_static` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_cms_static_groups`;
CREATE TABLE `s_cms_static_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL,
  `mapping_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mapping_id` (`mapping_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_cms_static_groups` (`id`, `name`, `key`, `active`, `mapping_id`) VALUES
(1,	'Links',	'left',	1,	NULL),
(2,	'Unten (Spalte 1)',	'bottom',	1,	NULL),
(3,	'Unten (Spalte 2)',	'bottom2',	1,	NULL),
(4,	'In Bearbeitung',	'disabled',	0,	NULL);

DROP TABLE IF EXISTS `s_cms_support`;
CREATE TABLE `s_cms_support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_template` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text2` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8_unicode_ci,
  `ticket_typeID` int(10) NOT NULL,
  `isocode` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'de',
  `shop_ids` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_cms_support` (`id`, `active`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `meta_title`, `meta_keywords`, `meta_description`, `ticket_typeID`, `isocode`, `shop_ids`) VALUES
(5,	1,	'Kontaktformular',	'<p>Schreiben Sie uns eine E-Mail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>',	'info@example.com',	'Kontaktformular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\nE-Mail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nBetreff: {sVars.betreff}\r\nKommentar: \r\n{sVars.kommentar}\r\n\r\n\r\n',	'Kontaktformular Shopware',	'<p>Ihr Formular wurde versendet!</p>',	NULL,	NULL,	NULL,	1,	'de',	NULL),
(8,	1,	'Partnerformular',	'<h2>Partner werden und mitverdienen!</h2>\r\n<p>Einfach unseren Link auf ihre Seite legen und Sie erhalten f&uuml;r jeden Umsatz ihrer vermittelten Kunden automatisch eine attraktive Provision auf den Netto-Auftragswert.</p>\r\n<p>Bitte f&uuml;llen Sie <span style=\"text-decoration: underline;\">unverbindlich</span> das Partnerformular aus. Wir werden uns umgehend mit Ihnen in Verbindung setzen!</p>',	'info@example.com',	'Partneranfrage - {$sShopname}\n{sVars.firma} moechte Partner Ihres Shops werden!\n\nFirma: {sVars.firma}\nAnsprechpartner: {sVars.ansprechpartner}\nStraße/Hausnr.: {sVars.strasse}\nPLZ / Ort: {sVars.plz} {sVars.ort}\neMail: {sVars.email}\nTelefon: {sVars.tel}\nFax: {sVars.fax}\nWebseite: {sVars.website}\n\nKommentar:\n{sVars.kommentar}\n\nProfil:\n{sVars.profil}',	'Partner Anfrage',	'<p>Die Anfrage wurde versandt!</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(9,	1,	'Defektes Produkt',	'<p>Sie erhalten von uns nach dem Absenden dieses Formulars innerhalb kurzer Zeit eine R&uuml;ckantwort mit einer RMA-Nummer und weiterer Vorgehensweise.</p>\r\n<p>Bitte f&uuml;llen Sie die Fehlerbeschreibung ausf&uuml;hrlich aus, Sie m&uuml;ssen diese dann nicht mehr dem Paket beilegen.</p>',	'info@example.com',	'Defektes Produkt - Shopware Demoshop\r\n\r\nFirma: {sVars.firma}\r\nKundennummer: {sVars.kdnr}\r\neMail: {sVars.email}\r\n\r\nRechnungsnummer: {sVars.rechnung}\r\nArtikelnummer: {sVars.artikel}\r\n\r\nDetaillierte Fehlerbeschreibung:\r\n--------------------------------\r\n{sVars.fehler}\r\n\r\nRechner: {sVars.rechner}\r\nSystem {sVars.system}\r\nWie tritt das Problem auf: {sVars.wie}\r\n',	'Online-Serviceformular',	'<p>Formular erfolgreich versandt!</p>',	NULL,	NULL,	NULL,	2,	'de',	NULL),
(10,	1,	'Rückgabe',	'<h2>Hier k&ouml;nnen Sie Informationen zur R&uuml;ckgabe einstellen...</h2>',	'info@example.com',	'Rückgabe - Shopware Demoshop\n \nKundennummer: {sVars.kdnr}\neMail: {sVars.email}\n \nRechnungsnummer: {sVars.rechnung}\nArtikelnummer: {sVars.artikel}\n \nKommentar:\n \n{sVars.info}',	'Rückgabe',	'<p>Formular erfolgreich versandt.</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(16,	1,	'Anfrage-Formular',	'<p>Schreiben Sie uns eine eMail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>',	'info@example.com',	'{sShopname} Anfrage-Formular\n\nAnrede: {sVars.anrede}\nVorname: {sVars.vorname}\nNachname: {sVars.nachname}\neMail: {sVars.email}\nTelefon: {sVars.telefon}\nArtikel: {sVars.sordernumber}\n\nFrage:\n{sVars.inquiry}',	'{sShopname} Anfrage-Formular',	'<p>Ihre Anfrage wurde versendet!</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(17,	1,	'Partner form',	'<h2><strong>Become partner and earn money!</strong></h2>\r\n<p>Link our Site and receive&nbsp;an attractive commission on the net contract price&nbsp;for every tornover of your&nbsp;provided customers.</p>\r\n<p>Please fill out the partner form <span style=\"text-decoration: underline;\">without obligation</span>.&nbsp;We will immediately get in contact with you!</p>',	'info@example.com',	'Partner inquiry - {$sShopname}\n{sVars.firma} want to become your partner!\n\nCompany: {sVars.firma}\nContact person: {sVars.ansprechpartner}\nStreet / No.: {sVars.strasse}\nPostal Code / City: {sVars.plz} {sVars.ort}\neMail: {sVars.email}\nPhone: {sVars.tel}\nFax: {sVars.fax}\nWebsite: {sVars.website}\n\nComment:\n{sVars.kommentar}\n\nProfile:\n{sVars.profil}',	'Partner inquiry',	'<p>&nbsp;</p>\r\n&nbsp;\r\n<div id=\"result_box\" dir=\"ltr\">The request has been sent!</div>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(18,	1,	'Contact',	'',	'info@example.com',	'Contact form Shopware Demoshop\r\n\r\nTitle: {sVars.anrede}\r\nFirst name: {sVars.vorname}\r\nLast name: {sVars.nachname}\r\neMail: {sVars.email}\r\nPhone: {sVars.telefon}\r\nSubject: {sVars.betreff}\r\nComment: \r\n{sVars.kommentar}\r\n\r\n\r\n',	'Contact form Shopware',	'<p>Your form was sent!</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(19,	1,	'Defective product',	'<p>&nbsp;</p>\r\n&nbsp;\r\n<h1>Defective product - for customers and traders</h1>\r\n<p>You will receive an answer&nbsp;from us&nbsp;with an RMA number an other approach&nbsp;after sending this form.&nbsp;</p>\r\n<p>Please fill out the error description, so you must not add this any more to the package.</p>',	'info@example.com',	'Defective product - Shopware Demoshop\n\nCompany: {sVars.firma}\nCustomer no.: {sVars.kdnr}\neMail: {sVars.email}\n\nInvoice no.: {sVars.rechnung}\nArticle no.: {sVars.artikel}\n\nDescription of failure:\n--------------------------------\n{sVars.fehler}\n\nType: {sVars.rechner}\nSystem {sVars.system}\nHow does the problem occur:\n{sVars.wie}',	'Online-Serviceform',	'<p>Form successfully sent!</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(20,	1,	'Return',	'<h2>Here you can write information about the return...</h2>',	'info@example.com',	'Return - Shopware Demoshop\n\nCustomer no.: {sVars.kdnr}\neMail: {sVars.email}\n\nInvoice no.: {sVars.rechnung}\nArticle no.: {sVars.artikel}\n\nComment:\n{sVars.info}',	'Return',	'<p>Form successfully sent.</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(21,	1,	'Inquiry form',	'<p>Send us an email.&nbsp;<br /><br />We look forward to hearing from you.</p>',	'info@example.com',	'{sShopname} Anfrage-Formular\n\nAnrede: {sVars.anrede}\nVorname: {sVars.vorname}\nNachname: {sVars.nachname}\neMail: {sVars.email}\nTelefon: {sVars.telefon}\nArtikel: {sVars.sordernumber}\n\nFrage:\n{sVars.inquiry}',	'{sShopname} Anfrage-Formular',	'<p>Your request has been sent!</p>',	NULL,	NULL,	NULL,	0,	'de',	NULL),
(22,	1,	'Support beantragen',	'<p>Wir freuen uns &uuml;ber Ihre Kontaktaufnahme.</p>',	'info@example.com',	'',	'Support beantragen',	'<p>Vielen Dank f&uuml;r Ihre Anfrage!</p>',	NULL,	NULL,	NULL,	1,	'de',	NULL);

DROP TABLE IF EXISTS `s_cms_support_attributes`;
CREATE TABLE `s_cms_support_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsSupportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsSupportID` (`cmsSupportID`),
  CONSTRAINT `s_cms_support_attributes_ibfk_1` FOREIGN KEY (`cmsSupportID`) REFERENCES `s_cms_support` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_cms_support_fields`;
CREATE TABLE `s_cms_support_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_msg` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `note` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `typ` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` int(1) NOT NULL,
  `supportID` int(11) NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `added` datetime NOT NULL,
  `position` int(11) NOT NULL,
  `ticket_task` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`supportID`)
) ENGINE=InnoDB AUTO_INCREMENT=119 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `added`, `position`, `ticket_task`) VALUES
(24,	'',	'anrede',	'',	'select',	1,	5,	'Anrede',	'normal',	'Frau;Herr',	'2007-11-02 03:28:48',	1,	''),
(35,	'',	'vorname',	'',	'text',	1,	5,	'Vorname',	'normal',	'',	'2007-11-06 03:17:48',	2,	''),
(36,	'',	'nachname',	'',	'text',	1,	5,	'Nachname',	'normal',	'',	'2007-11-06 03:17:57',	3,	'name'),
(37,	'',	'email',	'',	'email',	1,	5,	'E-Mail-Adresse',	'normal',	'',	'2007-11-06 03:18:36',	4,	'email'),
(38,	'',	'telefon',	'',	'text',	0,	5,	'Telefon',	'normal',	'',	'2007-11-06 03:18:49',	5,	''),
(39,	'',	'betreff',	'',	'text',	1,	5,	'Betreff',	'normal',	'',	'2007-11-06 03:18:57',	6,	'subject'),
(40,	'',	'kommentar',	'',	'textarea',	1,	5,	'Kommentar',	'normal',	'',	'2007-11-06 03:19:08',	7,	'message'),
(41,	'',	'firma',	'',	'text',	1,	8,	'Firma',	'normal',	'',	'2007-11-22 08:11:39',	1,	''),
(42,	'',	'ansprechpartner',	'',	'text',	1,	8,	'Ansprechpartner',	'normal',	'',	'2007-11-22 08:12:18',	2,	''),
(43,	'',	'strasse',	'',	'text',	1,	8,	'Straße & Hausnummer',	'normal',	'',	'2007-11-22 08:12:49',	3,	''),
(44,	'',	'plz;ort',	'',	'text2',	1,	8,	'PLZ ; Ort',	'plz;ort',	'',	'2007-11-22 08:12:59',	4,	''),
(45,	'',	'tel',	'',	'text',	1,	8,	'Telefon',	'normal',	'',	'2007-11-22 08:13:45',	5,	''),
(46,	'',	'fax',	'',	'text',	0,	8,	'Fax',	'normal',	'',	'2007-11-22 08:13:52',	6,	''),
(47,	'',	'email',	'',	'text',	1,	8,	'E-Mail',	'normal',	'',	'2007-11-22 08:13:58',	7,	''),
(48,	'',	'website',	'',	'text',	1,	8,	'Webseite',	'normal',	'',	'2007-11-22 08:14:07',	8,	''),
(49,	'',	'kommentar',	'',	'textarea',	0,	8,	'Kommentar',	'normal',	'',	'2007-11-22 08:14:21',	9,	''),
(50,	'',	'profil',	'',	'textarea',	1,	8,	'Firmenprofil',	'normal',	'',	'2007-11-22 08:14:34',	10,	''),
(51,	'',	'rechnung',	'',	'text',	1,	9,	'Rechnungsnummer',	'normal',	'',	'2007-11-06 17:21:49',	1,	''),
(52,	'',	'email',	'',	'text',	1,	9,	'E-Mail-Adresse',	'normal',	'',	'2007-11-06 17:19:20',	2,	'email'),
(53,	'',	'kdnr',	'',	'text',	1,	9,	'KdNr.(siehe Rechnung)',	'normal',	'',	'2007-11-06 17:19:10',	3,	'name'),
(54,	'',	'firma',	'',	'checkbox',	0,	9,	'Firma (Wenn ja, bitte ankreuzen)',	'',	'1',	'2007-11-06 17:18:36',	4,	''),
(55,	'',	'artikel',	'',	'textarea',	1,	9,	'Artikelnummer(n)',	'normal',	'',	'2007-11-06 17:22:13',	5,	'subject'),
(56,	'',	'fehler',	'',	'textarea',	1,	9,	'Detaillierte Fehlerbeschreibung',	'normal',	'',	'2007-11-06 17:22:33',	6,	'message'),
(57,	'',	'rechner',	'',	'textarea',	0,	9,	'Auf welchem Rechnertypen läuft das defekte Produkt?',	'normal',	'',	'2007-11-06 17:23:17',	7,	''),
(58,	'',	'system',	'',	'textarea',	0,	9,	'Mit welchem Betriebssystem arbeiten Sie?',	'normal',	'',	'2007-11-06 17:23:57',	8,	''),
(59,	'',	'wie',	'',	'select',	1,	9,	'Wie tritt das Problem auf?',	'normal',	'sporadisch; ständig',	'2007-11-06 17:24:26',	9,	''),
(60,	'',	'kdnr',	'',	'text',	1,	10,	'KdNr.(siehe Rechnung)',	'normal',	'',	'2007-11-06 17:31:38',	1,	''),
(61,	'',	'email',	'',	'text',	1,	10,	'E-Mail-Adresse',	'normal',	'',	'2007-11-06 17:31:51',	2,	''),
(62,	'',	'rechnung',	'',	'text',	1,	10,	'Rechnungsnummer',	'normal',	'',	'2007-11-06 17:32:02',	3,	''),
(63,	'',	'artikel',	'',	'textarea',	1,	10,	'Artikelnummer(n)',	'normal',	'',	'2007-11-06 17:32:17',	4,	''),
(64,	'',	'info',	'',	'textarea',	0,	10,	'Kommentar',	'normal',	'',	'2007-11-06 17:32:42',	5,	''),
(69,	'',	'inquiry',	'',	'textarea',	1,	16,	'Anfrage',	'normal',	'',	'2007-11-06 03:19:08',	1,	''),
(71,	'',	'nachname',	'',	'text',	1,	16,	'Nachname',	'normal',	'',	'2007-11-06 03:17:57',	2,	''),
(72,	'',	'anrede',	'',	'select',	1,	16,	'Anrede',	'normal',	'Frau;Herr',	'2007-11-02 03:28:48',	3,	''),
(73,	'',	'telefon',	'',	'text',	0,	16,	'Telefon',	'normal',	'',	'2007-11-06 03:18:49',	4,	''),
(74,	'',	'email',	'',	'text',	1,	16,	'E-Mail-Adresse',	'normal',	'',	'2007-11-06 03:18:36',	5,	''),
(75,	'',	'vorname',	'',	'text',	1,	16,	'Vorname',	'normal',	'',	'2007-11-06 03:17:48',	6,	''),
(76,	'',	'firma',	'',	'text',	1,	17,	'Company',	'normal',	'',	'2008-10-17 13:02:42',	1,	''),
(77,	'',	'ansprechpartner',	'',	'text',	1,	17,	'Contact person',	'normal',	'',	'2008-10-17 13:03:35',	2,	''),
(78,	'',	'strasse',	'',	'text',	1,	17,	'Street & house number',	'normal',	'',	'2008-10-17 13:05:55',	3,	''),
(79,	'',	'plz;ort',	'',	'text2',	1,	17,	'Postal Code ; City',	'plz;ort',	'',	'2008-10-17 13:06:23',	4,	''),
(80,	'',	'tel',	'',	'text',	1,	17,	'Phone',	'normal',	'',	'2008-10-17 13:06:35',	5,	''),
(81,	'',	'fax',	'',	'text',	0,	17,	'Fax',	'normal',	'',	'2008-10-17 13:06:48',	6,	''),
(82,	'',	'email',	'',	'text',	1,	17,	'eMail',	'normal',	'',	'2008-10-17 13:07:06',	7,	''),
(83,	'',	'website',	'',	'text',	1,	17,	'Website',	'normal',	'',	'2008-10-17 13:07:14',	8,	''),
(84,	'',	'kommentar',	'',	'textarea',	0,	17,	'Comment',	'normal',	'',	'2008-10-17 13:07:25',	9,	''),
(85,	'',	'profil',	'',	'textarea',	1,	17,	'Company profile',	'normal',	'',	'2008-10-17 13:07:43',	10,	''),
(86,	'',	'anrede',	'',	'select',	1,	18,	'Title',	'normal',	'Ms;Mr',	'2008-10-17 13:21:07',	1,	''),
(87,	'',	'vorname',	'',	'text',	1,	18,	'First name',	'normal',	'',	'2008-10-17 13:21:41',	2,	''),
(88,	'',	'nachname',	'',	'text',	1,	18,	'Last name',	'normal',	'',	'2008-10-17 13:22:01',	3,	''),
(89,	'',	'email',	'',	'text',	1,	18,	'eMail-Adress',	'normal',	'',	'2008-10-17 13:22:18',	4,	''),
(90,	'',	'telefon',	'',	'text',	0,	18,	'Phone',	'normal',	'',	'2008-10-17 13:22:28',	5,	''),
(91,	'',	'betreff',	'',	'text',	1,	18,	'Subject',	'normal',	'',	'2008-10-17 13:22:38',	6,	''),
(92,	'',	'kommentar',	'',	'textarea',	1,	18,	'Comment',	'normal',	'',	'2008-10-17 13:22:45',	7,	''),
(93,	'',	'firma',	'',	'checkbox',	0,	19,	'Company (If so, please mark)',	'',	'1',	'2008-10-17 13:45:44',	1,	''),
(94,	'',	'kdnr',	'',	'text',	1,	19,	'Customer no. (See invoice)',	'normal',	'',	'2008-10-17 13:46:04',	2,	''),
(95,	'',	'email',	'',	'text',	1,	19,	'Email address',	'normal',	'',	'2008-10-17 13:46:27',	3,	''),
(96,	'',	'rechnung',	'',	'text',	1,	19,	'Invoice number',	'normal',	'',	'2008-10-17 13:47:03',	4,	''),
(97,	'',	'artikel',	'',	'textarea',	1,	19,	'Article number(s)',	'normal',	'',	'2008-10-17 13:47:43',	5,	''),
(98,	'',	'fehler',	'',	'textarea',	1,	19,	'Detailed error description',	'normal',	'',	'2008-10-17 13:48:54',	6,	''),
(99,	'',	'rechner',	'',	'textarea',	0,	19,	'On which computer type does the defective product run?',	'normal',	'',	'2008-10-17 14:02:03',	7,	''),
(100,	'',	'system',	'',	'textarea',	0,	19,	'With which operating system do you work?',	'normal',	'',	'2008-10-17 14:02:36',	8,	''),
(101,	'',	'wie',	'',	'select',	1,	19,	'How does the problem occur?',	'normal',	'sporadically;permanently',	'2008-10-17 14:02:55',	9,	''),
(102,	'',	'kdnr',	'',	'text',	1,	20,	'Customer no. (See invoice)',	'normal',	'',	'2008-10-17 14:21:28',	1,	''),
(103,	'',	'email',	'',	'text',	1,	20,	'eMail-Adress',	'normal',	'',	'2008-10-17 14:22:12',	2,	''),
(104,	'',	'rechnung',	'',	'text',	1,	20,	'Invoice number',	'normal',	'',	'2008-10-17 14:22:43',	3,	''),
(105,	'',	'artikel',	'',	'textarea',	1,	20,	'Articlenumber(s)',	'normal',	'',	'2008-10-17 14:23:15',	4,	''),
(106,	'',	'info',	'',	'textarea',	0,	20,	'Comment',	'normal',	'',	'2008-10-17 14:23:37',	5,	''),
(107,	'',	'anrede',	'',	'select',	1,	21,	'Title',	'normal',	'Ms;Mr',	'2008-10-17 14:45:21',	1,	''),
(108,	'',	'vorname',	'',	'text',	1,	21,	'First name',	'normal',	'',	'2008-10-17 14:46:11',	2,	''),
(109,	'',	'nachname',	'',	'text',	1,	21,	'Last name',	'normal',	'',	'2008-10-17 14:46:31',	3,	''),
(110,	'',	'email',	'',	'text',	1,	21,	'eMail-Adress',	'normal',	'',	'2008-10-17 14:46:49',	4,	''),
(111,	'',	'telefon',	'',	'text',	0,	21,	'Phone',	'normal',	'',	'2008-10-17 14:47:00',	5,	''),
(112,	'',	'inquiry',	'',	'textarea',	1,	21,	'Inquiry',	'normal',	'',	'2008-10-17 14:47:25',	6,	''),
(113,	'',	'name',	'',	'text',	1,	22,	'Name',	'normal',	'',	'2009-04-15 22:20:30',	1,	'name'),
(114,	'',	'email',	'',	'email',	1,	22,	'eMail',	'normal',	'',	'2009-04-15 22:20:37',	2,	'email'),
(115,	'',	'betreff',	'',	'text',	1,	22,	'Betreff',	'normal',	'',	'2009-04-15 22:20:45',	3,	'subject'),
(116,	'',	'kommentar',	'',	'textarea',	1,	22,	'Kommentar',	'normal',	'',	'2009-04-15 22:21:07',	4,	'message'),
(117,	'',	'sordernumber',	'',	'hidden',	0,	16,	'Artikelnummer',	'normal',	'',	'2019-12-06 09:19:55',	7,	''),
(118,	'',	'sordernumber',	'',	'hidden',	0,	21,	'Order number',	'normal',	'',	'2019-12-06 09:19:55',	7,	'');

DROP TABLE IF EXISTS `s_core_acl_privileges`;
CREATE TABLE `s_core_acl_privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resourceID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `resourceID` (`resourceID`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_acl_privileges` (`id`, `resourceID`, `name`) VALUES
(1,	1,	'create'),
(2,	1,	'read'),
(3,	1,	'update'),
(4,	1,	'delete'),
(5,	2,	'create'),
(6,	2,	'read'),
(7,	2,	'update'),
(8,	2,	'delete'),
(10,	3,	'create'),
(11,	3,	'update'),
(12,	3,	'delete'),
(15,	4,	'create'),
(16,	4,	'read'),
(17,	4,	'update'),
(18,	4,	'delete'),
(19,	5,	'create'),
(20,	5,	'update'),
(21,	5,	'delete'),
(22,	5,	'read'),
(23,	6,	'createupdate'),
(24,	6,	'read'),
(26,	6,	'delete'),
(27,	5,	'detail'),
(28,	5,	'perform_order'),
(29,	7,	'create'),
(30,	7,	'read'),
(31,	7,	'update'),
(32,	7,	'delete'),
(33,	8,	'create'),
(34,	8,	'read'),
(35,	8,	'update'),
(36,	8,	'delete'),
(37,	8,	'export'),
(38,	8,	'generate'),
(39,	9,	'read'),
(40,	9,	'accept'),
(41,	9,	'comment'),
(42,	9,	'delete'),
(43,	10,	'create'),
(44,	10,	'read'),
(45,	10,	'update'),
(46,	10,	'delete'),
(47,	11,	'create'),
(48,	11,	'read'),
(49,	11,	'update'),
(50,	11,	'delete'),
(56,	13,	'read'),
(57,	14,	'create'),
(58,	14,	'read'),
(59,	14,	'update'),
(60,	14,	'delete'),
(61,	15,	'create'),
(62,	15,	'read'),
(63,	15,	'update'),
(64,	15,	'delete'),
(65,	16,	'create'),
(66,	16,	'read'),
(67,	16,	'update'),
(68,	16,	'delete'),
(69,	17,	'create'),
(70,	17,	'read'),
(71,	17,	'update'),
(72,	17,	'delete'),
(73,	18,	'createGroup'),
(74,	18,	'read'),
(75,	18,	'createSite'),
(76,	18,	'updateSite'),
(77,	18,	'deleteSite'),
(78,	18,	'deleteGroup'),
(79,	11,	'generate'),
(80,	19,	'read'),
(81,	20,	'read'),
(82,	20,	'delete'),
(83,	21,	'save'),
(84,	21,	'read'),
(85,	21,	'delete'),
(86,	22,	'create'),
(87,	22,	'read'),
(88,	22,	'update'),
(89,	22,	'delete'),
(90,	22,	'statistic'),
(91,	23,	'create'),
(92,	23,	'read'),
(93,	23,	'update'),
(94,	23,	'delete'),
(95,	24,	'read'),
(96,	25,	'delete'),
(97,	25,	'read'),
(98,	26,	'read'),
(99,	27,	'read'),
(100,	27,	'delete'),
(101,	27,	'create'),
(102,	27,	'upload'),
(103,	27,	'update'),
(104,	28,	'read'),
(105,	28,	'delete'),
(106,	28,	'update'),
(107,	28,	'create'),
(108,	28,	'comments'),
(110,	29,	'read'),
(112,	29,	'delete'),
(113,	29,	'save'),
(114,	30,	'create'),
(115,	30,	'read'),
(116,	30,	'update'),
(117,	30,	'delete'),
(118,	31,	'create'),
(119,	31,	'read'),
(120,	31,	'update'),
(121,	31,	'delete'),
(122,	32,	'delete'),
(123,	32,	'read'),
(124,	32,	'write'),
(125,	33,	'read'),
(126,	33,	'update'),
(127,	33,	'clear'),
(131,	35,	'create'),
(132,	35,	'read'),
(133,	35,	'update'),
(134,	35,	'delete'),
(136,	36,	'read'),
(137,	36,	'upload'),
(138,	36,	'download'),
(139,	36,	'install'),
(140,	36,	'update'),
(141,	37,	'read'),
(142,	37,	'swag-visitors-customers-widget'),
(143,	37,	'swag-last-orders-widget'),
(144,	37,	'swag-sales-widget'),
(145,	37,	'swag-merchant-widget'),
(146,	37,	'swag-upload-widget'),
(147,	37,	'swag-notice-widget'),
(148,	38,	'read'),
(149,	38,	'createFilters'),
(150,	38,	'editFilters'),
(151,	38,	'deleteFilters'),
(152,	38,	'editSingleArticle'),
(153,	38,	'doMultiEdit'),
(154,	38,	'doBackup'),
(155,	39,	'read'),
(156,	39,	'preview'),
(157,	39,	'changeTheme'),
(158,	39,	'createTheme'),
(159,	39,	'uploadTheme'),
(160,	39,	'configureTheme'),
(161,	39,	'configureSystem'),
(162,	40,	'read'),
(163,	40,	'update'),
(164,	40,	'skipUpdate'),
(166,	41,	'update'),
(167,	41,	'read'),
(168,	20,	'system'),
(169,	42,	'read'),
(170,	42,	'save'),
(171,	42,	'delete'),
(172,	42,	'search_index'),
(174,	42,	'charts'),
(175,	16,	'sql_rule'),
(176,	11,	'sqli'),
(177,	43,	'read'),
(178,	43,	'submit'),
(179,	43,	'manage');

DROP TABLE IF EXISTS `s_core_acl_resources`;
CREATE TABLE `s_core_acl_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_acl_resources` (`id`, `name`, `pluginID`) VALUES
(1,	'debug_test',	NULL),
(2,	'banner',	NULL),
(4,	'supplier',	NULL),
(5,	'customer',	NULL),
(6,	'form',	NULL),
(7,	'premium',	NULL),
(8,	'voucher',	NULL),
(9,	'vote',	NULL),
(10,	'mail',	NULL),
(11,	'productfeed',	NULL),
(13,	'overview',	NULL),
(14,	'order',	NULL),
(15,	'payment',	NULL),
(16,	'shipping',	NULL),
(17,	'snippet',	NULL),
(18,	'site',	NULL),
(19,	'systeminfo',	NULL),
(20,	'log',	NULL),
(21,	'riskmanagement',	NULL),
(22,	'partner',	NULL),
(23,	'category',	NULL),
(24,	'notification',	NULL),
(25,	'canceledorder',	NULL),
(26,	'analytics',	NULL),
(27,	'mediamanager',	NULL),
(28,	'blog',	NULL),
(29,	'article',	NULL),
(30,	'config',	NULL),
(31,	'emotion',	NULL),
(32,	'newslettermanager',	NULL),
(33,	'performance',	NULL),
(35,	'usermanager',	NULL),
(36,	'pluginmanager',	NULL),
(37,	'widgets',	NULL),
(38,	'articlelist',	NULL),
(39,	'theme',	NULL),
(40,	'swagupdate',	NULL),
(41,	'attributes',	NULL),
(42,	'customerstream',	NULL),
(43,	'benchmark',	NULL);

DROP TABLE IF EXISTS `s_core_acl_roles`;
CREATE TABLE `s_core_acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleID` int(11) NOT NULL,
  `resourceID` int(11) DEFAULT NULL,
  `privilegeID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleID` (`roleID`,`resourceID`,`privilegeID`),
  KEY `resourceID` (`resourceID`),
  KEY `privilegeID` (`privilegeID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_acl_roles` (`id`, `roleID`, `resourceID`, `privilegeID`) VALUES
(1,	1,	NULL,	NULL);

DROP TABLE IF EXISTS `s_core_auth`;
CREATE TABLE `s_core_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleID` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `encoder` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'LegacyBackendMd5',
  `apiKey` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `localeID` int(11) NOT NULL,
  `sessionID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `failedlogins` int(11) NOT NULL,
  `lockeduntil` datetime DEFAULT NULL,
  `extended_editor` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `disabled_cache` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_auth_attributes`;
CREATE TABLE `s_core_auth_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authID` (`authID`),
  CONSTRAINT `s_core_auth_attributes_ibfk_1` FOREIGN KEY (`authID`) REFERENCES `s_core_auth` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_auth_config`;
CREATE TABLE `s_core_auth_config` (
  `user_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `config` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`user_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_auth_roles`;
CREATE TABLE `s_core_auth_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enabled` int(1) NOT NULL,
  `admin` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_auth_roles` (`id`, `parentID`, `name`, `description`, `source`, `enabled`, `admin`) VALUES
(1,	NULL,	'local_admins',	'Default group that gains access to all shop functions',	'build-in',	1,	1);

DROP TABLE IF EXISTS `s_core_config_elements`;
CREATE TABLE `s_core_config_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `options` blob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_id_2` (`form_id`,`name`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1059 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `options`) VALUES
(186,	86,	'vouchertax',	's:2:\"19\";',	'MwSt. Gutscheine',	NULL,	'text',	0,	0,	0,	NULL),
(188,	86,	'discounttax',	's:2:\"19\";',	'MwSt. Rabatte',	NULL,	'text',	0,	0,	0,	NULL),
(189,	90,	'voteunlock',	'b:1;',	'Artikel-Bewertungen müssen freigeschaltet werden',	NULL,	'boolean',	0,	0,	0,	NULL),
(190,	84,	'backendautoordernumber',	'b:1;',	'Automatischer Vorschlag der Artikelnummer',	NULL,	'boolean',	0,	0,	0,	NULL),
(191,	84,	'backendautoordernumberprefix',	's:2:\"SW\";',	'Präfix für automatisch generierte Artikelnummer',	NULL,	'text',	0,	0,	0,	NULL),
(192,	90,	'votedisable',	'b:0;',	'Artikel-Bewertungen deaktivieren',	NULL,	'boolean',	0,	0,	1,	NULL),
(193,	90,	'votesendcalling',	'b:1;',	'Automatische Erinnerung zur Artikelbewertung senden',	'Nach Kauf dem Benutzer an die Artikelbewertung via E-Mail erinnern',	'boolean',	0,	0,	0,	NULL),
(194,	90,	'votecallingtime',	's:1:\"1\";',	'Tage bis die Erinnerungs-E-Mail verschickt wird',	'Tage bis der Kunde via E-Mail an die Artikel-Bewertung erinnert wird',	'text',	0,	0,	0,	NULL),
(195,	86,	'taxautomode',	'b:1;',	'Steuer für Rabatte dynamisch feststellen',	NULL,	'boolean',	0,	0,	1,	NULL),
(224,	102,	'lastarticles_show',	'b:1;',	'Artikelverlauf anzeigen',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(225,	102,	'lastarticles_controller',	's:61:\"index, listing, detail, custom, newsletter, sitemap, campaign\";',	'Controller-Auswahl',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(231,	102,	'lastarticlestoshow',	's:1:\"5\";',	'Anzahl Artikel in Verlauf (zuletzt angeschaut)',	NULL,	'text',	0,	0,	0,	NULL),
(235,	124,	'mailer_mailer',	's:4:\"mail\";',	'Methode zum Senden der Mail',	'mail, smtp oder file',	'text',	0,	0,	1,	NULL),
(236,	124,	'mailer_hostname',	's:0:\"\";',	'Hostname für die Message-ID',	'Wird im Header mittels HELO verwendet. Andernfalls wird der Wert aus SERVER_NAME oder \"localhost.localdomain\" genutzt.',	'text',	0,	0,	1,	NULL),
(237,	124,	'mailer_host',	's:9:\"localhost\";',	'Mail Host',	'Es kann auch ein anderer Port über dieses Muster genutzt werden: [hostname:port] - Bsp.: smtp1.example.com:25',	'text',	0,	0,	1,	NULL),
(238,	124,	'mailer_port',	's:2:\"25\";',	'Standard Port',	'Setzt den Standard SMTP Server-Port',	'text',	0,	0,	1,	NULL),
(239,	124,	'mailer_smtpsecure',	's:0:\"\";',	'Verbindungs Präfix',	'\"\", ssl, oder tls',	'text',	0,	0,	1,	NULL),
(240,	124,	'mailer_username',	's:0:\"\";',	'SMTP Benutzername',	NULL,	'text',	0,	0,	1,	NULL),
(241,	124,	'mailer_password',	's:0:\"\";',	'SMTP Passwort',	NULL,	'text',	0,	0,	1,	'a:2:{s:9:\"inputType\";s:8:\"password\";s:10:\"autoCreate\";a:1:{s:12:\"autocomplete\";s:3:\"off\";}}'),
(242,	124,	'mailer_auth',	's:0:\"\";',	'Verbindungs-Authentifizierung',	'plain, login oder crammd5',	'text',	0,	0,	1,	NULL),
(252,	0,	'cachesearch',	'i:86400;',	'Cache Suche',	NULL,	'interval',	0,	0,	0,	NULL),
(254,	128,	'setoffline',	'b:0;',	'Shop wegen Wartung sperren',	NULL,	'boolean',	0,	0,	1,	NULL),
(255,	128,	'offlineip',	's:1:\"0\";',	'Von der Sperrung ausgeschlossene IP',	NULL,	'text',	0,	0,	1,	NULL),
(269,	133,	'show',	'i:1;',	'Menü zeigen',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(270,	133,	'levels',	'i:2;',	'Anzahl Ebenen',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(271,	133,	'caching',	'i:1;',	'Caching aktivieren',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(272,	133,	'cachetime',	'i:86400;',	'Cachezeit',	NULL,	'interval',	0,	0,	0,	'a:0:{}'),
(273,	134,	'compareShow',	'i:1;',	'Vergleich zeigen',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(274,	134,	'maxComparisons',	'i:5;',	'Maximale Anzahl von zu vergleichenden Artikeln',	NULL,	'number',	0,	0,	1,	'a:0:{}'),
(286,	144,	'articlesperpage',	's:2:\"12\";',	'Artikel pro Seite',	NULL,	'text',	0,	0,	0,	NULL),
(289,	145,	'markasnew',	's:2:\"30\";',	'Artikel als neu markieren (Tage)',	NULL,	'text',	0,	0,	0,	NULL),
(290,	145,	'markastopseller',	's:2:\"30\";',	'Artikel als Topseller markieren (Verkäufe)',	NULL,	'text',	0,	0,	0,	NULL),
(291,	145,	'chartrange',	'i:8;',	'Anzahl Topseller für Charts',	NULL,	'number',	0,	0,	0,	NULL),
(292,	144,	'numberarticlestoshow',	's:11:\"12|24|36|48\";',	'Auswahl Artikel pro Seite',	NULL,	'text',	0,	0,	0,	NULL),
(293,	144,	'categorytemplates',	's:0:\"\";',	'Verfügbare Listen Layouts',	NULL,	'textarea',	0,	0,	0,	NULL),
(294,	147,	'maxpurchase',	's:3:\"100\";',	'Max. wählbare Artikelmenge / Artikel über Pulldown-Menü',	NULL,	'text',	0,	0,	0,	NULL),
(295,	147,	'notavailable',	's:21:\"Lieferzeit ca. 5 Tage\";',	'Text für nicht verfügbare Artikel',	NULL,	'text',	0,	0,	1,	NULL),
(296,	146,	'maxcrosssimilar',	's:1:\"8\";',	'Anzahl ähnlicher Artikel Cross-Selling',	NULL,	'text',	0,	0,	0,	NULL),
(297,	146,	'maxcrossalsobought',	's:1:\"8\";',	'Anzahl \"Kunden kauften auch\" Artikel Cross-Selling',	NULL,	'text',	0,	0,	0,	NULL),
(299,	145,	'chartinterval',	's:2:\"10\";',	'Anzahl der Tage, die für die Topseller-Generierung berücksichtigt werden',	NULL,	'text',	0,	0,	0,	NULL),
(300,	146,	'similarlimit',	's:1:\"0\";',	'Anzahl automatisch ermittelter, ähnlicher Artikel (Detailseite)',	'Wenn keine ähnlichen Produkte gefunden wurden, kann Shopware automatisch alternative Vorschläge generieren. Du kannst die automatischen Vorschläge aktivieren, indem du einen Wert größer als 0 einträgst. Das Aktivieren kann sich negativ auf die Performance des Shops auswirken.',	'text',	0,	0,	0,	NULL),
(301,	147,	'basketshippinginfo',	'b:1;',	'Lieferzeit im Warenkorb anzeigen',	NULL,	'boolean',	0,	0,	0,	NULL),
(303,	147,	'inquiryid',	's:2:\"16\";',	'Anfrage-Formular ID',	NULL,	'text',	0,	0,	1,	NULL),
(304,	147,	'inquiryvalue',	's:3:\"150\";',	'Mind. Warenkorbwert ab dem die Möglichkeit der individuellen Anfrage angeboten wird',	NULL,	'text',	0,	0,	0,	NULL),
(305,	147,	'usezoomplus',	'b:1;',	'Zoomviewer statt Lightbox auf Detailseite',	NULL,	'boolean',	0,	0,	0,	NULL),
(310,	147,	'deactivatebasketonnotification',	'b:1;',	'Warenkorb bei E-Mail-Benachrichtigung ausblenden',	'Warenkorb bei aktivierter E-Mail-Benachrichtigung und nicht vorhandenem Lagerbestand ausblenden',	'boolean',	0,	0,	0,	NULL),
(311,	147,	'instockinfo',	'b:0;',	'Lagerbestands-Unterschreitung im Warenkorb anzeigen',	NULL,	'boolean',	0,	0,	0,	NULL),
(312,	144,	'categorydetaillink',	'b:0;',	'Direkt auf Detailspringen, falls nur ein Artikel vorhanden ist',	NULL,	'boolean',	0,	0,	0,	NULL),
(314,	147,	'detailtemplates',	's:9:\":Standard\";',	'Verfügbare Templates Detailseite',	NULL,	'textarea',	0,	0,	0,	NULL),
(317,	157,	'minpassword',	's:1:\"8\";',	'Mindestlänge Passwort (Registrierung)',	NULL,	'text',	0,	0,	0,	NULL),
(318,	157,	'defaultpayment',	's:1:\"5\";',	'Standardzahlungsart (Id) (Registrierung)',	NULL,	'select',	1,	0,	1,	'a:3:{s:5:\"store\";s:12:\"base.Payment\";s:12:\"displayField\";s:11:\"description\";s:10:\"valueField\";s:2:\"id\";}'),
(319,	157,	'newsletterdefaultgroup',	's:1:\"1\";',	'Standard-Empfangsgruppe (ID) für registrierte Kunden (System / Newsletter)',	NULL,	'text',	0,	0,	1,	NULL),
(320,	157,	'shopwaremanagedcustomernumbers',	'b:1;',	'Shopware generiert Kundennummern',	NULL,	'boolean',	0,	0,	0,	NULL),
(321,	277,	'ignoreagb',	'b:0;',	'AGB - Checkbox auf Kassenseite deaktivieren',	NULL,	'boolean',	0,	10,	0,	NULL),
(323,	277,	'actdprcheck',	'b:0;',	'Datenschutzhinweise müssen über Checkbox akzeptiert werden',	'Bitte aktiviere vorher die Checkbox \"Datenschutzhinweis anzeigen\"',	'boolean',	0,	1,	0,	NULL),
(324,	157,	'paymentdefault',	's:1:\"5\";',	'Fallback-Zahlungsart (ID)',	NULL,	'text',	0,	0,	0,	NULL),
(325,	157,	'doubleemailvalidation',	'b:0;',	'E-Mail Addresse muss zweimal eingegeben werden.',	'E-Mail Addresse muss zweimal eingegeben werden, um Tippfehler zu vermeiden.',	'boolean',	0,	0,	0,	NULL),
(326,	277,	'newsletterextendedfields',	'b:1;',	'Erweiterte Felder in Newsletter-Registrierung abfragen',	NULL,	'boolean',	0,	10,	1,	NULL),
(327,	157,	'noaccountdisable',	'b:0;',	'\"Kein Kundenkonto\" deaktivieren',	NULL,	'boolean',	0,	0,	0,	NULL),
(585,	173,	'blockIp',	'N;',	'IP von Statistiken ausschließen',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(608,	189,	'sql_protection',	'b:1;',	'SQL-Injection-Schutz aktivieren',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(610,	189,	'xss_protection',	'b:1;',	'XSS-Schutz aktivieren',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(612,	189,	'rfi_protection',	'b:1;',	'RemoteFileInclusion-Schutz aktivieren',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(614,	0,	'vouchername',	's:9:\"Gutschein\";',	'Gutscheine Bezeichnung',	NULL,	'text',	0,	0,	1,	NULL),
(615,	190,	'minsearchlenght',	's:1:\"3\";',	'Minimale Suchwortlänge',	NULL,	'text',	0,	0,	0,	NULL),
(620,	0,	'discountname',	's:15:\"Warenkorbrabatt\";',	'Rabatte Bezeichnung ',	NULL,	'text',	0,	0,	1,	NULL),
(623,	0,	'surchargename',	's:20:\"Mindermengenzuschlag\";',	'Mindermengen Bezeichnung',	NULL,	'text',	0,	0,	1,	NULL),
(624,	192,	'no_order_mail',	'b:0;',	'Bestellbestätigung an Shopbetreiber deaktivieren',	NULL,	'boolean',	0,	0,	0,	NULL),
(625,	190,	'badwords',	's:375:\"ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,für,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,können,wird,sind,ihre,einem,of,du,sind,einer,über,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am\";',	'Blacklist für Keywords',	NULL,	'text',	1,	0,	0,	NULL),
(626,	0,	'paymentsurchargeadd',	's:25:\"Zuschlag für Zahlungsart\";',	'Bezeichnung proz. Zuschlag für Zahlungsart',	NULL,	'text',	0,	0,	1,	NULL),
(627,	0,	'paymentsurchargedev',	's:25:\"Abschlag für Zahlungsart\";',	'Bezeichnung proz. Abschlag für Zahlungsart',	NULL,	'text',	0,	0,	1,	NULL),
(628,	191,	'discountnumber',	's:11:\"sw-discount\";',	'Rabatte Bestellnummer',	NULL,	'text',	0,	0,	1,	NULL),
(629,	191,	'surchargenumber',	's:12:\"sw-surcharge\";',	'Mindermengen Bestellnummer',	NULL,	'text',	0,	0,	1,	NULL),
(630,	191,	'paymentsurchargenumber',	's:10:\"sw-payment\";',	'Zuschlag für Zahlungsart (Bestellnummer)',	NULL,	'text',	0,	0,	1,	NULL),
(631,	190,	'maxlivesearchresults',	's:1:\"6\";',	'Anzahl der Ergebnisse in der Livesuche',	NULL,	'text',	0,	0,	0,	NULL),
(633,	192,	'send_confirm_mail',	'b:1;',	'Registrierungsbestätigung in CC an Shopbetreiber schicken',	NULL,	'boolean',	0,	0,	0,	NULL),
(634,	277,	'optinnewsletter',	'b:0;',	'Double-Opt-In für Newsletter-Anmeldungen',	NULL,	'boolean',	0,	10,	0,	NULL),
(635,	277,	'optinvote',	'b:0;',	'Double-Opt-In für Blog- & Artikel-Bewertungen',	NULL,	'boolean',	0,	10,	0,	NULL),
(636,	191,	'shippingdiscountnumber',	's:16:\"SHIPPINGDISCOUNT\";',	'Abschlag-Versandregel (Bestellnummer)',	NULL,	'text',	0,	0,	1,	NULL),
(637,	0,	'shippingdiscountname',	's:15:\"Warenkorbrabatt\";',	'Abschlag-Versandregel (Bezeichnung)',	NULL,	'text',	0,	0,	1,	NULL),
(641,	192,	'orderstatemailack',	's:0:\"\";',	'Bestellstatus - Änderungen CC-Adresse',	NULL,	'text',	0,	0,	0,	NULL),
(642,	247,	'premiumshippiungasketselect',	's:93:\"MAX(a.topseller) as has_topseller, MAX(at.attr3) as has_comment, MAX(b.esdarticle) as has_esd\";',	'Erweitere SQL-Abfrage',	NULL,	'text',	1,	0,	0,	NULL),
(643,	247,	'premiumshippingnoorder',	'b:0;',	'Bestellung bei keiner verfügbaren Versandart blocken',	NULL,	'boolean',	1,	0,	0,	NULL),
(646,	249,	'routertolower',	'b:1;',	'Nur Kleinbuchstaben in den Urls nutzen',	NULL,	'boolean',	0,	0,	0,	NULL),
(649,	249,	'seometadescription',	'b:1;',	'Meta-Description von Artikel/Kategorien aufbereiten',	NULL,	'boolean',	0,	0,	1,	NULL),
(650,	249,	'routerremovecategory',	'b:0;',	'KategorieID aus Url entfernen',	NULL,	'boolean',	0,	0,	1,	NULL),
(651,	249,	'seoqueryblacklist',	's:50:\"sPage,sPerPage,sSupplier,sFilterProperties,p,n,s,f\";',	'SEO-Noindex Querys',	NULL,	'text',	0,	0,	0,	NULL),
(652,	249,	'seoviewportblacklist',	's:112:\"login,ticket,tellafriend,note,support,basket,admin,registerFC,newsletter,search,search,account,checkout,register\";',	'SEO-Noindex Viewports',	NULL,	'text',	0,	0,	0,	NULL),
(654,	249,	'seoremovecomments',	'b:1;',	'Html-Kommentare entfernen',	NULL,	'boolean',	0,	0,	0,	NULL),
(655,	249,	'seoqueryalias',	's:244:\"sSearch=q,\nsPage=p,\nsPerPage=n,\nsSupplier=s,\nsFilterProperties=f,\nsCategory=c,\nsCoreId=u,\nsTarget=t,\nsValidation=v,\nsTemplate=l,\npriceMin=min,\npriceMax=max,\nshippingFree=free,\nimmediateDelivery=delivery,\nsSort=o,\ncategoryFilter=cf,\nvariants=var\";',	'Query-Aliase',	NULL,	'textarea',	0,	0,	0,	NULL),
(656,	249,	'seobacklinkwhitelist',	's:54:\"www.shopware.de,\r\nwww.shopware.ag,\r\nwww.shopware-ag.de\";',	'SEO-Follow Backlinks',	NULL,	'textarea',	0,	0,	1,	NULL),
(658,	249,	'routerlastupdate',	NULL,	'Datum des letzten Updates',	NULL,	'datetime',	0,	0,	1,	NULL),
(659,	249,	'routercache',	's:5:\"86400\";',	'SEO-Urls Cachezeit Tabelle',	NULL,	'text',	0,	0,	0,	NULL),
(665,	157,	'vatcheckrequired',	'b:0;',	'USt-IdNr. für Firmenkunden als Pflichtfeld markieren',	NULL,	'boolean',	0,	0,	1,	NULL),
(667,	249,	'routerarticletemplate',	's:70:\"{sCategoryPath articleID=$sArticle.id}/{$sArticle.id}/{$sArticle.name}\";',	'SEO-Urls Artikel-Template',	NULL,	'text',	0,	0,	1,	NULL),
(668,	249,	'routercategorytemplate',	's:41:\"{sCategoryPath categoryID=$sCategory.id}/\";',	'SEO-Urls Kategorie-Template',	NULL,	'text',	0,	0,	1,	NULL),
(670,	249,	'seostaticurls',	NULL,	'sonstige SEO-Urls',	NULL,	'textarea',	0,	0,	1,	NULL),
(673,	119,	'shopName',	's:13:\"Shopware Demo\";',	'Name des Shops',	NULL,	'text',	1,	0,	1,	NULL),
(674,	119,	'mail',	's:16:\"info@example.com\";',	'Shopbetreiber E-Mail',	NULL,	'text',	1,	0,	1,	NULL),
(675,	119,	'address',	's:0:\"\";',	'Adresse',	NULL,	'textarea',	0,	0,	1,	NULL),
(677,	119,	'bankAccount',	's:0:\"\";',	'Bankverbindung',	NULL,	'textarea',	0,	0,	1,	NULL),
(843,	274,	'captchaColor',	's:8:\"51,51,51\";',	'Schriftfarbe Captcha (R,G,B)',	NULL,	'text',	0,	10,	1,	NULL),
(844,	173,	'botBlackList',	's:2768:\"antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;ko_yappo_robot;labelgrabber.txt;larbin;linkidator;linkscan;lockon;logo_gif;macworm;magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient\";',	'Bot-Liste',	NULL,	'textarea',	1,	20,	0,	NULL),
(847,	78,	'baseFile',	's:12:\"shopware.php\";',	'Base-File',	NULL,	'text',	1,	0,	0,	NULL),
(848,	253,	'esdKey',	's:33:\"552211cce724117c3178e3d22bec532ec\";',	'ESD-Key',	NULL,	'text',	1,	0,	0,	NULL),
(849,	147,	'blogdetailtemplates',	's:10:\":Standard;\";',	'Verfügbare Templates Blog-Detailseite',	NULL,	'textarea',	0,	0,	0,	NULL),
(851,	190,	'fuzzysearchexactmatchfactor',	'i:100;',	'Faktor für genaue Treffer',	NULL,	'number',	1,	0,	1,	NULL),
(852,	190,	'fuzzysearchlastupdate',	's:19:\"2010-01-01 00:00:00\";',	'Datum des letzten Updates',	NULL,	'datetime',	0,	0,	0,	NULL),
(853,	190,	'fuzzysearchmatchfactor',	'i:5;',	'Faktor für unscharfe Treffer',	NULL,	'number',	1,	0,	1,	NULL),
(854,	190,	'fuzzysearchmindistancentop',	'i:20;',	'Minimale Relevanz zum Topartikel in Prozent',	NULL,	'number',	1,	0,	1,	NULL),
(855,	190,	'fuzzysearchpartnamedistancen',	'i:25;',	'Maximal-Distanz für Teilnamen in Prozent',	NULL,	'number',	1,	0,	1,	NULL),
(856,	190,	'fuzzysearchpatternmatchfactor',	'i:50;',	'Faktor für Teiltreffer',	NULL,	'number',	1,	0,	1,	NULL),
(859,	190,	'fuzzysearchselectperpage',	's:11:\"12|24|36|48\";',	'Auswahl Ergebnisse pro Seite',	NULL,	'text',	1,	0,	1,	NULL),
(860,	253,	'esdMinSerials',	'i:5;',	'ESD-Min-Serials',	NULL,	'text',	1,	0,	0,	NULL),
(867,	255,	'alsoBoughtShow',	'b:1;',	'Anzeigen der Kunden-kauften-auch-Empfehlung',	NULL,	'checkbox',	1,	1,	1,	NULL),
(868,	255,	'alsoBoughtPerPage',	'i:4;',	'Anzahl an Artikel pro Seite in der Liste',	NULL,	'number',	1,	2,	1,	NULL),
(869,	255,	'alsoBoughtMaxPages',	'i:10;',	'Maximale Anzahl von Seiten in der Liste',	NULL,	'number',	1,	3,	1,	NULL),
(870,	255,	'similarViewedShow',	'b:1;',	'Anzeigen der Kunden-schauten-sich-auch-an-Empfehlung',	NULL,	'checkbox',	1,	5,	1,	NULL),
(871,	255,	'similarViewedPerPage',	'i:4;',	'Anzahl an Artikel pro Seite in der Liste',	NULL,	'number',	1,	6,	1,	NULL),
(872,	255,	'similarViewedMaxPages',	'i:10;',	'Maximale Anzahl von Seiten in der Liste',	NULL,	'number',	1,	7,	1,	NULL),
(873,	256,	'revocationNotice',	'b:1;',	'Zeige Widerrufsbelehrung an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(874,	256,	'newsletter',	'b:0;',	'Zeige Newsletter-Registrierung an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(875,	256,	'bankConnection',	'b:0;',	'Zeige Bankverbindungshinweis an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(876,	256,	'additionalFreeText',	'b:0;',	'Zeige weiteren Hinweis an',	'Snippet: ConfirmTextOrderDefault',	'boolean',	0,	0,	1,	'a:0:{}'),
(877,	256,	'commentVoucherArticle',	'b:0;',	'Zeige weitere Optionen an',	'Artikel hinzuf&uuml;gen, Gutschein hinzuf&uuml;gen, Kommentarfunktion',	'boolean',	0,	0,	1,	'a:0:{}'),
(879,	256,	'premiumArticles',	'b:0;',	'Zeige Prämienartikel an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(880,	256,	'countryNotice',	'b:1;',	'Zeige Länder-Beschreibung an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(881,	256,	'nettoNotice',	'b:0;',	'Zeige Hinweis für Netto-Bestellungen an',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(885,	256,	'mainFeatures',	's:290:\"{if $sBasketItem.additional_details.properties}\n    {$sBasketItem.additional_details.properties}\n{elseif $sBasketItem.additional_details.description}\n    {$sBasketItem.additional_details.description}\n{else}\n    {$sBasketItem.additional_details.description_long|strip_tags|truncate:50}\n{/if}\";',	'Template für die wesentliche Merkmale',	NULL,	'textarea',	0,	1,	1,	'a:0:{}'),
(886,	259,	'backendTimeout',	'i:7200;',	'PHP Timeout',	NULL,	'interval',	1,	0,	0,	'a:0:{}'),
(887,	259,	'backendLocales',	'a:2:{i:0;i:1;i:1;i:2;}',	'Auswählbare Sprachen',	NULL,	'select',	1,	0,	0,	'a:2:{s:5:\"store\";s:11:\"base.Locale\";s:11:\"multiSelect\";b:1;}'),
(888,	249,	'routerblogtemplate',	's:71:\"{sCategoryPath categoryID=$blogArticle.categoryId}/{$blogArticle.title}\";',	'SEO-Urls Blog-Template',	NULL,	'text',	0,	0,	1,	NULL),
(889,	256,	'detailModal',	'b:1;',	'Artikeldetails in Modalbox anzeigen',	NULL,	'boolean',	0,	0,	1,	NULL),
(893,	119,	'company',	's:0:\"\";',	'Firma',	NULL,	'textfield',	0,	0,	1,	NULL),
(894,	249,	'routercampaigntemplate',	's:16:\"{$campaign.name}\";',	'SEO-Urls Landingpage-Template',	NULL,	'text',	0,	0,	1,	NULL),
(897,	0,	'paymentSurchargeAbsolute',	's:25:\"Zuschlag für Zahlungsart\";',	'Pauschaler Aufschlag für Zahlungsart (Bezeichnung)',	NULL,	'text',	1,	0,	1,	NULL),
(898,	191,	'paymentSurchargeAbsoluteNumber',	's:19:\"sw-payment-absolute\";',	'Pauschaler Aufschlag für Zahlungsart (Bestellnummer)',	NULL,	'text',	1,	0,	1,	NULL),
(900,	263,	'MailCampaignsPerCall',	'i:1000;',	'Anzahl der Mails, die pro Cronjob-Aufruf versendet werden',	NULL,	'number',	1,	0,	0,	NULL),
(901,	189,	'own_filter',	'N;',	'Eigener Filter',	NULL,	'textarea',	0,	0,	0,	NULL),
(905,	157,	'accountPasswordCheck',	'b:1;',	'Aktuelles Passwort bei Passwort-Änderungen abfragen',	NULL,	'boolean',	1,	0,	0,	NULL),
(907,	249,	'preferBasePath',	'b:1;',	'Shopware-Kernel aus URL entfernen ',	'Entfernt \"shopware.php\" aus URLs. Verhindert, dass Suchmaschinen fälschlicherweise DuplicateContent im Shop erkennen. Wenn kein ModRewrite zur Verfügung steht, muss dieses Häcken entfernt werden.',	'boolean',	1,	0,	0,	NULL),
(909,	264,	'useShortDescriptionInListing',	'b:0;',	'In Listen-Ansichten immer die Artikel-Kurzbeschreibung anzeigen',	'Beeinflusst: Topseller, Kategorielisten, Einkaufswelten',	'checkbox',	0,	0,	0,	NULL),
(910,	265,	'defaultPasswordEncoder',	's:4:\"Auto\";',	'Passwort-Algorithmus',	'Mit welchem Algorithmus sollen die Passwörter verschlüsselt werden?',	'combo',	1,	0,	0,	'a:5:{s:8:\"editable\";b:0;s:10:\"valueField\";s:2:\"id\";s:12:\"displayField\";s:2:\"id\";s:13:\"triggerAction\";s:3:\"all\";s:5:\"store\";s:20:\"base.PasswordEncoder\";}'),
(911,	265,	'liveMigration',	'i:1;',	'Live Migration',	'Sollen vorhandene Benutzer-Passwörter mit anderen Passwort-Algorithmen beim nächsten Einloggen erneut gehasht werden? Das geschieht voll automatisch im Hintergrund, so dass die Passwörter sukzessiv auf einen neuen Algorithmus umgestellt werden können.',	'checkbox',	1,	0,	0,	NULL),
(912,	265,	'bcryptCost',	'i:10;',	'Bcrypt-Rechenaufwand',	'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',	'number',	1,	0,	0,	'a:2:{s:8:\"minValue\";s:1:\"4\";s:8:\"maxValue\";s:2:\"31\";}'),
(913,	265,	'sha256iterations',	'i:100000;',	'Sha256-Iterationen',	'Je höher der Rechenaufwand, desto aufwändiger ist es für einen möglichen Angreifer, ein Klartext-Passwort für das verschlüsselte Passwort zu berechnen.',	'number',	1,	0,	0,	'a:2:{s:8:\"minValue\";s:1:\"1\";s:8:\"maxValue\";s:7:\"1000000\";}'),
(914,	0,	'topSellerActive',	'i:1;',	'',	'',	'',	1,	0,	0,	''),
(915,	0,	'topSellerValidationTime',	'i:100;',	'',	'',	'',	1,	0,	0,	''),
(916,	0,	'topSellerRefreshStrategy',	'i:3;',	'',	'',	'',	1,	0,	0,	''),
(917,	0,	'topSellerPseudoSales',	'i:1;',	'',	'',	'',	1,	0,	0,	''),
(918,	0,	'seoRefreshStrategy',	'i:3;',	'',	'',	'',	1,	0,	0,	''),
(919,	0,	'searchRefreshStrategy',	'i:3;',	'',	'',	'',	1,	0,	0,	''),
(920,	0,	'showSupplierInCategories',	'i:1;',	'',	'',	'',	1,	0,	0,	''),
(922,	0,	'disableShopwareStatistics',	'i:0;',	'',	'',	'',	1,	0,	0,	''),
(923,	0,	'disableArticleNavigation',	'i:0;',	'',	'',	'',	1,	0,	0,	''),
(924,	0,	'similarRefreshStrategy',	'i:3;',	'',	'',	'',	1,	0,	0,	''),
(925,	0,	'similarActive',	'i:1;',	'',	'',	'',	1,	0,	0,	''),
(926,	0,	'similarValidationTime',	'i:100;',	'',	'',	'',	1,	0,	0,	''),
(927,	144,	'moveBatchModeEnabled',	'b:0;',	'Kategorien im Batch-Modus verschieben',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(928,	0,	'traceSearch',	'i:1;',	'',	'',	'',	1,	0,	0,	''),
(930,	0,	'displayFiltersInListings',	'i:1;',	'',	'',	'boolean',	1,	0,	0,	''),
(933,	266,	'admin',	'b:0;',	'Admin-View',	'Cache bei Artikel-Vorschau und Schnellbestellung deaktivieren',	'boolean',	0,	0,	0,	'a:0:{}'),
(934,	266,	'cacheControllers',	's:360:\"frontend/listing 3600\nfrontend/index 3600\nfrontend/detail 3600\nfrontend/campaign 14400\nwidgets/listing 14400\nfrontend/custom 14400\nfrontend/sitemap 14400\nfrontend/blog 14400\nwidgets/index 3600\nwidgets/checkout 3600\nwidgets/compare 3600\nwidgets/emotion 14400\nwidgets/recommendation 14400\nwidgets/lastArticles 3600\nwidgets/campaign 3600\nfrontend/listing/layout 0\";',	'Cache-Controller / Zeiten',	NULL,	'textarea',	0,	0,	0,	'a:0:{}'),
(935,	266,	'noCacheControllers',	's:81:\"widgets/lastArticles detail\nwidgets/checkout checkout,slt\nwidgets/compare compare\";',	'NoCache-Controller / Tags',	NULL,	'textarea',	0,	0,	0,	'a:0:{}'),
(936,	266,	'proxy',	'N;',	'Alternative Proxy-Url',	'Link zum Http-Proxy mit „http://“ am Anfang.',	'text',	0,	0,	0,	'a:0:{}'),
(937,	266,	'proxyPrune',	'b:1;',	'Proxy-Prune aktivieren',	'Das automatische Leeren des Caches aktivieren.',	'boolean',	0,	0,	0,	'a:0:{}'),
(938,	253,	'downloadAvailablePaymentStatus',	'a:1:{i:0;i:12;}',	'Download freigeben bei Zahlstatus',	'Definiere hier den Zahlstatus bei dem ein Download des ESD-Artikels möglich ist.',	'select',	1,	3,	0,	'a:4:{s:5:\"store\";s:18:\"base.PaymentStatus\";s:12:\"displayField\";s:11:\"description\";s:10:\"valueField\";s:2:\"id\";s:11:\"multiSelect\";b:1;}'),
(939,	144,	'forceArticleMainImageInListing',	'b:0;',	'Immer das Artikel-Vorschaubild anzeigen',	'z.B. im Listing oder beim Auswahl- und Bildkonfigurator ohne ausgewählte Variante. Wichtig: Bei Variantenfilterung auf aufgefächerten Variantengruppen wird diese Option nicht beachtet.',	'checkbox',	0,	0,	0,	'a:0:{}'),
(940,	256,	'sendOrderMail',	'b:1;',	'Bestell-Abschluss-E-Mail versenden',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(942,	157,	'requirePhoneField',	'b:0;',	'Telefon als Pflichtfeld behandeln',	'Beachte, dass du die Sternchenangabe über den Textbaustein RegisterLabelPhone konfigurieren musst',	'checkbox',	0,	0,	1,	'a:0:{}'),
(943,	267,	'sepaCompany',	's:0:\"\";',	'Firmenname',	NULL,	'text',	0,	1,	1,	NULL),
(944,	267,	'sepaHeaderText',	's:0:\"\";',	'Überschrift',	NULL,	'text',	0,	2,	1,	NULL),
(945,	267,	'sepaSellerId',	's:0:\"\";',	'Gläubiger-Identifikationsnummer',	NULL,	'text',	0,	3,	1,	NULL),
(946,	267,	'sepaSendEmail',	'i:1;',	'SEPA Mandat automatisch versenden',	NULL,	'checkbox',	0,	4,	1,	NULL),
(947,	267,	'sepaShowBic',	'i:1;',	'SEPA BIC Feld anzeigen',	NULL,	'checkbox',	0,	5,	1,	NULL),
(948,	267,	'sepaRequireBic',	'i:1;',	'SEPA BIC Feld erforderlich',	NULL,	'checkbox',	0,	6,	1,	NULL),
(949,	267,	'sepaShowBankName',	'i:1;',	'SEPA Kreditinstitut Feld anzeigen',	NULL,	'checkbox',	0,	7,	1,	NULL),
(950,	267,	'sepaRequireBankName',	'i:1;',	'SEPA Kreditinstitut Feld erforderlich',	NULL,	'checkbox',	0,	8,	1,	NULL),
(952,	249,	'seoSupplier',	'b:1;',	'Hersteller SEO-Informationen anwenden',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(953,	249,	'seoSupplierRouteTemplate',	's:46:\"{createSupplierPath supplierID=$sSupplier.id}/\";',	'SEO-Urls Hersteller-Template',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(954,	268,	'logMail',	'i:0;',	'Fehler an Shopbetreiber senden',	NULL,	'checkbox',	0,	0,	0,	'a:0:{}'),
(955,	173,	'maximumReferrerAge',	's:2:\"90\";',	'Maximales Alter für Referrer Statistikdaten',	'Alte Referrer Daten werden über den Aufräumen Cronjob gelöscht, falls aktiv',	'text',	0,	0,	1,	'a:0:{}'),
(956,	173,	'maximumImpressionAge',	's:2:\"90\";',	'Maximales Alter für Artikel-Impressions',	'Alte Impression Daten werden über den Aufräumen Cronjob gelöscht, falls aktiv',	'text',	0,	0,	1,	'a:0:{}'),
(957,	255,	'showTellAFriend',	'b:0;',	'Artikel weiterempfehlen anzeigen',	NULL,	'boolean',	0,	7,	1,	NULL),
(958,	102,	'lastarticles_time',	'i:15;',	'Speicherfrist in Tagen',	NULL,	'number',	0,	0,	0,	'a:0:{}'),
(959,	253,	'esdDownloadStrategy',	'i:1;',	'Downloadoption für ESD Dateien',	'<b>Achtung</b>: Diese Einstellung könnte die Funktionalität der ESD Downloads beeinträchtigen. Sobald die Dateien nicht mehr lokal sind, wird aus Sicherheitsgründen nur noch \'PHP\' verwendet. <br><br>Downloadstrategie für ESD Dateien.<br><b>Link</b>: Unter Umständen unsicher, da der Link von außen eingesehen werden kann.<br><b>PHP</b>: Der Link kann nicht eingesehen werden. PHP liefert die Datei aus. Dies kann zu Problemen bei größeren Dateien führen.<br><b>X-Sendfile</b>: Unterstützt größere Dateien und ist sicher. Benötigt das X-Sendfile Apache Module. <br><b>X-Accel</b>: Äquivalent zum X-Sendfile. Benötigt das Nginx Modul X-Accel.',	'select',	1,	4,	0,	'a:1:{s:5:\"store\";a:4:{i:0;a:2:{i:0;i:0;i:1;s:4:\"Link\";}i:1;a:2:{i:0;i:1;i:1;s:3:\"PHP\";}i:2;a:2:{i:0;i:2;i:1;s:20:\"X-Sendfile (Apache2)\";}i:3;a:2:{i:0;i:3;i:1;s:15:\"X-Accel (Nginx)\";}}}'),
(960,	269,	'update-api-endpoint',	's:34:\"http://update-api.shopware.com/v1/\";',	'API Endpoint',	NULL,	'text',	1,	0,	0,	'a:1:{s:6:\"hidden\";b:1;}'),
(961,	269,	'update-channel',	's:6:\"stable\";',	'Channel',	NULL,	'select',	0,	0,	0,	'a:1:{s:5:\"store\";a:4:{i:0;a:2:{i:0;s:6:\"stable\";i:1;s:6:\"stable\";}i:1;a:2:{i:0;s:4:\"beta\";i:1;s:4:\"beta\";}i:2;a:2:{i:0;s:2:\"rc\";i:1;s:2:\"rc\";}i:3;a:2:{i:0;s:3:\"dev\";i:1;s:3:\"dev\";}}}'),
(962,	269,	'update-code',	's:0:\"\";',	'Code',	NULL,	'text',	0,	0,	0,	'a:0:{}'),
(963,	269,	'update-fake-version',	'N;',	'Fake Version',	NULL,	'text',	0,	0,	0,	'a:1:{s:6:\"hidden\";b:1;}'),
(964,	269,	'update-feedback-api-endpoint',	's:43:\"http://feedback.update-api.shopware.com/v1/\";',	'Feedback API Endpoint',	NULL,	'text',	1,	0,	0,	'a:1:{s:6:\"hidden\";b:1;}'),
(965,	269,	'update-send-feedback',	'b:1;',	'Send feedback',	NULL,	'boolean',	0,	0,	0,	'a:0:{}'),
(966,	269,	'trackingUniqueId',	's:0:\"\";',	'Unique identifier',	NULL,	'text',	0,	0,	0,	'a:1:{s:6:\"hidden\";b:1;}'),
(967,	269,	'update-verify-signature',	'b:1;',	'Verify Signature',	NULL,	'boolean',	0,	0,	0,	'a:1:{s:6:\"hidden\";b:1;}'),
(968,	157,	'showphonenumberfield',	'b:0;',	'Telefon anzeigen',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(969,	157,	'doublepasswordvalidation',	'b:0;',	'Passwort muss zweimal eingegeben werden',	'Passwort muss zweimal angegeben werden, um Tippfehler zu vermeiden.',	'checkbox',	0,	0,	1,	'a:0:{}'),
(970,	157,	'showbirthdayfield',	'b:0;',	'Geburtstag anzeigen',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(971,	157,	'requirebirthdayfield',	'b:0;',	'Geburtstag als Pflichtfeld behandeln',	NULL,	'checkbox',	0,	0,	1,	'a:0:{}'),
(972,	157,	'showAdditionAddressLine1',	'b:0;',	'Adresszusatzzeile 1 anzeigen',	'',	'checkbox',	0,	0,	1,	'a:0:{}'),
(973,	157,	'showAdditionAddressLine2',	'b:0;',	'Adresszusatzzeile 2 anzeigen',	'',	'checkbox',	0,	0,	1,	'a:0:{}'),
(974,	157,	'requireAdditionAddressLine1',	'b:0;',	'Adresszusatzzeile 1 als Pflichtfeld behandeln',	'',	'checkbox',	0,	0,	1,	'a:0:{}'),
(975,	157,	'requireAdditionAddressLine2',	'b:0;',	'Adresszusatzzeile 2 als Pflichtfeld behandeln',	'',	'checkbox',	0,	0,	1,	'a:0:{}'),
(976,	270,	'addToQueuePerRequest',	'i:2048;',	'Number of products per queue request',	'The number of products, you want to add to queue per request. The higher the value, the longer a request will take. Too low values will result in overhead',	'number',	1,	0,	0,	'a:1:{s:10:\"attributes\";a:1:{s:8:\"minValue\";i:100;}}'),
(977,	270,	'batchItemsPerRequest',	'i:2048;',	'Products per batch request',	'The number of products, you want to be processed per request. The higher the value, the longer a request will take. Too low values will result in overhead',	'number',	1,	0,	0,	'a:1:{s:10:\"attributes\";a:1:{s:8:\"minValue\";i:50;}}'),
(978,	270,	'enableBackup',	'b:1;',	'Enable restore feature',	'Enable the restore feature.',	'checkbox',	0,	0,	0,	'a:0:{}'),
(979,	270,	'clearCache',	'b:0;',	'Invalidate products in batch mode',	'Will clear the cache for any product, which was changed in batch mode. When changing many products, this will be quite slow. Its recommended to clear the cache manually afterwards.',	'checkbox',	0,	0,	0,	'a:0:{}'),
(980,	147,	'basketShowCalculation',	'b:1;',	'Versandkostenberechnung im Warenkob anzeigen',	'Bei aktivierter Einstellung wird ein Versandkostenrechner auf der Warenkorbseite dargestellt. Diese Funktion ist nur für nicht angemeldete Kunden verfügbar.',	'boolean',	0,	0,	1,	NULL),
(981,	249,	'PageNotFoundDestination',	'i:-2;',	'\"Seite nicht gefunden\" Ziel',	'Wenn der Besucher eine nicht existierende Seite aufruft, wird ihm diese angezeigt.',	'select',	1,	0,	1,	'a:5:{s:5:\"store\";s:35:\"base.PageNotFoundDestinationOptions\";s:12:\"displayField\";s:4:\"name\";s:10:\"valueField\";s:2:\"id\";s:10:\"allowBlank\";b:0;s:8:\"pageSize\";i:25;}'),
(982,	249,	'PageNotFoundCode',	'i:404;',	'\"Seite nicht gefunden\" Fehlercode',	'Übertragener HTTP Statuscode bei \"Seite nicht gefunden\" meldungen',	'number',	1,	0,	1,	NULL),
(983,	157,	'showCompanySelectField',	'b:1;',	'\"Ich bin\" Auswahlfeld anzeigen',	'Wenn das Auswahlfeld nicht angezeigt wird, wird die Registrierung immer als Privatkunde durchgeführt. Das Auswahlfeld wird nur bei der Registrierung ausgeblendent, danach ist es beim Ändern der Benutzerdaten trotzdem verfügbar.',	'checkbox',	1,	0,	1,	'a:0:{}'),
(984,	119,	'metaIsFamilyFriendly',	'b:1;',	'Shop ist familienfreundlich',	'Setzt den Metatag \"isFamilyFriendly\" für Suchmaschinen',	'checkbox',	0,	0,	1,	'a:0:{}'),
(985,	249,	'seoCustomSiteRouteTemplate',	's:19:\"{$site.description}\";',	'SEO-Urls Shopseiten Template',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(986,	249,	'seoFormRouteTemplate',	's:12:\"{$form.name}\";',	'SEO-Urls Formular Template',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(987,	0,	'showImmediateDeliveryFacet',	'i:1;',	'',	'',	'boolean',	1,	0,	0,	NULL),
(988,	0,	'showShippingFreeFacet',	'i:1;',	'',	'',	'boolean',	1,	0,	0,	NULL),
(989,	0,	'showPriceFacet',	'i:1;',	'',	'',	'boolean',	1,	0,	0,	NULL),
(990,	0,	'showVoteAverageFacet',	'i:1;',	'',	'',	'boolean',	1,	0,	0,	NULL),
(991,	144,	'defaultListingSorting',	'i:1;',	'Kategorie Standard Sortierung',	'',	'custom-sorting-selection',	1,	0,	1,	NULL),
(992,	190,	'searchProductBoxLayout',	's:5:\"basic\";',	'Produkt Layout',	'Mit Hilfe des Produkt Layouts kannst du entscheiden, wie deine Produkte auf der Suchergebnis-Seite dargestellt werden sollen. Wähle eines der drei unterschiedlichen Layouts um die Ansicht perfekt auf dein Produktsortiment abzustimmen.',	'product-box-layout-select',	0,	0,	1,	NULL),
(993,	147,	'hideNoInStock',	'b:0;',	'Abverkaufsartikel ohne Lagerbestand ausblenden',	'Falls inaktiv, kann es zu längeren Ladezeiten im Listing kommen, wenn die aufgefächerte Variantenfilterung genutzt wird. Bei Nutzung von ElasticSearch tritt dieser Effekt nicht auf.',	'checkbox',	0,	0,	0,	NULL),
(994,	192,	'emailheaderplain',	's:0:\"\";',	'E-Mail Header Plaintext',	NULL,	'textarea',	0,	0,	1,	NULL),
(995,	192,	'emailfooterplain',	's:63:\"\nMit freundlichen Grüßen\n\nIhr Team von {config name=shopName}\";',	'E-Mail Footer Plaintext',	NULL,	'textarea',	0,	0,	1,	NULL),
(996,	192,	'emailheaderhtml',	's:240:\"<div>\n    {if $theme.mobileLogo}\n        <img src=\"{link file=$theme.mobileLogo fullPath}\" alt=\"Logo\" />\n    {else}\n        <img src=\"{link file=\'frontend/_public/src/img/logos/logo--mobile.png\' fullPath}\" alt=\"Logo\" />\n    {/if}\n    <br />\";',	'E-Mail Header HTML',	NULL,	'textarea',	0,	0,	1,	NULL),
(997,	192,	'emailfooterhtml',	's:84:\"<br/>\nMit freundlichen Grüßen<br/><br/>\n\nIhr Team von {config name=shopName}</div>\";',	'E-Mail Footer HTML',	NULL,	'textarea',	0,	0,	1,	NULL),
(998,	253,	'showEsd',	'b:1;',	'Sofortdownloads im Account anzeigen',	'Sofortdownloads können weiterhin über die Bestellübersicht heruntergeladen werden.',	'boolean',	1,	5,	1,	NULL),
(999,	259,	'firstRunWizardEnabled',	'b:1;',	'\'First Run Wizard\' beim Aufruf des Backends starten',	NULL,	'checkbox',	0,	0,	0,	NULL),
(1000,	256,	'showEsdWarning',	'b:1;',	'Checkbox zum Widerrufsrecht bei ESD Artikeln anzeigen',	NULL,	'boolean',	0,	0,	1,	'a:0:{}'),
(1001,	256,	'serviceAttrField',	's:0:\"\";',	'Artikel-Freitextfeld für Dienstleistungsartikel',	NULL,	'text',	0,	0,	1,	'a:0:{}'),
(1002,	249,	'seoIndexPaginationLinks',	'b:0;',	'prev/next-Tag auf paginierten Seiten benutzen',	'Wenn aktiv, wird auf paginierten Seiten anstatt des Canoncial-Tags der prev/next-Tag benutzt.',	'checkbox',	0,	0,	0,	'a:0:{}'),
(1003,	271,	'thumbnailNoiseFilter',	'b:0;',	'Rauschfilterung bei Thumbnails',	'Filtert beim Generieren der Thumbnails Bildfehler heraus. Achtung! Bei aktivierter Option kann das Generieren der Thumbnails wesentlich länger dauern',	'checkbox',	0,	0,	0,	'a:0:{}'),
(1004,	0,	'tokenSecret',	's:0:\"\";',	'Secret für die API Kommunikation',	NULL,	'text',	0,	0,	0,	NULL),
(1005,	249,	'RelatedArticlesOnArticleNotFound',	'b:1;',	'Zeige ähnliche Artikel auf der \"Artikel nicht gefunden\" Seite an',	'Wenn aktiviert, zeigt die \"Artikel nicht gefunden\" Seite die ähnlichen Artikel Vorschläge an. Deaktiviere diese Einstellung um die Standard \"Seite nicht gefunden\" Seite darzustellen.',	'boolean',	1,	0,	1,	''),
(1006,	157,	'showZipBeforeCity',	'b:1;',	'PLZ vor dem Stadtfeld anzeigen',	'Legt fest ob die PLZ vor oder nach der Stadt angezeigt werden soll. Nur für Shopware 5 Themes.',	'checkbox',	0,	0,	1,	'a:0:{}'),
(1007,	0,	'updateWizardStarted',	'b:1;',	'',	'',	'checkbox',	0,	0,	1,	NULL),
(1008,	272,	'mobileSitemap',	'b:1;',	'Mobile Sitemap generieren',	'Wenn diese Option aktiviert ist, wird eine zusätzliche sitemap.xml mit der Struktur für mobile Endgeräte generiert.',	'boolean',	1,	1,	0,	NULL),
(1009,	144,	'calculateCheapestPriceWithMinPurchase',	'b:0;',	'Mindestabnahme bei der Günstigsten-Preis-Berechnung berücksichtigen',	NULL,	'checkbox',	0,	0,	1,	NULL),
(1010,	144,	'useLastGraduationForCheapestPrice',	'b:0;',	'Staffelpreise in der Günstigsten Preis Berechnung berücksichtigen',	NULL,	'checkbox',	0,	0,	1,	NULL),
(1011,	0,	'lastBacklogId',	'i:0;',	'',	'Last processed backlog id',	'',	0,	0,	0,	NULL),
(1012,	190,	'activateNumberSearch',	'i:1;',	'Nummern Suche aktivieren',	NULL,	'checkbox',	1,	0,	0,	NULL),
(1013,	190,	'enableAndSearchLogic',	'b:0;',	'\"Und\" Suchlogik verwenden',	'Die Suche zeigt nur Treffer an, in denen alle Suchbegriffe vorkommen.',	'checkbox',	0,	0,	1,	NULL),
(1014,	256,	'always_select_payment',	'b:0;',	'Zahlungsart bei Bestellung immer auswählen',	NULL,	'boolean',	0,	0,	1,	NULL),
(1015,	259,	'ajaxTimeout',	'i:30;',	'Ajax Timeout',	'Definiert die maximale Ausführungszeit für ExtJS Ajax Requests (in Sekunden)',	'number',	1,	0,	0,	'a:1:{s:8:\"minValue\";i:6;}'),
(1016,	157,	'shopsalutations',	's:5:\"mr,ms\";',	'Verfügbare Anreden',	'Ermöglicht die Konfiguration welche Anreden in diesem Shop zur Verfügung stehen. Die hier definierten Keys werden automatisch als Textbaustein unter dem Namespace frontend/salutation angelegt und können dort übersetzt werden.',	'text',	0,	0,	1,	NULL),
(1017,	157,	'displayprofiletitle',	'b:0;',	'Titel Feld anzeigen',	NULL,	'boolean',	0,	0,	1,	NULL),
(1018,	0,	'installationDate',	's:16:\"2019-12-06 10:19\";',	'Installationsdatum',	NULL,	'text',	0,	0,	0,	NULL),
(1019,	0,	'installationSurvey',	'b:1;',	'Umfrage zur Installation',	NULL,	'boolean',	0,	0,	0,	NULL),
(1020,	0,	'assetTimestamp',	'i:0;',	'',	'Cache invalidation timestamp for assets',	'',	0,	0,	1,	NULL),
(1021,	277,	'sendRegisterConfirmation',	'b:1;',	'Bestätigungsmail nach Registrierung verschicken',	NULL,	'boolean',	0,	20,	0,	NULL),
(1022,	144,	'maxStoreFrontLimit',	'i:100;',	'Maximale Anzahl Produkte pro Seite',	NULL,	'number',	0,	0,	0,	NULL),
(1023,	189,	'strip_tags',	'b:1;',	'Global strip_tags verwenden',	'Wenn aktiviert wird jeder Formularinput im Frontend mittels strip_tags gefiltert.',	'checkbox',	1,	0,	0,	NULL),
(1024,	90,	'displayOnlySubShopVotes',	'b:0;',	'Nur Subshopspezifische Bewertungen anzeigen',	'description',	'checkbox',	0,	0,	1,	NULL),
(1025,	274,	'captchaMethod',	's:7:\"default\";',	'Captcha Methode',	'Wähle hier eine Methode aus, wie die Formulare gegen Spam-Bots geschützt werden sollen',	'combo',	1,	0,	1,	'a:5:{s:8:\"editable\";b:0;s:10:\"valueField\";s:2:\"id\";s:12:\"displayField\";s:11:\"displayname\";s:13:\"triggerAction\";s:3:\"all\";s:5:\"store\";s:12:\"base.Captcha\";}'),
(1026,	274,	'noCaptchaAfterLogin',	'b:0;',	'Nach Login ausblenden',	'Nach dem Login können Kunden Formulare ohne Captcha-Überprüfung absenden.',	'checkbox',	0,	1,	1,	''),
(1027,	144,	'displayListingBuyButton',	'b:0;',	'Kaufenbutton im Listing anzeigen',	'',	'checkbox',	1,	0,	1,	NULL),
(1028,	277,	'show_cookie_note',	'b:0;',	'Cookie Hinweis anzeigen',	'Wenn diese Option aktiv ist, wird eine Hinweismeldung angezeigt die den Nutzer über die Cookie-Richtlinien informiert. Der Inhalt kann über das Textbausteinmodul editiert werden.',	'boolean',	0,	21,	1,	NULL),
(1029,	277,	'data_privacy_statement_link',	's:0:\"\";',	'Link zur Datenschutzerklärung für Cookies',	NULL,	'text',	0,	20,	1,	NULL),
(1030,	0,	'listingMode',	's:16:\"full_page_reload\";',	'',	'',	'listing-filter-mode-select',	1,	0,	0,	NULL),
(1031,	157,	'registerCaptcha',	's:9:\"nocaptcha\";',	'Captcha in Registrierung verwenden',	'Wenn diese Option aktiv ist, wird ein Captcha zur Registrierung verwendent. Empfohlen für die Registrierung: Honeypot',	'combo',	1,	0,	1,	'a:5:{s:8:\"editable\";b:0;s:10:\"valueField\";s:2:\"id\";s:12:\"displayField\";s:11:\"displayname\";s:13:\"triggerAction\";s:3:\"all\";s:5:\"store\";s:12:\"base.Captcha\";}'),
(1032,	190,	'searchSortings',	's:13:\"|7|1|2|3|4|5|\";',	'Verfügbare Sortierungen',	'',	'custom-sorting-grid',	1,	0,	1,	NULL),
(1033,	190,	'searchFacets',	's:15:\"|1|2|3|4|5|6|7|\";',	'Verfügbare filter',	'',	'custom-facet-grid',	0,	0,	1,	NULL),
(1034,	263,	'newsletterCaptcha',	's:9:\"nocaptcha\";',	'Captcha in Newsletter verwenden',	'Die hier ausgewählte Captcha Methode wird bei der Newsletterregistrierung im Frontend verwendet.',	'combo',	1,	0,	1,	'a:5:{s:8:\"editable\";b:0;s:10:\"valueField\";s:2:\"id\";s:12:\"displayField\";s:11:\"displayname\";s:13:\"triggerAction\";s:3:\"all\";s:5:\"store\";s:12:\"base.Captcha\";}'),
(1035,	157,	'useSltCookie',	'b:1;',	'Shopware Login Cookie erstellen',	'Es wird ein Cookie gespeichert, an dem der Benutzer wieder identifiziert werden kann. Dieser wird nur für das Setzen der aktuellen Kundengruppe sowie aktiven Customer Streams verwendet',	'boolean',	1,	0,	0,	NULL),
(1036,	259,	'backendMenuOnHover',	'b:1;',	'Backend Menüeinträge automatisch ausklappen',	'Das Verhalten der Buttons in der oberen Menüleiste im Backend ändert sich mit dieser Option. Falls diese Option auf Nein gesetzt ist, müssen die Menüeinträge manuell durch einen Mausklick geöffnet werden. (Backend Cache leeren und Neuladen des Backends erforderlich)',	'checkbox',	0,	0,	0,	NULL),
(1037,	259,	'growlMessageDisplayPosition',	's:9:\"top-right\";',	'Benachrichtigungs Position',	'Mit dieser Option können die Backend Benachrichtungen an einer anderen Stelle angezeigt werden (Backend Cache leeren und Neuladen des Backends erforderlich)',	'select',	1,	0,	0,	'a:5:{s:8:\"editable\";b:0;s:10:\"valueField\";s:8:\"position\";s:12:\"displayField\";s:11:\"displayName\";s:9:\"queryMode\";s:5:\"local\";s:5:\"store\";s:19:\"base.CornerPosition\";}'),
(1038,	268,	'logMailAddress',	's:0:\"\";',	'Alternative E-Mail-Adresse für Fehlermeldungen',	'Wenn dieses Feld leer ist, wird die Shopbetreiber E-Mail-Adresse verwendet',	'text',	0,	0,	0,	NULL),
(1039,	144,	'manufacturerProductBoxLayout',	's:5:\"basic\";',	'Produktlayout im Herstellerlisting',	'',	'product-box-layout-select',	0,	0,	1,	NULL),
(1040,	268,	'logMailLevel',	's:7:\"Warning\";',	'Log-Level',	'Hier wird festgelegt, ab welchem Log-Level E-Mails versendet werden. Im Standard werden E-Mails ab dem Log-Level \"Warning\" verschickt. Um nur E-Mails bei Fehlern zu bekommen, kannst du das Log-Level erhöhen, zum Beispiel auf \"Error\" oder höher.',	'select',	1,	0,	0,	'a:1:{s:5:\"store\";a:8:{i:0;a:2:{i:0;s:5:\"DEBUG\";i:1;s:5:\"Debug\";}i:1;a:2:{i:0;s:4:\"INFO\";i:1;s:4:\"Info\";}i:2;a:2:{i:0;s:6:\"NOTICE\";i:1;s:6:\"Notice\";}i:3;a:2:{i:0;s:7:\"WARNING\";i:1;s:7:\"Warning\";}i:4;a:2:{i:0;s:5:\"ERROR\";i:1;s:5:\"Error\";}i:5;a:2:{i:0;s:8:\"CRITICAL\";i:1;s:8:\"Critical\";}i:6;a:2:{i:0;s:5:\"ALERT\";i:1;s:5:\"Alert\";}i:7;a:2:{i:0;s:9:\"EMERGENCY\";i:1;s:9:\"Emergency\";}}}'),
(1041,	277,	'actdprtext',	'b:1;',	'Datenschutzhinweise anzeigen',	'Betrifft die Formulare der Registrierung, Blog- & Artikelkommentare, Newsletter, Produkt-Verfügbarkeitsbenachrichtigung (Notification-Plugin) sowie die eigenen Formulare',	'boolean',	0,	0,	0,	NULL),
(1042,	277,	'privacyGuestCustomerMonths',	'i:6;',	'Schnellbesteller ohne Bestellungen nach X Monaten löschen',	'Der Cronjob \"Guest customer cleanup\" muss hierfür aktiviert sein.',	'number',	1,	30,	0,	NULL),
(1043,	277,	'privacyBasketMonths',	'i:6;',	'Abgebrochene Bestellungen nach X Monaten löschen',	'Der Cronjob \"Cancelled baskets cleanup\" muss hierfür aktiviert sein.',	'number',	1,	30,	0,	NULL),
(1044,	277,	'anonymizeIp',	'b:1;',	'Kunden IPs anonymisieren',	'Entfernt die letzten zwei Blöcke einer IPv4, resp. drei Blöcke einer IPv6 Adresse in Statistiken und Bestellungen, um rechtliche Rahmenbedingungen einzuhalten.',	'boolean',	0,	40,	0,	NULL),
(1045,	277,	'optinregister',	'b:0;',	'Double-Opt-In für Registrierung',	NULL,	'boolean',	0,	15,	0,	NULL),
(1046,	277,	'optintimetodelete',	'i:3;',	'Tage ohne Verifizierung bis zur Löschung',	'Für Double-Opt-In: Zeitraum, nachdem nicht bestätigte Aktionen gelöscht werden.',	'number',	0,	17,	0,	NULL),
(1047,	277,	'optinaccountless',	'b:0;',	'Double-Opt-In für Schnellbesteller',	NULL,	'boolean',	0,	16,	0,	NULL),
(1048,	277,	'cookie_note_mode',	'i:0;',	'Cookie-Hinweis-Modus',	NULL,	'select',	0,	21,	0,	'a:2:{s:5:\"store\";s:35:\"Shopware.apps.Base.store.CookieMode\";s:9:\"queryMode\";s:5:\"local\";}'),
(1049,	0,	'benchmarkTeaser',	'b:1;',	'Teaser zur Shopware BI',	NULL,	'boolean',	0,	0,	0,	NULL),
(1050,	147,	'proportionalTaxCalculation',	'b:0;',	'Anteilige Berechnung der Steuer-Positionen',	'',	'boolean',	0,	0,	0,	NULL),
(1051,	249,	'hrefLangEnabled',	'b:1;',	'href-lang in den Meta-Tags ausgeben',	'Wenn aktiv, werden in den Meta Tags alle Sprachen einer Seite ausgegeben',	'boolean',	0,	0,	0,	NULL),
(1052,	249,	'hrefLangCountry',	'b:1;',	'Im href-lang Sprache und Land verwenden',	'Wenn diese Option aktiviert ist, wird zusätzlich zur Sprache auch das Land ausgegeben, z.B. \"de-DE\" anstatt \"de\"',	'boolean',	0,	0,	0,	NULL),
(1053,	0,	'sitemapRefreshStrategy',	'i:3;',	'',	'',	'',	1,	0,	0,	''),
(1054,	0,	'sitemapRefreshTime',	'i:86400;',	'',	'',	'',	1,	0,	0,	''),
(1055,	0,	'sitemapLastRefresh',	'i:0;',	'',	'',	'',	1,	0,	0,	''),
(1056,	0,	'missingLicenseWarningThreshold',	'i:14;',	'',	'',	'',	1,	0,	0,	''),
(1057,	0,	'missingLicenseStopThreshold',	'i:21;',	'',	'',	'',	1,	0,	0,	''),
(1058,	249,	'hrefLangDefaultShop',	's:0:\"\";',	'href-lang Standardsprache',	'Gibt für diesen Shop \"x-default\" im href-lang-Tag aus und definiert damit die Sprache dieses Shops als Standardsprache.',	'combo',	0,	0,	0,	'a:4:{s:10:\"valueField\";s:2:\"id\";s:12:\"displayValue\";s:4:\"name\";s:5:\"store\";s:17:\"base.ShopLanguage\";s:9:\"queryMode\";s:6:\"remote\";}');

DROP TABLE IF EXISTS `s_core_config_element_translations`;
CREATE TABLE `s_core_config_element_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `locale_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id` (`element_id`,`locale_id`)
) ENGINE=InnoDB AUTO_INCREMENT=329 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`) VALUES
(1,	225,	2,	'Controller selection',	NULL),
(4,	224,	2,	'Display recently viewed items',	NULL),
(5,	269,	2,	'Activate expandable menu in storefront',	NULL),
(6,	273,	2,	'Display item comparison',	NULL),
(11,	186,	2,	'VAT vouchers',	NULL),
(12,	188,	2,	'VAT discounts',	NULL),
(13,	189,	2,	'Customer reviews must be approved',	NULL),
(14,	190,	2,	'Automatic item number suggestions',	NULL),
(15,	191,	2,	'Prefix for automatically generated item numbers',	NULL),
(16,	192,	2,	'Deactivate product evaluations ',	NULL),
(17,	193,	2,	'Automatically remind customer to submit reviews',	'Remind the customer via email of pending article reviews'),
(18,	194,	2,	'Days to wait before sending reminder',	'Days until the customer is reminded via Email of a pending article review'),
(19,	195,	2,	'Set tax for discounts dynamically',	NULL),
(22,	231,	2,	'Maximum number of items to display',	NULL),
(29,	252,	2,	'Cache search',	NULL),
(31,	254,	2,	'Close shop due to maintenance',	NULL),
(32,	255,	2,	'IP excluded from closure',	NULL),
(35,	270,	2,	'Number of tiers',	NULL),
(36,	271,	2,	'Activate caching',	NULL),
(37,	272,	2,	'Caching time',	NULL),
(38,	274,	2,	'Maximum number of items to be compared',	NULL),
(43,	286,	2,	'Items per page',	NULL),
(44,	287,	2,	'Standard sorting of listings',	NULL),
(46,	289,	2,	'Number of days items are considered new',	NULL),
(47,	290,	2,	'Number of days considered for top sellers',	NULL),
(48,	291,	2,	'Number of top sellers for charts',	NULL),
(49,	292,	2,	'Selection of items per page',	NULL),
(50,	293,	2,	'Available listing layouts',	NULL),
(51,	294,	2,	'Maximum number of items selectable via pull-down menu',	NULL),
(52,	295,	2,	'Text for unavailable items',	NULL),
(53,	296,	2,	'Number of similar items for cross selling',	NULL),
(54,	297,	2,	'Number of items \"customers also bought\"',	NULL),
(55,	298,	2,	'Standard template for new categories',	NULL),
(56,	299,	2,	'Number of days to be considered for top seller creation',	NULL),
(57,	300,	2,	'Number of automatically determined similar products (detail page)',	'If no similar articles are found, Shopware can automatically generates alternative suggestions. You can activate these suggestions if you enter a number greater than 0. May decrease performance when loading these articles.'),
(58,	301,	2,	'Show delivery time in shopping cart',	NULL),
(60,	303,	2,	'Request form ID',	NULL),
(61,	304,	2,	'Minimum shopping cart value for offering individual requests',	NULL),
(62,	305,	2,	'Zoom viewer instead of light box on detail page ',	NULL),
(67,	310,	2,	'Hide \"add to shopping cart\" option if item is out-of-stock',	'Customers can choose to be informed per email when an item is \"now in stock\".'),
(68,	311,	2,	'Display inventory shortages in shopping cart',	NULL),
(69,	312,	2,	'Jump to detail if only one item is available',	NULL),
(71,	314,	2,	'Available templates for detail page',	NULL),
(73,	317,	2,	'Minimum password length (registration)',	NULL),
(74,	318,	2,	'Standard payment method ID (registration)',	NULL),
(75,	319,	2,	'Standard recipient group ID for registered customers (system / newsletter)',	NULL),
(76,	320,	2,	'Generate customer numbers automatically',	NULL),
(77,	321,	2,	'Deactivate AGB terms checkbox on checkout page',	NULL),
(78,	322,	2,	'Display country and state fields in shipping address forms',	NULL),
(79,	323,	2,	'Data protection conditions must be accepted via checkbox',	'Please activate the checkbox \"Data protection information will be shown\" first.'),
(80,	324,	2,	'Default payment method ID',	NULL),
(81,	325,	2,	'Confirm customer email addresses',	'Customers must enter email addresses twice, in order to avoid typing mistakes.'),
(82,	326,	2,	'Check extended fields in newsletter registration',	NULL),
(83,	327,	2,	'Deactivate \"no customer account\"',	NULL),
(84,	585,	2,	'Exclude IP from statistics',	NULL),
(85,	586,	2,	'Google Analytics ID',	NULL),
(86,	587,	2,	'Google Conversion ID',	NULL),
(87,	588,	2,	'Anonymous IP address',	NULL),
(88,	589,	2,	'Cache controller/Times',	NULL),
(89,	590,	2,	'NoCache Controller/Tags',	NULL),
(90,	591,	2,	'Alternative Proxy URL',	NULL),
(91,	592,	2,	'Admin view',	NULL),
(95,	608,	2,	'Activate SQL injection protection',	NULL),
(96,	609,	2,	'SQL injection filter',	NULL),
(97,	610,	2,	'Activate XXS protection',	NULL),
(98,	611,	2,	'XXS filter',	NULL),
(99,	612,	2,	'Activate Remote File Inclusion protection',	NULL),
(100,	613,	2,	'RemoteFileInclusion-filter',	NULL),
(101,	614,	2,	'Vouchers designated as',	NULL),
(102,	615,	2,	'Maximum search term length',	NULL),
(103,	620,	2,	'Discounts designated as',	NULL),
(104,	623,	2,	'Shortages designated as',	NULL),
(105,	624,	2,	'Disable order confirmation to shop owner',	NULL),
(106,	625,	2,	'Blacklist for keywords',	NULL),
(107,	626,	2,	'Surcharges on payment methods designated as',	NULL),
(108,	627,	2,	'Designation percentual deduction on payment method',	NULL),
(109,	628,	2,	'Order number for discounts',	NULL),
(110,	629,	2,	'Order  number for shortages',	NULL),
(111,	630,	2,	'Surcharge on payment method',	NULL),
(112,	631,	2,	'Number of live search results',	NULL),
(114,	633,	2,	'Send registration confirmation to shop owner in CC',	NULL),
(115,	634,	2,	'Double opt in for newsletter subscriptions',	NULL),
(116,	635,	2,	'Double opt in for blog comments & customer reviews',	NULL),
(117,	636,	2,	'Order number for deduction dispatch rule',	NULL),
(118,	637,	2,	'Deduction dispatch rule designated as',	NULL),
(119,	641,	2,	'Order status - Changes to CC addresses',	NULL),
(120,	642,	2,	'Extended SQL query',	NULL),
(121,	643,	2,	'Block orders with no available shipping type',	NULL),
(122,	646,	2,	'Only use lower case letters in URLs',	NULL),
(124,	649,	2,	'Prepare meta description of categories / items',	NULL),
(125,	650,	2,	'Remove Category ID from URL',	NULL),
(126,	651,	2,	'SEO noindex queries',	NULL),
(127,	652,	2,	'SEO noindex viewsports',	NULL),
(129,	654,	2,	'Remove HTML comments',	NULL),
(130,	655,	2,	'Query aliases',	NULL),
(131,	656,	2,	'SEO follow backlinks',	NULL),
(133,	658,	2,	'Last update',	NULL),
(134,	659,	2,	'SEO URLs caching timetable',	NULL),
(140,	665,	2,	'Mark VAT ID number as required for company customers',	NULL),
(142,	667,	2,	'SEO URLs item template',	NULL),
(143,	668,	2,	'SEO URLs category template',	NULL),
(145,	670,	2,	'Other SEO URLs',	NULL),
(148,	673,	2,	'Shop name',	NULL),
(149,	674,	2,	'Shop owner email',	NULL),
(150,	675,	2,	'Address',	NULL),
(152,	677,	2,	'Bank account',	NULL),
(153,	843,	2,	'Captcha font color (R,G,B)',	NULL),
(154,	844,	2,	'Bot list',	NULL),
(155,	845,	2,	'Version',	NULL),
(156,	846,	2,	'Revision',	NULL),
(157,	847,	2,	'Base file',	NULL),
(158,	848,	2,	'ESD key',	NULL),
(159,	849,	2,	'Available templates for blog detail page',	NULL),
(161,	851,	2,	'Factor for accurate hits ',	NULL),
(162,	852,	2,	'Last update',	NULL),
(163,	853,	2,	'Factor for inaccurate hits ',	NULL),
(164,	854,	2,	'Minimum relevance for top items (%)',	NULL),
(165,	855,	2,	'Maximum distance allowed for partial names (%)',	NULL),
(166,	856,	2,	'Factor for partial hits',	NULL),
(169,	859,	2,	'Selection results per page',	NULL),
(170,	860,	2,	'ESD-Min-Serials',	NULL),
(171,	867,	2,	'Display \"customers also bought\" recommendations',	NULL),
(172,	868,	2,	'Number of items per page in the list',	NULL),
(173,	869,	2,	'Maximum number of pages in the list',	NULL),
(174,	870,	2,	'Display \"customers also viewed\" recommendations',	NULL),
(175,	871,	2,	'Number of items per page in the list',	NULL),
(176,	872,	2,	'Maximum number of pages in the list',	NULL),
(177,	873,	2,	'Display shop cancellation policy',	NULL),
(178,	874,	2,	'Display newsletter registration',	NULL),
(179,	875,	2,	'Display bank detail notice',	NULL),
(180,	876,	2,	'Display further notices',	'Snippet: ConfirmTextOrderDefault'),
(181,	877,	2,	'Display further options',	'Add product, add voucher, comment function'),
(182,	878,	2,	'Show Bonus System (if installed)',	NULL),
(183,	879,	2,	'Display \"free with purchase\" items',	NULL),
(184,	880,	2,	'Display country descriptions',	NULL),
(185,	881,	2,	'Display information for net orders',	NULL),
(189,	885,	2,	'Template for essential characteristics',	NULL),
(190,	886,	2,	'PHP timeout',	NULL),
(191,	887,	2,	'Selectable languages ',	NULL),
(192,	888,	2,	'SEO URLs blog template',	NULL),
(193,	889,	2,	'Display item details in modal box',	NULL),
(197,	893,	2,	'Company',	NULL),
(198,	894,	2,	'SEO URLs landing page template',	NULL),
(199,	897,	2,	'All-inclusive surcharges on payment methods designated as',	NULL),
(200,	898,	2,	'Order number for all-inclusive surcharges on payment methods designated as',	NULL),
(203,	909,	2,	'Always display item descriptions in listing views',	'Affected views: Top seller, category listings, emotions'),
(205,	236,	2,	'Message ID hostname',	'Will be received in headers on a default HELO string. If not defined, the value returned from SERVER_NAME, \"localhost.localdomain\" will be used.'),
(206,	235,	2,	'Sending method',	'mail, SMTP or file'),
(207,	238,	2,	'Default port',	'Sets the default SMTP server port.'),
(208,	239,	2,	'Connection prefix',	'\"\", ssl, or tls'),
(209,	237,	2,	'Mail host',	'You can also specify a different port by using this format: [hostname:port] - e.g., smtp1.example.com:25'),
(210,	242,	2,	'Connection auth',	'plain, login or crammd5'),
(211,	240,	2,	'SMTP username',	NULL),
(212,	241,	2,	'SMTP password',	NULL),
(213,	901,	2,	'Own filter',	NULL),
(214,	905,	2,	'Check current password at password-change requests',	NULL),
(215,	900,	2,	'Number of mails sent per call',	NULL),
(216,	938,	2,	' Release download with payment status',	'Define the payment status in which a download of ESD items is possible.'),
(217,	939,	2,	'Always display the article preview image',	'e.g. in listings or when using selection or picture configurator with no selected variant. Important: If you filter on expanded variant groups, this configuration will be ignored.'),
(218,	940,	2,	'Send order mail',	NULL),
(219,	0,	2,	'Force http canonical url',	NULL),
(220,	942,	2,	'Treat phone field as required',	'Note that you must configure the asterisk indication in the snippet RegisterLabelPhone'),
(222,	910,	2,	'Password algorithm',	'With which algorithm should the password be encrypted?'),
(223,	911,	2,	'Live migration',	'Should available user passwords be rehashed with other algorithms on next login? This is done automatically in the background, so that passwords can be gradually converted to a new algorithm.'),
(224,	912,	2,	'Bcrypt iterations',	'The higher the number of iterations, the more difficult it is for a potential attacker to calculate the clear-text password for the encrypted password.'),
(225,	913,	2,	'Sha256 iterations',	'The higher the number of iterations, the more difficult it is for a potential attacker to calculate the clear-text password for the encrypted password.'),
(226,	933,	2,	'Admin view',	'Deactivate cache for item preview in express checkout'),
(227,	934,	2,	'Controller cache timeouts',	NULL),
(228,	935,	2,	'Skip caching for controllers / tags',	NULL),
(229,	936,	2,	'Alternative proxy URL',	'Prepend \"http://\" to HTTP proxy links'),
(230,	937,	2,	'Activate cache clearing',	'Enable automatic cache clearing.'),
(232,	943,	2,	'Creditor name',	'Name of the creditor to be included in the mandate.'),
(233,	944,	2,	'Header text',	'Header text of the mandate.'),
(234,	945,	2,	'Creditor number',	'Number of the creditor to be included in the mandate.'),
(235,	946,	2,	'Send email',	'Send email to the customer with the attached SEPA mandate file.'),
(236,	947,	2,	'Show SEPA\'s BIC field',	'Allow customer to specify its BIC when filling in SEPA payment data.'),
(237,	948,	2,	'Require SEPA\'s BIC field',	'Require customer to specify its BIC when filling in SEPA payment data. This option is ignored if the field is hidden.'),
(238,	949,	2,	'Show SEPA\'s bank name field',	'Allow customer to specify its bank name when filling in SEPA payment data.'),
(239,	950,	2,	'Require SEPA\'s bank name field',	'Require customer to specify its bank name when filling in SEPA payment data. This option is ignored if the field is hidden.'),
(241,	952,	2,	'Supplier SEO',	NULL),
(242,	953,	2,	'Supplier SEO URLs template',	NULL),
(243,	955,	2,	'Maximum age for referrer statistics',	'Old referrer data will be deleted by the cron job call if active'),
(244,	956,	2,	'Maximum age for impression statistics',	'Old impression data will be deleted by the cron job call if active'),
(245,	957,	2,	'Show recommend product',	NULL),
(246,	941,	2,	'Force http canonical url',	'This option does not take effect if the option \"Use always SSL\" is activated.'),
(247,	958,	2,	'Storage period in days',	NULL),
(248,	959,	2,	'Download strategy for ESD files',	'<b>Warning</b>: Changing this setting might break ESD downloads. If not sure, use default (PHP)<br><br>Strategy to generate the download links for ESD files. If you use an external storage, the method PHP will always be used for security reasons.<br><b>Link</b>: Better performance, but possibly insecure <br><b>PHP</b>: More secure, but memory consuming, especially for bigger files <br><b>X-Sendfile</b>: Secure and lightweight, but requires X-Sendfile module and Apache2 web server <br><b>X-Accel</b>: Equivalent to X-Sendfile, but requires Nginx web server instead'),
(249,	965,	1,	'Feedback senden',	NULL),
(250,	962,	1,	'Aktionscode',	NULL),
(251,	961,	1,	'Update Kanal',	NULL),
(252,	968,	2,	'Show phone number field',	NULL),
(253,	969,	2,	'Password must be entered twice.',	'Password must be entered twice in order to avoid typing errors'),
(254,	970,	2,	'Show Birthday field',	NULL),
(255,	971,	2,	'Birthday is required',	NULL),
(256,	972,	2,	'Show additional address line 1',	''),
(257,	973,	2,	'Show additional address line 2',	''),
(258,	974,	2,	'Treat additional address line 1 as required',	''),
(259,	975,	2,	'Treat additional address line 2 as required',	''),
(260,	954,	2,	'Report errors to shop owner',	NULL),
(261,	907,	2,	'Remove \"Shopware\" from URLs',	'Remove \"shopware.php\" from URLs. Prevents search engines from incorrectly identifying duplicate content in the shop. If mod_rewrite is not available in Apache, this option must be disabled.'),
(262,	927,	2,	'Move categories in batch mode',	NULL),
(263,	980,	2,	'Show shipping fee calculation in shopping cart',	'If enabled, a shipping cost calculator will be displayed in the cart page. This is only available for customers who haven\'t logged in'),
(264,	981,	2,	'\"Page not found\" destination',	'When the user requests a non-existent page, he will be shown the following page.'),
(265,	982,	2,	'\"Page not found\" error code',	'HTTP code used in \"Page not found\" responses'),
(266,	983,	2,	'Show \"I am\" select field',	'If this option is false, all registrations will be done as a private customer. This option only affects the registration, it is still available when editing user data.'),
(267,	984,	2,	'Shop is family friendly',	'Will set the meta tag \"isFamilyFriendly\" for search engines'),
(268,	985,	2,	'Custom site SEO URLs template',	NULL),
(269,	986,	2,	'Form SEO URLs template',	NULL),
(270,	976,	1,	'Anzahl der Produkte pro Queue-Request',	'Anzahl der Produkte, die je Request in den Queue geladen werden. Je größer die Zahl, desto länger dauern die Requests. Zu kleine Werte erhöhen den Overhead.'),
(271,	977,	1,	'Anzahl der Produkte pro Batch-Request',	'Anzahl der Produkte, die je Request verarbeitet werden. Je größer die Zahl, desto länger dauern die Requests. Zu kleine Werte erhöhen den Overhead.'),
(272,	978,	1,	'Rückgängig-Funktion aktivieren',	'Ermöglicht es, einzelne Mehrfach-Änderungen rückgängig zu machen. Diese Funktion ersetzt kein Backup.'),
(273,	979,	1,	'Automatische Cache-Invalidierung aktivieren',	'Invalidiert den Cache für jedes Produkt, das geändert wird. Bei vielen Produkten kann sich das negativ auf die Dauer des Vorgangs auswirken. Es wird daher empfohlen, den Cache nach Ende des Vorgangs manuell zu leeren.'),
(274,	992,	2,	'Product layout',	'Product layout allows you to control how your products are presented on the search result page. Choose between three different layouts to fine-tune your product display.'),
(275,	993,	2,	'Do not show on sale products that are out of stock ',	'If inactive, the listing may take longer to load if the split variant filtering is used. This effect does not occur when using ElasticSearch.'),
(276,	994,	2,	'Email header plaintext',	NULL),
(277,	995,	2,	'Email footer plaintext',	NULL),
(278,	996,	2,	'Email header HTML',	NULL),
(279,	997,	2,	'Email footer HTML',	NULL),
(280,	998,	2,	'Show instant downloads in account',	'Instant downloads can already be downloaded from the order details page.'),
(281,	999,	2,	'Run \'First run wizard\' on next backend execution',	''),
(282,	1000,	2,	'Show checkbox for the right of revocations for ESD products',	NULL),
(283,	1001,	2,	'Product free text field for service products',	NULL),
(284,	1002,	2,	'Use prev/next-tag on paginated sites',	'If active, use prev/next-tag instead of the Canoncial-tag on paginated sites'),
(285,	1003,	2,	'Thumbnail noise filter',	'Produces clearer thumbnails. May increase thumbnail generation time.'),
(286,	1005,	2,	'Display related articles on \"Article not found\" page',	'If enabled, \"Article not found\" page will display related articles suggestions. Disable to use the standard \"Page not found\" page'),
(287,	1006,	2,	'Show zip code field before city field',	'Determines if the zip code field should be shown before or after the the city field. Only applicable for Shopware 5 themes'),
(288,	1008,	2,	'Generate mobile sitemap',	'If enabled, an additional sitemap.xml file will be generated with the site structure for mobile devices'),
(289,	1009,	2,	'Consider product minimum order quantity for cheapest price calculation',	NULL),
(290,	1010,	2,	'Consider product graduatation for cheapest price calculation',	NULL),
(291,	1013,	2,	'Use \"and\" search logic',	'The search will only return results that match all the search terms.'),
(292,	1014,	2,	'Always select payment method in checkout',	NULL),
(293,	1015,	2,	'Ajax timeout',	'Defines the max execution time for ExtJS ajax requests (in seconds)'),
(294,	1016,	2,	'Available salutations',	'Allows to configure the available shop salutations in frontend registration and account. Inserted keys are generated automatically as snippet inside the frontend/salutation namespace.'),
(295,	1017,	2,	'Show title field',	NULL),
(296,	1021,	2,	'Send confirmation email after registration',	NULL),
(297,	1022,	2,	'Maximum number of items per page',	NULL),
(298,	1023,	2,	'Use strip_tags globally',	'When activated, each form input in the frontend is filtered using strip_tags.'),
(299,	1025,	2,	'Captcha Method',	'Choose the method to protect the forms against spam bots.'),
(300,	1026,	2,	'Disable after login',	'If set to yes, captchas are disabled for logged in customers'),
(301,	1027,	2,	'Display buy button in listing',	''),
(302,	1028,	2,	'Show cookie hint',	'If this option is active, a notification message will be displayed informing the user of the cookie guidelines. The content can be edited via the text editor module.'),
(303,	1029,	2,	'Link to the data privacy statement for cookies',	NULL),
(305,	991,	2,	'Default category sorting',	NULL),
(306,	1032,	2,	'Available sortings',	NULL),
(307,	1033,	2,	'Available filter',	NULL),
(310,	1036,	2,	'Automatically expand backend menu entries',	'The behavior of the buttons in the upper menu in the backend changes with this option. If this option is set to No, the menu entries must be opened manually by a mouse click. (backend cache needs to be cleared and the backend must be reloaded)'),
(311,	1037,	2,	'Notification position',	'With this option the backend notifications can be displayed at different positions (backend cache needs to be cleared and the backend must be reloaded)'),
(312,	1038,	2,	'Alternative email address for errors',	'If this field is empty, the shop owners email address will be used'),
(313,	1035,	2,	'Create Shopware Login Cookie',	'A cookie is stored, where the user can be identified again. This cookie is only used for setting the current customer group and the active Customer Streams'),
(314,	1039,	2,	'Manufacturer page product layout',	''),
(315,	1040,	2,	'Log level',	'Here you can choose the minimum log level for sending an e-mail. The default is \"Warning\". To focus on actual errors, you can increase the log level for example to \"Error\" or higher.'),
(316,	1034,	2,	'Use captcha for newsletter',	'The selected captcha method is used in the newsletter registration in the frontend.'),
(317,	1041,	2,	'Data protection information will be shown',	'Affects the registration, blog & product comments, newsletters and the product notification plugin form, but also your own forms'),
(318,	1042,	2,	'Delete accountless customers without orders after x months',	'The cronjob \"Guest customer cleanup\" must be active'),
(319,	1043,	2,	'Delete canceled orders after x months',	'The cronjob \"Cancelled baskets cleanup\" must be active'),
(320,	1044,	2,	'Anonymize customer IPs',	'Removes the last two blocks of IPv4 and three blocks of IPv6 addresses in statistics and orders to comply with privacy laws.'),
(321,	1045,	2,	'Double opt in for registrations',	NULL),
(322,	1046,	2,	'Days without confirmation until deletion',	'For Double-Opt-In: Time after which unconfirmed actions are deleted.'),
(323,	1047,	2,	'Double opt in for quick orderer',	NULL),
(324,	1048,	2,	'Cookie notice mode',	NULL),
(325,	1031,	2,	'Use captcha in registration',	'If active, a captcha will be shown in the registration. The recommended method for registrations is honeypot.'),
(326,	1050,	2,	'Proportional calculation of tax positions',	NULL),
(327,	1051,	2,	'Output href-lang in the meta tags',	'If active, all languages of a page are displayed in the the meta tags'),
(328,	1052,	2,	'Use language and country in href-lang',	'If this option is activated, the country is output in addition to the language, e.g. \"en-GB\" instead of \"en\"');

DROP TABLE IF EXISTS `s_core_config_forms`;
CREATE TABLE `s_core_config_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  `plugin_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `plugin_id` (`plugin_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=278 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `plugin_id`) VALUES
(77,	NULL,	'Base',	'Shopeinstellungen',	NULL,	0,	NULL),
(78,	NULL,	'Core',	'System',	NULL,	10,	NULL),
(79,	NULL,	'Product',	'Artikel',	NULL,	20,	NULL),
(80,	NULL,	'Frontend',	'Storefront',	NULL,	30,	NULL),
(82,	NULL,	'Interface',	'Schnittstellen',	NULL,	50,	NULL),
(83,	NULL,	'Payment',	'Zahlungsarten',	NULL,	60,	NULL),
(84,	79,	'Product29',	'Artikelnummern',	NULL,	1,	NULL),
(86,	79,	'Product35',	'Sonstige MwSt.-Sätze',	NULL,	4,	NULL),
(87,	79,	'PriceGroup',	'Preisgruppen',	NULL,	5,	NULL),
(88,	79,	'Unit',	'Preiseinheiten',	NULL,	6,	NULL),
(90,	80,	'Rating',	'Artikelbewertungen',	NULL,	8,	NULL),
(92,	NULL,	'Other',	'Weitere Einstellungen',	NULL,	60,	NULL),
(102,	80,	'LastArticles',	'Artikelverlauf',	'',	0,	NULL),
(118,	77,	'Shop',	'Shops',	NULL,	0,	NULL),
(119,	77,	'MasterData',	'Stammdaten',	NULL,	10,	NULL),
(120,	77,	'Currency',	'Währungen',	NULL,	20,	NULL),
(121,	77,	'Locale',	'Lokalisierungen',	NULL,	30,	NULL),
(123,	77,	'Tax',	'Steuern',	NULL,	50,	NULL),
(124,	77,	'Mail',	'Mailer',	NULL,	60,	NULL),
(125,	77,	'Number',	'Nummernkreise',	NULL,	70,	NULL),
(126,	77,	'CustomerGroup',	'Kundengruppen',	NULL,	80,	NULL),
(128,	78,	'Service',	'Wartung',	NULL,	20,	NULL),
(133,	80,	'AdvancedMenu',	'Erweitertes Menü',	'',	0,	29),
(134,	80,	'Compare',	'Artikelvergleich',	NULL,	0,	NULL),
(144,	80,	'Frontend30',	'Kategorien / Listen',	NULL,	1,	NULL),
(145,	80,	'Frontend76',	'Topseller / Neuheiten',	NULL,	2,	NULL),
(146,	80,	'Frontend77',	'Cross-Selling / Ähnliche Art.',	NULL,	3,	NULL),
(147,	80,	'Frontend79',	'Warenkorb / Artikeldetails',	NULL,	5,	NULL),
(157,	80,	'Frontend33',	'Anmeldung / Registrierung',	NULL,	0,	NULL),
(173,	78,	'Statistics',	'Statistiken',	'',	0,	31),
(180,	77,	'Country',	'Länder',	NULL,	50,	NULL),
(189,	78,	'InputFilter',	'InputFilter',	'',	0,	35),
(190,	80,	'Search',	'Suche',	NULL,	4,	NULL),
(191,	80,	'Frontend71',	'Rabatte / Zuschläge',	NULL,	5,	NULL),
(192,	80,	'Frontend60',	'E-Mail-Einstellungen',	NULL,	10,	NULL),
(247,	80,	'Frontend93',	'Versandkosten-Modul',	NULL,	11,	NULL),
(249,	80,	'Frontend100',	'SEO/Router-Einstellungen',	NULL,	12,	NULL),
(251,	77,	'CountryArea',	'Länder-Zonen',	NULL,	51,	NULL),
(253,	79,	'Esd',	'ESD',	NULL,	0,	NULL),
(255,	80,	'Recommendation',	'Artikelempfehlungen',	NULL,	8,	NULL),
(256,	80,	'Checkout',	'Bestellabschluss',	NULL,	0,	NULL),
(257,	77,	'PageGroup',	'Shopseiten-Gruppen',	NULL,	90,	NULL),
(258,	78,	'CronJob',	'Cronjobs',	NULL,	50,	NULL),
(259,	78,	'Auth',	'Backend',	'',	0,	36),
(261,	77,	'Document',	'PDF-Belegerstellung',	NULL,	90,	NULL),
(263,	92,	'Newsletter',	'Newsletter',	NULL,	0,	NULL),
(264,	92,	'LegacyOptions',	'Abwärtskompatibilität',	NULL,	0,	NULL),
(265,	78,	'Passwörter',	'Passwörter',	NULL,	0,	49),
(266,	78,	'HttpCache',	'Frontend cache (HTTP cache)',	NULL,	0,	52),
(267,	80,	'SEPA',	'SEPA-Konfiguration',	NULL,	0,	NULL),
(268,	78,	'Log',	'Log',	NULL,	0,	2),
(269,	78,	'SwagUpdate',	'Shopware Auto Update',	NULL,	0,	55),
(270,	92,	'MultiEdit',	'Mehrfachänderung',	'',	0,	NULL),
(271,	80,	'Media',	'Medien',	NULL,	13,	NULL),
(272,	80,	'Sitemap',	'Sitemap',	NULL,	0,	NULL),
(273,	92,	'CoreLicense',	'Shopware-Lizenz',	NULL,	0,	NULL),
(274,	80,	'Captcha',	'Captcha',	NULL,	0,	NULL),
(276,	80,	'CustomSearch',	'Filter / Sortierung',	NULL,	0,	NULL),
(277,	92,	'Privacy',	'Datenschutz',	NULL,	0,	NULL);

DROP TABLE IF EXISTS `s_core_config_form_translations`;
CREATE TABLE `s_core_config_form_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(11) unsigned NOT NULL,
  `locale_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_config_form_translations` (`id`, `form_id`, `locale_id`, `label`, `description`) VALUES
(1,	77,	2,	'Shop settings',	NULL),
(2,	78,	2,	'System',	NULL),
(3,	79,	2,	'Items',	NULL),
(4,	80,	2,	'Frontend',	NULL),
(5,	82,	2,	'Interfaces',	NULL),
(6,	83,	2,	'Payment methods',	NULL),
(7,	84,	2,	'Item numbers',	NULL),
(8,	86,	2,	'Other VAT rates',	NULL),
(9,	87,	2,	'Price groups',	NULL),
(10,	88,	2,	'Price units',	NULL),
(12,	90,	2,	'Customer reviews',	NULL),
(13,	92,	2,	'Additional settings',	NULL),
(14,	102,	2,	'Recently viewed items',	NULL),
(15,	118,	2,	'Shops',	NULL),
(16,	119,	2,	'Basic information',	NULL),
(17,	120,	2,	'Currencies',	NULL),
(18,	121,	2,	'Localizations',	NULL),
(19,	122,	2,	'Templates',	NULL),
(20,	123,	2,	'Taxes',	NULL),
(21,	124,	2,	'Mailers',	NULL),
(22,	125,	2,	'Number ranges',	NULL),
(23,	126,	2,	'Customer groups',	NULL),
(24,	127,	2,	'Caching',	NULL),
(25,	128,	2,	'Service',	NULL),
(26,	133,	2,	'Advanced menu',	NULL),
(27,	134,	2,	'Item comparison',	NULL),
(28,	135,	2,	'Tag cloud',	NULL),
(29,	144,	2,	'Categories / lists',	NULL),
(30,	145,	2,	'Top seller / novelties',	NULL),
(31,	146,	2,	'Cross selling / item details',	NULL),
(32,	147,	2,	'Shopping cart / item details',	NULL),
(33,	157,	2,	'Login / registration',	NULL),
(34,	173,	2,	'Statstics',	NULL),
(35,	174,	2,	'Google Analytics',	NULL),
(36,	175,	2,	'HttpCache',	NULL),
(37,	176,	2,	'Log',	NULL),
(38,	177,	2,	'Debug',	NULL),
(39,	180,	2,	'Countries',	NULL),
(40,	189,	2,	'Input filter',	NULL),
(41,	190,	2,	'Search',	NULL),
(42,	191,	2,	'Discounts / surcharges',	NULL),
(43,	192,	2,	'Email settings',	NULL),
(44,	247,	2,	'Shipping costs module',	NULL),
(46,	249,	2,	'SEO / router settings',	NULL),
(48,	251,	2,	'Country areas',	NULL),
(50,	253,	2,	'ESD',	NULL),
(51,	255,	2,	'Item recommendations',	NULL),
(52,	256,	2,	'Checkout',	NULL),
(53,	257,	2,	'Shop page groups',	NULL),
(54,	258,	2,	'Cronjobs',	NULL),
(55,	259,	2,	'Backend',	NULL),
(56,	261,	2,	'PDF document creation',	NULL),
(57,	262,	2,	'Store API',	NULL),
(58,	264,	2,	'Legacy options',	NULL),
(59,	265,	2,	'Passwords',	NULL),
(61,	267,	2,	'SEPA configuration',	NULL),
(62,	271,	2,	'Media',	''),
(63,	270,	2,	'Multi edit',	NULL),
(64,	272,	2,	'Sitemap',	NULL),
(65,	273,	2,	'Shopware license',	NULL),
(67,	276,	2,	'Filter / Sorting',	NULL),
(68,	277,	2,	'Privacy',	NULL);

DROP TABLE IF EXISTS `s_core_config_mails`;
CREATE TABLE `s_core_config_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stateId` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `frommail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `fromname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `contentHTML` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `ishtml` int(1) NOT NULL,
  `attachment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `mailtype` int(11) NOT NULL DEFAULT '1',
  `context` longtext COLLATE utf8_unicode_ci,
  `dirty` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `stateId` (`stateId`),
  CONSTRAINT `s_core_config_mails_ibfk_1` FOREIGN KEY (`stateId`) REFERENCES `s_core_states` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_config_mails` (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`, `dirty`) VALUES
(1,	NULL,	'sREGISTERCONFIRMATION',	'{config name=mail}',	'{config name=shopName}',	'Ihre Anmeldung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n        \nHallo {$salutation|salutation} {$lastname},\n\nvielen Dank für Ihre Anmeldung in unserem Shop.\nSie erhalten Zugriff über Ihre E-Mail-Adresse {$sMAIL} und dem von Ihnen gewählten Kennwort.\nSie können Ihr Kennwort jederzeit nachträglich ändern.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n            {include file=\"string:{config name=emailheaderhtml}\"}\n            <br/><br/>\n            <p>\n                Hallo {$salutation|salutation} {$lastname},<br/>\n                <br/>\n                vielen Dank für Ihre Anmeldung in unserem Shop.<br/>\n                Sie erhalten Zugriff über Ihre E-Mail-Adresse <strong>{$sMAIL}</strong> und dem von Ihnen gewählten Kennwort.<br/>\n                Sie können Ihr Kennwort jederzeit nachträglich ändern.\n            </p>\n            {include file=\"string:{config name=emailfooterhtml}\"}\n        </div>',	1,	'',	2,	'a:14:{s:5:\"sMAIL\";s:14:\"xy@example.org\";s:7:\"sConfig\";a:0:{}s:6:\"street\";s:15:\"Musterstraße 1\";s:7:\"zipcode\";s:5:\"12345\";s:4:\"city\";s:11:\"Musterstadt\";s:7:\"country\";s:1:\"2\";s:5:\"state\";N;s:13:\"customer_type\";s:7:\"private\";s:10:\"salutation\";s:4:\"Herr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:11:\"accountmode\";s:1:\"0\";s:5:\"email\";s:14:\"xy@example.org\";s:10:\"additional\";a:1:{s:13:\"customer_type\";s:7:\"private\";}}',	0),
(2,	NULL,	'sORDER',	'{config name=mail}',	'{config name=shopName}',	'Ihre Bestellung im {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n        \nHallo {$billingaddress.salutation|salutation} {$billingaddress.lastname},\n\nvielen Dank für Ihre Bestellung im {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\nInformationen zu Ihrer Bestellung:\n\nPos.  Art.Nr.               Beschreibung                                      Menge       Preis       Summe\n{foreach item=details key=position from=$sOrderDetails}\n{{$position+1}|fill:4}  {$details.ordernumber|fill:20}  {$details.articlename|fill:49}  {$details.quantity|fill:6}  {$details.price|padding:8|currency|unescape:\"htmlall\"}      {$details.amount|padding:8|currency|unescape:\"htmlall\"}\n{/foreach}\n\nVersandkosten: {$sShippingCosts|currency|unescape:\"htmlall\"}\nGesamtkosten Netto: {$sAmountNet|currency|unescape:\"htmlall\"}\n{if !$sNet}\n{foreach $sTaxRates as $rate => $value}\nzzgl. {$rate|number_format:0}% MwSt. {$value|currency|unescape:\"htmlall\"}\n{/foreach}\nGesamtkosten Brutto: {$sAmount|currency|unescape:\"htmlall\"}\n{/if}\n\nGewählte Zahlungsart: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == \"debit\"}\nIhre Bankverbindung:\nKontonr: {$sPaymentTable.account}\nBLZ: {$sPaymentTable.bankcode}\nInstitut: {$sPaymentTable.bankname}\nKontoinhaber: {$sPaymentTable.bankholder}\n\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\n{/if}\n{if $additional.payment.name == \"prepayment\"}\n\nUnsere Bankverbindung:\nKonto: ###\nBLZ: ###\n{/if}\n\n\nGewählte Versandart: {$sDispatch.name}\n{$sDispatch.description}\n\n{if $sComment}\nIhr Kommentar:\n{$sComment}\n{/if}\n\nRechnungsadresse:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street} {$billingaddress.streetnumber}\n{if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}\n\n{$additional.country.countryname}\n\nLieferadresse:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street} {$shippingaddress.streetnumber}\n{if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}\n\n{$additional.countryShipping.countryname}\n\n{if $billingaddress.ustid}\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\n{/if}\n\n\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n            {include file=\"string:{config name=emailheaderhtml}\"}\n            <br/><br/>\n            <p>Hallo {$billingaddress.salutation|salutation} {$billingaddress.lastname},<br/>\n                <br/>\n                vielen Dank für Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.<br/>\n                <br/>\n                <strong>Informationen zu Ihrer Bestellung:</strong></p><br/>\n            <table width=\"80%\" border=\"0\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:12px;\">\n                <tr>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Pos.</strong></td>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Artikel</strong></td>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\">Bezeichnung</td>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Menge</strong></td>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Preis</strong></td>\n                    <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Summe</strong></td>\n                </tr>\n\n                {foreach item=details key=position from=$sOrderDetails}\n                <tr>\n                    <td style=\"border-bottom:1px solid #cccccc;\">{$position+1|fill:4} </td>\n                    <td style=\"border-bottom:1px solid #cccccc;\">{if $details.image.src.0 && $details.modus == 0}<img style=\"height: 57px;\" height=\"57\" src=\"{$details.image.src.0}\" alt=\"{$details.articlename}\" />{else} {/if}</td>\n                    <td style=\"border-bottom:1px solid #cccccc;\">\n                      {$details.articlename|wordwrap:80|indent:4}<br>\n                      Artikel-Nr: {$details.ordernumber|fill:20}\n                    </td>\n                    <td style=\"border-bottom:1px solid #cccccc;\">{$details.quantity|fill:6}</td>\n                    <td style=\"border-bottom:1px solid #cccccc;\">{$details.price|padding:8|currency}</td>\n                    <td style=\"border-bottom:1px solid #cccccc;\">{$details.amount|padding:8|currency}</td>\n                </tr>\n                {/foreach}\n\n            </table>\n        \n            <p>\n                <br/>\n                <br/>\n                Versandkosten: {$sShippingCosts|currency}<br/>\n                Gesamtkosten Netto: {$sAmountNet|currency}<br/>\n                {if !$sNet}\n                {foreach $sTaxRates as $rate => $value}\n                zzgl. {$rate|number_format:0}% MwSt. {$value|currency}<br/>\n                {/foreach}\n                <strong>Gesamtkosten Brutto: {$sAmount|currency}</strong><br/>\n                {/if}\n                <br/>\n                <br/>\n                <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>\n                {$additional.payment.additionaldescription}\n                {if $additional.payment.name == \"debit\"}\n                Ihre Bankverbindung:<br/>\n                Kontonr: {$sPaymentTable.account}<br/>\n                BLZ: {$sPaymentTable.bankcode}<br/>\n                Institut: {$sPaymentTable.bankname}<br/>\n                Kontoinhaber: {$sPaymentTable.bankholder}<br/>\n                <br/>\n                Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>\n                {/if}\n                <br/>\n                <br/>\n                {if $additional.payment.name == \"prepayment\"}\n                Unsere Bankverbindung:<br/>\n                Konto: ###<br/>\n                BLZ: ###<br/>\n                {/if}\n                <br/>\n                <br/>\n                <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>\n                {$sDispatch.description}<br/>\n            </p>\n            <p>\n                {if $sComment}\n                <strong>Ihr Kommentar:</strong><br/>\n                {$sComment}<br/>\n                {/if}\n                <br/>\n                <br/>\n                <strong>Rechnungsadresse:</strong><br/>\n                {$billingaddress.company}<br/>\n                {$billingaddress.firstname} {$billingaddress.lastname}<br/>\n                {$billingaddress.street} {$billingaddress.streetnumber}<br/>\n                {if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}<br/>\n                {$additional.country.countryname}<br/>\n                <br/>\n                <br/>\n                <strong>Lieferadresse:</strong><br/>\n                {$shippingaddress.company}<br/>\n                {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\n                {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\n                {if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}<br/>\n                {$additional.countryShipping.countryname}<br/>\n                <br/>\n                {if $billingaddress.ustid}\n                Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\n                Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>\n                bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\n                {/if}\n                <br/>\n                <br/>\n                Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.<br/>\n                {include file=\"string:{config name=emailfooterhtml}\"}\n            </p>\n        </div>',	1,	'',	2,	'a:22:{s:13:\"sOrderDetails\";a:2:{i:0;a:54:{s:2:\"id\";s:3:\"670\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:6:\"userID\";s:1:\"0\";s:11:\"articlename\";s:11:\"ELASTIC CAP\";s:9:\"articleID\";s:3:\"152\";s:11:\"ordernumber\";s:7:\"SW10153\";s:12:\"shippingfree\";s:1:\"0\";s:8:\"quantity\";s:1:\"1\";s:5:\"price\";s:5:\"29,95\";s:8:\"netprice\";s:15:\"25.168067226891\";s:8:\"tax_rate\";s:2:\"19\";s:5:\"datum\";s:19:\"2017-08-07 14:09:12\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:12:\"lastviewport\";s:8:\"register\";s:9:\"useragent\";s:76:\"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0\";s:6:\"config\";s:0:\"\";s:14:\"currencyFactor\";s:1:\"1\";s:8:\"packunit\";s:0:\"\";s:12:\"mainDetailId\";s:3:\"707\";s:15:\"articleDetailId\";s:3:\"708\";s:11:\"minpurchase\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:7:\"instock\";s:2:\"12\";s:14:\"suppliernumber\";s:0:\"\";s:11:\"maxpurchase\";s:3:\"100\";s:13:\"purchasesteps\";i:1;s:12:\"purchaseunit\";N;s:9:\"laststock\";s:1:\"0\";s:12:\"shippingtime\";s:0:\"\";s:11:\"releasedate\";N;s:12:\"sReleaseDate\";N;s:3:\"ean\";s:0:\"\";s:8:\"stockmin\";s:1:\"0\";s:8:\"ob_attr1\";s:0:\"\";s:8:\"ob_attr2\";N;s:8:\"ob_attr3\";N;s:8:\"ob_attr4\";N;s:8:\"ob_attr5\";N;s:8:\"ob_attr6\";N;s:12:\"shippinginfo\";b:1;s:3:\"esd\";s:1:\"0\";s:18:\"additional_details\";a:94:{s:9:\"articleID\";i:152;s:16:\"articleDetailsID\";i:708;s:11:\"ordernumber\";s:9:\"SW10152.1\";s:9:\"highlight\";b:0;s:11:\"description\";s:0:\"\";s:16:\"description_long\";s:2404:\"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p><p>Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue.</p>  <p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p> <p>Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla.</p> <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede. Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi.</p>\";s:3:\"esd\";b:0;s:11:\"articleName\";s:23:\"WINDSTOPPER MÜTZE WARM\";s:5:\"taxID\";i:1;s:3:\"tax\";i:19;s:7:\"instock\";i:12;s:11:\"isAvailable\";b:1;s:6:\"weight\";i:0;s:12:\"shippingtime\";N;s:16:\"pricegroupActive\";b:0;s:12:\"pricegroupID\";N;s:6:\"length\";i:0;s:6:\"height\";i:0;s:5:\"width\";i:0;s:9:\"laststock\";b:0;s:14:\"additionaltext\";s:0:\"\";s:5:\"datum\";s:10:\"2015-02-05\";s:5:\"sales\";i:0;s:13:\"filtergroupID\";i:8;s:17:\"priceStartingFrom\";N;s:18:\"pseudopricePercent\";N;s:15:\"sVariantArticle\";N;s:13:\"sConfigurator\";b:1;s:9:\"metaTitle\";s:0:\"\";s:12:\"shippingfree\";b:0;s:14:\"suppliernumber\";s:0:\"\";s:12:\"notification\";b:0;s:3:\"ean\";s:0:\"\";s:8:\"keywords\";s:0:\"\";s:12:\"sReleasedate\";s:0:\"\";s:8:\"template\";s:0:\"\";s:10:\"attributes\";a:2:{s:4:\"core\";a:23:{s:2:\"id\";s:3:\"720\";s:9:\"articleID\";s:3:\"152\";s:16:\"articledetailsID\";s:3:\"708\";s:5:\"attr1\";s:0:\"\";s:5:\"attr2\";s:0:\"\";s:5:\"attr3\";s:0:\"\";s:5:\"attr4\";s:0:\"\";s:5:\"attr5\";s:0:\"\";s:5:\"attr6\";s:0:\"\";s:5:\"attr7\";s:0:\"\";s:5:\"attr8\";s:0:\"\";s:5:\"attr9\";s:0:\"\";s:6:\"attr10\";s:0:\"\";s:6:\"attr11\";s:0:\"\";s:6:\"attr12\";s:0:\"\";s:6:\"attr13\";s:0:\"\";s:6:\"attr14\";s:0:\"\";s:6:\"attr15\";s:0:\"\";s:6:\"attr16\";s:0:\"\";s:6:\"attr17\";N;s:6:\"attr18\";s:0:\"\";s:6:\"attr19\";s:0:\"\";s:6:\"attr20\";s:0:\"\";}s:9:\"marketing\";a:4:{s:5:\"isNew\";b:0;s:11:\"isTopSeller\";b:0;s:10:\"comingSoon\";b:0;s:7:\"storage\";a:0:{}}}s:17:\"allowBuyInListing\";b:0;s:5:\"attr1\";s:0:\"\";s:5:\"attr2\";s:0:\"\";s:5:\"attr3\";s:0:\"\";s:5:\"attr4\";s:0:\"\";s:5:\"attr5\";s:0:\"\";s:5:\"attr6\";s:0:\"\";s:5:\"attr7\";s:0:\"\";s:5:\"attr8\";s:0:\"\";s:5:\"attr9\";s:0:\"\";s:6:\"attr10\";s:0:\"\";s:6:\"attr11\";s:0:\"\";s:6:\"attr12\";s:0:\"\";s:6:\"attr13\";s:0:\"\";s:6:\"attr14\";s:0:\"\";s:6:\"attr15\";s:0:\"\";s:6:\"attr16\";s:0:\"\";s:6:\"attr17\";N;s:6:\"attr18\";s:0:\"\";s:6:\"attr19\";s:0:\"\";s:6:\"attr20\";s:0:\"\";s:12:\"supplierName\";s:8:\"LÖFFLER\";s:11:\"supplierImg\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:10:\"supplierID\";i:10;s:19:\"supplierDescription\";s:1267:\"<p>L&Ouml;FFLER ist anders. Denn anders als die meisten Mitbewerber hat sich L&Ouml;FFLER schon Anfang der 1990er Jahre entschieden, auch weiterhin in &Ouml;sterreich zu produzieren. Nat&uuml;rlich nach h&ouml;chsten ethischen und &ouml;kologischen Standards, wie sie nur in &Ouml;sterreich bzw. in der Europ&auml;ischen Union gelten. Mit gut ausgebildeten, kompetenten und motivierten Mitarbeiterinnen und Mitarbeitern.</p>  <p>Viele Sportswear-Konzerne haben im Streben nach h&ouml;chsten Gewinnmargen ihre Fertigung l&auml;ngst in Billiglohnl&auml;nder verlagert. Miserable Arbeitsbedingungen, Hungerl&ouml;hne und Kinderarbeit sind dort immer wieder an der Tagesordnung. H&ouml;chst fragw&uuml;rdig sind auch die Umweltzerst&ouml;rung durch r&uuml;cksichtslose Produktionsmethoden und die hohe Schadstoffbelastung der auf diese Weise hergestellten Textilien.</p> <p>70 Prozent aller Stoffe, die L&Ouml;FFLER verarbeitet, kommen aus der eigenen Strickerei in Ried im Innkreis. Das ist einzigartig - und eine wichtige Grundlage f&uuml;r die herausragende Qualit&auml;t, die Fair Sportswear von L&Ouml;FFLER auszeichnet.</p> <p>Weitere Informationen zu dem Hersteller finden Sie <a title=\"www.loeffler.at\" href=\"http://www.loeffler.at/\" target=\"_blank\">hier</a>.</p>\";s:19:\"supplier_attributes\";a:0:{}s:10:\"newArticle\";b:0;s:9:\"sUpcoming\";b:0;s:9:\"topseller\";b:0;s:7:\"valFrom\";i:1;s:5:\"valTo\";N;s:4:\"from\";i:1;s:2:\"to\";N;s:5:\"price\";s:5:\"29,95\";s:11:\"pseudoprice\";s:1:\"0\";s:14:\"referenceprice\";N;s:15:\"has_pseudoprice\";b:0;s:13:\"price_numeric\";d:29.949999999999999;s:19:\"pseudoprice_numeric\";i:0;s:16:\"price_attributes\";a:0:{}s:10:\"pricegroup\";s:2:\"EK\";s:11:\"minpurchase\";i:1;s:11:\"maxpurchase\";s:3:\"100\";s:13:\"purchasesteps\";i:1;s:12:\"purchaseunit\";N;s:13:\"referenceunit\";N;s:8:\"packunit\";s:0:\"\";s:6:\"unitID\";N;s:5:\"sUnit\";a:2:{s:4:\"unit\";N;s:11:\"description\";N;}s:15:\"unit_attributes\";a:0:{}s:5:\"image\";a:12:{s:2:\"id\";i:366;s:8:\"position\";N;s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:11:\"description\";s:0:\"\";s:9:\"extension\";s:3:\"jpg\";s:4:\"main\";b:0;s:8:\"parentId\";N;s:5:\"width\";i:1492;s:6:\"height\";i:1500;s:10:\"thumbnails\";a:3:{i:0;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:3:\"200\";s:9:\"maxHeight\";s:3:\"200\";s:10:\"attributes\";a:0:{}}i:1;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:3:\"600\";s:9:\"maxHeight\";s:3:\"600\";s:10:\"attributes\";a:0:{}}i:2;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:4:\"1280\";s:9:\"maxHeight\";s:4:\"1280\";s:10:\"attributes\";a:0:{}}}s:10:\"attributes\";a:0:{}s:9:\"attribute\";a:0:{}}s:6:\"prices\";a:1:{i:0;a:22:{s:7:\"valFrom\";i:1;s:5:\"valTo\";N;s:4:\"from\";i:1;s:2:\"to\";N;s:5:\"price\";s:5:\"29,95\";s:11:\"pseudoprice\";s:1:\"0\";s:14:\"referenceprice\";s:1:\"0\";s:18:\"pseudopricePercent\";N;s:15:\"has_pseudoprice\";b:0;s:13:\"price_numeric\";d:29.949999999999999;s:19:\"pseudoprice_numeric\";i:0;s:16:\"price_attributes\";a:0:{}s:10:\"pricegroup\";s:2:\"EK\";s:11:\"minpurchase\";i:1;s:11:\"maxpurchase\";s:3:\"100\";s:13:\"purchasesteps\";i:1;s:12:\"purchaseunit\";N;s:13:\"referenceunit\";N;s:8:\"packunit\";s:0:\"\";s:6:\"unitID\";N;s:5:\"sUnit\";a:2:{s:4:\"unit\";N;s:11:\"description\";N;}s:15:\"unit_attributes\";a:0:{}}}s:10:\"linkBasket\";s:42:\"shopware.php?sViewport=basket&sAdd=SW10153\";s:11:\"linkDetails\";s:42:\"shopware.php?sViewport=detail&sArticle=152\";s:11:\"linkVariant\";s:57:\"shopware.php?sViewport=detail&sArticle=152&number=SW10153\";s:11:\"sProperties\";a:3:{i:1;a:11:{s:2:\"id\";i:1;s:8:\"optionID\";i:1;s:4:\"name\";s:10:\"Artikeltyp\";s:7:\"groupID\";i:8;s:9:\"groupName\";s:7:\"Fashion\";s:5:\"value\";s:16:\"Bildkonfigurator\";s:6:\"values\";a:1:{i:4;s:16:\"Bildkonfigurator\";}s:12:\"isFilterable\";b:1;s:7:\"options\";a:1:{i:0;a:3:{s:2:\"id\";i:4;s:4:\"name\";s:16:\"Bildkonfigurator\";s:10:\"attributes\";a:0:{}}}s:5:\"media\";a:0:{}s:10:\"attributes\";a:0:{}}i:3;a:11:{s:2:\"id\";i:3;s:8:\"optionID\";i:3;s:4:\"name\";s:8:\"Material\";s:7:\"groupID\";i:8;s:9:\"groupName\";s:7:\"Fashion\";s:5:\"value\";s:20:\"Polyester, Baumwolle\";s:6:\"values\";a:2:{i:108;s:9:\"Polyester\";i:163;s:9:\"Baumwolle\";}s:12:\"isFilterable\";b:1;s:7:\"options\";a:2:{i:0;a:3:{s:2:\"id\";i:108;s:4:\"name\";s:9:\"Polyester\";s:10:\"attributes\";a:0:{}}i:1;a:3:{s:2:\"id\";i:163;s:4:\"name\";s:9:\"Baumwolle\";s:10:\"attributes\";a:0:{}}}s:5:\"media\";a:0:{}s:10:\"attributes\";a:0:{}}i:18;a:11:{s:2:\"id\";i:18;s:8:\"optionID\";i:18;s:4:\"name\";s:5:\"Farbe\";s:7:\"groupID\";i:8;s:9:\"groupName\";s:7:\"Fashion\";s:5:\"value\";s:12:\"Rot, Schwarz\";s:6:\"values\";a:2:{i:166;s:3:\"Rot\";i:155;s:7:\"Schwarz\";}s:12:\"isFilterable\";b:1;s:7:\"options\";a:2:{i:0;a:3:{s:2:\"id\";i:166;s:4:\"name\";s:3:\"Rot\";s:10:\"attributes\";a:0:{}}i:1;a:3:{s:2:\"id\";i:155;s:4:\"name\";s:7:\"Schwarz\";s:10:\"attributes\";a:0:{}}}s:5:\"media\";a:2:{i:166;a:13:{s:7:\"valueId\";i:166;s:2:\"id\";i:355;s:8:\"position\";N;s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:11:\"description\";s:3:\"rot\";s:9:\"extension\";s:3:\"jpg\";s:4:\"main\";N;s:8:\"parentId\";N;s:5:\"width\";i:40;s:6:\"height\";i:40;s:10:\"thumbnails\";a:0:{}s:10:\"attributes\";a:0:{}s:9:\"attribute\";a:0:{}}i:155;a:13:{s:7:\"valueId\";i:155;s:2:\"id\";i:357;s:8:\"position\";N;s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:11:\"description\";s:7:\"schwarz\";s:9:\"extension\";s:3:\"jpg\";s:4:\"main\";N;s:8:\"parentId\";N;s:5:\"width\";i:40;s:6:\"height\";i:40;s:10:\"thumbnails\";a:0:{}s:10:\"attributes\";a:0:{}s:9:\"attribute\";a:0:{}}}s:10:\"attributes\";a:0:{}}}s:10:\"properties\";s:106:\"Artikeltyp:&nbsp;Bildkonfigurator,&nbsp;Material:&nbsp;Polyester, Baumwolle,&nbsp;Farbe:&nbsp;Rot, Schwarz\";}s:6:\"amount\";s:5:\"29,95\";s:9:\"amountnet\";s:5:\"25,17\";s:12:\"priceNumeric\";s:5:\"29.95\";s:5:\"image\";a:15:{s:2:\"id\";i:366;s:8:\"position\";N;s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:11:\"description\";s:0:\"\";s:9:\"extension\";s:3:\"jpg\";s:4:\"main\";b:0;s:8:\"parentId\";N;s:5:\"width\";i:1492;s:6:\"height\";i:1500;s:10:\"thumbnails\";a:3:{i:0;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:3:\"200\";s:9:\"maxHeight\";s:3:\"200\";s:10:\"attributes\";a:0:{}}i:1;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:3:\"600\";s:9:\"maxHeight\";s:3:\"600\";s:10:\"attributes\";a:0:{}}i:2;a:6:{s:6:\"source\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:12:\"retinaSource\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:9:\"sourceSet\";s:141:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x\";s:8:\"maxWidth\";s:4:\"1280\";s:9:\"maxHeight\";s:4:\"1280\";s:10:\"attributes\";a:0:{}}}s:10:\"attributes\";a:0:{}s:9:\"attribute\";a:0:{}s:3:\"src\";a:4:{s:8:\"original\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:0;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:1;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:2;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";}s:5:\"srchd\";a:4:{s:8:\"original\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:0;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:1;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";i:2;s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";}s:3:\"res\";a:1:{s:8:\"original\";a:2:{s:5:\"width\";i:1500;s:6:\"height\";i:1492;}}}s:11:\"linkDetails\";s:42:\"shopware.php?sViewport=detail&sArticle=152\";s:10:\"linkDelete\";s:41:\"shopware.php?sViewport=basket&sDelete=670\";s:8:\"linkNote\";s:40:\"shopware.php?sViewport=note&sAdd=SW10153\";s:3:\"tax\";s:4:\"4,78\";s:13:\"orderDetailId\";s:3:\"208\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:52:{s:2:\"id\";s:3:\"673\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:6:\"userID\";s:1:\"0\";s:11:\"articlename\";s:15:\"Warenkorbrabatt\";s:9:\"articleID\";s:1:\"0\";s:11:\"ordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:12:\"shippingfree\";s:1:\"0\";s:8:\"quantity\";s:1:\"1\";s:5:\"price\";s:5:\"-2,00\";s:8:\"netprice\";s:5:\"-1.68\";s:8:\"tax_rate\";s:2:\"19\";s:5:\"datum\";s:19:\"2017-08-07 14:09:20\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:12:\"lastviewport\";s:0:\"\";s:9:\"useragent\";s:0:\"\";s:6:\"config\";s:0:\"\";s:14:\"currencyFactor\";s:1:\"1\";s:8:\"packunit\";N;s:12:\"mainDetailId\";N;s:15:\"articleDetailId\";N;s:11:\"minpurchase\";i:1;s:5:\"taxID\";N;s:7:\"instock\";N;s:14:\"suppliernumber\";N;s:11:\"maxpurchase\";s:3:\"100\";s:13:\"purchasesteps\";i:1;s:12:\"purchaseunit\";N;s:9:\"laststock\";N;s:12:\"shippingtime\";N;s:11:\"releasedate\";N;s:12:\"sReleaseDate\";N;s:3:\"ean\";N;s:8:\"stockmin\";N;s:8:\"ob_attr1\";N;s:8:\"ob_attr2\";N;s:8:\"ob_attr3\";N;s:8:\"ob_attr4\";N;s:8:\"ob_attr5\";N;s:8:\"ob_attr6\";N;s:12:\"shippinginfo\";b:0;s:3:\"esd\";s:1:\"0\";s:6:\"amount\";s:5:\"-2,00\";s:9:\"amountnet\";s:5:\"-1,68\";s:12:\"priceNumeric\";s:2:\"-2\";s:11:\"linkDetails\";s:40:\"shopware.php?sViewport=detail&sArticle=0\";s:10:\"linkDelete\";s:41:\"shopware.php?sViewport=basket&sDelete=673\";s:8:\"linkNote\";s:49:\"shopware.php?sViewport=note&sAdd=SHIPPINGDISCOUNT\";s:3:\"tax\";s:5:\"-0,32\";s:13:\"orderDetailId\";s:3:\"209\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:14:\"billingaddress\";a:26:{s:2:\"id\";s:1:\"5\";s:7:\"company\";s:0:\"\";s:10:\"department\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:5:\"title\";s:0:\"\";s:8:\"lastname\";s:10:\"Mustermann\";s:6:\"street\";s:15:\"Musterstraße 1\";s:7:\"zipcode\";s:5:\"12345\";s:4:\"city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:5:\"vatId\";s:0:\"\";s:22:\"additionalAddressLine1\";s:0:\"\";s:22:\"additionalAddressLine2\";s:0:\"\";s:9:\"countryId\";s:1:\"2\";s:7:\"stateId\";s:0:\"\";s:8:\"customer\";N;s:7:\"country\";N;s:5:\"state\";s:0:\"\";s:6:\"userID\";s:1:\"3\";s:9:\"countryID\";s:1:\"2\";s:7:\"stateID\";s:0:\"\";s:5:\"ustid\";s:0:\"\";s:24:\"additional_address_line1\";s:0:\"\";s:24:\"additional_address_line2\";s:0:\"\";s:10:\"attributes\";N;}s:15:\"shippingaddress\";a:26:{s:2:\"id\";s:1:\"5\";s:7:\"company\";s:0:\"\";s:10:\"department\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:5:\"title\";s:0:\"\";s:8:\"lastname\";s:10:\"Mustermann\";s:6:\"street\";s:15:\"Musterstraße 1\";s:7:\"zipcode\";s:5:\"12345\";s:4:\"city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:5:\"vatId\";s:0:\"\";s:22:\"additionalAddressLine1\";s:0:\"\";s:22:\"additionalAddressLine2\";s:0:\"\";s:9:\"countryId\";s:1:\"2\";s:7:\"stateId\";s:0:\"\";s:8:\"customer\";N;s:7:\"country\";N;s:5:\"state\";s:0:\"\";s:6:\"userID\";s:1:\"3\";s:9:\"countryID\";s:1:\"2\";s:7:\"stateID\";s:0:\"\";s:5:\"ustid\";s:0:\"\";s:24:\"additional_address_line1\";s:0:\"\";s:24:\"additional_address_line2\";s:0:\"\";s:10:\"attributes\";N;}s:10:\"additional\";a:8:{s:7:\"country\";a:15:{s:2:\"id\";s:1:\"2\";s:11:\"countryname\";s:11:\"Deutschland\";s:10:\"countryiso\";s:2:\"DE\";s:6:\"areaID\";s:1:\"1\";s:9:\"countryen\";s:7:\"GERMANY\";s:8:\"position\";s:1:\"1\";s:6:\"notice\";s:0:\"\";s:7:\"taxfree\";s:1:\"0\";s:13:\"taxfree_ustid\";s:1:\"0\";s:21:\"taxfree_ustid_checked\";s:1:\"0\";s:6:\"active\";s:1:\"1\";s:4:\"iso3\";s:3:\"DEU\";s:29:\"display_state_in_registration\";s:1:\"0\";s:27:\"force_state_in_registration\";s:1:\"0\";s:11:\"countryarea\";s:11:\"deutschland\";}s:5:\"state\";a:0:{}s:4:\"user\";a:33:{s:2:\"id\";s:1:\"3\";s:6:\"userID\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:20\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";i:0;s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:14:\"customernumber\";s:5:\"20005\";s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";}s:15:\"countryShipping\";a:15:{s:2:\"id\";s:1:\"2\";s:11:\"countryname\";s:11:\"Deutschland\";s:10:\"countryiso\";s:2:\"DE\";s:6:\"areaID\";s:1:\"1\";s:9:\"countryen\";s:7:\"GERMANY\";s:8:\"position\";s:1:\"1\";s:6:\"notice\";s:0:\"\";s:7:\"taxfree\";s:1:\"0\";s:13:\"taxfree_ustid\";s:1:\"0\";s:21:\"taxfree_ustid_checked\";s:1:\"0\";s:6:\"active\";s:1:\"1\";s:4:\"iso3\";s:3:\"DEU\";s:29:\"display_state_in_registration\";s:1:\"0\";s:27:\"force_state_in_registration\";s:1:\"0\";s:11:\"countryarea\";s:11:\"deutschland\";}s:13:\"stateShipping\";a:0:{}s:7:\"payment\";a:21:{s:2:\"id\";s:1:\"5\";s:4:\"name\";s:10:\"prepayment\";s:11:\"description\";s:8:\"Vorkasse\";s:8:\"template\";s:14:\"prepayment.tpl\";s:5:\"class\";s:14:\"prepayment.php\";s:5:\"table\";s:0:\"\";s:4:\"hide\";s:1:\"0\";s:21:\"additionaldescription\";s:108:\"Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.\";s:13:\"debit_percent\";s:1:\"0\";s:9:\"surcharge\";s:1:\"0\";s:15:\"surchargestring\";s:0:\"\";s:8:\"position\";s:1:\"1\";s:6:\"active\";s:1:\"1\";s:9:\"esdactive\";s:1:\"0\";s:11:\"embediframe\";s:0:\"\";s:12:\"hideprospect\";s:1:\"0\";s:6:\"action\";N;s:8:\"pluginID\";N;s:6:\"source\";N;s:15:\"mobile_inactive\";s:1:\"0\";s:10:\"validation\";a:0:{}}s:10:\"charge_vat\";b:1;s:8:\"show_net\";b:1;}s:9:\"sTaxRates\";a:1:{s:5:\"19.00\";d:5.0800000000000001;}s:14:\"sShippingCosts\";s:8:\"3,90 EUR\";s:7:\"sAmount\";s:9:\"31,85 EUR\";s:14:\"sAmountNumeric\";d:31.850000000000001;s:10:\"sAmountNet\";s:9:\"26,77 EUR\";s:17:\"sAmountNetNumeric\";d:26.77;s:12:\"sOrderNumber\";i:20003;s:9:\"sOrderDay\";s:10:\"07.08.2017\";s:10:\"sOrderTime\";s:5:\"14:09\";s:8:\"sComment\";s:0:\"\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}s:9:\"sCurrency\";s:3:\"EUR\";s:9:\"sLanguage\";i:1;s:8:\"sSubShop\";i:1;s:4:\"sEsd\";N;s:4:\"sNet\";b:0;s:13:\"sPaymentTable\";a:0:{}s:9:\"sDispatch\";a:10:{s:2:\"id\";s:1:\"9\";s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";s:11:\"calculation\";s:1:\"1\";s:11:\"status_link\";s:0:\"\";s:21:\"surcharge_calculation\";s:1:\"3\";s:17:\"bind_shippingfree\";s:1:\"0\";s:12:\"shippingfree\";N;s:15:\"tax_calculation\";s:1:\"0\";s:21:\"tax_calculation_value\";N;}}',	0),
(3,	NULL,	'sTELLAFRIEND',	'{config name=mail}',	'{config name=shopName}',	'{$sName} empfiehlt Ihnen {$sArticle}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\n{$sName} hat für Sie bei {$sShop} ein interessantes Produkt gefunden, das Sie sich anschauen sollten:\n\n{$sArticle}\n{$sLink}\n\n{$sComment}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n		{$sName} hat für Sie bei {$sShop} ein interessantes Produkt gefunden, das Sie sich anschauen sollten:<br/>\n        <br/>\n        <strong><a href=\"{$sLink}\">{$sArticle}</a></strong><br/>\n    </p>\n    {if $sComment}\n        <div style=\"border: 2px solid black; border-radius: 5px; padding: 5px;\"><p>{$sComment}</p></div><br/>\n    {/if}\n    \n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:4:{s:5:\"sName\";s:11:\"Peter Meyer\";s:8:\"sArticle\";s:10:\"Blumenvase\";s:5:\"sLink\";s:31:\"http://shopware.example/test123\";s:8:\"sComment\";s:36:\"Hey Peter - das musst du dir ansehen\";}',	0),
(5,	NULL,	'sNOSERIALS',	'{config name=mail}',	'{config name=shopName}',	'Achtung - keine freien Seriennummern für {$sArticleName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nes sind keine weiteren freien Seriennummern für den Artikel\n\n{$sArticleName}\n\nverfügbar. Bitte stelle umgehend neue Seriennummern ein oder deaktiviere den Artikel.\nAußerdem weise dem Kunden {$sMail} bitte manuell eine Seriennummer zu.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        es sind keine weiteren freien Seriennummern für den Artikel<br/>\n    </p>\n    <strong>{$sArticleName}</strong><br/>\n    <p>\n        verfügbar. Bitte stelle umgehend neue Seriennummern ein oder deaktiviere den Artikel.<br/>\n        Außerdem weise dem Kunden {$sMail} bitte manuell eine Seriennummer zu.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:2:{s:12:\"sArticleName\";s:20:\"ESD Download Artikel\";s:5:\"sMail\";s:23:\"max.mustermann@mail.com\";}',	0),
(7,	NULL,	'sVOUCHER',	'{config name=mail}',	'{config name=shopName}',	'Ihr Gutschein',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$customer},\n\n{$user} ist Ihrer Empfehlung gefolgt und hat soeben bei {$sShop} bestellt.\nWir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.\n\nIhr Gutschein-Code lautet: XXX\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$customer},<br/>\n        <br/>\n        {$user} ist Ihrer Empfehlung gefolgt und hat soeben bei {$sShop} bestellt.<br/>\n        Wir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.<br/>\n        <br/>\n        <strong>Ihr Gutschein-Code lautet: XXX</strong>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:2:{s:8:\"customer\";s:11:\"Peter Meyer\";s:4:\"user\";s:11:\"Hans Maiser\";}',	0),
(12,	NULL,	'sCUSTOMERGROUPHACCEPTED',	'{config name=mail}',	'{config name=shopName}',	'Ihr Händleraccount wurde freigeschaltet',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nIhr Händleraccount bei {$sShop} wurde freigeschaltet.\nAb sofort kaufen Sie zum Netto-EK bei uns ein.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        Ihr Händleraccount bei {$sShop} wurde freigeschaltet.<br/>\n        Ab sofort kaufen Sie zum Netto-EK bei uns ein.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(13,	NULL,	'sCUSTOMERGROUPHREJECTED',	'{config name=mail}',	'{config name=shopName}',	'Ihr Händleraccount wurde abgelehnt',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.\nBei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n		<br/>\n        vielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.<br/>\n        Bei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(19,	NULL,	'sCANCELEDQUESTION',	'{config name=mail}',	'{config name=shopName}',	'Ihre abgebrochene Bestellung - Jetzt Feedback geben und Gutschein kassieren',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n \nSie haben vor kurzem Ihre Bestellung auf {$sShop} nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist. Bitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.\nVielen Dank für Ihre Unterstützung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        Sie haben vor kurzem Ihre Bestellung auf {$sShop} nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist. Bitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.<br/>\n        Vielen Dank für Ihre Unterstützung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(20,	NULL,	'sCANCELEDVOUCHER',	'{config name=mail}',	'{config name=shopName}',	'Ihre abgebrochene Bestellung - Gutschein-Code anbei',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n \nSie haben vor kurzem Ihre Bestellung bei {$sShop} nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen {if $sVoucherpercental == \"1\"}{$sVouchervalue} %{else}{$sVouchervalue|currency|unescape:\"htmlall\"}{/if} Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung bei {$sShop} erleichtern. Ihr Gutschein ist 2 Monate gültig und kann mit dem Code \"{$sVouchercode}\" eingelöst werden. Wir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n         Hallo,<br/>\n         <br/>\n         Sie haben vor kurzem Ihre Bestellung bei {$sShop} nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen {if $sVoucherpercental == \"1\"}{$sVouchervalue} %{else}{$sVouchervalue|currency}{/if} Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung bei {$sShop} erleichtern. Ihr Gutschein ist 2 Monate gültig und kann mit dem Code \"<strong>{$sVouchercode}</strong>\" eingelöst werden. Wir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:5:{s:12:\"sVouchercode\";s:8:\"23A7BCA4\";s:13:\"sVouchervalue\";i:15;s:15:\"sVouchervalidto\";N;s:17:\"sVouchervalidfrom\";N;s:17:\"sVoucherpercental\";i:0;}',	0),
(21,	9,	'sORDERSTATEMAIL9',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:1:\"9\";s:9:\"clearedID\";s:1:\"9\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:18:\"partially_invoiced\";s:19:\"cleared_description\";s:30:\"Teilweise in Rechnung gestellt\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(22,	10,	'sORDERSTATEMAIL10',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"10\";s:9:\"clearedID\";s:2:\"10\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:19:\"completely_invoiced\";s:19:\"cleared_description\";s:29:\"Komplett in Rechnung gestellt\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(24,	13,	'sORDERSTATEMAIL13',	'{config name=mail}',	'{config name=shopName}',	'1. Mahnung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\ndies ist Ihre erste Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nBitte begleichen Sie schnellstmöglich Ihre Rechnung!\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        dies ist Ihre erste Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"13\";s:9:\"clearedID\";s:2:\"13\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:12:\"1st_reminder\";s:19:\"cleared_description\";s:10:\"1. Mahnung\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(25,	16,	'sORDERSTATEMAIL16',	'{config name=mail}',	'{config name=shopName}',	'Inkasso der Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nSie haben inzwischen 3 Mahnungen zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} erhalten!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nSie werden in Kürze Post von einem Inkasso Unternehmen erhalten!\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        Sie haben inzwischen 3 Mahnungen zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} erhalten!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        <strong>Sie werden in Kürze Post von einem Inkasso Unternehmen erhalten!</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"16\";s:9:\"clearedID\";s:2:\"16\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:10:\"encashment\";s:19:\"cleared_description\";s:7:\"Inkasso\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(26,	15,	'sORDERSTATEMAIL15',	'{config name=mail}',	'{config name=shopName}',	'3. Mahnung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\ndies ist Ihre dritte und letzte Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nBitte begleichen Sie schnellstmöglich Ihre Rechnung!\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        dies ist Ihre dritte und letzte Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"15\";s:9:\"clearedID\";s:2:\"15\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:12:\"3rd_reminder\";s:19:\"cleared_description\";s:10:\"3. Mahnung\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(27,	14,	'sORDERSTATEMAIL14',	'{config name=mail}',	'{config name=shopName}',	'2. Mahnung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\ndies ist Ihre zweite Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nBitte begleichen Sie schnellstmöglich Ihre Rechnung!\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        dies ist Ihre zweite Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"}!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"14\";s:9:\"clearedID\";s:2:\"14\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:12:\"2nd_reminder\";s:19:\"cleared_description\";s:10:\"2. Mahnung\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(28,	12,	'sORDERSTATEMAIL12',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} ist komplett bezahlt',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:16:\"Komplett bezahlt\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(32,	17,	'sORDERSTATEMAIL17',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(35,	18,	'sORDERSTATEMAIL18',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"18\";s:9:\"clearedID\";s:2:\"18\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:8:\"reserved\";s:19:\"cleared_description\";s:10:\"Reserviert\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(36,	19,	'sORDERSTATEMAIL19',	'{config name=mail}',	'{config name=shopName}',	'Verzögerung der Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"19\";s:9:\"clearedID\";s:2:\"19\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:7:\"delayed\";s:19:\"cleared_description\";s:10:\"Verzoegert\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(37,	20,	'sORDERSTATEMAIL20',	'{config name=mail}',	'{config name=shopName}',	'Wiedergutschrift der Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"20\";s:9:\"clearedID\";s:2:\"20\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:12:\"re_crediting\";s:19:\"cleared_description\";s:16:\"Wiedergutschrift\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(40,	NULL,	'sARTICLESTOCK',	'{config name=mail}',	'{config name=shopName}',	'Lagerbestand von {$sData.count} Artikel{if $sData.count>1}n{/if} unter Mindestbestand ',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nfolgende Artikel haben den Mindestbestand unterschritten:\n\nBestellnummer     Artikelname    Bestand/Mindestbestand\n{foreach from=$sJob.articles item=sArticle key=key}\n{$sArticle.ordernumber}       {$sArticle.name}        {$sArticle.instock}/{$sArticle.stockmin}\n{/foreach}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        folgende Artikel haben den Mindestbestand unterschritten:<br/>\n    </p>\n    <table width=\"80%\" border=\"0\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:12px;\">\n        <tr>\n            <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Bestellnummer</strong></td>\n            <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Artikelname</strong></td>\n            <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Bestand/Mindestbestand</strong></td>\n        </tr>\n    \n        {foreach from=$sJob.articles item=sArticle key=key}\n            <tr>\n                <td>{$sArticle.ordernumber}</td>\n                <td>{$sArticle.name}</td>\n                <td>{$sArticle.instock}/{$sArticle.stockmin}</td>\n            </tr>\n        {/foreach}\n    </table>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>\n',	1,	'',	2,	'a:2:{s:5:\"sData\";a:2:{s:5:\"count\";i:1;s:7:\"numbers\";a:1:{i:2;s:10:\"SW10002841\";}}s:4:\"sJob\";a:1:{s:8:\"articles\";a:1:{s:7:\"SW10200\";a:48:{s:11:\"ordernumber\";s:7:\"SW10200\";s:2:\"id\";s:3:\"441\";s:9:\"articleID\";s:3:\"201\";s:6:\"unitID\";N;s:4:\"name\";s:26:\"Hervorgehobene Darstellung\";s:11:\"description\";s:139:\"Über diese Option lassen sich Artikel in der Storefront besonders kennzeichnen. Standardmäßig werden diese Artikel als \"Tipp\" angezeigt.\";s:16:\"description_long\";s:172:\"<p><span>&Uuml;ber diese Option lassen sich Artikel in der Storefront besonders kennzeichnen. Standardm&auml;&szlig;ig werden diese Artikel als \"Tipp\" angezeigt.</span></p>\";s:12:\"shippingtime\";N;s:5:\"added\";s:10:\"2012-07-16\";s:9:\"topseller\";s:1:\"1\";s:8:\"keywords\";s:0:\"\";s:5:\"taxID\";s:1:\"1\";s:10:\"supplierID\";s:2:\"14\";s:7:\"changed\";s:19:\"2012-08-30 16:17:44\";s:16:\"articledetailsID\";s:3:\"441\";s:14:\"suppliernumber\";s:0:\"\";s:4:\"kind\";s:1:\"1\";s:14:\"additionaltext\";s:0:\"\";s:11:\"impressions\";s:1:\"0\";s:5:\"sales\";s:1:\"0\";s:6:\"active\";s:1:\"1\";s:7:\"instock\";s:1:\"0\";s:8:\"stockmin\";s:2:\"96\";s:6:\"weight\";s:5:\"0.000\";s:8:\"position\";s:1:\"0\";s:5:\"attr1\";s:0:\"\";s:5:\"attr2\";s:0:\"\";s:5:\"attr3\";s:0:\"\";s:5:\"attr4\";s:0:\"\";s:5:\"attr5\";s:0:\"\";s:5:\"attr6\";s:0:\"\";s:5:\"attr7\";s:0:\"\";s:5:\"attr8\";s:0:\"\";s:5:\"attr9\";s:0:\"\";s:6:\"attr10\";s:0:\"\";s:6:\"attr11\";s:0:\"\";s:6:\"attr12\";s:0:\"\";s:6:\"attr13\";s:0:\"\";s:6:\"attr14\";s:0:\"\";s:6:\"attr15\";s:0:\"\";s:6:\"attr16\";s:0:\"\";s:6:\"attr17\";N;s:6:\"attr18\";s:0:\"\";s:6:\"attr19\";s:0:\"\";s:6:\"attr20\";s:0:\"\";s:8:\"supplier\";s:7:\"Example\";s:4:\"unit\";N;s:3:\"tax\";s:5:\"19.00\";}}}}',	0),
(41,	NULL,	'sNEWSLETTERCONFIRMATION',	'{config name=mail}',	'{config name=shopName}',	'Vielen Dank für Ihre Newsletter-Anmeldung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:9:{s:27:\"sUser.subscribeToNewsletter\";s:1:\"1\";s:16:\"sUser.newsletter\";s:0:\"\";s:16:\"sUser.salutation\";s:4:\"Herr\";s:15:\"sUser.firstname\";s:3:\"Max\";s:14:\"sUser.lastname\";s:10:\"Mustermann\";s:12:\"sUser.street\";s:0:\"\";s:13:\"sUser.zipcode\";s:0:\"\";s:10:\"sUser.city\";s:0:\"\";s:15:\"sUser.Speichern\";s:0:\"\";}',	0),
(42,	NULL,	'sOPTINNEWSLETTER',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre Newsletter-Anmeldung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter.\nBitte bestätigen Sie die Anmeldung über den nachfolgenden Link:\n\n{$sConfirmLink}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter.<br/>\n        Bitte bestätigen Sie die Anmeldung über den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Bestätigen</a>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:10:{s:12:\"sConfirmLink\";s:24:\"http://shopware.example/\";s:27:\"sUser.subscribeToNewsletter\";s:1:\"1\";s:16:\"sUser.newsletter\";s:0:\"\";s:16:\"sUser.salutation\";s:0:\"\";s:15:\"sUser.firstname\";s:0:\"\";s:14:\"sUser.lastname\";s:0:\"\";s:12:\"sUser.street\";s:0:\"\";s:13:\"sUser.zipcode\";s:0:\"\";s:10:\"sUser.city\";s:0:\"\";s:15:\"sUser.Speichern\";s:0:\"\";}',	0),
(43,	NULL,	'sOPTINVOTE',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre Artikel-Bewertung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für die Bewertung des Artikels {$sArticle.articleName}.\nBitte bestätigen Sie die Bewertung über den nachfolgenden Link:\n\n{$sConfirmLink}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank für die Bewertung des Artikels {$sArticle.articleName}.<br/>\n        Bitte bestätigen Sie die Bewertung über nach den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Artikelbewertung bestätigen</a>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:2:{s:12:\"sConfirmLink\";s:133:\"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=rating&sConfirmation=6avE5xLF22DTp8gNPaZ8KRUfJhflnvU9\";s:8:\"sArticle\";a:1:{s:11:\"articleName\";s:24:\"DIE ZEIT 5 Cowhide mokka\";}}',	0),
(44,	NULL,	'sARTICLEAVAILABLE',	'{config name=mail}',	'{config name=shopName}',	'Ihr Artikel ist wieder verfügbar',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nIhr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.\n\n{$sArticleLink}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n   {include file=\"string:{config name=emailheaderhtml}\"}\n   <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        Ihr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.<br/>\n        <br/>\n        <a href=\"{$sArticleLink}\">{$sOrdernumber}</a>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:3:{s:12:\"sArticleLink\";s:70:\"http://shopware.example/genusswelten/koestlichkeiten/272/spachtelmasse\";s:12:\"sOrdernumber\";s:7:\"SW10239\";s:5:\"sData\";N;}',	0),
(45,	NULL,	'sACCEPTNOTIFICATION',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre E-Mail-Benachrichtigung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank, dass Sie sich für die automatische E-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.\nBitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link:\n\n{$sConfirmLink}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank, dass Sie sich für die automatische E-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.<br/>\n        Bitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Bestätigen</a>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:2:{s:12:\"sConfirmLink\";s:177:\"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=notifyConfirm&sNotificationConfirmation=j48FnwtKhMycfizOyYe0CtB0UKzgoeYG&sNotify=1&number=SW10165\";s:12:\"sArticleName\";s:24:\"DIE ZEIT 5 Cowhide mokka\";}',	0),
(51,	NULL,	'sORDERSEPAAUTHORIZATION',	'{config name=mail}',	'{config name=shopName}',	'SEPA Lastschriftmandat',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nim Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n    	Hallo {$paymentInstance.firstName} {$paymentInstance.lastName},<br/>\n    	<br/>\n    	im Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:1:{s:15:\"paymentInstance\";a:3:{s:9:\"firstName\";s:3:\"Max\";s:8:\"lastName\";s:10:\"Mustermann\";s:11:\"orderNumber\";s:5:\"20003\";}}',	0),
(52,	NULL,	'sCONFIRMPASSWORDCHANGE',	'{config name=mail}',	'{config name=shopName}',	'Passwort vergessen - Passwort zurücksetzen',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$user.salutation|salutation} {$user.lastname},\n\nim Shop {$sShop} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen. Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.\n\n{$sUrlReset}\n\nDieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden. Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$user.salutation|salutation} {$user.lastname},<br/>\n        <br/>\n        im Shop {$sShop} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen.\n        Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.<br/>\n        <br/>\n        <a href=\"{$sUrlReset}\">Passwort zurücksetzen</a><br/>\n        <br/>\n        Dieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden.\n        Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:4:{s:9:\"sUrlReset\";s:83:\"http://shopware.example/account/resetPassword/hash/pdiR4nNSvvTYHQGxC0K2PxLk5QtQilXm\";s:4:\"sUrl\";s:0:\"\";s:4:\"sKey\";s:0:\"\";s:4:\"user\";a:21:{s:11:\"accountmode\";s:1:\"0\";s:6:\"active\";s:1:\"1\";s:9:\"affiliate\";s:1:\"0\";s:8:\"birthday\";N;s:15:\"confirmationkey\";s:0:\"\";s:13:\"customergroup\";s:2:\"EK\";s:14:\"customernumber\";s:5:\"20001\";s:5:\"email\";s:16:\"test@example.com\";s:12:\"failedlogins\";s:1:\"0\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:8:\"language\";s:1:\"1\";s:15:\"internalcomment\";s:0:\"\";s:11:\"lockeduntil\";N;s:9:\"subshopID\";s:1:\"1\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:10:\"newsletter\";s:1:\"0\";s:10:\"attributes\";b:0;}}',	0),
(53,	1,	'sORDERSTATEMAIL1',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} ist in Bearbeitung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"1\";s:8:\"statusID\";s:1:\"1\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:10:\"in_process\";s:18:\"status_description\";s:23:\"In Bearbeitung (Wartet)\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(54,	2,	'sORDERSTATEMAIL2',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} komplett abgeschlossen',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"2\";s:8:\"statusID\";s:1:\"2\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:9:\"completed\";s:18:\"status_description\";s:22:\"Komplett abgeschlossen\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(55,	11,	'sORDERSTATEMAIL11',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"11\";s:9:\"clearedID\";s:2:\"11\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:14:\"partially_paid\";s:19:\"cleared_description\";s:17:\"Teilweise bezahlt\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(56,	5,	'sORDERSTATEMAIL5',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} ist bereit zur Lieferung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"5\";s:8:\"statusID\";s:1:\"5\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:18:\"ready_for_delivery\";s:18:\"status_description\";s:20:\"Zur Lieferung bereit\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(57,	3,	'sORDERSTATEMAIL3',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\n\nInformationen zu Ihrer Bestellung:\n==================================\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:3}      {$details.articleordernumber}     {$details.name|fill:30}     {$details.quantity} x {$details.price|string_format:\"%.2f\"} {$sOrder.currency}\n{/foreach}\n\nVersandkosten: {$sOrder.invoice_shipping|string_format:\"%.2f\"} {$sOrder.currency}\nNetto-Gesamt: {$sOrder.invoice_amount_net|string_format:\"%.2f\"} {$sOrder.currency}\nGesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:\"%.2f\"} {$sOrder.currency}\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        <strong>Informationen zu Ihrer Bestellung:</strong></p><br/>\n        <table width=\"80%\" border=\"0\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:12px;\">\n            <tr>\n                <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Artikel</strong></td>\n                <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Pos.</strong></td>\n                <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Art-Nr.</strong></td>\n                <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Menge</strong></td>\n                <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Preis</strong></td>\n            </tr>\n            {foreach item=details key=position from=$sOrderDetails}\n            <tr>\n                <td>{$details.name|wordwrap:80|indent:4}</td>\n                <td>{$position+1|fill:4} </td>\n                <td>{$details.ordernumber|fill:20}</td>\n                <td>{$details.quantity|fill:6}</td>\n                <td>{$details.price|padding:8} {$sOrder.currency}</td>\n            </tr>\n            {/foreach}\n        </table>\n    <p>    \n        <br/>\n        Versandkosten: {$sOrder.invoice_shipping|string_format:\"%.2f\"} {$sOrder.currency}<br/>\n        Netto-Gesamt: {$sOrder.invoice_amount_net|string_format:\"%.2f\"} {$sOrder.currency}<br/>\n        Gesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:\"%.2f\"} {$sOrder.currency}<br/>\n    	<br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"3\";s:8:\"statusID\";s:1:\"3\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:19:\"partially_completed\";s:18:\"status_description\";s:23:\"Teilweise abgeschlossen\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(58,	8,	'sORDERSTATEMAIL8',	'{config name=mail}',	'{config name=shopName}',	'Statusänderung zur Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"8\";s:8:\"statusID\";s:1:\"8\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:22:\"clarification_required\";s:18:\"status_description\";s:18:\"Klärung notwendig\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(59,	4,	'sORDERSTATEMAIL4',	'{config name=mail}',	'{config name=shopName}',	'Stornierung der Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"4\";s:8:\"statusID\";s:1:\"4\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:18:\"cancelled_rejected\";s:18:\"status_description\";s:21:\"Storniert / Abgelehnt\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(60,	6,	'sORDERSTATEMAIL6',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} wurde teilweise ausgeliefert',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"6\";s:8:\"statusID\";s:1:\"6\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:19:\"partially_delivered\";s:18:\"status_description\";s:22:\"Teilweise ausgeliefert\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(61,	7,	'sORDERSTATEMAIL7',	'{config name=mail}',	'{config name=shopName}',	'Bestellung bei {config name=shopName} wurde ausgeliefert',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!\nDie Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n        <br/>\n        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} hat sich geändert!<br/>\n        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>\n        <br/>\n        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	3,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"3\";s:10:\"customerID\";s:1:\"3\";s:14:\"invoice_amount\";s:5:\"31.85\";s:18:\"invoice_amount_net\";s:5:\"26.77\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-08-07 14:09:26\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"17\";s:9:\"clearedID\";s:2:\"17\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:4:\"open\";s:19:\"cleared_description\";s:5:\"Offen\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:21:\"Komplett ausgeliefert\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}s:13:\"sOrderDetails\";a:2:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"208\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"152\";s:18:\"articleordernumber\";s:9:\"SW10152.1\";s:5:\"price\";s:5:\"29.95\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"29.95\";s:4:\"name\";s:31:\"WINDSTOPPER MÜTZE WARM Schwarz\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}i:1;a:20:{s:14:\"orderdetailsID\";s:3:\"209\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:1:\"0\";s:18:\"articleordernumber\";s:16:\"SHIPPINGDISCOUNT\";s:5:\"price\";s:2:\"-2\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:2:\"-2\";s:4:\"name\";s:15:\"Warenkorbrabatt\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"4\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"0\";s:3:\"tax\";N;s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"0\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";N;s:10:\"attribute2\";N;s:10:\"attribute3\";N;s:10:\"attribute4\";N;s:10:\"attribute5\";N;s:10:\"attribute6\";N;}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:0:\"\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20005\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:15:\"Musterstraße 1\";s:32:\"billing_additional_address_line1\";s:0:\"\";s:32:\"billing_additional_address_line2\";s:0:\"\";s:15:\"billing_zipcode\";s:5:\"12345\";s:12:\"billing_city\";s:11:\"Musterstadt\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";N;s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"59\";s:16:\"shipping_company\";s:0:\"\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:15:\"Musterstraße 1\";s:33:\"shipping_additional_address_line1\";s:0:\"\";s:33:\"shipping_additional_address_line2\";s:0:\"\";s:16:\"shipping_zipcode\";s:5:\"12345\";s:13:\"shipping_city\";s:11:\"Musterstadt\";s:16:\"shipping_stateID\";N;s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"3\";s:8:\"password\";s:60:\"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:14:\"xy@example.org\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2017-08-07\";s:9:\"lastlogin\";s:19:\"2017-08-07 14:09:26\";s:9:\"sessionID\";s:26:\"hkkhfl82i1jejfvd2f0ucr6om4\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:0:\"\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"5\";s:27:\"default_shipping_address_id\";s:1:\"5\";s:5:\"title\";N;s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"1239c089-6b2f-4461-9134-c02026970bff.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(62,	NULL,	'sBIRTHDAY',	'{config name=mail}',	'{config name=shopName}',	'Herzlichen Glückwunsch zum Geburtstag von {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n \nAlles Gute zum Geburtstag. Zu Ihrem persönlichen Jubiläum haben wir uns etwas Besonderes ausgedacht, wir senden Ihnen hiermit einen Geburtstagscode über {if $sVoucher.value}{$sVoucher.value|currency|unescape:\"htmlall\"}{else}{$sVoucher.percental} %{/if}, den Sie bei Ihrer nächsten Bestellung in unserem Online-Shop: {$sShopURL} ganz einfach einlösen können.\n \nIhr persönlicher Geburtstags-Code lautet: {$sVoucher.code}\n{if $sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig vom {$sVoucher.valid_from|date_format:\"%d.%m.%Y\"} bis zum {$sVoucher.valid_to|date_format:\"%d.%m.%Y\"}.{/if}\n{if $sVoucher.valid_from && !$sVoucher.valid_to}Dieser Code ist gültig ab dem {$sVoucher.valid_from|date_format:\"%d.%m.%Y\"}.{/if}\n{if !$sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig bis zum {$sVoucher.valid_to|date_format:\"%d.%m.%Y\"}.{/if}\n\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n	<p>Hallo {$sUser.salutation|salutation} {$sUser.lastname},</p>\n 	<p><strong>Alles Gute zum Geburtstag</strong>. Zu Ihrem persönlichen Jubiläum haben wir uns etwas Besonderes ausgedacht, wir senden Ihnen hiermit einen Geburtstagscode über {if $sVoucher.value}{$sVoucher.value|currency|unescape:\"htmlall\"}{else}{$sVoucher.percental} %{/if}, den Sie bei Ihrer nächsten Bestellung in unserem <a href=\"{$sShopURL}\" title=\"{$sShop}\">Online-Shop</a> ganz einfach einlösen können.</p>\n 	<p><strong>Ihr persönlicher Geburtstags-Code lautet: <span style=\"text-decoration:underline;\">{$sVoucher.code}</span></strong><br/>\n 	{if $sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig vom {$sVoucher.valid_from|date_format:\"%d.%m.%Y\"} bis zum {$sVoucher.valid_to|date_format:\"%d.%m.%Y\"}.{/if}\n 	{if $sVoucher.valid_from && !$sVoucher.valid_to}Dieser Code ist gültig ab dem {$sVoucher.valid_from|date_format:\"%d.%m.%Y\"}.{/if}\n 	{if !$sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig bis zum {$sVoucher.valid_to|date_format:\"%d.%m.%Y\"}.{/if}\n</p>\n \n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:3:{s:5:\"sUser\";a:28:{s:6:\"userID\";s:1:\"1\";s:7:\"company\";s:11:\"Muster GmbH\";s:10:\"department\";N;s:10:\"salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:6:\"street\";s:13:\"Musterstr. 55\";s:7:\"zipcode\";s:5:\"55555\";s:4:\"city\";s:12:\"Musterhausen\";s:5:\"phone\";s:14:\"05555 / 555555\";s:9:\"countryID\";s:1:\"2\";s:5:\"ustid\";N;s:5:\"text1\";N;s:5:\"text2\";N;s:5:\"text3\";N;s:5:\"text4\";N;s:5:\"text5\";N;s:5:\"text6\";N;s:5:\"email\";s:16:\"test@example.com\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:10:\"newsletter\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";}s:8:\"sVoucher\";a:6:{s:13:\"vouchercodeID\";s:3:\"201\";s:4:\"code\";s:8:\"0B818118\";s:5:\"value\";s:1:\"5\";s:9:\"percental\";s:1:\"0\";s:8:\"valid_to\";s:10:\"2017-12-31\";s:10:\"valid_from\";s:10:\"2017-10-22\";}s:5:\"sData\";N;}',	0),
(63,	NULL,	'sARTICLECOMMENT',	'{config name=mail}',	'{config name=shopName}',	'Artikel bewerten',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},\n\nSie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.\nSo helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.\n\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\n\nBestellnummer     Artikelname     Bewertungslink\n{foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n{$sArticle.articleordernumber}      {$sArticle.name}      {$sArticle.link_rating_tab}\n{/if}\n{/foreach}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>\n    <br/>\n    Sie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>\n    So helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.<br/>\n    <br/>\n    Hier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.<br/>\n    <br/>\n    <table width=\"80%\" border=\"0\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:12px;\">\n        <tr>\n          <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\">Artikel</td>\n          <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\">Bestellnummer</td>\n          <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\">Artikelname</td>\n          <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\">Bewertungslink</td>\n        </tr>\n        {foreach from=$sArticles item=sArticle key=key}\n        {if !$sArticle.modus}\n            <tr>\n                <td style=\"border-bottom:1px solid #cccccc;\">\n                  {if $sArticle.image_small && $sArticle.modus == 0}\n                    <img style=\"height: 57px;\" height=\"57\" src=\"{$sArticle.image_small}\" alt=\"{$sArticle.articlename}\" />\n                  {else}\n                  {/if}\n                </td>\n                <td style=\"border-bottom:1px solid #cccccc;\">{$sArticle.articleordernumber}</td>\n                <td style=\"border-bottom:1px solid #cccccc;\">{$sArticle.name}</td>\n                <td style=\"border-bottom:1px solid #cccccc;\">\n                    <a href=\"{$sArticle.link_rating_tab}\">Link</a>\n                </td>\n            </tr>\n        {/if}\n        {/foreach}\n    </table>\n    <br/><br/>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	'a:4:{s:7:\"sConfig\";a:0:{}s:6:\"sOrder\";a:38:{s:2:\"id\";s:2:\"59\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:12:\"order_number\";s:5:\"20003\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"271.85\";s:18:\"invoice_amount_net\";s:6:\"228.45\";s:16:\"invoice_shipping\";s:3:\"3.9\";s:20:\"invoice_shipping_net\";s:4:\"3.28\";s:9:\"ordertime\";s:19:\"2017-10-09 11:41:41\";s:6:\"status\";s:1:\"2\";s:8:\"statusID\";s:1:\"2\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"5\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";s:19:\"2017-10-09 00:00:00\";s:12:\"cleared_date\";s:19:\"2017-10-09 00:00:00\";s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:19:\"cleared_description\";s:16:\"Komplett bezahlt\";s:18:\"status_description\";s:22:\"Komplett abgeschlossen\";s:19:\"payment_description\";s:8:\"Vorkasse\";s:20:\"dispatch_description\";s:16:\"Standard Versand\";s:20:\"currency_description\";s:4:\"Euro\";}s:5:\"sUser\";a:76:{s:7:\"orderID\";s:2:\"59\";s:15:\"billing_company\";s:11:\"Muster GmbH\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:13:\"Musterstr. 55\";s:15:\"billing_zipcode\";s:5:\"55555\";s:12:\"billing_city\";s:12:\"Musterhausen\";s:5:\"phone\";s:14:\"05555 / 555555\";s:13:\"billing_phone\";s:14:\"05555 / 555555\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:6:\"bcrypt\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2017-10-09 11:41:41\";s:9:\"sessionID\";s:26:\"sh860bhb7plloqm4teo8s99tq0\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";s:38:\"0626e2f8-db4a-41b3-b103-e9cece25f51a.1\";s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sArticles\";a:1:{i:212;a:25:{s:14:\"orderdetailsID\";s:3:\"212\";s:7:\"orderID\";s:2:\"59\";s:11:\"ordernumber\";s:5:\"20003\";s:9:\"articleID\";s:3:\"134\";s:18:\"articleordernumber\";s:7:\"SW10153\";s:5:\"price\";s:5:\"49.99\";s:8:\"quantity\";s:1:\"1\";s:7:\"invoice\";s:5:\"49.99\";s:4:\"name\";s:11:\"ELASTIC CAP\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"0\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:3:\"esd\";s:1:\"0\";s:9:\"subshopID\";s:1:\"1\";s:8:\"language\";s:1:\"1\";s:4:\"link\";s:42:\"http://shopware.example/elastic-muetze-153\";s:15:\"link_rating_tab\";s:57:\"http://shopware.example/elastic-muetze-153?jumpTab=rating\";s:11:\"image_large\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:11:\"image_small\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";s:14:\"image_original\";s:68:\"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg\";}}}',	0),
(64,	NULL,	'sORDERDOCUMENTS',	'{config name=mail}',	'{config name=shopName}',	'Dokumente zur Bestellung {$orderNumber}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n\nvielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie Dokumente zu Ihrer Bestellung als PDF.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>\n        <br/>\n        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie Dokumente zu Ihrer Bestellung als PDF.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	4,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:12:\"order_number\";s:5:\"20002\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"201.86\";s:18:\"invoice_amount_net\";s:6:\"169.63\";s:16:\"invoice_shipping\";s:1:\"0\";s:20:\"invoice_shipping_net\";s:1:\"0\";s:9:\"ordertime\";s:19:\"2012-08-31 08:51:46\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"4\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:0:\"\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:0:\"\";s:19:\"payment_description\";s:0:\"\";s:20:\"dispatch_description\";s:0:\"\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}s:13:\"sOrderDetails\";a:1:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"204\";s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:9:\"articleID\";s:3:\"197\";s:18:\"articleordernumber\";s:7:\"SW10196\";s:5:\"price\";s:5:\"34.99\";s:8:\"quantity\";s:1:\"2\";s:7:\"invoice\";s:5:\"69.98\";s:4:\"name\";s:7:\"Artikel\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"1\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:11:\"shopware AG\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:20:\"Mustermannstraße 92\";s:32:\"billing_additional_address_line1\";N;s:32:\"billing_additional_address_line2\";N;s:15:\"billing_zipcode\";s:5:\"48624\";s:12:\"billing_city\";s:12:\"Schöppingen\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";s:1:\"3\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"57\";s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:33:\"shipping_additional_address_line1\";N;s:33:\"shipping_additional_address_line2\";N;s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:16:\"shipping_stateID\";s:1:\"3\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:3:\"md5\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:9:\"sessionID\";s:26:\"uiorqd755gaar8dn89ukp178c7\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";N;s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(65,	NULL,	'sOPTINREGISTER',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre Anmeldung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für Ihre Anmeldung bei {$sShop}.\nBitte bestätigen Sie die Registrierung über den nachfolgenden Link:\n\n{$sConfirmLink}\n\nDurch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank für Ihre Anmeldung bei {$sShop}.<br/>\n        Bitte bestätigen Sie die Registrierung über den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Anmeldung abschließen</a><br/>\n        <br/>\n        Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.<br/>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(66,	NULL,	'sOPTINBLOGCOMMENT',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre Blogartikel-Bewertung',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo,\n\nvielen Dank für die Bewertung des Blogartikels „{$sArticle.title}“.\nBitte bestätigen Sie die Bewertung über den nachfolgenden Link:\n\n{$sConfirmLink}\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        vielen Dank für die Bewertung des Blogartikels „{$sArticle.title}“.<br/>\n        Bitte bestätigen Sie die Bewertung über den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Blogartikel-Bewertung abschließen</a><br/>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(67,	NULL,	'sOPTINREGISTERACCOUNTLESS',	'{config name=mail}',	'{config name=shopName}',	'Bitte bestätigen Sie Ihre E-Mail-Adresse für Ihre Bestellung bei {config name=shopName}',	'{include file=\"string:{config name=emailheaderplain}\"}\n    \nHallo,\n\nBitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link:\n\n{$sConfirmLink}\n\nNach der Bestätigung werden Sie in den Bestellabschluss geleitet, dort können Sie Ihre Bestellung nochmals überprüfen und abschließen.\nDurch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo,<br/>\n        <br/>\n        Bitte bestätigen Sie Ihre E-Mail-Adresse über den nachfolgenden Link:<br/>\n        <br/>\n        <a href=\"{$sConfirmLink}\">Bestellung fortsetzen</a><br/>\n        <br/>\n        Nach der Bestätigung werden Sie in den Bestellabschluss geleitet, dort können Sie Ihre Bestellung nochmals überprüfen und abschließen.<br/>\n        Durch diese Bestätigung erklären Sie sich ebenso damit einverstanden, dass wir Ihnen im Rahmen der Vertragserfüllung weitere E-Mails senden dürfen.<br/>\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	2,	NULL,	0),
(68,	NULL,	'document_invoice',	'{config name=mail}',	'{config name=shopName}',	'Rechnung zur Bestellung {$orderNumber}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n\nvielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Rechnung zu Ihrer Bestellung als PDF.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>\n        <br/>\n        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Rechnung zu Ihrer Bestellung als PDF.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	4,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:12:\"order_number\";s:5:\"20002\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"201.86\";s:18:\"invoice_amount_net\";s:6:\"169.63\";s:16:\"invoice_shipping\";s:1:\"0\";s:20:\"invoice_shipping_net\";s:1:\"0\";s:9:\"ordertime\";s:19:\"2012-08-31 08:51:46\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"4\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:0:\"\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:0:\"\";s:19:\"payment_description\";s:0:\"\";s:20:\"dispatch_description\";s:0:\"\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}s:13:\"sOrderDetails\";a:1:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"204\";s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:9:\"articleID\";s:3:\"197\";s:18:\"articleordernumber\";s:7:\"SW10196\";s:5:\"price\";s:5:\"34.99\";s:8:\"quantity\";s:1:\"2\";s:7:\"invoice\";s:5:\"69.98\";s:4:\"name\";s:7:\"Artikel\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"1\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:11:\"shopware AG\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:20:\"Mustermannstraße 92\";s:32:\"billing_additional_address_line1\";N;s:32:\"billing_additional_address_line2\";N;s:15:\"billing_zipcode\";s:5:\"48624\";s:12:\"billing_city\";s:12:\"Schöppingen\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";s:1:\"3\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"57\";s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:33:\"shipping_additional_address_line1\";N;s:33:\"shipping_additional_address_line2\";N;s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:16:\"shipping_stateID\";s:1:\"3\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:3:\"md5\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:9:\"sessionID\";s:26:\"uiorqd755gaar8dn89ukp178c7\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";N;s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(69,	NULL,	'document_delivery_note',	'{config name=mail}',	'{config name=shopName}',	'Lieferschein zur Bestellung {$orderNumber}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n\nvielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie den Lieferschein zu Ihrer Bestellung als PDF.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>\n        <br/>\n        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie den Lieferschein zu Ihrer Bestellung als PDF.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	4,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:12:\"order_number\";s:5:\"20002\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"201.86\";s:18:\"invoice_amount_net\";s:6:\"169.63\";s:16:\"invoice_shipping\";s:1:\"0\";s:20:\"invoice_shipping_net\";s:1:\"0\";s:9:\"ordertime\";s:19:\"2012-08-31 08:51:46\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"4\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:0:\"\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:0:\"\";s:19:\"payment_description\";s:0:\"\";s:20:\"dispatch_description\";s:0:\"\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}s:13:\"sOrderDetails\";a:1:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"204\";s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:9:\"articleID\";s:3:\"197\";s:18:\"articleordernumber\";s:7:\"SW10196\";s:5:\"price\";s:5:\"34.99\";s:8:\"quantity\";s:1:\"2\";s:7:\"invoice\";s:5:\"69.98\";s:4:\"name\";s:7:\"Artikel\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"1\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:11:\"shopware AG\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:20:\"Mustermannstraße 92\";s:32:\"billing_additional_address_line1\";N;s:32:\"billing_additional_address_line2\";N;s:15:\"billing_zipcode\";s:5:\"48624\";s:12:\"billing_city\";s:12:\"Schöppingen\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";s:1:\"3\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"57\";s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:33:\"shipping_additional_address_line1\";N;s:33:\"shipping_additional_address_line2\";N;s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:16:\"shipping_stateID\";s:1:\"3\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:3:\"md5\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:9:\"sessionID\";s:26:\"uiorqd755gaar8dn89ukp178c7\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";N;s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(70,	NULL,	'document_credit',	'{config name=mail}',	'{config name=shopName}',	'Gutschrift zur Bestellung {$orderNumber}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n\nvielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Gutschrift zu Ihrer Bestellung als PDF.\n\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>\n        <br/>\n        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Gutschrift zu Ihrer Bestellung als PDF.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	4,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:12:\"order_number\";s:5:\"20002\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"201.86\";s:18:\"invoice_amount_net\";s:6:\"169.63\";s:16:\"invoice_shipping\";s:1:\"0\";s:20:\"invoice_shipping_net\";s:1:\"0\";s:9:\"ordertime\";s:19:\"2012-08-31 08:51:46\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"4\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:0:\"\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:0:\"\";s:19:\"payment_description\";s:0:\"\";s:20:\"dispatch_description\";s:0:\"\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}s:13:\"sOrderDetails\";a:1:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"204\";s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:9:\"articleID\";s:3:\"197\";s:18:\"articleordernumber\";s:7:\"SW10196\";s:5:\"price\";s:5:\"34.99\";s:8:\"quantity\";s:1:\"2\";s:7:\"invoice\";s:5:\"69.98\";s:4:\"name\";s:7:\"Artikel\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"1\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:11:\"shopware AG\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:20:\"Mustermannstraße 92\";s:32:\"billing_additional_address_line1\";N;s:32:\"billing_additional_address_line2\";N;s:15:\"billing_zipcode\";s:5:\"48624\";s:12:\"billing_city\";s:12:\"Schöppingen\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";s:1:\"3\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"57\";s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:33:\"shipping_additional_address_line1\";N;s:33:\"shipping_additional_address_line2\";N;s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:16:\"shipping_stateID\";s:1:\"3\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:3:\"md5\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:9:\"sessionID\";s:26:\"uiorqd755gaar8dn89ukp178c7\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";N;s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0),
(71,	NULL,	'document_cancellation',	'{config name=mail}',	'{config name=shopName}',	'Stornorechnung zur Bestellung {$orderNumber}',	'{include file=\"string:{config name=emailheaderplain}\"}\n\nHallo {$sUser.salutation|salutation} {$sUser.lastname},\n\nvielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Stornorechnung zu Ihrer Bestellung als PDF.\n{include file=\"string:{config name=emailfooterplain}\"}',	'<div style=\"font-family:arial; font-size:12px;\">\n    {include file=\"string:{config name=emailheaderhtml}\"}\n    <br/><br/>\n    <p>\n        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>\n        <br/>\n        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Stornorechnung zu Ihrer Bestellung als PDF.\n    </p>\n    {include file=\"string:{config name=emailfooterhtml}\"}\n</div>',	1,	'',	4,	'a:4:{s:6:\"sOrder\";a:40:{s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:12:\"order_number\";s:5:\"20002\";s:6:\"userID\";s:1:\"1\";s:10:\"customerID\";s:1:\"1\";s:14:\"invoice_amount\";s:6:\"201.86\";s:18:\"invoice_amount_net\";s:6:\"169.63\";s:16:\"invoice_shipping\";s:1:\"0\";s:20:\"invoice_shipping_net\";s:1:\"0\";s:9:\"ordertime\";s:19:\"2012-08-31 08:51:46\";s:6:\"status\";s:1:\"7\";s:8:\"statusID\";s:1:\"7\";s:7:\"cleared\";s:2:\"12\";s:9:\"clearedID\";s:2:\"12\";s:9:\"paymentID\";s:1:\"4\";s:13:\"transactionID\";s:0:\"\";s:7:\"comment\";s:0:\"\";s:15:\"customercomment\";s:0:\"\";s:3:\"net\";s:1:\"0\";s:5:\"netto\";s:1:\"0\";s:9:\"partnerID\";s:0:\"\";s:11:\"temporaryID\";s:0:\"\";s:7:\"referer\";s:0:\"\";s:11:\"cleareddate\";N;s:12:\"cleared_date\";N;s:12:\"trackingcode\";s:0:\"\";s:8:\"language\";s:1:\"1\";s:8:\"currency\";s:3:\"EUR\";s:14:\"currencyFactor\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:10:\"dispatchID\";s:1:\"9\";s:10:\"currencyID\";s:1:\"1\";s:12:\"cleared_name\";s:15:\"completely_paid\";s:19:\"cleared_description\";s:0:\"\";s:11:\"status_name\";s:20:\"completely_delivered\";s:18:\"status_description\";s:0:\"\";s:19:\"payment_description\";s:0:\"\";s:20:\"dispatch_description\";s:0:\"\";s:20:\"currency_description\";s:4:\"Euro\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}s:13:\"sOrderDetails\";a:1:{i:0;a:20:{s:14:\"orderdetailsID\";s:3:\"204\";s:7:\"orderID\";s:2:\"57\";s:11:\"ordernumber\";s:5:\"20002\";s:9:\"articleID\";s:3:\"197\";s:18:\"articleordernumber\";s:7:\"SW10196\";s:5:\"price\";s:5:\"34.99\";s:8:\"quantity\";s:1:\"2\";s:7:\"invoice\";s:5:\"69.98\";s:4:\"name\";s:7:\"Artikel\";s:6:\"status\";s:1:\"0\";s:7:\"shipped\";s:1:\"0\";s:12:\"shippedgroup\";s:1:\"0\";s:11:\"releasedate\";s:10:\"0000-00-00\";s:5:\"modus\";s:1:\"0\";s:10:\"esdarticle\";s:1:\"1\";s:5:\"taxID\";s:1:\"1\";s:3:\"tax\";s:5:\"19.00\";s:8:\"tax_rate\";s:2:\"19\";s:3:\"esd\";s:1:\"1\";s:10:\"attributes\";a:6:{s:10:\"attribute1\";s:0:\"\";s:10:\"attribute2\";s:0:\"\";s:10:\"attribute3\";s:0:\"\";s:10:\"attribute4\";s:0:\"\";s:10:\"attribute5\";s:0:\"\";s:10:\"attribute6\";s:0:\"\";}}}s:5:\"sUser\";a:82:{s:15:\"billing_company\";s:11:\"shopware AG\";s:18:\"billing_department\";s:0:\"\";s:18:\"billing_salutation\";s:2:\"mr\";s:14:\"customernumber\";s:5:\"20001\";s:17:\"billing_firstname\";s:3:\"Max\";s:16:\"billing_lastname\";s:10:\"Mustermann\";s:14:\"billing_street\";s:20:\"Mustermannstraße 92\";s:32:\"billing_additional_address_line1\";N;s:32:\"billing_additional_address_line2\";N;s:15:\"billing_zipcode\";s:5:\"48624\";s:12:\"billing_city\";s:12:\"Schöppingen\";s:5:\"phone\";s:0:\"\";s:13:\"billing_phone\";s:0:\"\";s:17:\"billing_countryID\";s:1:\"2\";s:15:\"billing_stateID\";s:1:\"3\";s:15:\"billing_country\";s:11:\"Deutschland\";s:18:\"billing_countryiso\";s:2:\"DE\";s:19:\"billing_countryarea\";s:11:\"deutschland\";s:17:\"billing_countryen\";s:7:\"GERMANY\";s:5:\"ustid\";s:0:\"\";s:13:\"billing_text1\";N;s:13:\"billing_text2\";N;s:13:\"billing_text3\";N;s:13:\"billing_text4\";N;s:13:\"billing_text5\";N;s:13:\"billing_text6\";N;s:7:\"orderID\";s:2:\"57\";s:16:\"shipping_company\";s:11:\"shopware AG\";s:19:\"shipping_department\";s:0:\"\";s:19:\"shipping_salutation\";s:2:\"mr\";s:18:\"shipping_firstname\";s:3:\"Max\";s:17:\"shipping_lastname\";s:10:\"Mustermann\";s:15:\"shipping_street\";s:20:\"Mustermannstraße 92\";s:33:\"shipping_additional_address_line1\";N;s:33:\"shipping_additional_address_line2\";N;s:16:\"shipping_zipcode\";s:5:\"48624\";s:13:\"shipping_city\";s:12:\"Schöppingen\";s:16:\"shipping_stateID\";s:1:\"3\";s:18:\"shipping_countryID\";s:1:\"2\";s:16:\"shipping_country\";s:11:\"Deutschland\";s:19:\"shipping_countryiso\";s:2:\"DE\";s:20:\"shipping_countryarea\";s:11:\"deutschland\";s:18:\"shipping_countryen\";s:7:\"GERMANY\";s:14:\"shipping_text1\";N;s:14:\"shipping_text2\";N;s:14:\"shipping_text3\";N;s:14:\"shipping_text4\";N;s:14:\"shipping_text5\";N;s:14:\"shipping_text6\";N;s:2:\"id\";s:1:\"1\";s:8:\"password\";s:0:\"\";s:7:\"encoder\";s:3:\"md5\";s:5:\"email\";s:16:\"test@example.com\";s:6:\"active\";s:1:\"1\";s:11:\"accountmode\";s:1:\"0\";s:15:\"confirmationkey\";s:0:\"\";s:9:\"paymentID\";s:1:\"5\";s:10:\"firstlogin\";s:10:\"2011-11-23\";s:9:\"lastlogin\";s:19:\"2012-01-04 14:12:05\";s:9:\"sessionID\";s:26:\"uiorqd755gaar8dn89ukp178c7\";s:10:\"newsletter\";s:1:\"0\";s:10:\"validation\";s:1:\"0\";s:9:\"affiliate\";s:1:\"0\";s:13:\"customergroup\";s:2:\"EK\";s:13:\"paymentpreset\";s:1:\"0\";s:8:\"language\";s:1:\"1\";s:9:\"subshopID\";s:1:\"1\";s:7:\"referer\";s:0:\"\";s:12:\"pricegroupID\";N;s:15:\"internalcomment\";s:0:\"\";s:12:\"failedlogins\";s:1:\"0\";s:11:\"lockeduntil\";N;s:26:\"default_billing_address_id\";s:1:\"1\";s:27:\"default_shipping_address_id\";s:1:\"3\";s:5:\"title\";s:0:\"\";s:10:\"salutation\";s:2:\"mr\";s:9:\"firstname\";s:3:\"Max\";s:8:\"lastname\";s:10:\"Mustermann\";s:8:\"birthday\";N;s:11:\"login_token\";N;s:11:\"preisgruppe\";s:1:\"1\";s:11:\"billing_net\";s:1:\"1\";}s:9:\"sDispatch\";a:2:{s:4:\"name\";s:16:\"Standard Versand\";s:11:\"description\";s:0:\"\";}}',	0);

DROP TABLE IF EXISTS `s_core_config_mails_attachments`;
CREATE TABLE `s_core_config_mails_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) NOT NULL,
  `mediaID` int(11) NOT NULL,
  `shopID` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`,`mediaID`,`shopID`),
  KEY `mediaID` (`mediaID`),
  KEY `shopID` (`shopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `s_core_config_mails_attributes`;
CREATE TABLE `s_core_config_mails_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`),
  CONSTRAINT `s_core_config_mails_attributes_ibfk_1` FOREIGN KEY (`mailID`) REFERENCES `s_core_config_mails` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_config_values`;
CREATE TABLE `s_core_config_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `shop_id` int(11) unsigned DEFAULT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id_shop_id` (`element_id`,`shop_id`),
  KEY `shop_id` (`shop_id`),
  KEY `element_id` (`element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_countries`;
CREATE TABLE `s_core_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `countryiso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `areaID` int(11) DEFAULT NULL,
  `countryen` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `notice` text COLLATE utf8_unicode_ci,
  `taxfree` int(11) DEFAULT NULL,
  `taxfree_ustid` int(11) DEFAULT NULL,
  `taxfree_ustid_checked` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `iso3` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_state_in_registration` int(1) NOT NULL,
  `force_state_in_registration` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `areaID` (`areaID`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_countries` (`id`, `countryname`, `countryiso`, `areaID`, `countryen`, `position`, `notice`, `taxfree`, `taxfree_ustid`, `taxfree_ustid_checked`, `active`, `iso3`, `display_state_in_registration`, `force_state_in_registration`) VALUES
(2,	'Deutschland',	'DE',	1,	'GERMANY',	1,	'',	0,	0,	0,	1,	'DEU',	0,	0),
(3,	'Arabische Emirate',	'AE',	2,	'ARAB EMIRATES',	10,	'',	0,	0,	0,	0,	'ARE',	0,	0),
(4,	'Australien',	'AU',	2,	'AUSTRALIA',	10,	'',	0,	0,	0,	0,	'AUS',	0,	0),
(5,	'Belgien',	'BE',	3,	'BELGIUM',	10,	'',	0,	0,	0,	0,	'BEL',	0,	0),
(7,	'Dänemark',	'DK',	3,	'DENMARK',	10,	'',	0,	0,	0,	0,	'DNK',	0,	0),
(8,	'Finnland',	'FI',	3,	'FINLAND',	10,	'',	0,	0,	0,	0,	'FIN',	0,	0),
(9,	'Frankreich',	'FR',	3,	'FRANCE',	10,	'',	0,	0,	0,	0,	'FRA',	0,	0),
(10,	'Griechenland',	'GR',	3,	'GREECE',	10,	'',	0,	0,	0,	0,	'GRC',	0,	0),
(11,	'Großbritannien',	'GB',	3,	'GREAT BRITAIN',	10,	'',	0,	0,	0,	0,	'GBR',	0,	0),
(12,	'Irland',	'IE',	3,	'IRELAND',	10,	'',	0,	0,	0,	0,	'IRL',	0,	0),
(13,	'Island',	'IS',	3,	'ICELAND',	10,	'',	0,	0,	0,	0,	'ISL',	0,	0),
(14,	'Italien',	'IT',	3,	'ITALY',	10,	'',	0,	0,	0,	0,	'ITA',	0,	0),
(15,	'Japan',	'JP',	2,	'JAPAN',	10,	'',	0,	0,	0,	0,	'JPN',	0,	0),
(16,	'Kanada',	'CA',	2,	'CANADA',	10,	'',	0,	0,	0,	0,	'CAN',	0,	0),
(18,	'Luxemburg',	'LU',	3,	'LUXEMBOURG',	10,	'',	0,	0,	0,	0,	'LUX',	0,	0),
(20,	'Namibia',	'NA',	2,	'NAMIBIA',	10,	'',	0,	0,	0,	0,	'NAM',	0,	0),
(21,	'Niederlande',	'NL',	3,	'NETHERLANDS',	10,	'',	0,	0,	0,	0,	'NLD',	0,	0),
(22,	'Norwegen',	'NO',	3,	'NORWAY',	10,	'',	0,	0,	0,	0,	'NOR',	0,	0),
(23,	'Österreich',	'AT',	3,	'AUSTRIA',	1,	'',	0,	0,	0,	0,	'AUT',	0,	0),
(24,	'Portugal',	'PT',	3,	'PORTUGAL',	10,	'',	0,	0,	0,	0,	'PRT',	0,	0),
(25,	'Schweden',	'SE',	3,	'SWEDEN',	10,	'',	0,	0,	0,	0,	'SWE',	0,	0),
(26,	'Schweiz',	'CH',	3,	'SWITZERLAND',	10,	'',	1,	0,	0,	0,	'CHE',	0,	0),
(27,	'Spanien',	'ES',	3,	'SPAIN',	10,	'',	0,	0,	0,	0,	'ESP',	0,	0),
(28,	'USA',	'US',	2,	'USA',	10,	'',	0,	0,	0,	0,	'USA',	0,	0),
(29,	'Liechtenstein',	'LI',	3,	'LIECHTENSTEIN',	10,	'',	0,	0,	0,	0,	'LIE',	0,	0),
(30,	'Polen',	'PL',	3,	'POLAND',	10,	'',	0,	0,	0,	0,	'POL',	0,	0),
(31,	'Ungarn',	'HU',	3,	'HUNGARY',	10,	'',	0,	0,	0,	0,	'HUN',	0,	0),
(32,	'Türkei',	'TR',	2,	'TURKEY',	10,	'',	0,	0,	0,	0,	'TUR',	0,	0),
(33,	'Tschechien',	'CZ',	3,	'CZECH REPUBLIC',	10,	'',	0,	0,	0,	0,	'CZE',	0,	0),
(34,	'Slowakei',	'SK',	3,	'SLOVAKIA',	10,	'',	0,	0,	0,	0,	'SVK',	0,	0),
(35,	'Rum&auml;nien',	'RO',	3,	'ROMANIA',	10,	'',	0,	0,	0,	0,	'ROU',	0,	0),
(36,	'Brasilien',	'BR',	2,	'BRAZIL',	10,	'',	0,	0,	0,	0,	'BRA',	0,	0),
(37,	'Israel',	'IL',	2,	'ISRAEL',	10,	'',	0,	0,	0,	0,	'ISR',	0,	0);

DROP TABLE IF EXISTS `s_core_countries_areas`;
CREATE TABLE `s_core_countries_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_countries_areas` (`id`, `name`, `active`) VALUES
(1,	'deutschland',	1),
(2,	'welt',	1),
(3,	'europa',	1);

DROP TABLE IF EXISTS `s_core_countries_attributes`;
CREATE TABLE `s_core_countries_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countryID` (`countryID`),
  CONSTRAINT `s_core_countries_attributes_ibfk_1` FOREIGN KEY (`countryID`) REFERENCES `s_core_countries` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_countries_states`;
CREATE TABLE `s_core_countries_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryID` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shortcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryID` (`countryID`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_countries_states` (`id`, `countryID`, `name`, `shortcode`, `position`, `active`) VALUES
(2,	2,	'Niedersachsen',	'NI',	0,	1),
(3,	2,	'Nordrhein-Westfalen',	'NW',	0,	1),
(5,	2,	'Baden-Württemberg',	'BW',	0,	1),
(6,	2,	'Bayern',	'BY',	0,	1),
(7,	2,	'Berlin',	'BE',	0,	1),
(8,	2,	'Brandenburg',	'BB',	0,	1),
(9,	2,	'Bremen',	'HB',	0,	1),
(10,	2,	'Hamburg',	'HH',	0,	1),
(11,	2,	'Hessen',	'HE',	0,	1),
(12,	2,	'Mecklenburg-Vorpommern',	'MV',	0,	1),
(13,	2,	'Rheinland-Pfalz',	'RP',	0,	1),
(14,	2,	'Saarland',	'SL',	0,	1),
(15,	2,	'Sachsen',	'SN',	0,	1),
(16,	2,	'Sachsen-Anhalt',	'ST',	0,	1),
(17,	2,	'Schleswig-Holstein',	'SH',	0,	1),
(18,	2,	'Thüringen',	'TH',	0,	1),
(20,	28,	'Alabama',	'AL',	0,	1),
(21,	28,	'Alaska',	'AK',	0,	1),
(22,	28,	'Arizona',	'AZ',	0,	1),
(23,	28,	'Arkansas',	'AR',	0,	1),
(24,	28,	'Kalifornien',	'CA',	0,	1),
(25,	28,	'Colorado',	'CO',	0,	1),
(26,	28,	'Connecticut',	'CT',	0,	1),
(27,	28,	'Delaware',	'DE',	0,	1),
(28,	28,	'Florida',	'FL',	0,	1),
(29,	28,	'Georgia',	'GA',	0,	1),
(30,	28,	'Hawaii',	'HI',	0,	1),
(31,	28,	'Idaho',	'ID',	0,	1),
(32,	28,	'Illinois',	'IL',	0,	1),
(33,	28,	'Indiana',	'IN',	0,	1),
(34,	28,	'Iowa',	'IA',	0,	1),
(35,	28,	'Kansas',	'KS',	0,	1),
(36,	28,	'Kentucky',	'KY',	0,	1),
(37,	28,	'Louisiana',	'LA',	0,	1),
(38,	28,	'Maine',	'ME',	0,	1),
(39,	28,	'Maryland',	'MD',	0,	1),
(40,	28,	'Massachusetts',	'MA',	0,	1),
(41,	28,	'Michigan',	'MI',	0,	1),
(42,	28,	'Minnesota',	'MN',	0,	1),
(43,	28,	'Mississippi',	'MS',	0,	1),
(44,	28,	'Missouri',	'MO',	0,	1),
(45,	28,	'Montana',	'MT',	0,	1),
(46,	28,	'Nebraska',	'NE',	0,	1),
(47,	28,	'Nevada',	'NV',	0,	1),
(48,	28,	'New Hampshire',	'NH',	0,	1),
(49,	28,	'New Jersey',	'NJ',	0,	1),
(50,	28,	'New Mexico',	'NM',	0,	1),
(51,	28,	'New York',	'NY',	0,	1),
(52,	28,	'North Carolina',	'NC',	0,	1),
(53,	28,	'North Dakota',	'ND',	0,	1),
(54,	28,	'Ohio',	'OH',	0,	1),
(55,	28,	'Oklahoma',	'OK',	0,	1),
(56,	28,	'Oregon',	'OR',	0,	1),
(57,	28,	'Pennsylvania',	'PA',	0,	1),
(58,	28,	'Rhode Island',	'RI',	0,	1),
(59,	28,	'South Carolina',	'SC',	0,	1),
(60,	28,	'South Dakota',	'SD',	0,	1),
(61,	28,	'Tennessee',	'TN',	0,	1),
(62,	28,	'Texas',	'TX',	0,	1),
(63,	28,	'Utah',	'UT',	0,	1),
(64,	28,	'Vermont',	'VT',	0,	1),
(65,	28,	'Virginia',	'VA',	0,	1),
(66,	28,	'Washington',	'WA',	0,	1),
(67,	28,	'West Virginia',	'WV',	0,	1),
(68,	28,	'Wisconsin',	'WI',	0,	1),
(69,	28,	'Wyoming',	'WY',	0,	1);

DROP TABLE IF EXISTS `s_core_countries_states_attributes`;
CREATE TABLE `s_core_countries_states_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stateID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stateID` (`stateID`),
  CONSTRAINT `s_core_countries_states_attributes_ibfk_1` FOREIGN KEY (`stateID`) REFERENCES `s_core_countries_states` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_currencies`;
CREATE TABLE `s_core_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `standard` int(1) NOT NULL,
  `factor` double NOT NULL,
  `templatechar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `symbol_position` int(11) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_currencies` (`id`, `currency`, `name`, `standard`, `factor`, `templatechar`, `symbol_position`, `position`) VALUES
(1,	'EUR',	'Euro',	1,	1,	'&euro;',	0,	0);

DROP TABLE IF EXISTS `s_core_customergroups`;
CREATE TABLE `s_core_customergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupkey` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `tax` int(1) NOT NULL DEFAULT '0',
  `taxinput` int(1) NOT NULL,
  `mode` int(11) NOT NULL,
  `discount` double NOT NULL,
  `minimumorder` double NOT NULL,
  `minimumordersurcharge` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupkey` (`groupkey`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_customergroups` (`id`, `groupkey`, `description`, `tax`, `taxinput`, `mode`, `discount`, `minimumorder`, `minimumordersurcharge`) VALUES
(1,	'EK',	'Shopkunden',	1,	1,	0,	0,	0,	0),
(2,	'H',	'Händler',	1,	0,	0,	0,	0,	0);

DROP TABLE IF EXISTS `s_core_customergroups_attributes`;
CREATE TABLE `s_core_customergroups_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerGroupID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customerGroupID` (`customerGroupID`),
  CONSTRAINT `s_core_customergroups_attributes_ibfk_1` FOREIGN KEY (`customerGroupID`) REFERENCES `s_core_customergroups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_customergroups_discounts`;
CREATE TABLE `s_core_customergroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `basketdiscount` double NOT NULL,
  `basketdiscountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`basketdiscountstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_customerpricegroups`;
CREATE TABLE `s_core_customerpricegroups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `netto` int(1) unsigned NOT NULL,
  `active` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_detail_states`;
CREATE TABLE `s_core_detail_states` (
  `id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_detail_states` (`id`, `description`, `position`, `mail`) VALUES
(0,	'Offen',	1,	0),
(1,	'In Bearbeitung',	2,	0),
(2,	'Storniert',	3,	0),
(3,	'Abgeschlossen',	4,	0);

DROP TABLE IF EXISTS `s_core_documents`;
CREATE TABLE `s_core_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numbers` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `top` int(11) NOT NULL,
  `bottom` int(11) NOT NULL,
  `pagebreak` int(11) NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_documents` (`id`, `name`, `template`, `numbers`, `left`, `right`, `top`, `bottom`, `pagebreak`, `key`) VALUES
(1,	'Rechnung',	'index.tpl',	'doc_0',	25,	10,	20,	20,	10,	'invoice'),
(2,	'Lieferschein',	'index_ls.tpl',	'doc_1',	25,	10,	20,	20,	10,	'delivery_note'),
(3,	'Gutschrift',	'index_gs.tpl',	'doc_2',	25,	10,	20,	20,	10,	'credit'),
(4,	'Stornorechnung',	'index_sr.tpl',	'doc_3',	25,	10,	20,	20,	10,	'cancellation');

DROP TABLE IF EXISTS `s_core_documents_box`;
CREATE TABLE `s_core_documents_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) NOT NULL,
  `name` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `style` longtext COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=180 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_documents_box` (`id`, `documentID`, `name`, `style`, `value`) VALUES
(1,	1,	'Body',	'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;',	''),
(2,	1,	'Logo',	'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;',	'<p><img src=\"http://assets.shopware.com/demoshop-logo/logo--tablet.png\" alt=\"Demoshop\" /></p>'),
(3,	1,	'Header_Recipient',	'',	''),
(4,	1,	'Header',	'height: 60mm;',	''),
(5,	1,	'Header_Sender',	'',	'<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(6,	1,	'Header_Box_Left',	'width: 120mm;\r\nheight:60mm;\r\nfloat:left;',	''),
(7,	1,	'Header_Box_Right',	'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;',	'<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(8,	1,	'Header_Box_Bottom',	'font-size:14px;\r\nheight: 10mm;',	''),
(9,	1,	'Content',	'height: 65mm;\r\nwidth: 170mm;',	''),
(10,	1,	'Td',	'white-space:nowrap;\r\npadding: 5px 0;',	''),
(11,	1,	'Td_Name',	'white-space:normal;',	''),
(12,	1,	'Td_Line',	'border-bottom: 1px solid #999;\r\nheight: 0px;',	''),
(13,	1,	'Td_Head',	'border-bottom:1px solid #000;',	''),
(14,	1,	'Footer',	'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;',	'<table style=\"vertical-align: top;\" width=\"100%\" border=\"0\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Demo GmbH</span></p>\r\n<p><span style=\"font-size: xx-small;\">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style=\"font-size: xx-small;\">Musterstadt</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Bankverbindung</span></p>\r\n<p><span style=\"font-size: xx-small;\">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style=\"font-size: xx-small;\">aaaa<br /></span></td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">AGB<br /></span></p>\r\n<p><span style=\"font-size: xx-small;\">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style=\"font-size: xx-small;\">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(15,	1,	'Content_Amount',	'margin-left:90mm;',	''),
(16,	1,	'Content_Info',	'',	'<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(68,	2,	'Body',	'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;',	''),
(69,	2,	'Logo',	'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;',	'<p><img src=\"http://assets.shopware.com/demoshop-logo/logo--tablet.png\" alt=\"Demoshop\" /></p>'),
(70,	2,	'Header_Recipient',	'',	''),
(71,	2,	'Header',	'height: 60mm;',	''),
(72,	2,	'Header_Sender',	'',	'<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(73,	2,	'Header_Box_Left',	'width: 120mm;\r\nheight:60mm;\r\nfloat:left;',	''),
(74,	2,	'Header_Box_Right',	'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;',	'<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(75,	2,	'Header_Box_Bottom',	'font-size:14px;\r\nheight: 10mm;',	''),
(76,	2,	'Content',	'height: 65mm;\r\nwidth: 170mm;',	''),
(77,	2,	'Td',	'white-space:nowrap;\r\npadding: 5px 0;',	''),
(78,	2,	'Td_Name',	'white-space:normal;',	''),
(79,	2,	'Td_Line',	'border-bottom: 1px solid #999;\r\nheight: 0px;',	''),
(80,	2,	'Td_Head',	'border-bottom:1px solid #000;',	''),
(81,	2,	'Footer',	'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;',	'<table style=\"vertical-align: top;\" width=\"100%\" border=\"0\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Demo GmbH</span></p>\r\n<p><span style=\"font-size: xx-small;\">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style=\"font-size: xx-small;\">Musterstadt</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Bankverbindung</span></p>\r\n<p><span style=\"font-size: xx-small;\">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style=\"font-size: xx-small;\">aaaa<br /></span></td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">AGB<br /></span></p>\r\n<p><span style=\"font-size: xx-small;\">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style=\"font-size: xx-small;\">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(82,	2,	'Content_Amount',	'margin-left:90mm;',	''),
(83,	2,	'Content_Info',	'',	'<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(84,	3,	'Body',	'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;',	''),
(85,	3,	'Logo',	'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;',	'<p><img src=\"http://assets.shopware.com/demoshop-logo/logo--tablet.png\" alt=\"Demoshop\" /></p>'),
(86,	3,	'Header_Recipient',	'',	''),
(87,	3,	'Header',	'height: 60mm;',	''),
(88,	3,	'Header_Sender',	'',	'<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(89,	3,	'Header_Box_Left',	'width: 120mm;\r\nheight:60mm;\r\nfloat:left;',	''),
(90,	3,	'Header_Box_Right',	'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;',	'<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(91,	3,	'Header_Box_Bottom',	'font-size:14px;\r\nheight: 10mm;',	''),
(92,	3,	'Content',	'height: 65mm;\r\nwidth: 170mm;',	''),
(93,	3,	'Td',	'white-space:nowrap;\r\npadding: 5px 0;',	''),
(94,	3,	'Td_Name',	'white-space:normal;',	''),
(95,	3,	'Td_Line',	'border-bottom: 1px solid #999;\r\nheight: 0px;',	''),
(96,	3,	'Td_Head',	'border-bottom:1px solid #000;',	''),
(97,	3,	'Footer',	'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;',	'<table style=\"vertical-align: top;\" width=\"100%\" border=\"0\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Demo GmbH</span></p>\r\n<p><span style=\"font-size: xx-small;\">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style=\"font-size: xx-small;\">Musterstadt</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Bankverbindung</span></p>\r\n<p><span style=\"font-size: xx-small;\">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style=\"font-size: xx-small;\">aaaa<br /></span></td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">AGB<br /></span></p>\r\n<p><span style=\"font-size: xx-small;\">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style=\"font-size: xx-small;\">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(98,	3,	'Content_Amount',	'margin-left:90mm;',	''),
(99,	3,	'Content_Info',	'',	'<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(100,	4,	'Body',	'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;',	''),
(101,	4,	'Logo',	'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;',	'<p><img src=\"http://assets.shopware.com/demoshop-logo/logo--tablet.png\" alt=\"Demoshop\" /></p>'),
(102,	4,	'Header_Recipient',	'',	''),
(103,	4,	'Header',	'height: 60mm;',	''),
(104,	4,	'Header_Sender',	'',	'<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(105,	4,	'Header_Box_Left',	'width: 120mm;\r\nheight:60mm;\r\nfloat:left;',	''),
(106,	4,	'Header_Box_Right',	'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;',	'<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(107,	4,	'Header_Box_Bottom',	'font-size:14px;\r\nheight: 10mm;',	''),
(108,	4,	'Content',	'height: 65mm;\r\nwidth: 170mm;',	''),
(109,	4,	'Td',	'white-space:nowrap;\r\npadding: 5px 0;',	''),
(110,	4,	'Td_Name',	'white-space:normal;',	''),
(111,	4,	'Td_Line',	'border-bottom: 1px solid #999;\r\nheight: 0px;',	''),
(112,	4,	'Td_Head',	'border-bottom:1px solid #000;',	''),
(113,	4,	'Footer',	'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;',	'<table style=\"vertical-align: top;\" width=\"100%\" border=\"0\">\r\n<tbody>\r\n<tr valign=\"top\">\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Demo GmbH</span></p>\r\n<p><span style=\"font-size: xx-small;\">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style=\"font-size: xx-small;\">Musterstadt</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Bankverbindung</span></p>\r\n<p><span style=\"font-size: xx-small;\">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style=\"font-size: xx-small;\">aaaa<br /></span></td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">AGB<br /></span></p>\r\n<p><span style=\"font-size: xx-small;\">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style=\"width: 25%;\">\r\n<p><span style=\"font-size: xx-small;\">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style=\"font-size: xx-small;\">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(114,	4,	'Content_Amount',	'margin-left:90mm;',	''),
(115,	4,	'Content_Info',	'',	'<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>');

DROP TABLE IF EXISTS `s_core_engine_elements`;
CREATE TABLE `s_core_engine_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) unsigned NOT NULL DEFAULT '0',
  `domname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `default` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `store` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `required` int(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `variantable` int(1) unsigned NOT NULL,
  `help` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `translatable` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `databasefield` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_engine_elements` (`id`, `groupID`, `domname`, `default`, `type`, `store`, `label`, `required`, `position`, `name`, `layout`, `variantable`, `help`, `translatable`) VALUES
(22,	7,	'attr[3]',	'',	'textarea',	NULL,	'Kommentar',	0,	3,	'attr3',	'',	0,	'Optionaler Kommentar',	1),
(33,	7,	'attr[1]',	'',	'text',	NULL,	'Freitext-1',	0,	1,	'attr1',	'w200',	1,	'Freitext zur Anzeige auf der Detailseite',	1),
(34,	7,	'attr[2]',	'',	'text',	NULL,	'Freitext-2',	0,	2,	'attr2',	'w200',	1,	'Freitext zur Anzeige auf der Detailseite',	1);

DROP TABLE IF EXISTS `s_core_engine_groups`;
CREATE TABLE `s_core_engine_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `variantable` int(1) unsigned NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_engine_groups` (`id`, `name`, `label`, `layout`, `variantable`, `position`) VALUES
(1,	'basic',	'Stammdaten',	'column',	1,	1),
(2,	'description',	'Beschreibung',	NULL,	0,	2),
(3,	'advanced',	'Einstellungen',	'column',	1,	5),
(7,	'additional',	'Zusatzfelder',	NULL,	1,	7),
(8,	'reference_price',	'Grundpreisberechnung',	NULL,	0,	4),
(10,	'price',	'Preise und Kundengruppen',	NULL,	0,	3),
(11,	'property',	'Eigenschaften',	NULL,	0,	6);

DROP TABLE IF EXISTS `s_core_licenses`;
CREATE TABLE `s_core_licenses` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `license` text COLLATE utf8_unicode_ci NOT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `notation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` int(11) unsigned NOT NULL,
  `source` int(11) unsigned NOT NULL,
  `added` date NOT NULL,
  `creation` date DEFAULT NULL,
  `expiration` date DEFAULT NULL,
  `active` int(1) NOT NULL,
  `plugin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_locales`;
CREATE TABLE `s_core_locales` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `territory` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`)
) ENGINE=InnoDB AUTO_INCREMENT=256 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_locales` (`id`, `locale`, `language`, `territory`) VALUES
(1,	'de_DE',	'Deutsch',	'Deutschland'),
(2,	'en_GB',	'Englisch',	'Vereinigtes Königreich'),
(3,	'aa_DJ',	'Afar',	'Dschibuti'),
(4,	'aa_ER',	'Afar',	'Eritrea'),
(5,	'aa_ET',	'Afar',	'Äthiopien'),
(6,	'af_NA',	'Afrikaans',	'Namibia'),
(7,	'af_ZA',	'Afrikaans',	'Südafrika'),
(8,	'ak_GH',	'Akan',	'Ghana'),
(9,	'am_ET',	'Amharisch',	'Äthiopien'),
(10,	'ar_AE',	'Arabisch',	'Vereinigte Arabische Emirate'),
(11,	'ar_BH',	'Arabisch',	'Bahrain'),
(12,	'ar_DZ',	'Arabisch',	'Algerien'),
(13,	'ar_EG',	'Arabisch',	'Ägypten'),
(14,	'ar_IQ',	'Arabisch',	'Irak'),
(15,	'ar_JO',	'Arabisch',	'Jordanien'),
(16,	'ar_KW',	'Arabisch',	'Kuwait'),
(17,	'ar_LB',	'Arabisch',	'Libanon'),
(18,	'ar_LY',	'Arabisch',	'Libyen'),
(19,	'ar_MA',	'Arabisch',	'Marokko'),
(20,	'ar_OM',	'Arabisch',	'Oman'),
(21,	'ar_QA',	'Arabisch',	'Katar'),
(22,	'ar_SA',	'Arabisch',	'Saudi-Arabien'),
(23,	'ar_SD',	'Arabisch',	'Sudan'),
(24,	'ar_SY',	'Arabisch',	'Syrien'),
(25,	'ar_TN',	'Arabisch',	'Tunesien'),
(26,	'ar_YE',	'Arabisch',	'Jemen'),
(27,	'as_IN',	'Assamesisch',	'Indien'),
(28,	'az_AZ',	'Aserbaidschanisch',	'Aserbaidschan'),
(29,	'be_BY',	'Weißrussisch',	'Belarus'),
(30,	'bg_BG',	'Bulgarisch',	'Bulgarien'),
(31,	'bn_BD',	'Bengalisch',	'Bangladesch'),
(32,	'bn_IN',	'Bengalisch',	'Indien'),
(33,	'bo_CN',	'Tibetisch',	'China'),
(34,	'bo_IN',	'Tibetisch',	'Indien'),
(35,	'bs_BA',	'Bosnisch',	'Bosnien und Herzegowina'),
(36,	'byn_ER',	'Blin',	'Eritrea'),
(37,	'ca_ES',	'Katalanisch',	'Spanien'),
(38,	'cch_NG',	'Atsam',	'Nigeria'),
(39,	'cs_CZ',	'Tschechisch',	'Tschechische Republik'),
(40,	'cy_GB',	'Walisisch',	'Vereinigtes Königreich'),
(41,	'da_DK',	'Dänisch',	'Dänemark'),
(42,	'de_AT',	'Deutsch',	'Österreich'),
(43,	'de_BE',	'Deutsch',	'Belgien'),
(44,	'de_CH',	'Deutsch',	'Schweiz'),
(45,	'de_LI',	'Deutsch',	'Liechtenstein'),
(46,	'de_LU',	'Deutsch',	'Luxemburg'),
(47,	'dv_MV',	'Maledivisch',	'Malediven'),
(48,	'dz_BT',	'Bhutanisch',	'Bhutan'),
(49,	'ee_GH',	'Ewe-Sprache',	'Ghana'),
(50,	'ee_TG',	'Ewe-Sprache',	'Togo'),
(51,	'el_CY',	'Griechisch',	'Zypern'),
(52,	'el_GR',	'Griechisch',	'Griechenland'),
(53,	'en_AS',	'Englisch',	'Amerikanisch-Samoa'),
(54,	'en_AU',	'Englisch',	'Australien'),
(55,	'en_BE',	'Englisch',	'Belgien'),
(56,	'en_BW',	'Englisch',	'Botsuana'),
(57,	'en_BZ',	'Englisch',	'Belize'),
(58,	'en_CA',	'Englisch',	'Kanada'),
(59,	'en_GU',	'Englisch',	'Guam'),
(60,	'en_HK',	'Englisch',	'Sonderverwaltungszone Hongkong'),
(61,	'en_IE',	'Englisch',	'Irland'),
(62,	'en_IN',	'Englisch',	'Indien'),
(63,	'en_JM',	'Englisch',	'Jamaika'),
(64,	'en_MH',	'Englisch',	'Marshallinseln'),
(65,	'en_MP',	'Englisch',	'Nördliche Marianen'),
(66,	'en_MT',	'Englisch',	'Malta'),
(67,	'en_NA',	'Englisch',	'Namibia'),
(68,	'en_NZ',	'Englisch',	'Neuseeland'),
(69,	'en_PH',	'Englisch',	'Philippinen'),
(70,	'en_PK',	'Englisch',	'Pakistan'),
(71,	'en_SG',	'Englisch',	'Singapur'),
(72,	'en_TT',	'Englisch',	'Trinidad und Tobago'),
(73,	'en_UM',	'Englisch',	'Amerikanisch-Ozeanien'),
(74,	'en_US',	'Englisch',	'Vereinigte Staaten'),
(75,	'en_VI',	'Englisch',	'Amerikanische Jungferninseln'),
(76,	'en_ZA',	'Englisch',	'Südafrika'),
(77,	'en_ZW',	'Englisch',	'Simbabwe'),
(78,	'es_AR',	'Spanisch',	'Argentinien'),
(79,	'es_BO',	'Spanisch',	'Bolivien'),
(80,	'es_CL',	'Spanisch',	'Chile'),
(81,	'es_CO',	'Spanisch',	'Kolumbien'),
(82,	'es_CR',	'Spanisch',	'Costa Rica'),
(83,	'es_DO',	'Spanisch',	'Dominikanische Republik'),
(84,	'es_EC',	'Spanisch',	'Ecuador'),
(85,	'es_ES',	'Spanisch',	'Spanien'),
(86,	'es_GT',	'Spanisch',	'Guatemala'),
(87,	'es_HN',	'Spanisch',	'Honduras'),
(88,	'es_MX',	'Spanisch',	'Mexiko'),
(89,	'es_NI',	'Spanisch',	'Nicaragua'),
(90,	'es_PA',	'Spanisch',	'Panama'),
(91,	'es_PE',	'Spanisch',	'Peru'),
(92,	'es_PR',	'Spanisch',	'Puerto Rico'),
(93,	'es_PY',	'Spanisch',	'Paraguay'),
(94,	'es_SV',	'Spanisch',	'El Salvador'),
(95,	'es_US',	'Spanisch',	'Vereinigte Staaten'),
(96,	'es_UY',	'Spanisch',	'Uruguay'),
(97,	'es_VE',	'Spanisch',	'Venezuela'),
(98,	'et_EE',	'Estnisch',	'Estland'),
(99,	'eu_ES',	'Baskisch',	'Spanien'),
(100,	'fa_AF',	'Persisch',	'Afghanistan'),
(101,	'fa_IR',	'Persisch',	'Iran'),
(102,	'fi_FI',	'Finnisch',	'Finnland'),
(103,	'fil_PH',	'Filipino',	'Philippinen'),
(104,	'fo_FO',	'Färöisch',	'Färöer'),
(105,	'fr_BE',	'Französisch',	'Belgien'),
(106,	'fr_CA',	'Französisch',	'Kanada'),
(107,	'fr_CH',	'Französisch',	'Schweiz'),
(108,	'fr_FR',	'Französisch',	'Frankreich'),
(109,	'fr_LU',	'Französisch',	'Luxemburg'),
(110,	'fr_MC',	'Französisch',	'Monaco'),
(111,	'fr_SN',	'Französisch',	'Senegal'),
(112,	'fur_IT',	'Friulisch',	'Italien'),
(113,	'ga_IE',	'Irisch',	'Irland'),
(114,	'gaa_GH',	'Ga-Sprache',	'Ghana'),
(115,	'gez_ER',	'Geez',	'Eritrea'),
(116,	'gez_ET',	'Geez',	'Äthiopien'),
(117,	'gl_ES',	'Galizisch',	'Spanien'),
(118,	'gsw_CH',	'Schweizerdeutsch',	'Schweiz'),
(119,	'gu_IN',	'Gujarati',	'Indien'),
(120,	'gv_GB',	'Manx',	'Vereinigtes Königreich'),
(121,	'ha_GH',	'Hausa',	'Ghana'),
(122,	'ha_NE',	'Hausa',	'Niger'),
(123,	'ha_NG',	'Hausa',	'Nigeria'),
(124,	'ha_SD',	'Hausa',	'Sudan'),
(125,	'haw_US',	'Hawaiisch',	'Vereinigte Staaten'),
(126,	'he_IL',	'Hebräisch',	'Israel'),
(127,	'hi_IN',	'Hindi',	'Indien'),
(128,	'hr_HR',	'Kroatisch',	'Kroatien'),
(129,	'hu_HU',	'Ungarisch',	'Ungarn'),
(130,	'hy_AM',	'Armenisch',	'Armenien'),
(131,	'id_ID',	'Indonesisch',	'Indonesien'),
(132,	'ig_NG',	'Igbo-Sprache',	'Nigeria'),
(133,	'ii_CN',	'Sichuan Yi',	'China'),
(134,	'is_IS',	'Isländisch',	'Island'),
(135,	'it_CH',	'Italienisch',	'Schweiz'),
(136,	'it_IT',	'Italienisch',	'Italien'),
(137,	'ja_JP',	'Japanisch',	'Japan'),
(138,	'ka_GE',	'Georgisch',	'Georgien'),
(139,	'kaj_NG',	'Jju',	'Nigeria'),
(140,	'kam_KE',	'Kamba',	'Kenia'),
(141,	'kcg_NG',	'Tyap',	'Nigeria'),
(142,	'kfo_CI',	'Koro',	'Côte d?Ivoire'),
(143,	'kk_KZ',	'Kasachisch',	'Kasachstan'),
(144,	'kl_GL',	'Grönländisch',	'Grönland'),
(145,	'km_KH',	'Kambodschanisch',	'Kambodscha'),
(146,	'kn_IN',	'Kannada',	'Indien'),
(147,	'ko_KR',	'Koreanisch',	'Republik Korea'),
(148,	'kok_IN',	'Konkani',	'Indien'),
(149,	'kpe_GN',	'Kpelle-Sprache',	'Guinea'),
(150,	'kpe_LR',	'Kpelle-Sprache',	'Liberia'),
(151,	'ku_IQ',	'Kurdisch',	'Irak'),
(152,	'ku_IR',	'Kurdisch',	'Iran'),
(153,	'ku_SY',	'Kurdisch',	'Syrien'),
(154,	'ku_TR',	'Kurdisch',	'Türkei'),
(155,	'kw_GB',	'Kornisch',	'Vereinigtes Königreich'),
(156,	'ky_KG',	'Kirgisisch',	'Kirgisistan'),
(157,	'ln_CD',	'Lingala',	'Demokratische Republik Kongo'),
(158,	'ln_CG',	'Lingala',	'Kongo'),
(159,	'lo_LA',	'Laotisch',	'Laos'),
(160,	'lt_LT',	'Litauisch',	'Litauen'),
(161,	'lv_LV',	'Lettisch',	'Lettland'),
(162,	'mk_MK',	'Mazedonisch',	'Mazedonien'),
(163,	'ml_IN',	'Malayalam',	'Indien'),
(164,	'mn_CN',	'Mongolisch',	'China'),
(165,	'mn_MN',	'Mongolisch',	'Mongolei'),
(166,	'mr_IN',	'Marathi',	'Indien'),
(167,	'ms_BN',	'Malaiisch',	'Brunei Darussalam'),
(168,	'ms_MY',	'Malaiisch',	'Malaysia'),
(169,	'mt_MT',	'Maltesisch',	'Malta'),
(170,	'my_MM',	'Birmanisch',	'Myanmar'),
(171,	'nb_NO',	'Norwegisch Bokmål',	'Norwegen'),
(172,	'nds_DE',	'Niederdeutsch',	'Deutschland'),
(173,	'ne_IN',	'Nepalesisch',	'Indien'),
(174,	'ne_NP',	'Nepalesisch',	'Nepal'),
(175,	'nl_BE',	'Niederländisch',	'Belgien'),
(176,	'nl_NL',	'Niederländisch',	'Niederlande'),
(177,	'nn_NO',	'Norwegisch Nynorsk',	'Norwegen'),
(178,	'nr_ZA',	'Süd-Ndebele-Sprache',	'Südafrika'),
(179,	'nso_ZA',	'Nord-Sotho-Sprache',	'Südafrika'),
(180,	'ny_MW',	'Nyanja-Sprache',	'Malawi'),
(181,	'oc_FR',	'Okzitanisch',	'Frankreich'),
(182,	'om_ET',	'Oromo',	'Äthiopien'),
(183,	'om_KE',	'Oromo',	'Kenia'),
(184,	'or_IN',	'Orija',	'Indien'),
(185,	'pa_IN',	'Pandschabisch',	'Indien'),
(186,	'pa_PK',	'Pandschabisch',	'Pakistan'),
(187,	'pl_PL',	'Polnisch',	'Polen'),
(188,	'ps_AF',	'Paschtu',	'Afghanistan'),
(189,	'pt_BR',	'Portugiesisch',	'Brasilien'),
(190,	'pt_PT',	'Portugiesisch',	'Portugal'),
(191,	'ro_MD',	'Rumänisch',	'Republik Moldau'),
(192,	'ro_RO',	'Rumänisch',	'Rumänien'),
(193,	'ru_RU',	'Russisch',	'Russische Föderation'),
(194,	'ru_UA',	'Russisch',	'Ukraine'),
(195,	'rw_RW',	'Ruandisch',	'Ruanda'),
(196,	'sa_IN',	'Sanskrit',	'Indien'),
(197,	'se_FI',	'Nord-Samisch',	'Finnland'),
(198,	'se_NO',	'Nord-Samisch',	'Norwegen'),
(199,	'sh_BA',	'Serbo-Kroatisch',	'Bosnien und Herzegowina'),
(200,	'sh_CS',	'Serbo-Kroatisch',	'Serbien und Montenegro'),
(201,	'sh_YU',	'Serbo-Kroatisch',	''),
(202,	'si_LK',	'Singhalesisch',	'Sri Lanka'),
(203,	'sid_ET',	'Sidamo',	'Äthiopien'),
(204,	'sk_SK',	'Slowakisch',	'Slowakei'),
(205,	'sl_SI',	'Slowenisch',	'Slowenien'),
(206,	'so_DJ',	'Somali',	'Dschibuti'),
(207,	'so_ET',	'Somali',	'Äthiopien'),
(208,	'so_KE',	'Somali',	'Kenia'),
(209,	'so_SO',	'Somali',	'Somalia'),
(210,	'sq_AL',	'Albanisch',	'Albanien'),
(211,	'sr_BA',	'Serbisch',	'Bosnien und Herzegowina'),
(212,	'sr_CS',	'Serbisch',	'Serbien und Montenegro'),
(213,	'sr_ME',	'Serbisch',	'Montenegro'),
(214,	'sr_RS',	'Serbisch',	'Serbien'),
(215,	'sr_YU',	'Serbisch',	''),
(216,	'ss_SZ',	'Swazi',	'Swasiland'),
(217,	'ss_ZA',	'Swazi',	'Südafrika'),
(218,	'st_LS',	'Süd-Sotho-Sprache',	'Lesotho'),
(219,	'st_ZA',	'Süd-Sotho-Sprache',	'Südafrika'),
(220,	'sv_FI',	'Schwedisch',	'Finnland'),
(221,	'sv_SE',	'Schwedisch',	'Schweden'),
(222,	'sw_KE',	'Suaheli',	'Kenia'),
(223,	'sw_TZ',	'Suaheli',	'Tansania'),
(224,	'syr_SY',	'Syrisch',	'Syrien'),
(225,	'ta_IN',	'Tamilisch',	'Indien'),
(226,	'te_IN',	'Telugu',	'Indien'),
(227,	'tg_TJ',	'Tadschikisch',	'Tadschikistan'),
(228,	'th_TH',	'Thailändisch',	'Thailand'),
(229,	'ti_ER',	'Tigrinja',	'Eritrea'),
(230,	'ti_ET',	'Tigrinja',	'Äthiopien'),
(231,	'tig_ER',	'Tigre',	'Eritrea'),
(232,	'tn_ZA',	'Tswana-Sprache',	'Südafrika'),
(233,	'to_TO',	'Tongaisch',	'Tonga'),
(234,	'tr_TR',	'Türkisch',	'Türkei'),
(236,	'ts_ZA',	'Tsonga',	'Südafrika'),
(237,	'tt_RU',	'Tatarisch',	'Russische Föderation'),
(238,	'ug_CN',	'Uigurisch',	'China'),
(239,	'uk_UA',	'Ukrainisch',	'Ukraine'),
(240,	'ur_IN',	'Urdu',	'Indien'),
(241,	'ur_PK',	'Urdu',	'Pakistan'),
(242,	'uz_AF',	'Usbekisch',	'Afghanistan'),
(243,	'uz_UZ',	'Usbekisch',	'Usbekistan'),
(244,	've_ZA',	'Venda-Sprache',	'Südafrika'),
(245,	'vi_VN',	'Vietnamesisch',	'Vietnam'),
(246,	'wal_ET',	'Walamo-Sprache',	'Äthiopien'),
(247,	'wo_SN',	'Wolof',	'Senegal'),
(248,	'xh_ZA',	'Xhosa',	'Südafrika'),
(249,	'yo_NG',	'Yoruba',	'Nigeria'),
(250,	'zh_CN',	'Chinesisch',	'China'),
(251,	'zh_HK',	'Chinesisch',	'Sonderverwaltungszone Hongkong'),
(252,	'zh_MO',	'Chinesisch',	'Sonderverwaltungszone Macao'),
(253,	'zh_SG',	'Chinesisch',	'Singapur'),
(254,	'zh_TW',	'Chinesisch',	'Taiwan'),
(255,	'zu_ZA',	'Zulu',	'Südafrika');

DROP TABLE IF EXISTS `s_core_log`;
CREATE TABLE `s_core_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `user` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ip_address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `user_agent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_log` (`id`, `type`, `key`, `text`, `date`, `user`, `ip_address`, `user_agent`, `value4`) VALUES
(1,	'backend',	'Versandkosten Verwaltung',	'Einstellungen wurden erfolgreich gespeichert.',	'2012-08-28 10:38:26',	'Administrator',	'217.86.247.178',	'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:14.0) Gecko/20100101 Firefox/14.0.1 FirePHP/0.7.1',	'');

DROP TABLE IF EXISTS `s_core_menu`;
CREATE TABLE `s_core_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `onclick` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `pluginID` int(11) unsigned DEFAULT NULL,
  `controller` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shortcut` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`parent`)
) ENGINE=InnoDB AUTO_INCREMENT=132 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_menu` (`id`, `parent`, `name`, `onclick`, `class`, `position`, `active`, `pluginID`, `controller`, `shortcut`, `action`) VALUES
(1,	NULL,	'Artikel',	NULL,	'ico package_green article--main',	0,	1,	NULL,	'Article',	NULL,	NULL),
(2,	1,	'Anlegen',	'',	'sprite-inbox--plus article--add-article',	-3,	1,	NULL,	'Article',	'STRG + ALT + N',	'Detail'),
(4,	1,	'Kategorien',	'',	'sprite-blue-folders-stack article--categories',	0,	1,	NULL,	'Category',	NULL,	'Index'),
(6,	1,	'Hersteller',	NULL,	'sprite-truck article--manufacturers',	2,	1,	NULL,	'Supplier',	NULL,	'Index'),
(7,	NULL,	'Inhalte',	NULL,	'ico2 note03 contents--main',	0,	1,	NULL,	'Content',	NULL,	NULL),
(8,	30,	'Banner',	NULL,	'sprite-image-medium marketing--banner',	0,	1,	NULL,	'Banner',	NULL,	'Index'),
(9,	30,	'Einkaufswelten',	'',	'sprite-pin marketing--shopping-worlds',	1,	1,	NULL,	'Emotion',	NULL,	'Index'),
(10,	30,	'Gutscheine',	NULL,	'sprite-mail-open-image marketing--vouchers',	3,	1,	NULL,	'Voucher',	NULL,	'Index'),
(11,	30,	'Pr&auml;mienartikel',	NULL,	'sprite-star marketing--premium-items',	2,	1,	NULL,	'Premium',	NULL,	'Index'),
(12,	30,	'Produktexporte',	NULL,	'sprite-folder-export marketing--product-exports',	5,	1,	NULL,	'ProductFeed',	NULL,	'Index'),
(15,	7,	'Shopseiten',	NULL,	'sprite-documents contents--shopsites',	0,	1,	NULL,	'Site',	NULL,	'Index'),
(20,	NULL,	'Kunden',	NULL,	'ico customer customers--main',	0,	1,	NULL,	'Customer',	NULL,	NULL),
(21,	20,	'Kundenliste',	NULL,	'sprite-ui-scroll-pane-detail customers--customer-list',	0,	1,	NULL,	'Customer',	'STRG + ALT + K',	'Index'),
(22,	20,	'Bestellungen',	NULL,	'sprite-sticky-notes-pin customers--orders',	0,	1,	NULL,	'Order',	'STRG + ALT + B',	'Index'),
(23,	NULL,	'Einstellungen',	NULL,	'ico2 wrench_screwdriver settings--main',	0,	1,	NULL,	'ConfigurationMenu',	NULL,	NULL),
(25,	23,	'Benutzerverwaltung',	NULL,	'sprite-user-silhouette settings--user-management',	-2,	1,	NULL,	'UserManager',	NULL,	'Index'),
(26,	23,	'Versandkosten',	NULL,	'sprite-envelope--arrow settings--delivery-charges',	0,	1,	NULL,	'Shipping',	NULL,	'Index'),
(27,	23,	'Zahlungsarten',	NULL,	'sprite-credit-cards settings--payment-methods',	0,	1,	NULL,	'Payment',	NULL,	'Index'),
(28,	23,	'E-Mail-Vorlagen',	NULL,	'sprite-mail--pencil settings--mail-presets',	0,	1,	NULL,	'Mail',	NULL,	'Index'),
(29,	23,	'Performance',	NULL,	'sprite-bin-full settings--performance',	-5,	1,	NULL,	'Performance',	NULL,	'Index'),
(30,	NULL,	'Marketing',	NULL,	'ico2 chart_bar01 marketing--main',	0,	1,	NULL,	'Marketing',	NULL,	NULL),
(31,	69,	'Übersicht',	NULL,	'sprite-report-paper marketing--analyses--overview',	-5,	1,	NULL,	'Overview',	NULL,	'Index'),
(32,	69,	'Statistiken / Diagramme',	NULL,	'sprite-chart',	-4,	1,	NULL,	'Analytics',	NULL,	'Index'),
(40,	NULL,	'',	NULL,	'ico question_frame shopware-help-menu',	999,	1,	NULL,	NULL,	NULL,	NULL),
(41,	114,	'Onlinehilfe aufrufen',	'window.open(\'http://www.shopware.de/wiki\',\'Shopware\',\'width=800,height=550,scrollbars=yes\')',	'sprite-lifebuoy misc--help--online-help',	0,	1,	NULL,	'Onlinehelp',	NULL,	NULL),
(44,	40,	'Über Shopware',	'createShopwareVersionMessage()',	'sprite-shopware-logo misc--about-shopware',	2,	1,	NULL,	'AboutShopware',	NULL,	'Index'),
(50,	1,	'Bewertungen',	NULL,	'sprite-balloon article--ratings',	3,	1,	NULL,	'Vote',	NULL,	'Index'),
(56,	30,	'Partnerprogramm',	'',	'sprite-xfn-colleague marketing--partner-program',	6,	1,	NULL,	'Partner',	NULL,	'Index'),
(57,	7,	'Formulare',	NULL,	'sprite-application-form contents--forms',	2,	1,	NULL,	'Form',	NULL,	'Index'),
(58,	30,	'Newsletter',	'',	'sprite-paper-plane marketing--newsletters',	7,	1,	NULL,	'NewsletterManager',	NULL,	'Index'),
(59,	69,	'Abbruch-Analyse',	'',	'sprite-chart-down-color marketing--analyses--abort-analyses',	0,	1,	NULL,	'CanceledOrder',	NULL,	'Index'),
(62,	23,	'Riskmanagement',	'',	'sprite-funnel--exclamation',	0,	1,	NULL,	'RiskManagement',	NULL,	'Index'),
(63,	23,	'Systeminfo',	NULL,	'sprite-blueprint settings--system-info',	-3,	1,	40,	'Systeminfo',	NULL,	'Index'),
(64,	7,	'Medienverwaltung',	NULL,	'sprite-inbox-image contents--media-manager',	4,	1,	NULL,	'MediaManager',	NULL,	'Index'),
(65,	20,	'Zahlungen',	NULL,	'sprite-credit-cards settings--payment-methods',	0,	1,	NULL,	'Payments',	NULL,	NULL),
(66,	1,	'Übersicht',	'',	'sprite-ui-scroll-pane-list article--overview',	-2,	1,	NULL,	'ArticleList',	'STRG + ALT + O',	'Index'),
(68,	23,	'Logfile',	'',	'sprite-cards-stack settings--logfile',	-2,	1,	NULL,	'Log',	NULL,	'Index'),
(69,	30,	'Auswertungen',	NULL,	'sprite-chart marketing--analyses',	-1,	1,	NULL,	'AnalysisMenu',	NULL,	NULL),
(72,	1,	'Eigenschaften',	'',	'sprite-property-blue article--properties',	0,	1,	NULL,	'Property',	NULL,	'Index'),
(75,	20,	'Anlegen',	'',	'sprite-user--plus customers--add-customer',	-1,	1,	NULL,	'Customer',	NULL,	'Detail'),
(84,	69,	'E-Mail Benachrichtigung',	'',	'sprite-mail-forward',	4,	1,	NULL,	'Notification',	NULL,	'Index'),
(85,	7,	'Blog',	'',	'sprite-application-blog contents--blog',	1,	1,	NULL,	'Blog',	NULL,	'Index'),
(88,	114,	'Zum Forum',	'window.open(\'https://forum.shopware.com\')',	'sprite-balloons-box misc--help--board',	-1,	1,	NULL,	'Forum',	NULL,	NULL),
(91,	29,	'Shopcache leeren',	NULL,	'sprite-edit-shade settings--performance--cache',	1,	1,	NULL,	'Performance',	'STRG + ALT + X',	'Config'),
(107,	23,	'Textbausteine',	NULL,	'sprite-edit-shade settings--snippets',	0,	1,	NULL,	'Snippet',	NULL,	'Index'),
(109,	40,	'Tastaturk&uuml;rzel',	'createKeyNavOverlay()',	'sprite-keyboard-command misc--shortcuts',	1,	1,	NULL,	'ShortCutMenu',	'STRG + ALT + H',	'Index'),
(110,	23,	'Grundeinstellungen',	NULL,	'sprite-wrench-screwdriver settings--basic-settings',	-5,	1,	NULL,	'Config',	NULL,	'Index'),
(114,	40,	'Hilfe',	NULL,	'sprite-lifebuoy misc--help',	0,	1,	NULL,	'HelpMenu',	NULL,	NULL),
(115,	40,	'Feedback senden',	NULL,	'sprite-briefcase--arrow',	0,	1,	NULL,	'Feedback',	NULL,	'Index'),
(118,	40,	'SwagUpdate',	NULL,	'sprite-arrow-continue-090 misc--software-update',	0,	1,	55,	'SwagUpdate',	NULL,	'Index'),
(119,	23,	'Theme Manager',	NULL,	'sprite-application-icon-large settings--theme-manager',	0,	1,	NULL,	'Theme',	NULL,	'Index'),
(120,	23,	'Plugin Manager',	NULL,	'sprite-application-block settings--plugin-manager',	0,	1,	56,	'PluginManager',	'STRG + ALT + P',	'Index'),
(121,	23,	'Premium Plugins',	NULL,	'sprite-star settings--premium-plugins',	0,	1,	56,	'PluginManager',	NULL,	'PremiumPlugins'),
(122,	1,	'Product Streams',	'',	'sprite-product-streams',	50,	1,	NULL,	'ProductStream',	'',	'index'),
(123,	23,	'Freitextfeld-Verwaltung',	'',	'sprite-attributes',	-1,	1,	NULL,	'Attributes',	NULL,	'Index'),
(124,	29,	'Performance',	NULL,	'sprite-bin-full settings--performance',	2,	1,	NULL,	'Performance',	NULL,	'Index'),
(125,	NULL,	'Connect',	NULL,	'shopware-connect',	0,	1,	NULL,	NULL,	NULL,	NULL),
(127,	7,	'Import/Export',	NULL,	'sprite-arrow-circle-double-135 contents--import-export',	3,	1,	NULL,	'PluginManager',	NULL,	'ImportExport'),
(128,	20,	'Customer Streams',	'',	'sprite-customer-streams',	20,	1,	NULL,	'Customer',	NULL,	'customer_stream'),
(129,	69,	'Shopware BI',	NULL,	'sprite-benchmark',	1,	1,	NULL,	'Benchmark',	NULL,	NULL),
(130,	129,	'Einstellungen',	NULL,	'sprite-wrench-screwdriver settings--basic-settings',	1,	0,	NULL,	'Benchmark',	NULL,	'Settings'),
(131,	129,	'Übersicht',	NULL,	'sprite-report-paper marketing--analyses--overview',	0,	1,	NULL,	'Benchmark',	NULL,	'index');

DROP TABLE IF EXISTS `s_core_optin`;
CREATE TABLE `s_core_optin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `datum` datetime NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_paymentmeans`;
CREATE TABLE `s_core_paymentmeans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `table` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `hide` int(1) NOT NULL,
  `additionaldescription` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `debit_percent` double NOT NULL DEFAULT '0',
  `surcharge` double NOT NULL DEFAULT '0',
  `surchargestring` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `esdactive` int(1) NOT NULL,
  `embediframe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hideprospect` int(1) NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  `mobile_inactive` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_paymentmeans` (`id`, `name`, `description`, `template`, `class`, `table`, `hide`, `additionaldescription`, `debit_percent`, `surcharge`, `surchargestring`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`, `mobile_inactive`) VALUES
(2,	'debit',	'Lastschrift',	'debit.tpl',	'debit.php',	'',	0,	'Zusatztext',	0,	0,	'',	4,	0,	0,	'',	0,	NULL,	NULL,	NULL,	0),
(3,	'cash',	'Nachnahme',	'cash.tpl',	'cash.php',	'',	0,	'(zzgl. 2,00 Euro Nachnahmegebühren)',	0,	0,	'',	2,	0,	0,	'',	0,	NULL,	NULL,	NULL,	0),
(4,	'invoice',	'Rechnung',	'invoice.tpl',	'invoice.php',	'',	0,	'Sie zahlen einfach und bequem auf Rechnung. Shopware bietet z.B. auch die Möglichkeit, Rechnung automatisiert erst ab der 2. Bestellung für Kunden zur Verfügung zu stellen, um Zahlungsausfälle zu vermeiden.',	0,	0,	'',	3,	0,	1,	'',	0,	NULL,	NULL,	NULL,	0),
(5,	'prepayment',	'Vorkasse',	'prepayment.tpl',	'prepayment.php',	'',	0,	'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.',	0,	0,	'',	1,	1,	0,	'',	0,	NULL,	NULL,	NULL,	0),
(6,	'sepa',	'SEPA',	'sepa.tpl',	'sepa',	'',	0,	'SEPA debit',	0,	0,	'',	5,	0,	0,	'',	0,	'',	NULL,	1,	0);

DROP TABLE IF EXISTS `s_core_paymentmeans_attributes`;
CREATE TABLE `s_core_paymentmeans_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentmeanID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paymentmeanID` (`paymentmeanID`),
  CONSTRAINT `s_core_paymentmeans_attributes_ibfk_1` FOREIGN KEY (`paymentmeanID`) REFERENCES `s_core_paymentmeans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_paymentmeans_countries`;
CREATE TABLE `s_core_paymentmeans_countries` (
  `paymentID` int(11) unsigned NOT NULL,
  `countryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_paymentmeans_subshops`;
CREATE TABLE `s_core_paymentmeans_subshops` (
  `paymentID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`subshopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_payment_data`;
CREATE TABLE `s_core_payment_data` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_mean_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `use_billing_data` int(1) DEFAULT NULL,
  `bankname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bic` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iban` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_code` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_holder` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payment_mean_id_2` (`payment_mean_id`,`user_id`),
  KEY `payment_mean_id` (`payment_mean_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_payment_instance`;
CREATE TABLE `s_core_payment_instance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `payment_mean_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(15) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_number` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `account_holder` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bank_code` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `bic` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `iban` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `amount` decimal(20,4) DEFAULT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_mean_id` (`payment_mean_id`),
  KEY `payment_mean_id_2` (`payment_mean_id`),
  KEY `order_id` (`order_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_plugins`;
CREATE TABLE `s_core_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
  `translations` text COLLATE utf8_unicode_ci,
  `description_long` mediumtext COLLATE utf8_unicode_ci,
  `active` int(1) unsigned NOT NULL,
  `added` datetime NOT NULL,
  `installation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `refresh_date` datetime DEFAULT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `copyright` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `license` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `version` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `support` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changes` mediumtext COLLATE utf8_unicode_ci,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `store_version` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `store_date` datetime DEFAULT NULL,
  `capability_update` int(1) NOT NULL,
  `capability_install` int(1) NOT NULL,
  `capability_enable` int(1) NOT NULL,
  `update_source` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `update_version` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `capability_secure_uninstall` int(1) NOT NULL DEFAULT '0',
  `in_safe_mode` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `translations`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`, `capability_secure_uninstall`, `in_safe_mode`) VALUES
(2,	'Core',	'ErrorHandler',	'ErrorHandler',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(7,	'Core',	'Cron',	'Cron',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(8,	'Core',	'Router',	'Router',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(9,	'Core',	'CronBirthday',	'CronBirthday',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(10,	'Core',	'System',	'System',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(11,	'Core',	'ViewportForward',	'ViewportForward',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(12,	'Core',	'Shop',	'Shop',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(13,	'Core',	'PostFilter',	'PostFilter',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(14,	'Core',	'CronRating',	'CronRating',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(15,	'Core',	'ControllerBase',	'ControllerBase',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(16,	'Core',	'CronStock',	'CronStock',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(19,	'Frontend',	'RouterRewrite',	'RouterRewrite',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(21,	'Frontend',	'Facebook',	'Facebook',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(22,	'Frontend',	'Seo',	'Seo',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(29,	'Frontend',	'AdvancedMenu',	'Erweitertes Menü',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(31,	'Frontend',	'Statistics',	'Statistics',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(33,	'Frontend',	'Notification',	'Notification',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(34,	'Frontend',	'TagCloud',	'TagCloud',	'Default',	'',	NULL,	'',	0,	'2012-08-28 00:00:00',	NULL,	NULL,	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(35,	'Frontend',	'InputFilter',	'InputFilter',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(36,	'Backend',	'Auth',	'Auth',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(37,	'Backend',	'Menu',	'Menu',	'Default',	'',	NULL,	'',	1,	'2012-08-28 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2010, shopware AG',	'',	'1',	'http://www.shopware.de/wiki/',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(40,	'Backend',	'Check',	'Systeminfo',	'Default',	'',	NULL,	'',	1,	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	'2010-10-18 00:00:00',	NULL,	'shopware AG',	'Copyright © 2011, shopware AG',	'',	'1.0.0',	'http://wiki.shopware.de',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(43,	'Backend',	'Locale',	'Locale',	'Default',	'',	NULL,	'',	1,	'2012-08-27 22:28:53',	'2012-08-27 22:28:53',	'2012-08-27 22:28:53',	NULL,	'shopware AG',	'Copyright &copy; 2011, shopware AG',	'',	'1.0.0',	'http://wiki.shopware.de',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(44,	'Core',	'RestApi',	'RestApi',	'Default',	'',	NULL,	'',	1,	'2012-07-13 12:03:13',	'2012-07-13 12:03:36',	'2012-07-13 12:03:36',	NULL,	'shopware AG',	'Copyright © 2012, shopware AG',	'',	'1.0.0',	'http://wiki.shopware.de',	'',	'http://www.shopware.de/',	NULL,	NULL,	0,	0,	0,	NULL,	NULL,	0,	0),
(49,	'Core',	'PasswordEncoder',	'PasswordEncoder',	'Default',	NULL,	NULL,	NULL,	1,	'2013-04-16 12:13:54',	'2013-04-16 14:07:23',	'2013-04-16 14:07:23',	'2013-04-16 14:07:23',	'shopware AG',	'Copyright © 2013, shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	0,	0,	NULL,	NULL,	0,	0),
(50,	'Core',	'MarketingAggregate',	'Shopware Marketing Aggregat Funktionen',	'Default',	NULL,	NULL,	NULL,	1,	'2013-04-30 14:19:13',	'2013-04-30 14:26:48',	'2013-04-30 14:26:48',	'2013-04-30 14:26:51',	'shopware AG',	'Copyright © 2012, shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	'http://www.shopware.de/',	NULL,	NULL,	1,	1,	1,	NULL,	NULL,	0,	0),
(51,	'Core',	'RebuildIndex',	'Shopware Such- und SEO-Index',	'Default',	NULL,	NULL,	NULL,	1,	'2013-05-19 10:53:24',	'2013-05-21 13:28:04',	'2013-05-21 13:28:04',	'2013-05-21 13:28:07',	'shopware AG',	'Copyright © 2012, shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	'http://www.shopware.de/',	NULL,	NULL,	1,	1,	1,	NULL,	NULL,	0,	0),
(52,	'Core',	'HttpCache',	'Frontendcache (HttpCache)',	'Default',	NULL,	NULL,	NULL,	0,	'2013-05-27 15:57:59',	'2013-05-27 15:58:09',	'2013-05-27 15:58:09',	'2013-05-27 15:58:10',	'shopware AG',	'Copyright © 2012, shopware AG',	NULL,	'1.1.0',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	0,	1,	NULL,	NULL,	0,	0),
(53,	'Core',	'PaymentMethods',	'Payment Methods',	'Default',	'Shopware Payment Methods handling. This plugin is required to handle payment methods, and should not be deactivated',	NULL,	NULL,	1,	'2013-10-30 08:12:22',	'2013-10-30 08:13:26',	'2013-10-30 08:13:26',	'2013-10-30 08:13:34',	'shopware AG',	'Copyright © 2013, shopware AG',	NULL,	'1.0.1',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	0,	1,	NULL,	NULL,	0,	0),
(54,	'Core',	'Debug',	'Debug',	'Default',	NULL,	NULL,	NULL,	0,	'2014-01-17 09:19:05',	NULL,	NULL,	'2014-01-17 09:19:07',	'shopware AG',	'Copyright © shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	1,	NULL,	NULL,	0,	0),
(55,	'Backend',	'SwagUpdate',	'Shopware Auto Update',	'Default',	NULL,	NULL,	NULL,	1,	'2014-05-06 09:03:01',	'2014-05-06 09:03:06',	'2014-05-06 09:03:06',	'2014-05-06 09:03:09',	'shopware AG',	'Copyright © 2012, shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	1,	NULL,	NULL,	0,	0),
(56,	'Backend',	'PluginManager',	'Plugin Manager',	'Default',	NULL,	NULL,	NULL,	1,	'2014-11-07 11:55:46',	'2014-11-07 11:55:54',	'2014-11-07 11:55:54',	'2014-11-07 11:55:57',	'shopware AG',	'Copyright © 2012, shopware AG',	NULL,	'1.0.0',	NULL,	NULL,	NULL,	NULL,	NULL,	1,	1,	1,	NULL,	NULL,	0,	0);

DROP TABLE IF EXISTS `s_core_plugin_categories`;
CREATE TABLE `s_core_plugin_categories` (
  `id` int(11) NOT NULL,
  `locale` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `name` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_pricegroups`;
CREATE TABLE `s_core_pricegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_pricegroups` (`id`, `description`) VALUES
(1,	'Standard');

DROP TABLE IF EXISTS `s_core_pricegroups_discounts`;
CREATE TABLE `s_core_pricegroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  `discount` double NOT NULL,
  `discountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`customergroupID`,`discountstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_rewrite_urls`;
CREATE TABLE `s_core_rewrite_urls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `main` int(1) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`,`subshopID`),
  KEY `org_path` (`org_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_rulesets`;
CREATE TABLE `s_core_rulesets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentID` int(11) NOT NULL,
  `rule1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rule2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_sessions`;
CREATE TABLE `s_core_sessions` (
  `id` varchar(128) COLLATE utf8_bin NOT NULL,
  `data` mediumblob NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `expiry` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sess_expiry` (`expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `s_core_sessions_backend`;
CREATE TABLE `s_core_sessions_backend` (
  `id` varchar(128) COLLATE utf8_bin NOT NULL,
  `data` mediumblob NOT NULL,
  `modified` int(10) unsigned NOT NULL,
  `expiry` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sess_expiry` (`expiry`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;


DROP TABLE IF EXISTS `s_core_shops`;
CREATE TABLE `s_core_shops` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `main_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_url` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosts` text COLLATE utf8_unicode_ci NOT NULL,
  `secure` int(1) unsigned NOT NULL,
  `template_id` int(11) unsigned DEFAULT NULL,
  `document_template_id` int(11) unsigned DEFAULT NULL,
  `category_id` int(11) unsigned DEFAULT NULL,
  `locale_id` int(11) unsigned DEFAULT NULL,
  `currency_id` int(11) unsigned DEFAULT NULL,
  `customer_group_id` int(11) unsigned DEFAULT NULL,
  `fallback_id` int(11) unsigned DEFAULT NULL,
  `customer_scope` int(1) NOT NULL,
  `default` int(1) unsigned NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `main_id` (`main_id`),
  KEY `host` (`host`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
(1,	NULL,	'Deutsch',	NULL,	0,	NULL,	'',	NULL,	'',	0,	11,	4,	3,	1,	1,	1,	NULL,	0,	1,	1);

DROP TABLE IF EXISTS `s_core_shop_currencies`;
CREATE TABLE `s_core_shop_currencies` (
  `shop_id` int(11) unsigned NOT NULL,
  `currency_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_shop_currencies` (`shop_id`, `currency_id`) VALUES
(1,	1);

DROP TABLE IF EXISTS `s_core_shop_pages`;
CREATE TABLE `s_core_shop_pages` (
  `shop_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_snippets`;
CREATE TABLE `s_core_snippets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  `dirty` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`shopID`,`name`,`localeID`)
) ENGINE=InnoDB AUTO_INCREMENT=2625 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`, `dirty`) VALUES
(2587,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentSepaLabelIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2588,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentSepaLabelIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2589,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentSepaLabelBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2590,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentSepaLabelBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2591,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentSepaLabelBankName',	'Ihre Bank',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2592,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentSepaLabelBankName',	'Name of bank',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2593,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentSepaLabelUseBillingData',	'Rechnungs-Adresse in Mandat übernehmen?',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2594,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentSepaLabelUseBillingData',	'Use billing information for SEPA debit mandate?',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2595,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentSepaInfoFields',	'Die mit einem * markierten Felder sind Pflichtfelder.',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2596,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentSepaInfoFields',	'The fields marked with * are required.',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2597,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailCreditorNumber',	'Gläubiger-Identifikationsnummer:',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2598,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailCreditorNumber',	'Creditor number:',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2599,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailMandateReference',	'Mandatsreferenz: <strong>{$data.orderNumber}</strong>',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2600,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailMandateReference',	'Mandate reference: <strong>{$data.orderNumber}</strong>',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2601,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailDirectDebitMandate',	'SEPA-Lastschriftmandat',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2602,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailDirectDebitMandate',	'SEPA direct debit mandate',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2603,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailBody',	'Ich ermächtige den {$config.sepaCompany}, Zahlungen von meinem Konto mittels Lastschrift einzuziehen. Zugleich weise ich mein Kreditinstitut an, die von dem {$config.sepaCompany} auf mein Konto gezogenen Lastschriften einzulösen.</p><p> Hinweis: Ich kann innerhalb von acht Wochen, beginnend mit dem Belastungsdatum, die Erstattung des belasteten Betrages verlangen. Es gelten dabei die mit meinem Kreditinstitut vereinbarten Bedingungen.',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2604,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailBody',	'I hereby authorize payments to be made from my account to {$config.sepaCompany} via direct debit. At the same time, I instruct my financial institution to honor the debits drawn from my account.</p><p>Note: I may request reimbursement for the debited amount up to eight weeks following the date of the transfer, in accordance with preexisting terms and conditions set by my bank.',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2605,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailName',	'Vorname und Name (Kontoinhaber)',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2606,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailName',	'Account holder\'s name',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2607,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailAddress',	'Straße und Hausnummer',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2608,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailAddress',	'Address',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2609,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailZip',	'Postleitzahl und Ort',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2610,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailZip',	'Zip code and City',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2611,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailBankName',	'Kreditinstitut',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2612,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailBankName',	'Bank',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2613,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2614,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2615,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2616,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2617,	'frontend/plugins/payment/sepaemail',	1,	1,	'SepaEmailSignature',	'Datum, Ort und Unterschrift',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2618,	'frontend/plugins/payment/sepaemail',	1,	2,	'SepaEmailSignature',	'Signature (including date and location)',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2619,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentDebitLabelIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2620,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentDebitLabelIban',	'IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2621,	'frontend/plugins/payment/sepa',	1,	1,	'PaymentDebitLabelBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2622,	'frontend/plugins/payment/sepa',	1,	2,	'PaymentDebitLabelBic',	'BIC',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2623,	'frontend/plugins/payment/sepa',	1,	1,	'ErrorIBAN',	'Ungültige IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0),
(2624,	'frontend/plugins/payment/sepa',	1,	2,	'ErrorIBAN',	'Invalid IBAN',	'2013-11-01 00:00:00',	'2013-11-01 00:00:00',	0);

DROP TABLE IF EXISTS `s_core_states`;
CREATE TABLE `s_core_states` (
  `id` int(11) NOT NULL,
  `name` varchar(55) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `group` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_states` (`id`, `name`, `description`, `position`, `group`, `mail`) VALUES
(-1,	'cancelled',	'Abgebrochen',	25,	'state',	0),
(0,	'open',	'Offen',	1,	'state',	1),
(1,	'in_process',	'In Bearbeitung (Wartet)',	2,	'state',	1),
(2,	'completed',	'Komplett abgeschlossen',	3,	'state',	0),
(3,	'partially_completed',	'Teilweise abgeschlossen',	4,	'state',	0),
(4,	'cancelled_rejected',	'Storniert / Abgelehnt',	5,	'state',	1),
(5,	'ready_for_delivery',	'Zur Lieferung bereit',	6,	'state',	1),
(6,	'partially_delivered',	'Teilweise ausgeliefert',	7,	'state',	1),
(7,	'completely_delivered',	'Komplett ausgeliefert',	8,	'state',	1),
(8,	'clarification_required',	'Klärung notwendig',	9,	'state',	1),
(9,	'partially_invoiced',	'Teilweise in Rechnung gestellt',	1,	'payment',	0),
(10,	'completely_invoiced',	'Komplett in Rechnung gestellt',	2,	'payment',	0),
(11,	'partially_paid',	'Teilweise bezahlt',	3,	'payment',	0),
(12,	'completely_paid',	'Komplett bezahlt',	4,	'payment',	0),
(13,	'1st_reminder',	'1. Mahnung',	5,	'payment',	0),
(14,	'2nd_reminder',	'2. Mahnung',	6,	'payment',	0),
(15,	'3rd_reminder',	'3. Mahnung',	7,	'payment',	0),
(16,	'encashment',	'Inkasso',	8,	'payment',	0),
(17,	'open',	'Offen',	0,	'payment',	0),
(18,	'reserved',	'Reserviert',	9,	'payment',	0),
(19,	'delayed',	'Verzoegert',	10,	'payment',	0),
(20,	're_crediting',	'Wiedergutschrift',	11,	'payment',	0),
(21,	'review_necessary',	'Überprüfung notwendig',	12,	'payment',	0),
(30,	'no_credit_approved',	'Es wurde kein Kredit genehmigt.',	30,	'payment',	1),
(31,	'the_credit_has_been_preliminarily_accepted',	'Der Kredit wurde vorlaeufig akzeptiert.',	31,	'payment',	1),
(32,	'the_credit_has_been_accepted',	'Der Kredit wurde genehmigt.',	32,	'payment',	1),
(33,	'the_payment_has_been_ordered',	'Die Zahlung wurde angewiesen.',	33,	'payment',	1),
(34,	'a_time_extension_has_been_registered',	'Es wurde eine Zeitverlaengerung eingetragen.',	34,	'payment',	1),
(35,	'the_process_has_been_cancelled',	'Vorgang wurde abgebrochen.',	35,	'payment',	1);

DROP TABLE IF EXISTS `s_core_subscribes`;
CREATE TABLE `s_core_subscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `listener` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribe` (`subscribe`,`type`,`listener`),
  KEY `plugin_namespace_init_storage` (`type`,`subscribe`,`position`),
  KEY `pluginID` (`pluginID`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(1,	'Enlight_Bootstrap_InitResource_Auth',	0,	'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAuth',	36,	0),
(2,	'Enlight_Controller_Action_PreDispatch',	0,	'Shopware_Plugins_Backend_Auth_Bootstrap::onPreDispatchBackend',	36,	0),
(3,	'Enlight_Bootstrap_InitResource_Menu',	0,	'Shopware_Plugins_Backend_Menu_Bootstrap::onInitResourceMenu',	37,	0),
(5,	'Enlight_Controller_Action_PostDispatch',	0,	'Shopware_Plugins_Core_ControllerBase_Bootstrap::onPostDispatch',	15,	100),
(6,	'Enlight_Controller_Front_StartDispatch',	0,	'Shopware_Plugins_Core_ErrorHandler_Bootstrap::onStartDispatch',	2,	0),
(9,	'Enlight_Plugins_ViewRenderer_FilterRender',	0,	'Shopware_Plugins_Core_PostFilter_Bootstrap::onFilterRender',	13,	0),
(10,	'Enlight_Controller_Front_RouteStartup',	0,	'Shopware_Plugins_Core_Router_Bootstrap::onRouteStartup',	8,	0),
(11,	'Enlight_Controller_Front_RouteShutdown',	0,	'Shopware_Plugins_Core_Router_Bootstrap::onRouteShutdown',	8,	0),
(12,	'Enlight_Controller_Router_FilterAssembleParams',	0,	'Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble',	8,	0),
(13,	'Enlight_Controller_Router_FilterUrl',	0,	'Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl',	8,	0),
(14,	'Enlight_Controller_Router_Assemble',	0,	'Shopware_Plugins_Core_Router_Bootstrap::onAssemble',	8,	100),
(17,	'Enlight_Bootstrap_InitResource_System',	0,	'Shopware_Plugins_Core_System_Bootstrap::onInitResourceSystem',	10,	0),
(18,	'Enlight_Bootstrap_InitResource_Modules',	0,	'Shopware_Plugins_Core_System_Bootstrap::onInitResourceModules',	10,	0),
(21,	'Enlight_Controller_Front_PreDispatch',	0,	'Shopware_Plugins_Core_ViewportForward_Bootstrap::onPreDispatch',	11,	10),
(25,	'Enlight_Plugins_ViewRenderer_FilterRender',	0,	'Shopware_Plugins_Frontend_Seo_Bootstrap::onFilterRender',	22,	0),
(26,	'Enlight_Controller_Action_PostDispatch',	0,	'Shopware_Plugins_Frontend_Seo_Bootstrap::onPostDispatch',	22,	0),
(30,	'Enlight_Controller_Front_StartDispatch',	0,	'Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onStartDispatch',	19,	0),
(37,	'Enlight_Controller_Action_PostDispatch',	0,	'Shopware_Plugins_Frontend_LastArticles_Bootstrap::onPostDispatch',	23,	0),
(38,	'Enlight_Controller_Front_RouteShutdown',	0,	'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onRouteShutdown',	35,	0),
(41,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_Check',	0,	'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend',	40,	0),
(52,	'Enlight_Bootstrap_InitResource_BackendSession',	0,	'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession',	36,	0),
(56,	'Enlight_Controller_Front_DispatchLoopStartup',	0,	'Shopware_Plugins_Core_RestApi_Bootstrap::onDispatchLoopStartup',	44,	0),
(57,	'Enlight_Controller_Front_PreDispatch',	0,	'Shopware_Plugins_Core_RestApi_Bootstrap::onFrontPreDispatch',	44,	0),
(58,	'Enlight_Bootstrap_InitResource_Auth',	0,	'Shopware_Plugins_Core_RestApi_Bootstrap::onInitResourceAuth',	44,	0),
(69,	'Enlight_Bootstrap_InitResource_PasswordEncoder',	0,	'Shopware_Plugins_Core_PasswordEncoder_Bootstrap::onInitResourcePasswordEncoder',	49,	0),
(70,	'Shopware_Components_Password_Manager_AddEncoder',	0,	'Shopware_Plugins_Core_PasswordEncoder_Bootstrap::onAddEncoder',	49,	0),
(71,	'Shopware_Modules_Order_SaveOrder_ProcessDetails',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::incrementTopSeller',	50,	0),
(72,	'Shopware_Modules_Articles_GetArticleCharts',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::afterTopSellerSelected',	50,	0),
(73,	'Enlight_Bootstrap_InitResource_TopSeller',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::initTopSellerResource',	50,	0),
(74,	'Enlight_Controller_Action_Backend_Config_InitTopSeller',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::initTopSeller',	50,	0),
(75,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_TopSeller',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::getTopSellerBackendController',	50,	0),
(76,	'Shopware_CronJob_RefreshTopSeller',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::refreshTopSeller',	50,	0),
(77,	'Shopware\\Models\\Article\\Article::postUpdate',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::refreshArticle',	50,	0),
(78,	'Shopware\\Models\\Article\\Article::postPersist',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::refreshArticle',	50,	0),
(79,	'Shopware_Modules_Order_SaveOrder_ProcessDetails',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::addNewAlsoBought',	50,	0),
(80,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_AlsoBought',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::getAlsoBoughtBackendController',	50,	0),
(81,	'Enlight_Bootstrap_InitResource_AlsoBought',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::initAlsoBoughtResource',	50,	0),
(82,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_SimilarShown',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::getSimilarShownBackendController',	50,	0),
(83,	'Enlight_Bootstrap_InitResource_SimilarShown',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::initSimilarShownResource',	50,	0),
(85,	'Shopware_Plugins_LastArticles_ResetLastArticles',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::afterSimilarShownArticlesReset',	50,	0),
(86,	'Shopware_Modules_Articles_Before_SetLastArticle',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::beforeSetLastArticle',	50,	0),
(87,	'Shopware_CronJob_RefreshSimilarShown',	0,	'Shopware_Plugins_Core_MarketingAggregate_Bootstrap::refreshSimilarShown',	50,	0),
(88,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_Seo',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::getSeoBackendController',	51,	0),
(89,	'Enlight_Bootstrap_InitResource_SeoIndex',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::initSeoIndexResource',	51,	0),
(90,	'Enlight_Controller_Front_DispatchLoopShutdown',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::onAfterSendResponse',	51,	0),
(91,	'Shopware_CronJob_RefreshSeoIndex',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::onRefreshSeoIndex',	51,	0),
(92,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_SearchIndex',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::getSearchIndexBackendController',	51,	0),
(93,	'Shopware_CronJob_RefreshSearchIndex',	0,	'Shopware_Plugins_Core_RebuildIndex_Bootstrap::refreshSearchIndex',	51,	0),
(94,	'Enlight_Controller_Action_PreDispatch',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPreDispatch',	52,	0),
(95,	'Shopware\\Models\\Article\\Article::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(96,	'Shopware\\Models\\Category\\Category::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(97,	'Shopware\\Models\\Banner\\Banner::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(98,	'Shopware\\Models\\Article\\Article::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(99,	'Shopware\\Models\\Category\\Category::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(100,	'Shopware\\Models\\Banner\\Banner::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(102,	'Shopware_CronJob_ClearHttpCache',	0,	'Shopware_Plugins_Core_HttpCacheBootstrap::onClearHttpCache',	52,	0),
(103,	'Shopware_Plugins_HttpCache_InvalidateCacheId',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onInvalidateCacheId',	52,	0),
(105,	'Shopware\\Models\\Blog\\Blog::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(106,	'Shopware\\Models\\Blog\\Blog::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(107,	'Shopware_Modules_Admin_InitiatePaymentClass_AddClass',	0,	'Shopware_Plugins_Core_PaymentMethods_Bootstrap::addPaymentClass',	53,	0),
(108,	'Enlight_Controller_Action_PostDispatchSecure',	0,	'Shopware_Plugins_Core_PaymentMethods_Bootstrap::addPaths',	53,	0),
(109,	'Enlight_Controller_Action_PostDispatchSecure_Backend_Order',	0,	'Shopware_Plugins_Core_PaymentMethods_Bootstrap::onBackendOrderPostDispatch',	53,	0),
(110,	'Shopware_Plugins_HttpCache_ClearCache',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onClearCache',	52,	0),
(111,	'Enlight_Controller_Action_PostDispatchSecure_Backend_Customer',	0,	'Shopware_Plugins_Core_PaymentMethods_Bootstrap::onBackendCustomerPostDispatch',	53,	0),
(112,	'Enlight_Controller_Action_PostDispatch_Backend_Index',	0,	'Shopware_Plugins_Backend_SwagUpdate_Bootstrap::onBackendIndexPostDispatch',	55,	0),
(113,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_SwagUpdate',	0,	'Shopware_Plugins_Backend_SwagUpdate_Bootstrap::onGetSwagUpdateControllerPath',	55,	0),
(114,	'Enlight_Bootstrap_InitResource_SwagUpdateUpdateCheck',	0,	'Shopware_Plugins_Backend_SwagUpdate_Bootstrap::onInitUpdateCheck',	55,	0),
(115,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager',	0,	'Shopware_Plugins_Backend_PluginManager_Bootstrap::getDefaultControllerPath',	56,	0),
(116,	'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginInstaller',	0,	'Shopware_Plugins_Backend_PluginManager_Bootstrap::getDefaultControllerPath',	56,	0),
(117,	'Shopware\\Models\\Emotion\\Emotion::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(118,	'Shopware\\Models\\Emotion\\Emotion::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(119,	'Enlight_Controller_Front_DispatchLoopShutdown',	0,	'Shopware_Plugins_Core_System_Bootstrap::onDispatchLoopShutdown',	10,	0),
(120,	'Shopware\\Models\\Article\\Price::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(121,	'Shopware\\Models\\Article\\Price::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(122,	'Shopware\\Models\\Article\\Detail::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(123,	'Shopware\\Models\\Article\\Detail::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(124,	'Enlight_Bootstrap_InitResource_http_cache.cache_control',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::initCacheControl',	52,	0),
(125,	'Enlight_Bootstrap_InitResource_http_cache.cache_id_collector',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::initCacheIdCollector',	52,	0),
(126,	'Shopware\\Models\\Site\\Site::postPersist',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0),
(127,	'Shopware\\Models\\Site\\Site::postUpdate',	0,	'Shopware_Plugins_Core_HttpCache_Bootstrap::onPostPersist',	52,	0);

DROP TABLE IF EXISTS `s_core_tax`;
CREATE TABLE `s_core_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax` decimal(10,2) NOT NULL,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `tax` (`tax`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_tax` (`id`, `tax`, `description`) VALUES
(1,	19.00,	'19%'),
(4,	7.00,	'7 %');

DROP TABLE IF EXISTS `s_core_tax_rules`;
CREATE TABLE `s_core_tax_rules` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `areaID` int(11) unsigned DEFAULT NULL,
  `countryID` int(11) unsigned DEFAULT NULL,
  `stateID` int(11) unsigned DEFAULT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `customer_groupID` int(11) unsigned NOT NULL,
  `tax` decimal(10,2) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `countryID` (`countryID`),
  KEY `stateID` (`stateID`),
  KEY `areaID` (`areaID`),
  KEY `tax_rate_by_conditions` (`customer_groupID`,`areaID`,`countryID`,`stateID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_templates`;
CREATE TABLE `s_core_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `author` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `license` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `esi` tinyint(1) unsigned NOT NULL,
  `style_support` tinyint(1) unsigned NOT NULL,
  `emotion` tinyint(1) unsigned NOT NULL,
  `version` int(11) unsigned NOT NULL,
  `plugin_id` int(11) unsigned DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`template`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_templates_config_elements`;
CREATE TABLE `s_core_templates_config_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `default_value` text COLLATE utf8_unicode_ci,
  `selection` text COLLATE utf8_unicode_ci,
  `field_label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `support_text` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `allow_blank` int(1) NOT NULL DEFAULT '1',
  `container_id` int(11) NOT NULL,
  `attributes` text COLLATE utf8_unicode_ci,
  `less_compatible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `template_id_name` (`template_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_templates_config_layout`;
CREATE TABLE `s_core_templates_config_layout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL,
  `template_id` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attributes` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_templates_config_set`;
CREATE TABLE `s_core_templates_config_set` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `element_values` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_templates_config_values`;
CREATE TABLE `s_core_templates_config_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `element_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id_shop_id` (`element_id`,`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_core_theme_settings`;
CREATE TABLE `s_core_theme_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `compiler_force` int(1) NOT NULL,
  `compiler_create_source_map` int(1) NOT NULL,
  `compiler_compress_css` int(1) NOT NULL,
  `compiler_compress_js` int(1) NOT NULL,
  `force_reload_snippets` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_theme_settings` (`id`, `compiler_force`, `compiler_create_source_map`, `compiler_compress_css`, `compiler_compress_js`, `force_reload_snippets`) VALUES
(2,	0,	0,	1,	1,	0);

DROP TABLE IF EXISTS `s_core_translations`;
CREATE TABLE `s_core_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `objectdata` longtext COLLATE utf8_unicode_ci NOT NULL,
  `objectkey` int(11) unsigned NOT NULL,
  `objectlanguage` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `dirty` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `objecttype` (`objecttype`,`objectkey`,`objectlanguage`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`, `dirty`) VALUES
(1,	'config_mails',	'a:4:{s:8:\"fromMail\";s:18:\"{config name=mail}\";s:8:\"fromName\";s:22:\"{config name=shopName}\";s:7:\"subject\";s:38:\"Documents to your order {$orderNumber}\";s:7:\"content\";s:331:\"{include file=\"string:{config name=emailheaderplain}\"}\n\nHello {$sUser.salutation|salutation} {$sUser.firstname} {$sUser.lastname},\n\nThank you for your order at {config name=shopName}. In the attachement you will find documents about your order as PDF.\nWe wish you a nice day.\n\n{include file=\"string:{config name=emailfooterplain}\"}\";}',	64,	'2',	0),
(2,	'documents',	'a:4:{i:1;a:1:{s:4:\"name\";s:7:\"Invoice\";}i:2;a:1:{s:4:\"name\";s:18:\"Notice of delivery\";}i:3;a:1:{s:4:\"name\";s:6:\"Credit\";}i:4;a:1:{s:4:\"name\";s:12:\"Cancellation\";}}',	1,	'2',	0),
(3,	'custom_facet',	'a:1:{i:0;a:1:{s:5:\"label\";s:9:\"Varianten\";}}',	1,	'1',	0);

DROP TABLE IF EXISTS `s_core_units`;
CREATE TABLE `s_core_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_units` (`id`, `unit`, `description`) VALUES
(1,	'l',	'Liter'),
(2,	'g',	'Gramm'),
(5,	'lfm',	'Laufende(r) Meter'),
(6,	'kg',	'Kilogramm'),
(8,	'Paket(e)',	'Paket(e)'),
(9,	'Stck.',	'Stück');

DROP TABLE IF EXISTS `s_core_widgets`;
CREATE TABLE `s_core_widgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_core_widgets` (`id`, `name`, `label`, `plugin_id`) VALUES
(1,	'swag-sales-widget',	'Umsatz Heute und Gestern',	NULL),
(2,	'swag-upload-widget',	'Drag and Drop Upload',	NULL),
(3,	'swag-visitors-customers-widget',	'Besucher online',	NULL),
(4,	'swag-last-orders-widget',	'Letzte Bestellungen',	NULL),
(5,	'swag-notice-widget',	'Notizzettel',	NULL),
(6,	'swag-merchant-widget',	'Händlerfreischaltung',	NULL),
(7,	'swag-shopware-news-widget',	'shopware News',	NULL),
(8,	'swag-bi-base',	NULL,	NULL);

DROP TABLE IF EXISTS `s_core_widget_views`;
CREATE TABLE `s_core_widget_views` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  `column` int(11) unsigned NOT NULL,
  `position` int(11) unsigned NOT NULL,
  `data` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `widget_id` (`widget_id`,`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_crontab`;
CREATE TABLE `s_crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `elementID` int(11) DEFAULT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `next` datetime DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `interval` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  `disable_on_error` tinyint(1) NOT NULL DEFAULT '1',
  `end` datetime DEFAULT NULL,
  `inform_template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inform_mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action` (`action`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `disable_on_error`, `end`, `inform_template`, `inform_mail`, `pluginID`) VALUES
(1,	'Geburtstagsgruß',	'birthday',	NULL,	'',	'2010-10-16 23:42:58',	'2010-10-16 12:26:44',	86400,	1,	1,	'2010-10-16 12:26:44',	'',	'',	NULL),
(2,	'Aufräumen',	'clearing',	NULL,	'',	'2010-10-16 12:34:38',	'2010-10-16 12:34:32',	86400,	1,	1,	'2010-10-16 12:34:32',	'',	'',	NULL),
(3,	'Lagerbestand Warnung',	'article_stock',	NULL,	'',	'2010-10-16 12:34:33',	'2010-10-16 12:34:31',	86400,	1,	1,	'2010-10-16 12:34:32',	'sARTICLESTOCK',	'{$sConfig.sMAIL}',	NULL),
(5,	'Suche',	'search',	NULL,	'',	'2010-10-16 12:34:38',	'2010-10-16 12:34:32',	86400,	1,	1,	'2010-10-16 12:34:32',	'',	'',	NULL),
(6,	'eMail-Benachrichtigung',	'notification',	NULL,	'',	'2010-10-17 00:20:28',	'2010-10-16 12:26:44',	86400,	1,	1,	'2010-10-16 12:26:44',	'',	'',	NULL),
(7,	'Artikelbewertung per eMail',	'article_comment',	NULL,	'',	'2010-10-16 12:35:18',	'2010-10-16 12:34:32',	86400,	1,	1,	'2010-10-16 12:34:32',	'',	'',	NULL),
(8,	'Topseller Refresh',	'RefreshTopSeller',	NULL,	'',	'2013-05-21 14:29:44',	NULL,	86400,	1,	1,	'2013-05-21 14:29:44',	'',	'',	50),
(9,	'Similar shown article refresh',	'RefreshSimilarShown',	NULL,	'',	'2013-05-21 14:29:44',	NULL,	86400,	1,	1,	'2013-05-21 14:29:44',	'',	'',	50),
(10,	'Refresh seo index',	'RefreshSeoIndex',	NULL,	'',	'2013-05-21 13:28:04',	NULL,	86400,	1,	1,	'2013-05-21 13:28:04',	'',	'',	51),
(11,	'Refresh search index',	'RefreshSearchIndex',	NULL,	'',	'2013-05-21 13:28:04',	NULL,	86400,	1,	1,	'2013-05-21 13:28:04',	'',	'',	51),
(12,	'HTTP Cache löschen',	'ClearHttpCache',	NULL,	'',	'2019-12-07 03:00:00',	NULL,	86400,	1,	1,	'2019-12-07 03:00:00',	'',	'',	52),
(13,	'Media Garbage Collector',	'MediaCrawler',	NULL,	'',	'2019-12-06 09:19:53',	NULL,	86400,	0,	1,	'2019-12-06 09:19:53',	'',	'',	NULL),
(14,	'Basket Signature cleanup',	'CleanupSignatures',	NULL,	'',	'2016-10-11 08:34:13',	NULL,	86400,	1,	1,	'2016-10-11 08:34:13',	'',	'',	NULL),
(15,	'Customer Stream refresh',	'RefreshCustomerStreams',	NULL,	'',	'2016-01-01 01:00:00',	NULL,	7200,	1,	0,	'2016-01-01 01:00:01',	'',	'',	NULL),
(16,	'Cancelled baskets cleanup',	'CleanupCancelledBaskets',	NULL,	'',	NULL,	NULL,	86400,	1,	0,	NULL,	'',	'',	0),
(17,	'Guest customer cleanup',	'CleanupGuestCustomers',	NULL,	'',	NULL,	NULL,	86400,	1,	0,	NULL,	'',	'',	0),
(18,	'Opt-In table cleanup',	'OptinCleanup',	NULL,	'',	'2019-12-06 03:00:00',	NULL,	86400,	1,	0,	'2016-01-01 01:00:00',	'',	'',	NULL),
(19,	'Lösche nicht aktivierte Benutzer',	'RegistrationCleanup',	NULL,	'',	'2019-12-06 03:00:00',	NULL,	86400,	1,	0,	'2016-01-01 01:00:00',	'',	'',	NULL),
(20,	'Sitemap generation',	'SitemapGeneration',	NULL,	'',	'2019-12-06 00:00:00',	NULL,	86400,	0,	0,	'2016-01-01 01:00:00',	'',	'',	NULL);

DROP TABLE IF EXISTS `s_customer_search_index`;
CREATE TABLE `s_customer_search_index` (
  `id` int(11) NOT NULL,
  `email` varchar(70) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` int(1) DEFAULT NULL,
  `accountmode` int(11) DEFAULT NULL,
  `firstlogin` date DEFAULT NULL,
  `newsletter` int(1) DEFAULT NULL,
  `shop_id` int(11) DEFAULT NULL,
  `default_billing_address_id` int(11) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `customernumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_group_id` int(11) DEFAULT NULL,
  `customer_group_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `country_id` int(11) DEFAULT NULL,
  `country_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `state_id` int(11) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `count_orders` int(11) DEFAULT NULL,
  `invoice_amount_sum` double DEFAULT NULL,
  `invoice_amount_avg` double DEFAULT NULL,
  `invoice_amount_min` double DEFAULT NULL,
  `invoice_amount_max` double DEFAULT NULL,
  `first_order_time` date DEFAULT NULL,
  `last_order_time` date DEFAULT NULL,
  `has_canceled_orders` int(1) DEFAULT NULL,
  `product_avg` double DEFAULT NULL,
  `ordered_at_weekdays` text COLLATE utf8_unicode_ci,
  `ordered_in_shops` text COLLATE utf8_unicode_ci,
  `ordered_on_devices` text COLLATE utf8_unicode_ci,
  `ordered_with_deliveries` text COLLATE utf8_unicode_ci,
  `ordered_with_payments` text COLLATE utf8_unicode_ci,
  `ordered_products` longtext COLLATE utf8_unicode_ci,
  `ordered_products_of_categories` longtext COLLATE utf8_unicode_ci,
  `ordered_products_of_manufacturer` longtext COLLATE utf8_unicode_ci,
  `index_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_customer_streams`;
CREATE TABLE `s_customer_streams` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `conditions` longtext COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  `freeze_up` datetime DEFAULT NULL,
  `static` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_customer_streams_attributes`;
CREATE TABLE `s_customer_streams_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `streamID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `streamID` (`streamID`),
  CONSTRAINT `s_customer_streams_attributes_ibfk_1` FOREIGN KEY (`streamID`) REFERENCES `s_customer_streams` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_customer_streams_mapping`;
CREATE TABLE `s_customer_streams_mapping` (
  `stream_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  PRIMARY KEY `stream_id` (`stream_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_banners`;
CREATE TABLE `s_emarketing_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `valid_from` datetime DEFAULT '0000-00-00 00:00:00',
  `valid_to` datetime DEFAULT '0000-00-00 00:00:00',
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link_target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `categoryID` int(11) NOT NULL DEFAULT '0',
  `extension` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_banners_attributes`;
CREATE TABLE `s_emarketing_banners_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bannerID` (`bannerID`),
  CONSTRAINT `s_emarketing_banners_attributes_ibfk_1` FOREIGN KEY (`bannerID`) REFERENCES `s_emarketing_banners` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_banners_statistics`;
CREATE TABLE `s_emarketing_banners_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) NOT NULL,
  `display_date` date NOT NULL,
  `clicks` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_date` (`bannerID`,`display_date`),
  KEY `bannerID` (`bannerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_lastarticles`;
CREATE TABLE `s_emarketing_lastarticles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `sessionID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`sessionID`,`shopID`),
  KEY `userID` (`userID`),
  KEY `time` (`time`),
  KEY `sessionID` (`sessionID`),
  KEY `get_last_articles` (`sessionID`,`time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_partner`;
CREATE TABLE `s_emarketing_partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `web` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profil` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `fix` double NOT NULL DEFAULT '0',
  `percent` double NOT NULL DEFAULT '0',
  `cookielifetime` int(11) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `userID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idcode` (`idcode`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_partner_attributes`;
CREATE TABLE `s_emarketing_partner_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partnerID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `partnerID` (`partnerID`),
  CONSTRAINT `FK__s_emarketing_partner` FOREIGN KEY (`partnerID`) REFERENCES `s_emarketing_partner` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_referer`;
CREATE TABLE `s_emarketing_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_tellafriend`;
CREATE TABLE `s_emarketing_tellafriend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `recipient` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sender` int(11) NOT NULL DEFAULT '0',
  `confirmed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_vouchers`;
CREATE TABLE `s_emarketing_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vouchercode` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `numberofunits` int(11) NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `minimumcharge` double NOT NULL DEFAULT '0',
  `shippingfree` int(1) NOT NULL DEFAULT '0',
  `bindtosupplier` int(11) DEFAULT NULL,
  `valid_from` date DEFAULT NULL,
  `valid_to` date DEFAULT NULL,
  `ordercode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modus` int(1) NOT NULL DEFAULT '0',
  `percental` int(1) NOT NULL,
  `numorder` int(11) NOT NULL,
  `customergroup` int(11) DEFAULT NULL,
  `restrictarticles` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `strict` int(1) NOT NULL,
  `subshopID` int(1) DEFAULT NULL,
  `taxconfig` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `customer_stream_ids` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `modus` (`modus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_vouchers_attributes`;
CREATE TABLE `s_emarketing_vouchers_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID` (`voucherID`),
  CONSTRAINT `s_emarketing_vouchers_attributes_ibfk_1` FOREIGN KEY (`voucherID`) REFERENCES `s_emarketing_vouchers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emarketing_voucher_codes`;
CREATE TABLE `s_emarketing_voucher_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cashed` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `voucherID_cashed` (`voucherID`,`cashed`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion`;
CREATE TABLE `s_emotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) DEFAULT NULL,
  `cell_spacing` int(11) NOT NULL,
  `cell_height` int(11) NOT NULL,
  `article_height` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `show_listing` int(1) NOT NULL,
  `is_landingpage` int(1) NOT NULL,
  `seo_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `template_id` int(11) DEFAULT NULL,
  `device` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0,1,2,3,4',
  `fullscreen` int(11) NOT NULL DEFAULT '0',
  `mode` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'masonry',
  `position` int(11) DEFAULT '1',
  `parent_id` int(11) DEFAULT NULL,
  `preview_id` int(11) DEFAULT NULL,
  `preview_secret` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `customer_stream_ids` longtext COLLATE utf8_unicode_ci,
  `replacement` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `preview_id` (`preview_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_attributes`;
CREATE TABLE `s_emotion_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emotionID` (`emotionID`),
  CONSTRAINT `s_emotion_attributes_ibfk_1` FOREIGN KEY (`emotionID`) REFERENCES `s_emotion` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_categories`;
CREATE TABLE `s_emotion_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotion_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_element`;
CREATE TABLE `s_emotion_element` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) NOT NULL,
  `componentID` int(11) NOT NULL,
  `start_row` int(11) NOT NULL,
  `start_col` int(11) NOT NULL,
  `end_row` int(11) NOT NULL,
  `end_col` int(11) NOT NULL,
  `css_class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `get_emotion_elements` (`emotionID`,`start_row`,`start_col`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_element_value`;
CREATE TABLE `s_emotion_element_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) NOT NULL,
  `elementID` int(11) NOT NULL,
  `componentID` int(11) NOT NULL,
  `fieldID` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `emotionID` (`elementID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_element_viewports`;
CREATE TABLE `s_emotion_element_viewports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `elementID` int(11) NOT NULL,
  `emotionID` int(11) NOT NULL,
  `alias` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_row` int(11) NOT NULL,
  `start_col` int(11) NOT NULL,
  `end_row` int(11) NOT NULL,
  `end_col` int(11) NOT NULL,
  `visible` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_presets`;
CREATE TABLE `s_emotion_presets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `premium` tinyint(1) NOT NULL DEFAULT '0',
  `custom` tinyint(1) NOT NULL DEFAULT '1',
  `thumbnail` longtext COLLATE utf8_unicode_ci,
  `preview` longtext COLLATE utf8_unicode_ci,
  `preset_data` longtext COLLATE utf8_unicode_ci NOT NULL,
  `required_plugins` longtext COLLATE utf8_unicode_ci,
  `emotion_translations` text COLLATE utf8_unicode_ci,
  `assets_imported` tinyint(1) NOT NULL DEFAULT '1',
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_preset_translations`;
CREATE TABLE `s_emotion_preset_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `presetID` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'de_DE',
  PRIMARY KEY (`id`),
  UNIQUE KEY `presetID` (`presetID`,`locale`),
  CONSTRAINT `s_emotion_preset_translations_preset_fk` FOREIGN KEY (`presetID`) REFERENCES `s_emotion_presets` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_shops`;
CREATE TABLE `s_emotion_shops` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotion_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_emotion_templates`;
CREATE TABLE `s_emotion_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_emotion_templates` (`id`, `name`, `file`) VALUES
(1,	'Standard',	'index.tpl');

DROP TABLE IF EXISTS `s_es_backend_backlog`;
CREATE TABLE `s_es_backend_backlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity` varchar(500) COLLATE utf8_unicode_ci NOT NULL,
  `entity_id` int(11) NOT NULL,
  `time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_es_backlog`;
CREATE TABLE `s_es_backlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `payload` text COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_export`;
CREATE TABLE `s_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `last_export` datetime NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `show` int(1) NOT NULL DEFAULT '1',
  `count_articles` int(11) NOT NULL,
  `expiry` datetime NOT NULL,
  `interval` int(11) NOT NULL,
  `formatID` int(11) NOT NULL DEFAULT '1',
  `last_change` datetime NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `encodingID` int(11) NOT NULL DEFAULT '1',
  `categoryID` int(11) DEFAULT NULL,
  `currencyID` int(11) DEFAULT NULL,
  `customergroupID` int(11) DEFAULT NULL,
  `partnerID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `languageID` int(11) DEFAULT NULL,
  `active_filter` int(1) NOT NULL DEFAULT '1',
  `image_filter` int(1) NOT NULL DEFAULT '0',
  `stockmin_filter` int(1) NOT NULL DEFAULT '0',
  `instock_filter` int(11) NOT NULL,
  `price_filter` double NOT NULL,
  `own_filter` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `header` longtext COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `footer` longtext COLLATE utf8_unicode_ci NOT NULL,
  `count_filter` int(11) NOT NULL,
  `multishopID` int(11) DEFAULT NULL,
  `variant_export` int(11) unsigned NOT NULL DEFAULT '1',
  `cache_refreshed` datetime DEFAULT NULL,
  `dirty` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`, `cache_refreshed`, `dirty`) VALUES
(1,	'Google Produktsuche',	'2000-01-01 00:00:00',	0,	'4ebfa063359a73c356913df45b3fbe7f',	1,	0,	'2000-01-01 00:00:00',	0,	2,	'0000-00-00 00:00:00',	'export.txt',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nid{#S#}\ntitel{#S#}\nbeschreibung{#S#}\nlink{#S#}\nbild_url{#S#}\nean{#S#}\ngewicht{#S#}\nmarke{#S#}\nmpn{#S#}\nzustand{#S#}\nproduktart{#S#}\npreis{#S#}\nversand{#S#}\nstandort{#S#}\nwährung\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape|htmlentities}{#S#}\n{$sArticle.description_long|strip_tags|html_entity_decode|trim|regex_replace:\"#[^\\wöäüÖÄÜß\\.%&-+ ]#i\":\"\"|strip|truncate:500:\"...\":true|htmlentities|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:1}{#S#}\n{$sArticle.ean|escape}{#S#}\n{if $sArticle.weight}{$sArticle.weight|escape:\"number\"}{\" kg\"}{/if}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\nNeu{#S#}\n{$sArticle.articleID|category:\" > \"|escape}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:\"prepayment\":\"de\"}{#S#}\n{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(2,	'Kelkoo',	'2000-01-01 00:00:00',	0,	'f2d27fbba2dabc03789f0ac25f82d93f',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'kelkoo.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nurl{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nofferid{#S#}\nimage{#S#}\navailability{#S#}\ndeliverycost\n{/strip}{#L#}',	'{strip}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.name|escape|truncate:70}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:150:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.image|image:2|escape}{#S#}\n{if $sArticle.instock}001{else}002{/if}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(3,	'billiger.de',	'2000-01-01 00:00:00',	0,	'9ca7fd14bc772898bf01d9904d72c1ea',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'billiger.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\naid{#S#}\nbrand{#S#}\nmpnr{#S#}\nean{#S#}\nname{#S#}\ndesc{#S#}\nshop_cat{#S#}\nprice{#S#}\nppu{#S#}\nlink{#S#}\nimage{#S#}\ndlv_time{#S#}\ndlv_cost{#S#}\npzn\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.ean|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.price|escape:number}{#S#}\n{if $sArticle.purchaseunit}{$sArticle.price/$sArticle.purchaseunit*$sArticle.referenceunit|escape:number} {\"\\x80\"} / {$sArticle.referenceunit} {$sArticle.unit}{/if}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\"|escape:number}{#S#}\n\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(4,	'Idealo',	'2000-01-01 00:00:00',	0,	'2648057f0020fbeb7e69c238036b25e8',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'idealo.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nKategorie{#S#}\nHersteller{#S#}\nProduktbezeichnung{#S#}\nPreis{#S#}\nHersteller-Artikelnummer{#S#}\nEAN{#S#}\nPZN{#S#}\nISBN{#S#}\nVersandkosten Nachnahme{#S#}\nVersandkosten Vorkasse{#S#}\nVersandkosten Bankeinzug{#S#}\nDeeplink{#S#}\nLieferzeit{#S#}\nArtikelnummer{#S#}\nLink Produktbild{#S#}\nProdukt Kurztext\n{/strip}{#L#}',	'{strip}\n{$sArticle.articleID|category:\">\"|escape|replace:\"|\":\"\"}{#S#}\n{$sArticle.supplier|replace:\"|\":\"\"}{#S#}\n{$sArticle.name|strip_tags|strip|trim|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{#S#}\n{#S#}\n{#S#}\n{#S#}\n{$sArticle|@shippingcost:\"cash\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle|@shippingcost:\"debit\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:\"|\":\"\"}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime|escape} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:300:\"...\":true|escape}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(5,	'Yatego',	'2000-01-01 00:00:00',	0,	'75838aee39eab65375b5241544035f42',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'yatego.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}foreign_id{#S#}\narticle_nr{#S#}\ntitle{#S#}\ntax{#S#}\ncategories{#S#}\nunits{#S#}\nshort_desc{#S#}\nlong_desc{#S#}\npicture{#S#}\nurl{#S#}\nprice{#S#}\nprice_uvp{#S#}\ndelivery_date{#S#}\ntop_offer{#S#}\nstock{#S#}\npackage_size{#S#}\nquantity_unit{#S#}\nmpn{#S#}\nmanufacturer{#S#}\nstatus{#S#}\nvariants\n{/strip}{#L#}',	'{strip}\n{$sArticle.articleID|escape}{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|replace:\"|\":\"\"} {#S#}\n{$sArticle.tax}{#S#}\n{$sArticle.articleID|category:\">\"|escape},{$sArticle.supplier}{#S#}\n{$sArticle.weight}{#S#}\n{$sArticle.description|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|replace:\"|\":\"\"|escape}{#S#}\n\"{$sArticle.description_long|trim|html_entity_decode|replace:\"|\":\"|\"|replace:\'\"\':\'\"\"\'}<p>{$sArticle.attr1|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr2|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr3|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr4|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr5|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr6|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr7|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr8|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr9|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr10|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr11|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr12|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr13|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr14|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr15|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr16|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr17|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr18|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr19|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}<p>{$sArticle.attr20|regex_replace:\"/^(\\d)$/\":\"\"|regex_replace:\"/^0000-00-00$/\":\"\"|strip}\"{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:\"|\":\"\"}{#S#}\n{if $sArticle.configurator}0{else}{$sArticle.price|escape:\"number\"|escape}{/if}{#S#}\n{$sArticle.pseudoprice|escape}{#S#}\nLieferzeit in Tagen: {$sArticle.shippingtime|replace:\"0\":\"sofort\"}{#S#}\n{$sArticle.topseller}{#S#}\n{if $sArticle.configurator}\"-1\"{else}{$sArticle.instock}{/if}{#S#}\n{$sArticle.purchaseunit}{#S#}\n{$sArticle.unit_description}{#S#}\n{$sArticle.suppliernumber}{#S#}\n{$sArticle.supplier}{#S#}\n{$sArticle.active}{#S#}\n{if $sArticle.configurator}{$sArticle.articleID|escape}{else}{/if}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(6,	'schottenland.de',	'2000-01-01 00:00:00',	0,	'ad16704bf9e58f1f66f99cca7864e63d',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'schottenland.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nHersteller|\nProduktbezeichnung|\nProduktbeschreibung|\nPreis|\nVerfügbarkeit|\nEAN|\nHersteller AN|\nDeeplink|\nArtikelnummer|\nDAN_Ingram|\nVersandkosten Nachnahme|\nVersandkosten Vorkasse|\nVersandkosten Kreditkarte|\nVersandkosten Bankeinzug\n{/strip}{#L#}',	'{strip}\n{$sArticle.supplier|replace:\"|\":\"\"}|\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|replace:\"|\":\"\"}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|replace:\"|\":\"\"}|\n{$sArticle.price|escape:\"number\"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.ean|replace:\"|\":\"\"}|\n{$sArticle.suppliernumber|replace:\"|\":\"\"}|\n{$sArticle.articleID|link:$sArticle.name|replace:\"|\":\"\"}|\n{$sArticle.ordernumber|replace:\"|\":\"\"}|\n|\n{$sArticle|@shippingcost:\"cash\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle|@shippingcost:\"credituos\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle|@shippingcost:\"debit\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(7,	'guenstiger.de',	'2000-01-01 00:00:00',	0,	'5428e68f168eae36c3882b4cf29730bb',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'guenstiger.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|replace:\"|\":\"\"}|\n{$sArticle.supplier|replace:\"|\":\"\"}|\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|replace:\"|\":\"\"}|\n{$sArticle.price|escape:\"number\"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:\"|\":\"\"}|\n{$sArticle.image|image:0}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|replace:\"|\":\"\"}|\n{$sArticle|@shippingcost:\"cash\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n|\n{$sArticle|@shippingcost:\"debit\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n|\n{$sArticle|@shippingcost:\"invoice\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle.ean|replace:\"|\":\"\"}|\n{$sArticle.weight|replace:\"|\":\"\"}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(8,	'geizhals.at',	'2000-01-01 00:00:00',	0,	'0102715b70fa7d60d61c15c8e025824a',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'geizhals.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nID{#S#}\nHersteller{#S#}\nArtikelbezeichnung{#S#}\nKategorie{#S#}\nBeschreibungsfeld{#S#}\nBild{#S#}\nUrl{#S#}\nLagerstandl{#S#}\nVersandkosten{#S#}\nVersandkostenNachname{#S#}\nPreis{#S#}\nEAN{#S#}\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|escape}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:0}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle|@shippingcost:\"cash\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{$sArticle.ean|escape}{#S#}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(9,	'Ciao',	'2000-01-01 00:00:00',	0,	'b8728935bc62480971c0dfdf74eabf6f',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'ciao.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	1,	0,	0,	0,	'',	'{strip}\nOffer ID{#S#}\nBrand{#S#}\nProduct Name{#S#}\nCategory{#S#}\nDescription{#S#}\nImage URL{#S#}\nProduct URL{#S#}\nDelivery{#S#}\nShippingCost{#S#}\nPrice{#S#}\nProduct ID{#S#}\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:0}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\"|escape:\"number\"}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{#S#}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(10,	'Pangora',	'2000-01-01 00:00:00',	0,	'162a610b4a85c13fd448f9f5e2290fd5',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'pangora.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\noffer-id{#S#}\nmfname{#S#}\nlabel{#S#}\nmerchant-category{#S#}\ndescription{#S#}\nimage-url{#S#}\noffer-url{#S#}\nships-in{#S#}\nrelease-date{#S#}\ndelivery-charge{#S#}\nprices	old-prices{#S#}\nproduct-id{#S#}\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:0|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.releasedate|escape}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{#S#}\n{/strip}{#L#}\n\n',	'',	0,	1,	1,	NULL,	0),
(11,	'Shopping.com',	'2000-01-01 00:00:00',	0,	'cb29f40e760f11b9071d081b8ca8039c',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'shopping.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nMPN|\nEAN|\nHersteller|\nProduktname|\nProduktbeschreibung|\nPreis|\nProdukt-URL|\nProduktbild-URL|\nKategorie|\nVerfügbar|\nVerfügbarkeitsdetails|\nVersandkosten\n{/strip}{#L#}',	'{strip}\n|\n{$sArticle.ean}|\n{$sArticle.supplier}|\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode}|\n{$sArticle.price|escape:\"number\"}|\n{$sArticle.articleID|link:$sArticle.name}|\n{$sArticle.image|image:1}|\n{$sArticle.articleID|category:\">\"}|\n{if $sArticle.instock}Ja{else}Nein{/if}|\n{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(12,	'Hitmeister',	'2000-01-01 00:00:00',	0,	'76de62d0fd5ec76b483aa6529d36ee45',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'hitmeister.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nean{#S#}\ncondition{#S#}\nprice{#S#}\ncomment{#S#}\noffer_id{#S#}\nlocation{#S#}\ncount{#S#}\ndelivery_time{#S#}\n{/strip}{#L#}',	'{strip}\n{$sArticle.ean|escape}{#S#}\n100{#S#}\n{$sArticle.price*100}{#S#}\n{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{#S#}\n{#S#}\n{if $sArticle.instock}b{else}d{/if}{#S#}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(13,	'evendi.de',	'2000-01-01 00:00:00',	0,	'5ac98a759a6f392ea0065a500acf82e6',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'evendi.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nEindeutige Händler-Artikelnummer{#S#}\nPreis in Euro{#S#}\nKategorie{#S#}\nProduktbezeichnung{#S#}\nProduktbeschreibung{#S#}\nLink auf Detailseite{#S#}\nLieferzeit{#S#}\nEAN-Nummer{#S#}\nHersteller-Artikelnummer{#S#}\nLink auf Produktbild{#S#}\nHersteller{#S#}\nVersandVorkasse{#S#}\nVersandNachnahme{#S#}\nVersandLastschrift{#S#}\nVersandKreditkarte{#S#}\nVersandRechnung{#S#}\nVersandPayPal\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.price|escape:\"number\"|escape}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{#F#}{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#F#}{#S#}\n{$sArticle.ean|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.image|image:0|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle|@shippingcost:\"prepayment\":\"de\"|escape:\"number\"|escape}{#S#}\n{$sArticle|@shippingcost:\"cash\":\"de\"|escape:\"number\"|escape}{#S#}\n{$sArticle|@shippingcost:\"debit\":\"de\"|escape:\"number\"|escape}{#S#}\n{\"\"|escape}{#S#}\n{$sArticle|@shippingcost:\"invoice\":\"de\"|escape:\"number\"|escape}{#S#}\n{$sArticle|@shippingcost:\"paypal\":\"de\"|escape:\"number\"|escape}{#S#}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(14,	'affili.net',	'2000-01-01 00:00:00',	0,	'bc960c18cbeea9038314d040e7dc92f5',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'affilinet.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nart_number{#S#}\ncategory{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nimg_url{#S#}\ndeeplink1{#S#}\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.articleID|category:\">\"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:\"number\"}{#S#}\n{$sArticle.image|image:2|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(15,	'Google Produktsuche XML',	'2000-01-01 00:00:00',	0,	'e8eca3b3bbbad77afddb67b8138900e1',	1,	0,	'2000-01-01 00:00:00',	0,	3,	'2008-09-27 09:52:17',	'export.xml',	2,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n	<atom:link href=\"http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}\" rel=\"self\" type=\"application/rss+xml\" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>Beschreibung im Header hinterlegen</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>DE</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/_default/frontend/_resources/images/logo.jpg</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>',	'<item> \n    <g:id>{$sArticle.articleID|escape}</g:id>\n	<title>{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|escape}</title>\n	<description>{$sArticle.description_long|strip_tags|strip|truncate:900:\"...\"|escape}</description>\n	<g:google_product_category>Wählen Sie hier Ihre Google Produkt-Kategorie</g:google_product_category>\n	<g:product_type>{$sArticle.articleID|category:\" > \"|escape}</g:product_type>\n	<link>{$sArticle.articleID|link:$sArticle.name|escape}</link>\n	<g:image_link>{$sArticle.image|image:1}</g:image_link>\n	<g:condition>neu</g:condition>\n	<g:availability>{if $sArticle.esd}bestellbar{elseif $sArticle.instock>0}bestellbar{elseif $sArticle.releasedate && $sArticle.releasedate|strtotime > $smarty.now}vorbestellt{elseif $sArticle.shippingtime}bestellbar{else}nicht auf lager{/if}</g:availability>\n	<g:price>{$sArticle.price|format:\"number\"}</g:price>\n	<g:brand>{$sArticle.supplier|escape}</g:brand>\n	<g:gtin>{$sArticle.suppliernumber|replace:\"|\":\"\"}</g:gtin>\n	<g:mpn>{$sArticle.suppliernumber|escape}</g:mpn>\n	<g:shipping>\n       <g:country>DE</g:country>\n       <g:service>Standard</g:service>\n       <g:price>{$sArticle|@shippingcost:\"prepayment\":\"de\"|escape:number}</g:price>\n    </g:shipping>\n  {if $sArticle.changed}<pubDate>{$sArticle.changed|date_format:\"%a, %d %b %Y %T %Z\"}</pubDate>{/if}		\n</item>',	'</channel>\n</rss>',	0,	1,	1,	NULL,	0),
(16,	'preissuchmaschine.de',	'2000-01-01 00:00:00',	0,	'67fbbab544165d9d4e5352f9a12054a0',	1,	0,	'2000-01-01 00:00:00',	0,	1,	'0000-00-00 00:00:00',	'preissuchmaschine.csv',	1,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}',	'{strip}\n{$sArticle.ordernumber|replace:\"|\":\"\"}|\n{$sArticle.supplier|replace:\"|\":\"\"}|\n{$sArticle.name|strip_tags|strip|truncate:80:\"...\":true|replace:\"|\":\"\"}|\n{$sArticle.price|escape:\"number\"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:\"|\":\"\"}|\n{$sArticle.image|image:0}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:\"...\":true|html_entity_decode|replace:\"|\":\"\"}|\n{$sArticle|@shippingcost:\"cash\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n|\n{$sArticle|@shippingcost:\"debit\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n|\n{$sArticle|@shippingcost:\"invoice\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle|@shippingcost:\"prepayment\":\"de\":\"Deutsche Post Standard\"|escape:\"number\"}|\n{$sArticle.ean|replace:\"|\":\"\"}|\n{$sArticle.weight|replace:\"|\":\"\"}\n{/strip}{#L#}',	'',	0,	1,	1,	NULL,	0),
(17,	'RSS Feed-Template',	'2000-01-01 00:00:00',	0,	'3a6ff2a4f921a10d33d9b9ec25529a5d',	1,	0,	'2000-01-01 00:00:00',	0,	3,	'0000-00-00 00:00:00',	'export.xml',	2,	NULL,	1,	1,	'',	NULL,	0,	0,	0,	0,	0,	'',	'<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n<channel>\n	<atom:link href=\"http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}\" rel=\"self\" type=\"application/rss+xml\" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>Shopbeschreibung ...</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>{#L#}',	'<item> \n	<title>{$sArticle.name|strip_tags|htmlspecialchars_decode|strip|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name}</link>\n	<description>{if $sArticle.image}\n		<a href=\"{$sArticle.articleID|link:$sArticle.name}\" style=\"border:0 none;\">\n			<img src=\"{$sArticle.image|image:0}\" align=\"right\" style=\"padding: 0pt 0pt 12px 12px; float: right;\" />\n		</a>\n{/if}\n		{$sArticle.description_long|strip_tags|regex_replace:\"/[^\\wöäüÖÄÜß .?!,&:%;\\-\\\"\']/i\":\"\"|trim|truncate:900:\"...\"|escape}\n	</description>\n	<category>{$sArticle.articleID|category:\">\"|htmlspecialchars_decode|escape}</category>\n{if $sArticle.changed} 	{assign var=\"sArticleChanged\" value=$sArticle.changed|strtotime}<pubDate>{\"r\"|date:$sArticleChanged}</pubDate>{\"rn\"}{/if}\n</item>{#L#}',	'</channel>\n</rss>',	0,	1,	1,	NULL,	0);

DROP TABLE IF EXISTS `s_export_articles`;
CREATE TABLE `s_export_articles` (
  `feedID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_export_attributes`;
CREATE TABLE `s_export_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exportID` (`exportID`),
  CONSTRAINT `s_export_attributes_ibfk_1` FOREIGN KEY (`exportID`) REFERENCES `s_export` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_export_categories`;
CREATE TABLE `s_export_categories` (
  `feedID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_export_suppliers`;
CREATE TABLE `s_export_suppliers` (
  `feedID` int(11) NOT NULL,
  `supplierID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`supplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter`;
CREATE TABLE `s_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `comparable` int(1) NOT NULL,
  `sortmode` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `get_sets_query` (`position`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_articles`;
CREATE TABLE `s_filter_articles` (
  `articleID` int(10) unsigned NOT NULL,
  `valueID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`articleID`,`valueID`),
  KEY `valueID` (`valueID`),
  KEY `articleID` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_attributes`;
CREATE TABLE `s_filter_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filterID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filterID` (`filterID`),
  CONSTRAINT `s_filter_attributes_ibfk_1` FOREIGN KEY (`filterID`) REFERENCES `s_filter` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_options`;
CREATE TABLE `s_filter_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filterable` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `get_options_query` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_options_attributes`;
CREATE TABLE `s_filter_options_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `optionID` (`optionID`),
  CONSTRAINT `s_filter_options_attributes_ibfk_1` FOREIGN KEY (`optionID`) REFERENCES `s_filter_options` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_relations`;
CREATE TABLE `s_filter_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`optionID`),
  KEY `get_set_assigns_query` (`groupID`,`position`),
  KEY `groupID_2` (`groupID`),
  KEY `optionID` (`optionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_values`;
CREATE TABLE `s_filter_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `media_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `optionID` (`optionID`,`value`),
  KEY `get_property_value_by_option_id_query` (`optionID`,`position`),
  KEY `optionID_2` (`optionID`),
  KEY `filters_order_by_position` (`optionID`,`position`,`id`),
  KEY `filters_order_by_numeric` (`optionID`,`id`),
  KEY `filters_order_by_alphanumeric` (`optionID`,`value`,`id`),
  KEY `media_id` (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_filter_values_attributes`;
CREATE TABLE `s_filter_values_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `valueID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `valueID` (`valueID`),
  CONSTRAINT `s_filter_values_attributes_ibfk_1` FOREIGN KEY (`valueID`) REFERENCES `s_filter_values` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_library_component`;
CREATE TABLE `s_library_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `x_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `convert_function` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cls` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
(2,	'Text Element',	'emotion-components-html-element',	NULL,	'',	'component_html',	'html-text-element',	NULL),
(3,	'Banner',	'emotion-components-banner',	'getBannerMappingLinks',	'',	'component_banner',	'banner-element',	NULL),
(4,	'Artikel',	'emotion-components-article',	'getArticle',	'',	'component_article',	'article-element',	NULL),
(5,	'Kategorie-Teaser',	'emotion-components-category-teaser',	'getCategoryTeaser',	'',	'component_category_teaser',	'category-teaser-element',	NULL),
(6,	'Blog-Artikel',	'emotion-components-blog',	'getBlogEntry',	'',	'component_blog',	'blog-element',	NULL),
(7,	'Banner-Slider',	'emotion-components-banner-slider',	'getBannerSlider',	'',	'component_banner_slider',	'banner-slider-element',	NULL),
(8,	'Youtube-Video',	'emotion-components-youtube',	NULL,	'',	'component_youtube',	'youtube-element',	NULL),
(9,	'iFrame-Element',	'emotion-components-iframe',	NULL,	'',	'component_iframe',	'iframe-element',	NULL),
(10,	'Hersteller-Slider',	'emotion-components-manufacturer-slider',	'getManufacturerSlider',	'',	'component_manufacturer_slider',	'manufacturer-slider-element',	NULL),
(11,	'Artikel-Slider',	'emotion-components-article-slider',	'getArticleSlider',	'',	'component_article_slider',	'article-slider-element',	NULL),
(12,	'HTML5 Video-Element',	'emotion-components-html-video',	'getHtml5Video',	'',	'component_video',	'emotion--element-video',	NULL),
(13,	'Code Element',	'emotion-components-html-code',	NULL,	'',	'component_html_code',	'html-code-element',	NULL);

DROP TABLE IF EXISTS `s_library_component_field`;
CREATE TABLE `s_library_component_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `componentID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `x_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `field_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `support_text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `help_title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `help_text` text COLLATE utf8_unicode_ci NOT NULL,
  `store` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `display_field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value_field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `default_value` text COLLATE utf8_unicode_ci NOT NULL,
  `allow_blank` int(1) NOT NULL,
  `translatable` int(1) NOT NULL DEFAULT '0',
  `position` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=104 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`, `translatable`, `position`) VALUES
(3,	3,	'file',	'mediaselectionfield',	'',	'Bild',	'',	'',	'',	'',	'',	'',	'',	0,	0,	3),
(4,	2,	'text',	'tinymce',	'',	'Text',	'Anzuzeigender Text',	'HTML-Text',	'Geben Sie hier den Text ein der im Element angezeigt werden soll.',	'',	'',	'',	'',	0,	1,	4),
(5,	4,	'article',	'emotion-components-fields-article',	'',	'Artikelsuche',	'Der anzuzeigende Artikel',	'Lorem ipsum dolor',	'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam',	'',	'',	'',	'',	0,	0,	9),
(6,	2,	'cms_title',	'textfield',	'',	'Titel',	'',	'',	'',	'',	'',	'',	'',	1,	1,	6),
(7,	3,	'bannerMapping',	'hidden',	'json',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	7),
(8,	4,	'article_type',	'emotion-components-fields-article-type',	'',	'Typ des Artikels',	'',	'',	'',	'',	'',	'',	'',	0,	0,	8),
(9,	5,	'image_type',	'emotion-components-fields-category-image-type',	'',	'Typ des Bildes',	'',	'',	'',	'',	'',	'',	'',	0,	0,	9),
(10,	5,	'image',	'mediaselectionfield',	'',	'Bild',	'',	'',	'',	'',	'',	'',	'',	1,	0,	10),
(11,	5,	'category_selection',	'emotion-components-fields-category-selection',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	11),
(12,	6,	'entry_amount',	'numberfield',	'',	'Anzahl',	'',	'',	'',	'',	'',	'',	'',	0,	0,	12),
(13,	7,	'banner_slider_title',	'textfield',	'',	'Überschrift',	'',	'',	'',	'',	'',	'',	'',	1,	1,	13),
(15,	7,	'banner_slider_arrows',	'checkbox',	'',	'Pfeile anzeigen',	'',	'',	'',	'',	'',	'',	'',	0,	0,	15),
(16,	7,	'banner_slider_numbers',	'checkbox',	'',	'Nummern ausgeben',	'Bitte beachten Sie, dass diese Einstellung nur Auswirkungen auf das \"Emotion\"-Template hat.',	'',	'',	'',	'',	'',	'',	0,	0,	16),
(17,	7,	'banner_slider_scrollspeed',	'numberfield',	'',	'Scroll-Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'',	0,	0,	17),
(18,	7,	'banner_slider_rotation',	'checkbox',	'',	'Automatisch rotieren',	'',	'',	'',	'',	'',	'',	'',	0,	0,	18),
(19,	7,	'banner_slider_rotatespeed',	'numberfield',	'',	'Rotations Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'5000',	0,	0,	19),
(20,	7,	'banner_slider',	'hidden',	'json',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	20),
(22,	8,	'video_id',	'textfield',	'',	'Youtube-Video ID',	'',	'',	'',	'',	'',	'',	'',	0,	1,	22),
(23,	8,	'video_hd',	'checkbox',	'',	'HD-Video verwenden',	'',	'',	'',	'',	'',	'',	'',	0,	0,	23),
(24,	9,	'iframe_url',	'textfield',	'',	'URL',	'',	'',	'',	'',	'',	'',	'',	0,	1,	24),
(25,	10,	'manufacturer_type',	'emotion-components-fields-manufacturer-type',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	25),
(26,	10,	'manufacturer_category',	'emotion-components-fields-category-selection',	'',	'',	'',	'',	'',	'',	'',	'',	'',	1,	0,	26),
(27,	10,	'selected_manufacturers',	'hidden',	'json',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	27),
(28,	10,	'manufacturer_slider_title',	'textfield',	'',	'Überschrift',	'',	'',	'',	'',	'',	'',	'',	1,	1,	28),
(30,	10,	'manufacturer_slider_arrows',	'checkbox',	'',	'Pfeile anzeigen',	'',	'',	'',	'',	'',	'',	'',	0,	0,	30),
(32,	10,	'manufacturer_slider_scrollspeed',	'numberfield',	'',	'Scroll-Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'',	0,	0,	32),
(33,	10,	'manufacturer_slider_rotation',	'checkbox',	'',	'Automatisch rotieren',	'',	'',	'',	'',	'',	'',	'',	0,	0,	33),
(34,	10,	'manufacturer_slider_rotatespeed',	'numberfield',	'',	'Rotations Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'5000',	0,	0,	34),
(36,	11,	'article_slider_type',	'emotion-components-fields-article-slider-type',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	36),
(37,	11,	'selected_articles',	'hidden',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	100),
(38,	11,	'article_slider_max_number',	'numberfield',	'',	'max. Anzahl',	'',	'',	'',	'',	'',	'',	'',	0,	0,	39),
(39,	11,	'article_slider_title',	'textfield',	'',	'Überschrift',	'',	'',	'',	'',	'',	'',	'',	1,	1,	40),
(41,	11,	'article_slider_arrows',	'checkbox',	'',	'Pfeile anzeigen',	'',	'',	'',	'',	'',	'',	'',	0,	0,	42),
(43,	11,	'article_slider_scrollspeed',	'numberfield',	'',	'Scroll-Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'',	0,	0,	44),
(44,	11,	'article_slider_rotation',	'checkbox',	'',	'Automatisch rotieren',	'',	'',	'',	'',	'',	'',	'',	0,	0,	45),
(45,	11,	'article_slider_rotatespeed',	'numberfield',	'',	'Rotations Geschwindigkeit',	'',	'',	'',	'',	'',	'',	'5000',	0,	0,	46),
(47,	3,	'link',	'textfield',	'',	'Link',	'',	'',	'',	'',	'',	'',	'',	1,	1,	47),
(48,	5,	'blog_category',	'checkboxfield',	'',	'Blog-Kategorie',	'Bei der ausgewählten Kategorie handelt es sich um eine Blog-Kategorie',	'',	'',	'',	'',	'',	'',	0,	0,	48),
(59,	11,	'article_slider_category',	'emotion-components-fields-category-selection',	'',	'',	'',	'',	'',	'',	'',	'',	'',	1,	0,	38),
(65,	3,	'bannerPosition',	'hidden',	'',	'',	'',	'',	'',	'',	'',	'',	'center',	0,	0,	NULL),
(66,	4,	'productImageOnly',	'checkboxfield',	'',	'Nur Produktbild',	'Bei aktivierter Einstellung wird nur das Produktbild dargestellt.',	'',	'',	'',	'label',	'key',	'',	0,	0,	10),
(68,	6,	'blog_entry_selection',	'emotion-components-fields-category-selection',	'',	'Kategorie',	'',	'',	'',	'',	'label',	'key',	'',	0,	0,	10),
(69,	12,	'videoMode',	'emotion-components-fields-video-mode',	'',	'Modus',	'Bestimmen Sie das Verhalten des Videos. Legen Sie fest, ob das Video skalierend, füllend oder gestreckt dargestellt werden soll.',	'',	'',	'',	'label',	'key',	'',	0,	0,	40),
(70,	12,	'overlay',	'textfield',	'',	'Overlay Farbe',	'Legen Sie eine Hintergrundfarbe für das Overlay fest. Ein RGBA-Wert wird empfohlen.',	'',	'',	'',	'',	'',	'rgba(0, 0, 0, .2)',	1,	0,	71),
(71,	12,	'originTop',	'numberfield',	'',	'Oberer Ausgangspunkt',	'Legt den oberen Ausgangspunkt für die Skalierung des Videos fest. Die Angabe erfolgt in Prozent.',	'',	'',	'',	'',	'',	'50',	1,	0,	69),
(72,	12,	'originLeft',	'numberfield',	'',	'Linker Ausgangspunkt',	'Legt den linken Ausgangspunkt für die Skalierung des Videos fest. Die Angabe erfolgt in Prozent.',	'',	'',	'',	'',	'',	'50',	1,	0,	68),
(73,	12,	'scale',	'numberfield',	'',	'Zoom-Faktor',	'Wenn Sie den Modus Füllen gewählt haben können Sie den Zoom-Faktor mit dieser Option ändern.',	'',	'',	'',	'',	'',	'1.0',	1,	0,	67),
(74,	12,	'muted',	'checkbox',	'',	'Video stumm schalten',	'Die Ton-Spur des Videos wird stumm geschaltet',	'',	'',	'',	'',	'',	'1',	1,	0,	60),
(75,	12,	'loop',	'checkbox',	'',	'Video schleifen',	'Das Video wird in einer Dauerschleife angezeigt',	'',	'',	'',	'',	'',	'1',	1,	0,	59),
(76,	12,	'controls',	'checkbox',	'',	'Video-Steuerung anzeigen',	'Nicht für den Modus Füllen oder Strecken empfohlen.',	'',	'',	'',	'',	'',	'1',	1,	0,	58),
(77,	12,	'autobuffer',	'checkbox',	'',	'Video automatisch vorladen',	'',	'',	'',	'',	'',	'',	'1',	1,	0,	57),
(78,	12,	'autoplay',	'checkbox',	'',	'Video automatisch abspielen',	'',	'',	'',	'',	'',	'',	'1',	1,	0,	56),
(79,	12,	'html_text',	'tinymce',	'',	'Overlay Text',	'Sie können ein Overlay mit einem Text über das Video legen.',	'',	'',	'',	'',	'',	'',	1,	0,	70),
(80,	12,	'fallback_picture',	'mediatextfield',	'',	'Vorschau-Bild',	'Das Vorschau-Bild wird gezeigt wenn das Video noch nicht abgespielt wird.',	'',	'',	'',	'',	'',	'',	0,	0,	44),
(81,	12,	'h264_video',	'mediatextfield',	'',	'.mp4 Video',	'Video für Browser mit MP4 Support. Auch externer Pfad möglich.',	'',	'',	'',	'',	'',	'',	0,	0,	43),
(82,	12,	'ogg_video',	'mediatextfield',	'',	'.ogv/.ogg Video',	'Video für Browser mit Ogg Support. Auch externer Pfad möglich.',	'',	'',	'',	'',	'',	'',	0,	0,	42),
(83,	12,	'webm_video',	'mediatextfield',	'',	'.webm Video',	'Video für Browser mit WebM Support. Auch externer Pfad möglich.',	'',	'',	'',	'',	'',	'',	0,	0,	41),
(84,	2,	'needsNoStyling',	'checkbox',	'',	'Kein Styling hinzufügen',	'Definiert, dass kein weiteres Layout-Styling hinzugefügt wird.',	'',	'',	'',	'',	'',	'0',	0,	0,	10),
(85,	3,	'title',	'textfield',	'',	'Title Text',	'',	'',	'',	'',	'',	'',	'',	1,	1,	50),
(86,	11,	'article_slider_stream',	'productstreamselection',	'',	'',	'',	'',	'',	'',	'name',	'id',	'',	0,	0,	38),
(87,	13,	'javascript',	'codemirrorfield',	'',	'JavaScript Code',	'',	'',	'',	'',	'',	'',	'',	1,	1,	0),
(88,	13,	'smarty',	'codemirrorfield',	'',	'HTML Code',	'',	'',	'',	'',	'',	'',	'',	1,	1,	1),
(89,	3,	'banner_link_target',	'emotion-components-fields-link-target',	'',	'Link-Ziel',	'',	'',	'',	'',	'',	'',	'',	1,	0,	48),
(90,	4,	'article_category',	'emotion-components-fields-category-selection',	'',	'Kategorie',	'',	'',	'',	'',	'',	'',	'',	1,	0,	9),
(91,	4,	'no_border',	'checkbox',	'',	'',	'',	'',	'',	'',	'',	'',	'',	1,	0,	90),
(92,	11,	'no_border',	'checkbox',	'',	'',	'',	'',	'',	'',	'',	'',	'',	1,	0,	90),
(93,	10,	'no_border',	'checkbox',	'',	'',	'',	'',	'',	'',	'',	'',	'',	1,	0,	90),
(94,	8,	'video_autoplay',	'checkbox',	'',	'Video automatisch starten',	'',	'',	'',	'',	'',	'',	'0',	0,	0,	24),
(95,	8,	'video_related',	'checkbox',	'',	'Empfehlungen ausblenden',	'',	'',	'',	'',	'',	'',	'0',	0,	0,	25),
(96,	8,	'video_controls',	'checkbox',	'',	'Steuerung ausblenden',	'',	'',	'',	'',	'',	'',	'0',	0,	0,	26),
(97,	8,	'video_start',	'numberfield',	'',	'Starten nach x-Sekunden',	'',	'',	'',	'',	'',	'',	'',	1,	0,	27),
(98,	8,	'video_end',	'numberfield',	'',	'Stoppen nach x-Sekunden',	'',	'',	'',	'',	'',	'',	'',	1,	0,	28),
(99,	8,	'video_info',	'checkbox',	'',	'Info ausblenden',	'',	'',	'',	'',	'',	'',	'0',	0,	0,	29),
(100,	8,	'video_branding',	'checkbox',	'',	'Branding ausblenden',	'',	'',	'',	'',	'',	'',	'0',	0,	0,	30),
(101,	8,	'video_loop',	'checkbox',	'',	'Loop aktivieren',	'',	'',	'Loop ist nicht mit Start- und Endzeiten kompatibel. Video wird wieder von Beginn abgespielt.',	'',	'',	'',	'0',	0,	0,	31),
(102,	11,	'selected_variants',	'hidden',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	100),
(103,	4,	'variant',	'emotion-components-fields-variant',	'',	'',	'',	'',	'',	'',	'',	'',	'',	0,	0,	9);

DROP TABLE IF EXISTS `s_media`;
CREATE TABLE `s_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `width` int(11) unsigned DEFAULT NULL,
  `height` int(11) unsigned DEFAULT NULL,
  `userID` int(11) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Album` (`albumID`),
  KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_media_album`;
CREATE TABLE `s_media_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentID` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL,
  `garbage_collectable` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`, `garbage_collectable`) VALUES
(-13,	'Papierkorb',	NULL,	12,	1),
(-12,	'Hersteller',	NULL,	12,	1),
(-11,	'Blog',	NULL,	3,	1),
(-10,	'Unsortiert',	NULL,	7,	1),
(-9,	'Sonstiges',	-6,	3,	1),
(-8,	'Musik',	-6,	2,	1),
(-7,	'Video',	-6,	1,	1),
(-6,	'Dateien',	NULL,	6,	1),
(-5,	'Newsletter',	NULL,	4,	1),
(-4,	'Aktionen',	NULL,	5,	1),
(-3,	'Einkaufswelten',	NULL,	3,	1),
(-2,	'Banner',	NULL,	1,	1),
(-1,	'Artikel',	NULL,	2,	1);

DROP TABLE IF EXISTS `s_media_album_settings`;
CREATE TABLE `s_media_album_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `create_thumbnails` int(11) NOT NULL,
  `thumbnail_size` text COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `thumbnail_high_dpi` int(1) DEFAULT NULL,
  `thumbnail_quality` int(11) DEFAULT NULL,
  `thumbnail_high_dpi_quality` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `albumID` (`albumID`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_media_album_settings` (`id`, `albumID`, `create_thumbnails`, `thumbnail_size`, `icon`, `thumbnail_high_dpi`, `thumbnail_quality`, `thumbnail_high_dpi_quality`) VALUES
(1,	-10,	0,	'',	'sprite-blue-folder',	0,	90,	60),
(2,	-9,	0,	'',	'sprite-blue-folder',	0,	90,	60),
(3,	-8,	0,	'',	'sprite-blue-folder',	0,	90,	60),
(4,	-7,	0,	'',	'sprite-blue-folder',	0,	90,	60),
(5,	-6,	0,	'',	'sprite-blue-folder',	0,	90,	60),
(6,	-5,	0,	'',	'sprite-inbox-document-text',	0,	90,	60),
(7,	-4,	0,	'',	'sprite-target',	0,	90,	60),
(8,	-3,	1,	'800x800;1280x1280;1920x1920',	'sprite-target',	1,	90,	60),
(9,	-2,	1,	'800x800;1280x1280;1920x1920',	'sprite-pictures',	1,	90,	60),
(10,	-1,	1,	'200x200;600x600;1280x1280',	'sprite-inbox',	1,	90,	60),
(11,	-11,	1,	'200x200;600x600;1280x1280',	'sprite-leaf',	1,	90,	60),
(12,	-12,	0,	'',	'sprite-hard-hat',	0,	90,	60),
(13,	-13,	0,	'',	'sprite-bin-metal-full',	0,	90,	60);

DROP TABLE IF EXISTS `s_media_association`;
CREATE TABLE `s_media_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) NOT NULL,
  `targetType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `targetID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Media` (`mediaID`),
  KEY `Target` (`targetID`,`targetType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_media_attributes`;
CREATE TABLE `s_media_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mediaID` (`mediaID`),
  CONSTRAINT `s_media_attributes_ibfk_1` FOREIGN KEY (`mediaID`) REFERENCES `s_media` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_multi_edit_backup`;
CREATE TABLE `s_multi_edit_backup` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `filter_string` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Filter string of the backed up change',
  `operation_string` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Operations applied after the backup',
  `items` int(255) unsigned NOT NULL COMMENT 'Number of items affected by the backup',
  `date` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Creation date',
  `size` int(255) unsigned NOT NULL COMMENT 'Size of the backup file',
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Path of the backup file',
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Hash of the backup file',
  PRIMARY KEY (`id`),
  KEY `date` (`date`),
  KEY `size` (`size`),
  KEY `items` (`items`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Backups known to the system';


DROP TABLE IF EXISTS `s_multi_edit_filter`;
CREATE TABLE `s_multi_edit_filter` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Name of the filter',
  `filter_string` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The actual filter string',
  `description` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'User description of the filter',
  `created` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Creation date',
  `is_favorite` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Did the user mark this filter as favorite?',
  `is_simple` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Can the filter be loaded and modified with the simple editor?',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds all multi edit filters';

INSERT INTO `s_multi_edit_filter` (`id`, `name`, `filter_string`, `description`, `created`, `is_favorite`, `is_simple`) VALUES
(1,	'<b>Abverkauf</b><br><small>nicht auf Lager</small>',	'   ARTICLE.LASTSTOCK  ISTRUE and DETAIL.INSTOCK <= 0',	'Abverkauf-Artikel ohne Lagerbestand',	NULL,	1,	0),
(2,	'Hauptartikel',	'ismain',	'Alle Hauptartikel (einfache Artikel und Standardvarianten)',	NULL,	0,	0),
(3,	'Mit Staffelpreisen',	'HASBLOCKPRICE',	'',	NULL,	0,	0),
(4,	'Highlight',	'ARTICLE.HIGHLIGHT ISTRUE ',	'Zeit alle Highlight-Artikel',	NULL,	0,	0),
(5,	'Konfigurator-Artikel',	'HASCONFIGURATOR  AND ISMAIN ',	'Artikel mit Konfiguratoren',	NULL,	0,	0),
(7,	'Varianten',	'HASCONFIGURATOR ',	'Alle Varianten',	NULL,	0,	0),
(8,	'Ohne Kategorie',	'CATEGORY.ID ISNULL  and ISMAIN ',	'Artikel ohne Kategoriezuordnung',	NULL,	1,	0),
(16,	'Artikel ohne Bilder',	'HASNOIMAGE ',	'Artikel ohne Bilder',	NULL,	1,	0),
(17,	'Komplexer Filter',	'ismain and CATEGORY.ACTIVE ISTRUE and SUPPLIER.NAME IN ( \"Teapavilion\" , \"Feinbrennerei Sasse\" ) ',	'',	NULL,	0,	0),
(18,	'Artikel mit Händlerpreisen',	'PRICE.CUSTOMERGROUPKEY IN (\"B2B\" , \"H\")',	'Alle Artikel, für die Händlerpreise gepflegt werden.',	NULL,	0,	0),
(20,	'Rote Artikel',	'CONFIGURATOROPTION.NAME = \"%Rot%\"  or PROPERTYOPTION.VALUE = \"rot\" ',	'Alle Artikel mit \"rot\" als Konfiguratoroption oder Eigenschaft',	NULL,	0,	0),
(21,	'Regulärer Ausdruck',	'DETAIL.NUMBER !~ \"^sw[0-9]*\" ',	'Findet alle Artikel, die <b>nicht</b> eine Bestellnummer nach dem Schema swZAHL haben.',	NULL,	0,	0),
(22,	'Artikel ohne Bewertung',	'  VOTE.ID ISNULL  and ismain',	'Zeigt alle Artikel ohne Bewertungen und Kommentar',	NULL,	0,	0),
(23,	'Artikel mit nicht-freigeschalteten Bewertungen',	'VOTE.ACTIVE = \"0\"',	'Zeigt alle Artikel, die mindestens eine inaktive Bewertung haben',	NULL,	0,	1);

DROP TABLE IF EXISTS `s_multi_edit_queue`;
CREATE TABLE `s_multi_edit_queue` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'Queued resource (e.g. product)',
  `filter_string` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The actual filter string',
  `operations` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'Operations to apply',
  `items` int(255) unsigned NOT NULL COMMENT 'Initial number of objects in the queue',
  `active` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'When active, the queue is allowed to be progressed by cronjob',
  `created` datetime DEFAULT '0000-00-00 00:00:00' COMMENT 'Creation date',
  PRIMARY KEY (`id`),
  KEY `filter_string` (`filter_string`(255)),
  KEY `created` (`created`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Holds the batch process queue';


DROP TABLE IF EXISTS `s_multi_edit_queue_articles`;
CREATE TABLE `s_multi_edit_queue_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) unsigned NOT NULL COMMENT 'Id of the queue this article belongs to',
  `detail_id` int(11) unsigned NOT NULL COMMENT 'Id of the article detail',
  PRIMARY KEY (`id`),
  UNIQUE KEY `queue_id_2` (`queue_id`,`detail_id`),
  KEY `detail_id` (`detail_id`),
  KEY `queue_id` (`queue_id`),
  CONSTRAINT `s_multi_edit_queue_articles_ibfk_1` FOREIGN KEY (`detail_id`) REFERENCES `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  CONSTRAINT `s_multi_edit_queue_articles_ibfk_2` FOREIGN KEY (`queue_id`) REFERENCES `s_multi_edit_queue` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Products belonging to a certain queue';


DROP TABLE IF EXISTS `s_order`;
CREATE TABLE `s_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `invoice_amount` double NOT NULL DEFAULT '0',
  `invoice_amount_net` double NOT NULL,
  `invoice_shipping` double NOT NULL DEFAULT '0',
  `invoice_shipping_net` double NOT NULL,
  `invoice_shipping_tax_rate` double DEFAULT NULL,
  `ordertime` datetime DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `cleared` int(11) NOT NULL DEFAULT '0',
  `paymentID` int(11) NOT NULL DEFAULT '0',
  `transactionID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `comment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `customercomment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `internalcomment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `net` int(1) NOT NULL,
  `taxfree` int(11) NOT NULL,
  `partnerID` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `temporaryID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `cleareddate` datetime DEFAULT NULL,
  `trackingcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `dispatchID` int(11) NOT NULL,
  `currency` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `currencyFactor` double NOT NULL,
  `subshopID` int(11) NOT NULL,
  `remote_addr` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `deviceType` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_proportional_calculation` tinyint(4) NOT NULL DEFAULT '0',
  `changed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partnerID` (`partnerID`),
  KEY `userID` (`userID`),
  KEY `ordertime` (`ordertime`),
  KEY `cleared` (`cleared`),
  KEY `status` (`status`),
  KEY `paymentID` (`paymentID`),
  KEY `temporaryID` (`temporaryID`),
  KEY `ordernumber` (`ordernumber`),
  KEY `transactionID` (`transactionID`),
  KEY `ordernumber_2` (`ordernumber`,`status`),
  KEY `invoice_amount` (`invoice_amount`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_attributes`;
CREATE TABLE `s_order_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  `attribute4` text COLLATE utf8_unicode_ci,
  `attribute5` text COLLATE utf8_unicode_ci,
  `attribute6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  CONSTRAINT `s_order_attributes_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `s_order` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_basket`;
CREATE TABLE `s_order_basket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shippingfree` int(1) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `netprice` double NOT NULL DEFAULT '0',
  `tax_rate` double NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modus` int(11) NOT NULL DEFAULT '0',
  `esdarticle` int(1) NOT NULL,
  `partnerID` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  `lastviewport` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `useragent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `config` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `currencyFactor` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessionID` (`sessionID`),
  KEY `articleID` (`articleID`),
  KEY `datum` (`datum`),
  KEY `get_basket` (`sessionID`,`id`,`datum`),
  KEY `ordernumber` (`ordernumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_basket_attributes`;
CREATE TABLE `s_order_basket_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basketID` int(11) DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  `attribute4` text COLLATE utf8_unicode_ci,
  `attribute5` text COLLATE utf8_unicode_ci,
  `attribute6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basketID` (`basketID`),
  CONSTRAINT `s_order_basket_attributes_ibfk_2` FOREIGN KEY (`basketID`) REFERENCES `s_order_basket` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_basket_signatures`;
CREATE TABLE `s_order_basket_signatures` (
  `signature` varchar(200) COLLATE utf8_unicode_ci NOT NULL,
  `basket` longtext COLLATE utf8_unicode_ci NOT NULL,
  `created_at` date NOT NULL,
  PRIMARY KEY (`signature`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_billingaddress`;
CREATE TABLE `s_order_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `orderID` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `customernumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `stateID` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  KEY `userid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_billingaddress_attributes`;
CREATE TABLE `s_order_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` text COLLATE utf8_unicode_ci,
  `text2` text COLLATE utf8_unicode_ci,
  `text3` text COLLATE utf8_unicode_ci,
  `text4` text COLLATE utf8_unicode_ci,
  `text5` text COLLATE utf8_unicode_ci,
  `text6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`),
  CONSTRAINT `s_order_billingaddress_attributes_ibfk_2` FOREIGN KEY (`billingID`) REFERENCES `s_order_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_comparisons`;
CREATE TABLE `s_order_comparisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `sessionID` (`sessionID`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_details`;
CREATE TABLE `s_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `shipped` int(11) NOT NULL DEFAULT '0',
  `shippedgroup` int(11) NOT NULL DEFAULT '0',
  `releasedate` date DEFAULT NULL,
  `modus` int(11) NOT NULL,
  `esdarticle` int(1) NOT NULL,
  `taxID` int(11) DEFAULT NULL,
  `tax_rate` double NOT NULL,
  `config` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `ean` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `pack_unit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `articleDetailID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `orderID` (`orderID`),
  KEY `articleID` (`articleID`),
  KEY `ordernumber` (`ordernumber`),
  KEY `articleordernumber` (`articleordernumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_details_attributes`;
CREATE TABLE `s_order_details_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detailID` int(11) DEFAULT NULL,
  `attribute1` text COLLATE utf8_unicode_ci,
  `attribute2` text COLLATE utf8_unicode_ci,
  `attribute3` text COLLATE utf8_unicode_ci,
  `attribute4` text COLLATE utf8_unicode_ci,
  `attribute5` text COLLATE utf8_unicode_ci,
  `attribute6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detailID` (`detailID`),
  CONSTRAINT `s_order_details_attributes_ibfk_1` FOREIGN KEY (`detailID`) REFERENCES `s_order_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_documents`;
CREATE TABLE `s_order_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `orderID` int(11) unsigned NOT NULL,
  `amount` double NOT NULL,
  `docID` int(11) NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderID` (`orderID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_documents_attributes`;
CREATE TABLE `s_order_documents_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentID` (`documentID`),
  CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_esd`;
CREATE TABLE `s_order_esd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialID` int(255) NOT NULL DEFAULT '0',
  `esdID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL DEFAULT '0',
  `orderdetailsID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_history`;
CREATE TABLE `s_order_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `previous_order_status_id` int(11) DEFAULT NULL,
  `order_status_id` int(11) DEFAULT NULL,
  `previous_payment_status_id` int(11) DEFAULT NULL,
  `payment_status_id` int(11) DEFAULT NULL,
  `comment` text COLLATE utf8_unicode_ci NOT NULL,
  `change_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user` (`userID`),
  KEY `order` (`orderID`),
  KEY `current_payment_status` (`payment_status_id`),
  KEY `current_order_status` (`order_status_id`),
  KEY `previous_payment_status` (`previous_payment_status_id`),
  KEY `previous_order_status` (`previous_order_status_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_notes`;
CREATE TABLE `s_order_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sUniqueID` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `basket_count_notes` (`sUniqueID`,`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_number`;
CREATE TABLE `s_order_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=929 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_order_number` (`id`, `number`, `name`, `desc`) VALUES
(1,	20002,	'user',	'Kunden'),
(920,	20000,	'invoice',	'Bestellungen'),
(921,	20000,	'doc_1',	'Lieferscheine'),
(922,	20000,	'doc_2',	'Gutschriften'),
(924,	20000,	'doc_0',	'Rechnungen'),
(925,	10000,	'articleordernumber',	'Artikelbestellnummer  '),
(926,	10000,	'sSERVICE1',	'Service - 1'),
(927,	10000,	'sSERVICE2',	'Service - 2'),
(928,	110,	'blogordernumber',	'Blog - ID');

DROP TABLE IF EXISTS `s_order_shippingaddress`;
CREATE TABLE `s_order_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `orderID` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `countryID` int(11) NOT NULL,
  `stateID` int(11) DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_order_shippingaddress_attributes`;
CREATE TABLE `s_order_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` text COLLATE utf8_unicode_ci,
  `text2` text COLLATE utf8_unicode_ci,
  `text3` text COLLATE utf8_unicode_ci,
  `text4` text COLLATE utf8_unicode_ci,
  `text5` text COLLATE utf8_unicode_ci,
  `text6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`),
  CONSTRAINT `s_order_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_order_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_plugin_recommendations`;
CREATE TABLE `s_plugin_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) NOT NULL,
  `banner_active` int(1) NOT NULL,
  `new_active` int(1) NOT NULL,
  `bought_active` int(1) NOT NULL,
  `supplier_active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID_2` (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_plugin_widgets_notes`;
CREATE TABLE `s_plugin_widgets_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_premium_dispatch`;
CREATE TABLE `s_premium_dispatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `calculation` int(1) unsigned NOT NULL,
  `surcharge_calculation` int(1) unsigned NOT NULL,
  `tax_calculation` int(11) unsigned NOT NULL,
  `shippingfree` decimal(10,2) unsigned DEFAULT NULL,
  `multishopID` int(11) unsigned DEFAULT NULL,
  `customergroupID` int(11) unsigned DEFAULT NULL,
  `bind_shippingfree` int(1) unsigned NOT NULL,
  `bind_time_from` int(11) unsigned DEFAULT NULL,
  `bind_time_to` int(11) unsigned DEFAULT NULL,
  `bind_instock` int(1) unsigned DEFAULT NULL,
  `bind_laststock` int(1) unsigned NOT NULL,
  `bind_weekday_from` int(1) unsigned DEFAULT NULL,
  `bind_weekday_to` int(1) unsigned DEFAULT NULL,
  `bind_weight_from` decimal(10,3) DEFAULT NULL,
  `bind_weight_to` decimal(10,3) DEFAULT NULL,
  `bind_price_from` decimal(10,2) DEFAULT NULL,
  `bind_price_to` decimal(10,2) DEFAULT NULL,
  `bind_sql` mediumtext COLLATE utf8_unicode_ci,
  `status_link` mediumtext COLLATE utf8_unicode_ci,
  `calculation_sql` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
(9,	'Standard Versand',	0,	'',	'',	1,	0,	0,	3,	0,	NULL,	NULL,	NULL,	0,	NULL,	NULL,	NULL,	0,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL,	'',	NULL);

DROP TABLE IF EXISTS `s_premium_dispatch_attributes`;
CREATE TABLE `s_premium_dispatch_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatchID` (`dispatchID`),
  CONSTRAINT `s_premium_dispatch_attributes_ibfk_1` FOREIGN KEY (`dispatchID`) REFERENCES `s_premium_dispatch` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_premium_dispatch_categories`;
CREATE TABLE `s_premium_dispatch_categories` (
  `dispatchID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_premium_dispatch_countries`;
CREATE TABLE `s_premium_dispatch_countries` (
  `dispatchID` int(11) NOT NULL,
  `countryID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_premium_dispatch_countries` (`dispatchID`, `countryID`) VALUES
(9,	2),
(9,	3),
(9,	4),
(9,	5),
(9,	7),
(9,	8),
(9,	9),
(9,	10),
(9,	11),
(9,	12),
(9,	13),
(9,	14),
(9,	15),
(9,	16),
(9,	18),
(9,	20),
(9,	21),
(9,	22),
(9,	23),
(9,	24),
(9,	25),
(9,	26),
(9,	27),
(9,	28),
(9,	29),
(9,	30),
(9,	31),
(9,	32),
(9,	33),
(9,	34),
(9,	35),
(9,	36),
(9,	37);

DROP TABLE IF EXISTS `s_premium_dispatch_holidays`;
CREATE TABLE `s_premium_dispatch_holidays` (
  `dispatchID` int(11) unsigned NOT NULL,
  `holidayID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`holidayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_premium_dispatch_paymentmeans`;
CREATE TABLE `s_premium_dispatch_paymentmeans` (
  `dispatchID` int(11) NOT NULL,
  `paymentID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`paymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_premium_dispatch_paymentmeans` (`dispatchID`, `paymentID`) VALUES
(9,	2),
(9,	3),
(9,	4),
(9,	5);

DROP TABLE IF EXISTS `s_premium_holidays`;
CREATE TABLE `s_premium_holidays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calculation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_premium_holidays` (`id`, `name`, `calculation`, `date`) VALUES
(1,	'Neujahr',	'DATE(\'01-01\')',	'2011-01-01'),
(3,	'Heilige drei Könige',	'DATE(\'01-06\')',	'2011-01-06'),
(4,	'Rosenmontag',	'DATE_SUB(EASTERDATE(), INTERVAL 48 DAY)',	'2011-03-07'),
(5,	'Josefstag',	'DATE(\'03/19\')',	'2011-03-19'),
(6,	'Karfreitag',	'DATE_SUB(EASTERDATE(), INTERVAL 2 DAY)',	'2011-04-22'),
(7,	'Ostermontag',	'DATE_ADD(EASTERDATE(), INTERVAL 1 DAY)',	'2011-04-25'),
(8,	'Tag der Arbeit',	'DATE(\'05/01\')',	'2011-05-01'),
(9,	'Christi Himmelfahrt',	'DATE_ADD(EASTERDATE(), INTERVAL 39 DAY)',	'2011-06-02'),
(10,	'Pfingstmontag',	'DATE_ADD(EASTERDATE(), INTERVAL 50 DAY)',	'2011-06-13'),
(11,	'Fronleichnam',	'DATE_ADD(EASTERDATE(), INTERVAL 60 DAY)',	'2011-06-23'),
(13,	'Mariä Himmelfahrt',	'DATE(\'08/15\')',	'2011-08-15'),
(14,	'Tag der Deutschen Einheit',	'DATE(\'10/03\')',	'2011-10-03'),
(15,	'Nationalfeiertag (Österreich)',	'DATE(\'10/26\')',	'2010-10-26'),
(16,	'Reformationstag',	'DATE(\'10/31\')',	'2010-10-31'),
(17,	'Allerheiligen',	'DATE(\'11/01\')',	'2010-11-01'),
(18,	'Buß- und Bettag',	'SUBDATE(DATE(\'11-23\'), DAYOFWEEK(DATE(\'11-23\'))+IF(DAYOFWEEK(DATE(\'11-23\'))>4,-4,3))',	'2010-11-17'),
(19,	'Mariä Empfängnis',	'DATE(\'12/8\')',	'2010-12-08'),
(20,	'Heiligabend',	'DATE(\'12/24\')',	'2010-12-24'),
(21,	'1. Weihnachtstag',	'DATE(\'12/25\')',	'2010-12-25'),
(22,	'2. Weihnachtstag (Stephanstag)',	'DATE(\'12/26\')',	'2010-12-26'),
(23,	'Silvester',	'DATE(\'12/31\')',	'2010-12-31');

DROP TABLE IF EXISTS `s_premium_shippingcosts`;
CREATE TABLE `s_premium_shippingcosts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` decimal(10,3) unsigned NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `factor` decimal(10,2) NOT NULL,
  `dispatchID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from` (`from`,`dispatchID`)
) ENGINE=InnoDB AUTO_INCREMENT=236 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_premium_shippingcosts` (`id`, `from`, `value`, `factor`, `dispatchID`) VALUES
(235,	0.000,	3.90,	0.00,	9);

DROP TABLE IF EXISTS `s_product_streams`;
CREATE TABLE `s_product_streams` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `conditions` text COLLATE utf8_unicode_ci,
  `type` int(11) DEFAULT NULL,
  `sorting` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  `sorting_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_product_streams_articles`;
CREATE TABLE `s_product_streams_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stream_id` (`stream_id`,`article_id`),
  KEY `s_product_streams_articles_fk_article_id` (`article_id`),
  CONSTRAINT `s_product_streams_articles_fk_article_id` FOREIGN KEY (`article_id`) REFERENCES `s_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `s_product_streams_articles_fk_stream_id` FOREIGN KEY (`stream_id`) REFERENCES `s_product_streams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_product_streams_attributes`;
CREATE TABLE `s_product_streams_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `streamID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `streamID` (`streamID`),
  CONSTRAINT `s_product_streams_attributes_ibfk_1` FOREIGN KEY (`streamID`) REFERENCES `s_product_streams` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_product_streams_selection`;
CREATE TABLE `s_product_streams_selection` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `stream_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stream_id` (`stream_id`,`article_id`),
  KEY `s_product_streams_selection_fk_article_id` (`article_id`),
  CONSTRAINT `s_product_streams_selection_fk_article_id` FOREIGN KEY (`article_id`) REFERENCES `s_articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `s_product_streams_selection_fk_stream_id` FOREIGN KEY (`stream_id`) REFERENCES `s_product_streams` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_schema_version`;
CREATE TABLE `s_schema_version` (
  `version` int(11) NOT NULL,
  `start_date` datetime NOT NULL,
  `complete_date` datetime DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `error_msg` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_schema_version` (`version`, `start_date`, `complete_date`, `name`, `error_msg`) VALUES
(101,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-extended-editor-field',	NULL),
(102,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-new-emotions',	NULL),
(103,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-field-blog-teaser',	NULL),
(104,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-field-blog-thumbnail-size',	NULL),
(105,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	's_core_sessions_backend',	NULL),
(106,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-path-to-categories',	NULL),
(107,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-legacy-cache-config',	NULL),
(108,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-category-listing-indexes',	NULL),
(109,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-translations',	NULL),
(110,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-disabled-cache-field',	NULL),
(111,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-password-encoder',	NULL),
(112,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'link-cache-menu-to-the-new-performance-module',	NULL),
(113,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'adds-performance-sql',	NULL),
(114,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-new-top-seller',	NULL),
(115,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-denormalized-category-table',	NULL),
(116,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'optimize-emotion-queries',	NULL),
(117,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-article-detail-page-indexes',	NULL),
(118,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'optimize-property-backend-queries',	NULL),
(119,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-batchmode-option',	NULL),
(120,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-cache-log',	NULL),
(121,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-cache-label',	NULL),
(122,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'optimize-search-index-queries',	NULL),
(123,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-dummy-plugins',	NULL),
(124,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-router-url-cache',	NULL),
(125,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'adds-filter-performance-sql',	NULL),
(126,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-cache-menu-items',	NULL),
(127,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'install-http-plugin',	NULL),
(128,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'adds-community-store-resource',	NULL),
(129,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-mailer-config-help-text-typo',	NULL),
(130,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-typo-in-no-script-notices-snippet',	NULL),
(131,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-top-seller-index',	NULL),
(132,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-similar-shown-listener',	NULL),
(133,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-esd-payment-status-config',	NULL),
(134,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-http-cache-cleanup-cronjob',	NULL),
(135,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-main-image-listing-config',	NULL),
(136,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-send-order-mail-config',	NULL),
(137,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-lastarticles-save-time',	NULL),
(138,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-typo-last-articles-headline',	NULL),
(139,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-plugin-form-from-config',	NULL),
(140,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-emotion-grid-id',	NULL),
(141,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-force-canonical-http-config',	NULL),
(142,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-listing-description-config',	NULL),
(143,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'deactivate-router-to-lower-scope',	NULL),
(144,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-require-phone-field-config',	NULL),
(145,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-default-incorrect-translation',	NULL),
(146,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'http-cache-events',	NULL),
(147,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'translations',	NULL),
(148,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'payment-method-refactor',	NULL),
(149,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-sepa-database-items',	NULL),
(150,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-checkout-payment-info-change-config',	NULL),
(151,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-missing-emotion-field-types',	NULL),
(200,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'drop-unused-factory-table',	NULL),
(201,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-supplier-seo',	NULL),
(202,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'unregister-routerold-dead-event',	NULL),
(203,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-http-cache-clear-event',	NULL),
(204,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-supplier-seo-template',	NULL),
(205,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'date-time-picker-label-update',	NULL),
(206,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'change-image-attributes',	NULL),
(207,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'product-feed-export',	NULL),
(208,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'snippet-handling-refactoring',	NULL),
(209,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-meta-title',	NULL),
(210,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-meta-title-to-blog',	NULL),
(211,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-always-secure',	NULL),
(212,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'product-feed-export',	NULL),
(213,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-payment-methods-plugin',	NULL),
(214,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-debit-fields-to-payment-data',	NULL),
(215,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'enlarge-zipcode-and-streetnumber-user',	NULL),
(216,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'enlarge-zipcode-and-streetnumber-billing',	NULL),
(217,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'enlarge-zipcode-and-streetnumber-billing_shipping',	NULL),
(218,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-old-debug-plugins',	NULL),
(219,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'customer-payment-data-editing-in-be',	NULL),
(220,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-payment-data-restriction',	NULL),
(221,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-log-plugin',	NULL),
(222,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-logmail-config',	NULL),
(223,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-statistic-config',	NULL),
(224,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-statistic-impression-table',	NULL),
(225,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-emotion-required-fields',	NULL),
(226,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-tellafriend-remove-option',	NULL),
(227,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-canonical-force-http-translation',	NULL),
(228,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-canonical-force-http-description',	NULL),
(229,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-last-articles-plugin-config-form',	NULL),
(230,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'fix-emotion-mandatory-fields',	NULL),
(231,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-redirectDownload-config-var',	NULL),
(301,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'drop-unused-cms-content-table',	NULL),
(302,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-secure-cron',	NULL),
(303,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-seo-categories',	NULL),
(304,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'widget-system-refactoring',	NULL),
(305,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-product-feed-caching',	NULL),
(306,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-secure-cron-description',	NULL),
(307,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'install-swag-update',	NULL),
(308,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'increase-session-id-fields',	NULL),
(309,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'merchant-email-translation',	NULL),
(310,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'swagupdate-translations',	NULL),
(311,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'subshop-maintenance-support',	NULL),
(312,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-search-form-translation',	NULL),
(313,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-order-detail-fields',	NULL),
(314,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-order-detail-pack-unit',	NULL),
(315,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-shipping-address-country-field-labels',	NULL),
(316,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-seo-nofollow-labels',	NULL),
(317,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-store-api-url',	NULL),
(318,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-payment-methods-version',	NULL),
(319,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'update-google-plugin',	NULL),
(330,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-secure-uninstall-capability',	NULL),
(350,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-template-parent',	NULL),
(351,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-template-menu',	NULL),
(352,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-theme-config',	NULL),
(353,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-horizontal-scrolling-emotion',	NULL),
(354,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'add-video-element',	NULL),
(355,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'remove-config-module-template-manager',	NULL),
(356,	'2019-12-06 10:19:52',	'2019-12-06 10:19:52',	'email-datetime-format',	NULL),
(357,	'2019-12-06 10:19:52',	'2019-12-06 10:19:53',	'add-changed-and-mobileactive-columns',	NULL),
(358,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-registration-field-options',	NULL),
(359,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add_additional_address_data_order_billing',	NULL),
(360,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add_additional_address_data_order_shipping',	NULL),
(361,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'additional_address-config',	NULL),
(362,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add_additional_address_data_user_billing',	NULL),
(363,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add_additional_address_data_user_shipping',	NULL),
(364,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-article-impression',	NULL),
(365,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'google-analytics-plugin-upgrade',	NULL),
(366,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'translations',	NULL),
(367,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'mobile-statistics',	NULL),
(368,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'form-elem-text2-labels',	NULL),
(369,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'change-min-purchase',	NULL),
(370,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'optimize-performance',	NULL),
(371,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-multi-edit-config-options',	NULL),
(372,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'setup-multi-edit',	NULL),
(373,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'address-streetnumber-merge',	NULL),
(374,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-billing-enlarge-street-field',	NULL),
(375,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-billing-merge-street-number',	NULL),
(376,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-billing-drop-street-number',	NULL),
(377,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-shipping-enlarge-street-field',	NULL),
(378,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-shipping-merge-street-number',	NULL),
(379,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-order-shipping-drop-street-number',	NULL),
(380,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-billing-enlarge-street-field',	NULL),
(381,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-billing-merge-street-number',	NULL),
(382,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-billing-drop-street-number',	NULL),
(383,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-shipping-enlarge-street-field',	NULL),
(384,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-shipping-merge-street-number',	NULL),
(385,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	's-user-shipping-drop-street-number',	NULL),
(386,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'import-multiedit-plugin-tables',	NULL),
(387,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'display-shipping-calculations-in-basket',	NULL),
(388,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-fields-position',	NULL),
(389,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-custom-form-field-sorting',	NULL),
(390,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-device-column',	NULL),
(391,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-attr17',	NULL),
(392,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-listerner',	NULL),
(393,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-404-page-config-options',	NULL),
(394,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-company-register-config',	NULL),
(395,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-obsolete-trusted-shops-code',	NULL),
(396,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-family-friendly-meta',	NULL),
(397,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'form-meta-data',	NULL),
(398,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-custom-and-form-seo-template',	NULL),
(399,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-rewrite-table',	NULL),
(400,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'article-list-config-translations',	NULL),
(401,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-table',	NULL),
(402,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-dead-event',	NULL),
(403,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-hint-to-basket-colors',	NULL),
(404,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-sLanguage-from-item-export',	NULL),
(405,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'import-compare-settings',	NULL),
(406,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-advanced-menu-listeners',	NULL),
(407,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'empty-migration-placeholder',	NULL),
(408,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'variant-price-surcharge-refactor',	NULL),
(409,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-property-media',	NULL),
(410,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-fields',	NULL),
(411,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-columns',	NULL),
(412,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-liveshopping-bundle-columns',	NULL),
(413,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'tidy-up-session-tables',	NULL),
(414,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-product-box-layout',	NULL),
(415,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-promotions',	NULL),
(416,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-dummy-plugins',	NULL),
(417,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-routerold-plugin',	NULL),
(418,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'shop-specific-search-statistics',	NULL),
(419,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'extract-acl-service',	NULL),
(420,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-plugin-manager',	NULL),
(421,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-store-api-plugin',	NULL),
(422,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-plugin-categories',	NULL),
(423,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-config-variables',	NULL),
(424,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-deprecated-config-variables',	NULL),
(425,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-old-templates',	NULL),
(426,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'extract-cron-service',	NULL),
(427,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-nostock-config',	NULL),
(428,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-old-instock-config',	NULL),
(429,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'activate-performance-filters',	NULL),
(430,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-shop-page-form-shopid',	NULL),
(431,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'cache-emotion-landing-pages',	NULL),
(432,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'html5-tags-for-snippets',	NULL),
(433,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'emotion-device-column-as-varchar',	NULL),
(434,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-components',	NULL),
(435,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'mark-deprecated-fields-emotions',	NULL),
(436,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-html5-video-fields',	NULL),
(437,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-dirty-flag-email-translations',	NULL),
(438,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-email-template-header-footer-fields',	NULL),
(439,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-document-footer-styling',	NULL),
(440,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-sorder-email-template',	NULL),
(441,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-hide-downloads-in-account',	NULL),
(442,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-option-to-disable-styling-emotions',	NULL),
(443,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-wizard-configuration-value',	NULL),
(444,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-imprint-to-bottom-shop-group',	NULL),
(445,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'import-vrrl-plugin-settings',	NULL),
(446,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'easy-registration-process',	NULL),
(447,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-tag-cloud-defaults',	NULL),
(448,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-first-run-wizard-snippet',	NULL),
(449,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-pagination-seo',	NULL),
(450,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-high-dpi-album-settings',	NULL),
(451,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-email-header-field',	NULL),
(452,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-styling-classes-main-menu',	NULL),
(453,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-table-encoding',	NULL),
(454,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'rename-theme-manager',	NULL),
(455,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'cache-invalidate-variants',	NULL),
(456,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'emotion-worlds-cache-invalidation',	NULL),
(457,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-thumbnail-config',	NULL),
(458,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-selfhealing-plugin',	NULL),
(459,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'translate-form-fields',	NULL),
(460,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-email-header-field',	NULL),
(461,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-sorder-email-thumbnails',	NULL),
(462,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-position-column',	NULL),
(463,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'translate-emotion-html-video',	NULL),
(464,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-feed-dirty-flag',	NULL),
(465,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'token-secret',	NULL),
(466,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'supplier-seo-url',	NULL),
(467,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-listing-seo-url',	NULL),
(468,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-feeds-thumbnails-size',	NULL),
(469,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-404-article-page-config',	NULL),
(470,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'update-price-variation-column-type',	NULL),
(471,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-attr17',	NULL),
(472,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-landing-page-parent',	NULL),
(473,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-zip-city-flip-option',	NULL),
(474,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'switch-zipcode-city-emails',	NULL),
(475,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-missing-emails',	NULL),
(476,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-less-compatible-flag',	NULL),
(477,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'disable-tag-cloud-for-installation',	NULL),
(478,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-banner-title-attr',	NULL),
(479,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'implement-after-update-wizard',	NULL),
(480,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'translate-multi-edit-config-form',	NULL),
(481,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-vat-validation-from-core',	NULL),
(482,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-horizontal-scrolling',	NULL),
(483,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'set-device-type-nullable',	NULL),
(484,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-seo-title-to-categories',	NULL),
(485,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-new-sprites-media-manager-albums',	NULL),
(486,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'generate-mobile-sitemap',	NULL),
(487,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-service-product-snippet',	NULL),
(488,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'reset-search-index',	NULL),
(489,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-backend-menu-icons',	NULL),
(490,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-email-templates-images-height',	NULL),
(491,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'change-enabled-payment-methods',	NULL),
(492,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unnecessary-data-from-base-install',	NULL),
(493,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-mail-translations',	NULL),
(494,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-cheapest-price-calculation-config',	NULL),
(495,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-shopping-worlds-grid',	NULL),
(496,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-cheapest-price-selection-config',	NULL),
(497,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-email-payment-method-description',	NULL),
(498,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-and-add-acl-privileges',	NULL),
(499,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-swag-update-acl-privileges',	NULL),
(500,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'destroy-bot-sessions',	NULL),
(501,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-added-column-in-mailaddresses',	NULL),
(502,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-unused-voucher-table',	NULL),
(503,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'rename-multi-edit',	NULL),
(504,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-customergroup-index',	NULL),
(505,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-voucher-indexes',	NULL),
(506,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'password-reset',	NULL),
(600,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-support-max-generated-similar-products',	NULL),
(601,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'implement-elastic-search',	NULL),
(602,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-premium-plugins-menu-item',	NULL),
(603,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-product-streams',	NULL),
(604,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'move-discount-surchage-names-into-snippets',	NULL),
(605,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-and-search-config',	NULL),
(606,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-state-name-column',	NULL),
(607,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-rss-feed-widget',	NULL),
(608,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-product-stream-icon',	NULL),
(609,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-media-recycling',	NULL),
(610,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-deprecated-adodb',	NULL),
(611,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-deprecated-api',	NULL),
(612,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-deprecated-multilanguage',	NULL),
(613,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-media-meta',	NULL),
(614,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-document-logo-path',	NULL),
(615,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'password-reset',	NULL),
(616,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'product-stream-emotion-field',	NULL),
(617,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-html5-emotion-handler',	NULL),
(618,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-trash-album-settings',	NULL),
(619,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-widgets-listing-cache-tag',	NULL),
(620,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-delivered-in-future-column',	NULL),
(621,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-filter-options',	NULL),
(622,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-seo-title',	NULL),
(623,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-payment-config-item',	NULL),
(624,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-null-tax-rules',	NULL),
(625,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'rename-feedback',	NULL),
(626,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-email-template-variables',	NULL),
(627,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-order-email-voucher-image',	NULL),
(628,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-validation-index',	NULL),
(629,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'implement-ajax-timeout',	NULL),
(630,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-google-analytics',	NULL),
(631,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-search-results-per-page',	NULL),
(632,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'optimize-hide-no-instock-label',	NULL),
(633,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-index-for-image-mappings',	NULL),
(634,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-index-for-basket-ordernumber',	NULL),
(635,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-index-for-plugin-id',	NULL),
(636,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'streamline-german-email-wording',	NULL),
(637,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'deactivate-similar-products',	NULL),
(638,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'fix-serialized-data',	NULL),
(700,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-filter-values',	NULL),
(701,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-emotion-backend-options',	NULL),
(702,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-emotion-element-css-class',	NULL),
(703,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'activate-html-code-widget-by-default',	NULL),
(704,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-referercheck',	NULL),
(705,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'rename-category-template-column',	NULL),
(706,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-debit-table',	NULL),
(707,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-new-emotion-link-target-field',	NULL),
(708,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'attribute-administration',	NULL),
(709,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'add-user-addresses',	NULL),
(710,	'2019-12-06 10:19:53',	'2019-12-06 10:19:53',	'remove-fax-field',	NULL),
(711,	'2019-12-06 10:19:53',	'2019-12-06 10:19:54',	'create-address-migrate-table',	NULL),
(712,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-user-billing',	NULL),
(713,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-user-shipping',	NULL),
(714,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-order-billing',	NULL),
(715,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-order-shipping',	NULL),
(716,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'copy-addresses-to-addressbook',	NULL),
(717,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'set-default-billingaddresses',	NULL),
(718,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'set-default-shippingaddresses',	NULL),
(719,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'create-address-attribute-tables',	NULL),
(720,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-order-billing-attributes',	NULL),
(721,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-order-shipping-attributes',	NULL),
(722,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-user-shipping-attributes',	NULL),
(723,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-user-billing-attributes',	NULL),
(724,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'cleanup-address-migration',	NULL),
(725,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-license-plugin-from-initial-db',	NULL),
(726,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-shipping-billing',	NULL),
(727,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'core-license-config',	NULL),
(728,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'create-s-user-columns',	NULL),
(729,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-additional-userdata',	NULL),
(730,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'drop-birthday-column',	NULL),
(731,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-emotion-grids',	NULL),
(732,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-landingpage-teaser',	NULL),
(733,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-translatable-field-with-data',	NULL),
(734,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'new-emotion-shop-association',	NULL),
(735,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-old-emotion-relation',	NULL),
(736,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-article-widget-categorie-selection',	NULL),
(737,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-emotion-element-viewport',	NULL),
(738,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixed-iframe-widget-xtype',	NULL),
(739,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-emotion-widget-settings',	NULL),
(740,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'new-border-setting-for-emotion-widgets',	NULL),
(741,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-salutation-mails',	NULL),
(742,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-title-user-billing',	NULL),
(743,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-title-order-billing',	NULL),
(744,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-title-order-shipping',	NULL),
(745,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-title-user-shipping',	NULL),
(746,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-shipping',	NULL),
(747,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'ekw-mode-migration',	NULL),
(748,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-unused-fields',	NULL),
(749,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'make-ustid-nullable',	NULL),
(750,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-article-details-base-price',	NULL),
(751,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'drop-unused-menu-fields',	NULL),
(752,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'delete-payment-plugin',	NULL),
(753,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-s-user-customer-number',	NULL),
(754,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'update-order-billing-customer-number',	NULL),
(755,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'migrate-customer-number',	NULL),
(756,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'drop-customer-number',	NULL),
(757,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-array-store-field',	NULL),
(758,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-attributes-read-acl',	NULL),
(759,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-noviewselect',	NULL),
(760,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'change-plugin-unique-key',	NULL),
(761,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'change-performance-menu',	NULL),
(762,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'increase-img-character-size',	NULL),
(763,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-attributes-read-acl',	NULL),
(764,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-addon-premiums',	NULL),
(765,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-article-configurator-templates',	NULL),
(766,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-article-details',	NULL),
(767,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-campaigns-articles',	NULL),
(768,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-order',	NULL),
(769,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-order-basket',	NULL),
(770,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-order-details',	NULL),
(771,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-ordernumber-schema-order-notes',	NULL),
(772,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'allow-label-nullable',	NULL),
(773,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-library-component-fields',	NULL),
(774,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-extra-config-elements',	NULL),
(775,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-asset-version-config-element',	NULL),
(776,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fix-alter-ignore-migrations',	NULL),
(777,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fix-invalid-birthdays',	NULL),
(778,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-attribtue-default-value',	NULL),
(779,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fixing-bot-detection-ios',	NULL),
(780,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fix-debit-payment',	NULL),
(781,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'update-forum-link',	NULL),
(782,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-partner-idcode-index',	NULL),
(783,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'increase-media-path-columns',	NULL),
(784,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-paging-to-notfoundfield',	NULL),
(785,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-connect-default-menu',	NULL),
(786,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'extend-api-cache-invalidation',	NULL),
(787,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-missing-translation-basicsettings-login-and-registration',	NULL),
(788,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'change-Seo-Router-description',	NULL),
(789,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-crontab-disableonerror',	NULL),
(790,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'clarify-basic-settings-label',	NULL),
(791,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'increase-password-hash-size',	NULL),
(792,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-send-registration-email-confirmation-config',	NULL),
(793,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-attribute-table-for-search',	NULL),
(794,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'fix-shop-page-limiting',	NULL),
(795,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-salutation-snippets',	NULL),
(796,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-uniqueid-config-element',	NULL),
(797,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-salutation-snippets',	NULL),
(798,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-sytem-log-acl',	NULL),
(799,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-1-of-10',	NULL),
(800,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-2-of-10',	NULL),
(801,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-3-of-10',	NULL),
(802,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-4-of-10',	NULL),
(803,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-5-of-10',	NULL),
(804,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-6-of-10',	NULL),
(805,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-7-of-10',	NULL),
(806,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-8-of-10',	NULL),
(807,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-9-of-10',	NULL),
(808,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'attribute-translation-fix-10-of-10',	NULL),
(809,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'change-session-tables',	NULL),
(810,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'change-description-of-article-cover-setting',	NULL),
(811,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-attributes-partner',	NULL),
(812,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-max-limit-config',	NULL),
(813,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'session-expiry',	NULL),
(814,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'create-configurator-options-attribute-table',	NULL),
(815,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-strip-tags-configuration-option',	NULL),
(816,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'short-cut-menu-plugin-manager',	NULL),
(817,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-plugin-translations',	NULL),
(818,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'make-some-fields-in-order-table-nullable',	NULL),
(819,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'shortcut-help-menu',	NULL),
(820,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'plugin-safe-mode',	NULL),
(900,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-last-articles-config-elements',	NULL),
(901,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-vote-shop-id',	NULL),
(902,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-import-export-legacy-module',	NULL),
(903,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-menu-import-export-advanced',	NULL),
(904,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-captcha-selection',	NULL),
(905,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-backend-listing-index',	NULL),
(906,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'remove-countryshipping-registration-config-option',	NULL),
(907,	'2019-12-06 10:19:54',	'2019-12-06 10:19:54',	'add-signed-basket',	NULL),
(908,	'2019-12-06 10:19:54',	'2019-12-06 10:19:55',	'change-varchar-attributes',	NULL),
(909,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-buy-button-config',	NULL),
(910,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cookie-permissions',	NULL),
(911,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-facet-behavior-switch',	NULL),
(912,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-mail-template',	NULL),
(913,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'force-category-selection-for-category-teaser',	NULL),
(914,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-preview-id-for-emotions',	NULL),
(916,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-capcha-to-register-page',	NULL),
(917,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-sorting-module',	NULL),
(918,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-facet-module',	NULL),
(919,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-article-emotion-elements',	NULL),
(920,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-description-to-linear-meter',	NULL),
(921,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-shipping-free-flag',	NULL),
(922,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-instock',	NULL),
(923,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-customer-streams',	NULL),
(924,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cache-control',	NULL),
(925,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-user-config',	NULL),
(926,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-emotion-presets',	NULL),
(928,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-captcha-to-newsletter-page',	NULL),
(929,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-stream-rules',	NULL),
(930,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'fix-variant-weight',	NULL),
(931,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-slt-config',	NULL),
(932,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-http-cache-route',	NULL),
(933,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-customer-stream-menu',	NULL),
(934,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-stream-attributes',	NULL),
(935,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-listing-layout-config',	NULL),
(936,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-stream-index',	NULL),
(937,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-type-field',	NULL),
(938,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-preset-emotion-translations',	NULL),
(939,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-translations-for-facets',	NULL),
(940,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'fix-import-export-menu-entry',	NULL),
(941,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'replace-default-document-logo',	NULL),
(942,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-shipping-acl',	NULL),
(943,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-new-colum-search-fields',	NULL),
(944,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'update-german-translations',	NULL),
(945,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-category-column-external-target',	NULL),
(946,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-customer-stream-type',	NULL),
(947,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-ordercode-field-length-in_s_marketing_vouchers',	NULL),
(948,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'update-customer-stream-conditions',	NULL),
(949,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-deprecation-hint-to-maxpages-form',	NULL),
(951,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-shipping-phone',	NULL),
(952,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'implement-backend-menu-configuration',	NULL),
(953,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'implement-growl-message-configuration',	NULL),
(954,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'edit-company-register-config',	NULL),
(955,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'rename-payment-order-state',	NULL),
(956,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'seperate-logmail-address',	NULL),
(957,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'site-active-status',	NULL),
(958,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-http-cache-events-for-site',	NULL),
(959,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'fix-emotion-default-value',	NULL),
(960,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-article-shippingtime-translation',	NULL),
(961,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'update-email-templates',	NULL),
(962,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-ordernumber-form-field',	NULL),
(963,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'update-emotion-url-template',	NULL),
(964,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'translate-slt-setting',	NULL),
(965,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-album-garbage-collect-option',	NULL),
(1200,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-productbox-for-manufacturer',	NULL),
(1201,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-secure-shop-config',	NULL),
(1202,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-maxpages-formelement',	NULL),
(1203,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-blog-indexpage-configuration',	NULL),
(1204,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-variant-filter',	NULL),
(1205,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-canonical-http-setting',	NULL),
(1206,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-laststock-field-to-variants',	NULL),
(1207,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-new-seo-alias-for-variants',	NULL),
(1208,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-checkout-seo-routes',	NULL),
(1209,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-error-mail-log-level-config-option',	NULL),
(1210,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'fix-config-table-index',	NULL),
(1211,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-description-of-article-cover-setting',	NULL),
(1212,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'correct-typo-in-newsletter-backend-config',	NULL),
(1213,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-laststock-on-variant-template-generation',	NULL),
(1214,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'improve-email-header-field',	NULL),
(1215,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'form-active-status',	NULL),
(1216,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-shopware-connect-menu',	NULL),
(1217,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-smtp-password-fieldtype',	NULL),
(1218,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-privacy-options',	NULL),
(1219,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cron-for-personal-data-cleanup',	NULL),
(1221,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-double-opt-in-dates',	NULL),
(1222,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'privacy-checkbox-note',	NULL),
(1223,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-ip-anonymization',	NULL),
(1224,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'cleanup-optin-table',	NULL),
(1225,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-double-opt-in-register',	NULL),
(1226,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-blog-comment-mailtemplate',	NULL),
(1227,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-minimum-order-surcharge',	NULL),
(1228,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'soptinregister-english-on-update',	NULL),
(1229,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'accountless-optin-switch',	NULL),
(1230,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cookie-removal',	NULL),
(1231,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-description-to-hideNoInStock-config-element',	NULL),
(1400,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-benchmark-config-table',	NULL),
(1401,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-benchmark-menu',	NULL),
(1402,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-benchmark-teaser-config',	NULL),
(1403,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'implement-document-type-key',	NULL),
(1404,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'remove-language-distinction-from-shop-pages',	NULL),
(1405,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-es-backend-backlog',	NULL),
(1406,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-changed-timestamp-columns',	NULL),
(1407,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-english-translations',	NULL),
(1408,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'proportional-tax-calculation',	NULL),
(1409,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-shipping-tax-column',	NULL),
(1410,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-sqli-privilege',	NULL),
(1411,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-order-documents-key',	NULL),
(1413,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'href-lang-configuration',	NULL),
(1414,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-statistics-acl-role',	NULL),
(1415,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-necessary-columns-to-bi',	NULL),
(1416,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-article-detail-id-to-s-order-details',	NULL),
(1418,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-sitemap-configuration',	NULL),
(1419,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cron-for-personal-data-cleanup',	NULL),
(1421,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-double-opt-in-dates',	NULL),
(1422,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'privacy-checkbox-note',	NULL),
(1423,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-ip-anonymization',	NULL),
(1424,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'cleanup-optin-table',	NULL),
(1425,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-double-opt-in-register',	NULL),
(1426,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-blog-comment-mailtemplate',	NULL),
(1427,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-minimum-order-surcharge',	NULL),
(1428,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'soptinregister-english-on-update',	NULL),
(1429,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'accountless-optin-switch',	NULL),
(1430,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-cookie-removal',	NULL),
(1431,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-description-to-hideNoInStock-config-element',	NULL),
(1432,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'improve-plugin-manager',	NULL),
(1433,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-analytics-last-id',	NULL),
(1434,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-href-default-selection',	NULL),
(1435,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'update-esd-helptext',	NULL),
(1436,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'document-type-mail-templates',	NULL),
(1437,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-bi-last-update-date-column',	NULL),
(1438,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'add-bi-widgets',	NULL),
(1439,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-back-document-id-to-tmp',	NULL),
(1440,	'2019-12-06 10:19:55',	'2019-12-06 10:19:55',	'change-document-id-to-lowercase',	NULL);

DROP TABLE IF EXISTS `s_search_custom_facet`;
CREATE TABLE `s_search_custom_facet` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` int(1) unsigned NOT NULL,
  `unique_key` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_in_categories` int(1) unsigned NOT NULL,
  `deletable` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `facet` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_identifier` (`unique_key`),
  KEY `sorting` (`display_in_categories`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_search_custom_facet` (`id`, `active`, `unique_key`, `display_in_categories`, `deletable`, `position`, `name`, `facet`) VALUES
(1,	1,	'CategoryFacet',	0,	0,	1,	'Kategorien',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\CategoryFacet\":{\"label\":\"Kategorien\", \"depth\": \"2\"}}'),
(2,	1,	'ImmediateDeliveryFacet',	1,	0,	2,	'Sofort lieferbar',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ImmediateDeliveryFacet\":{\"label\":\"Sofort lieferbar\"}}'),
(3,	1,	'ManufacturerFacet',	1,	0,	3,	'Hersteller',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ManufacturerFacet\":{\"label\":\"Hersteller\"}}'),
(4,	1,	'PriceFacet',	1,	0,	4,	'Preis',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\PriceFacet\":{\"label\":\"Preis\"}}'),
(5,	1,	'PropertyFacet',	1,	0,	5,	'Eigenschaften',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\PropertyFacet\":[]}'),
(6,	1,	'ShippingFreeFacet',	1,	0,	6,	'Versandkostenfrei',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ShippingFreeFacet\":{\"label\":\"Versandkostenfrei\"}}'),
(7,	1,	'VoteAverageFacet',	1,	0,	7,	'Bewertungen',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\VoteAverageFacet\":{\"label\":\"Bewertung\"}}'),
(8,	0,	'WeightFacet',	1,	0,	8,	'Gewicht',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\WeightFacet\":{\"label\":\"Gewicht\",\"suffix\":\"kg\",\"digits\":2}}'),
(9,	0,	'WidthFacet',	1,	0,	9,	'Breite',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\WidthFacet\":{\"label\":\"Breite\",\"suffix\":\"cm\",\"digits\":2}}'),
(10,	0,	'HeightFacet',	1,	0,	10,	'Höhe',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\HeightFacet\":{\"label\":\"Höhe\",\"suffix\":\"cm\",\"digits\":2}}'),
(11,	0,	'LengthFacet',	1,	0,	11,	'Länge',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\LengthFacet\":{\"label\":\"Länge\",\"suffix\":\"cm\",\"digits\":2}}'),
(12,	0,	'VariantFacet',	1,	0,	11,	'Varianten',	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\VariantFacet\":{\"groupIds\":\"\", \"expandGroupIds\":\"\"}}');

DROP TABLE IF EXISTS `s_search_custom_sorting`;
CREATE TABLE `s_search_custom_sorting` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `display_in_categories` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `sortings` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sorting` (`display_in_categories`,`position`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_search_custom_sorting` (`id`, `label`, `active`, `display_in_categories`, `position`, `sortings`) VALUES
(1,	'Erscheinungsdatum',	1,	1,	-10,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\ReleaseDateSorting\":{\"direction\":\"DESC\"}}'),
(2,	'Beliebtheit',	1,	1,	1,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\PopularitySorting\":{\"direction\":\"DESC\"}}'),
(3,	'Niedrigster Preis',	1,	1,	2,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\PriceSorting\":{\"direction\":\"ASC\"}}'),
(4,	'Höchster Preis',	1,	1,	3,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\PriceSorting\":{\"direction\":\"DESC\"}}'),
(5,	'Artikelbezeichnung',	1,	1,	4,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\ProductNameSorting\":{\"direction\":\"ASC\"}}'),
(7,	'Beste Ergebnisse',	1,	0,	6,	'{\"Shopware\\\\Bundle\\\\SearchBundle\\\\Sorting\\\\SearchRankingSorting\":{\"direction\":\"DESC\"}}');

DROP TABLE IF EXISTS `s_search_fields`;
CREATE TABLE `s_search_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `relevance` int(11) NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tableID` int(11) NOT NULL,
  `do_not_split` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`tableID`),
  KEY `tableID` (`tableID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_search_fields` (`id`, `name`, `relevance`, `field`, `tableID`, `do_not_split`) VALUES
(1,	'Kategorie-Keywords',	10,	'metakeywords',	2,	0),
(2,	'Kategorie-Überschrift',	70,	'description',	2,	0),
(3,	'Artikel-Name',	400,	'name',	1,	0),
(4,	'Artikel-Keywords',	10,	'keywords',	1,	0),
(5,	'Artikel-Bestellnummer',	50,	'ordernumber',	4,	0),
(6,	'Hersteller-Name',	45,	'name',	3,	0),
(7,	'Artikel-Name Übersetzung',	50,	'name',	5,	0),
(8,	'Artikel-Keywords Übersetzung',	10,	'keywords',	5,	0);

DROP TABLE IF EXISTS `s_search_index`;
CREATE TABLE `s_search_index` (
  `keywordID` int(11) NOT NULL,
  `fieldID` int(11) NOT NULL,
  `elementID` int(11) NOT NULL,
  PRIMARY KEY (`keywordID`,`fieldID`,`elementID`),
  KEY `clean_up_index` (`keywordID`,`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_search_keywords`;
CREATE TABLE `s_search_keywords` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `soundex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `soundex` (`soundex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_search_tables`;
CREATE TABLE `s_search_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referenz_table` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foreign_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `where` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `s_search_tables` (`id`, `table`, `referenz_table`, `foreign_key`, `where`) VALUES
(1,	's_articles',	NULL,	NULL,	NULL),
(2,	's_categories',	's_articles_categories',	'categoryID',	NULL),
(3,	's_articles_supplier',	NULL,	'supplierID',	NULL),
(4,	's_articles_details',	's_articles_details',	'id',	NULL),
(5,	's_articles_translations',	NULL,	NULL,	NULL),
(6,	's_articles_attributes',	NULL,	NULL,	NULL);

DROP TABLE IF EXISTS `s_statistics_article_impression`;
CREATE TABLE `s_statistics_article_impression` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleId` int(11) unsigned NOT NULL,
  `shopId` int(11) unsigned NOT NULL,
  `date` date NOT NULL DEFAULT '0000-00-00',
  `impressions` int(11) NOT NULL,
  `deviceType` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'desktop',
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleId_2` (`articleId`,`shopId`,`date`,`deviceType`),
  KEY `articleId` (`articleId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_statistics_currentusers`;
CREATE TABLE `s_statistics_currentusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `page` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `deviceType` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'desktop',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_statistics_pool`;
CREATE TABLE `s_statistics_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_statistics_referer`;
CREATE TABLE `s_statistics_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_statistics_search`;
CREATE TABLE `s_statistics_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `results` int(11) NOT NULL,
  `shop_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `searchterm` (`searchterm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `s_statistics_visitors`;
CREATE TABLE `s_statistics_visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopID` int(11) NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `pageimpressions` int(11) NOT NULL DEFAULT '0',
  `uniquevisits` int(11) NOT NULL DEFAULT '0',
  `deviceType` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'desktop',
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user`;
CREATE TABLE `s_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(1024) COLLATE utf8_unicode_ci NOT NULL,
  `encoder` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'md5',
  `email` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `accountmode` int(11) NOT NULL,
  `confirmationkey` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `paymentID` int(11) NOT NULL DEFAULT '0',
  `doubleOptinRegister` tinyint(1) DEFAULT '0',
  `doubleOptinEmailSentDate` datetime DEFAULT NULL,
  `doubleOptinConfirmDate` datetime DEFAULT NULL,
  `firstlogin` date NOT NULL DEFAULT '0000-00-00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessionID` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `validation` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `affiliate` int(10) NOT NULL DEFAULT '0',
  `customergroup` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `paymentpreset` int(11) NOT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `subshopID` int(11) NOT NULL,
  `referer` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pricegroupID` int(11) unsigned DEFAULT NULL,
  `internalcomment` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `failedlogins` int(11) NOT NULL,
  `lockeduntil` datetime DEFAULT NULL,
  `default_billing_address_id` int(11) DEFAULT NULL,
  `default_shipping_address_id` int(11) DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `customernumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login_token` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `changed` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `sessionID` (`sessionID`),
  KEY `firstlogin` (`firstlogin`),
  KEY `lastlogin` (`lastlogin`),
  KEY `pricegroupID` (`pricegroupID`),
  KEY `customergroup` (`customergroup`),
  KEY `validation` (`validation`),
  KEY `default_billing_address_id` (`default_billing_address_id`),
  KEY `default_shipping_address_id` (`default_shipping_address_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_addresses`;
CREATE TABLE `s_user_addresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci DEFAULT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `country_id` int(11) NOT NULL,
  `state_id` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `country_id` (`country_id`),
  KEY `state_id` (`state_id`),
  CONSTRAINT `s_user_addresses_ibfk_1` FOREIGN KEY (`country_id`) REFERENCES `s_core_countries` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `s_user_addresses_ibfk_2` FOREIGN KEY (`state_id`) REFERENCES `s_core_countries_states` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `s_user_addresses_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_addresses_attributes`;
CREATE TABLE `s_user_addresses_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `address_id` int(11) NOT NULL,
  `text1` text COLLATE utf8_unicode_ci,
  `text2` text COLLATE utf8_unicode_ci,
  `text3` text COLLATE utf8_unicode_ci,
  `text4` text COLLATE utf8_unicode_ci,
  `text5` text COLLATE utf8_unicode_ci,
  `text6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `address_id` (`address_id`),
  CONSTRAINT `s_user_addresses_attributes_ibfk_1` FOREIGN KEY (`address_id`) REFERENCES `s_user_addresses` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_attributes`;
CREATE TABLE `s_user_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`),
  CONSTRAINT `s_user_attributes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_billingaddress`;
CREATE TABLE `s_user_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `stateID` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_billingaddress_attributes`;
CREATE TABLE `s_user_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` text COLLATE utf8_unicode_ci,
  `text2` text COLLATE utf8_unicode_ci,
  `text3` text COLLATE utf8_unicode_ci,
  `text4` text COLLATE utf8_unicode_ci,
  `text5` text COLLATE utf8_unicode_ci,
  `text6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`),
  CONSTRAINT `s_user_billingaddress_attributes_ibfk_1` FOREIGN KEY (`billingID`) REFERENCES `s_user_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_shippingaddress`;
CREATE TABLE `s_user_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) DEFAULT NULL,
  `stateID` int(11) DEFAULT NULL,
  `additional_address_line1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additional_address_line2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


DROP TABLE IF EXISTS `s_user_shippingaddress_attributes`;
CREATE TABLE `s_user_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` text COLLATE utf8_unicode_ci,
  `text2` text COLLATE utf8_unicode_ci,
  `text3` text COLLATE utf8_unicode_ci,
  `text4` text COLLATE utf8_unicode_ci,
  `text5` text COLLATE utf8_unicode_ci,
  `text6` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`),
  CONSTRAINT `s_user_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_user_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


-- 2019-12-06 09:22:52
