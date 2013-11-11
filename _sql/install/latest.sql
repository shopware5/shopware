SET NAMES 'utf8';
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_addon_premiums`
--

DROP TABLE IF EXISTS `s_addon_premiums`;
CREATE TABLE IF NOT EXISTS `s_addon_premiums` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `startprice` double NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ordernumber_export` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `subshopID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles`
--

DROP TABLE IF EXISTS `s_articles`;
CREATE TABLE IF NOT EXISTS `s_articles` (
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
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_attributes`
--

DROP TABLE IF EXISTS `s_articles_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned DEFAULT NULL,
  `articledetailsID` int(11) unsigned DEFAULT NULL,
  `attr1` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr3` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr7` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr8` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr9` mediumtext COLLATE utf8_unicode_ci,
  `attr10` mediumtext COLLATE utf8_unicode_ci,
  `attr11` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr12` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr13` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr14` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr15` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr16` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr17` date DEFAULT NULL,
  `attr18` mediumtext COLLATE utf8_unicode_ci,
  `attr19` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr20` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articledetailsID` (`articledetailsID`),
  KEY `articleID` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_avoid_customergroups`
--

DROP TABLE IF EXISTS `s_articles_avoid_customergroups`;
CREATE TABLE IF NOT EXISTS `s_articles_avoid_customergroups` (
  `articleID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  UNIQUE KEY `articleID` (`articleID`,`customergroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_categories`
--

DROP TABLE IF EXISTS `s_articles_categories`;
CREATE TABLE IF NOT EXISTS `s_articles_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`categoryID`),
  KEY `categoryID` (`categoryID`),
  KEY `articleID_2` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_details`
--

DROP TABLE IF EXISTS `s_articles_details`;
CREATE TABLE IF NOT EXISTS `s_articles_details` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL DEFAULT '0',
  `ordernumber` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `suppliernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `kind` int(1) NOT NULL DEFAULT '0',
  `additionaltext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `active` int(11) unsigned NOT NULL DEFAULT '0',
  `instock` int(11) DEFAULT NULL,
  `stockmin` int(11) unsigned DEFAULT NULL,
  `weight` decimal(10,3) unsigned DEFAULT NULL,
  `position` int(11) unsigned NOT NULL,
  `width` decimal(10,3) unsigned DEFAULT NULL,
  `height` decimal(10,3) unsigned DEFAULT NULL,
  `length` decimal(10,3) unsigned DEFAULT NULL,
  `ean` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `unitID` int(11) unsigned DEFAULT NULL,
  `purchasesteps` int(11) unsigned DEFAULT NULL,
  `maxpurchase` int(11) unsigned DEFAULT NULL,
  `minpurchase` int(11) unsigned DEFAULT NULL,
  `purchaseunit` decimal(10,3) unsigned DEFAULT NULL,
  `referenceunit` decimal(10,3) unsigned DEFAULT NULL,
  `packunit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `releasedate` date DEFAULT NULL,
  `shippingfree` int(1) unsigned NOT NULL DEFAULT '0',
  `shippingtime` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordernumber` (`ordernumber`),
  KEY `articleID` (`articleID`),
  KEY `releasedate` (`releasedate`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_downloads`
--

DROP TABLE IF EXISTS `s_articles_downloads`;
CREATE TABLE IF NOT EXISTS `s_articles_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `size` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_downloads_attributes`
--

DROP TABLE IF EXISTS `s_articles_downloads_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_downloads_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `downloadID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `downloadID` (`downloadID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_esd`
--

DROP TABLE IF EXISTS `s_articles_esd`;
CREATE TABLE IF NOT EXISTS `s_articles_esd` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_esd_attributes`
--

DROP TABLE IF EXISTS `s_articles_esd_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_esd_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `esdID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `esdID` (`esdID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_esd_serials`
--

DROP TABLE IF EXISTS `s_articles_esd_serials`;
CREATE TABLE IF NOT EXISTS `s_articles_esd_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialnumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `esdID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `esdID` (`esdID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_img`
--

DROP TABLE IF EXISTS `s_articles_img`;
CREATE TABLE IF NOT EXISTS `s_articles_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) DEFAULT NULL,
  `img` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
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
  KEY `media_id` (`media_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_img_attributes`
--

DROP TABLE IF EXISTS `s_articles_img_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_img_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imageID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `imageID` (`imageID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_information`
--

DROP TABLE IF EXISTS `s_articles_information`;
CREATE TABLE IF NOT EXISTS `s_articles_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hauptid` (`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_information_attributes`
--

DROP TABLE IF EXISTS `s_articles_information_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_information_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `informationID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `informationID` (`informationID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_notification`
--

DROP TABLE IF EXISTS `s_articles_notification`;
CREATE TABLE IF NOT EXISTS `s_articles_notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `send` int(1) unsigned NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopLink` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_prices`
--

DROP TABLE IF EXISTS `s_articles_prices`;
CREATE TABLE IF NOT EXISTS `s_articles_prices` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_prices_attributes`
--

DROP TABLE IF EXISTS `s_articles_prices_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_prices_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `priceID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priceID` (`priceID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_relationships`
--

DROP TABLE IF EXISTS `s_articles_relationships`;
CREATE TABLE IF NOT EXISTS `s_articles_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_similar`
--

DROP TABLE IF EXISTS `s_articles_similar`;
CREATE TABLE IF NOT EXISTS `s_articles_similar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_supplier`
--

DROP TABLE IF EXISTS `s_articles_supplier`;
CREATE TABLE IF NOT EXISTS `s_articles_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `img` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_supplier_attributes`
--

DROP TABLE IF EXISTS `s_articles_supplier_attributes`;
CREATE TABLE IF NOT EXISTS `s_articles_supplier_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `supplierID` (`supplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_translations`
--

DROP TABLE IF EXISTS `s_articles_translations`;
CREATE TABLE IF NOT EXISTS `s_articles_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL,
  `languageID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `keywords` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description_long` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `description_clear` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `attr1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr3` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr4` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attr5` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`languageID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13928 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_vote`
--

DROP TABLE IF EXISTS `s_articles_vote`;
CREATE TABLE IF NOT EXISTS `s_articles_vote` (
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
  `answer_date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_dependencies`
--

DROP TABLE IF EXISTS `s_article_configurator_dependencies`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_dependencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `configurator_set_id` int(10) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `child_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_groups`
--

DROP TABLE IF EXISTS `s_article_configurator_groups`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_options`
--

DROP TABLE IF EXISTS `s_article_configurator_options`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_option_relations`
--

DROP TABLE IF EXISTS `s_article_configurator_option_relations`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_option_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`article_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_price_surcharges`
--

DROP TABLE IF EXISTS `s_article_configurator_price_surcharges`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_price_surcharges` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `configurator_set_id` int(10) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `child_id` int(11) unsigned DEFAULT NULL,
  `surcharge` decimal(10,3) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_sets`
--

DROP TABLE IF EXISTS `s_article_configurator_sets`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_sets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_set_group_relations`
--

DROP TABLE IF EXISTS `s_article_configurator_set_group_relations`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_set_group_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(11) unsigned DEFAULT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `set_id` (`set_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_configurator_set_option_relations`
--

DROP TABLE IF EXISTS `s_article_configurator_set_option_relations`;
CREATE TABLE IF NOT EXISTS `s_article_configurator_set_option_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(11) unsigned DEFAULT NULL,
  `option_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_img_mappings`
--

DROP TABLE IF EXISTS `s_article_img_mappings`;
CREATE TABLE IF NOT EXISTS `s_article_img_mappings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `image_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `image_id` (`image_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_article_img_mapping_rules`
--

DROP TABLE IF EXISTS `s_article_img_mapping_rules`;
CREATE TABLE IF NOT EXISTS `s_article_img_mapping_rules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mapping_id` int(11) NOT NULL,
  `option_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_billing_template`
--

DROP TABLE IF EXISTS `s_billing_template`;
CREATE TABLE IF NOT EXISTS `s_billing_template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `typ` mediumint(11) NOT NULL,
  `group` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `show` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=23 ;

--
-- Daten für Tabelle `s_billing_template`
--

INSERT INTO `s_billing_template` (`ID`, `name`, `value`, `typ`, `group`, `desc`, `position`, `show`) VALUES
(1, 'top', '1cm', 2, 'margin', 'Seitenabstand oben', 0, 1),
(2, 'right', '0.81cm', 2, 'margin', 'Seitenrand rechts', 0, 1),
(3, 'bottom', '0cm', 2, 'margin', 'Seitenabstand unten', 0, 1),
(4, 'left', '2.41cm', 2, 'margin', 'Seitenabstand links', 0, 1),
(5, 'top2', '5cm', 2, 'header', 'Logohöhe', 6, 1),
(7, 'margin', '1cm', 2, 'headline', 'Überschrift Abstand zur Anschrift', 0, 1),
(8, 'left', '0cm', 2, 'sender', 'Abstand links (negativ Wert möglich)', 0, 1),
(9, 'footer', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>', 1, 'footer', 'Fusszeile', 2, 1),
(13, 'right', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 56780<br />info@demo.de<br />www.demo.de</p>', 1, 'header', 'Briefkopf rechts', 9, 1),
(14, 'sender', 'Demo GmbH - Straße 3 - 00000 Musterstadt', 2, 'sender', 'Absender', 0, 1),
(15, 'left', '100px', 2, 'footer', 'Abstand links', 0, 1),
(16, 'bottom', '100px', 2, 'footer', 'Abstand unten', 1, 1),
(17, 'number', '10', 2, 'content_middle', 'Anzahl angezeigter Postionen', 2, 1),
(18, 'text', '', 1, 'content_middle', 'Freitext', 4, 1),
(19, 'height', '12cm', 2, 'content_middle', 'Inhaltsabstand zum obigen Seitenrand', 0, 1),
(20, 'top', '<p><img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="" width="393" height="78" /></p>', 1, 'header', 'Logo oben', 7, 1),
(21, 'top', '1cm', 2, 'sender', 'Abstand unten zum Logo (negativ Wert möglich)', 0, 1),
(22, 'margin', '2.2cm', 2, 'header', 'Abstand rechts (negativ Wert möglich)', 8, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog`
--

DROP TABLE IF EXISTS `s_blog`;
CREATE TABLE IF NOT EXISTS `s_blog` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog_assigned_articles`
--

DROP TABLE IF EXISTS `s_blog_assigned_articles`;
CREATE TABLE IF NOT EXISTS `s_blog_assigned_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `article_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_id` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog_attributes`
--

DROP TABLE IF EXISTS `s_blog_attributes`;
CREATE TABLE IF NOT EXISTS `s_blog_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog_comments`
--

DROP TABLE IF EXISTS `s_blog_comments`;
CREATE TABLE IF NOT EXISTS `s_blog_comments` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog_media`
--

DROP TABLE IF EXISTS `s_blog_media`;
CREATE TABLE IF NOT EXISTS `s_blog_media` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned NOT NULL,
  `media_id` int(11) unsigned NOT NULL,
  `preview` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blogID` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_blog_tags`
--

DROP TABLE IF EXISTS `s_blog_tags`;
CREATE TABLE IF NOT EXISTS `s_blog_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `blog_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blogID` (`blog_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_articles`
--

DROP TABLE IF EXISTS `s_campaigns_articles`;
CREATE TABLE IF NOT EXISTS `s_campaigns_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_banner`
--

DROP TABLE IF EXISTS `s_campaigns_banner`;
CREATE TABLE IF NOT EXISTS `s_campaigns_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linkTarget` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_containers`
--

DROP TABLE IF EXISTS `s_campaigns_containers`;
CREATE TABLE IF NOT EXISTS `s_campaigns_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) DEFAULT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_groups`
--

DROP TABLE IF EXISTS `s_campaigns_groups`;
CREATE TABLE IF NOT EXISTS `s_campaigns_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_campaigns_groups`
--

INSERT INTO `s_campaigns_groups` (`id`, `name`) VALUES
(1, 'Newsletter-Empfänger');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_html`
--

DROP TABLE IF EXISTS `s_campaigns_html`;
CREATE TABLE IF NOT EXISTS `s_campaigns_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `alignment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_links`
--

DROP TABLE IF EXISTS `s_campaigns_links`;
CREATE TABLE IF NOT EXISTS `s_campaigns_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_logs`
--

DROP TABLE IF EXISTS `s_campaigns_logs`;
CREATE TABLE IF NOT EXISTS `s_campaigns_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mailingID` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_mailaddresses`
--

DROP TABLE IF EXISTS `s_campaigns_mailaddresses`;
CREATE TABLE IF NOT EXISTS `s_campaigns_mailaddresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer` int(1) NOT NULL,
  `groupID` int(11) NOT NULL,
  `email` varchar(90) COLLATE utf8_unicode_ci NOT NULL,
  `lastmailing` int(11) NOT NULL,
  `lastread` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `email` (`email`),
  KEY `lastmailing` (`lastmailing`),
  KEY `lastread` (`lastread`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_maildata`
--

DROP TABLE IF EXISTS `s_campaigns_maildata`;
CREATE TABLE IF NOT EXISTS `s_campaigns_maildata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `salutation` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `streetnumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `zipcode` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `added` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`groupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_mailings`
--

DROP TABLE IF EXISTS `s_campaigns_mailings`;
CREATE TABLE IF NOT EXISTS `s_campaigns_mailings` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_positions`
--

DROP TABLE IF EXISTS `s_campaigns_positions`;
CREATE TABLE IF NOT EXISTS `s_campaigns_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `containerID` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_sender`
--

DROP TABLE IF EXISTS `s_campaigns_sender`;
CREATE TABLE IF NOT EXISTS `s_campaigns_sender` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_campaigns_sender`
--

INSERT INTO `s_campaigns_sender` (`id`, `email`, `name`) VALUES
(1, 'info@example.com', 'Newsletter Absender');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_templates`
--

DROP TABLE IF EXISTS `s_campaigns_templates`;
CREATE TABLE IF NOT EXISTS `s_campaigns_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_campaigns_templates`
--

INSERT INTO `s_campaigns_templates` (`id`, `path`, `description`) VALUES
(1, 'index.tpl', 'Standardtemplate'),
(2, 'indexh.tpl', 'Händler');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_categories`
--

DROP TABLE IF EXISTS `s_categories`;
CREATE TABLE IF NOT EXISTS `s_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned DEFAULT NULL,
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
  `noviewselect` int(1) unsigned DEFAULT NULL,
  `active` int(1) NOT NULL,
  `blog` int(11) NOT NULL,
  `showfiltergroups` int(11) NOT NULL,
  `external` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hidefilter` int(1) NOT NULL,
  `hidetop` int(1) NOT NULL,
  `mediaID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `description` (`description`),
  KEY `position` (`position`),
  KEY `left` (`left`,`right`),
  KEY `level` (`level`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `s_categories`
--

INSERT INTO `s_categories` (`id`, `parent`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `noviewselect`, `active`, `blog`, `showfiltergroups`, `external`, `hidefilter`, `hidetop`, `mediaID`) VALUES
(1, NULL, 'Root', 0, 1, 6, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, 0, 0, 0),
(3, 1, 'Deutsch', 0, 2, 3, 1, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, '', '', '', NULL, 0, 1, 0, 0, '', 0, 0, NULL),
(4, 1, 'Englisch', 1, 4, 5, 1, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, '', '', '', NULL, 0, 1, 0, 0, '', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_categories_attributes`
--

DROP TABLE IF EXISTS `s_categories_attributes`;
CREATE TABLE IF NOT EXISTS `s_categories_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) unsigned DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID` (`categoryID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=35 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_categories_avoid_customergroups`
--

DROP TABLE IF EXISTS `s_categories_avoid_customergroups`;
CREATE TABLE IF NOT EXISTS `s_categories_avoid_customergroups` (
  `categoryID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  UNIQUE KEY `articleID` (`categoryID`,`customergroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_content`
--

DROP TABLE IF EXISTS `s_cms_content`;
CREATE TABLE IF NOT EXISTS `s_cms_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `attachment` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_groups`
--

DROP TABLE IF EXISTS `s_cms_groups`;
CREATE TABLE IF NOT EXISTS `s_cms_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_static`
--

DROP TABLE IF EXISTS `s_cms_static`;
CREATE TABLE IF NOT EXISTS `s_cms_static` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=44 ;

--
-- Daten für Tabelle `s_cms_static`
--

INSERT INTO `s_cms_static` (`id`, `tpl1variable`, `tpl1path`, `tpl2variable`, `tpl2path`, `tpl3variable`, `tpl3path`, `description`, `html`, `grouping`, `position`, `link`, `target`, `parentID`, `page_title`, `meta_keywords`, `meta_description`) VALUES
(1, '', '', '', '', '', '', 'Kontakt', '<p>F&uuml;gen Sie hier Ihre Kontaktdaten ein</p>', 'gLeft|gBottom', 1, 'shopware.php?sViewport=ticket&sFid=5', '_self', 0, '', '', ''),
(2, '', '', '', '', '', '', 'Hilfe / Support', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft', 1, '', '', 0, '', '', ''),
(3, '', '', '', '', '', '', 'Impressum', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft', 20, '', '', 0, '', '', ''),
(4, '', '', '', '', '', '', 'AGB', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 18, '', '', 0, '', '', ''),
(6, '', '', '', '', '', '', 'Versand und Zahlungsbedingungen', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 3, '', '', 0, '', '', ''),
(7, '', '', '', '', '', '', 'Datenschutz', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom2', 6, '', '', 0, '', '', ''),
(8, '', '', '', '', '', '', 'Widerrufsrecht', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 5, '', '', 0, '', '', ''),
(9, '', '', '', '', '', '', 'Über uns', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom2', 0, '', '', 0, '', '', ''),
(21, '', '', '', '', '', '', 'Händler-Login', '', 'gLeft', 0, 'shopware.php?sViewport=registerFC&sUseSSL=1&sValidation=H', '', 0, '', '', ''),
(25, '', '', '', '', '', '', 'Aktuelles', '', 'gBottom2', 0, 'shopware.php?sViewport=content&sContent=1', '', 0, '', '', ''),
(26, '', '', '', '', '', '', 'Newsletter', '', 'gBottom2', 0, 'shopware.php?sViewport=newsletter', '', 0, '', '', ''),
(27, '', '', '', '', '', '', 'About us', '<p>Text</p>', 'eLeft|eBottom', 0, '', '', 0, '', '', ''),
(28, '', '', '', '', '', '', 'Payment / Dispatch', '<p>Text</p>', 'eLeft|eBottom', 0, '', '', 0, '', '', ''),
(29, '', '', '', '', '', '', 'Privacy', '<p>Text</p>', 'eLeft|eBottom', 0, '', '', 0, '', '', ''),
(30, '', '', '', '', '', '', 'Help / Support', '<p>Text</p>', 'eLeft|eBottom', 0, '', '', 0, '', '', ''),
(32, '', '', '', '', '', '', 'Newsletter', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport=newsletter', '', 0, '', '', ''),
(33, '', '', '', '', '', '', 'Reseller-Login', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport,registerFC&sUseSSL=1&sValidation=H', '', 0, '', '', ''),
(34, '', '', '', '', '', '', 'Contact', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport=ticket&sFid=18', '', 0, '', '', ''),
(35, '', '', '', '', '', '', 'Site Map', '', 'eBottom', 0, 'shopware.php?sViewport=sitemap', '', 0, '', '', ''),
(37, '', '', '', '', '', '', 'Partnerprogramm', '<h1>Jetzt Partner werden</h1>\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gBottom', 0, 'shopware.php?sViewport=ticket&sFid=8', '_self', 0, '', '', ''),
(38, '', '', '', '', '', '', 'Affiliate program', '', 'eBottom2', 4, 'shopware.php?sViewport=ticket&sFid=17', '_self', 0, '', '', ''),
(39, '', '', '', '', '', '', 'Defektes Produkt', '<p>Defektes Produkt.</p>', 'gBottom', 0, 'shopware.php?sViewport=ticket&sFid=9', '_self', 0, '', '', ''),
(40, '', '', '', '', '', '', 'Defective product', '<p>Defective product.</p>', 'eBottom', 4, 'shopware.php?sViewport=ticket&sFid=19', '_self', 0, '', '', ''),
(41, '', '', '', '', '', '', 'Rückgabe', '<p>R&uuml;ckgabe.</p>', 'gBottom', 4, 'shopware.php?sViewport=ticket&sFid=10', '_self', 0, '', '', ''),
(42, '', '', '', '', '', '', 'Return', '<p>Return.</p>', 'eBottom2', 3, 'shopware.php?sViewport=ticket&sFid=20', '_self', 0, '', '', ''),
(43, '', '', '', '', '', '', 'rechtliche Vorabinformationen', '<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 0, '', '', 0, '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_static_attributes`
--

DROP TABLE IF EXISTS `s_cms_static_attributes`;
CREATE TABLE IF NOT EXISTS `s_cms_static_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsStaticID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsStaticID` (`cmsStaticID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_static_groups`
--

DROP TABLE IF EXISTS `s_cms_static_groups`;
CREATE TABLE IF NOT EXISTS `s_cms_static_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL,
  `mapping_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `mapping_id` (`mapping_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `s_cms_static_groups`
--

INSERT INTO `s_cms_static_groups` (`id`, `name`, `key`, `active`, `mapping_id`) VALUES
(1, 'Links', 'gLeft', 1, NULL),
(2, 'Unten (Spalte 1)', 'gBottom', 1, NULL),
(3, 'Unten (Spalte 2)', 'gBottom2', 1, NULL),
(4, 'In Bearbeitung', 'gDisabled', 0, NULL),
(7, 'Englisch links', 'eLeft', 1, 1),
(9, 'Englisch unten (Spalte 1)', 'eBottom', 1, 2),
(10, 'Englisch unten (Spalte 2)', 'eBottom2', 1, 3);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_support`
--

DROP TABLE IF EXISTS `s_cms_support`;
CREATE TABLE IF NOT EXISTS `s_cms_support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email_template` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `email_subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `text2` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `ticket_typeID` int(10) NOT NULL,
  `isocode` varchar(3) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'de',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

--
-- Daten für Tabelle `s_cms_support`
--

INSERT INTO `s_cms_support` (`id`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `ticket_typeID`, `isocode`) VALUES
(5, 'Kontaktformular', '<p>Schreiben Sie uns eine eMail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>', 'info@example.com', 'Kontaktformular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nBetreff: {sVars.betreff}\r\nKommentar: \r\n{sVars.kommentar}\r\n\r\n\r\n', 'Kontaktformular Shopware', '<p>Ihr Formular wurde versendet!</p>', 1, 'de'),
(8, 'Partnerformular', '<h2>Partner werden und mitverdienen!</h2>\r\n<p>Einfach unseren Link auf ihre Seite legen und Sie erhalten f&uuml;r jeden Umsatz ihrer vermittelten Kunden automatisch eine attraktive Provision auf den Netto-Auftragswert.</p>\r\n<p>Bitte f&uuml;llen Sie <span style="text-decoration: underline;">unverbindlich</span> das Partnerformular aus. Wir werden uns umgehend mit Ihnen in Verbindung setzen!</p>', 'info@example.com', 'Partneranfrage - {$sShopname}\r\n{sVars.firma} moechte Partner Ihres Shops werden!\r\n\r\nFirma: {sVars.firma}\r\nAnsprechpartner: {sVars.ansprechpartner}\r\nStraße/Hausnr.: {sVars.strasse}\r\nPLZ / Ort: {sVars.plz} {sVars.ort}\r\neMail: {sVars.email}\r\nTelefon: {sVars.tel}\r\nFax: {sVars.fax}\r\nWebseite: {sVars.webseite}\r\nBetreff: {sVars.betreff}\r\n\r\nKommentar: \r\n{sVars.kommentar}\r\n\r\nProfil:\r\n{sVars.profil}', 'Partner Anfrage', '<p>Die Anfrage wurde versandt!</p>', 0, 'de'),
(9, 'Defektes Produkt', '<h1>Defektes Produkt - f&uuml;r Endkunden und H&auml;ndler</h1>\r\n<p>Sie erhalten von uns nach dem Absenden dieses Formulars innerhalb kurzer Zeit eine R&uuml;ckantwort mit einer RMA-Nummer und weiterer Vorgehensweise.</p>\r\n<p>Bitte f&uuml;llen Sie die Fehlerbeschreibung ausf&uuml;hrlich aus, Sie m&uuml;ssen diese dann nicht mehr dem Paket beilegen.</p>', 'info@example.com', 'Defektes Produkt - Shopware Demoshop\r\n\r\nFirma: {sVars.firma}\r\nKundennummer: {sVars.kdnr}\r\neMail: {sVars.email}\r\n\r\nRechnungsnummer: {sVars.rechnung}\r\nArtikelnummer: {sVars.artikel}\r\n\r\nDetaillierte Fehlerbeschreibung:\r\n--------------------------------\r\n{sVars.fehler}\r\n\r\nRechner: {sVars.rechner}\r\nSystem {sVars.system}\r\nWie tritt das Problem auf: {sVars.wie}\r\n', 'Online-Serviceformular', '<p>Formular erfolgreich versandt!</p>', 2, 'de'),
(10, 'Rückgabe', '<h2>Hier k&ouml;nnen Sie Informationen zur R&uuml;ckgabe einstellen...</h2>', 'info@example.com', 'Rükgabe - Shopware Demoshop\n \nKundennummer: {sVars.kdnr}\neMail: {sVars.email}\n \nRechnungsnummer: {sVars.rechnung}\nArtikelnummer: {sVars.artikel}\n \nKommentar:\n \n{sVars.info}', 'Rückgabe', '<p>Formular erfolgreich versandt.</p>', 0, 'de'),
(16, 'Anfrage-Formular', '<p>Schreiben Sie uns eine eMail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>', 'info@example.com', 'Anfrage-Formular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nFrage: \r\n{sVars.inquiry}\r\n\r\n\r\n', 'Anfrage-Formular Shopware', '<p>Ihre Anfrage wurde versendet!</p>', 0, 'de'),
(17, 'Partner form', '<h2><strong>Become partner and earn money!</strong></h2>\r\n<p>Link our Site and receive&nbsp;an attractive commission on the net contract price&nbsp;for every tornover of your&nbsp;provided customers.</p>\r\n<p>Please fill out the partner form <span style="text-decoration: underline;">without obligation</span>.&nbsp;We will immediately get in contact with you!</p>', 'info@example.com', 'Partner inquiry - {$sShopname}\r\n{sVars.firma} want to become your partner!\r\n\r\nCompany: {sVars.firma}\r\nContact person: {sVars.ansprechpartner}\r\nStreet / No.: {sVars.strasse}\r\nPostal Code / City: {sVars.plz} {sVars.ort}\r\neMail: {sVars.email}\r\nPhone: {sVars.tel}\r\nFax: {sVars.fax}\r\nWebsite: {sVars.webseite}\r\nSubject: {sVars.betreff}\r\n\r\nComment: \r\n{sVars.kommentar}\r\n\r\nProfile:\r\n{sVars.profil}', 'Partner inquiry', '<p>&nbsp;</p>\r\n&nbsp;\r\n<div id="result_box" dir="ltr">The request has been sent!</div>', 0, 'de'),
(18, 'Contact', '', 'info@example.com', 'Contact form Shopware Demoshop\r\n\r\nTitle: {sVars.anrede}\r\nFirst name: {sVars.vorname}\r\nLast name: {sVars.nachname}\r\neMail: {sVars.email}\r\nPhone: {sVars.telefon}\r\nSubject: {sVars.betreff}\r\nComment: \r\n{sVars.kommentar}\r\n\r\n\r\n', 'Contact form Shopware', '<p>Your form was sent!</p>', 0, 'de'),
(19, 'Defective product', '<p>&nbsp;</p>\r\n&nbsp;\r\n<h1>Defective product - for customers and traders</h1>\r\n<p>You will receive an answer&nbsp;from us&nbsp;with an RMA number an other approach&nbsp;after sending this form.&nbsp;</p>\r\n<p>Please fill out the error description, so you must not add this any more to the package.</p>', 'info@example.com', 'INSERT INTO s_user_service\r\n(clientnumber, email, billingnumber, articles, description, description2, description3,\r\ndescription4,date,type)\r\nVALUES (\r\n			''{$kdnr}'',\r\n			''{$email}'',\r\n			''{$rechnung}'',\r\n			''{$artikel}'',\r\n			''{$fehler}'',\r\n			''{$rechner}'',\r\n			''{$system}'',\r\n			''{$wie}'',\r\n			''{$date}'',\r\n1\r\n		)', 'Online-Serviceform', '<p>Form successfully sent!</p>', 0, 'de'),
(20, 'Return', '<h2>Here you can write information about the return...</h2>', 'info@example.com', 'INSERT INTO s_user_service\r\n(clientnumber, email, billingnumber, articles, description, description2, description3,\r\ndescription4,date,type)\r\nVALUES (\r\n			''{$kdnr}'',\r\n			''{$email}'',\r\n			''{$rechnung}'',\r\n			''{$artikel}'',\r\n			''{$info}'',\r\n			'''',\r\n			'''',\r\n			'''',\r\n			''{$date}'',\r\n2\r\n		)\r\n', 'Return', '<p>Form successfully sent.</p>', 0, 'de'),
(21, 'Inquiry form', '<p>Send us an email.&nbsp;<br /><br />We look forward to hearing from you.</p>', 'info@example.com', 'Anfrage-Formular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nFrage: \r\n{sVars.inquiry}\r\n\r\n\r\n', 'Inquiry form Shopware', '<p>Your request has been sent!</p>', 0, 'de'),
(22, 'Support beantragen', '<p>Wir freuen uns &uuml;ber Ihre Kontaktaufnahme.</p>', 'info@example.com', '', 'Support beantragen', '<p>Vielen Dank f&uuml;r Ihre Anfrage!</p>', 1, 'de');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_support_attributes`
--

DROP TABLE IF EXISTS `s_cms_support_attributes`;
CREATE TABLE IF NOT EXISTS `s_cms_support_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cmsSupportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cmsSupportID` (`cmsSupportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_support_fields`
--

DROP TABLE IF EXISTS `s_cms_support_fields`;
CREATE TABLE IF NOT EXISTS `s_cms_support_fields` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=117 ;

--
-- Daten für Tabelle `s_cms_support_fields`
--

INSERT INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `added`, `position`, `ticket_task`) VALUES
(12, '', 'sdfg', '', '', 0, 0, 'sdf', '', '', '0000-00-00 00:00:00', 0, ''),
(24, '', 'anrede', '', 'select', 1, 5, 'Anrede', 'normal', 'Frau;Herr', '2007-11-02 03:28:48', 0, ''),
(35, '', 'vorname', '', 'text', 1, 5, 'Vorname', 'normal', '', '2007-11-06 03:17:48', 0, ''),
(36, '', 'nachname', '', 'text', 1, 5, 'Nachname', 'normal', '', '2007-11-06 03:17:57', 0, 'name'),
(37, '', 'email', '', 'email', 1, 5, 'eMail-Adresse', 'normal', '', '2007-11-06 03:18:36', 0, 'email'),
(38, '', 'telefon', '', 'text', 0, 5, 'Telefon', 'normal', '', '2007-11-06 03:18:49', 0, ''),
(39, '', 'betreff', '', 'text', 1, 5, 'Betreff', 'normal', '', '2007-11-06 03:18:57', 0, 'subject'),
(40, '', 'kommentar', '', 'textarea', 1, 5, 'Kommentar', 'normal', '', '2007-11-06 03:19:08', 0, 'message'),
(41, '', 'firma', '', 'text', 1, 8, 'Firma', 'normal', '', '2007-11-22 08:11:39', 0, ''),
(42, '', 'ansprechpartner', '', 'text', 1, 8, 'Ansprechpartner', 'normal', '', '2007-11-22 08:12:18', 0, ''),
(43, '', 'strasse', '', 'text2', 1, 8, 'Straße / Hausnummer', 'strasse;nr', '', '2007-11-22 08:12:49', 0, ''),
(44, '', 'plz;ort', '', 'text2', 1, 8, 'PLZ / Ort', 'plz;ort', '', '2007-11-22 08:12:59', 0, ''),
(45, '', 'tel', '', 'text', 1, 8, 'Telefon', 'normal', '', '2007-11-22 08:13:45', 0, ''),
(46, '', 'fax', '', 'text', 0, 8, 'Fax', 'normal', '', '2007-11-22 08:13:52', 0, ''),
(47, '', 'email', '', 'text', 1, 8, 'eMail', 'normal', '', '2007-11-22 08:13:58', 0, ''),
(48, '', 'website', '', 'text', 1, 8, 'Webseite', 'normal', '', '2007-11-22 08:14:07', 0, ''),
(49, '', 'kommentar', '', 'textarea', 0, 8, 'Kommentar', 'normal', '', '2007-11-22 08:14:21', 0, ''),
(50, '', 'profil', '', 'textarea', 1, 8, 'Firmenprofil', 'normal', '', '2007-11-22 08:14:34', 0, ''),
(51, '', 'rechnung', '', 'text', 1, 9, 'Rechnungsnummer', 'normal', '', '2007-11-06 17:21:49', 0, ''),
(52, '', 'email', '', 'text', 1, 9, 'eMail-Adresse', 'normal', '', '2007-11-06 17:19:20', 0, 'email'),
(53, '', 'kdnr', '', 'text', 1, 9, 'KdNr.(siehe Rechnung)', 'normal', '', '2007-11-06 17:19:10', 0, 'name'),
(54, '', 'firma', '', 'checkbox', 0, 9, 'Firma (Wenn ja, bitte ankreuzen)', '', '1', '2007-11-06 17:18:36', 0, ''),
(55, '', 'artikel', '', 'textarea', 1, 9, 'Artikelnummer(n)', 'normal', '', '2007-11-06 17:22:13', 0, 'subject'),
(56, '', 'fehler', '', 'textarea', 1, 9, 'Detaillierte Fehlerbeschreibung', 'normal', '', '2007-11-06 17:22:33', 0, 'message'),
(57, '', 'rechner', '', 'textarea', 0, 9, 'Auf welchem Rechnertypen läuft das defekte Produkt?', 'normal', '', '2007-11-06 17:23:17', 0, ''),
(58, '', 'system', '', 'textarea', 0, 9, 'Mit welchem Betriebssystem arbeiten Sie?', 'normal', '', '2007-11-06 17:23:57', 0, ''),
(59, '', 'wie', '', 'select', 1, 9, 'Wie tritt das Problem auf?', 'normal', 'sporadisch; ständig', '2007-11-06 17:24:26', 0, ''),
(60, '', 'kdnr', '', 'text', 1, 10, 'KdNr.(siehe Rechnung)', 'normal', '', '2007-11-06 17:31:38', 1, ''),
(61, '', 'email', '', 'text', 1, 10, 'eMail-Adresse', 'normal', '', '2007-11-06 17:31:51', 2, ''),
(62, '', 'rechnung', '', 'text', 1, 10, 'Rechnungsnummer', 'normal', '', '2007-11-06 17:32:02', 3, ''),
(63, '', 'artikel', '', 'textarea', 1, 10, 'Artikelnummer(n)', 'normal', '', '2007-11-06 17:32:17', 4, ''),
(64, '', 'info', '', 'textarea', 0, 10, 'Kommentar', 'normal', '', '2007-11-06 17:32:42', 5, ''),
(69, '', 'inquiry', '', 'textarea', 1, 16, 'Anfrage', 'normal', '', '2007-11-06 03:19:08', 5, ''),
(71, '', 'nachname', '', 'text', 1, 16, 'Nachname', 'normal', '', '2007-11-06 03:17:57', 2, ''),
(72, '', 'anrede', '', 'select', 1, 16, 'Anrede', 'normal', 'Frau;Herr', '2007-11-02 03:28:48', 0, ''),
(73, '', 'telefon', '', 'text', 0, 16, 'Telefon', 'normal', '', '2007-11-06 03:18:49', 3, ''),
(74, '', 'email', '', 'text', 1, 16, 'eMail-Adresse', 'normal', '', '2007-11-06 03:18:36', 0, ''),
(75, '', 'vorname', '', 'text', 1, 16, 'Vorname', 'normal', '', '2007-11-06 03:17:48', 1, ''),
(76, '', 'firma', '', 'text', 1, 17, 'Company', 'normal', '', '2008-10-17 13:02:42', 0, ''),
(77, '', 'ansprechpartner', '', 'text', 1, 17, 'Contact person', 'normal', '', '2008-10-17 13:03:35', 4, ''),
(78, '', 'strasse', '', 'text2', 1, 17, 'Street / house number', 'strasse;nr', '', '2008-10-17 13:05:55', 0, ''),
(79, '', 'plz;ort', '', 'text2', 1, 17, 'Postal Code / City', 'plz;ort', '', '2008-10-17 13:06:23', 0, ''),
(80, '', 'tel', '', 'text', 1, 17, 'Phone', 'normal', '', '2008-10-17 13:06:35', 0, ''),
(81, '', 'fax', '', 'text', 0, 17, 'Fax', 'normal', '', '2008-10-17 13:06:48', 0, ''),
(82, '', 'email', '', 'text', 1, 17, 'eMail', 'normal', '', '2008-10-17 13:07:06', 0, ''),
(83, '', 'website', '', 'text', 1, 17, 'Website', 'normal', '', '2008-10-17 13:07:14', 0, ''),
(84, '', 'kommentar', '', 'textarea', 0, 17, 'Comment', 'normal', '', '2008-10-17 13:07:25', 0, ''),
(85, '', 'profil', '', 'textarea', 1, 17, 'Company profile', 'normal', '', '2008-10-17 13:07:43', 0, ''),
(86, '', 'anrede', '', 'select', 1, 18, 'Title', 'normal', 'Ms;Mr', '2008-10-17 13:21:07', 0, ''),
(87, '', 'vorname', '', 'text', 1, 18, 'First name', 'normal', '', '2008-10-17 13:21:41', 0, ''),
(88, '', 'nachname', '', 'text', 1, 18, 'Last name', 'normal', '', '2008-10-17 13:22:01', 0, ''),
(89, '', 'email', '', 'text', 1, 18, 'eMail-Adress', 'normal', '', '2008-10-17 13:22:18', 0, ''),
(90, '', 'telefon', '', 'text', 0, 18, 'Phone', 'normal', '', '2008-10-17 13:22:28', 0, ''),
(91, '', 'betreff', '', 'text', 1, 18, 'Subject', 'normal', '', '2008-10-17 13:22:38', 0, ''),
(92, '', 'kommentar', '', 'textarea', 1, 18, 'Comment', 'normal', '', '2008-10-17 13:22:45', 0, ''),
(93, '', 'firma', '', 'checkbox', 0, 19, 'Company (If so, please mark)', '', '1', '2008-10-17 13:45:44', 0, ''),
(94, '', 'kdnr', '', 'text', 1, 19, 'Customer no. (See invoice)', 'normal', '', '2008-10-17 13:46:04', 0, ''),
(95, '', 'email', '', 'text', 1, 19, 'eMail-Adress', 'normal', '', '2008-10-17 13:46:27', 0, ''),
(96, '', 'rechnung', '', 'text', 1, 19, 'Invoice number', 'normal', '', '2008-10-17 13:47:03', 0, ''),
(97, '', 'artikel', '', 'textarea', 1, 19, 'Articlenumber(s)', 'normal', '', '2008-10-17 13:47:43', 0, ''),
(98, '', 'fehler', '', 'textarea', 1, 19, 'Detailed error description', 'normal', '', '2008-10-17 13:48:54', 0, ''),
(99, '', 'rechner', '', 'textarea', 0, 19, 'On which computer type does the defective product run?', 'normal', '', '2008-10-17 14:02:03', 0, ''),
(100, '', 'system', '', 'textarea', 0, 19, 'With which operating system do you work?', 'normal', '', '2008-10-17 14:02:36', 0, ''),
(101, '', 'wie', '', 'select', 1, 19, 'How doeas the problem occur?', 'normal', 'sporadically;permanently', '2008-10-17 14:02:55', 0, ''),
(102, '', 'kdnr', '', 'text', 1, 20, 'Customer no. (See invoice)', 'normal', '', '2008-10-17 14:21:28', 1, ''),
(103, '', 'email', '', 'text', 1, 20, 'eMail-Adress', 'normal', '', '2008-10-17 14:22:12', 2, ''),
(104, '', 'rechnung', '', 'text', 1, 20, 'Invoice number', 'normal', '', '2008-10-17 14:22:43', 3, ''),
(105, '', 'artikel', '', 'textarea', 1, 20, 'Articlenumber(s)', 'normal', '', '2008-10-17 14:23:15', 4, ''),
(106, '', 'info', '', 'textarea', 0, 20, 'Comment', 'normal', '', '2008-10-17 14:23:37', 5, ''),
(107, '', 'anrede', '', 'select', 1, 21, 'Title', 'normal', 'Ms;Mr', '2008-10-17 14:45:21', 0, ''),
(108, '', 'vorname', '', 'text', 1, 21, 'First name', 'normal', '', '2008-10-17 14:46:11', 0, ''),
(109, '', 'nachname', '', 'text', 1, 21, 'Last name', 'normal', '', '2008-10-17 14:46:31', 0, ''),
(110, '', 'email', '', 'text', 1, 21, 'eMail-Adress', 'normal', '', '2008-10-17 14:46:49', 0, ''),
(111, '', 'telefon', '', 'text', 0, 21, 'Phone', 'normal', '', '2008-10-17 14:47:00', 0, ''),
(112, '', 'inquiry', '', 'textarea', 1, 21, 'Inquiry', 'normal', '', '2008-10-17 14:47:25', 0, ''),
(113, '', 'name', '', 'text', 1, 22, 'Name', 'normal', '', '2009-04-15 22:20:30', 0, 'name'),
(114, '', 'email', '', 'email', 1, 22, 'eMail', 'normal', '', '2009-04-15 22:20:37', 0, 'email'),
(115, '', 'betreff', '', 'text', 1, 22, 'Betreff', 'normal', '', '2009-04-15 22:20:45', 0, 'subject'),
(116, '', 'kommentar', '', 'textarea', 1, 22, 'Kommentar', 'normal', '', '2009-04-15 22:21:07', 0, 'message');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_acl_privileges`
--

DROP TABLE IF EXISTS `s_core_acl_privileges`;
CREATE TABLE IF NOT EXISTS `s_core_acl_privileges` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `resourceID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `resourceID` (`resourceID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=141 ;

--
-- Daten für Tabelle `s_core_acl_privileges`
--

INSERT INTO `s_core_acl_privileges` (`id`, `resourceID`, `name`) VALUES
(1, 1, 'create'),
(2, 1, 'read'),
(3, 1, 'update'),
(4, 1, 'delete'),
(5, 2, 'create'),
(6, 2, 'read'),
(7, 2, 'update'),
(8, 2, 'delete'),
(10, 3, 'create'),
(11, 3, 'update'),
(12, 3, 'delete'),
(15, 4, 'create'),
(16, 4, 'read'),
(17, 4, 'update'),
(18, 4, 'delete'),
(19, 5, 'create'),
(20, 5, 'update'),
(21, 5, 'delete'),
(22, 5, 'read'),
(23, 6, 'createupdate'),
(24, 6, 'read'),
(26, 6, 'delete'),
(27, 5, 'detail'),
(28, 5, 'perform_order'),
(29, 7, 'create'),
(30, 7, 'read'),
(31, 7, 'update'),
(32, 7, 'delete'),
(33, 8, 'create'),
(34, 8, 'read'),
(35, 8, 'update'),
(36, 8, 'delete'),
(37, 8, 'export'),
(38, 8, 'generate'),
(39, 9, 'read'),
(40, 9, 'accept'),
(41, 9, 'comment'),
(42, 9, 'delete'),
(43, 10, 'create'),
(44, 10, 'read'),
(45, 10, 'update'),
(46, 10, 'delete'),
(47, 11, 'create'),
(48, 11, 'read'),
(49, 11, 'update'),
(50, 11, 'delete'),
(56, 13, 'read'),
(57, 14, 'create'),
(58, 14, 'read'),
(59, 14, 'update'),
(60, 14, 'delete'),
(61, 15, 'create'),
(62, 15, 'read'),
(63, 15, 'update'),
(64, 15, 'delete'),
(65, 16, 'create'),
(66, 16, 'read'),
(67, 16, 'update'),
(68, 16, 'delete'),
(69, 17, 'create'),
(70, 17, 'read'),
(71, 17, 'update'),
(72, 17, 'delete'),
(73, 18, 'createGroup'),
(74, 18, 'read'),
(75, 18, 'createSite'),
(76, 18, 'updateSite'),
(77, 18, 'deleteSite'),
(78, 18, 'deleteGroup'),
(79, 11, 'generate'),
(80, 19, 'read'),
(81, 20, 'read'),
(82, 20, 'delete'),
(83, 21, 'save'),
(84, 21, 'read'),
(85, 21, 'delete'),
(86, 22, 'create'),
(87, 22, 'read'),
(88, 22, 'update'),
(89, 22, 'delete'),
(90, 22, 'statistic'),
(91, 23, 'create'),
(92, 23, 'read'),
(93, 23, 'update'),
(94, 23, 'delete'),
(95, 24, 'read'),
(96, 25, 'delete'),
(97, 25, 'read'),
(98, 26, 'read'),
(99, 27, 'read'),
(100, 27, 'delete'),
(101, 27, 'create'),
(102, 27, 'upload'),
(103, 27, 'update'),
(104, 28, 'read'),
(105, 28, 'delete'),
(106, 28, 'update'),
(107, 28, 'create'),
(108, 28, 'comments'),
(110, 29, 'read'),
(112, 29, 'delete'),
(113, 29, 'save'),
(114, 30, 'create'),
(115, 30, 'read'),
(116, 30, 'update'),
(117, 30, 'delete'),
(118, 31, 'create'),
(119, 31, 'read'),
(120, 31, 'update'),
(121, 31, 'delete'),
(122, 32, 'delete'),
(123, 32, 'read'),
(124, 32, 'write'),
(125, 33, 'read'),
(126, 33, 'update'),
(127, 33, 'clear'),
(128, 34, 'read'),
(129, 34, 'export'),
(130, 34, 'import'),
(131, 35, 'create'),
(132, 35, 'read'),
(133, 35, 'update'),
(134, 35, 'delete'),
(136, 36, 'read'),
(137, 36, 'upload'),
(138, 36, 'download'),
(139, 36, 'install'),
(140, 36, 'update');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_acl_resources`
--

DROP TABLE IF EXISTS `s_core_acl_resources`;
CREATE TABLE IF NOT EXISTS `s_core_acl_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=37 ;

--
-- Daten für Tabelle `s_core_acl_resources`
--

INSERT INTO `s_core_acl_resources` (`id`, `name`, `pluginID`) VALUES
(1, 'debug_test', NULL),
(2, 'banner', NULL),
(4, 'supplier', NULL),
(5, 'customer', NULL),
(6, 'form', NULL),
(7, 'premium', NULL),
(8, 'voucher', NULL),
(9, 'vote', NULL),
(10, 'mail', NULL),
(11, 'productfeed', NULL),
(13, 'overview', NULL),
(14, 'order', NULL),
(15, 'payment', NULL),
(16, 'shipping', NULL),
(17, 'snippet', NULL),
(18, 'site', NULL),
(19, 'systeminfo', NULL),
(20, 'log', NULL),
(21, 'riskmanagement', NULL),
(22, 'partner', NULL),
(23, 'category', NULL),
(24, 'notification', NULL),
(25, 'canceledorder', NULL),
(26, 'analytics', NULL),
(27, 'mediamanager', NULL),
(28, 'blog', NULL),
(29, 'article', NULL),
(30, 'config', NULL),
(31, 'emotion', NULL),
(32, 'newslettermanager', NULL),
(33, 'cache', NULL),
(34, 'importexport', NULL),
(35, 'usermanager', NULL),
(36, 'pluginmanager', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_acl_roles`
--

DROP TABLE IF EXISTS `s_core_acl_roles`;
CREATE TABLE IF NOT EXISTS `s_core_acl_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleID` int(11) NOT NULL,
  `resourceID` int(11) DEFAULT NULL,
  `privilegeID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roleID` (`roleID`,`resourceID`,`privilegeID`),
  KEY `resourceID` (`resourceID`),
  KEY `privilegeID` (`privilegeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `s_core_acl_roles`
--

INSERT INTO `s_core_acl_roles` (`id`, `roleID`, `resourceID`, `privilegeID`) VALUES
(1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_auth`
--

DROP TABLE IF EXISTS `s_core_auth`;
CREATE TABLE IF NOT EXISTS `s_core_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roleID` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `apiKey` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `localeID` int(11) NOT NULL,
  `sessionID` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `admin` int(1) NOT NULL,
  `salted` int(1) unsigned NOT NULL,
  `failedlogins` int(11) NOT NULL,
  `lockeduntil` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=50 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_auth_attributes`
--

DROP TABLE IF EXISTS `s_core_auth_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_auth_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `authID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `authID` (`authID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_auth_roles`
--

DROP TABLE IF EXISTS `s_core_auth_roles`;
CREATE TABLE IF NOT EXISTS `s_core_auth_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) DEFAULT NULL,
  `name` varchar(255) CHARACTER SET latin1 NOT NULL,
  `description` text CHARACTER SET latin1 NOT NULL,
  `source` varchar(255) CHARACTER SET latin1 NOT NULL,
  `enabled` int(1) NOT NULL,
  `admin` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `s_core_auth_roles`
--

INSERT INTO `s_core_auth_roles` (`id`, `parentID`, `name`, `description`, `source`, `enabled`, `admin`) VALUES
(1, NULL, 'local_admins', 'Default group that gains access to all shop functions', 'build-in', 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_elements`
--

DROP TABLE IF EXISTS `s_core_config_elements`;
CREATE TABLE IF NOT EXISTS `s_core_config_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `filters` blob,
  `validators` blob,
  `options` blob,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_id_2` (`form_id`,`name`),
  KEY `form_id` (`form_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=900 ;

--
-- Daten für Tabelle `s_core_config_elements`
--

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(186, 86, 'vouchertax', 's:2:"19";', 'MwSt. Gutscheine', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(188, 86, 'discounttax', 's:2:"19";', 'MwSt. Rabatte', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(189, 90, 'voteunlock', 'b:1;', 'Artikel-Bewertungen müssen freigeschaltet werden', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(190, 84, 'backendautoordernumber', 'b:1;', 'Automatischer Vorschlag der Artikelnummer', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(191, 84, 'backendautoordernumberprefix', 's:2:"SW";', 'Präfix für automatisch generierte Artikelnummer', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(192, 90, 'votedisable', 'b:0;', 'Artikel-Bewertungen deaktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(193, 90, 'votesendcalling', 'b:1;', 'Automatische Erinnerung zur Artikelbewertung senden', 'Nach Kauf dem Benutzer an die Artikelbewertung via E-Mail erinnern', 'boolean', 0, 0, 0, NULL, NULL, NULL),
(194, 90, 'votecallingtime', 's:1:"1";', 'Tage bis die Erinnerungs-E-Mail verschickt wird', 'Tage bis der Kunde via E-Mail an die Artikel-Bewertung erinnert wird', 'text', 0, 0, 0, NULL, NULL, NULL),
(195, 86, 'taxautomode', 'b:1;', 'Steuer für Rabatte dynamisch feststellen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(224, 102, 'show', 'b:1;', 'Artikelverlauf anzeigen', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(225, 102, 'controller', 's:61:"index, listing, detail, custom, newsletter, sitemap, campaign";', 'Controller-Auswahl', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(226, 102, 'thumb', 's:1:"0";', 'Vorschaubild-Größe', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(227, 102, 'time', 'i:15;', 'Speicherfrist in Tagen', NULL, 'text', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(231, 102, 'lastarticlestoshow', 's:1:"5";', 'Anzahl Artikel in Verlauf (zuletzt angeschaut)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(234, 124, 'mailer_encoding', 's:4:"8bit";', 'Sets the Encoding of the message', 'Options for this are: "8bit", "7bit", "binary", "base64" and "quoted-printable".', 'text', 0, 0, 1, NULL, NULL, NULL),
(235, 124, 'mailer_mailer', 's:4:"mail";', 'Method to send the mail', 'Options for this are: "mail", "smtp" and "file"', 'text', 0, 0, 1, NULL, NULL, NULL),
(236, 124, 'mailer_hostname', 's:0:"";', 'Hostname to use in the Message-Id', 'Will be Received in headers. On default a HELO string. If empty, the value returned from SERVER_NAME is used or "localhost.localdomain".', 'text', 0, 0, 1, NULL, NULL, NULL),
(237, 124, 'mailer_host', 's:9:"localhost";', 'Mail host', 'You can also specify a different port by using this format: [hostname:port] (e.g. "smtp1.example.com:25").', 'text', 0, 0, 1, NULL, NULL, NULL),
(238, 124, 'mailer_port', 's:2:"25";', 'Default Port', 'Sets the default SMTP server port.', 'text', 0, 0, 1, NULL, NULL, NULL),
(239, 124, 'mailer_smtpsecure', 's:0:"";', 'Sets connection prefix.', 'Options are: "", "ssl" or "tls"', 'text', 0, 0, 1, NULL, NULL, NULL),
(240, 124, 'mailer_username', 's:0:"";', 'SMTP username', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(241, 124, 'mailer_password', 's:0:"";', 'SMTP password', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(242, 124, 'mailer_auth', 's:0:"";', 'Connection auth', 'Options are: "", "plain",  "login" or "crammd5"', 'text', 0, 0, 1, NULL, NULL, NULL),
(246, 127, 'cachecategory', 'i:86400;', 'Kategorien Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(247, 127, 'cacheprices', 'i:86400;', 'Preise Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(248, 127, 'cachechart', 'i:86400;', 'Topseller Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(249, 127, 'cachesupplier', 'i:86400;', 'Hersteller Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(250, 127, 'cachearticle', 'i:86400;', 'Artikeldetailseite Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(251, 127, 'cachecountries', 'i:86400;', 'Länderliste Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(252, 127, 'cachesearch', 'i:86400;', 'Cache Suche', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(253, 127, 'cachetranslations', 'i:86400;', 'Übersetzungen Pufferzeit', NULL, 'interval', 0, 0, 0, NULL, NULL, NULL),
(254, 128, 'setoffline', 'b:0;', 'Shop wegen Wartung sperren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(255, 128, 'offlineip', 's:1:"0";', 'Von der Sperrung ausgeschlossene IP', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(256, 127, 'deletecacheafterorder', 'b:0;', 'Shopcache nach jeder Bestellung leeren (Performance lastig)', 'Warnung! Kann massive Performance-Einbrüche nach sich ziehen', 'boolean', 0, 0, 0, NULL, NULL, NULL),
(257, 127, 'disablecache', 'b:1;', 'Shopcache deaktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(269, 133, 'show', 'i:1;', 'Menü zeigen', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(270, 133, 'levels', 'i:2;', 'Anzahl Ebenen', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(271, 133, 'caching', 'i:1;', 'Caching aktivieren', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(272, 133, 'cachetime', 'i:86400;', 'Cachezeit', NULL, 'interval', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(273, 134, 'show', 'i:1;', 'Vergleich zeigen', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(274, 134, 'maxComparisons', 'i:5;', 'Maximale Anzahl von zu vergleichenden Artikeln', NULL, 'number', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(275, 135, 'show', 'b:1;', 'Tag-Cloud anzeigen', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(276, 135, 'controller', 's:14:"index, listing";', 'Controller-Auswahl', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(277, 135, 'tagCloudClass', 's:3:"tag";', 'Name der Tag-Klasse', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(278, 135, 'tagCloudMax', 'i:46;', 'Maximale Anzahl Begriffe', NULL, 'number', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(279, 135, 'tagCloudSplit', 'i:3;', 'Anzahl der Stufen', NULL, 'number', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(280, 135, 'tagTime', 'i:30;', 'Die berücksichtigte Zeit in Tagen', NULL, 'number', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(286, 144, 'articlesperpage', 's:2:"12";', 'Artikel pro Seite', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(287, 144, 'orderbydefault', 's:12:"a.datum DESC";', 'Standardsortierung Listings', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(288, 144, 'maxpages', 's:1:"8";', 'Kategorien max. Anzahl Seiten', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(289, 145, 'markasnew', 's:2:"30";', 'Artikel als neu markieren (Tage)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(290, 145, 'markastopseller', 's:2:"30";', 'Artikel als Topseller markieren (Verkäufe)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(291, 145, 'chartrange', 's:1:"5";', 'Anzahl Topseller für Charts', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(292, 144, 'numberarticlestoshow', 's:11:"12|24|36|48";', 'Auswahl Artikel pro Seite', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(293, 144, 'categorytemplates', 's:141:"article_listing_1col.tpl:Liste;article_listing_2col.tpl:Zweispaltig;article_listing_3col.tpl:Dreispaltig;article_listing_4col.tpl:Vierspaltig";', 'Verfügbare Templates Kategorien', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(294, 147, 'maxpurchase', 's:3:"100";', 'Max. wählbare Artikelmenge / Artikel über Pulldown-Menü', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(295, 147, 'notavailable', 's:21:"Lieferzeit ca. 5 Tage";', 'Text für nicht verfügbare Artikel', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(296, 146, 'maxcrosssimilar', 's:1:"8";', 'Anzahl ähnlicher Artikel Cross-Selling', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(297, 146, 'maxcrossalsobought', 's:1:"8";', 'Anzahl "Kunden kauften auch" Artikel Cross-Selling', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(298, 144, 'category_default_tpl', 's:24:"article_listing_4col.tpl";', 'Standard-Template für neue Kategorien', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(299, 145, 'chartinterval', 's:2:"10";', 'Anzahl der Tage, die für die Topseller-Generierung berücksichtigt werden', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(300, 146, 'similarlimit', 's:1:"3";', 'Anzahl automatisch ermittelter, ähnlicher Artikel (Detailseite)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(301, 147, 'basketshippinginfo', 'b:1;', 'Lieferzeit im Warenkorb anzeigen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(302, 145, 'articlelimit', 's:2:"50";', 'Anzahl der Artikel, die unter Neuheiten ausgegeben werden', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(303, 147, 'inquiryid', 's:2:"16";', 'Anfrage-Formular ID', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(304, 147, 'inquiryvalue', 's:3:"150";', 'Mind. Warenkorbwert ab dem die Möglichkeit der individuellen Anfrage angeboten wird', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(305, 147, 'usezoomplus', 'b:1;', 'Zoomviewer statt Lightbox auf Detailseite', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(306, 147, 'liveinstock', 'b:1;', 'Lagerbestand auf Detailseite in Echtzeit abfragen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(307, 147, 'configmaxcombinations', 's:4:"1000";', 'Maximale Anzahl an Konfigurator-Varianten je Artikel', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(308, 147, 'deactivatenoinstock', 'b:0;', 'Abverkaufsartikel ohne Lagerbestand deaktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(309, 147, 'showbundlemainarticle', 'b:1;', 'Hauptartikel im Bundle anzeigen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(310, 147, 'deactivatebasketonnotification', 'b:1;', 'Warenkorb bei eMail-Benachrichtigung ausblenden', 'Warenkorb bei aktivierter eMail-Benachrichtigung und nicht vorhandenem Lagerbestand ausblenden', 'boolean', 0, 0, 0, NULL, NULL, NULL),
(311, 147, 'instockinfo', 'b:0;', 'Lagerbestands-Unterschreitung im Warenkorb anzeigen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(312, 144, 'categorydetaillink', 'b:0;', 'Direkt auf Detailspringen, falls nur ein Artikel vorhanden ist', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(313, 147, 'configcustomfields', 's:22:"Freitext 1, Freitext 2";', 'Konfigurator Freitextfelder', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(314, 147, 'detailtemplates', 's:9:":Standard";', 'Verfügbare Templates Detailseite', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(315, 144, 'maxsupplierscategory', 's:2:"30";', 'Max. Anzahl Hersteller in Sidebar', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(317, 157, 'minpassword', 's:1:"8";', 'Mindestlänge Passwort (Registrierung)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(318, 157, 'defaultpayment', 's:1:"5";', 'Standardzahlungsart (Id) (Registrierung)', NULL, 'select', 1, 0, 1, NULL, NULL, 0x613a333a7b733a353a2273746f7265223b733a31323a22626173652e5061796d656e74223b733a31323a22646973706c61794669656c64223b733a31313a226465736372697074696f6e223b733a31303a2276616c75654669656c64223b733a323a226964223b7d),
(319, 157, 'newsletterdefaultgroup', 's:1:"1";', 'Standard-Empfangsgruppe (ID) für registrierte Kunden (System / Newsletter)', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(320, 157, 'shopwaremanagedcustomernumbers', 'b:1;', 'Shopware generiert Kundennummern', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(321, 157, 'ignoreagb', 'b:0;', 'AGB - Checkbox auf Kassenseite deaktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(322, 157, 'countryshipping', 'b:1;', 'Land bei Lieferadresse abfragen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(323, 157, 'actdprcheck', 'b:0;', 'Datenschutz-Bedingungen müssen über Checkbox akzeptiert werden', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(324, 157, 'paymentdefault', 's:1:"5";', 'Fallback-Zahlungsart (ID)', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(325, 157, 'doubleemailvalidation', 'b:0;', 'E-Mail Addresse muss zweimal eingegeben werden.', 'E-Mail Addresse muss zweimal eingegeben werden, um Tippfehler zu vermeiden.', 'boolean', 0, 0, 0, NULL, NULL, NULL),
(326, 157, 'newsletterextendedfields', 'b:1;', 'Erweiterte Felder in Newsletter-Registrierung abfragen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(327, 157, 'noaccountdisable', 'b:0;', '"Kein Kundenkonto" deaktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(585, 173, 'blockIp', 'N;', 'IP von Statistiken ausschließen', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(586, 174, 'tracking_code', 'N;', 'Google Analytics-ID', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(587, 174, 'conversion_code', 'N;', 'Google Conversion-ID', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(588, 174, 'anonymize_ip', 'b:1;', 'IP-Adresse anonymisieren', NULL, 'checkbox', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(593, 176, 'logDb', 'i:1;', 'Fehler in Datenbank schreiben', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(594, 176, 'logMail', 'i:0;', 'Fehler an Shopbetreiber senden', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(595, 177, 'AllowIP', 's:0:"";', 'Auf IP beschränken', NULL, 'text', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(608, 189, 'sql_protection', 'b:1;', 'SQL-Injection-Schutz aktivieren', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(609, 189, 'sql_regex', 's:134:"s_core_|s_order_|benchmark.*\\(|insert.+into|update.+set|(?:delete|select).+from|drop.+(?:table|database)|truncate.+table|union.+select";', 'SQL-Injection-Filter', NULL, 'textarea', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(610, 189, 'xss_protection', 'b:1;', 'XSS-Schutz aktivieren', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(611, 189, 'xss_regex', 's:42:"javascript:|src\\s*=|on[a-z]+\\s*=|style\\s*=";', 'XSS-Filter', NULL, 'textarea', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(612, 189, 'rfi_protection', 'b:1;', 'RemoteFileInclusion-Schutz aktivieren', NULL, 'checkbox', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(613, 189, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 'RemoteFileInclusion-Filter', NULL, 'textarea', 0, 0, 0, NULL, NULL, 0x613a303a7b7d),
(614, 191, 'vouchername', 's:9:"Gutschein";', 'Gutscheine Bezeichnung', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(615, 190, 'minsearchlenght', 's:1:"3";', 'Minimale Suchwortlänge', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(620, 191, 'discountname', 's:15:"Warenkorbrabatt";', 'Rabatte Bezeichnung ', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(623, 191, 'surchargename', 's:20:"Mindermengenzuschlag";', 'Mindermengen Bezeichnung', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(624, 192, 'no_order_mail', 'b:0;', 'Bestellbestätigung nicht an Shopbetreiber schicken', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(625, 190, 'badwords', 's:381:"ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,fÃ¼r,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,kÃ¶nnen,wird,sind,ihre,einem,of,du,sind,einer,Ã¼ber,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am";', 'Blacklist für Keywords', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(626, 191, 'paymentsurchargeadd', 's:25:"Zuschlag für Zahlungsart";', 'Bezeichnung proz. Zuschlag für Zahlungsart', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(627, 191, 'paymentsurchargedev', 's:25:"Abschlag für Zahlungsart";', 'Bezeichnung proz. Abschlag für Zahlungsart', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(628, 191, 'discountnumber', 's:11:"sw-discount";', 'Rabatte Bestellnummer', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(629, 191, 'surchargenumber', 's:12:"sw-surcharge";', 'Mindermengen Bestellnummer', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(630, 191, 'paymentsurchargenumber', 's:10:"sw-payment";', 'Zuschlag für Zahlungsart (Bestellnummer)', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(631, 190, 'maxlivesearchresults', 's:1:"6";', 'Anzahl der Ergebnisse in der Livesuche', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(632, 191, 'ignoreshippingfreeforsurcharges', 'b:0;', 'Absolute Zahlungszuschläge für Versandkosten immer berechnen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(633, 192, 'send_confirm_mail', 'b:1;', 'Registrierungsbestätigung in CC an Shopbetreiber schicken', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(634, 192, 'optinnewsletter', 'b:0;', 'Double-Opt-In für Newsletter-Anmeldungen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(635, 192, 'optinvote', 'b:1;', 'Double-Opt-In für Artikel-Bewertungen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(636, 191, 'shippingdiscountnumber', 's:16:"SHIPPINGDISCOUNT";', 'Abschlag-Versandregel (Bestellnummer)', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(637, 191, 'shippingdiscountname', 's:15:"Warenkorbrabatt";', 'Abschlag-Versandregel (Bezeichnung)', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(641, 192, 'orderstatemailack', 's:0:"";', 'Bestellstatus - Änderungen CC-Adresse', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(642, 247, 'premiumshippiungasketselect', 's:93:"MAX(a.topseller) as has_topseller, MAX(at.attr3) as has_comment, MAX(b.esdarticle) as has_esd";', 'Erweitere SQL-Abfrage', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(643, 247, 'premiumshippingnoorder', 'b:0;', 'Bestellung bei keiner verfügbaren Versandart blocken', NULL, 'boolean', 1, 0, 0, NULL, NULL, NULL),
(646, 249, 'routertolower', 'b:1;', 'Nur Kleinbuchstaben in den Urls nutzen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(648, 249, 'redirectnotfound', 'b:1;', 'Bei nicht vorhandenen Kategorien/Artikel auf Startseite umleiten', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(649, 249, 'seometadescription', 'b:1;', 'Meta-Description von Artikel/Kategorien aufbereiteten', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(650, 249, 'routerremovecategory', 'b:0;', 'KategorieID aus Url entfernen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(651, 249, 'seoqueryblacklist', 's:50:"sPage,sPerPage,sSupplier,sFilterProperties,p,n,s,f";', 'SEO-Nofollow Querys', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(652, 249, 'seoviewportblacklist', 's:112:"login,ticket,tellafriend,note,support,basket,admin,registerFC,newsletter,search,search,account,checkout,register";', 'SEO-Nofollow Viewports', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(653, 249, 'seoremovewhitespaces', 'b:1;', 'überflüssige Leerzeichen / Zeilenumbrüchen entfernen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(654, 249, 'seoremovecomments', 'b:1;', 'Html-Kommentare entfernen', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(655, 249, 'seoqueryalias', 's:127:"sSearch=q,\nsPage=p,\nsPerPage=n,\nsSupplier=s,\nsFilterProperties=f,\nsCategory=c,\nsCoreId=u,\nsTarget=t,\nsValidation=v,\nsTemplate=l";', 'Query-Aliase', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(656, 249, 'seobacklinkwhitelist', 's:54:"www.shopware.de,\r\nwww.shopware.ag,\r\nwww.shopware-ag.de";', 'SEO-Follow Backlinks', NULL, 'textarea', 0, 0, 1, NULL, NULL, NULL),
(657, 249, 'seorelcanonical', 'b:1;', 'SEO-Canonical-Tags nutzen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(658, 249, 'routerlastupdate', NULL, 'Datum des letzten Updates', NULL, 'datetime', 0, 0, 1, NULL, NULL, NULL),
(659, 249, 'routercache', 's:5:"86400";', 'SEO-Urls Cachezeit Tabelle', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(660, 249, 'routerurlcache', 's:5:"86400";', 'SEO-Urls Cachezeit Urls', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(661, 248, 'vatcheckendabled', 'b:0;', 'Modul aktivieren', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(662, 248, 'vatcheckadvancednumber', 's:0:"";', 'Eigene USt-IdNr. für die Überprüfung', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(663, 248, 'vatcheckadvanced', 'b:0;', 'Erweiterte Überprüfung aktivieren', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(664, 248, 'vatcheckadvancedcountries', 's:2:"AT";', 'gültige Länder für erweiterte Überprüfung', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(665, 248, 'vatcheckrequired', 'b:0;', 'USt-IdNr. als Pflichtfeld markieren', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(666, 248, 'vatcheckdebug', 'b:0;', 'Erweiterte Fehlerausgabe aktivieren', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(667, 249, 'routerarticletemplate', 's:70:"{sCategoryPath articleID=$sArticle.id}/{$sArticle.id}/{$sArticle.name}";', 'SEO-Urls Artikel-Template', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(668, 249, 'routercategorytemplate', 's:41:"{sCategoryPath categoryID=$sCategory.id}/";', 'SEO-Urls Kategorie-Template', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(669, 248, 'vatchecknoservice', 'b:1;', 'Wenn Service nicht erreichbar ist, nur einfach Überpürfung durchführen.', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(670, 249, 'seostaticurls', 's:50:"sViewport=cat&sCategory={$sCategoryStart},listing/";', 'sonstige SEO-Urls', NULL, 'textarea', 0, 0, 1, NULL, NULL, NULL),
(671, 248, 'vatcheckconfirmation', 'b:0;', 'Amtliche Bestätigungsmitteilung bei der erweiterten Überprüfung anfordern', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(672, 248, 'vatcheckvalidresponse', 's:4:"A, D";', 'Gültige Ergebnisse bei der erweiterten Überprüfung', NULL, 'text', 0, 0, 0, NULL, NULL, NULL),
(673, 119, 'shopName', 's:13:"Shopware Demo";', 'Name des Shops', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(674, 119, 'mail', 's:16:"info@example.com";', 'Shopbetreiber eMail', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(675, 119, 'address', 's:0:"";', 'Adresse', NULL, 'textarea', 0, 0, 1, NULL, NULL, NULL),
(676, 119, 'taxNumber', 's:0:"";', 'UStId', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(677, 119, 'bankAccount', 's:0:"";', 'Bankverbindung', NULL, 'textarea', 0, 0, 1, NULL, NULL, NULL),
(843, 119, 'captchaColor', 's:7:"0,0,255";', 'Schriftfarbe Captcha (R,G,B)', NULL, 'text', 0, 10, 1, NULL, NULL, NULL),
(844, 173, 'botBlackList', 's:2780:"antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;iron33;israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;ko_yappo_robot;labelgrabber.txt;larbin;legs;linkidator;linkscan;lockon;logo_gif;macworm;magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient";', 'Bot-Liste', NULL, 'textarea', 1, 20, 0, NULL, NULL, NULL),
(845, 78, 'version', 's:5:"4.0.0";', 'Version', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(846, 78, 'revision', 's:4:"3024";', 'Revision', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(847, 78, 'baseFile', 's:12:"shopware.php";', 'Base-File', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(848, 253, 'esdKey', 's:33:"552211cce724117c3178e3d22bec532ec";', 'ESD-Key', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(849, 147, 'blogdetailtemplates', 's:10:":Standard;";', 'Verfügbare Templates Blog-Detailseite', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(850, 190, 'fuzzysearchdistance', 'i:20;', 'Maximal-Distanz für Unscharfe Suche in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(851, 190, 'fuzzysearchexactmatchfactor', 'i:100;', 'Faktor für genaue Treffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(852, 190, 'fuzzysearchlastupdate', 's:19:"2010-01-01 00:00:00";', 'Datum des letzten Updates', NULL, 'datetime', 0, 0, 0, NULL, NULL, NULL),
(853, 190, 'fuzzysearchmatchfactor', 'i:5;', 'Faktor für unscharfe Treffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(854, 190, 'fuzzysearchmindistancentop', 'i:20;', 'Minimale Relevanz zum Topartikel in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(855, 190, 'fuzzysearchpartnamedistancen', 'i:25;', 'Maximal-Distanz für Teilnamen in Prozent', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(856, 190, 'fuzzysearchpatternmatchfactor', 'i:50;', 'Faktor für Teiltreffer', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(857, 190, 'fuzzysearchpricefilter', 's:47:"5|10|20|50|100|300|600|1000|1500|2500|3500|5000";', 'Auswahl Preisfilter', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(858, 190, 'fuzzysearchresultsperpage', 'i:12;', 'Ergebnisse pro Seite', NULL, 'number', 1, 0, 1, NULL, NULL, NULL),
(859, 190, 'fuzzysearchselectperpage', 's:11:"12|24|36|48";', 'Auswahl Ergebnisse pro Seite', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(860, 253, 'esdMinSerials', 'i:5;', 'ESD-Min-Serials', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(867, 255, 'alsoBoughtShow', 'b:1;', 'Anzeigen der Kunden-kauften-auch-Empfehlung', NULL, 'checkbox', 1, 1, 1, NULL, NULL, NULL),
(868, 255, 'alsoBoughtPerPage', 'i:4;', 'Anzahl an Artikel pro Seite in der Liste', NULL, 'number', 1, 2, 1, NULL, NULL, NULL),
(869, 255, 'alsoBoughtMaxPages', 'i:10;', 'Maximale Anzahl von Seiten in der Liste', NULL, 'number', 1, 3, 1, NULL, NULL, NULL),
(870, 255, 'similarViewedShow', 'b:1;', 'Anzeigen der Kunden-schauten-sich-auch-an-Empfehlung', NULL, 'checkbox', 1, 5, 1, NULL, NULL, NULL),
(871, 255, 'similarViewedPerPage', 'i:4;', 'Anzahl an Artikel pro Seite in der Liste', NULL, 'number', 1, 6, 1, NULL, NULL, NULL),
(872, 255, 'similarViewedMaxPages', 'i:10;', 'Maximale Anzahl von Seiten in der Liste', NULL, 'number', 1, 7, 1, NULL, NULL, NULL),
(873, 256, 'revocationNotice', 'b:1;', 'Zeige Widerrufsbelehrung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(874, 256, 'newsletter', 'b:0;', 'Zeige Newsletter-Registrierung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(875, 256, 'bankConnection', 'b:0;', 'Zeige Bankverbindungshinweis an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(876, 256, 'additionalFreeText', 'b:0;', 'Zeige weiteren Hinweis an', 'Snippet: ConfirmTextOrderDefault', 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(877, 256, 'commentVoucherArticle', 'b:0;', 'Zeige weitere Optionen an', 'Artikel hinzuf&uuml;gen, Gutschein hinzuf&uuml;gen, Kommentarfunktion', 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(878, 256, 'bonusSystem', 'b:0;', 'Zeige Bonus-System an (falls installiert)', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(879, 256, 'premiumArticles', 'b:0;', 'Zeige Prämienartikel an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(880, 256, 'countryNotice', 'b:1;', 'Zeige Länder-Beschreibung an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(881, 256, 'nettoNotice', 'b:0;', 'Zeige Hinweis für Netto-Bestellungen an', NULL, 'boolean', 0, 0, 1, NULL, NULL, 0x613a303a7b7d),
(882, 256, 'basketHeaderColor', 's:7:"#f5f5f5";', 'Warenkorbkopf Hintergrundfarbe', '(Hex-Code)', 'color', 0, 1, 1, NULL, NULL, 0x613a303a7b7d),
(883, 256, 'basketHeaderFontColor', 's:4:"#000";', 'Warenkorbkopf Textfarbe', '(Hex-Code)', 'color', 0, 1, 1, NULL, NULL, 0x613a303a7b7d),
(884, 256, 'basketTableColor', 's:7:"#f5f5f5";', 'Warenkorbtabelle Hintergrundfarbe', '(Hex-Code)', 'color', 0, 1, 1, NULL, NULL, 0x613a303a7b7d),
(885, 256, 'mainFeatures', 's:290:"{if $sBasketItem.additional_details.properties}\n    {$sBasketItem.additional_details.properties}\n{elseif $sBasketItem.additional_details.description}\n    {$sBasketItem.additional_details.description}\n{else}\n    {$sBasketItem.additional_details.description_long|strip_tags|truncate:50}\n{/if}";', 'Template für die wesentliche Merkmale', NULL, 'textarea', 0, 1, 1, NULL, NULL, 0x613a303a7b7d),
(886, 259, 'backendTimeout', 'i:7200;', 'Timeout', NULL, 'interval', 1, 0, 0, NULL, NULL, 0x613a303a7b7d),
(887, 259, 'backendLocales', 'a:2:{i:0;i:1;i:1;i:2;}', 'Auswählbare Sprachen', NULL, 'select', 1, 0, 0, NULL, NULL, 0x613a323a7b733a353a2273746f7265223b733a31313a22626173652e4c6f63616c65223b733a31313a226d756c746953656c656374223b623a313b7d),
(888, 249, 'routerblogtemplate', 's:71:"{sCategoryPath categoryID=$blogArticle.categoryId}/{$blogArticle.title}";', 'SEO-Urls Blog-Template', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(889, 256, 'detailModal', 'b:1;', 'Artikeldetails in Modalbox anzeigen', NULL, 'boolean', 0, 0, 1, NULL, NULL, NULL),
(890, 260, 'tsid', 's:0:"";', 'Trusted-Shops-ID', '', 'text', 0, 0, 1, NULL, NULL, NULL),
(891, 144, 'blogcategory', 's:0:"";', 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen', '', 'text', 0, 0, 1, NULL, NULL, NULL),
(892, 144, 'bloglimit', 's:1:"3";', 'Anzahl Blog-Einträge auf Startseite', '', 'text', 0, 0, 1, NULL, NULL, NULL),
(893, 119, 'company', 's:0:"";', 'Firma', NULL, 'textfield', 0, 0, 1, NULL, NULL, NULL),
(894, 249, 'routercampaigntemplate', 's:64:"{sCategoryPath categoryID=$campaign.categoryId}/{$campaign.name}";', 'SEO-Urls Landingpage-Template', NULL, 'text', 0, 0, 1, NULL, NULL, NULL),
(897, 191, 'paymentSurchargeAbsolute', 's:25:"Zuschlag für Zahlungsart";', 'Pauschaler Aufschlag für Zahlungsart (Bezeichnung)', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(898, 191, 'paymentSurchargeAbsoluteNumber', 's:19:"sw-payment-absolute";', 'Pauschaler Aufschlag für Zahlungsart (Bestellnummer)', NULL, 'text', 1, 0, 1, NULL, NULL, NULL),
(899, 262, 'StoreApiUrl', 's:33:"http://store.shopware.de/storeApi";', 'Store API Url', NULL, 'text', 0, 0, 1, NULL, NULL, 0x613a303a7b7d);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_element_translations`
--

DROP TABLE IF EXISTS `s_core_config_element_translations`;
CREATE TABLE IF NOT EXISTS `s_core_config_element_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `locale_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `element_id` (`element_id`,`locale_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=202 ;

--
-- Daten für Tabelle `s_core_config_element_translations`
--

INSERT INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`) VALUES
(1, 225, 2, 'Controller selection', NULL),
(2, 276, 2, 'Controller selection', NULL),
(4, 224, 2, 'Show', NULL),
(5, 269, 2, 'Show', NULL),
(6, 273, 2, 'Show', NULL),
(7, 275, 2, 'Show', NULL),
(11, 186, 2, 'VAT vouchers', NULL),
(12, 188, 2, 'VAT discounts', NULL),
(13, 189, 2, 'Article evaluations need to be unlocked', NULL),
(14, 190, 2, 'Automatical suggestion of article number', NULL),
(15, 191, 2, 'Prefix für automatically generated article number', NULL),
(16, 192, 2, 'Deactivate article evaluations ', NULL),
(17, 193, 2, 'Send automatical reminder of article evaluation', 'Remind buyer of article evaluation via e-mail after purchase '),
(18, 194, 2, 'Days until the reminder e-mail will be sent', 'Days until the the customer will be reminded of the article evaluation'),
(19, 195, 2, 'Set tax for discounts dynamically', NULL),
(20, 226, 2, 'Size of preview picture', NULL),
(21, 227, 2, 'Storage period in days', NULL),
(22, 231, 2, 'Number of articles in process (recently viewed)', NULL),
(23, 246, 2, 'Categories buffering time', NULL),
(24, 247, 2, 'Prices buffering time', NULL),
(25, 248, 2, 'Top seller buffering time', NULL),
(26, 249, 2, 'Manufacturer buffering time', NULL),
(27, 250, 2, 'Article detail page buffering time', NULL),
(28, 251, 2, 'Country list buffering time', NULL),
(29, 252, 2, 'Cache search', NULL),
(30, 253, 2, 'Translations buffering time', NULL),
(31, 254, 2, 'Close shop due to maintenance', NULL),
(32, 255, 2, 'IP excluded from closure', NULL),
(33, 256, 2, 'Empty shop cache after each order (performance-weighted)', 'Warning: This might have negative effects on your performance!'),
(34, 257, 2, 'Deactivate shop cache', NULL),
(35, 270, 2, 'Number of levels', NULL),
(36, 271, 2, 'Activate caching', NULL),
(37, 272, 2, 'Caching time', NULL),
(38, 274, 2, 'Maximum number of articles to be compared', NULL),
(39, 277, 2, 'Name of the tag class', NULL),
(40, 278, 2, 'Maximum number of terms', NULL),
(41, 279, 2, 'Number of steps', NULL),
(42, 280, 2, 'Considered time in days', NULL),
(43, 286, 2, 'Articles per page', NULL),
(44, 287, 2, 'Standard sorting of listings', NULL),
(45, 288, 2, 'Categories max. number of pages', NULL),
(46, 289, 2, 'Mark articles as new (days)', NULL),
(47, 290, 2, 'Mark articles as top seller', NULL),
(48, 291, 2, 'Number of top sellers for charts', NULL),
(49, 292, 2, 'Selection of articles per page', NULL),
(50, 293, 2, 'Available templates categories', NULL),
(51, 294, 2, 'Max. number of selectable articles/articles via pulldown menu', NULL),
(52, 295, 2, 'Text for non-available articles', NULL),
(53, 296, 2, 'Number of similar articles for cross selling', NULL),
(54, 297, 2, 'Number of customers also bought"articles cross selling"', NULL),
(55, 298, 2, 'Standard template for new categories', NULL),
(56, 299, 2, 'Number of days to be considered for top seller creation', NULL),
(57, 300, 2, 'Number of automatically determined similar articles (detail page)', NULL),
(58, 301, 2, 'Show delivery time in shopping cart', NULL),
(59, 302, 2, 'Number of articles shown under novelties', NULL),
(60, 303, 2, 'Request form ID', NULL),
(61, 304, 2, 'Min. shopping cart value from which option of individual request is offered', NULL),
(62, 305, 2, 'Zoom viewer instead of light box on detail page ', NULL),
(63, 306, 2, 'Check stock level on detail page in real time', NULL),
(64, 307, 2, 'Max. number of configurator variants per article', NULL),
(65, 308, 2, 'Deactivate sales articles without stock level', NULL),
(66, 309, 2, 'Show main articles in bundles', NULL),
(67, 310, 2, 'Hide shopping cart with e-mail notification', 'Hide shopping cart with activated e-mail notification and non-existing stock level'),
(68, 311, 2, 'Show in shopping cart if stock level is undershot', NULL),
(69, 312, 2, 'Jump to detail if only one article is available', NULL),
(70, 313, 2, 'Configurator open text fields', NULL),
(71, 314, 2, 'Available templates detail page', NULL),
(72, 315, 2, 'Max. number of manufacturers in sidebar', NULL),
(73, 317, 2, 'Min. lenth of password (registration)', NULL),
(74, 318, 2, 'Standard payment method (Id) (registration)', NULL),
(75, 319, 2, 'Standard group of recipients (ID) for registered customers (System/newsletter)', NULL),
(76, 320, 2, 'Shopware generates customer numbers', NULL),
(77, 321, 2, 'Deactivate terms-checkbox on checkout page.', NULL),
(78, 322, 2, 'Check country with shipping address', NULL),
(79, 323, 2, 'Data protection regulations need to be accepted over checkbox', NULL),
(80, 324, 2, 'Fallback payment type (ID)', NULL),
(81, 325, 2, 'E-mail address must be entered twice.', 'E-mail address must be entered twice in order to avoid typing errors'),
(82, 326, 2, 'Check extended fields in newsletter registration', NULL),
(83, 327, 2, 'deactivate No customer account', NULL),
(84, 585, 2, 'Exclude IP from statistics', NULL),
(85, 586, 2, 'Google Analytics-ID', NULL),
(86, 587, 2, 'Google Conversion-ID', NULL),
(87, 588, 2, 'Anonymise IP address', NULL),
(88, 589, 2, 'Cache controller/Times', NULL),
(89, 590, 2, 'NoCache Controller/Tags', NULL),
(90, 591, 2, 'Alternative Proxy URL', NULL),
(91, 592, 2, 'Admin view', NULL),
(92, 593, 2, 'Write error in database', NULL),
(93, 594, 2, 'Send error to shop owner', NULL),
(94, 595, 2, 'Limit to IP', NULL),
(95, 608, 2, 'Activate SQL injection protection', NULL),
(96, 609, 2, 'SQL injection filter', NULL),
(97, 610, 2, 'Activate XXS protection', NULL),
(98, 611, 2, 'XXS filter', NULL),
(99, 612, 2, 'Activate RemoteFileInclusion-protection', NULL),
(100, 613, 2, 'RemoteFileInclusion-filter', NULL),
(101, 614, 2, 'Designation vouchers', NULL),
(102, 615, 2, 'Max. length of search term', NULL),
(103, 620, 2, 'Designation discounts', NULL),
(104, 623, 2, 'Designation of reduced quantities', NULL),
(105, 624, 2, 'Do not send order confirmation to shop owner', NULL),
(106, 625, 2, 'Blacklist for keywords', NULL),
(107, 626, 2, 'Designation for percental surcharge on payment method ', NULL),
(108, 627, 2, 'Designation percental deduction on payment method', NULL),
(109, 628, 2, 'Discounts order number', NULL),
(110, 629, 2, 'Reduced quantities order number', NULL),
(111, 630, 2, 'Surcharge on payment method', NULL),
(112, 631, 2, 'Number of results live search', NULL),
(113, 632, 2, 'Always calculate absolute surcharges for shipping costs', NULL),
(114, 633, 2, 'Send registration confirmation to shop owner in CC.', NULL),
(115, 634, 2, 'Double-opt-in for newsletter subscriptions', NULL),
(116, 635, 2, 'Double-opt-in for article evaluations', NULL),
(117, 636, 2, 'Deduction dispatch rule (order number)', NULL),
(118, 637, 2, 'Deduction dispatch rules (designation)', NULL),
(119, 641, 2, 'Order status - Chnages CC address', NULL),
(120, 642, 2, 'Extended SQL query', NULL),
(121, 643, 2, 'Block order with no available shipping type', NULL),
(122, 646, 2, 'Only use lower case letters in URLs', NULL),
(123, 648, 2, 'Redirect to starting page in case of non-available categories/articles ', NULL),
(124, 649, 2, 'Prepare meta description od articles/categories', NULL),
(125, 650, 2, 'Remove Category ID from URL', NULL),
(126, 651, 2, 'SEO-Nofollow-Querys', NULL),
(127, 652, 2, 'SEO-Nofollow Viewports', NULL),
(128, 653, 2, 'Remove needless blank spaces or line breaks', NULL),
(129, 654, 2, 'Remove HTML comments', NULL),
(130, 655, 2, 'Query-Aliase', NULL),
(131, 656, 2, 'SEO-Follow Backlinks', NULL),
(132, 657, 2, 'Use SEO Canonical Tags', NULL),
(133, 658, 2, 'Date of last update', NULL),
(134, 659, 2, 'SEO-URLs caching time table', NULL),
(135, 660, 2, 'SEO-URLs caching time URLs', NULL),
(136, 661, 2, 'Activate module', NULL),
(137, 662, 2, 'Separate VAT ID for verification', NULL),
(138, 663, 2, 'Activate extended verification', NULL),
(139, 664, 2, 'Valid countries for extended verification', NULL),
(140, 665, 2, 'Mark VAT ID number as required', NULL),
(141, 666, 2, 'Activate extended error output', NULL),
(142, 667, 2, 'SEO URLs article template', NULL),
(143, 668, 2, 'SEO URLs category template', NULL),
(144, 669, 2, 'If service is not available, complete normal verification', NULL),
(145, 670, 2, 'Other SEO URLs', NULL),
(146, 671, 2, 'Request official confirmation with extended verification', NULL),
(147, 672, 2, 'Valid results with extended verification.', NULL),
(148, 673, 2, 'Shop name', NULL),
(149, 674, 2, 'Shop owner e-mail', NULL),
(150, 675, 2, 'Address', NULL),
(151, 676, 2, 'VAT ID', NULL),
(152, 677, 2, 'Bank account', NULL),
(153, 843, 2, 'Font color Captcha (R,G,B)', NULL),
(154, 844, 2, 'Bot list', NULL),
(155, 845, 2, 'Version', NULL),
(156, 846, 2, 'Revision', NULL),
(157, 847, 2, 'Base file', NULL),
(158, 848, 2, 'ESD key', NULL),
(159, 849, 2, 'Available templates blog detail page', NULL),
(160, 850, 2, 'Max. distance for fuzzy search (percentage)', NULL),
(161, 851, 2, 'Factor for accurate hits ', NULL),
(162, 852, 2, 'Date of the last update', NULL),
(163, 853, 2, 'Factor for inaccurate hits ', NULL),
(164, 854, 2, 'Min. relevance for top articles (percentage)', NULL),
(165, 855, 2, 'Max. distance for partial names (percentage)', NULL),
(166, 856, 2, 'Factor for partial hits', NULL),
(167, 857, 2, 'Selection price filter', NULL),
(168, 858, 2, 'Results per page', NULL),
(169, 859, 2, 'Selection results per page', NULL),
(170, 860, 2, 'ESD-Min-Serials', NULL),
(171, 867, 2, 'Show Customers-also-bought-recommendation', NULL),
(172, 868, 2, 'Number of articles per page in the list', NULL),
(173, 869, 2, 'Max. number of pages in the list.', NULL),
(174, 870, 2, 'Show customers-also-viewed-recommendation', NULL),
(175, 871, 2, 'Number of articles par page in the list', NULL),
(176, 872, 2, 'Max. number of pages in the list.', NULL),
(177, 873, 2, 'Shop cancellation policy', NULL),
(178, 874, 2, 'Show newsletter registration', NULL),
(179, 875, 2, 'Show bank detail notice', NULL),
(180, 876, 2, 'Show further notices', 'Snippet: ConfirmTextOrderDefault'),
(181, 877, 2, 'Show further options', 'Add article, add voucher, comment function'),
(182, 878, 2, 'Show Bonus System (if installed)', NULL),
(183, 879, 2, 'Show premium articles', NULL),
(184, 880, 2, 'Show country descriptions', NULL),
(185, 881, 2, 'Show information for net orders', NULL),
(186, 882, 2, 'Background color of shopping cart-header', '(Hex-Code)'),
(187, 883, 2, 'Text color of shopping cart header', '(Hex-Code)'),
(188, 884, 2, 'Background color of shopping cart table', '(Hex-Code)'),
(189, 885, 2, 'Template for essential characteristics', NULL),
(190, 886, 2, 'Timeout', NULL),
(191, 887, 2, 'Selectable languages ', NULL),
(192, 888, 2, 'SEO-URLs blog template', NULL),
(193, 889, 2, 'Show article details in modal box', NULL),
(194, 890, 2, 'Trusted Shops ID', NULL),
(195, 891, 2, 'Show blog entries from category (ID) on starting page', NULL),
(196, 892, 2, 'Number of blog entries on starting page', NULL),
(197, 893, 2, 'Company', NULL),
(198, 894, 2, 'SEO-URLs landingpage template', NULL),
(199, 897, 2, 'Lump sum for payment method (description)', NULL),
(200, 898, 2, 'Lump sum for payment method (order number)', NULL),
(201, 899, 2, 'Store API URL', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_forms`
--

DROP TABLE IF EXISTS `s_core_config_forms`;
CREATE TABLE IF NOT EXISTS `s_core_config_forms` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `plugin_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `plugin_id` (`plugin_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=263 ;

--
-- Daten für Tabelle `s_core_config_forms`
--

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(77, NULL, 'Base', 'Shopeinstellungen', NULL, 0, 0, NULL),
(78, NULL, 'Core', 'System', NULL, 10, 0, NULL),
(79, NULL, 'Product', 'Artikel', NULL, 20, 0, NULL),
(80, NULL, 'Frontend', 'Storefront', NULL, 30, 0, NULL),
(82, NULL, 'Interface', 'Schnittstellen', NULL, 50, 0, NULL),
(83, NULL, 'Payment', 'Zahlungsarten', NULL, 60, 0, NULL),
(84, 79, 'Product29', 'Artikelnummern', NULL, 1, 0, NULL),
(86, 79, 'Product35', 'Sonstige MwSt.-Sätze', NULL, 4, 1, NULL),
(87, 79, 'PriceGroup', 'Preisgruppen', NULL, 5, 0, NULL),
(88, 79, 'Unit', 'Preiseinheiten', NULL, 6, 0, NULL),
(89, 79, 'Attribute', 'Artikel-Freitextfelder', NULL, 7, 0, NULL),
(90, 80, 'Rating', 'Artikelbewertungen', NULL, 8, 0, NULL),
(92, NULL, 'Other', 'Weitere Einstellungen', NULL, 60, 0, NULL),
(102, 80, 'LastArticles', 'Artikelverlauf', '', 0, 1, 23),
(118, 77, 'Shop', 'Shops', NULL, 0, 0, NULL),
(119, 77, 'MasterData', 'Stammdaten', NULL, 10, 1, NULL),
(120, 77, 'Currency', 'Währungen', NULL, 20, 0, NULL),
(121, 77, 'Locale', 'Lokalisierungen', NULL, 30, 0, NULL),
(122, 77, 'Template', 'Templates', NULL, 40, 0, NULL),
(123, 77, 'Tax', 'Steuern', NULL, 50, 0, NULL),
(124, 77, 'Mail', 'Mailer', NULL, 60, 1, NULL),
(125, 77, 'Number', 'Nummernkreise', NULL, 70, 0, NULL),
(126, 77, 'CustomerGroup', 'Kundengruppen', NULL, 80, 0, NULL),
(127, 78, 'QueryCache', 'Caching', NULL, 10, 0, NULL),
(128, 78, 'Service', 'Wartung', NULL, 20, 0, NULL),
(133, 80, 'AdvancedMenu', 'Erweitertes Menü', '', 0, 1, 29),
(134, 80, 'Compare', 'Artikelvergleich', NULL, 0, 1, 20),
(135, 80, 'TagCloud', 'Schlagwortwolke', '', 0, 1, 34),
(144, 80, 'Frontend30', 'Kategorien / Listen', NULL, 1, 0, NULL),
(145, 80, 'Frontend76', 'Topseller / Neuheiten', NULL, 2, 0, NULL),
(146, 80, 'Frontend77', 'Cross-Selling / Ähnliche Art.', NULL, 3, 0, NULL),
(147, 80, 'Frontend79', 'Warenkorb / Artikeldetails', NULL, 5, 1, NULL),
(157, 80, 'Frontend33', 'Anmeldung / Registrierung', NULL, 0, 1, NULL),
(173, 78, 'Statistics', 'Statistiken', '', 0, 1, 31),
(174, 82, 'Google', 'Google Analytics', '', 0, 1, 26),
(176, 78, 'Log', 'Log', '', 0, 0, 1),
(177, 78, 'Debug', 'Debug', '', 0, 0, 3),
(180, 77, 'Country', 'Länder', NULL, 50, 0, NULL),
(189, 78, 'InputFilter', 'InputFilter', '', 0, 0, 35),
(190, 80, 'Search', 'Suche', NULL, 4, 0, NULL),
(191, 80, 'Frontend71', 'Rabatte / Zuschläge', NULL, 5, 1, NULL),
(192, 80, 'Frontend60', 'eMail-Einstellungen', NULL, 10, 0, NULL),
(247, 80, 'Frontend93', 'Versandkosten-Modul', NULL, 11, 0, NULL),
(248, 80, 'Frontend101', 'USt-IdNr. Überprüfung', NULL, 11, 1, NULL),
(249, 80, 'Frontend100', 'SEO/Router-Einstellungen', NULL, 12, 1, NULL),
(250, 78, 'Widget', 'Widgets', NULL, 30, 0, NULL),
(251, 77, 'CountryArea', 'Länder-Zonen', NULL, 51, 0, NULL),
(252, 78, 'Plugin', 'Plugins', NULL, 20, 0, NULL),
(253, 79, 'Esd', 'ESD', NULL, 0, 0, NULL),
(255, 80, 'Recommendation', 'Artikelempfehlungen', NULL, 8, 1, NULL),
(256, 80, 'Checkout', 'Bestellabschluss', NULL, 0, 1, NULL),
(257, 77, 'PageGroup', 'Shopseiten-Gruppen', NULL, 90, 0, NULL),
(258, 78, 'CronJob', 'Cronjobs', NULL, 50, 0, NULL),
(259, 78, 'Auth', 'Backend', '', 0, 0, 36),
(260, 82, 'TrustedShop', 'Trusted-Shops', '\r\n<style type="text/css">\r\n    body { padding: 10px; }\r\n    h2 { margin: 0 0 .5em; }\r\n    p { margin: 0 0 1.5em }\r\n    .logo { margin: 1em; }\r\n    .logo img { display: block; width: 300px; height: 140px; margin: 0 auto }\r\n</style>\r\n\r\n<div class="logo">\r\n	<img src=''data:image/gif;base64,R0lGODlhLAGMAOZSAP///0xLTszMzAFotsxmM//MZuCFZ2aZzP735ZmZmfvhkv/MzNptSfrr5uDf\r\n4PfagzMzM/3moo641ZnMzJmZZuqpk///zGZmZswzAPHGuAAAAPjh2plmZv3ttsTW4gAzZu23pv/6\r\ntjOZzLKSidi4rdjWslxQMP//mbFTOBgYHMzMmcyZmQBmzNXf5nt6fGaZmeaZgP//ZrSnberz9X5v\r\nRDMzZuTl5deWgejo6LCmoc1CF+rv8PHptObeoczMZkdAMuXkxLa2tqSAdSd/wAEYQWZmM8uyXD9p\r\nhN7gxABLlYCAgu3q5zMzACcZD5GDTx02SLCwsOvr6////wAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA\r\nAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAACH5\r\nBAEAAFIALAAAAAAsAYwAAAf/gFKCg4SFhoeIiYqLjI2Oj5CRkpOUlZaXmJmaQVIJnZqgoaKjpKWm\r\np5OcCQlKF66vsEqenqi1tre4ubqKq62wv8CuAbBSnLvHyMnKy50Jwc+wAdLT1BefzNjZ2tuMUAku\r\n0OEX1OQQ5hDDtNzr7O24q+Di0dPm5NLn+OcX6u79/v+R4Mn7lUCAwSDO6t3Lx9CcNYAQI0rsJS5B\r\nEAHjhAkAwLFjgnoNQzrkJ7GkyWXelMQLdrEjgI8aOa5yIFOkTXNSBJzcyRNXShcrgXW0IcDZsHEb\r\nHQSAcMEGAKU3bQboSbVqqKKsgK5MAAXWRhwXmL5amjQsBCgczTK8IACHg4Qh/x9anUvXkTdWKuMV\r\nlCksAceWDsaRBSBgKQS/ANTm2+jyY8gAJOtKrou1l8p9jAnHC6DE5dOwgwWc+6pYH9+NQW5OXi15\r\nlWUXSjIDCHI0Iw6+ICFsFH2YI++1vlMcjgrBBevjPCtbvqDEKePAsHQDsDHSLGODHG2UxodYtHB8\r\n4xrua5ZTp8Hy6M+rN88+ffv17uPDn/++vnz79O/rz88fv//95jGj3DewgcPVBWi9JFZY3dGkoHQu\r\nYXQTY47hg4N24rmm4YYcdujhhyCGKOKIJJZo4okohqhTMghp6AI8QV1wWxQZhXWBSzhIuKAwIgVQ\r\nlHBJLSbTd/mw5YANt3mm5P+STDbp5JNQRinllFRWaeWTkeUy4It8EVTTMOgYZBRxDHWXAE2/pYAY\r\nFDYZ6QAOUcQp55x01mnnnXjmqeeefPbp55+ABvrSirtsidhLwTgIGj1kinTokBBo0J05wlUInkFI\r\nBqrpppx26umnng56zICy+PYMZL6hs1CjIaUAmUEumKPBjR1doEEKQa6FKZyAcvRnR3H6CqqeAPAJ\r\n7LDBFhuqssZmaQuHLiToDBRijuWjjoYVyVyHrrT63XcBJOngKqk2FJsANvQJ7IXq+urrhbwiWycA\r\nM+J5rLCecVqvvumqW9Au3CboVGNHDbPUqkytIsASTSJgARAqwEWkkAoGESH/BBNzJ8CRewIwwQEe\r\n4HDAEC0we2exHx8AchQjT2CyvHFOIIIELtHJUcoqe3DzBDZMMEGyL58ssgg6bwpACwcQHbSdL+3S\r\n4oayDOybqQbnhs5eSiKg9dZca20BEjmElfFZL6VgtsXFBqCBTdS+SewBA0gQxRADlIzvsVEAAPcA\r\nLMQNgAgDuCxs3jUTXric+XoG9wFHH0Az4kgPwHffLkchAgseDHDAbUg/bjizLtlAebGHKwk0Ry3Q\r\nXXTH/+JCKkUFCRCEEj4SZs9IUruEAABdW+D77787XIISGBdJKYS23sQWuvEyDTfNdLdgQwszSo8D\r\n9Xm30LcEPbdgeeDTz1hs/wvkc3ShU+TvG4X15V9vtwcS6Ly4shzBPcQE5PM8nc9R+OzU/Hlbn/RI\r\nN70BToB72Ukf6dx3vry5L3uqW9q8WncLbinhMq+AQGcUxKjDfE53vNuaBRwGvBL+rgRhWZusRiMT\r\nFUZKA7cqE6bypDe/0e0AfducDYagsiHo7Gg5JBwOAIfDAYigZEjrGwsOEAUbiEAERTwi6VKnsgHc\r\nD3D3A4AEhiCBCUiOBSTjiAdGp7gsToCHXuTbEKa3N6V5gG6a05sPPQY4zTnFA4BbIhcBgEfJHYB8\r\nQ8CcBG3mrFI8TWGI+oULrnMww7DFSV0joQlN2AHfqUBt+JAUR6CgQrMFAP8oARhbQdx2sucBgG48\r\n3F4UlPhH0u2Ni3HC4sg0xzIjbjFuO7RiERmHOsk90ZeAE4HINPdGXQ7MiyzIm8pU9je/eRFkqPzj\r\n4iRgRBzc8AAuU13qWCCBxTnRiHvbXCC7qbnUDWB1xKIgKgZ0qKLEwlSqQkeCGsa1SdqzA/isZAc+\r\nMjGDFM9VsuHnpTbWvDnVEHrnrCHjKGfQKEigjkwEHM2oCTLJ6YyaPqSby5D5wxbwrQVjZAH5PkpR\r\nhdIPmfWT3BCaSTMvisCkmeNhEWWmuZ/N7ZzUhCLcZsY3+cXNizJlAeYAh04aFnIUHZJNV16xikYy\r\nRTZLiqQ9S6jPfOIzBCv/KJ5WhZMCGSkpAS40R+z6NS9TRs+kQi2ZQTlCTRZ8c6M15dttgFrMO/a0\r\nl0O4nlCRlNb5ATBvmQvcdDDaTLi+dH7IHIJifdhHOxIVo4ut4hoP2tbFEi2CzSLUKe6yoRdlBiM8\r\nWgit6FnPqVLVqh2IQGpDUIIfhLV417FRWsK6PFLazKx1M+ld5zQBnQH1rVokpkZreES60WyndvPo\r\nGkMqPZLGcaf9ohcW4bpSItbysEbkIxg9ANKQ5S+QffTAM7nL3TTyjIguJS93Mcs6zZqCW3ohXAIM\r\nRo8NPql3pv0davGp2ghEIAQ9wOQ5ZtXCW2kyARkbq/OSQLO+yS8JjLMo//3GKDnJyU2iwX0pNa3I\r\nN57BscKPOxrfbMBcHEgOaRD22BfVysc6VljDX+zbS9PoVrix4HKMC2QwzamzPF7uuDF+Lt9+zONB\r\nGvSooODsTGbzCzQJ5h4uuC9+TShCSVrAqv31r38BHKkB+8UBXY7UbtZyEdsi7mMecGgrP+YyldEk\r\nWEhLGjaL1U2decBxN5OzzszZzTkHywYqu57KkDRoNrMVik75czd1WrmHYhPPWkS0x+TMvUW3spsl\r\ns8FDoVg5NpsSB5bmGaaNjDh1lgIv3ziUDZzBIMI8mSlQmnLwHNYDFciAAriWgQ960IEQ5DPLWj5B\r\nD8JMYAGkwMCIQbAM0f/FNHc5m3SDMxzhgCZtIXZEe7mN9rRrZrpqI8589/octa3NbWibe9rdJKcg\r\nO5Ikb6ezFlm5IGwSkCTNPDI19xgtab02awuowAkmMAcRPkDwDzwBAjTwAX+1zHAFnEAF/4QATWCC\r\nNgAIeKBmXhapkwXny9ltWc1+2dIGhzdtr9WVFd7jtzfONFMjVStAeQVU04KOMElZhL8DgAVkEPCB\r\nJ+HnFZbcz2vghBOkluFadrgMvkNgJbngtedQMMwChQMPhIzlU896nHrWRbWCSlTvxSAwYtOYmpNt\r\n3/weIQJ84NoPJEGlT/xw0JNwgQicAOkKyLvDkxepKHcEI1DXx67mVXL/05XO8E76IOHxJu4PIp7k\r\nhTcZ49s1+U417RSsPtU+vLEoWEOytBbQOQ1SQIS3W1ECSLBACHggABdXOAkJuHvDI5B3YWt1bS9K\r\n4TmOTSRXlbmg9LKB1KJwPuEjKTvCF5bxhX8b4Tdx+cz3DXYkT5jpT9sGDgo+9NPl/Omka4HO//Px\r\n5RR+rffK5aHwRTgWspCZd0SqvgNAazVges0BwXchUL0FenCEoFdYBbKndwJ4Aku3ezAUQ7zHFFol\r\nVjNUaufAGRO3GDrCFBZTGOCRGLqxHX4RK8LhAvXyGcXzSA8iViMoeEwRBbxBKxgiglBRO64mHeb3\r\nK0h2CZnnCmfiavaw/xCPkjWgBwA+wASl90UToF9XdlUyUH8V9gIBSHsC6HBFEFZm8x2xkTdghXEG\r\nxXcwdCO2civHZhAwJCsa4IVcSGDHFgRbCEPHxgpfeIKkoxQx1GU0gYWSgmDHdoAXEARrgwNgdSu7\r\ncYDJ5oepEoZYF4NU6F6hUIP6hhGwEVro4Hjv14My0AQfUGEsMIT6ZVX+9gT+Z0U9cAJNqHcPcAI+\r\nsBZYMxRhJk8EhTi24hdVmBhN9xe3MlsFAUOeUYa+sTYOEimI4QDJogRrEyQaEGWr+BK/+BRi1kIQ\r\noIdrc2DLiBi2ojYBEIgbQYj+Yoig8AtoQRQckX2/0RtMMmU+mAKTWP9hlnhlRZhPXIaEfFMC/vWJ\r\nCvAATrg2QOIZb7ERfMeAzBYstrIPuuiKh0EuAhCLOKA2sziHe2GLs1GMFrc2CTAweQNmGtAZVIiL\r\nwxiQ0gGRaFOFyriPOFAPmuQddDhmgkiNzWIKZggLcQghHlFzAZB9ugN6oacCkhh0BwA8+9VrEJcE\r\nfUOJJeCJ7vgAoegDTJcgG0EdBFaF57BI+Zg3Z1iMTYkrAalCxXaAMBRlCImHECIaX9hO/Zg3Fgl4\r\n/Nh0GImMOPB0ZsFJ4MCMEAAA/aiV00iS6WSNmVCDG+QMGwEFsgAX3siDOBd6PAABRBB0Q5B65niT\r\nFjCKmzgE7PiTQAn/j//Ej9LRalhJKbXFK67IFskTGAy5ClGAhxoQSh8JQ66xEVdZjF75EXwYiH6R\r\nN1gJeF/IgpGSkXm4ivuYAl2hScmTQjfilpUHl6X2XrBwKISzHSr5iH2pdkUQhOR4ift1AiHgBIHp\r\nfyLAA4zZmCfgBL0nmuBGjPlALW2hjx9ZhmbpEniYAgmpG1FZixqANq1pblpZcdBYKwS2j9ZnjOtJ\r\nlrZCLV9II5p0irjImybnm8GCfplAEDPnTvrwQbsDkwhAAfQXdCKgf1WVT0YXAifwA+NIkz75iY1Z\r\nAAowivlwNn+3gPgYXftohhTJkCrhhebJlpISlYtIb6WJPC+yh9OY/5Cz4gwwZI+ahC9jyZ3aISkO\r\ncGxa+KLFiJFayRz7oD4CWoil8AwFUW8YaA72ZZxpN0I9wAQZKjkC4Ds3iU8nsHMPGnQsIAAbKoCN\r\nmaY/kDHCsQ9is2yZ4ooHSDZNaaQtSp9UiYuxCYvSYZSASD8INqfO2KOI857IaJR+YSsWs4pgNVr0\r\n+UJfmGhN+puksFS/4EhKAAU5ki2PAn/BQwPR+WKqN6FW5ZwPoKWbeABnCopp+gAeOnoNMacNoZRu\r\nU30GsTHSd6uYMn23ShS6KgAoiKtPYRC3kSOjCXmVwRjB+pbBkiPC6gAGEaxoAqy2equk06u/Sq2T\r\nWmpyiQmXOhYPqP8YmaE1IWRlI6QCKaCOQ0iqYOqcJ2ACofpiSMCETdiqrvoAFDA2+KCvd5iKjnc6\r\n3ZZ49zKwhwOwoGNyhQMsCvtskbck23pkp3Yq1JAPAcAwVkploOp/QwAEvnaT7gqq6jgEZlqdaVoA\r\nD2AEDPEtu7evCOKvDlt4p7N45ZawBJs49GOzBgttjqh4HEdtBHtyAcopYCcKmYcqhXE7plGux+k7\r\nCDBs6ngA+decvaYA8BqyI8uh9loAJlsAxTMrRREpF4Amp+gqUveyd2NuBWu2M7u2UuKI5ea2AXuw\r\naOtt5uMADoIsQxsKKxEAaNGNs2ql5jpC0LmJAtCxFNprIWAEQPj/tEhwdwpAr6xaslpbAGvaj1hZ\r\nb96hMcwGAOr3CoiCNoqYI8H5lgAQI1GGiAYRnNAKDIvkCqkbnImxPMCwMRcQZaD1CkFAI69AdtBB\r\nIfuwavtgKu3kAo7ErKEyg5QQDRWoIRcUHiQoayVkAltqRUhguPikegVAAxowvdzEAwGItZI7uSbA\r\nVWgBVoiBNvd4GDPkolS5lgS5SWuzamgIQy1JOpD6hXJ6gEXxhVm4h+1rbGEYlQdolQGcp0YKAVHg\r\nv6LpAPz7iwwsiK6oBBCpAWiBlUPCvxI3iHFJCsrbJGrhF9DLtDkpnVaVfyGgAjQAmEi4RGYqe+1Y\r\nr+E7uclzYLhC/xgUzJ1RZxC8WH1vAUNo8b7nqYwtqZWrWX3ye5/7+BQT95mEEZvYR5BuYQM7GpUb\r\nw2xdaKvPaMNr2Yp2q8VtoTZKEAWpGcETvJYW2cSzciTYgbfIKwmWihQeHHXlOlUNGq9+hH8ARgFF\r\nQHpIyEMC0AEunHRoGsOT+4SR0hna6IobgZT4yItC1HccAcSXC1bR6KKrSTiIOlts0RKNOh3H6IqI\r\ngQNTvKN2extX3EI1fMZVKDvoYpFMKSmiXMNkHCkEecZPl8EFe7zdagnRgBqvABQXxKlzPFVFML0D\r\nYKYqoMeA6XYQegAlAMhI98Iw3KGTa7KGLBxSwxs8qrmOPFsQMP8wkpyHYNVVYPuBrhgAw9m+8quA\r\ns5Ikwzgdo8yFvXHKRHyLW5ynF5GFmLQxY2wrEqyLDLk2l9nEJDgseXuNwlA7ltIQIBy4wPOX6jgA\r\nF9Bzbmd6YHQAAuC9gYx3g0zN1ay1howxPrKpKqSBOixfO0o64fzNe/hCFpMs43yjuekKxLiMNfzK\r\niCHFBexCFnPFUaA2o6XKtyJzUflCfhGQsuzPEBmkmDTQWqlJBt3GkXCSgsEVsWJ2ZcI7poUAI+x/\r\nP1d/QwBFGX0CZB3NszfNrvrRWju+A8ZVnfSGOUxQaPwotuJ3rUjJDuCLa3mt/Sifj0LJQeqoParT\r\nYlgzV1yF3Cj/0K34d7+I2A/MGKtolDlSh2t5y5AN1V9HoJcQDQ9oE0pwpZMkA3ZMiTeGTc9M1htt\r\n1pCbd62q1lpbuQaor9z8yv/IFdx5EWBLjNFIxfJZ24s6Ky0iv7vZz4Mdz4lWLF0IwAiiMEJ9Fswd\r\nv5Z8mfsbhh2pATTxdEyslRAQBG54yZ9yeaNgLR0UF6BtQhQw2twkACWg0amt2g3X0Wnt2iQaFZWC\r\nKTjavvSiNgeo0HnoorSiwPhbp6vAxNWNGEBM2FF53FFQhnV6wAmcp2yRmkkMwAfYGVoptgKtIHnq\r\n3bpMCuCAtCJxASOUX4OrsRrt3iguzazt0WqNsqyCMZiBLoTR/yHbyGo3OOOaOhtYw05HvSFi8tII\r\nwRhBbj4z8Rb0RjoJHDscgi6cOSAbswrOwRW34U53mB2rkCRFMU9ULgvGa3mabQmtAOIigQQODTw0\r\nYMxD4L0pbtZlvdqt7doy8OIjsb4O+6/VVrM4knh3Dnm53LZPsudrO247G9Wm8GQIIxJBUOaVZAFn\r\nnpiduObR/I6rSsgfjZ1y3rJL2eckt3IhRz9rBbQ/+22fPm5qW7M2c7MH++ndxumnPkgHrQlGMd42\r\nkQCBq0++0+j+xwI9CenBdgJFAAEAGLlA6dqFrK+ybQ4ucBHfOeiRZ+dwi+orByWNt7BXMuigfm6A\r\nTrPa3ptUaP8KE0smIj5J+cToxnzM7e3eIeCgH3AEPSDsxD65DWE2880dv3eaozkwRj4wRbEbGnKj\r\nM+4KEuwN10Eu9r4hNqBkSYFIhJGppuK6Ru7jHDLjo/lmiPMWNugUCo8VWO4LOS7xCu9qrouwX87L\r\n95Ath4EEC30OJUBV52gBTlDuE3DuZp24D3oATEjpH00BKQsBFOADexwSFpGK+Uu/fWjdmuynXziN\r\nvkiVgHe+sVhg/3u/6PnJlFzTWSjAVJka+F2nMOiV97sRfa2RVp+FeaPfST/2tDJBu0zyCOMCW0Oc\r\ntG6TmCgD5a6qvJ64MzmdK87irv3zmeQEMRADXKuvsVOr9In/mhIXlQ5ilgUOeNG4h7ETBGDRo0j9\r\nd0Gg9cujldQC1ADayWC777OBov1KxNb6dD6C+Deq12LiFGEv0DAqAGyiSWrTur/4+VDw0hP0Xod+\r\nAW+fsuFemCWsApqYmHfvAzOpmO7+7lo73xoA+II/7zFuopSfhorPEYx/jJykG7ntEv4sJk9vOO98\r\n4cTIFlRPYGCbOxFsPpECBVFwJKXbdFppX5btLroIrVXIn5XMnQt51HwICBAaFzYAAFGIiYgACQJS\r\nj5CRkpOUkgEBEJmajQmanpkqFhYdpKURHScmSQOsrQMqJxGys7QRIUZNHwMsAicKCg/BDwXExcbE\r\nTimfEMoU/z5FyssXUAICNosXGgmGAIKNGhoOhi6DOIIXSt4C4AKHUYbZ0SkaEIaLUdnbAALnLvPV\r\ngtoxGjSQngtx77K5MGRDkLIUFwCQi5iQoCF+4C4IdJhpXoCG2rgJmOcggAYlArLVUwIOghKEihYl\r\nqESzJqULy3J+gjiqlE9bFHS5YiXiRKxasowm08Vigi9gwo5JJfYj2s4U83QmCCLAAQ5sId+pA1dI\r\nYjlB4Aiu09BuEYBsGrC2tPcOX8h9aMEpwcvWUAKLnTKWVchQkKaIEw+9tXgRbop2aOVq+OhNpKCS\r\naem1DVzOnSJGjmyKpolTp2lQPUmdktXhQY2hrV48MJrU6P8JIyY0CJ0QAWrUqceSnbbqKUWARl2/\r\nVtS3lto/eCfNafvLFqM+bvkMBYlL91B2vlD+QugqKIhfxgUFEgYAUuA4i9bdWSZYeSAEHNkinq9n\r\nEsKFBAhxQ11bMYE22oGTJIDJaTnx9FMHs5wgwyqwDXCEMwUM4wMFJqTwwSpDOOVbMMBJxQyDOv1X\r\nzTVgaRTYfVGYFNFIGkAhHRSL7RXPNjhY8912KXRnF3MBdRNSfnxtQ80+zy22EHvqVGOWi4a1tU8Q\r\nOCwWUX3iAQAFONtgtJBJ17HH1T7seJYIIwi2GcmCKO6Uwyir1XKCCxTClsQTP/yQCREfEtWLML+V\r\nWAwN9MT/ucxWK64JV1rjnZdZRA5UJl6PeWn23ZdByhfjXTS2oxKTmUFgQ6WTZqllYZmx9WhLVi4G\r\nKWR3XSorpOKQKR9GaekX0zuNuOlmaYpqokwPIdRJiy9KVNhKEtDmycIBPIxIoqHFGEFcscYx6tWa\r\nCVwgrgv6vOMlTscVYkO47aR0gTjrlvZuuObtI66Q9Damkb0uFBJEaS8B4ICCEKSrWLj64KCEuAw7\r\nAAXD/9Ulk7wCLSxQSgmoSvBxGl9g3iICKIEJx2qCK6ybncSJlSeDhAAhUkklwIKzQ01bwgmEFopt\r\nAVUV+4mK1hTIDTefsUf00Kraw801Q8tHV9FOK300AF9J/23000S71fTWJRtSddRar3k12GKP/atM\r\noZ18IJym/ZeOVSkkkCxSvyhwQg8JDDFzzUMcMMHN1g6zczHQdDSeSnEmQE3QZzdubsmOPx755JNn\r\nXTblmP9qOeabO955gcGqfaALp7nwTjU4kOOJD3PP8ktvv/iiAhQH1J7ABCqUUHfO1w5egHCZaMMP\r\nR6ZBhNy3ZZO9tdZDP948185313T0XFe/fObYZ6+95zOJjiDbnmyTsn9RsGS4Cq0rAHvd7CsQA/u8\r\n6zw4BVYNwg9Wl2z7ieKNrilAI2URABSCUJYgKKERdelReJZEtf81QhxmgoIEG3GlBADwIoqTYBCi\r\n8L9qSP/wg4sLwgeRA8LwEGh7KEwh5QzkvdEQyxMREU/wIhIAeUAAfe3LITACxzvfGYN+8giArEyl\r\nupX9zFvKARk46tUlG7zqXQVJy0KoA464kSotQnxVpDKVAhz85T+toseXMrOwMFJEhWhM42egIBoS\r\nSIEBBNABAdSWkwQ0xHDlGYmxIMC69emQh4TKkA+JAcQ9diUbQQjCQ5ahDP4xTmz1MYmO6GHBcwwk\r\nboERISX/8pi1HPCAAJBRuOhhpP9YcCAuSscgLMhJC6LEgHBBB47USMvsAWAHuMxleCIxgjcSgAAY\r\nCCYG5AhHDMxxWMsQQBA2AcOdQEAGsdBh/Ho4SGQYcWX/cQnTSLDyGPMdBomaQ1KotqOBeq1FmaRc\r\njOJIec61JC2UIVmHqepzHv2ocz9bcwBWBFLLfjZuBzMI6Axw4IBqCGAFBkCBQnWgg2DKkQAMMEAF\r\nFrCBBhiiARUwprDYFrfhjScBNjyR4ZxglBxOU34+LJzhOiGXGaGlESLNhCNtkET/xWVgpFRdWT41\r\nwLgEAaRsGSMz4pkVmcJTI0gyDCZiaBEc6KogC8IRmh4DOX+mEZcB5SAJSLCCGxiAAQ0VpjEjagAY\r\ngGAB1uMGDDDQyzaNLxMXwA84FnKBaKxyJwGAxS9OilLfUSCmmrlf8CIlLpDAEJyaA8nDjjQIc8l1\r\nK60S/19LzlHQeXBzLybBlZHoQZAveu2p1OEmc+JyQqtm7pYAncEOooADG9hAACTwKgPgGFaHEqCs\r\nIMgARdNavQxggAPCemFHISA+TZBymZ+ghxPsBkhBVrMARoAG3MajSLkE7y6Iy8RxFkfTs0EHJ30R\r\nj0DaAyQFdZYeUUDVN9hyqiyJch7mqYwDCuFZqoGWHgT1ykVIW1XTKiK1Ae1RNbj61TiK9aERnWgD\r\nLMrbBgNgATpAwcmWUS/VnQggDaKHDJibM+dW0wmG2aMNgEQPYs3jP9uyYDWQ513x0KMQa3nSpb5U\r\nj3N+SYggWa8ACFoIXSU1JKdC5Wdrhd5TMW0dVPWvZ/9QG2DYdtWrBhYrRMtaARBswMFY3toG4niy\r\ndOwvuzVqiP6M1YwC7LV3PjQCDYg3VGUEAVUQGJgR0XJErjzSu7zai6SuK9l6RMEb1BlsNSZrHBvo\r\nSjyoSpSp6utUIicKIqfjLy2xGtAdtPZUB73BbKMsTDlKNAO6ZXCWR801sEohbQh6YUyZsQ3EKSNk\r\n28pKEZzRYcEBxwhOMAGbszJK9rrIrjrZ7oprqjmJZCJWLDXOxfzjF+IiNxMuaBfbCEG6MOEkJZ8Y\r\nGASehB/iXuRnp7tEabMH4Bm8FraxNQCnbTtbs16Z1PB2sAEwILriRQTMUPizPIgTDRPQgAJG8MFU\r\njCD/AyfQwE9zDl4KDnK/V18SKzjhN3GPR+zGNU9rXZla1KDHcavx9nLU23jlbonaXL42trJdt6dh\r\nAIMKZODd8SY1WrO81rZOuEFcUTRj8digOfepT2ROeEcGIY54CJpG2jCsJw5o5+6q8HP+jXoiKK1a\r\nghoUoXBU+ZRzS1FRxzzmXx11RoUgurd6QgAWJoirx+NRnXDz7UI37qv/5wBvDtYGUIgbnA/b9IpL\r\n/e8ppPrJuapprc+2rC//+qgbkAEHL6Dx3AA1ADIARxA0LQM6AK7oVM2MnINDiDJ8dWsBW0WfHUam\r\ngtjLqicThdeG+JuLYzHgZx+5kucSEa/tqgG+ytAD/0e05bn1uuJlrgPIp7UBll/A7sEaURAYQAcz\r\nN8QGItxCVb/6YUqyYTU0cqJGhuv1Q2+QILJYxX3K0LjcVzRcGWWN4bv//dYTqGrPDduU1zaYv2SA\r\nWbsO//frwPIAIGrKd1tXBmEbsAAYAAOThwEVYAgE0IBDQwAShmrB5UxQUFAgtUiDxn3BsxfU4AB1\r\nBUMmNh5QYBiPcX4E0R/FIXSmxH4G9YIwGIMyOIM0WIM2eIMxyFVPRlsHNmUtZ3z9F4QOaFZwpIBb\r\nVgEbEHYNUHxLCHkPaAgR1TSm1kJSwHkhtU/w1AjykHNW1H1xZiQnIg68ooXFMWgoEgAMc0CstIZs\r\n2P+GbviGcBiHcjiHFiQEHKBQKHB/+JdgXCd8Qqh4GAWBQwNWMJABFfB/MMAAD5ZRaPWEBACAUQgA\r\niSiFOmBzLTQc2hAF1NAIeacJ/zMXKmE/nxg8jmRJgHVv6icNVLiKrPgIK+BL98dQPvhyfviHARhv\r\nC+ByhgADXwVWBtA06sYNX/V8hwcDFhWF+mcIYQcAFUAAwEhvrGh2nxBfk2Ee4qEMsrIQJzId0baN\r\n3IdhIKh+2lApY0ZckIADrZiOopEDUlB4YjVMv7d/tfiHfkgACnh8FVABDNaM6vaL89Z4IAB9QzOJ\r\nhlABEeWMwKiIy1gBvwgAGwCEAAAC9EaB3iN+/8L/WSmAU5akTFwRgsoAgvUQivXADNYwceJIXKum\r\nXZBAkerYiiswAndoeGalWxVli9XDcg5JAI23YIZwVkNjiJDHkLyniAtAAGgFYRkAAgRgUQ3wiD+p\r\nA+/2VVtmAEkJA2g1iYbIDTC3NQhIdunIecxgQ3ahDi5hFjOEEaNofoEFUzuRinB1ai2Zjm7Eg8KU\r\nfwmWAfNok9WzkMdoAFfGeBd1eLfFjBiQfMXXjLyobrlFAO9mj0ODgAxgiDrQgAswWwwgiKTWAL/V\r\nktLIMuQXENTBlpVhQuagDMIzD2EBfjoRl6s4AgXWgwlWZdGnl/HWjIbweAGpAwywAEqpjIr4YBig\r\n/1tQOYQSKY/AOXOzNTQboH9fBYDw90usCZaa8DbYSB0HcXYhQy6LcQ6lOQ+t5paeIAUz4QgOwJon\r\nA0wPhXgLsAB5CX8NwJ4xp3y7t5QAEFHLyZiTpwMW9YRDOH3IyQBNCYB/GZy72JDc0J6Kx2UsSYWd\r\nOViG4UTzoAR31Fh5YYbK4ET0EHefEABSwEbmqTYkYEz6SJvWkwH4yVvsmYj3aKJEiAFoFYy9+WAC\r\nyZ/1aYz/55sRCVGz1XgTRaJcM28f+gjFYj9DRYam+BCjJHcpApdBep4C+Ye7xVvTB5HMuHuW15S/\r\npwMKmJwOSaAw4IxFiVZLaHnqZlEQZnn86XzSl/+UCIplbXqTGNA95hkEUjAcOrEwNkQ6IcUgTUqF\r\nGGCgQhiJ1mNRN0p5vsmQNzqYkuiMyeiADdibARqAjtkAcKRuKzqbQvilmJplEvkI5fmhdAo+4tcR\r\n2xIXisKh4/kINtCnwpKANrmMAQifi5iIy6mATol57/allNqQJrqcBjqYYVqfBIiThpCUmxqfzrk1\r\nMNBQyTpqvqV5rPp9pmd6F/AIC8qqCPKn9MiMCAkCltl4mBeZ9bl7DXmI3ICAG0CQYRqQSLisv6mT\r\nDkmsb8o1yPeYDOCcX2o9B9io8AZhEoatkSCd03oYSmCtANtCv8Q16WoAzaqwXqduWfeeRsmtknr/\r\nj8uqmz2pn8UKlbZpCJO5qLeVlLYIYdHnW8XXkwgJagQpjIA6aluGAtfapDNxAaIaJ2gongebjnC0\r\nNUqZYNYDAs73S5CnqQ3wfAuQiIY4b1fWqAHZs5JqAA0wlfm5j5DHk3q5Zc45lQJZlFeWUVTZNCsL\r\nb1MIsABQEwVLsxtarS6As5XwqTmrNs/XNE2JmbtYqcc4mZWJkB/rsSCwrLuni+O6sVELUQ8GR/xK\r\npT46hIMYtPsJri7KswgZb8/3tpPwqTHLpAbrqZS7ik/KDYrKDbO1AfcJAMEoo2i1twDwgEopfMu4\r\nZUfplxc1r2kFuA3GYMq3o4ELurYKoJdpuluD/3myC7Zstbk04bahcbnE6z0jwIBNE5D2eJTwCpwb\r\n0LEAcKNcugHaCkcVkIhopabSp5f8ujVFG1G9S6kKaJuwGrhL+FUK2IS3qZWdS2pjl7z0m7yu2jTp\r\nGkcT9aRbBmo64LkKCGH6d3gFWoiJOzRWy4ztBoWRuVYKuLq5+H/UK4m/WZQ60JBPGJDGZwDH6mCd\r\nKgVuW78izKoXbD2ayp+Yt57DmbvyuZPuOZtb+bsSpcB1+3iPGJAMFoWHOIwwsAGryw0xuoC/2XIB\r\nOKLuh3nQOsJK3KSEWz1Kma6TSXmKWLQzt57vl4stx2BU6a3cYJC3SMEczK35SLGpCwKMN1vUi/+V\r\n8Uuy53qik4e48JdRSbzEdByXKPCbWlllX1quPvuqUwa1xVqAV7acxqSAX3W0S8mlOUqof5yUK1y6\r\nswW0Xxu935u4JqoD7FjHmtySHBC/y4nGByysMSyyU9sAy5p8+Il5E/VLPdy/qXuPgamTzYif46t/\r\nwet+75mUBhlMm9zLLUmg7hlvARnDpFvB8DqlEambZLWTJqqIN3qIE4WT41uIExvKxSqUdBlMKJCq\r\nvtzN3nMDhal43iuo9KpbWwOrvGl5P5y6EHijZ8o1+eqYEXl4ELnOthi1vJmIvwRMnaZQwCWn3hzQ\r\n3sO8X9eMFpVbP+mcUizPoPubSQhWKtyY99j/qLO1nlX2yeLaozfJi6iLy7rMi790YNTHAf8s0Cbd\r\nivfLs7tnjCU6sQ/JjCCNsU3ZeEiJUeTqxk2omZCHsZKopRUFsWEMam/Ki1apeD7Mi5sWiwolBWTX\r\nPRR4vKp60lKNIDwNjPb4pdVcrFYWgCcbkIQZwIVZlPmobvr4g9MHqE9IiBB7UYccuz6Kz7xZYJzG\r\nUP7MtlJAp1Od1+r4S8Knqw09ecsnR4RqeZi3gAz2vH+6f7+rm2N9ow+tj8RMmw+Zj/rMzw410nYt\r\nCagWwo9Qtnr92QgCVjHc0b71eNq6hLYawNDnn8oItTdqPUf7ex2sl1Hbt3JdW3TNAU29kpGQ/zbI\r\nC9rA3SYYsJVFCYRzi8zFTLq/GKCRSsHcSr4NG8q5nLTZPEx4yNQ08dvBvd0DDYTT16yOCIBeTJDJ\r\nGIlcvIi0m7g+vL0FpodLvduanbmU4NncXd9UKAAEDcCPebKNWtjAq9zM6Jy3HHMLdoAMmXW951Dv\r\nbdeoFrP0bd8QjtIte4gbjJBcWplR25AD/nUPCbQGqXIRhgLA5ZWYW+IRfuIHW9XCeMH6PHOkbM0P\r\nZtvtfWAijt0Azds18eAovuOsubMyLFEbTuAHmIu33WkhbuM2od08vuTm6eMwvqYgsL3VTddSAFyW\r\n2ODyvapMvuXEG7+0icVyLdIiDt8lruRcfpDmObu80W3U62nbv6TUNW6J8Y3mdN7NKY2s7D3lEqbb\r\nmZ2516rjdR7o9FvCWcZ4B67nEgbfT20TgC7ojl6/Qiu+j1eViF7jAG3mj57pdn5bUZ6Yej7i2S0J\r\nnN3oml7qm6yH1i1hM3Hj8o3ppv7qvtxLJV0JUA3rts7ktX7ruo7iWr7rvo7mpP7rwj7sxE4TgQAA\r\nOw=='' alt="Trusted Shops Logo">\r\n</div>\r\n\r\n<div class="info">\r\n	<h2>Gütesiegel und Käuferschutz</h2>\r\n	<p>\r\n		Trusted Shops ist das bekannte Internet-Gütesiegel für Online-Shops mit Käuferschutz für IhreKunden. Bei einer Zertifizierung wird Ihr Shop umfassenden Tests unterzogen. Diese Prüfung mit mehr als 100 Einzelkriterien orientiert sich an den Forderungen von Verbraucherschützern sowie dem nationalen und europäischen Recht.\r\n	</p>\r\n\r\n\r\n	<h2>Mehr Umsatz durch mehr Vertrauen!</h2>\r\n	<p>\r\n		Das Trusted Shops Gütesiegel ist optimal, um das Vertrauen Ihrer Online-Kunden zu steigern. Vertrauen steigert die Bereitschaft Ihrer Kunden, bei Ihnen einzukaufen.\r\n	</p>\r\n\r\n	<h2>Weniger Kaufabbrüche</h2>\r\n	<p>\r\n		Sie bieten Ihren Online-Kunden ein starkes Argument: Den Trusted Shops Käuferschutz. Durch diese zusätzliche Sicherheit werden weniger Einkäufe abgebrochen.\r\n	</p>\r\n\r\n	<h2>Ertragreiche und nachhaltige Kundenbeziehung</h2>\r\n	<p>\r\n		Das Trusted Shops Gütesiegel mit Käuferschutz ist für viele Online-Shopper ein nachhaltiges Qualitätsmerkmal für sicheres Einkaufen im Web. Aus Einmalkäufern werden Stammkunden.\r\n	</p>\r\n	<div class="register">\r\n		<a class="button" href="http://www.trustedshops.de/shopbetreiber/index.html?et_cid=14&et_lid=29818" target="_blank">\r\n			Informieren und anmelden!\r\n		</a>\r\n	</div>\r\n</div>', 2, 0, NULL),
(261, 77, 'Document', 'PDF-Belegerstellung', NULL, 90, 0, NULL),
(262, 92, 'StoreApi', 'StoreApi', NULL, 0, 0, 45);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_form_translations`
--

DROP TABLE IF EXISTS `s_core_config_form_translations`;
CREATE TABLE IF NOT EXISTS `s_core_config_form_translations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `form_id` int(11) unsigned NOT NULL,
  `locale_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=58 ;

--
-- Daten für Tabelle `s_core_config_form_translations`
--

INSERT INTO `s_core_config_form_translations` (`id`, `form_id`, `locale_id`, `label`, `description`) VALUES
(1, 77, 2, 'Shop settings', NULL),
(2, 78, 2, 'System', NULL),
(3, 79, 2, 'Article', NULL),
(4, 80, 2, 'Storefront', NULL),
(5, 82, 2, 'Interfaces', NULL),
(6, 83, 2, 'Payment methods', NULL),
(7, 84, 2, 'Article numbers', NULL),
(8, 86, 2, 'Other VAT-rates', NULL),
(9, 87, 2, 'Price groups', NULL),
(10, 88, 2, 'Price units', NULL),
(11, 89, 2, 'Article open text fields', NULL),
(12, 90, 2, 'Article evaluations', NULL),
(13, 92, 2, 'Further settings', NULL),
(14, 102, 2, 'Article process', NULL),
(15, 118, 2, 'Shops', NULL),
(16, 119, 2, 'Master data', NULL),
(17, 120, 2, 'Currencies', NULL),
(18, 121, 2, 'Localizations', NULL),
(19, 122, 2, 'Templates', NULL),
(20, 123, 2, 'Taxes', NULL),
(21, 124, 2, 'Mailers', NULL),
(22, 125, 2, 'Number ranges', NULL),
(23, 126, 2, 'Customer groups', NULL),
(24, 127, 2, 'Caching', NULL),
(25, 128, 2, 'Service', NULL),
(26, 133, 2, 'Advanced menu', NULL),
(27, 134, 2, 'Article comparison', NULL),
(28, 135, 2, 'Tag cloud', NULL),
(29, 144, 2, 'Categories/lists', NULL),
(30, 145, 2, 'Top seller/novelties', NULL),
(31, 146, 2, 'Cross Selling/Article details', NULL),
(32, 147, 2, 'Shopping cart/aticle details', NULL),
(33, 157, 2, 'Login/registration', NULL),
(34, 173, 2, 'Statstics', NULL),
(35, 174, 2, 'Google Analytics', NULL),
(36, 175, 2, 'HttpCache', NULL),
(37, 176, 2, 'Log', NULL),
(38, 177, 2, 'Debug', NULL),
(39, 180, 2, 'Countries', NULL),
(40, 189, 2, 'InputFilter', NULL),
(41, 190, 2, 'Search', NULL),
(42, 191, 2, 'Discounts/surcharges', NULL),
(43, 192, 2, 'e-mail settings', NULL),
(44, 247, 2, 'Shipping costs-module', NULL),
(45, 248, 2, 'Check VAT ID number', NULL),
(46, 249, 2, 'SEO/Router settings', NULL),
(47, 250, 2, 'Widgets', NULL),
(48, 251, 2, 'Country-Areas', NULL),
(49, 252, 2, 'Plugins', NULL),
(50, 253, 2, 'ESD', NULL),
(51, 255, 2, 'Article recommendations', NULL),
(52, 256, 2, 'Checkout', NULL),
(53, 257, 2, 'Shop pages-groups', NULL),
(54, 258, 2, 'Cronjobs', NULL),
(55, 259, 2, 'Backend', NULL),
(56, 261, 2, 'PDF document creation', NULL),
(57, 262, 2, 'StoreApi', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_mails`
--

DROP TABLE IF EXISTS `s_core_config_mails`;
CREATE TABLE IF NOT EXISTS `s_core_config_mails` (
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
  `context` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `stateId` (`stateId`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=51 ;

--
-- Daten für Tabelle `s_core_config_mails`
--

INSERT INTO `s_core_config_mails` (`id`, `stateId`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `mailtype`, `context`) VALUES
(1, NULL, 'sREGISTERCONFIRMATION', '{config name=mail}', '{config name=shopName}', 'Ihre Anmeldung bei {config name=shopName}', 'Hallo {salutation} {firstname} {lastname},\n \nvielen Dank für Ihre Anmeldung in unserem Shop.\n \nSie erhalten Zugriff über Ihre eMail-Adresse {sMAIL}\nund dem von Ihnen gewählten Kennwort.\n \nSie können sich Ihr Kennwort jederzeit per eMail erneut zuschicken lassen.\n \nMit freundlichen Grüßen,\n \nIhr Team von {config name=shopName}', '<div style="font-family:arial; font-size:12px;">\n<img src="#" alt="Logo" />\n<p>\nHallo {salutation} {firstname} {lastname},<br/><br/>\n \nvielen Dank für Ihre Anmeldung in unserem Shop.<br/><br/>\n \nSie erhalten Zugriff über Ihre eMail-Adresse <strong>{sMAIL}</strong><br/>\nund dem von Ihnen gewählten Kennwort.<br/><br/>\n \nSie können sich Ihr Kennwort jederzeit per eMail erneut zuschicken lassen.<br/><br/>\n \nMit freundlichen Grüßen,<br/><br/>\n \nIhr Team von {config name=shopName}\n</p>\n</div>', 1, '', 2, NULL),
(2, NULL, 'sORDER', '{config name=mail}', '{config name=shopName}', 'Ihre Bestellung im {config name=shopName}', 'Hallo {$billingaddress.firstname} {$billingaddress.lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\nInformationen zu Ihrer Bestellung:\r\n \r\nPos. Art.Nr.              Menge         Preis        Summe\r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\r\n{$details.articlename|wordwrap:49|indent:5}\r\n{/foreach}\r\n \r\nVersandkosten: {$sShippingCosts}\r\nGesamtkosten Netto: {$sAmountNet}\r\n{if !$sNet}\r\nGesamtkosten Brutto: {$sAmount}\r\n{/if}\r\n \r\nGewählte Zahlungsart: {$additional.payment.description}\r\n{$additional.payment.additionaldescription}\r\n{if $additional.payment.name == "debit"}\r\nIhre Bankverbindung:\r\nKontonr: {$sPaymentTable.account}\r\nBLZ:{$sPaymentTable.bankcode}\r\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\r\n{/if}\r\n{if $additional.payment.name == "prepayment"}\r\n \r\nUnsere Bankverbindung:\r\n{config name=bankAccount}\r\n{/if}\r\n \r\n{if $sComment}\r\nIhr Kommentar:\r\n{$sComment}\r\n{/if}\r\n \r\nRechnungsadresse:\r\n{$billingaddress.company}\r\n{$billingaddress.firstname} {$billingaddress.lastname}\r\n{$billingaddress.street} {$billingaddress.streetnumber}\r\n{$billingaddress.zipcode} {$billingaddress.city}\r\n{$billingaddress.phone}\r\n{$additional.country.countryname}\r\n \r\nLieferadresse:\r\n{$shippingaddress.company}\r\n{$shippingaddress.firstname} {$shippingaddress.lastname}\r\n{$shippingaddress.street} {$shippingaddress.streetnumber}\r\n{$shippingaddress.zipcode} {$shippingaddress.city}\r\n{$additional.country.countryname}\r\n \r\n{if $billingaddress.ustid}\r\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\r\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\r\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\r\n{/if}\r\n \r\n \r\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \r\n\r\nWir wünschen Ihnen noch einen schönen Tag.\r\n \r\n{config name=address}\r\n\r\n ', '<div style="font-family:arial; font-size:12px;">\r\n \r\n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\r\n \r\nvielen Dank fuer Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\n<br/>\r\n<br/>\r\n<strong>Informationen zu Ihrer Bestellung:</strong></p>\r\n  <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">\r\n    <tr>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Summe</strong></td>\r\n    </tr>\r\n \r\n    {foreach item=details key=position from=$sOrderDetails}\r\n    <tr>\r\n      <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.1}<img src="{$details.image.src.1}" alt="{$details.articlename}" />{else} {/if}</td>\r\n      <td>{$position+1|fill:4} </td>\r\n      <td>{$details.ordernumber|fill:20}</td>\r\n      <td>{$details.quantity|fill:6}</td>\r\n      <td>{$details.price|padding:8}{$sCurrency}</td>\r\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\r\n    </tr>\r\n    <tr>\r\n      <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>\r\n    </tr>\r\n    {/foreach}\r\n \r\n  </table>\r\n \r\n<p>\r\n  <br/>\r\n  <br/>\r\n    Versandkosten: {$sShippingCosts}<br/>\r\n    Gesamtkosten Netto: {$sAmountNet}<br/>\r\n    {if !$sNet}\r\n    Gesamtkosten Brutto: {$sAmount}<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>\r\n    {$additional.payment.additionaldescription}\r\n    {if $additional.payment.name == "debit"}\r\n    Ihre Bankverbindung:<br/>\r\n    Kontonr: {$sPaymentTable.account}<br/>\r\n    BLZ:{$sPaymentTable.bankcode}<br/>\r\n    Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    {if $additional.payment.name == "prepayment"}\r\n    Unsere Bankverbindung:<br/>\r\n    {config name=bankAccount}\r\n    {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\r\n</p>\r\n<p>\r\n  {if $sComment}\r\n    <strong>Ihr Kommentar:</strong><br/>\r\n    {$sComment}<br/>\r\n  {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Rechnungsadresse:</strong><br/>\r\n    {$billingaddress.company}<br/>\r\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\r\n    {$billingaddress.street} {$billingaddress.streetnumber}<br/>\r\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\r\n    {$billingaddress.phone}<br/>\r\n    {$additional.country.countryname}<br/>\r\n  <br/>\r\n  <br/>\r\n    <strong>Lieferadresse:</strong><br/>\r\n    {$shippingaddress.company}<br/>\r\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\r\n    {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\r\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\r\n    {$additional.countryShipping.countryname}<br/>\r\n  <br/>\r\n    {if $billingaddress.ustid}\r\n    Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\r\n    Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>\r\n    bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt: <br/>{config name=address}\r\n    <br/>\r\n    Mit freundlichen Grüßen,<br/>\r\n    Ihr Team von {config name=shopName}<br/>\r\n</p>\r\n</div>', 1, '', 2, NULL),
(3, NULL, 'sTELLAFRIEND', '{config name=mail}', '{config name=shopName}', '{sName} empfiehlt Ihnen {sArticle}', 'Hallo,\r\n\r\n{sName} hat für Sie bei {sShop} ein interessantes Produkt gefunden, dass Sie sich anschauen sollten:\r\n\r\n{sArticle}\r\n{sLink}\r\n\r\n{sComment}\r\n\r\nBis zum naechsten Mal und mit freundlichen Gruessen,\r\n\r\nIhre Kontaktdaten', '', 0, '', 2, NULL),
(4, NULL, 'sPASSWORD', '{config name=mail}', '{config name=shopName}', 'Passwort vergessen - Ihre Zugangsdaten zu {sShop}', 'Hallo,\n\nIhre Zugangsdaten zu {sShopURL} lauten wie folgt:\nBenutzer: {sMail}\nPasswort: {sPassword}\n\nBis zum naechsten Mal und mit freundlichen Gruessen,\n\n{config name=address}', '', 0, '', 2, NULL),
(5, NULL, 'sNOSERIALS', '{config name=mail}', '{config name=shopName}', 'Achtung - keine freien Seriennummern für {sArticleName}', 'Hallo,\r\n\r\nes sind keine weiteren freien Seriennummern für den Artikel {sArticleName} verfügbar. Bitte stellen Sie umgehend neue Seriennummern ein oder deaktivieren Sie den Artikel. \r\n\r\n{config name=shopName}', '', 0, '', 2, NULL),
(7, NULL, 'sVOUCHER', '{config name=mail}', '{config name=shopName}', 'Ihr Gutschein', 'Hallo {customer},\n\n{user} ist Ihrer Empfehlung gefolgt und hat so eben im Demoshop bestellt.\nWir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.\n			\nIhr Gutschein-Code lautet: XXX\n			\nViele Grüße,\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(12, NULL, 'sCUSTOMERGROUPHACCEPTED', '{config name=mail}', '{config name=shopName}', 'Ihr Händleraccount wurde freigeschaltet', 'Hallo,\n\nIhr Händleraccount auf {config name=shopName} wurde freigeschaltet\n\nAb sofort kaufen Sie zum Netto-EK bei uns ein.\n\nMit freundlichen Grüßen,\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(13, NULL, 'sCUSTOMERGROUPHREJECTED', '{config name=mail}', '{config name=shopName}', 'Ihr Händleraccount wurde abgelehnt', 'Sehr geehrter Kunde,\n\nvielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.\n\nBei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.\n\nMit freundlichen Grüßen\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(14, 1, 'sORDERSTATEMAIL1', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.', '', 0, '', 3, NULL),
(15, 2, 'sORDERSTATEMAIL2', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.', '', 0, '', 3, NULL),
(19, NULL, 'sCANCELEDQUESTION', '{config name=mail}', '{config name=shopName}', 'Ihre abgebrochene Bestellung - Jetzt Feedback geben und Gutschein kassieren', 'Lieber Kunde,\r\n \r\nsie haben vor kurzem Ihre Bestellung auf Demoshop.de nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist.\r\n \r\nBitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.\r\n \r\nVielen Dank für Ihre Unterstützung.', '', 0, '', 2, NULL),
(20, NULL, 'sCANCELEDVOUCHER', '{config name=mail}', '{config name=shopName}', 'Ihre abgebrochene Bestellung - Gutschein-Code anbei', 'Lieber Kunde,\r\n \r\nsie haben vor kurzem Ihre Bestellung auf Demoshop.de nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen 5,00 € Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung auf demoshop.de erleichtern.\r\n \r\nIhr Gutschein ist 2 Monate gültig und kann mit dem Code "{$sVouchercode}" eingelöst werden.\r\n\r\nWir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.\r\n', '', 0, '', 2, NULL),
(21, 9, 'sORDERSTATEMAIL9', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(22, 10, 'sORDERSTATEMAIL10', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(23, 11, 'sORDERSTATEMAIL11', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. \n\nDer neue Status lautet nun {$sOrder.status_description}.', '', 0, '', 3, NULL),
(24, 13, 'sORDERSTATEMAIL13', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(25, 16, 'sORDERSTATEMAIL16', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(26, 15, 'sORDERSTATEMAIL15', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(27, 14, 'sORDERSTATEMAIL14', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(28, 12, 'sORDERSTATEMAIL12', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(29, 5, 'sORDERSTATEMAIL5', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} \n{$sUser.billing_firstname} {$sUser.billing_lastname},\n \nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d.%m.%Y"} \nhat sich geändert. Der neun Staus lautet nun {$sOrder.status_description}.\n \nMit freundlichen Grüßen,\nIhr Team von {config name=shopName}', '', 0, '', 3, NULL),
(30, 8, 'sORDERSTATEMAIL8', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n \nMit freundlichen Grüßen,\nIhr Team von {config name=shopName}', '', 0, '', 3, NULL),
(31, 3, 'sORDERSTATEMAIL3', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"} Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d.%m.%Y"} \nhat sich geändert. Der neue Staus lautet nun "{$sOrder.status_description}".\n \n \nInformationen zu Ihrer Bestellung:\n================================== \n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:3} {$details.articleordernumber|fill:10:" ":"..."} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:"%.2f"} {$sConfig.sCURRENCY}\n{/foreach}\n \nVersandkosten: {$sOrder.invoice_shipping} {$sConfig.sCURRENCY}\nNetto-Gesamt: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sConfig.sCURRENCY}\nGesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:"%.2f"} {$sConfig.sCURRENCY}\n \nMit freundlichen Grüßen,\nIhr Team von {config name=shopName}\n\n', '', 0, '', 3, NULL),
(32, 17, 'sORDERSTATEMAIL17', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(33, 4, 'sORDERSTATEMAIL4', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n \nMit freundlichen Grüßen,\nIhr Team von {config name=shopName}', '', 0, '', 3, NULL),
(34, 6, 'sORDERSTATEMAIL6', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\n \nMit freundlichen Grüßen,\nIhr Team von {config name=shopName}', '', 0, '', 3, NULL),
(35, 18, 'sORDERSTATEMAIL18', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(36, 19, 'sORDERSTATEMAIL19', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(37, 20, 'sORDERSTATEMAIL20', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(38, 7, 'sORDERSTATEMAIL7', '{config name=mail}', '{config name=shopName}', 'Statusänderung zur Bestellung bei {config name=shopName}', '', '', 0, '', 3, NULL),
(39, NULL, 'sBIRTHDAY', '{config name=mail}', '{config name=shopName}', 'Herzlichen Glückwunsch zum Geburtstag von {config name=shopName}', 'Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.firstname} {$sUser.lastname},\n\nMit freundlichen Grüßen,\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(40, NULL, 'sARTICLESTOCK', '{config name=mail}', '{config name=shopName}', 'Lagerbestand von {$sData.count} Artikel{if $sData.count>1}n{/if} unter Mindestbestand ', 'Hallo,\n\nfolgende Artikel haben den Mindestbestand unterschritten:\n\nBestellnummer Artikelname Bestand/Mindestbestand \n{foreach from=$sJob.articles item=sArticle key=key}\n{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin} \n{/foreach}\n', '', 1, '', 2, NULL),
(41, NULL, 'sNEWSLETTERCONFIRMATION', '{config name=mail}', '{config name=shopName}', 'Vielen Dank für Ihre Newsletter-Anmeldung', 'Hallo,\n\nvielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.\n\nViele Grüße,\n\nIhr Team von {config name=shopName}\n\n\nKontaktdaten:\n{config name=address}', '', 0, '', 2, NULL),
(42, NULL, 'sOPTINNEWSLETTER', '{config name=mail}', '{config name=shopName}', 'Bitte bestätigen Sie Ihre Newsletter-Anmeldung', 'Hallo, \n\nvielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter. \n\nBitte bestätigen Sie die Anmeldung über den nachfolgenden Link: {$sConfirmLink} \n\n\nViele Grüße\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(43, NULL, 'sOPTINVOTE', '{config name=mail}', '{config name=shopName}', 'Bitte bestätigen Sie Ihre Artikel-Bewertung', 'Hallo, \n\nvielen Dank für die Bewertung des Artikels {$sArticle.articleName}. \n\nBitte bestätigen Sie die Bewertung über nach den nachfolgenden Link: {$sConfirmLink} \n\nViele Grüße', '', 0, '', 2, NULL),
(44, NULL, 'sARTICLEAVAILABLE', '{config name=mail}', '{config name=shopName}', 'Ihr Artikel ist wieder verfügbar', 'Hallo,\n\nIhr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.\n\n{$sArticleLink}\n\nViele Grüße\n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(45, NULL, 'sACCEPTNOTIFICATION', '{config name=mail}', '{config name=shopName}', 'Bitte bestätigen Sie Ihre E-Mail-Benachrichtigung', 'Hallo,\n\nvielen Dank, dass Sie sich für die automatische e-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.\n\nBitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link: \n\n{$sConfirmLink}\n\nViele Grüße \n\nIhr Team von {config name=shopName}', '', 0, '', 2, NULL),
(46, NULL, 'sARTICLECOMMENT', '{config name=mail}', '{config name=shopName}', 'Artikel bewerten', 'Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\n\nSie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>\nSo helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.\n\n\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\n\n{foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n{$sArticle.articleordernumber} {$sArticle.name} {$sArticle.link}\n{/if}\n{/foreach}\n\nViele Grüße,\n\nIhr Team von {config name=shopName}', '<div>\nHallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n<br/>\nSie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>\nSo helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.\n<br/><br/>\n\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\n\n<table>\n {foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n <tr>\n  <td>{$sArticle.articleordernumber}</td>\n  <td>{$sArticle.name}</td>\n  <td>\n  <a href="{$sArticle.link}">link</a>\n  </td>\n </tr>\n{/if}\n {/foreach}\n</table>\n\n\nViele Grüße,<br />\nIhr Team von {config name=shopName}\n</div>', 1, '', 2, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_mails_attachments`
--

DROP TABLE IF EXISTS `s_core_config_mails_attachments`;
CREATE TABLE IF NOT EXISTS `s_core_config_mails_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) NOT NULL,
  `mediaID` int(11) NOT NULL,
  `shopID` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`,`mediaID`,`shopID`),
  KEY `mediaID` (`mediaID`),
  KEY `shopID` (`shopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_mails_attributes`
--

DROP TABLE IF EXISTS `s_core_config_mails_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_config_mails_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_values`
--

DROP TABLE IF EXISTS `s_core_config_values`;
CREATE TABLE IF NOT EXISTS `s_core_config_values` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `element_id` int(11) unsigned NOT NULL,
  `shop_id` int(11) unsigned DEFAULT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`),
  KEY `element_id` (`element_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=56 ;

--
-- Daten für Tabelle `s_core_config_values`
--

INSERT INTO `s_core_config_values` (`id`, `element_id`, `shop_id`, `value`) VALUES
(16, 274, 1, 's:1:"5";'),
(24, 585, 1, 's:0:"";'),
(31, 274, 1, 's:1:"5";'),
(39, 585, 1, 's:0:"";'),
(47, 658, 1, 's:19:"2012-08-28 00:21:05";'),
(48, 275, 1, 'b:0;'),
(49, 673, 1, 's:15:"Shopware 4 Demo";'),
(50, 843, 1, 's:8:"51,51,51";'),
(51, 291, 1, 's:1:"8";'),
(54, 226, 1, 's:1:"3";'),
(55, 227, 1, 's:2:"15";');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries`
--

DROP TABLE IF EXISTS `s_core_countries`;
CREATE TABLE IF NOT EXISTS `s_core_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `countryiso` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `areaID` int(11) DEFAULT NULL,
  `countryen` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `notice` text COLLATE utf8_unicode_ci,
  `shippingfree` int(11) DEFAULT NULL,
  `taxfree` int(11) DEFAULT NULL,
  `taxfree_ustid` int(11) DEFAULT NULL,
  `taxfree_ustid_checked` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  `iso3` varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
  `display_state_in_registration` int(1) NOT NULL,
  `force_state_in_registration` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `areaID` (`areaID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=38 ;

--
-- Daten für Tabelle `s_core_countries`
--

INSERT INTO `s_core_countries` (`id`, `countryname`, `countryiso`, `areaID`, `countryen`, `position`, `notice`, `shippingfree`, `taxfree`, `taxfree_ustid`, `taxfree_ustid_checked`, `active`, `iso3`, `display_state_in_registration`, `force_state_in_registration`) VALUES
(2, 'Deutschland', 'DE', 1, 'GERMANY', 1, '', 0, 0, 0, 0, 1, 'DEU', 0, 0),
(3, 'Arabische Emirate', 'AE', 2, 'ARAB EMIRATES', 10, '', 0, 0, 0, 0, 0, 'ARE', 0, 0),
(4, 'Australien', 'AU', 2, 'AUSTRALIA', 10, '', 0, 0, 0, 0, 0, 'AUS', 0, 0),
(5, 'Belgien', 'BE', 3, 'BELGIUM', 10, '', 0, 0, 0, 0, 0, 'BEL', 0, 0),
(7, 'Dänemark', 'DK', 3, 'DENMARK', 10, '', 0, 0, 0, 0, 0, 'DNK', 0, 0),
(8, 'Finnland', 'FI', 3, 'FINLAND', 10, '', 0, 0, 0, 0, 0, 'FIN', 0, 0),
(9, 'Frankreich', 'FR', 3, 'FRANCE', 10, '', 0, 0, 0, 0, 0, 'FRA', 0, 0),
(10, 'Griechenland', 'GR', 3, 'GREECE', 10, '', 0, 0, 0, 0, 0, 'GRC', 0, 0),
(11, 'Großbritannien', 'GB', 3, 'GREAT BRITAIN', 10, '', 0, 0, 0, 0, 0, 'GBR', 0, 0),
(12, 'Irland', 'IE', 3, 'IRELAND', 10, '', 0, 0, 0, 0, 0, 'IRL', 0, 0),
(13, 'Island', 'IS', 3, 'ICELAND', 10, '', 0, 0, 0, 0, 0, 'ISL', 0, 0),
(14, 'Italien', 'IT', 3, 'ITALY', 10, '', 0, 0, 0, 0, 0, 'ITA', 0, 0),
(15, 'Japan', 'JP', 2, 'JAPAN', 10, '', 0, 0, 0, 0, 0, 'JPN', 0, 0),
(16, 'Kanada', 'CA', 2, 'CANADA', 10, '', 0, 0, 0, 0, 0, 'CAN', 0, 0),
(18, 'Luxemburg', 'LU', 3, 'LUXEMBOURG', 10, '', 0, 0, 0, 0, 0, 'LUX', 0, 0),
(20, 'Namibia', 'NA', 2, 'NAMIBIA', 10, '', 0, 0, 0, 0, 0, 'NAM', 0, 0),
(21, 'Niederlande', 'NL', 3, 'NETHERLANDS', 10, '', 0, 0, 0, 0, 0, 'NLD', 0, 0),
(22, 'Norwegen', 'NO', 3, 'NORWAY', 10, '', 0, 0, 0, 0, 0, 'NOR', 0, 0),
(23, 'Österreich', 'AT', 3, 'AUSTRIA', 1, '', 0, 0, 0, 0, 0, 'AUT', 0, 0),
(24, 'Portugal', 'PT', 3, 'PORTUGAL', 10, '', 0, 0, 0, 0, 0, 'PRT', 0, 0),
(25, 'Schweden', 'SE', 3, 'SWEDEN', 10, '', 0, 0, 0, 0, 0, 'SWE', 0, 0),
(26, 'Schweiz', 'CH', 3, 'SWITZERLAND', 10, '', 0, 1, 0, 0, 0, 'CHE', 0, 0),
(27, 'Spanien', 'ES', 3, 'SPAIN', 10, '', 0, 0, 0, 0, 0, 'ESP', 0, 0),
(28, 'USA', 'US', 2, 'USA', 10, '', 0, 0, 0, 0, 0, 'USA', 0, 0),
(29, 'Liechtenstein', 'LI', 3, 'LIECHTENSTEIN', 10, '', 0, 0, 0, 0, 0, 'LIE', 0, 0),
(30, 'Polen', 'PL', 3, 'POLAND', 10, '', 0, 0, 0, 0, 0, 'POL', 0, 0),
(31, 'Ungarn', 'HU', 3, 'HUNGARY', 10, '', 0, 0, 0, 0, 0, 'HUN', 0, 0),
(32, 'Türkei', 'TR', 2, 'TURKEY', 10, '', 0, 0, 0, 0, 0, 'TUR', 0, 0),
(33, 'Tschechien', 'CZ', 3, 'CZECH REPUBLIC', 10, '', 0, 0, 0, 0, 0, 'CZE', 0, 0),
(34, 'Slowakei', 'SK', 3, 'SLOVAKIA', 10, '', 0, 0, 0, 0, 0, 'SVK', 0, 0),
(35, 'Rum&auml;nien', 'RO', 3, 'ROMANIA', 10, '', 0, 0, 0, 0, 0, 'ROU', 0, 0),
(36, 'Brasilien', 'BR', 2, 'BRAZIL', 10, '', 0, 0, 0, 0, 0, 'BRA', 0, 0),
(37, 'Israel', 'IL', 2, 'ISRAEL', 10, '', 0, 0, 0, 0, 0, 'ISR', 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries_areas`
--

DROP TABLE IF EXISTS `s_core_countries_areas`;
CREATE TABLE IF NOT EXISTS `s_core_countries_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `s_core_countries_areas`
--

INSERT INTO `s_core_countries_areas` (`id`, `name`, `active`) VALUES
(1, 'deutschland', 1),
(2, 'welt', 1),
(3, 'europa', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries_attributes`
--

DROP TABLE IF EXISTS `s_core_countries_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_countries_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `countryID` (`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries_states`
--

DROP TABLE IF EXISTS `s_core_countries_states`;
CREATE TABLE IF NOT EXISTS `s_core_countries_states` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryID` int(11) DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shortcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `countryID` (`countryID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=70 ;

--
-- Daten für Tabelle `s_core_countries_states`
--

INSERT INTO `s_core_countries_states` (`id`, `countryID`, `name`, `shortcode`, `position`, `active`) VALUES
(2, 2, 'Niedersachsen', 'NI', 0, 1),
(3, 2, 'Nordrhein-Westfalen', 'NW', 0, 1),
(5, 2, 'Baden-Württemberg', 'BW', 0, 1),
(6, 2, 'Bayern', 'BY', 0, 1),
(7, 2, 'Berlin', 'BE', 0, 1),
(8, 2, 'Brandenburg', 'BB', 0, 1),
(9, 2, 'Bremen', 'HB', 0, 1),
(10, 2, 'Hamburg', 'HH', 0, 1),
(11, 2, 'Hessen', 'HE', 0, 1),
(12, 2, 'Mecklenburg-Vorpommern', 'MV', 0, 1),
(13, 2, 'Rheinland-Pfalz', 'RP', 0, 1),
(14, 2, 'Saarland', 'SL', 0, 1),
(15, 2, 'Sachsen', 'SN', 0, 1),
(16, 2, 'Sachsen-Anhalt', 'ST', 0, 1),
(17, 2, 'Schleswig-Holstein', 'SH', 0, 1),
(18, 2, 'Thüringen', 'TH', 0, 1),
(20, 28, 'Alabama', 'AL', 0, 1),
(21, 28, 'Alaska', 'AK', 0, 1),
(22, 28, 'Arizona', 'AZ', 0, 1),
(23, 28, 'Arkansas', 'AR', 0, 1),
(24, 28, 'Kalifornien', 'CA', 0, 1),
(25, 28, 'Colorado', 'CO', 0, 1),
(26, 28, 'Connecticut', 'CT', 0, 1),
(27, 28, 'Delaware', 'DE', 0, 1),
(28, 28, 'Florida', 'FL', 0, 1),
(29, 28, 'Georgia', 'GA', 0, 1),
(30, 28, 'Hawaii', 'HI', 0, 1),
(31, 28, 'Idaho', 'ID', 0, 1),
(32, 28, 'Illinois', 'IL', 0, 1),
(33, 28, 'Indiana', 'IN', 0, 1),
(34, 28, 'Iowa', 'IA', 0, 1),
(35, 28, 'Kansas', 'KS', 0, 1),
(36, 28, 'Kentucky', 'KY', 0, 1),
(37, 28, 'Louisiana', 'LA', 0, 1),
(38, 28, 'Maine', 'ME', 0, 1),
(39, 28, 'Maryland', 'MD', 0, 1),
(40, 28, 'Massachusetts', 'MA', 0, 1),
(41, 28, 'Michigan', 'MI', 0, 1),
(42, 28, 'Minnesota', 'MN', 0, 1),
(43, 28, 'Mississippi', 'MS', 0, 1),
(44, 28, 'Missouri', 'MO', 0, 1),
(45, 28, 'Montana', 'MT', 0, 1),
(46, 28, 'Nebraska', 'NE', 0, 1),
(47, 28, 'Nevada', 'NV', 0, 1),
(48, 28, 'New Hampshire', 'NH', 0, 1),
(49, 28, 'New Jersey', 'NJ', 0, 1),
(50, 28, 'New Mexico', 'NM', 0, 1),
(51, 28, 'New York', 'NY', 0, 1),
(52, 28, 'North Carolina', 'NC', 0, 1),
(53, 28, 'North Dakota', 'ND', 0, 1),
(54, 28, 'Ohio', 'OH', 0, 1),
(55, 28, 'Oklahoma', 'OK', 0, 1),
(56, 28, 'Oregon', 'OR', 0, 1),
(57, 28, 'Pennsylvania', 'PA', 0, 1),
(58, 28, 'Rhode Island', 'RI', 0, 1),
(59, 28, 'South Carolina', 'SC', 0, 1),
(60, 28, 'South Dakota', 'SD', 0, 1),
(61, 28, 'Tennessee', 'TN', 0, 1),
(62, 28, 'Texas', 'TX', 0, 1),
(63, 28, 'Utah', 'UT', 0, 1),
(64, 28, 'Vermont', 'VT', 0, 1),
(65, 28, 'Virginia', 'VA', 0, 1),
(66, 28, 'Washington', 'WA', 0, 1),
(67, 28, 'West Virginia', 'WV', 0, 1),
(68, 28, 'Wisconsin', 'WI', 0, 1),
(69, 28, 'Wyoming', 'WY', 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries_states_attributes`
--

DROP TABLE IF EXISTS `s_core_countries_states_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_countries_states_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stateID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `stateID` (`stateID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_currencies`
--

DROP TABLE IF EXISTS `s_core_currencies`;
CREATE TABLE IF NOT EXISTS `s_core_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `standard` int(1) NOT NULL,
  `factor` double NOT NULL,
  `templatechar` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `symbol_position` int(11) unsigned NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_core_currencies`
--

INSERT INTO `s_core_currencies` (`id`, `currency`, `name`, `standard`, `factor`, `templatechar`, `symbol_position`, `position`) VALUES
(1, 'EUR', 'Euro', 1, 1, '&euro;', 0, 0),
(2, 'USD', 'US-Dollar', 0, 1.3625, '$', 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customergroups`
--

DROP TABLE IF EXISTS `s_core_customergroups`;
CREATE TABLE IF NOT EXISTS `s_core_customergroups` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_customergroups`
--

INSERT INTO `s_core_customergroups` (`id`, `groupkey`, `description`, `tax`, `taxinput`, `mode`, `discount`, `minimumorder`, `minimumordersurcharge`) VALUES
(1, 'EK', 'Shopkunden', 1, 1, 0, 0, 10, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customergroups_attributes`
--

DROP TABLE IF EXISTS `s_core_customergroups_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_customergroups_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customerGroupID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `customerGroupID` (`customerGroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customergroups_discounts`
--

DROP TABLE IF EXISTS `s_core_customergroups_discounts`;
CREATE TABLE IF NOT EXISTS `s_core_customergroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `basketdiscount` double NOT NULL,
  `basketdiscountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`basketdiscountstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customerpricegroups`
--

DROP TABLE IF EXISTS `s_core_customerpricegroups`;
CREATE TABLE IF NOT EXISTS `s_core_customerpricegroups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `netto` int(1) unsigned NOT NULL,
  `active` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_detail_states`
--

DROP TABLE IF EXISTS `s_core_detail_states`;
CREATE TABLE IF NOT EXISTS `s_core_detail_states` (
  `id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_core_detail_states`
--

INSERT INTO `s_core_detail_states` (`id`, `description`, `position`, `mail`) VALUES
(0, 'Offen', 1, 0),
(1, 'In Bearbeitung', 2, 0),
(2, 'Storniert', 3, 0),
(3, 'Abgeschlossen', 4, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_documents`
--

DROP TABLE IF EXISTS `s_core_documents`;
CREATE TABLE IF NOT EXISTS `s_core_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `numbers` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `top` int(11) NOT NULL,
  `bottom` int(11) NOT NULL,
  `pagebreak` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `s_core_documents`
--

INSERT INTO `s_core_documents` (`id`, `name`, `template`, `numbers`, `left`, `right`, `top`, `bottom`, `pagebreak`) VALUES
(1, 'Rechnung', 'index.tpl', 'doc_0', 25, 10, 20, 20, 10),
(2, 'Lieferschein', 'index_ls.tpl', 'doc_1', 25, 10, 20, 20, 10),
(3, 'Gutschrift', 'index_gs.tpl', 'doc_2', 25, 10, 20, 20, 10),
(4, 'Stornorechnung', 'index_sr.tpl', 'doc_3', 25, 10, 20, 20, 10);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_documents_box`
--

DROP TABLE IF EXISTS `s_core_documents_box`;
CREATE TABLE IF NOT EXISTS `s_core_documents_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) NOT NULL,
  `name` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `style` longtext COLLATE utf8_unicode_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=180 ;

--
-- Daten für Tabelle `s_core_documents_box`
--

INSERT INTO `s_core_documents_box` (`id`, `documentID`, `name`, `style`, `value`) VALUES
(1, 1, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(2, 1, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(3, 1, 'Header_Recipient', '', ''),
(4, 1, 'Header', 'height: 60mm;', ''),
(5, 1, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(6, 1, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(7, 1, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(8, 1, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(9, 1, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(10, 1, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(11, 1, 'Td_Name', 'white-space:normal;', ''),
(12, 1, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(13, 1, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(14, 1, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(15, 1, 'Content_Amount', 'margin-left:90mm;', ''),
(16, 1, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(68, 2, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(69, 2, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(70, 2, 'Header_Recipient', '', ''),
(71, 2, 'Header', 'height: 60mm;', ''),
(72, 2, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(73, 2, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(74, 2, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(75, 2, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(76, 2, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(77, 2, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(78, 2, 'Td_Name', 'white-space:normal;', ''),
(79, 2, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(80, 2, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(81, 2, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(82, 2, 'Content_Amount', 'margin-left:90mm;', ''),
(83, 2, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(84, 3, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(85, 3, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(86, 3, 'Header_Recipient', '', ''),
(87, 3, 'Header', 'height: 60mm;', ''),
(88, 3, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(89, 3, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(90, 3, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(91, 3, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(92, 3, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(93, 3, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(94, 3, 'Td_Name', 'white-space:normal;', ''),
(95, 3, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(96, 3, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(97, 3, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(98, 3, 'Content_Amount', 'margin-left:90mm;', ''),
(99, 3, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(100, 4, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(101, 4, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(102, 4, 'Header_Recipient', '', ''),
(103, 4, 'Header', 'height: 60mm;', ''),
(104, 4, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(105, 4, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(106, 4, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(107, 4, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(108, 4, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(109, 4, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(110, 4, 'Td_Name', 'white-space:normal;', ''),
(111, 4, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(112, 4, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(113, 4, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(114, 4, 'Content_Amount', 'margin-left:90mm;', ''),
(115, 4, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_elements`
--

DROP TABLE IF EXISTS `s_core_engine_elements`;
CREATE TABLE IF NOT EXISTS `s_core_engine_elements` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=57 ;

--
-- Daten für Tabelle `s_core_engine_elements`
--

INSERT INTO `s_core_engine_elements` (`id`, `groupID`, `domname`, `default`, `type`, `store`, `label`, `required`, `position`, `name`, `layout`, `variantable`, `help`, `translatable`) VALUES
(22, 7, 'attr[3]', '', 'textarea', NULL, 'Kommentar', 0, 3, 'attr3', '', 0, 'Optionaler Kommentar', 1),
(33, 7, 'attr[1]', '', 'textfield', NULL, 'Freitext-1', 0, 1, 'attr1', 'w200', 1, 'Freitext zur Anzeige auf der Detailseite', 1),
(34, 7, 'attr[2]', '', 'textfield', NULL, 'Freitext-2', 0, 2, 'attr2', 'w200', 1, 'Freitext zur Anzeige auf der Detailseite', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_groups`
--

DROP TABLE IF EXISTS `s_core_engine_groups`;
CREATE TABLE IF NOT EXISTS `s_core_engine_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `layout` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `variantable` int(1) unsigned NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `s_core_engine_groups`
--

INSERT INTO `s_core_engine_groups` (`id`, `name`, `label`, `layout`, `variantable`, `position`) VALUES
(1, 'basic', 'Stammdaten', 'column', 1, 1),
(2, 'description', 'Beschreibung', NULL, 0, 2),
(3, 'advanced', 'Einstellungen', 'column', 1, 5),
(7, 'additional', 'Zusatzfelder', NULL, 1, 7),
(8, 'reference_price', 'Grundpreisberechnung', NULL, 0, 4),
(10, 'price', 'Preise und Kundengruppen', NULL, 0, 3),
(11, 'property', 'Eigenschaften', NULL, 0, 6);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_queries`
--

DROP TABLE IF EXISTS `s_core_engine_queries`;
CREATE TABLE IF NOT EXISTS `s_core_engine_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `option` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `value` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `domelement` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13 ;

--
-- Daten für Tabelle `s_core_engine_queries`
--

INSERT INTO `s_core_engine_queries` (`id`, `query`, `option`, `value`, `domelement`) VALUES
(2, 'SELECT DISTINCT description, domvalue FROM s_core_engine_values WHERE domelement=''txtart'' ORDER BY position ASC', 'description', 'value', 'txtart'),
(3, 'SELECT id,selection FROM s_core_variants ORDER BY selection ASC', 'selection ', 'id', 'txtvariantenkriterium'),
(4, 'SELECT DISTINCT description, domvalue FROM s_core_engine_values WHERE domelement=''txtversandkostenfrei'' ORDER BY position ASC', 'description', 'domvalue', 'txtversandkostenfrei'),
(6, 'SELECT DISTINCT description, domvalue FROM s_core_engine_values WHERE domelement=''txtaktiv'' ORDER BY position ASC', 'description', 'domvalue', 'txtaktiv'),
(7, 'SELECT DISTINCT id, tax, description FROM s_core_tax ORDER BY id ASC', 'description', 'id', 'txtmwst'),
(8, 'SELECT DISTINCT id, unit, description FROM s_core_units ORDER BY id ASC', 'description', 'id', 'txtunit'),
(11, 'SELECT DISTINCT id, description FROM s_core_pricegroups ORDER BY id ASC', 'description', 'id', 'selectPricegroup'),
(12, 'SELECT DISTINCT id, name AS description FROM s_filter ORDER BY name ASC', 'description', 'id', 'selectFilterGroup');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_factory`
--

DROP TABLE IF EXISTS `s_core_factory`;
CREATE TABLE IF NOT EXISTS `s_core_factory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `basename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `basefile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inheritname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inheritfile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`basename`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=21 ;

--
-- Daten für Tabelle `s_core_factory`
--

INSERT INTO `s_core_factory` (`id`, `description`, `basename`, `basefile`, `inheritname`, `inheritfile`) VALUES
(1, 'Artikelhandling', 'sArticles', 'sArticles.php', 'myArticles', 'myArticles.php'),
(2, 'Kundenbereich', 'sAdmin', 'sAdmin.php', '', ''),
(3, 'Warenkorb', 'sBasket', 'sBasket.php', '', ''),
(4, 'Kategoriesystem', 'sCategories', 'sCategories.php', '', ''),
(5, 'Kernfunktionalitäten', 'sCore', 'sCore.php', 'myCore', 'myCore.php'),
(7, 'Cross-Selling', 'sCrossselling', 'sCrossselling.php', '', ''),
(8, 'Bestellhandling', 'sOrder', 'sOrder.php', '', ''),
(13, 'Marketing-Funktionen', 'sMarketing', 'sMarketing.php', '', ''),
(14, 'Content-Management', 'sCms', 'sCms.php', '', ''),
(15, 'Intelligente Suche', 'sSearch', 'sSearch.php', '', ''),
(17, 'Support-Funktionen', 'sCmsSupport', 'sCmsSupport.php', '', ''),
(18, 'Cache-Funktionen', 'sCache', 'sCache.php', '', ''),
(19, 'Ticket Support', 'sTicketSystem', 'sTicketSystem.php', '', ''),
(20, 'Router', 'sRouter', 'sRouter.php', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_licences`
--

DROP TABLE IF EXISTS `s_core_licences`;
CREATE TABLE IF NOT EXISTS `s_core_licences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `module` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inactive` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_licenses`
--

DROP TABLE IF EXISTS `s_core_licenses`;
CREATE TABLE IF NOT EXISTS `s_core_licenses` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_locales`
--

DROP TABLE IF EXISTS `s_core_locales`;
CREATE TABLE IF NOT EXISTS `s_core_locales` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `territory` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=256 ;

--
-- Daten für Tabelle `s_core_locales`
--

INSERT INTO `s_core_locales` (`id`, `locale`, `language`, `territory`) VALUES
(1, 'de_DE', 'Deutsch', 'Deutschland'),
(2, 'en_GB', 'Englisch', 'Vereinigtes Königreich'),
(3, 'aa_DJ', 'Afar', 'Dschibuti'),
(4, 'aa_ER', 'Afar', 'Eritrea'),
(5, 'aa_ET', 'Afar', 'Äthiopien'),
(6, 'af_NA', 'Afrikaans', 'Namibia'),
(7, 'af_ZA', 'Afrikaans', 'Südafrika'),
(8, 'ak_GH', 'Akan', 'Ghana'),
(9, 'am_ET', 'Amharisch', 'Äthiopien'),
(10, 'ar_AE', 'Arabisch', 'Vereinigte Arabische Emirate'),
(11, 'ar_BH', 'Arabisch', 'Bahrain'),
(12, 'ar_DZ', 'Arabisch', 'Algerien'),
(13, 'ar_EG', 'Arabisch', 'Ägypten'),
(14, 'ar_IQ', 'Arabisch', 'Irak'),
(15, 'ar_JO', 'Arabisch', 'Jordanien'),
(16, 'ar_KW', 'Arabisch', 'Kuwait'),
(17, 'ar_LB', 'Arabisch', 'Libanon'),
(18, 'ar_LY', 'Arabisch', 'Libyen'),
(19, 'ar_MA', 'Arabisch', 'Marokko'),
(20, 'ar_OM', 'Arabisch', 'Oman'),
(21, 'ar_QA', 'Arabisch', 'Katar'),
(22, 'ar_SA', 'Arabisch', 'Saudi-Arabien'),
(23, 'ar_SD', 'Arabisch', 'Sudan'),
(24, 'ar_SY', 'Arabisch', 'Syrien'),
(25, 'ar_TN', 'Arabisch', 'Tunesien'),
(26, 'ar_YE', 'Arabisch', 'Jemen'),
(27, 'as_IN', 'Assamesisch', 'Indien'),
(28, 'az_AZ', 'Aserbaidschanisch', 'Aserbaidschan'),
(29, 'be_BY', 'Weißrussisch', 'Belarus'),
(30, 'bg_BG', 'Bulgarisch', 'Bulgarien'),
(31, 'bn_BD', 'Bengalisch', 'Bangladesch'),
(32, 'bn_IN', 'Bengalisch', 'Indien'),
(33, 'bo_CN', 'Tibetisch', 'China'),
(34, 'bo_IN', 'Tibetisch', 'Indien'),
(35, 'bs_BA', 'Bosnisch', 'Bosnien und Herzegowina'),
(36, 'byn_ER', 'Blin', 'Eritrea'),
(37, 'ca_ES', 'Katalanisch', 'Spanien'),
(38, 'cch_NG', 'Atsam', 'Nigeria'),
(39, 'cs_CZ', 'Tschechisch', 'Tschechische Republik'),
(40, 'cy_GB', 'Walisisch', 'Vereinigtes Königreich'),
(41, 'da_DK', 'Dänisch', 'Dänemark'),
(42, 'de_AT', 'Deutsch', 'Österreich'),
(43, 'de_BE', 'Deutsch', 'Belgien'),
(44, 'de_CH', 'Deutsch', 'Schweiz'),
(45, 'de_LI', 'Deutsch', 'Liechtenstein'),
(46, 'de_LU', 'Deutsch', 'Luxemburg'),
(47, 'dv_MV', 'Maledivisch', 'Malediven'),
(48, 'dz_BT', 'Bhutanisch', 'Bhutan'),
(49, 'ee_GH', 'Ewe-Sprache', 'Ghana'),
(50, 'ee_TG', 'Ewe-Sprache', 'Togo'),
(51, 'el_CY', 'Griechisch', 'Zypern'),
(52, 'el_GR', 'Griechisch', 'Griechenland'),
(53, 'en_AS', 'Englisch', 'Amerikanisch-Samoa'),
(54, 'en_AU', 'Englisch', 'Australien'),
(55, 'en_BE', 'Englisch', 'Belgien'),
(56, 'en_BW', 'Englisch', 'Botsuana'),
(57, 'en_BZ', 'Englisch', 'Belize'),
(58, 'en_CA', 'Englisch', 'Kanada'),
(59, 'en_GU', 'Englisch', 'Guam'),
(60, 'en_HK', 'Englisch', 'Sonderverwaltungszone Hongkong'),
(61, 'en_IE', 'Englisch', 'Irland'),
(62, 'en_IN', 'Englisch', 'Indien'),
(63, 'en_JM', 'Englisch', 'Jamaika'),
(64, 'en_MH', 'Englisch', 'Marshallinseln'),
(65, 'en_MP', 'Englisch', 'Nördliche Marianen'),
(66, 'en_MT', 'Englisch', 'Malta'),
(67, 'en_NA', 'Englisch', 'Namibia'),
(68, 'en_NZ', 'Englisch', 'Neuseeland'),
(69, 'en_PH', 'Englisch', 'Philippinen'),
(70, 'en_PK', 'Englisch', 'Pakistan'),
(71, 'en_SG', 'Englisch', 'Singapur'),
(72, 'en_TT', 'Englisch', 'Trinidad und Tobago'),
(73, 'en_UM', 'Englisch', 'Amerikanisch-Ozeanien'),
(74, 'en_US', 'Englisch', 'Vereinigte Staaten'),
(75, 'en_VI', 'Englisch', 'Amerikanische Jungferninseln'),
(76, 'en_ZA', 'Englisch', 'Südafrika'),
(77, 'en_ZW', 'Englisch', 'Simbabwe'),
(78, 'es_AR', 'Spanisch', 'Argentinien'),
(79, 'es_BO', 'Spanisch', 'Bolivien'),
(80, 'es_CL', 'Spanisch', 'Chile'),
(81, 'es_CO', 'Spanisch', 'Kolumbien'),
(82, 'es_CR', 'Spanisch', 'Costa Rica'),
(83, 'es_DO', 'Spanisch', 'Dominikanische Republik'),
(84, 'es_EC', 'Spanisch', 'Ecuador'),
(85, 'es_ES', 'Spanisch', 'Spanien'),
(86, 'es_GT', 'Spanisch', 'Guatemala'),
(87, 'es_HN', 'Spanisch', 'Honduras'),
(88, 'es_MX', 'Spanisch', 'Mexiko'),
(89, 'es_NI', 'Spanisch', 'Nicaragua'),
(90, 'es_PA', 'Spanisch', 'Panama'),
(91, 'es_PE', 'Spanisch', 'Peru'),
(92, 'es_PR', 'Spanisch', 'Puerto Rico'),
(93, 'es_PY', 'Spanisch', 'Paraguay'),
(94, 'es_SV', 'Spanisch', 'El Salvador'),
(95, 'es_US', 'Spanisch', 'Vereinigte Staaten'),
(96, 'es_UY', 'Spanisch', 'Uruguay'),
(97, 'es_VE', 'Spanisch', 'Venezuela'),
(98, 'et_EE', 'Estnisch', 'Estland'),
(99, 'eu_ES', 'Baskisch', 'Spanien'),
(100, 'fa_AF', 'Persisch', 'Afghanistan'),
(101, 'fa_IR', 'Persisch', 'Iran'),
(102, 'fi_FI', 'Finnisch', 'Finnland'),
(103, 'fil_PH', 'Filipino', 'Philippinen'),
(104, 'fo_FO', 'Färöisch', 'Färöer'),
(105, 'fr_BE', 'Französisch', 'Belgien'),
(106, 'fr_CA', 'Französisch', 'Kanada'),
(107, 'fr_CH', 'Französisch', 'Schweiz'),
(108, 'fr_FR', 'Französisch', 'Frankreich'),
(109, 'fr_LU', 'Französisch', 'Luxemburg'),
(110, 'fr_MC', 'Französisch', 'Monaco'),
(111, 'fr_SN', 'Französisch', 'Senegal'),
(112, 'fur_IT', 'Friulisch', 'Italien'),
(113, 'ga_IE', 'Irisch', 'Irland'),
(114, 'gaa_GH', 'Ga-Sprache', 'Ghana'),
(115, 'gez_ER', 'Geez', 'Eritrea'),
(116, 'gez_ET', 'Geez', 'Äthiopien'),
(117, 'gl_ES', 'Galizisch', 'Spanien'),
(118, 'gsw_CH', 'Schweizerdeutsch', 'Schweiz'),
(119, 'gu_IN', 'Gujarati', 'Indien'),
(120, 'gv_GB', 'Manx', 'Vereinigtes Königreich'),
(121, 'ha_GH', 'Hausa', 'Ghana'),
(122, 'ha_NE', 'Hausa', 'Niger'),
(123, 'ha_NG', 'Hausa', 'Nigeria'),
(124, 'ha_SD', 'Hausa', 'Sudan'),
(125, 'haw_US', 'Hawaiisch', 'Vereinigte Staaten'),
(126, 'he_IL', 'Hebräisch', 'Israel'),
(127, 'hi_IN', 'Hindi', 'Indien'),
(128, 'hr_HR', 'Kroatisch', 'Kroatien'),
(129, 'hu_HU', 'Ungarisch', 'Ungarn'),
(130, 'hy_AM', 'Armenisch', 'Armenien'),
(131, 'id_ID', 'Indonesisch', 'Indonesien'),
(132, 'ig_NG', 'Igbo-Sprache', 'Nigeria'),
(133, 'ii_CN', 'Sichuan Yi', 'China'),
(134, 'is_IS', 'Isländisch', 'Island'),
(135, 'it_CH', 'Italienisch', 'Schweiz'),
(136, 'it_IT', 'Italienisch', 'Italien'),
(137, 'ja_JP', 'Japanisch', 'Japan'),
(138, 'ka_GE', 'Georgisch', 'Georgien'),
(139, 'kaj_NG', 'Jju', 'Nigeria'),
(140, 'kam_KE', 'Kamba', 'Kenia'),
(141, 'kcg_NG', 'Tyap', 'Nigeria'),
(142, 'kfo_CI', 'Koro', 'Côte d?Ivoire'),
(143, 'kk_KZ', 'Kasachisch', 'Kasachstan'),
(144, 'kl_GL', 'Grönländisch', 'Grönland'),
(145, 'km_KH', 'Kambodschanisch', 'Kambodscha'),
(146, 'kn_IN', 'Kannada', 'Indien'),
(147, 'ko_KR', 'Koreanisch', 'Republik Korea'),
(148, 'kok_IN', 'Konkani', 'Indien'),
(149, 'kpe_GN', 'Kpelle-Sprache', 'Guinea'),
(150, 'kpe_LR', 'Kpelle-Sprache', 'Liberia'),
(151, 'ku_IQ', 'Kurdisch', 'Irak'),
(152, 'ku_IR', 'Kurdisch', 'Iran'),
(153, 'ku_SY', 'Kurdisch', 'Syrien'),
(154, 'ku_TR', 'Kurdisch', 'Türkei'),
(155, 'kw_GB', 'Kornisch', 'Vereinigtes Königreich'),
(156, 'ky_KG', 'Kirgisisch', 'Kirgisistan'),
(157, 'ln_CD', 'Lingala', 'Demokratische Republik Kongo'),
(158, 'ln_CG', 'Lingala', 'Kongo'),
(159, 'lo_LA', 'Laotisch', 'Laos'),
(160, 'lt_LT', 'Litauisch', 'Litauen'),
(161, 'lv_LV', 'Lettisch', 'Lettland'),
(162, 'mk_MK', 'Mazedonisch', 'Mazedonien'),
(163, 'ml_IN', 'Malayalam', 'Indien'),
(164, 'mn_CN', 'Mongolisch', 'China'),
(165, 'mn_MN', 'Mongolisch', 'Mongolei'),
(166, 'mr_IN', 'Marathi', 'Indien'),
(167, 'ms_BN', 'Malaiisch', 'Brunei Darussalam'),
(168, 'ms_MY', 'Malaiisch', 'Malaysia'),
(169, 'mt_MT', 'Maltesisch', 'Malta'),
(170, 'my_MM', 'Birmanisch', 'Myanmar'),
(171, 'nb_NO', 'Norwegisch Bokmål', 'Norwegen'),
(172, 'nds_DE', 'Niederdeutsch', 'Deutschland'),
(173, 'ne_IN', 'Nepalesisch', 'Indien'),
(174, 'ne_NP', 'Nepalesisch', 'Nepal'),
(175, 'nl_BE', 'Niederländisch', 'Belgien'),
(176, 'nl_NL', 'Niederländisch', 'Niederlande'),
(177, 'nn_NO', 'Norwegisch Nynorsk', 'Norwegen'),
(178, 'nr_ZA', 'Süd-Ndebele-Sprache', 'Südafrika'),
(179, 'nso_ZA', 'Nord-Sotho-Sprache', 'Südafrika'),
(180, 'ny_MW', 'Nyanja-Sprache', 'Malawi'),
(181, 'oc_FR', 'Okzitanisch', 'Frankreich'),
(182, 'om_ET', 'Oromo', 'Äthiopien'),
(183, 'om_KE', 'Oromo', 'Kenia'),
(184, 'or_IN', 'Orija', 'Indien'),
(185, 'pa_IN', 'Pandschabisch', 'Indien'),
(186, 'pa_PK', 'Pandschabisch', 'Pakistan'),
(187, 'pl_PL', 'Polnisch', 'Polen'),
(188, 'ps_AF', 'Paschtu', 'Afghanistan'),
(189, 'pt_BR', 'Portugiesisch', 'Brasilien'),
(190, 'pt_PT', 'Portugiesisch', 'Portugal'),
(191, 'ro_MD', 'Rumänisch', 'Republik Moldau'),
(192, 'ro_RO', 'Rumänisch', 'Rumänien'),
(193, 'ru_RU', 'Russisch', 'Russische Föderation'),
(194, 'ru_UA', 'Russisch', 'Ukraine'),
(195, 'rw_RW', 'Ruandisch', 'Ruanda'),
(196, 'sa_IN', 'Sanskrit', 'Indien'),
(197, 'se_FI', 'Nord-Samisch', 'Finnland'),
(198, 'se_NO', 'Nord-Samisch', 'Norwegen'),
(199, 'sh_BA', 'Serbo-Kroatisch', 'Bosnien und Herzegowina'),
(200, 'sh_CS', 'Serbo-Kroatisch', 'Serbien und Montenegro'),
(201, 'sh_YU', 'Serbo-Kroatisch', ''),
(202, 'si_LK', 'Singhalesisch', 'Sri Lanka'),
(203, 'sid_ET', 'Sidamo', 'Äthiopien'),
(204, 'sk_SK', 'Slowakisch', 'Slowakei'),
(205, 'sl_SI', 'Slowenisch', 'Slowenien'),
(206, 'so_DJ', 'Somali', 'Dschibuti'),
(207, 'so_ET', 'Somali', 'Äthiopien'),
(208, 'so_KE', 'Somali', 'Kenia'),
(209, 'so_SO', 'Somali', 'Somalia'),
(210, 'sq_AL', 'Albanisch', 'Albanien'),
(211, 'sr_BA', 'Serbisch', 'Bosnien und Herzegowina'),
(212, 'sr_CS', 'Serbisch', 'Serbien und Montenegro'),
(213, 'sr_ME', 'Serbisch', 'Montenegro'),
(214, 'sr_RS', 'Serbisch', 'Serbien'),
(215, 'sr_YU', 'Serbisch', ''),
(216, 'ss_SZ', 'Swazi', 'Swasiland'),
(217, 'ss_ZA', 'Swazi', 'Südafrika'),
(218, 'st_LS', 'Süd-Sotho-Sprache', 'Lesotho'),
(219, 'st_ZA', 'Süd-Sotho-Sprache', 'Südafrika'),
(220, 'sv_FI', 'Schwedisch', 'Finnland'),
(221, 'sv_SE', 'Schwedisch', 'Schweden'),
(222, 'sw_KE', 'Suaheli', 'Kenia'),
(223, 'sw_TZ', 'Suaheli', 'Tansania'),
(224, 'syr_SY', 'Syrisch', 'Syrien'),
(225, 'ta_IN', 'Tamilisch', 'Indien'),
(226, 'te_IN', 'Telugu', 'Indien'),
(227, 'tg_TJ', 'Tadschikisch', 'Tadschikistan'),
(228, 'th_TH', 'Thailändisch', 'Thailand'),
(229, 'ti_ER', 'Tigrinja', 'Eritrea'),
(230, 'ti_ET', 'Tigrinja', 'Äthiopien'),
(231, 'tig_ER', 'Tigre', 'Eritrea'),
(232, 'tn_ZA', 'Tswana-Sprache', 'Südafrika'),
(233, 'to_TO', 'Tongaisch', 'Tonga'),
(234, 'tr_TR', 'Türkisch', 'Türkei'),
(236, 'ts_ZA', 'Tsonga', 'Südafrika'),
(237, 'tt_RU', 'Tatarisch', 'Russische Föderation'),
(238, 'ug_CN', 'Uigurisch', 'China'),
(239, 'uk_UA', 'Ukrainisch', 'Ukraine'),
(240, 'ur_IN', 'Urdu', 'Indien'),
(241, 'ur_PK', 'Urdu', 'Pakistan'),
(242, 'uz_AF', 'Usbekisch', 'Afghanistan'),
(243, 'uz_UZ', 'Usbekisch', 'Usbekistan'),
(244, 've_ZA', 'Venda-Sprache', 'Südafrika'),
(245, 'vi_VN', 'Vietnamesisch', 'Vietnam'),
(246, 'wal_ET', 'Walamo-Sprache', 'Äthiopien'),
(247, 'wo_SN', 'Wolof', 'Senegal'),
(248, 'xh_ZA', 'Xhosa', 'Südafrika'),
(249, 'yo_NG', 'Yoruba', 'Nigeria'),
(250, 'zh_CN', 'Chinesisch', 'China'),
(251, 'zh_HK', 'Chinesisch', 'Sonderverwaltungszone Hongkong'),
(252, 'zh_MO', 'Chinesisch', 'Sonderverwaltungszone Macao'),
(253, 'zh_SG', 'Chinesisch', 'Singapur'),
(254, 'zh_TW', 'Chinesisch', 'Taiwan'),
(255, 'zu_ZA', 'Zulu', 'Südafrika');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_log`
--

DROP TABLE IF EXISTS `s_core_log`;
CREATE TABLE IF NOT EXISTS `s_core_log` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_log`
--

INSERT INTO `s_core_log` (`id`, `type`, `key`, `text`, `date`, `user`, `ip_address`, `user_agent`, `value4`) VALUES
(1, 'backend', 'Versandkosten Verwaltung', 'Einstellungen wurden erfolgreich gespeichert.', '2012-08-28 10:38:26', 'Administrator', '217.86.247.178', 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:14.0) Gecko/20100101 Firefox/14.0.1 FirePHP/0.7.1', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_menu`
--

DROP TABLE IF EXISTS `s_core_menu`;
CREATE TABLE IF NOT EXISTS `s_core_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned DEFAULT NULL,
  `hyperlink` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `onclick` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `style` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `class` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `pluginID` int(11) unsigned DEFAULT NULL,
  `resourceID` int(11) DEFAULT NULL,
  `controller` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `shortcut` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`parent`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=118 ;

--
-- Daten für Tabelle `s_core_menu`
--

INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES
(1, NULL, '', 'Artikel', NULL, NULL, 'ico package_green', 0, 1, NULL, NULL, 'Article', NULL, NULL),
(2, 1, '', 'Anlegen', '', NULL, 'sprite-inbox--plus', -3, 1, NULL, NULL, 'Article', 'STRG + ALT + N', 'Detail'),
(4, 1, '', 'Kategorien', '', NULL, 'sprite-blue-folders-stack', 0, 1, NULL, 23, 'Category', NULL, 'Index'),
(6, 1, '', 'Hersteller', NULL, NULL, 'sprite-truck', 2, 1, NULL, NULL, 'Supplier', NULL, 'Index'),
(7, NULL, '', 'Inhalte', NULL, NULL, 'ico2 note03', 0, 1, NULL, NULL, 'Content', NULL, NULL),
(8, 30, '', 'Banner', NULL, NULL, 'sprite-image-medium', 0, 1, NULL, 2, 'Banner', NULL, 'Index'),
(9, 30, '', 'Einkaufswelten', '', NULL, 'sprite-pin', 1, 1, NULL, NULL, 'Emotion', NULL, 'Index'),
(10, 30, '', 'Gutscheine', NULL, NULL, 'sprite-mail-open-image', 3, 1, NULL, 8, 'Voucher', NULL, 'Index'),
(11, 30, '', 'Pr&auml;mienartikel', NULL, NULL, 'sprite-star', 2, 1, NULL, 7, 'Premium', NULL, 'Index'),
(12, 30, '', 'Produktexporte', NULL, NULL, 'sprite-folder-export', 5, 1, NULL, 11, 'ProductFeed', NULL, 'Index'),
(15, 7, '', 'Shopseiten', NULL, NULL, 'sprite-documents', 0, 1, NULL, NULL, 'Site', NULL, 'Index'),
(20, NULL, '', 'Kunden', NULL, NULL, 'ico customer', 0, 1, NULL, NULL, 'Customer', NULL, NULL),
(21, 20, '', 'Kundenliste', NULL, NULL, 'sprite-ui-scroll-pane-detail', 0, 1, NULL, NULL, 'Customer', 'STRG + ALT + K', 'Index'),
(22, 20, '', 'Bestellungen', NULL, NULL, 'sprite-sticky-notes-pin', 0, 1, NULL, NULL, 'Order', 'STRG + ALT + B', 'Index'),
(23, NULL, '', 'Einstellungen', NULL, NULL, 'ico2 wrench_screwdriver', 0, 1, NULL, NULL, 'ConfigurationMenu', NULL, NULL),
(25, 23, '', 'Benutzerverwaltung', NULL, NULL, 'sprite-user-silhouette', -2, 1, NULL, 35, 'UserManager', NULL, 'Index'),
(26, 23, '', 'Versandkosten', NULL, NULL, 'sprite-envelope--arrow', 0, 1, NULL, 16, 'Shipping', NULL, 'Index'),
(27, 23, '', 'Zahlungsarten', NULL, NULL, 'sprite-credit-cards', 0, 1, NULL, NULL, 'Payment', NULL, 'Index'),
(28, 23, '', 'eMail-Vorlagen', NULL, NULL, 'sprite-mail--pencil', 0, 1, NULL, 10, 'Mail', NULL, 'Index'),
(29, 23, '', 'Shopcache leeren', NULL, NULL, 'sprite-bin-full', -5, 1, NULL, NULL, 'Cache', NULL, 'Index'),
(30, NULL, '', 'Marketing', NULL, NULL, 'ico2 chart_bar01', 0, 1, NULL, NULL, 'Marketing', NULL, NULL),
(31, 69, '', 'Übersicht', NULL, NULL, 'sprite-report-paper', -5, 1, NULL, 13, 'Overview', NULL, 'Index'),
(32, 69, '', 'Statistiken / Diagramme', NULL, NULL, 'sprite-chart', -4, 1, NULL, 26, 'Analytics', NULL, 'Index'),
(40, NULL, '', '', NULL, NULL, 'ico question_frame', 0, 1, NULL, NULL, NULL, NULL, NULL),
(41, 114, '', 'Onlinehilfe aufrufen', 'window.open(''http://www.shopware.de/wiki'',''Shopware'',''width=800,height=550,scrollbars=yes'')', NULL, 'sprite-lifebuoy', 0, 1, NULL, NULL, 'Onlinehelp', NULL, NULL),
(44, 40, '', 'Über Shopware', 'createShopwareVersionMessage()', NULL, 'sprite-shopware-logo', 2, 1, NULL, NULL, 'AboutShopware', NULL, 'Index'),
(46, 7, '', 'Import/Export', '', NULL, 'sprite-arrow-circle-double-135', 3, 1, NULL, 34, 'ImportExport', NULL, 'Index'),
(50, 1, '', 'Bewertungen', NULL, NULL, 'sprite-balloon', 3, 1, NULL, 9, 'Vote', NULL, 'Index'),
(56, 30, '', 'Partnerprogramm', '', NULL, 'sprite-xfn-colleague', 6, 1, NULL, 22, 'Partner', NULL, 'Index'),
(57, 7, '', 'Formulare', NULL, NULL, 'sprite-application-form', 2, 1, NULL, NULL, 'Form', NULL, 'Index'),
(58, 30, '', 'Newsletter', '', NULL, 'sprite-paper-plane', 7, 1, NULL, 32, 'NewsletterManager', NULL, 'Index'),
(59, 69, '', 'Abbruch-Analyse', '', NULL, 'sprite-chart-down-color', 0, 1, NULL, 25, 'CanceledOrder', NULL, 'Index'),
(62, 23, '', 'Riskmanagement', '', NULL, 'sprite-funnel--exclamation', 0, 1, NULL, NULL, 'RiskManagement', NULL, 'Index'),
(63, 23, '', 'Systeminfo', NULL, NULL, 'sprite-blueprint', -3, 1, 40, 19, 'Systeminfo', NULL, 'Index'),
(64, 7, '', 'Medienverwaltung', NULL, NULL, 'sprite-inbox-image', 4, 1, NULL, 27, 'MediaManager', NULL, 'Index'),
(65, 20, '', 'Zahlungen', NULL, NULL, 'sprite-money-coin', 0, 1, NULL, NULL, 'Payments', NULL, NULL),
(66, 1, '', 'Übersicht', '', NULL, 'sprite-ui-scroll-pane-list', -2, 1, NULL, 13, 'ArticleList', 'STRG + ALT + O', 'Index'),
(68, 23, '', 'Logfile', '', NULL, 'sprite-cards-stack', -2, 1, NULL, NULL, 'Log', NULL, 'Index'),
(69, 30, '', 'Auswertungen', NULL, NULL, 'sprite-chart', -1, 1, NULL, NULL, 'AnalysisMenu', NULL, NULL),
(72, 1, '', 'Eigenschaften', '', NULL, 'sprite-property-blue', 0, 1, NULL, NULL, 'Property', NULL, 'Index'),
(75, 20, '', 'Anlegen', '', NULL, 'sprite-user--plus', -1, 1, NULL, NULL, 'Customer', NULL, 'Detail'),
(84, 69, '', 'E-Mail Benachrichtigung', '', NULL, 'sprite-mail-forward', 4, 1, NULL, 24, 'Notification', NULL, 'Index'),
(85, 7, '', 'Blog', '', NULL, 'sprite-application-blog', 1, 1, NULL, 28, 'Blog', NULL, 'Index'),
(88, 114, '', 'Zum Forum', 'window.open(''http://www.shopware-community.de'',''Shopware'',''width=800,height=550,scrollbars=yes'')', NULL, 'sprite-balloons-box', -1, 1, NULL, NULL, 'Forum', NULL, NULL),
(91, 29, '', 'Textbausteine + Template', NULL, NULL, 'sprite-edit-shade', 1, 1, NULL, NULL, 'Cache', 'STRG + ALT + T', 'Template'),
(97, 29, '', 'Artikel + Kategorien', NULL, NULL, 'sprite-gear', 1, 1, NULL, NULL, 'Cache', 'STRG + ALT + X', 'Config'),
(98, 29, '', 'Konfiguration', NULL, NULL, 'sprite-blue-folders-stack', 1, 1, NULL, NULL, 'Cache', 'STRG + ALT + F', 'Frontend'),
(107, 23, '', 'Textbausteine', NULL, NULL, 'sprite-edit-shade', 0, 1, NULL, NULL, 'Snippet', NULL, 'Index'),
(109, 40, '', 'Tastaturk&uuml;rzel', 'createKeyNavOverlay()', NULL, 'sprite-keyboard-command', 1, 1, NULL, NULL, 'ShortCutMenu', NULL, 'Index'),
(110, 23, '', 'Grundeinstellungen', NULL, NULL, 'sprite-wrench-screwdriver', -5, 1, NULL, NULL, 'Config', NULL, 'Index'),
(114, 40, '', 'Hilfe', NULL, NULL, 'sprite-lifebuoy', 0, 1, NULL, NULL, 'HelpMenu', NULL, NULL),
(115, 40, '', 'Feedback senden', NULL, NULL, 'sprite-briefcase--arrow', 0, 1, NULL, NULL, 'BetaFeedback', NULL, 'Index'),
(116, 23, '', 'Plugin Manager', NULL, NULL, 'sprite-application-block', 0, 1, 46, 36, 'PluginManager', NULL, 'Index'),
(117, 29, '', 'Proxy/Model-Cache', NULL, NULL, 'sprite-gear', 1, 1, NULL, NULL, 'Cache', NULL, 'Proxy');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_multilanguage`
--

DROP TABLE IF EXISTS `s_core_multilanguage`;
CREATE TABLE IF NOT EXISTS `s_core_multilanguage` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `mainID` int(11) unsigned DEFAULT NULL,
  `isocode` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  `locale` int(11) unsigned NOT NULL,
  `parentID` int(11) unsigned NOT NULL,
  `flagstorefront` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `flagbackend` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `skipbackend` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `defaultcustomergroup` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `doc_template` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0/de/forms',
  `separate_numbers` tinyint(4) NOT NULL,
  `domainaliase` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `defaultcurrency` int(11) NOT NULL,
  `default` int(1) NOT NULL,
  `switchCurrencies` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `switchLanguages` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `scoped_registration` int(1) DEFAULT NULL,
  `fallback` int(11) unsigned DEFAULT NULL,
  `navigation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_multilanguage`
--

INSERT INTO `s_core_multilanguage` (`id`, `mainID`, `isocode`, `locale`, `parentID`, `flagstorefront`, `flagbackend`, `skipbackend`, `name`, `defaultcustomergroup`, `template`, `doc_template`, `separate_numbers`, `domainaliase`, `defaultcurrency`, `default`, `switchCurrencies`, `switchLanguages`, `scoped_registration`, `fallback`, `navigation`) VALUES
(1, NULL, '1', 1, 3, '', 'de.png', 1, 'Deutsch', 'EK', 'orange', 'orange', 0, '', 1, 1, '1|2', '1|6', NULL, 0, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_optin`
--

DROP TABLE IF EXISTS `s_core_optin`;
CREATE TABLE IF NOT EXISTS `s_core_optin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans`
--

DROP TABLE IF EXISTS `s_core_paymentmeans`;
CREATE TABLE IF NOT EXISTS `s_core_paymentmeans` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `s_core_paymentmeans`
--

INSERT INTO `s_core_paymentmeans` (`id`, `name`, `description`, `template`, `class`, `table`, `hide`, `additionaldescription`, `debit_percent`, `surcharge`, `surchargestring`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`) VALUES
(2, 'debit', 'Lastschrift', 'debit.tpl', 'debit.php', 's_user_debit', 0, 'Zusatztext', 0, 0, '', 4, 1, 0, '', 0, NULL, NULL, NULL),
(3, 'cash', 'Nachnahme', 'cash.tpl', 'cash.php', '', 0, '(zzgl. 2,00 Euro Nachnahmegebühren)', 0, 0, '', 2, 1, 0, '', 0, NULL, NULL, NULL),
(4, 'invoice', 'Rechnung', 'invoice.tpl', 'invoice.php', '', 0, 'Sie zahlen einfach und bequem auf Rechnung. Shopware bietet z.B. auch die Möglichkeit, Rechnung automatisiert erst ab der 2. Bestellung für Kunden zur Verfügung zu stellen, um Zahlungsausfälle zu vermeiden.', 0, 0, '', 3, 1, 1, '', 0, NULL, NULL, NULL),
(5, 'prepayment', 'Vorkasse', 'prepayment.tpl', 'prepayment.php', '', 0, 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.', 0, 0, '', 1, 1, 0, '', 0, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans_attributes`
--

DROP TABLE IF EXISTS `s_core_paymentmeans_attributes`;
CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentmeanID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `paymentmeanID` (`paymentmeanID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans_countries`
--

DROP TABLE IF EXISTS `s_core_paymentmeans_countries`;
CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_countries` (
  `paymentID` int(11) unsigned NOT NULL,
  `countryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans_subshops`
--

DROP TABLE IF EXISTS `s_core_paymentmeans_subshops`;
CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_subshops` (
  `paymentID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`subshopID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugins`
--

DROP TABLE IF EXISTS `s_core_plugins`;
CREATE TABLE IF NOT EXISTS `s_core_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `source` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci,
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=47 ;

--
-- Daten für Tabelle `s_core_plugins`
--

INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(1, 'Core', 'Log', 'Log', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(2, 'Core', 'ErrorHandler', 'ErrorHandler', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(3, 'Core', 'Debug', 'Debug', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(4, 'Core', 'BenchmarkEvents', 'BenchmarkEvents', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(5, 'Core', 'Benchmark', 'Benchmark', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(7, 'Core', 'Cron', 'Cron', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(8, 'Core', 'Router', 'Router', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(9, 'Core', 'CronBirthday', 'CronBirthday', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(10, 'Core', 'System', 'System', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(11, 'Core', 'ViewportForward', 'ViewportForward', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(12, 'Core', 'Shop', 'Shop', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(13, 'Core', 'PostFilter', 'PostFilter', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(14, 'Core', 'CronRating', 'CronRating', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(15, 'Core', 'ControllerBase', 'ControllerBase', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(16, 'Core', 'CronStock', 'CronStock', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(17, 'Core', 'Api', 'Api', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(18, 'Core', 'License', 'License', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(19, 'Frontend', 'RouterRewrite', 'RouterRewrite', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(20, 'Frontend', 'Compare', 'Compare', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(21, 'Frontend', 'Facebook', 'Facebook', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(22, 'Frontend', 'Seo', 'Seo', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(23, 'Frontend', 'LastArticles', 'LastArticles', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(24, 'Frontend', 'RouterOld', 'RouterOld', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(26, 'Frontend', 'Google', 'Google', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(29, 'Frontend', 'AdvancedMenu', 'AdvancedMenu', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(31, 'Frontend', 'Statistics', 'Statistics', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(33, 'Frontend', 'Notification', 'Notification', 'Default', '', '', 0, '2012-08-28 00:00:00', NULL, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(34, 'Frontend', 'TagCloud', 'TagCloud', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(35, 'Frontend', 'InputFilter', 'InputFilter', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(36, 'Backend', 'Auth', 'Auth', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(37, 'Backend', 'Menu', 'Menu', 'Default', '', '', 1, '2012-08-28 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(39, 'Frontend', 'Payment', 'Payment', 'Default', '', '', 1, '2012-08-28 00:00:00', '2011-05-11 14:06:17', '2011-05-11 14:06:17', NULL, 'shopware AG', 'Copyright ? 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(40, 'Backend', 'Check', 'Systeminfo', 'Default', '', '', 1, '2010-10-18 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', NULL, 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(43, 'Backend', 'Locale', 'Locale', 'Default', '', '', 1, '2012-08-27 22:28:53', '2012-08-27 22:28:53', '2012-08-27 22:28:53', NULL, 'shopware AG', 'Copyright &copy; 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(44, 'Core', 'RestApi', 'RestApi', 'Default', '', '', 1, '2012-07-13 12:03:13', '2012-07-13 12:03:36', '2012-07-13 12:03:36', NULL, 'shopware AG', 'Copyright © 2012, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/', NULL, NULL, 0, 0, 0, NULL, NULL),
(45, 'Backend', 'StoreApi', 'StoreApi', 'Default', NULL, NULL, 1, '2012-08-19 14:34:36', '2012-08-19 14:34:46', '2012-08-19 14:34:46', '2012-08-21 09:59:19', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, NULL),
(46, 'Core', 'PluginManager', 'PluginManager', 'Default', NULL, NULL, 1, '2012-08-19 14:34:36', '2012-08-19 14:34:46', '2012-08-19 14:34:59', '2012-08-21 09:59:19', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugin_configs`
--

DROP TABLE IF EXISTS `s_core_plugin_configs`;
CREATE TABLE IF NOT EXISTS `s_core_plugin_configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`pluginID`,`localeID`,`shopID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=15 ;

--
-- Daten für Tabelle `s_core_plugin_configs`
--

INSERT INTO `s_core_plugin_configs` (`id`, `name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
(1, 'show', 's:1:"1";', 23, 1, 1),
(2, 'controller', 's:14:"index, listing";', 34, 1, 1),
(3, 'show', 's:1:"1";', 34, 1, 1),
(4, 'sql_protection', 's:1:"1";', 35, 1, 1),
(5, 'sql_regex', 's:134:"s_core|s_order|benchmark.*\\(|insert.+into|update.+set|delete.+from|select.+from|drop.+(?:table|database)|truncate.+table|union.+select";', 35, 1, 1),
(6, 'xss_protection', 's:1:"1";', 35, 1, 1),
(7, 'xss_regex', 's:42:"javascript:|src\\s*=|on[a-z]+\\s*=|style\\s*=";', 35, 1, 1),
(8, 'rfi_protection', 's:1:"1";', 35, 1, 1),
(9, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 35, 1, 1),
(10, 'locales', 's:11:"de_DE,en_GB";', 43, 1, 1),
(11, 'cacheControllers', 's:201:"frontend/listing 300\nfrontend/index 300\nfrontend/detail 300\nfrontend/campaign 600\nwidgets/listing 300\nfrontend/custom 600\nfrontend/sitemap 600\nwidgets/index 300\nwidgets/checkout 300\nwidgets/compare 30\n";', 42, 1, 1),
(12, 'noCacheControllers', 's:98:"frontend/checkout checkout\nfrontend/note checkout\nfrontend/detail detail\nfrontend/compare compare\n";', 42, 1, 1),
(13, 'proxy', 's:0:"";', 42, 1, 1),
(14, 'admin', 's:1:"1";', 42, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugin_elements`
--

DROP TABLE IF EXISTS `s_core_plugin_elements`;
CREATE TABLE IF NOT EXISTS `s_core_plugin_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pluginID` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `order` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `filters` mediumtext COLLATE utf8_unicode_ci,
  `validators` mediumtext COLLATE utf8_unicode_ci,
  `options` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pluginID` (`pluginID`,`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `s_core_plugin_elements`
--

INSERT INTO `s_core_plugin_elements` (`id`, `pluginID`, `name`, `value`, `label`, `description`, `type`, `required`, `order`, `scope`, `filters`, `validators`, `options`) VALUES
(1, 1, 'logDb', 'i:1;', 'Fehler in Datenbank schreiben', '', 'Checkbox', 0, 0, 0, NULL, NULL, ''),
(2, 1, 'logMail', 's:1:"0";', 'Fehler an Shopbetreiber senden', '', 'Checkbox', 0, 0, 0, NULL, NULL, ''),
(3, 34, 'show', 'i:1;', 'Tag-Cloud anzeigen', '', 'Checkbox', 0, 0, 1, NULL, NULL, ''),
(4, 34, 'controller', 's:14:"index, listing";', 'Controller-Auswahl', '', 'Text', 0, 0, 1, NULL, NULL, ''),
(5, 35, 'sql_protection', 'i:1;', 'SQL-Injection-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL, ''),
(6, 35, 'sql_regex', 's:134:"s_core|s_order|benchmark.*\\(|insert.+into|update.+set|delete.+from|select.+from|drop.+(?:table|database)|truncate.+table|union.+select";', 'SQL-Injection-Filter', '', 'Text', 0, 0, 0, NULL, NULL, ''),
(7, 35, 'xss_protection', 'i:1;', 'XSS-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL, ''),
(8, 35, 'xss_regex', 's:42:"javascript:|src\\s*=|on[a-z]+\\s*=|style\\s*=";', 'XSS-Filter', '', 'Text', 0, 0, 0, NULL, NULL, ''),
(9, 23, 'show', 'i:1;', 'Artikelverlauf anzeigen', '', 'Checkbox', 0, 0, 1, NULL, NULL, ''),
(10, 35, 'rfi_protection', 'i:1;', 'RemoteFileInclusion-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL, ''),
(11, 35, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 'RemoteFileInclusion-Filter', '', 'Text', 0, 0, 0, NULL, NULL, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_pricegroups`
--

DROP TABLE IF EXISTS `s_core_pricegroups`;
CREATE TABLE IF NOT EXISTS `s_core_pricegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_pricegroups`
--

INSERT INTO `s_core_pricegroups` (`id`, `description`) VALUES
(1, 'Standard');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_pricegroups_discounts`
--

DROP TABLE IF EXISTS `s_core_pricegroups_discounts`;
CREATE TABLE IF NOT EXISTS `s_core_pricegroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  `discount` double NOT NULL,
  `discountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`customergroupID`,`discountstart`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rewrite`
--

DROP TABLE IF EXISTS `s_core_rewrite`;
CREATE TABLE IF NOT EXISTS `s_core_rewrite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `replace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rewrite_urls`
--

DROP TABLE IF EXISTS `s_core_rewrite_urls`;
CREATE TABLE IF NOT EXISTS `s_core_rewrite_urls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `main` int(1) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`,`subshopID`),
  KEY `org_path` (`org_path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rulesets`
--

DROP TABLE IF EXISTS `s_core_rulesets`;
CREATE TABLE IF NOT EXISTS `s_core_rulesets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentID` int(11) NOT NULL,
  `rule1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value1` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `rule2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value2` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_sessions`
--

DROP TABLE IF EXISTS `s_core_sessions`;
CREATE TABLE IF NOT EXISTS `s_core_sessions` (
  `id` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `expireref` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `data` longtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `expiry` (`expiry`),
  KEY `expireref` (`expireref`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_shops`
--

DROP TABLE IF EXISTS `s_core_shops`;
CREATE TABLE IF NOT EXISTS `s_core_shops` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `main_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `position` int(11) NOT NULL,
  `host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `hosts` text COLLATE utf8_unicode_ci NOT NULL,
  `secure` int(1) unsigned NOT NULL,
  `secure_host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `secure_base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_core_shops`
--

INSERT INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `hosts`, `secure`, `secure_host`, `secure_base_path`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
(1, NULL, 'Deutsch', NULL, 0, NULL, '', '', 0, NULL, NULL, 11, 4, 3, 1, 1, 1, NULL, 0, 1, 1),
(2, 1, 'Englisch', '', 0, NULL, NULL, '', 0, NULL, NULL, NULL, NULL, 4, 2, 1, 1, NULL, 0, 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_shop_currencies`
--

DROP TABLE IF EXISTS `s_core_shop_currencies`;
CREATE TABLE IF NOT EXISTS `s_core_shop_currencies` (
  `shop_id` int(11) unsigned NOT NULL,
  `currency_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_core_shop_currencies`
--

INSERT INTO `s_core_shop_currencies` (`shop_id`, `currency_id`) VALUES
(1, 1),
(1, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_shop_pages`
--

DROP TABLE IF EXISTS `s_core_shop_pages`;
CREATE TABLE IF NOT EXISTS `s_core_shop_pages` (
  `shop_id` int(11) unsigned NOT NULL,
  `group_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`shop_id`,`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_snippets`
--

DROP TABLE IF EXISTS `s_core_snippets`;
CREATE TABLE IF NOT EXISTS `s_core_snippets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`shopID`,`name`,`localeID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2587 ;

--
-- Daten für Tabelle `s_core_snippets`
--

INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1, 'backend/error/index', 1, 1, 'ErrorIndexTitle', 'Fehler', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(2, 'frontend/index/header', 1, 1, 'IndexMetaRobots', 'index,follow', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(3, 'frontend/index/header', 1, 1, 'IndexMetaRevisit', '15 days', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(6, 'frontend/index/index', 1, 1, 'IndexLinkDefault', 'zur Startseite wechseln', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(7, 'frontend/compare/index', 1, 1, 'CompareActionStart', 'Vergleich starten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(8, 'frontend/compare/index', 1, 1, 'CompareActionDelete', 'Vergleich löschen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(9, 'frontend/index/checkout_actions', 1, 1, 'IndexLinkCart', 'Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(10, 'frontend/index/checkout_actions', 1, 1, 'IndexInfoArticles', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(11, 'frontend/index/checkout_actions', 1, 1, 'IndexActionShowPositions', 'Positionen anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(12, 'frontend/account/content_right', 1, 1, 'AccountLinkOverview', 'Mein Konto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(13, 'frontend/checkout/finish', 1, 1, 'FinishTitleRightOfRevocation', 'Widerrufsrecht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(14, 'frontend/index/checkout_actions', 1, 1, 'IndexLinkNotepad', 'Merkzettel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(15, 'frontend/checkout/confirm', 1, 1, 'ConfirmErrorAGB', 'Bitte bestätigen Sie unsere AGB', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(17, 'frontend/blog/detail', 1, 1, 'BlogHeaderSocialmedia', 'Weiterempfehlen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(18, 'frontend/index/breadcrumb', 1, 1, 'BreadcrumbDefault', 'Sie sind hier:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(20, 'frontend/plugins/index/viewlast', 1, 1, 'WidgetsRecentlyViewedLinkDetails', 'Mehr Informationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(21, 'frontend/blog/box', 1, 1, 'BlogInfoFrom', 'Von:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(22, 'frontend/blog/box', 1, 1, 'BlogInfoComments', 'Kommentare', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(23, 'frontend/blog/box', 1, 1, 'BlogLinkMore', 'Mehr lesen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(25, 'frontend/index/footer', 1, 1, 'FooterInfoIncludeVat', '* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl. <span style="text-decoration: underline;"><a title="Versandkosten" href="{url controller=custom sCustom=6}">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben', '2010-01-01 00:00:00', '2010-10-07 23:31:10'),
(27, 'frontend/checkout/finish', 1, 1, 'FinishTextRightOfRevocation', 'Informationen zum Widerrufsrecht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(28, 'frontend/blog/index', 1, 1, 'BlogLinkRSS', 'RSS-Feed', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(29, 'frontend/blog/index', 1, 1, 'BlogLinkAtom', 'Atom-Feed', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(30, 'frontend/listing/listing_actions', 1, 1, 'ListingLabelSort', 'Sortierung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(31, 'frontend/listing/listing_actions', 1, 1, 'ListingSortRelease', 'Erscheinungsdatum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(32, 'frontend/listing/listing_actions', 1, 1, 'ListingSortRating', 'Beliebtheit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(33, 'frontend/listing/listing_actions', 1, 1, 'ListingSortPriceLowest', 'Niedrigster Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(34, 'frontend/listing/listing_actions', 1, 1, 'ListingSortPriceHighest', 'Höchster Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(35, 'frontend/listing/listing_actions', 1, 1, 'ListingSortName', 'Artikelbezeichnung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(36, 'frontend/listing/listing_actions', 1, 1, 'ListingLabelItemsPerPage', 'Artikel pro Seite:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(37, 'frontend/listing/listing_actions', 1, 1, 'ListingLabelView', 'Ansicht:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(38, 'frontend/listing/listing_actions', 1, 1, 'ListingViewTable', 'Liste', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(39, 'frontend/listing/listing_actions', 1, 1, 'ListingView2Cols', 'Zweispaltig', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(40, 'frontend/listing/listing_actions', 1, 1, 'ListingView3Cols', 'Dreispaltig', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(41, 'frontend/listing/listing_actions', 1, 1, 'ListingView4Cols', 'Vierspaltig', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(42, 'frontend/listing/box_article', 1, 1, 'ListingBoxNoPicture', 'Kein Bild vorhanden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(43, 'frontend/listing/box_article', 1, 1, 'ListingBoxLinkBuy', 'Jetzt bestellen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(44, 'frontend/listing/box_article', 1, 1, 'ListingBoxLinkDetails', 'Zum Produkt', '2010-01-01 00:00:00', '2010-10-16 16:47:26'),
(45, 'frontend/detail/navigation', 1, 1, 'DetailNavCount', 'von', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(46, 'frontend/detail/navigation', 1, 1, 'DetailNavIndex', 'Übersicht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(47, 'frontend/account/ajax_login', 1, 1, 'LoginActionNext', 'Einloggen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(48, 'frontend/detail/index', 1, 1, 'DetailFrom', 'von', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(50, 'frontend/detail/buy', 1, 1, 'DetailBuyLabelQuantity', 'Menge', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(51, 'frontend/detail/buy', 1, 1, 'DetailBuyActionAdd', 'In den Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(52, 'frontend/detail/actions', 1, 1, 'DetailLinkVoucher', 'Artikel weiterempfehlen', '2010-01-01 00:00:00', '2010-10-17 18:49:27'),
(53, 'frontend/detail/actions', 1, 1, 'DetailLinkReview', 'Bewertung schreiben', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(54, 'frontend/detail/actions', 1, 1, 'DetailLinkNotepad', 'Auf den Merkzettel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(55, 'frontend/detail/actions', 1, 1, 'DetailLinkContact', 'Fragen zum Artikel?', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(56, 'frontend/detail/tabs', 1, 1, 'DetailTabsDescription', 'Beschreibung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(57, 'frontend/detail/tabs', 1, 1, 'DetailTabsRating', 'Bewertungen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(58, 'frontend/detail/description', 1, 1, 'DetailDescriptionHeader', 'Produktinformationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(59, 'frontend/blog/detail', 1, 1, 'BlogHeaderLinks', 'Weitere Informationen zu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(60, 'frontend/detail/comment', 1, 1, 'DetailCommentHeader', 'Kundenbewertungen für', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(61, 'frontend/detail/comment', 1, 1, 'DetailCommentHeaderWriteReview', 'Bewertung schreiben', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(62, 'frontend/detail/comment', 1, 1, 'DetailCommentTextReview', 'Bewertungen werden nach Überprüfung freigeschaltet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(63, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelName', 'Ihr Name', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(64, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelSummary', 'Zusammenfassung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(65, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(66, 'frontend/detail/comment', 1, 1, 'Rate10', '10 sehr gut', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(67, 'frontend/detail/comment', 1, 1, 'Rate9', '9', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(68, 'frontend/detail/comment', 1, 1, 'Rate8', '8', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(69, 'frontend/detail/comment', 1, 1, 'Rate7', '7', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(70, 'frontend/detail/comment', 1, 1, 'Rate6', '6', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(71, 'frontend/detail/comment', 1, 1, 'Rate5', '5', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(72, 'frontend/detail/comment', 1, 1, 'Rate4', '4', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(73, 'frontend/detail/comment', 1, 1, 'Rate3', '3', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(74, 'frontend/detail/comment', 1, 1, 'Rate2', '2', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(75, 'frontend/detail/comment', 1, 1, 'Rate1', '1 sehr schlecht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(76, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelText', 'Ihre Meinung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(77, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelCaptcha', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(78, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoFields', 'Die mit einem * markierten Felder sind Pflichtfelder.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(79, 'frontend/detail/comment', 1, 1, 'DetailCommentActionSave', 'Speichern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(80, 'frontend/detail/similar', 1, 1, 'DetailSimilarHeader', 'Ähnliche Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(81, 'frontend/search/paging', 1, 1, 'ListingSortRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(82, 'frontend/search/paging', 1, 1, 'ListingSortPriceLowest', 'Niedrigster Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(83, 'frontend/search/paging', 1, 1, 'ListingSortPriceHighest', 'Höchster Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(84, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddHeader', 'Der Artikel wurde erfolgreich in den Warenkorb gelegt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(85, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLinkBack', 'Weiter shoppen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(86, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLinkCart', 'Warenkorb anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(87, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddHeaderCrossSelling', 'Diese Artikel könnten Ihnen gefallen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(88, 'frontend/checkout/ajax_amount', 1, 1, 'AjaxAmountInfoCountArticles', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(89, 'frontend/account/ajax_login', 1, 1, 'LoginHeader', 'Eine Online-Bestellung ist einfach', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(90, 'frontend/account/ajax_login', 1, 1, 'LoginLabelMail', 'Ihre eMail-Adresse:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(91, 'frontend/account/ajax_login', 1, 1, 'LoginLabelNew', 'Neuer Kunde', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(92, 'frontend/account/ajax_login', 1, 1, 'LoginLabelExisting', 'Ich bin bereits Kunde und mein Passwort lautet', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(93, 'frontend/account/ajax_login', 1, 1, 'LoginLinkLostPassword', 'Passwort vergessen?', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(94, 'frontend/account/ajax_login', 1, 1, 'LoginActionClose', 'Fenster schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(95, 'frontend/detail/navigation', 1, 1, 'DetailNavNext', 'Weiter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(96, 'frontend/listing/listing_actions', 1, 1, 'ListingTextFrom', 'von', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(97, 'frontend/listing/listing_actions', 1, 1, 'ListingTextSite', 'Seite', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(98, 'frontend/listing/listing_actions', 1, 1, 'ListingLinkNext', 'Nächste Seite', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(99, 'frontend/listing/box_article', 1, 1, 'ListingBoxArticleStartsAt', 'ab', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(101, 'frontend/widgets/compare/index', 1, 1, 'DetailActionLinkCompare', 'Artikel vergleichen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(102, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalHeadline', 'Ihre persönlichen Angaben', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(103, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalLabelType', 'Ich bin', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(104, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalLabelPrivate', 'Privatkunde', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(105, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalLabelBusiness', 'Firma', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(106, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelSalutation', 'Anrede*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(107, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(108, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(109, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelFirstname', 'Vorname*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(110, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelLastname', 'Nachname*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(111, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelNoAccount', 'Kein Kundenkonto anlegen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(112, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelMail', 'Ihre eMail-Adresse*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(113, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelMailConfirmation', 'Wiederholen Sie Ihre eMail-Adresse*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(114, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelPassword', 'Ihr Passwort*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(115, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelPasswordRepeat', 'Wiederholen Sie Ihr Passwort*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(116, 'frontend/register/personal_fieldset', 1, 1, 'RegisterInfoPassword', 'Ihr Passwort muss mindestens ', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(117, 'frontend/register/personal_fieldset', 1, 1, 'RegisterInfoPasswordCharacters', 'Zeichen umfassen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(118, 'frontend/register/personal_fieldset', 1, 1, 'RegisterInfoPassword2', 'Berücksichtigen Sie Groß- und Kleinschreibung.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(119, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelPhone', 'Telefon*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(120, 'frontend/register/personal_fieldset', 1, 1, 'RegisterLabelBirthday', 'Geburtsdatum:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(121, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoNotAvailable', 'Diese Auswahl steht nicht zur Verfügung!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(122, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoShippingfree', 'Versandkostenfreie Lieferung!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(123, 'frontend/detail/data', 1, 1, 'DetailDataInfoArticleStartsAt', 'ab', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(124, 'frontend/blog/filter', 1, 1, 'BlogHeaderFilterProperties', 'Filter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(125, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingHeadline', 'Ihre Adresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(126, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingLabelStreet', 'Straße und Nr*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(127, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingLabelCity', 'PLZ und Ort*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(128, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingLabelCountry', 'Land*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(129, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingLabelSelect', 'Bitte wählen...', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(130, 'frontend/register/billing_fieldset', 1, 1, 'RegisterBillingLabelShipping', 'Die <strong>Lieferadresse</strong> weicht von der Rechnungsadresse ab.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(131, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingHeadline', 'Ihre abweichende Lieferadresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(132, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelSalutation', 'Anrede*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(133, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelCompany', 'Firma:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(134, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelDepartment', 'Abteilung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(135, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelFirstname', 'Vorname*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(136, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelLastname', 'Nachname*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(137, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelStreet', 'Straße und Nr*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(138, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelCity', 'PLZ und Ort*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(139, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelCountry', 'Land*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(140, 'frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelSelect', 'Bitte wählen...', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(141, 'frontend/register/payment_fieldset', 1, 1, 'RegisterPaymentHeadline', 'Bitte wählen Sie die von Ihnen bevorzugte Zahlungsart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(142, 'frontend/plugins/payment/debit', 1, 1, 'PaymentDebitLabelAccount', 'Kontonummer*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(143, 'frontend/plugins/payment/debit', 1, 1, 'PaymentDebitLabelBankcode', 'Bankleitzahl*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(144, 'frontend/plugins/payment/debit', 1, 1, 'PaymentDebitLabelBankname', 'Ihre Bank*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(145, 'frontend/plugins/payment/debit', 1, 1, 'PaymentDebitLabelName', 'Konto-Inhaber*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(146, 'frontend/plugins/payment/debit', 1, 1, 'PaymentDebitInfoFields', 'Die mit einem * markierten Felder sind Pflichtfelder.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(147, 'frontend/register/index', 1, 1, 'RegisterInfoAdvantages', '<h2>Meine Vorteile</h2>\r\n<ul>\r\n<li>Schnelleres einkaufen</li>\r\n<li>Speichern Sie Ihre Benutzerdaten und Einstellungen</li>\r\n<li>Einblick in Ihre Bestellungen inkl. Sendungsauskunft</li>\r\n<li>Verwalten Sie Ihr Newsletter-Abo</li>\r\n</ul>', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(148, 'frontend/register/error_message', 1, 1, 'RegisterErrorHeadline', 'Ein Fehler ist aufgetreten!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(149, 'frontend/detail/navigation', 1, 1, 'DetailNavPrevious', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(150, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoFillOutFields', 'Bitte füllen Sie alle rot markierten Felder aus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(151, 'frontend/listing/filter_supplier', 1, 1, 'FilterSupplierHeadline', 'Hersteller', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(152, 'frontend/listing/listing', 1, 1, 'ListingInfoFilterSupplier', 'Produkte von', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(153, 'frontend/listing/listing', 1, 1, 'ListingLinkAllSuppliers', 'Alle Hersteller anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(154, 'frontend/custom/right.tpl', 1, 1, 'CustomHeader', 'Direkter Kontakt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(155, 'frontend/forms/index', 1, 1, 'FormsTextContact', '<strong>Demoshop<br />\r\n</strong><br />\r\nFügen Sie hier Ihre Kontaktdaten ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(156, 'frontend/checkout/shipping_costs', 1, 1, 'ShippingHeader', 'Versandkostenberechnung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(157, 'frontend/checkout/shipping_costs', 1, 1, 'ShippingLabelDeliveryCountry', '1. Lieferland:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(158, 'frontend/checkout/shipping_costs', 1, 1, 'ShippingLabelPayment', '2. Zahlungsart:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(159, 'frontend/checkout/shipping_costs', 1, 1, 'ShipppingLabelDispatch', '3. Versandart:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(160, 'frontend/account/shipping', 1, 1, 'ShippingLinkSend', 'Ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(161, 'frontend/checkout/actions', 1, 1, 'CheckoutActionsLinkProceed', 'Zur Kasse gehen!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(162, 'frontend/index/checkout_actions', 1, 1, 'IndexLinkCheckout', 'Zur Kasse', '2010-01-01 00:00:00', '2010-10-15 17:02:04'),
(163, 'frontend/blog/detail', 1, 1, 'BlogHeaderCrossSelling', 'Passende Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(164, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmHeaderBilling', 'Rechnungsadresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(165, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmSalutationMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(166, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmLinkChangeBilling', 'Ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(167, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmLinkSelectBilling', 'Andere', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(168, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmHeaderShipping', 'Lieferadresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(169, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmLinkChangeShipping', 'Ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(170, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmLinkSelectShipping', 'Andere', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(171, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmHeaderPayment', 'Gewählte Zahlungsart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(172, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmInfoInstantDownload', 'Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(173, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmLinkChangePayment', 'Ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(174, 'frontend/account/billing', 1, 1, 'BillingLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(175, 'frontend/account/content_right', 1, 1, 'AccountHeaderNavigation', 'Mein Konto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(176, 'frontend/ticket/listing', 1, 1, 'TicketTitle', 'Ticketsystem', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(177, 'frontend/account/content_right', 1, 1, 'AccountLinkPreviousOrders', 'Meine Bestellungen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(178, 'frontend/account/content_right', 1, 1, 'AccountLinkDownloads', 'Meine Sofortdownloads', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(179, 'frontend/account/content_right', 1, 1, 'AccountLinkBillingAddress', 'Rechnungsadresse ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(180, 'frontend/account/content_right', 1, 1, 'AccountLinkShippingAddress', 'Lieferadresse ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(181, 'frontend/account/content_right', 1, 1, 'AccountLinkPayment', 'Zahlungsart ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(182, 'frontend/account/content_right', 1, 1, 'AccountLinkNotepad', 'Merkzettel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(183, 'frontend/account/content_right', 1, 1, 'AccountLinkLogout', 'Abmelden Logout', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(184, 'frontend/account/index', 1, 1, 'AccountHeaderWelcome', 'Willkommen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(185, 'frontend/account/index', 1, 1, 'AccountHeaderInfo', 'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(186, 'frontend/account/success_messages', 1, 1, 'AccountAccountSuccess', 'Zugangsdaten wurden erfolgreich gespeichert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(187, 'frontend/account/index', 1, 1, 'AccountHeaderBasic', 'Benutzerinformationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(188, 'frontend/account/index', 1, 1, 'AccountLinkChangePassword', 'Passwort ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(189, 'frontend/account/index', 1, 1, 'AccountLinkChangePayment', 'Zahlungsart ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(190, 'frontend/account/index', 1, 1, 'AccountLabelNewPassword', 'Neues Passwort*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(191, 'frontend/account/index', 1, 1, 'AccountLabelRepeatPassword', 'Passwort-Wiederholung*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(192, 'frontend/account/index', 1, 1, 'AccountHeaderNewsletter', 'Ihre Newslettereinstellungen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(193, 'frontend/account/index', 1, 1, 'AccountLabelWantNewsletter', 'Ja, ich möchte den kostenlosen {$sShopname} Newsletter erhalten. Sie können sich jederzeit wieder abmelden!', '2010-01-01 00:00:00', '2010-10-15 13:25:20'),
(194, 'frontend/account/success_messages', 1, 1, 'AccountPaymentSuccess', 'Ihre Zahlungsweise wurde erfolgreich gespeichert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(195, 'frontend/account/index', 1, 1, 'AccountHeaderPrimaryBilling', 'Primäre Rechnungsadresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(196, 'frontend/account/index', 1, 1, 'AccountLinkSelectBilling', 'Andere wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(197, 'frontend/account/index', 1, 1, 'AccountHeaderPrimaryShipping', 'Primäre Lieferadresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(198, 'frontend/account/index', 1, 1, 'AccountLinkSelectShipping', 'Andere wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(199, 'frontend/account/orders', 1, 1, 'OrdersHeader', 'Bestellungen nach Datum sortiert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(200, 'frontend/account/downloads', 1, 1, 'DownloadsColumnDate', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(201, 'frontend/account/orders', 1, 1, 'OrderColumnId', 'Bestellnummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(202, 'frontend/account/orders', 1, 1, 'OrderColumnDispatch', 'Versandart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(203, 'frontend/account/orders', 1, 1, 'OrderColumnStatus', 'Bestellstatus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(204, 'frontend/account/orders', 1, 1, 'OrderColumnActions', 'Aktionen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(205, 'frontend/account/order_item', 1, 1, 'OrderItemInfoNotProcessed', 'Bestellung wurde noch nicht bearbeitet', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(206, 'frontend/account/order_item', 1, 1, 'OrderActionSlide', 'Anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(207, 'frontend/account/downloads', 1, 1, 'DownloadsColumnName', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(208, 'frontend/account/order_item', 1, 1, 'OrderItemColumnQuantity', 'Anzahl', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(209, 'frontend/account/order_item', 1, 1, 'OrderItemColumnPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(210, 'frontend/account/order_item', 1, 1, 'OrderItemColumnTotal', 'Summe', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(211, 'frontend/account/order_item', 1, 1, 'OrderItemColumnDate', 'Vom:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(212, 'frontend/account/order_item', 1, 1, 'OrderItemColumnId', 'Bestellnummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(213, 'frontend/account/order_item', 1, 1, 'OrderItemColumnDispatch', 'Versandart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(214, 'frontend/account/order_item', 1, 1, 'OrderLinkRepeat', 'Bestellung wiederholen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(215, 'frontend/account/order_item', 1, 1, 'OrderItemShippingcosts', 'Versandkosten:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(216, 'frontend/account/order_item', 1, 1, 'OrderItemTotal', 'Gesamtsumme:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(217, 'frontend/account/downloads', 1, 1, 'DownloadsHeader', 'Ihre Sofortdownloads nach Datum sortiert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(218, 'frontend/note/index', 1, 1, 'NoteHeadline', 'Merkzettel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(219, 'frontend/note/index', 1, 1, 'NoteText', 'Speichern Sie hier Ihre pers&ouml;nlichen Favoriten - bis Sie das n&auml;chste Mal bei uns sind.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(220, 'frontend/note/index', 1, 1, 'NoteText2', 'Einfach den gewünschten Artikel auf die Merkliste setzen und {$sShopname} speichert für Sie automatisch Ihre persönliche Merkliste.\r\nSo können Sie bequem bei einem späteren Besuch Ihre vorgemerkten Artikel wieder abrufen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(221, 'frontend/note/index', 1, 1, 'NoteColumnName', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(222, 'frontend/note/index', 1, 1, 'NoteColumnPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(223, 'frontend/checkout/error_messages', 1, 1, 'ConfirmInfoPaymentNotCompatibleWithESD', 'Diese Zahlungsart steht für Sofortdownloads nicht zur Verfügung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(224, 'frontend/checkout/cart', 1, 1, 'CartTitle', 'Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(225, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLabelQuantity', 'Menge', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(227, 'frontend/account/logout', 1, 1, 'LogoutInfoFinished', 'Sie wurden erfolgreich ausgeloggt!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(228, 'frontend/account/logout', 1, 1, 'LogoutLinkHomepage', 'zur Startseite wechseln', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(229, 'frontend/checkout/ajax_cart', 1, 1, 'AjaxCartInfoEmpty', 'Ihr Warenkorb ist leer', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(230, 'frontend/checkout/ajax_cart', 1, 1, 'AjaxCartLinkBasket', 'Warenkorb öffnen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(231, 'frontend/search/paging', 1, 1, 'ListingSortRelevance', 'Relevanz', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(232, 'frontend/search/paging', 1, 1, 'ListingLabelSort', 'Sortierung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(233, 'frontend/newsletter/index', 1, 1, 'sNewsletterOptionSubscribe', 'Newsletter abonnieren', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(234, 'frontend/newsletter/index', 1, 1, 'sNewsletterOptionUnsubscribe', 'Newsletter abbestellen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(235, 'frontend/newsletter/index', 1, 1, 'sNewsletterLabelMail', 'Ihre eMail-Adresse*:', '2010-01-01 00:00:00', '2010-10-15 13:18:53'),
(236, 'frontend/newsletter/index', 1, 1, 'sNewsletterInfo', 'Abonnieren Sie jetzt einfach unseren regelmäßig erscheinenden\r\nNewsletter und Sie werden stets als Erster über\r\nneue Artikel und Angebote informiert.<br />\r\nDer Newsletter ist natürlich jederzeit über einen Link in der\r\neMail oder dieser Seite wieder abbestellbar.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(237, 'frontend/newsletter/index', 1, 1, 'sNewsletterButton', 'Speichern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(238, 'frontend/listing/box_article', 1, 1, 'ListingBoxTip', 'TIPP!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(239, 'frontend/listing/box_article', 1, 1, 'ListingBoxInstantDownload', 'Download', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(240, 'frontend/detail/liveshopping/ticker/countdown', 1, 1, 'LiveTickerCurrentPrice', 'Aktueller Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(241, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeDays', 'Tage', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(242, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeHours', 'Stunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(243, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeMinutes', 'Minuten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(244, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeSeconds', 'Sekunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(245, 'frontend/register/steps', 1, 1, 'CheckoutStepBasketNumber', '1', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(247, 'frontend/search/fuzzy', 1, 1, 'SearchFuzzyHeadlineEmpty', 'Leider wurden zu "{$sRequests.sSearchOrginal}" keine Artikel gefunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(248, 'frontend/register/steps', 1, 1, 'CheckoutStepRegisterNumber', '2', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(250, 'frontend/index/index', 1, 1, 'IndexRealizedWith', 'Realisiert mit ', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(251, 'frontend/index/menu_left', 1, 1, 'MenuLeftHeading', 'Informationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(252, 'frontend/widgets/advanced_menu/index', 1, 1, 'IndexLinkHome', 'Home', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(253, 'frontend/widgets/compare/index', 1, 1, 'ListingBoxLinkCompare', 'Vergleichen', '2010-01-01 00:00:00', '2010-10-17 19:01:26'),
(256, 'frontend/listing/box_similar', 1, 1, 'SimilarBoxLinkCompare', 'Vergleichen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(257, 'frontend/tellafriend/index', 1, 1, 'TellAFriendHeadline', 'weiterempfehlen.', '2010-01-01 00:00:00', '2010-10-17 18:49:19'),
(258, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLabelName', 'Ihr Name', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(259, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLabelMail', 'Ihre eMail-Adresse*:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(260, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLabelFriendsMail', 'Empfänger eMail-Adresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(261, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLabelComment', 'Ihr Kommentar:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(262, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLabelCaptcha', 'Captcha', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(263, 'frontend/tellafriend/index', 1, 1, 'TellAFriendLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(264, 'frontend/tellafriend/index', 1, 1, 'TellAFriendActionSubmit', 'Senden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(265, 'frontend/forms/elements', 1, 1, 'SupportLabelCaptcha', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(266, 'frontend/forms/elements', 1, 1, 'SupportLabelInfoFields', 'Die mit einem * markierten Felder sind Pflichtfelder.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(267, 'frontend/forms/elements', 1, 1, 'SupportActionSubmit', 'Senden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(268, 'frontend/compare/index', 1, 1, 'CompareInfoCount', 'Artikel vergleichen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(269, 'frontend/compare/col_description', 1, 1, 'CompareColumnPicture', 'Bild', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(270, 'frontend/compare/col_description', 1, 1, 'CompareColumnName', 'Name', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(271, 'frontend/compare/col_description', 1, 1, 'CompareColumnRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(272, 'frontend/compare/col_description', 1, 1, 'CompareColumnDescription', 'Beschreibung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(273, 'frontend/compare/col_description', 1, 1, 'CompareColumnPrice', 'Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(274, 'frontend/compare/overlay', 1, 1, 'CompareActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(275, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoSuccess', 'Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(276, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoAverageRate', 'Durchschnittliche Kundenbewertung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(277, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoRating', 'aus {$sArticle.sVoteAverange.count} Kundenbewertungen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(278, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoFrom', 'Von:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(279, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddErrorHeader', 'Artikel konnte nicht in den Warenkorb gelegt werden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(280, 'frontend/detail/data', 1, 1, 'DetailDataInfoSavePercent', 'gespart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(282, 'frontend/detail/related', 1, 1, 'DetailRelatedHeader', 'Hierzu passende Artikel:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(283, 'frontend/account/index', 1, 1, 'AccountTitle', 'Kundenkonto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(284, 'frontend/note/item', 1, 1, 'NoteLinkDetails', 'Zum Produkt', '2010-01-01 00:00:00', '2010-10-16 16:47:40'),
(285, 'frontend/note/item', 1, 1, 'NoteLinkCompare', 'Vergleichen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(286, 'frontend/note/item', 1, 1, 'NoteInfoId', 'Bestellnummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(287, 'frontend/note/item', 1, 1, 'NoteLinkDelete', 'Löschen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(288, 'frontend/note/item', 1, 1, 'NoteLinkBuy', 'Kaufen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(289, 'frontend/detail/buy', 1, 1, 'DetailBuyValueSelect', 'Bitte wählen...', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(290, 'frontend/detail/data', 1, 1, 'DetailDataInfoContent', 'Inhalt:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(291, 'frontend/detail/data', 1, 1, 'DetailDataInfoBaseprice', 'Grundpreis:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(292, 'frontend/compare/added', 1, 1, 'CompareHeaderTitle', 'Artikel vergleichen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(293, 'frontend/compare/added', 1, 1, 'LoginActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(294, 'frontend/detail/article_config_upprice', 1, 1, 'DetailConfigActionSubmit', 'Jetzt aktualisieren', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(295, 'frontend/newsletter/listing', 1, 1, 'NewsletterListingLinkDetails', '[mehr]', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(296, 'frontend/newsletter/detail', 1, 1, 'NewsletterDetailLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(297, 'frontend/newsletter/detail', 1, 1, 'NewsletterDetailLinkNewWindow', 'Newsletter in neuem Fenster öffnen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(298, 'frontend/blog/detail', 1, 1, 'BlogInfoCategories', 'Kategoriezuordnung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(299, 'frontend/blog/detail', 1, 1, 'BlogLinkComments', 'Zu den Kommentaren des Artikels', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(300, 'frontend/blog/detail', 1, 1, 'BlogInfoComments', 'Kommentare', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(301, 'frontend/blog/detail', 1, 1, 'BlogHeaderRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(302, 'frontend/blog/box', 1, 1, 'BlogInfoRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(303, 'frontend/blog/detail', 1, 1, 'BlogInfoFrom', 'von', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(304, 'frontend/blog/bookmarks', 1, 1, 'BookmarkTwitter', 'Twitter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(305, 'frontend/blog/bookmarks', 1, 1, 'BookmarkFacebook', 'Facebook', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(306, 'frontend/blog/bookmarks', 1, 1, 'BookmarkDelicious', 'Delicious', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(307, 'frontend/blog/bookmarks', 1, 1, 'BookmarkDiggit', 'Diggit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(308, 'frontend/blog/comments', 1, 1, 'BlogHeaderWriteComment', 'Kommentar schreiben', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(309, 'frontend/blog/comments', 1, 1, 'BlogInfoFields', 'Die mit einem * markierten Felder sind Pflichtfelder.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(310, 'frontend/blog/comments', 1, 1, 'BlogLabelName', 'Ihr Name', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(311, 'frontend/blog/comments', 1, 1, 'BlogLabelMail', 'Ihre eMail-Adresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(312, 'frontend/blog/comments', 1, 1, 'BlogLabelRating', 'Bewertung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(313, 'frontend/blog/comments', 1, 1, 'rate10', '10 sehr gut', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(314, 'frontend/blog/comments', 1, 1, 'rate9', '9', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(315, 'frontend/blog/comments', 1, 1, 'rate8', '8', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(316, 'frontend/blog/comments', 1, 1, 'rate7', '7', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(317, 'frontend/blog/comments', 1, 1, 'rate6', '6', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(318, 'frontend/blog/comments', 1, 1, 'rate5', '5', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(319, 'frontend/blog/comments', 1, 1, 'rate4', '4', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(320, 'frontend/blog/comments', 1, 1, 'rate3', '3', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(321, 'frontend/blog/comments', 1, 1, 'rate2', '2', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(322, 'frontend/blog/comments', 1, 1, 'rate1', '1 sehr schlecht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(323, 'frontend/blog/comments', 1, 1, 'BlogLabelSummary', 'Zusammenfassung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(324, 'frontend/blog/comments', 1, 1, 'BlogLabelComment', 'Ihre Meinung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(325, 'frontend/blog/comments', 1, 1, 'BlogLabelCaptcha', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(326, 'frontend/blog/comments', 1, 1, 'BlogLinkSaveComment', 'Speichern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(327, 'frontend/checkout/cart', 1, 1, 'CartInfoFreeShipping', 'VERSANDKOSTENFREI', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(328, 'frontend/checkout/cart', 1, 1, 'CartInfoFreeShippingDifference', '- Bestellen Sie f&uuml;r weitere {$sShippingcostsDifference|currency} um Ihre Bestellung versandkostenfrei in {$sCountry.countryname} zu erhalten!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(329, 'frontend/checkout/cart_header', 1, 1, 'CartColumnName', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(330, 'frontend/checkout/cart_header', 1, 1, 'CartColumnAvailability', 'Verfügbarkeit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(331, 'frontend/checkout/cart_header', 1, 1, 'CartColumnPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(333, 'frontend/checkout/cart_item', 1, 1, 'CartItemLinkDelete', 'Löschen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(334, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterActionAddVoucher', 'Hinzufügen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(335, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterLabelAddArticle', 'Artikel hinzufügen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(337, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterActionAdd', 'Hinzufügen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(338, 'frontend/checkout/cart_footer', 1, 1, 'CartFooterSum', 'Summe', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(339, 'frontend/checkout/cart_footer', 1, 1, 'CartFooterShipping', 'Versandkosten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(340, 'frontend/checkout/cart_footer', 1, 1, 'CartFooterTotal', 'Gesamtsumme', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(341, 'frontend/checkout/actions', 1, 1, 'CheckoutActionsLinkLast', 'Weiter shoppen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(342, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeader', 'Bitte überprüfen Sie Ihre Bestellung nochmals, bevor Sie sie senden.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(343, 'frontend/checkout/confirm', 1, 1, 'ConfirmInfoChange', 'Rechnungsadresse, Lieferadresse und Zahlungsart k&ouml;nnen Sie jetzt noch &auml;ndern.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(344, 'frontend/checkout/confirm', 1, 1, 'ConfirmInfoPaymentData', '<strong>\r\nUnsere Bankverbindung:\r\n</strong>\r\nVolksbank Musterstadt\r\nBLZ:\r\nKto.-Nr.:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(345, 'frontend/checkout/confirm_header', 1, 1, 'CheckoutColumnTax', 'Enthaltene MwSt.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(346, 'frontend/checkout/confirm', 1, 1, 'ConfirmLabelComment', 'Kommentar:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(347, 'frontend/checkout/confirm', 1, 1, 'ConfirmTerms', 'Ich habe die <a href="{url controller=custom sCustom=4 forceSecure}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.', '2010-01-01 00:00:00', '2010-10-07 23:31:45'),
(348, 'frontend/checkout/confirm', 1, 1, 'ConfirmTextOrderDefault', 'Optionaler FreitextBei Zahlung per Bankeinzug oder per Kreditkarte erfolgt die Belastung Ihres Kontos fünf Tage nach Bestellung der Ware.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(349, 'frontend/checkout/confirm', 1, 1, 'ConfirmActionSubmit', 'Zahlungspflichtig bestellen', '2010-01-01 00:00:00', '2010-10-16 16:51:57'),
(350, 'frontend/account/password', 1, 1, 'PasswordHeader', 'Passwort vergessen? Hier können Sie ein neues Passwort anfordern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(351, 'frontend/account/password', 1, 1, 'PasswordLabelMail', 'Ihre eMail-Adresse:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(352, 'frontend/account/password', 1, 1, 'PasswordText', 'Wir senden Ihnen ein neues, zufällig generiertes Passwort. Dieses können Sie dann im Kundenbereich ändern.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(353, 'frontend/account/password', 1, 1, 'PasswordLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(354, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleHeader', 'Sparen Sie jetzt mit unseren Bundle-Angeboten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(355, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleActionAdd', 'In den Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(356, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPriceForAll', 'Preis für alle', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(357, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPriceInstead', 'Statt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(358, 'frontend/detail/bundle/box_bundle', 1, 1, 'BundleInfoPercent', '% Rabatt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(359, 'frontend/detail/description', 1, 1, 'DetailDescriptionHeaderDownloads', 'Verfügbare Downloads:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(360, 'frontend/detail/description', 1, 1, 'DetailDescriptionLinkDownload', 'Download', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(361, 'frontend/checkout/premiums', 1, 1, 'PremiumsHeader', 'Bitte wählen Sie zwischen den folgenden Prämien', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(362, 'frontend/newsletter/index', 1, 1, 'NewsletterTitle', 'Newsletter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(363, 'frontend/checkout/premiums', 1, 1, 'PremiumActionAdd', 'Prämie auswählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(365, 'frontend/checkout/actions', 1, 1, 'CheckoutActionsLinkOffer', 'Angebot anfordern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(366, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterHeadline', 'Newsletter abonnieren', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(367, 'frontend/account/login', 1, 1, 'LoginHeaderNew', 'Sie sind neu bei', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(368, 'frontend/account/login', 1, 1, 'LoginInfoNew', 'Kein Problem, eine Shopbestellung ist einfach und sicher. Die Anmeldung dauert nur wenige Augenblicke.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(369, 'frontend/account/login', 1, 1, 'LoginLinkRegister', 'Neuer Kunde', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(370, 'frontend/account/login', 1, 1, 'LoginHeaderExistingCustomer', 'Sie besitzen bereits ein Kundenkonto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(371, 'frontend/account/login', 1, 1, 'LoginHeaderFields', 'Einloggen mit Ihrer eMail-Adresse und Ihrem Passwort', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(372, 'frontend/account/login', 1, 1, 'LoginLabelMail', 'Ihre eMail-Adresse:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(373, 'frontend/account/login', 1, 1, 'LoginLabelPassword', 'Ihr Passwort:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(374, 'frontend/account/login', 1, 1, 'LoginLinkLogon', 'Anmelden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(375, 'frontend/account/login', 1, 1, 'LoginLinkLostPassword', 'Passwort vergessen?', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(376, 'frontend/index/checkout_actions', 1, 1, 'IndexLinkAccount', 'Mein Konto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(377, 'frontend/checkout/cart', 1, 1, 'CartInfoEmpty', 'Sie haben keine Artikel im Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(378, 'frontend/account/index', 1, 1, 'AccountLinkChangeBilling', 'Rechnungsadresse ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(379, 'frontend/account/index', 1, 1, 'AccountLinkChangeShipping', 'Lieferadresse ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(380, 'frontend/account/order_item', 1, 1, 'OrderItemColumnName', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(381, 'frontend/account/orders', 1, 1, 'OrderColumnDate', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(382, 'frontend/account/downloads', 1, 1, 'DownloadsColumnLink', 'Download', '2010-01-01 00:00:00', '2010-09-28 11:54:19');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(383, 'frontend/account/content_right', 1, 1, 'sTicketSysSupportManagement', 'Supportverwaltung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(384, 'frontend/account/downloads', 1, 1, 'DownloadsSerialnumber', 'Ihre Seriennummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(385, 'frontend/account/downloads', 1, 1, 'DownloadsLink', 'Downloaden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(386, 'frontend/account/downloads', 1, 1, 'DownloadsInfoAccessDenied', 'Dieser Download stehen Ihnen nicht zur Verfügung!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(387, 'frontend/account/downloads', 1, 1, 'DownloadsInfoNotFound', 'Keine Downloads verfügbar', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(388, 'frontend/account/downloads', 1, 1, 'DownloadsInfoEmpty', 'Sie haben noch keine Sofortdownloadartikel gekauft', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(389, 'frontend/account/index', 1, 1, 'AccountHeaderPayment', 'Gewählte Zahlungsart', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(390, 'frontend/account/index', 1, 1, 'AccountInfoInstantDownloads', 'Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(391, 'frontend/account/index', 1, 1, 'AccountSalutationMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(392, 'frontend/account/shipping', 1, 1, 'ShippingLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(393, 'frontend/account/select_shipping', 1, 1, 'SelectShippingHeader', 'Auswählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(394, 'frontend/account/select_address', 1, 1, 'SelectAddressSubmit', 'Auswählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(395, 'frontend/account/select_address', 1, 1, 'SelectAddressSalutationMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(396, 'frontend/account/select_shipping', 1, 1, 'SelectShippingLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(397, 'frontend/account/payment', 1, 1, 'PaymentLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(398, 'frontend/account/index', 1, 1, 'AccountSalutationMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(399, 'frontend/checkout/cart_header', 1, 1, 'CartColumnQuantity', 'Anzahl', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(400, 'frontend/checkout/cart_header', 1, 1, 'CartColumnTotal', 'Summe', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(401, 'frontend/plugins/trusted_shops/logo', 1, 1, 'WidgetsTrustedLogo', 'Trusted Shops Gütesiegel - Bitte hier Gültigkeit prüfen!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(402, 'frontend/plugins/trusted_shops/logo', 1, 1, 'WidgetsTrustedLogoText', '<a title="Mehr Informationen zu {config name=Shopname}" href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank"> {config name=Shopname} ist ein von Trusted Shops gepr&uuml;fter Onlineh&auml;ndler mit G&uuml;tesiegel und <a href="http://www.trustedshops.de/info/garantiebedingungen/" target="_blank">K&auml;uferschutz.</a> <a title="Mehr Informationen zu " href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank">Mehr...</a> </a>', '2010-01-01 00:00:00', '2010-10-17 19:02:20'),
(403, 'frontend/checkout/finish', 1, 1, 'FinishHeaderThankYou', 'Vielen Dank für Ihre Bestellung bei ', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(404, 'frontend/checkout/finish', 1, 1, 'FinishInfoConfirmationMail', 'Wir haben Ihnen eine Bestellbestätigung per eMail geschickt.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(405, 'frontend/checkout/finish', 1, 1, 'FinishInfoPrintOrder', 'Wir empfehlen die unten aufgeführte Bestellbestätigung auszudrucken.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(406, 'frontend/checkout/finish', 1, 1, 'FinishLinkPrint', 'Bestellbestätigung jetzt ausdrucken!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(407, 'frontend/checkout/finish', 1, 1, 'FinishHeaderItems', 'Informationen zu Ihrer Bestellung:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(408, 'frontend/checkout/finish', 1, 1, 'FinishInfoId', 'Bestellnummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(409, 'frontend/search/paging', 1, 1, 'ListingSortName', 'Bezeichnung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(410, 'frontend/account/order_item', 1, 1, 'OrderInfoNoDispatch', 'Nicht angegeben', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(411, 'frontend/account/order_item', 1, 1, 'OrderItemInfoInProgress', 'Bestellung ist in Bearbeitung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(412, 'frontend/account/order_item', 1, 1, 'OrderItemInfoShipped', 'Bestellung wurde verschickt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(413, 'frontend/account/order_item', 1, 1, 'OrderItemInfoPartiallyShipped', 'Bestellung wurde teilweise verschickt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(414, 'frontend/account/order_item', 1, 1, 'OrderItemInfoCanceled', 'Bestellung wurde storniert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(415, 'frontend/account/order_item', 1, 1, 'OrderItemInfoBundle', 'BUNDLE RABATT', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(416, 'frontend/account/order_item', 1, 1, 'OrderItemInfoInstantDownload', 'Jetzt downloaden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(417, 'frontend/account/order_item', 1, 1, 'OrderItemInfoFree', 'GRATIS', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(418, 'frontend/account/order_item', 1, 1, 'OrderItemColumnTracking', 'Paket-Tracking:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(419, 'frontend/account/order_item', 1, 1, 'OrderItemNetTotal', 'Gesamtsumme Netto:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(420, 'frontend/account/orders', 1, 1, 'OrdersInfoEmpty', 'Sie haben noch keine Bestellung durchgeführt.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(421, 'frontend/account/ajax_logout', 1, 1, 'AccountLogoutHeader', 'Logout erfolgreich!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(422, 'frontend/account/ajax_logout', 1, 1, 'AccountLogoutText', 'Sie wurden erfolgreich ausgeloggt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(423, 'frontend/account/password', 1, 1, 'PasswordInfoSuccess', 'Ihr neues Passwort wurde Ihnen zugeschickt', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(424, 'frontend/custom/right.tpl', 1, 1, 'CustomTextContact', '<strong>Demoshop<br />\r\n</strong><br />\r\nFügen Sie hier Ihre Kontaktdaten ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(425, 'frontend/checkout/cart_item', 1, 1, 'CartItemInfoFree', 'GRATIS!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(426, 'frontend/checkout/cart_item', 1, 1, 'CartItemInfoPremium', 'Als kleines Dankeschön, bekommen Sie diesen Artikel Gratis dazu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(427, 'frontend/checkout/cart_item', 1, 1, 'CartItemInfoBundle', 'BUNDLE RABATT', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(428, 'frontend/account/select_billing', 1, 1, 'SelectBillingLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(429, 'frontend/account/select_billing', 1, 1, 'SelectBillingHeader', 'Auswählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(430, 'frontend/account/select_billing', 1, 1, 'SelectBillingInfoEmpty', 'Nachdem Sie die erste Bestellung durchgef?hrt haben, k?nnen Sie hier auf vorherige Rechnungsadressen zugreifen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(431, 'frontend/account/select_shipping', 1, 1, 'SelectShippingInfoEmpty', 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Lieferadressen zugreifen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(432, 'frontend/blog/atom', 1, 1, 'BlogAtomFeedHeader', 'Blog / Atom Feed', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(433, 'frontend/blog/comments', 1, 1, 'BlogInfoFailureFields', 'Bitte füllen Sie alle rot markierten Felder aus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(434, 'frontend/blog/comments', 1, 1, 'BlogInfoSuccessOptin', 'Vielen Dank für die Abgabe Ihrer Bewertung! \r\n	Sie erhalten in wenigen Minuten eine Bestätigungsmail.\r\n	Bestätigen Sie den Link in dieser eMail um die Bewertung freizugeben.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(435, 'frontend/blog/comments', 1, 1, 'BlogInfoSuccess', 'Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(436, 'frontend/blog/detail', 1, 1, 'BlogHeaderDownloads', 'Verfügbare Downloads:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(437, 'frontend/blog/detail', 1, 1, 'BlogLinkDownload', 'Download', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(438, 'frontend/blog/detail', 1, 1, 'BlogInfoComment', 'Unser Kommentar zu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(439, 'frontend/blog/detail', 1, 1, 'BlogInfoTags', 'Tags', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(440, 'frontend/blog/filter', 1, 1, 'BlogHeaderFilterCategories', 'Kategorien', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(441, 'frontend/blog/filter', 1, 1, 'BlogHeaderFilterDate', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(442, 'frontend/blog/filter', 1, 1, 'BlogHeaderFilterAuthor', 'Autoren', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(443, 'frontend/blog/rss', 1, 1, 'BlogRssFeedHeader', 'Blog / RSS Feed', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(444, 'frontend/checkout/added', 1, 1, 'AddArticleLinkBack', 'Weiter shoppen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(445, 'frontend/checkout/ajax_cart', 1, 1, 'AjaxCartInfoBundle', 'BUNDLE RABATT', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(446, 'frontend/checkout/ajax_cart', 1, 1, 'AjaxCartInfoFree', 'GRATIS!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(447, 'frontend/checkout/cart', 1, 1, 'CartInfoMinimumSurcharge', 'Achtung. Sie haben den Mindestbestellwert von {$sMinimumSurcharge|currency} noch nicht erreicht! ', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(448, 'frontend/checkout/cart', 1, 1, 'CartInfoNoDispatch', 'Achtung: Für Ihr Warenkorb/Adresse wurde keine Versandart hinterlegt.<br /> Bitte kontaktieren Sie den Shopbetreiber.<br />', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(449, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeaderNewsletter', 'Möchten Sie mehr Informationen?', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(450, 'frontend/checkout/confirm', 1, 1, 'ConfirmLabelNewsletter', 'Ja, ich möchte den kostenlosen {$sShopname} Newsletter erhalten! Sie können sich jederzeit wieder abmelden!', '2010-01-01 00:00:00', '2010-10-15 13:24:52'),
(451, 'frontend/checkout/confirm_left', 1, 1, 'ConfirmSalutationMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(452, 'frontend/checkout/finish', 1, 1, 'FinishInfoTransaction', 'Transaktionsnummer:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(453, 'frontend/checkout/premiums', 1, 1, 'PremiumInfoNoPicture', 'Kein Bild vorhanden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(454, 'frontend/checkout/premiums', 1, 1, 'PremiumsInfoDifference', 'noch', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(455, 'frontend/checkout/premiums', 1, 1, 'PremiumsInfoAtAmount', 'ab', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(456, 'frontend/content/detail', 1, 1, 'ContentInfoPicture', 'Auf dem Bild zu sehen:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(457, 'frontend/content/detail', 1, 1, 'ContentHeaderInformation', 'Weitere Informationen:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(458, 'frontend/content/detail', 1, 1, 'ContentHeaderDownloads', 'Dateianhang:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(459, 'frontend/content/detail', 1, 1, 'ContentLinkDownload', 'Herunterladen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(460, 'frontend/content/detail', 1, 1, 'ContentInfoNotFound', 'Inhalt konnte nicht gefunden werden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(461, 'frontend/content/detail', 1, 1, 'ContentLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(462, 'frontend/content/index', 1, 1, 'ContentLinkDetails', '[mehr]', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(463, 'frontend/content/index', 1, 1, 'ContentInfoEmpty', 'Derzeit keine Einträge vorhanden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(464, 'frontend/detail/article_config_step', 1, 1, 'DetailConfigValueSelect', 'Bitte wählen...', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(465, 'frontend/detail/article_config_step', 1, 1, 'DetailConfigActionSubmit', 'Jetzt aktualisieren', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(466, 'frontend/detail/bundle/box_related', 1, 1, 'BundleHeader', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(467, 'frontend/detail/bundle/box_related', 1, 1, 'BundleActionAdd', 'In den Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(468, 'frontend/detail/bundle/box_related', 1, 1, 'BundleInfoPriceForAll', 'Preis für alle', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(469, 'frontend/compare/added', 1, 1, 'CompareActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(470, 'frontend/detail/buy', 1, 1, 'DetailBuyInfoNotAvailable', 'Dieser Artikel steht derzeit nicht zur Verfügung!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(471, 'frontend/detail/buy', 1, 1, 'DetailBuyLabelSurcharge', 'Aufpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(472, 'frontend/detail/comment', 1, 1, 'DetailCommentInfoSuccessOptin', 'Vielen Dank für die Abgabe Ihrer Bewertung! \r\n	Sie erhalten in wenigen Minuten eine Bestätigungsmail.\r\n	Bestätigen Sie den Link in dieser eMail um die Bewertung freizugeben.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(473, 'frontend/detail/comment', 1, 1, 'DetailCommentLabelMail', 'Ihre eMail-Adresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(474, 'frontend/compare/added', 1, 1, 'CompareInfoMaxReached', 'Es können maximal {config name=maxComparisons} Artikel in einem Schritt verglichen werden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(475, 'frontend/newsletter/index', 1, 1, 'NewsletterLabelSelect', 'Bitte wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(476, 'frontend/detail/data', 1, 1, 'DetailDataPriceInfo', 'Preise {if $sOutputNet}zzgl.{else}inkl.{/if} gesetzlicher MwSt. <a title="Versandkosten" href="{url controller=custom sCustom=6}" style="text-decoration:underline">zzgl. Versandkosten</a>', '2010-01-01 00:00:00', '2010-10-16 08:52:39'),
(477, 'frontend/detail/data', 1, 1, 'DetailDataHeaderBlockprices', 'Staffelpreise', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(478, 'frontend/detail/data', 1, 1, 'DetailDataColumnQuantity', 'Menge', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(479, 'frontend/detail/data', 1, 1, 'DetailDataColumnPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(480, 'frontend/detail/data', 1, 1, 'DetailDataInfoUntil', 'bis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(481, 'frontend/detail/data', 1, 1, 'DetailDataInfoFrom', 'ab', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(482, 'frontend/detail/description', 1, 1, 'DetailDescriptionLinkInformation', 'Weitere Artikel von {$information.description}', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(483, 'frontend/detail/description', 1, 1, 'DetailDescriptionComment', 'Unser Kommentar zu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(484, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownStartPrice', 'Startpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(485, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownCurrentPrice', 'Aktueller Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(486, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownRemaining', 'Noch', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(487, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownRemainingPieces', 'Stück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(488, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownPriceFails', 'Preis fällt um', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(489, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownMinutes', 'Minuten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(490, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCountdownPriceRising', 'Preis steigt um', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(491, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownStartPrice', 'Startpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(492, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownCurrentPrice', 'Aktueller Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(493, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownRemaining', 'Noch', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(494, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownRemainingPieces', 'Stück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(495, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownPriceFails', 'Preis fällt um', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(496, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownMinutes', 'Minuten', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(497, 'frontend/detail/liveshopping/detail_countdown', 1, 1, 'LiveCountdownPriceRising', 'Preis steigt um', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(498, 'frontend/account/payment', 1, 1, 'PaymentLinkSend', 'Ändern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(499, 'frontend/account/orders', 1, 1, 'MyOrdersTitle', 'Bestellungen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(500, 'frontend/account/orders', 1, 1, 'AccountTitle', 'Kundenkonto', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(501, 'frontend/detail/liveshopping/ticker/countdown', 1, 1, 'LiveTickerStartPrice', 'Startpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(502, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeRemaining', 'Noch', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(503, 'frontend/detail/liveshopping/ticker/timeline', 1, 1, 'LiveTimeRemainingPieces', 'Stück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(504, 'frontend/account/success_messages', 1, 1, 'AccountShippingSuccess', 'Ihre Lieferadresse wurde erfolgreich gespeichert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(505, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyInfoErrorMail', 'Bitte geben Sie eine gültige eMail-Adresse ein', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(506, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyHeader', 'Benachrichtigen Sie mich, wenn der Artikel lieferbar ist.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(507, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyLabelMail', 'Ihre E-Mail Adresse', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(508, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyActionSubmit', 'Eintragen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(509, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyInfoSuccess', 'Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(510, 'frontend/forms/index', 1, 1, 'FormsLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(511, 'frontend/forms/elements', 1, 1, 'SupportInfoFillRedFields', 'Bitte füllen Sie alle rot markierten Felder aus.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(512, 'frontend/tellafriend/index', 1, 1, 'TellAFriendHeaderSuccess', 'Vielen Dank. Die Weiterempfehlung wurde erfolgreich verschickt.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(513, 'frontend/tellafriend/index', 1, 1, 'TellAFriendInfoFields', 'Bitte füllen Sie alle benötigten Felder aus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(514, 'frontend/index/footer', 1, 1, 'FooterInfoExcludeVat', '* Alle Preise verstehen sich zzgl. Mehrwertsteuer und <span style="text-decoration: underline;"><a title="Versandkosten" href="{url controller=custom sCustom=6}">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben', '2010-01-01 00:00:00', '2010-10-14 14:45:36'),
(515, 'frontend/index/categories_top', 1, 1, 'IndexLinkHome', 'Home', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(516, 'frontend/listing/filter_properties', 1, 1, 'FilterHeadline', 'Filter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(517, 'frontend/listing/filter_properties', 1, 1, 'FilterHeadlineCategory', 'Filtern nach', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(518, 'frontend/listing/filter_properties', 1, 1, 'FilterLinkDefault', 'Alle anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(519, 'frontend/listing/box_article', 1, 1, 'ListingBoxNew', 'NEU', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(520, 'frontend/listing/box_similar', 1, 1, 'SimilarBoxLinkDetails', 'Zum Produkt', '2010-01-01 00:00:00', '2010-10-16 16:47:29'),
(521, 'frontend/newsletter/detail', 1, 1, 'NewsletterDetailInfoEmpty', 'Eintrag nicht gefunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(522, 'frontend/compare/overlay', 1, 1, 'LoginActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(523, 'frontend/compare/overlay', 1, 1, 'CompareHeader', 'Produktvergleich', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(524, 'frontend/newsletter/listing', 1, 1, 'NewsletterListingInfoEmpty', 'Derzeit keine Einträge vorhanden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(525, 'frontend/listing/box_similar', 1, 1, 'SimilarBoxMore', 'Zum Produkt', '2010-01-01 00:00:00', '2010-10-16 16:47:33'),
(526, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataShippingDays', 'Werktage', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(527, 'frontend/register/index', 1, 1, 'RegisterHeadlineSupplier', 'Händler-Anmeldung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(528, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier', 'Sie besitzen bereits einen Händleraccount?', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(529, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier2', 'Klicken Sie hier, um sich einzuloggen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(530, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier3', 'Nach der Anmeldung sehen Sie bis zur Freischaltung Endkunden-Preise', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(531, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier4', 'Senden Sie uns Ihren Gewerbenachweis per Fax!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(532, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier5', 'Senden Sie Ihren Gewerbenachweis per Fax an +49 2555 92 95 61. Wenn Sie bereits Händler bei uns sind,<br />können Sie diesen Schritt überspringen und müssen natürlich keinen Gewerbenachweis senden.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(533, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier6', 'Wir prüfen Ihre Angaben und schalten Sie frei!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(534, 'frontend/register/index', 1, 1, 'RegisterInfoSupplier7', 'Wir schalten Sie nach Prüfung als Händler frei. Sie erhalten dann von uns eine Info per E-Mail.<br />Von nun an sehen Sie direkt Ihren Händler-EK, auf den Produkt und Übersichtsseiten.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(535, 'frontend/search/filter_category', 1, 1, 'SearchFilterLinkDefault', 'Alle Kategorien anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(537, 'frontend/search/fuzzy', 1, 1, 'SearchFuzzyInfoShortTerm', 'Der eingegebene Suchbegriff ist leider zu kurz.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(538, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftLinkDefault', 'Alle anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(539, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftInfoSuppliers', 'Weitere Hersteller:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(540, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineFilter', 'nach Filtern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(541, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftLinkAllFilters', 'Alle Filter', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(542, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftLinkAllSuppliers', 'Alle Hersteller', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(543, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftLinkAllPrices', 'Alle Preise', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(544, 'frontend/search/index', 1, 1, 'SearchHeadline', 'Zu "{$sSearchTerm|escape}" wurden {$sSearchResultsNum} Artikel gefunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(545, 'frontend/search/supplier', 1, 1, 'SearchTo', 'Zu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(546, 'frontend/search/supplier', 1, 1, 'SearchWere', 'wurden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(547, 'frontend/search/supplier', 1, 1, 'SearchArticlesFound', 'Artikel gefunden!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(548, 'frontend/sitemap/index', 1, 1, 'SitemapHeader', 'Sitemap - Alle Kategorien auf einen Blick', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(549, 'frontend/ticket/navigation', 1, 1, 'TicketHeader', 'Supportverwaltung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(550, 'frontend/ticket/navigation', 1, 1, 'TicketLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(551, 'frontend/ticket/navigation', 1, 1, 'TicketLinkSupport', 'Support beantragen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(552, 'frontend/ticket/navigation', 1, 1, 'TicketLinkIndex', 'Supportübersicht', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(553, 'frontend/ticket/navigation', 1, 1, 'TicketLinkLogout', 'Abmelden Logout', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(554, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoEmpty', 'Es existiert kein Ticket mit dieser ID.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(555, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoTicket', 'Details zu dem Ticket', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(556, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoStatusClose', 'Dieses Ticket wurde geschlossen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(557, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoStatusProgress', 'Dieses Ticket wird zur Zeit noch bearbeitet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(558, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoAnswer', 'Ihre Antwort', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(559, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoQuestion', 'Ihre Ticketanfrage:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(560, 'frontend/checkout/ajax_add_article', 1, 1, 'LoginActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(561, 'frontend/ticket/listing', 1, 1, 'TicketInfoId', 'TicketID', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(562, 'frontend/register/steps.tpl', 1, 1, 'CheckoutStepBasketNumber', '1', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(563, 'frontend/ticket/listing', 1, 1, 'TicketInfoStatus', 'Status', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(564, 'frontend/ticket/listing', 1, 1, 'TicketHeadline', 'Supportverwaltung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(565, 'frontend/ticket/listing', 1, 1, 'TicketLinkDetails', '[Details anzeigen]', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(567, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsHeadline', 'Trusted Shops Gütesiegel - Bitte hier klicken.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(568, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsSalutationMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(569, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsSalutationMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(570, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsSalutationCompany', 'Firma', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(571, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsInfo', 'Anmeldung zur Geld-zurück-Garantie', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(572, 'frontend/plugins/trusted_shops/form', 1, 1, 'WidgetsTrustedShopsText', 'Als zus&auml;tzlichen Service bieten wir Ihnen den Trusted Shops K&auml;uferschutz an. Wir &uuml;bernehmen alle Kosten dieser Garantie, Sie m&uuml;ssen sich lediglich anmelden.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(573, 'frontend/account/select_address', 1, 1, 'SelectAddressSalutationMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(574, 'frontend/checkout/premiums', 1, 1, 'PremiumInfoSelect', 'Bitte wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(576, 'frontend/account/ajax_logout', 1, 1, 'LoginActionClose', 'Schließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(577, 'frontend/blog/comments', 1, 1, 'BlogInfoComments', 'Kommentare', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(578, 'frontend/register/billing_fieldset', 1, 1, 'RegisterLabelDepartment', 'Abteilung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(579, 'frontend/register/steps', 1, 1, 'CheckoutStepConfirmNumber', '3', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(580, 'frontend/ticket/detail', 1, 1, 'TicketDetailInfoShopAnswer', 'Unsere Antwort', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(581, 'frontend/widgets/blog/listing', 1, 1, 'WidgetsBlogHeadline', 'Neu in unserem Blog', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(582, 'frontend/register/billing_fieldset', 1, 1, 'RegisterLabelCompany', 'Name*', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(583, 'frontend/error/exception', 1, 1, 'ExceptionHeader', 'Ups! Ein Fehler ist aufgetreten!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(584, 'frontend/error/exception', 1, 1, 'ExceptionText', 'Die nachfolgenden Hinweise sollten Ihnen weiterhelfen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(585, 'frontend/register/billing_fieldset', 1, 1, 'RegisterHeaderCompany', 'Firma', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(586, 'frontend/detail/description', 1, 1, 'ArticleTipMoreInformation', 'Weiterführende Links zu', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(587, 'frontend/blog/index', 1, 1, 'ListingLinkAllSuppliers', 'Alle Autoren anzeigen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(588, 'frontend/blog/index', 1, 1, 'ListingInfoFilterSupplier', 'Filter nach Autor', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(589, 'frontend/register/billing_fieldset', 1, 1, 'RegisterLabelTaxId', 'Umsatzsteuer-ID', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(591, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataShippingtime', 'Lieferzeit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(592, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoInstantDownload', 'Als Sofortdownload verfügbar', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(593, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoShipping', 'Dieser Artikel erscheint am', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(594, 'frontend', 1, 1, 'RegisterPasswordLength', 'Bitte w&auml;hlen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.', '2010-01-01 00:00:00', '2010-10-12 19:45:13'),
(595, 'frontend', 1, 1, 'RegisterAjaxEmailNotEqual', 'Die eMail-Adressen stimmen nicht &uuml;berein.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(596, 'frontend', 1, 1, 'RegisterAjaxEmailNotValid', 'Bitte geben Sie eine g&uuml;ltige eMail-Adresse ein.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(597, 'frontend/checkout/confirm_item', 1, 1, 'CheckoutItemPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(598, 'frontend/custom/ajax', 1, 1, 'CustomAjaxActionNewWindow', 'Im neuen Fenster öffnen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(599, 'frontend/account/ajax_logout', 1, 1, 'AccountLogoutButton', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(600, 'frontend/checkout/ajax_cart', 1, 1, 'AjaxCartLinkConfirm', 'Zur Kasse', '2010-01-01 00:00:00', '2010-10-15 16:59:48'),
(601, 'frontend/account/success_messages', 1, 1, 'AccountBillingSuccess', 'Erfolgreich gespeichert', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(602, 'frontend/search/paging', 1, 1, 'ListingSortRelease', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(603, 'frontend/note/item', 1, 1, 'NoteInfoSupplier', 'Hersteller:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(604, 'frontend/note/index', 1, 1, 'NoteTitle', 'Merkzettel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(605, 'frontend/account/content_right', 1, 1, 'TicketLinkSupport', 'Support-Anfrage', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(606, 'frontend/note/item', 1, 1, 'NoteLinkZoom', 'Bild vergrößern', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(607, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterBillingLabelCity', 'Plz / Ort', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(608, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterBillingLabelStreet', 'Straße / Hausnr.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(609, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterLabelLastname', 'Nachname', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(610, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterLabelFirstname', 'Vorname', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(611, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterLabelMs', 'Frau', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(612, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterLabelMr', 'Herr', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(613, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterPleaseChoose', 'Bitte wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(614, 'frontend/newsletter/index', 1, 1, 'NewsletterRegisterLabelSalutation', 'Anrede', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(615, 'frontend/register/index', 1, 1, 'RegisterIndexActionSubmit', 'Registrierung abschließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(616, 'frontend/checkout/added', 1, 1, 'CheckoutAddArticleLinkBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(617, 'frontend', 1, 1, 'RegisterAjaxEmailForgiven', 'Diese eMail-Adresse ist bereits registriert.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(618, 'frontend/checkout/added', 1, 1, 'CheckoutAddArticleInfoAdded', '"{$sArticleName}" wurde in den Warenkorb gelegt!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(619, 'frontend/checkout/confirm_footer', 1, 1, 'CheckoutFinishTaxInformation', 'Der Empfänger der Leistung schuldet die Steuer', '2010-01-01 00:00:00', '2010-10-16 10:37:58'),
(620, 'frontend/checkout/confirm_item', 1, 1, 'CartItemInfoFree', 'Kostenlos', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(621, 'frontend/account/password', 1, 1, 'LoginBack', 'Zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(622, 'frontend/checkout/cart_footer', 1, 1, 'CartFooterTotalTax', 'zzgl. {$rate}&nbsp;% MwSt.:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(623, 'frontend/checkout/cart_footer', 1, 1, 'CartFooterTotalNet', 'Gesamtsumme ohne MwSt.:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(624, 'frontend/detail/error', 1, 1, 'DetailRelatedHeader', 'Dieser Artikel ist leider nicht mehr verfügbar!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(625, 'frontend/detail/error', 1, 1, 'DetailRelatedHeaderSimilarArticles', 'Ähnliche Artikel:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(626, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyInfoValid', 'Vielen Dank!\r\n\r\nWir haben Ihre Anfrage gespeichert!\r\nSie werden benachrichtigt sobald der Artikel wieder verfügbar ist.\r\n', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(627, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyInfoInvalid', 'Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(628, 'frontend/search/paging', 1, 1, 'ListingPaging', 'Blättern:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(629, 'frontend/search/paging', 1, 1, 'ListingLinkPrevious', 'Seite zurück', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(630, 'frontend/search/paging', 1, 1, 'ListingTextNext', '>', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(631, 'frontend/search/paging', 1, 1, 'ListingLinkNext', 'Seite vor', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(632, 'frontend/search/paging', 1, 1, 'ListingTextPrevious', '<', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(633, 'frontend/listing/listing_actions', 1, 1, 'ListingPaging', 'Blättern:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(634, 'frontend/listing/listing_actions', 1, 1, 'ListingTextPrevious', '&lt;', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(635, 'frontend/listing/listing_actions', 1, 1, 'ListingLinkPrevious', 'Vorherige Seite', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(636, 'frontend/listing/listing_actions', 1, 1, 'ListingTextNext', '&gt;', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(637, 'frontend/checkout/ajax_add_article', 1, 1, 'ListingBoxNoPicture', 'Kein Bild', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(639, 'frontend/detail/header', 1, 1, 'DetailChooseFirst', 'Bitte wählen Sie zuerst eine Variante aus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(640, 'frontend/custom/ajax', 1, 1, 'CustomAjaxActionClose', 'Schliessen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(641, 'frontend', 1, 1, 'sMailConfirmation', 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet. Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestätigen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(642, 'frontend', 1, 1, 'AccountLoginTitle', 'Login', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(643, 'frontend/ticket/listing', 1, 1, 'TicketInfoDate', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(644, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterLabelAddVoucher', 'Gutschein hinzufügen:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(645, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterAddVoucherLabelInline', 'Gutschein-Nummer', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(649, 'frontend/checkout/confirm', 1, 1, 'ConfirmTextRightOfRevocation', '<h3 class="underline">Widerrufsbelehrung</h3><p>Bitte beachten Sie bei Ihrer Bestellung auch unsere <a href="{url controller=custom sCustom=8 forceSecure}" data-modal-height="500" data-modal-width="800">Widerrufsbelehrung</a>.</p>', '2010-09-23 21:23:42', '2010-10-15 13:23:33'),
(650, 'frontend/account/billing', 1, 1, 'BillingLinkSend', 'Ändern', '2010-09-23 21:23:52', '2010-09-28 11:54:19'),
(659, 'frontend/index/index', 1, 1, 'IndexNoscriptNotice', 'Um {$sShopname} in vollen Umfang nutzen zu k&ouml;nnen, empfehlen wir Ihnen Javascript in Ihren Browser zu aktiveren.', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(660, 'frontend/index/index', 1, 1, 'IndexRealizedShopsystem', 'Shopware', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(661, 'frontend/index/header', 1, 1, 'IndexMetaHttpContentType', 'text/html; charset=iso-8859-1', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(664, 'frontend/index/header', 1, 1, 'IndexMetaMsNavButtonColor', '#dd4800', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(665, 'frontend/index/header', 1, 1, 'IndexMetaShortcutIcon', '{link file=''frontend/_resources/favicon.ico''}', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(669, 'frontend/error/index', 1, 1, 'ErrorIndexTitle', 'Es ist ein Fehler aufgetreten', '2010-09-23 14:38:13', '2010-10-15 13:26:59'),
(684, 'frontend/compare/overlay', 1, 1, 'CompareLinkPrint', 'Drucken', '2010-09-23 21:28:38', '2010-09-28 11:54:19'),
(686, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'ListingBoxArticleStartsAt', 'ab', '2010-09-24 14:59:39', '2010-09-28 11:54:19'),
(692, 'frontend/search/fuzzy', 1, 1, 'SearchHeadline', 'Zu "{$sRequests.sSearch}" wurden {$sSearchResults.sArticlesCount} Artikel gefunden!', '2010-09-25 12:28:44', '2010-09-28 11:54:19'),
(694, 'backend/cache/skeleton', 1, 1, 'WindowTitle', 'Cache', '2010-09-25 13:05:39', '2010-09-28 11:54:19'),
(695, 'frontend/account/ajax_login', 1, 1, 'LoginInfoNew', 'Kein Problem, eine Shopbestellung ist einfach und sicher. Die Anmeldung dauert nur wenige Augenblicke.', '2010-09-25 13:25:28', '2010-09-28 11:54:19'),
(696, 'frontend/account/ajax_login', 1, 1, 'LoginActionCreateAccount', 'Weiter', '2010-09-25 13:25:28', '2010-09-28 11:54:19'),
(697, 'frontend/custom/right', 1, 1, 'CustomHeader', 'Direkter Kontakt', '2010-09-25 13:27:32', '2010-09-28 11:54:19'),
(698, 'frontend/custom/right', 1, 1, 'CustomTextContact', 'Ihre Adresse', '2010-09-25 13:27:32', '2010-09-28 11:54:19'),
(699, 'frontend/account/ajax_login', 1, 1, 'LoginLabelPassword', 'Ihr Passwort:', '2010-09-25 14:17:21', '2010-09-28 11:54:19'),
(700, 'frontend/checkout/confirm_header', 1, 1, 'CartColumnTotal', 'Summe', '2010-09-25 16:02:40', '2010-09-28 11:54:19'),
(701, 'frontend/account/ajax_login', 1, 1, 'LoginTextExisting', 'Einloggen mit Ihrer eMail-Adresse und Ihrem Passwort', '2010-09-25 16:43:39', '2010-09-28 11:54:19'),
(702, 'frontend/register/index', 1, 1, 'RegisterLabelDataCheckbox', 'Hiermit akzeptiere ich die Datenschutz-Bestimmungen', '2010-09-25 17:39:30', '2010-09-28 11:54:19'),
(703, 'frontend/content/paging', 1, 1, 'ListingPaging', 'Blättern:', '2010-09-26 11:44:05', '2010-09-28 11:54:19'),
(704, 'frontend/content/paging', 1, 1, 'ListingLinkPrevious', 'Zurück', '2010-09-26 11:44:05', '2010-09-28 11:54:19'),
(705, 'frontend/content/paging', 1, 1, 'ListingTextPrevious', '&lt;', '2010-09-26 11:44:05', '2010-09-28 11:54:19'),
(706, 'frontend/content/paging', 1, 1, 'ListingLinkNext', 'Weiter', '2010-09-26 11:44:05', '2010-09-28 11:54:19'),
(707, 'frontend/content/paging', 1, 1, 'ListingTextNext', '&gt;', '2010-09-26 11:44:05', '2010-09-28 11:54:19'),
(708, 'frontend/account/success_messages', 1, 1, 'AccountNewsletterSuccess', 'Erfolgreich gespeichert', '2010-09-26 12:26:18', '2010-09-28 11:54:19'),
(710, 'frontend/ticket/detail', 1, 1, 'TicketDetailLinkBack', 'Zurück', '2010-09-26 15:14:11', '2010-09-28 11:54:19'),
(711, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsOffersLink', 'Weitere Artikel in dieser Kategorie &raquo;', '2010-09-26 15:18:42', '2010-09-28 11:54:19'),
(712, 'frontend/newsletter/listing', 1, 1, 'NewsletterListingHeaderName', 'Name', '2010-09-27 09:52:02', '2010-09-28 11:54:19'),
(714, 'frontend/detail/config_step', 1, 1, 'DetailConfigValueSelect', 'Bitte wählen', '2010-09-27 11:19:49', '2010-09-28 11:54:19'),
(715, 'frontend/detail/config_step', 1, 1, 'DetailConfigActionSubmit', 'Auswählen', '2010-09-27 11:19:49', '2010-09-28 11:54:19'),
(716, 'frontend/detail/config_upprice', 1, 1, 'DetailConfigActionSubmit', 'Auswählen', '2010-09-27 11:19:49', '2010-09-28 11:54:19'),
(717, 'frontend/search/filter_category', 1, 1, 'SearchFilterCategoryHeading', 'Suchergebnis nach Kategorien einschr&auml;nken', '2010-09-27 14:27:46', '2010-09-28 11:54:19'),
(718, 'frontend/search/paging', 1, 1, 'ListingLabelItemsPerPage', 'Artikel pro Seite:', '2010-09-27 14:27:46', '2010-09-28 11:54:19'),
(719, 'frontend/search/ajax', 1, 1, 'SearchAjaxLinkAllResults', 'Alle Ergebnisse anzeigen', '2010-09-27 14:36:16', '2010-09-28 11:54:19'),
(720, 'frontend/search/ajax', 1, 1, 'SearchAjaxInfoResults', 'Treffer', '2010-09-27 14:36:16', '2010-09-28 11:54:19'),
(721, 'frontend/plugins/recommendation/blocks_index', 1, 1, 'IndexNewArticlesSlider', 'Neu im Sortiment:', '2010-09-27 15:36:42', '2010-09-28 11:54:19'),
(722, 'frontend/plugins/recommendation/blocks_index', 1, 1, 'IndexSimilaryArticlesSlider', 'Ähnliche Artikel wie die, die Sie sich angesehen haben:', '2010-09-27 15:36:42', '2010-09-28 11:54:19'),
(723, 'frontend/plugins/recommendation/blocks_index', 1, 1, 'IndexSupplierSlider', 'Unsere Top Marken', '2010-09-27 15:36:42', '2010-09-28 11:54:19'),
(730, 'frontend/detail/comment', 1, 2, 'Rate2', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(731, 'frontend/detail/comment', 1, 2, 'Rate3', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(732, 'frontend/detail/comment', 1, 2, 'Rate4', '4', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(733, 'frontend/detail/comment', 1, 2, 'Rate5', '5', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(734, 'frontend/detail/comment', 1, 2, 'Rate6', '6', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(735, 'frontend/detail/comment', 1, 2, 'Rate7', '7', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(736, 'frontend/detail/comment', 1, 2, 'Rate8', '8', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(737, 'frontend/detail/comment', 1, 2, 'Rate9', '9', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(738, 'frontend/detail/comment', 1, 2, 'Rate10', '10 excellent', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(774, 'frontend/blog/index', 1, 2, 'BlogLinkAtom', 'Atom-Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(776, 'frontend/blog/index', 1, 2, 'BlogLinkRSS', 'RSS-Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(780, 'frontend/widgets/topseller', 1, 2, 'WidgetsTopsellerNoPicture', 'no picture available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(799, 'frontend/index/header', 1, 2, 'IndexMetaAuthor', 'hello', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(812, 'frontend/account/ajax_login', 1, 2, 'LoginLinkLostPassword', 'Forgot your password?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(819, 'frontend/widgets/topseller', 1, 2, 'TopsellerHeading', 'topseller', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(826, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(836, 'frontend/register/personal_fieldset', 1, 2, 'RegisterInfoPasswordCharacters', 'characters', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(867, 'frontend/register/error_message', 1, 2, 'RegisterErrorHeadline', 'An error has occurred!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(884, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(930, 'frontend/account/order_item', 1, 2, 'OrderItemColumnDate', 'From:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(948, 'frontend/checkout/ajax_cart', 1, 2, 'AjaxCartInfoEmpty', 'Your shopping cart is empty', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(964, 'frontend/register/steps', 1, 2, 'CheckoutStepBasketNumber', '1', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(967, 'frontend/register/steps', 1, 2, 'CheckoutStepRegisterNumber', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(997, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoFrom', 'From:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1013, 'frontend/newsletter/listing', 1, 2, 'NewsletterListingLinkDetails', '[more]', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1022, 'frontend/blog/bookmarks', 1, 2, 'BookmarkTwitter', 'Twitter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1023, 'frontend/blog/bookmarks', 1, 2, 'BookmarkFacebook', 'Facebook', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1024, 'frontend/blog/bookmarks', 1, 2, 'BookmarkDelicious', 'Delicious', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1025, 'frontend/blog/bookmarks', 1, 2, 'BookmarkDiggit', 'Diggit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1031, 'frontend/blog/comments', 1, 2, 'rate10', '10 excellent', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1032, 'frontend/blog/comments', 1, 2, 'rate9', '9', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1033, 'frontend/blog/comments', 1, 2, 'rate8', '8', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1034, 'frontend/blog/comments', 1, 2, 'rate7', '7', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1035, 'frontend/blog/comments', 1, 2, 'rate6', '6', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1036, 'frontend/blog/comments', 1, 2, 'rate5', '5', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1037, 'frontend/blog/comments', 1, 2, 'rate4', '4', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1038, 'frontend/blog/comments', 1, 2, 'rate3', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1039, 'frontend/blog/comments', 1, 2, 'rate2', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1050, 'backend/error/index', 1, 2, 'ErrorIndexTitle', 'error', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1078, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPercent', '% discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1094, 'frontend/account/login', 1, 2, 'LoginLinkLostPassword', 'Forgot your password?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1110, 'frontend/account/index', 1, 2, 'AccountSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1142, 'frontend/account/password', 1, 2, 'PasswordInfoSuccess', 'Your new password has been sent to you', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1181, 'frontend/content/index', 1, 2, 'ContentLinkDetails', '[more]', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1223, 'frontend/account/success_messages', 1, 2, 'AccountShippingSuccess', 'Your shipping address has been saved successfully', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1238, 'frontend/listing/box_article', 1, 2, 'ListingBoxNew', 'NEW', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1276, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoStatusClose', 'This ticket has been closed.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1282, 'frontend/register/steps.tpl', 1, 2, 'CheckoutStepBasketNumber', '1', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1286, 'frontend/widgets/paypal/logo', 1, 2, 'WidgetsPayPalLogo', 'PayPal-method of payment seal', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1288, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1293, 'frontend/account/select_address', 1, 2, 'SelectAddressSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1298, 'frontend/register/steps', 1, 2, 'CheckoutStepConfirmNumber', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1331, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterLabelMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1349, 'frontend/search/paging', 1, 2, 'ListingTextNext', '>', '0000-00-00 00:00:00', '2010-09-28 11:54:19');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1351, 'frontend/search/paging', 1, 2, 'ListingTextPrevious', '<', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1358, 'frontend/detail/header', 1, 2, 'DetailChooseFirst', 'Please select a variant first', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1361, 'frontend', 1, 2, 'AccountLoginTitle', 'Login', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1365, 'frontend/widgets/paypal/logo', 1, 2, 'WidgetPaypalText', 'PayPal', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1367, 'frontend', 1, 1, 'CheckoutArticleLessStock', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern. (#0 von #1 lieferbar).', '2010-09-27 17:36:07', '2010-09-28 11:54:19'),
(1371, 'documents/index', 1, 1, 'DocumentIndexCustomerID', 'Kunden-Nr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1372, 'documents/index', 1, 1, 'DocumentIndexUstID', 'USt-IdNr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1373, 'documents/index', 1, 1, 'DocumentIndexOrderID', 'Bestell-Nr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1374, 'documents/index', 1, 1, 'DocumentIndexDate', 'Datum:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1375, 'documents/index', 1, 1, 'DocumentIndexDeliveryDate', 'Liefertermin:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1376, 'documents/index', 1, 1, 'DocumentIndexInvoiceNumber', 'Rechnung Nr. {$Document.id}', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1377, 'documents/index', 1, 1, 'DocumentIndexPageCounter', 'Seite {$page+1} von {$Pages|@count}', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1378, 'documents/index', 1, 1, 'DocumentIndexHeadPosition', 'Pos.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1379, 'documents/index', 1, 1, 'DocumentIndexHeadArticleID', 'Art-Nr.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1380, 'documents/index', 1, 1, 'DocumentIndexHeadName', 'Bezeichnung', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1381, 'documents/index', 1, 1, 'DocumentIndexHeadQuantity', 'Anz.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1382, 'documents/index', 1, 1, 'DocumentIndexHeadTax', 'MwSt.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1383, 'documents/index', 1, 1, 'DocumentIndexHeadPrice', 'Brutto Preis', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1384, 'documents/index', 1, 1, 'DocumentIndexHeadAmount', 'Brutto Gesamt', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1385, 'documents/index', 1, 1, 'DocumentIndexHeadNet', 'Netto Preis', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1386, 'documents/index', 1, 1, 'DocumentIndexHeadNetAmount', 'Netto Gesamt', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1387, 'documents/index', 1, 1, 'DocumentIndexTotalNet', 'Gesamtkosten Netto:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1388, 'documents/index', 1, 1, 'DocumentIndexTax', 'zzgl. {$key} MwSt:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1389, 'documents/index', 1, 1, 'DocumentIndexTotal', 'Gesamtkosten:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1390, 'documents/index', 1, 1, 'DocumentIndexAdviceNet', 'Hinweis: Der Empfänger der Leistung schuldet die Steuer.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1391, 'documents/index', 1, 1, 'DocumentIndexSelectedPayment', 'Gew&auml;hlte Zahlungsart', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1392, 'documents/index', 1, 1, 'DocumentIndexVoucher', '\n						Für den nächsten Einkauf schenken wir Ihnen einen {$Document.voucher.value} {$Document.voucher.prefix} Gutschein\n						mit dem Code "{$Document.voucher.code}".<br />\n					', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1393, 'documents/index', 1, 1, 'DocumentIndexComment', 'Kommentar:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1394, 'documents/index', 1, 1, 'DocumentIndexSelectedDispatch', 'Gewählte Versandart:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1395, 'documents/index', 1, 1, 'DocumentIndexCurrency', '\n					<br>Euro Umrechnungsfaktor: {$Order._currency.factor|replace:".":","}\n					', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1399, 'frontend', 1, 1, 'AccountPasswordNotEqual', 'Die Passwörter stimmen nicht überein.', '2010-09-29 20:32:08', '2010-09-29 20:32:08'),
(1400, 'backend/index/index', 1, 1, 'IndexTitle', 'Shopware {config name=Version}  (Rev. 3650, 18.10.2010) - Backend (c)2010,2011 shopware AG', '2010-09-29 22:30:38', '2010-10-17 12:08:50'),
(1401, 'backend/plugin/viewport', 1, 1, 'tree_titel', 'Plugins', '2010-09-30 10:00:13', '2010-09-30 10:00:13'),
(1403, 'frontend', 1, 1, 'CheckoutSelectVariant', 'Bitte wählen Sie eine Variante aus, um den gewünschte Artikel in den Warenkorb zu legen.', '2010-10-01 10:53:18', '2010-10-01 10:53:18'),
(1404, 'frontend/account/index', 1, 1, 'AccountLinkChangeMail', 'E-Mail ändern', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1405, 'frontend/account/index', 1, 1, 'AccountLabelNewMail', 'Neue E-Mail-Adresse', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1406, 'frontend/account/index', 1, 1, 'AccountLabelMail', 'E-Mail-Wiederholung', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1407, 'frontend/plugins/notification/index', 1, 1, 'DetailNotifyAlreadyRegistered', 'Sie haben sich bereits für eine Benachrichtigung eingetragen!', '2010-01-01 00:00:00', '2010-10-06 12:28:47'),
(1408, 'documents/index_ls', 1, 1, 'DocumentIndexShippingNumber', 'Lieferschein Nr.', '2010-10-05 09:27:18', '2010-10-05 09:27:18'),
(1409, 'documents/index_ls', 1, 1, 'DocumentIndexPageCounter', 'Seite {$page+1} von {$Pages|@count}', '2010-10-05 09:27:18', '2010-10-05 09:27:18'),
(1410, 'documents/index_ls', 1, 1, 'DocumentIndexInvoiceID', 'Zur Rechnung:', '2010-10-05 09:27:18', '2010-10-05 09:27:18'),
(1411, 'frontend/plugins/paypal/logo', 1, 1, 'PaypalLogoAlt', 'Paypal', '2010-10-05 09:31:18', '2010-10-05 09:31:18'),
(1412, 'frontend/plugins/paypal/logo', 1, 1, 'PaypalLogoText', 'Paypal', '2010-10-05 09:31:18', '2010-10-05 09:31:18'),
(1418, 'frontend/checkout/shipping_costs', 1, 1, 'DispatchHeadNotice', 'Versandinfo:', '2010-10-05 09:34:18', '2010-10-05 09:34:18'),
(1419, 'frontend/checkout/confirm', 1, 1, 'ConfirmDoPayment', 'Zahlungspflichtig bestellen', '2010-10-05 13:53:10', '2010-10-05 13:53:10'),
(1430, 'frontend', 1, 1, 'CheckoutArticleNoStock', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern.', '2010-10-05 23:11:09', '2010-10-05 23:11:09'),
(1431, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLinkConfirm', 'Zur Kasse', '2010-10-06 19:29:43', '2010-10-15 17:02:07'),
(1432, 'frontend/checkout/ajax_add_article', 1, 1, 'ListingBoxArticleStartsAt', 'ab', '2010-10-06 19:29:43', '2010-10-06 19:29:43'),
(1433, 'frontend/checkout/confirm', 1, 1, 'ConfirmErrorStock', 'Ein Artikel aus Ihrer Bestellung ist nicht mehr verfügbar! Bitte entfernen Sie die Position aus dem Warenkorb!', '2010-10-06 19:41:24', '2010-10-06 19:41:24'),
(1434, 'frontend/checkout/confirm_item', 1, 1, 'CheckoutItemLaststock', 'NICHT LIEFERBAR', '2010-10-06 19:56:58', '2010-10-06 19:56:58'),
(1435, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsTable', 'Tabellen-Ansicht', '2010-10-06 22:24:50', '2010-10-06 22:24:50'),
(1436, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsList', 'Listen-Ansicht', '2010-10-06 22:24:50', '2010-10-06 22:24:50'),
(1438, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddHeaderError', 'Hinweis:', '2010-10-07 14:24:05', '2010-10-07 14:24:05'),
(1439, 'backend/activate/skeleton', 1, 1, 'WindowTitle', 'Cache leeren', '2010-10-07 16:05:16', '2010-10-07 16:05:16'),
(1446, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeadDispatch', 'Versandart:', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1447, 'frontend/checkout/confirm', 1, 1, 'ConfirmLabelDispatch', 'Aktuell ausgewählte Versandart', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1448, 'frontend/checkout/confirm', 1, 1, 'ConfirmLinkChangeDispatch', 'Ändern', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1449, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeadDispatchNotice', 'Versandinformationen', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1455, 'frontend/plugins/index/tagcloud', 1, 1, 'TagcloudHead', 'Tagwolke', '2010-10-08 16:11:27', '2010-10-08 16:11:27'),
(1456, 'frontend/plugins/index/topseller', 1, 1, 'TopsellerHeading', 'Topseller', '2010-10-08 16:11:27', '2010-10-09 09:50:03'),
(1457, 'frontend/plugins/index/topseller', 1, 1, 'WidgetsTopsellerNoPicture', 'Kein Bild vorhanden', '2010-10-08 16:11:27', '2010-10-16 11:26:45'),
(1458, 'backend/plugins/coupons/skeleton', 1, 1, 'WindowTitle', 'Coupon Verwaltung', '2010-10-08 16:31:25', '2010-10-08 16:31:25'),
(1472, 'frontend/home/index', 1, 1, 'WidgetsBlogHeadline', 'Blog', '2010-10-08 18:29:11', '2010-10-16 11:26:56'),
(1482, 'backend/plugins/coupons/pdf/index', 1, 1, 'PluginsBackendCouponsInfo', 'Der Gutschein ist gültig bis zum \r\n				', '2010-10-11 15:38:57', '2010-10-11 15:38:57'),
(1483, 'backend/plugins/coupons/pdf/index', 1, 1, 'PluginsBackendCouponsCharge', 'Bitte beachten Sie den Mindestbestellwert von {$coupon.minimumcharge|currency}\r\n					', '2010-10-11 15:43:46', '2010-10-11 15:43:46'),
(1484, 'backend/plugins/coupons/pdf/index', 1, 1, 'PluginsBackendCouponsText', '\n				Sie können den Gutschein einfach während des Bestellprozesses im Warenkorb einlösen.\n				Wir wünschen Ihnen viel Spaß bei dem Besuch unseres Shops. Bei Fragen oder Problemen erreichen Sie uns jederzeit\n				unter folgenden Kontaktdaten: Musterfirma | Musterstraße | Musterort\n				', '2010-10-11 15:44:56', '2010-10-11 15:44:56'),
(1485, 'frontend/widgets/advanced_menu/advanced_menu', 1, 1, 'IndexLinkHome', 'Home', '2010-10-12 06:53:25', '2010-10-12 06:53:25'),
(1486, 'frontend/account/order_item', 1, 1, 'OrderItemCustomerComment', 'Ihr Kommentar', '2010-10-12 06:54:17', '2010-10-12 06:54:17'),
(1487, 'frontend/account/order_item', 1, 1, 'OrderItemComment', 'Unser Kommentar', '2010-10-12 06:54:17', '2010-10-12 06:54:17'),
(1488, 'frontend/checkout/error_messages', 1, 1, 'ConfirmInfoNoDispatch', 'Keine Versandart', '2010-10-12 07:45:26', '2010-10-12 07:45:26'),
(1489, 'frontend/checkout/error_messages', 1, 1, 'ConfirmInfoMinimumSurcharge', 'Mindestbestellwert nicht erreicht', '2010-10-12 07:45:26', '2010-10-12 07:45:26'),
(1490, 'newsletter/index/header', 1, 1, 'NewsletterHeaderLinkHome', 'Home', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1491, 'newsletter/container/article', 1, 1, 'NewsletterBoxArticleStartsAt', 'ab', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1492, 'newsletter/container/article', 1, 1, 'NewsletterBoxArticleLinkDetails', 'Mehr Infos', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1493, 'newsletter/index/footer', 1, 1, 'NewsletterFooterNavigation', '<a href="#" target="_blank" style="font-size:10px;">Kontakt</a> | <a href="#" target="_blank" style="font-size:10px;">Impressum</a>', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1494, 'newsletter/index/footer', 1, 1, 'NewsletterFooterInfoIncludeVat', '* Alle Preise inkl. gesetzl. Mehrwertsteuer und <span style="text-decoration: underline;"><a title="Versandkosten" href="{url controller=custom sCustom=6}">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben', '2010-10-12 10:05:03', '2010-10-15 12:58:35'),
(1495, 'newsletter/index/footer', 1, 1, 'NewsletterFooterInfoExcludeVat', '* Alle Preise verstehen sich zzgl. Mehrwertsteuer  und <span style="text-decoration: underline;"><a title="Versandkosten" href="{url controller=custom sCustom=6}">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben', '2010-10-12 10:05:03', '2010-10-15 12:58:20'),
(1497, 'newsletter/index/footer', 1, 1, 'NewsletterFooterLinkUnsubscribe', 'Vom Newsletter abmelden', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1498, 'newsletter/index/footer', 1, 1, 'NewsletterFooterLinkNewWindow', 'Newsletter im Browser öffnen', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
(1499, 'frontend/detail/liveshopping/category_countdown', 1, 1, 'LiveCategoryOffersEnds', 'Angebot endet in:', '2010-10-12 19:39:31', '2010-10-12 19:39:31'),
(1500, 'frontend/detail/liveshopping/category', 1, 1, 'LiveCategoryPreviousPrice', 'Ursprünglicher Preis:', '2010-10-12 19:39:32', '2010-10-12 19:39:32'),
(1501, 'frontend/detail/liveshopping/category', 1, 1, 'LiveCategorySavingPercent', 'Sie sparen:', '2010-10-12 19:39:32', '2010-10-12 19:39:32'),
(1502, 'frontend/detail/liveshopping/category', 1, 1, 'LiveCategoryOffersEnds', 'Angebot endet in:', '2010-10-12 19:39:32', '2010-10-12 19:39:32'),
(1503, 'frontend/detail/liveshopping/category', 1, 1, 'LiveCategoryCurrentPrice', 'Aktueller Preis:', '2010-10-12 19:39:32', '2010-10-12 19:39:32'),
(1504, 'documents/index_sr', 1, 1, 'DocumentIndexTotalNet', 'Gesamtkosten Netto:', '2010-10-13 01:35:47', '2010-10-13 01:35:47'),
(1505, 'documents/index_sr', 1, 1, 'DocumentIndexTax', 'zzgl. {$key} MwSt:', '2010-10-13 01:35:47', '2010-10-13 01:35:47'),
(1506, 'documents/index_sr', 1, 1, 'DocumentIndexTotal', 'Gesamtkosten:', '2010-10-13 01:35:47', '2010-10-13 01:35:47'),
(1507, 'documents/index_sr', 1, 1, 'DocumentIndexCancelationNumber', 'Stornorechnung zur Rechnung Nr. {$Document.bid}', '2010-10-13 01:35:47', '2010-10-13 01:35:47'),
(1508, 'documents/index_sr', 1, 1, 'DocumentIndexPageCounter', 'Seite {$page+1} von {$Pages|@count}', '2010-10-13 01:35:47', '2010-10-13 01:35:47'),
(1509, 'frontend', 1, 1, 'CheckoutSelectPremiumVariant', 'Bitte wählen Sie eine Variante aus, um den gewünschte Prämie in den Warenkorb zu legen.', '2010-10-13 17:38:03', '2010-10-13 17:38:03'),
(1511, 'backend/plugin/skeleton', 1, 1, 'WindowTitle', 'Plugin Manager', '2010-10-13 22:40:31', '2010-10-13 22:40:31'),
(1513, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexNewArticlesSlider', 'Neu im Sortiment:', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1514, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexSimilaryArticlesSlider', 'Ähnliche Artikel wie die, die Sie sich angesehen haben:', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1515, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexSupplierSlider', 'Unsere Top Marken', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1516, 'frontend/account/order_item.tpl', 1, 1, 'OrderItemCustomerComment', 'Ihr Kommentar', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1517, 'frontend/account/order_item.tpl', 1, 1, 'OrderItemComment', 'Unser Kommentar', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1518, 'frontend/checkout/confirm_header', 1, 1, 'CheckoutColumnExcludeTax', 'zzgl. Mwst.', '2010-10-15 13:21:16', '2010-10-15 13:22:00'),
(1519, 'frontend', 1, 1, 'RegisterPasswordNotEqual', 'Die Passw&ouml;rter stimmen nicht &uuml;berein.', '2010-10-15 14:29:54', '2010-10-15 14:29:54'),
(1520, 'frontend/checkout/finish_header', 1, 1, 'CartColumnTotal', 'Summe', '2010-10-15 14:35:34', '2010-10-15 14:35:34'),
(1522, 'backend/snippet/skeleton', 1, 2, 'WindowTitle', 'Textbausteine', '2010-10-15 16:57:50', '2010-10-15 16:57:50'),
(1524, 'frontend/account/ajax_login', 1, 1, 'LoginLabelNoAccount', 'Kein Kundenkonto erstellen', '2010-10-16 08:36:09', '2010-10-16 08:36:09'),
(1526, 'frontend/account/login', 1, 1, 'AccountLoginTitle', 'Login', '2010-10-16 09:41:49', '2010-10-16 09:41:49'),
(1527, 'frontend/account/login', 1, 1, 'LoginLabelNoAccount', 'Kein Kundenkonto erstellen', '2010-10-16 09:42:13', '2010-10-16 09:42:13'),
(1536, 'frontend/index/search', 1, 1, 'IndexSearchFieldValue', 'Suche:', '2010-10-16 15:53:05', '2010-10-16 15:53:05'),
(1537, 'frontend/compare/add_article', 1, 1, 'CompareHeaderTitle', 'Artikel vergleichen', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1540, 'frontend/compare/add_article', 1, 1, 'CompareInfoMaxReached', 'Es können maximal {config name=maxComparisons} Artikel in einem Schritt verglichen werden', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1541, 'frontend/plugins/advanced_menu/advanced_menu', 1, 1, 'IndexLinkHome', 'Home', '2010-10-17 13:45:56', '2010-10-17 13:45:56'),
(1542, 'frontend/account/downloads', 1, 1, 'MyDownloadsTitle', 'Meine Sofortdownloads', '2010-10-17 18:52:49', '2010-10-17 18:54:46'),
(1543, 'backend/license/skeleton', 1, 1, 'WindowTitle', 'Lizenzen', '2010-10-17 19:12:33', '2010-10-17 19:12:33'),
(1544, 'backend/plugins/recommendation/skeleton', 1, 1, 'WindowTitle', 'Slider-Komponenten', '2010-10-17 19:25:09', '2010-10-17 19:25:09'),
(1545, 'frontend/account/select_billing', 1, 1, 'SelectBillingTitle', 'Adresse auswählen', '2010-10-18 00:29:20', '2010-10-18 00:57:55'),
(1546, 'frontend/account/billing', 1, 1, 'ChangeBillingTitle', 'Rechnungsadresse ändern', '2010-10-18 00:29:20', '2010-10-18 00:56:36'),
(1547, 'frontend/account/shipping', 1, 1, 'ChangeShippingTitle', 'Lieferadresse ändern', '2010-10-18 00:29:59', '2010-10-18 00:57:08'),
(1548, 'frontend/checkout/finish_footer', 1, 1, 'CheckoutFinishTaxInformation', 'Der Empfänger der Leistung schuldet die Steuer', '2010-10-18 00:31:38', '2010-10-18 00:31:38'),
(1549, 'frontend/account/payment', 1, 1, 'ChangePaymentTitle', 'Zahlungsart ändern', '2010-10-18 00:57:15', '2010-10-18 00:57:25'),
(1550, 'frontend/account/select_shipping', 1, 1, 'SelectShippingTitle', 'Adresse auswählen', '2010-10-18 00:58:04', '2010-10-18 00:58:11'),
(1551, 'frontend/error/exception', 1, 1, 'InformText', 'Wir wurden bereits über das Problem informiert und arbeiten an einer Lösung, bitte versuchen Sie es in Kürze erneut.', '2010-10-25 16:51:50', '2010-10-25 16:51:50'),
(1552, 'frontend/listing/box_article', 1, 1, 'Star', '*', '2010-12-08 02:51:26', '2010-12-08 02:51:26'),
(1553, 'frontend/listing/box_article', 1, 1, 'reducedPrice', 'Statt: ', '2010-12-08 02:52:32', '2010-12-08 02:52:32'),
(1554, 'backend/index/menu', 1, 2, 'Alle schliessen', 'Close all', '2011-03-31 11:47:42', '2011-03-31 11:47:42'),
(1555, 'backend/index/menu', 1, 2, 'Anlegen', 'New', '2011-03-31 11:48:05', '2011-03-31 11:48:56'),
(1556, 'backend/index/menu', 1, 2, 'Artikel', 'Products', '2011-03-31 11:49:30', '2011-04-01 11:42:15'),
(1557, 'backend/index/menu', 1, 2, 'Artikel + Kategorien', 'Products + Categories', '2011-03-31 11:50:05', '2011-03-31 11:50:05'),
(1558, 'backend/index/menu', 1, 2, 'Einstellungen', 'Settings', '2011-03-31 11:50:26', '2011-03-31 11:50:26'),
(1559, 'backend/auth/login_panel', 1, 2, 'UserNameField', 'User', '2011-04-01 11:34:47', '2011-04-01 11:36:30'),
(1560, 'backend/auth/login_panel', 1, 2, 'PasswordMessage', 'Please enter a password!', '2011-04-01 11:35:29', '2011-04-01 11:36:08'),
(1561, 'backend/auth/login_panel', 1, 2, 'UserNameMessage', 'Please enter a user name!', '2011-04-01 11:35:57', '2011-04-01 11:36:28'),
(1562, 'backend/index/index', 1, 2, 'SearchLabel', 'Search', '2011-04-01 11:37:50', '2011-04-01 11:39:30'),
(1563, 'backend/index/index', 1, 2, 'AccountMissing', 'No account created!', '2011-04-01 11:38:03', '2011-04-01 11:39:25'),
(1564, 'backend/index/index', 1, 2, 'UserLabel', 'User: {$UserName}', '2011-04-01 11:38:20', '2011-04-01 11:39:31'),
(1565, 'backend/index/index', 1, 2, 'LiveViewLabel', 'Shop view', '2011-04-01 11:38:40', '2011-04-01 11:39:26'),
(1566, 'backend/index/index', 1, 2, 'AccountBalance', 'Balance: {$Amount} SC', '2011-04-01 11:38:57', '2011-04-01 11:39:24'),
(1567, 'backend/index/menu', 1, 2, 'Fenster', 'Window', '2011-04-01 11:39:53', '2011-04-01 11:40:07'),
(1568, 'backend/index/menu', 1, 2, 'Inhalte', 'Content', '2011-04-01 11:40:43', '2011-04-01 11:40:47'),
(1569, 'backend/index/menu', 1, 2, 'Hilfe', 'Help', '2011-04-01 11:41:03', '2011-04-01 11:41:08'),
(1570, 'backend/index/menu', 1, 2, 'Kunden', 'Customers', '2011-04-01 11:41:58', '2011-04-01 11:42:04'),
(1571, 'backend/auth/login_panel', 1, 2, 'LoginButton', 'Login', '2011-04-01 11:37:09', '2011-04-01 11:37:09'),
(1572, 'backend/auth/login_panel', 1, 2, 'LocaleField', 'Language', '2011-04-01 11:37:32', '2011-04-01 11:37:32'),
(1573, 'backend/auth/login_panel', 1, 2, 'PasswordField', 'Password', '2011-04-01 11:37:32', '2011-04-01 11:37:32'),
(1574, 'frontend/account/password', 1, 1, 'PasswordSendAction', 'Passwort anfordern', '2011-05-17 11:47:42', '2011-05-17 11:47:42'),
(1575, 'frontend/listing/box_article', 1, 1, 'ListingBoxArticleContent', 'Inhalt', '2011-05-24 10:31:14', '2011-05-24 10:31:47'),
(1576, 'frontend/listing/box_article', 1, 1, 'ListingBoxBaseprice', 'Grundpreis', '2011-05-24 10:33:36', '2011-05-24 10:33:55'),
(1577, 'frontend/note/item', 1, 1, 'NoteUnitPriceContent', 'Inhalt', '2011-05-24 11:25:13', '2011-05-24 11:26:33'),
(1578, 'frontend/note/item', 1, 1, 'NoteUnitPriceBaseprice', 'Grundpreis', '2011-05-24 11:25:13', '2011-05-24 11:26:46'),
(1579, 'frontend/compare/col', 1, 1, 'CompareContent', 'Inhalt', '2011-05-24 11:51:10', '2011-05-24 11:51:36'),
(1580, 'frontend/compare/col', 1, 1, 'CompareBaseprice', 'Grundpreis', '2011-05-24 11:51:10', '2011-05-24 11:51:46'),
(1581, 'frontend/account/order_item', 1, 1, 'OrderItemInfoContent', 'Inhalt', '2011-05-24 13:11:55', '2011-05-24 13:51:56'),
(1582, 'frontend/account/order_item', 1, 1, 'OrderItemInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(1583, 'frontend/account/order_item', 1, 1, 'OrderItemInfoCurrentPrice', 'Aktueller Einzelpreis', '2011-05-24 14:22:31', '2011-05-24 14:22:59'),
(1584, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(1585, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoContent', 'Inhalt', '2011-05-24 14:22:31', '2011-05-24 14:22:59'),
(1586, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalRequiredText', '* hierbei handelt es sich um ein Pflichtfeld', '2011-05-24 17:12:28', '2011-05-24 17:13:52'),
(1587, 'frontend/account/internalMessages', 1, 1, 'LoginFailureLocked', 'Zu viele fehlgeschlagene Versuche. Ihr Account wurde vorübergehend deaktivert - bitte probieren Sie es in einigen Minuten erneut!', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1588, 'frontend/account/internalMessages', 1, 1, 'LoginFailureActive', 'Ihr Kundenkonto wurde deaktiviert, bitte wenden Sie sich zwecks Klärung persönlich an uns!', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1589, 'frontend/account/internalMessages', 1, 1, 'LoginFailure', 'Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1590, 'frontend/account/internalMessages', 1, 1, 'ErrorFillIn', 'Bitte füllen Sie alle rot markierten Felder aus', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1591, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureNotFound', 'Diese eMail-Adresse wurde nicht gefunden', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1592, 'frontend/account/internalMessages', 1, 1, 'NewsletterMailDeleted', 'Ihre eMail-Adresse wurde gelöscht', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1593, 'frontend/account/internalMessages', 1, 1, 'NewsletterSuccess', 'Vielen Dank. Wir haben Ihre Adresse eingetragen.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1594, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureAlreadyRegistered', 'Sie erhalten unseren Newsletter bereits', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1595, 'frontend/account/internalMessages', 1, 1, 'UnknownError', 'Ein unbekannter Fehler ist aufgetreten', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1596, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureInvalid', 'Bitte geben Sie eine gültige eMail-Adresse ein', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1597, 'frontend/account/internalMessages', 1, 1, 'NewsletterFailureMail', 'Bitte geben sie eine eMail-Adresse an', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1598, 'frontend/account/internalMessages', 1, 1, 'VatFailureDate', 'Die eingegebene USt-IdNr. ist ungültig. Sie ist erst ab dem %s gültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1599, 'frontend/account/internalMessages', 1, 1, 'VatFailureUnknownError', 'Es ist ein unerwarteter Fehler bei der Überprüfung der USt-IdNr. aufgetreten. Bitte kontaktieren Sie den Shopbetreiber. Fehlercode: %d', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1600, 'frontend/account/internalMessages', 1, 1, 'VatFailureErrorField', 'Das Feld %s passt nicht zur USt-IdNr.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1601, 'frontend/account/internalMessages', 1, 1, 'VatFailureErrorFields', 'Firma,Ort,PLZ,Straße,Land', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1602, 'frontend/account/internalMessages', 1, 1, 'VatFailureInvalid', 'Die eingegebene USt-IdNr. ist ungültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1603, 'frontend/account/internalMessages', 1, 1, 'VatFailureEmpty', 'Bitte geben Sie eine USt-IdNr. an.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1604, 'frontend/account/internalMessages', 1, 1, 'MailFailureNotEqual', 'Die eMail-Adressen stimmen nicht überein.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1605, 'frontend/account/internalMessages', 1, 1, 'MailFailure', 'Bitte geben Sie eine gültige eMail-Adresse ein', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1606, 'frontend/account/internalMessages', 1, 1, 'MailFailureAlreadyRegistered', 'Diese eMail-Adresse ist bereits registriert', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1607, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureMinimumCharge', 'Der Mindestumsatz für diesen Gutschein beträgt {sMinimumCharge} €', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1608, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureSupplier', 'Dieser Gutschein ist nur für Produkte von {sSupplier} gültig', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1609, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureProducts', 'Dieser Gutschein ist nur für bestimmte Produkte gültig.', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1610, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureCustomerGroup', 'Dieser Gutschein ist für Ihre Kundengruppe nicht verfügbar', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1611, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureOnlyOnes', 'Pro Bestellung kann nur ein Gutschein eingelöst werden', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1612, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureNotFound', 'Gutschein konnte nicht gefunden werden oder ist nicht mehr gültig', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1613, 'frontend/basket/internalMessages', 1, 1, 'VoucherFailureAlreadyUsed', 'Dieser Gutschein wurde bereits bei einer vorherigen Bestellung eingelöst', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1614, 'frontend/ticket/internalMessages', 1, 1, 'TicketFailureFields', 'Bitte füllen Sie alle Felder aus!', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1615, 'frontend/ticket/internalMessages', 1, 1, 'TicketReplySuccessful', 'Ihre Antwort wurde erfolgreich übertragen!', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1616, 'frontend/account/password', 1, 1, 'ErrorForgotMail', 'Bitte geben Sie Ihre eMail-Adresse ein', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1617, 'frontend/account/password', 1, 1, 'ErrorForgotMailUnknown', 'Diese Mailadresse ist uns nicht bekannt', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1618, 'frontend/account/content_right', 1, 1, 'AccountLinkPartnerStatistic', 'Provisionen', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1619, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticHeader', 'Provisions Übersicht', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1620, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelFromDate', 'Von:', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1621, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelToDate', 'Bis:', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1623, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnDate', 'Datum', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1624, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnId', 'Bestellnummer', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1625, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnNetAmount', 'Netto Umsatz', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1626, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticColumnProvision', 'Provision', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1627, 'frontend/account/partner_statistic', 1, 1, 'Provisions', 'Provisionen', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1628, 'frontend/account/partner_statistic_item', 1, 1, 'PartnerStatisticItemSum', 'Gesamtsumme:', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1629, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticSubmitFilter', 'Filtern', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1630, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticInfoEmpty', 'Keine Auswertung vorhanden', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1631, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelTimeUnit', 'KW', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1632, 'frontend/account/partner_statistic', 1, 1, 'PartnerStatisticLabelNetTurnover', 'Netto-Umsatz', '2012-06-22 12:59:53', '2012-06-25 16:54:02'),
(1633, 'frontend/detail/comment', 1, 1, 'InquiryTextArticle', 'Ich habe folgende Fragen zum Artikel', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1634, 'frontend/checkout/finish_item', 1, 1, 'CartItemInfoFree', 'Kostenlos', '2012-08-27 22:28:57', '2012-08-27 22:28:57'),
(1635, 'frontend/blog/filter', 1, 1, 'BlogHeaderFilterTags', 'Tags', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(1636, 'frontend/blog/filter', 1, 2, 'BlogHeaderFilterTags', 'tags', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1637, 'frontend/detail/description', 1, 1, 'DetailDescriptionSupplier', 'Hersteller-Beschreibung', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1638, 'frontend/detail/description', 1, 2, 'DetailDescriptionSupplier', 'Supplier description', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(1640, 'frontend/blog/comments', 1, 1, 'DetailCommentTextReview', 'Kommentare werden nach Überprüfung freigeschaltet.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(1641, 'frontend/blog/comments', 1, 2, 'DetailCommentTextReview', 'Comments will be released after verification.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1642, 'frontend/index/index', 1, 2, 'IndexLinkDefault', 'Switch to homepage', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1643, 'frontend/compare/index', 1, 2, 'CompareActionStart', 'Start comparison', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1644, 'frontend/compare/index', 1, 2, 'CompareActionDelete', 'Delete comparison', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1645, 'frontend/index/checkout_actions', 1, 2, 'IndexLinkCart', 'Shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1646, 'frontend/index/checkout_actions', 1, 2, 'IndexInfoArticles', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1647, 'frontend/index/checkout_actions', 1, 2, 'IndexActionShowPositions', 'Display positions', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1648, 'frontend/account/content_right', 1, 2, 'AccountLinkOverview', 'My account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1649, 'frontend/checkout/finish', 1, 2, 'FinishTitleRightOfRevocation', 'Cancellation right', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1650, 'frontend/index/checkout_actions', 1, 2, 'IndexLinkNotepad', 'Wish list', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1651, 'frontend/checkout/confirm', 1, 2, 'ConfirmErrorAGB', 'Please confirm the general terms and conditions', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1653, 'frontend/blog/detail', 1, 2, 'BlogHeaderSocialmedia', 'Recommend', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1654, 'frontend/index/breadcrumb', 1, 2, 'BreadcrumbDefault', 'You are here:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1656, 'frontend/plugins/index/viewlast', 1, 2, 'WidgetsRecentlyViewedLinkDetails', 'Further information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1657, 'frontend/blog/box', 1, 2, 'BlogInfoFrom', 'From:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1658, 'frontend/blog/box', 1, 2, 'BlogInfoComments', 'Comments', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1659, 'frontend/blog/box', 1, 2, 'BlogLinkMore', 'Read more', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1660, 'frontend/index/footer', 1, 2, 'FooterInfoIncludeVat', 'All prices incl. value added tax ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1661, 'frontend/checkout/finish', 1, 2, 'FinishTextRightOfRevocation', 'Information on cancellation right', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1662, 'frontend/listing/listing_actions', 1, 2, 'ListingLabelSort', 'Sorting', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1663, 'frontend/listing/listing_actions', 1, 2, 'ListingSortRelease', 'Release date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1664, 'frontend/listing/listing_actions', 1, 2, 'ListingSortRating', 'Popularity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1665, 'frontend/listing/listing_actions', 1, 2, 'ListingSortPriceLowest', 'Minimum price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1666, 'frontend/listing/listing_actions', 1, 2, 'ListingSortPriceHighest', 'Maximum price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1667, 'frontend/listing/listing_actions', 1, 2, 'ListingSortName', 'Article description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1668, 'frontend/listing/listing_actions', 1, 2, 'ListingLabelItemsPerPage', 'Articles per page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1669, 'frontend/listing/listing_actions', 1, 2, 'ListingLabelView', 'View', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1670, 'frontend/listing/listing_actions', 1, 2, 'ListingViewTable', 'List', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1671, 'frontend/listing/listing_actions', 1, 2, 'ListingView2Cols', 'Two-columned', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1672, 'frontend/listing/listing_actions', 1, 2, 'ListingView3Cols', 'Three-columned', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1673, 'frontend/listing/listing_actions', 1, 2, 'ListingView4Cols', 'Four-columned', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1674, 'frontend/listing/box_article', 1, 2, 'ListingBoxNoPicture', 'No image available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1675, 'frontend/listing/box_article', 1, 2, 'ListingBoxLinkBuy', 'Order now', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1676, 'frontend/listing/box_article', 1, 2, 'ListingBoxLinkDetails', 'Go to article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1677, 'frontend/detail/navigation', 1, 2, 'DetailNavCount', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1678, 'frontend/detail/navigation', 1, 2, 'DetailNavIndex', 'Overview', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1679, 'frontend/account/ajax_login', 1, 2, 'LoginActionNext', 'Login', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1680, 'frontend/detail/index', 1, 2, 'DetailFrom', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1683, 'frontend/detail/buy', 1, 2, 'DetailBuyActionAdd', 'Add to shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1684, 'frontend/detail/actions', 1, 2, 'DetailLinkVoucher', 'Recommend article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1685, 'frontend/detail/actions', 1, 2, 'DetailLinkReview', 'Comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1686, 'frontend/detail/actions', 1, 2, 'DetailLinkNotepad', 'Add to wish list', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1687, 'frontend/detail/actions', 1, 2, 'DetailLinkContact', 'Do you have any questions concerning this article?', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1688, 'frontend/detail/tabs', 1, 2, 'DetailTabsDescription', 'Description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1689, 'frontend/detail/tabs', 1, 2, 'DetailTabsRating', 'Evaluations', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1690, 'frontend/detail/description', 1, 2, 'DetailDescriptionHeader', 'Article information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1691, 'frontend/blog/detail', 1, 2, 'BlogHeaderLinks', 'Further information on', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1692, 'frontend/detail/comment', 1, 2, 'DetailCommentHeader', 'Customer evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1693, 'frontend/detail/comment', 1, 2, 'DetailCommentHeaderWriteReview', 'Write an evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1694, 'frontend/detail/comment', 1, 2, 'DetailCommentTextReview', 'Evaluations will be activated after verification', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1695, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelName', 'Your name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1696, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelSummary', 'Summary', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1697, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelRating', 'Evaluation ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1698, 'frontend/detail/comment', 1, 2, 'Rate1', '1 very poor', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1699, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelText', 'Your opinion', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1700, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelCaptcha', 'Please enter the numbers in the following text field.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1701, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoFields', 'The fields marked with * are required.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1702, 'frontend/detail/comment', 1, 2, 'DetailCommentActionSave', 'Save', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1703, 'frontend/detail/similar', 1, 2, 'DetailSimilarHeader', 'Similar articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1704, 'frontend/search/paging', 1, 2, 'ListingSortRating', 'Evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1705, 'frontend/search/paging', 1, 2, 'ListingSortPriceLowest', 'Minimum price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1706, 'frontend/search/paging', 1, 2, 'ListingSortPriceHighest', 'Maximum price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1707, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeader', 'The article has been added to the shopping cart successfully', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1708, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLinkBack', 'Continue shopping', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1709, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLinkCart', 'View shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1710, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeaderCrossSelling', 'You may also like these articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1711, 'frontend/checkout/ajax_amount', 1, 2, 'AjaxAmountInfoCountArticles', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1712, 'frontend/account/ajax_login', 1, 2, 'LoginHeader', 'An online order is simple', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1713, 'frontend/account/ajax_login', 1, 2, 'LoginLabelMail', 'Your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1714, 'frontend/account/ajax_login', 1, 2, 'LoginLabelNew', 'New customer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1715, 'frontend/account/ajax_login', 1, 2, 'LoginLabelExisting', 'I am already customer and my password is', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1716, 'frontend/account/ajax_login', 1, 2, 'LoginActionClose', 'Close window', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1717, 'frontend/detail/navigation', 1, 2, 'DetailNavNext', 'Next  ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1718, 'frontend/listing/listing_actions', 1, 2, 'ListingTextFrom', 'From ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1719, 'frontend/listing/listing_actions', 1, 2, 'ListingTextSite', 'Page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1720, 'frontend/listing/listing_actions', 1, 2, 'ListingLinkNext', 'Next page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1721, 'frontend/listing/box_article', 1, 2, 'ListingBoxArticleStartsAt', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1722, 'frontend/widgets/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1723, 'frontend/register/personal_fieldset', 1, 2, 'RegisterPersonalHeadline', 'Your personal information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1724, 'frontend/register/personal_fieldset', 1, 2, 'RegisterPersonalLabelType', 'I am  ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1725, 'frontend/register/personal_fieldset', 1, 2, 'RegisterPersonalLabelPrivate', 'Private customer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1726, 'frontend/register/personal_fieldset', 1, 2, 'RegisterPersonalLabelBusiness', 'Company', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1727, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelSalutation', 'Title*', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1728, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1729, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelFirstname', 'First name*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1730, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelLastname', 'Last name*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1731, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelNoAccount', 'Don''t create customer account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1732, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelMail', 'Your e-mail address*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1733, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelMailConfirmation', 'Reenter your e-mail address*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1734, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelPassword', 'Your password*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1735, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelPasswordRepeat', 'Reenter your password*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1736, 'frontend/register/personal_fieldset', 1, 2, 'RegisterInfoPassword', 'Your password must contain at least', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1737, 'frontend/register/personal_fieldset', 1, 2, 'RegisterInfoPassword2', 'The search is case sensitive.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1738, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelPhone', 'Phone*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1739, 'frontend/register/personal_fieldset', 1, 2, 'RegisterLabelBirthday', 'Date of birth:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1740, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoNotAvailable', 'These options are not available!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1741, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoShippingfree', 'Free of shipping costs!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1742, 'frontend/detail/data', 1, 2, 'DetailDataInfoArticleStartsAt', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1743, 'frontend/blog/filter', 1, 2, 'BlogHeaderFilterProperties', 'Filter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1744, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingHeadline', 'Your address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1745, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelStreet', 'Street and number*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1746, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelCity', 'Zipcode and city*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1747, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelCountry', 'Country*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1749, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelShipping', 'The <strong>shipping address</strong> does not match with the billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1750, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingHeadline', 'Your alternative shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1751, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelSalutation', 'Title*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1752, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelCompany', 'Company', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1753, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelDepartment', 'Department', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1754, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelFirstname', 'First name*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1755, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelLastname', 'Last name*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1756, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelStreet', 'Street and number*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1757, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelCity', 'Postal code and city*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1758, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelCountry', 'Country*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1760, 'frontend/register/payment_fieldset', 1, 2, 'RegisterPaymentHeadline', 'Please select your preferred type of payment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1761, 'frontend/plugins/payment/debit', 1, 2, 'PaymentDebitLabelAccount', 'Account number*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1762, 'frontend/plugins/payment/debit', 1, 2, 'PaymentDebitLabelBankcode', 'Bank identification number*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1763, 'frontend/plugins/payment/debit', 1, 2, 'PaymentDebitLabelBankname', 'Name of bank*: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1764, 'frontend/plugins/payment/debit', 1, 2, 'PaymentDebitLabelName', 'Account holder*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1765, 'frontend/plugins/payment/debit', 1, 2, 'PaymentDebitInfoFields', 'The fields marked with * are required.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1766, 'frontend/register/index', 1, 2, 'RegisterInfoAdvantages', '<h2>My advantages</h2>\n<ul>\n<li>Faster shopping</li>\n<li>Save your user data and settings</li>\n<li>View your orders incl. shipment information</li>\n<li>Manage your newsletter subscription</li>\n</ul>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1767, 'frontend/detail/navigation', 1, 2, 'DetailNavPrevious', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1768, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoFillOutFields', 'Please complete all fields marked in red', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1769, 'frontend/listing/filter_supplier', 1, 2, 'FilterSupplierHeadline', 'Manufacturer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1770, 'frontend/listing/listing', 1, 2, 'ListingInfoFilterSupplier', 'Products from', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1771, 'frontend/listing/listing', 1, 2, 'ListingLinkAllSuppliers', 'Show all manufacturers', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1772, 'frontend/custom/right.tpl', 1, 2, 'CustomHeader', 'Direct contact', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1773, 'frontend/forms/index', 1, 2, 'FormsTextContact', '<strong>Demoshop<br />\n</strong><br />\nEnter your contact details here', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1774, 'frontend/checkout/shipping_costs', 1, 2, 'ShippingHeader', 'Shipping fee calculation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1775, 'frontend/checkout/shipping_costs', 1, 2, 'ShippingLabelDeliveryCountry', '1. Supplier country', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1776, 'frontend/checkout/shipping_costs', 1, 2, 'ShippingLabelPayment', '2. Payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1777, 'frontend/checkout/shipping_costs', 1, 2, 'ShipppingLabelDispatch', '3. Shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1778, 'frontend/account/shipping', 1, 2, 'ShippingLinkSend', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1779, 'frontend/checkout/actions', 1, 2, 'CheckoutActionsLinkProceed', 'Proceed to checkout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1780, 'frontend/index/checkout_actions', 1, 2, 'IndexLinkCheckout', 'Proceed to checkout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1781, 'frontend/blog/detail', 1, 2, 'BlogHeaderCrossSelling', 'Related articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1782, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmHeaderBilling', 'Billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1783, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmLinkChangeBilling', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1784, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmLinkSelectBilling', 'Others', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1785, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmHeaderShipping', 'Shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1786, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmLinkChangeShipping', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1787, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmLinkSelectShipping', 'Others', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1788, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmHeaderPayment', 'Selected payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1789, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmInfoInstantDownload', 'Purchase of instant downloads by debit or credit card only', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1790, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmLinkChangePayment', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1791, 'frontend/account/billing', 1, 2, 'BillingLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1792, 'frontend/account/content_right', 1, 2, 'AccountHeaderNavigation', 'My account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1793, 'frontend/ticket/listing', 1, 2, 'TicketTitle', 'Ticket system', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1794, 'frontend/account/content_right', 1, 2, 'AccountLinkPreviousOrders', 'My orders ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1795, 'frontend/account/content_right', 1, 2, 'AccountLinkDownloads', 'My instant downloads', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1796, 'frontend/account/content_right', 1, 2, 'AccountLinkBillingAddress', 'Change billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1797, 'frontend/account/content_right', 1, 2, 'AccountLinkShippingAddress', 'Change shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1798, 'frontend/account/content_right', 1, 2, 'AccountLinkPayment', 'Change payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1799, 'frontend/account/content_right', 1, 2, 'AccountLinkNotepad', 'Wish list', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1800, 'frontend/account/content_right', 1, 2, 'AccountLinkLogout', 'Logout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1801, 'frontend/account/index', 1, 2, 'AccountHeaderWelcome', 'Welcome', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1802, 'frontend/account/index', 1, 2, 'AccountHeaderInfo', 'This is your account dashboard which enables you to view your accoount activities.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1803, 'frontend/account/success_messages', 1, 2, 'AccountAccountSuccess', 'The access data have been saved successfully.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1804, 'frontend/account/index', 1, 2, 'AccountHeaderBasic', 'User information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1805, 'frontend/account/index', 1, 2, 'AccountLinkChangePassword', 'Change password', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1806, 'frontend/account/index', 1, 2, 'AccountLinkChangePayment', 'Change payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1807, 'frontend/account/index', 1, 2, 'AccountLabelNewPassword', 'New password*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1808, 'frontend/account/index', 1, 2, 'AccountLabelRepeatPassword', 'Reenter password*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1809, 'frontend/account/index', 1, 2, 'AccountHeaderNewsletter', 'Your newsletter settings', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1810, 'frontend/account/index', 1, 2, 'AccountLabelWantNewsletter', 'Yes, I would like to subscribe to the free {$sShopname} newsletter. You have the possibility to unsubscribe at any time.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1811, 'frontend/account/success_messages', 1, 2, 'AccountPaymentSuccess', 'Your payment method has been saved successfully.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1812, 'frontend/account/index', 1, 2, 'AccountHeaderPrimaryBilling', 'Primary billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1813, 'frontend/account/index', 1, 2, 'AccountLinkSelectBilling', 'Select others', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1814, 'frontend/account/index', 1, 2, 'AccountHeaderPrimaryShipping', 'Primary shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1815, 'frontend/account/index', 1, 2, 'AccountLinkSelectShipping', 'Select others', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1816, 'frontend/account/orders', 1, 2, 'OrdersHeader', 'Sort orders by date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1817, 'frontend/account/downloads', 1, 2, 'DownloadsColumnDate', 'Date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1818, 'frontend/account/orders', 1, 2, 'OrderColumnId', 'Order number: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1819, 'frontend/account/orders', 1, 2, 'OrderColumnDispatch', 'Shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1820, 'frontend/account/orders', 1, 2, 'OrderColumnStatus', 'Order status', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1821, 'frontend/account/orders', 1, 2, 'OrderColumnActions', 'Special offers', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1822, 'frontend/account/order_item', 1, 2, 'OrderItemInfoNotProcessed', 'Your order has not been processed yet.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1823, 'frontend/account/order_item', 1, 2, 'OrderActionSlide', 'View', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1824, 'frontend/account/downloads', 1, 2, 'DownloadsColumnName', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1825, 'frontend/account/order_item', 1, 2, 'OrderItemColumnQuantity', 'Quantity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1826, 'frontend/account/order_item', 1, 2, 'OrderItemColumnPrice', 'Price per unit', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1827, 'frontend/account/order_item', 1, 2, 'OrderItemColumnTotal', 'Sum', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1828, 'frontend/account/order_item', 1, 2, 'OrderItemColumnId', 'Order number: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1829, 'frontend/account/order_item', 1, 2, 'OrderItemColumnDispatch', 'Shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1830, 'frontend/account/order_item', 1, 2, 'OrderLinkRepeat', 'Repeat order', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1831, 'frontend/account/order_item', 1, 2, 'OrderItemShippingcosts', 'Shipping costs:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1832, 'frontend/account/order_item', 1, 2, 'OrderItemTotal', 'Total sum:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1833, 'frontend/account/downloads', 1, 2, 'DownloadsHeader', 'Sort instant downloads by date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1834, 'frontend/note/index', 1, 2, 'NoteHeadline', 'Wish list', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1835, 'frontend/note/index', 1, 2, 'NoteText', 'Save your personal favorits until your next visit', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1836, 'frontend/note/index', 1, 2, 'NoteText2', 'Simply add a desired article to the wish list and {$sShopname} will save it for you. Thus you are able to call up your selected articles the next time you visit the online shop. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1837, 'frontend/note/index', 1, 2, 'NoteColumnName', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1838, 'frontend/note/index', 1, 2, 'NoteColumnPrice', 'Price per unit', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1839, 'frontend/checkout/error_messages', 1, 2, 'ConfirmInfoPaymentNotCompatibleWithESD', 'This payment method is not available for instant downloads.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1840, 'frontend/checkout/cart', 1, 2, 'CartTitle', 'Shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1841, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLabelQuantity', 'Quantity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1843, 'frontend/account/logout', 1, 2, 'LogoutInfoFinished', 'You have been logged out successfully.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1844, 'frontend/account/logout', 1, 2, 'LogoutLinkHomepage', 'Switch to homepage', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1845, 'frontend/checkout/ajax_cart', 1, 2, 'AjaxCartLinkBasket', 'View shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1846, 'frontend/search/paging', 1, 2, 'ListingSortRelevance', 'Relevance', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1847, 'frontend/search/paging', 1, 2, 'ListingLabelSort', 'Sorting', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1848, 'frontend/newsletter/index', 1, 2, 'sNewsletterOptionSubscribe', 'Subscribe to newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1849, 'frontend/newsletter/index', 1, 2, 'sNewsletterOptionUnsubscribe', 'Unsubscribe to newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1850, 'frontend/newsletter/index', 1, 2, 'sNewsletterLabelMail', 'Your e-mail address*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1851, 'frontend/newsletter/index', 1, 2, 'sNewsletterInfo', 'Subscribe now to our regulary released newsletter and be informed about the latest products and special offers. You are able to unsubscribe via link included in this e-mail or via website at any time. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1852, 'frontend/newsletter/index', 1, 2, 'sNewsletterButton', 'Save', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1853, 'frontend/listing/box_article', 1, 2, 'ListingBoxTip', 'Hint!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1854, 'frontend/listing/box_article', 1, 2, 'ListingBoxInstantDownload', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1855, 'frontend/detail/liveshopping/ticker/countdown', 1, 2, 'LiveTickerCurrentPrice', 'Current price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1856, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeDays', 'Days', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1857, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeHours', 'Hours', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1858, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeMinutes', 'Minutes', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1859, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeSeconds', 'Seconds', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1861, 'frontend/search/fuzzy', 1, 2, 'SearchFuzzyHeadlineEmpty', 'No results', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1863, 'frontend/index/index', 1, 2, 'IndexRealizedWith', 'Realized by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1864, 'frontend/index/menu_left', 1, 2, 'MenuLeftHeading', 'Information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1865, 'frontend/widgets/advanced_menu/index', 1, 2, 'IndexLinkHome', 'Home', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1866, 'frontend/widgets/compare/index', 1, 2, 'ListingBoxLinkCompare', 'Compare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1869, 'frontend/listing/box_similar', 1, 2, 'SimilarBoxLinkCompare', 'Compare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1870, 'frontend/tellafriend/index', 1, 2, 'TellAFriendHeadline', 'Recommend', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1871, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLabelName', 'Your name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1872, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLabelMail', 'Your e-mail address*:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1874, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLabelComment', 'Your comment:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1875, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLabelCaptcha', 'Captcha', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1876, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1877, 'frontend/tellafriend/index', 1, 2, 'TellAFriendActionSubmit', 'Send', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1878, 'frontend/forms/elements', 1, 2, 'SupportLabelCaptcha', 'Please enter the numbers in the following text field.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1879, 'frontend/forms/elements', 1, 2, 'SupportLabelInfoFields', 'The fields marked with * are required.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1880, 'frontend/forms/elements', 1, 2, 'SupportActionSubmit', 'Send', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1881, 'frontend/compare/index', 1, 2, 'CompareInfoCount', 'Compare article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1882, 'frontend/compare/col_description', 1, 2, 'CompareColumnPicture', 'Image', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1883, 'frontend/compare/col_description', 1, 2, 'CompareColumnName', 'Name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1884, 'frontend/compare/col_description', 1, 2, 'CompareColumnRating', 'Evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1885, 'frontend/compare/col_description', 1, 2, 'CompareColumnDescription', 'Description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1886, 'frontend/compare/col_description', 1, 2, 'CompareColumnPrice', 'Price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1887, 'frontend/compare/overlay', 1, 2, 'CompareActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1888, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoSuccess', 'Thank you for evaluating our article! The article will be activated after verification.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1889, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoAverageRate', 'Average customer evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1890, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoRating', 'from {$sArticle.sVoteAverange.count} customer evaluations', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1891, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddErrorHeader', 'The article could not be added to the shopping cart. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1892, 'frontend/detail/data', 1, 2, 'DetailDataInfoSavePercent', 'Saved', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1893, 'frontend/detail/related', 1, 2, 'DetailRelatedHeader', 'Complementary articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1894, 'frontend/account/index', 1, 2, 'AccountTitle', 'Customer account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1895, 'frontend/note/item', 1, 2, 'NoteLinkDetails', 'View article ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1896, 'frontend/note/item', 1, 2, 'NoteLinkCompare', 'Compare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1897, 'frontend/note/item', 1, 2, 'NoteInfoId', 'Order number: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1898, 'frontend/note/item', 1, 2, 'NoteLinkDelete', 'Delete', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1899, 'frontend/note/item', 1, 2, 'NoteLinkBuy', 'Purchase', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1901, 'frontend/detail/data', 1, 2, 'DetailDataInfoContent', 'Content:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1902, 'frontend/detail/data', 1, 2, 'DetailDataInfoBaseprice', 'Basis price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1903, 'frontend/compare/added', 1, 2, 'CompareHeaderTitle', 'Compare articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1904, 'frontend/compare/added', 1, 2, 'LoginActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1905, 'frontend/detail/article_config_upprice', 1, 2, 'DetailConfigActionSubmit', 'Update now', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1906, 'frontend/newsletter/detail', 1, 2, 'NewsletterDetailLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1907, 'frontend/newsletter/detail', 1, 2, 'NewsletterDetailLinkNewWindow', 'Open the newsletter in a new window', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1908, 'frontend/blog/detail', 1, 2, 'BlogInfoCategories', 'Category assignment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1909, 'frontend/blog/detail', 1, 2, 'BlogLinkComments', 'View comments ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1910, 'frontend/blog/detail', 1, 2, 'BlogInfoComments', 'Comments', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1911, 'frontend/blog/detail', 1, 2, 'BlogHeaderRating', 'Evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1912, 'frontend/blog/box', 1, 2, 'BlogInfoRating', 'Evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1913, 'frontend/blog/detail', 1, 2, 'BlogInfoFrom', 'From:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1914, 'frontend/blog/comments', 1, 2, 'BlogHeaderWriteComment', 'Write a comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1915, 'frontend/blog/comments', 1, 2, 'BlogInfoFields', 'The fields marked with * are required.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1916, 'frontend/blog/comments', 1, 2, 'BlogLabelName', 'Your name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1917, 'frontend/blog/comments', 1, 2, 'BlogLabelMail', 'Your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1918, 'frontend/blog/comments', 1, 2, 'BlogLabelRating', 'Evaluation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1919, 'frontend/blog/comments', 1, 2, 'rate1', '1 very poor', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1920, 'frontend/blog/comments', 1, 2, 'BlogLabelSummary', 'Summary', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1921, 'frontend/blog/comments', 1, 2, 'BlogLabelComment', 'Your opinion:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1922, 'frontend/blog/comments', 1, 2, 'BlogLabelCaptcha', 'Please enter the numbers in the following text field.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1923, 'frontend/blog/comments', 1, 2, 'BlogLinkSaveComment', 'Save', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1924, 'frontend/checkout/cart', 1, 2, 'CartInfoFreeShipping', 'FREE OF SHIPPING COSTS!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1925, 'frontend/checkout/cart', 1, 2, 'CartInfoFreeShippingDifference', 'Order for another {$sShippingcostsDifference|currency} in order to receive your order free of shipping costs.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1926, 'frontend/checkout/cart_header', 1, 2, 'CartColumnName', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1927, 'frontend/checkout/cart_header', 1, 2, 'CartColumnAvailability', 'Availability', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1928, 'frontend/checkout/cart_header', 1, 2, 'CartColumnPrice', 'Price per unit', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1930, 'frontend/checkout/cart_item', 1, 2, 'CartItemLinkDelete', 'Delete', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1931, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterActionAddVoucher', 'Add', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1932, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterLabelAddArticle', 'Add article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1934, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterActionAdd', 'Add', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1935, 'frontend/checkout/cart_footer', 1, 2, 'CartFooterSum', 'Sum', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1936, 'frontend/checkout/cart_footer', 1, 2, 'CartFooterShipping', 'Shipping costs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1937, 'frontend/checkout/cart_footer', 1, 2, 'CartFooterTotal', 'Total amount', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1938, 'frontend/checkout/actions', 1, 2, 'CheckoutActionsLinkLast', 'Continue shopping', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1939, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeader', 'Please double-check your order before sending. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1940, 'frontend/checkout/confirm', 1, 2, 'ConfirmInfoChange', 'You can still change the billing address, shipping address and payment method.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1941, 'frontend/checkout/confirm', 1, 2, 'ConfirmInfoPaymentData', '<strong>\nOur bank account:\n</strong>\nVolksbank Musterstadt\nBank identification number:\nAccount number:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1942, 'frontend/checkout/confirm_header', 1, 2, 'CheckoutColumnTax', 'Included VAT:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1943, 'frontend/checkout/confirm', 1, 2, 'ConfirmLabelComment', 'Comment: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1944, 'frontend/checkout/confirm', 1, 2, 'ConfirmTerms', 'I have read the <a href="{url controller=custom sCustom=4 forceSecure}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> of your shop and do agree.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1945, 'frontend/checkout/confirm', 1, 2, 'ConfirmTextOrderDefault', 'Optional free text If you pay direct debit or credit card, the debiting of the account takes place 5 days after your order. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1946, 'frontend/checkout/confirm', 1, 2, 'ConfirmActionSubmit', 'Send order', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1947, 'frontend/account/password', 1, 2, 'PasswordHeader', 'Forgot your password? Here you have the option to request a new password.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1948, 'frontend/account/password', 1, 2, 'PasswordLabelMail', 'Your e-mail address:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1949, 'frontend/account/password', 1, 2, 'PasswordText', 'We will send you a new, randomly generated password. You can change it in the customer area afterwards. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1950, 'frontend/account/password', 1, 2, 'PasswordLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1951, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleHeader', 'Save good money with our bundle offerings', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1952, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleActionAdd', 'Add to shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1953, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPriceForAll', 'Price for all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1954, 'frontend/detail/bundle/box_bundle', 1, 2, 'BundleInfoPriceInstead', 'instead of ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1955, 'frontend/detail/description', 1, 2, 'DetailDescriptionHeaderDownloads', 'Available downloads:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1956, 'frontend/detail/description', 1, 2, 'DetailDescriptionLinkDownload', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1957, 'frontend/checkout/premiums', 1, 2, 'PremiumsHeader', 'Please select one of the following premiums ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1958, 'frontend/newsletter/index', 1, 2, 'NewsletterTitle', 'Newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1959, 'frontend/checkout/premiums', 1, 2, 'PremiumActionAdd', 'Select premium ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1960, 'frontend/checkout/actions', 1, 2, 'CheckoutActionsLinkOffer', 'Request offer ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1961, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterHeadline', 'Subscribe to newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1962, 'frontend/account/login', 1, 2, 'LoginHeaderNew', 'You are new at', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1963, 'frontend/account/login', 1, 2, 'LoginInfoNew', 'No problem! A shop order is easy and secure. The registration only takes a few minutes. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1964, 'frontend/account/login', 1, 2, 'LoginLinkRegister', 'New customer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1965, 'frontend/account/login', 1, 2, 'LoginHeaderExistingCustomer', 'You already have a customer accoount.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1966, 'frontend/account/login', 1, 2, 'LoginHeaderFields', 'Log in with your e-mail address and your password', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1967, 'frontend/account/login', 1, 2, 'LoginLabelMail', 'Your e-mail address:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1968, 'frontend/account/login', 1, 2, 'LoginLabelPassword', 'Your password:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1969, 'frontend/account/login', 1, 2, 'LoginLinkLogon', 'Login', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1970, 'frontend/index/checkout_actions', 1, 2, 'IndexLinkAccount', 'My account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1971, 'frontend/checkout/cart', 1, 2, 'CartInfoEmpty', 'Your shopping cart does not contain any articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1972, 'frontend/account/index', 1, 2, 'AccountLinkChangeBilling', 'Change billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1973, 'frontend/account/index', 1, 2, 'AccountLinkChangeShipping', 'Change shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1974, 'frontend/account/order_item', 1, 2, 'OrderItemColumnName', 'Article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1975, 'frontend/account/orders', 1, 2, 'OrderColumnDate', 'Date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1976, 'frontend/account/downloads', 1, 2, 'DownloadsColumnLink', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1977, 'frontend/account/content_right', 1, 2, 'sTicketSysSupportManagement', 'Support management', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1978, 'frontend/account/downloads', 1, 2, 'DownloadsSerialnumber', 'Your serial number:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1979, 'frontend/account/downloads', 1, 2, 'DownloadsLink', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1980, 'frontend/account/downloads', 1, 2, 'DownloadsInfoAccessDenied', 'This download is currently not available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1981, 'frontend/account/downloads', 1, 2, 'DownloadsInfoNotFound', 'No download available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1982, 'frontend/account/downloads', 1, 2, 'DownloadsInfoEmpty', 'You have not purchased any instant downloads yet', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1983, 'frontend/account/index', 1, 2, 'AccountHeaderPayment', 'Selected payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1984, 'frontend/account/index', 1, 2, 'AccountInfoInstantDownloads', 'Purchase of instant downloads by debit or credit card only', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1985, 'frontend/account/shipping', 1, 2, 'ShippingLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1986, 'frontend/account/select_shipping', 1, 2, 'SelectShippingHeader', 'Select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1987, 'frontend/account/select_address', 1, 2, 'SelectAddressSubmit', 'Select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1988, 'frontend/account/select_address', 1, 2, 'SelectAddressSalutationMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1989, 'frontend/account/select_shipping', 1, 2, 'SelectShippingLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1990, 'frontend/account/payment', 1, 2, 'PaymentLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1991, 'frontend/account/index', 1, 2, 'AccountSalutationMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1992, 'frontend/checkout/cart_header', 1, 2, 'CartColumnQuantity', 'Quantity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1993, 'frontend/checkout/cart_header', 1, 2, 'CartColumnTotal', 'Sum', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1994, 'frontend/plugins/trusted_shops/logo', 1, 2, 'WidgetsTrustedLogo', 'Trusted Shop quality seal - Please check validity here!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1995, 'frontend/plugins/trusted_shops/logo', 1, 2, 'WidgetsTrustedLogoText', '<a title="More information on {config name=Shopname}" href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank"> {config name=Shopname} is a shop approved by the quality seal of Trusted Shops and <a href="http://www.trustedshops.de/info/garantiebedingungen/" target="_blank">buyer protection.</a> <a title="More information on " href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank">More...</a> </a>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1996, 'frontend/checkout/finish', 1, 2, 'FinishHeaderThankYou', 'Thank you for your order with', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1997, 'frontend/checkout/finish', 1, 2, 'FinishInfoConfirmationMail', 'We have sent you the order confirmation by e-mail.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1998, 'frontend/checkout/finish', 1, 2, 'FinishInfoPrintOrder', 'We recommend to print out the order confirmation below.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(1999, 'frontend/checkout/finish', 1, 2, 'FinishLinkPrint', 'Print out your order confirmation now', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2000, 'frontend/checkout/finish', 1, 2, 'FinishHeaderItems', 'Information on your order:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2001, 'frontend/checkout/finish', 1, 2, 'FinishInfoId', 'Order number: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2002, 'frontend/search/paging', 1, 2, 'ListingSortName', 'Description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2003, 'frontend/account/order_item', 1, 2, 'OrderInfoNoDispatch', 'Not stated', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2004, 'frontend/account/order_item', 1, 2, 'OrderItemInfoInProgress', 'The order is in process', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2005, 'frontend/account/order_item', 1, 2, 'OrderItemInfoShipped', 'The order has been sent', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2006, 'frontend/account/order_item', 1, 2, 'OrderItemInfoPartiallyShipped', 'The order has been sent in part', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2007, 'frontend/account/order_item', 1, 2, 'OrderItemInfoCanceled', 'The order has been cancelled', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2008, 'frontend/account/order_item', 1, 2, 'OrderItemInfoBundle', 'BUNDLE DISCOUNT', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2009, 'frontend/account/order_item', 1, 2, 'OrderItemInfoInstantDownload', 'Download now', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2010, 'frontend/account/order_item', 1, 2, 'OrderItemInfoFree', 'FOR FREE!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2011, 'frontend/account/order_item', 1, 2, 'OrderItemColumnTracking', 'Tracking your package:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2012, 'frontend/account/order_item', 1, 2, 'OrderItemNetTotal', 'Total sum (net)', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2013, 'frontend/account/orders', 1, 2, 'OrdersInfoEmpty', 'You have not recently ordered. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2014, 'frontend/account/ajax_logout', 1, 2, 'AccountLogoutHeader', 'Successful logout!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2015, 'frontend/account/ajax_logout', 1, 2, 'AccountLogoutText', 'You have been logged out successfully.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2016, 'frontend/custom/right.tpl', 1, 2, 'CustomTextContact', '<strong>Demoshop<br />\n</strong><br />\nEnter your contact details here', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2017, 'frontend/checkout/cart_item', 1, 2, 'CartItemInfoFree', 'For  free!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2018, 'frontend/checkout/cart_item', 1, 2, 'CartItemInfoPremium', 'As a small token of our thanks, you receive this article for free.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2019, 'frontend/checkout/cart_item', 1, 2, 'CartItemInfoBundle', 'Bundle discount', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2020, 'frontend/account/select_billing', 1, 2, 'SelectBillingLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2021, 'frontend/account/select_billing', 1, 2, 'SelectBillingHeader', 'Select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2022, 'frontend/account/select_billing', 1, 2, 'SelectBillingInfoEmpty', 'After you have completed your first order, you can access previous billing addresses here.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2023, 'frontend/account/select_shipping', 1, 2, 'SelectShippingInfoEmpty', 'After you have completed your first order, you can access previous shipping addresses here.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2024, 'frontend/blog/atom', 1, 2, 'BlogAtomFeedHeader', 'Blog/Atom feed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2025, 'frontend/blog/comments', 1, 2, 'BlogInfoFailureFields', 'Please complete all fields marked in red', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2026, 'frontend/blog/comments', 1, 2, 'BlogInfoSuccessOptin', 'Thank you for submitting your evaluation. You will receive a confirmation e-mail in just a few minutes. Please confirm the link including in the email in order to release your evaluation.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2027, 'frontend/blog/comments', 1, 2, 'BlogInfoSuccess', 'Thank you for submitting your evaluation. Your evaluation will be released after verification. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2028, 'frontend/blog/detail', 1, 2, 'BlogHeaderDownloads', 'Available downloads:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2029, 'frontend/blog/detail', 1, 2, 'BlogLinkDownload', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2030, 'frontend/blog/detail', 1, 2, 'BlogInfoComment', 'Our comment on', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2031, 'frontend/blog/detail', 1, 2, 'BlogInfoTags', 'Tags', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2032, 'frontend/blog/filter', 1, 2, 'BlogHeaderFilterCategories', 'Categories', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2033, 'frontend/blog/filter', 1, 2, 'BlogHeaderFilterDate', 'Date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2034, 'frontend/blog/filter', 1, 2, 'BlogHeaderFilterAuthor', 'Authors', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2035, 'frontend/blog/rss', 1, 2, 'BlogRssFeedHeader', 'Blog / RSS Feed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2036, 'frontend/checkout/added', 1, 2, 'AddArticleLinkBack', 'Continue shopping', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2037, 'frontend/checkout/ajax_cart', 1, 2, 'AjaxCartInfoBundle', 'BUNDLE DISCOUNT', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2038, 'frontend/checkout/ajax_cart', 1, 2, 'AjaxCartInfoFree', 'For free!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2039, 'frontend/checkout/cart', 1, 2, 'CartInfoMinimumSurcharge', 'Caution. You have not yet reached the minimum order value of {$sMinimumSurcharge|currency}.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2040, 'frontend/checkout/cart', 1, 2, 'CartInfoNoDispatch', 'Caution: There is no shipping type available for your shopping cart/address. <br /> Please contact the shop owner.<br />', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2041, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeaderNewsletter', 'Would you like more information?', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2042, 'frontend/checkout/confirm', 1, 2, 'ConfirmLabelNewsletter', 'Yes, I would like to subscribe to our free {$sShopname} newsletter. You have the possibility to unsubscribe at any time.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2043, 'frontend/checkout/confirm_left', 1, 2, 'ConfirmSalutationMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2044, 'frontend/checkout/finish', 1, 2, 'FinishInfoTransaction', 'Transaction number:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2045, 'frontend/checkout/premiums', 1, 2, 'PremiumInfoNoPicture', 'No image available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2046, 'frontend/checkout/premiums', 1, 2, 'PremiumsInfoDifference', 'Still', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2047, 'frontend/checkout/premiums', 1, 2, 'PremiumsInfoAtAmount', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2048, 'frontend/content/detail', 1, 2, 'ContentInfoPicture', 'Displayed on the picture', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2049, 'frontend/content/detail', 1, 2, 'ContentHeaderInformation', 'Further Information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2050, 'frontend/content/detail', 1, 2, 'ContentHeaderDownloads', 'Document attachment:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2051, 'frontend/content/detail', 1, 2, 'ContentLinkDownload', 'Download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2052, 'frontend/content/detail', 1, 2, 'ContentInfoNotFound', 'The content could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2053, 'frontend/content/detail', 1, 2, 'ContentLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2054, 'frontend/content/index', 1, 2, 'ContentInfoEmpty', 'Unfortunately, no entries are available at present.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2056, 'frontend/detail/article_config_step', 1, 2, 'DetailConfigActionSubmit', 'Update now', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2057, 'frontend/detail/bundle/box_related', 1, 2, 'BundleHeader', 'Buy this article bundled with ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2058, 'frontend/detail/bundle/box_related', 1, 2, 'BundleActionAdd', 'Add to shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2059, 'frontend/detail/bundle/box_related', 1, 2, 'BundleInfoPriceForAll', 'Prices for all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2060, 'frontend/compare/added', 1, 2, 'CompareActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2061, 'frontend/detail/buy', 1, 2, 'DetailBuyInfoNotAvailable', 'This article is currently not available.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2062, 'frontend/detail/buy', 1, 2, 'DetailBuyLabelSurcharge', 'Additional charge', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2063, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoSuccessOptin', 'Thank you for submitting your evaluation.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2064, 'frontend/detail/comment', 1, 2, 'DetailCommentLabelMail', 'Your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2065, 'frontend/compare/added', 1, 2, 'CompareInfoMaxReached', 'You can only compare a maximum of 5 items at a time', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2066, 'frontend/newsletter/index', 1, 2, 'NewsletterLabelSelect', 'Please select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2067, 'frontend/detail/data', 1, 2, 'DetailDataPriceInfo', 'Prices {if $sOutputNet} plus {else}incl.{/if} VAT <a title="shipping costs" href="{url controller=custom sCustom=6}" style="text-decoration:underline">plus shipping costs</a>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2068, 'frontend/detail/data', 1, 2, 'DetailDataHeaderBlockprices', 'Graduated prices', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2069, 'frontend/detail/data', 1, 2, 'DetailDataColumnQuantity', 'Quantity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2070, 'frontend/detail/data', 1, 2, 'DetailDataColumnPrice', 'Unit price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2071, 'frontend/detail/data', 1, 2, 'DetailDataInfoUntil', 'To', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2072, 'frontend/detail/data', 1, 2, 'DetailDataInfoFrom', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2073, 'frontend/detail/description', 1, 2, 'DetailDescriptionLinkInformation', 'Further articles by {$information.description}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2074, 'frontend/detail/description', 1, 2, 'DetailDescriptionComment', 'Our comment on', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2075, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownStartPrice', 'Starting price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2076, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownCurrentPrice', 'Current price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2077, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownRemaining', 'Still', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2078, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownRemainingPieces', 'Pieces ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2079, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownPriceFails', 'Price falls by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2080, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownMinutes', 'Minutes', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2081, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCountdownPriceRising', 'Price increases by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2082, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownStartPrice', 'Starting price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2083, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownCurrentPrice', 'Current price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2084, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownRemaining', 'Still', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2085, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownRemainingPieces', 'Pieces', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2086, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownPriceFails', 'Price falls by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2087, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownMinutes', 'Minutes', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2088, 'frontend/detail/liveshopping/detail_countdown', 1, 2, 'LiveCountdownPriceRising', 'Price increases by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2089, 'frontend/account/payment', 1, 2, 'PaymentLinkSend', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2090, 'frontend/account/orders', 1, 2, 'MyOrdersTitle', 'Orders', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2091, 'frontend/account/orders', 1, 2, 'AccountTitle', 'Customer account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2092, 'frontend/detail/liveshopping/ticker/countdown', 1, 2, 'LiveTickerStartPrice', 'Starting price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2093, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeRemaining', 'Still', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2094, 'frontend/detail/liveshopping/ticker/timeline', 1, 2, 'LiveTimeRemainingPieces', 'Pieces', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2095, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoErrorMail', 'Please enter a valid e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2096, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyHeader', 'Please inform me as soon as the article is available again.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2097, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyLabelMail', 'Your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2098, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyActionSubmit', 'Enter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2099, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoSuccess', 'Please confirm the link contained in the e-mail that you have just received. We will inform you as soon as the article is available again. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2100, 'frontend/forms/index', 1, 2, 'FormsLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2101, 'frontend/forms/elements', 1, 2, 'SupportInfoFillRedFields', 'Please fill out all fields that are marked red', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2102, 'frontend/tellafriend/index', 1, 2, 'TellAFriendHeaderSuccess', 'Thank you! The recommendation has been forwarded successfully!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2103, 'frontend/tellafriend/index', 1, 2, 'TellAFriendInfoFields', 'Please fill out all required fields', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2104, 'frontend/index/footer', 1, 2, 'FooterInfoExcludeVat', '* All prices are quoted net of the statutory value-added tax and <span style="text-decoration: underline;"><a title="shipping costs" href="{url controller=custom sCustom=6}">shipping costs</a></span> and possibly delivery charges, if not otherwise described', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2105, 'frontend/index/categories_top', 1, 2, 'IndexLinkHome', 'Home', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2106, 'frontend/listing/filter_properties', 1, 2, 'FilterHeadline', 'Filter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2107, 'frontend/listing/filter_properties', 1, 2, 'FilterHeadlineCategory', 'Filter by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2108, 'frontend/listing/filter_properties', 1, 2, 'FilterLinkDefault', 'Show all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2109, 'frontend/listing/box_similar', 1, 2, 'SimilarBoxLinkDetails', 'Go to product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2110, 'frontend/newsletter/detail', 1, 2, 'NewsletterDetailInfoEmpty', 'Entry could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2111, 'frontend/compare/overlay', 1, 2, 'LoginActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2112, 'frontend/compare/overlay', 1, 2, 'CompareHeader', 'Product comparison', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2113, 'frontend/newsletter/listing', 1, 2, 'NewsletterListingInfoEmpty', 'No existing entries', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2114, 'frontend/listing/box_similar', 1, 2, 'SimilarBoxMore', 'Go to product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2115, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataShippingDays', 'Workdays', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2116, 'frontend/register/index', 1, 2, 'RegisterHeadlineSupplier', 'Merchant registration.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2117, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier', 'Do you already have a merchant account?', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2118, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier2', 'Click here to log in!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2119, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier3', 'After the registration you will be displayed the retail prices until your account has been verified.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2120, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier4', 'Please send us your business license by fax.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2121, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier5', 'Please send us your business license by fax to +49 2555 92 95 61. If you are already registered as a merchant,<br /> you can skip this step.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2122, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier6', 'We will validate your indications and activate your account!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2123, 'frontend/register/index', 1, 2, 'RegisterInfoSupplier7', 'We will activate your account after verification. You will then receive a confirmation e-mail. <br /> From now on, you will be displayed the merchant purchase prices on the product- and overview pages.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2124, 'frontend/search/filter_category', 1, 2, 'SearchFilterLinkDefault', 'Show all categories', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2126, 'frontend/search/fuzzy', 1, 2, 'SearchFuzzyInfoShortTerm', 'The entered search term is too short.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2127, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftLinkDefault', 'Show all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2128, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftInfoSuppliers', 'Additional manufacturers', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2129, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlineFilter', 'Filter by', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2130, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftLinkAllFilters', 'All filters', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2131, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftLinkAllSuppliers', 'All manufacturers', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2132, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftLinkAllPrices', 'All prices:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2133, 'frontend/search/index', 1, 2, 'SearchHeadline', 'The following products match your search "{$sSearchTerm|escape}": {$sSearchResultsNum}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2134, 'frontend/search/supplier', 1, 2, 'SearchTo', 'For ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2135, 'frontend/search/supplier', 1, 2, 'SearchWere', 'have been', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2136, 'frontend/search/supplier', 1, 2, 'SearchArticlesFound', 'Articles found!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2137, 'frontend/sitemap/index', 1, 2, 'SitemapHeader', 'Sitemap - All categories at a glance', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2138, 'frontend/ticket/navigation', 1, 2, 'TicketHeader', 'Support administration', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2139, 'frontend/ticket/navigation', 1, 2, 'TicketLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2140, 'frontend/ticket/navigation', 1, 2, 'TicketLinkSupport', 'Request support', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2141, 'frontend/ticket/navigation', 1, 2, 'TicketLinkIndex', 'Support overview', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2142, 'frontend/ticket/navigation', 1, 2, 'TicketLinkLogout', 'Unsubscribe/Logout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2143, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoEmpty', 'There does not exist an entry with this ID.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2144, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoTicket', 'Details about the ticket', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2145, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoStatusProgress', 'This ticket is in progress.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2146, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoAnswer', 'Your response', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2147, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoQuestion', 'Your ticket request', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2148, 'frontend/checkout/ajax_add_article', 1, 2, 'LoginActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2149, 'frontend/ticket/listing', 1, 2, 'TicketInfoId', 'Ticket ID', '2012-08-22 15:57:47', '2012-08-22 15:57:47');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(2150, 'frontend/ticket/listing', 1, 2, 'TicketInfoStatus', 'Status', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2151, 'frontend/ticket/listing', 1, 2, 'TicketHeadline', 'Support administration', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2152, 'frontend/ticket/listing', 1, 2, 'TicketLinkDetails', '[Show details]', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2153, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsHeadline', 'Trusted Shops Quality Seal - Please click here.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2154, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsSalutationMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2155, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsSalutationCompany', 'Company', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2156, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsInfo', 'Sign up for money-back guarantee', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2157, 'frontend/plugins/trusted_shops/form', 1, 2, 'WidgetsTrustedShopsText', 'As an additional service we offer Trusted Shops Buyer Protection. We will bear all costs of this guarantee. All you need to do is register.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2158, 'frontend/checkout/premiums', 1, 2, 'PremiumInfoSelect', 'Please select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2159, 'frontend/account/ajax_logout', 1, 2, 'LoginActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2160, 'frontend/blog/comments', 1, 2, 'BlogInfoComments', 'Comments', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2161, 'frontend/register/billing_fieldset', 1, 2, 'RegisterLabelDepartment', 'Department', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2162, 'frontend/ticket/detail', 1, 2, 'TicketDetailInfoShopAnswer', 'Our response', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2163, 'frontend/widgets/blog/listing', 1, 2, 'WidgetsBlogHeadline', 'New in our blog', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2164, 'frontend/register/billing_fieldset', 1, 2, 'RegisterLabelCompany', 'Name*', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2165, 'frontend/error/exception', 1, 2, 'ExceptionHeader', 'Oops! An error has occured!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2166, 'frontend/error/exception', 1, 2, 'ExceptionText', 'The following notes should help you.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2167, 'frontend/register/billing_fieldset', 1, 2, 'RegisterHeaderCompany', 'Company', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2168, 'frontend/detail/description', 1, 2, 'ArticleTipMoreInformation', 'Onward links to', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2169, 'frontend/blog/index', 1, 2, 'ListingLinkAllSuppliers', 'Show all authors', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2170, 'frontend/blog/index', 1, 2, 'ListingInfoFilterSupplier', 'Filter by author', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2171, 'frontend/register/billing_fieldset', 1, 2, 'RegisterLabelTaxId', 'VAT ID', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2173, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataShippingtime', 'Delivery time', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2174, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoInstantDownload', 'Available as instant download', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2175, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoShipping', 'This article will be released at', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2176, 'frontend', 1, 2, 'RegisterPasswordLength', 'Your password should contain at least {config name="MinPassword"} characters', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2177, 'frontend', 1, 2, 'RegisterAjaxEmailNotEqual', 'The e-mail addresses do not match', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2178, 'frontend', 1, 2, 'RegisterAjaxEmailNotValid', 'Please enter a valid e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2179, 'frontend/checkout/confirm_item', 1, 2, 'CheckoutItemPrice', 'Unit price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2180, 'frontend/custom/ajax', 1, 2, 'CustomAjaxActionNewWindow', 'Open in new window', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2181, 'frontend/account/ajax_logout', 1, 2, 'AccountLogoutButton', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2182, 'frontend/checkout/ajax_cart', 1, 2, 'AjaxCartLinkConfirm', 'Proceed to checkout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2183, 'frontend/account/success_messages', 1, 2, 'AccountBillingSuccess', 'Saved successfully', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2184, 'frontend/search/paging', 1, 2, 'ListingSortRelease', 'Date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2185, 'frontend/note/item', 1, 2, 'NoteInfoSupplier', 'Manufacturer:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2186, 'frontend/note/index', 1, 2, 'NoteTitle', 'Wish list', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2187, 'frontend/account/content_right', 1, 2, 'TicketLinkSupport', 'Support request', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2188, 'frontend/note/item', 1, 2, 'NoteLinkZoom', 'Enlarge picture', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2189, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterBillingLabelCity', 'Zipcode/City', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2190, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterBillingLabelStreet', 'Street/Street number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2191, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterLabelLastname', 'Last name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2192, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterLabelFirstname', 'First name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2193, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterLabelMs', 'Mrs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2194, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterPleaseChoose', 'Please select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2195, 'frontend/newsletter/index', 1, 2, 'NewsletterRegisterLabelSalutation', 'Title', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2196, 'frontend/register/index', 1, 2, 'RegisterIndexActionSubmit', 'Complete registration', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2197, 'frontend/checkout/added', 1, 2, 'CheckoutAddArticleLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2198, 'frontend', 1, 2, 'RegisterAjaxEmailForgiven', 'This e-mail address is already registered.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2199, 'frontend/checkout/added', 1, 2, 'CheckoutAddArticleInfoAdded', '{$sArticleName} has been added to shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2200, 'frontend/checkout/confirm_footer', 1, 2, 'CheckoutFinishTaxInformation', 'The recipient of the service owes the tax', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2201, 'frontend/checkout/confirm_item', 1, 2, 'CartItemInfoFree', 'Free', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2202, 'frontend/account/password', 1, 2, 'LoginBack', 'Back ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2203, 'frontend/checkout/cart_footer', 1, 2, 'CartFooterTotalTax', 'Plus {$rate}&nbsp;% VAT:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2204, 'frontend/checkout/cart_footer', 1, 2, 'CartFooterTotalNet', 'Total amount without VAT:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2205, 'frontend/detail/error', 1, 2, 'DetailRelatedHeader', 'Unfortunately, this article is no longer available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2206, 'frontend/detail/error', 1, 2, 'DetailRelatedHeaderSimilarArticles', 'Similar articles:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2207, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoValid', 'Thank you!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2208, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoInvalid', 'An error has occured while validating your e-mail address.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2209, 'frontend/search/paging', 1, 2, 'ListingPaging', 'Browse:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2210, 'frontend/search/paging', 1, 2, 'ListingLinkPrevious', 'Page back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2211, 'frontend/search/paging', 1, 2, 'ListingLinkNext', 'Page forward', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2212, 'frontend/listing/listing_actions', 1, 2, 'ListingPaging', 'Browse:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2213, 'frontend/listing/listing_actions', 1, 2, 'ListingTextPrevious', '&lt;', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2214, 'frontend/listing/listing_actions', 1, 2, 'ListingLinkPrevious', 'Previous page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2215, 'frontend/listing/listing_actions', 1, 2, 'ListingTextNext', '&gt;', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2216, 'frontend/checkout/ajax_add_article', 1, 2, 'ListingBoxNoPicture', 'No image', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2218, 'frontend/custom/ajax', 1, 2, 'CustomAjaxActionClose', 'Close', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2219, 'frontend', 1, 2, 'sMailConfirmation', 'Thank you! We have sent a confirmation e-mail. All you need to do is click the link in the e-mail in order to confirm your registration.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2220, 'frontend/ticket/listing', 1, 2, 'TicketInfoDate', 'Date', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2221, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterLabelAddVoucher', 'Add voucher', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2222, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterAddVoucherLabelInline', 'Voucher number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2223, 'frontend/checkout/confirm', 1, 2, 'ConfirmTextRightOfRevocation', 'Information on cancellation right [Fill / Text module]', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2224, 'frontend/account/billing', 1, 2, 'BillingLinkSend', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2225, 'frontend/index/index', 1, 2, 'IndexNoscriptNotice', 'To be able to use {$sShopname} in full range, we recommend activating Javascript in your browser.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2226, 'frontend/index/index', 1, 2, 'IndexRealizedShopsystem', 'Shopware', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2227, 'frontend/index/header', 1, 2, 'IndexMetaHttpContentType', 'text/html; charset=iso-8859-1', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2228, 'frontend/index/header', 1, 2, 'IndexMetaMsNavButtonColor', '#dd4800', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2229, 'frontend/index/header', 1, 2, 'IndexMetaShortcutIcon', '{link file=''frontend/_resources/favicon.ico''}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2230, 'frontend/error/index', 1, 2, 'ErrorIndexTitle', 'An error has occured!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2231, 'frontend/compare/overlay', 1, 2, 'CompareLinkPrint', 'Print', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2232, 'frontend/plugins/recommendation/slide_articles', 1, 2, 'ListingBoxArticleStartsAt', 'from', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2233, 'frontend/search/fuzzy', 1, 2, 'SearchHeadline', 'The following products have been found matching your search "{$sRequests.sSearch}":  {$sSearchResults.sArticlesCount} ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2234, 'frontend/account/ajax_login', 1, 2, 'LoginInfoNew', 'No problem, a shop order is easy and secure. The registration only takes a few moments.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2235, 'frontend/account/ajax_login', 1, 2, 'LoginActionCreateAccount', 'Next', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2236, 'frontend/custom/right', 1, 2, 'CustomHeader', 'Direct contact', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2237, 'frontend/custom/right', 1, 2, 'CustomTextContact', 'Your address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2238, 'frontend/account/ajax_login', 1, 2, 'LoginLabelPassword', 'Your password', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2239, 'frontend/checkout/confirm_header', 1, 2, 'CartColumnTotal', 'Total', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2240, 'frontend/account/ajax_login', 1, 2, 'LoginTextExisting', 'Login with your e-Mail address and your password', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2241, 'frontend/register/index', 1, 2, 'RegisterLabelDataCheckbox', 'Hereby I accept the data protection regulations', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2242, 'frontend/content/paging', 1, 2, 'ListingPaging', 'Flip:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2243, 'frontend/content/paging', 1, 2, 'ListingLinkPrevious', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2244, 'frontend/content/paging', 1, 2, 'ListingTextPrevious', '&lt;', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2245, 'frontend/content/paging', 1, 2, 'ListingLinkNext', 'Next', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2246, 'frontend/content/paging', 1, 2, 'ListingTextNext', '&gt;', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2247, 'frontend/account/success_messages', 1, 2, 'AccountNewsletterSuccess', 'Saved successfully', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2248, 'frontend/ticket/detail', 1, 2, 'TicketDetailLinkBack', 'Back', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2249, 'frontend/listing/listing_actions', 1, 2, 'ListingActionsOffersLink', 'Further articles in this category:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2250, 'frontend/newsletter/listing', 1, 2, 'NewsletterListingHeaderName', 'Name', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2251, 'frontend/detail/config_step', 1, 2, 'DetailConfigValueSelect', 'Please select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2252, 'frontend/detail/config_step', 1, 2, 'DetailConfigActionSubmit', 'Select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2253, 'frontend/detail/config_upprice', 1, 2, 'DetailConfigActionSubmit', 'Select', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2254, 'frontend/search/filter_category', 1, 2, 'SearchFilterCategoryHeading', 'Restrict search results to categories', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2255, 'frontend/search/paging', 1, 2, 'ListingLabelItemsPerPage', 'Products per page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2256, 'frontend/search/ajax', 1, 2, 'SearchAjaxLinkAllResults', 'Show all search results', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2257, 'frontend/search/ajax', 1, 2, 'SearchAjaxInfoResults', 'Results', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2258, 'frontend/plugins/recommendation/blocks_index', 1, 2, 'IndexNewArticlesSlider', 'New in our product range:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2259, 'frontend/plugins/recommendation/blocks_index', 1, 2, 'IndexSimilaryArticlesSlider', 'Articles similar to those you have recently viewed:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2260, 'frontend/plugins/recommendation/blocks_index', 1, 2, 'IndexSupplierSlider', 'Our top brands', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2261, 'frontend', 1, 2, 'CheckoutArticleLessStock', 'Unfortunately, the requested article is not deliverable in the desired quantities. (#0 of #1 deliverable).', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2262, 'documents/index', 1, 2, 'DocumentIndexCustomerID', 'Customer No.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2263, 'documents/index', 1, 2, 'DocumentIndexUstID', 'VAT registration number:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2264, 'documents/index', 1, 2, 'DocumentIndexOrderID', 'Order No.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2265, 'documents/index', 1, 2, 'DocumentIndexDate', 'Date:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2266, 'documents/index', 1, 2, 'DocumentIndexDeliveryDate', 'Delivery date:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2267, 'documents/index', 1, 2, 'DocumentIndexInvoiceNumber', 'Invoice No. {$Document.id}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2268, 'documents/index', 1, 2, 'DocumentIndexPageCounter', 'Page {$page+1} of {$Pages|@count}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2269, 'documents/index', 1, 2, 'DocumentIndexHeadPosition', 'Pos.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2270, 'documents/index', 1, 2, 'DocumentIndexHeadArticleID', 'Art. No.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2271, 'documents/index', 1, 2, 'DocumentIndexHeadName', 'Description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2272, 'documents/index', 1, 2, 'DocumentIndexHeadQuantity', 'Quantity', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2273, 'documents/index', 1, 2, 'DocumentIndexHeadTax', 'VAT', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2274, 'documents/index', 1, 2, 'DocumentIndexHeadPrice', 'Gross price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2275, 'documents/index', 1, 2, 'DocumentIndexHeadAmount', 'Gross total:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2276, 'documents/index', 1, 2, 'DocumentIndexHeadNet', 'Net price:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2277, 'documents/index', 1, 2, 'DocumentIndexHeadNetAmount', 'Net total:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2278, 'documents/index', 1, 2, 'DocumentIndexTotalNet', 'Total costs net:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2279, 'documents/index', 1, 2, 'DocumentIndexTax', 'Plus {$key} VAT:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2280, 'documents/index', 1, 2, 'DocumentIndexTotal', 'Total costs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2281, 'documents/index', 1, 2, 'DocumentIndexAdviceNet', 'Please note: The recipient of the service owes the tax.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2282, 'documents/index', 1, 2, 'DocumentIndexSelectedPayment', 'Selected payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2283, 'documents/index', 1, 2, 'DocumentIndexComment', 'Comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2284, 'documents/index', 1, 2, 'DocumentIndexSelectedDispatch', 'Selected shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2286, 'frontend', 1, 2, 'AccountPasswordNotEqual', 'The passwords do not match.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2287, 'frontend', 1, 2, 'CheckoutSelectVariant', 'Please select a variant to add desired article to the shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2288, 'frontend/account/index', 1, 2, 'AccountLinkChangeMail', 'Change e-mail', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2289, 'frontend/account/index', 1, 2, 'AccountLabelNewMail', 'New e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2290, 'frontend/account/index', 1, 2, 'AccountLabelMail', 'e-mail confirmation', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2291, 'frontend/plugins/notification/index', 1, 2, 'DetailNotifyAlreadyRegistered', 'You already have subscribed for a notification.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2292, 'documents/index_ls', 1, 2, 'DocumentIndexShippingNumber', 'Delivery note No.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2293, 'documents/index_ls', 1, 2, 'DocumentIndexPageCounter', 'Page {$page+1} of {$Pages|@count}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2294, 'documents/index_ls', 1, 2, 'DocumentIndexInvoiceID', 'For invoice', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2295, 'frontend/plugins/paypal/logo', 1, 2, 'PaypalLogoAlt', 'Paypal', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2296, 'frontend/plugins/paypal/logo', 1, 2, 'PaypalLogoText', 'Paypal', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2297, 'frontend/checkout/shipping_costs', 1, 2, 'DispatchHeadNotice', 'Shipping information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2298, 'frontend/checkout/confirm', 1, 2, 'ConfirmDoPayment', 'Complete payment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2299, 'frontend', 1, 2, 'CheckoutArticleNoStock', 'Unfortunately, the requested article is no longer available in the desired quantities.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2300, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLinkConfirm', 'Proceed to checkout', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2301, 'frontend/checkout/ajax_add_article', 1, 2, 'ListingBoxArticleStartsAt', 'from', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2302, 'frontend/checkout/confirm', 1, 2, 'ConfirmErrorStock', 'One of your desired articles is not available. Please remove this item from shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2303, 'frontend/checkout/confirm_item', 1, 2, 'CheckoutItemLaststock', 'NOT AVAILABLE', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2304, 'frontend/listing/listing_actions', 1, 2, 'ListingActionsSettingsTable', 'Table view', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2305, 'frontend/listing/listing_actions', 1, 2, 'ListingActionsSettingsList', 'List view', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2307, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeaderError', 'Please note:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2308, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeadDispatch', 'Shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2309, 'frontend/checkout/confirm', 1, 2, 'ConfirmLabelDispatch', 'Selected shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2310, 'frontend/checkout/confirm', 1, 2, 'ConfirmLinkChangeDispatch', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2311, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeadDispatchNotice', 'Shipping information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2312, 'frontend/plugins/index/tagcloud', 1, 2, 'TagcloudHead', 'Tag cloud', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2313, 'frontend/plugins/index/topseller', 1, 2, 'TopsellerHeading', 'Top seller', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2314, 'frontend/plugins/index/topseller', 1, 2, 'WidgetsTopsellerNoPicture', 'No image available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2315, 'frontend/home/index', 1, 2, 'WidgetsBlogHeadline', 'Blog', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2316, 'frontend/widgets/advanced_menu/advanced_menu', 1, 2, 'IndexLinkHome', 'Home', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2317, 'frontend/account/order_item', 1, 2, 'OrderItemCustomerComment', 'Your comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2318, 'frontend/account/order_item', 1, 2, 'OrderItemComment', 'Our comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2319, 'frontend/checkout/error_messages', 1, 2, 'ConfirmInfoNoDispatch', 'No shipping type', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2320, 'frontend/checkout/error_messages', 1, 2, 'ConfirmInfoMinimumSurcharge', 'Minimum order value not reached', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2321, 'newsletter/index/header', 1, 2, 'NewsletterHeaderLinkHome', 'Home', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2322, 'newsletter/container/article', 1, 2, 'NewsletterBoxArticleStartsAt', 'From', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2323, 'newsletter/container/article', 1, 2, 'NewsletterBoxArticleLinkDetails', 'More information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2324, 'newsletter/index/footer', 1, 2, 'NewsletterFooterNavigation', '<a href="#" target="_blank" style="font-size:10px;">Kontakt</a> | <a href="#" target="_blank" style="font-size:10px;">Imprint</a>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2325, 'newsletter/index/footer', 1, 2, 'NewsletterFooterInfoIncludeVat', '* All prices are quoted net of the statutory value-added tax and <span style="text-decoration: underline;"><a title="shipping costs" href="{url controller=custom sCustom=6}">shipping costs</a></span> and possibly delivery charges, if not otherwise described', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2326, 'newsletter/index/footer', 1, 2, 'NewsletterFooterInfoExcludeVat', '* All prices are quoted net of the statutory value-added tax and <span style="text-decoration: underline;"><a title="shipping costs" href="{url controller=custom sCustom=6}">shipping costs</a></span> and possibly delivery charges, unless otherwise described', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2328, 'newsletter/index/footer', 1, 2, 'NewsletterFooterLinkUnsubscribe', 'Unsubscribe to the newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2329, 'newsletter/index/footer', 1, 2, 'NewsletterFooterLinkNewWindow', 'Open newsletter in browser', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2330, 'frontend/detail/liveshopping/category_countdown', 1, 2, 'LiveCategoryOffersEnds', 'Offer ends in:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2331, 'frontend/detail/liveshopping/category', 1, 2, 'LiveCategoryPreviousPrice', 'Original price:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2332, 'frontend/detail/liveshopping/category', 1, 2, 'LiveCategorySavingPercent', 'You save:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2333, 'frontend/detail/liveshopping/category', 1, 2, 'LiveCategoryOffersEnds', 'Offer ends in:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2334, 'frontend/detail/liveshopping/category', 1, 2, 'LiveCategoryCurrentPrice', 'Current price:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2335, 'documents/index_sr', 1, 2, 'DocumentIndexTotalNet', 'Total costs net:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2336, 'documents/index_sr', 1, 2, 'DocumentIndexTax', 'Plus {$key} VAT:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2337, 'documents/index_sr', 1, 2, 'DocumentIndexTotal', 'Total costs', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2338, 'documents/index_sr', 1, 2, 'DocumentIndexCancelationNumber', 'Reversal invoice for invoice No. {$Document.bid}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2339, 'documents/index_sr', 1, 2, 'DocumentIndexPageCounter', 'Page {$page+1} of {$Pages|@count}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2340, 'frontend', 1, 2, 'CheckoutSelectPremiumVariant', 'Please select a variant to add desired bonus to the shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2342, 'frontend/plugins/recommendation/blocks_listing', 1, 2, 'IndexNewArticlesSlider', 'New in our product range:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2343, 'frontend/plugins/recommendation/blocks_listing', 1, 2, 'IndexSimilaryArticlesSlider', 'Articles similar to those you have recently viewed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2344, 'frontend/plugins/recommendation/blocks_listing', 1, 2, 'IndexSupplierSlider', 'Our top brands', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2345, 'frontend/account/order_item.tpl', 1, 2, 'OrderItemCustomerComment', 'Your comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2346, 'frontend/account/order_item.tpl', 1, 2, 'OrderItemComment', 'Our comment', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2347, 'frontend/checkout/confirm_header', 1, 2, 'CheckoutColumnExcludeTax', 'Plus VAT', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2348, 'frontend', 1, 2, 'RegisterPasswordNotEqual', 'The passwords do not match.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2349, 'frontend/checkout/finish_header', 1, 2, 'CartColumnTotal', 'Amount', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2350, 'frontend/account/ajax_login', 1, 2, 'LoginLabelNoAccount', 'Do not create customer account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2351, 'frontend/account/login', 1, 2, 'AccountLoginTitle', 'Login', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2352, 'frontend/account/login', 1, 2, 'LoginLabelNoAccount', 'Do not create customer account', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2353, 'frontend/index/search', 1, 2, 'IndexSearchFieldValue', 'Search:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2354, 'frontend/compare/add_article', 1, 2, 'CompareHeaderTitle', 'Compare articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2355, 'frontend/compare/add_article', 1, 2, 'CompareInfoMaxReached', 'You can only compare a maximum of 5 articles in a single step', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2356, 'frontend/plugins/advanced_menu/advanced_menu', 1, 2, 'IndexLinkHome', 'Home', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2357, 'frontend/account/downloads', 1, 2, 'MyDownloadsTitle', 'My instant downloads', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2358, 'frontend/account/select_billing', 1, 2, 'SelectBillingTitle', 'Select address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2359, 'frontend/account/billing', 1, 2, 'ChangeBillingTitle', 'Change billing address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2360, 'frontend/account/shipping', 1, 2, 'ChangeShippingTitle', 'Change shipping address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2361, 'frontend/checkout/finish_footer', 1, 2, 'CheckoutFinishTaxInformation', 'The recipient of the service owes the tax', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2362, 'frontend/account/payment', 1, 2, 'ChangePaymentTitle', 'Change payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2363, 'frontend/account/select_shipping', 1, 2, 'SelectShippingTitle', 'Select address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2364, 'frontend/error/exception', 1, 2, 'InformText', 'We have been informed about the problem and try to solve it. Please try again within a short time.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2365, 'frontend/listing/box_article', 1, 2, 'Star', '*', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2366, 'frontend/listing/box_article', 1, 2, 'reducedPrice', 'Instead of:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2367, 'frontend/account/password', 1, 2, 'PasswordSendAction', 'Request password', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2368, 'frontend/listing/box_article', 1, 2, 'ListingBoxArticleContent', 'Content', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2369, 'frontend/listing/box_article', 1, 2, 'ListingBoxBaseprice', 'Basic price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2370, 'frontend/note/item', 1, 2, 'NoteUnitPriceContent', 'Content', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2371, 'frontend/note/item', 1, 2, 'NoteUnitPriceBaseprice', 'Basic price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2372, 'frontend/compare/col', 1, 2, 'CompareContent', 'Content', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2373, 'frontend/compare/col', 1, 2, 'CompareBaseprice', 'Basic price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2374, 'frontend/account/order_item', 1, 2, 'OrderItemInfoContent', 'Content', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2375, 'frontend/account/order_item', 1, 2, 'OrderItemInfoBaseprice', 'Basic price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2376, 'frontend/account/order_item', 1, 2, 'OrderItemInfoCurrentPrice', 'Current unit price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2377, 'frontend/plugins/recommendation/slide_articles', 1, 2, 'SlideArticleInfoBaseprice', 'Basic price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2378, 'frontend/plugins/recommendation/slide_articles', 1, 2, 'SlideArticleInfoContent', 'Content', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2379, 'frontend/register/personal_fieldset', 1, 2, 'RegisterPersonalRequiredText', 'The fields marked with * are required', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2380, 'frontend/account/internalMessages', 1, 2, 'LoginFailureLocked', 'Too many failed login attempts. Your account has been temporarily deactivated - Please try again in a few minutes!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2381, 'frontend/account/internalMessages', 1, 2, 'LoginFailureActive', 'Your customer account has been deactivated. Please contact us personally.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2382, 'frontend/account/internalMessages', 1, 2, 'LoginFailure', 'Your access data could not be assigned to a user', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2383, 'frontend/account/internalMessages', 1, 2, 'ErrorFillIn', 'Please fill out all fields that are marked red', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2384, 'frontend/account/internalMessages', 1, 2, 'NewsletterFailureNotFound', 'This e-mail address could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2385, 'frontend/account/internalMessages', 1, 2, 'NewsletterMailDeleted', 'Your e-mail address has been deleted.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2386, 'frontend/account/internalMessages', 1, 2, 'NewsletterSuccess', 'Thank you! We have entered your address.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2387, 'frontend/account/internalMessages', 1, 2, 'NewsletterFailureAlreadyRegistered', 'You are already subscribed to our newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2388, 'frontend/account/internalMessages', 1, 2, 'UnknownError', 'An unknown error has occured.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2389, 'frontend/account/internalMessages', 1, 2, 'NewsletterFailureInvalid', 'Please enter a valid e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2390, 'frontend/account/internalMessages', 1, 2, 'NewsletterFailureMail', 'Please enter an e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2391, 'frontend/account/internalMessages', 1, 2, 'VatFailureDate', 'The entered VAT ID is invalid. It becomes valid on %s', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2392, 'frontend/account/internalMessages', 1, 2, 'VatFailureUnknownError', 'An error has occured while checking the VAT ID. Please contact the shop owner. Error code: %d', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2393, 'frontend/account/internalMessages', 1, 2, 'VatFailureErrorField', 'The field %s does not match the VAT ID', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2394, 'frontend/account/internalMessages', 1, 2, 'VatFailureErrorFields', 'Company,City,Zipcode,Street,Country', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2395, 'frontend/account/internalMessages', 1, 2, 'VatFailureInvalid', 'The entered VAT ID is invalid', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2396, 'frontend/account/internalMessages', 1, 2, 'VatFailureEmpty', 'Please enter a VAT ID', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2397, 'frontend/account/internalMessages', 1, 2, 'MailFailureNotEqual', 'The e-mail addresses do not match', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2398, 'frontend/account/internalMessages', 1, 2, 'MailFailure', 'Please enter a valid E-Mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2399, 'frontend/account/internalMessages', 1, 2, 'MailFailureAlreadyRegistered', 'This e-mail address is already registered', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2400, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureMinimumCharge', 'The minimum charge for this voucher is {sMinimumCharge} EUR', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2401, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureSupplier', 'This voucher is only valid for products by {sSupplier} ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2402, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureProducts', 'This voucher is only valid for certain products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2403, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureCustomerGroup', 'This voucher is not available for your customer group', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2404, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureOnlyOnes', 'Only one voucher can be used per order.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2405, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureNotFound', 'Voucher could not be found or is no longer valid', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2406, 'frontend/basket/internalMessages', 1, 2, 'VoucherFailureAlreadyUsed', 'This voucher has been redeemed during a previous order', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2407, 'frontend/ticket/internalMessages', 1, 2, 'TicketFailureFields', 'Please fill out all relevant fields!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2408, 'frontend/ticket/internalMessages', 1, 2, 'TicketReplySuccessful', 'Your response has been submitted successfully!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2409, 'frontend/account/password', 1, 2, 'ErrorForgotMail', 'Please enter your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2410, 'frontend/account/password', 1, 2, 'ErrorForgotMailUnknown', 'e-mail address unknown', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2411, 'frontend/account/content_right', 1, 2, 'AccountLinkPartnerStatistic', 'Commissions', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2412, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticHeader', 'Overview of commissions', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2413, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticLabelFromDate', 'From:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2414, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticLabelToDate', 'to:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2415, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticColumnDate', 'Date ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2416, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticColumnId', 'Order number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2417, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticColumnNetAmount', 'Net turnover', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2418, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticColumnProvision', 'Commission', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2419, 'frontend/account/partner_statistic', 1, 2, 'Provisions', 'Commissions', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2420, 'frontend/account/partner_statistic_item', 1, 2, 'PartnerStatisticItemSum', 'Total amount:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2421, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticSubmitFilter', 'Filter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2422, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticInfoEmpty', 'No assessment available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2423, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticLabelTimeUnit', 'Calendar week', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2424, 'frontend/account/partner_statistic', 1, 2, 'PartnerStatisticLabelNetTurnover', 'Net turnover', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2426, 'frontend/plugins/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2427, 'frontend/detail/comment', 1, 2, 'DetailCommentInfoFromAdmin', 'Admin', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2428, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelState', 'State', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2429, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelState', 'State', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2432, 'frontend/checkout/confirm_payment', 1, 2, 'CheckoutPaymentHeadline', 'Payment method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2433, 'frontend/checkout/confirm_payment', 1, 2, 'CheckoutPaymentLinkSend', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2434, 'frontend/checkout/confirm_dispatch', 1, 2, 'CheckoutDispatchHeadline', 'Shipping method', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2435, 'frontend/checkout/confirm_dispatch', 1, 2, 'CheckoutDispatchLinkSend', 'Change', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2436, 'frontend/index/checkout_actions', 1, 2, 'IndexLinkService', 'Service/Help', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2437, 'frontend/plugins/trusted_shops/logo', 1, 2, 'WidgetsTrustedLogoText2', '<span><strong>Secure</strong> shopping</span><br/>certified by Trusted Shops', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2438, 'frontend/plugins/paypal/logo', 1, 2, 'PaypalLogoText2', 'Easy ordering and paying', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2439, 'frontend/blog/box', 1, 2, 'BlogInfoTags', 'Tags:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2440, 'frontend/index/menu_footer', 1, 2, 'sFooterServiceHotlineHead', 'Service hotline', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2442, 'frontend/index/menu_footer', 1, 2, 'sFooterShopNavi1', 'Shop service', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2443, 'frontend/index/menu_footer', 1, 2, 'sFooterShopNavi2', 'Information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2444, 'frontend/index/menu_footer', 1, 2, 'sFooterNewsletterHead', 'Newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2446, 'frontend/index/menu_footer', 1, 2, 'IndexFooterNewsletterValue', 'Your e-mail address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2447, 'frontend/index/menu_footer', 1, 2, 'IndexFooterNewsletterSubmit', 'Subscribe to newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2448, 'frontend/listing/right', 1, 2, 'FilterHeadline', 'Filter by:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2449, 'frontend/listing/filter_supplier', 1, 2, 'FilterLinkDefault', 'Display all', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2451, 'frontend/checkout/cart', 1, 2, 'sPremiumsHead', 'Please choose between the following bonuses', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2452, 'frontend/checkout/premiums', 1, 2, 'sBonusPriceFree', '<strong>Free</strong><br />Secure your bonus now!</p>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2453, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeadlineAGBandRevocation', 'Terms and conditions, cancellation policy', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2454, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeadlinePersonalInformation', 'Your personal information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2455, 'frontend/detail/comment', 1, 2, 'InquiryTextArticle', 'I have the following questions on the article', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2456, 'frontend/error/service', 1, 2, 'ServiceIndexTitle', 'Maintenance work', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2457, 'frontend/error/service', 1, 2, 'ServiceHeader', 'Not available due to maintenance!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2458, 'frontend/error/service', 1, 2, 'ServiceText', 'Due to maintenance work, the shop is temporarily not available.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2459, 'frontend/note/item', 1, 2, 'NoteInfoDate', 'Added on:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2460, 'frontend', 1, 1, 'CheckoutArticleNotFound', 'Das Produkt konnte nicht gefunden werden.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2461, 'frontend', 1, 2, 'CheckoutArticleNotFound', 'Article could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2478, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsTitle', 'Ansicht:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2479, 'frontend/plugins/index/viewlast', 1, 1, 'WidgetsRecentlyViewedHeadline', 'Zuletzt angeschaute Artikel', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2480, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoInstock', 'Sofort versandfertig, Lieferzeit ca. 1-3 Werktage', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2481, 'frontend/detail/data', 1, 1, 'DetailDataId', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2482, 'frontend/checkout/cart_item', 1, 1, 'CartItemInfoId', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2483, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterIdLabelInline', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2484, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineCutdown', 'Filtern nach:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2485, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineSupplier', 'Hersteller', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2486, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlinePrice', 'Preis', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2487, 'frontend/plugins/compare/index', 1, 1, 'ListingBoxLinkCompare', 'Vergleichen', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2488, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLabelOrdernumber', 'Artikel-Nr.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2489, 'frontend/index/footer', 1, 1, 'IndexCopyright', 'Copyright ©  - Alle Rechte vorbehalten', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2490, 'newsletter/index/footer', 1, 1, 'NewsletterFooterCopyright', 'Copyright ©  - Alle Rechte vorbehalten', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2491, 'frontend/register/steps', 1, 1, 'CheckoutStepBasketText', 'Ihr Warenkorb', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2493, 'frontend/register/steps', 1, 1, 'CheckoutStepConfirmText', 'Prüfen und Bestellen', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2494, 'frontend/index/checkout_actions', 1, 1, 'IndexLinkService', 'Service/Hilfe', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2495, 'frontend/detail/index', 1, 1, 'DetailFromNew', 'Hersteller', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2497, 'frontend/index/menu_footer', 1, 1, 'sFooterServiceHotline', 'Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2499, 'frontend/index/menu_footer', 1, 1, 'sFooterShopNavi1', 'Shop Service', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2500, 'frontend/index/menu_footer', 1, 1, 'sFooterShopNavi2', 'Informationen', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2501, 'frontend/index/menu_footer', 1, 1, 'sFooterNewsletterHead', 'Newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2503, 'frontend/index/menu_footer', 1, 1, 'sFooterNewsletter', 'Abonnieren Sie den kostenlosen DemoShop Newsletter und verpassen Sie keine Neuigkeit oder Aktion mehr aus dem DemoShop.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2504, 'backend/activate/skeleton', 1, 2, 'WindowTitle', 'Clear cache', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2505, 'backend/cache/skeleton', 1, 2, 'WindowTitle', 'Cache', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2507, 'backend/index/index', 1, 2, 'IndexTitle', 'Shopware {config name=Version}  (Rev. 3650, 18.10.2010) - Backend (c)2010,2011 shopware AG', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2508, 'backend/license/skeleton', 1, 2, 'WindowTitle', 'Licenses ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2509, 'backend/plugin/viewport', 1, 2, 'tree_titel', 'Plugins ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2510, 'backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsInfo', 'The voucher is valid until', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2511, 'backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsCharge', 'Please note the minimum order value of {$coupon.minimumcharge|currency}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2513, 'backend/plugins/coupons/skeleton', 1, 2, 'WindowTitle', 'Coupon management', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2514, 'backend/plugins/recommendation/skeleton', 1, 2, 'WindowTitle', 'Slider components', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2517, 'frontend/checkout/finish_item', 1, 2, 'CartItemInfoFree', 'Free', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2518, 'backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsText', 'We hope you enjoy your visit of our online shop. For questions or problems please contact us under : Sample company/Sample street/ Sample town', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2519, 'backend/plugin/skeleton', 1, 2, 'WindowTitle', 'Plugin Manager', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2520, 'frontend/register/index', 1, 2, 'RegisterTitle', 'Registration', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2521, 'frontend/register/index', 1, 1, 'RegisterTitle', 'Registrierung', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2523, 'frontend/register/steps', 1, 1, 'CheckoutStepRegisterText', 'Ihre Adresse', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2525, 'frontend/plugins/index/viewlast', 1, 2, 'WidgetsRecentlyViewedHeadline', 'Viewed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2526, 'frontend/detail/data', 1, 2, 'DetailDataId', 'Order number: ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2527, 'frontend/detail/buy', 1, 2, 'DetailBuyLabelQuantity', 'Amount', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2528, 'frontend/register/billing_fieldset', 1, 2, 'RegisterBillingLabelSelect', 'Please select…', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2529, 'frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelSelect', 'Please select…', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2530, 'frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLabelOrdernumber', 'Order number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2531, 'frontend/register/steps', 1, 2, 'CheckoutStepBasketText', 'Shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2532, 'frontend/register/steps', 1, 2, 'CheckoutStepRegisterText', 'Registration', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2533, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlinePrice', 'by price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2534, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlineSupplier', 'by manufacturer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2535, 'frontend/tellafriend/index', 1, 2, 'TellAFriendLabelFriendsMail', 'Recipient´s email address ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2536, 'frontend/detail/buy', 1, 2, 'DetailBuyValueSelect', 'Please select…', '2012-08-22 15:57:47', '2012-08-22 15:57:47');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(2537, 'frontend/checkout/cart_item', 1, 2, 'CartItemInfoId', 'Order number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2538, 'frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterIdLabelInline', 'Order number', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2539, 'frontend/detail/article_config_step', 1, 2, 'DetailConfigValueSelect', 'Please select…', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2540, 'frontend/register/steps', 1, 2, 'CheckoutStepConfirmText', 'Complete order', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2541, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoInstock', 'Ready to ship today, <br/> Delivery time appr. 1-3 workdays', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2542, 'frontend/index/footer', 1, 2, 'IndexCopyright', 'Copyright &copy; 2010 shopware.ag - All rights reserved.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2543, 'documents/index', 1, 2, 'DocumentIndexVoucher', '', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2544, 'documents/index', 1, 2, 'DocumentIndexCurrency', '', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2545, 'frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlineCutdown', 'Restrict search result.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2546, 'frontend/listing/listing_actions', 1, 2, 'ListingActionsSettingsTitle', 'Select view', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2547, 'newsletter/index/footer', 1, 2, 'NewsletterFooterCopyright', 'Copyright © 2010 shopware AG - All rights reserved.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2548, 'frontend/plugins/compare/index', 1, 2, 'ListingBoxLinkCompare', 'Compare ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2549, 'frontend/index/menu_footer', 1, 2, 'sFooterServiceHotline', 'Telephone support and counselling under:<br /><br /><strong>0180 - 000000</strong><br/>Mon-Fri, 9 am - 5 pm', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2550, 'frontend/index/menu_footer', 1, 2, 'sFooterNewsletter', 'Subscribe to the free demoshop newsletter and ensure that you will no longer miss any of our demoshop offers or news.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2551, 'frontend/detail/index', 1, 2, 'DetailFromNew', 'Manufacturer:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2552, 'frontend/checkout/confirm', 1, 2, 'ConfirmHeadlineAdditionalOptions', 'Further options', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2553, 'frontend/index/index', 1, 2, 'WidgetsTrustedLogoText2', '<span><strong>Secure</strong> shopping</span><br/>certified by Trusted Shops', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2554, 'frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataNotAvailable', 'Delivery time approx. 5 working days', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2555, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataNotAvailable', 'Lieferzeit ca. 5 Tage', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2556, 'frontend/search/ajax', 1, 1, 'ListingBoxNoPicture', 'Kein Bild', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2557, 'frontend/search/ajax', 1, 2, 'ListingBoxNoPicture', 'No image', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2558, 'widgets/emotion/components/component_article', 1, 2, 'ListingBoxNoPicture', 'No image', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2559, 'widgets/emotion/components/component_article', 1, 1, 'ListingBoxNoPicture', 'No image', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2560, 'widgets/emotion/components/component_blog', 1, 1, 'EmotionBlogPreviewNopic', 'Kein Bild vorhanden', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2561, 'widgets/emotion/components/component_blog', 1, 2, 'EmotionBlogPreviewNopic', 'No image available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2562, 'widgets/recommendation/slide_articles', 1, 2, 'reducedPrice', 'Instead of:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2563, 'widgets/emotion/slide_articles', 1, 2, 'reducedPrice', 'Instead of:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2564, 'frontend/checkout/shipping_costs', 1, 1, 'RegisterBillingLabelState', 'Bundesstaat:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2565, 'frontend/checkout/shipping_costs', 1, 2, 'RegisterBillingLabelState', 'State', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2566, 'frontend/detail/tabs', 1, 1, 'DetailTabsAccessories', 'Zubehör', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2567, 'frontend/detail/tabs', 1, 2, 'DetailTabsAccessories', 'Accessories', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2568, 'frontend/checkout/shipping_costs', 1, 1, 'StateSelection', 'Bitte wählen:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2569, 'frontend/checkout/shipping_costs', 1, 2, 'StateSelection', 'Please select:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2570, 'frontend/plugins/recommendation/blocks_detail', 1, 2, 'DetailViewedArticlesSlider', 'Customers also viewed:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2571, 'frontend/plugins/facebook/blocks_detail', 1, 1, 'facebookTabTitle', 'Facebook-Kommentare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2572, 'frontend/plugins/facebook/blocks_detail', 1, 2, 'facebookTabTitle', 'Facebook comments', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2573, 'frontend/checkout/confirm', 1, 1, 'ConfirmTextRightOfRevocationNew', '<p>Bitte beachten Sie bei Ihrer Bestellung auch unsere <a href="{url controller=custom sCustom=8 forceSecure}" data-modal-height="500" data-modal-width="800">Widerrufsbelehrung</a>.</p>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2574, 'frontend/checkout/confirm', 1, 2, 'ConfirmTextRightOfRevocationNew', '<p>Please also note our <a href="{url controller=custom sCustom=8 forceSecure}" data-modal-height="500" data-modal-width="800">cancellation policy</a>.</p>', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
(2575, 'frontend/plugins/compare/index', 1, 1, 'DetailActionLinkCompare', 'Artikel vergleichen', '2012-08-27 22:29:57', '2012-08-27 22:29:57'),
(2576, 'frontend/index/header', 1, 1, 'IndexMetaAuthor', '', '2012-08-27 22:29:58', '2012-08-27 22:29:58'),
(2577, 'frontend/index/header', 1, 1, 'IndexMetaCopyright', '', '2012-08-27 22:29:58', '2012-08-27 22:29:58'),
(2578, 'frontend/index/header', 1, 1, 'IndexMetaKeywordsStandard', '', '2012-08-27 22:29:58', '2012-08-27 22:29:58'),
(2579, 'frontend/index/header', 1, 1, 'IndexMetaDescriptionStandard', '', '2012-08-27 22:29:58', '2012-08-27 22:29:58'),
(2581, 'frontend/index/index', 1, 1, 'WidgetsTrustedLogoText2', '<span><strong>Sicher</strong> einkaufen</span><br/>Trusted Shops zertifiziert', '2012-08-28 08:07:34', '2012-08-28 08:07:34'),
(2582, 'frontend/plugins/trusted_shops/logo', 1, 1, 'WidgetsTrustedLogoText2', '<span><strong>Sicher</strong> einkaufen</span><br/>Trusted Shops zertifiziert', '2012-08-28 08:07:35', '2012-08-28 08:07:35'),
(2583, 'frontend/blog/box', 1, 1, 'BlogInfoTags', 'Tags:', '2012-08-28 08:07:36', '2012-08-28 08:07:36'),
(2584, 'frontend/index/menu_footer', 1, 1, 'sFooterServiceHotlineHead', 'Service Hotline', '2012-08-28 08:07:36', '2012-08-28 08:07:36'),
(2585, 'frontend/index/menu_footer', 1, 1, 'IndexFooterNewsletterValue', 'Ihre E-Mail Adresse', '2012-08-28 08:07:36', '2012-08-28 08:07:36'),
(2586, 'frontend/index/menu_footer', 1, 1, 'IndexFooterNewsletterSubmit', 'Newsletter abonnieren', '2012-08-28 08:07:36', '2012-08-28 08:07:36');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_states`
--

DROP TABLE IF EXISTS `s_core_states`;
CREATE TABLE IF NOT EXISTS `s_core_states` (
  `id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `group` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_core_states`
--

INSERT INTO `s_core_states` (`id`, `description`, `position`, `group`, `mail`) VALUES
(-1, 'Abgebrochen', 25, 'state', 0),
(0, 'Offen', 1, 'state', 1),
(1, 'In Bearbeitung (Wartet)', 2, 'state', 1),
(2, 'Komplett abgeschlossen', 3, 'state', 0),
(3, 'Teilweise abgeschlossen', 4, 'state', 0),
(4, 'Storniert / Abgelehnt', 5, 'state', 1),
(5, 'Zur Lieferung bereit', 6, 'state', 1),
(6, 'Teilweise ausgeliefert', 7, 'state', 1),
(7, 'Komplett ausgeliefert', 8, 'state', 1),
(8, 'Klärung notwendig', 9, 'state', 1),
(9, 'Teilweise in Rechnung gestellt', 1, 'payment', 0),
(10, 'Komplett in Rechnung gestellt', 2, 'payment', 0),
(11, 'Teilweise bezahlt', 3, 'payment', 0),
(12, 'Komplett bezahlt', 4, 'payment', 0),
(13, '1. Mahnung', 5, 'payment', 0),
(14, '2. Mahnung', 6, 'payment', 0),
(15, '3. Mahnung', 7, 'payment', 0),
(16, 'Inkasso', 8, 'payment', 0),
(17, 'Offen', 0, 'payment', 0),
(18, 'Reserviert', 9, 'payment', 0),
(19, 'Verzoegert', 10, 'payment', 0),
(20, 'Wiedergutschrift', 11, 'payment', 0),
(21, 'Überprüfung notwendig', 12, 'payment', 0),
(30, 'Es wurde kein Kredit genehmigt.', 30, 'payment', 1),
(31, 'Der Kredit wurde vorlaeufig akzeptiert.', 31, 'payment', 1),
(32, 'Der Kredit wurde genehmigt.', 32, 'payment', 1),
(33, 'Die Zahlung wurde von der Hanseatic Bank angewiesen.', 33, 'payment', 1),
(34, 'Es wurde eine Zeitverlaengerung eingetragen.', 34, 'payment', 1),
(35, 'Vorgang wurde abgebrochen.', 35, 'payment', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_subscribes`
--

DROP TABLE IF EXISTS `s_core_subscribes`;
CREATE TABLE IF NOT EXISTS `s_core_subscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `listener` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribe` (`subscribe`,`type`,`listener`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=64 ;

--
-- Daten für Tabelle `s_core_subscribes`
--

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(1, 'Enlight_Bootstrap_InitResource_Auth', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAuth', 36, 0),
(2, 'Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onPreDispatchBackend', 36, 0),
(3, 'Enlight_Bootstrap_InitResource_Menu', 0, 'Shopware_Plugins_Backend_Menu_Bootstrap::onInitResourceMenu', 37, 0),
(4, 'Enlight_Bootstrap_InitResource_Api', 0, 'Shopware_Plugins_Core_Api_Bootstrap::onInitResourceApi', 17, 0),
(5, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Core_ControllerBase_Bootstrap::onPostDispatch', 15, 100),
(6, 'Enlight_Controller_Front_StartDispatch', 0, 'Shopware_Plugins_Core_ErrorHandler_Bootstrap::onStartDispatch', 2, 0),
(7, 'Enlight_Bootstrap_InitResource_Log', 0, 'Shopware_Plugins_Core_Log_Bootstrap::onInitResourceLog', 1, 0),
(8, 'Enlight_Controller_Front_RouteStartup', 0, 'Shopware_Plugins_Core_Log_Bootstrap::onRouteStartup', 1, 0),
(9, 'Enlight_Plugins_ViewRenderer_FilterRender', 0, 'Shopware_Plugins_Core_PostFilter_Bootstrap::onFilterRender', 13, 0),
(10, 'Enlight_Controller_Front_RouteStartup', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onRouteStartup', 8, 0),
(11, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onRouteShutdown', 8, 0),
(12, 'Enlight_Controller_Router_FilterAssembleParams', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble', 8, 0),
(13, 'Enlight_Controller_Router_FilterUrl', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl', 8, 0),
(14, 'Enlight_Controller_Router_Assemble', 0, 'Shopware_Plugins_Core_Router_Bootstrap::onAssemble', 8, 100),
(15, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_Shop_Bootstrap::onPreDispatch', 12, 0),
(16, 'Enlight_Bootstrap_InitResource_Shop', 0, 'Shopware_Plugins_Core_Shop_Bootstrap::onInitResourceShop', 12, 0),
(17, 'Enlight_Bootstrap_InitResource_System', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceSystem', 10, 0),
(18, 'Enlight_Bootstrap_InitResource_Modules', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceModules', 10, 0),
(19, 'Enlight_Bootstrap_InitResource_Adodb', 0, 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceAdodb', 10, 0),
(21, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_ViewportForward_Bootstrap::onPreDispatch', 11, 10),
(23, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Compare_Bootstrap::onPostDispatch', 20, 0),
(24, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Frontend_Statistics_Bootstrap::onDispatchLoopShutdown', 31, 0),
(25, 'Enlight_Plugins_ViewRenderer_FilterRender', 0, 'Shopware_Plugins_Frontend_Seo_Bootstrap::onFilterRender', 22, 0),
(26, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Seo_Bootstrap::onPostDispatch', 22, 0),
(27, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_TagCloud_Bootstrap::onPostDispatch', 34, 0),
(30, 'Enlight_Controller_Front_StartDispatch', 0, 'Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onStartDispatch', 19, 0),
(31, 'Enlight_Controller_Router_Route', 0, 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onRoute', 24, 10),
(32, 'Enlight_Controller_Router_Assemble', 0, 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onAssemble', 24, 10),
(37, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_LastArticles_Bootstrap::onPostDispatch', 23, 0),
(38, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onRouteShutdown', 35, 0),
(40, 'Enlight_Bootstrap_InitResource_Payments', 0, 'Shopware_Plugins_Frontend_Payment_Bootstrap::onInitResourcePayments', 39, 0),
(41, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_Check', 0, 'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend', 40, 0),
(52, 'Enlight_Bootstrap_InitResource_BackendSession', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession', 36, 0),
(53, 'Enlight_Bootstrap_InitResource_Acl', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', 36, 0),
(55, 'Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_Locale_Bootstrap::onPreDispatchBackend', 43, 0),
(56, 'Enlight_Controller_Front_DispatchLoopStartup', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onDispatchLoopStartup', 44, 0),
(57, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onFrontPreDispatch', 44, 0),
(58, 'Enlight_Bootstrap_InitResource_Auth', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onInitResourceAuth', 44, 0),
(59, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Core_Log_Bootstrap::onDispatchLoopShutdown', 1, 500),
(60, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager', 0, 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetPluginController', 46, 0),
(61, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_Store', 0, 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetStoreController', 46, 0),
(62, 'Enlight_Bootstrap_InitResource_StoreApi', 0, 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onInitResourceStoreApi', 45, 0),
(63, 'Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onPreDispatch', 45, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_tax`
--

DROP TABLE IF EXISTS `s_core_tax`;
CREATE TABLE IF NOT EXISTS `s_core_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax` decimal(10,2) NOT NULL,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `s_core_tax`
--

INSERT INTO `s_core_tax` (`id`, `tax`, `description`) VALUES
(1, '19.00', '19%'),
(4, '7.00', '7 %');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_tax_rules`
--

DROP TABLE IF EXISTS `s_core_tax_rules`;
CREATE TABLE IF NOT EXISTS `s_core_tax_rules` (
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
  KEY `areaID` (`areaID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_templates`
--

DROP TABLE IF EXISTS `s_core_templates`;
CREATE TABLE IF NOT EXISTS `s_core_templates` (
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`template`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=22 ;

--
-- Daten für Tabelle `s_core_templates`
--

INSERT INTO `s_core_templates` (`id`, `template`, `name`, `description`, `author`, `license`, `esi`, `style_support`, `emotion`, `version`, `plugin_id`) VALUES
(4, 'orange', 'Orange', NULL, 'shopware AG', 'AGPL', 0, 0, 0, 1, NULL),
(11, 'emotion_orange', 'Emotion Orange', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(14, 'emotion_turquoise', 'Emotion Turquoise', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(15, 'emotion_brown', 'Emotion Brown', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(16, 'emotion_gray', 'Emotion Gray', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(17, 'emotion_red', 'Emotion Red', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(18, 'emotion_blue', 'Emotion Blue', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(19, 'emotion_green', 'Emotion Green', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(20, 'emotion_black', 'Emotion Black', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL),
(21, 'emotion_pink', 'Emotion Pink', NULL, 'shopware AG', 'AGPL', 1, 0, 1, 2, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_translations`
--

DROP TABLE IF EXISTS `s_core_translations`;
CREATE TABLE IF NOT EXISTS `s_core_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `objectdata` longtext COLLATE utf8_unicode_ci NOT NULL,
  `objectkey` int(11) unsigned NOT NULL,
  `objectlanguage` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `objecttype` (`objecttype`,`objectkey`,`objectlanguage`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=116 ;

--
-- Daten für Tabelle `s_core_translations`
--

INSERT INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`) VALUES
(58, 'config_payment', 'a:13:{i:3;a:2:{s:11:"description";s:16:"cash on delivery";s:21:"additionaldescription";s:105:"Available in Germany only. Please note that at delivery, you will be asked to pay an additional EUR 2,00.";}i:7;a:2:{s:11:"description";s:10:"Creditcard";s:21:"additionaldescription";s:162:"Testing the United-Online-Services creditcard-payment is very easy. Just enter the following data: \nType: Mastercard, Nummer: 4111111111111111, CVC/ CVV-Code: 111";}i:2;a:2:{s:11:"description";s:5:"Debit";s:21:"additionaldescription";s:11:"Insert text";}i:4;a:2:{s:11:"description";s:7:"Invoice";s:21:"additionaldescription";s:152:"You pay easy and secure by invoice.\n\nIt is for example possible to reopen the invoice account after the costumer made his second order. (Riskmanagement)";}i:5;a:2:{s:11:"description";s:10:"Prepayment";s:21:"additionaldescription";s:194:"Once we receive the funds you send us via bank transfer, we will ship your order. We cannot be responsible for paying bank fees, so make sure the full invoice amount will be received on our end.";}i:17;a:1:{s:21:"additionaldescription";s:98:"A quick and easy way to pay with your creditcard. We accept: VISA / Master Card / American Express";}i:18;a:1:{s:21:"additionaldescription";s:209:"Carefree shopping on the internet - you can do it now in over 8,000 affiliated online shops using DIRECTebanking.com. You benefit not only from immediate delivery but also from our consumer protection policy.\n";}i:6;a:1:{s:21:"additionaldescription";s:163:"Please enter your real account data, who are connected to your address. An automatic process will check the address.\n\nAll entries will not be charged in this demo!";}i:8;a:1:{s:21:"additionaldescription";s:147:"Testing Giropay from our partner United-Online-Services in this demo isn´t possible.\nIf you are interested in getting an account please contact us.";}i:15;a:1:{s:21:"additionaldescription";s:7:"Invoice";}i:14;a:1:{s:21:"additionaldescription";s:10:"Prepayment";}i:11;a:1:{s:21:"additionaldescription";s:64:"Pay the quick and secured way! Paying direct! No account needed!";}i:12;a:1:{s:21:"additionaldescription";s:64:"Pay the quick and secured way! Paying direct! No account needed!";}}', 1, 'en'),
(59, 'config_snippets', 'a:546:{s:15:"sPaymentESDInfo";a:1:{s:5:"value";s:69:"Purchase of direct downloads is only possible by debit or credit card";}s:8:"sAGBText";a:1:{s:5:"value";s:181:"I have read the <a href="{$sBasefile}?sViewport=custom&cCUSTOM=4" title="Terms"><span style="text-decoration:underline;">Terms</span></a> of your shop and agree with their coverage.";}s:10:"sOrderInfo";a:1:{s:5:"value";s:112:"(Optionaler Freitext)If you pay debit or with your creditcard your bank account will be charged after five days.";}s:15:"sRegister_right";a:1:{s:5:"value";s:183:"<p>\nInsert your right of withdrawl here.\n<br /><br />\n<a href="{$sBasefile}?sViewport=custom&cCUSTOM=8" title="Right of Withdrawl">more informations to your right of withdrawl</a></p>";}s:28:"sNewsletterOptionUnsubscribe";a:1:{s:5:"value";s:26:"unsubscribe the newsletter";}s:26:"sNewsletterOptionSubscribe";a:1:{s:5:"value";s:24:"subscribe the newsletter";}s:15:"sNewsletterInfo";a:1:{s:5:"value";s:151:"Subsribe and get our newsletter.<br />\nOf course you can cancel this newsletter any time. Use the hyperlink in your eMail or visit this website again. ";}s:17:"sNewsletterButton";a:1:{s:5:"value";s:4:"Save";}s:20:"sNewsletterLabelMail";a:1:{s:5:"value";s:19:"Your eMail-address:";}s:22:"sNewsletterLabelSelect";a:1:{s:5:"value";s:10:"I want to:";}s:17:"sInfoEmailDeleted";a:1:{s:5:"value";s:32:"Your eMail-address were deleted.";}s:19:"sInfoEmailRegiested";a:1:{s:5:"value";s:35:"Thanks. We added your eMail-address";}s:16:"sErrorEnterEmail";a:1:{s:5:"value";s:31:"Please specify an email address";}s:16:"sErrorForgotMail";a:1:{s:5:"value";s:31:"Please enter your email address";}s:10:"sDelivery1";a:1:{s:5:"value";s:47:"Ready for shipment,<br/>\nShipping time 1-3 days";}s:17:"sErrorLoginActive";a:1:{s:5:"value";s:84:"Your account has been disabled, to clarify please get in contact with us personally!";}s:27:"sAccountACommentisdeposited";a:1:{s:5:"value";s:20:"A Comment is leaved!";}s:15:"sAccountArticle";a:1:{s:5:"value";s:7:"Article";}s:22:"sAccountBillingAddress";a:1:{s:5:"value";s:15:"Billing Address";}s:15:"sAccountComment";a:1:{s:5:"value";s:11:"Annotation:";}s:16:"sAccountcompany ";a:1:{s:5:"value";s:7:"Company";}s:16:"sAccountDownload";a:1:{s:5:"value";s:8:"Download";}s:19:"sAccountDownloadNow";a:1:{s:5:"value";s:12:"Download now";}s:29:"sAccountDownloadssortedbydate";a:1:{s:5:"value";s:29:"Your downloads sorted by date";}s:24:"sAccountErrorhasoccurred";a:1:{s:5:"value";s:22:"An Error has occurred!";}s:12:"sAccountFree";a:1:{s:5:"value";s:4:"FREE";}s:12:"sAccountfrom";a:1:{s:5:"value";s:5:"From:";}s:18:"sAccountgrandtotal";a:1:{s:5:"value";s:12:"Grand total:";}s:18:"sAccountIwanttoget";a:1:{s:5:"value";s:31:"Yes, I want to receive the free";}s:23:"sAccountmethodofpayment";a:1:{s:5:"value";s:15:"Choosen Payment";}s:14:"sAccountmodify";a:1:{s:5:"value";s:6:"Modify";}s:10:"sAccountMr";a:1:{s:5:"value";s:2:"Mr";}s:10:"sAccountMs";a:1:{s:5:"value";s:2:"Ms";}s:19:"sAccountNewPassword";a:1:{s:5:"value";s:14:"New password*:";}s:26:"sAccountnewslettersettings";a:1:{s:5:"value";s:24:"Your Newsletter settings";}s:14:"sAccountNumber";a:1:{s:5:"value";s:8:"Quantity";}s:21:"sAccountOrdercanceled";a:1:{s:5:"value";s:19:"Order was cancelled";}s:27:"sAccountOrderhasbeenshipped";a:1:{s:5:"value";s:22:"Order has been shipped";}s:23:"sAccountOrderinprogress";a:1:{s:5:"value";s:17:"Order in progress";}s:28:"sAccountOrdernotvetprocessed";a:1:{s:5:"value";s:32:"Order has not been processed yet";}s:19:"sAccountOrdernumber";a:1:{s:5:"value";s:12:"Ordernumber:";}s:29:"sAccountOrderpartiallyshipped";a:1:{s:5:"value";s:27:"Order was partially shipped";}s:26:"sAccountOrderssortedbydate";a:1:{s:5:"value";s:21:"Orders sorted by date";}s:18:"sAccountOrderTotal";a:1:{s:5:"value";s:12:"Order total:";}s:23:"sAccountPackagetracking";a:1:{s:5:"value";s:17:"Package tracking:";}s:12:"sAccountplus";a:1:{s:5:"value";s:4:"plus";}s:22:"sAccountRepeatpassword";a:1:{s:5:"value";s:17:"Repeat password*:";}s:16:"sAccountShipping";a:1:{s:5:"value";s:15:"Shipping costs:";}s:23:"sAccountshippingaddress";a:1:{s:5:"value";s:16:"Shipping address";}s:21:"sAccountthenewsletter";a:1:{s:5:"value";s:11:"Newsletter!";}s:13:"sAccountTotal";a:1:{s:5:"value";s:5:"Total";}s:17:"sAccountUnitprice";a:1:{s:5:"value";s:10:"Unit price";}s:22:"sAccountYouraccessdata";a:1:{s:5:"value";s:16:"Your access data";}s:24:"sAccountYouremailaddress";a:1:{s:5:"value";s:21:"Your e-mail address*:";}s:24:"sAccountyourSerialnumber";a:1:{s:5:"value";s:19:"Your Serial Number:";}s:26:"sAccountyourSerialnumberto";a:1:{s:5:"value";s:21:"Your Serial Number to";}s:19:"sAjaxcomparearticle";a:1:{s:5:"value";s:16:"Compared Article";}s:18:"sAjaxdeletecompare";a:1:{s:5:"value";s:14:"Delete Compare";}s:17:"sAjaxstartcompare";a:1:{s:5:"value";s:13:"Start Compare";}s:9:"sArticle1";a:1:{s:5:"value";s:12:"1 (very bad)";}s:10:"sArticle10";a:1:{s:5:"value";s:14:"10 (excellent)";}s:9:"sArticle2";a:1:{s:5:"value";s:1:"2";}s:9:"sArticle3";a:1:{s:5:"value";s:1:"3";}s:9:"sArticle4";a:1:{s:5:"value";s:1:"4";}s:9:"sArticle5";a:1:{s:5:"value";s:1:"5";}s:9:"sArticle6";a:1:{s:5:"value";s:1:"6";}s:9:"sArticle7";a:1:{s:5:"value";s:1:"7";}s:9:"sArticle8";a:1:{s:5:"value";s:1:"8";}s:9:"sArticle9";a:1:{s:5:"value";s:1:"9";}s:19:"sArticleaccessories";a:1:{s:5:"value";s:11:"Accessories";}s:19:"sArticleaddtobasked";a:1:{s:5:"value";s:11:"add to cart";}s:20:"sArticleaddtonotepad";a:1:{s:5:"value";s:16:"Add to favorites";}s:21:"sArticleafterageckeck";a:1:{s:5:"value";s:52:"Attention! Delivery only after successful age check!";}s:24:"sArticleallmanufacturers";a:1:{s:5:"value";s:22:"Show all manufacturers";}s:14:"sArticleamount";a:1:{s:5:"value";s:9:"Quantity:";}s:11:"sArticleand";a:1:{s:5:"value";s:3:"and";}s:22:"sArticlearticleperpage";a:1:{s:5:"value";s:16:"Article per page";}s:21:"sArticleavailableasan";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:26:"sArticleavailabledownloads";a:1:{s:5:"value";s:20:"Available downloads:";}s:21:"sArticleAvailablefrom";a:1:{s:5:"value";s:12:"Available as";}s:26:"sArticleavailableimmediate";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:12:"sArticleback";a:1:{s:5:"value";s:4:"Back";}s:20:"sArticleblockpricing";a:1:{s:5:"value";s:13:"Block pricing";}s:10:"sArticleby";a:1:{s:5:"value";s:3:"By:";}s:24:"sArticlechoosefirstexecu";a:1:{s:5:"value";s:32:"Attention! Choose version first!";}s:22:"sArticlecollectvoucher";a:1:{s:5:"value";s:34:"Tell a friend and catch a voucher!";}s:15:"sArticleCompare";a:1:{s:5:"value";s:7:"Compare";}s:23:"sArticlecustomerreviews";a:1:{s:5:"value";s:20:"Customer reviews for";}s:17:"sArticledatasheet";a:1:{s:5:"value";s:9:"Datasheet";}s:12:"sArticledays";a:1:{s:5:"value";s:4:"Days";}s:24:"sArticledaysshippingfree";a:1:{s:5:"value";s:14:"Shipping free!";}s:20:"sArticledeliverytime";a:1:{s:5:"value";s:13:"Shipping time";}s:19:"sArticledescription";a:1:{s:5:"value";s:11:"Description";}s:16:"sArticledownload";a:1:{s:5:"value";s:8:"Download";}s:19:"sArticleeightpoints";a:1:{s:5:"value";s:8:"8 Points";}s:23:"sArticleenterthenumbers";a:1:{s:5:"value";s:50:"Please enter the numbers in the following text box";}s:27:"sArticlefilloutallredfields";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:18:"sArticlefivepoints";a:1:{s:5:"value";s:8:"5 Points";}s:18:"sArticlefourpoints";a:1:{s:5:"value";s:8:"4 Points";}s:20:"sArticlefreeshipping";a:1:{s:5:"value";s:14:"Shipping Free!";}s:12:"sArticlefrom";a:1:{s:5:"value";s:4:"from";}s:26:"sArticlefurtherinformation";a:1:{s:5:"value";s:19:"further information";}s:19:"sArticlegetavoucher";a:1:{s:5:"value";s:16:"is your voucher*";}s:20:"sArticlehighestprice";a:1:{s:5:"value";s:13:"Highest Price";}s:12:"sArticleincl";a:1:{s:5:"value";s:5:"incl.";}s:19:"sArticleinthebasket";a:1:{s:5:"value";s:24:"add to you shopping cart";}s:17:"sArticleitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:16:"sArticlelanguage";a:1:{s:5:"value";s:9:"Language:";}s:13:"sArticlelegal";a:1:{s:5:"value";s:5:"legal";}s:14:"sArticlelooked";a:1:{s:5:"value";s:11:"Last viewed";}s:19:"sArticlelowestprice";a:1:{s:5:"value";s:12:"Lowest Price";}s:19:"sArticlemainarticle";a:1:{s:5:"value";s:12:"Main article";}s:21:"sArticlematchingitems";a:1:{s:5:"value";s:27:"Frequently Bought Together:";}s:23:"sArticleMoreinformation";a:1:{s:5:"value";s:20:"Get more information";}s:28:"sArticlemoreinformationabout";a:1:{s:5:"value";s:26:"Get more information about";}s:11:"sArticlenew";a:1:{s:5:"value";s:3:"NEW";}s:12:"sArticlenext";a:1:{s:5:"value";s:4:"Next";}s:18:"sArticleninepoints";a:1:{s:5:"value";s:8:"9 Points";}s:17:"sArticlenoPicture";a:1:{s:5:"value";s:20:"No picture available";}s:10:"sArticleof";a:1:{s:5:"value";s:2:"by";}s:16:"sArticleonepoint";a:1:{s:5:"value";s:7:"1 Point";}s:19:"sArticleonesiteback";a:1:{s:5:"value";s:13:"One Site back";}s:22:"sArticleonesiteforward";a:1:{s:5:"value";s:16:"One Site forward";}s:20:"sArticleonthenotepad";a:1:{s:5:"value";s:16:"add to favorites";}s:19:"sArticleordernumber";a:1:{s:5:"value";s:10:"Order No.:";}s:23:"sArticleotherarticlesof";a:1:{s:5:"value";s:22:"Other articles made by";}s:20:"sArticleourcommenton";a:1:{s:5:"value";s:13:"Our review to";}s:11:"sArticleout";a:1:{s:5:"value";s:3:"out";}s:16:"sArticleoverview";a:1:{s:5:"value";s:8:"Overview";}s:20:"sArticlepleasechoose";a:1:{s:5:"value";s:16:"Please choose...";}s:25:"sArticlepleasecompleteall";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:20:"sArticlepleaseselect";a:1:{s:5:"value";s:16:"Please choose...";}s:18:"sArticlepopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:14:"sArticleprices";a:1:{s:5:"value";s:6:"Prices";}s:18:"sArticleproductsof";a:1:{s:5:"value";s:16:"Products made by";}s:29:"sArticlequestionsaboutarticle";a:1:{s:5:"value";s:22:"Questions for article?";}s:22:"sArticlerecipientemail";a:1:{s:5:"value";s:22:"Receiver email address";}s:17:"sArticlerecommend";a:1:{s:5:"value";s:36:": tell a friend and catch a voucher!";}s:27:"sArticlerecommendandvoucher";a:1:{s:5:"value";s:32:"Tell a friend and get a voucher!";}s:16:"sArticlereleased";a:1:{s:5:"value";s:19:"unrated (universal)";}s:30:"sArticlereleasedafterverificat";a:1:{s:5:"value";s:44:"Reviews will be released after verification.";}s:19:"sArticlereleasedate";a:1:{s:5:"value";s:12:"Release Date";}s:27:"sArticlereleasedfrom12years";a:1:{s:5:"value";s:20:"Restricted 12 years+";}s:27:"sArticlereleasedfrom16years";a:1:{s:5:"value";s:20:"Restricted 16 years+";}s:27:"sArticlereleasedfrom18years";a:1:{s:5:"value";s:20:"Restricted 18 years+";}s:26:"sArticlereleasedfrom6years";a:1:{s:5:"value";s:24:"Restricted from 6 years+";}s:14:"sArticleReview";a:1:{s:5:"value";s:16:"Costumer Review:";}s:15:"sArticlereview1";a:1:{s:5:"value";s:16:"Costumer Review:";}s:15:"sArticlereviews";a:1:{s:5:"value";s:7:"Reviews";}s:12:"sArticlesave";a:1:{s:5:"value";s:5:"saved";}s:14:"sArticlescroll";a:1:{s:5:"value";s:6:"Scroll";}s:19:"sArticlesevenpoints";a:1:{s:5:"value";s:8:"7 Points";}s:16:"sArticleshipping";a:1:{s:5:"value";s:110:"<a href="{$sBasefile}?sViewport=custom&cCUSTOM=28" title="Shipping rates & policies">Shipping rates & policies";}s:27:"sArticleshippinginformation";a:1:{s:5:"value";s:33:"See our shipping rates & policies";}s:15:"sArticleshowall";a:1:{s:5:"value";s:8:"Show all";}s:28:"sArticleshowallmanufacturers";a:1:{s:5:"value";s:22:"Show all manufacturers";}s:23:"sArticlesimilararticles";a:1:{s:5:"value";s:15:"Suggested Items";}s:17:"sArticlesixpoints";a:1:{s:5:"value";s:8:"6 Points";}s:12:"sArticlesort";a:1:{s:5:"value";s:5:"Sort:";}s:15:"sArticlesummary";a:1:{s:5:"value";s:7:"Summary";}s:17:"sArticlesurcharge";a:1:{s:5:"value";s:17:"Additional charge";}s:26:"sArticlesystemrequirements";a:1:{s:5:"value";s:21:"Systemrequirement for";}s:15:"sArticletaxplus";a:1:{s:5:"value";s:8:"VAT plus";}s:16:"sArticletaxplus1";a:1:{s:5:"value";s:5:"VAT +";}s:17:"sArticletenpoints";a:1:{s:5:"value";s:9:"10 Points";}s:24:"sArticlethankyouverymuch";a:1:{s:5:"value";s:62:"Thank you very much. The recommendation was successfully sent.";}s:23:"sArticlethefieldsmarked";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:19:"sArticlethreepoints";a:1:{s:5:"value";s:8:"3 Points";}s:11:"sArticletip";a:1:{s:5:"value";s:4:"TIP!";}s:30:"sArticletipavailableasanimmedi";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:26:"sArticletipmoreinformation";a:1:{s:5:"value";s:25:"Further information about";}s:29:"sArticletipproductinformation";a:1:{s:5:"value";s:19:"Product information";}s:11:"sArticletop";a:1:{s:5:"value";s:3:"TOP";}s:30:"sArticletopaveragecustomerrevi";a:1:{s:5:"value";s:17:"Customer Reviews:";}s:30:"sArticletopImmediatelyavailabl";a:1:{s:5:"value";s:8:"In Stock";}s:14:"sArticletosave";a:1:{s:5:"value";s:4:"Save";}s:25:"sArticletoseeinthepicture";a:1:{s:5:"value";s:16:"On this picture:";}s:17:"sArticletwopoints";a:1:{s:5:"value";s:8:"2 Points";}s:13:"sArticleuntil";a:1:{s:5:"value";s:5:"until";}s:17:"sArticleupdatenow";a:1:{s:5:"value";s:10:"Update now";}s:29:"sArticlewithoutagerestriction";a:1:{s:5:"value";s:23:"Without age restriction";}s:19:"sArticleworkingdays";a:1:{s:5:"value";s:10:"Workingday";}s:25:"sArticlewriteanassessment";a:1:{s:5:"value";s:15:"Create a review";}s:20:"sArticlewriteareview";a:1:{s:5:"value";s:15:"Create a review";}s:19:"sArticlewritereview";a:1:{s:5:"value";s:15:"Create a review";}s:19:"sArticleyourcomment";a:1:{s:5:"value";s:13:"Your comment:";}s:16:"sArticleyourname";a:1:{s:5:"value";s:9:"Your Name";}s:19:"sArticleyouropinion";a:1:{s:5:"value";s:13:"Your comment:";}s:18:"sArticlezeropoints";a:1:{s:5:"value";s:8:"0 Points";}s:23:"sBasketaddedtothebasket";a:1:{s:5:"value";s:32:"was added to your shopping cart!";}s:13:"sBasketamount";a:1:{s:5:"value";s:9:"Quantity:";}s:14:"sBasketArticle";a:1:{s:5:"value";s:7:"Article";}s:30:"sBasketarticlefromourcatalogue";a:1:{s:5:"value";s:29:"Add articles from our catalog";}s:22:"sBasketarticlenotfound";a:1:{s:5:"value";s:17:"Article not found";}s:20:"sBasketasanimmediate";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:23:"sBasketasasmallthankyou";a:1:{s:5:"value";s:63:"As a small thank-you, you receive this article free in addition";}s:19:"sBasketavailability";a:1:{s:5:"value";s:8:"In Stock";}s:20:"sBasketavailablefrom";a:1:{s:5:"value";s:20:"Available from stock";}s:21:"sBasketbacktomainpage";a:1:{s:5:"value";s:17:"Back to Mainpage!";}s:21:"sBasketbasketdiscount";a:1:{s:5:"value";s:13:"Cart-discount";}s:30:"sBasketbetweenfollowingpremium";a:1:{s:5:"value";s:44:"Please choose between the following premiums";}s:15:"sBasketcheckout";a:1:{s:5:"value";s:8:"Checkout";}s:30:"sBasketcheckoutcustomerswithyo";a:1:{s:5:"value";s:45:"Customers Who Bought This Item Also Bought\n  ";}s:23:"sBasketcontinueshopping";a:1:{s:5:"value";s:17:"Continue shopping";}s:30:"sBasketcustomerswithyoursimila";a:1:{s:5:"value";s:48:"Customers Viewing This Page May Be Interested in";}s:30:"sBasketdeletethisitemfrombaske";a:1:{s:5:"value";s:34:"Erase this article from the basket";}s:15:"sBasketdelivery";a:1:{s:5:"value";s:13:"Shipping time";}s:22:"sBasketdeliverycountry";a:1:{s:5:"value";s:16:"Shipping country";}s:24:"sBasketdesignatedarticle";a:1:{s:5:"value";s:50:"Favorites - selected articles for a later purchase";}s:15:"sBasketdispatch";a:1:{s:5:"value";s:16:"Mode of shipment";}s:23:"sBasketerasefromnotepad";a:1:{s:5:"value";s:37:"Erase this article from the favorites";}s:25:"sBasketforwardingexpenses";a:1:{s:5:"value";s:25:"Shipping rates & policies";}s:11:"sBasketfree";a:1:{s:5:"value";s:5:"FREE!";}s:12:"sBasketfree1";a:1:{s:5:"value";s:4:"FREE";}s:11:"sBasketfrom";a:1:{s:5:"value";s:4:"from";}s:18:"sBasketinthebasket";a:1:{s:5:"value";s:20:"add to shopping cart";}s:20:"sBasketintothebasket";a:1:{s:5:"value";s:20:"Add to shopping cart";}s:28:"sBasketItautomaticallystores";a:1:{s:5:"value";s:126:"automatically stores your personal favorite-list.\nYou can comfortably retrieve your registered articles in a subsequent visit.";}s:26:"sBasketjustthedesireditems";a:1:{s:5:"value";s:56:"Put simply the desired articles on the favorite-list and";}s:23:"sBasketlastinyourbasket";a:1:{s:5:"value";s:32:"Last article added in your cart:";}s:24:"sBasketminimumordervalue";a:1:{s:5:"value";s:37:"Attention. The minimum order value of";}s:13:"sBasketmodify";a:1:{s:5:"value";s:6:"Modify";}s:23:"sBasketmoreinformations";a:1:{s:5:"value";s:16:"More information";}s:27:"sBasketnoitemsonyournotepad";a:1:{s:5:"value";s:45:"There are no articles on your favorites-list.";}s:25:"sBasketnopictureavailable";a:1:{s:5:"value";s:20:"No picture available";}s:14:"sBasketnotepad";a:1:{s:5:"value";s:9:"Favorites";}s:20:"sBasketnotreachedyet";a:1:{s:5:"value";s:19:"is not reached yet!";}s:13:"sBasketnumber";a:1:{s:5:"value";s:8:"Quantity";}s:18:"sBasketordernumber";a:1:{s:5:"value";s:9:"Order No.";}s:14:"sBasketpayment";a:1:{s:5:"value";s:17:"Method of Payment";}s:19:"sBasketpleasechoose";a:1:{s:5:"value";s:16:"Please choose...";}s:23:"sBasketrecalculateprice";a:1:{s:5:"value";s:31:"Recalculate price - update cart";}s:26:"sBasketsaveyourpersonalfav";a:1:{s:5:"value";s:60:"Save your personal favorites - until you visit us next time.";}s:17:"sBasketshowbasket";a:1:{s:5:"value";s:18:"Show shopping cart";}s:18:"sBasketstep1basket";a:1:{s:5:"value";s:21:"Step1 - Shopping Cart";}s:15:"sBasketsubtotal";a:1:{s:5:"value";s:9:"Subtotal:";}s:10:"sBasketsum";a:1:{s:5:"value";s:5:"Total";}s:17:"sBaskettocheckout";a:1:{s:5:"value";s:9:"Checkout!";}s:15:"sBaskettotalsum";a:1:{s:5:"value";s:5:"Total";}s:16:"sBasketunitprice";a:1:{s:5:"value";s:10:"Unit price";}s:15:"sBasketweekdays";a:1:{s:5:"value";s:8:"Workdays";}s:17:"sBasketyourbasket";a:1:{s:5:"value";s:18:"Your shopping cart";}s:24:"sBasketyourbasketisempty";a:1:{s:5:"value";s:43:"There are no articles in your shopping cart";}s:21:"sCategorymanufacturer";a:1:{s:5:"value";s:12:"Manufacturer";}s:18:"sCategorynopicture";a:1:{s:5:"value";s:20:"No picture available";}s:26:"sCategoryothermanufacturer";a:1:{s:5:"value";s:19:"Other manufacturers";}s:16:"sCategoryshowall";a:1:{s:5:"value";s:8:"Show all";}s:18:"sCategorytopseller";a:1:{s:5:"value";s:9:"Topseller";}s:18:"sContentattachment";a:1:{s:5:"value";s:11:"Attachment:";}s:12:"sContentback";a:1:{s:5:"value";s:4:"Back";}s:14:"sContentbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:21:"sContentbrowseforward";a:1:{s:5:"value";s:14:"Browse forward";}s:26:"sContentcurrentlynoentries";a:1:{s:5:"value";s:30:"Currently no entries available";}s:16:"sContentdownload";a:1:{s:5:"value";s:8:"Download";}s:21:"sContententrynotfound";a:1:{s:5:"value";s:14:"No entry found";}s:21:"sContentgobackonepage";a:1:{s:5:"value";s:16:"Browse backward ";}s:12:"sContentmore";a:1:{s:5:"value";s:6:"[more]";}s:24:"sContentmoreinformations";a:1:{s:5:"value";s:17:"More information:";}s:21:"sContentonthispicture";a:1:{s:5:"value";s:16:"On this picture:";}s:20:"sCustomdirectcontact";a:1:{s:5:"value";s:14:"Direct contact";}s:19:"sCustomsitenotfound";a:1:{s:5:"value";s:14:"Page not found";}s:19:"sErrorBillingAdress";a:1:{s:5:"value";s:40:"Please fill out all fields marked in red";}s:14:"sErrorcheckout";a:1:{s:5:"value";s:8:"Checkout";}s:21:"sErrorCookiesDisabled";a:1:{s:5:"value";s:66:"To use this feature, you must have cookies enabled in your browser";}s:11:"sErrorEmail";a:1:{s:5:"value";s:35:"Please enter a valid e-mail address";}s:19:"sErrorEmailForgiven";a:1:{s:5:"value";s:40:"This email address is already registered";}s:19:"sErrorEmailNotFound";a:1:{s:5:"value";s:33:"This e-mail address was not found";}s:11:"sErrorerror";a:1:{s:5:"value";s:21:"An error has occurred";}s:23:"sErrorForgotMailUnknown";a:1:{s:5:"value";s:28:"This mail address is unknown";}s:10:"sErrorhome";a:1:{s:5:"value";s:4:"Home";}s:11:"sErrorLogin";a:1:{s:5:"value";s:50:"Your access data could not be assigned to any user";}s:23:"sErrorMerchantNotActive";a:1:{s:5:"value";s:73:"You are not registered as a reseller or your account has not approved yet";}s:29:"sErrormoreinterestingarticles";a:1:{s:5:"value";s:17:"You may also like";}s:22:"sErrororderwascanceled";a:1:{s:5:"value";s:22:"The order was canceled";}s:14:"sErrorPassword";a:1:{s:5:"value";s:57:"Please choose a password consisting at least 6 characters";}s:21:"sErrorShippingAddress";a:1:{s:5:"value";s:40:"Please fill out all fields marked in red";}s:27:"sErrorthisarticleisnolonger";a:1:{s:5:"value";s:34:"The product has been discontinued!";}s:12:"sErrorUnknow";a:1:{s:5:"value";s:29:"An unknown error has occurred";}s:16:"sErrorValidEmail";a:1:{s:5:"value";s:35:"Please enter a valid e-mail address";}s:13:"sIndexaccount";a:1:{s:5:"value";s:10:"My Account";}s:16:"sIndexactivation";a:1:{s:5:"value";s:284:"3. Activation: As soon as both satisfactory forms are given to us and provided that your signatures match, we switch you for plays from 18 freely. Afterwards we immediately send you a confirmation email. Then you can quite simply order the USK 18 title comfortably about the web shop.";}s:25:"sIndexallpricesexcludevat";a:1:{s:5:"value";s:28:"* All prices exclude VAT and";}s:25:"sIndexandpossibledelivery";a:1:{s:5:"value";s:50:"and possibly shipping fees unless otherwise stated";}s:12:"sIndexappear";a:1:{s:5:"value";s:9:"Released:";}s:13:"sIndexarticle";a:1:{s:5:"value";s:10:"article(s)";}s:19:"sIndexarticlesfound";a:1:{s:5:"value";s:18:" article(s) found!";}s:24:"sIndexavailabledownloads";a:1:{s:5:"value";s:20:"Available downloads:";}s:16:"sIndexbacktohome";a:1:{s:5:"value";s:12:"back to home";}s:12:"sIndexbasket";a:1:{s:5:"value";s:16:"My shopping cart";}s:25:"sIndexcertifiedonlineshop";a:1:{s:5:"value";s:113:"Trusted Shops certified online shop with money-back guarantee. Click on the seal in order to verify the validity.";}s:26:"sIndexchangebillingaddress";a:1:{s:5:"value";s:22:"Change billing address";}s:27:"sIndexchangedeliveryaddress";a:1:{s:5:"value";s:23:"Change delivery address";}s:19:"sIndexchangepayment";a:1:{s:5:"value";s:24:"Change method of payment";}s:19:"sIndexclientaccount";a:1:{s:5:"value";s:16:"Customer account";}s:26:"sIndexcompareupto5articles";a:1:{s:5:"value";s:42:"You can compare up to 5 items in one step!";}s:15:"sIndexcopyright";a:1:{s:5:"value";s:51:"Copyright © 2008 shopware.ag - All rights reserved.";}s:11:"sIndexcover";a:1:{s:5:"value";s:6:"Cover:";}s:14:"sIndexcurrency";a:1:{s:5:"value";s:9:"Currency:";}s:14:"sIndexdownload";a:1:{s:5:"value";s:8:"Download";}s:13:"sIndexenglish";a:1:{s:5:"value";s:7:"English";}s:11:"sIndexextra";a:1:{s:5:"value";s:7:"Extras:";}s:28:"sIndexforreasonofinformation";a:1:{s:5:"value";s:477:"For reasons of information we display in our online shop also games which own no gift suitable for young people. However, there is a way for customers, who have already completed the eighteenth year,  to purchase these games. Now you can order with us also quite simply USK 18 games above the postal dispatch way. To fulfil the requirements of the protection of children and young people-sedate you must be personalised in addition simply by Postident. The way is quite simply:";}s:12:"sIndexfrench";a:1:{s:5:"value";s:6:"French";}s:10:"sIndexfrom";a:1:{s:5:"value";s:4:"from";}s:12:"sIndexgerman";a:1:{s:5:"value";s:6:"German";}s:11:"sIndexhello";a:1:{s:5:"value";s:5:"Hello";}s:10:"sIndexhome";a:1:{s:5:"value";s:4:"Home";}s:20:"sIndexhowcaniacquire";a:1:{s:5:"value";s:68:"How can i acquire the games which are restricted only from 18 years?";}s:14:"sIndexlanguage";a:1:{s:5:"value";s:9:"Language:";}s:12:"sIndexlogout";a:1:{s:5:"value";s:6:"Logout";}s:14:"sIndexmybasket";a:1:{s:5:"value";s:12:"Show my cart";}s:24:"sIndexmyinstantdownloads";a:1:{s:5:"value";s:20:"My instant downloads";}s:14:"sIndexmyorders";a:1:{s:5:"value";s:9:"My orders";}s:22:"sIndexnoagerestriction";a:1:{s:5:"value";s:23:"Without age restriction";}s:13:"sIndexnotepad";a:1:{s:5:"value";s:9:"Favorites";}s:18:"sIndexonthepicture";a:1:{s:5:"value";s:16:"On this picture:";}s:17:"sIndexordernumber";a:1:{s:5:"value";s:10:"Order no.:";}s:18:"sIndexourcommentto";a:1:{s:5:"value";s:13:"Our review on";}s:14:"sIndexoverview";a:1:{s:5:"value";s:8:"Overview";}s:16:"sIndexpagenumber";a:1:{s:5:"value";s:11:"Pagenumber:";}s:26:"sIndexpossiblydeliveryfees";a:1:{s:5:"value";s:50:"and possibly shipping fees unless otherwise stated";}s:15:"sIndexpostident";a:1:{s:5:"value";s:171:"2. POSTIDENT: The German post provides a POSTIDENT form in the confirmation of your majority. Please, sign this also. Then the German post sends both signed documents to .";}s:19:"sIndexpricesinclvat";a:1:{s:5:"value";s:27:"* All prices incl. VAT plus";}s:14:"sIndexprinting";a:1:{s:5:"value";s:6:"Print:";}s:25:"sIndexproductinformations";a:1:{s:5:"value";s:19:"Product information";}s:18:"sIndexrealizedwith";a:1:{s:5:"value";s:13:"realised with";}s:30:"sIndexrealizedwiththeshopsyste";a:1:{s:5:"value";s:57:"realized by shopware ag an the webshop-software Shopware ";}s:14:"sIndexreleased";a:1:{s:5:"value";s:19:"unrated (universal)";}s:25:"sIndexreleasedfrom12years";a:1:{s:5:"value";s:20:"Restricted 12 years+";}s:25:"sIndexreleasedfrom16years";a:1:{s:5:"value";s:20:"Restricted 16 years+";}s:25:"sIndexreleasedfrom18years";a:1:{s:5:"value";s:20:"Restricted 18 years+";}s:24:"sIndexreleasedfrom6years";a:1:{s:5:"value";s:19:"Restricted 6 years+";}s:12:"sIndexsearch";a:1:{s:5:"value";s:0:"";}s:14:"sIndexshipping";a:1:{s:5:"value";s:14:"Shipping rates";}s:14:"sIndexshopware";a:1:{s:5:"value";s:8:"Shopware";}s:17:"sIndexshownotepad";a:1:{s:5:"value";s:14:"Show favorites";}s:21:"sIndexsimilararticles";a:1:{s:5:"value";s:15:"Suggested Items";}s:10:"sIndexsite";a:1:{s:5:"value";s:4:"Page";}s:22:"sIndexsuitablearticles";a:1:{s:5:"value";s:16:"Suggested Items:";}s:27:"sIndexsystemrequirementsfor";a:1:{s:5:"value";s:22:"System requirement for";}s:8:"sIndexto";a:1:{s:5:"value";s:2:"To";}s:23:"sIndextrustedshopslabel";a:1:{s:5:"value";s:61:"Trusted shops stamp of quality - request validity check here!";}s:19:"sIndexviewmyaccount";a:1:{s:5:"value";s:16:"Visit my account";}s:19:"sIndexwelcometoyour";a:1:{s:5:"value";s:28:"and welcome to your personal";}s:10:"sIndexwere";a:1:{s:5:"value";s:4:"were";}s:16:"sIndexyouarehere";a:1:{s:5:"value";s:13:"You are here:";}s:19:"sIndexyouloadthepdf";a:1:{s:5:"value";s:222:"1. Load the PDF registration form in your customer area and print it out. Please, present this form together with your identity card or passport in a branch of the German post. Mark the appropriate field and sign the form.";}s:26:"sInfoEmailAlreadyRegiested";a:1:{s:5:"value";s:34:"You already receive our newsletter";}s:26:"sLoginalreadyhaveanaccount";a:1:{s:5:"value";s:25:"I am a returning customer";}s:15:"sLoginareyounew";a:1:{s:5:"value";s:27:"New customer? Start here at";}s:10:"sLoginback";a:1:{s:5:"value";s:4:"Back";}s:18:"sLogindealeraccess";a:1:{s:5:"value";s:16:"Reseller account";}s:11:"sLoginerror";a:1:{s:5:"value";s:22:"An error has occurred!";}s:24:"sLoginloginwithyouremail";a:1:{s:5:"value";s:50:"Please login using your eMail address and password";}s:18:"sLoginlostpassword";a:1:{s:5:"value";s:21:"Forgot your password?";}s:28:"sLoginlostpasswordhereyoucan";a:1:{s:5:"value";s:57:"Forgot your password? Here you can request a new password";}s:17:"sLoginnewcustomer";a:1:{s:5:"value";s:12:"New customer";}s:28:"sLoginnewpasswordhasbeensent";a:1:{s:5:"value";s:38:"Your new password has been sent to you";}s:15:"sLoginnoproblem";a:1:{s:5:"value";s:91:"No problem, ordering from us is easy and secure. The registration takes only a few moments.";}s:14:"sLoginpassword";a:1:{s:5:"value";s:14:"Your password:";}s:17:"sLoginregisternow";a:1:{s:5:"value";s:12:"Register now";}s:16:"sLoginstep1login";a:1:{s:5:"value";s:26:"Step1 - Login/Registration";}s:27:"sLoginwewillsendyouanewpass";a:1:{s:5:"value";s:94:"We will send you a new, randomly generated password. This can be changed in the customer area.";}s:21:"sLoginyouremailadress";a:1:{s:5:"value";s:20:"Your e-mail address:";}s:27:"sOrderprocessacceptourterms";a:1:{s:5:"value";s:46:"Please accept our general terms and conditions";}s:19:"sOrderprocessamount";a:1:{s:5:"value";s:8:"Quantity";}s:20:"sOrderprocessarticle";a:1:{s:5:"value";s:7:"Article";}s:26:"sOrderprocessbillingadress";a:1:{s:5:"value";s:72:"You can change billing address, shipping address and payment method now.";}s:27:"sOrderprocessbillingadress1";a:1:{s:5:"value";s:15:"Billing address";}s:19:"sOrderprocesschange";a:1:{s:5:"value";s:6:"Change";}s:25:"sOrderprocesschangebasket";a:1:{s:5:"value";s:20:"Change shopping cart";}s:30:"sOrderprocesschangeyourpayment";a:1:{s:5:"value";s:122:"Please change your payment method. The purchase of instant downloads is currently not possible with your selected payment!";}s:22:"sOrderprocessclickhere";a:1:{s:5:"value";s:44:"Trusted Shops stamp of quality - click here.";}s:20:"sOrderprocesscomment";a:1:{s:5:"value";s:11:"Annotation:";}s:20:"sOrderprocesscompany";a:1:{s:5:"value";s:7:"Company";}s:28:"sOrderprocessdeliveryaddress";a:1:{s:5:"value";s:16:"Shipping address";}s:21:"sOrderprocessdispatch";a:1:{s:5:"value";s:16:"Shipping method:";}s:25:"sOrderprocessdoesnotreach";a:1:{s:5:"value";s:16:"not reached yet!";}s:28:"sOrderprocessenteradditional";a:1:{s:5:"value";s:57:"Please, give here additional information about your order";}s:30:"sOrderprocessforwardingexpense";a:1:{s:5:"value";s:15:"Shipping costs:";}s:25:"sOrderprocessforyourorder";a:1:{s:5:"value";s:28:"Thank you for your order at ";}s:17:"sOrderprocessfree";a:1:{s:5:"value";s:4:"FREE";}s:26:"sOrderprocessimportantinfo";a:1:{s:5:"value";s:45:"Important information to the shipping country";}s:30:"sOrderprocessinformationsabout";a:1:{s:5:"value";s:30:"Informations about your order:";}s:27:"sOrderprocessmakethepayment";a:1:{s:5:"value";s:15:"Please pay now:";}s:30:"sOrderprocessminimumordervalue";a:1:{s:5:"value";s:46:"Attention. You have the minimum order value of";}s:15:"sOrderprocessmr";a:1:{s:5:"value";s:2:"Mr";}s:15:"sOrderprocessms";a:1:{s:5:"value";s:2:"Ms";}s:21:"sOrderprocessnettotal";a:1:{s:5:"value";s:10:"Net total:";}s:24:"sOrderprocessordernumber";a:1:{s:5:"value";s:13:"Order number:";}s:30:"sOrderprocessperorderonevouche";a:1:{s:5:"value";s:41:"Per order max. one voucher can be cashed.";}s:24:"sOrderprocesspleasecheck";a:1:{s:5:"value";s:50:"Please, check your order again, before sending it.";}s:18:"sOrderprocessprice";a:1:{s:5:"value";s:5:"Price";}s:18:"sOrderprocessprint";a:1:{s:5:"value";s:5:"Print";}s:27:"sOrderprocessprintorderconf";a:1:{s:5:"value";s:33:"Print out order confirmation now!";}s:29:"sOrderprocessrecommendtoprint";a:1:{s:5:"value";s:55:"We recommend to print out the order confirmation below.";}s:23:"sOrderprocessrevocation";a:1:{s:5:"value";s:18:"Right of withdrawl";}s:26:"sOrderprocesssameappliesto";a:1:{s:5:"value";s:42:"The same applies to the selected articles.";}s:28:"sOrderprocessselectedpayment";a:1:{s:5:"value";s:22:"Choosen payment method";}s:30:"sOrderprocessspecifythetransfe";a:1:{s:5:"value";s:60:"Please, give by the referral the following intended purpose:";}s:25:"sOrderprocesstotalinclvat";a:1:{s:5:"value";s:16:"Total incl. VAT:";}s:23:"sOrderprocesstotalprice";a:1:{s:5:"value";s:5:"Total";}s:29:"sOrderprocesstransactionumber";a:1:{s:5:"value";s:19:"Transaction number:";}s:30:"sOrderprocesstrustedshopmember";a:1:{s:5:"value";s:155:"As a member of Trusted Shops, we offer a\n     additional money-back guarantee. We take all\n     Cost of this warranty, you only need to be\n     registered.";}s:26:"sOrderprocessvouchernumber";a:1:{s:5:"value";s:15:"Voucher number:";}s:27:"sOrderprocesswehaveprovided";a:1:{s:5:"value";s:48:"We have sent you an order confirmation by eMail.";}s:28:"sOrderprocessyourvouchercode";a:1:{s:5:"value";s:62:"Please, enter your voucher code here and click on the "arrow".";}s:21:"sPaymentaccountnumber";a:1:{s:5:"value";s:16:"Account number*:";}s:22:"sPaymentbankcodenumber";a:1:{s:5:"value";s:17:"Bank code number:";}s:28:"sPaymentchooseyourcreditcard";a:1:{s:5:"value";s:26:"Choose your credit card *:";}s:24:"sPaymentcreditcardnumber";a:1:{s:5:"value";s:26:"Your credit card number *:";}s:25:"sPaymentcurrentlyselected";a:1:{s:5:"value";s:18:"Currently selected";}s:26:"sPaymentcurrentlyselected1";a:1:{s:5:"value";s:18:"Currently Selected";}s:11:"sPaymentEsd";a:1:{s:5:"value";s:14:"online banking";}s:23:"sPaymentmarkedfieldsare";a:1:{s:5:"value";s:43:"Please fill in all required address fields.";}s:13:"sPaymentmonth";a:1:{s:5:"value";s:5:"Month";}s:24:"sPaymentnameofcardholder";a:1:{s:5:"value";s:20:"Name of cardholder*:";}s:16:"sPaymentshipping";a:1:{s:5:"value";s:25:"Shipping rates & policies";}s:18:"sPaymentvaliduntil";a:1:{s:5:"value";s:14:"Valid until *:";}s:12:"sPaymentyear";a:1:{s:5:"value";s:4:"Year";}s:16:"sPaymentyourbank";a:1:{s:5:"value";s:11:"Your Bank*:";}s:22:"sPaymentyourcreditcard";a:1:{s:5:"value";s:15:"Your creditcard";}s:19:"sRegisteraccessdata";a:1:{s:5:"value";s:10:"Your Login";}s:25:"sRegisterafterregistering";a:1:{s:5:"value";s:79:"Once your account is approved, you will see your reseller prices in this shop.\n";}s:30:"sRegisteralreadyhaveatraderacc";a:1:{s:5:"value";s:36:"You have already a reseller account?";}s:13:"sRegisterback";a:1:{s:5:"value";s:4:"Back";}s:18:"sRegisterbirthdate";a:1:{s:5:"value";s:10:"Birthdate:";}s:19:"sRegistercharacters";a:1:{s:5:"value";s:11:"Characters.";}s:19:"sRegistercityandzip";a:1:{s:5:"value";s:22:"Postal Code and City*:";}s:25:"sRegisterclickheretologin";a:1:{s:5:"value";s:20:"Click here to log in";}s:16:"sRegistercompany";a:1:{s:5:"value";s:8:"Company:";}s:22:"sRegisterconsiderupper";a:1:{s:5:"value";s:31:"Consider upper and lower case. ";}s:16:"sRegistercountry";a:1:{s:5:"value";s:9:"Country*:";}s:19:"sRegisterdepartment";a:1:{s:5:"value";s:11:"Department:";}s:29:"sRegisterenterdeliveryaddress";a:1:{s:5:"value";s:29:"Enter a delivery address here";}s:22:"sRegistererroroccurred";a:1:{s:5:"value";s:22:"An error has occurred!";}s:12:"sRegisterfax";a:1:{s:5:"value";s:4:"Fax:";}s:21:"sRegisterfieldsmarked";a:1:{s:5:"value";s:18:"* required fields.";}s:18:"sRegisterfirstname";a:1:{s:5:"value";s:13:"First name *:";}s:22:"sRegisterforavatexempt";a:1:{s:5:"value";s:89:"Enter a VAT number to remove tax for orders being delivered to a country outside the EU. ";}s:23:"sRegisterfreetextfields";a:1:{s:5:"value";s:44:"Additional Info: (for example: mobile phone)";}s:20:"sRegisterinthefuture";a:1:{s:5:"value";s:59:"Please set up your account so that you can see your orders.";}s:17:"sRegisterlastname";a:1:{s:5:"value";s:11:"Last name*:";}s:11:"sRegistermr";a:1:{s:5:"value";s:3:"Mr.";}s:11:"sRegisterms";a:1:{s:5:"value";s:3:"Ms.";}s:13:"sRegisternext";a:1:{s:5:"value";s:4:"Next";}s:26:"sRegisternocustomeraccount";a:1:{s:5:"value";s:30:"Do not open a customer account";}s:29:"sRegisteronthisfollowingpages";a:1:{s:5:"value";s:0:"";}s:14:"sRegisterphone";a:1:{s:5:"value";s:7:"Phone*:";}s:21:"sRegisterpleasechoose";a:1:{s:5:"value";s:14:"Please choose:";}s:21:"sRegisterpleaseselect";a:1:{s:5:"value";s:17:"Please choose...\n";}s:27:"sRegisterrepeatyourpassword";a:1:{s:5:"value";s:22:"Repeat your password*:";}s:19:"sRegisterrevocation";a:1:{s:5:"value";s:18:"Right of withdrawl";}s:13:"sRegistersave";a:1:{s:5:"value";s:4:"Save";}s:22:"sRegisterselectpayment";a:1:{s:5:"value";s:43:"Please choose your preferred payment method";}s:29:"sRegistersendusyourtradeproof";a:1:{s:5:"value";s:28:"Fax your VAT number to us at";}s:30:"sRegistersendusyourtradeproofb";a:1:{s:5:"value";s:151:"Send your VAT number by fax to +49 2555 99 75 0 99.\nIf you are already a registered re-seller, you can skip this part. You don´t have to send it again.";}s:25:"sRegisterseperatedelivery";a:1:{s:5:"value";s:50:"I would like to enter a different delivery address";}s:30:"sRegistershippingaddressdiffer";a:1:{s:5:"value";s:56:"Your shipping address differs from your billing address.";}s:24:"sRegisterstreetandnumber";a:1:{s:5:"value";s:16:"Street and No.*:";}s:28:"sRegistersubscribenewsletter";a:1:{s:5:"value";s:26:"Sign up for our newsletter";}s:14:"sRegistertitle";a:1:{s:5:"value";s:7:"Title*:";}s:27:"sRegistertraderregistration";a:1:{s:5:"value";s:21:"Reseller Registration";}s:14:"sRegistervatid";a:1:{s:5:"value";s:11:"VAT number:";}s:16:"sRegisterwecheck";a:1:{s:5:"value";s:49:"After verification your account will be approved.";}s:27:"sRegisterwecheckyouastrader";a:1:{s:5:"value";s:212:"After verification your account will be approved. After clearing you get informations per eMail.\nFrom now on, you´ll see your special reseller prices directly, displayed on the product-detail- and overview-pages.";}s:24:"sRegisteryouraccountdata";a:1:{s:5:"value";s:17:"Your billing data";}s:18:"sRegisteryouremail";a:1:{s:5:"value";s:20:"Your eMail-address*:";}s:21:"sRegisteryourpassword";a:1:{s:5:"value";s:15:"Your password*:";}s:27:"sRegisteryourpasswordatlast";a:1:{s:5:"value";s:33:"Your password needs a minimum of ";}s:19:"sSearchafterfilters";a:1:{s:5:"value";s:9:"by filter";}s:20:"sSearchallcategories";a:1:{s:5:"value";s:19:"Show all categories";}s:17:"sSearchallfilters";a:1:{s:5:"value";s:11:"All filters";}s:22:"sSearchallmanufacturer";a:1:{s:5:"value";s:17:"All Manufacturers";}s:16:"sSearchallprices";a:1:{s:5:"value";s:10:"All Prices";}s:20:"sSearcharticlesfound";a:1:{s:5:"value";s:18:" article(s) found!";}s:22:"sSearcharticlesperpage";a:1:{s:5:"value";s:18:"Articles per page:";}s:13:"sSearchbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:21:"sSearchbymanufacturer";a:1:{s:5:"value";s:15:"by manufacturer";}s:14:"sSearchbyprice";a:1:{s:5:"value";s:8:"by price";}s:14:"sSearchelected";a:1:{s:5:"value";s:8:"Choosen:";}s:19:"sSearchhighestprice";a:1:{s:5:"value";s:13:"Highest price";}s:16:"sSearchitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:18:"sSearchlowestprice";a:1:{s:5:"value";s:12:"Lowest price";}s:15:"sSearchnextpage";a:1:{s:5:"value";s:9:"Next page";}s:22:"sSearchnoarticlesfound";a:1:{s:5:"value";s:42:"Your search did not match to any articles!";}s:18:"sSearchonepageback";a:1:{s:5:"value";s:13:"One page back";}s:25:"sSearchothermanufacturers";a:1:{s:5:"value";s:20:"Other manufacturers:";}s:17:"sSearchpopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:18:"sSearchreleasedate";a:1:{s:5:"value";s:12:"Release date";}s:16:"sSearchrelevance";a:1:{s:5:"value";s:9:"Relevance";}s:23:"sSearchsearchcategories";a:1:{s:5:"value";s:20:"Search by categories";}s:19:"sSearchsearchresult";a:1:{s:5:"value";s:16:"Search result(s)";}s:25:"sSearchsearchtermtooshort";a:1:{s:5:"value";s:37:"The entered search term is too short.";}s:14:"sSearchshowall";a:1:{s:5:"value";s:8:"Show all";}s:11:"sSearchsort";a:1:{s:5:"value";s:5:"Sort:";}s:9:"sSearchto";a:1:{s:5:"value";s:2:"to";}s:29:"sSearchunfortunatelytherewere";a:1:{s:5:"value";s:38:"unfortunately no entries were found to";}s:11:"sSearchwere";a:1:{s:5:"value";s:4:"were";}s:24:"sSupportallmanufacturers";a:1:{s:5:"value";s:22:"List all manufacturers";}s:22:"sSupportarticleperpage";a:1:{s:5:"value";s:18:"Articles per page:";}s:12:"sSupportback";a:1:{s:5:"value";s:4:"Back";}s:14:"sSupportbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:23:"sSupportenterthenumbers";a:1:{s:5:"value";s:52:"Please enter the numbers into the following text box";}s:21:"sSupportentrynotfound";a:1:{s:5:"value";s:15:"No Entry found.";}s:24:"sSupportfieldsmarketwith";a:1:{s:5:"value";s:43:"Please fill in all required address fields.";}s:20:"sSupporthighestprice";a:1:{s:5:"value";s:13:"Highest price";}s:17:"sSupportitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:19:"sSupportlowestprice";a:1:{s:5:"value";s:12:"Lowest price";}s:16:"sSupportnextpage";a:1:{s:5:"value";s:16:"One page forward";}s:19:"sSupportonepageback";a:1:{s:5:"value";s:13:"One page back";}s:18:"sSupportpopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:19:"sSupportreleasedate";a:1:{s:5:"value";s:12:"Release date";}s:12:"sSupportsend";a:1:{s:5:"value";s:4:"Send";}s:12:"sSupportsort";a:1:{s:5:"value";s:8:"sort by:";}s:21:"sVoucherAlreadyCashed";a:1:{s:5:"value";s:53:"This voucher was already cashed with a previous order";}s:23:"sVoucherBoundToSupplier";a:1:{s:5:"value";s:56:"This voucher is valid only for products from {sSupplier}";}s:21:"sVoucherMinimumCharge";a:1:{s:5:"value";s:64:"The minimum turnover for this voucher amounts {sMinimumCharge} ?";}s:16:"sVoucherNotFound";a:1:{s:5:"value";s:51:"Voucher could not be found or is not valid any more";}s:23:"sVoucherOnlyOnePerOrder";a:1:{s:5:"value";s:40:"Per order only one voucher can be cashed";}s:26:"sVoucherWrongCustomergroup";a:1:{s:5:"value";s:52:"This coupon is not available for your customer group";}s:25:"sArticleinformationsabout";a:1:{s:5:"value";s:47:"Information about restricted 18 years+-articles";}s:27:"sArticlethevoucherautomatic";a:1:{s:5:"value";s:209:"* The voucher will be delivered automatically to you by eMail after registration and the first order of your friend. You have to be registered with the choosen email address in the shop to receive the voucher.";}s:18:"sBasketrecalculate";a:1:{s:5:"value";s:11:"Recalculate";}s:11:"sLoginlogin";a:1:{s:5:"value";s:5:"Login";}s:25:"sOrderprocesssendordernow";a:1:{s:5:"value";s:14:"Send order now";}s:28:"sOrderprocessforthemoneyback";a:1:{s:5:"value";s:41:"Registration for the money-back guarantee";}s:17:"sSearchcategories";a:1:{s:5:"value";s:11:"Categories:";}s:19:"sSearchmanufacturer";a:1:{s:5:"value";s:13:"Manufacturer:";}s:21:"sSearchshowallresults";a:1:{s:5:"value";s:16:"Show all results";}s:21:"sSearchnosearchengine";a:1:{s:5:"value";s:24:"No search engine support";}s:27:"sSupportfilloutallredfields";a:1:{s:5:"value";s:38:"Please fill out all red marked fields.";}s:12:"sArticlesend";a:1:{s:5:"value";s:4:"Send";}s:14:"sContact_right";a:1:{s:5:"value";s:72:"<strong>Demoshop<br />\n</strong><br />\nAdd your contact information here";}s:12:"sBankContact";a:1:{s:5:"value";s:71:"<strong>\nOur bank:\n</strong>\nVolksbank Musterstadt\nBIN:\nAccount number:";}s:11:"sArticleof1";a:1:{s:5:"value";s:2:"of";}s:25:"sBasketshippingdifference";a:1:{s:5:"value";s:49:"Order #1 #2 to get the whole order shipping free!";}s:19:"sRegister_advantage";a:1:{s:5:"value";s:218:"<h2>My advantages</h2>\n<ul>\n<li>faster shopping</li>\n<li>Save your user data and settings</li>\n<li>Have a look at your orders including shipping information</li>\n<li>Administrate your newsletter subscription</li>\n</ul>";}s:20:"sArticlePricePerUnit";a:1:{s:5:"value";s:14:"Price per unit";}s:18:"sArticleLastViewed";a:1:{s:5:"value";s:11:"Last viewed";}s:16:"sBasketLessStock";a:1:{s:5:"value";s:57:"Unfortunately there are not enough articles left in stock";}s:20:"sBasketLessStockRest";a:1:{s:5:"value";s:76:"Unfortunately there are not enough articles left in stock (x of y selctable)";}s:24:"sBasketPremiumDifference";a:1:{s:5:"value";s:36:"You almost reached this premium! Add";}s:19:"sAGBTextPaymentform";a:1:{s:5:"value";s:42:"I read the general terms and conditions...";}s:14:"sBasketInquiry";a:1:{s:5:"value";s:19:"Solicit a quotation";}s:21:"sArticleCompareDetail";a:1:{s:5:"value";s:15:"Compare article";}}', 1, 'en'),
(60, 'config_mails', 'a:2:{s:7:"subject";s:22:"Order shipped in parts";s:7:"content";s:334:"Dear{if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d-%m-%Y"} has changed. The new status is as follows: {$sOrder.status_description}.";}', 23, '2'),
(61, 'config_mails', 'a:4:{s:8:"fromMail";s:16:"{$sConfig.sMAIL}";s:8:"fromName";s:20:"{$sConfig.sSHOPNAME}";s:7:"subject";s:38:"Your order with {config name=shopName}";s:7:"content";s:334:"Dear{if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d-%m-%Y"} has changed. The new status is as follows: {$sOrder.status_description}.";}', 14, '2'),
(62, 'config_mails', 'a:2:{s:7:"subject";s:38:"Your order with {config name=shopName}";s:7:"content";s:553:"Hello {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nThe status of your order {$sOrder.ordernumber} has changed!\nThe current status of your order is as follows: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\n \nBest regards,\nYour team of {config name=shopName}";}', 30, '2'),
(63, 'config_mails', 'a:2:{s:7:"subject";s:36:"Your order at {config name=shopName}";s:7:"content";s:332:"Dear{if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number{$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d-%m-%Y"} has changed. The new status is as follows {$sOrder.status_description}.";}', 15, '2'),
(64, 'config_mails', 'a:2:{s:7:"subject";s:38:"Your order with {config name=shopName}";s:7:"content";s:551:"Hello {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nThe order status of your order {$sOrder.ordernumber} has changed!\nThe order now has the following status: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\n \nBest regards,\nYour team of {config name=shopName}";}', 33, '2'),
(65, 'config_mails', 'a:2:{s:7:"subject";s:13:"Status change";s:7:"content";s:923:"Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"} Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nThe status of your order {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d.%m.%Y"} \nhas changed. The new status is as follows: "{$sOrder.status_description}".\n \n \nInformation on your order:\n================================== \n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:3} {$details.articleordernumber|fill:10:" ":"..."} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:"%.2f"} {$sConfig.sCURRENCY}\n{/foreach}\n \nShipping costs: {$sOrder.invoice_shipping} {$sConfig.sCURRENCY}\nNet total: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sConfig.sCURRENCY}\nTotal amount incl. VAT: {$sOrder.invoice_amount|string_format:"%.2f"} {$sConfig.sCURRENCY}\n \nBest regards,\nYour team of {config name=shopName}\n\n";}', 31, '2'),
(66, 'config_mails', 'a:2:{s:7:"subject";s:38:"Your order with {config name=shopName}";s:7:"content";s:552:"Hello {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nThe order status of your order {$sOrder.ordernumber} has changed!\nYour order now has the following status: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\n \nBest regards,\nYour team of {config name=shopName}";}', 34, '2');
INSERT INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`) VALUES
(67, 'config_mails', 'a:2:{s:7:"subject";s:38:"Your order with {config name=shopName}";s:7:"content";s:389:"Dear{if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} \n{$sUser.billing_firstname} {$sUser.billing_lastname},\n \nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d.%m.%Y"} \nhas changed. The new status is as follows: {$sOrder.status_description}.\n \nBest regards,\nYour team of {config name=shopName}";}', 29, '2'),
(68, 'config_mails', 'a:2:{s:7:"subject";s:18:"Your voucher order";s:7:"content";s:231:"Hello {$sUser.billing_firstname}{$sUser.billing_lastname},\n \nThank you for your order. (Number: {$sOrder.ordernumber}).\n\nPlease find your voucher code attached to this e-mail.\n\n{$EventResult.code}\n\nBest regards,\n\nYour Shopware team";}', 47, '2'),
(69, 'config_mails', 'a:2:{s:7:"subject";s:55:"Voucher order - No sufficient number of codes available";s:7:"content";s:201:"Hello,\n\nThere''s no sufficient number of codes available for the order {$Ordernumber}! \n\nPlease check if a code has been assigned to this order and, if not, send the customer a voucher code manually.  \n";}', 48, '2'),
(70, 'config_mails', 'a:3:{s:7:"subject";s:37:"Your registration has been successful";s:7:"content";s:293:"Hello {salutation} {firstname} {lastname},\n \nThank you for your registration with our Shop.\n \nYou will gain access via the e-mail address {sMAIL}\nand the password you have chosen.\n \nYou can have your password sent to you by e-mail anytime. \n \nBest regards\n \nYour team of {config name=shopName}";s:11:"contentHtml";s:356:"<div>\nHello {salutation} {firstname} {lastname},<br/><br/>\n \nThank you for your registration with our Shop.<br/><br/>\n \nYou will gain access via the e-mail address {sMAIL} and the password you have chosen.<br/><br/>\n \nYou can have your password sent to you by e-mail anytime. <br/><br/>\n \nBest regards<br/><br/>\n \nYour team of {config name=shopName}\n</div>";}', 1, '2'),
(71, 'config_mails', 'a:3:{s:7:"subject";s:28:"Your order with the demoshop";s:7:"content";s:1802:"Hello {$billingaddress.firstname} {$billingaddress.lastname},\n \nThank you for your order at {config name=shopName} (Nummer: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.\nInformation on your order:\n \nPos. Art.No.              Quantities         Price        Total\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\n{$details.articlename|wordwrap:49|indent:5}\n{/foreach}\n \nShipping costs: {$sShippingCosts}\nTotal net: {$sAmountNet}\n{if !$sNet}\nTotal gross: {$sAmount}\n{/if}\n \nSelected payment type: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == "debit"}\nYour bank connection:\nAccount number: {$sPaymentTable.account}\nBIN:{$sPaymentTable.bankcode}\nWe will withdraw the money from your bank account within the next days.\n{/if}\n{if $additional.payment.name == "prepayment"}\n \nOur bank connection:\nAccount: ###\nBIN: ###\n{/if}\n \n{if $sComment}\nYour comment:\n{$sComment}\n{/if}\n \nBilling address:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street} {$billingaddress.streetnumber}\n{$billingaddress.zipcode} {$billingaddress.city}\n{$billingaddress.phone}\n{$additional.country.countryname}\n \nShipping address:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street} {$shippingaddress.streetnumber}\n{$shippingaddress.zipcode} {$shippingaddress.city}\n{$additional.country.countryname}\n \n{if $billingaddress.ustid}\nYour VAT-ID: {$billingaddress.ustid}\nIn case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax. \n{/if}\n ";s:11:"contentHtml";s:3556:"<div style="font-family:arial; font-size:12px;">\n \n<p>Hello {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\n \nThank you for your order with {config name=shopName} (Nummer: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.\n<br/>\n<br/>\n<strong>Information on your order:</strong></p>\n  <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">\n    <tr>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art.No.</strong></td>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Quantities</strong></td>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Price</strong></td>\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Total</strong></td>\n    </tr>\n \n    {foreach item=details key=position from=$sOrderDetails}\n    <tr>\n      <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.1}<img src="{$details.image.src.1}" alt="{$details.articlename}" />{else} {/if}</td>\n      <td>{$position+1|fill:4} </td>\n      <td>{$details.ordernumber|fill:20}</td>\n      <td>{$details.quantity|fill:6}</td>\n      <td>{$details.price|padding:8}{$sCurrency}</td>\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\n    </tr>\n    <tr>\n      <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>\n    </tr>\n    {/foreach}\n \n  </table>\n \n<p>\n  <br/>\n  <br/>\n    Shipping costs:: {$sShippingCosts}<br/>\n    Total net: {$sAmountNet}<br/>\n    {if !$sNet}\n    Total gross: {$sAmount}<br/>\n    {/if}\n  <br/>\n  <br/>\n    <strong>Selected payment type:</strong> {$additional.payment.description}<br/>\n    {$additional.payment.additionaldescription}\n    {if $additional.payment.name == "debit"}\n    Your bank connection:<br/>\n    Account number: {$sPaymentTable.account}<br/>\n    BIN:{$sPaymentTable.bankcode}<br/>\n    We will withdraw the money from your bank account within the next days.<br/>\n    {/if}\n  <br/>\n  <br/>\n    {if $additional.payment.name == "prepayment"}\n    Our bank connection:<br/>\n    {config name=bankAccount}\n    {/if} \n  <br/>\n  <br/>\n    <strong>Selected dispatch:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\n</p>\n<p>\n  {if $sComment}\n    <strong>Your comment:</strong><br/>\n    {$sComment}<br/>\n  {/if} \n  <br/>\n  <br/>\n    <strong>Billing address:</strong><br/>\n    {$billingaddress.company}<br/>\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\n    {$billingaddress.street} {$billingaddress.streetnumber}<br/>\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\n    {$billingaddress.phone}<br/>\n    {$additional.country.countryname}<br/>\n  <br/>\n  <br/>\n    <strong>Shipping address:</strong><br/>\n    {$shippingaddress.company}<br/>\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\n    {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\n    {$additional.countryShipping.countryname}<br/>\n  <br/>\n    {if $billingaddress.ustid}\n    Your VAT-ID: {$billingaddress.ustid}<br/>\n    In case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax. \n    {/if}\n  <br/>\n  <br/>\n\n    Your Team of {config name=shopName}<br/>\n</p>\n</div>";}', 2, '2'),
(72, 'config_mails', 'a:2:{s:7:"subject";s:33:"{sName} recommends you {sArticle}";s:7:"content";s:189:"Hello,\n\n{sName} has found an interesting product for you on {sShop} that you should have a look at:\n\n{sArticle}\n{sLink}\n\n{sComment}\n\nBest regards and see you next time\n\nYour contact details";}', 3, '2'),
(73, 'config_mails', 'a:2:{s:7:"subject";s:46:"Forgot password - Your access data for {sShop}";s:7:"content";s:127:"Hello,\n\nYour access data for {sShopURL} is as follows:\nUser: {sMail}\nPassword: {sPassword}\n\nBest regards\n\n{config name=address}";}', 4, '2'),
(74, 'config_mails', 'a:2:{s:7:"subject";s:53:"Attention - no free serial numbers for {sArticleName}";s:7:"content";s:269:"Hello,\n\nThere is no additional free serial numbers available for the article {sArticleName}. Please provide new serial numbers immediately or deactivate the article. Please assign a serial number to the customer {sMail} manually.\n\nBest regards,\n\n{config name=shopName}\n";}', 5, '2'),
(75, 'config_mails', 'a:2:{s:7:"subject";s:12:"Your voucher";s:7:"content";s:268:"Hello {customer},\n\n{user} has followed your recommendation and just ordered at {config name=shopName}.\nThis is why we give you a X € voucher, which you can redeem with your next order.\n			\nYour voucher code is as follows: XXX\n			\nBest regards,\n{config name=shopName}";}', 7, '2'),
(76, 'config_mails', 'a:2:{s:7:"subject";s:37:"Your trader account has been unlocked";s:7:"content";s:184:"Hello,\n\nYour trader account {config name=shopName} has been unlocked.\n  \nFrom now on, we will charge you the net purchase price. \n  \nBest regards\n  \nYour team of {config name=shopName}";}', 12, '2'),
(77, 'config_mails', 'a:2:{s:7:"subject";s:40:"Your trader acount has not been accepted";s:7:"content";s:307:"Dear customer,\n\nThank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a trader. \n\nIn case of further questions please do not heitate to contact us via telephone, fax or e-mail. \n\nBest regards\n\nYour Team of {config name=shopName}";}', 13, '2'),
(78, 'config_mails', 'a:2:{s:7:"subject";s:69:"Your aborted order process - Send us your feedback and get a voucher!";s:7:"content";s:378:"Dear customer,\n \nYou have recently aborted an order process on Demoshop.de - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.\n \nPlease tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher. \n \nThank you for your feedback";}', 19, '2'),
(79, 'config_mails', 'a:2:{s:7:"subject";s:50:"Your aborted order process - Voucher code enclosed";s:7:"content";s:351:"Dear customer,\n \nYou have recently aborted an order process on Demoshop.de - today, we would like to give you a 5,00 Euro-voucher - and therefore make it easier for you to decide for an order with Demoshop.de.\n \nYour voucher is valid for two months and can be redeemed by entering the code "{$sVouchercode}".\n\nWe would be pleased to accept your order!";}', 20, '2'),
(80, 'config_mails', 'a:2:{s:7:"subject";s:40:"Happy Birthday from {$sConfig.sSHOPNAME}";s:7:"content";s:174:"Hello {if $sUser.salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.firstname} {$sUser.lastname},\n\nBest regards\nYour team of {$sConfig.sSHOPNAME}";}', 39, '2'),
(81, 'config_mails', 'a:2:{s:7:"subject";s:83:"Stock level of {$sData.count} article{if $sData.count>1}s{/if} under minimum stock ";s:7:"content";s:260:"Hello,\nThe following articles have undershot the minimum stock:\nOrder number Name of article Stock/Minimum stock \n{foreach from=$sJob.articles item=sArticle key=key}\n{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin} \n{/foreach}\n";}', 40, '2'),
(82, 'config_mails', 'a:2:{s:7:"subject";s:42:"Thank you for your newsletter subscription";s:7:"content";s:78:"Hello,\n\nThank you for your newsletter subscription at {config name=shopName}\n\n";}', 41, '2'),
(83, 'config_mails', 'a:2:{s:7:"subject";s:43:"Please confirm your newsletter subscription";s:7:"content";s:208:"Hello, \n\nThank you for signing up for our regularly published newsletter. \n\nPlease confirm your subscription by clicking the following link: {$sConfirmLink} \n\nBest regards\n\nYour Team of {config name=shopName}";}', 42, '2'),
(84, 'config_mails', 'a:2:{s:7:"subject";s:38:"Please confirm your article evaluation";s:7:"content";s:164:"Hello, \n\nThank you for evaluating the article{$sArticle.articleName}. \n\nPlease confirm the evaluation by clicking the following link: {$sConfirmLink} \n\nBest regards";}', 43, '2'),
(85, 'config_mails', 'a:2:{s:7:"subject";s:31:"Your article is available again";s:7:"content";s:148:"Hello, \n\nYour article with the order number {$sOrdernumber} is available again. \n\n{$sArticleLink} \n\nBest regards\nYour Team of {config name=shopName}";}', 44, '2'),
(86, 'config_mails', 'a:2:{s:7:"subject";s:39:"Please confirm your e-mail notification";s:7:"content";s:240:"Hello, \n\nThank you for signing up for the automatical e-Mail notification for the article {$sArticleName}. \nPlease confirm the notification by clicking the following link:\n\n{$sConfirmLink} \n\nBest regards\n\nYour Team of {config name=shopName}";}', 45, '2'),
(87, 'config_mails', 'a:2:{s:7:"subject";s:16:"Evaluate article";s:7:"content";s:948:"<p>Hello {if $sUser.salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n</p>\nYou have recently purchased articles from {config name=shopName}. We would be pleased if you could evaluate these items. Doing so, you can help us improve our services, and you have the opportunity to tell other customers your opinion. \nBy the way: You do not necessarily have to comment on the articles you have bought. You can select the ones you like best. We would welcome any feedback that you have. \nHere you can find the links to the evaluations of your purchased articles.\n<p>\n</p>\n<table>\n {foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n <tr>\n  <td>{$sArticle.articleordernumber}</td>\n  <td>{$sArticle.name}</td>\n  <td>\n  <a href="{$sArticle.link}">link</a>\n  </td>\n </tr>\n{/if}\n {/foreach}\n</table>\n\n<p>\nBest regards,<br />\nYour team of {config name=shopName}\n</p>";}', 46, '2');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_units`
--

DROP TABLE IF EXISTS `s_core_units`;
CREATE TABLE IF NOT EXISTS `s_core_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_core_units`
--

INSERT INTO `s_core_units` (`id`, `unit`, `description`) VALUES
(1, 'l', 'Liter'),
(2, 'g', 'Gramm'),
(5, 'lfm', 'lfm'),
(6, 'kg', 'Kilogramm'),
(8, 'Paket(e)', 'Paket(e)'),
(9, 'Stck.', 'Stück');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_widgets`
--

DROP TABLE IF EXISTS `s_core_widgets`;
CREATE TABLE IF NOT EXISTS `s_core_widgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `s_core_widgets`
--

INSERT INTO `s_core_widgets` (`id`, `name`, `label`) VALUES
(1, 'swag-sales-widget', 'Umsatz Heute und Gestern'),
(2, 'swag-upload-widget', 'Drag and Drop Upload'),
(3, 'swag-visitors-customers-widget', 'Besucher online'),
(4, 'swag-last-orders-widget', 'Letzte Bestellungen'),
(5, 'swag-notice-widget', 'Notizzettel'),
(6, 'swag-merchant-widget', 'Händlerfreischaltung');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_widget_views`
--

DROP TABLE IF EXISTS `s_core_widget_views`;
CREATE TABLE IF NOT EXISTS `s_core_widget_views` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column` int(11) unsigned NOT NULL,
  `position` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `widget_id` (`widget_id`,`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_crontab`
--

DROP TABLE IF EXISTS `s_crontab`;
CREATE TABLE IF NOT EXISTS `s_crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `elementID` int(11) DEFAULT NULL,
  `data` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `next` datetime DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `interval` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  `end` datetime DEFAULT NULL,
  `inform_template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `inform_mail` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action` (`action`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `s_crontab`
--

INSERT INTO `s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`, `pluginID`) VALUES
(1, 'Geburtstagsgruß', 'birthday', NULL, '', '2010-10-16 23:42:58', '2010-10-16 12:26:44', 86400, 1, '2010-10-16 12:26:44', '', '', NULL),
(2, 'Aufräumen', 'clearing', NULL, '', '2010-10-16 12:34:38', '2010-10-16 12:34:32', 86400, 1, '2010-10-16 12:34:32', '', '', NULL),
(3, 'Lagerbestand Warnung', 'article_stock', NULL, '', '2010-10-16 12:34:33', '2010-10-16 12:34:31', 86400, 1, '2010-10-16 12:34:32', 'sARTICLESTOCK', '{$sConfig.sMAIL}', NULL),
(5, 'Suche', 'search', NULL, '', '2010-10-16 12:34:38', '2010-10-16 12:34:32', 86400, 1, '2010-10-16 12:34:32', '', '', NULL),
(6, 'eMail-Benachrichtigung', 'notification', NULL, '', '2010-10-17 00:20:28', '2010-10-16 12:26:44', 86400, 1, '2010-10-16 12:26:44', '', '', NULL),
(7, 'Artikelbewertung per eMail', 'article_comment', NULL, '', '2010-10-16 12:35:18', '2010-10-16 12:34:32', 86400, 1, '2010-10-16 12:34:32', '', '', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_banners`
--

DROP TABLE IF EXISTS `s_emarketing_banners`;
CREATE TABLE IF NOT EXISTS `s_emarketing_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `valid_from` datetime DEFAULT '0000-00-00 00:00:00',
  `valid_to` datetime DEFAULT '0000-00-00 00:00:00',
  `img` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link_target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `categoryID` int(11) NOT NULL DEFAULT '0',
  `extension` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `liveshoppingID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_banners_attributes`
--

DROP TABLE IF EXISTS `s_emarketing_banners_attributes`;
CREATE TABLE IF NOT EXISTS `s_emarketing_banners_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bannerID` (`bannerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_banners_statistics`
--

DROP TABLE IF EXISTS `s_emarketing_banners_statistics`;
CREATE TABLE IF NOT EXISTS `s_emarketing_banners_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bannerID` int(11) NOT NULL,
  `display_date` date NOT NULL,
  `clicks` int(11) NOT NULL,
  `views` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `display_date` (`bannerID`,`display_date`),
  KEY `bannerID` (`bannerID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_lastarticles`
--

DROP TABLE IF EXISTS `s_emarketing_lastarticles`;
CREATE TABLE IF NOT EXISTS `s_emarketing_lastarticles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(120) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) unsigned NOT NULL,
  `sessionID` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`sessionID`,`shopID`),
  KEY `userID` (`userID`),
  KEY `time` (`time`),
  KEY `sessionID` (`sessionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_partner`
--

DROP TABLE IF EXISTS `s_emarketing_partner`;
CREATE TABLE IF NOT EXISTS `s_emarketing_partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `contact` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `streetnumber` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotions`
--

DROP TABLE IF EXISTS `s_emarketing_promotions`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `mode` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link_target` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `valid_from` date NOT NULL DEFAULT '0000-00-00',
  `valid_to` date NOT NULL DEFAULT '0000-00-00',
  `position` int(11) NOT NULL DEFAULT '0',
  `img` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `liveshoppingID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_articles`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_articles`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_banner`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_banner`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linkTarget` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_containers`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_containers`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `container` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_html`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_html`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_links`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_links`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_main`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_main`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `positionGroup` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `datum` date NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `linktarget` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_positions`
--

DROP TABLE IF EXISTS `s_emarketing_promotion_positions`;
CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `containerID` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_referer`
--

DROP TABLE IF EXISTS `s_emarketing_referer`;
CREATE TABLE IF NOT EXISTS `s_emarketing_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_tellafriend`
--

DROP TABLE IF EXISTS `s_emarketing_tellafriend`;
CREATE TABLE IF NOT EXISTS `s_emarketing_tellafriend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `recipient` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `sender` int(11) NOT NULL DEFAULT '0',
  `confirmed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_vouchers`
--

DROP TABLE IF EXISTS `s_emarketing_vouchers`;
CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers` (
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
  `ordercode` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `modus` int(1) NOT NULL DEFAULT '0',
  `percental` int(1) NOT NULL,
  `numorder` int(11) NOT NULL,
  `customergroup` int(11) DEFAULT NULL,
  `restrictarticles` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `strict` int(1) NOT NULL,
  `subshopID` int(1) DEFAULT NULL,
  `taxconfig` varchar(15) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_vouchers_attributes`
--

DROP TABLE IF EXISTS `s_emarketing_vouchers_attributes`;
CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID` (`voucherID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_vouchers_cashed`
--

DROP TABLE IF EXISTS `s_emarketing_vouchers_cashed`;
CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers_cashed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `voucherID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_voucher_codes`
--

DROP TABLE IF EXISTS `s_emarketing_voucher_codes`;
CREATE TABLE IF NOT EXISTS `s_emarketing_voucher_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cashed` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion`
--

DROP TABLE IF EXISTS `s_emotion`;
CREATE TABLE IF NOT EXISTS `s_emotion` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) DEFAULT NULL,
  `cell_height` int(11) NOT NULL,
  `article_height` int(11) NOT NULL,
  `container_width` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `is_landingpage` int(1) NOT NULL,
  `landingpage_block` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `landingpage_teaser` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion_attributes`
--

DROP TABLE IF EXISTS `s_emotion_attributes`;
CREATE TABLE IF NOT EXISTS `s_emotion_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emotionID` (`emotionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion_categories`
--

DROP TABLE IF EXISTS `s_emotion_categories`;
CREATE TABLE IF NOT EXISTS `s_emotion_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotion_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion_element`
--

DROP TABLE IF EXISTS `s_emotion_element`;
CREATE TABLE IF NOT EXISTS `s_emotion_element` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) NOT NULL,
  `componentID` int(11) NOT NULL,
  `start_row` int(11) NOT NULL,
  `start_col` int(11) NOT NULL,
  `end_row` int(11) NOT NULL,
  `end_col` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion_element_value`
--

DROP TABLE IF EXISTS `s_emotion_element_value`;
CREATE TABLE IF NOT EXISTS `s_emotion_element_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) NOT NULL,
  `elementID` int(11) NOT NULL,
  `componentID` int(11) NOT NULL,
  `fieldID` int(11) NOT NULL,
  `value` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `emotionID` (`elementID`),
  KEY `fieldID` (`fieldID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emotion_grid`
--

DROP TABLE IF EXISTS `s_emotion_grid`;
CREATE TABLE IF NOT EXISTS `s_emotion_grid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_emotion_grid`
--

INSERT INTO `s_emotion_grid` (`id`, `name`, `cols`, `rows`, `width`, `height`) VALUES
(1, 'first-grid', 4, 10, 150, 150);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export`
--

DROP TABLE IF EXISTS `s_export`;
CREATE TABLE IF NOT EXISTS `s_export` (
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `s_export`
--

INSERT INTO `s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`) VALUES
(1, 'Google Produktsuche', '2000-01-01 00:00:00', 0, '4ebfa063359a73c356913df45b3fbe7f', 1, 0, '2000-01-01 00:00:00', 3456, 2, '0000-00-00 00:00:00', 'export.txt', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nid{#S#}\ntitel{#S#}\nbeschreibung{#S#}\nlink{#S#}\nbild_url{#S#}\nean{#S#}\ngewicht{#S#}\nmarke{#S#}\nmpn{#S#}\nzustand{#S#}\nprodukttyp{#S#}\npreis{#S#}\nversand{#S#}\nstandort{#S#}\nwährung\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}\n{$sArticle.description_long|strip_tags|html_entity_decode|trim|regex_replace:"#[^\\wöäüÖÄÜß\\.%&-+ ]#i":""|strip|truncate:500:"...":true|htmlentities|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:4}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{if $sArticle.weight}{$sArticle.weight|escape:"number"}{" kg"}{/if}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\nNeu{#S#}\n{$sArticle.articleID|category:" > "|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:"prepayment":"de"},AT::DHL:{$sArticle|@shippingcost:"prepayment":"at"}{#S#}\n{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}', '', 0, 6, 1),
(2, 'Kelkoo', '2000-01-01 00:00:00', 0, 'f2d27fbba2dabc03789f0ac25f82d93f', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'kelkoo.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nurl{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nofferid{#S#}\nimage{#S#}\navailability{#S#}\ndeliverycost\n{/strip}{#L#}', '{strip}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.name|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.image|image:5|escape}{#S#}\n{if $sArticle.instock}001{else}002{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(3, 'billiger.de', '2000-01-01 00:00:00', 0, '9ca7fd14bc772898bf01d9904d72c1ea', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'billiger.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nid{#S#}\nhersteller{#S#}\nmodell_nr{#S#}\nname{#S#}\nkategorie{#S#}\npreis{#S#}\nbeschreibung{#S#}\nbild_klein{#S#}\nbild_gross{#S#}\nlink{#S#}\nlieferzeit{#S#}\nlieferkosten{#S#}\nwaehrung{#S#}\naufbauservice{#S#}\n24_Std_service{#S#}\nEAN{#S#}\nASIN{#S#}\nISBN{#S#}\nPZN{#S#}\nISMN{#S#}\nEPC{#S#}\nVIN{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.articleID|category:">"|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle.image|image:3}|\n{$sArticle.image|image:5}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sCurrency.currency}|\n|\n|\n{$sArticle.attr6|replace:"|":""}|\n|\n|\n|\n|\n|\n|\n{/strip}{#L#}', '', 0, 1, 1),
(4, 'Idealo', '2000-01-01 00:00:00', 0, '2648057f0020fbeb7e69c238036b25e8', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'idealo.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nKategorie|\nHersteller|\nProduktbezeichnung|\nHersteller-Artikelnummer|\nEAN|\nPZN|\nISBN|\nPreis|\nVersandkosten Nachnahme|\nVersandkosten Vorkasse|\nVersandkosten Bankeinzug|\nDeeplink|\nLieferzeit|\nArtikelnummer|\nLink Produktbild|\nProdukt Kurztext|\n{/strip}{#L#}', '{strip}\n{$sArticle.articleID|category:">"|escape}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|replace:"|":""}|\n{$sArticle.suppliernumber|replace:"|":""}|\n{$sArticle.attr6|escape}|\n|\n|\n{$sArticle.price|escape:"number"}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.ordernumber|escape}|\n{$sArticle.image|image:5}{#S#}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{/strip}{#L#}', '', 0, 1, 1),
(5, 'Yatego', '2000-01-01 00:00:00', 0, '75838aee39eab65375b5241544035f42', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'yatego.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nforeign_id{#S#}\narticle_nr{#S#}\ntitle{#S#}\ncategories{#S#}\nlong_desc{#S#}\npicture{#S#}\nurl{#S#}\ndelivery_surcharge{#S#}\nprice\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(6, 'schottenland.de', '2000-01-01 00:00:00', 0, 'ad16704bf9e58f1f66f99cca7864e63d', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'schottenland.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nHersteller|\nProduktbezeichnung|\nProduktbeschreibung|\nPreis|\nVerfügbarkeit|\nEAN|\nHersteller AN|\nDeeplink|\nArtikelnummer|\nDAN_Ingram|\nVersandkosten Nachnahme|\nVersandkosten Vorkasse|\nVersandkosten Kreditkarte|\nVersandkosten Bankeinzug\n{/strip}{#L#}', '{strip}\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.suppliernumber|replace:"|":""}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.ordernumber|replace:"|":""}|\n|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"credituos":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n{/strip}{#L#}', '', 0, 1, 1),
(7, 'guenstiger.de', '2000-01-01 00:00:00', 0, '5428e68f168eae36c3882b4cf29730bb', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'guenstiger.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|replace:"|":""}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.image|image:2}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.weight|replace:"|":""}\n{/strip}{#L#}', '', 0, 1, 1),
(8, 'geizhals.at', '2000-01-01 00:00:00', 0, '0102715b70fa7d60d61c15c8e025824a', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'geizhals.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nID{#S#}\nHersteller{#S#}\nArtikelbezeichnung{#S#}\nKategorie{#S#}\nBeschreibungsfeld{#S#}\nBild{#S#}\nUrl{#S#}\nLagerstandl{#S#}\nVersandkosten{#S#}\nVersandkostenNachname{#S#}\nPreis{#S#}\nEAN{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(9, 'Ciao', '2000-01-01 00:00:00', 0, 'b8728935bc62480971c0dfdf74eabf6f', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'ciao.csv', 1, NULL, 1, 1, '', NULL, 0, 1, 0, 0, 0, '', '{strip}\nOffer ID{#S#}\nBrand{#S#}\nProduct Name{#S#}\nCategory{#S#}\nDescription{#S#}\nImage URL{#S#}\nProduct URL{#S#}\nDelivery{#S#}\nShippingCost{#S#}\nPrice{#S#}\nProduct ID{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(10, 'Pangora', '2000-01-01 00:00:00', 0, '162a610b4a85c13fd448f9f5e2290fd5', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'pangora.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\noffer-id{#S#}\nmfname{#S#}\nlabel{#S#}\nmerchant-category{#S#}\ndescription{#S#}\nimage-url{#S#}\noffer-url{#S#}\nships-in{#S#}\nrelease-date{#S#}\ndelivery-charge{#S#}\nprices	old-prices{#S#}\nproduct-id{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.releasedate|escape}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}\n\n', '', 0, 1, 1),
(11, 'Shopping.com', '2000-01-01 00:00:00', 0, 'cb29f40e760f11b9071d081b8ca8039c', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'shopping.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nMPN|\nEAN|\nHersteller|\nProduktname|\nProduktbeschreibung|\nPreis|\nProdukt-URL|\nProduktbild-URL|\nKategorie|\nVerfügbar|\nVerfügbarkeitsdetails|\nVersandkosten\n{/strip}{#L#}', '{strip}\n|\n{$sArticle.attr6}|\n{$sArticle.supplier}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode}|\n{$sArticle.price|escape:"number"}|\n{$sArticle.articleID|link:$sArticle.name}|\n{$sArticle.image|image:4}|\n{$sArticle.articleID|category:">"}|\n{if $sArticle.instock}Ja{else}Nein{/if}|\n{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(12, 'Hitmeister', '2000-01-01 00:00:00', 0, '76de62d0fd5ec76b483aa6529d36ee45', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'hitmeister.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nean{#S#}\ncondition{#S#}\nprice{#S#}\ncomment{#S#}\noffer_id{#S#}\nlocation{#S#}\ncount{#S#}\ndelivery_time{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.attr6|escape}{#S#}\n100{#S#}\n{$sArticle.price*100}{#S#}\n{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{#S#}\n{#S#}\n{if $sArticle.instock}b{else}d{/if}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(13, 'evendi.de', '2000-01-01 00:00:00', 0, '5ac98a759a6f392ea0065a500acf82e6', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'evendi.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nEindeutige Händler-Artikelnummer{#S#}\nPreis in Euro{#S#}\nKategorie{#S#}\nProduktbezeichnung{#S#}\nProduktbeschreibung{#S#}\nLink auf Detailseite{#S#}\nLieferzeit{#S#}\nEAN-Nummer{#S#}\nHersteller-Artikelnummer{#S#}\nLink auf Produktbild{#S#}\nHersteller{#S#}\nVersandVorkasse{#S#}\nVersandNachnahme{#S#}\nVersandLastschrift{#S#}\nVersandKreditkarte{#S#}\nVersandRechnung{#S#}\nVersandPayPal\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{#S#}\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"paypal":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(14, 'affili.net', '2000-01-01 00:00:00', 0, 'bc960c18cbeea9038314d040e7dc92f5', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'affilinet.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nart_number{#S#}\ncategory{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nimg_url{#S#}\ndeeplink1{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.image|image:5|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(15, 'Google Produktsuche XML', '2000-01-01 00:00:00', 0, 'e8eca3b3bbbad77afddb67b8138900e1', 1, 0, '2000-01-01 00:00:00', 0, 3, '2008-09-27 09:52:17', 'export.xml', 2, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '<?xml version="1.0" encoding="UTF-8" ?>\n\n<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n	<atom:link href="http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}" rel="self" type="application/rss+xml" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>test</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>', '<item> \n	<title>{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name|escape}</link>\n	<description>{$sArticle.description_long|strip_tags|regex_replace:"/[^wöäüÖÄÜß .?!,&:%;-\\"'']/i":""|trim|truncate:900:"..."|escape}</description>\n	<category>{$sArticle.articleID|category:" > "|escape}</category>\n	{if $sArticle.changed}<pubDate>{$sArticle.changed|date_format:"%a, %d %b %Y %T %Z"}</pubDate>{/if}\n	<g:bild_url>{$sArticle.image|image:4}</g:bild_url>\n{*<g:verfallsdatum>2006-12-20</g:verfallsdatum>*}\n	<g:preis>{$sArticle.price|format:"number"}</g:preis>\n{*<g:preisart>ab</g:preisart>*}\n{*	<g:währung>{$sCurrency.currency}</g:währung>*}\n{*	<g:zahlungsmethode>Barzahlung;Scheck;Visa;MasterCard;AmericanExpress;Lastschrift</g:zahlungsmethode>*}\n{*<g:menge>20</g:menge>*}\n	<g:marke>{$sArticle.supplier|escape}</g:marke>\n	<g:ean>{$sArticle.attr6|escape}</g:ean>\n{*<g:hersteller>{$sArticle.supplier|escape}</g:hersteller>*}\n{*<g:hersteller_kennung>834</g:hersteller_kennung>*}\n{*<g:speicher>512</g:speicher>*}\n{*<g:prozessorgeschwindigkeit>2</g:prozessorgeschwindigkeit>*}\n	<g:modellnummer>{$sArticle.suppliernumber|escape}</g:modellnummer>\n{*<g:größe>14x14x3</g:größe>*}\n	<g:gewicht>2</g:gewicht>\n	<g:zustand>neu</g:zustand>\n{*<g:farbe>schwarz</g:farbe>*}\n	<g:produkttyp>{$sArticle.articleID|category:"/"|escape}</g:produkttyp>\n</item>', '</channel>\n</rss>', 0, 1, 1),
(16, 'preissuchmaschine.de', '2000-01-01 00:00:00', 0, '67fbbab544165d9d4e5352f9a12054a0', 1, 0, '2000-01-01 00:00:00', 0, 1, '0000-00-00 00:00:00', 'preissuchmaschine.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|replace:"|":""}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.image|image:2}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.weight|replace:"|":""}\n{/strip}{#L#}', '', 0, 1, 1),
(17, 'RSS Feed-Template', '2000-01-01 00:00:00', 0, '3a6ff2a4f921a10d33d9b9ec25529a5d', 1, 0, '2000-01-01 00:00:00', 0, 3, '0000-00-00 00:00:00', 'export.xml', 2, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '<?xml version="1.0" encoding="UTF-8" ?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n	<atom:link href="http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}" rel="self" type="application/rss+xml" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>Shopbeschreibung ...</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>{#L#}', '<item> \n	<title>{$sArticle.name|strip_tags|htmlspecialchars_decode|strip|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name}</link>\n	<description>{if $sArticle.image}\n		<a href="{$sArticle.articleID|link:$sArticle.name}" style="border:0 none;">\n			<img src="{$sArticle.image|image:3}" align="right" style="padding: 0pt 0pt 12px 12px; float: right;" />\n		</a>\n{/if}\n		{$sArticle.description_long|strip_tags|regex_replace:"/[^\\wöäüÖÄÜß .?!,&:%;\\-\\"'']/i":""|trim|truncate:900:"..."|escape}\n	</description>\n	<category>{$sArticle.articleID|category:">"|htmlspecialchars_decode|escape}</category>\n{if $sArticle.changed} 	{assign var="sArticleChanged" value=$sArticle.changed|strtotime}<pubDate>{"r"|date:$sArticleChanged}</pubDate>{"rn"}{/if}\n</item>{#L#}', '</channel>\n</rss>', 0, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_articles`
--

DROP TABLE IF EXISTS `s_export_articles`;
CREATE TABLE IF NOT EXISTS `s_export_articles` (
  `feedID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`articleID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_attributes`
--

DROP TABLE IF EXISTS `s_export_attributes`;
CREATE TABLE IF NOT EXISTS `s_export_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exportID` (`exportID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_categories`
--

DROP TABLE IF EXISTS `s_export_categories`;
CREATE TABLE IF NOT EXISTS `s_export_categories` (
  `feedID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_suppliers`
--

DROP TABLE IF EXISTS `s_export_suppliers`;
CREATE TABLE IF NOT EXISTS `s_export_suppliers` (
  `feedID` int(11) NOT NULL,
  `supplierID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`supplierID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter`
--

DROP TABLE IF EXISTS `s_filter`;
CREATE TABLE IF NOT EXISTS `s_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  `comparable` int(1) NOT NULL,
  `sortmode` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_articles`
--

DROP TABLE IF EXISTS `s_filter_articles`;
CREATE TABLE IF NOT EXISTS `s_filter_articles` (
  `articleID` int(10) unsigned NOT NULL,
  `valueID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`articleID`,`valueID`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_attributes`
--

DROP TABLE IF EXISTS `s_filter_attributes`;
CREATE TABLE IF NOT EXISTS `s_filter_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filterID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filterID` (`filterID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_options`
--

DROP TABLE IF EXISTS `s_filter_options`;
CREATE TABLE IF NOT EXISTS `s_filter_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `filterable` int(1) NOT NULL,
  `default` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_relations`
--

DROP TABLE IF EXISTS `s_filter_relations`;
CREATE TABLE IF NOT EXISTS `s_filter_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`optionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_values`
--

DROP TABLE IF EXISTS `s_filter_values`;
CREATE TABLE IF NOT EXISTS `s_filter_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `optionID` (`optionID`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_library_component`
--

DROP TABLE IF EXISTS `s_library_component`;
CREATE TABLE IF NOT EXISTS `s_library_component` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `x_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `convert_function` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cls` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `s_library_component`
--

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
(2, 'HTML-Element', '', NULL, '', 'component_html', 'html-text-element', NULL),
(3, 'Banner', 'emotion-components-banner', 'getBannerMappingLinks', '', 'component_banner', 'banner-element', NULL),
(4, 'Artikel', 'emotion-components-article', 'getArticle', '', 'component_article', 'article-element', NULL),
(5, 'Kategorie-Teaser', 'emotion-components-category-teaser', 'getCategoryTeaser', '', 'component_category_teaser', 'category-teaser-element', NULL),
(6, 'Blog-Artikel', 'emotion-components-blog', 'getBlogEntry', '', 'component_blog', 'blog-element', NULL),
(7, 'Banner-Slider', 'emotion-components-banner-slider', 'getBannerSlider', '', 'component_banner_slider', 'banner-slider-element', NULL),
(8, 'Youtube-Video', '', NULL, '', 'component_youtube', 'youtube-element', NULL),
(9, 'iFrame-Element', '', NULL, '', 'component_iframe', 'iframe-element', NULL),
(10, 'Hersteller-Slider', 'emotion-components-manufacturer-slider', 'getManufacturerSlider', '', 'component_manufacturer_slider', 'manufacturer-slider-element', NULL),
(11, 'Artikel-Slider', 'emotion-components-article-slider', 'getArticleSlider', '', 'component_article_slider', 'article-slider-element', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_library_component_field`
--

DROP TABLE IF EXISTS `s_library_component_field`;
CREATE TABLE IF NOT EXISTS `s_library_component_field` (
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
  `default_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allow_blank` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=48 ;

--
-- Daten für Tabelle `s_library_component_field`
--

INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`) VALUES
(3, 3, 'file', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', 0),
(4, 2, 'text', 'tinymce', '', 'Text', 'Anzuzeigender Text', 'HTML-Text', 'Geben Sie hier den Text ein der im Element angezeigt werden soll.', '', '', '', '', 0),
(5, 4, 'article', 'emotion-components-fields-article', '', 'Artikelsuche', 'Der anzuzeigende Artikel', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', '', '', '', '', 0),
(6, 2, 'cms_title', 'textfield', '', 'Titel', '', '', '', '', '', '', '', 0),
(7, 3, 'bannerMapping', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
(8, 4, 'article_type', 'emotion-components-fields-article-type', '', 'Typ des Artikels', '', '', '', '', '', '', '', 0),
(9, 5, 'image_type', 'emotion-components-fields-category-image-type', '', 'Typ des Bildes', '', '', '', '', '', '', '', 0),
(10, 5, 'image', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', 0),
(11, 5, 'category_selection', 'emotion-components-fields-category-selection', '', '', '', '', '', '', '', '', '', 1),
(12, 6, 'entry_amount', 'numberfield', '', 'Anzahl', '', '', '', '', '', '', '', 0),
(13, 7, 'banner_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 0),
(15, 7, 'banner_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', 0),
(16, 7, 'banner_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', 0),
(17, 7, 'banner_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', 0),
(18, 7, 'banner_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', 0),
(19, 7, 'banner_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', 0),
(20, 7, 'banner_slider', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
(22, 8, 'video_id', 'textfield', '', 'Youtube-Video ID', '', '', '', '', '', '', '', 0),
(23, 8, 'video_hd', 'checkbox', '', 'HD-Video verwenden', '', '', '', '', '', '', '', 0),
(24, 9, 'iframe_url', 'textfield', '', 'URL', '', '', '', '', '', '', '', 0),
(25, 10, 'manufacturer_type', 'emotion-components-fields-manufacturer-type', '', '', '', '', '', '', '', '', '', 0),
(26, 10, 'manufacturer_category', 'emotion-components-fields-category-selection', '', '', '', '', '', '', '', '', '', 1),
(27, 10, 'selected_manufacturers', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
(28, 10, 'manufacturer_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 0),
(30, 10, 'manufacturer_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', 0),
(31, 10, 'manufacturer_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', 0),
(32, 10, 'manufacturer_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', 0),
(33, 10, 'manufacturer_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', 0),
(34, 10, 'manufacturer_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', 0),
(36, 11, 'article_slider_type', 'emotion-components-fields-article-slider-type', '', '', '', '', '', '', '', '', '', 0),
(37, 11, 'selected_articles', 'hidden', 'json', '', '', '', '', '', '', '', '', 0),
(38, 11, 'article_slider_max_number', 'numberfield', '', 'max. Anzahl', '', '', '', '', '', '', '', 0),
(39, 11, 'article_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', 0),
(41, 11, 'article_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', 0),
(42, 11, 'article_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', 0),
(43, 11, 'article_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', 0),
(44, 11, 'article_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', 0),
(45, 11, 'article_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', 0),
(47, 3, 'link', 'textfield', '', 'Link', '', '', '', '', '', '', '', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_media`
--

DROP TABLE IF EXISTS `s_media`;
CREATE TABLE IF NOT EXISTS `s_media` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `file_size` int(10) unsigned NOT NULL,
  `userID` int(11) NOT NULL,
  `created` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Album` (`albumID`),
  KEY `path` (`path`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_media_album`
--

DROP TABLE IF EXISTS `s_media_album`;
CREATE TABLE IF NOT EXISTS `s_media_album` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `parentID` int(11) DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_media_album`
--

INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-11, 'Blog', NULL, 3),
(-10, 'Unsortiert', NULL, 7),
(-9, 'Sonstiges', -6, 3),
(-8, 'Musik', -6, 2),
(-7, 'Video', -6, 1),
(-6, 'Dateien', NULL, 6),
(-5, 'Newsletter', NULL, 4),
(-4, 'Aktionen', NULL, 5),
(-3, 'Einkaufswelten', NULL, 3),
(-2, 'Banner', NULL, 1),
(-1, 'Artikel', NULL, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_media_album_settings`
--

DROP TABLE IF EXISTS `s_media_album_settings`;
CREATE TABLE IF NOT EXISTS `s_media_album_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `create_thumbnails` int(11) NOT NULL,
  `thumbnail_size` text COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `albumID` (`albumID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `s_media_album_settings`
--

INSERT INTO `s_media_album_settings` (`id`, `albumID`, `create_thumbnails`, `thumbnail_size`, `icon`) VALUES
(1, -10, 0, '', 'sprite-blue-folder'),
(2, -9, 0, '', 'sprite-blue-folder'),
(3, -8, 0, '', 'sprite-blue-folder'),
(4, -7, 0, '', 'sprite-blue-folder'),
(5, -6, 0, '', 'sprite-blue-folder'),
(6, -5, 0, '', 'sprite-blue-folder'),
(7, -4, 0, '', 'sprite-blue-folder'),
(8, -3, 0, '', 'sprite-blue-folder'),
(9, -2, 0, '', 'sprite-blue-folder'),
(10, -1, 1, '30x30;57x57;105x105;140x140;285x255;720x600', 'sprite-blue-folder'),
(11, -11, 1, '57x57;140x140;285x255;720x600', 'sprite-blue-folder');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_media_association`
--

DROP TABLE IF EXISTS `s_media_association`;
CREATE TABLE IF NOT EXISTS `s_media_association` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) NOT NULL,
  `targetType` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `targetID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `Media` (`mediaID`),
  KEY `Target` (`targetID`,`targetType`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_media_attributes`
--

DROP TABLE IF EXISTS `s_media_attributes`;
CREATE TABLE IF NOT EXISTS `s_media_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mediaID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mediaID` (`mediaID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order`
--

DROP TABLE IF EXISTS `s_order`;
CREATE TABLE IF NOT EXISTS `s_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `invoice_amount` double NOT NULL DEFAULT '0',
  `invoice_amount_net` double NOT NULL,
  `invoice_shipping` double NOT NULL DEFAULT '0',
  `invoice_shipping_net` double NOT NULL,
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
  `partnerID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `temporaryID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `cleareddate` datetime DEFAULT NULL,
  `trackingcode` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `language` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `dispatchID` int(11) NOT NULL,
  `currency` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `currencyFactor` double NOT NULL,
  `subshopID` int(11) NOT NULL,
  `remote_addr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `partnerID` (`partnerID`),
  KEY `userID` (`userID`),
  KEY `ordertime` (`ordertime`),
  KEY `cleared` (`cleared`),
  KEY `status` (`status`),
  KEY `paymentID` (`paymentID`),
  KEY `temporaryID` (`temporaryID`),
  KEY `ordernumber` (`ordernumber`),
  KEY `transactionID` (`transactionID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_attributes`
--

DROP TABLE IF EXISTS `s_order_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_basket`
--

DROP TABLE IF EXISTS `s_order_basket`;
CREATE TABLE IF NOT EXISTS `s_order_basket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
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
  `liveshoppingID` int(11) NOT NULL,
  `bundleID` int(11) unsigned NOT NULL,
  `bundle_join_ordernumber` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessionID` (`sessionID`),
  KEY `articleID` (`articleID`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_basket_attributes`
--

DROP TABLE IF EXISTS `s_order_basket_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_billingaddress`
--

DROP TABLE IF EXISTS `s_order_billingaddress`;
CREATE TABLE IF NOT EXISTS `s_order_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `orderID` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `customernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `streetnumber` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `stateID` int(11) NOT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  KEY `userid` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_billingaddress_attributes`
--

DROP TABLE IF EXISTS `s_order_billingaddress_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_comparisons`
--

DROP TABLE IF EXISTS `s_order_comparisons`;
CREATE TABLE IF NOT EXISTS `s_order_comparisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `sessionID` (`sessionID`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_details`
--

DROP TABLE IF EXISTS `s_order_details`;
CREATE TABLE IF NOT EXISTS `s_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `orderID` (`orderID`),
  KEY `articleID` (`articleID`),
  KEY `ordernumber` (`ordernumber`),
  KEY `articleordernumber` (`articleordernumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_details_attributes`
--

DROP TABLE IF EXISTS `s_order_details_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_documents`
--

DROP TABLE IF EXISTS `s_order_documents`;
CREATE TABLE IF NOT EXISTS `s_order_documents` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `orderID` int(11) unsigned NOT NULL,
  `amount` double NOT NULL,
  `docID` int(11) NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `orderID` (`orderID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_documents_attributes`
--

DROP TABLE IF EXISTS `s_order_documents_attributes`;
CREATE TABLE IF NOT EXISTS `s_order_documents_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentID` (`documentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_esd`
--

DROP TABLE IF EXISTS `s_order_esd`;
CREATE TABLE IF NOT EXISTS `s_order_esd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialID` int(255) NOT NULL DEFAULT '0',
  `esdID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL DEFAULT '0',
  `orderdetailsID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_history`
--

DROP TABLE IF EXISTS `s_order_history`;
CREATE TABLE IF NOT EXISTS `s_order_history` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_notes`
--

DROP TABLE IF EXISTS `s_order_notes`;
CREATE TABLE IF NOT EXISTS `s_order_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sUniqueID` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_number`
--

DROP TABLE IF EXISTS `s_order_number`;
CREATE TABLE IF NOT EXISTS `s_order_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(20) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `desc` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=929 ;

--
-- Daten für Tabelle `s_order_number`
--

INSERT INTO `s_order_number` (`id`, `number`, `name`, `desc`) VALUES
(1, 20001, 'user', 'Kunden'),
(920, 20000, 'invoice', 'Bestellungen'),
(921, 20000, 'doc_1', 'Lieferscheine'),
(922, 20000, 'doc_2', 'Gutschriften'),
(924, 20000, 'doc_0', 'Rechnungen'),
(925, 10000, 'articleordernumber', 'Artikelbestellnummer  '),
(926, 10000, 'sSERVICE1', 'Service - 1'),
(927, 10000, 'sSERVICE2', 'Service - 2'),
(928, 110, 'blogordernumber', 'Blog - ID');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_shippingaddress`
--

DROP TABLE IF EXISTS `s_order_shippingaddress`;
CREATE TABLE IF NOT EXISTS `s_order_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  `orderID` int(11) NOT NULL,
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `streetnumber` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL,
  `stateID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_shippingaddress_attributes`
--

DROP TABLE IF EXISTS `s_order_shippingaddress_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_benchmark_log`
--

DROP TABLE IF EXISTS `s_plugin_benchmark_log`;
CREATE TABLE IF NOT EXISTS `s_plugin_benchmark_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `hash` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `query` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `parameters` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `time` float NOT NULL,
  `ipaddress` varchar(24) COLLATE utf8_unicode_ci NOT NULL,
  `route` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `session` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `datum` (`datum`),
  KEY `session` (`session`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=418 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_recommendations`
--

DROP TABLE IF EXISTS `s_plugin_recommendations`;
CREATE TABLE IF NOT EXISTS `s_plugin_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) NOT NULL,
  `banner_active` int(1) NOT NULL,
  `new_active` int(1) NOT NULL,
  `bought_active` int(1) NOT NULL,
  `supplier_active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID_2` (`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_widgets_notes`
--

DROP TABLE IF EXISTS `s_plugin_widgets_notes`;
CREATE TABLE IF NOT EXISTS `s_plugin_widgets_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch`
--

DROP TABLE IF EXISTS `s_premium_dispatch`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_premium_dispatch`
--

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
(9, 'Standard Versand', 0, '', '', 1, 0, 0, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_attributes`
--

DROP TABLE IF EXISTS `s_premium_dispatch_attributes`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatchID` (`dispatchID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_categories`
--

DROP TABLE IF EXISTS `s_premium_dispatch_categories`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch_categories` (
  `dispatchID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`categoryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_countries`
--

DROP TABLE IF EXISTS `s_premium_dispatch_countries`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch_countries` (
  `dispatchID` int(11) NOT NULL,
  `countryID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`countryID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_premium_dispatch_countries`
--

INSERT INTO `s_premium_dispatch_countries` (`dispatchID`, `countryID`) VALUES
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 7),
(9, 8),
(9, 9),
(9, 10),
(9, 11),
(9, 12),
(9, 13),
(9, 14),
(9, 15),
(9, 16),
(9, 18),
(9, 20),
(9, 21),
(9, 22),
(9, 23),
(9, 24),
(9, 25),
(9, 26),
(9, 27),
(9, 28),
(9, 29),
(9, 30),
(9, 31),
(9, 32),
(9, 33),
(9, 34),
(9, 35),
(9, 36),
(9, 37);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_holidays`
--

DROP TABLE IF EXISTS `s_premium_dispatch_holidays`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch_holidays` (
  `dispatchID` int(11) unsigned NOT NULL,
  `holidayID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`holidayID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_paymentmeans`
--

DROP TABLE IF EXISTS `s_premium_dispatch_paymentmeans`;
CREATE TABLE IF NOT EXISTS `s_premium_dispatch_paymentmeans` (
  `dispatchID` int(11) NOT NULL,
  `paymentID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`paymentID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Daten für Tabelle `s_premium_dispatch_paymentmeans`
--

INSERT INTO `s_premium_dispatch_paymentmeans` (`dispatchID`, `paymentID`) VALUES
(9, 2),
(9, 3),
(9, 4),
(9, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_holidays`
--

DROP TABLE IF EXISTS `s_premium_holidays`;
CREATE TABLE IF NOT EXISTS `s_premium_holidays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `calculation` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=24 ;

--
-- Daten für Tabelle `s_premium_holidays`
--

INSERT INTO `s_premium_holidays` (`id`, `name`, `calculation`, `date`) VALUES
(1, 'Neujahr', 'DATE(''01-01'')', '2011-01-01'),
(2, 'Berchtoldstag', 'DATE(''01/02'')', '2011-01-02'),
(3, 'Heilige drei Könige', 'DATE(''01-06'')', '2011-01-06'),
(4, 'Rosenmontag', 'DATE_SUB(EASTERDATE(), INTERVAL 48 DAY)', '2011-03-07'),
(5, 'Josefstag', 'DATE(''03/19'')', '2011-03-19'),
(6, 'Karfreitag', 'DATE_SUB(EASTERDATE(), INTERVAL 2 DAY)', '2011-04-22'),
(7, 'Ostermontag', 'DATE_ADD(EASTERDATE(), INTERVAL 1 DAY)', '2011-04-25'),
(8, 'Tag der Arbeit', 'DATE(''05/01'')', '2011-05-01'),
(9, 'Christi Himmelfahrt', 'DATE_ADD(EASTERDATE(), INTERVAL 39 DAY)', '2011-06-02'),
(10, 'Pfingstmontag', 'DATE_ADD(EASTERDATE(), INTERVAL 50 DAY)', '2011-06-13'),
(11, 'Fronleichnam', 'DATE_ADD(EASTERDATE(), INTERVAL 60 DAY)', '2011-06-23'),
(12, 'Bundesfeier (Schweiz)', 'DATE(''08-01'')', '2011-08-01'),
(13, 'Mariä Himmelfahrt', 'DATE(''08/15'')', '2011-08-15'),
(14, 'Tag der Deutschen Einheit', 'DATE(''10/03'')', '2011-10-03'),
(15, 'Nationalfeiertag (Österreich)', 'DATE(''10/26'')', '2010-10-26'),
(16, 'Reformationstag', 'DATE(''10/31'')', '2010-10-31'),
(17, 'Allerheiligen', 'DATE(''11/01'')', '2010-11-01'),
(18, 'Buß- und Bettag', 'SUBDATE(DATE(''11-23''), DAYOFWEEK(DATE(''11-23''))+IF(DAYOFWEEK(DATE(''11-23''))>4,-4,3))', '2010-11-17'),
(19, 'Mariä Empfängnis', 'DATE(''12/8'')', '2010-12-08'),
(20, 'Heiligabend', 'DATE(''12/24'')', '2010-12-24'),
(21, '1. Weihnachtstag', 'DATE(''12/25'')', '2010-12-25'),
(22, '2. Weihnachtstag (Stephanstag)', 'DATE(''12/26'')', '2010-12-26'),
(23, 'Sylvester', 'DATE(''12/31'')', '2010-12-31');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_shippingcosts`
--

DROP TABLE IF EXISTS `s_premium_shippingcosts`;
CREATE TABLE IF NOT EXISTS `s_premium_shippingcosts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` decimal(10,3) unsigned NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `factor` decimal(10,2) NOT NULL,
  `dispatchID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from` (`from`,`dispatchID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=236 ;

--
-- Daten für Tabelle `s_premium_shippingcosts`
--

INSERT INTO `s_premium_shippingcosts` (`id`, `from`, `value`, `factor`, `dispatchID`) VALUES
(235, '0.000', '3.90', '0.00', 9);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_fields`
--

DROP TABLE IF EXISTS `s_search_fields`;
CREATE TABLE IF NOT EXISTS `s_search_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `relevance` int(11) NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tableID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`tableID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=9 ;

--
-- Daten für Tabelle `s_search_fields`
--

INSERT INTO `s_search_fields` (`id`, `name`, `relevance`, `field`, `tableID`) VALUES
(1, 'Kategorie-Keywords', 10, 'metakeywords', 2),
(2, 'Kategorie-Überschrift', 70, 'description', 2),
(3, 'Artikel-Name', 400, 'name', 1),
(4, 'Artikel-Keywords', 10, 'keywords', 1),
(5, 'Artikel-Bestellnummer', 50, 'ordernumber', 4),
(6, 'Hersteller-Name', 45, 'name', 3),
(7, 'Artikel-Name Übersetzung', 50, 'name', 5),
(8, 'Artikel-Keywords Übersetzung', 10, 'keywords', 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_index`
--

DROP TABLE IF EXISTS `s_search_index`;
CREATE TABLE IF NOT EXISTS `s_search_index` (
  `keywordID` int(11) NOT NULL,
  `fieldID` int(11) NOT NULL,
  `elementID` int(11) NOT NULL,
  PRIMARY KEY (`keywordID`,`fieldID`,`elementID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_keywords`
--

DROP TABLE IF EXISTS `s_search_keywords`;
CREATE TABLE IF NOT EXISTS `s_search_keywords` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `soundex` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `soundex` (`soundex`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_tables`
--

DROP TABLE IF EXISTS `s_search_tables`;
CREATE TABLE IF NOT EXISTS `s_search_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referenz_table` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `foreign_key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `where` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `s_search_tables`
--

INSERT INTO `s_search_tables` (`id`, `table`, `referenz_table`, `foreign_key`, `where`) VALUES
(1, 's_articles', NULL, NULL, NULL),
(2, 's_categories', 's_articles_categories', 'categoryID', NULL),
(3, 's_articles_supplier', NULL, 'supplierID', NULL),
(4, 's_articles_details', 's_articles_details', 'id', NULL),
(5, 's_articles_translations', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_currentusers`
--

DROP TABLE IF EXISTS `s_statistics_currentusers`;
CREATE TABLE IF NOT EXISTS `s_statistics_currentusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `page` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `time` datetime DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_pool`
--

DROP TABLE IF EXISTS `s_statistics_pool`;
CREATE TABLE IF NOT EXISTS `s_statistics_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_referer`
--

DROP TABLE IF EXISTS `s_statistics_referer`;
CREATE TABLE IF NOT EXISTS `s_statistics_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_search`
--

DROP TABLE IF EXISTS `s_statistics_search`;
CREATE TABLE IF NOT EXISTS `s_statistics_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_visitors`
--

DROP TABLE IF EXISTS `s_statistics_visitors`;
CREATE TABLE IF NOT EXISTS `s_statistics_visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopID` int(11) NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `pageimpressions` int(11) NOT NULL DEFAULT '0',
  `uniquevisits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user`
--

DROP TABLE IF EXISTS `s_user`;
CREATE TABLE IF NOT EXISTS `s_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `accountmode` int(11) NOT NULL,
  `confirmationkey` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `paymentID` int(11) NOT NULL DEFAULT '0',
  `firstlogin` date NOT NULL DEFAULT '0000-00-00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessionID` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
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
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `sessionID` (`sessionID`),
  KEY `firstlogin` (`firstlogin`),
  KEY `lastlogin` (`lastlogin`),
  KEY `pricegroupID` (`pricegroupID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_attributes`
--

DROP TABLE IF EXISTS `s_user_attributes`;
CREATE TABLE IF NOT EXISTS `s_user_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_billingaddress`
--

DROP TABLE IF EXISTS `s_user_billingaddress`;
CREATE TABLE IF NOT EXISTS `s_user_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `customernumber` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `streetnumber` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `phone` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `fax` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `stateID` int(11) NOT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_billingaddress_attributes`
--

DROP TABLE IF EXISTS `s_user_billingaddress_attributes`;
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

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_debit`
--

DROP TABLE IF EXISTS `s_user_debit`;
CREATE TABLE IF NOT EXISTS `s_user_debit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `account` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `bankcode` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `bankname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `bankholder` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_shippingaddress`
--

DROP TABLE IF EXISTS `s_user_shippingaddress`;
CREATE TABLE IF NOT EXISTS `s_user_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `department` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `salutation` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `firstname` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `lastname` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `street` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `streetnumber` varchar(6) COLLATE utf8_unicode_ci NOT NULL,
  `zipcode` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `city` varchar(70) COLLATE utf8_unicode_ci NOT NULL,
  `countryID` int(11) DEFAULT NULL,
  `stateID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_shippingaddress_attributes`
--

DROP TABLE IF EXISTS `s_user_shippingaddress_attributes`;
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2 ;

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `s_articles_attributes`
--
ALTER TABLE `s_articles_attributes`
  ADD CONSTRAINT `s_articles_attributes_ibfk_1` FOREIGN KEY (`articleID`) REFERENCES `s_articles` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
  ADD CONSTRAINT `s_articles_attributes_ibfk_2` FOREIGN KEY (`articledetailsID`) REFERENCES `s_articles_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_downloads_attributes`
--
ALTER TABLE `s_articles_downloads_attributes`
  ADD CONSTRAINT `s_articles_downloads_attributes_ibfk_1` FOREIGN KEY (`downloadID`) REFERENCES `s_articles_downloads` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_esd_attributes`
--
ALTER TABLE `s_articles_esd_attributes`
  ADD CONSTRAINT `s_articles_esd_attributes_ibfk_1` FOREIGN KEY (`esdID`) REFERENCES `s_articles_esd` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_img_attributes`
--
ALTER TABLE `s_articles_img_attributes`
  ADD CONSTRAINT `s_articles_img_attributes_ibfk_1` FOREIGN KEY (`imageID`) REFERENCES `s_articles_img` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_information_attributes`
--
ALTER TABLE `s_articles_information_attributes`
  ADD CONSTRAINT `s_articles_information_attributes_ibfk_1` FOREIGN KEY (`informationID`) REFERENCES `s_articles_information` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_prices_attributes`
--
ALTER TABLE `s_articles_prices_attributes`
  ADD CONSTRAINT `s_articles_prices_attributes_ibfk_1` FOREIGN KEY (`priceID`) REFERENCES `s_articles_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_articles_supplier_attributes`
--
ALTER TABLE `s_articles_supplier_attributes`
  ADD CONSTRAINT `s_articles_supplier_attributes_ibfk_1` FOREIGN KEY (`supplierID`) REFERENCES `s_articles_supplier` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_categories_attributes`
--
ALTER TABLE `s_categories_attributes`
  ADD CONSTRAINT `s_categories_attributes_ibfk_1` FOREIGN KEY (`categoryID`) REFERENCES `s_categories` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_cms_static_attributes`
--
ALTER TABLE `s_cms_static_attributes`
  ADD CONSTRAINT `s_cms_static_attributes_ibfk_1` FOREIGN KEY (`cmsStaticID`) REFERENCES `s_cms_static` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_cms_support_attributes`
--
ALTER TABLE `s_cms_support_attributes`
  ADD CONSTRAINT `s_cms_support_attributes_ibfk_1` FOREIGN KEY (`cmsSupportID`) REFERENCES `s_cms_support` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_auth_attributes`
--
ALTER TABLE `s_core_auth_attributes`
  ADD CONSTRAINT `s_core_auth_attributes_ibfk_1` FOREIGN KEY (`authID`) REFERENCES `s_core_auth` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_config_mails`
--
ALTER TABLE `s_core_config_mails`
  ADD CONSTRAINT `s_core_config_mails_ibfk_1` FOREIGN KEY (`stateId`) REFERENCES `s_core_states` (`id`);

--
-- Constraints der Tabelle `s_core_config_mails_attributes`
--
ALTER TABLE `s_core_config_mails_attributes`
  ADD CONSTRAINT `s_core_config_mails_attributes_ibfk_1` FOREIGN KEY (`mailID`) REFERENCES `s_core_config_mails` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_countries_attributes`
--
ALTER TABLE `s_core_countries_attributes`
  ADD CONSTRAINT `s_core_countries_attributes_ibfk_1` FOREIGN KEY (`countryID`) REFERENCES `s_core_countries` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_countries_states_attributes`
--
ALTER TABLE `s_core_countries_states_attributes`
  ADD CONSTRAINT `s_core_countries_states_attributes_ibfk_1` FOREIGN KEY (`stateID`) REFERENCES `s_core_countries_states` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_customergroups_attributes`
--
ALTER TABLE `s_core_customergroups_attributes`
  ADD CONSTRAINT `s_core_customergroups_attributes_ibfk_1` FOREIGN KEY (`customerGroupID`) REFERENCES `s_core_customergroups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_core_paymentmeans_attributes`
--
ALTER TABLE `s_core_paymentmeans_attributes`
  ADD CONSTRAINT `s_core_paymentmeans_attributes_ibfk_1` FOREIGN KEY (`paymentmeanID`) REFERENCES `s_core_paymentmeans` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_emarketing_banners_attributes`
--
ALTER TABLE `s_emarketing_banners_attributes`
  ADD CONSTRAINT `s_emarketing_banners_attributes_ibfk_1` FOREIGN KEY (`bannerID`) REFERENCES `s_emarketing_banners` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_emarketing_vouchers_attributes`
--
ALTER TABLE `s_emarketing_vouchers_attributes`
  ADD CONSTRAINT `s_emarketing_vouchers_attributes_ibfk_1` FOREIGN KEY (`voucherID`) REFERENCES `s_emarketing_vouchers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_emotion_attributes`
--
ALTER TABLE `s_emotion_attributes`
  ADD CONSTRAINT `s_emotion_attributes_ibfk_1` FOREIGN KEY (`emotionID`) REFERENCES `s_emotion` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_export_attributes`
--
ALTER TABLE `s_export_attributes`
  ADD CONSTRAINT `s_export_attributes_ibfk_1` FOREIGN KEY (`exportID`) REFERENCES `s_export` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_filter_attributes`
--
ALTER TABLE `s_filter_attributes`
  ADD CONSTRAINT `s_filter_attributes_ibfk_1` FOREIGN KEY (`filterID`) REFERENCES `s_filter` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_media_attributes`
--
ALTER TABLE `s_media_attributes`
  ADD CONSTRAINT `s_media_attributes_ibfk_1` FOREIGN KEY (`mediaID`) REFERENCES `s_media` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_attributes`
--
ALTER TABLE `s_order_attributes`
  ADD CONSTRAINT `s_order_attributes_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `s_order` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_basket_attributes`
--
ALTER TABLE `s_order_basket_attributes`
  ADD CONSTRAINT `s_order_basket_attributes_ibfk_2` FOREIGN KEY (`basketID`) REFERENCES `s_order_basket` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_billingaddress_attributes`
--
ALTER TABLE `s_order_billingaddress_attributes`
  ADD CONSTRAINT `s_order_billingaddress_attributes_ibfk_2` FOREIGN KEY (`billingID`) REFERENCES `s_order_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_details_attributes`
--
ALTER TABLE `s_order_details_attributes`
  ADD CONSTRAINT `s_order_details_attributes_ibfk_1` FOREIGN KEY (`detailID`) REFERENCES `s_order_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_documents_attributes`
--
ALTER TABLE `s_order_documents_attributes`
  ADD CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_order_shippingaddress_attributes`
--
ALTER TABLE `s_order_shippingaddress_attributes`
  ADD CONSTRAINT `s_order_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_order_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_premium_dispatch_attributes`
--
ALTER TABLE `s_premium_dispatch_attributes`
  ADD CONSTRAINT `s_premium_dispatch_attributes_ibfk_1` FOREIGN KEY (`dispatchID`) REFERENCES `s_premium_dispatch` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_user_attributes`
--
ALTER TABLE `s_user_attributes`
  ADD CONSTRAINT `s_user_attributes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_user_billingaddress_attributes`
--
ALTER TABLE `s_user_billingaddress_attributes`
  ADD CONSTRAINT `s_user_billingaddress_attributes_ibfk_1` FOREIGN KEY (`billingID`) REFERENCES `s_user_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

--
-- Constraints der Tabelle `s_user_shippingaddress_attributes`
--
ALTER TABLE `s_user_shippingaddress_attributes`
  ADD CONSTRAINT `s_user_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_user_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- Prepares
SET FOREIGN_KEY_CHECKS = 0;

-- 2-fix-delta-table.sql
INSERT IGNORE INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-12, 'Hersteller', NULL, 12);

-- 3-rename-navigation-snippets-detail.sql
UPDATE  `s_core_snippets` SET  `value` =  'Zur Übersicht' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Back to overview' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 2;

-- 4-insert-supplier-album.sql
INSERT IGNORE INTO  `s_media_album_settings` (
    `id` ,
    `albumID` ,
    `create_thumbnails` ,
    `thumbnail_size` ,
    `icon`
)
VALUES (
    NULL ,  '-12',  '0',  '',  'sprite-blue-folder'
);

-- 5-fix-last-article-thumb-size.sql
UPDATE `s_core_config_elements` SET `value` = 'i:2;',
`type` = 'number' WHERE `name` = 'thumb';

-- 6-fix-customer_state_id.sql
ALTER TABLE `s_user_billingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_billingaddress` SET `stateID` = Null WHERE `stateID` = 0;
ALTER TABLE `s_user_shippingaddress` CHANGE `stateID` `stateID` INT( 11 ) NULL DEFAULT NULL;
UPDATE `s_user_shippingaddress` SET `stateID` = Null WHERE `stateID` = 0;

-- 7-add-default-merchant-group.sql
INSERT IGNORE INTO `s_core_customergroups` (`id`, `groupkey`, `description`, `tax`, `taxinput`, `mode`, `discount`, `minimumorder`, `minimumordersurcharge`) VALUES
(2, 'H', 'Händler', 1, 0, 0, 0, 0, 0);

-- 9-add-inquiry-basket-snippet.sql
INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES ('frontend/detail/comment', '1', '1', 'InquiryTextBasket', 'Bitte unterbreiten Sie mir ein Angebot über die nachfolgenden Positionen', NOW(), NOW());

-- 8-fix-partner-form-template.sql
UPDATE `s_cms_support` SET `email_template` = 'Partneranfrage - {$sShopname}
{sVars.firma} moechte Partner Ihres Shops werden!

Firma: {sVars.firma}
Ansprechpartner: {sVars.ansprechpartner}
Straße/Hausnr.: {sVars.strasse}
PLZ / Ort: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Telefon: {sVars.tel}
Fax: {sVars.fax}
Webseite: {sVars.website}

Kommentar:
{sVars.kommentar}

Profil:
{sVars.profil}' WHERE name = 'Partnerformular' AND MD5(s_cms_support.email_template) = 'b24502c9de57c8777a638190d52c18d5';

UPDATE `s_cms_support` SET `email_template` = 'Partner inquiry - {$sShopname}
{sVars.firma} want to become your partner!

Company: {sVars.firma}
Contact person: {sVars.ansprechpartner}
Street / No.: {sVars.strasse}
Postal Code / City: {sVars.plz} {sVars.ort}
eMail: {sVars.email}
Phone: {sVars.tel}
Fax: {sVars.fax}
Website: {sVars.website}

Comment:
{sVars.kommentar}

Profile:
{sVars.profil}' WHERE name = 'Partner form' AND MD5(s_cms_support.email_template) = 'a179ec3e50b3135baab41f9badbd259a';

-- 10-add-support-for-iron-browser.sql
UPDATE `s_core_config_elements` SET `value` = 's:2773:"antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;ko_yappo_robot;labelgrabber.txt;larbin;legs;linkidator;linkscan;lockon;logo_gif;macworm;magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient";' WHERE `s_core_config_elements`.`name` = 'botBlackList';

-- 11-add-show-listing-option.sql
DROP TABLE IF EXISTS s_emotion_backup;
CREATE TABLE IF NOT EXISTS `s_emotion_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `active` int(1) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) DEFAULT NULL,
  `cell_height` int(11) NOT NULL,
  `article_height` int(11) NOT NULL,
  `container_width` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `valid_from` datetime DEFAULT NULL,
  `valid_to` datetime DEFAULT NULL,
  `userID` int(11) DEFAULT NULL,
  `show_listing` int(1) NOT NULL,
  `is_landingpage` int(1) NOT NULL,
  `landingpage_block` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `landingpage_teaser` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_keywords` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `seo_description` text COLLATE utf8_unicode_ci NOT NULL,
  `create_date` datetime DEFAULT NULL,
  `template` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
RENAME TABLE s_emotion TO s_emotion_backup;
INSERT IGNORE INTO s_emotion_new (`id`,`active`, `name`,`cols`,`cell_height`,`article_height`,`container_width`,`rows`,`valid_from`,`valid_to`,`userID`,`is_landingpage`,`landingpage_block`,`landingpage_teaser`,`seo_keywords`,`seo_description`,`create_date`,`template`,`modified`)
SELECT `id`,`active`, `name`,`cols`,`cell_height`,`article_height`,`container_width`,`rows`,`valid_from`,`valid_to`,`userID`,`is_landingpage`,`landingpage_block`,`landingpage_teaser`,`seo_keywords`,`seo_description`,`create_date`,`template`,`modified` FROM s_emotion_backup;
RENAME TABLE s_emotion_new TO s_emotion;
DROP TABLE s_emotion_backup;

-- 12-fix-vat-service-label.sql
UPDATE `s_core_config_elements`
SET `label` = 'Wenn der Service nicht erreichbar ist, nur eine einfache Überprüfung durchführen'
WHERE `name` = 'vatchecknoservice';

-- 13-fix-translation.sql
UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_element_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `label` = REPLACE(label, 'Article', 'Product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'article', 'product')
WHERE locale_id = 2;
UPDATE s_core_config_form_translations
SET `description` = REPLACE(description, 'Article', 'Product')
WHERE locale_id = 2;

-- 14-remove-kind-3.sql
DELETE ad, at, ap
FROM s_articles_details ad
LEFT JOIN s_articles_attributes at
ON ad.id=at.articledetailsID
LEFT JOIN s_articles_prices ap
ON ad.id=ap.articledetailsID
WHERE ad.kind=3;

-- 15-fix-frontend-translation.sql
REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/index/checkout_actions', 1, 2, 'IndexInfoArticles', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingSortName', 'Product description', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingLabelItemsPerPage', 'Products per page', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_article', 1, 2, 'ListingBoxLinkDetails', 'Go to product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/actions', 1, 2, 'DetailLinkVoucher', 'Recommend product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/actions', 1, 2, 'DetailLinkContact', 'Do you have any questions concerning this product?', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/description', 1, 2, 'DetailDescriptionHeader', 'Product information', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/similar', 1, 2, 'DetailSimilarHeader', 'Similar products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeader', 'The product has been added to the shopping cart successfully', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddHeaderCrossSelling', 'You may also like these products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_amount', 1, 2, 'AjaxAmountInfoCountArticles', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/widgets/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/blog/detail', 1, 2, 'BlogHeaderCrossSelling', 'Related products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/account/downloads', 1, 2, 'DownloadsColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/index', 1, 2, 'NoteText2', 'Simply add a desired product to the wish list and {$sShopname} will save it for you. Thus you are able to call up your selected products the next time you visit the online shop. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/index', 1, 2, 'NoteColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/index', 1, 2, 'CompareInfoCount', 'Compare product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'DetailCommentInfoSuccess', 'Thank you for evaluating our product! The product will be activated after verification.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'DetailCommentInfoRating', 'from {$sArticle.sVoteAverange.count} customer evaluations', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddErrorHeader', 'The product could not be added to the shopping cart. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/related', 1, 2, 'DetailRelatedHeader', 'Complementary products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/note/item', 1, 2, 'NoteLinkDetails', 'View product ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/added', 1, 2, 'CompareHeaderTitle', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_header', 1, 2, 'CartColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterLabelAddArticle', 'Add product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart', 1, 2, 'CartInfoEmpty', 'Your shopping cart does not contain any products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/account/order_item', 1, 2, 'OrderItemColumnName', 'Product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_item', 1, 2, 'CartItemInfoPremium', 'As a small token of our thanks, you receive this product for free.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/bundle/box_related', 1, 2, 'BundleHeader', 'Buy this product bundled with ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/buy', 1, 2, 'DetailBuyInfoNotAvailable', 'This product is currently not available.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/description', 1, 2, 'DetailDescriptionLinkInformation', 'Further products by {$information.description}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/notification/index', 1, 2, 'DetailNotifyHeader', 'Please inform me as soon as the product is available again.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/notification/index', 1, 2, 'DetailNotifyInfoSuccess', 'Please confirm the link contained in the e-mail that you have just received. We will inform you as soon as the product is available again. ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/supplier', 1, 2, 'SearchArticlesFound', 'Products found!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoShipping', 'This product will be released at', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/added', 1, 2, 'CheckoutAddArticleInfoAdded', '{$sArticleName} has been added to shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/error', 1, 2, 'DetailRelatedHeader', 'Unfortunately, this product is no longer available', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/error', 1, 2, 'DetailRelatedHeaderSimilarArticles', 'Similar products:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy', 1, 2, 'SearchHeadline', 'The following products have been found matching your search "{$sRequests.sSearch}":  {$sSearchResults.sArticlesCount} ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/listing_actions', 1, 2, 'ListingActionsOffersLink', 'Further products in this category:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_index', 1, 2, 'IndexSimilaryArticlesSlider', 'Products similar to those you have recently viewed:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleLessStock', 'Unfortunately, the requested product is not deliverable in the desired quantities. (#0 of #1 deliverable).', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutSelectVariant', 'Please select a variant to add desired product to the shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleNoStock', 'Unfortunately, the requested product is no longer available in the desired quantities.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/confirm', 1, 2, 'ConfirmErrorStock', 'One of your desired products is not available. Please remove this item from shopping cart!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_listing', 1, 2, 'IndexSimilaryArticlesSlider', 'Products similar to those you have recently viewed', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/add_article', 1, 2, 'CompareHeaderTitle', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/compare/add_article', 1, 2, 'CompareInfoMaxReached', 'You can only compare a maximum of {config name=maxComparisons} products in a single step', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/compare/index', 1, 2, 'DetailActionLinkCompare', 'Compare products', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/comment', 1, 2, 'InquiryTextArticle', 'I have the following questions on the product', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_detail', 1, 2, 'DetailBoughtArticlesSlider', 'Customers also bought:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleNotFound', 'Product could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

-- 16-fix-frontend-translation_see_detail.sql
REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/box_article', 1, 2, 'ListingBoxLinkDetails', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_similar', 1, 2, 'SimilarBoxLinkDetails', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/listing/box_similar', 1, 2, 'SimilarBoxMore', 'See details', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

-- 17-refactor_cache_module.sql
DELETE FROM `s_core_menu` WHERE `name` = 'Proxy/Model-Cache';
DELETE FROM `s_core_menu` WHERE `name` = 'Konfiguration';
UPDATE `s_core_menu` SET `name` = 'Konfiguration + Template', `action` = 'Config', `shortcut` = 'STRG + ALT + X'  WHERE `name` = 'Textbausteine + Template';
UPDATE `s_core_menu` SET `action` = 'Frontend', `shortcut` = 'STRG + ALT + F' WHERE `name` = 'Artikel + Kategorien';

-- 1-fix-some-table-layouts.sql
DROP TABLE IF EXISTS `s_core_plugin_configs`, `s_core_plugin_elements`, `s_core_engine_queries`, `s_core_licences`, `s_plugin_benchmark_log`;
ALTER TABLE `s_filter_articles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_order_history` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_plugin_widgets_notes` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- 2-fix-broken-unicode-strings.sql
UPDATE `s_core_config_elements` SET `value` = 's:375:"ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,für,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,können,wird,sind,ihre,einem,of,du,sind,einer,über,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am";' WHERE `name` LIKE 'badwords';

-- 3-increase-mail-context-size.sql
ALTER TABLE `s_core_config_mails` CHANGE `context` `context` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;

-- 4-add-index-to-search-statistics.sql
DROP TABLE IF EXISTS s_statistics_search_backup;
CREATE TABLE IF NOT EXISTS `s_statistics_search_new` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) CHARACTER SET latin1 NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `searchterm` (`searchterm`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
RENAME TABLE s_statistics_search TO s_statistics_search_backup;
INSERT INTO s_statistics_search_new
(SELECT * FROM s_statistics_search_backup);
RENAME TABLE s_statistics_search_new TO s_statistics_search;
DROP TABLE s_statistics_search_backup;

-- 5-update-plugin-description.sql
UPDATE `s_core_plugins` set description = REPLACE(description, 'als einziger BaFin-zertifizierter', 'als BaFin-zertifizierter') WHERE name LIKE "HeidelPayment" OR name LIKE "HeidelActions";

-- 6-fix-configurator-table-layout.sql
CREATE TABLE IF NOT EXISTS `new_s_article_configurator_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `group_id` (`group_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_options` (`id`, `group_id`, `name`, `position`)
SELECT `id`, `group_id`, `name`, `position` FROM `s_article_configurator_options`;
DROP TABLE IF EXISTS `s_article_configurator_options`;
RENAME TABLE `new_s_article_configurator_options` TO `s_article_configurator_options`;

CREATE TABLE IF NOT EXISTS `new_s_article_configurator_set_group_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `group_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`group_id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_set_group_relations` (`set_id`, `group_id`)
SELECT `set_id`, `group_id` FROM `s_article_configurator_set_group_relations`;
DROP TABLE IF EXISTS `s_article_configurator_set_group_relations`;
RENAME TABLE `new_s_article_configurator_set_group_relations` TO `s_article_configurator_set_group_relations`;

CREATE TABLE IF NOT EXISTS `new_s_article_configurator_set_option_relations` (
  `set_id` int(11) unsigned NOT NULL DEFAULT '0',
  `option_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`set_id`,`option_id`)
) ENGINE=InnoDB DEFAULT COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_article_configurator_set_option_relations` (`set_id`, `option_id`)
SELECT `set_id`, `option_id` FROM `s_article_configurator_set_option_relations`;
DROP TABLE IF EXISTS `s_article_configurator_set_option_relations`;
RENAME TABLE `new_s_article_configurator_set_option_relations` TO `s_article_configurator_set_option_relations`;

-- 7-remove-unused-config-elements.sql
DELETE FROM `s_core_config_elements`
WHERE `name` IN ('revision', 'version');

UPDATE `s_core_config_elements` SET value = 'i:8;', `type` = 'number' WHERE name = 'chartrange';
UPDATE `s_core_config_elements` SET value = 's:8:"51,51,51";' WHERE name = 'captchaColor';
UPDATE `s_core_config_elements` SET value = 's:15:"Shopware 4 Demo";' WHERE name = 'shopName';
DELETE FROM `s_core_config_values` WHERE id < 56;

-- 8-change-decimal-precision-of-purchaseunit.sql
ALTER TABLE `s_articles_details` CHANGE `purchaseunit` `purchaseunit` DECIMAL( 11, 4 ) UNSIGNED NULL DEFAULT NULL;

-- 9-add-snippets-for-frontend-order-item.sql
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoCompleted', 'Komplett abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoPartiallyCompleted', 'Teilweise abgeschlossen');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoClarificationNeeded', 'Klärung notwendig');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 1, 'OrderItemInfoReadyForShipping', 'Zur Lieferung bereit');

INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoCompleted', 'Completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoPartiallyCompleted', 'Partially completed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoClarificationNeeded', 'Clarification needed');
INSERT IGNORE INTO `s_core_snippets` (`namespace`,`shopID`,`localeID`,`name`,`value`) VALUES('frontend/account/order_item', 1, 2, 'OrderItemInfoReadyForShipping', 'Ready for shipping');
-- 1-fix-emotion-foreign-key.sql
-- //

DROP TABLE IF EXISTS s_emotion_attributes_new;
DROP TABLE IF EXISTS s_emotion_attributes_backup;

-- Copy structure and data to new table, this does _not_ copy the foreign keys, that's exacly what we want
CREATE TABLE s_emotion_attributes_new LIKE s_emotion_attributes;
INSERT INTO s_emotion_attributes_new SELECT * FROM s_emotion_attributes;

RENAME TABLE s_emotion_attributes TO s_emotion_attributes_backup, s_emotion_attributes_new TO s_emotion_attributes;

-- Add missing foreign key
ALTER TABLE `s_emotion_attributes` ADD FOREIGN KEY ( `emotionID` ) REFERENCES `s_emotion` (
        `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

DROP TABLE s_emotion_attributes_backup;

-- 2-trim-links.sql
-- //

UPDATE `s_articles_information` SET `link` = TRIM( `link` ) ;

-- 3-fix-blog-attributes.sql
-- //

DROP TABLE IF EXISTS s_blog_attributes_new;
DROP TABLE IF EXISTS s_blog_attributes_backup;

-- Copy structure and data to new table, this does _not_ copy the foreign keys, that's exacly what we want
CREATE TABLE s_blog_attributes_new LIKE s_blog_attributes;
INSERT INTO s_blog_attributes_new SELECT * FROM s_blog_attributes;

RENAME TABLE s_blog_attributes TO s_blog_attributes_backup, s_blog_attributes_new TO s_blog_attributes;

-- Add missing foreign key
ALTER TABLE `s_blog_attributes` ADD FOREIGN KEY ( `blog_id` ) REFERENCES `s_blog` (
        `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

DROP TABLE s_blog_attributes_backup;

-- 4-fix-cronstock-mail.sql
-- //

UPDATE s_core_config_mails SET ishtml = 0 WHERE name = 'sARTICLESTOCK';


-- 5-fix-config-values-length.sql
-- //

ALTER TABLE `s_core_config_values` CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- 6-install-self-healing.sql


-- //

DELETE FROM s_core_plugins WHERE name = 'SelfHealing';

INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(NULL, 'Core', 'SelfHealing', 'SelfHealing', 'Default', NULL, NULL, 1, '2012-10-16 12:13:54', '2012-10-16 14:07:23', '2012-10-16 14:07:23', '2012-10-16 14:07:23', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, NULL);

SET @parent = (SELECT id FROM s_core_plugins WHERE name='SelfHealing');

DELETE FROM s_core_subscribes WHERE listener
IN (
	'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchLoopShutdown',
	'Shopware_Plugins_Core_SelfHealing_Bootstrap::onStartDispatch'
);

INSERT IGNORE INTO  `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchLoopShutdown', @parent, -9999),
(NULL, 'Enlight_Controller_Front_StartDispatch', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onStartDispatch', @parent, -9999);


-- 7-update-self-healing.sql


-- //

DELETE FROM s_core_plugins WHERE name = 'SelfHealing';

INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(NULL, 'Core', 'SelfHealing', 'SelfHealing', 'Default', NULL, NULL, 1, '2012-10-16 12:13:54', '2012-10-16 14:07:23', '2012-10-16 14:07:23', '2012-10-16 14:07:23', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, NULL);

SET @parent = (SELECT id FROM s_core_plugins WHERE name='SelfHealing');

DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';
DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100),
(NULL, 'Enlight_Controller_Front_PostDispatch', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100),
(NULL, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100);

-- 8-import-configurator-templates.sql

-- //

CREATE TABLE IF NOT EXISTS `s_article_configurator_templates` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_number` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `suppliernumber` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `additionaltext` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `impressions` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `active` int(11) unsigned NOT NULL DEFAULT '0',
  `instock` int(11) DEFAULT NULL,
  `stockmin` int(11) unsigned DEFAULT NULL,
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
  PRIMARY KEY (`id`),
  KEY `articleID` (`article_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_article_configurator_templates_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) unsigned DEFAULT NULL,
  `attr1` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr2` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr3` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr7` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr8` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr9` mediumtext COLLATE utf8_unicode_ci,
  `attr10` mediumtext COLLATE utf8_unicode_ci,
  `attr11` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr12` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr13` varchar(255) COLLATE utf8_unicode_ci DEFAULT '0',
  `attr14` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr15` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr16` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr17` date DEFAULT NULL,
  `attr18` mediumtext COLLATE utf8_unicode_ci,
  `attr19` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attr20` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `templateID` (`template_id`),
  CONSTRAINT `s_article_configurator_templates_attributes_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `s_article_configurator_templates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION

) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_article_configurator_template_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `template_id` int(10) unsigned DEFAULT NULL,
  `customer_group_key` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `from` int(10) unsigned NOT NULL,
  `to` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `pseudoprice` double DEFAULT NULL,
  `baseprice` double DEFAULT NULL,
  `percent` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pricegroup_2` (`customer_group_key`,`from`),
  KEY `pricegroup` (`customer_group_key`,`to`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_article_configurator_template_prices_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_price_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `priceID` (`template_price_id`),
  CONSTRAINT `s_article_configurator_template_prices_attributes_ibfk_1` FOREIGN KEY (`template_price_id`) REFERENCES `s_article_configurator_template_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- 9-adds-context-for-old-template-mails.sql
-- //

UPDATE `s_core_config_mails` SET `context` = 'a:4:{s:5:"sName";s:11:"Peter Meyer";s:8:"sArticle";s:10:"Blumenvase";s:5:"sLink";s:31:"http://shop.example.org/test123";s:8:"sComment";s:36:"Hey Peter - das musst du dir ansehen";}'
WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND';

UPDATE `s_core_config_mails` SET 
`context` = 'a:2:{s:12:"sArticleName";s:20:"ESD Download Artikel";s:5:"sMail";s:23:"max.mustermann@mail.com";}'
WHERE `s_core_config_mails`.`name` = 'sNOSERIALS';


UPDATE `s_core_config_mails` SET 
`context`= 'a:2:{s:9:"sPassword";s:7:"xFqr3zp";s:5:"sMail";s:18:"nutzer@example.org";}'
WHERE `s_core_config_mails`.`name` = 'sPASSWORD';


UPDATE `s_core_config_mails`  SET `context` = 'a:30:{s:5:"sShop";s:7:"Deutsch";s:8:"sShopURL";s:27:"http://trunk.qa.shopware.in";s:7:"sConfig";a:0:{}s:5:"sMAIL";s:14:"xy@example.org";s:7:"country";s:1:"2";s:13:"customer_type";s:7:"private";s:10:"salutation";s:4:"Herr";s:9:"firstname";s:8:"Banjimen";s:8:"lastname";s:6:"Ercmer";s:5:"phone";s:8:"55555555";s:3:"fax";N;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;s:11:"sValidation";N;s:9:"birthyear";s:0:"";s:10:"birthmonth";s:0:"";s:8:"birthday";s:0:"";s:11:"dpacheckbox";N;s:7:"company";s:0:"";s:6:"street";s:14:"Musterstreaße";s:12:"streetnumber";s:2:"55";s:7:"zipcode";s:5:"55555";s:4:"city";s:11:"Musterhsuen";s:10:"department";s:0:"";s:15:"shippingAddress";N;s:7:"stateID";N;}'
WHERE `s_core_config_mails`.`name` = 'sREGISTERCONFIRMATION';

UPDATE `s_core_config_mails` SET 
`context`= 'a:2:{s:8:"customer";s:11:"Peter Meyer";s:4:"user";s:11:"Hans Maiser";}'
WHERE `s_core_config_mails`.`name` = 'sVOUCHER';

-- 10-remove-bonussytem-from-config.sql
-- //

SET @parent = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem');
DELETE FROM `s_core_config_values` WHERE `element_id` = @parent;
DELETE FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem';

-- 11-improve-customer-incrementation.sql
-- //

UPDATE `s_order_number` SET `s_order_number`.`number`=`s_order_number`.`number`+1 WHERE `s_order_number`.`name` ='user';

-- 1-add-newsletter-config.sql
-- //

SET @help_parent = (SELECT id FROM s_core_config_forms WHERE name='Other');

INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, @help_parent , 'Newsletter', 'Newsletter', NULL, 0, 0, NULL);

SET @parent = (SELECT id FROM s_core_config_forms WHERE name = 'Newsletter');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'MailCampaignsPerCall', 'i:1000;', 'Anzahl der Mails, die pro Cronjob-Aufruf versendet werden', NULL, 'number', 1, 0, 0, NULL, NULL, NULL);

-- 2-fix-google-product-export.sql
-- Set multishopID to 1 if the default ID does not exist //

UPDATE `s_export` SET `multishopID`=1 WHERE `multishopID` NOT IN (SELECT `id` FROM `s_core_shops`) AND `name`='Google Produktsuche';

-- 3-fix-product-export-eans.sql
-- replace attr6 with ean //

UPDATE
	`s_export`
SET
	`body` = REPLACE(`body`, '$sArticle.attr6', '$sArticle.ean')
WHERE
	`last_export` LIKE '2000%';


-- 4-add-new-version-of-skrill-payment.sql


UPDATE `s_core_plugins` SET `version` = '2.0.0', `update_version` = NULL WHERE `name` = 'PaymentSkrill';


-- 5-update-input-filter-config.sql

SET @parent = (SELECT f.id FROM s_core_config_forms f WHERE f.name = 'InputFilter');

DELETE e FROM s_core_config_elements e
WHERE e.form_id = @parent
AND e.name LIKE '%_regex';

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'own_filter', 'N;', 'Eigener Filter', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'rfi_protection', 'b:1;', 'RemoteFileInclusion-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'sql_protection', 'b:1;', 'SQL-Injection-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'xss_protection', 'b:1;', 'XSS-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL);

-- 6-add-shop-url.sql

CREATE TABLE IF NOT EXISTS `s_core_shops_new` (
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
  `secure_host` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `secure_base_path` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT IGNORE INTO `s_core_shops_new` (
  `id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `hosts`, `secure`, `secure_host`, `secure_base_path`,
  `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`
)
SELECT
  `id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `hosts`, `secure`, `secure_host`, `secure_base_path`,
  `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`
FROM s_core_shops;

DROP TABLE IF EXISTS s_core_shops;
RENAME TABLE s_core_shops_new TO s_core_shops;

UPDATE `s_core_shops` SET `base_url` = `base_path` WHERE `base_path` IS NOT NULL AND `main_id` IS NOT NULL;
UPDATE `s_core_shops` SET `secure_base_path` = NULL, `secure_host` = NULL, `host` = NULL, `base_path` = NULL WHERE `main_id` IS NOT NULL;

-- 7-change_snippet_confirm_dispatch.sql

UPDATE `s_core_snippets`
SET `value` = "Ändern"
WHERE `name` LIKE 'CheckoutDispatchLinkSend'
AND `namespace` LIKE 'frontend/checkout/confirm_dispatch'
AND `value` LIKE "Ã„ndern";

-- 8-change-snippet-document-index-tax.sql

UPDATE `s_core_snippets`
SET `value` = "zzgl. {$key}% MwSt:"
WHERE `name` LIKE 'DocumentIndexTax'
AND `value` LIKE "zzgl. {$key} MwSt:";

UPDATE `s_core_snippets`
SET `value` = "Plus {$key}% VAT:"
WHERE `name` LIKE 'DocumentIndexTax'
AND `value` LIKE "Plus {$key} VAT:";

-- 9-fixes-seo-typo.sql
--  //

UPDATE `s_core_config_elements` SET `label`='Meta-Description von Artikel/Kategorien aufbereiten' WHERE `label`='Meta-Description von Artikel/Kategorien aufbereiteten' AND `name`='seometadescription';

-- 1-add-account-config.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'Anmeldung / Registrierung');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'accountPasswordCheck', 'b:1;', 'Aktuelles Passwort bei Passwort-Änderungen abfragen', NULL, 'boolean', 1, 0, 0, NULL, NULL, NULL);

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'InputFilter');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'refererCheck', 'b:1;', 'Referrer-Check aktivieren', NULL, 'boolean', 1, 0, 0, NULL, NULL, NULL);

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/account/index', 1, 2, 'AccountLabelCurrentPassword', 'Your current password*:', '2013-01-23 08:57:47', '2013-01-23 08:57:47'),
('frontend', 1, 2, 'AccountCurrentPassword', 'Your current password is wrong', '2013-01-23 08:57:47', '2013-01-23 08:57:47');


-- 2-fix-article-attribute-types.sql
-- //

UPDATE `s_core_engine_elements` SET `type`='text' WHERE `type`='textfield' AND `name` IN ('attr1', 'attr2');

-- 3-add-shop-routing-option.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'SEO/Router-Einstellungen');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'preferBasePath', 'b:1;', 'Shopware-Kernel aus URL entfernen ', 'Entfernt "shopware.php" aus URLs. Verhindert, dass Suchmaschinen fälschlicherweise DuplicateContent im Shop erkennen. Wenn kein ModRewrite zur Verfügung steht, muss dieses Häcken entfernt werden.', 'boolean', 1, 0, 0, NULL, NULL, NULL);

-- 1-add-esd-config.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'ESD');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'redirectDownload', 'b:0;', 'Auf Download-Datei direkt verlinken', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL);


SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'redirectDownload');

INSERT IGNORE INTO `s_core_config_element_translations` (`id` ,`element_id` ,`locale_id` ,`label` ,`description`)
VALUES (NULL , @parent, '2', 'Link to download file directly', NULL);

-- 2-add-default-email-templates.sql
-- //

UPDATE `s_cms_support` SET `email_template` = 'Return - Shopware Demoshop

Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Comment:
{sVars.info}'
WHERE `name` LIKE "Return"
AND `email_template` LIKE "INSERT INTO s_user_service%";



UPDATE `s_cms_support` SET `email_template` = 'Defective product - Shopware Demoshop

Company: {sVars.firma}
Customer no.: {sVars.kdnr}
eMail: {sVars.email}

Invoice no.: {sVars.rechnung}
Article no.: {sVars.artikel}

Description of failure:
--------------------------------
{sVars.fehler}

Type: {sVars.rechner}
System {sVars.system}
How does the problem occur:
{sVars.wie}'
WHERE `name` LIKE "Defective product"
AND `email_template` LIKE "INSERT INTO s_user_service%";

-- 1-remove-broken-subscribes.sql
-- SW-5202-remove-broken-subscribes

-- //

DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Core_Shop_Bootstrap::%';
DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Backend_Locale_Bootstrap::%';

-- 2-remove-needless-note-compare-snippet.sql
-- //

DELETE FROM `s_core_snippets` WHERE `name` = 'NoteLinkCompare' AND `namespace` = 'frontend/note/item';

-- 3-change-notification-snippet.sql
--  //

UPDATE `s_core_snippets`
SET `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten. Eventuell wurde Ihre eMail-Adresse bereits validiert."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.";


UPDATE `s_core_snippets`
SET `value` = "An error has occurred while validating your e-mail address. Possibly your email address has already been validated."
WHERE `name` = 'DetailNotifyInfoInvalid'
AND `namespace` = 'frontend/plugins/notification/index'
AND `value` = "An error has occured while validating your e-mail address.";

-- 4-replicate-the-multilanguage-parent-category-id.sql
-- //

UPDATE s_core_multilanguage as m, s_core_shops as s SET m.parentID=s.category_id WHERE m.id = s.id;

-- 5-change-blog-settings-description.sql
-- //

SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'blogcategory' AND `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen');

UPDATE `s_core_config_elements`
SET `label` = 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen (Nur alte Templatebasis)'
WHERE `id` = @parent;

UPDATE `s_core_config_element_translations`
SET `label` = 'Show blog entries from category (ID) on starting page (Only old template base)'
WHERE `id` = @parent;


-- 6-remove-broken-cms-support-field.sql
-- //
DELETE FROM `s_cms_support_fields` WHERE `name` LIKE "sdfg" AND `label` LIKE "sdf";

-- 7-fix-umlauts-in-frontend-account-snippet.sql
-- //

UPDATE `s_core_snippets` 
SET `value` = 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnungsadressen zugreifen.'
WHERE `name` LIKE 'SelectBillingInfoEmpty' 
AND `value` LIKE 'Nachdem Sie die erste Bestellung durchgef?hrt haben, k?nnen Sie hier auf vorherige Rechnungsadressen zugreifen.';

-- 8-add-frontend-account-snippet.sql
-- //

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelMr', 'Mr', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelMr', 'Herr', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 2, 'RegisterShippingLabelMrs', 'Mrs', '2013-04-22 16:04:23', '2013-04-22 16:04:23'),
('frontend/register/shipping_fieldset', 1, 1, 'RegisterShippingLabelMrs', 'Frau', '2013-04-22 16:04:23', '2013-04-22 16:04:23');

-- 9-fix-esd-snippet.sql
-- //
UPDATE `s_core_snippets`
SET `value` = 'Dieser Download steht Ihnen nicht zur Verfügung!'
WHERE `name` LIKE 'DownloadsInfoAccessDenied'
AND `value` LIKE 'Dieser Download stehen Ihnen nicht zur Verfügung!';



SET @parentId = (SELECT id FROM s_core_config_forms WHERE name = 'Other' LIMIT 1);

INSERT IGNORE INTO `s_core_config_forms`
(`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`)
VALUES (NULL, @parentId, 'LegacyOptions', 'Abwärtskompatibilität', NULL, '0', '0', NULL);

SET @formId = (SELECT id FROM s_core_config_forms WHERE name = 'LegacyOptions' LIMIT 1);

INSERT IGNORE INTO `s_core_config_form_translations` (`id`, `form_id`, `locale_id`, `label`, `description`)
VALUES (NULL, @formId, '2', 'Legacy options', NULL);

INSERT IGNORE INTO `s_core_config_elements`
(`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`)
VALUES (NULL, @formId, 'useShortDescriptionInListing', 'b:0;', 'In Listen-Ansichten immer die Artikel-Kurzbeschreibung anzeigen', 'Beeinflusst: Topseller, Kategorielisten, Einkaufswelten', 'checkbox', '0', '0', '0', NULL, NULL);

SET @elementId = (SELECT id FROM s_core_config_elements WHERE name = 'useShortDescriptionInListing' LIMIT 1);

INSERT IGNORE INTO `s_core_config_element_translations` (`id`, `element_id`, `locale_id`, `label`, `description`)
VALUES (NULL, @elementId, '2', 'Always display item descriptions in listing views', 'Affected views: Top seller, category listings, emotions');
