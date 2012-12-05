-- phpMyAdmin SQL Dump
-- version 2.11.8.1deb5+lenny6
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 22. Oktober 2010 um 16:15
-- Server Version: 5.1.51
-- PHP-Version: 5.3.3-0.dotdeb.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `final_1`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cb_events`
--

CREATE TABLE IF NOT EXISTS `cb_events` (
  `eventID` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `action` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `externalBDRID` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `bdrID` varchar(64) COLLATE latin1_german1_ci NOT NULL,
  `price` double NOT NULL,
  `currency` varchar(4) COLLATE latin1_german1_ci NOT NULL,
  `crn` varchar(64) COLLATE latin1_german1_ci NOT NULL,
  `systemID` int(2) NOT NULL,
  `linkID` varchar(64) COLLATE latin1_german1_ci NOT NULL,
  `xml` text COLLATE latin1_german1_ci NOT NULL,
  `cb_datetime` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  `shopware_datetime` datetime NOT NULL,
  UNIQUE KEY `event-id` (`eventID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Daten für Tabelle `cb_events`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `cb_orders`
--

CREATE TABLE IF NOT EXISTS `cb_orders` (
  `uid` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `cb_uid` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `cb_linknr` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `cb_transaction_id` int(11) NOT NULL,
  `cb_price` double NOT NULL,
  `currency` varchar(4) COLLATE latin1_german1_ci NOT NULL,
  `externalBDRID` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `coreID` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `status` tinyint(2) NOT NULL,
  `date_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Daten für Tabelle `cb_orders`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eos_reserved_orders`
--

CREATE TABLE IF NOT EXISTS `eos_reserved_orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `werbecode` varchar(255) COLLATE latin1_german1_ci DEFAULT NULL,
  `transactionID` int(11) NOT NULL,
  `reference` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `status` int(11) NOT NULL,
  `bookdate` date DEFAULT NULL,
  `bookvalue` decimal(8,2) NOT NULL,
  `added` datetime NOT NULL,
  `changed` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactionID` (`transactionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `eos_reserved_orders`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `eos_risk_results`
--

CREATE TABLE IF NOT EXISTS `eos_risk_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `result` varchar(255) NOT NULL,
  `added` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `eos_risk_results`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `paypal_orders`
--

CREATE TABLE IF NOT EXISTS `paypal_orders` (
  `suid` varchar(255) CHARACTER SET latin1 NOT NULL,
  `payerId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `transactionId` varchar(64) CHARACTER SET latin1 NOT NULL,
  `paymentStatus` varchar(32) CHARACTER SET latin1 NOT NULL,
  `authorization` tinyint(1) NOT NULL,
  `booked` tinyint(1) NOT NULL,
  `price` double NOT NULL,
  `refunded` double NOT NULL,
  `currency` varchar(4) CHARACTER SET latin1 NOT NULL,
  `stransId` varchar(255) CHARACTER SET latin1 NOT NULL,
  `ipn` text CHARACTER SET latin1 NOT NULL,
  `dateTime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Daten für Tabelle `paypal_orders`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `saferpay_orders`
--

CREATE TABLE IF NOT EXISTS `saferpay_orders` (
  `trans_id` int(11) NOT NULL AUTO_INCREMENT,
  `orders_id` varchar(64) CHARACTER SET latin1 NOT NULL DEFAULT '0',
  `saferpay_account_id` varchar(96) CHARACTER SET latin1 NOT NULL,
  `saferpay_id` varchar(96) CHARACTER SET latin1 DEFAULT NULL,
  `saferpay_token` varchar(96) CHARACTER SET latin1 NOT NULL,
  `saferpay_amount` decimal(15,4) NOT NULL DEFAULT '0.0000',
  `saferpay_currency` varchar(8) CHARACTER SET latin1 NOT NULL,
  `saferpay_provider_id` int(11) DEFAULT '0',
  `saferpay_provider_name` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `saferpay_eci` int(1) NOT NULL,
  `saferpay_complete` int(1) NOT NULL DEFAULT '0',
  `saferpay_complete_result` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `date_added` datetime DEFAULT NULL,
  `last_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`trans_id`),
  KEY `IDX_ORDER` (`orders_id`),
  KEY `IDX_SAFERPAY_ID` (`saferpay_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `saferpay_orders`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_addon_premiums`
--

CREATE TABLE IF NOT EXISTS `s_addon_premiums` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `startprice` double NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) NOT NULL DEFAULT '0',
  `articleID` varchar(30) NOT NULL,
  `subshopID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_addon_premiums`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles`
--

CREATE TABLE IF NOT EXISTS `s_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `supplierID` int(11) unsigned NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `description_long` longtext NOT NULL,
  `shippingtime` varchar(11) NOT NULL DEFAULT '0',
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `active` int(1) unsigned NOT NULL DEFAULT '0',
  `shippingfree` int(1) unsigned NOT NULL DEFAULT '0',
  `releasedate` date NOT NULL DEFAULT '0000-00-00',
  `variantID` int(11) NOT NULL DEFAULT '0',
  `taxID` int(11) unsigned NOT NULL DEFAULT '0',
  `pseudosales` int(11) NOT NULL DEFAULT '0',
  `topseller` int(1) unsigned NOT NULL DEFAULT '0',
  `free` int(1) unsigned NOT NULL,
  `keywords` varchar(255) NOT NULL,
  `minpurchase` int(11) unsigned NOT NULL,
  `purchasesteps` int(11) unsigned NOT NULL,
  `maxpurchase` int(11) unsigned NOT NULL,
  `purchaseunit` double NOT NULL,
  `referenceunit` double NOT NULL,
  `packunit` text NOT NULL,
  `unitID` int(11) unsigned NOT NULL DEFAULT '0',
  `changetime` datetime NOT NULL,
  `pricegroupID` int(11) unsigned NOT NULL,
  `pricegroupActive` int(1) unsigned NOT NULL,
  `filtergroupID` int(11) NOT NULL,
  `laststock` int(1) NOT NULL,
  `crossbundlelook` int(1) unsigned NOT NULL,
  `notification` int(1) unsigned NOT NULL COMMENT 'send notification',
  `template` varchar(255) NOT NULL,
  `mode` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`),
  KEY `name` (`name`),
  KEY `supplierID` (`supplierID`),
  KEY `releasedate` (`releasedate`),
  KEY `shippingtime` (`shippingtime`),
  FULLTEXT KEY `description` (`description`),
  FULLTEXT KEY `name_2` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_attributes`
--

CREATE TABLE IF NOT EXISTS `s_articles_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articledetailsID` int(11) NOT NULL DEFAULT '0',
  `attr1` varchar(255) NOT NULL DEFAULT '0',
  `attr2` varchar(255) NOT NULL DEFAULT '0',
  `attr3` varchar(255) NOT NULL DEFAULT '0',
  `attr4` varchar(255) NOT NULL,
  `attr5` varchar(255) NOT NULL,
  `attr6` varchar(255) NOT NULL,
  `attr7` varchar(255) NOT NULL,
  `attr8` varchar(255) NOT NULL DEFAULT '0',
  `attr9` text NOT NULL,
  `attr10` text NOT NULL,
  `attr11` varchar(200) NOT NULL,
  `attr12` varchar(200) NOT NULL,
  `attr13` varchar(255) NOT NULL DEFAULT '0',
  `attr14` varchar(200) NOT NULL,
  `attr15` varchar(30) NOT NULL,
  `attr16` varchar(30) NOT NULL,
  `attr17` date NOT NULL DEFAULT '0000-00-00',
  `attr18` text NOT NULL,
  `attr19` varchar(255) NOT NULL,
  `attr20` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articledetailsID` (`articledetailsID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_attributes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_avoid_customergroups`
--

CREATE TABLE IF NOT EXISTS `s_articles_avoid_customergroups` (
  `articleID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  UNIQUE KEY `articleID` (`articleID`,`customergroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_articles_avoid_customergroups`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_bundles`
--

CREATE TABLE IF NOT EXISTS `s_articles_bundles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `rab_type` varchar(255) NOT NULL,
  `taxID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  `max_quantity_enable` int(11) unsigned NOT NULL,
  `max_quantity` int(11) unsigned NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `datum` datetime NOT NULL,
  `customergroups` text NOT NULL,
  `sells` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_bundles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_bundles_articles`
--

CREATE TABLE IF NOT EXISTS `s_articles_bundles_articles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bundleID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundleID_2` (`bundleID`,`ordernumber`),
  KEY `bundleID` (`bundleID`,`ordernumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_bundles_articles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_bundles_prices`
--

CREATE TABLE IF NOT EXISTS `s_articles_bundles_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bundleID` int(11) unsigned NOT NULL,
  `customergroup` varchar(255) NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundleID_2` (`bundleID`,`customergroup`),
  KEY `bundleID` (`bundleID`,`customergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_bundles_prices`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_bundles_stint`
--

CREATE TABLE IF NOT EXISTS `s_articles_bundles_stint` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `bundleID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundleID` (`bundleID`,`ordernumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_bundles_stint`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_categories`
--

CREATE TABLE IF NOT EXISTS `s_articles_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `categoryID` int(11) NOT NULL DEFAULT '0',
  `categoryparentID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`categoryID`),
  KEY `categoryID` (`categoryID`),
  KEY `categoryparentID` (`categoryparentID`),
  KEY `articleID_2` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_categories`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_details`
--

CREATE TABLE IF NOT EXISTS `s_articles_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(40) NOT NULL,
  `suppliernumber` varchar(50) NOT NULL,
  `kind` int(1) NOT NULL DEFAULT '0',
  `additionaltext` varchar(40) NOT NULL,
  `impressions` int(11) NOT NULL DEFAULT '0',
  `sales` int(11) NOT NULL DEFAULT '0',
  `active` int(11) NOT NULL DEFAULT '0',
  `instock` int(11) NOT NULL,
  `stockmin` int(11) NOT NULL,
  `esd` int(1) NOT NULL,
  `weight` double NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ordernumber` (`ordernumber`),
  KEY `articleID` (`articleID`),
  KEY `articleID_2` (`articleID`,`kind`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_details`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_downloads`
--

CREATE TABLE IF NOT EXISTS `s_articles_downloads` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `description` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `size` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_downloads`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_esd`
--

CREATE TABLE IF NOT EXISTS `s_articles_esd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articledetailsID` int(11) NOT NULL DEFAULT '0',
  `file` varchar(255) NOT NULL,
  `serials` int(1) NOT NULL DEFAULT '0',
  `notification` int(1) NOT NULL DEFAULT '0',
  `maxdownloads` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `articledetailsID` (`articledetailsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_esd`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_esd_serials`
--

CREATE TABLE IF NOT EXISTS `s_articles_esd_serials` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialnumber` varchar(255) NOT NULL,
  `esdID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `esdID` (`esdID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_esd_serials`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups` (
  `articleID` int(10) unsigned NOT NULL DEFAULT '0',
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `groupname` varchar(50) NOT NULL,
  `groupdescription` text NOT NULL,
  `groupimage` varchar(255) NOT NULL,
  `groupposition` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`articleID`,`groupID`),
  UNIQUE KEY `artikel_id` (`articleID`,`groupname`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_articles_groups`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_accessories`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_accessories` (
  `articleID` int(10) unsigned NOT NULL DEFAULT '0',
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `groupname` varchar(50) NOT NULL,
  `groupdescription` text NOT NULL,
  `groupimage` varchar(255) NOT NULL,
  PRIMARY KEY (`articleID`,`groupID`),
  UNIQUE KEY `artikel_id` (`articleID`,`groupname`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_articles_groups_accessories`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_accessories_option`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_accessories_option` (
  `articleID` int(10) unsigned NOT NULL DEFAULT '0',
  `optionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `optionname` varchar(50) NOT NULL,
  `ordernumber` varchar(30) NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `pricenet` double NOT NULL DEFAULT '0',
  PRIMARY KEY (`optionID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_groups_accessories_option`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_option`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_option` (
  `articleID` int(10) unsigned NOT NULL DEFAULT '0',
  `optionID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `groupID` int(10) unsigned NOT NULL DEFAULT '0',
  `optionname` varchar(50) NOT NULL,
  `optioninstock` int(11) unsigned NOT NULL,
  `optionposition` int(11) unsigned NOT NULL,
  `optionactive` int(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`optionID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_groups_option`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_prices`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_prices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `valueID` int(11) unsigned DEFAULT NULL,
  `groupkey` varchar(35) NOT NULL,
  `price` double NOT NULL,
  `optionID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `valueID` (`valueID`,`groupkey`),
  UNIQUE KEY `groupkey` (`groupkey`,`optionID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_groups_prices`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_settings`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_settings` (
  `articleID` int(11) unsigned NOT NULL DEFAULT '0',
  `defaultorder` varchar(255) NOT NULL,
  `grouporder` varchar(255) NOT NULL,
  `optionorder` varchar(255) NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `instock` int(1) unsigned NOT NULL,
  `template` varchar(255) NOT NULL,
  `upprice` int(11) unsigned NOT NULL,
  PRIMARY KEY (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_articles_groups_settings`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_templates`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `object` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_groups_templates`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_groups_value`
--

CREATE TABLE IF NOT EXISTS `s_articles_groups_value` (
  `articleID` int(10) unsigned NOT NULL DEFAULT '0',
  `valueID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attr1` int(10) unsigned DEFAULT NULL,
  `attr2` int(10) unsigned DEFAULT NULL,
  `attr3` int(10) unsigned DEFAULT NULL,
  `attr4` int(10) unsigned DEFAULT NULL,
  `attr5` int(10) unsigned DEFAULT NULL,
  `attr6` int(10) unsigned DEFAULT NULL,
  `attr7` int(10) unsigned DEFAULT NULL,
  `attr8` int(10) unsigned DEFAULT NULL,
  `attr9` int(10) unsigned DEFAULT NULL,
  `attr10` int(10) unsigned DEFAULT NULL,
  `standard` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `ordernumber` varchar(255) DEFAULT NULL,
  `price` double NOT NULL,
  `pricenet` double NOT NULL,
  `instock` int(11) NOT NULL,
  `gv_attr1` varchar(255) DEFAULT NULL,
  `gv_attr2` varchar(255) DEFAULT NULL,
  `gv_attr3` varchar(255) DEFAULT NULL,
  `gv_attr4` varchar(255) DEFAULT NULL,
  `gv_attr5` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`valueID`),
  UNIQUE KEY `ordernumber` (`ordernumber`),
  UNIQUE KEY `attr1` (`attr1`,`attr2`,`attr3`,`attr4`,`attr5`,`attr6`,`attr7`,`attr8`,`attr9`,`attr10`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_groups_value`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_img`
--

CREATE TABLE IF NOT EXISTS `s_articles_img` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `img` varchar(100) NOT NULL,
  `main` int(1) NOT NULL,
  `description` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `relations` text NOT NULL,
  `extension` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `artikel_id` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_img`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_information`
--

CREATE TABLE IF NOT EXISTS `s_articles_information` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hauptid` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_information`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_live`
--

CREATE TABLE IF NOT EXISTS `s_articles_live` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) unsigned NOT NULL,
  `typeID` int(1) NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `rab_type` varchar(255) NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  `max_quantity_enable` int(1) unsigned NOT NULL,
  `max_quantity` int(11) unsigned NOT NULL,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `datum` datetime NOT NULL,
  `customergroups` text NOT NULL,
  `sells` int(11) unsigned NOT NULL,
  `frontpage_display` int(1) NOT NULL,
  `categories_display` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_live`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_live_prices`
--

CREATE TABLE IF NOT EXISTS `s_articles_live_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `customergroup` varchar(255) NOT NULL,
  `price` double NOT NULL,
  `endprice` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundleID_2` (`liveshoppingID`,`customergroup`),
  KEY `bundleID` (`liveshoppingID`,`customergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_live_prices`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_live_shoprelations`
--

CREATE TABLE IF NOT EXISTS `s_articles_live_shoprelations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `liveshoppingID` (`liveshoppingID`,`subshopID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_live_shoprelations`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_live_stint`
--

CREATE TABLE IF NOT EXISTS `s_articles_live_stint` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bundleID` (`liveshoppingID`,`ordernumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_live_stint`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_notification`
--

CREATE TABLE IF NOT EXISTS `s_articles_notification` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `mail` varchar(255) NOT NULL,
  `send` int(1) unsigned NOT NULL,
  `language` varchar(255) NOT NULL,
  `shopLink` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_notification`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_prices`
--

CREATE TABLE IF NOT EXISTS `s_articles_prices` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pricegroup` varchar(30) NOT NULL,
  `from` int(10) unsigned NOT NULL,
  `to` varchar(30) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articledetailsID` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `pseudoprice` double NOT NULL DEFAULT '0',
  `baseprice` double NOT NULL DEFAULT '0',
  `percent` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pricegroup` (`pricegroup`,`to`,`articledetailsID`),
  UNIQUE KEY `pricegroup_2` (`pricegroup`,`from`,`articledetailsID`),
  KEY `articleID` (`articleID`),
  KEY `articledetailsID` (`articledetailsID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_prices`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_relationships`
--

CREATE TABLE IF NOT EXISTS `s_articles_relationships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(30) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_relationships`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_similar`
--

CREATE TABLE IF NOT EXISTS `s_articles_similar` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(30) NOT NULL,
  `relatedarticle` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`relatedarticle`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_similar`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_supplier`
--

CREATE TABLE IF NOT EXISTS `s_articles_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `img` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_supplier`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_translations`
--

CREATE TABLE IF NOT EXISTS `s_articles_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL,
  `languageID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `description_long` text NOT NULL,
  `description_clear` text NOT NULL,
  `attr1` varchar(255) NOT NULL,
  `attr2` varchar(255) NOT NULL,
  `attr3` varchar(255) NOT NULL,
  `attr4` varchar(255) NOT NULL,
  `attr5` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `articleID` (`articleID`,`languageID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_translations`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_articles_vote`
--

CREATE TABLE IF NOT EXISTS `s_articles_vote` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `articleID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `headline` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `points` double NOT NULL,
  `datum` datetime NOT NULL,
  `active` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_articles_vote`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_billing_template`
--

CREATE TABLE IF NOT EXISTS `s_billing_template` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `typ` mediumint(11) NOT NULL,
  `group` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `show` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`ID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Daten für Tabelle `s_billing_template`
--

INSERT INTO `s_billing_template` (`ID`, `name`, `value`, `typ`, `group`, `desc`, `position`, `show`) VALUES
(13, 'right', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 56780<br />info@demo.de<br />www.demo.de</p>', 1, 'header', 'Briefkopf rechts', 9, 1),
(9, 'footer', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>', 1, 'footer', 'Fusszeile', 2, 1),
(8, 'left', '0cm', 2, 'sender', 'Abstand links (negativ Wert möglich)', 0, 1),
(16, 'bottom', '100px', 2, 'footer', 'Abstand unten', 1, 1),
(7, 'margin', '1cm', 2, 'headline', 'Überschrift Abstand zur Anschrift', 0, 1),
(5, 'top2', '5cm', 2, 'header', 'Logohöhe', 6, 1),
(3, 'bottom', '0cm', 2, 'margin', 'Seitenabstand unten', 0, 1),
(4, 'left', '2.41cm', 2, 'margin', 'Seitenabstand links', 0, 1),
(2, 'right', '0.81cm', 2, 'margin', 'Seitenrand rechts', 0, 1),
(1, 'top', '1cm', 2, 'margin', 'Seitenabstand oben', 0, 1),
(14, 'sender', 'Demo GmbH - Straße 3 - 00000 Musterstadt', 2, 'sender', 'Absender', 0, 1),
(17, 'number', '10', 2, 'content_middle', 'Anzahl angezeigter Postionen', 2, 1),
(20, 'top', '<p><img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="" width="393" height="78" /></p>', 1, 'header', 'Logo oben', 7, 1),
(22, 'margin', '2.2cm', 2, 'header', 'Abstand rechts (negativ Wert möglich)', 8, 1),
(15, 'left', '100px', 2, 'footer', 'Abstand links', 0, 1),
(18, 'text', '', 1, 'content_middle', 'Freitext', 4, 1),
(19, 'height', '12cm', 2, 'content_middle', 'Inhaltsabstand zum obigen Seitenrand', 0, 1),
(21, 'top', '1cm', 2, 'sender', 'Abstand unten zum Logo (negativ Wert möglich)', 0, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_articles`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_articles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_banner`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `linkTarget` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_banner`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_containers`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `value` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_containers`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_groups`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_campaigns_groups`
--

INSERT INTO `s_campaigns_groups` (`id`, `name`) VALUES
(1, 'Newsletter-Empfänger');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_html`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `headline` varchar(255) NOT NULL,
  `html` text NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `alignment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_html`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_links`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_links`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_logs`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `mailingID` int(11) NOT NULL DEFAULT '0',
  `email` varchar(255) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_logs`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_mailaddresses`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_mailaddresses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer` int(1) NOT NULL,
  `groupID` int(11) NOT NULL,
  `email` varchar(90) NOT NULL,
  `lastmailing` int(11) NOT NULL,
  `lastread` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `email` (`email`),
  KEY `lastmailing` (`lastmailing`),
  KEY `lastread` (`lastread`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_mailaddresses`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_maildata`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_maildata` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `salutation` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `street` varchar(255) DEFAULT NULL,
  `streetnumber` varchar(255) DEFAULT NULL,
  `zipcode` varchar(255) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `added` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`,`groupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_maildata`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_mailings`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_mailings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `groups` text NOT NULL,
  `subject` varchar(100) NOT NULL,
  `sendermail` varchar(255) NOT NULL,
  `sendername` varchar(255) NOT NULL,
  `plaintext` int(11) NOT NULL,
  `templateID` int(11) NOT NULL DEFAULT '0',
  `languageID` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '0',
  `locked` datetime NOT NULL,
  `recipients` int(11) NOT NULL,
  `read` int(11) NOT NULL,
  `clicked` int(11) NOT NULL,
  `customergroup` varchar(25) NOT NULL,
  `publish` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_mailings`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_positions`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `containerID` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_campaigns_positions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_sender`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_sender` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_campaigns_sender`
--

INSERT INTO `s_campaigns_sender` (`id`, `email`, `name`) VALUES
(1, 'info@example.com', 'Newsletter Absender');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_campaigns_templates`
--

CREATE TABLE IF NOT EXISTS `s_campaigns_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `path` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

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

CREATE TABLE IF NOT EXISTS `s_categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent` int(11) unsigned NOT NULL DEFAULT '1',
  `description` varchar(255) NOT NULL,
  `position` int(11) unsigned DEFAULT '0',
  `alias` int(11) NOT NULL DEFAULT '0',
  `metakeywords` text NOT NULL,
  `metadescription` text NOT NULL,
  `cmsheadline` varchar(255) NOT NULL,
  `cmstext` text NOT NULL,
  `template` varchar(255) NOT NULL,
  `noviewselect` int(1) unsigned NOT NULL,
  `aliassql` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `ac_attr1` varchar(255) NOT NULL,
  `ac_attr2` varchar(255) NOT NULL,
  `ac_attr3` varchar(255) NOT NULL,
  `ac_attr4` varchar(255) NOT NULL,
  `ac_attr5` varchar(255) NOT NULL,
  `ac_attr6` varchar(255) NOT NULL,
  `blog` int(11) NOT NULL,
  `showfiltergroups` int(11) NOT NULL,
  `external` varchar(255) NOT NULL,
  `hidefilter` int(1) NOT NULL,
  `hidetop` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parent` (`parent`),
  KEY `description` (`description`),
  KEY `position` (`position`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2151 ;

--
-- Daten für Tabelle `s_categories`
--

INSERT INTO `s_categories` (`id`, `parent`, `description`, `position`, `alias`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `noviewselect`, `aliassql`, `active`, `ac_attr1`, `ac_attr2`, `ac_attr3`, `ac_attr4`, `ac_attr5`, `ac_attr6`, `blog`, `showfiltergroups`, `external`, `hidefilter`, `hidetop`) VALUES
(3, 1, 'Deutsch', 1, 0, '', '', '', '', '', 0, '', 1, '', '', '', '', '', '', 0, 0, '', 0, 0),
(4, 1, 'Englisch', 2, 0, '', '', '', '', '', 0, '', 1, '', '', '', '', '', '', 0, 0, '', 0, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_categories_avoid_customergroups`
--

CREATE TABLE IF NOT EXISTS `s_categories_avoid_customergroups` (
  `categoryID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  UNIQUE KEY `articleID` (`categoryID`,`customergroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_categories_avoid_customergroups`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_content`
--

CREATE TABLE IF NOT EXISTS `s_cms_content` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `img` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_cms_content`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_groups`
--

CREATE TABLE IF NOT EXISTS `s_cms_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_cms_groups`
--

INSERT INTO `s_cms_groups` (`id`, `position`, `description`) VALUES
(1, 0, 'Aktuelles');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_static`
--

CREATE TABLE IF NOT EXISTS `s_cms_static` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tpl1variable` varchar(255) NOT NULL,
  `tpl1path` varchar(255) NOT NULL,
  `tpl2variable` varchar(255) NOT NULL,
  `tpl2path` varchar(255) NOT NULL,
  `tpl3variable` varchar(255) NOT NULL,
  `tpl3path` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `html` text NOT NULL,
  `grouping` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=44 ;

--
-- Daten für Tabelle `s_cms_static`
--

INSERT INTO `s_cms_static` (`id`, `tpl1variable`, `tpl1path`, `tpl2variable`, `tpl2path`, `tpl3variable`, `tpl3path`, `description`, `html`, `grouping`, `position`, `link`, `target`) VALUES
(1, '', '', '', '', '', '', 'Kontakt', '<p>F&uuml;gen Sie hier Ihre Kontaktdaten ein</p>', 'gLeft|gBottom', 1, 'shopware.php?sViewport=ticket&sFid,5', '_self'),
(2, '', '', '', '', '', '', 'Sitemap', '<h1>Hilfe und Support</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft', 1, 'shopware.php?sViewport=sitemap', ''),
(3, 'sContainerRight', '/contact/contact_right.tpl', '', '', '', '', 'Impressum', '<h1>Impressum</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 20, '', ''),
(37, '', '', '', '', '', '', 'Partnerprogramm', '<h1>Jetzt Partner werden</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gDisabled', 0, 'shopware.php?sViewport=ticket&sFid,8', '_self'),
(38, '', '', '', '', '', '', 'Affiliate program', '', 'eBottom2', 4, 'shopware.php?sViewport=ticket&sFid,17', '_self'),
(4, 'sContainerRight', '/contact/contact_right.tpl', '', '', '', '', 'AGB', '<h1>AGB</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 18, '', ''),
(6, '', '', '', '', '', '', 'Versand und Zahlungsbedingungen', '<h1>Versandinformationen</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 3, '', ''),
(7, '', '', '', '', '', '', 'Datenschutz', '<h1>Datenschutz</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom2', 6, '', ''),
(8, '', '', '', '', '', '', 'Widerrufsrecht', '<h1>Widerrufsrecht</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom', 5, '', ''),
(9, '', '', '', '', '', '', 'Über uns', '<h1>&Uuml;ber uns</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gLeft|gBottom2', 0, '', ''),
(42, '', '', '', '', '', '', 'Return', '<p>Return.</p>', 'eBottom2', 3, 'shopware.php?sViewport=ticket&sFid,20', '_self'),
(41, '', '', '', '', '', '', 'Rückgabe', '<p>R&uuml;ckgabe.</p>', 'gBottom', 4, 'shopware.php?sViewport=ticket&sFid,10', '_self'),
(40, '', '', '', '', '', '', 'Defective product', '<p>Defective product.</p>', 'eBottom', 4, 'shopware.php?sViewport=ticket&sFid,19', '_self'),
(21, '', '', '', '', '', '', 'Händler-Login', '', 'gDisabled', 0, 'shopware.php?sViewport=registerFC&sUseSSL=1&sValidation=H', ''),
(39, '', '', '', '', '', '', 'Defektes Produkt', '<p>Defektes Produkt.</p>', 'gBottom', 0, 'shopware.php?sViewport=ticket&sFid,9', '_self'),
(25, '', '', '', '', '', '', 'Aktuelles', '', 'gBottom2', 0, 'shopware.php?sViewport=content&sContent=1', ''),
(26, '', '', '', '', '', '', 'Newsletter', '', 'gLeft', 0, 'shopware.php?sViewport=newsletter', ''),
(28, '', '', '', '', '', '', 'Payment / Dispatch', '<p>Text</p>', 'eLeft|eBottom', 0, '', ''),
(27, '', '', '', '', '', '', 'About us', '<p>Text</p>', 'eLeft|eBottom', 0, '', ''),
(29, '', '', '', '', '', '', 'Privacy', '<p>Text</p>', 'eLeft|eBottom', 0, '', ''),
(30, '', '', '', '', '', '', 'Help / Support', '<p>Text</p>', 'eLeft|eBottom', 0, '', ''),
(43, '', '', '', '', '', '', 'rechtliche Vorabinformationen', '<h1>Rechtliche Vorabinformationen</h1>\r\n<h2>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas.</h2>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila. Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter.</p>\r\n<p>Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>\r\n<p>Isericordaliter Occatio ter aut Aliusmodi vel Fugo redigo, iam ops tam Plaga consulo sui ymo Zephyr humilitas. Ivi praebalteata Occumbo congruens seco, lea qui se surculus sed abhinc praejudico in forix curo. Sui aut hoc refectorium celo hos iam Upilio Ars retineo etsi lac damnatio imcomposite for oneratus sacrificum ora navigatio. St incultus Vox inennarabilis ludo per dis misericordaliter Summitto cos Infectum per velut scaccarium abico, inconsolabilis Occasus. Ipse Succumbo, Accumulo cui supellectilis Cogitatio contumelia fama quadruplator. Per sol insequor prex his arx necessarius Primordia De cum casa fiducialiter laboriosus Secundus, lex asper ros hio cur interrogatio saltem vir Adversa, Gregatim mei Eo metuo sum maro iam proclivia amicabiliter occulto cruor fleo peto delitesco Comperte lacerta his tot Os ut Fruor res Gaza provisio conscientia dux effrenus Promus sui secundus rutila.</p>\r\n<p>Celo nam balnearius Opprimo Pennatus, no decentia sui, dicto esse se pulchritudo, pupa Sive res indifferenter. Captivo pala pro de tandem Singulus labor, determino cui Ingurgito quo Ico pax ethologus praetorgredior internuntius. Ops foveo Huius dux respublica his animadverto dolus imperterritus. Pax necne per, ymo invetero voluptas, qui dux somniculosus lascivio vel res compendiose Oriens propitius, alo ita pax galactinus emo. Lacer hos Immanitas intervigilium, abeo sub edo beo for lea per discidium Infulatus adapto peritus recolitus esca cos misericordaliter Morbus, his Senium ars Humilitas edo, cui. Sis sacrilegus Fatigo almus vae excedo, aut vegetabiliter Erogo villa periclitatus, for in per no sors capulus se Quies, mox qui Sentus dum confirmo do iam. Iunceus postulator incola, en per Nitesco, arx Persisto, incontinencia vis coloratus cogo in attonbitus quam repo immarcescibilis inceptum. Ego Vena series sudo ac Nitidus. Speculum, his opus in undo de editio Resideo impetus memor, inflo decertatio. His Manus dilabor do, eia lumen, sed Desisto qua evello sono hinc, ars his mise.</p>', 'gDisabled', 0, '', ''),
(32, '', '', '', '', '', '', 'Newsletter', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport=newsletter', ''),
(33, '', '', '', '', '', '', 'Reseller-Login', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport,registerFC&sUseSSL=1&sValidation=H', ''),
(34, '', '', '', '', '', '', 'Contact', '', 'eLeft|eBottom', 0, 'shopware.php?sViewport=ticket&sFid=18', ''),
(35, '', '', '', '', '', '', 'Site Map', '', 'eBottom', 0, 'shopware.php?sViewport=sitemap', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_support`
--

CREATE TABLE IF NOT EXISTS `s_cms_support` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_template` text NOT NULL,
  `email_subject` varchar(255) NOT NULL,
  `text2` text NOT NULL,
  `ticket_typeID` int(10) NOT NULL,
  `isocode` varchar(3) NOT NULL DEFAULT 'de',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=23 ;

--
-- Daten für Tabelle `s_cms_support`
--

INSERT INTO `s_cms_support` (`id`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `ticket_typeID`, `isocode`) VALUES
(8, 'Partnerformular', '<h2>Partner werden und mitverdienen!</h2>\r\n<p>Einfach unseren Link auf ihre Seite legen und Sie erhalten f&uuml;r jeden Umsatz ihrer vermittelten Kunden automatisch eine attraktive Provision auf den Netto-Auftragswert.</p>\r\n<p>Bitte f&uuml;llen Sie <span style="text-decoration: underline;">unverbindlich</span> das Partnerformular aus. Wir werden uns umgehend mit Ihnen in Verbindung setzen!</p>', 'info@example.com', 'Partneranfrage - {$sShopname}\r\n{sVars.firma} moechte Partner Ihres Shops werden!\r\n\r\nFirma: {sVars.firma}\r\nAnsprechpartner: {sVars.ansprechpartner}\r\nStraße/Hausnr.: {sVars.strasse}\r\nPLZ / Ort: {sVars.plz} {sVars.ort}\r\neMail: {sVars.email}\r\nTelefon: {sVars.tel}\r\nFax: {sVars.fax}\r\nWebseite: {sVars.webseite}\r\nBetreff: {sVars.betreff}\r\n\r\nKommentar: \r\n{sVars.kommentar}\r\n\r\nProfil:\r\n{sVars.profil}', 'Partner Anfrage', '<p>Die Anfrage wurde versandt!</p>', 0, 'de'),
(5, 'Kontaktformular', '<p>Schreiben Sie uns eine eMail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>', 'info@example.com', 'Kontaktformular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nBetreff: {sVars.betreff}\r\nKommentar: \r\n{sVars.kommentar}\r\n\r\n\r\n', 'Kontaktformular Shopware', '<p>Ihr Formular wurde versendet!</p>', 1, 'de'),
(9, 'Defektes Produkt', '<h1>Defektes Produkt - f&uuml;r Endkunden und H&auml;ndler</h1>\r\n<p>Sie erhalten von uns nach dem Absenden dieses Formulars innerhalb kurzer Zeit eine R&uuml;ckantwort mit einer RMA-Nummer und weiterer Vorgehensweise.</p>\r\n<p>Bitte f&uuml;llen Sie die Fehlerbeschreibung ausf&uuml;hrlich aus, Sie m&uuml;ssen diese dann nicht mehr dem Paket beilegen.</p>', 'info@example.com', 'Defektes Produkt - Shopware Demoshop\r\n\r\nFirma: {sVars.firma}\r\nKundennummer: {sVars.kdnr}\r\neMail: {sVars.email}\r\n\r\nRechnungsnummer: {sVars.rechnung}\r\nArtikelnummer: {sVars.artikel}\r\n\r\nDetaillierte Fehlerbeschreibung:\r\n--------------------------------\r\n{sVars.fehler}\r\n\r\nRechner: {sVars.rechner}\r\nSystem {sVars.system}\r\nWie tritt das Problem auf: {sVars.wie}\r\n', 'Online-Serviceformular', '<p>Formular erfolgreich versandt!</p>', 2, 'de'),
(16, 'Anfrage-Formular', '<p>Schreiben Sie uns eine eMail.</p>\r\n<p>Wir freuen uns auf Ihre Kontaktaufnahme.</p>', 'info@example.com', 'Anfrage-Formular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nFrage: \r\n{sVars.inquiry}\r\n\r\n\r\n', 'Anfrage-Formular Shopware', '<p>Ihre Anfrage wurde versendet!</p>', 0, 'de'),
(17, 'Partner form', '<h2><strong>Become partner and earn money!</strong></h2>\r\n<p>Link our Site and receive&nbsp;an attractive commission on the net contract price&nbsp;for every tornover of your&nbsp;provided customers.</p>\r\n<p>Please fill out the partner form <span style="text-decoration: underline;">without obligation</span>.&nbsp;We will immediately get in contact with you!</p>', 'info@example.com', 'Partner inquiry - {$sShopname}\r\n{sVars.firma} want to become your partner!\r\n\r\nCompany: {sVars.firma}\r\nContact person: {sVars.ansprechpartner}\r\nStreet / No.: {sVars.strasse}\r\nPostal Code / City: {sVars.plz} {sVars.ort}\r\neMail: {sVars.email}\r\nPhone: {sVars.tel}\r\nFax: {sVars.fax}\r\nWebsite: {sVars.webseite}\r\nSubject: {sVars.betreff}\r\n\r\nComment: \r\n{sVars.kommentar}\r\n\r\nProfile:\r\n{sVars.profil}', 'Partner inquiry', '<p>&nbsp;</p>\r\n&nbsp;\r\n<div id="result_box" dir="ltr">The request has been sent!</div>', 0, 'de'),
(18, 'Contact', '', 'info@example.com', 'Contact form Shopware Demoshop\r\n\r\nTitle: {sVars.anrede}\r\nFirst name: {sVars.vorname}\r\nLast name: {sVars.nachname}\r\neMail: {sVars.email}\r\nPhone: {sVars.telefon}\r\nSubject: {sVars.betreff}\r\nComment: \r\n{sVars.kommentar}\r\n\r\n\r\n', 'Contact form Shopware', '<p>Your form was sent!</p>', 0, 'de'),
(19, 'Defective product', '<p>&nbsp;</p>\r\n&nbsp;\r\n<h1>Defective product - for customers and traders</h1>\r\n<p>You will receive an answer&nbsp;from us&nbsp;with an RMA number an other approach&nbsp;after sending this form.&nbsp;</p>\r\n<p>Please fill out the error description, so you must not add this any more to the package.</p>', 'info@example.com', 'INSERT INTO s_user_service\r\n(clientnumber, email, billingnumber, articles, description, description2, description3,\r\ndescription4,date,type)\r\nVALUES (\r\n			''{$kdnr}'',\r\n			''{$email}'',\r\n			''{$rechnung}'',\r\n			''{$artikel}'',\r\n			''{$fehler}'',\r\n			''{$rechner}'',\r\n			''{$system}'',\r\n			''{$wie}'',\r\n			''{$date}'',\r\n1\r\n		)', 'Online-Serviceform', '<p>Form successfully sent!</p>', 0, 'de'),
(20, 'Return', '<h2>Here you can write information about the return...</h2>', 'info@example.com', 'INSERT INTO s_user_service\r\n(clientnumber, email, billingnumber, articles, description, description2, description3,\r\ndescription4,date,type)\r\nVALUES (\r\n			''{$kdnr}'',\r\n			''{$email}'',\r\n			''{$rechnung}'',\r\n			''{$artikel}'',\r\n			''{$info}'',\r\n			'''',\r\n			'''',\r\n			'''',\r\n			''{$date}'',\r\n2\r\n		)\r\n', 'Return', '<p>Form successfully sent.</p>', 0, 'de'),
(21, 'Inquiry form', '<p>Send us an email.&nbsp;<br /><br />We look forward to hearing from you.</p>', 'info@example.com', 'Anfrage-Formular Shopware Demoshop\r\n\r\nAnrede: {sVars.anrede}\r\nVorname: {sVars.vorname}\r\nNachname: {sVars.nachname}\r\neMail: {sVars.email}\r\nTelefon: {sVars.telefon}\r\nFrage: \r\n{sVars.inquiry}\r\n\r\n\r\n', 'Inquiry form Shopware', '<p>Your request has been sent!</p>', 0, 'de'),
(22, 'Support beantragen', '<p>Wir freuen uns &uuml;ber Ihre Kontaktaufnahme.</p>', 'info@example.com', '', 'Support beantragen', '<p>Vielen Dank f&uuml;r Ihre Anfrage!</p>', 1, 'de');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_cms_support_fields`
--

CREATE TABLE IF NOT EXISTS `s_cms_support_fields` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `error_msg` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `typ` varchar(255) NOT NULL,
  `required` int(1) NOT NULL,
  `supportID` int(11) NOT NULL,
  `label` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `vtyp` varchar(255) NOT NULL,
  `added` datetime NOT NULL,
  `position` int(11) NOT NULL,
  `ticket_task` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`supportID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=117 ;

--
-- Daten für Tabelle `s_cms_support_fields`
--

INSERT INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `vtyp`, `added`, `position`, `ticket_task`) VALUES
(40, '', 'kommentar', '', 'textarea', 1, 5, 'Kommentar', 'normal', '', '', '2007-11-06 03:19:08', 0, 'message'),
(39, '', 'betreff', '', 'text', 1, 5, 'Betreff', 'normal', '', '', '2007-11-06 03:18:57', 0, 'subject'),
(44, '', 'plz;ort', '', 'text2', 1, 8, 'PLZ / Ort', 'plz;ort', '', '', '2007-11-22 08:12:59', 0, ''),
(43, '', 'strasse', '', 'text2', 1, 8, 'Straße / Hausnummer', 'strasse;nr', '', '', '2007-11-22 08:12:49', 0, ''),
(42, '', 'ansprechpartner', '', 'text', 1, 8, 'Ansprechpartner', 'normal', '', '', '2007-11-22 08:12:18', 0, ''),
(12, '', 'sdfg', '', '', 0, 0, 'sdf', '', '', '', '0000-00-00 00:00:00', 0, ''),
(36, '', 'nachname', '', 'text', 1, 5, 'Nachname', 'normal', '', '', '2007-11-06 03:17:57', 0, 'name'),
(41, '', 'firma', '', 'text', 1, 8, 'Firma', 'normal', '', '', '2007-11-22 08:11:39', 0, ''),
(24, '', 'anrede', '', 'select', 1, 5, 'Anrede', 'normal', 'Frau;Herr', '', '2007-11-02 03:28:48', 0, ''),
(38, '', 'telefon', '', 'text', 0, 5, 'Telefon', 'normal', '', '', '2007-11-06 03:18:49', 0, ''),
(37, '', 'email', '', 'email', 1, 5, 'eMail-Adresse', 'normal', '', '', '2007-11-06 03:18:36', 0, 'email'),
(35, '', 'vorname', '', 'text', 1, 5, 'Vorname', 'normal', '', '', '2007-11-06 03:17:48', 0, ''),
(45, '', 'tel', '', 'text', 1, 8, 'Telefon', 'normal', '', '', '2007-11-22 08:13:45', 0, ''),
(46, '', 'fax', '', 'text', 0, 8, 'Fax', 'normal', '', '', '2007-11-22 08:13:52', 0, ''),
(47, '', 'email', '', 'text', 1, 8, 'eMail', 'normal', '', '', '2007-11-22 08:13:58', 0, ''),
(48, '', 'website', '', 'text', 1, 8, 'Webseite', 'normal', '', '', '2007-11-22 08:14:07', 0, ''),
(49, '', 'kommentar', '', 'textarea', 0, 8, 'Kommentar', 'normal', '', '', '2007-11-22 08:14:21', 0, ''),
(50, '', 'profil', '', 'textarea', 1, 8, 'Firmenprofil', 'normal', '', '', '2007-11-22 08:14:34', 0, ''),
(51, '', 'rechnung', '', 'text', 1, 9, 'Rechnungsnummer', 'normal', '', '', '2007-11-06 17:21:49', 0, ''),
(52, '', 'email', '', 'text', 1, 9, 'eMail-Adresse', 'normal', '', '', '2007-11-06 17:19:20', 0, 'email'),
(53, '', 'kdnr', '', 'text', 1, 9, 'KdNr.(siehe Rechnung)', 'normal', '', '', '2007-11-06 17:19:10', 0, 'name'),
(54, '', 'firma', '', 'checkbox', 0, 9, 'Firma (Wenn ja, bitte ankreuzen)', '', '1', '', '2007-11-06 17:18:36', 0, ''),
(55, '', 'artikel', '', 'textarea', 1, 9, 'Artikelnummer(n)', 'normal', '', '', '2007-11-06 17:22:13', 0, 'subject'),
(56, '', 'fehler', '', 'textarea', 1, 9, 'Detaillierte Fehlerbeschreibung', 'normal', '', '', '2007-11-06 17:22:33', 0, 'message'),
(57, '', 'rechner', '', 'textarea', 0, 9, 'Auf welchem Rechnertypen läuft das defekte Produkt?', 'normal', '', '', '2007-11-06 17:23:17', 0, ''),
(58, '', 'system', '', 'textarea', 0, 9, 'Mit welchem Betriebssystem arbeiten Sie?', 'normal', '', '', '2007-11-06 17:23:57', 0, ''),
(59, '', 'wie', '', 'select', 1, 9, 'Wie tritt das Problem auf?', 'normal', 'sporadisch; ständig', '', '2007-11-06 17:24:26', 0, ''),
(69, '', 'inquiry', '', 'textarea', 1, 16, 'Anfrage', 'normal', '', '', '2007-11-06 03:19:08', 0, ''),
(76, '', 'firma', '', 'text', 1, 17, 'Company', 'normal', '', '', '2008-10-17 13:02:42', 0, ''),
(71, '', 'nachname', '', 'text', 1, 16, 'Nachname', 'normal', '', '', '2007-11-06 03:17:57', 0, ''),
(72, '', 'anrede', '', 'select', 1, 16, 'Anrede', 'normal', 'Frau;Herr', '', '2007-11-02 03:28:48', 0, ''),
(73, '', 'telefon', '', 'text', 0, 16, 'Telefon', 'normal', '', '', '2007-11-06 03:18:49', 0, ''),
(74, '', 'email', '', 'text', 1, 16, 'eMail-Adresse', 'normal', '', '', '2007-11-06 03:18:36', 0, ''),
(75, '', 'vorname', '', 'text', 1, 16, 'Vorname', 'normal', '', '', '2007-11-06 03:17:48', 0, ''),
(77, '', 'ansprechpartner', '', 'text', 1, 17, 'Contact person', 'normal', '', '', '2008-10-17 13:03:35', 0, ''),
(78, '', 'strasse', '', 'text2', 1, 17, 'Street / house number', 'strasse;nr', '', '', '2008-10-17 13:05:55', 0, ''),
(79, '', 'plz;ort', '', 'text2', 1, 17, 'Postal Code / City', 'plz;ort', '', '', '2008-10-17 13:06:23', 0, ''),
(80, '', 'tel', '', 'text', 1, 17, 'Phone', 'normal', '', '', '2008-10-17 13:06:35', 0, ''),
(81, '', 'fax', '', 'text', 0, 17, 'Fax', 'normal', '', '', '2008-10-17 13:06:48', 0, ''),
(82, '', 'email', '', 'text', 1, 17, 'eMail', 'normal', '', '', '2008-10-17 13:07:06', 0, ''),
(83, '', 'website', '', 'text', 1, 17, 'Website', 'normal', '', '', '2008-10-17 13:07:14', 0, ''),
(84, '', 'kommentar', '', 'textarea', 0, 17, 'Comment', 'normal', '', '', '2008-10-17 13:07:25', 0, ''),
(85, '', 'profil', '', 'textarea', 1, 17, 'Company profile', 'normal', '', '', '2008-10-17 13:07:43', 0, ''),
(86, '', 'anrede', '', 'select', 1, 18, 'Title', 'normal', 'Ms;Mr', '', '2008-10-17 13:21:07', 0, ''),
(87, '', 'vorname', '', 'text', 1, 18, 'First name', 'normal', '', '', '2008-10-17 13:21:41', 0, ''),
(88, '', 'nachname', '', 'text', 1, 18, 'Last name', 'normal', '', '', '2008-10-17 13:22:01', 0, ''),
(89, '', 'email', '', 'text', 1, 18, 'eMail-Adress', 'normal', '', '', '2008-10-17 13:22:18', 0, ''),
(90, '', 'telefon', '', 'text', 0, 18, 'Phone', 'normal', '', '', '2008-10-17 13:22:28', 0, ''),
(91, '', 'betreff', '', 'text', 1, 18, 'Subject', 'normal', '', '', '2008-10-17 13:22:38', 0, ''),
(92, '', 'kommentar', '', 'textarea', 1, 18, 'Comment', 'normal', '', '', '2008-10-17 13:22:45', 0, ''),
(93, '', 'firma', '', 'checkbox', 0, 19, 'Company (If so, please mark)', '', '1', '', '2008-10-17 13:45:44', 0, ''),
(94, '', 'kdnr', '', 'text', 1, 19, 'Customer no. (See invoice)', 'normal', '', '', '2008-10-17 13:46:04', 0, ''),
(95, '', 'email', '', 'text', 1, 19, 'eMail-Adress', 'normal', '', '', '2008-10-17 13:46:27', 0, ''),
(96, '', 'rechnung', '', 'text', 1, 19, 'Invoice number', 'normal', '', '', '2008-10-17 13:47:03', 0, ''),
(102, '', 'kdnr', '', 'text', 1, 20, 'Customer no. (See invoice)', 'normal', '', '', '2008-10-17 14:21:28', 1, ''),
(97, '', 'artikel', '', 'textarea', 1, 19, 'Articlenumber(s)', 'normal', '', '', '2008-10-17 13:47:43', 0, ''),
(98, '', 'fehler', '', 'textarea', 1, 19, 'Detailed error description', 'normal', '', '', '2008-10-17 13:48:54', 0, ''),
(99, '', 'rechner', '', 'textarea', 0, 19, 'On which computer type does the defective product run?', 'normal', '', '', '2008-10-17 14:02:03', 0, ''),
(100, '', 'system', '', 'textarea', 0, 19, 'With which operating system do you work?', 'normal', '', '', '2008-10-17 14:02:36', 0, ''),
(101, '', 'wie', '', 'select', 1, 19, 'How doeas the problem occur?', 'normal', 'sporadically;permanently', '', '2008-10-17 14:02:55', 0, ''),
(103, '', 'email', '', 'text', 1, 20, 'eMail-Adress', 'normal', '', '', '2008-10-17 14:22:12', 2, ''),
(104, '', 'rechnung', '', 'text', 1, 20, 'Invoice number', 'normal', '', '', '2008-10-17 14:22:43', 3, ''),
(105, '', 'artikel', '', 'textarea', 1, 20, 'Articlenumber(s)', 'normal', '', '', '2008-10-17 14:23:15', 4, ''),
(106, '', 'info', '', 'textarea', 0, 20, 'Comment', 'normal', '', '', '2008-10-17 14:23:37', 5, ''),
(107, '', 'anrede', '', 'select', 1, 21, 'Title', 'normal', 'Ms;Mr', '', '2008-10-17 14:45:21', 0, ''),
(108, '', 'vorname', '', 'text', 1, 21, 'First name', 'normal', '', '', '2008-10-17 14:46:11', 0, ''),
(109, '', 'nachname', '', 'text', 1, 21, 'Last name', 'normal', '', '', '2008-10-17 14:46:31', 0, ''),
(110, '', 'email', '', 'text', 1, 21, 'eMail-Adress', 'normal', '', '', '2008-10-17 14:46:49', 0, ''),
(111, '', 'telefon', '', 'text', 0, 21, 'Phone', 'normal', '', '', '2008-10-17 14:47:00', 0, ''),
(112, '', 'inquiry', '', 'textarea', 1, 21, 'Inquiry', 'normal', '', '', '2008-10-17 14:47:25', 0, ''),
(113, '', 'name', '', 'text', 1, 22, 'Name', 'normal', '', '', '2009-04-15 22:20:30', 0, 'name'),
(114, '', 'email', '', 'email', 1, 22, 'eMail', 'normal', '', '', '2009-04-15 22:20:37', 0, 'email'),
(115, '', 'betreff', '', 'text', 1, 22, 'Betreff', 'normal', '', '', '2009-04-15 22:20:45', 0, 'subject'),
(116, '', 'kommentar', '', 'textarea', 1, 22, 'Kommentar', 'normal', '', '', '2009-04-15 22:21:07', 0, 'message');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_auth`
--

CREATE TABLE IF NOT EXISTS `s_core_auth` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(60) NOT NULL,
  `sessionID` varchar(50) NOT NULL,
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `name` varchar(255) NOT NULL,
  `email` varchar(120) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `sidebar` int(1) NOT NULL DEFAULT '0',
  `window_height` int(11) NOT NULL,
  `window_width` int(11) NOT NULL,
  `window_size` text NOT NULL,
  `admin` int(1) NOT NULL,
  `rights` text NOT NULL,
  `salted` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

--
-- Daten für Tabelle `s_core_auth`
--

INSERT INTO `s_core_auth` (`id`, `username`, `password`, `sessionID`, `lastlogin`, `name`, `email`, `active`, `sidebar`, `window_height`, `window_width`, `window_size`, `admin`, `rights`, `salted`) VALUES
(48, 'demo', '84c2ef7bb215395c80119636233765f0', 'u2ltjkge2d7bo49llh177j3hq6', '2010-10-18 10:41:06', 'Administrator', 'info@shopware.ag', 1, 1, 0, 0, 'a:25:{s:4:"live";a:1:{i:1920;a:2:{s:6:"height";i:630;s:5:"width";i:1775;}}s:8:"articles";a:1:{i:1920;a:2:{s:6:"height";i:610;s:5:"width";i:1115;}}s:5:"start";a:1:{i:1920;a:2:{s:6:"height";i:735;s:5:"width";i:1455;}}s:3:"rss";a:1:{i:1920;a:2:{s:6:"height";i:455;s:5:"width";i:1060;}}s:13:"ticket_system";a:1:{i:1920;a:2:{s:6:"height";i:680;s:5:"width";i:1355;}}s:9:"cmsstatic";a:1:{i:1920;a:2:{s:6:"height";i:460;s:5:"width";i:780;}}s:10:"presetting";a:1:{i:1920;a:2:{s:6:"height";i:845;s:5:"width";i:1095;}}s:9:"orderlist";a:1:{i:1920;a:2:{s:6:"height";i:695;s:5:"width";i:1485;}}s:6:"import";a:1:{i:1920;a:2:{s:6:"height";i:665;s:5:"width";i:1540;}}s:5:"cache";a:1:{i:1920;a:2:{s:6:"height";i:635;s:5:"width";i:1897;}}s:7:"snippet";a:1:{i:1920;a:2:{s:6:"height";i:665;s:5:"width";i:1897;}}s:13:"mailcampaigns";a:1:{i:1920;a:2:{s:6:"height";i:650;s:5:"width";i:930;}}s:7:"useradd";a:1:{i:1920;a:2:{s:6:"height";i:410;s:5:"width";i:985;}}s:7:"support";a:1:{i:1680;a:2:{s:6:"height";i:520;s:5:"width";i:1260;}}s:11:"userdetails";a:1:{i:1920;a:2:{s:6:"height";i:450;s:5:"width";i:1905;}}s:8:"activate";a:1:{i:1920;a:2:{s:6:"height";i:295;s:5:"width";i:485;}}s:8:"vouchers";a:1:{i:1920;a:2:{s:6:"height";i:640;s:5:"width";i:800;}}s:4:"auth";a:1:{i:1920;a:2:{s:6:"height";i:530;s:5:"width";i:1040;}}s:12:"CouponsAdmin";a:1:{i:1920;a:2:{s:6:"height";i:685;s:5:"width";i:1897;}}s:12:"articlesfast";a:1:{i:1920;a:2:{s:6:"height";i:675;s:5:"width";i:1630;}}s:8:"shipping";a:1:{i:1920;a:2:{s:6:"height";i:600;s:5:"width";i:1290;}}s:6:"plugin";a:1:{i:1920;a:2:{s:6:"height";i:670;s:5:"width";i:1555;}}s:7:"account";a:1:{i:1920;a:2:{s:6:"height";i:840;s:5:"width";i:1340;}}s:7:"license";a:1:{i:1920;a:2:{s:6:"height";i:400;s:5:"width";i:590;}}s:19:"RecommendationAdmin";a:1:{i:1920;a:2:{s:6:"height";i:655;s:5:"width";i:1050;}}}', 1, '', 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_auth_files`
--

CREATE TABLE IF NOT EXISTS `s_core_auth_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `modID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_auth_files`
--

INSERT INTO `s_core_auth_files` (`id`, `userID`, `modID`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_checklist`
--

CREATE TABLE IF NOT EXISTS `s_core_checklist` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` int(11) NOT NULL,
  `checked` int(1) NOT NULL,
  `winurl` varchar(255) NOT NULL,
  `skeleton` varchar(255) NOT NULL,
  `attr1` varchar(255) NOT NULL,
  `area` varchar(255) NOT NULL,
  `subarea` varchar(255) NOT NULL,
  `option` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `paymentmean` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

--
-- Daten für Tabelle `s_core_checklist`
--

INSERT INTO `s_core_checklist` (`id`, `type`, `checked`, `winurl`, `skeleton`, `attr1`, `area`, `subarea`, `option`, `module`, `paymentmean`) VALUES
(1, 1, 0, '../presetting/settingsdetail.php?id=6', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > System', 'Ihre Shopware-Konto-ID', '', ''),
(2, 1, 0, '../presetting/settingsdetail.php?id=6', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > System', 'Shopbetreiber eMail', '', ''),
(3, 1, 0, '../presetting/documents.php', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > PDF-Belegerstellung', 'Allgemeine Überprüfung', '', ''),
(4, 2, 0, '', '', 'sOrderInfo', 'Textbausteine', '', 'sOrderInfo', '', ''),
(5, 2, 0, '', '', 'sContact_right', 'Textbausteine', '', 'Kontaktdaten', '', ''),
(6, 2, 0, '', '', 'sBankContact', 'Textbausteine', '', 'Bankverbindung', '', ''),
(7, 2, 0, '', '', 'sAGBTextPaymentform', 'Textbausteine', '', 'AGB / Zahlungsschnittstellen', '', ''),
(8, 2, 0, '', '', 'sDelivery1', 'Textbausteine', '', 'Lieferzeit', '', ''),
(9, 2, 0, '', '', 'sRegistersendusyourtradeproofb', 'Textbausteine', '', 'Händlerinformation', '', ''),
(10, 2, 0, '', '', 'sBasketNoDispatches', 'Textbausteine', '', 'Bestellfehler', '', ''),
(11, 1, 0, '../presetting/orderstatemail.php?artID=11', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Zahlstatus > Teilweise verschickt', '', ''),
(12, 1, 0, '../presetting/orderstatemail.php?artID=1', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > In Bearbeitung', '', ''),
(13, 1, 0, '../presetting/orderstatemail.php?artID=2', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > Komplett abgeschlossen', '', ''),
(14, 1, 0, '../presetting/orderstatemail.php?artID=4', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > Storniert / Abgelehnt', '', ''),
(15, 1, 0, '../presetting/orderstatemail.php?artID=5', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > Zur Lieferung bereit', '', ''),
(16, 1, 0, '../presetting/orderstatemail.php?artID=6', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > Teilweise ausgeliefert', '', ''),
(17, 1, 0, '../presetting/orderstatemail.php?artID=8', '', '', 'Grundeinstellungen', 'System / Basis-Konfiguration > Status-eMails', 'Bestellstatus > Klärung notwendig', '', ''),
(18, 0, 0, '../checklistopt/form_receiver.php', '', '', 'Formulare', '', 'eMail-Adressen (Empfänger)', '', ''),
(19, 0, 0, '../ticket_system/mail_settings.php', '', '', 'Ticket System', 'eMail-Vorlagen', 'Allgemeine Überprüfung', 'sTICKET', ''),
(20, 0, 0, '../mails/index.php', '', '', 'Standard eMail-Vorlagen', '', 'Allgemeine Überprüfung', '', ''),
(21, 1, 0, '../presetting/settingsdetail.php?id=92', '', '', 'Grundeinstellungen', 'Schnittstellen > Gate2shop', 'Überprüfung der Einstellungen', '', 'gate2shop'),
(22, 1, 0, '../../connectors/heidelpay/config.php', '', '', 'Grundeinstellungen', 'Schnittstellen > Heidelpay (Kreditkarte)', 'Überprüfung der Einstellungen', '', 'heidelpay_cc'),
(23, 1, 0, '../../connectors/heidelpay/config.php', '', '', 'Grundeinstellungen', 'Schnittstellen > Heidelpay (Debitkarte)', 'Überprüfung der Einstellungen', '', 'heidelpay_dc'),
(24, 1, 0, '../../connectors/heidelpay/config.php', '', '', 'Grundeinstellungen', 'Schnittstellen > Heidelpay (Onlineüberweisung)', 'Überprüfung der Einstellungen', '', 'heidelpay_ot'),
(25, 1, 0, '../../connectors/heidelpay/config.php', '', '', 'Grundeinstellungen', 'Schnittstellen > Heidelpay (Lastschrift)', 'Überprüfung der Einstellungen', '', 'heidelpay_dd'),
(26, 1, 0, '../../connectors/heidelpay/config.php', '', '', 'Grundeinstellungen', 'Schnittstellen > Heidelpay (Paypal)', 'Überprüfung der Einstellungen', '', 'heidelpay_va'),
(27, 1, 0, '../presetting/settingsdetail.php?id=96', '', '', 'Grundeinstellungen', 'Schnittstellen > Hanseatic Finanzierung', 'Überprüfung der Einstellungen', '', 'hanseatic'),
(28, 1, 0, '../presetting/settingsdetail.php?id=89', '', '', 'Grundeinstellungen', 'Schnittstellen > Saferpay', 'Überprüfung der Einstellungen', '', 'Saferpay'),
(29, 1, 0, '../presetting/settingsdetail.php?id=58', '', '', 'Grundeinstellungen', 'Schnittstellen > PayPal', 'Überprüfung der Einstellungen', '', 'paypal'),
(30, 1, 0, '../presetting/settingsdetail.php?id=88', '', '', 'Grundeinstellungen', 'Schnittstellen > PayPal Express', 'Überprüfung der Einstellungen', '', 'paypalexpress'),
(31, 1, 0, '../presetting/settingsdetail.php?id=66', '', '', 'Grundeinstellungen', 'Schnittstellen > Sofortüberweisung.de', 'Überprüfung der Einstellungen', '', 'sofortueberweisung'),
(32, 1, 0, '../presetting/settingsdetail.php?id=65', '', '', 'Grundeinstellungen', 'Schnittstellen > iPayment', 'Überprüfung der Einstellungen', '', 'ipayment'),
(33, 1, 0, '../presetting/settingsdetail.php?id=87', '', '', 'Grundeinstellungen', 'Schnittstellen > ClickandBuy', 'Überprüfung der Einstellungen', '', 'ClickandBuy'),
(34, 1, 0, '../presetting/settingsdetail.php?id=57', '', '', 'Grundeinstellungen', 'Schnittstellen > United Transfer (Direkt Vorkasse)', 'Überprüfung der Einstellungen', '', 'uos_ut_vk'),
(35, 1, 0, '../presetting/settingsdetail.php?id=57', '', '', 'Grundeinstellungen', 'Schnittstellen > United Transfer (Direkt Lastschrift)', 'Überprüfung der Einstellungen', '', 'uos_ut_ls'),
(36, 1, 0, '../presetting/settingsdetail.php?id=57', '', '', 'Grundeinstellungen', 'Schnittstellen > United Transfer (Direkt Kreditkarte)', 'Überprüfung der Einstellungen', '', 'uos_ut_kk'),
(37, 1, 0, '../presetting/settingsdetail.php?id=57', '', '', 'Grundeinstellungen', 'Schnittstellen > United Transfer (Direkt Giropay)', 'Überprüfung der Einstellungen', '', 'uos_ut_gp'),
(38, 1, 0, '../presetting/settingsdetail.php?id=57', '', '', 'Grundeinstellungen', 'Schnittstellen > United Transfer', 'Überprüfung der Einstellungen', '', 'uos_ut'),
(39, 1, 0, '../presetting/settingsdetail.php?id=37', '', '', 'Grundeinstellungen', 'Schnittstellen > Vorkasse UOS', 'Überprüfung der Einstellungen', '', 'prepaiduos'),
(40, 1, 0, '../presetting/settingsdetail.php?id=37', '', '', 'Grundeinstellungen', 'Schnittstellen > Rechnung UOS', 'Überprüfung der Einstellungen', '', 'invoiceuos'),
(41, 1, 0, '../presetting/settingsdetail.php?id=37', '', '', 'Grundeinstellungen', 'Schnittstellen > Giropay', 'Überprüfung der Einstellungen', '', 'giropayuos'),
(42, 1, 0, '../presetting/settingsdetail.php?id=37', '', '', 'Grundeinstellungen', 'Schnittstellen > Kreditkarte UOS', 'Überprüfung der Einstellungen', '', 'credituos'),
(43, 1, 0, '../presetting/settingsdetail.php?id=37', '', '', 'Grundeinstellungen', 'Schnittstellen > Lastschrift UOS', 'Überprüfung der Einstellungen', '', 'debituos'),
(44, 1, 0, '', 'shipping', '', 'Versandkosten', '', 'Überprüfung der Einstellungen', '', ''),
(45, 2, 0, '', '', 'sIndexMetaAuthor', 'Textbausteine', '', 'Meta-Tag Author', '', ''),
(46, 2, 0, '', '', 'sIndexMetaDescriptionStandard', 'Textbausteine', '', 'Meta-Tag Description', '', ''),
(47, 2, 0, '', '', 'sIndexMetaCopyright', 'Textbausteine', '', 'Meta-Tag Copyright', '', ''),
(48, 2, 0, '', '', 'sIndexMetaKeywordsStandard', 'Textbausteine', '', 'Meta-Tag Keywords Standard', '', ''),
(49, 2, 0, '', '', 'sIndexMetaRobots', 'Textbausteine', '', 'Meta-Tag Robots', '', ''),
(50, 2, 0, '', '', 'sIndexMetaRevisit', 'Textbausteine', '', 'Meta-Tag Revisit', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config`
--

CREATE TABLE IF NOT EXISTS `s_core_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `value` text NOT NULL,
  `description` varchar(255) NOT NULL,
  `required` int(1) NOT NULL,
  `warning` int(1) NOT NULL,
  `detailtext` varchar(255) NOT NULL,
  `multilanguage` int(11) NOT NULL,
  `fieldtype` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=445 ;

--
-- Daten für Tabelle `s_core_config`
--

INSERT INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(4, 6, 'sHOST', '', 'Shophost', 1, 1, '', 0, ''),
(5, 0, 'sSESSIONBYDB', '1', 'Sessions in DB verwalten', 0, 0, '', 0, ''),
(6, 14, 'sTEMPLATEPATH', 'templates/orange', 'Pfad zum Template', 0, 0, '', 0, ''),
(7, 6, 'sBASEFILE', 'shopware.php', 'Shopkernel', 1, 1, '', 0, ''),
(9, 6, 'sBASEPATH', '', 'Installationspfad', 1, 1, '', 0, ''),
(10, 14, 'sARTICLEIMAGES', '/images/articles', 'Pfad Artikelbilder', 1, 0, '', 0, ''),
(12, 30, 'sARTICLESPERPAGE', '12', 'Artikel pro Seite', 0, 0, '', 0, ''),
(13, 30, 'sORDERBYDEFAULT', 'a.datum DESC', 'Standardsortierung Listings', 0, 0, '', 0, ''),
(14, 12, 'sCACHECATEGORY', '86400', 'Kategorien Pufferzeit', 0, 0, '', 0, ''),
(15, 30, 'sMAXPAGES', '8', 'Kategorien max. Anzahl Seiten', 0, 0, '', 0, ''),
(16, 12, 'sCACHEPRICES', '86400', 'Preise Pufferzeit', 0, 0, '', 0, ''),
(17, 76, 'sMARKASNEW', '30', 'Artikel als neu markieren (Tage)', 0, 0, '', 0, ''),
(18, 76, 'sMARKASTOPSELLER', '30', 'Artikel als Topseller markieren (Verkäufe)', 0, 0, '', 0, ''),
(19, 76, 'sCHARTRANGE', '10', 'Anzahl Topseller für Charts', 0, 0, '', 0, ''),
(20, 12, 'sCACHECHART', '86400', 'Topseller Pufferzeit', 0, 0, '', 0, ''),
(21, 30, 'sNUMBERARTICLESTOSHOW', '12|24|36|48', 'Auswahl Artikel pro Seite', 0, 0, '', 0, ''),
(22, 6, 'sSHOPNAME', 'Shopware 3.5 Demo', 'Name des Shops', 0, 0, '', 1, ''),
(23, 12, 'sCACHESUPPLIER', '86400', 'Hersteller Pufferzeit', 0, 0, '', 0, ''),
(24, 14, 'sSUPPLIERIMAGES', '/images/supplier', 'Pfad Herstellerlogos', 0, 0, '', 0, ''),
(25, 80, 'sSHOWLASTARTICLES', '1', 'Artikelverlauf anzeigen (zuletzt angeschaut)', 0, 0, '', 0, 'int'),
(26, 80, 'sLASTARTICLESTOSHOW', '5', 'Anzahl Artikel in Verlauf (zuletzt angeschaut)', 0, 0, '', 0, ''),
(27, 12, 'sCACHEARTICLE', '86400', 'Artikeldetailseite Pufferzeit', 0, 0, '', 0, ''),
(29, 0, 'sCATEGORYPARENT', '1', 'ID Stammkategorie', 0, 0, '', 0, ''),
(210, 79, 'sINQUIRYVALUE', '150', 'Mind. Warenkorbwert ab dem die Möglichkeit der individuellen Anfrage angeboten wird', 0, 0, '', 0, ''),
(31, 0, 'sTHUMBBASKET', '1', 'Artikelbilder in Warenkorb anzeigen', 0, 0, '', 0, ''),
(32, 0, 'sTINYMCEOPTIONS', 'cleanup : true, language: ''de'',skin : ''o2k7'',skin_variant : ''silver'', convert_urls : false, \r\nmedia_strict : false,\r\nfullscreen_new_window: true, relative_urls : false, width: "100%", invalid_elements:''script,applet,iframe'', theme_advanced_resizng : true, theme_advanced_toolbar_location : ''top'', theme_advanced_toolbar_align : ''left'', theme_advanced_path_location : ''bottom'', theme_advanced_resizing : true, extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID|value],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]"', 'Globale Optionen für den TinyMce-Editor', 0, 0, '', 0, ''),
(33, 33, 'sMINPASSWORD', '8', 'Mindestlänge Passwort (Registrierung)', 0, 0, '', 0, ''),
(34, 12, 'sCACHECOUNTRIES', '86400', 'Länderliste Pufferzeit', 0, 0, '', 0, ''),
(35, 33, 'sDEFAULTPAYMENT', '5', 'Standardzahlungsart (Id) (Registrierung)', 0, 0, '', 0, ''),
(36, 31, 'sIMAGESIZES', '30:30:0;57:57:1;105:105:2;140:140:3;285:255:4;720:600:5', 'Thumbnails', 0, 0, '', 0, 'textarea'),
(37, 31, 'sLASTARTICLESTHUMB', '1', 'Thumbnail-Nr. der zuletzt angeschauten Artikel', 0, 0, '', 0, ''),
(38, 0, 'sORDERTABLE', '0:Position:2:FORMAT_LEFT; 1:Bestellnr:15:FORMAT_LEFT;2:Artikel:30:FORMAT_LEFT; 3:Einzelpreis:8:FORMAT_RIGHT; 4:Menge:3:FORMAT_LEFT; 5:Gesamtpreis:9:FORMAT_RIGHT;6:Lieferzeit:9:FORMAT_RIGHT', 'Datenfelder Bestellbestätigung (Sonstiges Allgemein)', 0, 0, '', 1, 'textarea'),
(39, 0, 'sCURRENCY', 'EUR', 'Währungssymbol', 0, 0, '', 0, ''),
(40, 12, 'sCACHESEARCH', '86400', 'Cache Suche', 0, 0, '', 0, ''),
(41, 14, 'sBANNER', '/images/ayww', 'Pfad Banner', 0, 0, '', 0, ''),
(43, 35, 'sVOUCHERTAX', '19', 'MwSt. Gutscheine', 0, 0, '', 0, ''),
(44, 71, 'sVOUCHERNAME', 'Gutschein', 'Gutscheine Bezeichnung', 0, 0, '', 1, ''),
(46, 35, 'sTAXSHIPPING', '19', 'MwSt. Versandkosten', 0, 0, '', 0, ''),
(47, 6, 'sMAIL', 'info@example.com', 'Shopbetreiber eMail', 0, 0, '', 0, ''),
(48, 14, 'sCMSIMAGES', '/images/cms', 'Pfad CMS-Bilder', 0, 0, '', 0, ''),
(49, 14, 'sCMSFILES', '/files/cms', 'Pfad CMS-Dateien', 0, 0, '', 0, ''),
(51, 14, 'sARTICLEFILES', '/files/downloads', 'Pfad Artikel-Downloads', 0, 0, '', 0, ''),
(56, 40, 'sESDKEY', '552211cce724117c3178e3d22bec532ec', 'Secret-Key', 0, 0, '', 0, ''),
(58, 39, 'sSHOWCLOUD', '0', 'Tagwolke anzeigen', 0, 0, '', 0, 'int'),
(59, 39, 'sTAGCLOUDCLASS', 'tag', 'Name der Tag-Klasse', 0, 0, '', 0, ''),
(60, 39, 'sTAGCLOUDDEFAULTCLASS', '0', 'Name der Standardklasse', 0, 0, '', 0, ''),
(61, 39, 'sTAGCLOUDMAX', '46', 'Maximale Anzahl Begriffe', 0, 0, '', 0, ''),
(62, 39, 'sTAGCLOUDSPLIT', '3', 'Anzahl der Stufen', 0, 0, '', 0, ''),
(63, 42, 'sMINSEARCHLENGHT', '3', 'Minimale Suchwortlänge', 0, 0, '', 0, ''),
(64, 0, 'sBOOKINGSECRETKEY', 'AMYMSKYG8282BVN123NY', 'Shopware Authentifizierung', 0, 0, '', 0, ''),
(66, 84, 'sCONTENTPERPAGE', '6', 'Anzahl Feeds pro Seite', 0, 0, '', 0, ''),
(67, 33, 'sNEWSLETTERDEFAULTGROUP', '1', 'Standard-Empfangsgruppe (ID) für registrierte Kunden (System / Newsletter)', 0, 0, '', 1, ''),
(74, 42, 'sFUZZYSEARCHEXACTMATCHFACTOR', '100', 'Faktor für genaue Treffer', 0, 0, '', 0, ''),
(75, 42, 'sFUZZYSEARCHPATTERNMATCHFACTOR', '50', 'Faktor für Teiltreffer', 0, 0, '', 0, ''),
(76, 42, 'sFUZZYSEARCHMATCHFACTOR', '5', 'Faktor für unscharfe Treffer', 0, 0, '', 0, ''),
(78, 42, 'sFUZZYSEARCHDISTANCE', '20', 'Maximal-Distanz für Unscharfe Suche in Prozent', 0, 0, '', 0, ''),
(79, 6, 'sUSESSL', '0', 'SSL aktivieren', 0, 0, '', 1, 'int'),
(80, 30, 'sCATEGORYTEMPLATES', 'article_listing_1col.tpl:Liste;article_listing_2col.tpl:Zweispaltig;article_listing_3col.tpl:Dreispaltig;article_listing_4col.tpl:Vierspaltig', 'Verfügbare Templates Kategorien', 0, 0, '', 0, 'textarea'),
(92, 79, 'sNOTAVAILABLE', 'Lieferzeit ca. 5 Tage', 'Text für nicht verfügbare Artikel', 0, 0, '', 1, ''),
(81, 79, 'sMAXPURCHASE', '100', 'Max. wählbare Artikelmenge / Artikel über Pulldown-Menü', 0, 0, '', 0, ''),
(93, 71, 'sDISCOUNTNAME', 'Warenkorbrabatt', 'Rabatte Bezeichnung ', 0, 0, '', 1, ''),
(152, 6, 'sACCOUNTID', 'AD7DC-asdasd-CB382-8D57B-0CA37-24C39', 'Ihre Shopware-Konto-ID', 0, 0, '', 0, ''),
(94, 77, 'sMAXCROSSSIMILAR', '8', 'Anzahl ähnlicher Artikel Cross-Selling', 0, 0, '', 0, ''),
(95, 77, 'sMAXCROSSALSOBOUGHT', '8', 'Anzahl "Kunden kauften auch" Artikel Cross-Selling', 0, 0, '', 0, ''),
(96, 0, 'sCURRENCYHTML', '€', 'Währungssymbol HTML-Code', 0, 0, '', 0, ''),
(97, 48, 'sCAMPAIGNSPOSITIONS', 'Rechts Oben:rightTop;Rechts Unten:rightBottom', 'Mögliche Positionen', 0, 0, '', 0, 'textarea'),
(98, 35, 'sDISCOUNTTAX', '19', 'MwSt. Rabatte', 0, 0, '', 0, ''),
(99, 0, 'sTAGCOUNTCAT', '4', 'Anzahl der Kategorien', 0, 0, '', 0, ''),
(100, 39, 'sTAGCOUNTARTICLES', '4', 'Anzahl der Artikel', 0, 0, '', 0, ''),
(102, 0, 'sTAGCOUNTSUPPLIER', '4', 'Anzahl der Hersteller', 0, 0, '', 0, ''),
(103, 0, 'sTAGRELEVANCECAT', '4', 'Relevanz der Katogerien', 0, 0, '', 0, ''),
(104, 0, 'sTAGRELEVANCESUPPLIER', '4', 'Relevanz der Hersteller', 0, 0, '', 0, ''),
(105, 39, 'sTAGRELEVANCEARTBASKET', '4', 'Relevanz der Artikel im Warenkorb', 0, 0, '', 0, ''),
(106, 39, 'sTAGRELEVANCEARTVIEWS', '4', 'Relevanz der angeschauten Artikel', 0, 0, '', 0, ''),
(107, 39, 'sTAGRELEVANCEARTSELLS', '4', 'Relevanz der verkauften Artikel', 0, 0, '', 0, ''),
(263, 72, 'sVOTEDISABLE', '0', 'Artikel-Bewertungen deaktivieren', 0, 0, '0', 0, 'int'),
(109, 39, 'sTAGTIME', '30', 'Die berücksichtigte Zeit in Tagen', 0, 0, '', 0, ''),
(113, 72, 'sVOTEUNLOCK', '1', 'Artikel-Bewertungen müssen freigeschaltet werden', 0, 0, '', 0, 'int'),
(114, 33, 'sSHOPWAREMANAGEDCUSTOMERNUMBERS', '1', 'Shopware generiert Kundennummern', 0, 0, '', 0, 'int'),
(115, 42, 'sFUZZYSEARCHPARTNAMEDISTANCEN', '25', 'Maximal-Distanz für Teilnamen in Prozent', 0, 0, '', 0, ''),
(116, 42, 'sFUZZYSEARCHMINDISTANCENTOP', '20', 'Minimale Relevanz zum Topartikel in Prozent', 0, 0, '', 0, ''),
(117, 0, 'sSERVICETYPES', '1:Defekte Artikel;2:Rückgaberecht', 'Serviceformulare Definition', 0, 0, '', 0, ''),
(118, 55, 'sUSERTIMEOUT', '7200', 'Timeout nach Sekunden (System/Admin/Allgemein)', 0, 0, '', 0, ''),
(120, 55, 'sREFRESHDASHBOARD', '600000', 'Intervall in ms. in der sich das Dashboard aktualisiert', 0, 0, '', 0, ''),
(119, 0, 'sHIDETEMPLATES', '0', 'Templatebrowser deaktivieren', 0, 0, '', 0, ''),
(121, 0, 'sMINSERIALS', '5', '', 0, 0, '', 0, ''),
(123, 0, 'sEXPORTSTOCKMIN', '1', 'Lagerbestand beim Portal Export berücksichtigen', 0, 0, '', 0, ''),
(124, 12, 'sCACHETRANSLATIONS', '86400', 'Übersetzungen Pufferzeit', 0, 0, '', 0, ''),
(126, 0, 'sAPI', 'dev1.shopvm.de-178182ea8ed197e7b05e2e3b1b631f93', '', 0, 0, '', 0, ''),
(138, 71, 'sSURCHARGENAME', 'Mindermengenzuschlag', 'Mindermengen Bezeichnung', 0, 0, '', 1, ''),
(147, 0, 'sTSID', '', 'Trusted-Shops-ID', 0, 0, '', 1, ''),
(148, 33, 'sIGNOREAGB', '0', 'AGB - Checkbox auf Kassenseite deaktivieren', 0, 0, '', 0, 'int'),
(149, 33, 'sCOUNTRYSHIPPING', '1', 'Land bei Lieferadresse abfragen', 0, 0, '', 0, 'int'),
(150, 55, 'sHIDESTART', '0', 'Startbildschirm deaktivieren', 0, 0, '', 0, 'int'),
(158, 60, 'sNO_ORDER_MAIL', '0', 'Bestellbestätigung nicht an Shopbetreiber schicken', 0, 0, '', 0, 'int'),
(159, 0, 'sCACHETRANSLATIONTABLE', '1;1224674330', 'TranslationTable', 0, 0, '', 0, ''),
(160, 42, 'sBADWORDS', 'ab,die,der,und,in,zu,den,das,nicht,von,sie,ist,des,sich,mit,dem,dass,er,es,ein,ich,auf,so,eine,auch,als,an,nach,wie,im,fÃ¼r,einen,um,werden,mehr,zum,aus,ihrem,style,oder,neue,spieler,kÃ¶nnen,wird,sind,ihre,einem,of,du,sind,einer,Ã¼ber,alle,neuen,bei,durch,kann,hat,nur,noch,zur,gegen,bis,aber,haben,vor,seine,ihren,jetzt,ihr,dir,etc,bzw,nach,deine,the,warum,machen,0,sowie,am', 'Blacklist für Keywords', 1, 1, '', 0, ''),
(161, 30, 'sCATEGORY_DEFAULT_TPL', 'article_listing_4col.tpl', 'Standard-Template für neue Kategorien', 0, 0, '', 0, ''),
(162, 71, 'sPAYMENTSURCHARGEADD', 'Zuschlag für Zahlungsart', 'Bezeichnung proz. Zuschlag für Zahlungsart', 0, 0, '', 1, ''),
(163, 71, 'sPAYMENTSURCHARGEDEV', 'Abschlag für Zahlungsart', 'Bezeichnung proz. Abschlag für Zahlungsart', 0, 0, '', 1, ''),
(164, 0, 'sCONFIGURATORINSTOCK', '1', 'Lagerbestände bei Konfigurator-Artikeln berücksichtigen', 0, 0, '', 0, ''),
(209, 79, 'sINQUIRYID', '16', 'Anfrage-Formular ID', 0, 0, '', 1, ''),
(171, 0, 'sVERSION', '3.5.1', '', 0, 0, '', 0, ''),
(175, 65, 'sIPAYMENT_ACCOUNTID', '', 'Ihre iPayment Account-ID', 0, 0, '', 0, ''),
(176, 65, 'sIPAYMENT_USERID', '', 'Ihre iPayment User-ID', 0, 0, '', 0, ''),
(177, 65, 'sIPAYMENT_TRANSACTIONPW', '', 'Ihr Transaktions-Passwort', 0, 0, '', 0, ''),
(178, 65, 'sIPAYMENT_SECURITYKEY', '', 'Ihr iPayment-Sicherheitsschlüssel', 0, 0, '', 0, ''),
(179, 66, 'sSOFORTUSERID', '', 'User-ID', 0, 0, '', 0, ''),
(180, 66, 'sSOFORTSECRETKEY', '', 'Sicherheitsschlüssel', 0, 0, '', 0, ''),
(181, 66, 'sSOFORTPROJECTID', '', 'Projekt-ID', 0, 0, '', 0, ''),
(185, 76, 'sCHARTINTERVAL', '10', 'Anzahl der Tage, die für die Topseller-Generierung berücksichtigt werden', 0, 0, '', 0, ''),
(186, 77, 'sSIMILARLIMIT', '3', 'Anzahl automatisch ermittelter, ähnlicher Artikel (Detailseite)', 0, 0, '', 0, ''),
(188, 37, 'sUOSDEFAULT', '1', 'Standardzahlverfahren des Kunden verwenden, soweit es vorhanden ist', 0, 0, '0', 0, ''),
(189, 79, 'sBASKETSHIPPINGINFO', '1', 'Lieferzeit im Warenkorb anzeigen', 0, 0, '', 0, 'int'),
(190, 84, 'sCMSPOSITIONS', 'Deutsch links:gLeft;Deutsch unten 1:gBottom;Deutsch unten 2:gBottom2;In Bearbeitung:gDisabled;Englisch links:eLeft;Englisch unten 1:eBottom;Englisch unten 2:eBottom2', 'Mögliche Positionen von Shopseiten', 0, 0, '', 0, 'textarea'),
(191, 29, 'sBACKENDAUTOORDERNUMBER', '1', 'Automatischer Vorschlag der Artikelnummer', 0, 0, '', 0, 'int'),
(192, 29, 'sBACKENDAUTOORDERNUMBERPREFIX', 'SW', 'Präfix für automatisch generierte Artikelnummer', 0, 0, '', 0, ''),
(193, 70, 'sMAXCOMPARISONS', '5', 'Maximale Anzahl von zu vergleichenden Artikeln', 0, 0, '', 0, ''),
(194, 0, 'sDONTATTACHSESSION', '0', 'Session-ID nicht an URL anhängen', 0, 0, '', 0, ''),
(195, 71, 'sDISCOUNTNUMBER', 'sw-discount', 'Rabatte Bestellnummer', 0, 0, '', 1, ''),
(196, 71, 'sSURCHARGENUMBER', 'sw-surcharge', 'Mindermengen Bestellnummer', 0, 0, '', 1, ''),
(197, 71, 'sPAYMENTSURCHARGENUMBER', 'sw-payment', 'Zuschlag für Zahlungsart (Bestellnummer)', 0, 0, '', 1, ''),
(201, 42, 'sMAXLIVESEARCHRESULTS', '6', 'Anzahl der Ergebnisse in der Livesuche', 0, 0, '', 0, ''),
(266, 65, 'sIPAYMENT_RESERVE', '0', 'Zahlung reservieren?', 0, 0, '', 0, ''),
(265, 6, 'sBLOCKIP', '', 'IP von Statistiken ausschließen', 1, 1, '', 0, ''),
(264, 79, 'sUSEZOOMPLUS', '1', 'Zoomviewer statt Lightbox auf Detailseite', 0, 0, '', 0, 'int'),
(206, 76, 'sARTICLELIMIT', '50', 'Anzahl der Artikel, die unter Neuheiten ausgegeben werden', 0, 0, '', 0, ''),
(207, 71, 'sIGNORESHIPPINGFREEFORSURCHARGES', '0', 'Absolute Zahlungszuschläge für Versandkosten immer berechnen', 0, 0, '', 0, 'int'),
(208, 6, 'sBOTBLACKLIST', 'antibot;appie;architext;bjaaland;digout4u;echo;fast-webcrawler;ferret;googlebot;gulliver;harvest;htdig;ia_archiver;jeeves;jennybot;linkwalker;lycos;mercator;moget;muscatferret;myweb;netcraft;nomad;petersnews;scooter;slurp;unlost_web_crawler;voila;voyager;webbase;weblayers;wget;wisenutbot;acme.spider;ahoythehomepagefinder;alkaline;arachnophilia;aretha;ariadne;arks;aspider;atn.txt;atomz;auresys;backrub;bigbrother;blackwidow;blindekuh;bloodhound;brightnet;bspider;cactvschemistryspider;cassandra;cgireader;checkbot;churl;cmc;collective;combine;conceptbot;coolbot;core;cosmos;cruiser;cusco;cyberspyder;deweb;dienstspider;digger;diibot;directhit;dnabot;download_express;dragonbot;dwcp;e-collector;ebiness;eit;elfinbot;emacs;emcspider;esther;evliyacelebi;nzexplorer;fdse;felix;fetchrover;fido;finnish;fireball;fouineur;francoroute;freecrawl;funnelweb;gama;gazz;gcreep;getbot;geturl;golem;grapnel;griffon;gromit;hambot;havindex;hometown;htmlgobble;hyperdecontextualizer;iajabot;ibm;iconoclast;ilse;imagelock;incywincy;informant;infoseek;infoseeksidewinder;infospider;inspectorwww;intelliagent;irobot;iron33;israelisearch;javabee;jbot;jcrawler;jobo;jobot;joebot;jubii;jumpstation;katipo;kdd;kilroy;ko_yappo_robot;labelgrabber.txt;larbin;legs;linkidator;linkscan;lockon;logo_gif;macworm;magpie;marvin;mattie;mediafox;merzscope;meshexplorer;mindcrawler;momspider;monster;motor;mwdsearch;netcarta;netmechanic;netscoop;newscan-online;nhse;northstar;occam;octopus;openfind;orb_search;packrat;pageboy;parasite;patric;pegasus;perignator;perlcrawler;phantom;piltdownman;pimptrain;pioneer;pitkow;pjspider;pka;plumtreewebaccessor;poppi;portalb;puu;python;raven;rbse;resumerobot;rhcs;roadrunner;robbie;robi;robofox;robozilla;roverbot;rules;safetynetrobot;search_au;searchprocess;senrigan;sgscout;shaggy;shaihulud;sift;simbot;site-valet;sitegrabber;sitetech;slcrawler;smartspider;snooper;solbot;spanner;speedy;spider_monkey;spiderbot;spiderline;spiderman;spiderview;spry;ssearcher;suke;suntek;sven;tach_bw;tarantula;tarspider;techbot;templeton;teoma_agent1;titin;titan;tkwww;tlspider;ucsd;udmsearch;urlck;valkyrie;victoria;visionsearch;vwbot;w3index;w3m2;wallpaper;wanderer;wapspider;webbandit;webcatcher;webcopy;webfetcher;webfoot;weblinker;webmirror;webmoose;webquest;webreader;webreaper;websnarf;webspider;webvac;webwalk;webwalker;webwatch;whatuseek;whowhere;wired-digital;wmir;wolp;wombat;worm;wwwc;wz101;xget;awbot;bobby;boris;bumblebee;cscrawler;daviesbot;ezresult;gigabot;gnodspider;internetseer;justview;linkbot;linkchecker;nederland.zoek;perman;pompos;pooodle;redalert;shoutcast;slysearch;ultraseek;webcompass;yandex;robot;yahoo;bot;psbot;crawl;RSS;larbin;ichiro;Slurp;msnbot;bot;Googlebot;ShopWiki;Bot;WebAlta;;abachobot;architext;ask jeeves;frooglebot;googlebot;lycos;spider;HTTPClient', 'Bot-Liste', 0, 0, '', 0, 'textarea'),
(267, 0, 'sHOOKPOINTS', 'shopware.php_start;shopware.php_licenseCheckAfter;shopware.php_botCheckAfter;shopware.php_assignPathBefore;shopware.php_viewportBefore;shopware.php_registerSmartyFilter;shopware.php_viewportAfter;shopware.php_defineTemplatePathAfter;shopware.php_pushContentBefore;shopware.php_pushContentAfter;sSystem.php_myErrorHandler_Start;sSystem.php_myErrorHandler_End;sSystem.php_catchErrors_End;sSystem.php_sStripSlashes_End;sSystem.php_sRenderViewport_Start;sSystem.php_sRenderViewport_AfterCustomRenderer;sSystem.php_sRenderViewport_End;sSystem.php_sMaskInput_End;sSystem.php_sInitSession_End;sSystem.php_sLog_Before;sSystem.php_sInitSmarty_End;sSystem.php_sInitConfig_End;sSystem.php_sTranslateConfig_End;sSystem.php_sInitCurrency_BeforeEnd;sSystem.php_sInitLanguage_BeforeEnd;sSystem.php_sPreProcess_End;sOrder.php_sendMail_Start;sOrder.php_sendMail_BeforeSend;sOrder.php_sendMail_BeforeSend2;sOrder.php_sendMail_BeforeSend3;sOrder.php_sendMail_AfterSend;sOrder.php_sSaveOrder_BeforeInsertMain;sOrder.php_sSaveOrder_BeforeInsertMain2;sOrder.php_sSaveOrder_ContentLoop;sOrder.php_sSaveOrder_BeforeInsert;sOrder.php_sSaveOrder_ModifyInstock;sOrder.php_sSaveOrder_ModifyUserData;sOrder.php_sSaveOrder_ModifyContent;sOrder.php_sSaveOrder_VariablesAssign;sOrder.php_sSaveOrder_BeforeSend;sOrder.php_sSaveOrder_BeforeDelete;sOrder.php_sSaveOrder_BeforeEnd;sOrder.php_sGetOrderNumber_Start;sOrder.php_sGetOrderNumber_Start2;sOrder.php_sGetOrderNumber_BeforeEnd;sOrder.php_sGetOrderNumber_End;sArticles.php_sGetArticleProperties_Start;sArticles.php_sGetArticleProperties_AfterSQL;sArticles.php_sGetArticleProperties_BeforeEnd;sArticles.php_sGetArticlesAverangeVote_AfterSQL;sArticles.php_sGetArticlesAverangeVote_BeforeEnd;sArticles.php_sSaveComment_AfterAssign;sArticles.php_sSaveComment_BeforeSQ;sArticles.php_sSaveComment_AfterSQL;sArticles.php_sGetArticlesByName_BeforeEnd;sArticles.php_sGetArticlesByCategory_Start;sArticles.php_sGetArticlesByCategory_BeforeSQL;sArticles.php_sGetArticlesByCategory_AfterSQL;sArticles.php_sGetArticlesByCategory_BeforeCountArticles1;sArticles.php_sGetArticlesByCategory_BeforeCountArticles2;sArticles.php_sGetArticlesByCategory_AfterCalculatingPages;sArticles.php_sGetArticlesByCategory_LoopArticlesStart;sArticles.php_sGetArticlesByCategory_LoopArticlesStart1;sArticles.php_sGetArticlesByCategory_LoopArticlesStart2;sArticles.php_sGetArticlesByCategory_LoopArticlesEnd;sArticles.php_sGetArticlesByCategory_BeforeEnd;sArticles.php_sGetCategoryProperties_BeforeEnd;sArticles.php_sGetAffectedSuppliers_BeforeEnd;sArticles.php_sCalculatingPrice_Start;sArticles.php_sCalculatingPrice_BeforeEnd;sArticles.php_sCalculatingPriceNum_Start;sArticles.php_sCalculatingPriceNum_BeforeEnd;sArticles.php_sGetArticleCharts_AfterSQL;sArticles.php_sGetArticleCharts_AfterSQL2;sArticles.php_sGetArticleCharts_LoopStart;sArticles.php_sGetArticleCharts_LoopEnd;sArticles.php_sGetArticleCharts_BeforeEnd;sArticles.php_sGetAllArticlesInCategory_Start;sArticles.php_sGetAllArticlesInCategory_AfterSQL;sArticles.php_sGetAllArticlesInCategory_LoopStart;sArticles.php_sGetAllArticlesInCategory_LoopEnd;sArticles.php_sGetAllArticlesInCategory_BeforeEnd;sArticles.php_sGetArticleAccessories_BeforeEnd;sArticles.php_sCreateTranslationTable_BeforeEnd;sArticles.php_sGetArticleConfig_Start;sArticles.php_sGetArticleConfig_Start2;sArticles.php_sGetArticleConfig_BeforeEnd;sArticles.php_sGetPricegroupDiscount_Start;sArticles.php_sGetPricegroupDiscount_BeforeEnd1;sArticles.php_sGetPricegroupDiscount_BeforeEnd2;sArticles.php_sGetPricegroupDiscount_BeforeEnd3;sArticles.php_sGetPricegroupDiscount_BeforeEnd4;sArticles.php_sGetPricegroupDiscount_Start;sArticles.php_sGetPricegroupDiscount_AfterSQL;sArticles.php_sGetPricegroupDiscount_AfterSQL2;sArticles.php_sGetPricegroupDiscount_AfterSQL3;sArticles.php_sGetCheapestPrice_BeforeEnd1;sArticles.php_sGetCheapestPrice_BeforeEnd2;sArticles.php_sGetCheapestPrice_BeforeEnd3;sArticles.php_sGetArticleById_Start;sArticles.php_sGetArticleById_Start2;sArticles.php_sGetArticleById_AfterSQL;sArticles.php_sGetArticleById_AfterQuery;sArticles.php_sGetArticleById_AfterLinks;sArticles.php_sGetArticleById_AfterCrossSelling;sArticles.php_sGetArticleById_AfterBlockPrices;sArticles.php_sGetArticleById_AfterVariantSQL;sArticles.php_sGetArticleById_VariantLoopStart;sArticles.php_sGetArticleById_VariantLoopEnd;sArticles.php_sGetArticleById_BeforeReturn;sArticles.php_sFormatPrice_BeforeEnd;sArticles.php_sRound_BeforeEnd;sArticles.php_sGetPromotionById_Start;sArticles.php_sGetPromotionById_BeforeSwitch;sArticles.php_sGetPromotionById_SQLRandom1;sArticles.php_sGetPromotionById_SQLRandom2;sArticles.php_sGetPromotionById_SQLNew1;sArticles.php_sGetPromotionById_SQLNew2;sArticles.php_sGetPromotionById_SQLTop;sArticles.php_sGetPromotionById_Image;sArticles.php_sGetPromotionById_Premium;sArticles.php_sGetPromotionById_AfterSQL;sArticles.php_sGetPromotionById_QueryStart;sArticles.php_sGetPromotionById_QueryEnd;sArticles.php_sGetPromotionById_BeforeEnd;sArticles.php_sOptimizeText_Start;sArticles.php_sOptimizeText_BeforeEnd;sArticles.php_sGetArticlePictures_BeforeEnd;sArticles.php_sGetPromotions_Start;sArticles.php_sGetPromotions_AfterSQL;sArticles.php_sGetPromotions_BeforeEnd;sBasket.php_sAddArticle_CheckIfArticleIsInBasket;sBasket.php_sGetBasket_Start;sBasket.php_sGetBasket_AfterSQL;sBasket.php_sGetBasket_Loop1;sBasket.php_sGetBasket_Loop2;sBasket.php_sGetBasket_Loop3;sBasket.php_sGetBasket_Loop4;sBasket.php_sGetBasket_Loop5;sBasket.php_sGetBasket_BeforeEnd1;sBasket.php_sGetBasket_BeforeEnd2;sBasket.php_sUpdateArticle_Start;sBasket.php_sUpdateArticle_Start2;sBasket.php_sUpdateArticle_Start3;sBasket.php_sUpdateArticle_Start4;sBasket.php_sUpdateArticle_Start5;sBasket.php_sUpdateArticle_Start6;sBasket.php_sUpdateArticle_Start7;sBasket.php_sUpdateArticle_Start8;sBasket.php_sAddArticle_Start;sBasket.php_sAddArticle_Start2;sBasket.php_sAddArticle_Start3;sBasket.php_sAddArticle_Start4;sBasket.php_sAddArticle_Start5;sAdmin.php_sLogout_Start;sAdmin.php_sLogout_End;sAdmin.php_sGetPaymentMeanById_BeforeEnd;sAdmin.php_sGetPaymentMeans_AfterSQL;sAdmin.php_sGetPaymentMeans_LoopStart;sAdmin.php_sGetPaymentMeans_LoopEnd;sAdmin.php_sGetPaymentMeans_BeforeEnd;sAdmin.php_sUpdateBilling_AfterSQL;sAdmin.php_sUpdateShipping_AfterSQL;sAdmin.php_sGetCountryList_AfterQuery;sAdmin.php_sSaveRegisterMainData_AfterSQL;sAdmin.php_sSaveRegisterBilling_AfterSQL;sAdmin.php_sSaveRegisterShipping_AfterSQL;sAdmin.php_sSaveRegisterSendConfirmation_Start1;sAdmin.php_sSaveRegisterSendConfirmation_Start2;sAdmin.php_sGetUserData_BeforeEnd;sAdmin.php_sGetShippingcosts_Start1;sAdmin.php_sGetShippingcosts_Start2;sAdmin.php_sGetShippingcosts_Start3;sAdmin.php_sGetShippingcosts_Start4;sAdmin.php_sGetShippingcosts_Start5;sCategories.php_sGetCategoriesAsArrayByIdTest_Start;sCategories.php_sGetCategoriesAsArrayByIdTest_AfterSQL;sCategories.php_sGetCategoriesAsArrayByIdTest_AfterSQL2;sCategories.php_sGetCategoriesAsArrayByIdTest_LoopStart;sCategories.php_sGetCategoriesAsArrayByIdTest_LoopEnd;sCategories.php_sGetCategoriesAsArrayByIdTest_AfterSQL3;sCategories.php_sGetCategoriesAsArrayByIdTest_Loop2Start;sCategories.php_sGetCategoriesAsArrayByIdTest_BeforeEnd;sCategories.php_sGetCategoriesAsArrayById_BeforeEnd;sCategories.php_sGetMainCategories_AfterSQL;sCategories.php_sGetMainCategories_LoopEnd;sCategories.php_sGetMainCategories_BeforeEnd;sCategories.php_sGetCategoryIdsByParent_BeforeEnd;sCategories.php_sGetCategoriesByParent_BeforeEnd;sCategories.php_sGetWholeCategoryTree_AfterSQL;sCategories.php_sGetWholeCategoryTree_LoopEnd;sCategories.php_sGetWholeCategoryTree_BeforeEnd;sCore.php_sStart_BeforeEnd;sCore.php_sCustomRenderer_BeforeEnd;sCore.php_rewriteLink_Start;sCore.php_rewriteLink_SSL;sCore.php_rewriteLink_BeforeEnd;s_detail.php_sRender_BeforeEnd;s_sale.php_sRender_BeforeEnd;s_sale.php_sRender_BeforeOrder;s_sale.php_sRender_BeforeOrder2;s_cat.php_sRender_AfterVariables;s_cat.php_sRender_BeforeEnd;articles_artikeln1.inc.php_Start;articles_artikeln1.inc.php_Post1;articles_artikeln1.inc.php_Post2;articles_artikeln1.inc.php_Post3;articles_artikeln1.inc.php_Post4;articles_artikeln1.inc.php_Post5;articles_artikeln1.inc.php_Post6;articles_artikeln1.inc.php_Post7;articles_artikeln1.inc.php_Post8;articles_artikeln1.inc.php_Post9;articles_artikeln1.inc.php_Post10;articles_artikeln1.inc.php_Post11;articles_artikeln1.inc.php_Post12;articles_artikeln1.inc.php_Post13;sCreateDocuments.php_Inject', 'Hook-Points zum Einfügen von eigenem Code', 0, 0, '', 0, ''),
(214, 60, 'sSEND_CONFIRM_MAIL', '1', 'Registrierungsbestätigung in CC an Shopbetreiber schicken', 0, 0, '', 0, 'int'),
(215, 87, 'sCABLINK', '', 'Premium-URL', 0, 0, '', 0, ''),
(216, 87, 'sCABSELLERID', '', 'SellerID', 0, 0, '', 0, ''),
(217, 87, 'sCABTMIPWD', '', 'TMI Passwort', 0, 0, '', 0, ''),
(218, 87, 'sCUSTOMERDATA', '', 'Kundendatenübergabe an/aus', 0, 0, '', 0, ''),
(219, 87, 'sSECONDCONFIRMATIONSTATUS', '', 'Second Confirmation an/aus', 0, 0, '', 0, ''),
(220, 88, 'sAPI_USERNAME', '', 'API-Benutzername', 0, 0, '', 0, ''),
(221, 88, 'sAPI_PASSWORD', '', 'API-Passwort', 0, 0, '', 0, ''),
(222, 88, 'sAPI_SIGNATURE', '', 'API-Signatur', 0, 0, '', 0, ''),
(223, 88, 'sAPI_SANDBOX', '0', 'PayPal-Sandbox nutzen (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(224, 88, 'sXPRESS', '1', 'PayPal Express im Warenkorb nutzen (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(225, 88, 'sAUTHORIZATION', '0', 'Zahlungsmodus (0 = sofort, 1 = reserviert*)', 0, 0, '', 0, ''),
(226, 88, 'sPaypalLogo', '0', 'PayPal Logo auf Startseite (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(227, 89, 'sSAFERPAY_ACCOUNTID', '', 'Account-ID', 0, 0, '', 0, ''),
(228, 89, 'sSAFERPAY_TESTSYSTEM', '', 'Testsystem nutzen (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(229, 89, 'sSAFERPAY_PASSWORD', '', 'Passwort Testsystem (nur für das Testsystem notwendig)', 0, 0, '', 0, ''),
(230, 89, 'sSAFERPAY_AUTHORIZATION', '0', 'Zahlungsmodus (0 = sofort, 1 = reserviert*)', 0, 0, '', 0, ''),
(231, 89, 'sSAFERPAY_CVC', '1', 'Abfrage der Kartenprüfnummer CVC (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(232, 89, 'sSAFERPAY_CARDHOLDER', '1', 'Abfrage des Karteninhabernamens (0 = aus, 1 = an)', 0, 0, '', 0, ''),
(233, 89, 'sSAFERPAY_MENUFONTCOLOR', '#000000', 'MENUFONTCOLOR (Schriftfarbe des Menüs**)', 0, 0, '', 0, ''),
(234, 89, 'sSAFERPAY_HEADFONTCOLOR', '#000000', 'HEADFONTCOLOR (Schriftfarbe der Reiter**)', 0, 0, '', 0, ''),
(235, 89, 'sSAFERPAY_BODYFONTCOLOR', '#000000', 'BODYFONTCOLOR (Schriftfarbe des Eingabebereichs**)', 0, 0, '', 0, ''),
(236, 89, 'sSAFERPAY_MENUCOLOR', '#93B1CF', 'MENUCOLOR: (Farbe inaktiver Reiter**)', 0, 0, '', 0, ''),
(237, 89, 'sSAFERPAY_HEADLINECOLOR', '#93B1CF', 'HEADLINECOLOR (Farbe der Trennlinie oben links**)', 0, 0, '', 0, ''),
(238, 89, 'sSAFERPAY_BODYCOLOR', '#E5E7E8', 'BODYCOLOR (Hintergrundfarbe des Saferpay VT**)', 0, 0, '', 0, ''),
(239, 89, 'sSAFERPAY_HEADCOLOR', '#134B83', 'HEADCOLOR (Hintergrundfarbe des oberen Bereichs**)', 0, 0, '', 0, ''),
(240, 90, 'sTICKETACCOUNTFORMID', '22', '"Mein-Konto" - Formular ID', 0, 0, '', 1, ''),
(374, 99, 'sHEIDELPAY_LIVE_URL', 'https://ctpe.net/frontend/payment.prc', 'Heidelpay Live URL', 1, 0, '', 0, ''),
(375, 99, 'sHEIDELPAY_DEMO_URL', 'https://test.ctpe.net/frontend/payment.prc', 'Heidelpay Demo URL', 1, 0, '', 0, ''),
(377, 99, 'sHEIDELPAY_NOTIFY_ADMIN', '0', 'Soll Admin eine Benachrichtigungs EMail erhalten 1=Ja 0=Nein', 1, 0, '', 0, ''),
(376, 99, 'sHEIDELPAY_NOTIFY_CUSTOMER', '0', 'Soll Kunde eine Benachrichtigungs EMail erhalten 1=Ja 0=Nein', 1, 0, '', 0, ''),
(371, 99, 'sHEIDELPAY_PAYMENT_TYPE_DC', 'DB', 'Payment Type Debit Card DB = Debit, PA = Preauthorisation', 1, 0, '', 0, ''),
(372, 99, 'sHEIDELPAY_PAYMENT_TYPE_VAPP', 'PA', 'Payment Type PayPal DB = Debit, PA = Preauthorisation', 1, 0, '', 0, ''),
(373, 99, 'sHEIDELPAY_STATUS_SUCCESS', '12', 'Status ID für Erfolg', 1, 0, '', 0, ''),
(369, 99, 'sHEIDELPAY_PAYMENT_TYPE_CC', 'DB', 'Payment Type Credit Card DB = Debit, PA = Preauthorisation', 1, 0, '', 0, ''),
(370, 99, 'sHEIDELPAY_PAYMENT_TYPE_DD', 'DB', 'Payment Type Direct Debit DB = Debit, PA = Preauthorisation', 1, 0, '', 0, ''),
(365, 99, 'sHEIDELPAY_USER_LOGIN', '', 'User Login', 1, 0, '', 0, ''),
(366, 99, 'sHEIDELPAY_USER_PASSWORD', '', 'User Password', 1, 0, '', 0, ''),
(367, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL', '', 'Transaction Channel', 1, 0, '', 0, ''),
(368, 99, 'sHEIDELPAY_TRANSACTION_MODE', 'INTEGRATOR_TEST', 'Transaction Mode: INTEGRATOR_TEST, CONNECTOR_TEST oder LIVE', 1, 0, '', 0, ''),
(364, 99, 'sHEIDELPAY_SECURITY_SENDER', '', 'Security Sender', 1, 0, '', 0, ''),
(268, 90, 'sTICKETNOTIFYEMAIL', '', 'Benachrichtung bei neuen / beantworteten Ticktes', 0, 0, 'Bitte hinterlegen Sie eine oder mehrer eMail-Adressen', 0, 'int'),
(269, 90, 'sTICKETEMAILMATCH', '1', 'Absender-eMail mit Kundenliste vergleichen', 0, 0, '', 0, 'int'),
(270, 90, 'sTICKETNOTIFYMAILCOSTUMER', '1', 'Ticketbestätigung an den Kunden', 0, 0, '', 0, 'int'),
(271, 93, 'sPREMIUMSHIPPIUNGASKETSELECT', 'MAX(a.topseller) as has_topseller, MAX(at.attr3) as has_comment, MAX(b.esdarticle) as has_esd', 'Erweitere SQL-Abfrage', 1, 0, '', 0, ''),
(272, 93, 'sPREMIUMSHIPPIUNGNOORDER', '0', 'Bestellung bei keiner verfügbaren Versandart blocken', 1, 0, '', 0, 'int'),
(273, 93, 'sPREMIUMSHIPPIUNG', '1', 'Modul aktivieren', 1, 0, '', 0, 'int'),
(274, 60, 'sOPTINNEWSLETTER', '0', 'Double-Opt-In für Newsletter-Anmeldungen', 0, 0, '', 0, 'int'),
(275, 60, 'sOPTINVOTE', '1', 'Double-Opt-In für Artikel-Bewertungen', 0, 0, '', 0, 'int'),
(276, 90, 'sTICKETSIDEBAR', '1', 'Offene Tickets in der Sidebar anzeigen', 0, 0, '', 0, 'int'),
(277, 94, 'sMONEYBOOKERS_MERCHANTID', '', 'Merchant ID', 1, 0, '', 0, ''),
(278, 94, 'sMONEYBOOKERS_INSTALLED_PAYMETHODSINSTALLED', '', 'Installierte Zahlungsarten', 0, 0, '', 0, ''),
(279, 94, 'sMONEYBOOKERS_SECRET', '', 'Secret', 1, 0, '', 0, ''),
(280, 94, 'sMONEYBOOKERS_EMAIL', '', 'Moneybookers Empfänger EMail', 0, 0, '', 0, ''),
(281, 94, 'sMONEYBOOKERS_STATUS_ID', '17', 'Bestellstatus für "Neue Bestellung"', 0, 0, '', 0, ''),
(282, 94, 'sMONEYBOOKERS_STATUS_PROCESSED', '11', 'Bestellstatus für "Bezahlung erfolgreich"', 0, 0, '', 0, ''),
(283, 94, 'sMONEYBOOKERS_STATUS_PENDING', '17', 'Bestellstatus für "Bezahlung ausstehend"', 0, 0, '', 0, ''),
(284, 94, 'sMONEYBOOKERS_STATUS_CANCELLED', '21', 'Bestellstatus für "Bezahlung abgebrochen"', 0, 0, '', 0, ''),
(285, 94, 'sMONEYBOOKERS_STATUS_FAILED', '21', 'Bestellstatus für "Bezahlung fehlgeschlagen"', 0, 0, '', 0, ''),
(286, 94, 'sMONEYBOOKERS_STATUS_CHARGEBACK', '21', 'Bestellstatus für "Bezahlung storniert"', 0, 0, '', 0, ''),
(287, 94, 'sMONEYBOOKERS_IFRAME_URL', 'https://www.moneybookers.com/app/payment.pl', 'Gateway URL - Tragen Sie hier die Gateway URL von Moneybookers ein.', 0, 0, '', 0, ''),
(288, 94, 'sMONEYBOOKERS_STYLE', '', 'Stylesheet für den Moneybookers Frame', 0, 0, '', 0, ''),
(289, 94, 'sMONEYBOOKERS_SWITCH_HIDE_LOGIN', '1', 'Moneybookers Login ausblenden (0=Nein, 1=Ja)', 0, 0, '', 0, ''),
(290, 94, 'sMONEYBOOKERS_CONFIRMATION_NOTE', '', 'Bestätigungstext', 0, 0, '', 0, ''),
(291, 95, 'sCLICKPAYPOPUP', '0', 'Popup', 1, 0, '', 0, ''),
(292, 95, 'sCLICKPAYDIRECTBOOK', '1', 'Sofortbuchung Kreditkarte', 1, 0, '', 0, ''),
(293, 95, 'sCLICKPAYMERCHANTCODE', '', 'Händler Code', 1, 0, '', 0, ''),
(294, 95, 'sCLICKPAYMERCHANTID', '', 'Händler ID', 1, 0, '', 0, ''),
(295, 95, 'sCLICKPAYSTYLESHEET', 'engine/connectors/clickpay/stylesheetmini.css', 'Stylesheet für ClickPay', 1, 0, '', 1, ''),
(296, 95, 'sCLICKPAYTEXT', 'Ihre Bestellung im Shopware-Testsystem', 'Text auf der Bezahlseite', 0, 0, '', 1, ''),
(297, 95, 'sCLICKPAYRISKPROVIDER', 'SDB_DelphiScore', 'Bonitäts-Schnittstelle / Anbieter', 1, 0, '', 0, ''),
(298, 95, 'sCLICKPAYELVDIRECTBOOK', '1', 'Sofortbuchung ELV', 1, 0, '', 0, ''),
(299, 95, 'sCLICKPAYSHOWCOMMENT', '0', 'Kommentarfeld anzeigen', 1, 0, '', 0, ''),
(300, 95, 'sCLICKPAYIFRAMESTYLESHEET', 'engine/connectors/clickpay/stylesheetiframe.css', 'Stylesheet für iFrame', 1, 0, '', 1, ''),
(301, 71, 'sPAYMENTSURCHARGEABSOLUTE', 'Zuschlag für Zahlungsart', 'Pauschaler Aufschlag für Zahlungsart (Bezeichnung)', 0, 0, '', 1, ''),
(302, 71, 'sPAYMENTSURCHARGEABSOLUTENUMBER', 'sw-payment-absolute', 'Pauschaler Aufschlag für Zahlungsart (Bestellnummer)', 0, 0, '', 1, ''),
(303, 96, 'sHANSEATIC_PARTNERID', '', 'Partner ID', 1, 0, '', 0, ''),
(304, 96, 'sHANSEATIC_PRESHAREDKEY', '', 'Pre Shared Key', 1, 0, '', 0, ''),
(305, 96, 'sHANSEATIC_NOTIFY_EMAIL', '', 'Benachrichtigungs EMail', 0, 0, '', 0, ''),
(306, 96, 'sHANSEATIC_STATUS_ID', '17', 'Bestellstatus für "Neue Bestellung"', 0, 0, '', 0, ''),
(307, 96, 'sHANSEATIC_STATUS_0_ID', '30', 'Bestellstatus für "Es wurde kein Kredit genehmigt."', 0, 0, '', 0, ''),
(308, 96, 'sHANSEATIC_STATUS_1_ID', '31', 'Bestellstatus für "Der Kredit wurde vorläufig akzeptiert."', 0, 0, '', 0, ''),
(309, 96, 'sHANSEATIC_STATUS_2_ID', '32', 'Bestellstatus für "Der Kredit wurde genehmigt."', 0, 0, '', 0, ''),
(310, 96, 'sHANSEATIC_STATUS_3_ID', '33', 'Bestellstatus für "Die Zahlung wurde von der Hanseatic Bank angewiesen."', 0, 0, '', 0, ''),
(311, 96, 'sHANSEATIC_STATUS_4_ID', '34', 'Bestellstatus für "Es wurde eine Zeitverlängerung eingetragen."', 0, 0, '', 0, ''),
(312, 96, 'sHANSEATIC_STATUS_5_ID', '35', 'Bestellstatus für "Vorgang wurde abgebrochen."', 0, 0, '', 0, ''),
(313, 96, 'sHANSEATIC_NOTIFY_CUSTOMER', '0', 'Mail an Kunden (1=Ja, 0=Nein)', 0, 0, '', 0, ''),
(314, 96, 'sHANSEATIC_NOTIFY_ADMIN', '0', 'Mail an Admin (1=Ja, 0=Nein)', 0, 0, '', 0, ''),
(315, 96, 'sHANSEATIC_ORDER_TYPE', 'MISC', 'Geben Sie hier den Bestelltype ein. <br><br>EDV<br>Weiße Ware (Küchenelektronik)<br>Multimedia<br>Braune Ware (Unterhaltungselektronik)<br>Fashion<br>Sport & Wellness<br>Reisen<br>Haus & Garten (auch Möbel, Küche usw.)<br>MISC (Sonstiges)', 0, 0, '', 0, ''),
(316, 96, 'sHANSEATIC_FORCE_UTF8', '0', 'UTF8 Encoding erzwingen (1=Ja, 0=Nein)', 0, 0, '', 0, ''),
(317, 96, 'sHANSEATIC_RATE_CALC_WIDTH', '550', 'Breite des Ratenrechners', 0, 0, '', 0, ''),
(318, 96, 'sHANSEATIC_RATE_CALC_HEIGHT', '350', 'Höhe des Ratenrechners', 0, 0, '', 0, ''),
(319, 96, 'sHANSEATIC_CHAR_FILTER', '-+/._,:;*?&=[](){}', 'Zeichen Filter - Hier können Sie Sonderzeichen eintragen die nach der Encode Maske zusätzlich zugelassen werden sollen. (A-Z a-z 0-9 \\ @ sind bereits automatisch enthalten) Bitte nur ändern wenn Sie genau wissen was Sie tun!!!', 0, 0, '', 0, ''),
(320, 96, 'sHANSEATIC_ENCODE_MASK', 'ä:ae,ö:oe,ü:ue,Ä:Ae,Ö:Oe,Ü:Ue,ß:ss,á:a,à:a,â:a,é:e,è:e,ê:e,í:i,ì:i,î:i,ó:o,ò:o,ô:o,ú:u,ù:u,û:u,ý:y,??:A,À:A,Â:A,É:E,È:E,Ê:E,??:I,Ì:I,Î:I,Ó:O,Ò:O,Ô:O,Ú:U,Ù:U,Û:U,??:Y,??:I,ï:i', 'Encode Maske - Hier können Sie Zeichen definieren die durch ein anderes getauscht werden sollen. z.B. "ä:ae,ö:oe,è:e" usw..', 0, 0, '', 0, ''),
(321, 96, 'sHANSEATIC_IFRAME_URL', 'https://www.online.jetzt-kredit.de/index.php?id=67', 'IFrame URL - Tragen Sie hier die IFrame URL der Hanseatic Bank ein. MIT Parameter ID=XX!', 0, 0, '', 0, ''),
(322, 96, 'sHANSEATIC_MICROSITE_URL', 'https://www.online.jetzt-kredit.de/index.php?id=55', 'Microsite URL - Tragen Sie hier die Mirosite URL der Hanseatic Bank ein. MIT Parameter ID=XX!', 0, 0, '', 0, ''),
(323, 96, 'sHANSEATIC_DELIVERY_URL', 'https://www.online.jetzt-kredit.de/pointofsales/index.php', 'Ware-versendet URL - Tragen Sie hier die URL für die Ware-versendet Schnittstelle der Hanseatic Bank ein. OHNE Parameter!', 0, 0, '', 0, ''),
(324, 96, 'sHANSEATIC_POLL_URL', 'https://www.online.jetzt-kredit.de/pointofsales/index.php', 'Poll URL - Tragen Sie hier die URL für die POLL Schnittstelle der Hanseatic Bank ein. OHNE Parameter!', 0, 0, '', 0, ''),
(325, 96, 'sHANSEATIC_CALCULATOR_URL', 'https://www.online.jetzt-kredit.de/index.php', 'Ratenrechner URL - Tragen Sie hier die URL für den Ratenrechner der Hanseatic Bank ein. OHNE Parameter!', 0, 0, '', 0, ''),
(326, 96, 'sHANSEATIC_COMMENT', 'Bankstatus: ', 'Welcher Kommentar soll in die Bestellung bei Status Änderungen (gefolgt vom Bank Status)', 0, 0, '', 0, ''),
(327, 96, 'sHANSEATIC_URL_TYPE', '1', 'Finanzierungsformular über Iframe oder MicreSite einbinden? 1 = Iframe, 2 = MicroSite', 0, 0, '', 0, ''),
(328, 96, 'sHANSEATIC_PERCENT', '10', 'Zinsen Angabe in Prozent für Artikel Anzeige', 0, 0, '', 0, ''),
(329, 96, 'sHANSEATIC_STYLE', '', 'Stylesheet für den Hanseatic Frame', 0, 0, '', 0, ''),
(330, 33, 'sACTDPRCHECK', '0', 'Datenschutz-Bedingungen müssen über Checkbox akzeptiert werden', 0, 0, '', 0, 'int'),
(331, 97, 'sSETOFFLINE', '0', 'Shop wegen Wartung sperren', 0, 0, '', 0, 'int'),
(332, 97, 'sOFFLINEIP', '0', 'Von der Sperrung ausgeschlossene IP', 0, 0, '', 0, ''),
(333, 79, 'sLIVEINSTOCK', '1', 'Lagerbestand auf Detailseite in Echtzeit abfragen', 0, 0, '', 0, 'int'),
(334, 12, 'sDELETECACHEAFTERORDER', '0', 'Shopcache nach jeder Bestellung leeren (Performance lastig)', 0, 0, 'Warnung! Kann massive Performance-Einbrüche nach sich ziehen', 0, 'int'),
(335, 33, 'sPAYMENTDEFAULT', '5', 'Fallback-Zahlungsart (ID)', 0, 0, '', 0, ''),
(336, 79, 'sCONFIGMAXCOMBINATIONS', '1000', 'Maximale Anzahl an Konfigurator-Varianten je Artikel', 0, 0, '', 0, ''),
(337, 79, 'sDEACTIVATENOINSTOCK', '0', 'Abverkaufsartikel ohne Lagerbestand deaktivieren', 0, 0, '', 0, 'int'),
(338, 65, 'sIPAYMENT_3DSECURE', '0', '3-D Secure Bild anzeigen?', 0, 0, '', 0, ''),
(341, 98, 'sMAILER_CharSet', 'iso-8859-1', 'Sets the CharSet of the message.', 0, 0, '', 1, ''),
(343, 98, 'sMAILER_Encoding', '8bit', 'Sets the Encoding of the message. Options for this are  "8bit", "7bit", "binary", "base64", and "quoted-printable".', 0, 0, '', 1, ''),
(347, 98, 'sMAILER_Mailer', 'mail', 'Method to send mail: ("mail", "sendmail", or "smtp").', 0, 0, '', 1, ''),
(351, 98, 'sMAILER_Hostname', '', 'Sets the hostname to use in Message-Id and Received headers and as default HELO string. If empty, the value returned by SERVER_NAME is used or "localhost.localdomain".', 0, 0, '', 1, ''),
(353, 98, 'sMAILER_Host', 'localhost', 'Sets the SMTP hosts.  All hosts must be separated by a semicolon.  You can also specify a different port for each host by using this format: [hostname:port] (e.g. "smtp1.example.com:25;smtp2.example.com"). Hosts will be tried in order.', 0, 0, '', 1, ''),
(354, 98, 'sMAILER_Port', '25', 'Sets the default SMTP server port.', 0, 0, '', 1, ''),
(356, 98, 'sMAILER_SMTPSecure', '', 'Sets connection prefix. Options are "", "ssl" or "tls"', 0, 0, '', 1, ''),
(358, 98, 'sMAILER_Username', '', 'Sets SMTP username.', 0, 0, '', 1, ''),
(359, 98, 'sMAILER_Password', '', 'Sets SMTP password.', 0, 0, '', 1, ''),
(378, 99, 'sHEIDELPAY_NOTIFY_EMAIL', '', 'Hier die EMail Adresse des Admin angeben', 1, 0, '', 0, ''),
(379, 99, 'sHEIDELPAY_STYLE', '', 'Hier kann eine CSS Datei angegeben werden, die für die form.php benutzt wird.', 1, 0, '', 0, ''),
(380, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_PAYPAL', '', 'Zusatz Channel für PayPal (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(381, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_MONEYBOOKERS', '', 'Zusatz Channel für Moneybookers (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(382, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_GIROPAY', '', 'Zusatz Channel für Giropay (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(383, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_SOFORT', '', 'Zusatz Channel für Sofortüberweisung (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(384, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_IDEAL', '', 'Zusatz Channel für IDeal (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(385, 99, 'sHEIDELPAY_TRANSACTION_CHANNEL_EPS', '', 'Zusatz Channel für EPS (überschreibt den Hauptchannel wenn gefüllt)', 1, 0, '', 0, ''),
(387, 94, 'sMONEYBOOKERS_INSTALLED_PAYMETHODS', 'ACC', 'Installed Payments', 0, 0, '', 0, ''),
(388, 79, 'sSHOWBUNDLEMAINARTICLE', '1', 'Hauptartikel im Bundle anzeigen', 0, 0, '', 0, 'int'),
(389, 6, 'sCAPTCHACOLOR', '0,0,255', 'Schriftfarbe Captcha (R,G,B)', 1, 1, '', 0, ''),
(390, 71, 'sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT', 'Abschlag-Versandregel (Bestellnummer)', 0, 0, '', 1, ''),
(391, 71, 'sSHIPPINGDISCOUNTNAME', 'Warenkorbrabatt', 'Abschlag-Versandregel (Bezeichnung)', 0, 0, '', 1, ''),
(392, 42, 'sFUZZYSEARCHPRICEFILTER', '5|10|20|50|100|300|600|1000|1500|2500|3500|5000', 'Auswahl Preisfilter', 0, 0, '', 0, ''),
(393, 42, 'sFUZZYSEARCHSELECTPERPAGE', '12|24|36|48', 'Auswahl Ergebnisse pro Seite', 0, 0, '', 0, ''),
(394, 42, 'sFUZZYSEARCHRESULTSPERPAGE', '12', 'Ergebnisse pro Seite', 0, 0, '', 0, ''),
(395, 79, 'sDEACTIVATEBASKETONNOTIFICATION', '1', 'Warenkorb bei eMail-Benachrichtigung ausblenden', 0, 0, 'Warenkorb bei aktivierter eMail-Benachrichtigung und nicht vorhandenem Lagerbestand ausblenden', 0, 'int'),
(396, 72, 'sVOTESENDCALLING', '1', 'Automatische Erinnerung zur Artikelbewertung senden', 0, 0, 'Nach Kauf dem Benutzer an die Artikelbewertung via E-Mail erinnern', 0, 'int'),
(397, 72, 'sVOTECALLINGTIME', '1', 'Tage bis die Erinnerungs-E-Mail verschickt wird', 0, 0, 'Tage bis der Kunde via E-Mail an die Artikel-Bewertung erinnert wird', 0, ''),
(436, 79, 'sDETAILTEMPLATES', ':Standard;../blog/details.tpl:Blog', 'Verf?gbare Templates Detailseite', 0, 0, '', 0, 'textarea'),
(401, 100, 'sROUTERUSEMODREWRITE', '1', 'Mod_Rewrite nutzen', 0, 0, '', 0, 'int'),
(402, 100, 'sROUTERTOLOWER', '1', 'Nur Kleinbuchstaben in den Urls nutzen', 0, 0, '', 1, 'int'),
(403, 100, 'sREDIRECTBASEFILE', '1', 'Startseite ohne Shopkernel in der Url nutzen', 0, 0, '', 0, 'int'),
(404, 100, 'sREDIRECTNOTFOUND', '1', 'Bei nicht vorhandenen Kategorien/Artikel auf Startseite umleiten', 0, 0, '', 0, 'int'),
(405, 100, 'sREDIRECTAFTERRENDER', '1', 'Auf die echte Url im Bestellprozess umleiten', 0, 0, '', 1, 'int'),
(406, 100, 'sSEOMETADESCRIPTION', '1', 'Meta-Description von Artikel/Kategorien aufbereiteten', 0, 0, '', 1, 'int'),
(407, 100, 'sROUTERREMOVECATEGORY', '0', 'KategorieID aus Url entfernen', 0, 0, '', 1, 'int'),
(408, 100, 'sSEOQUERYBLACKLIST', 'sPage,sPerPage,sSupplier,sFilterProperties,p,n,s,f', 'SEO-Nofollow Querys', 0, 0, '', 0, ''),
(409, 100, 'sSEOVIEWPORTBLACKLIST', 'login,ticket,tellafriend,note,support,basket,admin,registerFC,newsletter,search', 'SEO-Nofollow Viewports', 0, 0, '', 0, ''),
(410, 100, 'sSEOREMOVEWHITESPACES', '1', 'überflüssige Leerzeichen / Zeilenumbrüchen entfernen', 0, 0, '', 0, 'int'),
(411, 100, 'sSEOREMOVECOMMENTS', '1', 'Html-Kommentare entfernen', 0, 0, '', 0, 'int'),
(412, 100, 'sSEOQUERYALIAS', 'sSearch=q,\r\nsPage=p,\r\nsPerPage=n,\r\nsSupplier=s,\r\nsFilterProperties=f,\r\nsCategory=c,\r\nsCoreId=u,\r\nsTarget=t,\r\nsValidation=v', 'Query-Aliase', 0, 0, '', 0, 'textarea'),
(413, 100, 'sSEOBACKLINKWHITELIST', 'www.shopware.de,\r\nwww.shopware.ag,\r\nwww.shopware-ag.de', 'SEO-Follow Backlinks', 0, 0, '', 1, 'textarea'),
(414, 100, 'sSEORELCANONICAL', '1', 'SEO-Canonical-Tags nutzen', 0, 0, '', 1, 'int'),
(415, 100, 'sROUTERLASTUPDATE', 'a:1:{i:1;s:19:"2010-10-18 10:38:29";}', 'Datum des letzten Updates', 0, 0, '', 0, ''),
(439, 33, 'sNOACCOUNTDISABLE', '0', '"Kein Kundenkonto" deaktivieren', 0, 0, '', 0, 'int'),
(416, 100, 'sROUTERCACHE', '86400', 'SEO-Urls Cachezeit Tabelle', 0, 0, '', 0, ''),
(417, 100, 'sROUTERURLCACHE', '86400', 'SEO-Urls Cachezeit Urls', 0, 0, '', 0, ''),
(418, 60, 'sORDERSTATEMAILACK', '', 'Bestellstatus - Änderungen CC-Adresse', 0, 0, '', 0, ''),
(419, 79, 'sINSTOCKINFO', '0', 'Lagerbestands-Unterschreitung im Warenkorb anzeigen', 0, 0, '', 0, 'int'),
(420, 30, 'sCATEGORYDETAILLINK', '0', 'Direkt auf Detailspringen, falls nur ein Artikel vorhanden ist', 0, 0, '', 0, 'int'),
(421, 33, 'sDOUBLEEMAILVALIDATION', '0', 'E-Mail Addresse muss zweimal eingegeben werden.', 0, 0, 'E-Mail Addresse muss zweimal eingegeben werden, um Tippfehler zu vermeiden.', 0, 'int'),
(422, 84, 'sBLOGCATEGORY', '3', 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen', 0, 0, '', 1, 'text'),
(423, 84, 'sBLOGLIMIT', '3', 'Anzahl Blog-Einträge auf Startseite', 0, 0, '', 1, 'text'),
(424, 66, 'sNOTIFYKEY', '', 'Benachrichtungsschlüssel', 0, 0, '', 0, ''),
(425, 79, 'sCONFIGCUSTOMFIELDS', 'Freitext 1, Freitext 2', 'Konfigurator Freitextfelder', 0, 0, '', 0, ''),
(426, 101, 'sVATCHECKENDABLED', '0', 'Modul aktivieren', 0, 0, '', 1, 'int'),
(427, 101, 'sVATCHECKADVANCEDNUMBER', '', 'Eigene USt-IdNr. für die Überprüfung', 0, 0, '', 0, ''),
(428, 101, 'sVATCHECKADVANCED', '0', 'Erweiterte Überprüfung aktivieren', 0, 0, '', 1, 'int'),
(429, 101, 'sVATCHECKADVANCEDCOUNTRIES', 'AT', 'gültige Länder für erweiterte Überprüfung', 0, 0, '', 0, ''),
(430, 101, 'sVATCHECKREQUIRED', '0', 'USt-IdNr. als Pflichtfeld markieren', 0, 0, '', 1, 'int'),
(431, 101, 'sVATCHECKDEBUG', '0', 'Erweiterte Fehlerausgabe aktivieren', 0, 0, '', 1, 'int'),
(433, 100, 'sROUTERARTICLETEMPLATE', '{sCategoryPath articleID=$sArticle.id}/{$sArticle.id}/{$sArticle.name}', 'SEO-Urls Artikel-Template', 0, 0, '', 1, ''),
(434, 100, 'sROUTERCATEGORYTEMPLATE', '{sCategoryPath categoryID=$sCategory.id}', 'SEO-Urls Kategorie-Template', 0, 0, '', 1, ''),
(435, 33, 'sNEWSLETTEREXTENDEDFIELDS', '1', 'Erweiterte Felder in Newsletter-Registrierung abfragen', 0, 0, '', 1, 'int'),
(443, 100, 'sSEOSTATICURLS', 'sViewport=sale&sAction=doSale,Bestellung abgeschlossen\r\nsViewport=admin&sAction=orders,{$sConfig.sSnippets.sIndexmyorders}\r\nsViewport=admin&sAction=downloads,{$sConfig.sSnippets.sIndexmyinstantdownloads}\r\nsViewport=admin&sAction=billing,{$sConfig.sSnippets.sIndexchangebillingaddress}\r\nsViewport=admin&sAction=shipping,{$sConfig.sSnippets.sIndexchangedeliveryaddress}\r\nsViewport=admin&sAction=payment,{$sConfig.sSnippets.sIndexchangepayment}\r\nsViewport=admin&sAction=ticketview,{$sConfig.sSnippets.sTicketSysSupportManagement}\r\nsViewport=logout,{$sConfig.sSnippets.sIndexlogout}\r\nsViewport=support&sFid={$sConfig.sINQUIRYID}&sInquiry=basket,{$sConfig.sSnippets.sBasketInquiry}\r\nsViewport=support&sFid={$sConfig.sINQUIRYID}&sInquiry=detail,{$sConfig.sSnippets.sArticlequestionsaboutarticle}\r\n{foreach from=$sConfig.sViewports item=viewport key=viewportID}\r\n{if $viewportID!=search}\r\nsViewport={$viewportID},{$viewport.name}\r\n{/if}\r\n{/foreach}', 'sonstige SEO-Urls', 0, 0, '', 1, 'textarea'),
(438, 0, 'sFUZZYSEARCHLASTUPDATE', '', '', 0, 0, '', 0, ''),
(440, 12, 'sDISABLECACHE', '0', 'Shopcache deaktivieren', 0, 0, '', 0, 'int'),
(442, 101, 'sVATCHECKNOSERVICE', '1', 'Wenn Service nicht erreichbar ist, nur einfach Überpürfung durchführen.', 0, 0, '', 0, 'int'),
(444, 0, 'sUSEDEFAULTTEMPLATES', '1', '', 0, 0, '', 0, 'int');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_groups`
--

CREATE TABLE IF NOT EXISTS `s_core_config_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` int(1) NOT NULL,
  `parent` int(11) NOT NULL,
  `file` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `action` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=104 ;

--
-- Daten für Tabelle `s_core_config_groups`
--

INSERT INTO `s_core_config_groups` (`id`, `name`, `position`, `parent`, `file`, `description`, `action`) VALUES
(2, 'System / Basis-Konfiguration', 0, 0, '', '', ''),
(82, 'Erweiterte Systemeinstellungen', 1, 0, '', '', ''),
(3, 'Lizenzen', 2, 2, 'licences.php', '', ''),
(6, 'System', 1, 2, '', '', ''),
(83, 'Cronjobs / Bereinigung', 8, 0, '', '', ''),
(9, 'Artikel', 3, 0, '', '', ''),
(11, 'Artikel Attribute', 7, 9, 'attributes.php', '', ''),
(12, 'Caching', 2, 81, '', '', ''),
(13, 'Artikel MwSt.-Sätze', 3, 9, 'tax.php', '', ''),
(14, 'Dateipfade', 0, 82, '', '', ''),
(15, 'Nummernkreise', 11, 2, 'numbers.php', '', ''),
(51, 'Status-eMails', 11, 2, 'orderstatemail.php', '', ''),
(17, 'Schnittstellen', 7, 0, '', '', ''),
(22, 'Factory', 2, 82, 'factory.php', '', ''),
(23, 'Viewports', 1, 82, 'viewports.php', '', ''),
(48, 'Aktionen', 5, 38, '', '', ''),
(29, 'Artikelnummern', 1, 9, '', '', ''),
(72, 'Artikel-Bewertungen', 8, 9, '', '', ''),
(30, 'Kategorien / Listen', 1, 75, '', '', ''),
(31, 'Thumbnails', 4, 75, '', '', ''),
(75, 'Storefront', 4, 0, '', '', ''),
(33, 'Anmeldung / Registrierung', 0, 75, '', '', ''),
(76, 'Topseller / Neuheiten', 2, 75, '', '', ''),
(77, 'Cross-Selling / Ähnliche Art.', 3, 75, '', '', ''),
(79, 'Warenkorb / Artikeldetails', 5, 75, '', '', ''),
(80, 'Artikelverlauf', 6, 75, '', '', ''),
(71, 'Rabatte / Zuschläge', 5, 2, '', '', ''),
(35, 'Sonstige MwSt.-Sätze', 4, 9, '', '', ''),
(38, 'Module', 6, 0, '', '', ''),
(39, 'Tagwolke', 8, 75, '', '', ''),
(40, 'ESD', 2, 38, '', '', ''),
(41, 'Kundengruppen', 4, 2, 'customergroups.php', '', ''),
(42, 'Intelligente Suche', 4, 38, 'search.php', '', ''),
(45, 'PDF-Belegerstellung', 12, 2, '', '', ''),
(47, 'Preiseinheiten', 6, 9, 'units.php', '', ''),
(52, 'Länderauswahl', 6, 2, 'countries.php', '', ''),
(54, 'Trusted-Shops', 0, 17, 'trusted.php', '', ''),
(55, 'Admin-Oberfläche', 7, 2, '', '', ''),
(56, 'Shopware API', 3, 17, 'api.php', '', ''),
(59, 'Subshops', 3, 2, 'multilanguage.php', '', ''),
(60, 'eMail-Einstellungen', 10, 2, '', '', ''),
(61, 'Cronjobs', 8, 83, 'cronjobs.php', '', ''),
(64, 'Währungen', 4, 2, 'currencies.php', '', ''),
(65, 'iPayment', 0, 17, '', '', ''),
(66, 'Sofortüberweisung', 0, 17, '', '', ''),
(68, 'Preisgruppen', 5, 9, 'pricegroups.php', '', ''),
(69, 'Performance-Monitor', 8, 81, 'perf.php', '', ''),
(70, 'Produktvergleiche / Filter', 9, 38, '', '', ''),
(74, 'Bereinigungsscript', 9, 83, '../../core/php/cleanup_settings.php', '', ''),
(81, 'Performance', 2, 0, '', '', ''),
(84, 'CMS-Funktionen', 7, 75, '', '', ''),
(85, 'Debugging', 3, 82, '', '', ''),
(86, 'Hookpoints', 5, 82, 'hookpoints.php', '', ''),
(87, 'ClickandBuy', 0, 17, '', '<img src="../../connectors/clickandbuy/images/clickandbuy_logo1.gif" width="408" height="90"> <br>Mehr Geld verdienen und mehr Umsatz machen! <br>Sicher, schnell und einfach Zahlungen empfangen! <br>Kostenlose Anmeldung, Keine Grundgebühr! <br><a target="_blank" href="http://www.clickandbuy.com/DE/de/merchantportal/home.html"><strong>Mehr Infos zu ClickandBuy!</strong></a> <br><a target="_blank" href="../../connectors/clickandbuy/mreg.php"><strong>ClickandBuy Registrierung!</strong></a><br> <br /> <strong>Achtung! Diese Zahlungsart bietet keine Subshop-Unterstützung. Sie müssen die Zahlungart also per Risk-Management auf den zu verwendenen Shop beschränken!</strong>', ''),
(88, 'PayPal (Express)', 0, 17, '', '<div style="display: block; border: 1px solid rgb(169, 169, 169); margin: 0pt 5px 20px; padding: 5px; background-color: rgb(255, 255, 255); font-size: 12px;">\r\n<p style="font-weight: bold; margin: 4px 0pt;">PayPal &ndash; Ihr Partner für Online-Zahlungen</p>\r\n<p>Als einer der führenden Anbieter von Online-Zahlungslösungen ist PayPal Ihr verlässlicher Partner für schnelle und sichere Zahlungen in Ihrem Online-Shop. Mit PayPal empfangen Sie Zahlungen online genauso einfach wie an der Ladenkasse. Denn Sie aktivieren PayPal in Minuten und bieten allein in Deutschland Nutzern von über 10 Millionen PayPal-Konten sofort alle gängigen Zahlungsmethoden an. Sie können Ihren Umsatz nachweislich um bis zu 16% steigern und profitieren von umfassendem Risikomanagement.</p>\r\n\r\n<p>Und: PayPal kostet Sie nur dann etwas, wenn Ihr Kunde damit bezahlt.</p>\r\n\r\n<p><a target="_blank" href="http://altfarm.mediaplex.com/ad/ck/3484-50686-12439-4?ID=1">Zur PayPal-Anmeldung</a></p>\r\n\r\n<p style="font-weight: bold; margin: 20px 0px 4px 0px;">Hinweis:</p> Wenn Sie den PayPal Zahlungsmodus auf reserviert ändern, können die reservierten Zahlungen später manuell über Kunden -&gt; Zahlungen -&gt; PayPal eingezogen werden.</div>', ''),
(89, 'Saferpay', 0, 17, '', '<strong>Hinweis:</strong>* Wenn Sie den Sayferpay Zahlungsmodus auf reserviert ändern, können die reservierten Zahlungen später manuell über Kunden -> Zahlungen -> Sayferpay eingezogen werden.<br /><br />**Styling-Attribute zur farblichen Anpassung des Saferpay VT (optional) <a target="_blanc" href="../../../templates/0/de/media/img/default/store/saferpay_styling.jpg">Hilfe</a><br /><br />', ''),
(90, 'Ticket-System', 10, 38, '', '', ''),
(93, 'Premium-Versandkosten', 11, 38, '', '', ''),
(94, 'Moneybookers', 0, 17, '../../connectors/moneybookers/config.php', 'Moneybookers', ''),
(95, 'ClickPay', 0, 17, '', 'Konfigurationseite für das ClickPay Zahlungsmodul.', ''),
(97, 'Wartung', 13, 2, '', '', ''),
(98, 'Mailer', 12, 2, '', '', ''),
(100, 'SEO', 12, 38, '', '', ''),
(101, 'USt-IdNr. Überprüfung', 11, 38, '', '', ''),
(102, 'Alte Templatebasis', 2, 45, 'documents.php', '', ''),
(103, 'Neue Templatebasis', 2, 45, '', '', 'backend/document/settings');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_mails`
--

CREATE TABLE IF NOT EXISTS `s_core_config_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `frommail` varchar(255) NOT NULL,
  `fromname` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `contentHTML` text NOT NULL,
  `ishtml` int(1) NOT NULL,
  `htmlable` int(1) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

--
-- Daten für Tabelle `s_core_config_mails`
--

INSERT INTO `s_core_config_mails` (`id`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `htmlable`, `attachment`) VALUES
(1, 'sREGISTERCONFIRMATION', 'info@example.com', 'Shopware Demo', 'Ihre Anmeldung im Demoshop ', 'Hallo {salutation} {firstname} {lastname},\r\n \r\nvielen Dank für Ihre Anmeldung in unserem Shop.\r\n \r\nSie erhalten Zugriff über Ihre eMail-Adresse {sMAIL}\r\nund dem von Ihnen gewählten Kennwort.\r\n \r\nSie können sich Ihr Kennwort jederzeit per eMail erneut zuschicken lassen.\r\n \r\nMit freundlichen Grüßen,\r\n \r\nIhr Team der shopware AG', '<div style="font-family:arial; font-size:12px;">\r\n<img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="Logo" />\r\n<p>\r\nHallo {salutation} {firstname} {lastname},<br/><br/>\r\n \r\nvielen Dank für Ihre Anmeldung in unserem Shop.<br/><br/>\r\n \r\nSie erhalten Zugriff über Ihre eMail-Adresse <strong>{sMAIL}</strong><br/>\r\nund dem von Ihnen gewählten Kennwort.<br/><br/>\r\n \r\nSie können sich Ihr Kennwort jederzeit per eMail erneut zuschicken lassen.<br/><br/>\r\n \r\nMit freundlichen Grüßen,<br/><br/>\r\n \r\nIhr Team der shopware AG\r\n</p>\r\n</div>', 1, 1, '1.png;test.pdf/2.png;test2.pdf'),
(39, 'sBIRTHDAY', '{$sConfig.sMAIL}', '{$sConfig.sSHOPNAME}', 'Herzlichen Glückwunsch zum Geburtstag von {$sConfig.sSHOPNAME}', 'Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.firstname} {$sUser.lastname},\r\n\r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}', '', 0, 0, ''),
(2, 'sORDER', 'info@example.com', 'Shopware 3.0 Demo', 'Ihre Bestellung im Demoshop', 'Hallo {$billingaddress.firstname} {$billingaddress.lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\nInformationen zu Ihrer Bestellung:\r\n \r\nPos. Art.Nr.              Menge         Preis        Summe\r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\r\n{$details.articlename|wordwrap:49|indent:5}\r\n{/foreach}\r\n \r\nVersandkosten: {$sShippingCosts}\r\nGesamtkosten Netto: {$sAmountNet}\r\n{if !$sNet}\r\nGesamtkosten Brutto: {$sAmount}\r\n{/if}\r\n \r\nGewählte Zahlungsart: {$additional.payment.description}\r\n{$additional.payment.additionaldescription}\r\n{if $additional.payment.name == "debit"}\r\nIhre Bankverbindung:\r\nKontonr: {$sPaymentTable.account}\r\nBLZ:{$sPaymentTable.bankcode}\r\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\r\n{/if}\r\n{if $additional.payment.name == "prepayment"}\r\n \r\nUnsere Bankverbindung:\r\nKonto: ###\r\nBLZ: ###\r\n{/if}\r\n \r\n{if $sComment}\r\nIhr Kommentar:\r\n{$sComment}\r\n{/if}\r\n \r\nRechnungsadresse:\r\n{$billingaddress.company}\r\n{$billingaddress.firstname} {$billingaddress.lastname}\r\n{$billingaddress.street} {$billingaddress.streetnumber}\r\n{$billingaddress.zipcode} {$billingaddress.city}\r\n{$billingaddress.phone}\r\n{$additional.country.countryname}\r\n \r\nLieferadresse:\r\n{$shippingaddress.company}\r\n{$shippingaddress.firstname} {$shippingaddress.lastname}\r\n{$shippingaddress.street} {$shippingaddress.streetnumber}\r\n{$shippingaddress.zipcode} {$shippingaddress.city}\r\n{$additional.country.countryname}\r\n \r\n{if $billingaddress.ustid}\r\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\r\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\r\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\r\n{/if}\r\n \r\n \r\nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt:\r\n \r\nWir wünschen Ihnen noch einen schönen Tag.\r\n \r\nHier Ihre Kontaktdaten eingeben\r\n ', '<div style="font-family:arial; font-size:12px;">\r\n<img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="Logo" />\r\n \r\n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\r\n \r\nvielen Dank fuer Ihre Bestellung bei {$sConfig.sSHOPNAME} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\n<br/>\r\n<br/>\r\n<strong>Informationen zu Ihrer Bestellung:</strong></p>\r\n  <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">\r\n    <tr>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Summe</strong></td>\r\n    </tr>\r\n \r\n    {foreach item=details key=position from=$sOrderDetails}\r\n    <tr>\r\n      <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.1}<img src="{$details.image.src.1}" alt="{$details.articlename}" />{else} {/if}</td>\r\n      <td>{$position+1|fill:4} </td>\r\n      <td>{$details.ordernumber|fill:20}</td>\r\n      <td>{$details.quantity|fill:6}</td>\r\n      <td>{$details.price|padding:8}{$sCurrency}</td>\r\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\r\n    </tr>\r\n    <tr>\r\n      <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>\r\n    </tr>\r\n    {/foreach}\r\n \r\n  </table>\r\n \r\n<p>\r\n  <br/>\r\n  <br/>\r\n    Versandkosten: {$sShippingCosts}<br/>\r\n    Gesamtkosten Netto: {$sAmountNet}<br/>\r\n    {if !$sNet}\r\n    Gesamtkosten Brutto: {$sAmount}<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>\r\n    {$additional.payment.additionaldescription}\r\n    {if $additional.payment.name == "debit"}\r\n    Ihre Bankverbindung:<br/>\r\n    Kontonr: {$sPaymentTable.account}<br/>\r\n    BLZ:{$sPaymentTable.bankcode}<br/>\r\n    Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    {if $additional.payment.name == "prepayment"}\r\n    Unsere Bankverbindung:<br/>\r\n    Konto: ###<br/>\r\n    BLZ: ###<br/>\r\n    {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\r\n</p>\r\n<p>\r\n  {if $sComment}\r\n    <strong>Ihr Kommentar:</strong><br/>\r\n    {$sComment}<br/>\r\n  {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Rechnungsadresse:</strong><br/>\r\n    {$billingaddress.company}<br/>\r\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\r\n    {$billingaddress.street} {$billingaddress.streetnumber}<br/>\r\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\r\n    {$billingaddress.phone}<br/>\r\n    {$additional.country.countryname}<br/>\r\n  <br/>\r\n  <br/>\r\n    <strong>Lieferadresse:</strong><br/>\r\n    {$shippingaddress.company}<br/>\r\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\r\n    {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\r\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\r\n    {$additional.countryShipping.countryname}<br/>\r\n  <br/>\r\n    {if $billingaddress.ustid}\r\n    Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\r\n    Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>\r\n    bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt: <br/>\r\n    <br/>\r\n    Mit freundlichen Grüßen,<br/>\r\n    Ihr Team von {$sConfig.sSHOPNAME}<br/>\r\n</p>\r\n</div>', 1, 1, '1.png;test.pdf/2.png;test2.pdf'),
(3, 'sTELLAFRIEND', 'info@example.com', 'Shopware Demo', '{sName} empfiehlt Ihnen {sArticle}', 'Hallo,\r\n\r\n{sName} hat für Sie bei {sShop} ein interessantes Produkt gefunden, dass Sie sich anschauen sollten:\r\n\r\n{sArticle}\r\n{sLink}\r\n\r\n{sComment}\r\n\r\nBis zum naechsten Mal und mit freundlichen Gruessen,\r\n\r\nIhre Kontaktdaten', '', 0, 1, ''),
(4, 'sPASSWORD', 'info@example.com', 'Shopware Demo', 'Passwort vergessen - Ihre Zugangsdaten zu {sShop}', 'Hallo,\r\n\r\nihre Zugangsdaten zu {sShopURL} lauten wie folgt:\r\nBenutzer: {sMail}\r\nPasswort: {sPassword}\r\n\r\nBis zum naechsten Mal und mit freundlichen Gruessen,\r\n\r\nKontaktdaten', '', 0, 1, ''),
(5, 'sNOSERIALS', 'info@example.com', 'Shopware Demo', 'Achtung - keine freien Seriennummern für {sArticleName}', 'Hallo,\r\n\r\nes sind keine weiteren freien Seriennummern für den Artikel {sArticleName} verfügbar. Bitte stellen Sie umgehend neue Seriennummern ein oder deaktivieren Sie den Artikel. Bitte teilen Sie dem Kunden {sMail} manuell eine Seriennummer zu.\r\n\r\nViele Grüße,\r\n\r\nIhre Shopware\r\n', '', 0, 0, ''),
(6, 'sCHEAPER', 'info@example.com', 'Shopware Demo', '{sArticle} günstiger gesehen', 'Hallo,\r\n\r\n{sName} hat den Artikel {sArticle} günstiger entdeckt. \r\n\r\n{sArticle}\r\n{sLink}\r\n\r\nShopware ', '', 0, 0, ''),
(7, 'sVOUCHER', 'info@example.com', 'Shopware Demo', 'Ihr Gutschein', 'Hallo {customer},\r\n\r\n{user} ist Ihrer Empfehlung gefolgt und hat so eben im Demoshop bestellt.\r\nWir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.\r\n			\r\nIhr Gutschein-Code lautet: XXX\r\n			\r\nKontaktdaten\r\n', '', 0, 1, ''),
(8, 'sSERVICE1ACCEPTED', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung wegen Defekt wurde akzeptiert', ' Rücksendung wegen Defekt \r\n=========================\r\n\r\nHallo,\r\nbitte senden Sie den von Ihnen beschriebenen Artikel \r\nper Post ausreichend frankiert zurück.\r\n\r\nUNFREIE SENDUNGEN KÖNNEN \r\nLEIDER NICHT ANGENOMMEN WERDEN.\r\n\r\nWir werden Ihnen bei berechtigter Reklamation die \r\nRücksendekosten gutschreiben.\r\n\r\nNach Überprüfung des Defektes durch \r\nunsere Techniker wird entschieden,  ob eine kurzfristige \r\nReparatur möglich ist, oder ob Sie einen Austausch\r\nfür den defekten Artikel erhalten. Danach wird der \r\nArtikel von uns zum Hersteller zur Reparatur bzw. zum \r\nAustausch geschickt. Im Anschluß an eine Reparatur \r\nwird der Artikel nochmals von uns getestet und Ihnen danach \r\nsofort zugestellt. Dauert eine Reparatur wider Erwarten zu lange, \r\nwird mit Ihnen abgestimmt, ob doch ein Austausch von \r\nuns vorgenommen wird.\r\n\r\nWir bitten Sie um Verständnis, dass wir nur bei tat-\r\nsächlich vorhandenen Mängeln einen Austausch \r\noder eine für Sie kostenlose Reparatur vornehmen können. \r\nSollte hingegen kein Mangel vorliegen, so behalten wir uns vor, \r\nunseren Überprüfungsaufwand und die Rück-\r\nsendekosten an Sie in Rechnung zu stellen.\r\n\r\nIhre Rücksendenummer lautet RMA{number} \r\n(Bitte deutlich auf das Paket schreiben) \r\n\r\nWenn Sie bereits eine ausführliche Fehlerbeschreibung in das\r\nOnline-Service-Formular geschrieben haben, müssen Sie der\r\nRücksendung keine Fehlerbeschreibung mehr beilegen.\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, 0, ''),
(9, 'sSERVICE1REJECTED', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung wegen Defekt wurde abgelehnt', ' Rücksendung wegen Defekt \r\n=========================\r\n\r\nHallo,\r\n\r\nleider müssen wir die Reklamation ablehnen, aus folgendem Grund: \r\n\r\n\r\n  \r\nViele Grüße\r\nShopware2.de', '', 0, 0, ''),
(38, 'sORDERSTATEMAIL7', '', '', '', '', '', 0, 0, ''),
(15, 'sORDERSTATEMAIL2 ', '', '', '', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.', '', 0, 0, ''),
(13, 'sCUSTOMERGROUPHREJECTED', 'info@example.com', 'Shopware Demo', 'Ihr Händleraccount wurde abgelehnt', 'Sehr geehrter Kunde,\r\n\r\nvielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.\r\n\r\nBei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.\r\n\r\nMit freundlichen Grüßen\r\n\r\nIhr Shopware2.de Team', '', 0, 0, ''),
(14, 'sORDERSTATEMAIL1', '{$sConfig.sMAIL}', '{$sConfig.sSHOPNAME}', 'Ihr Bestellung bei {$sConfig.sSHOPNAME}', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.', '', 0, 0, ''),
(19, 'sCANCELEDQUESTION', 'info@example.com', 'Shopware Demo', 'Ihre abgebrochene Bestellung - Jetzt Feedback geben und Gutschein kassieren', 'Lieber Kunde,\r\n \r\nsie haben vor kurzem Ihre Bestellung auf Demoshop.de nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist.\r\n \r\nBitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.\r\n \r\nVielen Dank für Ihre Unterstützung.', '', 0, 0, ''),
(10, 'sSERVICE2ACCEPTED', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung gemäß Rückgaberecht wurde akzeptiert', 'R Ü C K S E N D U N G   W E G E N  N I C H T G E F A L L E N S\r\n(gemäß 14 tägiges Widerrufsrecht nach Erhalt der Ware)\r\n==================================================\r\n\r\nHallo,\r\n\r\nbitte senden Sie den von Ihnen beschriebenen Artikel per Post, mit einer Rechnungskopie und unter Angabe des Grundes ausreichend frankiert zurück.\r\n\r\nBitte haben Sie Verständnis dafür das unfreie Sendungen nicht angenommen werden können.\r\n\r\nBitte senden Sie das Produkt innerhalb der nächsten 7 Tage originalverpackt inkl. allem Zubehör an uns zurück\r\nsowie ohne Gebrauchsspuren. Sollte sich die zurückgesendete Ware in einem Zustand befinden, in dem sie nicht\r\nals Neuware verkauft werden kann, müssen Sie mit einem Abzug für diese Wertminderung bei der Gutschrift rechnen.\r\n\r\n\r\nIhre Rücksendenummer lautet RMA{number} (Bitte deutlich auf das Paket schreiben)\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, 0, ''),
(36, 'sORDERSTATEMAIL19', '', '', '', '', '', 0, 0, ''),
(37, 'sORDERSTATEMAIL20', '', '', '', '', '', 0, 0, ''),
(11, 'sSERVICE2REJECTED', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung gemäß Rückgaberecht wurde abgelehnt', 'Rücksendung wegen Widerrufsrecht\r\n======================================\r\n\r\nHallo,\r\n\r\nleider müssen wir Ihren Widerruf ablehnen, aus folgendem Grund: \r\n\r\n\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, 0, ''),
(35, 'sORDERSTATEMAIL18', '', '', '', '', '', 0, 0, ''),
(12, 'sCUSTOMERGROUPHACCEPTED', 'info@example.com', 'Shopware Demo', 'Ihr Händleraccount wurde freigeschaltet', 'Hallo,\r\n\r\nIhr Händleraccount auf "Shopware Demo" wurde freigeschaltet\r\n  \r\nAb sofort kaufen Sie zum Netto-EK bei uns ein.\r\n  \r\nMit freundlichen Grüßen,\r\n  \r\nDas Team von Shopware2.de', '', 0, 0, ''),
(20, 'sCANCELEDVOUCHER', 'info@example.com', 'Shopware Demo', 'Ihre abgebrochene Bestellung - Gutschein-Code anbei', 'Lieber Kunde,\r\n \r\nsie haben vor kurzem Ihre Bestellung auf Demoshop.de nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen 5,00 € Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung auf demoshop.de erleichtern.\r\n \r\nIhr Gutschein ist 2 Monate gültig und kann mit dem Code "{$sVouchercode}" eingelöst werden.\r\n\r\nWir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.\r\n', '', 0, 0, ''),
(21, 'sORDERSTATEMAIL9', '', '', '', '', '', 0, 0, ''),
(22, 'sORDERSTATEMAIL10', '', '', '', '', '', 0, 0, ''),
(23, 'sORDERSTATEMAIL11', 'info@example.com', 'Shopware Demoshop', 'teilweise verschickt', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.', '', 0, 0, ''),
(34, 'sORDERSTATEMAIL6', 'info@example.com', 'info@example.com', 'Ihr Bestellung bei Shopware', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n \r\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\r\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\r\n\r\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\r\n \r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}', '', 0, 0, ''),
(30, 'sORDERSTATEMAIL8', 'info@example.com', 'info@example.com', 'Ihr Bestellung bei Shopware', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n \r\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\r\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\r\n\r\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\r\n \r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}', '', 0, 0, ''),
(33, 'sORDERSTATEMAIL4', 'info@example.com', 'info@example.com', 'Ihr Bestellung bei Shopware', 'Hallo {if $sUser.billing_salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n \r\nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\r\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\r\n\r\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.\r\n \r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}', '', 0, 0, ''),
(24, 'sORDERSTATEMAIL13', '', '', '', '', '', 0, 0, ''),
(25, 'sORDERSTATEMAIL16', '', '', '', '', '', 0, 0, ''),
(41, 'sNEWSLETTERCONFIRMATION', 'info@example.com', 'info@example.com', 'Vielen Dank für Ihre Newsletter-Anmeldung', 'Hallo,\r\n\r\nvielen Dank für Ihre Newsletter-Anmeldung auf www.shopwarelive.de\r\n\r\n[Text einfügen]', '', 0, 1, ''),
(26, 'sORDERSTATEMAIL15', '', '', '', '', '', 0, 0, ''),
(27, 'sORDERSTATEMAIL14', '', '', '', '', '', 0, 0, ''),
(40, 'sARTICLESTOCK', '{$sConfig.sMAIL}', '{$sConfig.sSHOPNAME}', 'Lagerbestand von {$sData.count} Artikel{if $sData.count>1}n{/if} unter Mindestbestand ', 'Hallo,\r\nfolgende Artikel haben den Mindestbestand unterschritten:\r\nBestellnummer Artikelname Bestand/Mindestbestand \r\n{foreach from=$sJob.articles item=sArticle key=key}\r\n{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin} \r\n{/foreach}\r\n', '', 1, 0, ''),
(28, 'sORDERSTATEMAIL12', '', '', '', '', '', 0, 0, ''),
(29, 'sORDERSTATEMAIL5', 'info@example.com', 'Shopware Demo', 'Ihr Bestellung bei Shopware', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} \r\n{$sUser.billing_firstname} {$sUser.billing_lastname},\r\n \r\nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d.%m.%Y"} \r\nhat sich geändert. Der neun Staus lautet nun {$sOrder.status_description}.\r\n \r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}', '', 0, 0, ''),
(31, 'sORDERSTATEMAIL3', 'info@example.com', 'info@example.com', 'Status-Änderung', 'Sehr geehrte{if $sUser.billing_salutation eq "mr"}r Herr{elseif $sUser.billing_salutation eq "ms"} Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n \r\nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d.%m.%Y"} \r\nhat sich geändert. Der neue Staus lautet nun "{$sOrder.status_description}".\r\n \r\n \r\nInformationen zu Ihrer Bestellung:\r\n================================== \r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:3} {$details.articleordernumber|fill:10:" ":"..."} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:"%.2f"} {$sConfig.sCURRENCY}\r\n{/foreach}\r\n \r\nVersandkosten: {$sOrder.invoice_shipping} {$sConfig.sCURRENCY}\r\nNetto-Gesamt: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sConfig.sCURRENCY}\r\nGesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:"%.2f"} {$sConfig.sCURRENCY}\r\n \r\nMit freundlichen Grüßen,\r\nIhr Team von {$sConfig.sSHOPNAME}\r\n\r\n', '', 0, 0, ''),
(32, 'sORDERSTATEMAIL17', '', '', '', '', '', 0, 0, ''),
(42, 'sOPTINNEWSLETTER', 'info@example.com', 'info@example.com', 'Bitte bestätigen Sie Ihre Newsletter-Anmeldung', 'Hallo, vielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter. Bitte bestätigen Sie die Anmeldung über den nachfolgenden Link: {$sConfirmLink} Viele Grüße', '', 0, 1, ''),
(43, 'sOPTINVOTE', 'info@example.com', 'info@example.com', 'Bitte bestätigen Sie Ihre Artikel-Bewertung', 'Hallo, vielen Dank für die Bewertung des Artikels {$sArticle.articleName}. Bitte bestätigen Sie die Bewertung über nach den nachfolgenden Link: {$sConfirmLink} Viele Grüße', '', 0, 1, ''),
(44, 'sARTICLEAVAILABLE', 'info@example.com', 'Shopware 3.0 Demo', 'Ihr Artikel ist wieder verfügbar', 'Hallo, \r\n\r\nIhr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar. \r\n\r\n{$sArticleLink} \r\n\r\nViele Grüße Ihr Shopware 3.0 Demo Team ', '', 0, 0, ''),
(45, 'sACCEPTNOTIFICATION', 'info@example.com', 'Shopware 3.0 Demo', 'Bitte bestätigen Sie Ihre E-Mail-Benachrichtigung', 'Hallo, \r\n\r\nvielen Dank, dass Sie sich für die automatische e-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben. \r\nBitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link: \r\n\r\n{$sConfirmLink} \r\n\r\nViele Grüße Ihr Shopware 3.0 Demo Team ', '', 0, 0, ''),
(46, 'sARTICLECOMMENT', 'info@example.com', 'Shopware Demo', 'Artikel bewerten', '<p>Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n</p>\r\nSie haben bei uns vor einigen Tagen Produkte bei uns unter shopwareAG gekauft. Wir würden uns freuen, wenn Sie diese Produkte bewerten würden. So helfen Sie uns, unseren Service weiter zu steigern, und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen. \r\nÜbrigens, Sie müssen natürlich nicht jeden gekauften Artikel kommentieren, nehmen Sie einfach die wozu Sie Lust haben, wir freuen uns über jedes Feedback.\r\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\r\n<p>\r\n</p>\r\n<table>\r\n {foreach from=$sArticles item=sArticle key=key}\r\n{if !$sArticle.modus}\r\n <tr>\r\n  <td>{$sArticle.articleordernumber}</td>\r\n  <td>{$sArticle.name}</td>\r\n  <td>\r\n  <a href="{$sArticle.link}#bewertungen">link</a>\r\n  </td>\r\n </tr>\r\n{/if}\r\n {/foreach}\r\n</table>\r\n\r\n<p>\r\nMit freundlichen Grüßen,<br />\r\nIhr Team von shopwareAG\r\n</p>', '<p>Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n</p>\r\nSie haben bei uns vor einigen Tagen Produkte bei uns unter shopwareAG gekauft. Wir würden uns freuen, wenn Sie diese Produkte bewerten würden. So helfen Sie uns, unseren Service weiter zu steigern, und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen. \r\nÜbrigens, Sie müssen natürlich nicht jeden gekauften Artikel kommentieren, nehmen Sie einfach die wozu Sie Lust haben, wir freuen uns über jedes Feedback.\r\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\r\n<p>\r\n</p>\r\n<table>\r\n {foreach from=$sArticles item=sArticle key=key}\r\n{if !$sArticle.modus}\r\n <tr>\r\n  <td>{$sArticle.articleordernumber}</td>\r\n  <td>{$sArticle.name}</td>\r\n  <td>\r\n  <a href="{$sArticle.link}#bewertungen">link</a>\r\n  </td>\r\n </tr>\r\n{/if}\r\n {/foreach}\r\n</table>\r\n\r\n<p>\r\nMit freundlichen Grüßen,<br />\r\nIhr Team von shopwareAG\r\n</p>', 1, 1, ''),
(47, 'PluginCouponsSendCoupon', 'info@example.com', 'Shopware Demo', 'Ihre Gutschein Bestellung', 'Hallo {$sUser.billing_firstname}{$sUser.billing_lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrder.ordernumber}).\r\n\r\nAnbei schicken wir Ihnen die bestellten Gutschein-Codes.\r\n\r\n{$EventResult.code}\r\n\r\nViele Grüße,\r\n\r\nIhr Team von Shopware', '', 0, 0, ''),
(48, 'PluginCouponsInformMerchant', 'info@example.com', 'Shopware Demo', 'Gutschein Bestellung - Keine oder wenige Codes vorhanden', 'Hallo,\r\n\r\nfür die Gutschein-Bestellung mit der Bestellnummer {$Ordernumber} stehen keine oder wenige Gutschein-Codes zur Verfügung! Bitte prüfen Sie, ob dieser Bestellung ein Gutschein zugeordnet werden konnte und schicken Sie dem Kunden ggf. manuell einen Gutschein-Code.\r\n', '', 0, 0, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_text`
--

CREATE TABLE IF NOT EXISTS `s_core_config_text` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `description` text NOT NULL,
  `created` datetime DEFAULT NULL,
  `modified` datetime DEFAULT NULL,
  `locale` varchar(255) NOT NULL,
  `namespace` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`locale`,`namespace`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3839 ;

--
-- Daten für Tabelle `s_core_config_text`
--

INSERT INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`, `created`, `modified`, `locale`, `namespace`) VALUES
(1, 23, 'sErrorLogin', 'Ihre Zugangsdaten konnten keinem Benutzer zugeordnet werden', '', NULL, NULL, 'de_DE', 'Frontend'),
(2, 23, 'sErrorMerchantNotActive', 'Sie sind nicht als Händler registriert oder Ihr Account ist noch nicht freigeschaltet', '', NULL, NULL, 'de_DE', 'Frontend'),
(3, 23, 'sErrorEmail', 'Bitte geben Sie eine gültige eMail-Adresse ein', '', NULL, NULL, 'de_DE', 'Frontend'),
(4, 23, 'sErrorPassword', 'Bitte wählen Sie ein Passwort welches aus mindestens 6 Zeichen besteht', '', NULL, NULL, 'de_DE', 'Frontend'),
(5, 23, 'sErrorEmailNotFound', 'Diese eMail-Adresse wurde nicht gefunden', '', NULL, NULL, 'de_DE', 'Frontend'),
(6, 23, 'sErrorBillingAdress', 'Bitte füllen Sie alle rot markierten Felder aus', '', NULL, NULL, 'de_DE', 'Frontend'),
(7, 23, 'sErrorShippingAddress', 'Bitte füllen Sie alle rot markierten Felder aus', '', NULL, NULL, 'de_DE', 'Frontend'),
(8, 23, 'sErrorForgotMail', 'Bitte geben Sie Ihre eMail-Adresse ein', '', NULL, NULL, 'de_DE', 'Frontend'),
(9, 23, 'sErrorForgotMailUnknown', 'Diese Mailadresse ist uns nicht bekannt', '', NULL, NULL, 'de_DE', 'Frontend'),
(10, 23, 'sVoucherNotFound', 'Gutschein konnte nicht gefunden werden oder ist nicht mehr gültig', '', NULL, NULL, 'de_DE', 'Frontend'),
(11, 23, 'sVoucherOnlyOnePerOrder', 'Pro Bestellung kann nur ein Gutschein eingelöst werden', '', NULL, NULL, 'de_DE', 'Frontend'),
(12, 23, 'sVoucherBoundToSupplier', 'Dieser Gutschein ist nur für Produkte von {sSupplier} gültig', '', NULL, NULL, 'de_DE', 'Frontend'),
(13, 23, 'sVoucherMinimumCharge', 'Der Mindestumsatz für diesen Gutschein beträgt {sMinimumCharge} €', '', NULL, NULL, 'de_DE', 'Frontend'),
(14, 23, 'sErrorLoginActive', 'Ihr Kundenkonto wurde deaktiviert, bitte wenden Sie sich zwecks Klärung persönlich an uns!', '', NULL, NULL, 'de_DE', 'Frontend'),
(15, 23, 'sPaymentEsd', 'Online-Buchung', '', NULL, NULL, 'de_DE', 'Frontend'),
(16, 23, 'sVoucherAlreadyCashed', 'Dieser Gutschein wurde bereits bei einer vorherigen Bestellung eingelöst', '', NULL, NULL, 'de_DE', 'Frontend'),
(17, 23, 'sErrorCookiesDisabled', 'Um diese Funktion nutzen zu können, müssen Sie Cookies in Ihrem Browser aktivieren', '', NULL, NULL, 'de_DE', 'Frontend'),
(18, 12, 'sAGBText', 'Ich habe die <a href="{$sBasefile}?sViewport=custom&cCUSTOM=4" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.', '', NULL, NULL, 'de_DE', 'Frontend'),
(19, 12, 'sContact_right', '<strong>Demoshop<br />\r\n</strong><br />\r\nFügen Sie hier Ihre Kontaktdaten ein', '', NULL, NULL, 'de_DE', 'Frontend'),
(20, 12, 'sErrorUnknow', 'Unbekannter Fehler ist aufgetreten', '', NULL, NULL, 'de_DE', 'Frontend'),
(21, 12, 'sInfoEmailDeleted', 'Ihre eMail-Adresse wurde gelöscht', '', NULL, NULL, 'de_DE', 'Frontend'),
(22, 12, 'sInfoEmailRegiested', 'Vielen Dank. Wir haben Ihre Adresse eingetragen.', '', NULL, NULL, 'de_DE', 'Frontend'),
(23, 12, 'sOrderInfo', 'Optionaler FreitextBei Zahlung per Bankeinzug oder per Kreditkarte erfolgt die Belastung Ihres Kontos fünf Tage nach Bestellung der Ware.', '', NULL, NULL, 'de_DE', 'Frontend'),
(24, 12, 'sRegister_advantage', '<h2>Meine Vorteile</h2>\r\n<ul>\r\n<li>Schnelleres einkaufen</li>\r\n<li>Speichern Sie Ihre Benutzerdaten und Einstellungen</li>\r\n<li>Einblick in Ihre Bestellungen inkl. Sendungsauskunft</li>\r\n<li>Verwalten Sie Ihr Newsletter-Abo</li>\r\n</ul>', '', NULL, NULL, 'de_DE', 'Frontend'),
(25, 12, 'sRegister_right', '<p>\r\nFügen Sie hier Ihre Widerrufsbelehrung ein.\r\n<br /><br />\r\n<a href="{$sBasefile}?sViewport=custom&cCUSTOM=8" title="Widerrufsrecht">Einzelheiten zu Ihrem Widerrufsrecht</a></p>', '', NULL, NULL, 'de_DE', 'Frontend'),
(26, 12, 'sBankContact', '<strong>\r\nUnsere Bankverbindung:\r\n</strong>\r\nVolksbank Musterstadt\r\nBLZ:\r\nKto.-Nr.:', '', NULL, NULL, 'de_DE', 'Frontend'),
(27, 12, 'sInfoEmailAlreadyRegiested', 'Sie erhalten unseren Newsletter bereits', '', NULL, NULL, 'de_DE', 'Frontend'),
(28, 12, 'sNewsletterInfo', 'Abonnieren Sie jetzt einfach unseren regelm&auml;&szlig;ig erscheinenden\r\nNewsletter und Sie werden stets als Erster &uuml;ber\r\nneue Artikel und Angebote informiert.<br />\r\nDer Newsletter ist nat&uuml;rlich jederzeit &uuml;ber einen Link in der\r\neMail oder dieser Seite wieder abbestellbar.', '', NULL, NULL, 'de_DE', 'Frontend'),
(29, 12, 'sPaymentESDInfo', 'Kauf von Direktdownloads nur per Lastschrift oder Kreditkarte möglich', '', NULL, NULL, 'de_DE', 'Frontend'),
(30, 12, 'sAGBTextPaymentform', 'Ich habe die AGB gelesen... Dieser Text kann in den Textbausteinen angepasst werden.', '', NULL, NULL, 'de_DE', 'Frontend'),
(31, 12, 'sErrorValidEmail', 'Bitte geben Sie eine gültige eMail-Adresse ein', '', NULL, NULL, 'de_DE', 'Frontend'),
(32, 12, 'sErrorEnterEmail', 'Bitte geben sie eine eMail-Adresse an', '', NULL, NULL, 'de_DE', 'Frontend'),
(33, 12, 'sNewsletterLabelSelect', 'Ich m&ouml;chte:', '', NULL, NULL, 'de_DE', 'Frontend'),
(34, 12, 'sNewsletterLabelMail', 'Ihre eMail-Adresse:', '', NULL, NULL, 'de_DE', 'Frontend'),
(35, 12, 'sNewsletterOptionSubscribe', 'Newsletter abonnieren', '', NULL, NULL, 'de_DE', 'Frontend'),
(36, 12, 'sNewsletterOptionUnsubscribe', 'Newsletter abbestellen', '', NULL, NULL, 'de_DE', 'Frontend'),
(37, 12, 'sNewsletterButton', 'Speichern', '', NULL, NULL, 'de_DE', 'Frontend'),
(38, 12, 'sDelivery1', 'Sofort versandfertig,<br/>\r\nLieferzeit ca. 1-3 Werktage', '', NULL, NULL, 'de_DE', 'Frontend'),
(39, 12, 'sUSK18', '<strong>Wie kann ich Spiele erwerben, die erst ab 18 Jahren freigegeben sind?</strong><br/><br/>\r\n\r\n<strong>Diesen Artikel dürfen wir Ihnen leider nur zustellen, sofern Sie das 18. Lebensjahr überschritten haben!</strong>\r\n\r\n	</br></br>\r\n	Bei der Bestellung von Videospielen mit USK 18 Freigabe ist es rechtlich vorgeschrieben die Identität des Bestellers festzustellen.\r\n	</br></br>\r\n	Dieses wird durch die Versandart Einschreiben eigenhändig, in Verbindung mit der Zusendung des Personalausweises durch den Besteller, gewährleistet.\r\n	</br></br>\r\n	Wir bitten Sie uns daher eine einwandfrei lesbare Kopie Ihres Personalausweises Vorder- und Rückseite zur Verfügung zu stellen. Sie können uns diese Kopie per Mail, Fax oder Brief senden.\r\n	</br></br>\r\n	Sollten Sie uns die Kopie faxen oder brieflich zusenden, geben Sie bitte unbedingt Ihren Benutzernamen mit an.\r\n	</br></br>', '', NULL, NULL, 'de_DE', 'Frontend'),
(40, 23, 'sVoucherWrongCustomergroup', 'Dieser Gutschein ist für Ihre Kundengruppe nicht verfügbar', '', NULL, NULL, 'de_DE', 'Frontend'),
(41, 23, 'sErrorEmailForgiven', 'Diese eMail-Adresse ist bereits registriert\r\n', '', NULL, NULL, 'de_DE', 'Frontend'),
(42, 8, 'sAccountDownloadssortedbydate', 'Ihre Sofortdownloads nach Datum sortiert', 'Sofortdownloads nach Datum sortiert', NULL, NULL, 'de_DE', 'Frontend'),
(43, 8, 'sAccountDownload', 'Downloaden', 'Downloaden', NULL, NULL, 'de_DE', 'Frontend'),
(44, 8, 'sAccountyourSerialnumber', 'Ihre Seriennummer:', 'Ihre Seriennummer', NULL, NULL, 'de_DE', 'Frontend'),
(45, 8, 'sAccountOrderssortedbydate', 'Bestellungen nach Datum sortiert', 'Bestellungen nach Datum sortiert', NULL, NULL, 'de_DE', 'Frontend'),
(46, 8, 'sAccountfrom', 'Vom:', 'Vom', NULL, NULL, 'de_DE', 'Frontend'),
(47, 8, 'sAccountOrdernumber', 'Bestellnummer:', 'Bestellnummer', NULL, NULL, 'de_DE', 'Frontend'),
(48, 8, 'sAccountOrderTotal', 'Bestellsumme:', 'Bestellsumme', NULL, NULL, 'de_DE', 'Frontend'),
(49, 8, 'sAccountPackagetracking', 'Paket-Tracking:', 'Paket-Tracking', NULL, NULL, 'de_DE', 'Frontend'),
(50, 8, 'sAccountOrdernotvetprocessed', 'Bestellung wurde noch nicht bearbeitet', 'Bestellung wurde noch nicht bearbeitet', NULL, NULL, 'de_DE', 'Frontend'),
(51, 8, 'sAccountOrderinprogress', 'Bestellung ist in Bearbeitung', 'Bestellung ist in Bearbeitung', NULL, NULL, 'de_DE', 'Frontend'),
(52, 8, 'sAccountOrderhasbeenshipped', 'Bestellung wurde verschickt', 'Bestellung verschickt', NULL, NULL, 'de_DE', 'Frontend'),
(53, 8, 'sAccountOrderpartiallyshipped', 'Bestellung wurde teilweise verschickt', 'Bestellung teilweise verschickt', NULL, NULL, 'de_DE', 'Frontend'),
(54, 8, 'sAccountOrdercanceled', 'Bestellung wurde storniert', 'Bestellung storniert', NULL, NULL, 'de_DE', 'Frontend'),
(55, 8, 'sAccountACommentisdeposited', 'Es wurde ein Kommentar hinterlegt!', 'Es wurde ein Kommentar hinterlegt!', NULL, NULL, 'de_DE', 'Frontend'),
(56, 8, 'sAccountArticle', 'Artikel', 'Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(57, 8, 'sAccountNumber', 'Anzahl', 'Anzahl', NULL, NULL, 'de_DE', 'Frontend'),
(58, 8, 'sAccountUnitprice', 'St&uuml;ckpreis', 'St&uuml;ckpreis', NULL, NULL, 'de_DE', 'Frontend'),
(59, 8, 'sAccountTotal', 'Summe', 'Summe', NULL, NULL, 'de_DE', 'Frontend'),
(60, 8, 'sAccountDownloadNow', 'Jetzt downloaden', 'Jetzt downloaden', NULL, NULL, 'de_DE', 'Frontend'),
(61, 8, 'sAccountFree', 'GRATIS', 'GRATIS', NULL, NULL, 'de_DE', 'Frontend'),
(62, 8, 'sAccountyourSerialnumberto', 'Ihre Seriennummer zu', 'Seriennummer', NULL, NULL, 'de_DE', 'Frontend'),
(63, 8, 'sAccountShipping', 'Versandkosten:', 'Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(64, 8, 'sAccountgrandtotal', 'Gesamtsumme:', 'Gesamtsumme', NULL, NULL, 'de_DE', 'Frontend'),
(65, 8, 'sAccountComment', 'Kommentar:', 'Kommentar', NULL, NULL, 'de_DE', 'Frontend'),
(66, 8, 'sAccountErrorhasoccurred', 'Ein Fehler ist aufgetreten!', 'Ein Fehler ist aufgetreten!', NULL, NULL, 'de_DE', 'Frontend'),
(67, 8, 'sAccountBillingAddress', 'Rechnungsadresse', 'Rechnungsadresse', NULL, NULL, 'de_DE', 'Frontend'),
(68, 8, 'sAccountMr', 'Herr', 'Herr', NULL, NULL, 'de_DE', 'Frontend'),
(69, 8, 'sAccountMs', 'Frau', 'Frau', NULL, NULL, 'de_DE', 'Frontend'),
(70, 8, 'sAccountcompany ', 'Firma', 'Firma', NULL, NULL, 'de_DE', 'Frontend'),
(71, 8, 'sAccountmodify', '&Auml;ndern', '&Auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(72, 8, 'sAccountshippingaddress', 'Lieferadresse', 'Lieferadresse', NULL, NULL, 'de_DE', 'Frontend'),
(73, 8, 'sAccountmethodofpayment', 'Gew&auml;hlte Zahlungsart', 'Gew&auml;hlte Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(74, 8, 'sAccountIwanttoget', 'Ja, ich m&ouml;chte den kostenlosen', 'Ja, ich m&ouml;chte den kostenlosen', NULL, NULL, 'de_DE', 'Frontend'),
(75, 8, 'sAccountthenewsletter', 'Newsletter erhalten!', 'Newsletter erhalten!', NULL, NULL, 'de_DE', 'Frontend'),
(76, 8, 'sAccountYouraccessdata', 'Ihre Zugangsdaten', 'Ihre Zugangsdaten', NULL, NULL, 'de_DE', 'Frontend'),
(77, 8, 'sAccountYouremailaddress', 'Ihre eMail-Adresse*:', 'Ihre eMail-Adresse', NULL, NULL, 'de_DE', 'Frontend'),
(78, 8, 'sAccountNewPassword', 'Neues Passwort*:', 'Neues Passwort', NULL, NULL, 'de_DE', 'Frontend'),
(79, 8, 'sAccountRepeatpassword', 'Passwort-Wiederholung*:', 'Passwort-Wiederholung', NULL, NULL, 'de_DE', 'Frontend'),
(80, 8, 'sAccountnewslettersettings', 'Ihre Newslettereinstellungen', 'Ihre Newslettereinstellungen', NULL, NULL, 'de_DE', 'Frontend'),
(81, 9, 'sArticleavailableasan', 'Als Sofortdownload verf&uuml;gbar', 'Als Sofortdownload verf&uuml;gbar', NULL, NULL, 'de_DE', 'Frontend'),
(82, 9, 'sArticlenoPicture', 'Kein Bild vorhanden', 'Kein Bild vorhanden', NULL, NULL, 'de_DE', 'Frontend'),
(83, 9, 'sArticlezeropoints', '0 Punkte', '0 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(84, 9, 'sArticleonepoint', '1 Punkt', '1 Punkt', NULL, NULL, 'de_DE', 'Frontend'),
(85, 9, 'sArticletwopoints', '2 Punkte', '2 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(86, 9, 'sArticlethreepoints', '3 Punkte', '3 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(87, 9, 'sArticlefourpoints', '4 Punkte', '4 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(88, 9, 'sArticlefivepoints', '5 Punkte', '5 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(89, 9, 'sArticlesixpoints', '6 Punkte', '6 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(90, 9, 'sArticlesevenpoints', '7 Punkte', '7 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(91, 9, 'sArticleeightpoints', '8 Punkte', '8 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(92, 9, 'sArticleninepoints', '9 Punkte', '9 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(93, 9, 'sArticletenpoints', '10 Punkte', '10 Punkte', NULL, NULL, 'de_DE', 'Frontend'),
(94, 9, 'sArticlefrom', 'ab', 'ab', NULL, NULL, 'de_DE', 'Frontend'),
(95, 9, 'sArticletip', 'TIPP!', 'TIPP!', NULL, NULL, 'de_DE', 'Frontend'),
(96, 9, 'sArticlenew', 'NEU', 'NEU', NULL, NULL, 'de_DE', 'Frontend'),
(97, 9, 'sArticletipavailableasanimmedi', 'Als Sofortdownload verf&uuml;gbar', 'Als Sofortdownload verf&uuml;gbar', NULL, NULL, 'de_DE', 'Frontend'),
(98, 9, 'sArticleCompare', 'Vergleichen', 'Vergleichen', NULL, NULL, 'de_DE', 'Frontend'),
(99, 9, 'sArticleReview', 'Bewertung:', 'Bewertung', NULL, NULL, 'de_DE', 'Frontend'),
(100, 9, 'sArticlemoreinformationabout', 'Mehr Informationen zu', 'Mehr Informationen zu', NULL, NULL, 'de_DE', 'Frontend'),
(101, 9, 'sArticlefreeshipping', 'VERSANDKOSTENFREI', 'VERSANDKOSTENFREI', NULL, NULL, 'de_DE', 'Frontend'),
(102, 9, 'sArticletop', 'TOP', 'TOP', NULL, NULL, 'de_DE', 'Frontend'),
(103, 9, 'sArticleMoreinformation', 'Mehr Informationen', 'Mehr Informationen', NULL, NULL, 'de_DE', 'Frontend'),
(104, 9, 'sArticleAvailablefrom', 'Lieferbar ab', 'Lieferbar ab', NULL, NULL, 'de_DE', 'Frontend'),
(105, 9, 'sArticletopImmediatelyavailabl', 'Sofort lieferbar', 'Sofort lieferbar', NULL, NULL, 'de_DE', 'Frontend'),
(106, 9, 'sArticledeliverytime', 'Lieferzeit', 'Lieferzeit', NULL, NULL, 'de_DE', 'Frontend'),
(107, 9, 'sArticledays', 'Tage', 'Tage', NULL, NULL, 'de_DE', 'Frontend'),
(108, 9, 'sArticlepleaseselect', 'Bitte w&auml;hlen...', 'Bitte w&auml;hlen...', NULL, NULL, 'de_DE', 'Frontend'),
(109, 9, 'sArticleupdatenow', 'Jetzt aktualisieren', 'Jetzt aktualisieren', NULL, NULL, 'de_DE', 'Frontend'),
(110, 9, 'sArticleordernumber', 'Bestell-Nr.:', 'Bestell-Nr.:', NULL, NULL, 'de_DE', 'Frontend'),
(111, 9, 'sArticlelanguage', 'Sprache:', 'Sprache:', NULL, NULL, 'de_DE', 'Frontend'),
(112, 9, 'sArticledaysshippingfree', 'Versandkostenfreie Lieferung!', 'Versandkostenfreie Lieferung!', NULL, NULL, 'de_DE', 'Frontend'),
(113, 9, 'sArticleavailableimmediate', 'Als Sofortdownload verfügbar', 'Als Sofortdownload verfügbar', NULL, NULL, 'de_DE', 'Frontend'),
(114, 9, 'sArticleworkingdays', 'Werktage', 'Werktage', NULL, NULL, 'de_DE', 'Frontend'),
(115, 9, 'sArticleblockpricing', 'Staffelpreise', 'Staffelpreise', NULL, NULL, 'de_DE', 'Frontend'),
(116, 9, 'sArticleamount', 'Menge', 'Menge:', NULL, NULL, 'de_DE', 'Frontend'),
(117, 9, 'sArticleuntil', 'bis', 'bis', NULL, NULL, 'de_DE', 'Frontend'),
(118, 9, 'sArticleprices', 'Preise', 'Preise', NULL, NULL, 'de_DE', 'Frontend'),
(119, 9, 'sAccountplus', 'zzgl.', 'zzgl.', NULL, NULL, 'de_DE', 'Frontend'),
(120, 9, 'sArticleincl', 'inkl.', 'inkl.', NULL, NULL, 'de_DE', 'Frontend'),
(121, 9, 'sArticlelegal', 'gesetzlicher', 'gesetzlicher', NULL, NULL, 'de_DE', 'Frontend'),
(122, 9, 'sArticletaxplus', 'MwSt. zzgl.', 'MwSt. zzgl.', NULL, NULL, 'de_DE', 'Frontend'),
(123, 9, 'sArticleshipping', '<a href="{$sBasefile}?sViewport=custom&cCUSTOM=6" title="Versandkosten"> Versandkosten', 'Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(124, 9, 'sArticlesave', 'gespart', 'gespart', NULL, NULL, 'de_DE', 'Frontend'),
(125, 9, 'sArticleback', 'Zurück', 'Zurück', NULL, NULL, 'de_DE', 'Frontend'),
(126, 9, 'sArticleof', 'von', 'von', NULL, NULL, 'de_DE', 'Frontend'),
(127, 9, 'sArticleoverview', '&Uuml;bersicht', '&Uuml;bersicht', NULL, NULL, 'de_DE', 'Frontend'),
(128, 9, 'sArticlenext', 'Weiter', 'Weiter', NULL, NULL, 'de_DE', 'Frontend'),
(129, 9, 'sArticletoseeinthepicture', 'Auf dem Bild zu sehen:', 'Auf dem Bild zu sehen:', NULL, NULL, 'de_DE', 'Frontend'),
(130, 9, 'sArticlewithoutagerestriction', 'Ohne Altersbeschr&auml;nkung', 'Ohne Altersbeschr&auml;nkung', NULL, NULL, 'de_DE', 'Frontend'),
(131, 9, 'sArticlereleasedfrom6years', 'Freigegeben ab 6 Jahren', 'Freigegeben ab 6 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(132, 9, 'sArticlereleasedfrom12years', 'Freigegeben ab 12 Jahren', 'Freigegeben ab 12 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(133, 9, 'sArticlereleasedfrom16years', 'Freigegeben ab 16 Jahren', 'Freigegeben ab 16 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(134, 9, 'sArticlereleasedfrom18years', 'Freigegeben ab 18 Jahren', 'Freigegeben ab 18 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(135, 9, 'sArticleafterageckeck', 'Achtung! Auslieferung erst nach erfolgreicher Altersprüfung!', 'Achtung! Auslieferung erst nach erfolgreicher Altersprüfung!', NULL, NULL, 'de_DE', 'Frontend'),
(136, 9, 'sArticlefurtherinformation', 'weitere Informationen', 'weitere Informationen', NULL, NULL, 'de_DE', 'Frontend'),
(137, 9, 'sArticlepleasechoose', 'Bitte wählen...', 'Bitte wählen...', NULL, NULL, 'de_DE', 'Frontend'),
(138, 9, 'sArticlemainarticle', 'Hauptartikel', 'Hauptartikel', NULL, NULL, 'de_DE', 'Frontend'),
(139, 9, 'sArticlesurcharge', 'Aufpreis', 'Aufpreis', NULL, NULL, 'de_DE', 'Frontend'),
(140, 9, 'sArticleinthebasket', 'in den Warenkorb legen', 'in den Warenkorb legen', NULL, NULL, 'de_DE', 'Frontend'),
(3283, 0, 'sBasketstep3Overview', 'Bestellung abschlie¤en', '', '2010-08-16 10:15:00', '2010-08-16 10:15:00', 'de_DE', 'Frontend'),
(143, 9, 'sArticlecollectvoucher', 'Artikel weiterempfehlen und Gutschein kassieren!', 'Artikel weiterempfehlen und Gutschein kassieren!', NULL, NULL, 'de_DE', 'Frontend'),
(144, 9, 'sArticlewritereview', 'Bewertung schreiben', 'Bewertung schreiben', NULL, NULL, 'de_DE', 'Frontend'),
(145, 9, 'sArticleonthenotepad', 'auf den Merkzettel setzen', 'auf den Merkzettel setzen', NULL, NULL, 'de_DE', 'Frontend'),
(146, 9, 'sArticleaddtonotepad', 'Auf den Merkzettel', 'Auf den Merkzettel', NULL, NULL, 'de_DE', 'Frontend'),
(147, 9, 'sArticlequestionsaboutarticle', 'Fragen zum Artikel?', 'Fragen zum Artikel?', NULL, NULL, 'de_DE', 'Frontend'),
(148, 9, 'sArticledescription', 'Beschreibung', 'Beschreibung', NULL, NULL, 'de_DE', 'Frontend'),
(149, 9, 'sArticleaccessories', 'Zubeh&ouml;r', 'Zubeh&ouml;r', NULL, NULL, 'de_DE', 'Frontend'),
(150, 9, 'sArticlereviews', 'Bewertungen', 'Bewertungen', NULL, NULL, 'de_DE', 'Frontend'),
(151, 9, 'sArticletipproductinformation', 'Produktinformationen', 'Produktinformationen', NULL, NULL, 'de_DE', 'Frontend'),
(152, 9, 'sArticletipmoreinformation', 'Weitere Informationen zu', 'Weitere Informationen zu', NULL, NULL, 'de_DE', 'Frontend'),
(153, 9, 'sArticleotherarticlesof', 'Weitere Artikel von', 'Weitere Artikel von', NULL, NULL, 'de_DE', 'Frontend'),
(154, 9, 'sArticleavailabledownloads', 'Verf&uuml;gbare Downloads:', 'Verf&uuml;gbare Downloads', NULL, NULL, 'de_DE', 'Frontend'),
(155, 9, 'sArticledownload', 'Download', 'Download', NULL, NULL, 'de_DE', 'Frontend'),
(156, 9, 'sArticleourcommenton', 'Unser Kommentar zu', 'Unser Kommentar zu', NULL, NULL, 'de_DE', 'Frontend'),
(157, 9, 'sArticlematchingitems', 'Hierzu passende Artikel:', 'Hierzu passende Artikel:', NULL, NULL, 'de_DE', 'Frontend'),
(158, 9, 'sArticlecustomerreviews', 'Kundenbewertungen f&uuml;r', 'Kundenbewertungen f&uuml;r', NULL, NULL, 'de_DE', 'Frontend'),
(159, 9, 'sArticletopaveragecustomerrevi', 'Durchschnittliche Kundenbewertung:', 'Durchschnittliche Kundenbewertung:', NULL, NULL, 'de_DE', 'Frontend'),
(160, 9, 'sArticleout', 'aus', 'aus', NULL, NULL, 'de_DE', 'Frontend'),
(161, 9, 'sArticlewriteareview', 'Schreiben Sie eine Bewertung', 'Schreiben Sie eine Bewertung', NULL, NULL, 'de_DE', 'Frontend'),
(162, 9, 'sArticlefilloutallredfields', 'Bitte füllen Sie alle rot markierten Felder aus', 'Bitte füllen Sie alle rot markierten Felder aus', NULL, NULL, 'de_DE', 'Frontend'),
(163, 9, 'sArticleby', 'Von:', 'Von:', NULL, NULL, 'de_DE', 'Frontend'),
(164, 9, 'sArticlereleasedafterverificat', 'Bewertungen werden nach Überprüfung freigeschaltet.', 'Bewertungen werden nach Überprüfung freigeschaltet.', NULL, NULL, 'de_DE', 'Frontend'),
(165, 9, 'sArticleyourname', 'Ihr Name', 'Ihr Name', NULL, NULL, 'de_DE', 'Frontend'),
(166, 9, 'sArticlesummary', 'Zusammenfassung', 'Zusammenfassung', NULL, NULL, 'de_DE', 'Frontend'),
(167, 9, 'sArticlereview1', 'Bewertung', 'Bewertung', NULL, NULL, 'de_DE', 'Frontend'),
(168, 9, 'sArticle10', '10 sehr gut', '10 sehr gut', NULL, NULL, 'de_DE', 'Frontend'),
(169, 9, 'sArticle9', '9', '9', NULL, NULL, 'de_DE', 'Frontend'),
(170, 9, 'sArticle8', '8', '8', NULL, NULL, 'de_DE', 'Frontend'),
(171, 9, 'sArticle7', '7', '7', NULL, NULL, 'de_DE', 'Frontend'),
(172, 9, 'sArticle6', '6', '6', NULL, NULL, 'de_DE', 'Frontend'),
(173, 9, 'sArticle5', '5', '5', NULL, NULL, 'de_DE', 'Frontend'),
(174, 9, 'sArticle4', '4', '4', NULL, NULL, 'de_DE', 'Frontend'),
(175, 9, 'sArticle3', '3', '3', NULL, NULL, 'de_DE', 'Frontend'),
(176, 9, 'sArticle2', '2', '2', NULL, NULL, 'de_DE', 'Frontend'),
(177, 9, 'sArticle1', '1 sehr schlecht', '1 sehr schlecht', NULL, NULL, 'de_DE', 'Frontend'),
(178, 9, 'sArticleyouropinion', 'Ihre Meinung:', 'Ihre Meinung:', NULL, NULL, 'de_DE', 'Frontend'),
(179, 9, 'sArticleenterthenumbers', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', NULL, NULL, 'de_DE', 'Frontend'),
(180, 9, 'sArticlethefieldsmarked', 'Die mit einem * markierten Felder sind Pflichtfelder.', 'Die mit einem * markierten Felder sind Pflichtfelder.', NULL, NULL, 'de_DE', 'Frontend'),
(181, 9, 'sArticlesimilararticles', '&Auml;hnliche Artikel', '&Auml;hnliche Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(182, 9, 'sArticletaxplus1', 'MwSt. +', 'MwSt. +', NULL, NULL, 'de_DE', 'Frontend'),
(183, 9, 'sArticleshippinginformation', 'Informationen zu den Versandkosten', 'Informationen zu den Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(184, 9, 'sArticlechoosefirstexecu', 'Achtung! Bitte zuerst Ausführung wählen!', 'Achtung! Bitte zuerst Ausführung wählen!', NULL, NULL, 'de_DE', 'Frontend'),
(185, 9, 'sArticlescroll', 'Blättern', 'Blättern', NULL, NULL, 'de_DE', 'Frontend'),
(186, 9, 'sArticleonesiteback', 'Eine Seite zur&uuml;ck', 'Eine Seite zur&uuml;ck', NULL, NULL, 'de_DE', 'Frontend'),
(187, 9, 'sArticleonesiteforward', 'Eine Seite vor', 'Eine Seite vor', NULL, NULL, 'de_DE', 'Frontend'),
(188, 9, 'sArticlesort', 'Sortieren:', 'Sortieren:', NULL, NULL, 'de_DE', 'Frontend'),
(189, 9, 'sArticlereleasedate', 'Erscheinungsdatum', 'Erscheinungsdatum', NULL, NULL, 'de_DE', 'Frontend'),
(190, 9, 'sArticlepopularity', 'Beliebtheit', 'Beliebtheit', NULL, NULL, 'de_DE', 'Frontend'),
(191, 9, 'sArticlelowestprice', 'Niedrigster Preis', 'Niedrigster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(192, 9, 'sArticlehighestprice', 'Höchster Preis', 'Höchster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(193, 9, 'sArticleitemtitle', 'Artikelbezeichnung', 'Artikelbezeichnung', NULL, NULL, 'de_DE', 'Frontend'),
(194, 9, 'sArticleallmanufacturers', 'Alle Hersteller anzeigen', 'Alle Hersteller anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(195, 9, 'sArticlearticleperpage', 'Artikel pro Seite', 'Artikel pro Seite', NULL, NULL, 'de_DE', 'Frontend'),
(196, 9, 'sArticleproductsof', 'Produkte von', 'Produkte von', NULL, NULL, 'de_DE', 'Frontend'),
(197, 9, 'sArticleshowall', 'Alle anzeigen', 'Alle anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(198, 9, 'sArticleshowallmanufacturers', 'Alle Hersteller anzeigen', 'Alle Hersteller anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(199, 9, 'sArticlethankyouverymuch', 'Vielen Dank. Die Weiterempfehlung wurde erfolgreich verschickt.', 'Vielen Dank. Die Weiterempfehlung wurde erfolgreich verschickt.', NULL, NULL, 'de_DE', 'Frontend'),
(200, 9, 'sArticlepleasecompleteall', 'Bitte f&uuml;llen Sie alle ben&ouml;tigten Felder aus', 'Bitte f&uuml;llen Sie alle ben&ouml;tigten Felder aus', NULL, NULL, 'de_DE', 'Frontend'),
(201, 9, 'sArticlerecommend', 'weiterempfehlen und Gutschein kassieren!', 'weiterempfehlen', NULL, NULL, 'de_DE', 'Frontend'),
(202, 9, 'sArticlegetavoucher', 'Gutschein kassieren*', 'Gutschein kassieren*', NULL, NULL, 'de_DE', 'Frontend'),
(203, 9, 'sArticlerecipientemail', 'Empf&auml;nger eMail-Adresse', 'Empf&auml;nger eMail-Adresse', NULL, NULL, 'de_DE', 'Frontend'),
(204, 9, 'sArticleyourcomment', 'Ihr Kommentar:', 'Ihr Kommentar:', NULL, NULL, 'de_DE', 'Frontend'),
(205, 9, 'sArticlethevoucherautomatic', '* Der Gutschein wird Ihnen automatisch nach Anmeldung und der ersten Bestellung Ihres Bekannten per eMail zugestellt.\r\n        Sie m&uuml;ssen mit der angegebenen eMail-Adresse im Shop registriert sein, um den Gutschein zu erhalten.', '* Der Gutschein wird Ihnen automatisch nach Anmeldung und der ersten Bestellung Ihres Bekannten per eMail zugestellt.\r\n        Sie m&uuml;ssen mit der angegebenen eMail-Adresse im Shop registriert sein, um den Gutschein zu erhalten.', NULL, NULL, 'de_DE', 'Frontend'),
(206, 9, 'sArticleLastViewed', 'Angeschaut', 'Angeschaut', NULL, NULL, 'de_DE', 'Frontend'),
(207, 10, 'sBasketaddedtothebasket', 'wurde in den Warenkorb gelegt!', 'wurde in den Warenkorb gelegt!', NULL, NULL, 'de_DE', 'Frontend'),
(208, 10, 'sBasketbacktomainpage', 'Zur&uuml;ck zur Startseite!', 'Zur&uuml;ck zur Startseite!', NULL, NULL, 'de_DE', 'Frontend'),
(209, 10, 'sBasketcontinueshopping', 'Weiter shoppen', 'Weiter shoppen', NULL, NULL, 'de_DE', 'Frontend'),
(210, 10, 'sBasketshowbasket', 'Warenkorb anzeigen', 'Warenkorb anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(211, 10, 'sBaskettocheckout', 'Zur Kasse gehen!', 'Zur Kasse gehen!', NULL, NULL, 'de_DE', 'Frontend'),
(212, 10, 'sBasketcheckout', 'Zur Kasse gehen!', 'Zur Kasse', NULL, NULL, 'de_DE', 'Frontend'),
(213, 10, 'sBasketcheckoutcustomerswithyo', 'Kunden mit Ihrem Warenkorbinhalt, kaufen auch', 'Kunden mit Ihrem Warenkorbinhalt, kaufen auch', NULL, NULL, 'de_DE', 'Frontend'),
(214, 10, 'sBasketcustomerswithyoursimila', 'Kunden mit ähnlichen Interessen, haben sich auch angeschaut', 'Kunden mit ähnlichen Interessen, haben sich auch angeschaut', NULL, NULL, 'de_DE', 'Frontend'),
(215, 10, 'sBasketstep1basket', 'Warenkorb', 'Step1 - Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(216, 10, 'sBasketavailability', 'Verfügbarkeit', 'Verfügbarkeit', NULL, NULL, 'de_DE', 'Frontend'),
(217, 10, 'sBasketArticle', 'Artikel', 'Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(218, 10, 'sBasketnumber', 'Anzahl', 'Anzahl', NULL, NULL, 'de_DE', 'Frontend'),
(219, 10, 'sBasketunitprice', 'St&uuml;ckpreis', 'St&uuml;ckpreis', NULL, NULL, 'de_DE', 'Frontend'),
(220, 10, 'sBasketsum', 'Summe', 'Summe', NULL, NULL, 'de_DE', 'Frontend'),
(221, 10, 'sBasketordernumber', 'Bestell-Nr.', 'Bestell-Nr.', NULL, NULL, 'de_DE', 'Frontend'),
(222, 10, 'sBasketavailablefrom', 'Lieferbar ab', 'Lieferbar ab', NULL, NULL, 'de_DE', 'Frontend'),
(223, 10, 'sBasketasanimmediate', 'Als Sofortdownload verfügbar', 'Als Sofortdownload verfügbar', NULL, NULL, 'de_DE', 'Frontend'),
(224, 10, 'sBasketdelivery', 'Lieferzeit', 'Lieferzeit', NULL, NULL, 'de_DE', 'Frontend'),
(225, 10, 'sBasketweekdays', 'Werktage', 'Werktage', NULL, NULL, 'de_DE', 'Frontend'),
(226, 10, 'sBasketrecalculateprice', 'Preis neu berechnen - Warenkorb aktualisieren', 'Preis neu berechnen - Warenkorb aktualisieren', NULL, NULL, 'de_DE', 'Frontend'),
(227, 10, 'sBasketdeletethisitemfrombaske', 'Diesen Artikel aus dem Warenkorb l&ouml;schen', 'Diesen Artikel aus dem Warenkorb l&ouml;schen', NULL, NULL, 'de_DE', 'Frontend'),
(228, 10, 'sBasketasasmallthankyou', 'Als kleines Dankeschön, bekommen Sie diesen Artikel Gratis dazu', 'Als kleines Dankeschön, bekommen Sie diesen Artikel Gratis dazu', NULL, NULL, 'de_DE', 'Frontend'),
(229, 10, 'sBasketfree', 'GRATIS!', 'GRATIS!', NULL, NULL, 'de_DE', 'Frontend'),
(230, 10, 'sBasketdeliverycountry', 'Lieferland', 'Lieferland', NULL, NULL, 'de_DE', 'Frontend'),
(231, 10, 'sBasketdispatch', 'Versandart', 'Versandart', NULL, NULL, 'de_DE', 'Frontend'),
(232, 10, 'sBasketpayment', 'Zahlungsart', 'Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(233, 10, 'sBasketforwardingexpenses', 'Versandkosten', 'Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(234, 10, 'sBaskettotalsum', 'Gesamtsumme', 'Gesamtsumme', NULL, NULL, 'de_DE', 'Frontend'),
(235, 10, 'sBasketarticlenotfound', 'Artikel nicht gefunden', 'Artikel nicht gefunden', NULL, NULL, 'de_DE', 'Frontend'),
(236, 10, 'sBasketarticlefromourcatalogue', 'Artikel aus unserem Katalog hinzuf&uuml;gen', 'Artikel aus unserem Katalog hinzuf&uuml;gen', NULL, NULL, 'de_DE', 'Frontend'),
(237, 10, 'sBasketminimumordervalue', 'Achtung. Sie haben den Mindestbestellwert von', 'Achtung. Sie haben den Mindestbestellwert von', NULL, NULL, 'de_DE', 'Frontend'),
(238, 10, 'sBasketnotreachedyet', 'noch nicht erreicht!', 'noch nicht erreicht!', NULL, NULL, 'de_DE', 'Frontend'),
(239, 10, 'sBasketyourbasketisempty', 'Sie haben keine Artikel im Warenkorb', 'Sie haben keine Artikel im Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(240, 10, 'sBasketyourbasket', 'Ihr Warenkorb', 'Ihr Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(241, 10, 'sBasketlastinyourbasket', 'Zuletzt in Ihren Warenkorb gelegt:', 'Zuletzt in Ihren Warenkorb gelegt:', NULL, NULL, 'de_DE', 'Frontend'),
(242, 10, 'sBasketamount', 'Anzahl:', 'Anzahl:', NULL, NULL, 'de_DE', 'Frontend'),
(243, 10, 'sBasketbasketdiscount', 'Warenkorb-Rabatt', 'Warenkorb-Rabatt', NULL, NULL, 'de_DE', 'Frontend'),
(244, 10, 'sBasketmodify', '&Auml;ndern', '&Auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(245, 10, 'sBasketsubtotal', 'Zwischensumme:', 'Zwischensumme:', NULL, NULL, 'de_DE', 'Frontend'),
(246, 10, 'sBasketfree1', 'GRATIS', 'GRATIS', NULL, NULL, 'de_DE', 'Frontend'),
(247, 10, 'sBasketmoreinformations', 'Mehr Informationen', 'Mehr Informationen', NULL, NULL, 'de_DE', 'Frontend'),
(248, 10, 'sBasketnopictureavailable', 'Kein Bild vorhanden', 'Kein Bild vorhanden', NULL, NULL, 'de_DE', 'Frontend'),
(249, 10, 'sBasketbetweenfollowingpremium', 'Bitte wählen Sie zwischen den folgenden Prämien', 'Bitte wählen Sie zwischen den folgenden Prämien', NULL, NULL, 'de_DE', 'Frontend'),
(250, 10, 'sBasketpleasechoose', 'Bitte wählen...', 'Bitte wählen...', NULL, NULL, 'de_DE', 'Frontend'),
(251, 10, 'sBasketintothebasket', 'In den Warenkorb', 'In den Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(252, 10, 'sBasketinthebasket', 'in den Warenkorb legen', 'in den Warenkorb legen', NULL, NULL, 'de_DE', 'Frontend'),
(253, 10, 'sBasketfrom', 'ab', 'ab', NULL, NULL, 'de_DE', 'Frontend'),
(254, 10, 'sBasketnotepad', 'Merkzettel', 'Merkzettel', NULL, NULL, 'de_DE', 'Frontend'),
(255, 10, 'sBasketsaveyourpersonalfav', 'Speichern Sie hier Ihre pers&ouml;nlichen Favoriten - bis Sie das n&auml;chste Mal bei uns sind.', 'Speichern Sie hier Ihre pers&ouml;nlichen Favoriten - bis Sie das n&auml;chste Mal bei uns sind.', NULL, NULL, 'de_DE', 'Frontend'),
(256, 10, 'sBasketjustthedesireditems', 'Einfach den gew&uuml;nschten Artikel auf die Merkliste setzen und', 'Einfach den gew&uuml;nschten Artikel auf die Merkliste setzen und', NULL, NULL, 'de_DE', 'Frontend'),
(257, 10, 'sBasketItautomaticallystores', 'speichert für Sie automatisch Ihre persönliche Merkliste.\r\nSo können Sie bequem bei einem späteren Besuch Ihre vorgemerkten Artikel wieder abrufen.', 'speichert für Sie automatisch Ihre pers&ouml;nliche Merkliste.\r\n	So k&ouml;nnen Sie bequem bei einem sp&auml;teren Besuch Ihre vorgemerkten Artikel wieder abrufen.', NULL, NULL, 'de_DE', 'Frontend'),
(258, 10, 'sBasketdesignatedarticle', 'Merkzettel - Vorgemerkte Artikel f&uuml;r einen sp&auml;teren Einkauf', 'Merkzettel - Vorgemerkte Artikel f&uuml;r einen sp&auml;teren Einkauf', NULL, NULL, 'de_DE', 'Frontend'),
(259, 10, 'sBasketerasefromnotepad', 'Diesen Artikel aus dem Merkzettel l&ouml;schen', 'Diesen Artikel aus dem Merkzettel l&ouml;schen', NULL, NULL, 'de_DE', 'Frontend'),
(260, 10, 'sBasketnoitemsonyournotepad', 'Sie haben noch keine Artikel auf Ihre Merkliste gesetzt.', 'Sie haben noch keine Artikel auf Ihre Merkliste gesetzt.', NULL, NULL, 'de_DE', 'Frontend'),
(261, 11, 'sCategoryshowall', 'Alle anzeigen', 'Alle anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(262, 11, 'sCategorytopseller', 'Topseller', 'Topseller', NULL, NULL, 'de_DE', 'Frontend'),
(263, 11, 'sCategorynopicture', 'Kein Bild vorhanden', 'Kein Bild vorhanden', NULL, NULL, 'de_DE', 'Frontend'),
(264, 11, 'sCategorymanufacturer', 'Hersteller', 'Hersteller', NULL, NULL, 'de_DE', 'Frontend'),
(265, 11, 'sCategoryothermanufacturer', 'Weitere Hersteller', 'Weitere Hersteller', NULL, NULL, 'de_DE', 'Frontend'),
(266, 12, 'sContentonthispicture', 'Auf dem Bild zu sehen:', 'Auf dem Bild zu sehen:', NULL, NULL, 'de_DE', 'Frontend'),
(267, 12, 'sContentmoreinformations', 'Weitere Informationen:', 'Weitere Informationen:', NULL, NULL, 'de_DE', 'Frontend'),
(268, 12, 'sContentattachment', 'Dateianhang:', 'Dateianhang:', NULL, NULL, 'de_DE', 'Frontend'),
(269, 12, 'sContentdownload', 'Herunterladen', 'Herunterladen', NULL, NULL, 'de_DE', 'Frontend'),
(270, 12, 'sContententrynotfound', 'Eintrag nicht gefunden', 'Eintrag nicht gefunden', NULL, NULL, 'de_DE', 'Frontend'),
(271, 12, 'sContentback', 'Zur&uuml;ck', 'Zur&uuml;ck', NULL, NULL, 'de_DE', 'Frontend'),
(272, 12, 'sContentbrowse', 'Blättern:', 'Blättern:', NULL, NULL, 'de_DE', 'Frontend'),
(273, 12, 'sContentgobackonepage', 'Eine Seite zur&uuml;ck bl&auml;ttern', 'Eine Seite zur&uuml;ck bl&auml;ttern', NULL, NULL, 'de_DE', 'Frontend'),
(274, 12, 'sContentbrowseforward', 'Eine Seite vor bl&auml;ttern', 'Eine Seite vor bl&auml;ttern', NULL, NULL, 'de_DE', 'Frontend'),
(275, 12, 'sContentmore', '[mehr]', '[mehr]', NULL, NULL, 'de_DE', 'Frontend'),
(276, 12, 'sContentcurrentlynoentries', 'Derzeit keine Einträge vorhanden', 'Derzeit keine Einträge vorhanden', NULL, NULL, 'de_DE', 'Frontend'),
(277, 13, 'sCustomsitenotfound', 'Seite nicht gefunden', 'Seite nicht gefunden', NULL, NULL, 'de_DE', 'Frontend'),
(278, 13, 'sCustomdirectcontact', 'Direkter Kontakt', 'Direkter Kontakt', NULL, NULL, 'de_DE', 'Frontend'),
(279, 14, 'sErrorthisarticleisnolonger', 'Dieser Artikel befindet sich nicht mehr in unserem Sortiment!', 'Dieser Artikel befindet sich nicht mehr in unserem Sortiment!', NULL, NULL, 'de_DE', 'Frontend'),
(280, 14, 'sErrorhome', 'Startseite', 'Startseite', NULL, NULL, 'de_DE', 'Frontend'),
(281, 14, 'sErrormoreinterestingarticles', 'Weitere interessante Artikel', 'Weitere interessante Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(282, 14, 'sErrororderwascanceled', 'Die Bestellung wurde abgebrochen', 'Die Bestellung wurde abgebrochen', NULL, NULL, 'de_DE', 'Frontend'),
(283, 14, 'sErrorcheckout', 'Zur Kasse', 'Zur Kasse', NULL, NULL, 'de_DE', 'Frontend'),
(284, 14, 'sErrorerror', 'Es ist ein Fehler aufgetreten', 'Es ist ein Fehler aufgetreten', NULL, NULL, 'de_DE', 'Frontend'),
(285, 15, 'sLoginerror', 'Ein Fehler ist aufgetreten!', 'Ein Fehler ist aufgetreten!', NULL, NULL, 'de_DE', 'Frontend'),
(286, 15, 'sLoginstep1login', 'Step1 - Login/Anmeldung', 'Step1 - Login/Anmeldung', NULL, NULL, 'de_DE', 'Frontend'),
(287, 15, 'sLogindealeraccess', 'H&auml;ndlerzugang', 'H&auml;ndlerzugang', NULL, NULL, 'de_DE', 'Frontend'),
(288, 15, 'sLoginareyounew', 'Sie sind neu bei', 'Sie sind neu bei', NULL, NULL, 'de_DE', 'Frontend'),
(289, 15, 'sLoginnoproblem', 'Kein Problem, eine Shopbestellung ist einfach und sicher. Die Anmeldung dauert nur wenige Augenblicke.', 'Kein Problem, eine Shopbestellung ist einfach und sicher. Die Anmeldung dauert nur wenige Augenblicke.', NULL, NULL, 'de_DE', 'Frontend'),
(290, 15, 'sLoginregisternow', 'Jetzt registrieren', 'Jetzt registireren', NULL, NULL, 'de_DE', 'Frontend'),
(291, 15, 'sLoginnewcustomer', 'Neuer Kunde', 'Neuer Kunde', NULL, NULL, 'de_DE', 'Frontend'),
(292, 15, 'sLoginalreadyhaveanaccount', 'Sie besitzen bereits ein Kundenkonto', 'Sie besitzen bereits ein Kundenkonto', NULL, NULL, 'de_DE', 'Frontend'),
(293, 15, 'sLoginloginwithyouremail', 'Einloggen mit Ihrer eMail-Adresse und Ihrem Passwort', 'Einloggen mit Ihrer eMail-Adresse und Ihrem Passwort', NULL, NULL, 'de_DE', 'Frontend'),
(294, 15, 'sLoginyouremailadress', 'Ihre eMail-Adresse:', 'Ihre eMail-Adresse:', NULL, NULL, 'de_DE', 'Frontend'),
(295, 15, 'sLoginpassword', 'Ihr Passwort:', 'Ihr Passwort:', NULL, NULL, 'de_DE', 'Frontend'),
(296, 15, 'sLoginlostpassword', 'Passwort vergessen?', 'Passwort vergessen?', NULL, NULL, 'de_DE', 'Frontend'),
(297, 15, 'sLoginnewpasswordhasbeensent', 'Ihr neues Passwort wurde Ihnen zugeschickt', 'Ihr neues Passwort wurde Ihnen zugeschickt', NULL, NULL, 'de_DE', 'Frontend'),
(298, 15, 'sLoginlostpasswordhereyoucan', 'Passwort vergessen? Hier k&ouml;nnen Sie ein neues Passwort anfordern', 'Passwort vergessen? Hier k&ouml;nnen Sie ein neues Passwort anfordern', NULL, NULL, 'de_DE', 'Frontend'),
(299, 15, 'sLoginwewillsendyouanewpass', 'Wir senden Ihnen ein neues, zuf&auml;llig generiertes Passwort. Dieses k&ouml;nnen Sie dann im Kundenbereich &auml;ndern.', 'Wir senden Ihnen ein neues, zuf&auml;llig generiertes Passwort. Dieses k&ouml;nnen Sie dann im Kundenbereich &auml;ndern.', NULL, NULL, 'de_DE', 'Frontend'),
(300, 15, 'sLoginback', 'Zur&uuml;ck', 'Zur&uuml;ck', NULL, NULL, 'de_DE', 'Frontend'),
(301, 16, 'sOrderprocesspleasecheck', 'Bitte &uuml;berpr&uuml;fen Sie Ihre Bestellung nochmals, bevor Sie sie senden.', 'Bitte &uuml;berpr&uuml;fen Sie Ihre Bestellung nochmals, bevor Sie sie senden.', NULL, NULL, 'de_DE', 'Frontend'),
(302, 16, 'sOrderprocessbillingadress', 'Rechnungsadresse, Lieferadresse und Zahlungsart k&ouml;nnen Sie jetzt noch &auml;ndern.', 'Rechnungsadresse, Lieferadresse und Zahlungsart k&ouml;nnen Sie jetzt noch &auml;ndern.', NULL, NULL, 'de_DE', 'Frontend'),
(303, 16, 'sOrderprocesssameappliesto', 'Gleiches gilt für die gew&auml;hlten Artikel.', 'Gleiches gilt für die gew&auml;hlten Artikel.', NULL, NULL, 'de_DE', 'Frontend'),
(304, 16, 'sOrderprocessimportantinfo', 'Wichtige Info zum Lieferland', 'Wichtige Info zum Lieferland', NULL, NULL, 'de_DE', 'Frontend'),
(305, 16, 'sOrderprocessacceptourterms', 'Bitte akzeptieren Sie unsere AGB', 'Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(306, 16, 'sOrderprocessbillingadress1', 'Rechnungsadresse', 'Rechnungsadresse', NULL, NULL, 'de_DE', 'Frontend'),
(307, 16, 'sOrderprocessmr', 'Herr', 'Herr', NULL, NULL, 'de_DE', 'Frontend'),
(308, 16, 'sOrderprocessms', 'Frau', 'Frau', NULL, NULL, 'de_DE', 'Frontend'),
(309, 16, 'sOrderprocesscompany', 'Firma', 'Firma', NULL, NULL, 'de_DE', 'Frontend'),
(310, 16, 'sOrderprocesschange', '&Auml;ndern', '&Auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(311, 16, 'sOrderprocessdeliveryaddress', 'Lieferadresse', 'Lieferadresse', NULL, NULL, 'de_DE', 'Frontend'),
(312, 16, 'sOrderprocessselectedpayment', 'Gew&auml;hlte Zahlungsart', 'Gew&auml;hlte Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(313, 16, 'sOrderprocessarticle', 'Artikel', 'Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(314, 16, 'sOrderprocessamount', 'Anzahl', 'Anzahl', NULL, NULL, 'de_DE', 'Frontend'),
(315, 16, 'sOrderprocessprice', 'Preis', 'Preis', NULL, NULL, 'de_DE', 'Frontend'),
(316, 16, 'sOrderprocessdispatch', 'Versandart:', 'Versandart:', NULL, NULL, 'de_DE', 'Frontend'),
(317, 16, 'sOrderprocessforwardingexpense', 'Versandkosten:', 'Versandkosten:', NULL, NULL, 'de_DE', 'Frontend'),
(318, 16, 'sOrderprocessnettotal', 'Netto-Gesamt:', 'Netto-Gesamt:', NULL, NULL, 'de_DE', 'Frontend'),
(319, 16, 'sOrderprocesstotalinclvat', 'Gesamtbetrag inkl. MwSt.:', 'Gesamtbetrag inkl. MwSt.:', NULL, NULL, 'de_DE', 'Frontend'),
(320, 16, 'sOrderprocessvouchernumber', 'Gutschein-Nummer:', 'Gutschein-Nummer:', NULL, NULL, 'de_DE', 'Frontend'),
(321, 16, 'sOrderprocessyourvouchercode', 'Bitte geben Sie hier Ihren Gutschein-Code ein und klicken auf den „Pfeil???.', 'Bitte geben Sie hier Ihren Gutschein-Code ein und klicken auf den „Pfeil???.', NULL, NULL, 'de_DE', 'Frontend'),
(322, 16, 'sOrderprocessperorderonevouche', 'Pro Bestellung kann max. ein Gutschein eingel&ouml;st werden.', 'Pro Bestellung kann max. ein Gutschein eingel&ouml;st werden.', NULL, NULL, 'de_DE', 'Frontend'),
(323, 16, 'sOrderprocesschangeyourpayment', 'Bitte wechseln Sie Ihre Zahlungsart. Der Kauf von Sofortdownloads ist mit Ihrer aktuell gewählten Zahlungsart nicht möglich!', 'Bitte wechseln Sie Ihre Zahlungsart. Der Kauf von Sofortdownloads ist mit Ihrer aktuell gewählten Zahlungsart nicht möglich!', NULL, NULL, 'de_DE', 'Frontend'),
(324, 16, 'sOrderprocessmakethepayment', 'Bitte f&uuml;hren Sie nun die Zahlung durch:', 'Bitte f&uuml;hren Sie nun die Zahlung durch:', NULL, NULL, 'de_DE', 'Frontend'),
(325, 16, 'sOrderprocessenteradditional', 'Bitte geben Sie hier zusätzliche Informationen zu Ihrer Bestellung ein', 'Bitte geben Sie hier zusätzliche Informationen zu Ihrer Bestellung ein', NULL, NULL, 'de_DE', 'Frontend'),
(326, 16, 'sOrderprocessminimumordervalue', 'Achtung. Sie haben den Mindestbestellwert von ', 'Achtung. Sie haben den Mindestbestellwert von ', NULL, NULL, 'de_DE', 'Frontend'),
(327, 16, 'sOrderprocessdoesnotreach', 'noch nicht erreicht!', 'noch nicht erreicht!', NULL, NULL, 'de_DE', 'Frontend'),
(328, 16, 'sOrderprocesschangebasket', 'Warenkorb &auml;ndern', 'Warenkorb &auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(329, 16, 'sOrderprocesscomment', 'Kommentar:', 'Kommentar:', NULL, NULL, 'de_DE', 'Frontend'),
(330, 16, 'sOrderprocessrevocation', 'Widerrufsrecht', 'Widerrufsrecht', NULL, NULL, 'de_DE', 'Frontend'),
(331, 16, 'sOrderprocessforyourorder', 'Vielen Dank f&uuml;r Ihre Bestellung bei ', 'Vielen Dank f&uuml;r Ihre Bestellung bei ', NULL, NULL, 'de_DE', 'Frontend'),
(332, 16, 'sOrderprocesswehaveprovided', 'Wir haben Ihnen eine Bestellbest&auml;tigung per eMail geschickt.', 'Wir haben Ihnen eine Bestellbest&auml;tigung per eMail geschickt.', NULL, NULL, 'de_DE', 'Frontend'),
(333, 16, 'sOrderprocessrecommendtoprint', 'Wir empfehlen die unten aufgef&uuml;hrte Bestellbest&auml;tigung auszudrucken.', 'Wir empfehlen die unten aufgef&uuml;hrte Bestellbest&auml;tigung auszudrucken.', NULL, NULL, 'de_DE', 'Frontend'),
(334, 16, 'sOrderprocessprintorderconf', 'Bestellbest&auml;tigung jetzt ausdrucken!', 'Bestellbest&auml;tigung jetzt ausdrucken!', NULL, NULL, 'de_DE', 'Frontend'),
(335, 16, 'sOrderprocessprint', 'Drucken', 'Drucken', NULL, NULL, 'de_DE', 'Frontend'),
(336, 16, 'sOrderprocessordernumber', 'Bestellnummer:', 'Bestellnummer:', NULL, NULL, 'de_DE', 'Frontend'),
(337, 16, 'sOrderprocesstransactionumber', 'Transaktionsnummer:', 'Transaktionsnummer:', NULL, NULL, 'de_DE', 'Frontend'),
(338, 16, 'sOrderprocessinformationsabout', 'Informationen zu Ihrer Bestellung:', 'Informationen zu Ihrer Bestellung:', NULL, NULL, 'de_DE', 'Frontend'),
(339, 16, 'sOrderprocesstotalprice', 'Gesamtpreis', 'Gesamtpreis', NULL, NULL, 'de_DE', 'Frontend'),
(340, 16, 'sOrderprocessfree', 'GRATIS', 'GRATIS', NULL, NULL, 'de_DE', 'Frontend'),
(341, 16, 'sOrderprocessclickhere', 'Trusted Shops G&uuml;tesiegel - Bitte hier klicken.', 'Trusted Shops G&uuml;tesiegel - Bitte hier klicken.', NULL, NULL, 'de_DE', 'Frontend'),
(342, 16, 'sOrderprocesstrustedshopmember', 'Als Trusted Shops Mitglied bieten wir Ihnen als\r\n    zus&auml;tzlichen Service die Geld-zur&uuml;ck-Garantie\r\n    von Trusted Shops. Wir &uuml;bernehmen alle\r\n    Kosten dieser Garantie, Sie m&uuml;ssen sich lediglich\r\n    anmelden.', 'Als Trusted Shops Mitglied bieten wir Ihnen als\r\n    zus&auml;tzlichen Service die Geld-zur&uuml;ck-Garantie\r\n    von Trusted Shops. Wir &uuml;bernehmen alle\r\n    Kosten dieser Garantie, Sie m&uuml;ssen sich lediglich\r\n    anmelden.', NULL, NULL, 'de_DE', 'Frontend'),
(343, 16, 'sOrderprocessspecifythetransfe', 'Bitte geben Sie bei der &Uuml;berweisung folgenden Verwendungszweck an:', 'Bitte geben Sie bei der &Uuml;berweisung folgenden Verwendungszweck an:', NULL, NULL, 'de_DE', 'Frontend'),
(344, 17, 'sPaymentcurrentlyselected', 'Aktuell ausgew&auml;hlt', 'Aktuell ausgew&auml;hlt', NULL, NULL, 'de_DE', 'Frontend'),
(345, 17, 'sPaymentmarkedfieldsare', 'Die mit einem * markierten Felder sind Pflichtfelder.', 'Die mit einem * markierten Felder sind Pflichtfelder.', NULL, NULL, 'de_DE', 'Frontend'),
(346, 17, 'sPaymentyourcreditcard', 'Ihre Kreditkarte', 'Ihre Kreditkarte', NULL, NULL, 'de_DE', 'Frontend'),
(347, 17, 'sPaymentshipping', 'Versandkosten', 'Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(348, 17, 'sPaymentchooseyourcreditcard', 'W&auml;hlen Sie Ihre Kreditkarte*:', 'W&auml;hlen Sie Ihre Kreditkarte*:', NULL, NULL, 'de_DE', 'Frontend'),
(349, 17, 'sPaymentcreditcardnumber', 'Ihre Kreditkartennummer*:', 'Ihre Kreditkartennummer*:', NULL, NULL, 'de_DE', 'Frontend'),
(350, 17, 'sPaymentvaliduntil', 'G&uuml;ltig bis*:', 'G&uuml;ltig bis*:', NULL, NULL, 'de_DE', 'Frontend'),
(351, 17, 'sPaymentmonth', 'Monat', 'Monat', NULL, NULL, 'de_DE', 'Frontend'),
(352, 17, 'sPaymentyear', 'Jahr', 'Jahr', NULL, NULL, 'de_DE', 'Frontend'),
(353, 17, 'sPaymentnameofcardholder', 'Name des Karteninhabers *:', 'Name des Karteninhabers *:', NULL, NULL, 'de_DE', 'Frontend'),
(354, 17, 'sPaymentcurrentlyselected1', 'Aktuell Gewählt', 'AKTUELL GEW&Auml;HLT', NULL, NULL, 'de_DE', 'Frontend'),
(355, 17, 'sPaymentaccountnumber', 'Kontonummer*:', 'Kontonummer*:', NULL, NULL, 'de_DE', 'Frontend'),
(356, 17, 'sPaymentbankcodenumber', 'Bankleitzahl*:', 'Bankleitzahl*:', NULL, NULL, 'de_DE', 'Frontend'),
(357, 17, 'sPaymentyourbank', 'Ihre Bank*:', 'Ihre Bank*:', NULL, NULL, 'de_DE', 'Frontend'),
(358, 18, 'sRegisterrevocation', 'Widerrufsrecht', 'Widerrufsrecht', NULL, NULL, 'de_DE', 'Frontend'),
(359, 18, 'sRegistererroroccurred', 'Ein Fehler ist aufgetreten!', 'Ein Fehler ist aufgetreten!', NULL, NULL, 'de_DE', 'Frontend'),
(360, 18, 'sRegistertraderregistration', 'Händler-Anmeldung', 'Händler-Anmeldung', NULL, NULL, 'de_DE', 'Frontend'),
(361, 18, 'sRegisteralreadyhaveatraderacc', 'Sie besitzen bereits einen Händleraccount?', 'Sie besitzen bereits einen Händleraccount?', NULL, NULL, 'de_DE', 'Frontend'),
(362, 18, 'sRegisterclickheretologin', 'Klicken Sie hier, um sich einzuloggen', 'Klicken Sie hier um sich einzuloggen', NULL, NULL, 'de_DE', 'Frontend'),
(363, 18, 'sRegisterafterregistering', 'Nach der Anmeldung sehen Sie bis zur Freischaltung Endkunden-Preise', 'Nach der Anmeldung sehen Sie bis zur Freischaltung Endkunden-Preise', NULL, NULL, 'de_DE', 'Frontend'),
(364, 18, 'sRegistersendusyourtradeproof', 'Senden Sie uns Ihren Gewerbenachweis per Fax!', 'Senden Sie uns Ihren Gewerbenachweis per Fax!', NULL, NULL, 'de_DE', 'Frontend'),
(365, 18, 'sRegistersendusyourtradeproofb', 'Senden Sie Ihren Gewerbenachweis per Fax an +49 2555 92 95 61. Wenn Sie bereits Händler bei uns sind,<br />können Sie diesen Schritt überspringen und müssen natürlich keinen Gewerbenachweis senden.', 'Senden Sie Ihren Gewerbenachweis per Fax an +49 2555 92 95 61. Wenn Sie bereits Händler bei uns sind,<br />können Sie diesen Schritt überspringen und müssen natürlich keinen Gewerbenachweis senden.', NULL, NULL, 'de_DE', 'Frontend'),
(366, 18, 'sRegisterwecheck', 'Wir prüfen Ihre Angaben und schalten Sie frei!', 'Wir prüfen Ihre Angaben und schalten Sie frei!', NULL, NULL, 'de_DE', 'Frontend'),
(367, 18, 'sRegisterwecheckyouastrader', 'Wir schalten Sie nach Prüfung als Händler frei. Sie erhalten dann von uns eine Info per E-Mail.<br />Von nun an sehen Sie direkt Ihren Händler-EK, auf den Produkt und Übersichtsseiten.', 'Wir schalten Sie nach Prüfung als Händler frei. Sie erhalten dann von uns eine Info per E-Mail.<br />Von nun an sehen Sie direkt Ihren Händler-EK, auf den Produkt und Übersichtsseiten.', NULL, NULL, 'de_DE', 'Frontend'),
(368, 18, 'sRegisteraccessdata', 'Ihre Zugangsdaten', 'Ihre Zugangsdaten', NULL, NULL, 'de_DE', 'Frontend'),
(369, 18, 'sRegisterinthefuture', 'Damit Sie in Zukunft schnell und einfach Ihre Daten &auml;ndern oder Ihre Bestellung nachverfolgen k&ouml;nnen,<br /> legen Sie hier bitte Ihre pers&ouml;nlichen Zugangsdaten fest.', 'Damit Sie in Zukunft schnell und einfach Ihre Daten &auml;ndern oder Ihre Bestellung nachverfolgen k&ouml;nnen,<br /> legen Sie hier bitte Ihre pers&ouml;nlichen Zugangsdaten fest.', NULL, NULL, 'de_DE', 'Frontend'),
(370, 18, 'sRegisteryouremail', 'Ihre eMail-Adresse*:', 'Ihre eMail-Adresse*:', NULL, NULL, 'de_DE', 'Frontend'),
(371, 18, 'sRegistersubscribenewsletter', 'Ich möchte den Newsletter abonnieren Abmeldung jederzeit möglich.', 'Ich möchte den Newsletter abonnieren Abmeldung jederzeit möglich.', NULL, NULL, 'de_DE', 'Frontend'),
(372, 18, 'sRegisternocustomeraccount', 'Kein Kundenkonto anlegen', 'Kein Kundenkonto anlegen', NULL, NULL, 'de_DE', 'Frontend'),
(373, 18, 'sRegisteryourpassword', 'Ihr Passwort*:', 'Ihr Passwort:', NULL, NULL, 'de_DE', 'Frontend'),
(374, 18, 'sRegisteryourpasswordatlast', 'Ihr Passwort muss mindestens ', 'Ihr Passwort muss mindestens ', NULL, NULL, 'de_DE', 'Frontend'),
(375, 18, 'sRegistercharacters', 'Zeichen umfassen.', 'Zeichen umfassen.', NULL, NULL, 'de_DE', 'Frontend'),
(376, 18, 'sRegisterconsiderupper', 'Ber&uuml;cksichtigen Sie Gro&szlig;- und Kleinschreibung.', 'Ber&uuml;cksichtigen Sie Gro&szlig;- und Kleinschreibung.', NULL, NULL, 'de_DE', 'Frontend'),
(377, 18, 'sRegisterrepeatyourpassword', 'Wiederholen Sie Ihr Passwort*:', 'Wiederholen Sie Ihr Passwort:', NULL, NULL, 'de_DE', 'Frontend'),
(378, 18, 'sRegisteryouraccountdata', 'Ihre Rechnungsdaten', 'Ihre Rechnungsdaten', NULL, NULL, 'de_DE', 'Frontend'),
(379, 18, 'sRegisteronthisfollowingpages', 'Auf dieser und den folgenden Seiten nennen Sie uns bitte die für Ihre Bestellung notwendigen Daten.', 'Auf dieser und den folgenden Seiten nennen Sie uns die f&uuml;r Ihre  Bestellung notwendigen Daten.', NULL, NULL, 'de_DE', 'Frontend'),
(380, 18, 'sRegistertitle', 'Anrede*:', 'Anrede*:', NULL, NULL, 'de_DE', 'Frontend'),
(381, 18, 'sRegisterpleasechoose', 'Bitte w&auml;hlen:', 'Bitte w&auml;hlen:', NULL, NULL, 'de_DE', 'Frontend'),
(382, 18, 'sRegistermr', 'Herr', 'Herr', NULL, NULL, 'de_DE', 'Frontend'),
(383, 18, 'sRegisterms', 'Frau', 'Frau', NULL, NULL, 'de_DE', 'Frontend'),
(384, 18, 'sRegistercompany', 'Firma:', 'Firma:', NULL, NULL, 'de_DE', 'Frontend'),
(385, 18, 'sRegisterdepartment', 'Abteilung:', 'Abteilung:', NULL, NULL, 'de_DE', 'Frontend'),
(386, 18, 'sRegisterfirstname', 'Vorname*:', 'Vorname*:', NULL, NULL, 'de_DE', 'Frontend'),
(388, 18, 'sRegisterlastname', 'Nachname*:', 'Nachname*:', NULL, NULL, 'de_DE', 'Frontend'),
(389, 18, 'sRegisterstreetandnumber', 'Stra&szlig;e und Nr*:', 'Stra&szlig;e und Nr*:', NULL, NULL, 'de_DE', 'Frontend');
INSERT INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`, `created`, `modified`, `locale`, `namespace`) VALUES
(390, 18, 'sRegistercityandzip', 'PLZ und Ort*:', 'PLZ und Ort*:', NULL, NULL, 'de_DE', 'Frontend'),
(391, 18, 'sRegisterphone', 'Telefon*:', 'Telefon*:', NULL, NULL, 'de_DE', 'Frontend'),
(392, 18, 'sRegisterfreetextfields', 'Freitextfelder: z.B.Handy', 'Freitextfelder: z.B.Handy', NULL, NULL, 'de_DE', 'Frontend'),
(393, 18, 'sRegisterfax', 'Fax:', 'Fax:', NULL, NULL, 'de_DE', 'Frontend'),
(394, 18, 'sRegistercountry', 'Land*:', 'Land*:', NULL, NULL, 'de_DE', 'Frontend'),
(395, 18, 'sRegisterpleaseselect', 'Bitte w&auml;hlen...', 'Bitte w&auml;hlen...', NULL, NULL, 'de_DE', 'Frontend'),
(396, 18, 'sRegisterforavatexempt', 'F&uuml;r eine Umsatzsteuerbefreite Lieferung in EU-L&auml;nder geben Sie hier bitte Ihre g&uuml;ltige UST.ID. ein.', 'F&uuml;r eine Umsatzsteuerbefreite Lieferung in EU-L&auml;nder geben Sie hier bitte Ihre g&uuml;ltige UST.ID. ein.', NULL, NULL, 'de_DE', 'Frontend'),
(397, 18, 'sRegisterbirthdate', 'Geburtsdatum:', 'Geburtsdatum:', NULL, NULL, 'de_DE', 'Frontend'),
(398, 18, 'sRegisterseperatedelivery', 'Separate Lieferadresse', 'Separate Lieferadresse', NULL, NULL, 'de_DE', 'Frontend'),
(399, 18, 'sRegistershippingaddressdiffer', 'Ihre Lieferadresse weicht von Ihrer Rechnungsadresse ab.', 'Ihre Lieferadresse weicht von Ihrer Rechnungsadresse ab.', NULL, NULL, 'de_DE', 'Frontend'),
(400, 18, 'sRegisterfieldsmarked', 'Die mit einem * markierten Felder sind Pflichtfelder.', 'Die mit einem * markierten Felder sind Pflichtfelder.', NULL, NULL, 'de_DE', 'Frontend'),
(401, 18, 'sRegisterback', 'Zur&uuml;ck', 'Zur&uuml;ck', NULL, NULL, 'de_DE', 'Frontend'),
(402, 18, 'sRegistervatid', 'Ust.Id:', 'Ust.Id:', NULL, NULL, 'de_DE', 'Frontend'),
(403, 18, 'sRegisterenterdeliveryaddress', 'Bitte geben Sie Ihre Lieferanschrift ein', 'Bitte geben Sie Ihre Lieferanschrift ein', NULL, NULL, 'de_DE', 'Frontend'),
(404, 18, 'sRegisternext', 'Weiter', 'Weiter', NULL, NULL, 'de_DE', 'Frontend'),
(405, 18, 'sRegistersave', 'Speichern', 'Speichern', NULL, NULL, 'de_DE', 'Frontend'),
(406, 18, 'sRegisterselectpayment', 'Bitte w&auml;hlen Sie die von Ihnen bevorzugte Zahlungsart', 'Bitte w&auml;hlen Sie die von Ihnen bevorzugte Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(407, 19, 'sSupportbrowse', 'Blättern:', 'Blättern:', NULL, NULL, 'de_DE', 'Frontend'),
(408, 19, 'sSupportonepageback', 'Eine Seite zur&uuml;ck bl&auml;ttern', 'Eine Seite zur&uuml;ck bl&auml;ttern', NULL, NULL, 'de_DE', 'Frontend'),
(409, 19, 'sSupportnextpage', 'Eine Seite vor bl&auml;ttern', 'Eine Seite vor bl&auml;ttern', NULL, NULL, 'de_DE', 'Frontend'),
(410, 19, 'sSupportsort', 'Sortieren:', 'Sortieren:', NULL, NULL, 'de_DE', 'Frontend'),
(411, 19, 'sSupportreleasedate', 'Erscheinungsdatum', 'Erscheinungsdatum', NULL, NULL, 'de_DE', 'Frontend'),
(412, 19, 'sSupportpopularity', 'Beliebtheit', 'Beliebtheit', NULL, NULL, 'de_DE', 'Frontend'),
(413, 19, 'sSupportlowestprice', 'Niedrigster Preis', 'Niedrigster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(414, 19, 'sSupporthighestprice', 'Höchster Preis', 'Höchster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(415, 19, 'sSupportitemtitle', 'Artikelbezeichnung', 'Artikelbezeichnung', NULL, NULL, 'de_DE', 'Frontend'),
(416, 19, 'sSupportallmanufacturers', 'Alle Hersteller anzeigen', 'Alle Hersteller anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(417, 19, 'sSupportarticleperpage', 'Artikel pro Seite:', 'Artikel pro Seite:', NULL, NULL, 'de_DE', 'Frontend'),
(418, 19, 'sSupportenterthenumbers', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', 'Bitte geben Sie die Zahlenfolge in das nachfolgende Textfeld ein', NULL, NULL, 'de_DE', 'Frontend'),
(419, 19, 'sSupportfieldsmarketwith', 'Die mit einem * markierten Felder sind Pflichtfelder.', 'Die mit einem * markierten Felder sind Pflichtfelder.', NULL, NULL, 'de_DE', 'Frontend'),
(420, 19, 'sSupportsend', 'Senden', 'Senden', NULL, NULL, 'de_DE', 'Frontend'),
(421, 19, 'sSupportentrynotfound', 'Eintrag nicht gefunden.', 'Eintrag nicht gefunden.', NULL, NULL, 'de_DE', 'Frontend'),
(422, 19, 'sSupportback', 'Zur&uuml;ck', 'Zur&uuml;ck', NULL, NULL, 'de_DE', 'Frontend'),
(423, 20, 'sAjaxcomparearticle', 'Artikel vergleichen', 'Artikel vergleichen', NULL, NULL, 'de_DE', 'Frontend'),
(424, 20, 'sAjaxstartcompare', 'Vergleich starten', 'Vergleich starten', NULL, NULL, 'de_DE', 'Frontend'),
(425, 20, 'sAjaxdeletecompare', 'Vergleich löschen', 'Vergleich löschen', NULL, NULL, 'de_DE', 'Frontend'),
(426, 9, 'sArticleaddtobasked', 'in den Warenkorb', 'in den Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(427, 21, 'sSearchelected', 'Gewählt:', 'Gewählt:', NULL, NULL, 'de_DE', 'Frontend'),
(428, 21, 'sSearchallcategories', 'Alle Kategorien anzeigen', 'Alle Kategorien anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(429, 21, 'sSearchsearchcategories', 'Suchergebnis nach Kategorien', 'Suchergebnis nach Kategorien', NULL, NULL, 'de_DE', 'Frontend'),
(430, 21, 'sSearchunfortunatelytherewere', 'Leider wurden zu', 'Leider wurden zu', NULL, NULL, 'de_DE', 'Frontend'),
(431, 21, 'sSearchnoarticlesfound', 'keine Artikel gefunden!', 'keine Artikel gefunden!', NULL, NULL, 'de_DE', 'Frontend'),
(432, 21, 'sSearchsearchtermtooshort', 'Der eingegebene Suchbegriff ist leider zu kurz.', 'Der eingegebene Suchbegriff ist leider zu kurz.', NULL, NULL, 'de_DE', 'Frontend'),
(433, 21, 'sSearchbrowse', 'Bl&auml;ttern:', 'Bl&auml;ttern:', NULL, NULL, 'de_DE', 'Frontend'),
(434, 21, 'sSearchonepageback', 'Eine Seite zurück', 'Eine Seite zurück', NULL, NULL, 'de_DE', 'Frontend'),
(435, 21, 'sSearchnextpage', 'Eine Seite vor', 'Eine Seite vor', NULL, NULL, 'de_DE', 'Frontend'),
(436, 21, 'sSearchrelevance', 'Relevanz', 'Relevanz', NULL, NULL, 'de_DE', 'Frontend'),
(437, 21, 'sSearchreleasedate', 'Erscheinungsdatum', 'Erscheinungsdatum', NULL, NULL, 'de_DE', 'Frontend'),
(438, 21, 'sSearchpopularity', 'Beliebtheit', 'Beliebtheit', NULL, NULL, 'de_DE', 'Frontend'),
(439, 21, 'sSearchlowestprice', 'Niedrigster Preis', 'Niedrigster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(440, 21, 'sSearchhighestprice', 'Höchster Preis', 'Höchster Preis', NULL, NULL, 'de_DE', 'Frontend'),
(441, 21, 'sSearchitemtitle', 'Artikelbezeichnung', 'Artikelbezeichnung', NULL, NULL, 'de_DE', 'Frontend'),
(442, 21, 'sSearchsort', 'Sortieren:', 'Sortieren:', NULL, NULL, 'de_DE', 'Frontend'),
(443, 21, 'sSearcharticlesperpage', 'Artikel pro Seite:', 'Artikel pro Seite:', NULL, NULL, 'de_DE', 'Frontend'),
(444, 21, 'sSearchto', 'Zu', 'Zu', NULL, NULL, 'de_DE', 'Frontend'),
(445, 21, 'sSearchwere', 'wurden', 'wurden', NULL, NULL, 'de_DE', 'Frontend'),
(446, 21, 'sSearcharticlesfound', 'Artikel gefunden!', 'Artikel gefunden!', NULL, NULL, 'de_DE', 'Frontend'),
(447, 21, 'sSearchsearchresult', 'Suchergebnis', 'Suchergebnis', NULL, NULL, 'de_DE', 'Frontend'),
(448, 21, 'sSearchafterfilters', 'nach Filtern', 'nach Filtern', NULL, NULL, 'de_DE', 'Frontend'),
(449, 21, 'sSearchothermanufacturers', 'Weitere Hersteller:', 'Weitere Hersteller:', NULL, NULL, 'de_DE', 'Frontend'),
(450, 21, 'sSearchshowall', 'Alle anzeigen', 'Alle anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(451, 21, 'sSearchallfilters', 'Alle Filter', 'Alle Filter', NULL, NULL, 'de_DE', 'Frontend'),
(452, 21, 'sSearchbymanufacturer', 'nach Hersteller', 'nach Hersteller', NULL, NULL, 'de_DE', 'Frontend'),
(453, 21, 'sSearchallmanufacturer', 'Alle Hersteller', 'Alle Hersteller', NULL, NULL, 'de_DE', 'Frontend'),
(454, 21, 'sSearchbyprice', 'nach Preis', 'nach Preis', NULL, NULL, 'de_DE', 'Frontend'),
(455, 21, 'sSearchallprices', 'Alle Preise', 'Alle Preise', NULL, NULL, 'de_DE', 'Frontend'),
(456, 22, 'sIndexlanguage', 'Sprache:', 'Sprache:', NULL, NULL, 'de_DE', 'Frontend'),
(457, 22, 'sIndexgerman', 'Deutsch', 'Deutsch', NULL, NULL, 'de_DE', 'Frontend'),
(458, 22, 'sIndexenglish', 'Englisch', 'Englisch', NULL, NULL, 'de_DE', 'Frontend'),
(459, 22, 'sIndexfrench', 'Französisch', 'Französisch', NULL, NULL, 'de_DE', 'Frontend'),
(460, 22, 'sIndexcurrency', 'Währung:', 'Währung:', NULL, NULL, 'de_DE', 'Frontend'),
(461, 22, 'sIndexbacktohome', 'zur Startseite wechseln', 'zur Startseite wechseln', NULL, NULL, 'de_DE', 'Frontend'),
(462, 22, 'sIndexhome', 'Home', 'Home', NULL, NULL, 'de_DE', 'Frontend'),
(463, 22, 'sIndexviewmyaccount', 'Mein Konto anzeigen', 'Mein Konto anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(464, 22, 'sIndexaccount', 'Mein Konto', 'Mein Konto', NULL, NULL, 'de_DE', 'Frontend'),
(465, 22, 'sIndexshownotepad', 'Merkzettel anzeigen', 'Merkzettel anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(466, 22, 'sIndexnotepad', 'Merkzettel', 'Merkzettel', NULL, NULL, 'de_DE', 'Frontend'),
(467, 22, 'sIndexarticle', 'Artikel', 'Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(468, 22, 'sIndexmybasket', 'Warenkorb anzeigen', 'Warenkorb anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(469, 22, 'sIndexbasket', 'Warenkorb', 'Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(470, 22, 'sIndexcompareupto5articles', 'Sie können maximal 5 Artikel in einem Schritt vergleichen!', 'Sie können maximal 5 Artikel in einem Schritt vergleichen!', NULL, NULL, 'de_DE', 'Frontend'),
(471, 22, 'sIndexyouarehere', 'Sie sind hier:', 'Sie sind hier:', NULL, NULL, 'de_DE', 'Frontend'),
(472, 22, 'sIndextrustedshopslabel', 'Trusted Shops G&uuml;tesiegel - Bitte hier G&uuml;ltigkeit pr&uuml;fen!', 'Trusted Shops G&uuml;tesiegel - Bitte hier G&uuml;ltigkeit pr&uuml;fen!', NULL, NULL, 'de_DE', 'Frontend'),
(473, 22, 'sIndexcertifiedonlineshop', 'Gepr&uuml;fter Online-Shop mit kostenloser Geld-zurück- Garantie von Trusted Shops. Klicken Sie auf das G&uuml;tesiegel, um die G&uuml;ltigkeit zu pr&uuml;fen.', 'Gepr&uuml;fter Online-Shop mit kostenloser Geld-zurück- Garantie von Trusted Shops. Klicken Sie auf das G&uuml;tesiegel, um die G&uuml;ltigkeit zu pr&uuml;fen.', NULL, NULL, 'de_DE', 'Frontend'),
(474, 22, 'sIndexsite', 'Seite', 'Seite', NULL, NULL, 'de_DE', 'Frontend'),
(475, 22, 'sIndexfrom', 'von', 'von', NULL, NULL, 'de_DE', 'Frontend'),
(476, 22, 'sIndexto', 'Zu', 'Zu', NULL, NULL, 'de_DE', 'Frontend'),
(477, 22, 'sIndexwere', 'wurden', 'wurden', NULL, NULL, 'de_DE', 'Frontend'),
(478, 22, 'sIndexarticlesfound', 'Artikel gefunden!', 'Artikel gefunden!', NULL, NULL, 'de_DE', 'Frontend'),
(479, 22, 'sIndexhello', 'Hallo', 'Hallo', NULL, NULL, 'de_DE', 'Frontend'),
(480, 22, 'sIndexwelcometoyour', 'und Willkommen in Ihrem persönlichen', 'und Willkommen in Ihrem persönlichen', NULL, NULL, 'de_DE', 'Frontend'),
(481, 22, 'sIndexclientaccount', 'Kundenkonto', 'Kundenkonto', NULL, NULL, 'de_DE', 'Frontend'),
(482, 22, 'sIndexoverview', 'Übersicht', 'Übersicht', NULL, NULL, 'de_DE', 'Frontend'),
(483, 22, 'sIndexmyorders', 'Meine Bestellungen', 'Meine Bestellungen', NULL, NULL, 'de_DE', 'Frontend'),
(484, 22, 'sIndexmyinstantdownloads', 'Meine Sofortdownloads', 'Meine Sofortdownloads', NULL, NULL, 'de_DE', 'Frontend'),
(485, 22, 'sIndexchangebillingaddress', 'Rechnungsadresse &auml;ndern', 'Rechnungsadresse &auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(486, 22, 'sIndexchangedeliveryaddress', 'Lieferadresse &auml;ndern', 'Lieferadresse &auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(487, 22, 'sIndexchangepayment', 'Zahlungsart &auml;ndern', 'Zahlungsart &auml;ndern', NULL, NULL, 'de_DE', 'Frontend'),
(488, 22, 'sIndexlogout', 'Abmelden Logout', 'Abmelden Logout', NULL, NULL, 'de_DE', 'Frontend'),
(489, 26, 'sIndexallpricesexcludevat', '* Alle Preise verstehen sich zzgl. Mehrwertsteuer und ', '* Alle Preise verstehen sich zzgl. Mehrwertsteuer und ', NULL, NULL, 'de_DE', 'Frontend'),
(490, 22, 'sIndexshipping', 'Versandkosten', 'Versandkosten', NULL, NULL, 'de_DE', 'Frontend'),
(491, 22, 'sIndexpossiblydeliveryfees', 'sowie ggf. Nachnahmegeb&uuml;hren, wenn nicht anders beschrieben', 'sowie ggf. Nachnahmegeb&uuml;hren, wenn nicht anders beschrieben', NULL, NULL, 'de_DE', 'Frontend'),
(492, 26, 'sIndexpricesinclvat', '* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl.', '* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl.', NULL, NULL, 'de_DE', 'Frontend'),
(493, 22, 'sIndexandpossibledelivery', 'und ggf. Nachnahmegeb&uuml;hren, wenn nicht anders beschrieben', 'und ggf. Nachnahmegeb&uuml;hren, wenn nicht anders beschrieben', NULL, NULL, 'de_DE', 'Frontend'),
(494, 22, 'sIndexrealizedwith', 'realisiert mit der', 'realisiert mit der', NULL, NULL, 'de_DE', 'Frontend'),
(495, 22, 'sIndexrealizedwiththeshopsystem', 'Shopsoftware Shopware', 'Shopsoftware Shopware', NULL, NULL, 'de_DE', 'Frontend'),
(496, 22, 'sIndexshopware', 'Shopsoftware Shopware', 'Shopware', NULL, NULL, 'de_DE', 'Frontend'),
(497, 22, 'sIndexcopyright', 'Copyright &copy; 2010 shopware.ag - Alle Rechte vorbehalten.', 'Copyright © 2008 shopware.ag - Alle Rechte vorbehalten.', NULL, NULL, 'de_DE', 'Frontend'),
(498, 22, 'sIndexonthepicture', 'Auf dem Bild zu sehen:', 'Auf dem Bild zu sehen:', NULL, NULL, 'de_DE', 'Frontend'),
(499, 22, 'sIndexreleased', 'Freigegeben', 'Freigegeben', NULL, NULL, 'de_DE', 'Frontend'),
(500, 22, 'sIndexnoagerestriction', 'Ohne Altersbeschr&auml;nkung', 'Ohne Altersbeschr&auml;nkung', NULL, NULL, 'de_DE', 'Frontend'),
(501, 22, 'sIndexreleasedfrom6years', 'Freigegeben ab 6 Jahren', 'Freigegeben ab 6 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(502, 22, 'sIndexreleasedfrom12years', 'Freigegeben ab 12 Jahren', 'Freigegeben ab 12 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(503, 22, 'sIndexreleasedfrom16years', 'Freigegeben ab 16 Jahren', 'Freigegeben ab 16 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(504, 22, 'sIndexreleasedfrom18years', 'Freigegeben ab 18 Jahren', 'Freigegeben ab 18 Jahren', NULL, NULL, 'de_DE', 'Frontend'),
(505, 22, 'sIndexpagenumber', 'Seitenzahl:', 'Seitenzahl:', NULL, NULL, 'de_DE', 'Frontend'),
(506, 22, 'sIndexextra', 'Extras:', 'Extras:', NULL, NULL, 'de_DE', 'Frontend'),
(507, 22, 'sIndexprinting', 'Druck:', 'Druck:', NULL, NULL, 'de_DE', 'Frontend'),
(508, 22, 'sIndexcover', 'Umschlag:', 'Umschlag:', NULL, NULL, 'de_DE', 'Frontend'),
(509, 22, 'sIndexappear', 'Erschienen:', 'Erschienen:', NULL, NULL, 'de_DE', 'Frontend'),
(510, 22, 'sIndexordernumber', 'Bestellnr.:', 'Bestellnr.:', NULL, NULL, 'de_DE', 'Frontend'),
(511, 22, 'sIndexhowcaniacquire', 'Wie kann ich Spiele erwerben, die erst ab 18 Jahren freigegeben sind?', 'Wie kann ich Spiele erwerben, die erst ab 18 Jahren freigegeben sind?', NULL, NULL, 'de_DE', 'Frontend'),
(512, 22, 'sIndexforreasonofinformation', 'Aus Informationsgründen stellen wir in unserem Online-Shop auch Spiele aus, die keine Jugendfreigabe besitzen. Es gibt aber für Kunden, welche das achtzehnte Lebensjahr bereits vollendet haben, trotzdem eine Möglichkeit diese Spiele zu kaufen.\r\nJetzt können Sie bei arktis.de auch ganz einfach USK 18 Spiele über den Postversandweg bestellen. Um die Anforderungen des Jugendschutzgesetztes zu erfüllen müssen Sie sich dazu einfach per Postident personalisieren lassen. Dies funktioniert ganz einfach:', 'Aus Informationsgründen stellen wir in unserem Online-Shop auch Spiele aus, die keine Jugendfreigabe besitzen.Es gibt aber für Kunden, welche das achtzehnte Lebensjahr bereits vollendet haben, trotzdem eine Möglichkeit diese Spiele zu kaufen.', NULL, NULL, 'de_DE', 'Frontend'),
(513, 10, 'sBasketInquiry', 'Angebot anfordern', 'Angebot anfordern', NULL, NULL, 'de_DE', 'Frontend'),
(514, 19, 'sINQUIRYTEXTBASKET', 'Bitte unterbreiten Sie mir ein Angebot über die nachfolgenden Positionen', 'Anfrage-Formular Text Warenkorb', NULL, NULL, 'de_DE', 'Frontend'),
(515, 22, 'sIndexyouloadthepdf', '1. Sie laden das PDF Anmeldeformular in Ihrem Kundenbereich und drucken es aus. Bitte legen Sie dieses\r\nFormular zusammen mit Ihrem Personalausweis oder Reisepass in einer Filiale der Deutsche Post vor. Kreuzen Sie das zutreffende Feld an und unterschreiben Sie das Formular.', '1. Sie laden das PDF Anmeldeformular in Ihrem Kundenbereich und drucken es aus. Bitte legen Sie dieses\r\nFormular zusammen mit Ihrem Personalausweis oder Reisepass in einer Filiale der Deutsche Post vor. Kreuzen Sie das zutreffende Feld an und unterschreiben Sie das Formular.', NULL, NULL, 'de_DE', 'Frontend'),
(516, 22, 'sIndexpostident', '2. POSTIDENT: Die Deutsche Post erstellt ein POSTIDENT-Formular zur Bestätigung Ihrer Volljährigkeit. Bitte unterschreiben Sie auch dieses. Die Deutsche Post sendet dann beide unterschriebenen Dokumente an .', '2. POSTIDENT: Die Deutsche Post erstellt ein POSTIDENT-Formular zur Bestätigung Ihrer Volljährigkeit. Bitte unterschreiben Sie auch dieses. Die Deutsche Post sendet dann beide unterschriebenen Dokumente an ARKTIS.', NULL, NULL, 'de_DE', 'Frontend'),
(517, 22, 'sIndexactivation', '3. Freischaltung: Sobald uns die beiden ausgefüllten Formulare vorliegen und sofern Ihre Unterschriften übereinstimmen, schalten wir Sie für Spiele ab 18 frei. Anschließend senden wir Ihnen umgehend eine Bestätigungs-eMail. Danach können Sie ganz einfach die USK 18 Titel bequem über den Webshop bestellen.', '3. Freischaltung: Sobald uns die beiden ausgefüllten Formulare vorliegen und sofern Ihre Unterschriften übereinstimmen, schalten wir Sie für Spiele ab 18 frei. Anschließend senden wir Ihnen umgehend eine Bestätigungs-eMail. Danach können Sie ganz einfach die USK 18 Titel bequem über den Webshop unter www.arktis.de bestellen.', NULL, NULL, 'de_DE', 'Frontend'),
(518, 22, 'sIndexsimilararticles', '&Auml;hnliche Artikel', '&Auml;hnliche Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(519, 22, 'sIndexproductinformations', 'Produktinformationen', 'Produktinformationen', NULL, NULL, 'de_DE', 'Frontend'),
(520, 22, 'sIndexsystemrequirementsfor', 'Systemanforderungen f&uuml;r', 'Systemanforderungen f&uuml;r', NULL, NULL, 'de_DE', 'Frontend'),
(521, 22, 'sIndexavailabledownloads', 'Verf&uuml;gbare Downloads:', 'Verf&uuml;gbare Downloads:', NULL, NULL, 'de_DE', 'Frontend'),
(522, 22, 'sIndexdownload', 'Download', 'Download', NULL, NULL, 'de_DE', 'Frontend'),
(523, 22, 'sIndexourcommentto', 'Unser Kommentar zu', 'Unser Kommentar zu', NULL, NULL, 'de_DE', 'Frontend'),
(524, 22, 'sIndexsuitablearticles', 'Hierzu passende Artikel:', 'Hierzu passende Artikel:', NULL, NULL, 'de_DE', 'Frontend'),
(525, 9, 'sArticletosave', 'Speichern', 'Speichern', NULL, NULL, 'de_DE', 'Frontend'),
(526, 9, 'sArticlereleased', 'Freigegeben', 'Freigegeben', NULL, NULL, 'de_DE', 'Frontend'),
(527, 9, 'sArticleinformationsabout', 'Informationen zu USK-18 Artikeln', 'Informationen zu USK-18 Artikeln', NULL, NULL, 'de_DE', 'Frontend'),
(528, 9, 'sArticlewriteanassessment', 'Schreiben Sie eine Bewertung', 'Schreiben Sie eine Bewertung', NULL, NULL, 'de_DE', 'Frontend'),
(529, 10, 'sBasketshippingdifference', 'Bestellen Sie für weitere \r\n#1 #2 um Ihre Bestellung versandkostenfrei zu erhalten!', 'Bestellen Sie für weitere x,xx € ', NULL, NULL, 'de_DE', 'Frontend'),
(530, 10, 'sBasketrecalculate', 'Neu berechnen', 'Neu berechnen', NULL, NULL, 'de_DE', 'Frontend'),
(531, 15, 'sLoginlogin', 'Anmelden', 'Anmelden', NULL, NULL, 'de_DE', 'Frontend'),
(532, 16, 'sOrderprocessforthemoneyback', 'Anmeldung zur Geld-zur&uuml;ck-Garantie', 'Anmeldung zur Geld-zur&uuml;ck-Garantie', NULL, NULL, 'de_DE', 'Frontend'),
(533, 16, 'sOrderprocesssendordernow', 'Bestellung jetzt absenden', 'Bestellung jetzt absenden', NULL, NULL, 'de_DE', 'Frontend'),
(534, 21, 'sSearchnosearchengine', 'Keine Suche', 'No search engine support', NULL, NULL, 'de_DE', 'Frontend'),
(535, 21, 'sSearchshowallresults', 'Alle Ergebnisse anzeigen', 'Alle Ergebnisse anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(536, 21, 'sSearchmanufacturer', 'Hersteller:', 'Hersteller:', NULL, NULL, 'de_DE', 'Frontend'),
(537, 21, 'sSearchcategories', 'Kategorien:', 'Kategorien:', NULL, NULL, 'de_DE', 'Frontend'),
(538, 19, 'sSupportfilloutallredfields', 'Bitte füllen Sie alle rot markierten Felder aus.', 'Bitte füllen Sie alle rot markierten Felder aus.', NULL, NULL, 'de_DE', 'Frontend'),
(539, 10, 'sBasketPremiumDifference', 'noch', 'Prämie: Noch x,xx €', NULL, NULL, 'de_DE', 'Frontend'),
(540, 9, 'sArticlesend', 'Senden', 'Senden', NULL, NULL, 'de_DE', 'Frontend'),
(541, 9, 'sArticleof1', 'von', 'von', NULL, NULL, 'de_DE', 'Frontend'),
(542, 10, 'sBasketLessStock', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern', 'Nicht genügend auf Lager', NULL, NULL, 'de_DE', 'Frontend'),
(543, 10, 'sBasketLessStockRest', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern. x von y lieferbar', 'Nicht genügend auf Lager, X verfügbar', NULL, NULL, 'de_DE', 'Frontend'),
(544, 9, 'sArticlePricePerUnit', 'Stückpreis', 'Stückpreis', NULL, NULL, 'de_DE', 'Frontend'),
(545, 19, 'sINQUIRYTEXTARTICLE', 'Ich habe folgende Fragen zum Artikel', 'Anfrage-Formular Text Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(546, 9, 'sArticleCompareDetail', 'Diesen Artikel vergleichen', 'Artikel vergleichen', NULL, NULL, 'de_DE', 'Frontend'),
(3821, 0, 'AddArticle', 'Artikel hinzufügen', '', '2010-08-17 12:40:24', '2010-08-17 12:40:24', 'de_DE', 'Frontend'),
(548, 22, 'sIndexMetaRobots', 'index,follow', 'Meta Robots', NULL, NULL, 'de_DE', 'Frontend'),
(549, 22, 'sIndexMetaRevisit', '15 days', 'Meta Revisit', NULL, NULL, 'de_DE', 'Frontend'),
(550, 17, 'sPaymentyourname', 'Konto-Inhaber*:', 'Konto-Inhaber*:', NULL, NULL, 'de_DE', 'Frontend'),
(551, 16, 'sOrderprocessTax', 'Enthaltene MwSt.', 'Enthaltene MwSt.', NULL, NULL, 'de_DE', 'Frontend'),
(552, 24, 'sPaypalexpressTXNPending', 'Sobald Ihre Überweisung bei PayPal eingegangen ist, werden wir informiert – und verschicken die Ware dann umgehend.<br /><br />Sie haben den Betrag noch nicht überwiesen? Kein Problem. Die Bankverbindung von PayPal können Sie jederzeit in Ihrem PayPal-Konto abrufen.\r\nKlicken Sie in der Kontoübersicht direkt neben der Zahlung auf den Link "Details". Auf der nächsten Seite finden Sie unter dem Link "So schließen Sie Ihre PayPal-Zahlung per Banküberweisung ab“ alle nötigen Informationen.', 'Banktxnpending', NULL, NULL, 'de_DE', 'Frontend'),
(553, 24, 'sPaypalexpressPartialRefund', 'Wiedergutschrift: ', 'Wiedergutschrift', NULL, NULL, 'de_DE', 'Frontend'),
(554, 24, 'sPaypalexpressOrder', 'Vielen Dank für Ihre Bestellung.', 'Bestellbestätigung', NULL, NULL, 'de_DE', 'Frontend'),
(555, 24, 'sPaypalexpress', 'oder', 'Alternativ oder', NULL, NULL, 'de_DE', 'Frontend'),
(556, 24, 'sPaypalexpressApiErrorLink', 'Zurück zu PayPal', 'API Error Link', NULL, NULL, 'de_DE', 'Frontend'),
(557, 24, 'sIndexPayPallabel', 'PayPal-Bezahlmethoden-Logo', 'Index PayPal Logo', NULL, NULL, 'de_DE', 'Frontend'),
(558, 24, 'sPaypalexpressApiError', 'Bei der von Ihnen ausgwählten Zahlungsmethode ist ein Fehler aufgetreten. Bitte klicken Sie den unteren <br />Link um bei PayPal eine alternative Zahlungsmethode auszuwählen und mit dem Kauf fortzufahren.', 'API Error', NULL, NULL, 'de_DE', 'Frontend'),
(559, 25, 'sTicketSysReplySentSuccessful', 'Ihre Antwort wurde erfolgreich übertragen!', 'Ihre Antwort wurde erfolgreich übertragen!', NULL, NULL, 'de_DE', 'Frontend'),
(560, 25, 'sTicketSysFillRequiredFields', 'Bitte füllen Sie alle Felder aus!', 'Bitte füllen Sie alle Felder aus!', NULL, NULL, 'de_DE', 'Frontend'),
(561, 25, 'sTicketSysTicketIdNotFound', 'Es existiert kein Ticket mit dieser ID.', 'Es existiert kein Ticket mit dieser ID.', NULL, NULL, 'de_DE', 'Frontend'),
(562, 25, 'sTicketSysDetailsOfTicket', 'Details zu dem Ticket', 'Details zu dem Ticket', NULL, NULL, 'de_DE', 'Frontend'),
(563, 25, 'sTicketSysYourTicketEnquiry', 'Ihre Ticketanfrage:', 'Ihre Ticketanfrage:', NULL, NULL, 'de_DE', 'Frontend'),
(564, 25, 'sTicketSysYourTicketAnswer', 'Ihre Antwort', 'Ihre Antwort', NULL, NULL, 'de_DE', 'Frontend'),
(565, 25, 'sTicketSysTicketClosed', 'Dieses Ticket wurde geschlossen.', 'Dieses Ticket wurde geschlossen.', NULL, NULL, 'de_DE', 'Frontend'),
(566, 25, 'sTicketSysWorkingForAnswer', 'Dieses Ticket wird zur Zeit noch bearbeitet.', 'Dieses Ticket wird zur Zeit noch bearbeitet.', NULL, NULL, 'de_DE', 'Frontend'),
(567, 25, 'sTicketSysTicketFrom', 'Gestellt am', 'Gestellt am', NULL, NULL, 'de_DE', 'Frontend'),
(568, 25, 'sTicketSysTicketId', 'TicketID', 'TicketID', NULL, NULL, 'de_DE', 'Frontend'),
(569, 25, 'sTicketSysTicketType', 'Tickettyp', 'Tickettyp', NULL, NULL, 'de_DE', 'Frontend'),
(570, 25, 'sTicketSysTicketStatus', 'Status', 'Status', NULL, NULL, 'de_DE', 'Frontend'),
(571, 25, 'sTicketSysShowDetails', '[Details anzeigen]', '[Details anzeigen]', NULL, NULL, 'de_DE', 'Frontend'),
(572, 25, 'sTicketSysSupportManagement', 'Supportverwaltung', 'Supportverwaltung', NULL, NULL, 'de_DE', 'Frontend'),
(573, 29, 'sHeidelpayCancel', 'Die Bezahlung wurde nicht durchgeführt.', 'Text Abbruchseite', NULL, NULL, 'de_DE', 'Frontend'),
(574, 29, 'sHeidelpayHeadCancel', 'Die Bestellung wurde abgebrochen.', 'Überschrift Abbruchseite', NULL, NULL, 'de_DE', 'Frontend'),
(575, 29, 'sHeidelpayHeadSuccess', 'Ihre Bestellung ist erfolgreich bei uns eingegangen.', 'Überschrift Erfolgseite', NULL, NULL, 'de_DE', 'Frontend'),
(576, 29, 'sHeidelpaySuccess', 'Die Zahlung war erfolgreich.', 'Texgt Erfolgseite', NULL, NULL, 'de_DE', 'Frontend'),
(577, 29, 'sHeidelpayHeadFail', 'Bei der Bestellung ist ein Fehler aufgetreten.', 'Überschrift Fehlerseite', NULL, NULL, 'de_DE', 'Frontend'),
(578, 29, 'sHeidelpayFail', 'Die Bezahlung konnte nicht durchgeführt werden.', 'Text Fehlerseite', NULL, NULL, 'de_DE', 'Frontend'),
(579, 9, 'sCompareheadlinepicture', 'Bild', 'Artikelvergleich Bild', NULL, NULL, 'de_DE', 'Frontend'),
(580, 9, 'sCompareheadlinename', 'Name', 'Artikelvergleich Name', NULL, NULL, 'de_DE', 'Frontend'),
(581, 9, 'sCompareheadlinevoting', 'Bewertung', 'Artikelvergleich Bewertung', NULL, NULL, 'de_DE', 'Frontend'),
(582, 9, 'sCompareheadlinedescription', 'Beschreibung', 'Artikelvergleich Beschreibung', NULL, NULL, 'de_DE', 'Frontend'),
(583, 9, 'sCompareheadlineprice', 'Preis', 'Artikelvergleich Preis', NULL, NULL, 'de_DE', 'Frontend'),
(584, 0, 'sArticleCommitSaved', 'Vielen Dank für die Abgabe Ihrer Bewertung! Ihre Bewertung wird nach Überprüfung freigeschaltet.', 'Bewertung erfolgreich gespeichert', NULL, NULL, 'de_DE', 'Frontend'),
(585, 9, 'sArticleNotAvailable', 'Diese Auswahl steht nicht zur Verfügung!', 'Diese Auswahl steht nicht zur Verfügung!', NULL, NULL, 'de_DE', 'Frontend'),
(586, 8, 'sAllpricesexcludevat', '* Alle Preise verstehen sich zzgl. Mehrwertsteuer und  <span style="text-decoration: underline;"><a title="Versandkosten" href="{$sBasefile}?sViewport=custom&cCUSTOM=6">Versandkosten</a></span> sowie ggf. Nachnahmegebühren, wenn nicht anders beschrieben', 'Versandkosten Footer Text zzgl MwSt', NULL, NULL, 'de_DE', 'Frontend'),
(587, 8, 'sAllpricesinclvat', '* Alle Preise inkl. gesetzl. Mehrwertsteuer zzgl. <span style="text-decoration: underline;"><a title="Versandkosten" href="{$sBasefile}?sViewport=custom&cCUSTOM=6">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben', 'Versandkosten Footer Text inkl. MwSt', NULL, NULL, 'de_DE', 'Frontend'),
(588, 10, 'sBasketNoDispatches', 'Achtung: Für Ihr Warenkorb/Adresse wurde keine Versandart hinterlegt.<br /> Bitte kontaktieren Sie den Shopbetreiber.<br />', 'keine Versandart', NULL, NULL, 'de_DE', 'Frontend'),
(589, 23, 'sVoucherBoundToArticles', 'Dieser Gutschein ist nur für bestimmte Produkte gültig.', 'Dieser Gutschein ist nur für bestimmte Produkte gültig.', NULL, NULL, 'de_DE', 'Frontend'),
(590, 9, 'sArticleyourmail', 'Ihre eMail-Adresse', 'Ihre eMail-Adresse', NULL, NULL, 'de_DE', 'Frontend'),
(591, 9, 'sArticleCommitSavedOptIn', 'Vielen Dank für die Abgabe Ihrer Bewertung! \r\n	Sie erhalten in wenigen Minuten eine Bestätigungsmail.\r\n	Bestätigen Sie den Link in dieser eMail um die Bewertung freizugeben.', 'Bewertung erfolgreich gespeichert Opt-In', NULL, NULL, 'de_DE', 'Frontend'),
(592, 12, 'sMailConfirmation', 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet.\r\n	Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestätigen.', '', NULL, NULL, 'de_DE', 'Frontend'),
(593, 26, 'sCampaignsUnsubscribe', 'Vom Newsletter abmelden', 'Vom Newsletter abmelden', NULL, NULL, 'de_DE', 'Frontend'),
(594, 26, 'sCampaignsNavigation', '<a href="#" target="_blank" style="font-size:10px;">Kontakt</a> | <a href="#" target="_blank" style="font-size:10px;">Impressum</a>', 'Kontakt / Impressum', NULL, NULL, 'de_DE', 'Frontend'),
(595, 26, 'sCampaignsPlain', 'Sie erhalten diesen Newsletter in der Text-Darstellung, besuchen Sie bitte unseren Shop um auf die Angebote zugreifen zu können.', 'Sie erhalten diesen Newsletter in der Text-Darstellung, besuchen Sie bitte unseren Shop um auf die Angebote zugreifen zu können.', NULL, NULL, 'de_DE', 'Frontend'),
(596, 24, 'sPaypalexpressFormAcceptAGB', 'Bitte akzeptieren Sie unsere AGB', 'Form -Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(597, 24, 'sPaypalexpressFormAgbError', '<span style="color:#F00;">Bitte akzeptieren Sie unsere AGB</span>', 'FormError - Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(598, 24, 'sPaypalexpressFormButton', 'Weiter zur Bezahlung mit PayPal', 'Form - Weiter zur Bezahlung mit PayPal', NULL, NULL, 'de_DE', 'Frontend'),
(599, 24, 'sPaypalexpressFormInfo', 'Mit Klick auf diesen Button leiten wir Sie automatisch zu PayPal weiter. Dort können Sie Ihren Einkauf sicher und schnell bezahlen. Danach bekommen Sie von uns umgehend eine Bestellbestätigung.<br />', 'Form - Mit Klick auf diesen Button...', NULL, NULL, 'de_DE', 'Frontend'),
(600, 17, 'sClickAndBuyChoosenPayment', 'Gewählte Zahlungsart', 'Clickandbuy: Gewählte Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(601, 17, 'sClickAndBuyProceedToPayment', 'Weiter zur Zahlung', 'Clickandbuy: Weiter zur Zahlung', NULL, NULL, 'de_DE', 'Frontend'),
(602, 17, 'sClickAndBuyAcceptOurTerms', '<div class="error">Bitte akzeptieren Sie unsere AGB</div>', 'Clickandbuy: Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(603, 17, 'sClickAndBuyOrderAlreadySent', 'Die Bestellung wurde bereits abgeschickt<br /><a href="javascript:history.back;">zurück</a>', 'Clickandbuy: Bestellung wurde bereits abgeschickt', NULL, NULL, 'de_DE', 'Frontend'),
(604, 17, 'sIPaymentCreditcardHolder', 'Kreditkarten-Inhaber', 'iPayment: Kreditkarten-Inhaber', NULL, NULL, 'de_DE', 'Frontend'),
(605, 17, 'sIPaymentAmount', 'Bestellsumme', 'iPayment: Bestellsumme', NULL, NULL, 'de_DE', 'Frontend'),
(606, 17, 'sIPaymentCreditcardNumber', 'Kreditkarten-Nummer', 'iPayment: Kreditkarten-Nummer', NULL, NULL, 'de_DE', 'Frontend'),
(607, 17, 'sIPaymentCreditCheckDigit', 'Kreditkarten-Prüfziffer', 'iPayment: Kreditkarten-Prüfziffer', NULL, NULL, 'de_DE', 'Frontend'),
(608, 17, 'sIPaymentInfoField', '3-stellig im Unterschriftfeld auf der Rückseite der Karte Visa, Mastercard<br/><br />\r\n4-stellig auf der Kartenvorderseite American Express', 'iPayment: Infofeld', NULL, NULL, 'de_DE', 'Frontend'),
(609, 17, 'sIPaymentCreditValidUntil', 'Karte gültig bis', 'iPayment: Karte gültig bis', NULL, NULL, 'de_DE', 'Frontend'),
(610, 17, 'sIPaymentComment', 'Kommentar', 'iPayment: Kommentar', NULL, NULL, 'de_DE', 'Frontend'),
(611, 17, 'sIPaymentProcessInfo', 'Die Bearbeitung kann einige Sekunden dauern. Bitte warten Sie, bis die Bestätigungsseite angezeigt wird.', 'iPayment: Die Bearbeitung kann einige ...', NULL, NULL, 'de_DE', 'Frontend'),
(612, 17, 'sPayPalAcceptAGB', 'Hiermit akzeptiere ich die Shop-AGB', 'PayPal: Hiermit akzeptiere ich die Shop-AGB', NULL, NULL, 'de_DE', 'Frontend'),
(613, 17, 'sPaypalexpressOrderAlreadySent', 'Die Bestellung wurde bereits abgeschickt<br /><a href="javascript:history.back;">zurück</a>', 'Bestellung wurde bereits abgeschickt', NULL, NULL, 'de_DE', 'Frontend'),
(614, 17, 'sSofortProceedToPayment', 'Weiter zur Zahlung', 'Sofortueberweisung: Weiter zur Zahlung', NULL, NULL, 'de_DE', 'Frontend'),
(615, 17, 'sSofortAcceptOurTerms', '<div class="error">Bitte akzeptieren Sie unsere AGB</div>', 'Sofortueberweisung: Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(616, 27, 'sMoneybookersHeadSuccess', 'Ihre Bestellung ist erfolgreich bei uns eingegangen.', 'Überschrift bei Erfolg', NULL, NULL, 'de_DE', 'Frontend'),
(617, 27, 'sMoneybookersSuccess', 'Die Zahlung war erfolgreich.', 'Text bei Erfolg', NULL, NULL, 'de_DE', 'Frontend'),
(618, 27, 'sMoneybookersHeadFail', 'Bei der Bestellung ist ein Fehler aufgetreten.', 'Überschrift bei Fehler', NULL, NULL, 'de_DE', 'Frontend'),
(619, 27, 'sMoneybookersFail', 'Die Bezahlung konnte nicht durchgeführt werden.', 'Text bei Fehler', NULL, NULL, 'de_DE', 'Frontend'),
(620, 27, 'sMoneybookersHeadIFrame', 'Ihre Bestellung ist erfolgreich eingetragen worden.', 'Überschrift bei IFrame', NULL, NULL, 'de_DE', 'Frontend'),
(621, 27, 'sMoneybookersIFrame', 'Bitte folgen Sie nun den Anweisungen im IFrame um die Bezahlung zu durchlaufen.', 'Text bei IFrame', NULL, NULL, 'de_DE', 'Frontend'),
(622, 27, 'sMoneybookersAGB', 'Hiermit akzeptiere ich die Shop-AGB', 'Shop-AGB akzeptieren', NULL, NULL, 'de_DE', 'Frontend'),
(623, 27, 'sMoneybookersGoOn', 'Weiter zur Zahlung', 'Weiter zur Zahlung', NULL, NULL, 'de_DE', 'Frontend'),
(624, 27, 'sMoneybookersAcceptAGB', 'Bitte akzeptieren Sie unsere AGB', 'Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(625, 27, 'sMoneybookersBack', 'zurück', 'zurück', NULL, NULL, 'de_DE', 'Frontend'),
(626, 27, 'sMoneybookersForward', 'weiter', 'weiter', NULL, NULL, 'de_DE', 'Frontend'),
(627, 16, 'sClickPayErrorAborted', '<h1>Es ist ein unbekannter Fehler aufgetreten und die Bestellung konnte nicht abgeschlossen werden.</h1>\r\n	    			<br />\r\n	    			<h3>Bitte kontaktieren Sie den Shopbetreiber.</h3>', 'ClickPay Fehler Abbruch', NULL, NULL, 'de_DE', 'Frontend'),
(628, 16, 'sClickPayUserAborted', '<h1>Sie haben den Bezahlungsprozess abgebrochen.</h1>', 'ClickPay Benutzer Abbruch', NULL, NULL, 'de_DE', 'Frontend'),
(629, 16, 'sClickPaySuccess', '<h1>Bezahlungsprozess wurde erfolgreich abgeschlossen!</h1>\r\n			Klicken Sie <a href="{$sSuccessURL}" target="_top">hier</a> um auf die Bestellabschlussseite zu kommen.', 'ClickPay erfolgreicher Bezahlungsprozess', NULL, NULL, 'de_DE', 'Frontend'),
(632, 28, 'sHeadSuccess', 'Ihre Bestellung ist erfolgreich bei uns eingegangen.', 'Überschrift bei Erfolg', NULL, NULL, 'de_DE', 'Frontend'),
(633, 28, 'sHanseaticSuccess', 'Die Finanzierung wurde genehmigt.', 'Text bei Erfolg Gruen', NULL, NULL, 'de_DE', 'Frontend'),
(634, 28, 'sHanseaticOK', 'Die Finanzierung muß von einem Mitarbeiter geprüft werden. Dieser wird Sie so schell wie möglich kontaktieren.', 'Text bei Erfolg Gelb', NULL, NULL, 'de_DE', 'Frontend'),
(635, 28, 'sHeadFail', 'Bei der Bestellung ist ein Fehler aufgetreten.', 'Überschrift bei Fehler', NULL, NULL, 'de_DE', 'Frontend'),
(636, 28, 'sHanseaticFail', 'Die Finanzierung konnte nicht durchgeführt werden.', 'Text bei Fehler Rot', NULL, NULL, 'de_DE', 'Frontend'),
(637, 28, 'sHeadIFrame', 'Ihre Bestellung ist erfolgreich eingetragen worden.', 'Überschrift bei IFrame', NULL, NULL, 'de_DE', 'Frontend'),
(638, 28, 'sHanseaticIFrame', 'Bitte folgen Sie nun den Anweisungen im IFrame um die Finanzierungsanfrage zu durchlaufen.', 'Text bei IFrame', NULL, NULL, 'de_DE', 'Frontend'),
(639, 28, 'sHanseaticMicroSite', 'Bitte folgen Sie nun den Anweisungen im Browser-Fenster um die Finanzierungsanfrage zu durchlaufen.', 'Text bei MicroSite Fenster', NULL, NULL, 'de_DE', 'Frontend'),
(640, 28, 'sHanseaticRateNotice', '* Die angezeigte Rate ist ein Richtwert und kann abweichen.', 'Hinweistext für Raten im Artikel', NULL, NULL, 'de_DE', 'Frontend'),
(641, 28, 'sArticlefinance', 'Finanzierung', 'Tab Titel', NULL, NULL, 'de_DE', 'Frontend'),
(642, 28, 'sArticleduration', 'Laufzeit', 'Laufzeit', NULL, NULL, 'de_DE', 'Frontend'),
(643, 28, 'sArticlemonth', 'Monate', 'Monate für Laufzeit', NULL, NULL, 'de_DE', 'Frontend'),
(644, 28, 'sHanseaticAGB', 'Hiermit akzeptiere ich die Shop-AGB', 'Shop-AGB akzeptieren', NULL, NULL, 'de_DE', 'Frontend'),
(645, 28, 'sHanseaticNoFinance', 'Für diesen Warenkorbwert ist keine Finanzierung möglich.', 'Betrag zu niedrig für Finanzierung', NULL, NULL, 'de_DE', 'Frontend'),
(646, 28, 'sHanseaticNoFinanceMax', 'Der Warenkorbwert ist zu hoch für eine Finanzierung.', 'Betrag zu hoch für Finanzierung', NULL, NULL, 'de_DE', 'Frontend'),
(647, 28, 'sHanseaticNoFinanceMin', 'Der Warenkorbwert ist zu niedrig für eine Finanzierung.', 'Betrag zu niedrig für Finanzierung', NULL, NULL, 'de_DE', 'Frontend'),
(648, 28, 'sHanseaticGoOn', 'Weiter zur Zahlung', 'Weiter zur Zahlung', NULL, NULL, 'de_DE', 'Frontend'),
(649, 28, 'sHanseaticAcceptAGB', 'Bitte akzeptieren Sie unsere AGB', 'Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(650, 28, 'sHanseaticBack', 'zurück', 'zurück', NULL, NULL, 'de_DE', 'Frontend'),
(651, 18, 'sDPRCheckbox', 'Hiermit akzeptiere ich die Datenschutz-Bestimmungen', 'Hiermit akzeptiere ich die Datenschutz-Bestimmungen', NULL, NULL, 'de_DE', 'Frontend'),
(652, 22, 'sOfflineText', '<b>Wegen Wartungsarbeiten nicht erreichbar</b><br>Aufgrund nötiger Wartungsarbeiten ist der Shop zur Zeit nicht erreichbar.', 'Wartungsarbeiten', NULL, NULL, 'de_DE', 'Frontend'),
(653, 10, 'sSaferpayTerms', 'Bitte akzeptieren Sie unsere AGB', 'Saferpay: Bitte akzeptieren Sie unsere AGB', NULL, NULL, 'de_DE', 'Frontend'),
(654, 10, 'sSaferpayInfo', 'Mit Klick auf diesen Button leiten wir Sie automatisch zu Saferpay weiter.<br />Dort können Sie Ihren Einkauf einfach und sicher bezahlen.<br />', 'Saferpay: Mit Klick auf diesen Button..', NULL, NULL, 'de_DE', 'Frontend'),
(655, 10, 'sSaferpayContinue', 'Weiter zur Bezahlung mit Saferpay', 'Saferpay: Weiter zur Bezahlung mit Saferpay', NULL, NULL, 'de_DE', 'Frontend'),
(656, 10, 'sSaferpayPaymentMeanError', 'Fehler: Bitte verwenden Sie Saferpay als Zahlungsart!', 'Saferpay: Fehler: Bitte verwenden Sie Saferpay als Zahlungsart!', NULL, NULL, 'de_DE', 'Frontend'),
(657, 10, 'sSaferpayTestsystemError', 'Saferpay Konfigurationsfehler:<br />Bitte überprüfen Sie die Saferpay Account-ID und die Saferpay "Testsystem nutzen" Einstellung!', 'Saferpay Konfigurationsfehler', NULL, NULL, 'de_DE', 'Frontend'),
(658, 10, 'sIPaymentSubmitButton', '<input type="submit" id="ccform_submit" name="ccform_submit" value="Bestellung abschliessen" style="background-color: #ffe0aa; border: 1px solid #ffa200; padding: 6px; color:#6c0d00; font-weight: bold; font-size: 12px;cursor:pointer;">', 'iPayment: Bestellung abschliessen', NULL, NULL, 'de_DE', 'Frontend'),
(659, 29, 'sHeidelpayHeadIFrame', 'Ihre Bestellung ist erfolgreich eingetragen worden.', 'Überschrift IFrameseite', NULL, NULL, 'de_DE', 'Frontend'),
(660, 29, 'sHeidelpayIFrame', 'Bitte folgen Sie nun den Anweisungen im IFrame um die Bezahlung zu durchlaufen.', 'Text IFrameseite', NULL, NULL, 'de_DE', 'Frontend'),
(661, 29, 'sHeidelpayAGB', 'Hiermit akzeptiere ich die Shop-AGB', 'AGB Text', NULL, NULL, 'de_DE', 'Frontend'),
(662, 29, 'sHeidelpayGoOn', 'Weiter zur Zahlung', 'Text für Weiter zur Zahlung Knopf', NULL, NULL, 'de_DE', 'Frontend'),
(663, 29, 'sHeidelpayAcceptAGB', 'Bitte akzeptieren Sie unsere AGB.', 'AGB akzeptieren Text', NULL, NULL, 'de_DE', 'Frontend'),
(664, 29, 'sHeidelpayBack', 'zurück', 'Text für Zurück Knopf', NULL, NULL, 'de_DE', 'Frontend'),
(665, 29, 'sHeidelpayForward', 'weiter', 'Text für Weiter Knopf', NULL, NULL, 'de_DE', 'Frontend'),
(666, 8, 'sAccountNetGrandTotal', 'Gesamtsumme Netto:', 'Gesamtsumme Netto', NULL, NULL, 'de_DE', 'Frontend'),
(667, 21, 'sSearchSearch', 'Suche:', 'Suche', NULL, NULL, 'de_DE', 'Frontend'),
(668, 18, 'sRegisterUsealreadyusedShippin', 'Vorherige Lieferadresse verwenden', 'Vorherige Lieferadresse verwenden', NULL, NULL, 'de_DE', 'Frontend'),
(669, 18, 'sRegisterUse', 'Verwenden', 'Verwenden', NULL, NULL, 'de_DE', 'Frontend'),
(670, 18, 'sRegisterUsealreadyusedAdress', 'Vorherige Rechnungsadresse verwenden', 'Vorherige Rechnungsadresse verwenden', NULL, NULL, 'de_DE', 'Frontend'),
(671, 23, 'sNewsletterRegisterHeadline', 'Newsletter', 'Newsletter', NULL, NULL, 'de_DE', 'Frontend'),
(672, 23, 'sTicketSysSupportOrder', 'Support beantragen', 'Support beantragen', NULL, NULL, 'de_DE', 'Frontend'),
(673, 9, 'sBasePriceArt', 'Grundpreis:', 'Grundpreis:', NULL, NULL, 'de_DE', 'Frontend'),
(674, 9, 'sContentPer', 'Inhalt:', 'Inhalt pro', NULL, NULL, 'de_DE', 'Frontend'),
(675, 9, 'sArticleButtonMore', 'mehr', 'mehr', NULL, NULL, 'de_DE', 'Frontend'),
(676, 9, 'sArticleButtonCompare', 'vergleichen', 'Vergleichen', NULL, NULL, 'de_DE', 'Frontend'),
(677, 8, 'sSupportoverview', 'Supportübersicht', 'Support Übersicht', NULL, NULL, 'de_DE', 'Frontend'),
(678, 8, 'sAccountOrderNo', 'Sie haben noch keine Bestellung durchgeführt.', 'Sie haben noch keine Bestellung durchgeführt.', NULL, NULL, 'de_DE', 'Frontend'),
(679, 8, 'sIndexsearchbutton', 'LOS!', 'Suchbutton', NULL, NULL, 'de_DE', 'Frontend'),
(680, 10, 'sBasketchoosepremium', 'Prämie auswählen', 'Prämie auswählen', NULL, NULL, 'de_DE', 'Frontend'),
(681, 10, 'sBasketstep4', '4', 'Step 4', NULL, NULL, 'de_DE', 'Frontend'),
(682, 10, 'sBasketstep3', '3', 'Step 3', NULL, NULL, 'de_DE', 'Frontend'),
(683, 10, 'sBasketstep2', '2', 'Step 2', NULL, NULL, 'de_DE', 'Frontend'),
(684, 10, 'sBasketstep1', '1', 'Step 1', NULL, NULL, 'de_DE', 'Frontend'),
(685, 10, 'sBasketstep4order', 'Bestellen', 'Step 4 - Bestellen', NULL, NULL, 'de_DE', 'Frontend'),
(686, 10, 'sBasketstep3payment', 'Zahlungsart', 'Step 3 - Zahlungsart', NULL, NULL, 'de_DE', 'Frontend'),
(687, 10, 'sBasketstep2adress', 'Adresse', 'Step 2 - Adresse', NULL, NULL, 'de_DE', 'Frontend'),
(688, 10, 'sBasketBundleDiscountText', 'BUNDLE RABATT', 'BUNDLE RABATT', NULL, NULL, 'de_DE', 'Frontend'),
(689, 10, 'sBundleHeadline', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln:', 'Kaufen Sie diesen Artikel...', NULL, NULL, 'de_DE', 'Frontend'),
(690, 10, 'sRelatedBundleHeadline', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln:', 'Kaufen Sie diesen Artikel...', NULL, NULL, 'de_DE', 'Frontend'),
(691, 10, 'sBundleDiscountPrefix', 'Statt', 'Statt', NULL, NULL, 'de_DE', 'Frontend'),
(692, 10, 'sBundleDiscountPostfix', '% Rabatt', '% Rabatt', NULL, NULL, 'de_DE', 'Frontend'),
(693, 10, 'sBundlePriceForAll', 'Preis für alle:', 'Preis für alle:', NULL, NULL, 'de_DE', 'Frontend'),
(694, 9, 'sRegisterForNotification', 'Benachrichtigen Sie mich, wenn der Artikel lieferbar ist.', 'Benachrichtigen Sie mich, wenn der Artikel wieder vorhanden ist.', NULL, NULL, 'de_DE', 'Frontend'),
(695, 9, 'sRegisterForNotificationValid', 'Vielen Dank!\r\n\r\nWir haben Ihre Anfrage gespeichert!\r\nSie werden benachrichtigt sobald der Artikel wieder verfügbar ist.\r\n\r\n', 'Sie werden Benachrichtigt sobald der Artikel wieder verfügbar ist.', NULL, NULL, 'de_DE', 'Frontend'),
(696, 9, 'sRegisterNotificationInValid', 'Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.', 'Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.', NULL, NULL, 'de_DE', 'Frontend'),
(697, 9, 'sArticleNotificationSend', 'Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist', 'Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist', NULL, NULL, 'de_DE', 'Frontend'),
(698, 9, 'sAlreadyForArticleRegistered', 'Für diesen Artikeln haben Sie sich bereits für eine Benachrichtigung registriert.', 'Für diesen Artikeln haben Sie sich bereits für eine Benachrichtigung registriert.', NULL, NULL, 'de_DE', 'Frontend'),
(699, 18, 'sErrorEmailNotEqual', 'Die eMail-Adressen stimmen nicht überein.', 'Die eMail-Adressen stimmen nicht überein.', NULL, NULL, 'de_DE', 'Frontend'),
(700, 18, 'sRegisteryouremailconfirmation', 'Wiederholen Sie Ihre eMail-Adresse*:', 'Wiederholen Sie Ihre eMail-Adresse*:', NULL, NULL, 'de_DE', 'Frontend'),
(701, 18, 'sVatCheckErrorEmpty', 'Bitte geben Sie eine USt-IdNr. an.', '', NULL, NULL, 'de_DE', 'Frontend'),
(702, 18, 'sVatCheckErrorDate', 'Die eingegebene USt-IdNr. ist ungültig. Sie ist erst ab dem %s gültig.', '', NULL, NULL, 'de_DE', 'Frontend'),
(703, 18, 'sVatCheckErrorInvalid', 'Die eingegebene USt-IdNr. ist ungültig.', '', NULL, NULL, 'de_DE', 'Frontend'),
(704, 18, 'sVatCheckErrorField', 'Das Feld %s passt nicht zur USt-IdNr.', '', NULL, NULL, 'de_DE', 'Frontend'),
(705, 18, 'sVatCheckUnknownError', 'Es ist ein unerwarteter Fehler bei der Überprüfung der USt-IdNr. aufgetreten. Bitte kontaktieren Sie den Shopbetreiber. Fehlercode: %d', '', NULL, NULL, 'de_DE', 'Frontend'),
(706, 18, 'sVatCheckErrorInfo', 'Bitte passen Sie die Rechnungsadresse/USt-IdNr. an oder lassen Sie das Feld für die USt-IdNr. leer.', '', NULL, NULL, 'de_DE', 'Frontend'),
(707, 18, 'sVatCheckErrorFields', 'Firma,Ort,PLZ,Straße,Land', '', NULL, NULL, 'de_DE', 'Frontend'),
(708, 30, 'sBlogComments', 'Kommentare', 'Kommentare', NULL, NULL, 'de_DE', 'Frontend'),
(709, 30, 'sBlogReadMore', 'Mehr lesen', 'Mehr lesen', NULL, NULL, 'de_DE', 'Frontend'),
(710, 30, 'sBlogOlderArticles', 'Zurück', 'Zurück', NULL, NULL, 'de_DE', 'Frontend'),
(711, 30, 'sBlogNewerArticles', 'Weiter', 'Weiter', NULL, NULL, 'de_DE', 'Frontend'),
(712, 30, 'sBlogtoOverview', 'Zur &Uuml;bersicht', 'Zur &Uuml;bersicht', NULL, NULL, 'de_DE', 'Frontend'),
(713, 30, 'sBlogCategoryAssignment', 'Kategoriezuordnung', 'Kategoriezuordnung', NULL, NULL, 'de_DE', 'Frontend'),
(714, 30, 'sBlogToComments', 'Zu den Kommentaren des Artikels', 'Zu den Kommentaren des Artikels', NULL, NULL, 'de_DE', 'Frontend'),
(715, 30, 'sBlogTags', 'Tags', 'Tags', NULL, NULL, 'de_DE', 'Frontend'),
(716, 30, 'sBlogShowAllAuthors', 'Alle Autoren anzeigen', 'Alle Autoren anzeigen', NULL, NULL, 'de_DE', 'Frontend'),
(717, 30, 'sBlogAuthors', 'Autoren', 'Autoren', NULL, NULL, 'de_DE', 'Frontend'),
(718, 30, 'sBlogDate', 'Datum', 'Datum', NULL, NULL, 'de_DE', 'Frontend'),
(719, 30, 'sBlogRSS', 'RSS-Feed', 'RSS-Feed', NULL, NULL, 'de_DE', 'Frontend'),
(720, 30, 'sBlogAtom', 'Atom-Feed', 'Atom-Feed', NULL, NULL, 'de_DE', 'Frontend'),
(721, 30, 'sBlogCategories', 'Kategorien', 'Kategorien', NULL, NULL, 'de_DE', 'Frontend'),
(722, 30, 'sBlogNewInTheBlog', 'Neu in unserem Blog', 'Neu in unserem Blog', NULL, NULL, 'de_DE', 'Frontend'),
(723, 11, 'sCategoryFilterTo', 'Filtern nach', 'Filtern nach', NULL, NULL, 'de_DE', 'Frontend'),
(724, 8, 'sAccountRepeatOrder', 'Bestellung wiederholen', 'Bestellung wiederholen', NULL, NULL, 'de_DE', 'Frontend'),
(725, 20, 'sCompareClose', 'Schlie&szlig;en', 'Schließen', NULL, NULL, 'de_DE', 'Frontend'),
(726, 9, 'sArticlesBundleSaveMoney', 'Sparen Sie jetzt mit unseren Bundle-Angeboten', 'Sparen Sie jetzt mit unseren Bundle-Angeboten', NULL, NULL, 'de_DE', 'Frontend'),
(727, 9, 'sArticlesBundlePricesForAll', 'Preis für alle', 'Preis für alle', NULL, NULL, 'de_DE', 'Frontend'),
(728, 9, 'sArticlesBundleInstead', 'Statt', 'Statt', NULL, NULL, 'de_DE', 'Frontend'),
(729, 9, 'sArticlesBundleBuy', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln', NULL, NULL, 'de_DE', 'Frontend'),
(730, 9, 'sArticlesLiveshoppingAuctionEnd', 'Aktionsende', 'Aktionsende', NULL, NULL, 'de_DE', 'Frontend'),
(731, 9, 'sArticlesLiveshoppingStartPrice', 'Startpreis', 'Startpreis', NULL, NULL, 'de_DE', 'Frontend'),
(732, 9, 'sArticlesLiveshoppingActualPrice', 'Aktueller Preis', 'Aktueller Preis', NULL, NULL, 'de_DE', 'Frontend'),
(733, 9, 'sArticlesLiveshoppingHours', 'Stunden', 'Stunden', NULL, NULL, 'de_DE', 'Frontend'),
(734, 9, 'sArticlesLiveshoppingMinutes', 'Minuten', 'Minuten', NULL, NULL, 'de_DE', 'Frontend'),
(735, 9, 'sArticlesLiveshoppingSeconds', 'Sekunden', 'Sekunden', NULL, NULL, 'de_DE', 'Frontend'),
(736, 9, 'sArticlesLiveshoppingJust', 'Noch', 'Noch', NULL, NULL, 'de_DE', 'Frontend'),
(737, 9, 'sArticlesLiveshoppingPiece', 'Stück', 'Stück', NULL, NULL, 'de_DE', 'Frontend'),
(738, 9, 'sArticleLiveshoppingOfferEndsIn', 'Angebot endet in', 'Angebot endet in', NULL, NULL, 'de_DE', 'Frontend'),
(739, 9, 'sArticlesLiveshoppingPriceFalling', 'Preis fällt um', 'Preis fällt um', NULL, NULL, 'de_DE', 'Frontend'),
(740, 9, 'sArticlesLiveshoppingPriceRising', 'Preis steigt um', 'Preis steigt um', NULL, NULL, 'de_DE', 'Frontend'),
(741, 9, 'sArticlesLiveshoppingPriceFallingPerMinute', 'Preis sinkt im Minutentakt um', 'Preis sinkt im Minutentakt um', NULL, NULL, 'de_DE', 'Frontend'),
(742, 9, 'sArticlesLiveshoppingPriceRisingPerMinute', 'Preis steigt im Minutentakt um', 'Preis steigt im Minutentakt um', NULL, NULL, 'de_DE', 'Frontend'),
(743, 9, 'sArticlesLiveshoppingSpecialOfferTill', 'Sonderangebot nur noch bis zum:', 'Sonderangebot nur noch bis zum:', NULL, NULL, 'de_DE', 'Frontend'),
(744, 9, 'sArticleNotificationEmail', 'E-Mail', 'E-Mail', NULL, NULL, 'de_DE', 'Frontend'),
(745, 9, 'sArticlesNotificationSignIn', 'Eintragen', 'Eintragen', NULL, NULL, 'de_DE', 'Frontend'),
(746, 9, 'sArticlesShippingTill', 'Lieferung bis', 'Lieferung bis', NULL, NULL, 'de_DE', 'Frontend'),
(747, 9, 'sArticlesOrderInNext', 'Bestellen Sie in den  \r\nnächsten', 'Bestellen Sie in den   nächsten', NULL, NULL, 'de_DE', 'Frontend'),
(748, 9, 'sArticlesOrderInNextHours', 'Stunden und', 'Stunden und', NULL, NULL, 'de_DE', 'Frontend'),
(749, 9, 'sArticlesOrderInAndChoose', 'und wählen Sie', 'und wählen Sie', NULL, NULL, 'de_DE', 'Frontend'),
(750, 9, 'sArticlesOrderOvernight', 'Overnight-Express', 'Overnight-Express', NULL, NULL, 'de_DE', 'Frontend'),
(751, 9, 'sArticlesOrderToCashDesk', 'an der Kasse', 'an der Kasse', NULL, NULL, 'de_DE', 'Frontend'),
(752, 13, 'sNewsletterNewWindow', 'Newsletter in neuem Fenster öffnen', 'Newsletter in neuem Fenster öffnen', NULL, NULL, 'de_DE', 'Frontend'),
(754, 9, 'sArticlesLiveshoppingOriginallyPrice', 'Urspr?nglicher Preis:', 'Urspr?nglicher Preis:', NULL, NULL, 'de_DE', 'Frontend'),
(755, 9, 'sArticlesLiveshoppingYouSave', 'Sie sparen:', 'Sie sparen:', NULL, NULL, 'de_DE', 'Frontend'),
(756, 0, 'sBasketZoomPicture', 'Bild zoomen', '', '2010-07-21 10:59:10', '2010-07-21 10:59:10', 'de_DE', 'Frontend'),
(757, 0, 'sAjaxBasketYourBasket', 'Ihr Warenkorb', '', '2010-07-23 12:17:40', '2010-07-23 12:17:40', 'de_DE', 'Frontend');
INSERT INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`, `created`, `modified`, `locale`, `namespace`) VALUES
(758, 8, 'sNotificationLabel', 'Ihre E-Mail Adresse', 'Ihre E-Mail Adresse', '2010-08-04 17:15:03', '2010-08-04 17:15:03', 'de_DE', 'Frontend'),
(759, 8, 'sIndexOpenMyBasket', 'Warenkorb öffnen', 'Warenkorb öffnen', '2010-07-23 12:39:16', '2010-07-23 12:39:16', 'de_DE', 'Frontend'),
(760, 0, 'sAjaxBasketIsEmpty', 'Ihr Warenkorb ist leer', '', '2010-07-26 06:05:55', '2010-07-26 06:05:55', 'de_DE', 'Frontend'),
(761, 0, 'sAccountWelcome', 'Willkommen', '', '2010-07-26 09:00:26', '2010-07-26 09:00:26', 'de_DE', 'Frontend'),
(3834, 0, 'sAccountTeaserText', 'Dies ist Ihr Konto Dashboard, wo Sie die Möglichkeit haben, Ihre letzten Kontoaktivitäten einzusehen', '', '2010-08-17 14:19:38', '2010-08-17 14:19:38', 'de_DE', 'Frontend'),
(763, 0, 'sAccountRecentOrders', 'Ihre letzten Bestellungen', '', '2010-07-26 09:18:00', '2010-07-26 09:18:00', 'de_DE', 'Frontend'),
(764, 0, 'sAccountMyWishlist', 'Ihr Merkzettel', '', '2010-07-26 09:18:35', '2010-07-26 09:18:35', 'de_DE', 'Frontend'),
(765, 0, 'sAccountAddressbook', 'Adressbuch', '', '2010-07-28 11:07:06', '2010-07-28 11:07:06', 'de_DE', 'Frontend'),
(766, 0, 'sPrimaryBillingAddress', 'Primäre Rechnungsadresse', '', '2010-07-28 11:09:47', '2010-07-28 11:09:47', 'de_DE', 'Frontend'),
(767, 0, 'sPrimaryShippingAddress', 'Primäre Lieferadresse', '', '2010-07-28 11:11:05', '2010-07-28 11:11:05', 'de_DE', 'Frontend'),
(768, 0, 'sChangePassword', 'Passwort ändern', '', '2010-07-28 12:07:59', '2010-07-28 12:07:59', 'de_DE', 'Frontend'),
(769, 8, 'sRegisterPersonalSettings', 'Ihre persönlichen Angaben', 'Ihre persönlichen Angaben', '2010-07-30 12:58:21', '2010-07-30 12:58:21', 'de_DE', 'Frontend'),
(770, 8, 'sRegisterIamBuisness', 'Firma', 'Firma', '2010-07-30 11:11:37', '2010-07-30 11:11:37', 'de_DE', 'Frontend'),
(771, 0, 'sRegisterAddress', 'Ihre Adresse', '', '2010-07-30 13:19:39', '2010-07-30 13:19:39', 'de_DE', 'Frontend'),
(772, 0, 'sRegisterDifferentShippingAddress', 'Die <strong>Lieferadresse</strong> weicht von der Rechnungsadresse ab.', '', '2010-07-30 13:21:23', '2010-07-30 13:21:23', 'de_DE', 'Frontend'),
(773, 0, 'sRegisterCompanyInformations', 'Ihre Firmenangaben', '', '2010-07-30 13:24:55', '2010-07-30 13:24:55', 'de_DE', 'Frontend'),
(774, 0, 'sRegisterAlternativeShippingAddress', 'Ihre abweichende Lieferadresse', '', '2010-07-30 13:49:37', '2010-07-30 13:49:37', 'de_DE', 'Frontend'),
(775, 0, 'sSaleBillingAddress', 'Rechnungsadresse', '', '2010-08-02 11:23:16', '2010-08-02 11:23:16', 'de_DE', 'Frontend'),
(776, 0, 'sSaleShippingAddress', 'Lieferadresse', '', '2010-08-02 13:53:43', '2010-08-02 13:53:43', 'de_DE', 'Frontend'),
(777, 0, 'sBasketRegister', 'Registrierung', '', '2010-08-03 09:02:24', '2010-08-03 09:02:24', 'de_DE', 'Frontend'),
(779, 0, 'sBasketstep4finished', 'Bestellung abgeschlossen', '', '2010-08-03 09:03:09', '2010-08-03 09:03:09', 'de_DE', 'Frontend'),
(780, 0, 'sListingSupplierFilterArticlesFrom', 'Produkte von', '', '2010-08-04 15:33:02', '2010-08-04 15:33:02', 'de_DE', 'Frontend'),
(781, 0, 'ShippingCalulation', 'Versandkostenberechnung', '', '2010-08-04 18:59:46', '2010-08-04 18:59:46', 'de_DE', 'Frontend'),
(782, 0, 'vouchercode', 'Gutschein-Code', '', '2010-08-04 20:34:38', '2010-08-04 20:34:38', 'de_DE', 'Frontend'),
(784, 0, 'sRegisterIam', 'Ich bin', '', '2010-08-04 21:33:27', '2010-08-04 21:33:27', 'de_DE', 'Frontend'),
(786, 0, 'sRegisterIamPrivate', 'Privatkunde', '', '2010-08-04 21:33:27', '2010-08-04 21:33:27', 'de_DE', 'Frontend'),
(788, 0, 'sArticlerecommendandvoucher', 'sArticlerecommendandvoucher', '', '2010-08-04 22:32:53', '2010-08-04 22:32:53', 'de_DE', 'Frontend'),
(802, 0, 'NotesSupplierFrom', 'Von', '', '2010-08-06 11:29:54', '2010-08-06 11:29:54', 'de_DE', 'Frontend'),
(814, 0, 'sNotesPlaceInBasket', 'Jetzt bestellen', '', '2010-08-05 08:59:51', '2010-08-05 08:59:51', 'de_DE', 'Frontend'),
(816, 0, 'sNoteCompareArticle', 'Artikel vergleichen', '', '2010-08-05 08:59:51', '2010-08-05 08:59:51', 'de_DE', 'Frontend'),
(818, 0, 'sNotesArticleDetails', 'Details aufrufen', '', '2010-08-05 08:59:51', '2010-08-05 08:59:51', 'de_DE', 'Frontend'),
(820, 0, 'sBasketRemoveArticle', 'Artikel entfernen', '', '2010-08-05 08:59:51', '2010-08-05 08:59:51', 'de_DE', 'Frontend'),
(810, 0, 'ChangeBasket', 'Warenkorb ändern', '', '2010-08-06 11:49:53', '2010-08-06 11:49:53', 'de_DE', 'Frontend'),
(862, 0, 'ListingBuyNow', 'Jetzt bestellen', '', '2010-08-06 06:59:36', '2010-08-06 06:59:36', 'de_DE', 'Frontend'),
(864, 0, 'BasketShowPositions', 'Positionen anzeigen', '', '2010-08-06 07:42:09', '2010-08-06 07:42:09', 'de_DE', 'Frontend'),
(868, 0, 'Filter', 'Filter', '', '2010-08-06 08:33:52', '2010-08-06 08:33:52', 'de_DE', 'Frontend'),
(870, 0, 'RegisterConsideruUpper', 'RegisterConsideruUpper', '', '2010-08-06 08:35:01', '2010-08-06 08:35:01', 'de_DE', 'Frontend'),
(872, 0, 'NewsletterGetMoreInformations', 'Möchten Sie mehr Informationen?', '', '2010-08-06 11:49:53', '2010-08-06 11:49:53', 'de_DE', 'Frontend'),
(874, 0, 'FinishingOrder', 'Bestellung abschließen', '', '2010-08-06 11:49:53', '2010-08-06 11:49:53', 'de_DE', 'Frontend'),
(876, 0, 'AccountNoDownloads', 'Sie haben noch keine Sofortdownloadartikel gekauft', '', '2010-08-09 06:05:11', '2010-08-09 06:05:11', 'de_DE', 'Frontend'),
(878, 0, 'AccountDate', 'Datum', '', '2010-08-09 07:30:52', '2010-08-09 07:30:52', 'de_DE', 'Frontend'),
(880, 0, 'AccountDownloadLink', 'Downloadlink', '', '2010-08-09 07:30:52', '2010-08-09 07:30:52', 'de_DE', 'Frontend'),
(882, 0, 'Ordernumber', 'Bestellnummer', '', '2010-08-09 08:02:52', '2010-08-09 08:02:52', 'de_DE', 'Frontend'),
(884, 0, 'OrderStatus', 'Bestellstatus', '', '2010-08-09 08:05:51', '2010-08-09 08:05:51', 'de_DE', 'Frontend'),
(886, 0, 'OrderActions', 'Aktionen', '', '2010-08-09 08:16:26', '2010-08-09 08:16:26', 'de_DE', 'Frontend'),
(888, 0, 'ArticleYournNme', 'ArticleYournNme', '', '2010-08-09 08:18:48', '2010-08-09 08:18:48', 'de_DE', 'Frontend'),
(896, 0, 'AccountDetails', 'Details', '', '2010-08-09 08:23:12', '2010-08-09 08:23:12', 'de_DE', 'Frontend'),
(904, 0, 'DetailsForOrder', 'Details zur Bestellung', '', '2010-08-09 08:27:11', '2010-08-09 08:27:11', 'de_DE', 'Frontend'),
(906, 0, 'FilterView', 'Ansicht', '', '2010-08-09 12:21:03', '2010-08-09 12:21:03', 'de_DE', 'Frontend'),
(908, 0, 'Listing1col', 'Liste', '', '2010-08-09 12:21:03', '2010-08-09 12:21:03', 'de_DE', 'Frontend'),
(910, 0, 'Listing2col', 'Zweispaltig', '', '2010-08-09 12:21:03', '2010-08-09 12:21:03', 'de_DE', 'Frontend'),
(912, 0, 'Listing3col', 'Dreispaltig', '', '2010-08-09 12:21:03', '2010-08-09 12:21:03', 'de_DE', 'Frontend'),
(914, 0, 'Listing4col', 'Vierspaltig', '', '2010-08-09 12:21:03', '2010-08-09 12:21:03', 'de_DE', 'Frontend'),
(924, 0, 'AccountSlideOut', 'Anzeigen/Verstecken', '', '2010-08-09 12:57:19', '2010-08-09 12:57:19', 'de_DE', 'Frontend'),
(1026, 0, 'CheckoutCartDeleteItem', 'Shave 3 Rotation aus den Warenkorb löschen', '', '2010-08-13 14:11:50', '2010-08-13 14:11:50', 'de_DE', 'Frontend'),
(3838, 0, 'IndexCheckout', 'Kasse', '', '2010-08-17 14:37:03', '2010-08-17 14:37:03', 'de_DE', 'Frontend'),
(1176, 0, 'SitemapHeading', 'Sitemap - Alle Kategorien auf einen Blick', '', '2010-08-13 13:51:55', '2010-08-13 13:51:55', 'de_DE', 'Frontend'),
(2865, 0, 'AccountChangeBilling', 'Ändern', '', '2010-08-14 11:31:26', '2010-08-14 11:31:26', 'de_DE', 'Frontend'),
(2863, 0, 'AccountChangeShipping', 'Ändern', '', '2010-08-14 11:31:01', '2010-08-14 11:31:01', 'de_DE', 'Frontend'),
(1570, 0, 'AccountAjaxLoginCloseWindow', 'Fenster schließen', '', '2010-08-12 12:53:31', '2010-08-12 12:53:31', 'de_DE', 'Frontend'),
(1760, 0, 'AccountAjaxLoginPassword', '', '', '2010-08-13 11:49:58', '2010-08-13 11:49:58', 'de_DE', 'Frontend'),
(1828, 0, 'AccountDownloadAccessDenied', 'Dieser Download stehen Ihnen nicht zur Verfügung!', '', '2010-08-13 11:19:45', '2010-08-13 11:19:45', 'de_DE', 'Frontend'),
(1852, 0, 'SearchSearcResult', '', '', '2010-08-13 11:15:45', '2010-08-13 11:15:45', 'de_DE', 'Frontend'),
(1873, 0, 'RegisterBirthday', 'Geburtsdatum:', '', '2010-08-13 11:14:58', '2010-08-13 11:14:58', 'de_DE', 'Frontend'),
(1886, 0, 'BasketAddVoucher', 'Gutschein hinzufügen:', '', '2010-08-13 10:16:30', '2010-08-13 10:16:30', 'de_DE', 'Frontend'),
(1900, 0, 'BasketVoucherCode', 'Gutschein-Code', '', '2010-08-13 10:18:32', '2010-08-13 10:18:32', 'de_DE', 'Frontend'),
(1936, 0, 'CheckoutChangePaymentMethod', 'Ändern', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1940, 0, 'CheckoutSelectShippingAddress', 'Auswählen', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1943, 0, 'CheckoutChangeShippingAddress', 'Ändern', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1946, 0, 'CheckoutSelectBillingAddress', 'Auswählen', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1949, 0, 'CheckoutChangeBillingAddress', 'Ändern', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1954, 0, 'IndexSearch', '', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1957, 0, 'IndexMetaDescriptionStandard', '', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1960, 0, 'IndexMetaKeywordsStandard', '', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1963, 0, 'IndexMetaCopyright', '', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(1965, 0, 'IndexMetaAuthor', '', '', '2010-08-13 10:51:36', '2010-08-13 10:51:36', 'de_DE', 'Frontend'),
(2866, 0, 'AccountSelectBilling', 'Auswählen', '', '2010-08-14 11:31:26', '2010-08-14 11:31:26', 'de_DE', 'Frontend'),
(1982, 0, 'AccountSelectBillingTest', 'Test', '', '2010-08-13 10:48:04', '2010-08-13 10:48:04', 'de_DE', 'Frontend'),
(2864, 0, 'AccountSelectShipping', 'Auswählen', '', '2010-08-14 11:31:01', '2010-08-14 11:31:01', 'de_DE', 'Frontend'),
(2029, 0, 'ArticleEnterTheNumber', '', '', '2010-08-13 14:21:16', '2010-08-13 14:21:16', 'de_DE', 'Frontend'),
(2519, 0, 'CheckoutCartShippingFreeShipping', 'VERSANDKOSTENFREI', '', '2010-08-13 17:53:47', '2010-08-13 17:53:47', 'de_DE', 'Frontend'),
(2520, 0, 'CheckoutCartShippingFreeDifference', '- Bestellen Sie für weitere 380,00 &euro; um Ihre Bestellung versandkostenfrei zu erhalten!', '', '2010-08-13 17:53:47', '2010-08-13 17:53:47', 'de_DE', 'Frontend'),
(2867, 0, 'AccontChangePayment', 'Zahlungsart ändern', '', '2010-08-14 11:36:08', '2010-08-14 11:36:08', 'de_DE', 'Frontend'),
(2868, 0, 'GlobalLogout', 'Abmelden', '', '2010-08-14 11:50:17', '2010-08-14 11:50:17', 'de_DE', 'Frontend'),
(2871, 0, 'GlobalLogin', 'Anmelden', '', '2010-08-14 11:54:25', '2010-08-14 11:54:25', 'de_DE', 'Frontend'),
(2880, 0, 'SelectShippingAddress', 'Wählen Sie Ihre bevorzugte Lieferadresse', '', '2010-08-14 12:25:10', '2010-08-14 12:25:10', 'de_DE', 'Frontend'),
(2881, 0, 'SelectBillingAddress', 'Wählen Sie Ihre bevorzugte Rechnungsadresse', '', '2010-08-14 12:27:54', '2010-08-14 12:27:54', 'de_DE', 'Frontend'),
(2882, 0, 'NoGivenDispatchName', 'Nicht angegeben', '', '2010-08-14 12:59:43', '2010-08-14 12:59:43', 'de_DE', 'Frontend'),
(2888, 0, 'ListingSelectSort', 'Sortierung:', '', '2010-08-14 13:31:37', '2010-08-14 13:31:37', 'de_DE', 'Frontend'),
(2889, 0, 'ListingSelectPerPage', 'Artikel pro Seite:', '', '2010-08-14 13:31:37', '2010-08-14 13:31:37', 'de_DE', 'Frontend'),
(2890, 0, 'ListingSelectView', 'Ansicht:', '', '2010-08-14 13:31:37', '2010-08-14 13:31:37', 'de_DE', 'Frontend'),
(3036, 0, 'AccountLoginTitle', 'Login', '', '2010-08-14 16:08:23', '2010-08-14 16:08:23', 'de_DE', 'Frontend'),
(3041, 0, 'AccountTitle', 'Ihr Kundenkonto', '', '2010-08-14 16:11:47', '2010-08-14 16:11:47', 'de_DE', 'Frontend'),
(3042, 0, 'AccountChangePayment', 'Ändern', '', '2010-08-14 16:11:47', '2010-08-14 16:11:47', 'de_DE', 'Frontend'),
(3048, 0, 'AccountLogoutTitle', 'Logout', '', '2010-08-14 16:38:57', '2010-08-14 16:38:57', 'de_DE', 'Frontend'),
(3286, 0, 'CheckoutStepBasketNumber', '1', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3287, 0, 'CheckoutStepBasketText', 'Warenkorb', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3288, 0, 'CheckoutStepRegisterNumber', '2', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3289, 0, 'CheckoutStepRegisterText', 'Registrierung', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3290, 0, 'CheckoutStepConfirmNumber', '3', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3291, 0, 'CheckoutStepConfirmText', 'Bestellung abschließen', '', '2010-08-16 10:15:17', '2010-08-16 10:15:17', 'de_DE', 'Frontend'),
(3390, 0, 'CheckoutShippingCostsTitle', 'Versandkostenberechnung', '', '2010-08-16 11:44:07', '2010-08-16 11:44:07', 'de_DE', 'Frontend'),
(3391, 0, 'CheckoutShippingCostsDeliveryCountry', '1. Lieferland:', '', '2010-08-16 11:44:07', '2010-08-16 11:44:07', 'de_DE', 'Frontend'),
(3392, 0, 'CheckoutShippingCostsPayment', '2. Zahlungsart:', '', '2010-08-16 11:44:07', '2010-08-16 11:44:07', 'de_DE', 'Frontend'),
(3393, 0, 'CheckoutShippingCostsDispatch', '3. Versandart:', '', '2010-08-16 11:44:07', '2010-08-16 11:44:07', 'de_DE', 'Frontend'),
(3394, 0, 'CheckoutShippingCostsButton', 'Versandkosten berechnen', '', '2010-08-16 11:44:07', '2010-08-16 11:44:07', 'de_DE', 'Frontend'),
(3440, 0, 'BlogRssFeedTitle', 'Blog / RSS Feed', '', '2010-08-16 13:34:08', '2010-08-16 13:34:08', 'de_DE', 'Frontend'),
(3441, 0, 'BlogAtomFeedTitle', 'Blog / Atom Feed', '', '2010-08-16 13:34:14', '2010-08-16 13:34:14', 'de_DE', 'Frontend'),
(3450, 0, 'AccountAjaxLoginTitle', 'Eine Online-Bestellung ist einfach', '', '2010-08-16 14:08:01', '2010-08-16 14:08:01', 'de_DE', 'Frontend'),
(3454, 0, 'CheckoutAjaxCartTitle', 'Ihr Warenkorb:', '', '2010-08-16 14:19:58', '2010-08-16 14:19:58', 'de_DE', 'Frontend'),
(3524, 0, 'CheckoutAjaxCartIsEmpty', 'Ihr Warenkorb ist leer', '', '2010-08-16 14:39:07', '2010-08-16 14:39:07', 'de_DE', 'Frontend'),
(3762, 0, 'LoginLostPassword', 'Haben Sie Ihr Passwort vergessen? Hier klicken', '', '2010-08-17 09:24:00', '2010-08-17 09:24:00', 'de_DE', 'Frontend'),
(3763, 0, 'AreYouAlreadyHaveAPassword', 'Haben Sie bereits ein Passwort bei', '', '2010-08-17 09:37:46', '2010-08-17 09:37:46', 'de_DE', 'Frontend'),
(3764, 0, 'NewCustomer', 'Ich bin ein neuer Kunde', '', '2010-08-17 09:39:30', '2010-08-17 09:39:30', 'de_DE', 'Frontend'),
(3765, 0, 'ExistingCustomer', 'Ich bin bereits Kunde und mein Passwort lautet', '', '2010-08-17 09:39:30', '2010-08-17 09:39:30', 'de_DE', 'Frontend'),
(3835, 0, 'SuccessfullyLoggedOut', 'Sie wurden erfolgreich ausgeloggt!', '', '2010-08-17 14:19:49', '2010-08-17 14:19:49', 'de_DE', 'Frontend'),
(3789, 0, 'ThisArticle', 'Der Artikel', '', '2010-08-17 10:41:27', '2010-08-17 10:41:27', 'de_DE', 'Frontend'),
(3791, 0, 'AreSuccessfullyPlayedInBasket', 'wurde erfolgreich in den Warenkorb gelegt', '', '2010-08-17 10:42:13', '2010-08-17 10:42:13', 'de_DE', 'Frontend'),
(3796, 0, 'YouCouldLikeThisArticles', 'Diese Artikel könnten Ihnen gefallen', '', '2010-08-17 10:44:01', '2010-08-17 10:44:01', 'de_DE', 'Frontend'),
(3836, 0, 'Register', 'Registierung', '', '2010-08-17 14:21:14', '2010-08-17 14:21:14', 'de_DE', 'Frontend'),
(3825, 0, 'AccountLoginLostPassword', '', '', '2010-08-17 13:26:04', '2010-08-17 13:26:04', 'de_DE', 'Frontend');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_config_text_groups`
--

CREATE TABLE IF NOT EXISTS `s_core_config_text_groups` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `groupID` int(10) NOT NULL,
  `description` varchar(250) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `description` (`description`),
  UNIQUE KEY `groupID` (`groupID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=31 ;

--
-- Daten für Tabelle `s_core_config_text_groups`
--

INSERT INTO `s_core_config_text_groups` (`id`, `groupID`, `description`) VALUES
(1, 8, 'account'),
(2, 9, 'articles'),
(3, 10, 'basket'),
(4, 11, 'category'),
(5, 12, 'content'),
(6, 13, 'custom'),
(7, 14, 'error'),
(8, 15, 'login'),
(9, 16, 'orderprocess'),
(10, 17, 'payment'),
(11, 18, 'register'),
(12, 19, 'support'),
(13, 20, 'ajax'),
(14, 21, 'search'),
(15, 22, 'index'),
(23, 23, 'sonstige'),
(24, 24, 'paypalexpress'),
(25, 25, 'ticketsystem'),
(26, 26, 'campaigns'),
(27, 27, 'moneybookers'),
(28, 28, 'hanseatic'),
(29, 29, 'heidelpay'),
(30, 30, 'blog');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_countries`
--

CREATE TABLE IF NOT EXISTS `s_core_countries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `countryname` varchar(255) NOT NULL,
  `countryiso` varchar(255) NOT NULL,
  `countryarea` varchar(35) NOT NULL,
  `countryen` varchar(70) NOT NULL,
  `position` int(1) NOT NULL,
  `notice` text NOT NULL,
  `shippingfree` double NOT NULL,
  `taxfree` int(1) NOT NULL,
  `taxfree_ustid` int(1) NOT NULL,
  `taxfree_ustid_checked` int(1) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '1',
  `iso3` char(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

--
-- Daten für Tabelle `s_core_countries`
--

INSERT INTO `s_core_countries` (`id`, `countryname`, `countryiso`, `countryarea`, `countryen`, `position`, `notice`, `shippingfree`, `taxfree`, `taxfree_ustid`, `taxfree_ustid_checked`, `active`, `iso3`) VALUES
(2, 'Deutschland', 'DE', 'deutschland', 'GERMANY', 1, '', 0, 0, 0, 0, 1, 'DEU'),
(3, 'Arabische Emirate', 'AE', 'welt', 'ARAB EMIRATES', 10, '', 0, 0, 0, 0, 0, 'ARE'),
(4, 'Australien', 'AU', 'welt', 'AUSTRALIA', 10, '', 0, 0, 0, 0, 0, 'AUS'),
(5, 'Belgien', 'BE', 'europa', 'BELGIUM', 10, '', 0, 0, 0, 0, 0, 'BEL'),
(7, 'Dänemark', 'DK', 'europa', 'DENMARK', 10, '', 0, 0, 0, 0, 0, 'DNK'),
(8, 'Finnland', 'FI', 'europa', 'FINLAND', 10, '', 0, 0, 0, 0, 0, 'FIN'),
(9, 'Frankreich', 'FR', 'europa', 'FRANCE', 10, '', 0, 0, 0, 0, 0, 'FRA'),
(10, 'Griechenland', 'GR', 'europa', 'GREECE', 10, '', 0, 0, 0, 0, 0, 'GRC'),
(11, 'Großbritannien', 'GB', 'europa', 'GREAT BRITAIN', 10, '', 0, 0, 0, 0, 0, 'GBR'),
(12, 'Irland', 'IE', 'europa', 'IRELAND', 10, '', 0, 0, 0, 0, 0, 'IRL'),
(13, 'Island', 'IS', 'europa', 'ICELAND', 10, '', 0, 0, 0, 0, 0, 'ISL'),
(14, 'Italien', 'IT', 'europa', 'ITALY', 10, '', 0, 0, 0, 0, 0, 'ITA'),
(15, 'Japan', 'JP', 'welt', 'JAPAN', 10, '', 0, 0, 0, 0, 0, 'JPN'),
(16, 'Kanada', 'CA', 'welt', 'CANADA', 10, '', 0, 0, 0, 0, 0, 'CAN'),
(18, 'Luxemburg', 'LU', 'europa', 'LUXEMBOURG', 10, '', 0, 0, 0, 0, 0, 'LUX'),
(20, 'Namibia', 'NA', 'welt', 'NAMIBIA', 10, '', 0, 0, 0, 0, 0, 'NAM'),
(21, 'Niederlande', 'NL', 'europa', 'NETHERLANDS', 10, '', 0, 0, 0, 0, 0, 'NLD'),
(22, 'Norwegen', 'NO', 'europa', 'NORWAY', 10, '', 0, 0, 0, 0, 0, 'NOR'),
(23, 'Österreich', 'AT', 'europa', 'AUSTRIA', 1, '', 0, 0, 0, 0, 0, 'AUT'),
(24, 'Portugal', 'PT', 'europa', 'PORTUGAL', 10, '', 0, 0, 0, 0, 0, 'PRT'),
(25, 'Schweden', 'SE', 'europa', 'SWEDEN', 10, '', 0, 0, 0, 0, 0, 'SWE'),
(26, 'Schweiz', 'CH', 'europa', 'SWITZERLAND', 10, '', 0, 1, 0, 0, 0, 'CHE'),
(27, 'Spanien', 'ES', 'europa', 'SPAIN', 10, '', 0, 0, 0, 0, 0, 'ESP'),
(28, 'USA', 'US', 'welt', 'USA', 10, '', 0, 0, 0, 0, 0, 'USA'),
(29, 'Liechtenstein', 'LI', 'europa', 'LIECHTENSTEIN', 10, '', 0, 0, 0, 0, 0, 'LIE'),
(30, 'Polen', 'PL', 'europa', 'POLAND', 10, '', 0, 0, 0, 0, 0, 'POL'),
(31, 'Ungarn', 'HU', 'europa', 'HUNGARY', 10, '', 0, 0, 0, 0, 0, 'HUN'),
(32, 'Türkei', 'TR', 'welt', 'TURKEY', 10, '', 0, 0, 0, 0, 0, 'TUR'),
(33, 'Tschechien', 'CZ', 'europa', 'CZECH REPUBLIC', 10, '', 0, 0, 0, 0, 0, 'CZE'),
(34, 'Slowakei', 'SK', 'europa', 'SLOVAKIA', 10, '', 0, 0, 0, 0, 0, 'SVK'),
(35, 'Rum&auml;nien', 'RO', 'europa', 'ROMANIA', 10, '', 0, 0, 0, 0, 0, 'ROU'),
(36, 'Brasilien', 'BR', 'welt', 'BRAZIL', 10, '', 0, 0, 0, 0, 0, 'BRA'),
(37, 'Israel', 'IL', 'welt', 'ISRAEL', 10, '', 0, 0, 0, 0, 0, 'ISR');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_currencies`
--

CREATE TABLE IF NOT EXISTS `s_core_currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `currency` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `standard` int(1) NOT NULL,
  `factor` double NOT NULL,
  `templatechar` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_core_currencies`
--

INSERT INTO `s_core_currencies` (`id`, `currency`, `name`, `standard`, `factor`, `templatechar`, `position`) VALUES
(1, 'EUR', 'Euro', 1, 1, '&euro;', 0),
(2, 'USD', 'US-Dollar', 0, 1.3625, '$', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customergroups`
--

CREATE TABLE IF NOT EXISTS `s_core_customergroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupkey` varchar(5) NOT NULL,
  `description` varchar(30) NOT NULL,
  `tax` int(1) NOT NULL DEFAULT '0',
  `taxinput` int(1) NOT NULL,
  `mode` int(11) NOT NULL,
  `discount` double NOT NULL,
  `minimumorder` double NOT NULL,
  `minimumordersurcharge` double NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupkey` (`groupkey`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_customergroups`
--

INSERT INTO `s_core_customergroups` (`id`, `groupkey`, `description`, `tax`, `taxinput`, `mode`, `discount`, `minimumorder`, `minimumordersurcharge`) VALUES
(1, 'EK', 'Shopkunden', 1, 1, 0, 0, 10, 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customergroups_discounts`
--

CREATE TABLE IF NOT EXISTS `s_core_customergroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `basketdiscount` double NOT NULL,
  `basketdiscountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`basketdiscountstart`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_customergroups_discounts`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_customerpricegroups`
--

CREATE TABLE IF NOT EXISTS `s_core_customerpricegroups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `netto` int(1) unsigned NOT NULL,
  `active` int(1) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_customerpricegroups`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_documents`
--

CREATE TABLE IF NOT EXISTS `s_core_documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `numbers` varchar(25) NOT NULL,
  `left` int(11) NOT NULL,
  `right` int(11) NOT NULL,
  `top` int(11) NOT NULL,
  `bottom` int(11) NOT NULL,
  `pagebreak` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

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

CREATE TABLE IF NOT EXISTS `s_core_documents_box` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) NOT NULL,
  `name` varchar(35) NOT NULL,
  `style` longtext NOT NULL,
  `value` longtext NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=180 ;

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
(112, 4, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(113, 4, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(111, 4, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(110, 4, 'Td_Name', 'white-space:normal;', ''),
(109, 4, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(107, 4, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(108, 4, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(106, 4, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(105, 4, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(98, 3, 'Content_Amount', 'margin-left:90mm;', ''),
(99, 3, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(100, 4, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(101, 4, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(102, 4, 'Header_Recipient', '', ''),
(103, 4, 'Header', 'height: 60mm;', ''),
(104, 4, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(95, 3, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(96, 3, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(97, 3, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(94, 3, 'Td_Name', 'white-space:normal;', ''),
(93, 3, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(92, 3, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(91, 3, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(82, 2, 'Content_Amount', 'margin-left:90mm;', ''),
(83, 2, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(84, 3, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(85, 3, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(86, 3, 'Header_Recipient', '', ''),
(87, 3, 'Header', 'height: 60mm;', ''),
(88, 3, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(89, 3, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(90, 3, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(81, 2, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(80, 2, 'Td_Head', 'border-bottom:1px solid #000;', ''),
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
(114, 4, 'Content_Amount', 'margin-left:90mm;', ''),
(115, 4, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(116, 5, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(117, 5, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(118, 5, 'Header_Recipient', '', ''),
(119, 5, 'Header', 'height: 60mm;', ''),
(120, 5, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(121, 5, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(122, 5, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(123, 5, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(124, 5, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(125, 5, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(126, 5, 'Td_Name', 'white-space:normal;', ''),
(127, 5, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(128, 5, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(129, 5, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(130, 5, 'Content_Amount', 'margin-left:90mm;', ''),
(131, 5, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(132, 6, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(133, 6, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(134, 6, 'Header_Recipient', '', ''),
(135, 6, 'Header', 'height: 60mm;', ''),
(136, 6, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(137, 6, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(138, 6, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(139, 6, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(140, 6, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(141, 6, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(142, 6, 'Td_Name', 'white-space:normal;', ''),
(143, 6, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(144, 6, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(145, 6, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(146, 6, 'Content_Amount', 'margin-left:90mm;', ''),
(147, 6, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(148, 7, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(149, 7, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(150, 7, 'Header_Recipient', '', ''),
(151, 7, 'Header', 'height: 60mm;', ''),
(152, 7, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(153, 7, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(154, 7, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(155, 7, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(156, 7, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(157, 7, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(158, 7, 'Td_Name', 'white-space:normal;', ''),
(159, 7, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(160, 7, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(161, 7, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(162, 7, 'Content_Amount', 'margin-left:90mm;', ''),
(163, 7, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>'),
(164, 8, 'Body', 'width:100%;\r\nfont-family: Verdana, Arial, Helvetica, sans-serif;\r\nfont-size:11px;', ''),
(165, 8, 'Logo', 'height: 20mm;\r\nwidth: 90mm;\r\nmargin-bottom:5mm;', '<p><img src="http://www.shopware.de/logo/logo.png " alt="" /></p>'),
(166, 8, 'Header_Recipient', '', ''),
(167, 8, 'Header', 'height: 60mm;', ''),
(168, 8, 'Header_Sender', '', '<p>Demo GmbH - Stra&szlig;e 3 - 00000 Musterstadt</p>'),
(169, 8, 'Header_Box_Left', 'width: 120mm;\r\nheight:60mm;\r\nfloat:left;', ''),
(170, 8, 'Header_Box_Right', 'width: 45mm;\r\nheight: 60mm;\r\nfloat:left;\r\nmargin-top:-20px;\r\nmargin-left:5px;', '<p><strong>Demo GmbH </strong><br /> Max Mustermann<br /> Stra&szlig;e 3<br /> 00000 Musterstadt<br /> Fon: 01234 / 56789<br /> Fax: 01234 / 			56780<br />info@demo.de<br />www.demo.de</p>'),
(171, 8, 'Header_Box_Bottom', 'font-size:14px;\r\nheight: 10mm;', ''),
(172, 8, 'Content', 'height: 65mm;\r\nwidth: 170mm;', ''),
(173, 8, 'Td', 'white-space:nowrap;\r\npadding: 5px 0;', ''),
(174, 8, 'Td_Name', 'white-space:normal;', ''),
(175, 8, 'Td_Line', 'border-bottom: 1px solid #999;\r\nheight: 0px;', ''),
(176, 8, 'Td_Head', 'border-bottom:1px solid #000;', ''),
(177, 8, 'Footer', 'width: 170mm;\r\nposition:fixed;\r\nbottom:-20mm;\r\nheight: 15mm;', '<table style="height: 90px;" border="0" width="100%">\r\n<tbody>\r\n<tr valign="top">\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Demo GmbH</span></p>\r\n<p><span style="font-size: xx-small;">Steuer-Nr <br />UST-ID: <br />Finanzamt </span><span style="font-size: xx-small;">Musterstadt</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Bankverbindung</span></p>\r\n<p><span style="font-size: xx-small;">Sparkasse Musterstadt<br />BLZ: <br />Konto: </span></p>\r\n<span style="font-size: xx-small;">aaaa<br /></span></td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">AGB<br /></span></p>\r\n<p><span style="font-size: xx-small;">Gerichtsstand ist Musterstadt<br />Erf&uuml;llungsort Musterstadt<br />Gelieferte Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</span></p>\r\n</td>\r\n<td style="width: 25%;">\r\n<p><span style="font-size: xx-small;">Gesch&auml;ftsf&uuml;hrer</span></p>\r\n<p><span style="font-size: xx-small;">Max Mustermann</span></p>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>'),
(178, 8, 'Content_Amount', 'margin-left:90mm;', ''),
(179, 8, 'Content_Info', '', '<p>Die Ware bleibt bis zur vollst&auml;ndigen Bezahlung unser Eigentum</p>');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_elements`
--

CREATE TABLE IF NOT EXISTS `s_core_engine_elements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` int(11) NOT NULL DEFAULT '0',
  `domname` varchar(60) NOT NULL,
  `domvalue` varchar(60) NOT NULL,
  `domtype` varchar(60) NOT NULL,
  `domdescription` varchar(60) NOT NULL,
  `required` int(1) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  `databasefield` varchar(255) NOT NULL,
  `domclass` varchar(25) NOT NULL,
  `version` int(11) NOT NULL,
  `availablebyvariants` int(11) NOT NULL,
  `help` varchar(255) NOT NULL,
  `multilanguage` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `databasefield` (`databasefield`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=54 ;

--
-- Daten für Tabelle `s_core_engine_elements`
--

INSERT INTO `s_core_engine_elements` (`id`, `group`, `domname`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `version`, `availablebyvariants`, `help`, `multilanguage`) VALUES
(1, 1, 'txtArtikel', '', 'text', 'Artikel-Bezeichnung', 1, 2, 'name', 'w200', 0, 0, 'Bezeichnung für Artikel in Shop', 1),
(2, 1, 'txtHersteller', '', 'text', 'Hersteller', 1, 1, 'supplierID', 'w200', 0, 0, 'Auswahl Hersteller', 0),
(15, 2, 'txtlangbeschreibung', '', 'wysiwyg', 'Beschreibung', 0, 2, 'description_long', '', 0, 0, 'Langbeschreibung', 1),
(4, 1, 'txtbestellnr', '', 'text', 'Artikelnummer', 1, 3, 'ordernumber', 'w100', 0, 1, 'Eindeutige Artikelnummer für Zuordnung Warenwirtschaft', 0),
(5, 5, 'txtherstellernr', '', 'text', 'Herstellernummer', 0, 2, 'suppliernumber', '', 0, 1, 'Hersteller/Lieferantennummer', 0),
(6, 5, 'txtzusatztxt', '', 'text', 'Varianten-Bezeichnung', 0, 4, 'additionaltext', 'w200', 0, 1, 'Anzeige-Text bei Verwendung eindimensionaler Varianten (z.B. Farbe-Rot)', 1),
(29, 0, 'txtfreearticle', '', 'boolean', 'Gratis-Artikel', 0, 7, 'free', 'merge', 2, 0, '', 0),
(10, 3, 'txtlieferzeit', '', 'text', 'Lieferzeit (In Tagen)', 0, 1, 'shippingtime', '', 0, 0, 'Angabe der Lieferzeit (wenn nicht auf Lager)', 0),
(11, 3, 'txtversandkostenfrei', '', 'boolean', 'Versandkostenfrei', 0, 12, 'shippingfree', 'merge', 0, 0, 'Versandkostenfrei Ja / Nein', 0),
(12, 3, 'DV', '', 'date', 'Erscheinungsdatum', 0, 6, 'releasedate', '', 0, 0, 'Datum ab wann der Artikel verfügbar ist (wenn Lagerbestand = 0)', 0),
(13, 1, 'txtaktiv', '1', 'boolean', 'Aktiv', 0, 4, 'active', 'merge', 0, 0, 'Artikel im Shop anzeigen', 0),
(14, 6, 'price', '', 'price', '', 1, 0, '', '', 0, 1, '', 0),
(16, 3, 'toparticle', '', 'boolean', 'Artikel hervorheben', 0, 8, 'topseller', 'merge', 0, 0, 'Artikel im Shop hervorheben', 0),
(17, 3, 'txtmwst', '', 'select', 'MwSt', 1, 5, 'taxID', '', 0, 0, 'MwSt. Satz', 0),
(22, 7, 'attr[3]', '', 'textarea', 'Kommentar', 0, 3, 'attr3', '', 0, 0, 'Optionaler Kommentar', 1),
(24, 3, 'txtweight', '', 'text', 'Gewicht (KG)', 0, 4, 'weight', '', 0, 1, 'Gewicht des Artikels in KG zur Berechnung der Versandkosten', 0),
(33, 7, 'attr[1]', '', 'text', 'Freitext-1', 0, 1, 'attr1', 'w200', 0, 1, 'Freitext zur Anzeige auf der Detailseite', 1),
(27, 3, 'txtlager', '', 'text', 'Lagerbestand', 0, 2, 'instock', '', 0, 1, 'Aktueller Lagerbestand', 0),
(28, 3, 'txtmindestbestand', '', 'text', 'Lager-Mindestbestand', 0, 3, 'stockmin', '', 0, 1, '', 0),
(30, 0, 'txtesd', '', 'boolean', 'ESD-Produkt', 0, 9, 'esd', 'merge', 2, 0, '', 0),
(32, 5, 'txtshortdescription', '', 'textarea', 'Kurzbeschreibung', 0, 2, 'description', '', 0, 0, 'Kurzbeschreibung für Suchmaschinen, Exporte und Übersichten', 1),
(31, 5, 'txtkeywords', '', 'textarea', 'Keywords', 0, 3, 'keywords', '', 0, 0, 'Meta-Keywords für Suchmaschinen und intelligente Suche', 1),
(34, 7, 'attr[2]', '', 'text', 'Freitext-2', 0, 2, 'attr2', 'w200', 0, 1, 'Freitext zur Anzeige auf der Detailseite', 1),
(35, 3, 'txtminpurchase', '', 'text', 'Mindestabnahme', 0, 13, 'minpurchase', 'w30', 0, 0, 'Mindestabnahmemenge (muss teilbar durch Staffelung sein, wenn eingetragen)', 0),
(36, 3, 'txtpurchasesteps', '', 'text', 'Staffelung', 0, 14, 'purchasesteps', 'w30', 0, 0, 'Staffelung in der der Artikel gekauft werden kann (Deaktiviert Preisstaffeln, Mindestmenge muss teilbar durch Staffelung sein)', 0),
(38, 8, 'txtunit', 'Optional', 'select', 'Maßeinheit', 0, 1, 'unitID', '', 0, 0, 'Für Grundpreisberechnung', 0),
(37, 3, 'txtmaxpurchase', '', 'text', 'Maximalabnahme', 0, 15, 'maxpurchase', 'w30', 0, 0, 'Maximale Abnahmemenge (Standard: 1000)', 0),
(39, 8, 'txtpurchaseunit', '0', 'text', 'Inhalt', 0, 15, 'purchaseunit', 'w30', 0, 0, 'Grundpreis-Verordnung: Menge pro Stück', 0),
(40, 8, 'txtreferenceunit', '0', 'text', 'Grundeinheit', 0, 15, 'referenceunit', 'w30', 0, 0, 'Für Grundpreisberechnung: Wert der Grundeinheit (z.B. 100 für 100 Gramm)', 0),
(42, 3, 'datum', '', 'date', 'Einstelldatum', 0, 6, 'datum', 'w75', 0, 0, 'Einstelldatum', 0),
(45, 1, 'checkPricegroup', '', 'boolean', 'Daten aus Preisgruppe anwenden', 0, 5, 'pricegroupActive', 'merge', 0, 0, 'Preisgruppe für diesen Artikel anwenden', 0),
(46, 1, 'selectPricegroup', '', 'select', 'Preisgruppe', 0, 1, 'pricegroupID', '', 0, 0, 'Preisgruppe für diesen Artikel', 0),
(47, 3, 'pseudosales', '', 'text', 'Pseudo-Verkäufe (Berechnung Topseller)', 0, 8, 'pseudosales', 'merge', 0, 0, 'Ermöglicht die Anpassung der Topseller', 0),
(48, 1, 'selectFilterGroup', 'Bitte wählen', 'select', 'Eigenschaften', 0, 1, 'filtergroupID', '', 0, 0, 'Eigenschaftsgruppe für diesen Artikel', 0),
(49, 3, 'laststock', '', 'boolean', 'Abverkauf', 0, 9, 'laststock', 'merge', 0, 0, 'Artikel deaktivieren, wenn Lagerbestand = 0', 0),
(50, 8, 'txtpackunit', '0', 'text', 'Verpackungseinheit', 0, 16, 'packunit', 'w100', 0, 0, 'Einheit die in der Mengenauswahl ausgegeben wird (z.B.: Pakete)', 1),
(51, 3, 'notification', '', 'boolean', 'eMail-Benachrichtigung, wenn nicht auf Lager', 0, 3, 'notification', '', 0, 0, 'Benachrichtigungsfunktion einblenden, wenn Artikel nicht verfügbar', 0),
(52, 1, 'selectTemplate', 'Bitte wählen', 'select', 'Template', 0, 1, 'template', '', 0, 0, 'Template welches verwendet werden soll', 0),
(53, 0, 'changetime', '', 'text', 'Einstelldatum', 0, 6, 'changetime', 'w100', 0, 0, 'Einstelldatum', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_fieldsets`
--

CREATE TABLE IF NOT EXISTS `s_core_engine_fieldsets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postelement` varchar(30) NOT NULL,
  `table` varchar(30) NOT NULL,
  `queryfield` varchar(30) NOT NULL,
  `uniqueid` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_engine_fieldsets`
--

INSERT INTO `s_core_engine_fieldsets` (`id`, `postelement`, `table`, `queryfield`, `uniqueid`) VALUES
(1, 'txtHersteller', 's_articles_supplier', 'name', 'id');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_groups`
--

CREATE TABLE IF NOT EXISTS `s_core_engine_groups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group` varchar(60) NOT NULL,
  `availablebyvariants` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `s_core_engine_groups`
--

INSERT INTO `s_core_engine_groups` (`id`, `group`, `availablebyvariants`, `position`) VALUES
(1, 'Stammdaten', 1, 1),
(2, 'Beschreibung', 0, 2),
(3, 'Einstellungen', 1, 4),
(5, 'Hauptartikel-Daten', 1, 5),
(6, 'Preise', 1, 3),
(7, 'Zusatzfelder', 1, 6),
(8, 'Grundpreisberechnung', 0, 7),
(10, 'Kundengruppen', 0, 4);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_engine_queries`
--

CREATE TABLE IF NOT EXISTS `s_core_engine_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `query` text NOT NULL,
  `option` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `domelement` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

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
-- Tabellenstruktur für Tabelle `s_core_engine_values`
--

CREATE TABLE IF NOT EXISTS `s_core_engine_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `domelement` varchar(30) NOT NULL,
  `domvalue` varchar(30) NOT NULL,
  `description` varchar(30) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_core_engine_values`
--

INSERT INTO `s_core_engine_values` (`id`, `domelement`, `domvalue`, `description`, `position`) VALUES
(1, 'txtart', '1', 'Hauptartikel', 1),
(2, 'txtart', '2', 'Variante', 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_factory`
--

CREATE TABLE IF NOT EXISTS `s_core_factory` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `basename` varchar(255) NOT NULL,
  `basefile` varchar(255) NOT NULL,
  `inheritname` varchar(255) NOT NULL,
  `inheritfile` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`basename`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

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
(15, 'Intelligente Suche', 'sSearch', 'sSearch.php', '', ''),
(14, 'Content-Management', 'sCms', 'sCms.php', '', ''),
(13, 'Marketing-Funktionen', 'sMarketing', 'sMarketing.php', '', ''),
(17, 'Support-Funktionen', 'sCmsSupport', 'sCmsSupport.php', '', ''),
(18, 'Cache-Funktionen', 'sCache', 'sCache.php', '', ''),
(19, 'Ticket Support', 'sTicketSystem', 'sTicketSystem.php', '', ''),
(20, 'Router', 'sRouter', 'sRouter.php', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_hookpoints`
--

CREATE TABLE IF NOT EXISTS `s_core_hookpoints` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `module` varchar(255) NOT NULL,
  `position` varchar(255) NOT NULL,
  `code` text NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_hookpoints`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_im`
--

CREATE TABLE IF NOT EXISTS `s_core_im` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `datum` datetime NOT NULL,
  `receiver` int(11) NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_im`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_licences`
--

CREATE TABLE IF NOT EXISTS `s_core_licences` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL,
  `module` varchar(255) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `inactive` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_licences`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_locales`
--

CREATE TABLE IF NOT EXISTS `s_core_locales` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(255) NOT NULL,
  `language` varchar(255) NOT NULL,
  `territory` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `locale` (`locale`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=256 ;

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

CREATE TABLE IF NOT EXISTS `s_core_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `text` text NOT NULL,
  `datum` datetime NOT NULL,
  `value1` varchar(255) NOT NULL,
  `value2` varchar(255) NOT NULL,
  `value3` varchar(255) NOT NULL,
  `value4` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=49 ;

--
-- Daten für Tabelle `s_core_log`
--

INSERT INTO `s_core_log` (`id`, `type`, `key`, `text`, `datum`, `value1`, `value2`, `value3`, `value4`) VALUES
(1, 'backend', 'Templateauswahl', 'Cache wurde geleert', '2010-10-18 02:07:28', 'Administrator', '', '', ''),
(2, 'backend', 'Templateauswahl', 'Cache wurde geleert', '2010-10-18 02:08:49', 'Administrator', '', '', ''),
(3, 'backend', 'Templateauswahl', 'Cache wurde geleert', '2010-10-18 02:10:23', 'Administrator', '', '', ''),
(4, 'backend', 'Templateauswahl', 'Cache wurde geleert', '2010-10-18 02:11:36', 'Administrator', '', '', ''),
(5, 'backend', 'Grundeinstellungen', 'Eintrag wurde gespeichert', '2010-10-18 02:13:00', 'Administrator', '', '', ''),
(6, 'backend', 'Neuer Artikel', 'Stammdaten gespeichert', '2010-10-18 02:13:10', 'Administrator', '', '', ''),
(7, 'backend', 'Kategorien', 'Kategorie "test" wurde angelegt', '2010-10-18 02:13:15', 'Administrator', '', '', ''),
(8, 'backend', 'Neuer Artikel', 'Cache wurde geleert', '2010-10-18 02:13:23', 'Administrator', '', '', ''),
(9, 'backend', 'Grundeinstellungen', 'Preisgruppe wurde gelöscht', '2010-10-18 02:14:29', 'Administrator', '', '', ''),
(10, 'backend', 'Grundeinstellungen', 'Eintrag wurde gespeichert', '2010-10-18 02:14:32', 'Administrator', '', '', ''),
(11, 'backend', 'Grundeinstellungen', 'Cache wurde geleert', '2010-10-18 02:15:23', 'Administrator', '', '', ''),
(12, 'backend', 'Shopseiten', 'Keine Gruppe ausgewählt', '2010-10-18 02:18:41', 'Administrator', '', '', ''),
(13, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:18:46', 'Administrator', '', '', ''),
(14, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:18:53', 'Administrator', '', '', ''),
(15, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:19:01', 'Administrator', '', '', ''),
(16, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:19:09', 'Administrator', '', '', ''),
(17, 'backend', 'Shopseiten', 'Keine Gruppe ausgewählt', '2010-10-18 02:19:30', 'Administrator', '', '', ''),
(18, 'backend', 'Shopseiten', 'Keine Gruppe ausgewählt', '2010-10-18 02:19:38', 'Administrator', '', '', ''),
(19, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:20:02', 'Administrator', '', '', ''),
(20, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:20:22', 'Administrator', '', '', ''),
(21, 'backend', 'Shopseiten', 'Cache wurde geleert', '2010-10-18 02:20:25', 'Administrator', '', '', ''),
(22, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:20:52', 'Administrator', '', '', ''),
(23, 'backend', 'Shopseiten', 'Eintrag wurde gespeichert', '2010-10-18 02:21:00', 'Administrator', '', '', ''),
(24, 'backend', 'Shopseiten', 'Cache wurde geleert', '2010-10-18 02:21:03', 'Administrator', '', '', ''),
(25, 'backend', 'Shopseiten', 'Cache wurde geleert', '2010-10-18 02:37:02', 'Administrator', '', '', ''),
(26, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:07:34', 'Administrator', '', '', ''),
(27, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:07:39', 'Administrator', '', '', ''),
(28, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:04', 'Administrator', '', '', ''),
(29, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:06', 'Administrator', '', '', ''),
(30, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:15', 'Administrator', '', '', ''),
(31, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:18', 'Administrator', '', '', ''),
(32, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:21', 'Administrator', '', '', ''),
(33, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:24', 'Administrator', '', '', ''),
(34, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:26', 'Administrator', '', '', ''),
(35, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:30', 'Administrator', '', '', ''),
(36, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:32', 'Administrator', '', '', ''),
(37, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:36', 'Administrator', '', '', ''),
(38, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:41', 'Administrator', '', '', ''),
(39, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:45', 'Administrator', '', '', ''),
(40, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:47', 'Administrator', '', '', ''),
(41, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:50', 'Administrator', '', '', ''),
(42, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:52', 'Administrator', '', '', ''),
(43, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:54', 'Administrator', '', '', ''),
(44, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:56', 'Administrator', '', '', ''),
(45, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:08:59', 'Administrator', '', '', ''),
(46, 'backend', 'eMail-Vorlagen', 'Eintrag wurde gespeichert', '2010-10-18 04:09:01', 'Administrator', '', '', ''),
(47, 'backend', 'Versandkosten', 'Versandkosten Einstellungen wurden erfolgreich gespeichert', '2010-10-18 10:31:26', 'Administrator', '', '', ''),
(48, 'backend', 'Versandkosten', 'Versandkosten Einstellungen wurden erfolgreich gespeichert', '2010-10-18 10:31:52', 'Administrator', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_menu`
--

CREATE TABLE IF NOT EXISTS `s_core_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL DEFAULT '0',
  `hyperlink` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `onclick` varchar(255) NOT NULL,
  `style` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `active` int(1) NOT NULL DEFAULT '0',
  `pluginID` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`parent`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=99 ;

--
-- Daten für Tabelle `s_core_menu`
--

INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`) VALUES
(1, 0, '', 'Artikel', '', '', 'ico package_green', 0, 1, NULL),
(2, 1, '', 'Neu', 'loadSkeleton(''articles'');', 'background-position: 5px 5px;', 'ico2 package_add', -3, 1, NULL),
(4, 1, '', 'Kategorien', 'loadSkeleton(''categories'');', 'background-position: 5px 5px;', 'ico2 folders_stack', 0, 1, NULL),
(6, 1, '', 'Hersteller', 'loadSkeleton(''supplier'');', 'background-position: 5px 5px;', 'ico2 lorry', 2, 1, NULL),
(7, 0, '', 'Inhalte', '', '', 'ico2 note03', 0, 1, NULL),
(8, 30, '', 'Banner', 'loadSkeleton(''imagepromo'');', 'background-position: 5px 5px;', 'ico2 image', 0, 1, NULL),
(9, 30, '', 'Einkaufswelten', 'loadSkeleton(''promotion'')', 'background-position: 5px 5px;', 'ico2 pin', 1, 1, NULL),
(10, 30, '', 'Gutscheine', 'loadSkeleton(''vouchers'');', 'background-position: 5px 5px;', 'ico2 email_open_image', 3, 1, NULL),
(11, 30, '', 'Pr&auml;mienartikel', 'loadSkeleton(''premiums'');', 'background-position: 5px 5px;', 'ico2 star', 2, 1, NULL),
(12, 30, '', 'Produktexporte', 'loadSkeleton(''search_price'');', 'background-position: 5px 5px;', 'ico2 folder_open_image', 5, 1, NULL),
(15, 7, '', 'Shopseiten', 'loadSkeleton(''cmsstatic'');', 'background-position: 5px 5px;', 'ico2 documents', 0, 1, NULL),
(16, 7, '', 'Feeds', 'loadSkeleton(''cms'');', 'background-position: 5px 5px;', 'ico2 layout1', 1, 1, NULL),
(20, 0, '', 'Kunden', '', '', 'ico customer', 0, 1, NULL),
(21, 20, '', 'Kundenliste', 'loadSkeleton(''user'');', 'background-position: 5px 5px;', 'ico2 card_address', 0, 1, NULL),
(22, 20, '', 'Bestellungen', 'loadSkeleton(''orderlist'');', 'background-position: 5px 5px;', 'ico2 sticky_notes_pin', 0, 1, NULL),
(23, 0, '', 'Einstellungen', '', '', 'ico2 wrench_screwdriver', 0, 1, NULL),
(24, 23, '', 'Grundeinstellungen', 'loadSkeleton(''presetting'');', 'background-position: 5px 5px;', 'ico2 computer', -4, 1, NULL),
(25, 23, '', 'Benutzerverwaltung', 'loadSkeleton(''auth'');', 'background-position: 5px 5px;', 'ico2 status_online', -2, 1, NULL),
(26, 23, '', 'Versandkosten', 'loadSkeleton(''shipping'');', 'background-position: 5px 5px;', 'ico2 envelope_arrow ', 0, 1, NULL),
(27, 23, '', 'Zahlungsarten', 'loadSkeleton(''payment'');', 'background-position: 5px 5px;', 'ico2 creditcards', 0, 1, NULL),
(28, 23, '', 'eMail-Vorlagen', 'loadSkeleton(''mails'');', 'background-position: 5px 5px;', 'ico2 mail_pencil', 0, 1, NULL),
(29, 23, '', 'Shopcache leeren', 'openAction(''cache'');', 'background-position: 5px 5px;', 'ico2 bin', -5, 1, NULL),
(30, 0, '', 'Marketing', '', '', 'ico2 chart_bar01', 0, 1, NULL),
(31, 69, '', '&Uuml;bersicht', 'loadSkeleton(''overview'');', 'background-position: 5px 5px;', 'ico2 table_arrow', -5, 1, NULL),
(32, 69, '', 'Statistiken / Diagramme', 'loadSkeleton(''statistics'');', 'background-position: 5px 5px;', 'ico2 chart_curve1', -4, 1, NULL),
(58, 30, '', 'Newsletter (Campaigns)', 'loadSkeleton(''mailcampaigns'')', 'background-position: 5px 5px;', 'ico2 mails_stack', 7, 1, NULL),
(34, 0, '', 'Fenster', '', '', 'ico window', 0, 1, NULL),
(71, 23, '', 'Textbausteine', '', '', 'ico2 plugin', 0, 1, NULL),
(86, 71, '', 'Neue Templatebasis', 'openAction(''snippet'')', 'background-position: 5px 5px', 'ico2 plugin', 0, 1, NULL),
(36, 34, '', 'Nebeneinander', 'sWindows._groupHorizontal();', 'background-position: 5px 5px;', 'ico2 application_tile_horizontal', 0, 1, NULL),
(37, 34, '', 'Untereinander', 'sWindows._groupVertical();', 'background-position: 5px 5px;', 'ico2 application_tile_vertical', 0, 1, NULL),
(38, 34, '', 'Alle schliessen', 'sWindows._closeAll();', 'background-position: 5px 5px;', 'ico2 schliessen', 0, 1, NULL),
(39, 34, '', 'Alle minimie.', 'sWindows._minAll();', 'background-position: 5px 5px;', 'ico2 minimieren', 0, 1, NULL),
(40, 0, '', 'Hilfe', '', '', 'ico question_frame', 0, 1, NULL),
(41, 40, '', 'Onlinehilfe aufrufen', 'window.open(''http://www.shopware.de/wiki'',''Shopware'',''width=800,height=550,scrollbars=yes'')', 'background-position: 5px 5px;', 'ico2 book_open', 0, 1, NULL),
(63, 23, '', 'Systeminfo', 'loadSkeleton(''systeminfo'')', 'background-position: 5px 5px;', 'ico2 information_frame', -3, 1, NULL),
(42, 40, '', 'Shopware Account', 'openAccount();', 'background-position: 5px 5px;', 'ico2 shopware', 0, 1, NULL),
(43, 40, '', 'Lizenz', 'loadSkeleton(''lizenz'')', 'background-position: 5px 5px;', 'ico2 key', 0, 1, NULL),
(44, 40, '', 'Über Shopware', 'window.Growl(''{release}<br />(c)2010-2011 shopware AG'');', 'background-position: 5px 5px;', 'ico2 information_frame', 0, 1, NULL),
(45, 23, '', 'Templateauswahl', 'loadSkeleton(''templates'')', 'background-position: 5px 5px;', 'ico2 layout_header_footer_2', 0, 1, NULL),
(46, 7, '', 'Import/Export', 'loadSkeleton(''import'');', 'background-position: 5px 5px;', 'ico2 arrow_circle_double_135', 3, 1, NULL),
(50, 1, '', 'Bewertungen', 'loadSkeleton(''vote'');', 'background-position: 5px 5px;', 'ico2 bubble01', 3, 1, NULL),
(51, 30, '', 'Aktionen', 'loadSkeleton(''salescampaigns'')', 'background-position: 5px 5px;', 'ico2 aktion', 4, 1, NULL),
(62, 23, '', 'Riskmanagement', 'loadSkeleton(''risk'')', 'background-position: 5px 5px;', 'ico2 bulb_off', 0, 1, NULL),
(56, 30, '', 'Partnerprogramm', 'loadSkeleton(''partner'')', 'background-position: 5px 5px;', 'ico2 arrow_leftright_blue', 6, 1, NULL),
(57, 7, '', 'Formulare', 'loadSkeleton(''support'');', 'background-position: 5px 5px;', 'ico2 table02', 2, 1, NULL),
(59, 69, '', 'Abbruch-Analyse', 'loadSkeleton(''orderscanceled'');', 'background-position: 5px 5px;', 'cross', 0, 1, NULL),
(72, 1, '', 'Eigenschaften', 'loadSkeleton(''filter'');', 'background-position: 5px 5px;', 'ico2 databases_pencil', 0, 1, NULL),
(64, 7, '', 'Datei-Archiv', 'loadSkeleton(''browser'')', 'background-position: 5px 5px;', 'ico2 disc', 4, 1, NULL),
(65, 20, '', 'Zahlungen', '', 'background-position: 5px 5px;', 'ico2 date2', 0, 1, NULL),
(66, 1, '', '&Uuml;bersicht', 'loadSkeleton(''articlesfast'');', 'background-position: 5px 5px;', 'ico2 table_plus', -2, 1, NULL),
(68, 23, '', 'Logfile', 'loadSkeleton(''authlog'');', 'background-position: 5px 5px;', 'ico2 cards', -2, 1, NULL),
(69, 30, '', 'Auswertungen', '', 'background-position: 5px 5px;', 'ico2 chart_pie1', -1, 1, NULL),
(75, 20, '', 'Anlegen', 'loadSkeleton(''useradd'');', 'background-position: 5px 5px;', 'ico2 user_add', -1, 1, NULL),
(77, 65, '', 'Paypal', 'loadSkeleton(''paypalreserveorder'');', 'background-position: 5px 5px;', 'ico2 date2', 0, 1, NULL),
(78, 65, '', 'Saferpay', 'loadSkeleton(''saferpayreserveorder'');', 'background-position: 5px 5px;', 'ico2 date2', 0, 1, NULL),
(79, 20, '', 'Ticket-System', 'loadSkeleton(''ticket_system'');', 'background-position: 5px 5px;', 'ico2 sticky_notes_pin', 1, 1, NULL),
(88, 40, '', 'Zum Forum', 'window.open(''http://www.shopware-community.de'',''Shopware'',''width=800,height=550,scrollbars=yes'')', 'background-position: 5px 5px', 'ico2 book_open', -1, 1, NULL),
(81, 65, '', 'ClickPay', 'loadSkeleton(''clickpay'');', 'background-position: 5px 5px;', 'ico2 date2', 0, 1, NULL),
(82, 20, '', 'ClickPay Bonitätsüberprüfung', 'loadSkeleton(''clickpay_rating'');', 'background-position: 5px 5px;', 'ico2 date2', 0, 1, NULL),
(83, 20, '', 'Kundenspezifische Preise', 'loadSkeleton(''userprice'');', 'background-position: 5px 5px;', 'ico2 card_address', 0, 1, NULL),
(84, 69, '', 'E-Mail Benachrichtigung', 'loadSkeleton(''notificationStat'');', 'background-position: 5px 5px;', 'ico2 table_arrow', 4, 1, NULL),
(85, 7, '', 'Blog', 'loadSkeleton(''blog'');', 'background-position: 5px 5px;', 'ico2 layout1', 1, 1, NULL),
(87, 71, '', 'Alte Templatebasis', 'loadSkeleton(''snippets'')', 'background-position: 5px 5px', 'ico2 plugin', 1, 1, NULL),
(90, 23, '', 'Plugins', 'openAction(''plugin'');', 'background-position: 5px 5px;', 'ico2 bricks', -4, 1, NULL),
(91, 29, '', 'Textbausteine', 'deleteCache(''snippets'');', 'background-position: 5px 5px;', 'ico2 bin', 1, 1, NULL),
(97, 29, '', 'Artikel + Kategorien', 'deleteCache(''articles'');', 'background-position: 5px 5px;', 'ico2 bin', 1, 1, NULL),
(98, 29, '', 'Konfiguration', 'deleteCache(''config'');', 'background-position: 5px 5px;', 'ico2 bin', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_modules`
--

CREATE TABLE IF NOT EXISTS `s_core_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `licence` double NOT NULL,
  `log` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_modules`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_multilanguage`
--

CREATE TABLE IF NOT EXISTS `s_core_multilanguage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `isocode` varchar(15) NOT NULL,
  `locale` varchar(255) NOT NULL,
  `parentID` int(11) NOT NULL,
  `flagstorefront` varchar(255) NOT NULL,
  `flagbackend` varchar(255) NOT NULL,
  `skipbackend` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `defaultcustomergroup` varchar(25) NOT NULL,
  `template` varchar(255) NOT NULL,
  `doc_template` varchar(100) NOT NULL DEFAULT '0/de/forms',
  `separate_numbers` tinyint(4) NOT NULL,
  `domainaliase` text NOT NULL,
  `defaultcurrency` int(11) NOT NULL,
  `default` int(1) NOT NULL,
  `switchCurrencies` varchar(255) NOT NULL,
  `switchLanguages` varchar(255) NOT NULL,
  `fallback` varchar(255) NOT NULL,
  `encoding` varchar(255) NOT NULL,
  `navigation` varchar(255) NOT NULL,
  `inheritstyles` int(1) NOT NULL,
  `text1` varchar(255) NOT NULL,
  `text2` varchar(255) NOT NULL,
  `text3` varchar(255) NOT NULL,
  `text4` varchar(255) NOT NULL,
  `text5` varchar(255) NOT NULL,
  `text6` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_multilanguage`
--

INSERT INTO `s_core_multilanguage` (`id`, `isocode`, `locale`, `parentID`, `flagstorefront`, `flagbackend`, `skipbackend`, `name`, `defaultcustomergroup`, `template`, `doc_template`, `separate_numbers`, `domainaliase`, `defaultcurrency`, `default`, `switchCurrencies`, `switchLanguages`, `fallback`, `encoding`, `navigation`, `inheritstyles`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
(1, 'de', '1', 3, '', 'de.png', 1, 'Deutsch', 'EK', 'templates/orange', 'templates/orange', 0, '', 1, 1, '1|2', '1|6', '', '', '', 0, '', '', '', '', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_optin`
--

CREATE TABLE IF NOT EXISTS `s_core_optin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `hash` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `data` text COLLATE latin1_german1_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  KEY `datum` (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_optin`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans`
--

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `template` varchar(255) NOT NULL,
  `class` varchar(255) NOT NULL,
  `table` varchar(70) NOT NULL,
  `hide` int(1) NOT NULL,
  `additionaldescription` text NOT NULL,
  `debit_percent` double NOT NULL DEFAULT '0',
  `surcharge` double NOT NULL DEFAULT '0',
  `surchargestring` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `esdactive` int(1) NOT NULL,
  `embediframe` varchar(255) NOT NULL,
  `hideprospect` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Daten für Tabelle `s_core_paymentmeans`
--

INSERT INTO `s_core_paymentmeans` (`id`, `name`, `description`, `template`, `class`, `table`, `hide`, `additionaldescription`, `debit_percent`, `surcharge`, `surchargestring`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`) VALUES
(2, 'debit', 'Lastschrift', 'debit.tpl', 'debit.php', 's_user_debit', 0, 'Zusatztext', 0, 0, '', 4, 1, 0, '', 0),
(3, 'cash', 'Nachnahme', 'cash.tpl', 'cash.php', '', 0, '(zzgl. 2,00 Euro Nachnahmegebühren)', 0, 0, '', 2, 1, 0, '', 0),
(4, 'invoice', 'Rechnung', 'invoice.tpl', 'invoice.php', '', 0, 'Sie zahlen einfach und bequem auf Rechnung. Shopware bietet z.B. auch die Möglichkeit, Rechnung automatisiert erst ab der 2. Bestellung für Kunden zur Verfügung zu stellen, um Zahlungsausfälle zu vermeiden.', 0, 0, '', 3, 1, 1, '', 0),
(5, 'prepayment', 'Vorkasse', 'prepayment.tpl', 'prepayment.php', '', 0, 'Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.', 0, 0, '', 1, 1, 0, '', 0),
(17, 'ipayment', 'iPayment', 'ipayment.tpl', 'ipayment.php', '', 0, 'Zahlen Sie sicher, schnell und bequem per Kreditkarte. Wir akzeptieren die folgenden Kreditkarten: VISA / Master Card / American Express', 0, 0, '', 3, 0, 1, '../../.../../../../../engine/connectors/ipayment/silent_form_cc.php', 0),
(18, 'sofortueberweisung', 'sofortüberweisung.de', 'sofortueberweisung.tpl', 'sofortueberweisung.php', '', 0, 'Unbeschwert im Internet einkaufen, können Sie ab sofort bei über 8.000 Onlineshops, die an sofortüberweisung.de angeschlossen sind. Sie profitieren nicht nur von einer sofortigen Lieferung von Lagerware, sondern auch von unserem TÜV-geprüften Datenschutz.', 0, 0, '', 3, 0, 1, '../../.../../../../../engine/connectors/sofort/form.php', 0),
(19, 'ClickandBuy', 'ClickandBuy ', 'clickandbuy.tpl', 'clickandbuy.php', '', 0, 'Mit ClickandBuy zahlen Sie Ihre Einkäufe in Online-Shops einfach und schnell per Bankeinzug, Kreditkarte und allen gängigen Zahlmethoden. Viele Millionen Kunden nutzen bereits das ClickandBuy System. Weltweit, sicher und kostenfrei. <a target=_blanc href="http://ClickandBuy.com/DE/de/info.html"><u>Mehr Infos</u></a>', 0, 0, '', 1, 0, 1, '../../.../../../../../engine/connectors/clickandbuy/form.php', 0),
(20, 'paypalexpress', 'PayPal', 'paypalexpress.tpl', 'paypalexpress.php', '', 0, 'PayPal ist der Online-Zahlungsservice, mit dem Sie in Online-Shops sicher, einfach und schnell bezahlen und das kostenlos.', 0, 0, '', 1, 0, 1, '../../.../../../../../engine/connectors/paypalexpress/form.php', 0),
(21, 'Saferpay', 'Saferpay', 'saferpay.tpl', 'saferpay.php', '', 0, 'Saferpay - einfach - sicher - modular', 0, 0, '', 1, 0, 1, '../../.../../../../../engine/connectors/saferpay/form.php', 0),
(28, 'moneybookers', 'Moneybookers', 'moneybookers.tpl', 'moneybookers.class.php', '', 0, '', 0, 0, '', 0, 0, 1, '../../.../../../../../engine/connectors/moneybookers/form.php', 0),
(29, 'clickpay_elv', 'ClickPay ELV', 'clickpay.tpl', 'clickpay.php', '', 0, '', 0, 0, '', 0, 0, 0, '../../.../../../../../engine/connectors/clickpay/form_elv.php', 0),
(30, 'clickpay_giropay', 'ClickPay GiroPay', 'clickpay.tpl', 'clickpay.php', '', 0, '', 0, 0, '', 0, 0, 0, '../../.../../../../../engine/connectors/clickpay/form_giropay.php', 0),
(31, 'clickpay_credit', 'ClickPay Kreditkarte', 'clickpay.tpl', 'clickpay.php', '', 0, '', 0, 0, '', 0, 0, 0, '../../.../../../../../engine/connectors/clickpay/form.php', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans_countries`
--

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_countries` (
  `paymentID` int(11) unsigned NOT NULL,
  `countryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`countryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_core_paymentmeans_countries`
--

INSERT INTO `s_core_paymentmeans_countries` (`paymentID`, `countryID`) VALUES
(3, 2);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_paymentmeans_subshops`
--

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_subshops` (
  `paymentID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`paymentID`,`subshopID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_core_paymentmeans_subshops`
--

INSERT INTO `s_core_paymentmeans_subshops` (`paymentID`, `subshopID`) VALUES
(3, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugins`
--

CREATE TABLE IF NOT EXISTS `s_core_plugins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `label` varchar(255) NOT NULL,
  `source` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `description_long` text NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `installation_date` datetime DEFAULT NULL,
  `update_date` datetime DEFAULT NULL,
  `autor` varchar(255) DEFAULT NULL,
  `copyright` varchar(255) DEFAULT NULL,
  `license` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `support` varchar(255) NOT NULL,
  `changes` text NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

--
-- Daten für Tabelle `s_core_plugins`
--

INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
(1, 'Core', 'Log', 'Log', 'Default', '', '', 1, '2010-10-22 14:55:31', '2010-10-22 14:55:31', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(2, 'Core', 'ErrorHandler', 'ErrorHandler', 'Default', '', '', 1, '2010-10-22 14:55:23', '2010-10-22 14:55:23', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(3, 'Core', 'Debug', 'Debug', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(4, 'Core', 'BenchmarkEvents', 'BenchmarkEvents', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(5, 'Core', 'Benchmark', 'Benchmark', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(6, 'Core', 'Template', 'Template', 'Default', '', '', 1, '2010-10-22 14:56:16', '2010-10-22 14:56:16', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(7, 'Core', 'Cron', 'Cron', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(8, 'Core', 'Router', 'Router', 'Default', '', '', 1, '2010-10-22 14:55:47', '2010-10-22 14:55:47', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(9, 'Core', 'CronBirthday', 'CronBirthday', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(10, 'Core', 'System', 'System', 'Default', '', '', 1, '2010-10-22 14:56:05', '2010-10-22 14:56:05', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(11, 'Core', 'ViewportForward', 'ViewportForward', 'Default', '', '', 1, '2010-10-22 14:56:22', '2010-10-22 14:56:22', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(12, 'Core', 'Shop', 'Shop', 'Default', '', '', 1, '2010-10-22 14:55:57', '2010-10-22 14:55:57', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(13, 'Core', 'PostFilter', 'PostFilter', 'Default', '', '', 1, '2010-10-22 14:55:40', '2010-10-22 14:55:40', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(14, 'Core', 'CronRating', 'CronRating', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(15, 'Core', 'ControllerBase', 'ControllerBase', 'Default', '', '', 1, '2010-10-22 14:55:04', '2010-10-22 14:55:04', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(16, 'Core', 'CronStock', 'CronStock', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(17, 'Core', 'Api', 'Api', 'Default', '', '', 1, '2010-10-22 14:54:55', '2010-10-22 14:54:55', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(18, 'Core', 'License', 'License', 'Default', '', '', 1, '2010-10-22 14:56:39', '2010-10-22 14:56:39', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(19, 'Frontend', 'RouterRewrite', 'RouterRewrite', 'Default', '', '', 1, '2010-10-22 14:58:53', '2010-10-22 14:58:53', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(20, 'Frontend', 'Compare', 'Compare', 'Default', '', '', 1, '2010-10-22 14:57:52', '2010-10-22 14:57:52', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(21, 'Frontend', 'Facebook', 'Facebook', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(22, 'Frontend', 'Seo', 'Seo', 'Default', '', '', 1, '2010-10-22 14:58:04', '2010-10-22 14:58:04', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(23, 'Frontend', 'LastArticles', 'LastArticles', 'Default', '', '', 1, '2010-10-22 14:59:40', '2010-10-22 14:59:40', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(24, 'Frontend', 'RouterOld', 'RouterOld', 'Default', '', '', 1, '2010-10-22 14:59:09', '2010-10-22 14:59:09', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(25, 'Frontend', 'Ticket', 'Ticket', 'Default', '', '', 1, '2010-10-22 14:58:33', '2010-10-22 14:58:33', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(26, 'Frontend', 'Google', 'Google', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(27, 'Frontend', 'ViewportDispatcher', 'ViewportDispatcher', 'Default', '', '', 1, '2010-10-22 14:58:40', '2010-10-22 14:58:40', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(28, 'Frontend', 'Paypal', 'Paypal', 'Default', '', '', 1, '2010-10-22 14:59:16', '2010-10-22 14:59:16', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(29, 'Frontend', 'AdvancedMenu', 'AdvancedMenu', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(30, 'Frontend', 'CouponsSelling', 'CouponsSelling', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(31, 'Frontend', 'Statistics', 'Statistics', 'Default', '', '', 1, '2010-10-22 14:57:58', '2010-10-22 14:57:58', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(32, 'Frontend', 'Recommendation', 'Recommendation', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(33, 'Frontend', 'Notification', 'Notification', 'Default', '', '', 0, NULL, NULL, 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(34, 'Frontend', 'TagCloud', 'TagCloud', 'Default', '', '', 1, '2010-10-22 14:58:17', '2010-10-22 14:58:17', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(35, 'Frontend', 'InputFilter', 'InputFilter', 'Default', '', '', 1, '2010-10-22 14:59:25', '2010-10-22 14:59:25', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(36, 'Backend', 'Auth', 'Auth', 'Default', '', '', 1, '2010-10-22 14:54:40', '2010-10-22 14:54:40', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/'),
(37, 'Backend', 'Menu', 'Menu', 'Default', '', '', 1, '2010-10-22 14:54:48', '2010-10-22 14:54:48', 'shopware AG', 'Copyright © 2010, shopware AG', '', '1', 'http://www.shopware.de/wiki/', '', 'http://www.shopware.de/');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugin_configs`
--

CREATE TABLE IF NOT EXISTS `s_core_plugin_configs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `pluginID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Daten für Tabelle `s_core_plugin_configs`
--

INSERT INTO `s_core_plugin_configs` (`id`, `name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
(1, 'show', 's:1:"1";', 34, 1, 1),
(2, 'controller', 's:14:"index, listing";', 34, 1, 1),
(7, 'sql_protection', 's:1:"1";', 35, 1, 1),
(8, 'sql_regex', 's:134:"s_core|s_order|benchmark.*\\(|insert.+into|update.+set|delete.+from|select.+from|drop.+(?:table|database)|truncate.+table|union.+select";', 35, 1, 1),
(9, 'xss_protection', 's:1:"1";', 35, 1, 1),
(10, 'xss_regex', 's:42:"javascript:|src\\s*=|on[a-z]+\\s*=|style\\s*=";', 35, 1, 1),
(11, 'show', 's:1:"1";', 23, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_plugin_elements`
--

CREATE TABLE IF NOT EXISTS `s_core_plugin_elements` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `pluginID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `required` int(1) unsigned NOT NULL,
  `order` int(11) NOT NULL,
  `scope` int(11) unsigned NOT NULL,
  `filters` text,
  `validators` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pluginID` (`pluginID`,`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_core_plugin_elements`
--

INSERT INTO `s_core_plugin_elements` (`id`, `pluginID`, `name`, `value`, `label`, `description`, `type`, `required`, `order`, `scope`, `filters`, `validators`) VALUES
(1, 1, 'logDb', 'i:1;', 'Fehler in Datenbank schreiben', '', 'Checkbox', 0, 0, 0, NULL, NULL),
(2, 1, 'logMail', 's:1:"0";', 'Fehler an Shopbetreiber senden', '', 'Checkbox', 0, 0, 0, NULL, NULL),
(3, 34, 'show', 'i:1;', 'Tag-Cloud anzeigen', '', 'Checkbox', 0, 0, 1, NULL, NULL),
(4, 34, 'controller', 's:14:"index, listing";', 'Controller-Auswahl', '', 'Text', 0, 0, 1, NULL, NULL),
(5, 35, 'sql_protection', 'i:1;', 'SQL-Injection-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL),
(6, 35, 'sql_regex', 's:134:"s_core|s_order|benchmark.*\\(|insert.+into|update.+set|delete.+from|select.+from|drop.+(?:table|database)|truncate.+table|union.+select";', 'SQL-Injection-Filter', '', 'Text', 0, 0, 0, NULL, NULL),
(7, 35, 'xss_protection', 'i:1;', 'XSS-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL),
(8, 35, 'xss_regex', 's:42:"javascript:|src\\s*=|on[a-z]+\\s*=|style\\s*=";', 'XSS-Filter', '', 'Text', 0, 0, 0, NULL, NULL),
(9, 23, 'show', 'i:1;', 'Artikelverlauf anzeigen', '', 'Checkbox', 0, 0, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_pricegroups`
--

CREATE TABLE IF NOT EXISTS `s_core_pricegroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_pricegroups`
--

INSERT INTO `s_core_pricegroups` (`id`, `description`) VALUES
(1, 'Standard');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_pricegroups_discounts`
--

CREATE TABLE IF NOT EXISTS `s_core_pricegroups_discounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `customergroupID` int(11) NOT NULL,
  `discount` double NOT NULL,
  `discountstart` double NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`customergroupID`,`discountstart`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_core_pricegroups_discounts`
--

INSERT INTO `s_core_pricegroups_discounts` (`id`, `groupID`, `customergroupID`, `discount`, `discountstart`) VALUES
(9, 1, 1, 0, 1),
(5, 2, 1, 10, 1),
(6, 2, 2, 10, 1),
(7, 2, 3, 10, 1),
(8, 2, 4, 10, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_queries`
--

CREATE TABLE IF NOT EXISTS `s_core_queries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `sql` text NOT NULL,
  `v1name` varchar(255) NOT NULL,
  `v2name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Daten für Tabelle `s_core_queries`
--

INSERT INTO `s_core_queries` (`id`, `description`, `sql`, `v1name`, `v2name`) VALUES
(1, 'Alle Artikel mit Attribut X', 'SELECT \r\na.categoryID AS category,\r\na.id as articleID, \r\nordernumber,\r\ndatum,\r\na.active AS active, \r\nshippingfree, \r\naSupplier.name AS supplierName, \r\na.name AS articleName, \r\nprice, \r\nsales, \r\ntax,\r\nattr1,\r\nattr2,\r\nattr3,\r\nattr4,\r\nattr5,\r\nattr6,\r\nattr7,\r\nattr8,\r\nattr9,\r\nattr10,\r\nattr11,\r\nattr12,\r\nattr13,\r\nattr14,\r\nattr15,\r\nattr16,\r\nattr17,\r\nattr18,\r\nattr19,\r\nattr20,\r\ninstock\r\nFROM \r\ns_articles AS a,\r\ns_articles_supplier AS aSupplier, \r\ns_articles_details AS aDetails, \r\ns_articles_prices AS aPrices, \r\ns_core_tax AS aTax,\r\ns_articles_attributes AS aAttributes\r\nWHERE \r\na.taxID=aTax.id\r\nAND aAttributes.articledetailsID=aDetails.id\r\nAND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 \r\nAND aPrices.pricegroup=''EK'' AND aPrices.articleDetailsID=aDetails.id\r\nAND aPrices.to=''beliebig''\r\nAND attrV1 = ''V2''\r\nGROUP BY a.id ORDER BY a.datum DESC LIMIT 15', 'Attribut', 'Wert'),
(2, 'Alle ESD Artikel', 'SELECT \r\na.categoryID AS category,\r\na.id as articleID, \r\nordernumber,\r\na.active AS active, \r\nshippingfree, \r\naSupplier.name AS supplierName, \r\na.name AS articleName, \r\nprice, \r\nsales, \r\ntax,\r\nattr1,\r\nattr2,\r\nattr3,\r\nattr4,\r\nattr5,\r\nattr6,\r\nattr7,\r\nattr8,\r\nattr9,\r\nattr10,\r\nattr11,\r\nattr12,\r\nattr13,\r\nattr14,\r\nattr15,\r\nattr16,\r\nattr17,\r\nattr18,\r\nattr19,\r\nattr20,\r\ninstock\r\nFROM \r\ns_articles AS a,\r\ns_articles_supplier AS aSupplier, \r\ns_articles_details AS aDetails, \r\ns_articles_prices AS aPrices, \r\ns_articles_esd,\r\ns_core_tax AS aTax,\r\ns_articles_attributes AS aAttributes\r\nWHERE \r\na.taxID=aTax.id\r\nAND aAttributes.articledetailsID=aDetails.id\r\nAND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 \r\nAND aPrices.pricegroup=''EK'' AND aPrices.articleDetailsID=aDetails.id\r\nAND aPrices.to=''beliebig''\r\nAND s_articles_esd.articleID = a.id\r\nGROUP BY a.id \r\nORDER BY articleName ASC LIMIT 1500', '', ''),
(3, 'Alle noch nicht erschienenden Artikel', 'SELECT \r\na.id as articleID, \r\na.releasedate as value1,\r\nordernumber,\r\na.active AS active, \r\nshippingfree, \r\naSupplier.name AS supplierName, \r\na.name AS articleName, \r\nprice, \r\nsales, \r\ntax,\r\nattr1,\r\nattr2,\r\nattr3,\r\nattr4,\r\nattr5,\r\nattr6,\r\nattr7,\r\nattr8,\r\nattr9,\r\nattr10,\r\nattr11,\r\nattr12,\r\nattr13,\r\nattr14,\r\nattr15,\r\nattr16,\r\nattr17,\r\nattr18,\r\nattr19,\r\nattr20,\r\ninstock\r\nFROM \r\ns_articles AS a,\r\ns_articles_supplier AS aSupplier, \r\ns_articles_details AS aDetails, \r\ns_articles_prices AS aPrices, \r\ns_core_tax AS aTax,\r\ns_articles_attributes AS aAttributes\r\nWHERE \r\na.taxID=aTax.id\r\nAND aAttributes.articledetailsID=aDetails.id\r\nAND aSupplier.id=a.supplierID AND aDetails.articleID=a.id AND aDetails.kind=1 \r\nAND aPrices.pricegroup=''EK'' AND aPrices.articleDetailsID=aDetails.id\r\nAND aPrices.to=''beliebig''\r\nAND a.releasedate >= now()\r\nGROUP BY a.id \r\nORDER BY articleName ASC LIMIT 1500', '', ''),
(5, 'test', 'test', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rewrite`
--

CREATE TABLE IF NOT EXISTS `s_core_rewrite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `search` varchar(255) NOT NULL,
  `replace` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_core_rewrite`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rewrite_urls`
--

CREATE TABLE IF NOT EXISTS `s_core_rewrite_urls` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `org_path` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `main` int(1) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `path` (`path`,`subshopID`),
  KEY `org_path` (`org_path`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=81 ;

--
-- Daten für Tabelle `s_core_rewrite_urls`
--

INSERT INTO `s_core_rewrite_urls` (`id`, `org_path`, `path`, `main`, `subshopID`) VALUES
(1, 'sViewport=sale&sAction=doSale', 'Bestellung-abgeschlossen', 1, 1),
(2, 'sViewport=admin&sAction=orders', 'Meine-Bestellungen', 1, 1),
(3, 'sViewport=admin&sAction=downloads', 'Meine-Sofortdownloads', 1, 1),
(4, 'sViewport=admin&sAction=billing', 'Rechnungsadresse-aendern', 1, 1),
(5, 'sViewport=admin&sAction=shipping', 'Lieferadresse-aendern', 1, 1),
(6, 'sViewport=admin&sAction=payment', 'Zahlungsart-aendern', 1, 1),
(7, 'sViewport=admin&sAction=ticketview', 'Supportverwaltung', 1, 1),
(8, 'sViewport=logout', 'Vielen-Dank-fuer-Ihren-Besuch', 1, 1),
(9, 'sViewport=support&sFid=16&sInquiry=basket', 'Angebot-anfordern', 1, 1),
(10, 'sViewport=support&sFid=16&sInquiry=detail', 'Fragen-zum-Artikel', 1, 1),
(11, 'sViewport=cat', 'Kategorielisten', 1, 1),
(12, 'sViewport=detail', 'Artikeldetailseite', 1, 1),
(13, 'sViewport=custom', 'Statische-Seiten', 1, 1),
(14, 'sViewport=basket', 'Warenkorb', 1, 1),
(15, 'sViewport=login', 'Login', 1, 1),
(16, 'sViewport=newsletter', 'Newsletter', 1, 1),
(17, 'sViewport=register2', 'Rechnungsadresse', 1, 1),
(18, 'sViewport=register3', 'Registrierung-Zahlungsart', 1, 1),
(19, 'sViewport=register2shipping', 'Lieferanschrift', 1, 1),
(20, 'sViewport=sale', 'Bestellabschluss', 1, 1),
(21, 'sViewport=crossselling', 'Empfehlungen', 1, 1),
(22, 'sViewport=tellafriend', 'Artikel-weiterempfehlen', 1, 1),
(23, 'sViewport=admin', 'Ihr-Kundenkonto', 1, 1),
(24, 'sViewport=orders', 'Ihre-Bestellungen', 1, 1),
(25, 'sViewport=password', 'Passwort-vergessen', 1, 1),
(26, 'sViewport=content', 'Dynamische-Inhalte', 1, 1),
(27, 'sViewport=note', 'Merkzettel', 1, 1),
(28, 'sViewport=searchFuzzy', 'Suche', 1, 1),
(29, 'sViewport=sitemap', 'Sitemap', 1, 1),
(30, 'sViewport=cheaper', 'Artikel-guenstiger-gesehen', 1, 1),
(31, 'sViewport=registerFC', 'Registrierung-Start', 1, 1),
(32, 'sViewport=campaign', 'Aktion', 1, 1),
(33, 'sViewport=support', 'Support', 1, 1),
(34, 'sViewport=rma', 'rma', 1, 1),
(35, 'sViewport=ajax', 'Ajax-Funktionen', 1, 1),
(36, 'sViewport=paypalexpressGA', 'Paypal-Express-Guest-Account', 1, 1),
(37, 'sViewport=paypalexpressTXNPending', 'Paypal-Express-Order-Pending-Page', 1, 1),
(38, 'sViewport=paypalexpressGAReg', 'Paypal-Express-Guest-Account-Registration', 1, 1),
(39, 'sViewport=paypalexpressAPIError', 'Paypal-Express-API-Error', 1, 1),
(40, 'sViewport=ticket', 'Ticket-System-Formular', 1, 1),
(41, 'sViewport=ticketview', 'Ticket-Supportverwaltung', 1, 1),
(42, 'sViewport=ticketdirect', 'Direkte-Ticketanwort', 1, 1),
(43, 'sViewport=moneybookers_success', 'Moneybookers-Dankeseite', 1, 1),
(44, 'sViewport=moneybookers_fail', 'Moneybookers-Fehlerseite', 1, 1),
(45, 'sViewport=moneybookers_iframe', 'Moneybookers-IFrameseite', 1, 1),
(46, 'sViewport=hanseatic_success', 'Hanseatic-Dankeseite', 1, 1),
(47, 'sViewport=hanseatic_fail', 'Hanseatic-Fehlerseite', 1, 1),
(48, 'sViewport=hanseatic_iframe', 'Hanseatic-IFrameseite', 1, 1),
(49, 'sViewport=heidelpay_success', 'Heidelpay-Dankeseite', 1, 1),
(50, 'sViewport=heidelpay_cancel', 'Heidelpay-Abbruchseite', 1, 1),
(51, 'sViewport=heidelpay_fail', 'Heidelpay-Fehlerseite', 1, 1),
(52, 'sViewport=heidelpay_iframe', 'Heidelpay-IFrameseite', 1, 1),
(53, 'sViewport=newsletterListing', 'Newsletter-Archiv', 1, 1),
(54, 'sViewport=ticket&sFid=8', 'Partnerformular', 1, 1),
(55, 'sViewport=ticket&sFid=5', 'Kontaktformular', 1, 1),
(56, 'sViewport=ticket&sFid=9', 'Defektes-Produkt', 1, 1),
(80, 'sViewport=detail&sArticle=1', 'test/1/test', 1, 1),
(58, 'sViewport=ticket&sFid=16', 'Anfrage-Formular', 1, 1),
(59, 'sViewport=ticket&sFid=17', 'Partner-form', 1, 1),
(60, 'sViewport=ticket&sFid=18', 'Contact', 1, 1),
(61, 'sViewport=ticket&sFid=19', 'Defective-product', 1, 1),
(62, 'sViewport=ticket&sFid=20', 'Return', 1, 1),
(63, 'sViewport=ticket&sFid=21', 'Inquiry-form', 1, 1),
(64, 'sViewport=ticket&sFid=22', 'Support-beantragen', 1, 1),
(65, 'sViewport=custom&sCustom=2', 'Hilfe-Support', 1, 1),
(66, 'sViewport=custom&sCustom=3', 'Impressum', 1, 1),
(67, 'sViewport=custom&sCustom=4', 'AGB', 1, 1),
(68, 'sViewport=custom&sCustom=6', 'Versand-und-Zahlungsbedingungen', 1, 1),
(69, 'sViewport=custom&sCustom=7', 'Datenschutz', 1, 1),
(70, 'sViewport=custom&sCustom=8', 'Widerrufsrecht', 1, 1),
(71, 'sViewport=custom&sCustom=9', 'Ueber-uns', 1, 1),
(72, 'sViewport=custom&sCustom=28', 'Payment-Dispatch', 1, 1),
(73, 'sViewport=custom&sCustom=27', 'About-us', 1, 1),
(74, 'sViewport=custom&sCustom=29', 'Privacy', 1, 1),
(75, 'sViewport=custom&sCustom=30', 'Help-Support', 1, 1),
(76, 'sViewport=custom&sCustom=43', 'rechtliche-Vorabinformationen', 1, 1),
(77, 'sViewport=content&sContent=1', 'Aktuelles', 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_rulesets`
--

CREATE TABLE IF NOT EXISTS `s_core_rulesets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `paymentID` int(11) NOT NULL,
  `rule1` varchar(255) NOT NULL,
  `value1` varchar(255) NOT NULL,
  `rule2` varchar(255) NOT NULL,
  `value2` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_rulesets`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_sessions`
--

CREATE TABLE IF NOT EXISTS `s_core_sessions` (
  `id` varchar(64) NOT NULL,
  `expiry` int(11) unsigned NOT NULL,
  `expireref` varchar(255) DEFAULT NULL,
  `created` int(11) unsigned NOT NULL,
  `modified` int(11) unsigned NOT NULL,
  `data` longtext,
  PRIMARY KEY (`id`),
  KEY `expiry` (`expiry`),
  KEY `expireref` (`expireref`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_core_sessions`
--

INSERT INTO `s_core_sessions` (`id`, `expiry`, `expireref`, `created`, `modified`, `data`) VALUES
('ra098pl1kc7o8bmr5e3kt0tui5', 1440, NULL, 0, 1287359104, 'Shopware|N;'),
('qarbk689bhfupnu5999ndmog84', 1440, NULL, 0, 1287360469, 'Shopware|a:3:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:6:"orange";}s:14:"sUserGroupData";N;s:10:"sSupplier3";N;}'),
('1rshmte4gl0k6rcg8gl53rao46', 1440, NULL, 0, 1287360725, 'Shopware|a:3:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:4:"blue";}s:14:"sUserGroupData";N;s:10:"sSupplier3";N;}'),
('4tj0emcvatt9c1im33mlej65a7', 5400, NULL, 0, 1287366889, 'Shopware|a:3:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:4:"blue";}s:14:"sUserGroupData";N;s:10:"sSupplier3";N;}'),
('6lltps7bqcudea4e2e1govb595', 5400, NULL, 0, 1287391128, 'Shopware|a:9:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:4:"blue";}s:14:"sUserGroupData";N;s:12:"sLastArticle";i:1;s:8:"sPartner";N;s:8:"sCountry";i:2;s:9:"sRegister";C:11:"ArrayObject":59:{x:i:2;a:1:{s:7:"billing";a:1:{s:7:"country";i:2;}};m:a:0:{}}s:10:"sPaymentID";i:5;s:9:"sDispatch";i:9;s:10:"sSupplier3";N;}'),
('d5d3eullpb6fek2ifko555uq57', 1440, NULL, 0, 1287354938, 'Shopware|a:3:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:3:"red";}s:14:"sUserGroupData";N;s:10:"sSupplier3";N;}'),
('udstgaa24gj8u1gu2i05vbvp56', 5400, NULL, 0, 1287354014, 'Shopware|a:1:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:3:"red";}}'),
('m0jtoobo48gjp7at5rg89e0f97', 1440, NULL, 0, 1287354757, 'Shopware|a:16:{s:4:"Shop";O:20:"Shopware_Models_Shop":5:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";O:22:"Shopware_Models_Locale":2:{s:6:"\0*\0_id";i:1;s:10:"\0*\0_locale";s:5:"de_DE";}s:12:"\0*\0_currency";O:24:"Shopware_Models_Currency":2:{s:6:"\0*\0_id";i:1;s:11:"\0*\0_options";a:11:{s:8:"position";i:8;s:6:"script";N;s:6:"format";N;s:7:"display";i:2;s:9:"precision";i:2;s:4:"name";s:4:"Euro";s:8:"currency";s:3:"EUR";s:6:"symbol";s:3:"â‚¬";s:6:"locale";s:5:"de_DE";s:5:"value";i:0;s:7:"service";N;}}s:8:"\0*\0_host";s:14:"dev1.shopvm.de";s:12:"\0*\0_template";s:3:"red";}s:14:"sUserGroupData";a:9:{s:2:"id";s:1:"1";s:8:"groupkey";s:2:"EK";s:11:"description";s:10:"Shopkunden";s:3:"tax";s:1:"0";s:8:"taxinput";s:1:"0";s:4:"mode";s:1:"0";s:8:"discount";s:1:"0";s:12:"minimumorder";s:2:"10";s:21:"minimumordersurcharge";s:1:"5";}s:10:"sSupplier3";N;s:9:"sRegister";C:11:"ArrayObject":21:{x:i:2;a:0:{};m:a:0:{}}s:8:"sCountry";i:26;s:9:"sUserMail";s:14:"sk@shopware.de";s:13:"sUserPassword";s:32:"a15370144783d9da799fcfacb99a881d";s:7:"sUserId";s:1:"5";s:10:"sUserGroup";s:2:"EK";s:12:"sLastArticle";i:28;s:8:"sPartner";N;s:10:"sPaymentID";i:5;s:9:"sDispatch";i:6;s:8:"sReferer";N;s:15:"sOrderVariables";C:11:"ArrayObject":16223:{x:i:2;a:20:{s:9:"sUserData";a:3:{s:14:"billingaddress";a:23:{s:2:"id";s:1:"5";s:6:"userID";s:1:"5";s:7:"company";s:0:"";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:14:"customernumber";s:5:"20033";s:9:"firstname";s:9:"Sebastian";s:8:"lastname";s:7:"Klöpper";s:6:"street";s:11:"Hauptstraße";s:12:"streetnumber";s:2:"36";s:7:"zipcode";s:5:"48624";s:4:"city";s:11:"Schöppingen";s:5:"phone";s:12:"02555-997500";s:3:"fax";s:0:"";s:9:"countryID";s:2:"26";s:5:"ustid";s:0:"";s:5:"text1";s:0:"";s:5:"text2";s:0:"";s:5:"text3";s:0:"";s:5:"text4";s:0:"";s:5:"text5";s:0:"";s:5:"text6";s:0:"";s:8:"birthday";s:10:"1982-09-19";}s:10:"additional";a:6:{s:7:"country";a:13:{s:2:"id";s:2:"26";s:11:"countryname";s:7:"Schweiz";s:10:"countryiso";s:2:"CH";s:11:"countryarea";s:6:"europa";s:9:"countryen";s:11:"SWITZERLAND";s:8:"position";s:2:"10";s:6:"notice";s:0:"";s:12:"shippingfree";s:1:"0";s:7:"taxfree";s:1:"1";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"CHE";}s:4:"user";a:20:{s:2:"id";s:1:"5";s:8:"password";s:32:"a15370144783d9da799fcfacb99a881d";s:5:"email";s:14:"sk@shopware.de";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2010-09-27";s:9:"lastlogin";s:19:"2010-10-18 00:30:06";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";s:10:"newsletter";i:1;s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"5";s:8:"language";s:2:"de";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";}s:15:"countryShipping";a:13:{s:2:"id";s:2:"26";s:11:"countryname";s:7:"Schweiz";s:10:"countryiso";s:2:"CH";s:11:"countryarea";s:6:"europa";s:9:"countryen";s:11:"SWITZERLAND";s:8:"position";s:2:"10";s:6:"notice";s:0:"";s:12:"shippingfree";s:1:"0";s:7:"taxfree";s:1:"1";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"CHE";}s:7:"payment";a:16:{s:2:"id";s:1:"5";s:4:"name";s:10:"prepayment";s:11:"description";s:8:"Vorkasse";s:8:"template";s:14:"prepayment.tpl";s:5:"class";s:14:"prepayment.php";s:5:"table";s:0:"";s:4:"hide";s:1:"0";s:21:"additionaldescription";s:107:"Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.";s:13:"debit_percent";s:1:"0";s:9:"surcharge";s:1:"0";s:15:"surchargestring";s:0:"";s:8:"position";s:1:"1";s:6:"active";s:1:"1";s:9:"esdactive";s:1:"0";s:11:"embediframe";s:0:"";s:12:"hideprospect";s:1:"0";}s:10:"charge_vat";b:0;s:8:"show_net";b:0;}s:15:"shippingaddress";a:18:{s:2:"id";s:1:"2";s:6:"userID";s:1:"5";s:7:"company";s:11:"shopware AG";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:9:"Sebastian";s:8:"lastname";s:7:"Klöpper";s:6:"street";s:11:"Hauptstraße";s:12:"streetnumber";s:2:"36";s:7:"zipcode";s:5:"48624";s:4:"city";s:11:"Schöppingen";s:9:"countryID";s:2:"26";s:5:"text1";s:0:"";s:5:"text2";s:0:"";s:5:"text3";s:0:"";s:5:"text4";s:0:"";s:5:"text5";s:0:"";s:5:"text6";s:0:"";}}s:8:"sCountry";a:13:{s:2:"id";s:2:"26";s:11:"countryname";s:7:"Schweiz";s:10:"countryiso";s:2:"CH";s:11:"countryarea";s:6:"europa";s:9:"countryen";s:11:"SWITZERLAND";s:8:"position";s:2:"10";s:6:"notice";s:0:"";s:12:"shippingfree";s:1:"0";s:7:"taxfree";s:1:"1";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"CHE";}s:8:"sPayment";a:16:{s:2:"id";s:1:"5";s:4:"name";s:10:"prepayment";s:11:"description";s:8:"Vorkasse";s:8:"template";s:14:"prepayment.tpl";s:5:"class";s:14:"prepayment.php";s:5:"table";s:0:"";s:4:"hide";s:1:"0";s:21:"additionaldescription";s:107:"Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.";s:13:"debit_percent";s:1:"0";s:9:"surcharge";s:1:"0";s:15:"surchargestring";s:0:"";s:8:"position";s:1:"1";s:6:"active";s:1:"1";s:9:"esdactive";s:1:"0";s:11:"embediframe";s:0:"";s:12:"hideprospect";s:1:"0";}s:9:"sDispatch";a:27:{s:2:"id";s:1:"6";s:4:"name";s:15:"Express Versand";s:11:"description";s:0:"";s:11:"calculation";s:1:"1";s:11:"status_link";s:0:"";s:7:"instock";s:1:"0";s:8:"stockmin";s:1:"0";s:9:"laststock";s:1:"0";s:6:"weight";s:1:"0";s:13:"count_article";s:1:"1";s:12:"shippingfree";s:1:"0";s:6:"amount";s:5:"130.9";s:10:"amount_net";s:3:"110";s:14:"amount_display";s:5:"55.00";s:7:"max_tax";s:2:"19";s:6:"userID";s:1:"5";s:13:"has_topseller";s:1:"0";s:11:"has_comment";s:0:"";s:7:"has_esd";s:1:"0";s:20:"calculation_value_10";s:1:"0";s:20:"calculation_value_11";s:1:"0";s:20:"calculation_value_13";s:1:"0";s:9:"countryID";s:2:"26";s:9:"paymentID";s:1:"5";s:15:"customergroupID";s:1:"1";s:11:"multishopID";s:1:"1";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";}s:11:"sDispatches";a:2:{i:6;a:27:{s:2:"id";s:1:"6";s:4:"name";s:15:"Express Versand";s:11:"description";s:0:"";s:11:"calculation";s:1:"1";s:11:"status_link";s:0:"";s:7:"instock";s:1:"0";s:8:"stockmin";s:1:"0";s:9:"laststock";s:1:"0";s:6:"weight";s:1:"0";s:13:"count_article";s:1:"1";s:12:"shippingfree";s:1:"0";s:6:"amount";s:5:"130.9";s:10:"amount_net";s:3:"110";s:14:"amount_display";s:5:"55.00";s:7:"max_tax";s:2:"19";s:6:"userID";s:1:"5";s:13:"has_topseller";s:1:"0";s:11:"has_comment";s:0:"";s:7:"has_esd";s:1:"0";s:20:"calculation_value_10";s:1:"0";s:20:"calculation_value_11";s:1:"0";s:20:"calculation_value_13";s:1:"0";s:9:"countryID";s:2:"26";s:9:"paymentID";s:1:"5";s:15:"customergroupID";s:1:"1";s:11:"multishopID";s:1:"1";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";}i:9;a:27:{s:2:"id";s:1:"9";s:4:"name";s:14:"Normal Versand";s:11:"description";s:0:"";s:11:"calculation";s:1:"1";s:11:"status_link";s:228:"<a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&zip=&idc={$offerPosition.trackingcode}" onclick="return !window.open(this.href, ''popup'', ''width=500,height=600,left=20,top=20'');" target="_blank">hier</a>";s:7:"instock";s:1:"0";s:8:"stockmin";s:1:"0";s:9:"laststock";s:1:"0";s:6:"weight";s:1:"0";s:13:"count_article";s:1:"1";s:12:"shippingfree";s:1:"0";s:6:"amount";s:5:"130.9";s:10:"amount_net";s:3:"110";s:14:"amount_display";s:5:"55.00";s:7:"max_tax";s:2:"19";s:6:"userID";s:1:"5";s:13:"has_topseller";s:1:"0";s:11:"has_comment";s:0:"";s:7:"has_esd";s:1:"0";s:20:"calculation_value_10";s:1:"0";s:20:"calculation_value_11";s:1:"0";s:20:"calculation_value_13";s:1:"0";s:9:"countryID";s:2:"26";s:9:"paymentID";s:1:"5";s:15:"customergroupID";s:1:"1";s:11:"multishopID";s:1:"1";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";}}s:7:"sBasket";a:18:{s:7:"content";a:2:{i:0;a:52:{s:2:"id";s:4:"4027";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";s:6:"userID";s:1:"5";s:11:"articlename";s:35:"Jeans &quot;Style&quot; Größe 30/32";s:9:"articleID";s:2:"28";s:11:"ordernumber";s:6:"SW2026";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:6:"110,00";s:8:"netprice";s:3:"110";s:5:"datum";s:19:"2010-10-18 00:29:49";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:8:"checkout";s:9:"useragent";s:87:"Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"ob_attr1";s:0:"";s:8:"ob_attr2";s:0:"";s:8:"ob_attr3";s:0:"";s:8:"ob_attr4";s:0:"";s:8:"ob_attr5";s:0:"";s:8:"ob_attr6";s:0:"";s:14:"liveshoppingID";s:1:"0";s:8:"bundleID";s:1:"0";s:23:"bundle_join_ordernumber";s:0:"";s:8:"packunit";s:0:"";s:11:"minpurchase";i:1;s:5:"taxID";s:1:"1";s:7:"instock";s:2:"-1";s:14:"suppliernumber";s:0:"";s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";s:1:"0";s:9:"laststock";s:1:"0";s:12:"shippingtime";s:0:"";s:11:"releasedate";s:0:"";s:12:"sReleaseDate";s:0:"";s:8:"stockmin";s:1:"0";s:3:"esd";s:1:"0";s:8:"itemUnit";N;s:12:"shippinginfo";b:1;s:13:"amountWithTax";d:130.900000000000005684341886080801486968994140625;s:6:"amount";s:6:"110,00";s:9:"amountnet";s:6:"110,00";s:12:"priceNumeric";s:3:"110";s:5:"image";a:5:{s:3:"src";a:7:{s:8:"original";s:77:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba.jpg";i:0;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_0.jpg";i:1;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_1.jpg";i:2;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_2.jpg";i:3;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_3.jpg";i:4;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_4.jpg";i:5;s:79:"http://dev1.shopvm.de/images/articles/28_5a42f921cd26df3d4ed430f4565cb0ba_5.jpg";}s:3:"res";a:3:{s:8:"original";a:2:{s:5:"width";s:3:"600";s:6:"height";s:3:"800";}s:11:"description";s:0:"";s:9:"relations";s:0:"";}s:8:"position";s:1:"0";s:9:"extension";s:3:"jpg";s:4:"main";s:1:"1";}s:11:"linkDetails";s:41:"shopware.php?sViewport=detail&sArticle=28";s:10:"linkDelete";s:42:"shopware.php?sViewport=basket&sDelete=4027";s:8:"linkNote";s:39:"shopware.php?sViewport=note&sAdd=SW2026";s:3:"tax";s:5:"20,90";}i:1;a:51:{s:2:"id";s:4:"4030";s:9:"sessionID";s:26:"m0jtoobo48gjp7at5rg89e0f97";s:6:"userID";s:1:"5";s:11:"articlename";s:22:"- 50 % Warenkorbrabatt";s:9:"articleID";s:1:"0";s:11:"ordernumber";s:11:"sw-discount";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:6:"-55,00";s:8:"netprice";s:3:"-55";s:5:"datum";s:19:"2010-10-18 00:30:06";s:5:"modus";s:1:"3";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:8:"checkout";s:9:"useragent";s:87:"Mozilla/5.0 (Windows; U; Windows NT 6.1; de; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"ob_attr1";s:0:"";s:8:"ob_attr2";s:0:"";s:8:"ob_attr3";s:0:"";s:8:"ob_attr4";s:0:"";s:8:"ob_attr5";s:0:"";s:8:"ob_attr6";s:0:"";s:14:"liveshoppingID";s:1:"0";s:8:"bundleID";s:1:"0";s:23:"bundle_join_ordernumber";s:0:"";s:8:"packunit";N;s:11:"minpurchase";i:1;s:5:"taxID";N;s:7:"instock";N;s:14:"suppliernumber";N;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:9:"laststock";N;s:12:"shippingtime";N;s:11:"releasedate";N;s:12:"sReleaseDate";N;s:8:"stockmin";N;s:3:"esd";s:1:"0";s:8:"itemUnit";N;s:12:"shippinginfo";b:0;s:13:"amountWithTax";d:-65.4500000000000028421709430404007434844970703125;s:6:"amount";s:6:"-55,00";s:9:"amountnet";s:6:"-55,00";s:12:"priceNumeric";s:3:"-55";s:11:"linkDetails";s:40:"shopware.php?sViewport=detail&sArticle=0";s:10:"linkDelete";s:42:"shopware.php?sViewport=basket&sDelete=4030";s:8:"linkNote";s:44:"shopware.php?sViewport=note&sAdd=sw-discount";s:3:"tax";s:6:"-10,45";}}s:6:"Amount";s:5:"55,00";s:9:"AmountNet";s:5:"55,00";s:8:"Quantity";i:1;s:13:"AmountNumeric";d:72;s:16:"AmountNetNumeric";d:69.289999999999992041921359486877918243408203125;s:13:"AmountWithTax";s:5:"65,45";s:20:"AmountWithTaxNumeric";d:82.4500000000000028421709430404007434844970703125;s:18:"sLastActiveArticle";a:2:{s:2:"id";i:28;s:4:"link";s:41:"shopware.php?sViewport=detail&sDetails=28";}s:21:"sShippingcostsWithTax";d:17;s:17:"sShippingcostsNet";d:14.28999999999999914734871708787977695465087890625;s:17:"sShippingcostsTax";s:2:"19";s:24:"sShippingcostsDifference";N;s:9:"sTaxRates";a:1:{i:19;d:13.160000000000000142108547152020037174224853515625;}s:14:"sShippingcosts";d:14.28999999999999914734871708787977695465087890625;s:7:"sAmount";d:69.2900000000000062527760746888816356658935546875;s:10:"sAmountTax";d:13.160000000000000142108547152020037174224853515625;s:14:"sAmountWithTax";d:82.4500000000000028421709430404007434844970703125;}s:10:"sLaststock";a:2:{s:10:"hideBasket";b:0;s:8:"articles";a:1:{s:0:"";a:1:{s:10:"OutOfStock";b:0;}}}s:14:"sShippingcosts";d:14.28999999999999914734871708787977695465087890625;s:24:"sShippingcostsDifference";N;s:7:"sAmount";d:69.2900000000000062527760746888816356658935546875;s:14:"sAmountWithTax";d:82.4500000000000028421709430404007434844970703125;s:10:"sAmountTax";d:13.160000000000000142108547152020037174224853515625;s:10:"sAmountNet";d:69.289999999999992041921359486877918243408203125;s:9:"sPremiums";a:1:{i:0;a:7:{s:19:"premium_ordernumber";s:1:"5";s:10:"startprice";s:5:"10,00";s:9:"subshopID";s:1:"0";s:9:"articleID";s:2:"14";s:9:"available";s:1:"1";s:8:"sArticle";a:59:{s:9:"articleID";s:2:"14";s:16:"articleDetailsID";s:2:"17";s:11:"ordernumber";s:6:"SW2006";s:5:"datum";s:10:"2009-09-21";s:5:"sales";s:1:"1";s:9:"highlight";s:1:"0";s:11:"description";s:0:"";s:16:"description_long";s:1000:"Osus ut exstasis Arma eu sto se Simulatio en volubiliter, sed Crocinus mos Absit iam Cunctator transfero. Dux Vehiculum, se vicis Incol se nex incontinencia, exigo era Palus sum iam magnificabiliter loci, sal incurro, dux necessarius Negotium os orbis, era alatus ineo, vel loquor, hic sed, Viva tam. Ico explorator mos, Expello hinc hac talio, mensa plures utor to tutamen eia Extundo sentus ita Novus, his Securus, tam nam Crepundia, Torreo fas Prolixus, nec flecto alibi peragro. Nam Deficio contradictio ops Posco laeto aeger Freno ruo, votum Spero ergo Penetro, Pulmo pro, ops infra Vacuus ususfructus qui Perturpis, neco Illas his Libro. Vel emo mons liberalis longe vir ingredior, sui cautor Concito, far, Comitatus mus Ambiguus palma via Degenero pio ala Imputo. Pudeo teneo triticeus, iam conjuratio, regno re oro Aveho curiositas cicuta, dis Occulte, deprecativus typus caterva pauci, his re supermitto. Ruo in divortium ita sesquimellesimus Oppono plus celo sceptrum, res ingustatus hi An.";s:12:"supplierName";s:12:"Helly Hansen";s:11:"supplierImg";s:36:"6031d9dc3e4c21c0e4e58e0e3a37d153.jpg";s:11:"articleName";s:17:"Barrier Softshell";s:5:"price";s:6:"119,00";s:11:"pseudoprice";s:1:"0";s:3:"tax";s:2:"19";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";s:10:"0000-00-00";s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:7:"instock";s:2:"-1";s:6:"weight";s:1:"0";s:12:"shippingtime";s:0:"";s:10:"pricegroup";s:2:"EK";s:12:"pricegroupID";s:1:"1";s:16:"pricegroupActive";s:1:"0";s:13:"filtergroupID";s:1:"0";s:12:"purchaseunit";s:1:"0";s:13:"referenceunit";s:1:"0";s:6:"unitID";s:1:"0";s:9:"laststock";s:1:"0";s:14:"additionaltext";s:7:"Größe S";s:13:"sConfigurator";s:1:"0";s:3:"esd";s:1:"0";s:13:"sVoteAverange";a:2:{s:8:"averange";d:0;s:5:"count";d:0;}s:10:"newArticle";s:1:"0";s:9:"topseller";s:1:"0";s:9:"sUpcoming";s:1:"0";s:12:"sReleasedate";s:0:"";s:15:"sVariantArticle";b:1;s:17:"priceStartingFrom";i:0;s:5:"image";a:5:{s:3:"src";a:7:{s:8:"original";s:77:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f.jpg";i:0;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_0.jpg";i:1;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_1.jpg";i:2;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_2.jpg";i:3;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_3.jpg";i:4;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_4.jpg";i:5;s:79:"http://dev1.shopvm.de/images/articles/14_0ea72b18b478503812587ab3566ac76f_5.jpg";}s:3:"res";a:3:{s:8:"original";a:2:{s:5:"width";s:4:"4000";s:6:"height";s:4:"4000";}s:11:"description";s:0:"";s:9:"relations";s:0:"";}s:8:"position";s:1:"0";s:9:"extension";s:3:"jpg";s:4:"main";s:1:"1";}s:10:"linkBasket";s:41:"shopware.php?sViewport=basket&sAdd=SW2006";s:11:"linkDetails";s:41:"shopware.php?sViewport=detail&sArticle=14";s:4:"mode";s:3:"fix";}s:9:"sVariants";a:4:{i:0;a:2:{s:11:"ordernumber";s:6:"SW2006";s:14:"additionaltext";s:7:"Größe S";}i:1;a:2:{s:11:"ordernumber";s:6:"SW2007";s:14:"additionaltext";s:7:"Größe M";}i:2;a:2:{s:11:"ordernumber";s:6:"SW2009";s:14:"additionaltext";s:7:"Größe L";}i:3;a:2:{s:11:"ordernumber";s:6:"SW2010";s:14:"additionaltext";s:8:"Größe XL";}}}}s:11:"sNewsletter";N;s:8:"sComment";N;s:12:"sShowEsdNote";b:0;s:16:"sDispatchNoOrder";b:0;s:11:"ordernumber";i:10175;s:12:"sOrderNumber";i:10175;};m:a:0:{}}s:8:"sComment";s:0:"";}');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_snippets`
--

CREATE TABLE IF NOT EXISTS `s_core_snippets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `created` datetime NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `namespace` (`namespace`,`shopID`,`name`,`localeID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1551 ;

--
-- Daten für Tabelle `s_core_snippets`
--

INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1, 'backend/error/index', 1, 1, 'ErrorIndexTitle', 'Fehler', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(2, 'frontend/index/header', 1, 1, 'IndexMetaRobots', 'index,follow', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(3, 'frontend/index/header', 1, 1, 'IndexMetaRevisit', '15 days', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(4, 'frontend/index/header', 1, 1, 'IndexMetaKeywordsStandard', '', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(5, 'frontend/index/header', 1, 1, 'IndexMetaDescriptionStandard', '', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(16, 'frontend/register/index', 1, 1, 'RegisterTitle', 'Registrierung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(17, 'frontend/blog/detail', 1, 1, 'BlogHeaderSocialmedia', 'Weiterempfehlen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(18, 'frontend/index/breadcrumb', 1, 1, 'BreadcrumbDefault', 'Sie sind hier:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(19, 'frontend/plugins/index/viewlast', 1, 1, 'WidgetsRecentlyViewedHeadline', 'Angeschaut', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(20, 'frontend/plugins/index/viewlast', 1, 1, 'WidgetsRecentlyViewedLinkDetails', 'Mehr Informationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(21, 'frontend/blog/box', 1, 1, 'BlogInfoFrom', 'Von:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(22, 'frontend/blog/box', 1, 1, 'BlogInfoComments', 'Kommentare', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(23, 'frontend/blog/box', 1, 1, 'BlogLinkMore', 'Mehr lesen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(1483, 'backend/plugins/coupons/pdf/index', 1, 1, 'PluginsBackendCouponsCharge', 'Bitte beachten Sie den Mindestbestellwert von {$coupon.minimumcharge|currency}\r\n					', '2010-10-11 15:43:46', '2010-10-11 15:43:46'),
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
(49, 'frontend/detail/data', 1, 1, 'DetailDataId', 'Bestell-Nr.:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(1482, 'backend/plugins/coupons/pdf/index', 1, 1, 'PluginsBackendCouponsInfo', 'Der Gutschein ist gültig bis zum \r\n				', '2010-10-11 15:38:57', '2010-10-11 15:38:57'),
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
(226, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLabelOrdernumber', 'Bestellnummer', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(246, 'frontend/register/steps', 1, 1, 'CheckoutStepBasketText', 'Warenkorb', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(247, 'frontend/search/fuzzy', 1, 1, 'SearchFuzzyHeadlineEmpty', 'Leider wurden zu "{$sRequests.sSearchOrginal}" keine Artikel gefunden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(248, 'frontend/register/steps', 1, 1, 'CheckoutStepRegisterNumber', '2', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(249, 'frontend/register/steps', 1, 1, 'CheckoutStepRegisterText', 'Registrierung', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(250, 'frontend/index/index', 1, 1, 'IndexRealizedWith', 'Realisiert mit ', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(251, 'frontend/index/menu_left', 1, 1, 'MenuLeftHeading', 'Informationen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(252, 'frontend/widgets/advanced_menu/index', 1, 1, 'IndexLinkHome', 'Home', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(253, 'frontend/widgets/compare/index', 1, 1, 'ListingBoxLinkCompare', 'Vergleichen', '2010-01-01 00:00:00', '2010-10-17 19:01:26'),
(254, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlinePrice', 'nach Preis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(255, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineSupplier', 'nach Hersteller', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(281, 'frontend/detail/tabs', 1, 1, 'DetailTabsAccessories ', 'Zubehör', '2010-01-01 00:00:00', '2010-10-17 22:14:22'),
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
(328, 'frontend/checkout/cart', 1, 1, 'CartInfoFreeShippingDifference', '- Bestellen Sie für weitere {$sShippingcostsDifference|currency} um Ihre Bestellung versandkostenfrei zu erhalten!', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(329, 'frontend/checkout/cart_header', 1, 1, 'CartColumnName', 'Artikel', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(330, 'frontend/checkout/cart_header', 1, 1, 'CartColumnAvailability', 'Verfügbarkeit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(331, 'frontend/checkout/cart_header', 1, 1, 'CartColumnPrice', 'Stückpreis', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(332, 'frontend/checkout/cart_item', 1, 1, 'CartItemInfoId', 'Bestell-Nr.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(333, 'frontend/checkout/cart_item', 1, 1, 'CartItemLinkDelete', 'Löschen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(334, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterActionAddVoucher', 'Hinzufügen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(335, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterLabelAddArticle', 'Artikel hinzufügen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(336, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterIdLabelInline', 'Bestell-Nr.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(347, 'frontend/checkout/confirm', 1, 1, 'ConfirmTerms', 'Ich habe die <a href="{url controller=custom sCustom=4}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.', '2010-01-01 00:00:00', '2010-10-07 23:31:45'),
(348, 'frontend/checkout/confirm', 1, 1, 'ConfirmTextOrderDefault', 'Optionaler FreitextBei Zahlung per Bankeinzug oder per Kreditkarte erfolgt die Belastung Ihres Kontos fünf Tage nach Bestellung der Ware.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(349, 'frontend/checkout/confirm', 1, 1, 'ConfirmActionSubmit', 'Bestellung absenden', '2010-01-01 00:00:00', '2010-10-16 16:51:57'),
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
(1392, 'documents/index', 1, 1, 'DocumentIndexVoucher', '\n						Für den nächsten Einkauf schenken wir Ihnen einen {$Document.voucher.value} {$Document.voucher.prefix} Gutschein\n						mit dem Code "{$Document.voucher.code}".<br />\n					', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(365, 'frontend/checkout/actions', 1, 1, 'CheckoutActionsLinkOffer', 'Angebot anfordern', '2010-01-01 00:00:00', '2010-09-28 11:54:19');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
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
(382, 'frontend/account/downloads', 1, 1, 'DownloadsColumnLink', 'Download', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(402, 'frontend/plugins/trusted_shops/logo', 1, 1, 'WidgetsTrustedLogoText', '{$this->config(''sShopname'')} ist ein von Trusted Shops geprüfter Onlinehändler mit Gütesiegel und Käuferschutz.', '2010-01-01 00:00:00', '2010-10-17 19:02:20'),
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
(430, 'frontend/account/select_billing', 1, 1, 'SelectBillingInfoEmpty', 'Nachdem Sie die erste Bestellung durchgeführt haben, können Sie hier auf vorherige Rechnnungsadressen zugreifen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(474, 'frontend/compare/added', 1, 1, 'CompareInfoMaxReached', 'Es können maximal 5 Artikel in einem Schritt verglichen werden', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(475, 'frontend/newsletter/index', 1, 1, 'NewsletterLabelSelect', 'Bitte wählen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(476, 'frontend/detail/data', 1, 1, 'DetailDataPriceInfo', 'Preise {if $this->config(''sARTICLESOUTPUTNETTO'') == true}zzgl.{else}inkl.{/if} gesetzlicher MwSt. <a title="Versandkosten" href="{url controller=custom sCustom=6}" style="text-decoration:underline">zzgl. Versandkosten</a>', '2010-01-01 00:00:00', '2010-10-16 08:52:39'),
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
(536, 'frontend/register/steps', 1, 1, 'CheckoutStepConfirmText', 'Bestellung abschließen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(1431, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLinkConfirm', 'Zur Kasse', '2010-10-06 19:29:43', '2010-10-15 17:02:07'),
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
(590, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoInstock', 'Sofort versandfertig,<br/>\r\nLieferzeit ca. 1-3 Werktage', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(591, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataShippingtime', 'Lieferzeit', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(592, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoInstantDownload', 'Als Sofortdownload verfügbar', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(593, 'frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoShipping', 'Lieferbar ab', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(594, 'frontend', 1, 1, 'RegisterPasswordLength', 'Bitte wählen Sie ein Passwort welches aus mindestens {config name="MinPassword"} Zeichen besteht.', '2010-01-01 00:00:00', '2010-10-12 19:45:13'),
(595, 'frontend', 1, 1, 'RegisterAjaxEmailNotEqual', 'Die eMail-Adressen stimmen nicht überein.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(596, 'frontend', 1, 1, 'RegisterAjaxEmailNotValid', 'Bitte geben Sie eine gültige eMail-Adresse ein.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
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
(638, 'frontend/index/footer', 1, 1, 'IndexCopyright', 'Copyright &copy; 2010 shopware.ag - Alle Rechte vorbehalten.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(639, 'frontend/detail/header', 1, 1, 'DetailChooseFirst', 'Bitte wählen Sie zuerst eine Variante aus', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(640, 'frontend/custom/ajax', 1, 1, 'CustomAjaxActionClose', 'Schliessen', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(641, 'frontend', 1, 1, 'sMailConfirmation', 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet. Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestästigen.', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(642, 'frontend', 1, 1, 'AccountLoginTitle', 'Login', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(643, 'frontend/ticket/listing', 1, 1, 'TicketInfoDate', 'Datum', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(644, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterLabelAddVoucher', 'Gutschein hinzufügen:', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(645, 'frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterAddVoucherLabelInline', 'Gutschein-Nummer', '2010-01-01 00:00:00', '2010-09-28 11:54:19'),
(1394, 'documents/index', 1, 1, 'DocumentIndexSelectedDispatch', 'Gewählte Versandart:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(649, 'frontend/checkout/confirm', 1, 1, 'ConfirmTextRightOfRevocation', 'Informationen zum Widerrufsrecht [Füllen / Textbaustein]', '2010-09-23 21:23:42', '2010-10-15 13:23:33'),
(650, 'frontend/account/billing', 1, 1, 'BillingLinkSend', 'Ändern', '2010-09-23 21:23:52', '2010-09-28 11:54:19'),
(1395, 'documents/index', 1, 1, 'DocumentIndexCurrency', '\n					<br>Euro Umrechnungsfaktor: {$Order._currency.factor|replace:".":","}\n					', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1383, 'documents/index', 1, 1, 'DocumentIndexHeadPrice', 'Brutto Preis', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1384, 'documents/index', 1, 1, 'DocumentIndexHeadAmount', 'Brutto Gesamt', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1385, 'documents/index', 1, 1, 'DocumentIndexHeadNet', 'Netto Preis', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1386, 'documents/index', 1, 1, 'DocumentIndexHeadNetAmount', 'Netto Gesamt', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1387, 'documents/index', 1, 1, 'DocumentIndexTotalNet', 'Gesamtkosten Netto:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(659, 'frontend/index/index', 1, 1, 'IndexNoscriptNotice', 'Um {$sShopname} in vollen Umfang nutzen zu k&ouml;nnen, empfehlen wir Ihnen Javascript in Ihren Browser zu aktiveren.', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(660, 'frontend/index/index', 1, 1, 'IndexRealizedShopsystem', 'Shopware', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(661, 'frontend/index/header', 1, 1, 'IndexMetaHttpContentType', 'text/html; charset=iso-8859-1', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(662, 'frontend/index/header', 1, 1, 'IndexMetaAuthor', '', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(663, 'frontend/index/header', 1, 1, 'IndexMetaCopyright', '', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(664, 'frontend/index/header', 1, 1, 'IndexMetaMsNavButtonColor', '#dd4800', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(665, 'frontend/index/header', 1, 1, 'IndexMetaShortcutIcon', '{link file=''resources/favicon.ico''}', '2010-09-23 14:38:10', '2010-09-28 11:54:19'),
(1393, 'documents/index', 1, 1, 'DocumentIndexComment', 'Kommentar:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(669, 'frontend/error/index', 1, 1, 'ErrorIndexTitle', 'Es ist ein Fehler aufgetreten', '2010-09-23 14:38:13', '2010-10-15 13:26:59'),
(1388, 'documents/index', 1, 1, 'DocumentIndexTax', 'zzgl. {$key} MwSt:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1389, 'documents/index', 1, 1, 'DocumentIndexTotal', 'Gesamtkosten:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1390, 'documents/index', 1, 1, 'DocumentIndexAdviceNet', 'Hinweis: Der Empfänger der Leistung schuldet die Steuer.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1391, 'documents/index', 1, 1, 'DocumentIndexSelectedPayment', 'Gew&auml;hlte Zahlungsart', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1377, 'documents/index', 1, 1, 'DocumentIndexPageCounter', 'Seite {$page+1} von {$Pages|@count}', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1378, 'documents/index', 1, 1, 'DocumentIndexHeadPosition', 'Pos.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1379, 'documents/index', 1, 1, 'DocumentIndexHeadArticleID', 'Art-Nr.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1380, 'documents/index', 1, 1, 'DocumentIndexHeadName', 'Bezeichnung', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1381, 'documents/index', 1, 1, 'DocumentIndexHeadQuantity', 'Anz.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1382, 'documents/index', 1, 1, 'DocumentIndexHeadTax', 'MwSt.', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1517, 'frontend/account/order_item.tpl', 1, 1, 'OrderItemComment', 'Unser Kommentar', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(1516, 'frontend/account/order_item.tpl', 1, 1, 'OrderItemCustomerComment', 'Ihr Kommentar', '2010-01-01 00:00:00', '2010-01-01 00:00:00'),
(684, 'frontend/compare/overlay', 1, 1, 'CompareLinkPrint', 'Drucken', '2010-09-23 21:28:38', '2010-09-28 11:54:19'),
(685, 'frontend/detail/index', 1, 1, 'DetailChooseFirst', '', '2010-09-24 10:39:45', '2010-09-28 11:54:19'),
(686, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'ListingBoxArticleStartsAt', 'ab', '2010-09-24 14:59:39', '2010-09-28 11:54:19'),
(1373, 'documents/index', 1, 1, 'DocumentIndexOrderID', 'Bestell-Nr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1374, 'documents/index', 1, 1, 'DocumentIndexDate', 'Datum:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1375, 'documents/index', 1, 1, 'DocumentIndexDeliveryDate', 'Liefertermin:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1376, 'documents/index', 1, 1, 'DocumentIndexInvoiceNumber', 'Rechnung Nr. {$Document.id}', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(692, 'frontend/search/fuzzy', 1, 1, 'SearchHeadline', 'Zu "{$sRequests.sSearch}" wurden {$sSearchResults.sArticlesCount} Artikel gefunden!', '2010-09-25 12:28:44', '2010-09-28 11:54:19'),
(1511, 'backend/plugin/skeleton', 1, 1, 'WindowTitle', 'Plugin Manager', '2010-10-13 22:40:31', '2010-10-13 22:40:31'),
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
(709, 'backend/index/header', 1, 1, 'IndexTitle', '', '2010-09-26 13:00:05', '2010-09-28 11:54:19'),
(710, 'frontend/ticket/detail', 1, 1, 'TicketDetailLinkBack', 'Zurück', '2010-09-26 15:14:11', '2010-09-28 11:54:19'),
(711, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsOffersLink', 'Weitere Artikel in dieser Kategorie &raquo;', '2010-09-26 15:18:42', '2010-09-28 11:54:19'),
(712, 'frontend/newsletter/listing', 1, 1, 'NewsletterListingHeaderName', 'Name', '2010-09-27 09:52:02', '2010-09-28 11:54:19'),
(713, 'backend/snippet/skeleton', 1, 1, 'WindowTitle', '', '2010-09-27 14:54:29', '2010-09-28 11:54:19'),
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
(724, 'frontend/detail/similar', 6, 2, 'DetailSimilarHeader', 'related items', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(725, 'frontend/detail/comment', 6, 2, 'DetailCommentActionSave', 'save', '0000-00-00 00:00:00', '2010-09-28 11:54:19');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(726, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoFields', 'All boxes marked with * must be filled out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(727, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelCaptcha', 'Please type in this number sequence in the upcoming input box', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(728, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelText', 'your opinion', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(729, 'frontend/detail/comment', 6, 2, 'Rate1', '1 miserable', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(730, 'frontend/detail/comment', 6, 2, 'Rate2', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(731, 'frontend/detail/comment', 6, 2, 'Rate3', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(732, 'frontend/detail/comment', 6, 2, 'Rate4', '4', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(733, 'frontend/detail/comment', 6, 2, 'Rate5', '5', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(734, 'frontend/detail/comment', 6, 2, 'Rate6', '6', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(735, 'frontend/detail/comment', 6, 2, 'Rate7', '7', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(736, 'frontend/detail/comment', 6, 2, 'Rate8', '8', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(737, 'frontend/detail/comment', 6, 2, 'Rate9', '9', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(738, 'frontend/detail/comment', 6, 2, 'Rate10', '10 excellent', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(739, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(740, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelSummary', 'summary', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(741, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelName', 'your name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(742, 'frontend/detail/comment', 6, 2, 'DetailCommentTextReview', 'reviews will be published after being checked', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(743, 'frontend/detail/comment', 6, 2, 'DetailCommentHeaderWriteReview', 'write a review', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(744, 'frontend/blog/detail', 6, 2, 'BlogHeaderLinks', 'more information about', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(745, 'frontend/detail/comment', 6, 2, 'DetailCommentHeader', 'customer reviews on', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(746, 'frontend/detail/description', 6, 2, 'DetailDescriptionHeader', 'product information', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(747, 'frontend/detail/tabs', 6, 2, 'DetailTabsRating', 'reviews', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(748, 'frontend/detail/tabs', 6, 2, 'DetailTabsDescription', 'description', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(749, 'frontend/detail/actions', 6, 2, 'DetailLinkContact', 'Questions about the item?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(750, 'frontend/detail/actions', 6, 2, 'DetailLinkNotepad', 'add to notepad', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(751, 'frontend/detail/actions', 6, 2, 'DetailLinkReview', 'write a review', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(752, 'frontend/detail/actions', 6, 2, 'DetailLinkVoucher', 'recommend article', '0000-00-00 00:00:00', '2010-10-17 18:49:27'),
(753, 'frontend/detail/buy', 6, 2, 'DetailBuyActionAdd', 'add to shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(754, 'frontend/detail/buy', 6, 2, 'DetailBuyLabelQuantity', 'quantity', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(755, 'frontend/detail/data', 6, 2, 'DetailDataId', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(756, 'frontend/detail/index', 6, 2, 'DetailFrom', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(757, 'frontend/account/ajax_login', 6, 2, 'LoginActionNext', 'continue', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(758, 'frontend/detail/navigation', 6, 2, 'DetailNavIndex', 'overview', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(759, 'frontend/detail/navigation', 6, 2, 'DetailNavCount', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(760, 'frontend/listing/box_article', 6, 2, 'ListingBoxLinkDetails', 'more', '0000-00-00 00:00:00', '2010-10-15 16:58:42'),
(761, 'frontend/listing/box_article', 6, 2, 'ListingBoxLinkBuy', 'buy now', '0000-00-00 00:00:00', '2010-10-15 16:58:13'),
(762, 'frontend/listing/box_article', 6, 2, 'ListingBoxNoPicture', 'no picture available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(763, 'frontend/listing/listing_actions', 6, 2, 'ListingView4Cols', 'four columns', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(764, 'frontend/listing/listing_actions', 6, 2, 'ListingView3Cols', 'three columns', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(765, 'frontend/listing/listing_actions', 6, 2, 'ListingView2Cols', 'two columns', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(766, 'frontend/listing/listing_actions', 6, 2, 'ListingViewTable', 'list', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(767, 'frontend/listing/listing_actions', 6, 2, 'ListingLabelView', 'view', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(768, 'frontend/listing/listing_actions', 6, 2, 'ListingLabelItemsPerPage', 'items per page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(769, 'frontend/listing/listing_actions', 6, 2, 'ListingSortName', 'item description', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(770, 'frontend/listing/listing_actions', 6, 2, 'ListingSortPriceHighest', 'highest price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(771, 'frontend/listing/listing_actions', 6, 2, 'ListingSortPriceLowest', 'lowest price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(772, 'frontend/listing/listing_actions', 6, 2, 'ListingSortRating', 'popularity', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(773, 'frontend/listing/listing_actions', 6, 2, 'ListingSortRelease', 'release date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(774, 'frontend/blog/index', 6, 2, 'BlogLinkAtom', 'Atom-Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(775, 'frontend/listing/listing_actions', 6, 2, 'ListingLabelSort', 'sorting', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(776, 'frontend/blog/index', 6, 2, 'BlogLinkRSS', 'RSS-Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(777, 'frontend/checkout/finish', 6, 2, 'FinishTextRightOfRevocation', 'information on returns policy', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1518, 'frontend/checkout/confirm_header', 1, 1, 'CheckoutColumnExcludeTax', 'zzgl. Mwst.', '2010-10-15 13:21:16', '2010-10-15 13:22:00'),
(779, 'frontend/index/footer', 6, 2, 'FooterInfoIncludeVat', '""><a title=""Versandkosten"" href=""{$sBasefile}?sViewport=custom&cCUSTOM=6"">Versandkosten</a></span> und ggf. Nachnahmegebühren, wenn nicht anders beschrieben"', '0000-00-00 00:00:00', '2010-10-07 23:31:10'),
(780, 'frontend/widgets/topseller', 6, 2, 'WidgetsTopsellerNoPicture', 'no picture available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(781, 'frontend/blog/box', 6, 2, 'BlogLinkMore', 'read more', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(782, 'frontend/blog/box', 6, 2, 'BlogInfoFrom', 'from:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(783, 'frontend/blog/box', 6, 2, 'BlogInfoComments', 'comments', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(784, 'frontend/plugins/index/viewlast', 6, 2, 'WidgetsRecentlyViewedLinkDetails', 'more information', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(785, 'frontend/plugins/index/viewlast', 6, 2, 'WidgetsRecentlyViewedHeadline', 'recently viewed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(786, 'frontend/index/breadcrumb', 6, 2, 'BreadcrumbDefault', 'you are here:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(787, 'frontend/blog/detail', 6, 2, 'BlogHeaderSocialmedia', 'recommend ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(788, 'frontend/register/index', 6, 2, 'RegisterTitle', 'sign in', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(789, 'frontend/checkout/confirm', 6, 2, 'ConfirmErrorAGB', 'Please accept our general terms and conditions', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(790, 'frontend/index/checkout_actions', 6, 2, 'IndexLinkNotepad', 'notepad', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(791, 'frontend/checkout/finish', 6, 2, 'FinishTitleRightOfRevocation', 'returns policy', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(792, 'frontend/account/content_right', 6, 2, 'AccountLinkOverview', 'my account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(793, 'frontend/index/checkout_actions', 6, 2, 'IndexActionShowPositions', 'show ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(794, 'frontend/index/checkout_actions', 6, 2, 'IndexInfoArticles', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(795, 'frontend/index/checkout_actions', 6, 2, 'IndexLinkCart', 'shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(796, 'frontend/compare/index', 6, 2, 'CompareActionDelete', 'delete comparison', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(797, 'frontend/compare/index', 6, 2, 'CompareActionStart', 'start comparison', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(798, 'frontend/index/index', 6, 2, 'IndexLinkDefault', 'go to start page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(799, 'frontend/index/header', 6, 2, 'IndexMetaAuthor', 'hello', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(800, 'frontend/search/paging', 6, 2, 'ListingSortRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(801, 'frontend/search/paging', 6, 2, 'ListingSortPriceLowest', 'lowest price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(802, 'frontend/search/paging', 6, 2, 'ListingSortPriceHighest', 'highest price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(803, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddHeader', 'The article has been added to the shopping cart successfully.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(804, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddLinkBack', 'continue shopping', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(805, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddLinkCart', 'show shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(806, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddHeaderCrossSelling', 'you might like this item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(807, 'frontend/checkout/ajax_amount', 6, 2, 'AjaxAmountInfoCountArticles', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(808, 'frontend/account/ajax_login', 6, 2, 'LoginHeader', 'a+I120n online order is simple', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(809, 'frontend/account/ajax_login', 6, 2, 'LoginLabelMail', 'your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(810, 'frontend/account/ajax_login', 6, 2, 'LoginLabelNew', 'new customer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(811, 'frontend/account/ajax_login', 6, 2, 'LoginLabelExisting', 'I am already a customer and my password is', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(812, 'frontend/account/ajax_login', 6, 2, 'LoginLinkLostPassword', 'Forgot your password?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(813, 'frontend/account/ajax_login', 6, 2, 'LoginActionClose', 'close window', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(814, 'frontend/detail/navigation', 6, 2, 'DetailNavNext', 'continue', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(815, 'frontend/listing/listing_actions', 6, 2, 'ListingTextFrom', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(816, 'frontend/listing/listing_actions', 6, 2, 'ListingTextSite', 'site', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(817, 'frontend/listing/listing_actions', 6, 2, 'ListingLinkNext', 'next site', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(818, 'frontend/listing/box_article', 6, 2, 'ListingBoxArticleStartsAt', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(819, 'frontend/widgets/topseller', 6, 2, 'TopsellerHeading', 'topseller', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(820, 'frontend/widgets/compare/index', 6, 2, 'DetailActionLinkCompare', 'compare items', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(821, 'frontend/register/personal_fieldset', 6, 2, 'RegisterPersonalHeadline', 'your personal information', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(822, 'frontend/register/personal_fieldset', 6, 2, 'RegisterPersonalLabelType', 'I am already a customer and my password is', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(823, 'frontend/register/personal_fieldset', 6, 2, 'RegisterPersonalLabelPrivate', 'a private customer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(824, 'frontend/register/personal_fieldset', 6, 2, 'RegisterPersonalLabelBusiness', 'a corporation', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(825, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelSalutation', 'form of address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(826, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(827, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(828, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelFirstname', 'given name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(829, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelLastname', 'last name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(830, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelNoAccount', 'don''t create customer account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(831, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelMail', 'your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(832, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelMailConfirmation', 'repeat your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(833, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelPassword', 'your password', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(834, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelPasswordRepeat', 'repeat your password', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(835, 'frontend/register/personal_fieldset', 6, 2, 'RegisterInfoPassword', 'your password must have at least ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(836, 'frontend/register/personal_fieldset', 6, 2, 'RegisterInfoPasswordCharacters', 'characters', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(837, 'frontend/register/personal_fieldset', 6, 2, 'RegisterInfoPassword2', 'check case-sensitivity', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(838, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelPhone', 'phone:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(839, 'frontend/register/personal_fieldset', 6, 2, 'RegisterLabelBirthday', 'date of birth:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(840, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataInfoNotAvailable', 'This option is not eligible!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(841, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataInfoShippingfree', 'Free shipping!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(842, 'frontend/detail/data', 6, 2, 'DetailDataInfoArticleStartsAt', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(843, 'frontend/blog/filter', 6, 2, 'BlogHeaderFilterProperties', 'filter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(844, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingHeadline', 'your address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(845, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingLabelStreet', 'street name / house number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(846, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingLabelCity', 'postal code / city:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(847, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingLabelCountry', 'country:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(848, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingLabelSelect', 'Please select?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(849, 'frontend/register/billing_fieldset', 6, 2, 'RegisterBillingLabelShipping', 'The shipping address is different from the billing address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(850, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingHeadline', 'your differing shipping address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(851, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelSalutation', 'form of address:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(852, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelCompany', 'corporation:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(853, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelDepartment', 'department:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(854, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelFirstname', 'given name:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(855, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelLastname', 'last name.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(856, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelStreet', 'street name / house number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(857, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelCity', 'postal code / city:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(858, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelCountry', 'country:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(859, 'frontend/register/shipping_fieldset', 6, 2, 'RegisterShippingLabelSelect', 'Please select?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(860, 'frontend/register/payment_fieldset', 6, 2, 'RegisterPaymentHeadline', 'Please select your preferred method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(861, 'frontend/plugins/payment/debit', 6, 2, 'PaymentDebitLabelAccount', 'account number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(862, 'frontend/plugins/payment/debit', 6, 2, 'PaymentDebitLabelBankcode', 'bank code', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(863, 'frontend/plugins/payment/debit', 6, 2, 'PaymentDebitLabelBankname', 'your bank', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(864, 'frontend/plugins/payment/debit', 6, 2, 'PaymentDebitLabelName', 'account holder', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(865, 'frontend/plugins/payment/debit', 6, 2, 'PaymentDebitInfoFields', 'All boxes marked with * must be filled out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(866, 'frontend/register/index', 6, 2, 'RegisterInfoAdvantages', '<h2>my advantages</h2> <ul> <li>shopping faster</li> <li>save your user data and settings</li> <li>check your orders and shipping status</li> <li>manage your newsletter subscriptions</li> </ul>', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(867, 'frontend/register/error_message', 6, 2, 'RegisterErrorHeadline', 'An error has occurred!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(868, 'frontend/detail/navigation', 6, 2, 'DetailNavPrevious', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(869, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoFillOutFields', 'Please fill out all boxes in red', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(870, 'frontend/listing/filter_supplier', 6, 2, 'FilterSupplierHeadline', 'producer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(871, 'frontend/listing/listing', 6, 2, 'ListingInfoFilterSupplier', 'items from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(872, 'frontend/listing/listing', 6, 2, 'ListingLinkAllSuppliers', 'show all producers', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(873, 'frontend/custom/right.tpl', 6, 2, 'CustomHeader', 'direct contact', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(874, 'frontend/forms/index', 6, 2, 'FormsTextContact', '<strong>demo shop<br /> </strong><br /> insert your contact information here', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(875, 'frontend/checkout/shipping_costs', 6, 2, 'ShippingHeader', 'calculating shipping costs', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(876, 'frontend/checkout/shipping_costs', 6, 2, 'ShippingLabelDeliveryCountry', 'country of delivery', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(877, 'frontend/checkout/shipping_costs', 6, 2, 'ShippingLabelPayment', 'method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(878, 'frontend/checkout/shipping_costs', 6, 2, 'ShipppingLabelDispatch', 'mode of shipment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(879, 'frontend/account/shipping', 6, 2, 'ShippingLinkSend', 'change', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(880, 'frontend/checkout/actions', 6, 2, 'CheckoutActionsLinkProceed', 'check out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(881, 'frontend/index/checkout_actions', 6, 2, 'IndexLinkCheckout', 'check out', '0000-00-00 00:00:00', '2010-10-15 17:02:04'),
(882, 'frontend/blog/detail', 6, 2, 'BlogHeaderCrossSelling', 'items that match', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(883, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmHeaderBilling', 'billing address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(884, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(885, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmLinkChangeBilling', 'change', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(886, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmLinkSelectBilling', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(887, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmHeaderShipping', 'shipping address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(888, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmLinkChangeShipping', 'change', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(889, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmLinkSelectShipping', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(890, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmHeaderPayment', 'selected method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(891, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmInfoInstantDownload', 'purchase of instant downloads only via debit advice or credit card', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(892, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmLinkChangePayment', 'change', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(893, 'frontend/account/billing', 6, 2, 'BillingLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(894, 'frontend/account/content_right', 6, 2, 'AccountHeaderNavigation', 'my account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(895, 'frontend/ticket/listing', 6, 2, 'TicketTitle', 'ticket system', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(896, 'frontend/account/content_right', 6, 2, 'AccountLinkPreviousOrders', 'my order', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(897, 'frontend/account/content_right', 6, 2, 'AccountLinkDownloads', 'my instant downloads', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(898, 'frontend/account/content_right', 6, 2, 'AccountLinkBillingAddress', 'change billing address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(899, 'frontend/account/content_right', 6, 2, 'AccountLinkShippingAddress', 'change shipping address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(900, 'frontend/account/content_right', 6, 2, 'AccountLinkPayment', 'change method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(901, 'frontend/account/content_right', 6, 2, 'AccountLinkNotepad', 'notepad', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(902, 'frontend/account/content_right', 6, 2, 'AccountLinkLogout', 'log out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(903, 'frontend/account/index', 6, 2, 'AccountHeaderWelcome', 'welcome', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(904, 'frontend/account/index', 6, 2, 'AccountHeaderInfo', 'This is your account dashboard where you can check your recent account activities', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(905, 'frontend/account/success_messages', 6, 2, 'AccountAccountSuccess', 'Registration data has been saved successfully', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(906, 'frontend/account/index', 6, 2, 'AccountHeaderBasic', 'user data', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(907, 'frontend/account/index', 6, 2, 'AccountLinkChangePassword', 'change password', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(908, 'frontend/account/index', 6, 2, 'AccountLinkChangePayment', 'change method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(909, 'frontend/account/index', 6, 2, 'AccountLabelNewPassword', 'new password*:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(910, 'frontend/account/index', 6, 2, 'AccountLabelRepeatPassword', 'repeat password*:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(911, 'frontend/account/index', 6, 2, 'AccountHeaderNewsletter', 'your newletter settings', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(912, 'frontend/account/index', 6, 2, 'AccountLabelWantNewsletter', 'Yes, I would like to receive the free {$sShopname} newsletter', '0000-00-00 00:00:00', '2010-10-15 13:25:20'),
(913, 'frontend/account/success_messages', 6, 2, 'AccountPaymentSuccess', 'Your method of payment has been saved successfully.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(914, 'frontend/account/index', 6, 2, 'AccountHeaderPrimaryBilling', 'primary billing address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(915, 'frontend/account/index', 6, 2, 'AccountLinkSelectBilling', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(916, 'frontend/account/index', 6, 2, 'AccountHeaderPrimaryShipping', 'primary shipping address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(917, 'frontend/account/index', 6, 2, 'AccountLinkSelectShipping', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(918, 'frontend/account/orders', 6, 2, 'OrdersHeader', 'sort orders by date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(919, 'frontend/account/downloads', 6, 2, 'DownloadsColumnDate', 'date ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(920, 'frontend/account/orders', 6, 2, 'OrderColumnId', 'order number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(921, 'frontend/account/orders', 6, 2, 'OrderColumnDispatch', 'mode of shipment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(922, 'frontend/account/orders', 6, 2, 'OrderColumnStatus', 'order status', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(923, 'frontend/account/orders', 6, 2, 'OrderColumnActions', 'actions', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(924, 'frontend/account/order_item', 6, 2, 'OrderItemInfoNotProcessed', 'Order has not been processed yet', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(925, 'frontend/account/order_item', 6, 2, 'OrderActionSlide', 'show/hide', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(926, 'frontend/account/downloads', 6, 2, 'DownloadsColumnName', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(927, 'frontend/account/order_item', 6, 2, 'OrderItemColumnQuantity', 'number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(928, 'frontend/account/order_item', 6, 2, 'OrderItemColumnPrice', 'price per unit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(929, 'frontend/account/order_item', 6, 2, 'OrderItemColumnTotal', 'total', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(930, 'frontend/account/order_item', 6, 2, 'OrderItemColumnDate', 'From:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(931, 'frontend/account/order_item', 6, 2, 'OrderItemColumnId', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(932, 'frontend/account/order_item', 6, 2, 'OrderItemColumnDispatch', 'mode of shipment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(933, 'frontend/account/order_item', 6, 2, 'OrderLinkRepeat', 'repeat order', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(934, 'frontend/account/order_item', 6, 2, 'OrderItemShippingcosts', 'shipping costs:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(935, 'frontend/account/order_item', 6, 2, 'OrderItemTotal', 'total amount:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(936, 'frontend/account/downloads', 6, 2, 'DownloadsHeader', 'Sort your instant downloads by date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(937, 'frontend/note/index', 6, 2, 'NoteHeadline', 'notepad', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(938, 'frontend/note/index', 6, 2, 'NoteText', 'nlichen Favoriten - bis Sie das n&auml', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(939, 'frontend/note/index', 6, 2, 'NoteText2', 'Just add the wanted item to the shopping list and {$sShopname} saves your shopping list automatically. In this way you can comfortably get back to all items earmarked on a later visit.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(940, 'frontend/note/index', 6, 2, 'NoteColumnName', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(941, 'frontend/note/index', 6, 2, 'NoteColumnPrice', 'price per unit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(942, 'frontend/checkout/error_messages', 6, 2, 'ConfirmInfoPaymentNotCompatibleWithESD', 'This method of payment is not available for instant downloads', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(943, 'frontend/checkout/cart', 6, 2, 'CartTitle', 'shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(944, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddLabelQuantity', 'number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(945, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddLabelOrdernumber', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(946, 'frontend/account/logout', 6, 2, 'LogoutInfoFinished', 'You have been logged out successfully!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(947, 'frontend/account/logout', 6, 2, 'LogoutLinkHomepage', 'go to start page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(948, 'frontend/checkout/ajax_cart', 6, 2, 'AjaxCartInfoEmpty', 'Your shopping cart is empty', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(949, 'frontend/checkout/ajax_cart', 6, 2, 'AjaxCartLinkBasket', 'open shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(950, 'frontend/search/paging', 6, 2, 'ListingSortRelevance', 'relevance', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(951, 'frontend/search/paging', 6, 2, 'ListingLabelSort', 'sorting', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(952, 'frontend/newsletter/index', 6, 2, 'sNewsletterOptionSubscribe', 'subscribe to newsletter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(953, 'frontend/newsletter/index', 6, 2, 'sNewsletterOptionUnsubscribe', 'unsubscribe from newsletter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(954, 'frontend/newsletter/index', 6, 2, 'sNewsletterLabelMail', 'your email:', '0000-00-00 00:00:00', '2010-10-15 13:18:53'),
(955, 'frontend/newsletter/index', 6, 2, 'sNewsletterInfo', 'Subscribe to our periodical newsletter now and be the first to receive information on new items and offers.<br /> Needless to say, the newsletter can be unsubscribed via a link in the email or on this site.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(956, 'frontend/newsletter/index', 6, 2, 'sNewsletterButton', 'save', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(957, 'frontend/listing/box_article', 6, 2, 'ListingBoxTip', 'tip', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(958, 'frontend/listing/box_article', 6, 2, 'ListingBoxInstantDownload', 'available as instant download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(959, 'frontend/detail/liveshopping/ticker/countdown', 6, 2, 'LiveTickerCurrentPrice', 'current price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(960, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeDays', 'days', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(961, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeHours', 'hours', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(962, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeMinutes', 'minutes', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(963, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeSeconds', 'seconds', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(964, 'frontend/register/steps', 6, 2, 'CheckoutStepBasketNumber', '1', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(965, 'frontend/register/steps', 6, 2, 'CheckoutStepBasketText', 'shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(966, 'frontend/search/fuzzy', 6, 2, 'SearchFuzzyHeadlineEmpty', '"Unfortunately no items could be found for ""{$sRequests.sSearchOrginal}""<"', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(967, 'frontend/register/steps', 6, 2, 'CheckoutStepRegisterNumber', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(968, 'frontend/register/steps', 6, 2, 'CheckoutStepRegisterText', 'registration', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(969, 'frontend/index/index', 6, 2, 'IndexRealizedWith', 'realized with', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(970, 'frontend/index/menu_left', 6, 2, 'MenuLeftHeading', 'information', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(971, 'frontend/widgets/advanced_menu/index', 6, 2, 'IndexLinkHome', 'home', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(972, 'frontend/widgets/compare/index', 6, 2, 'ListingBoxLinkCompare', 'compare', '0000-00-00 00:00:00', '2010-10-17 19:01:26'),
(973, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftHeadlinePrice', 'price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(974, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftHeadlineSupplier', 'producer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(975, 'frontend/listing/box_similar', 6, 2, 'SimilarBoxLinkCompare', 'compare', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(976, 'frontend/tellafriend/index', 6, 2, 'TellAFriendHeadline', 'Recommend.', '0000-00-00 00:00:00', '2010-10-17 18:49:19'),
(977, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLabelName', 'your name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(978, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLabelMail', 'your email*:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(979, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLabelFriendsMail', 'recipient''s email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(980, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLabelComment', 'your comment:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(981, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLabelCaptcha', 'captcha', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(982, 'frontend/tellafriend/index', 6, 2, 'TellAFriendLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(983, 'frontend/tellafriend/index', 6, 2, 'TellAFriendActionSubmit', 'send', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(984, 'frontend/forms/elements', 6, 2, 'SupportLabelCaptcha', 'Please type in this number sequence in the upcoming input box', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(985, 'frontend/forms/elements', 6, 2, 'SupportLabelInfoFields', 'All boxes marked with * must be filled out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(986, 'frontend/forms/elements', 6, 2, 'SupportActionSubmit', 'send', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(987, 'frontend/compare/index', 6, 2, 'CompareInfoCount', 'compare items', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(988, 'frontend/compare/col_description', 6, 2, 'CompareColumnPicture', 'picture', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(989, 'frontend/compare/col_description', 6, 2, 'CompareColumnName', 'name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(990, 'frontend/compare/col_description', 6, 2, 'CompareColumnRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(991, 'frontend/compare/col_description', 6, 2, 'CompareColumnDescription', 'description', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(992, 'frontend/compare/col_description', 6, 2, 'CompareColumnPrice', 'price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(993, 'frontend/compare/overlay', 6, 2, 'CompareActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(994, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoSuccess', 'Thank you for sharing your review with us! Your review will be published after being checked.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(995, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoAverageRate', 'average customer rating:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(996, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoRating', 'based on {$sArticle.sVoteAverange.count} customer reviews', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(997, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoFrom', 'From:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(998, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddErrorHeader', 'Item could not be added to shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(999, 'frontend/detail/data', 6, 2, 'DetailDataInfoSavePercent', 'saved', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1000, 'frontend/detail/related', 6, 2, 'DetailRelatedHeader', 'Related items:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1001, 'frontend/account/index', 6, 2, 'AccountTitle', 'customer account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1002, 'frontend/note/item', 6, 2, 'NoteLinkDetails', 'more', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1003, 'frontend/note/item', 6, 2, 'NoteLinkCompare', 'compare', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1004, 'frontend/note/item', 6, 2, 'NoteInfoId', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1005, 'frontend/note/item', 6, 2, 'NoteLinkDelete', 'delete', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1006, 'frontend/note/item', 6, 2, 'NoteLinkBuy', 'buy', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1007, 'frontend/detail/buy', 6, 2, 'DetailBuyValueSelect', 'Please select?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1008, 'frontend/detail/data', 6, 2, 'DetailDataInfoContent', 'content', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1009, 'frontend/detail/data', 6, 2, 'DetailDataInfoBaseprice', 'base price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1010, 'frontend/compare/added', 6, 2, 'CompareHeaderTitle', 'compare items', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1011, 'frontend/compare/added', 6, 2, 'LoginActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1012, 'frontend/detail/article_config_upprice', 6, 2, 'DetailConfigActionSubmit', 'update now', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1013, 'frontend/newsletter/listing', 6, 2, 'NewsletterListingLinkDetails', '[more]', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1014, 'frontend/newsletter/detail', 6, 2, 'NewsletterDetailLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1015, 'frontend/newsletter/detail', 6, 2, 'NewsletterDetailLinkNewWindow', 'Open newsletter in new window', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1016, 'frontend/blog/detail', 6, 2, 'BlogInfoCategories', 'category classification', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1017, 'frontend/blog/detail', 6, 2, 'BlogLinkComments', 'go to the comments on this item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1018, 'frontend/blog/detail', 6, 2, 'BlogInfoComments', 'comments', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1019, 'frontend/blog/detail', 6, 2, 'BlogHeaderRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1020, 'frontend/blog/box', 6, 2, 'BlogInfoRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1021, 'frontend/blog/detail', 6, 2, 'BlogInfoFrom', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1022, 'frontend/blog/bookmarks', 6, 2, 'BookmarkTwitter', 'Twitter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1023, 'frontend/blog/bookmarks', 6, 2, 'BookmarkFacebook', 'Facebook', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1024, 'frontend/blog/bookmarks', 6, 2, 'BookmarkDelicious', 'Delicious', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1025, 'frontend/blog/bookmarks', 6, 2, 'BookmarkDiggit', 'Diggit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1026, 'frontend/blog/comments', 6, 2, 'BlogHeaderWriteComment', 'write comment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1027, 'frontend/blog/comments', 6, 2, 'BlogInfoFields', 'All boxes marked with * must be filled out', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1028, 'frontend/blog/comments', 6, 2, 'BlogLabelName', 'your name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1029, 'frontend/blog/comments', 6, 2, 'BlogLabelMail', 'your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1030, 'frontend/blog/comments', 6, 2, 'BlogLabelRating', 'rating', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1031, 'frontend/blog/comments', 6, 2, 'rate10', '10 excellent', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1032, 'frontend/blog/comments', 6, 2, 'rate9', '9', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1033, 'frontend/blog/comments', 6, 2, 'rate8', '8', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1034, 'frontend/blog/comments', 6, 2, 'rate7', '7', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1035, 'frontend/blog/comments', 6, 2, 'rate6', '6', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1036, 'frontend/blog/comments', 6, 2, 'rate5', '5', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1037, 'frontend/blog/comments', 6, 2, 'rate4', '4', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1038, 'frontend/blog/comments', 6, 2, 'rate3', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1039, 'frontend/blog/comments', 6, 2, 'rate2', '2', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1040, 'frontend/blog/comments', 6, 2, 'rate1', '1 miserable', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1041, 'frontend/blog/comments', 6, 2, 'BlogLabelSummary', 'summary', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1042, 'frontend/blog/comments', 6, 2, 'BlogLabelComment', 'your opinion', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1043, 'frontend/blog/comments', 6, 2, 'BlogLabelCaptcha', 'Please type in this number sequence in the upcoming input box', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1044, 'frontend/blog/comments', 6, 2, 'BlogLinkSaveComment', 'save', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1045, 'frontend/checkout/cart', 6, 2, 'CartInfoFreeShipping', 'free shipping', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1046, 'frontend/checkout/cart', 6, 2, 'CartInfoFreeShippingDifference', 'Place at least another {$sShippingcostsDifference|currency} of products in your shopping cart for free shipping! ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1047, 'frontend/checkout/cart_header', 6, 2, 'CartColumnName', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1048, 'frontend/checkout/cart_header', 6, 2, 'CartColumnAvailability', 'availability', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1049, 'frontend/checkout/cart_header', 6, 2, 'CartColumnPrice', 'price per unit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1050, 'backend/error/index', 6, 2, 'ErrorIndexTitle', 'error', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1051, 'frontend/checkout/cart_item', 6, 2, 'CartItemInfoId', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1052, 'frontend/checkout/cart_item', 6, 2, 'CartItemLinkDelete', 'delete', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1053, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterActionAddVoucher', 'add', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1054, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterLabelAddArticle', 'add item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1055, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterIdLabelInline', 'order number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1056, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterActionAdd', 'add', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1057, 'frontend/checkout/cart_footer', 6, 2, 'CartFooterSum', 'total', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1058, 'frontend/checkout/cart_footer', 6, 2, 'CartFooterShipping', 'shipping costs', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1059, 'frontend/checkout/cart_footer', 6, 2, 'CartFooterTotal', 'total', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1060, 'frontend/checkout/actions', 6, 2, 'CheckoutActionsLinkLast', 'continue shopping', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1061, 'frontend/checkout/confirm', 6, 2, 'ConfirmHeader', 'Please check your order once again before you send it in.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1062, 'frontend/checkout/confirm', 6, 2, 'ConfirmInfoChange', 'nnen Sie jetzt noch &auml', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1063, 'frontend/checkout/confirm', 6, 2, 'ConfirmInfoPaymentData', '<strong> our bank account: </strong> Volksbank Musterstadt bank code: account number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1064, 'frontend/checkout/confirm_header', 6, 2, 'CheckoutColumnTax', 'VAT included', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1065, 'frontend/error/index', 6, 2, 'ErrorIndexTitle', 'error', '0000-00-00 00:00:00', '2010-10-15 13:26:59'),
(1066, 'frontend/checkout/confirm', 6, 2, 'ConfirmLabelComment', 'comment:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1067, 'frontend/checkout/confirm', 6, 2, 'ConfirmTerms', '"">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden."', '0000-00-00 00:00:00', '2010-10-07 23:31:45'),
(1068, 'frontend/checkout/confirm', 6, 2, 'ConfirmTextOrderDefault', 'If you pay via bank collection or credit card the due amount will be withdrawn from your bank account five days after you place your order.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1069, 'frontend/checkout/confirm', 6, 2, 'ConfirmActionSubmit', 'complete order', '0000-00-00 00:00:00', '2010-10-16 16:51:57'),
(1070, 'frontend/account/password', 6, 2, 'PasswordHeader', 'Forgot your password? Here you can have a new password emailed to you.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1071, 'frontend/account/password', 6, 2, 'PasswordLabelMail', 'your email:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1072, 'frontend/account/password', 6, 2, 'PasswordText', 'We will send you a new  password generated randomly, which you can change in the user area.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1073, 'frontend/account/password', 6, 2, 'PasswordLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1074, 'frontend/detail/bundle/box_bundle', 6, 2, 'BundleHeader', 'Save now with our bundle-offers', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1075, 'frontend/detail/bundle/box_bundle', 6, 2, 'BundleActionAdd', 'In den Warenkorb', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1076, 'frontend/detail/bundle/box_bundle', 6, 2, 'BundleInfoPriceForAll', 'price for all', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1077, 'frontend/detail/bundle/box_bundle', 6, 2, 'BundleInfoPriceInstead', 'instead of', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1078, 'frontend/detail/bundle/box_bundle', 6, 2, 'BundleInfoPercent', '% discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1079, 'frontend/detail/description', 6, 2, 'DetailDescriptionHeaderDownloads', 'available downloads:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1080, 'frontend/detail/description', 6, 2, 'DetailDescriptionLinkDownload', 'download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1081, 'frontend/checkout/premiums', 6, 2, 'PremiumsHeader', 'Please choose between the following premiums', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1082, 'frontend/newsletter/index', 6, 2, 'NewsletterTitle', 'newsletter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1083, 'frontend/checkout/premiums', 6, 2, 'PremiumActionAdd', 'select a premium', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1084, 'frontend/checkout/actions', 6, 2, 'CheckoutActionsLinkOffer', 'request offer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1085, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterHeadline', 'subscribe to newsletter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1086, 'frontend/account/login', 6, 2, 'LoginHeaderNew', 'It''s your first time on', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1087, 'frontend/account/login', 6, 2, 'LoginInfoNew', 'No worries, a shop order is easy and safe. Registration will take only a few minutes.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1088, 'frontend/account/login', 6, 2, 'LoginLinkRegister', 'new customer', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1089, 'frontend/account/login', 6, 2, 'LoginHeaderExistingCustomer', 'You already have an account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1090, 'frontend/account/login', 6, 2, 'LoginHeaderFields', 'Log in with your email and password', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1091, 'frontend/account/login', 6, 2, 'LoginLabelMail', 'your email:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1092, 'frontend/account/login', 6, 2, 'LoginLabelPassword', 'your password:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1093, 'frontend/account/login', 6, 2, 'LoginLinkLogon', 'log in ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1094, 'frontend/account/login', 6, 2, 'LoginLinkLostPassword', 'Forgot your password?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1095, 'frontend/index/checkout_actions', 6, 2, 'IndexLinkAccount', 'my account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1096, 'frontend/checkout/cart', 6, 2, 'CartInfoEmpty', 'Your shopping cart is empty', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1097, 'frontend/account/index', 6, 2, 'AccountLinkChangeBilling', 'change billing address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1098, 'frontend/account/index', 6, 2, 'AccountLinkChangeShipping', 'change shipping address', '0000-00-00 00:00:00', '2010-09-28 11:54:19');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1099, 'frontend/account/order_item', 6, 2, 'OrderItemColumnName', 'item', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1100, 'frontend/account/orders', 6, 2, 'OrderColumnDate', 'date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1101, 'frontend/account/downloads', 6, 2, 'DownloadsColumnLink', 'download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1102, 'frontend/account/content_right', 6, 2, 'sTicketSysSupportManagement', 'support management', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1103, 'frontend/account/downloads', 6, 2, 'DownloadsSerialnumber', 'your serial number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1104, 'frontend/account/downloads', 6, 2, 'DownloadsLink', 'download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1105, 'frontend/account/downloads', 6, 2, 'DownloadsInfoAccessDenied', 'This download is not available for you.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1106, 'frontend/account/downloads', 6, 2, 'DownloadsInfoNotFound', 'no downloads available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1107, 'frontend/account/downloads', 6, 2, 'DownloadsInfoEmpty', 'You have yet not bought any items via instant download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1108, 'frontend/account/index', 6, 2, 'AccountHeaderPayment', 'selected method of payment', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1109, 'frontend/account/index', 6, 2, 'AccountInfoInstantDownloads', 'purchase of instant+I424 downloads only via debit advice or credit card', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1110, 'frontend/account/index', 6, 2, 'AccountSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1111, 'frontend/account/shipping', 6, 2, 'ShippingLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1112, 'frontend/account/select_shipping', 6, 2, 'SelectShippingHeader', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1113, 'frontend/account/select_address', 6, 2, 'SelectAddressSubmit', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1114, 'frontend/account/select_address', 6, 2, 'SelectAddressSalutationMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1115, 'frontend/account/select_shipping', 6, 2, 'SelectShippingLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1116, 'frontend/account/payment', 6, 2, 'PaymentLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1117, 'frontend/account/index', 6, 2, 'AccountSalutationMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1118, 'frontend/checkout/cart_header', 6, 2, 'CartColumnQuantity', 'number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1119, 'frontend/checkout/cart_header', 6, 2, 'CartColumnTotal', 'total', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1120, 'frontend/plugins/trusted_shops/logo', 6, 2, 'WidgetsTrustedLogo', 'Trusted Shops seal of quality - Please check validity here!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1121, 'frontend/plugins/trusted_shops/logo', 6, 2, 'WidgetsTrustedLogoText', '{$this->config(''sShopname'')} is validated by trusted shops', '0000-00-00 00:00:00', '2010-10-17 19:02:20'),
(1122, 'frontend/checkout/finish', 6, 2, 'FinishHeaderThankYou', 'Thank you for your order from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1123, 'frontend/checkout/finish', 6, 2, 'FinishInfoConfirmationMail', 'We have sent you a fulfilment confirmation to your email.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1124, 'frontend/checkout/finish', 6, 2, 'FinishInfoPrintOrder', 'We advise you to print the fulfilment confirmation below.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1125, 'frontend/checkout/finish', 6, 2, 'FinishLinkPrint', 'Print fulfilment confirmation now!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1126, 'frontend/checkout/finish', 6, 2, 'FinishHeaderItems', 'information on your order:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1127, 'frontend/checkout/finish', 6, 2, 'FinishInfoId', 'order number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1128, 'frontend/search/paging', 6, 2, 'ListingSortName', 'product description', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1129, 'frontend/account/order_item', 6, 2, 'OrderInfoNoDispatch', 'not available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1130, 'frontend/account/order_item', 6, 2, 'OrderItemInfoInProgress', 'Order is in process', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1131, 'frontend/account/order_item', 6, 2, 'OrderItemInfoShipped', 'Order has been shipped', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1132, 'frontend/account/order_item', 6, 2, 'OrderItemInfoPartiallyShipped', 'Order has been shipped in part', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1133, 'frontend/account/order_item', 6, 2, 'OrderItemInfoCanceled', 'Order has been cancelled', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1134, 'frontend/account/order_item', 6, 2, 'OrderItemInfoBundle', 'bundle discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1135, 'frontend/account/order_item', 6, 2, 'OrderItemInfoInstantDownload', 'download now', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1136, 'frontend/account/order_item', 6, 2, 'OrderItemInfoFree', 'free of charge', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1137, 'frontend/account/order_item', 6, 2, 'OrderItemColumnTracking', 'package tracking:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1138, 'frontend/account/order_item', 6, 2, 'OrderItemNetTotal', 'net total:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1139, 'frontend/account/orders', 6, 2, 'OrdersInfoEmpty', 'You have not yet placed an order.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1140, 'frontend/account/ajax_logout', 6, 2, 'AccountLogoutHeader', 'Logout successful!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1141, 'frontend/account/ajax_logout', 6, 2, 'AccountLogoutText', 'You have been logged out successfully!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1142, 'frontend/account/password', 6, 2, 'PasswordInfoSuccess', 'Your new password has been sent to you', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1143, 'frontend/custom/right.tpl', 6, 2, 'CustomTextContact', '<strong>demo shop<br /> </strong><br /> fill in your contact information', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1144, 'frontend/checkout/cart_item', 6, 2, 'CartItemInfoFree', 'Free of charge!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1145, 'frontend/checkout/cart_item', 6, 2, 'CartItemInfoPremium', 'As a thank you we add this item to your order for free.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1146, 'frontend/checkout/cart_item', 6, 2, 'CartItemInfoBundle', 'bundle discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1147, 'frontend/account/select_billing', 6, 2, 'SelectBillingLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1148, 'frontend/account/select_billing', 6, 2, 'SelectBillingHeader', 'select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1149, 'frontend/account/select_billing', 6, 2, 'SelectBillingInfoEmpty', 'After you have placed your first order, you can access former billing addresses here.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1150, 'frontend/account/select_shipping', 6, 2, 'SelectShippingInfoEmpty', 'After you have placed your first order, you can access former shipping addresses here.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1151, 'frontend/blog/atom', 6, 2, 'BlogAtomFeedHeader', 'blog / Atom Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1152, 'frontend/blog/comments', 6, 2, 'BlogInfoFailureFields', 'Please fill out all boxes in red', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1153, 'frontend/blog/comments', 6, 2, 'BlogInfoSuccessOptin', 'Thank you for sharing your review with us! You will receive a confirmation email in a few minutes. Click on the link in this email to release your review for publication.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1154, 'frontend/blog/comments', 6, 2, 'BlogInfoSuccess', 'Thank you for sharing your review with us! Your review will be published after being checked.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1155, 'frontend/blog/detail', 6, 2, 'BlogHeaderDownloads', 'available downloads:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1156, 'frontend/blog/detail', 6, 2, 'BlogLinkDownload', 'download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1157, 'frontend/blog/detail', 6, 2, 'BlogInfoComment', 'our comment on', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1158, 'frontend/blog/detail', 6, 2, 'BlogInfoTags', 'tags', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1159, 'frontend/blog/filter', 6, 2, 'BlogHeaderFilterCategories', 'categories', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1160, 'frontend/blog/filter', 6, 2, 'BlogHeaderFilterDate', 'date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1161, 'frontend/blog/filter', 6, 2, 'BlogHeaderFilterAuthor', 'authors', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1162, 'frontend/blog/rss', 6, 2, 'BlogRssFeedHeader', 'blog / RSS Feed', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1163, 'frontend/checkout/added', 6, 2, 'AddArticleLinkBack', 'continue shopping', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1164, 'frontend/checkout/ajax_cart', 6, 2, 'AjaxCartInfoBundle', 'bundle discount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1165, 'frontend/checkout/ajax_cart', 6, 2, 'AjaxCartInfoFree', 'Free of charge!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1166, 'frontend/checkout/cart', 6, 2, 'CartInfoMinimumSurcharge', 'Attention. You have not yet reached the minimum order value of {$sMinimumSurcharge|currency}  for free shipping!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1167, 'frontend/checkout/cart', 6, 2, 'CartInfoNoDispatch', 'Attention. There is no mode of shipmenrt selected for your shopping cart/address ! <br />Please contact the shop operator.<br />', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1168, 'frontend/checkout/confirm', 6, 2, 'ConfirmHeaderNewsletter', 'Do you want more information?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1169, 'frontend/checkout/confirm', 6, 2, 'ConfirmLabelNewsletter', 'Yes, I would like to receive the free {$sShopname} newsletter', '0000-00-00 00:00:00', '2010-10-15 13:24:52'),
(1170, 'frontend/checkout/confirm_left', 6, 2, 'ConfirmSalutationMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1171, 'frontend/checkout/finish', 6, 2, 'FinishInfoTransaction', 'transaction number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1172, 'frontend/checkout/premiums', 6, 2, 'PremiumInfoNoPicture', 'no picture available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1173, 'frontend/checkout/premiums', 6, 2, 'PremiumsInfoDifference', 'still', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1174, 'frontend/checkout/premiums', 6, 2, 'PremiumsInfoAtAmount', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1175, 'frontend/content/detail', 6, 2, 'ContentInfoPicture', 'displayed in the picture:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1176, 'frontend/content/detail', 6, 2, 'ContentHeaderInformation', 'more information:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1177, 'frontend/content/detail', 6, 2, 'ContentHeaderDownloads', 'file attachment:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1178, 'frontend/content/detail', 6, 2, 'ContentLinkDownload', 'download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1179, 'frontend/content/detail', 6, 2, 'ContentInfoNotFound', 'Content could not be found', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1180, 'frontend/content/detail', 6, 2, 'ContentLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1181, 'frontend/content/index', 6, 2, 'ContentLinkDetails', '[more]', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1182, 'frontend/content/index', 6, 2, 'ContentInfoEmpty', 'currently no entries available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1183, 'frontend/detail/article_config_step', 6, 2, 'DetailConfigValueSelect', 'Please select?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1184, 'frontend/detail/article_config_step', 6, 2, 'DetailConfigActionSubmit', 'update now', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1185, 'frontend/detail/bundle/box_related', 6, 2, 'BundleHeader', 'Buy this item together with the following items', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1186, 'frontend/detail/bundle/box_related', 6, 2, 'BundleActionAdd', 'add to shopping cart', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1187, 'frontend/detail/bundle/box_related', 6, 2, 'BundleInfoPriceForAll', 'price for all', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1188, 'frontend/compare/added', 6, 2, 'CompareActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1189, 'frontend/detail/buy', 6, 2, 'DetailBuyInfoNotAvailable', 'This item is currently not available!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1190, 'frontend/detail/buy', 6, 2, 'DetailBuyLabelSurcharge', 'surcharge', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1191, 'frontend/detail/comment', 6, 2, 'DetailCommentInfoSuccessOptin', 'Thank you for sharing your review with us! You will receive a confirmation email in a few minutes. Click on the link in this email to release your review for publication.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1192, 'frontend/detail/comment', 6, 2, 'DetailCommentLabelMail', 'your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1193, 'frontend/compare/added', 6, 2, 'CompareInfoMaxReached', 'You can compare a maximum of 5 items in a single step', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1194, 'frontend/newsletter/index', 6, 2, 'NewsletterLabelSelect', 'please select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1195, 'frontend/detail/data', 6, 2, 'DetailDataPriceInfo', 'prices {if $this->config(''sARTICLESOUTPUTNETTO'') == true}excl.{else}incl.{/if} VAT <a title="Versandkosten" href="{url controller=custom sCustom=6}"  style="text-decoration:underline">excl. shipping costs</a>', '0000-00-00 00:00:00', '2010-10-16 08:52:39'),
(1196, 'frontend/detail/data', 6, 2, 'DetailDataHeaderBlockprices', 'block pricing', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1197, 'frontend/detail/data', 6, 2, 'DetailDataColumnQuantity', 'amount', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1198, 'frontend/detail/data', 6, 2, 'DetailDataColumnPrice', 'price per unit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1199, 'frontend/detail/data', 6, 2, 'DetailDataInfoUntil', 'until', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1200, 'frontend/detail/data', 6, 2, 'DetailDataInfoFrom', 'from', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1201, 'frontend/detail/description', 6, 2, 'DetailDescriptionLinkInformation', 'additional items from {$information.description}', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1202, 'frontend/detail/description', 6, 2, 'DetailDescriptionComment', 'our comment on', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1203, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownStartPrice', 'start price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1204, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownCurrentPrice', 'current price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1205, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownRemaining', 'still', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1206, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownRemainingPieces', 'pieces', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1207, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownPriceFails', 'price drops by', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1208, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownMinutes', 'minutes', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1209, 'frontend/detail/liveshopping/category_countdown', 6, 2, 'LiveCountdownPriceRising', 'price rises by', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1210, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownStartPrice', 'start price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1211, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownCurrentPrice', 'current price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1212, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownRemaining', 'still', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1213, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownRemainingPieces', 'pieces', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1214, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownPriceFails', 'price drops by', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1215, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownMinutes', 'minutes', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1216, 'frontend/detail/liveshopping/detail_countdown', 6, 2, 'LiveCountdownPriceRising', 'price rises by', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1217, 'frontend/account/payment', 6, 2, 'PaymentLinkSend', 'change', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1218, 'frontend/account/orders', 6, 2, 'MyOrdersTitle', 'order', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1219, 'frontend/account/orders', 6, 2, 'AccountTitle', 'customer account', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1220, 'frontend/detail/liveshopping/ticker/countdown', 6, 2, 'LiveTickerStartPrice', 'start price', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1221, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeRemaining', 'still', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1222, 'frontend/detail/liveshopping/ticker/timeline', 6, 2, 'LiveTimeRemainingPieces', 'pieces', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1223, 'frontend/account/success_messages', 6, 2, 'AccountShippingSuccess', 'Your shipping address has been saved successfully', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1224, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyInfoErrorMail', 'Please fill in a valid email address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1225, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyHeader', 'Notify me as soon as the item is available.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1226, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyLabelMail', 'your email', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1227, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyActionSubmit', 'fill in', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1228, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyInfoSuccess', 'Click on the link in the email that you have just received. In this way, you will receive a notification by email as soon as the item is available.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1229, 'frontend/forms/index', 6, 2, 'FormsLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1230, 'frontend/forms/elements', 6, 2, 'SupportInfoFillRedFields', 'Please fill out all boxes in red.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1231, 'frontend/tellafriend/index', 6, 2, 'TellAFriendHeaderSuccess', 'Thank you. The recommendation has been sent in successfully.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1232, 'frontend/tellafriend/index', 6, 2, 'TellAFriendInfoFields', 'Please fill out all fields required .', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1233, 'frontend/index/footer', 6, 2, 'FooterInfoExcludeVat', '* All prices are net prices and subject to VAT and', '0000-00-00 00:00:00', '2010-10-14 14:45:36'),
(1234, 'frontend/index/categories_top', 6, 2, 'IndexLinkHome', 'home', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1235, 'frontend/listing/filter_properties', 6, 2, 'FilterHeadline', 'filter', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1236, 'frontend/listing/filter_properties', 6, 2, 'FilterHeadlineCategory', 'query by', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1237, 'frontend/listing/filter_properties', 6, 2, 'FilterLinkDefault', 'show all ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1238, 'frontend/listing/box_article', 6, 2, 'ListingBoxNew', 'NEW', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1239, 'frontend/listing/box_similar', 6, 2, 'SimilarBoxLinkDetails', 'more ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1240, 'frontend/newsletter/detail', 6, 2, 'NewsletterDetailInfoEmpty', 'entry not found', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1241, 'frontend/compare/overlay', 6, 2, 'LoginActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1242, 'frontend/compare/overlay', 6, 2, 'CompareHeader', 'compare', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1243, 'frontend/newsletter/listing', 6, 2, 'NewsletterListingInfoEmpty', 'currently no entries available', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1244, 'frontend/listing/box_similar', 6, 2, 'SimilarBoxMore', 'more', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1245, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataShippingDays', 'working days', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1246, 'frontend/register/index', 6, 2, 'RegisterHeadlineSupplier', 'retailer registration', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1247, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier', 'Do you already have a retailer account?', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1248, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier2', 'click here to log in', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1249, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier3', 'After the login retail prices will be on display until activation is complete', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1250, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier4', 'Send in your trade license by fax!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1251, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier5', 'Send in your trade license by fax to +49 2555 92 95 61. If you are already registered with us as retailer <br />you can skip this step and do not have to send in a trade license.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1252, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier6', 'We will check your information and activate your account!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1253, 'frontend/register/index', 6, 2, 'RegisterInfoSupplier7', 'We will activate your retailer account after your information has been checked. <br />You will receive a notification by email. From there on wholesale prices will be on display on the product sites and product overviews.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1254, 'frontend/register/index', 6, 2, 'RegisterLabelDataCheckbox', 'I herewith accept the privacy terms and conditions', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1255, 'frontend/search/filter_category', 6, 2, 'SearchFilterLinkDefault', 'show all categories', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1256, 'frontend/register/steps', 6, 2, 'CheckoutStepConfirmText', 'place order', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1257, 'frontend/search/fuzzy', 6, 2, 'SearchFuzzyInfoShortTerm', 'Unfortunately the search word is too short. ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1258, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftLinkDefault', 'show all', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1259, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftInfoSuppliers', 'additional producers', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1260, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftHeadlineFilter', 'by query', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1261, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftLinkAllFilters', 'all queries', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1262, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftLinkAllSuppliers', 'all producers', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1263, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftLinkAllPrices', 'all prices', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1264, 'frontend/search/index', 6, 2, 'SearchHeadline', '"For ""{$sSearchTerm|escape}""  {$sSearchResultsNum} items have been found "', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1265, 'frontend/search/supplier', 6, 2, 'SearchTo', 'for', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1266, 'frontend/search/supplier', 6, 2, 'SearchWere', 'were', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1267, 'frontend/search/supplier', 6, 2, 'SearchArticlesFound', 'items have been found', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1268, 'frontend/sitemap/index', 6, 2, 'SitemapHeader', 'sitemap - all categories on display', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1269, 'frontend/ticket/navigation', 6, 2, 'TicketHeader', 'support management', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1270, 'frontend/ticket/navigation', 6, 2, 'TicketLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1271, 'frontend/ticket/navigation', 6, 2, 'TicketLinkSupport', 'apply for support', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1272, 'frontend/ticket/navigation', 6, 2, 'TicketLinkIndex', 'support overview', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1273, 'frontend/ticket/navigation', 6, 2, 'TicketLinkLogout', 'logout', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1274, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoEmpty', 'A ticket with this ID does not exist.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1275, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoTicket', 'details of this ticket', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1276, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoStatusClose', 'This ticket has been closed.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1277, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoStatusProgress', 'This ticket is still being edited.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1278, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoAnswer', 'your reply', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1279, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoQuestion', 'your ticket request:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1280, 'frontend/checkout/ajax_add_article', 6, 2, 'LoginActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1281, 'frontend/ticket/listing', 6, 2, 'TicketInfoId', 'ticketID', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1282, 'frontend/register/steps.tpl', 6, 2, 'CheckoutStepBasketNumber', '1', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1283, 'frontend/ticket/listing', 6, 2, 'TicketInfoStatus', 'status', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1284, 'frontend/ticket/listing', 6, 2, 'TicketHeadline', 'support management', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1285, 'frontend/ticket/listing', 6, 2, 'TicketLinkDetails', '[show details]', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1286, 'frontend/widgets/paypal/logo', 6, 2, 'WidgetsPayPalLogo', 'PayPal-method of payment seal', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1287, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsHeadline', 'Trusted Shops seal of quality - Please click here!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1288, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1289, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsSalutationMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1290, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsSalutationCompany', 'corporation', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1291, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsInfo', 'register for money-back guarantee', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1292, 'frontend/plugins/trusted_shops/form', 6, 2, 'WidgetsTrustedShopsText', 'tzlichen Service die Geld-zur&uuml', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1293, 'frontend/account/select_address', 6, 2, 'SelectAddressSalutationMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1294, 'frontend/checkout/premiums', 6, 2, 'PremiumInfoSelect', 'please select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1295, 'frontend/account/ajax_logout', 6, 2, 'LoginActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1296, 'frontend/blog/comments', 6, 2, 'BlogInfoComments', 'comments', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1297, 'frontend/register/billing_fieldset', 6, 2, 'RegisterLabelDepartment', 'department', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1298, 'frontend/register/steps', 6, 2, 'CheckoutStepConfirmNumber', '3', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1299, 'frontend/ticket/detail', 6, 2, 'TicketDetailInfoShopAnswer', 'our reply', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1300, 'frontend/widgets/blog/listing', 6, 2, 'WidgetsBlogHeadline', 'new to our blog', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1301, 'frontend/register/billing_fieldset', 6, 2, 'RegisterLabelCompany', 'name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1302, 'frontend/error/exception', 6, 2, 'ExceptionHeader', 'Ups! An error has occurred!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1303, 'frontend/error/exception', 6, 2, 'ExceptionText', 'The following hints should help you.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1304, 'frontend/register/billing_fieldset', 6, 2, 'RegisterHeaderCompany', 'corporation', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1305, 'frontend/detail/description', 6, 2, 'ArticleTipMoreInformation', 'links to', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1306, 'frontend/blog/index', 6, 2, 'ListingLinkAllSuppliers', 'show all authors', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1307, 'frontend/blog/index', 6, 2, 'ListingInfoFilterSupplier', 'query by author', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1308, 'frontend/register/billing_fieldset', 6, 2, 'RegisterLabelTaxId', 'tax ID number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1309, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataInfoInstock', 'ready for shipment <br/> delivery time approx. 1-3 working days', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1310, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataShippingtime', 'delivery time', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1311, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataInfoInstantDownload', 'available as instant download', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1312, 'frontend/plugins/index/delivery_informations', 6, 2, 'DetailDataInfoShipping', 'not available until', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1313, 'frontend', 6, 2, 'RegisterPasswordLength', 'Please choose a password consisting of {config name="MinPassword"} signs at minimum.', '0000-00-00 00:00:00', '2010-10-12 19:45:13'),
(1314, 'frontend', 6, 2, 'RegisterAjaxEmailNotEqual', 'The email addresses are different.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1315, 'frontend', 6, 2, 'RegisterAjaxEmailNotValid', 'Please type in a valid email.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1316, 'frontend/checkout/confirm_item', 6, 2, 'CheckoutItemPrice', 'price per unit', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1317, 'frontend/custom/ajax', 6, 2, 'CustomAjaxActionNewWindow', 'open in new window', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1318, 'frontend/account/ajax_logout', 6, 2, 'AccountLogoutButton', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1319, 'frontend/checkout/ajax_cart', 6, 2, 'AjaxCartLinkConfirm', 'checkout\n', '0000-00-00 00:00:00', '2010-10-15 16:59:48'),
(1320, 'frontend/account/success_messages', 6, 2, 'AccountBillingSuccess', 'saved successfully', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1321, 'frontend/search/paging', 6, 2, 'ListingSortRelease', 'date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1322, 'frontend/note/item', 6, 2, 'NoteInfoSupplier', 'producer:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1323, 'frontend/note/index', 6, 2, 'NoteTitle', 'notepad', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1324, 'frontend/account/content_right', 6, 2, 'TicketLinkSupport', 'support request', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1325, 'frontend/note/item', 6, 2, 'NoteLinkZoom', 'enlarge picture', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1326, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterBillingLabelCity', 'postal code / city:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1327, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterBillingLabelStreet', 'street name / house number:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1328, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterLabelLastname', 'last name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1329, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterLabelFirstname', 'given name', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1330, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterLabelMs', 'Ms', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1331, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterLabelMr', 'Mr', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1332, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterPleaseChoose', 'please select', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1333, 'frontend/newsletter/index', 6, 2, 'NewsletterRegisterLabelSalutation', 'form of address', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1334, 'frontend/register/index', 6, 2, 'RegisterIndexActionSubmit', 'complete registration', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1335, 'frontend/checkout/added', 6, 2, 'CheckoutAddArticleLinkBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1336, 'frontend', 6, 2, 'RegisterAjaxEmailForgiven', 'This email is already registered.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1337, 'frontend/checkout/added', 6, 2, 'CheckoutAddArticleInfoAdded', '{$sArticleName} has been added to your shopping cart!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1338, 'frontend/checkout/confirm_footer', 6, 2, 'CheckoutFinishTaxInformation', 'This order comes without VAT.', '0000-00-00 00:00:00', '2010-10-16 10:37:58'),
(1339, 'frontend/checkout/confirm_item', 6, 2, 'CartItemInfoFree', 'free of charge', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1340, 'frontend/account/password', 6, 2, 'LoginBack', 'back', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1341, 'frontend/checkout/cart_footer', 6, 2, 'CartFooterTotalTax', '% MwSt.:"', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1342, 'frontend/checkout/cart_footer', 6, 2, 'CartFooterTotalNet', 'total amount excl. VAT:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1343, 'frontend/detail/error', 6, 2, 'DetailRelatedHeader', 'Unfortunately this item is not available anymore!', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1344, 'frontend/detail/error', 6, 2, 'DetailRelatedHeaderSimilarArticles', 'related items:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1345, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyInfoValid', 'Thank you! We have saved your enquiry! You will be notified as soon as the item is available.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1346, 'frontend/plugins/notification/index', 6, 2, 'DetailNotifyInfoInvalid', 'An errror has occurred during the validation of your email notificaton. ', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1347, 'frontend/search/paging', 6, 2, 'ListingPaging', 'scrolling:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1348, 'frontend/search/paging', 6, 2, 'ListingLinkPrevious', 'previous page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1349, 'frontend/search/paging', 6, 2, 'ListingTextNext', '>', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1350, 'frontend/search/paging', 6, 2, 'ListingLinkNext', 'next page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1351, 'frontend/search/paging', 6, 2, 'ListingTextPrevious', '<', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1352, 'frontend/listing/listing_actions', 6, 2, 'ListingPaging', 'scrolling:', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1353, 'frontend/listing/listing_actions', 6, 2, 'ListingTextPrevious', '"', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1354, 'frontend/listing/listing_actions', 6, 2, 'ListingLinkPrevious', 'previous page', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1355, 'frontend/listing/listing_actions', 6, 2, 'ListingTextNext', '"', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1356, 'frontend/checkout/ajax_add_article', 6, 2, 'ListingBoxNoPicture', 'no picture', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1357, 'frontend/index/footer', 6, 2, 'IndexCopyright', ' 2010 shopware.ag - Alle Rechte vorbehalten."', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1358, 'frontend/detail/header', 6, 2, 'DetailChooseFirst', 'Please select a variant first', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1359, 'frontend/custom/ajax', 6, 2, 'CustomAjaxActionClose', 'close', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1360, 'Frontend', 6, 2, 'sMailConfirmation', 'Thank you. You will receive a confirmation email in a few minutes. Click on the link in this email to confirm your registration.', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1361, 'frontend', 6, 2, 'AccountLoginTitle', 'login', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1362, 'frontend/ticket/listing', 6, 2, 'TicketInfoDate', 'date', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1363, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterLabelAddVoucher', 'add gift card', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1364, 'frontend/checkout/cart_footer_left', 6, 2, 'CheckoutFooterAddVoucherLabelInline', 'gift card number', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1365, 'frontend/widgets/paypal/logo', 6, 2, 'WidgetPaypalText', 'PayPal', '0000-00-00 00:00:00', '2010-09-28 11:54:19'),
(1366, 'frontend/detail/tabs', 6, 2, 'DetailTabsAccessories ', 'Accessories', '2010-09-27 16:31:26', '2010-10-17 22:14:22'),
(1367, 'frontend', 1, 1, 'CheckoutArticleLessStock', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern. (#0 von #1 lieferbar).', '2010-09-27 17:36:07', '2010-09-28 11:54:19'),
(1372, 'documents/index', 1, 1, 'DocumentIndexUstID', 'USt-IdNr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1371, 'documents/index', 1, 1, 'DocumentIndexCustomerID', 'Kunden-Nr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1396, 'documents/index', 6, 2, 'DocumentIndexCustomerID', 'Customernr.:', '2010-09-28 13:02:44', '2010-09-28 13:02:44'),
(1397, 'frontend/search/fuzzy_left', 6, 2, 'SearchLeftHeadlineCutdown', 'Suchergebnis einschr&auml;nken', '2010-09-29 16:28:49', '2010-09-29 16:28:49'),
(1398, 'frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineCutdown', 'Suchergebnis einschr&auml;nken', '2010-09-29 17:49:49', '2010-09-29 17:49:49'),
(1399, 'frontend', 1, 1, 'AccountPasswordNotEqual', 'Die Passwörter stimmen nicht überein.', '2010-09-29 20:32:08', '2010-09-29 20:32:08'),
(1400, 'backend/index/index', 1, 1, 'IndexTitle', 'Shopware {$this->config(''Version'')}  (Rev. 3650, 18.10.2010) - Backend (c)2010,2011 shopware AG', '2010-09-29 22:30:38', '2010-10-17 12:08:50'),
(1401, 'backend/plugin/viewport', 1, 1, 'tree_titel', 'Plugins', '2010-09-30 10:00:13', '2010-09-30 10:00:13'),
(1430, 'frontend', 1, 1, 'CheckoutArticleNoStock', 'Leider können wir den von Ihnen gewünschten Artikel nicht mehr in ausreichender Stückzahl liefern.', '2010-10-05 23:11:09', '2010-10-05 23:11:09'),
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
(1435, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsTable', 'Tabellen-Ansicht', '2010-10-06 22:24:50', '2010-10-06 22:24:50'),
(1436, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsList', 'Listen-Ansicht', '2010-10-06 22:24:50', '2010-10-06 22:24:50'),
(1419, 'frontend/checkout/confirm', 1, 1, 'ConfirmDoPayment', 'Zahlung durchführen', '2010-10-05 13:53:10', '2010-10-05 13:53:10'),
(1418, 'frontend/checkout/shipping_costs', 1, 1, 'DispatchHeadNotice', '', '2010-10-05 09:34:18', '2010-10-05 09:34:18'),
(1434, 'frontend/checkout/confirm_item', 1, 1, 'CheckoutItemLaststock', 'NICHT LIEFERBAR', '2010-10-06 19:56:58', '2010-10-06 19:56:58'),
(1433, 'frontend/checkout/confirm', 1, 1, 'ConfirmErrorStock', 'Ein Artikel aus Ihrer Bestellung ist nicht mehr verfügbar! Bitte entfernen Sie die Position aus dem Warenkorb!', '2010-10-06 19:41:24', '2010-10-06 19:41:24'),
(1432, 'frontend/checkout/ajax_add_article', 1, 1, 'ListingBoxArticleStartsAt', 'ab', '2010-10-06 19:29:43', '2010-10-06 19:29:43'),
(1437, 'frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsTitle', 'Anzeige wählen:', '2010-10-06 23:35:38', '2010-10-16 16:50:50'),
(1438, 'frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddHeaderError', 'Hinweis:', '2010-10-07 14:24:05', '2010-10-07 14:24:05'),
(1439, 'backend/activate/skeleton', 1, 1, 'WindowTitle', 'Cache leeren', '2010-10-07 16:05:16', '2010-10-07 16:05:16'),
(1446, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeadDispatch', 'Versandart:', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1447, 'frontend/checkout/confirm', 1, 1, 'ConfirmLabelDispatch', 'Aktuell ausgewählte Versandart', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1448, 'frontend/checkout/confirm', 1, 1, 'ConfirmLinkChangeDispatch', 'Ändern', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1449, 'frontend/checkout/confirm', 1, 1, 'ConfirmHeadDispatchNotice', 'Versandinformationen', '2010-10-07 21:09:32', '2010-10-07 21:09:32'),
(1514, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexSimilaryArticlesSlider', 'Ähnliche Artikel wie die, die Sie sich angesehen haben:', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1515, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexSupplierSlider', 'Unsere Top Marken', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1455, 'frontend/plugins/index/tagcloud', 1, 1, 'TagcloudHead', 'Tagwolke', '2010-10-08 16:11:27', '2010-10-08 16:11:27'),
(1456, 'frontend/plugins/index/topseller', 1, 1, 'TopsellerHeading', 'Topseller', '2010-10-08 16:11:27', '2010-10-09 09:50:03'),
(1457, 'frontend/plugins/index/topseller', 1, 1, 'WidgetsTopsellerNoPicture', 'Kein Bild vorhanden', '2010-10-08 16:11:27', '2010-10-16 11:26:45'),
(1458, 'backend/plugins/coupons/skeleton', 1, 1, 'WindowTitle', 'Coupon Verwaltung', '2010-10-08 16:31:25', '2010-10-08 16:31:25'),
(1513, 'frontend/plugins/recommendation/blocks_listing', 1, 1, 'IndexNewArticlesSlider', 'Neu im Sortiment:', '2010-10-15 00:35:20', '2010-10-15 00:35:20'),
(1472, 'frontend/home/index', 1, 1, 'WidgetsBlogHeadline', 'Blog', '2010-10-08 18:29:11', '2010-10-16 11:26:56'),
(1473, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaHttpContentType', 'text/html; charset=iso-8859-1', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1474, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaAuthor', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1475, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaCopyright', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1476, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaRobots', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1477, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaRevisit', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1478, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaKeywordsStandard', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1479, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaDescriptionStandard', '', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1480, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaShortcutIcon', '{link file=''frontend/_resources/favicon.ico''}', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
(1481, 'templates/_default/frontend/index/header.tpl', 1, 1, 'IndexMetaMsNavButtonColor', '#dd4800', '2010-10-08 18:29:11', '2010-10-08 18:29:11'),
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
(1496, 'newsletter/index/footer', 1, 1, 'NewsletterFooterCopyright', 'Copyright © 2010 shopware AG - Alle Rechte vorbehalten.', '2010-10-12 10:05:03', '2010-10-12 10:05:03'),
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
(1508, 'documents/index_sr', 1, 1, 'DocumentIndexPageCounter', 'Seite {$page+1} von {$Pages|@count}', '2010-10-13 01:35:47', '2010-10-13 01:35:47');
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(1509, 'frontend', 1, 1, 'CheckoutSelectPremiumVariant', 'Bitte wählen Sie eine Variante aus, um den gewünschte Prämie in den Warenkorb zu legen.', '2010-10-13 17:38:03', '2010-10-13 17:38:03'),
(1510, 'frontend', 1, 1, 'CheckoutArticleNotFound', 'Artikel konnte nicht gefunden werden.', '2010-10-13 17:43:06', '2010-10-13 17:43:06'),
(1519, 'frontend', 1, 1, 'RegisterPasswordNotEqual', 'Die Passwörter stimmen nicht überein.', '2010-10-15 14:29:54', '2010-10-15 14:29:54'),
(1520, 'frontend/checkout/finish_header', 1, 1, 'CartColumnTotal', 'Summe', '2010-10-15 14:35:34', '2010-10-15 14:35:34'),
(1521, 'frontend/checkout/finish_item', 1, 1, 'CartItemInfoFree', '', '2010-10-15 14:35:34', '2010-10-15 14:35:34'),
(1522, 'backend/snippet/skeleton', 6, 2, 'WindowTitle', 'Textbausteine', '2010-10-15 16:57:50', '2010-10-15 16:57:50'),
(1523, 'frontend/checkout/ajax_add_article', 6, 2, 'AjaxAddLinkConfirm', 'check out', '2010-10-15 17:01:48', '2010-10-15 17:02:07'),
(1524, 'frontend/account/ajax_login', 1, 1, 'LoginLabelNoAccount', 'Kein Kundenkonto erstellen', '2010-10-16 08:36:09', '2010-10-16 08:36:09'),
(1525, 'frontend/custom/ajax', 1, 1, 'LoginActionClose', '', '2010-10-16 08:53:15', '2010-10-16 08:53:15'),
(1526, 'frontend/account/login', 1, 1, 'AccountLoginTitle', '', '2010-10-16 09:41:49', '2010-10-16 09:41:49'),
(1527, 'frontend/account/login', 1, 1, 'LoginLabelNoAccount', 'Kein Kundenkonto erstellen', '2010-10-16 09:42:13', '2010-10-16 09:42:13'),
(1543, 'backend/license/skeleton', 1, 1, 'WindowTitle', 'Lizenzen', '2010-10-17 19:12:33', '2010-10-17 19:12:33'),
(1544, 'backend/plugins/recommendation/skeleton', 1, 1, 'WindowTitle', 'Slider-Komponenten', '2010-10-17 19:25:09', '2010-10-17 19:25:09'),
(1545, 'frontend/account/select_billing', 1, 1, 'SelectBillingTitle', 'Adresse auswählen', '2010-10-18 00:29:20', '2010-10-18 00:57:55'),
(1541, 'frontend/plugins/advanced_menu/advanced_menu', 1, 1, 'IndexLinkHome', 'Home', '2010-10-17 13:45:56', '2010-10-17 13:45:56'),
(1542, 'frontend/account/downloads', 1, 1, 'MyDownloadsTitle', 'Meine Sofortdownloads', '2010-10-17 18:52:49', '2010-10-17 18:54:46'),
(1536, 'frontend/index/search', 1, 1, 'IndexSearchFieldValue', 'Suche:', '2010-10-16 15:53:05', '2010-10-16 15:53:05'),
(1537, 'frontend/compare/add_article', 1, 1, 'CompareHeaderTitle', 'Artikel vergleichen', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1538, 'frontend/compare/add_article', 1, 1, 'LoginActionClose', '', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1539, 'frontend/compare/add_article', 1, 1, 'CompareActionClose', '', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1540, 'frontend/compare/add_article', 1, 1, 'CompareInfoMaxReached', 'Es können maximal 5 Artikel in einem Schritt verglichen werden', '2010-10-17 09:10:41', '2010-10-17 09:10:41'),
(1546, 'frontend/account/billing', 1, 1, 'ChangeBillingTitle', 'Rechnungsadresse ändern', '2010-10-18 00:29:20', '2010-10-18 00:56:36'),
(1547, 'frontend/account/shipping', 1, 1, 'ChangeShippingTitle', 'Lieferadresse ändern', '2010-10-18 00:29:59', '2010-10-18 00:57:08'),
(1548, 'frontend/checkout/finish_footer', 1, 1, 'CheckoutFinishTaxInformation', 'Der Empfänger der Leistung schuldet die Steuer', '2010-10-18 00:31:38', '2010-10-18 00:31:38'),
(1549, 'frontend/account/payment', 1, 1, 'ChangePaymentTitle', 'Zahlungsart ändern', '2010-10-18 00:57:15', '2010-10-18 00:57:25'),
(1550, 'frontend/account/select_shipping', 1, 1, 'SelectShippingTitle', 'Adresse auswählen', '2010-10-18 00:58:04', '2010-10-18 00:58:11');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_states`
--

CREATE TABLE IF NOT EXISTS `s_core_states` (
  `id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `group` varchar(25) NOT NULL,
  `mail` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_core_states`
--

INSERT INTO `s_core_states` (`id`, `description`, `position`, `group`, `mail`) VALUES
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
(-1, 'Abgebrochen', 25, 'state', 0),
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
-- Tabellenstruktur für Tabelle `s_core_statistics`
--

CREATE TABLE IF NOT EXISTS `s_core_statistics` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `chart` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `table` int(1) NOT NULL,
  `leaf` int(1) NOT NULL,
  `dtyp` int(1) NOT NULL,
  `description` text NOT NULL,
  `header` text NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=22 ;

--
-- Daten für Tabelle `s_core_statistics`
--

INSERT INTO `s_core_statistics` (`id`, `name`, `file`, `chart`, `type`, `table`, `leaf`, `dtyp`, `description`, `header`, `position`) VALUES
(1, 'Schnell-&Uuml;bersicht', 'condata', 'MSColumn3DLineDY', 'file', 1, 1, 1, '', 'header:Datum;name:Datum;sortable:false;width:150;date:true#header:Bestellungen;name:Bestellungen;sortable:false;width:150;summaryType:sum#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum#header:Abgr. Warenkoerbe;name:Abgebrochene Warenkoerbe;sortable:false;width:150;summaryType:sum#header:Neukunden;name:Neukunden;sortable:false;width:150;summaryType:sum#header:Besucher;name:Visits;sortable:false;width:150;summaryType:sum#header:Seitenaufrufe;name:Hits;sortable:false;width:150;summaryType:sum', 1),
(2, 'Rating-&Uuml;bersicht', 'conrate', 'MSColumn3DLineDY', 'file', 1, 1, 1, '', 'header:Datum;name:Datum;width:150;sortable:false#header:Order Abandonment Rate;name:oar;width:150;sortable:false#header:Basket Conversion Rate;name:bcr;width:150;sortable:false#header:Order Conversion Rate;name:ocr;width:150;sortable:false#header:Basket/Visit Conversion Rate;name:bvcr;width:150;sortable:false', 2),
(3, 'Conversion-Rate', 'conversion', 'MSColumn3DLineDY', 'file', 1, 1, 2, '', 'header:Woche;name:Woche;sortable:false;width:150#header:Conversion-Rate;name:Conversion-Rate;sortable:false;width:150#header:Bestellungen;name:Bestellungen;sortable:false;width:150;summaryType:sum#header:Visits;name:Visits;sortable:false;width:150;summaryType:sum', 3),
(5, 'Umsatz nach Zahlungsart', 'amount_payment', 'Doughnut3D', 'file', 1, 1, 4, '', 'header:Zahlungsart;name:Zahlungsart;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum#header:Prozent;name:Prozent;sortable:false;width:150;summaryType:sum', 5),
(6, 'Umsatz nach Hersteller', 'amount_supplier', '', 'file', 1, 1, 4, '', 'header:Hersteller;name:Hersteller;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum#header:Prozent;name:Prozent;sortable:false;width:150;summaryType:sum', 6),
(7, 'Umsatz nach Kalenderwochen', 'amount_week', 'MSCombi2D', 'file', 1, 1, 2, '', 'header:Woche;name:Woche;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150', 7),
(8, 'Durchs. Umsatz nach Wochentagen', 'amount_weekday', 'Bar2D', 'file', 1, 1, 1, '', 'header:Wochentag;name:Wochentag;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150#header:Anzahl;name:Count;sortable:false;width:150', 8),
(9, 'Durchs. Umsatz nach Uhrzeit', 'amount_daytime', 'MSCombiDY2D', 'file', 1, 1, 1, '', 'header:Uhrzeit;name:Stunde;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150', 9),
(10, 'Umsatz nach Monaten', 'amount_month', 'MSCombi2D', 'file', 1, 1, 3, '', 'header:Monat;name:Monat;sortable:false;width:150#header:Jahr;name:Jahr;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum', 10),
(11, 'Umsatz nach Referer', 'referer_user', '', 'file', 1, 1, 1, '', 'header:Host;name:Host;sortable:false;width:150#header:Ges. Umsatz;name:Umsatz;sortable:false;width:150#header:Lead-Wert;name:Umsatz/Bestellungen;sortable:false;width:150#header:Kundenwert;name:Kundenwert;sortable:false;width:150#header:Umsatz Neuk.;name:Umsatz Neukunden;sortable:false;width:150#header:Umsatz Altk.;name:Umsatz Altkunden;sortable:false;width:150#header:Bestellungen;name:Bestellungen;sortable:false;width:150#header:Neukunden;name:Neukunden;sortable:false;width:150#header:Altkunden;name:Altkunden;sortable:false;width:150#header:Umsatz/Neuk.;name:Umsatz/Neukunden;sortable:false;width:150#header:Umsatz/Altk.;name:Umsatz/Altkunden;sortable:false;width:150', 11),
(12, 'Umsatz &Uuml;bersicht', 'forecast', '', 'file', 1, 1, 0, '', 'header:Beschreibung;name:Beschreibung;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150#header:Tagesumsatz;name:Tagesumsatz;sortable:false;width:150#header:Bestellungen;name:Bestellungen;sortable:false;width:150#header:Bestell. pro Tag;name:Bestellungen pro Tag;sortable:false;width:150#header:Tage;name:Tage;sortable:false;width:150', 12),
(13, 'Umsatz nach Partnern', 'amount_partner', 'Bar2D', 'file', 1, 1, 1, '', 'header:Tracking Code;name:Tracking Code;sortable:false;width:150#header:Partner;name:Partner;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum', 13),
(14, 'Kunden nach L&auml;ndern', 'amount_user_country', 'Doughnut3D', 'file', 0, 1, 0, '', 'header:Land;name:Land;sortable:false;width:150#header:Anzahl;name:Anzahl;sortable:false;width:150', 14),
(15, 'Besucher Zugriffsquellen', 'referer', '', 'file', 1, 1, 1, '', 'header:Anzahl;name:Anzahl;sortable:false;width:150#header:Referer;name:referer;sortable:false;width:150#header:Options;name:Options;sortable:false;width:150', 15),
(16, 'Auswertung Suche', 'search', '', 'file', 1, 1, 1, '', 'header:Suchwort;name:searchterm;sortable:false;width:150#header:Anzahl;name:count;sortable:false;width:150#header:Suchergebnisse;name:results;sortable:false;width:150', 16),
(17, 'Artikel nach Verk&auml;ufen', 'article.sales', '', 'file', 1, 1, 1, '', 'header:Bestellnr.;name:articleordernumber;sortable:false;width:150#header:Namen;name:name;sortable:false;width:150#header:Sales;name:sales;sortable:false;width:150;summaryType:sum#header:Aufrufe;name:impressions;sortable:false;width:150;summaryType:sum', 17),
(18, 'Artikel nach Aufrufen', 'article.views', '', 'file', 1, 1, 1, '', 'header:Namen;name:name;sortable:false;width:150#header:Aufrufe;name:impressions;sortable:false;width:150;summaryType:sum#header:Sales;name:sales;sortable:false;width:150;summaryType:sum', 18),
(19, 'Anteil Neu-/Stammkunden', 'new_old_user', 'MSCombiDY2D', 'file', 1, 1, 2, '', 'header:Kalenderwoche;name:Woche;sortable:false;width:150#header:Neukunden;name:Anteil Neukunden;sortable:false;width:150#header:Stammkunden;name:Anteil Stammkunden;sortable:false;width:150', 19),
(20, 'Umsatz nach Versandart', 'amount_dispatch', 'Doughnut3D', 'file', 1, 1, 4, '', 'header:Versandart;name:Versandart;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum#header:Prozent;name:Prozent;sortable:false;width:150;summaryType:sum', 5),
(21, 'Subshop-Auswertung', 'amount_subshop', '', 'file', 1, 1, 1, '', 'header:Shop;name:Shop;sortable:false;width:150#header:Umsatz;name:Umsatz;sortable:false;width:150;summaryType:sum#header:Bestellungen;name:Bestellungen;sortable:false;width:150;summaryType:sum', 5);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_subscribes`
--

CREATE TABLE IF NOT EXISTS `s_core_subscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe` varchar(255) NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `listener` varchar(255) NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `position` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribe` (`subscribe`,`type`,`listener`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=38 ;

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
(20, 'Enlight_Bootstrap_InitResource_Template', 0, 'Shopware_Plugins_Core_Template_Bootstrap::onInitResourceTemplate', 6, 0),
(21, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_ViewportForward_Bootstrap::onPreDispatch', 11, 10),
(22, 'Enlight_Bootstrap_InitResource_License', 0, 'Shopware_Plugins_Core_License_Bootstrap::onInitResourceLicense', 18, 0),
(23, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Compare_Bootstrap::onPostDispatch', 20, 0),
(24, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Frontend_Statistics_Bootstrap::onDispatchLoopShutdown', 31, 0),
(25, 'Enlight_Plugins_ViewRenderer_FilterRender', 0, 'Shopware_Plugins_Frontend_Seo_Bootstrap::onFilterRender', 22, 0),
(26, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Seo_Bootstrap::onPostDispatch', 22, 0),
(27, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_TagCloud_Bootstrap::onPostDispatch', 34, 0),
(28, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Ticket_Bootstrap::onPostDispatch', 25, 0),
(29, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Frontend_ViewportDispatcher_Bootstrap::onPreDispatch', 27, 50),
(30, 'Enlight_Controller_Front_StartDispatch', 0, 'Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onStartDispatch', 19, 0),
(31, 'Enlight_Controller_Router_Route', 0, 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onRoute', 24, 10),
(32, 'Enlight_Controller_Router_Assemble', 0, 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onAssemble', 24, 10),
(33, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_Paypal_Bootstrap::onPostDispatch', 28, 0),
(34, 'Enlight_Controller_Dispatcher_ControllerPath_Frontend_Paypal', 0, 'Shopware_Plugins_Frontend_Paypal_Bootstrap::onGetControllerPath', 28, 0),
(35, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Frontend_Paypal_Bootstrap::onPreDispatch', 28, 10),
(36, 'Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onPreDispatch', 35, 0),
(37, 'Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Frontend_LastArticles_Bootstrap::onPostDispatch', 23, 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_tax`
--

CREATE TABLE IF NOT EXISTS `s_core_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax` double NOT NULL DEFAULT '0',
  `description` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `s_core_tax`
--

INSERT INTO `s_core_tax` (`id`, `tax`, `description`) VALUES
(1, 19, '19%'),
(4, 7, '7 %');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_translations`
--

CREATE TABLE IF NOT EXISTS `s_core_translations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objecttype` varchar(255) NOT NULL,
  `objectdata` longtext NOT NULL,
  `objectkey` varchar(255) NOT NULL,
  `objectlanguage` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `objecttype` (`objecttype`,`objectkey`,`objectlanguage`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=60 ;

--
-- Daten für Tabelle `s_core_translations`
--

INSERT INTO `s_core_translations` (`id`, `objecttype`, `objectdata`, `objectkey`, `objectlanguage`) VALUES
(58, 'config_payment', 'a:13:{i:3;a:2:{s:11:"description";s:16:"cash on delivery";s:21:"additionaldescription";s:105:"Available in Germany only. Please note that at delivery, you will be asked to pay an additional EUR 2,00.";}i:7;a:2:{s:11:"description";s:10:"Creditcard";s:21:"additionaldescription";s:162:"Testing the United-Online-Services creditcard-payment is very easy. Just enter the following data: \nType: Mastercard, Nummer: 4111111111111111, CVC/ CVV-Code: 111";}i:2;a:2:{s:11:"description";s:5:"Debit";s:21:"additionaldescription";s:11:"Insert text";}i:4;a:2:{s:11:"description";s:7:"Invoice";s:21:"additionaldescription";s:152:"You pay easy and secure by invoice.\n\nIt is for example possible to reopen the invoice account after the costumer made his second order. (Riskmanagement)";}i:5;a:2:{s:11:"description";s:10:"Prepayment";s:21:"additionaldescription";s:194:"Once we receive the funds you send us via bank transfer, we will ship your order. We cannot be responsible for paying bank fees, so make sure the full invoice amount will be received on our end.";}i:17;a:1:{s:21:"additionaldescription";s:98:"A quick and easy way to pay with your creditcard. We accept: VISA / Master Card / American Express";}i:18;a:1:{s:21:"additionaldescription";s:209:"Carefree shopping on the internet - you can do it now in over 8,000 affiliated online shops using DIRECTebanking.com. You benefit not only from immediate delivery but also from our consumer protection policy.\n";}i:6;a:1:{s:21:"additionaldescription";s:163:"Please enter your real account data, who are connected to your address. An automatic process will check the address.\n\nAll entries will not be charged in this demo!";}i:8;a:1:{s:21:"additionaldescription";s:147:"Testing Giropay from our partner United-Online-Services in this demo isn´t possible.\nIf you are interested in getting an account please contact us.";}i:15;a:1:{s:21:"additionaldescription";s:7:"Invoice";}i:14;a:1:{s:21:"additionaldescription";s:10:"Prepayment";}i:11;a:1:{s:21:"additionaldescription";s:64:"Pay the quick and secured way! Paying direct! No account needed!";}i:12;a:1:{s:21:"additionaldescription";s:64:"Pay the quick and secured way! Paying direct! No account needed!";}}', '1', 'en'),
(59, 'config_snippets', 'a:546:{s:15:"sPaymentESDInfo";a:1:{s:5:"value";s:69:"Purchase of direct downloads is only possible by debit or credit card";}s:8:"sAGBText";a:1:{s:5:"value";s:181:"I have read the <a href="{$sBasefile}?sViewport=custom&cCUSTOM=4" title="Terms"><span style="text-decoration:underline;">Terms</span></a> of your shop and agree with their coverage.";}s:10:"sOrderInfo";a:1:{s:5:"value";s:112:"(Optionaler Freitext)If you pay debit or with your creditcard your bank account will be charged after five days.";}s:15:"sRegister_right";a:1:{s:5:"value";s:183:"<p>\nInsert your right of withdrawl here.\n<br /><br />\n<a href="{$sBasefile}?sViewport=custom&cCUSTOM=8" title="Right of Withdrawl">more informations to your right of withdrawl</a></p>";}s:28:"sNewsletterOptionUnsubscribe";a:1:{s:5:"value";s:26:"unsubscribe the newsletter";}s:26:"sNewsletterOptionSubscribe";a:1:{s:5:"value";s:24:"subscribe the newsletter";}s:15:"sNewsletterInfo";a:1:{s:5:"value";s:151:"Subsribe and get our newsletter.<br />\nOf course you can cancel this newsletter any time. Use the hyperlink in your eMail or visit this website again. ";}s:17:"sNewsletterButton";a:1:{s:5:"value";s:4:"Save";}s:20:"sNewsletterLabelMail";a:1:{s:5:"value";s:19:"Your eMail-address:";}s:22:"sNewsletterLabelSelect";a:1:{s:5:"value";s:10:"I want to:";}s:17:"sInfoEmailDeleted";a:1:{s:5:"value";s:32:"Your eMail-address were deleted.";}s:19:"sInfoEmailRegiested";a:1:{s:5:"value";s:35:"Thanks. We added your eMail-address";}s:16:"sErrorEnterEmail";a:1:{s:5:"value";s:31:"Please specify an email address";}s:16:"sErrorForgotMail";a:1:{s:5:"value";s:31:"Please enter your email address";}s:10:"sDelivery1";a:1:{s:5:"value";s:47:"Ready for shipment,<br/>\nShipping time 1-3 days";}s:17:"sErrorLoginActive";a:1:{s:5:"value";s:84:"Your account has been disabled, to clarify please get in contact with us personally!";}s:27:"sAccountACommentisdeposited";a:1:{s:5:"value";s:20:"A Comment is leaved!";}s:15:"sAccountArticle";a:1:{s:5:"value";s:7:"Article";}s:22:"sAccountBillingAddress";a:1:{s:5:"value";s:15:"Billing Address";}s:15:"sAccountComment";a:1:{s:5:"value";s:11:"Annotation:";}s:16:"sAccountcompany ";a:1:{s:5:"value";s:7:"Company";}s:16:"sAccountDownload";a:1:{s:5:"value";s:8:"Download";}s:19:"sAccountDownloadNow";a:1:{s:5:"value";s:12:"Download now";}s:29:"sAccountDownloadssortedbydate";a:1:{s:5:"value";s:29:"Your downloads sorted by date";}s:24:"sAccountErrorhasoccurred";a:1:{s:5:"value";s:22:"An Error has occurred!";}s:12:"sAccountFree";a:1:{s:5:"value";s:4:"FREE";}s:12:"sAccountfrom";a:1:{s:5:"value";s:5:"From:";}s:18:"sAccountgrandtotal";a:1:{s:5:"value";s:12:"Grand total:";}s:18:"sAccountIwanttoget";a:1:{s:5:"value";s:31:"Yes, I want to receive the free";}s:23:"sAccountmethodofpayment";a:1:{s:5:"value";s:15:"Choosen Payment";}s:14:"sAccountmodify";a:1:{s:5:"value";s:6:"Modify";}s:10:"sAccountMr";a:1:{s:5:"value";s:2:"Mr";}s:10:"sAccountMs";a:1:{s:5:"value";s:2:"Ms";}s:19:"sAccountNewPassword";a:1:{s:5:"value";s:14:"New password*:";}s:26:"sAccountnewslettersettings";a:1:{s:5:"value";s:24:"Your Newsletter settings";}s:14:"sAccountNumber";a:1:{s:5:"value";s:8:"Quantity";}s:21:"sAccountOrdercanceled";a:1:{s:5:"value";s:19:"Order was cancelled";}s:27:"sAccountOrderhasbeenshipped";a:1:{s:5:"value";s:22:"Order has been shipped";}s:23:"sAccountOrderinprogress";a:1:{s:5:"value";s:17:"Order in progress";}s:28:"sAccountOrdernotvetprocessed";a:1:{s:5:"value";s:32:"Order has not been processed yet";}s:19:"sAccountOrdernumber";a:1:{s:5:"value";s:12:"Ordernumber:";}s:29:"sAccountOrderpartiallyshipped";a:1:{s:5:"value";s:27:"Order was partially shipped";}s:26:"sAccountOrderssortedbydate";a:1:{s:5:"value";s:21:"Orders sorted by date";}s:18:"sAccountOrderTotal";a:1:{s:5:"value";s:12:"Order total:";}s:23:"sAccountPackagetracking";a:1:{s:5:"value";s:17:"Package tracking:";}s:12:"sAccountplus";a:1:{s:5:"value";s:4:"plus";}s:22:"sAccountRepeatpassword";a:1:{s:5:"value";s:17:"Repeat password*:";}s:16:"sAccountShipping";a:1:{s:5:"value";s:15:"Shipping costs:";}s:23:"sAccountshippingaddress";a:1:{s:5:"value";s:16:"Shipping address";}s:21:"sAccountthenewsletter";a:1:{s:5:"value";s:11:"Newsletter!";}s:13:"sAccountTotal";a:1:{s:5:"value";s:5:"Total";}s:17:"sAccountUnitprice";a:1:{s:5:"value";s:10:"Unit price";}s:22:"sAccountYouraccessdata";a:1:{s:5:"value";s:16:"Your access data";}s:24:"sAccountYouremailaddress";a:1:{s:5:"value";s:21:"Your e-mail address*:";}s:24:"sAccountyourSerialnumber";a:1:{s:5:"value";s:19:"Your Serial Number:";}s:26:"sAccountyourSerialnumberto";a:1:{s:5:"value";s:21:"Your Serial Number to";}s:19:"sAjaxcomparearticle";a:1:{s:5:"value";s:16:"Compared Article";}s:18:"sAjaxdeletecompare";a:1:{s:5:"value";s:14:"Delete Compare";}s:17:"sAjaxstartcompare";a:1:{s:5:"value";s:13:"Start Compare";}s:9:"sArticle1";a:1:{s:5:"value";s:12:"1 (very bad)";}s:10:"sArticle10";a:1:{s:5:"value";s:14:"10 (excellent)";}s:9:"sArticle2";a:1:{s:5:"value";s:1:"2";}s:9:"sArticle3";a:1:{s:5:"value";s:1:"3";}s:9:"sArticle4";a:1:{s:5:"value";s:1:"4";}s:9:"sArticle5";a:1:{s:5:"value";s:1:"5";}s:9:"sArticle6";a:1:{s:5:"value";s:1:"6";}s:9:"sArticle7";a:1:{s:5:"value";s:1:"7";}s:9:"sArticle8";a:1:{s:5:"value";s:1:"8";}s:9:"sArticle9";a:1:{s:5:"value";s:1:"9";}s:19:"sArticleaccessories";a:1:{s:5:"value";s:11:"Accessories";}s:19:"sArticleaddtobasked";a:1:{s:5:"value";s:11:"add to cart";}s:20:"sArticleaddtonotepad";a:1:{s:5:"value";s:16:"Add to favorites";}s:21:"sArticleafterageckeck";a:1:{s:5:"value";s:52:"Attention! Delivery only after successful age check!";}s:24:"sArticleallmanufacturers";a:1:{s:5:"value";s:22:"Show all manufacturers";}s:14:"sArticleamount";a:1:{s:5:"value";s:9:"Quantity:";}s:11:"sArticleand";a:1:{s:5:"value";s:3:"and";}s:22:"sArticlearticleperpage";a:1:{s:5:"value";s:16:"Article per page";}s:21:"sArticleavailableasan";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:26:"sArticleavailabledownloads";a:1:{s:5:"value";s:20:"Available downloads:";}s:21:"sArticleAvailablefrom";a:1:{s:5:"value";s:12:"Available as";}s:26:"sArticleavailableimmediate";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:12:"sArticleback";a:1:{s:5:"value";s:4:"Back";}s:20:"sArticleblockpricing";a:1:{s:5:"value";s:13:"Block pricing";}s:10:"sArticleby";a:1:{s:5:"value";s:3:"By:";}s:24:"sArticlechoosefirstexecu";a:1:{s:5:"value";s:32:"Attention! Choose version first!";}s:22:"sArticlecollectvoucher";a:1:{s:5:"value";s:34:"Tell a friend and catch a voucher!";}s:15:"sArticleCompare";a:1:{s:5:"value";s:7:"Compare";}s:23:"sArticlecustomerreviews";a:1:{s:5:"value";s:20:"Customer reviews for";}s:17:"sArticledatasheet";a:1:{s:5:"value";s:9:"Datasheet";}s:12:"sArticledays";a:1:{s:5:"value";s:4:"Days";}s:24:"sArticledaysshippingfree";a:1:{s:5:"value";s:14:"Shipping free!";}s:20:"sArticledeliverytime";a:1:{s:5:"value";s:13:"Shipping time";}s:19:"sArticledescription";a:1:{s:5:"value";s:11:"Description";}s:16:"sArticledownload";a:1:{s:5:"value";s:8:"Download";}s:19:"sArticleeightpoints";a:1:{s:5:"value";s:8:"8 Points";}s:23:"sArticleenterthenumbers";a:1:{s:5:"value";s:50:"Please enter the numbers in the following text box";}s:27:"sArticlefilloutallredfields";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:18:"sArticlefivepoints";a:1:{s:5:"value";s:8:"5 Points";}s:18:"sArticlefourpoints";a:1:{s:5:"value";s:8:"4 Points";}s:20:"sArticlefreeshipping";a:1:{s:5:"value";s:14:"Shipping Free!";}s:12:"sArticlefrom";a:1:{s:5:"value";s:4:"from";}s:26:"sArticlefurtherinformation";a:1:{s:5:"value";s:19:"further information";}s:19:"sArticlegetavoucher";a:1:{s:5:"value";s:16:"is your voucher*";}s:20:"sArticlehighestprice";a:1:{s:5:"value";s:13:"Highest Price";}s:12:"sArticleincl";a:1:{s:5:"value";s:5:"incl.";}s:19:"sArticleinthebasket";a:1:{s:5:"value";s:24:"add to you shopping cart";}s:17:"sArticleitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:16:"sArticlelanguage";a:1:{s:5:"value";s:9:"Language:";}s:13:"sArticlelegal";a:1:{s:5:"value";s:5:"legal";}s:14:"sArticlelooked";a:1:{s:5:"value";s:11:"Last viewed";}s:19:"sArticlelowestprice";a:1:{s:5:"value";s:12:"Lowest Price";}s:19:"sArticlemainarticle";a:1:{s:5:"value";s:12:"Main article";}s:21:"sArticlematchingitems";a:1:{s:5:"value";s:27:"Frequently Bought Together:";}s:23:"sArticleMoreinformation";a:1:{s:5:"value";s:20:"Get more information";}s:28:"sArticlemoreinformationabout";a:1:{s:5:"value";s:26:"Get more information about";}s:11:"sArticlenew";a:1:{s:5:"value";s:3:"NEW";}s:12:"sArticlenext";a:1:{s:5:"value";s:4:"Next";}s:18:"sArticleninepoints";a:1:{s:5:"value";s:8:"9 Points";}s:17:"sArticlenoPicture";a:1:{s:5:"value";s:20:"No picture available";}s:10:"sArticleof";a:1:{s:5:"value";s:2:"by";}s:16:"sArticleonepoint";a:1:{s:5:"value";s:7:"1 Point";}s:19:"sArticleonesiteback";a:1:{s:5:"value";s:13:"One Site back";}s:22:"sArticleonesiteforward";a:1:{s:5:"value";s:16:"One Site forward";}s:20:"sArticleonthenotepad";a:1:{s:5:"value";s:16:"add to favorites";}s:19:"sArticleordernumber";a:1:{s:5:"value";s:10:"Order No.:";}s:23:"sArticleotherarticlesof";a:1:{s:5:"value";s:22:"Other articles made by";}s:20:"sArticleourcommenton";a:1:{s:5:"value";s:13:"Our review to";}s:11:"sArticleout";a:1:{s:5:"value";s:3:"out";}s:16:"sArticleoverview";a:1:{s:5:"value";s:8:"Overview";}s:20:"sArticlepleasechoose";a:1:{s:5:"value";s:16:"Please choose...";}s:25:"sArticlepleasecompleteall";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:20:"sArticlepleaseselect";a:1:{s:5:"value";s:16:"Please choose...";}s:18:"sArticlepopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:14:"sArticleprices";a:1:{s:5:"value";s:6:"Prices";}s:18:"sArticleproductsof";a:1:{s:5:"value";s:16:"Products made by";}s:29:"sArticlequestionsaboutarticle";a:1:{s:5:"value";s:22:"Questions for article?";}s:22:"sArticlerecipientemail";a:1:{s:5:"value";s:22:"Receiver email address";}s:17:"sArticlerecommend";a:1:{s:5:"value";s:36:": tell a friend and catch a voucher!";}s:27:"sArticlerecommendandvoucher";a:1:{s:5:"value";s:32:"Tell a friend and get a voucher!";}s:16:"sArticlereleased";a:1:{s:5:"value";s:19:"unrated (universal)";}s:30:"sArticlereleasedafterverificat";a:1:{s:5:"value";s:44:"Reviews will be released after verification.";}s:19:"sArticlereleasedate";a:1:{s:5:"value";s:12:"Release Date";}s:27:"sArticlereleasedfrom12years";a:1:{s:5:"value";s:20:"Restricted 12 years+";}s:27:"sArticlereleasedfrom16years";a:1:{s:5:"value";s:20:"Restricted 16 years+";}s:27:"sArticlereleasedfrom18years";a:1:{s:5:"value";s:20:"Restricted 18 years+";}s:26:"sArticlereleasedfrom6years";a:1:{s:5:"value";s:24:"Restricted from 6 years+";}s:14:"sArticleReview";a:1:{s:5:"value";s:16:"Costumer Review:";}s:15:"sArticlereview1";a:1:{s:5:"value";s:16:"Costumer Review:";}s:15:"sArticlereviews";a:1:{s:5:"value";s:7:"Reviews";}s:12:"sArticlesave";a:1:{s:5:"value";s:5:"saved";}s:14:"sArticlescroll";a:1:{s:5:"value";s:6:"Scroll";}s:19:"sArticlesevenpoints";a:1:{s:5:"value";s:8:"7 Points";}s:16:"sArticleshipping";a:1:{s:5:"value";s:110:"<a href="{$sBasefile}?sViewport=custom&cCUSTOM=28" title="Shipping rates & policies">Shipping rates & policies";}s:27:"sArticleshippinginformation";a:1:{s:5:"value";s:33:"See our shipping rates & policies";}s:15:"sArticleshowall";a:1:{s:5:"value";s:8:"Show all";}s:28:"sArticleshowallmanufacturers";a:1:{s:5:"value";s:22:"Show all manufacturers";}s:23:"sArticlesimilararticles";a:1:{s:5:"value";s:15:"Suggested Items";}s:17:"sArticlesixpoints";a:1:{s:5:"value";s:8:"6 Points";}s:12:"sArticlesort";a:1:{s:5:"value";s:5:"Sort:";}s:15:"sArticlesummary";a:1:{s:5:"value";s:7:"Summary";}s:17:"sArticlesurcharge";a:1:{s:5:"value";s:17:"Additional charge";}s:26:"sArticlesystemrequirements";a:1:{s:5:"value";s:21:"Systemrequirement for";}s:15:"sArticletaxplus";a:1:{s:5:"value";s:8:"VAT plus";}s:16:"sArticletaxplus1";a:1:{s:5:"value";s:5:"VAT +";}s:17:"sArticletenpoints";a:1:{s:5:"value";s:9:"10 Points";}s:24:"sArticlethankyouverymuch";a:1:{s:5:"value";s:62:"Thank you very much. The recommendation was successfully sent.";}s:23:"sArticlethefieldsmarked";a:1:{s:5:"value";s:34:"Please fill in all required fields";}s:19:"sArticlethreepoints";a:1:{s:5:"value";s:8:"3 Points";}s:11:"sArticletip";a:1:{s:5:"value";s:4:"TIP!";}s:30:"sArticletipavailableasanimmedi";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:26:"sArticletipmoreinformation";a:1:{s:5:"value";s:25:"Further information about";}s:29:"sArticletipproductinformation";a:1:{s:5:"value";s:19:"Product information";}s:11:"sArticletop";a:1:{s:5:"value";s:3:"TOP";}s:30:"sArticletopaveragecustomerrevi";a:1:{s:5:"value";s:17:"Customer Reviews:";}s:30:"sArticletopImmediatelyavailabl";a:1:{s:5:"value";s:8:"In Stock";}s:14:"sArticletosave";a:1:{s:5:"value";s:4:"Save";}s:25:"sArticletoseeinthepicture";a:1:{s:5:"value";s:16:"On this picture:";}s:17:"sArticletwopoints";a:1:{s:5:"value";s:8:"2 Points";}s:13:"sArticleuntil";a:1:{s:5:"value";s:5:"until";}s:17:"sArticleupdatenow";a:1:{s:5:"value";s:10:"Update now";}s:29:"sArticlewithoutagerestriction";a:1:{s:5:"value";s:23:"Without age restriction";}s:19:"sArticleworkingdays";a:1:{s:5:"value";s:10:"Workingday";}s:25:"sArticlewriteanassessment";a:1:{s:5:"value";s:15:"Create a review";}s:20:"sArticlewriteareview";a:1:{s:5:"value";s:15:"Create a review";}s:19:"sArticlewritereview";a:1:{s:5:"value";s:15:"Create a review";}s:19:"sArticleyourcomment";a:1:{s:5:"value";s:13:"Your comment:";}s:16:"sArticleyourname";a:1:{s:5:"value";s:9:"Your Name";}s:19:"sArticleyouropinion";a:1:{s:5:"value";s:13:"Your comment:";}s:18:"sArticlezeropoints";a:1:{s:5:"value";s:8:"0 Points";}s:23:"sBasketaddedtothebasket";a:1:{s:5:"value";s:32:"was added to your shopping cart!";}s:13:"sBasketamount";a:1:{s:5:"value";s:9:"Quantity:";}s:14:"sBasketArticle";a:1:{s:5:"value";s:7:"Article";}s:30:"sBasketarticlefromourcatalogue";a:1:{s:5:"value";s:29:"Add articles from our catalog";}s:22:"sBasketarticlenotfound";a:1:{s:5:"value";s:17:"Article not found";}s:20:"sBasketasanimmediate";a:1:{s:5:"value";s:34:"Available as an immediate download";}s:23:"sBasketasasmallthankyou";a:1:{s:5:"value";s:63:"As a small thank-you, you receive this article free in addition";}s:19:"sBasketavailability";a:1:{s:5:"value";s:8:"In Stock";}s:20:"sBasketavailablefrom";a:1:{s:5:"value";s:20:"Available from stock";}s:21:"sBasketbacktomainpage";a:1:{s:5:"value";s:17:"Back to Mainpage!";}s:21:"sBasketbasketdiscount";a:1:{s:5:"value";s:13:"Cart-discount";}s:30:"sBasketbetweenfollowingpremium";a:1:{s:5:"value";s:44:"Please choose between the following premiums";}s:15:"sBasketcheckout";a:1:{s:5:"value";s:8:"Checkout";}s:30:"sBasketcheckoutcustomerswithyo";a:1:{s:5:"value";s:45:"Customers Who Bought This Item Also Bought\n  ";}s:23:"sBasketcontinueshopping";a:1:{s:5:"value";s:17:"Continue shopping";}s:30:"sBasketcustomerswithyoursimila";a:1:{s:5:"value";s:48:"Customers Viewing This Page May Be Interested in";}s:30:"sBasketdeletethisitemfrombaske";a:1:{s:5:"value";s:34:"Erase this article from the basket";}s:15:"sBasketdelivery";a:1:{s:5:"value";s:13:"Shipping time";}s:22:"sBasketdeliverycountry";a:1:{s:5:"value";s:16:"Shipping country";}s:24:"sBasketdesignatedarticle";a:1:{s:5:"value";s:50:"Favorites - selected articles for a later purchase";}s:15:"sBasketdispatch";a:1:{s:5:"value";s:16:"Mode of shipment";}s:23:"sBasketerasefromnotepad";a:1:{s:5:"value";s:37:"Erase this article from the favorites";}s:25:"sBasketforwardingexpenses";a:1:{s:5:"value";s:25:"Shipping rates & policies";}s:11:"sBasketfree";a:1:{s:5:"value";s:5:"FREE!";}s:12:"sBasketfree1";a:1:{s:5:"value";s:4:"FREE";}s:11:"sBasketfrom";a:1:{s:5:"value";s:4:"from";}s:18:"sBasketinthebasket";a:1:{s:5:"value";s:20:"add to shopping cart";}s:20:"sBasketintothebasket";a:1:{s:5:"value";s:20:"Add to shopping cart";}s:28:"sBasketItautomaticallystores";a:1:{s:5:"value";s:126:"automatically stores your personal favorite-list.\nYou can comfortably retrieve your registered articles in a subsequent visit.";}s:26:"sBasketjustthedesireditems";a:1:{s:5:"value";s:56:"Put simply the desired articles on the favorite-list and";}s:23:"sBasketlastinyourbasket";a:1:{s:5:"value";s:32:"Last article added in your cart:";}s:24:"sBasketminimumordervalue";a:1:{s:5:"value";s:37:"Attention. The minimum order value of";}s:13:"sBasketmodify";a:1:{s:5:"value";s:6:"Modify";}s:23:"sBasketmoreinformations";a:1:{s:5:"value";s:16:"More information";}s:27:"sBasketnoitemsonyournotepad";a:1:{s:5:"value";s:45:"There are no articles on your favorites-list.";}s:25:"sBasketnopictureavailable";a:1:{s:5:"value";s:20:"No picture available";}s:14:"sBasketnotepad";a:1:{s:5:"value";s:9:"Favorites";}s:20:"sBasketnotreachedyet";a:1:{s:5:"value";s:19:"is not reached yet!";}s:13:"sBasketnumber";a:1:{s:5:"value";s:8:"Quantity";}s:18:"sBasketordernumber";a:1:{s:5:"value";s:9:"Order No.";}s:14:"sBasketpayment";a:1:{s:5:"value";s:17:"Method of Payment";}s:19:"sBasketpleasechoose";a:1:{s:5:"value";s:16:"Please choose...";}s:23:"sBasketrecalculateprice";a:1:{s:5:"value";s:31:"Recalculate price - update cart";}s:26:"sBasketsaveyourpersonalfav";a:1:{s:5:"value";s:60:"Save your personal favorites - until you visit us next time.";}s:17:"sBasketshowbasket";a:1:{s:5:"value";s:18:"Show shopping cart";}s:18:"sBasketstep1basket";a:1:{s:5:"value";s:21:"Step1 - Shopping Cart";}s:15:"sBasketsubtotal";a:1:{s:5:"value";s:9:"Subtotal:";}s:10:"sBasketsum";a:1:{s:5:"value";s:5:"Total";}s:17:"sBaskettocheckout";a:1:{s:5:"value";s:9:"Checkout!";}s:15:"sBaskettotalsum";a:1:{s:5:"value";s:5:"Total";}s:16:"sBasketunitprice";a:1:{s:5:"value";s:10:"Unit price";}s:15:"sBasketweekdays";a:1:{s:5:"value";s:8:"Workdays";}s:17:"sBasketyourbasket";a:1:{s:5:"value";s:18:"Your shopping cart";}s:24:"sBasketyourbasketisempty";a:1:{s:5:"value";s:43:"There are no articles in your shopping cart";}s:21:"sCategorymanufacturer";a:1:{s:5:"value";s:12:"Manufacturer";}s:18:"sCategorynopicture";a:1:{s:5:"value";s:20:"No picture available";}s:26:"sCategoryothermanufacturer";a:1:{s:5:"value";s:19:"Other manufacturers";}s:16:"sCategoryshowall";a:1:{s:5:"value";s:8:"Show all";}s:18:"sCategorytopseller";a:1:{s:5:"value";s:9:"Topseller";}s:18:"sContentattachment";a:1:{s:5:"value";s:11:"Attachment:";}s:12:"sContentback";a:1:{s:5:"value";s:4:"Back";}s:14:"sContentbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:21:"sContentbrowseforward";a:1:{s:5:"value";s:14:"Browse forward";}s:26:"sContentcurrentlynoentries";a:1:{s:5:"value";s:30:"Currently no entries available";}s:16:"sContentdownload";a:1:{s:5:"value";s:8:"Download";}s:21:"sContententrynotfound";a:1:{s:5:"value";s:14:"No entry found";}s:21:"sContentgobackonepage";a:1:{s:5:"value";s:16:"Browse backward ";}s:12:"sContentmore";a:1:{s:5:"value";s:6:"[more]";}s:24:"sContentmoreinformations";a:1:{s:5:"value";s:17:"More information:";}s:21:"sContentonthispicture";a:1:{s:5:"value";s:16:"On this picture:";}s:20:"sCustomdirectcontact";a:1:{s:5:"value";s:14:"Direct contact";}s:19:"sCustomsitenotfound";a:1:{s:5:"value";s:14:"Page not found";}s:19:"sErrorBillingAdress";a:1:{s:5:"value";s:40:"Please fill out all fields marked in red";}s:14:"sErrorcheckout";a:1:{s:5:"value";s:8:"Checkout";}s:21:"sErrorCookiesDisabled";a:1:{s:5:"value";s:66:"To use this feature, you must have cookies enabled in your browser";}s:11:"sErrorEmail";a:1:{s:5:"value";s:35:"Please enter a valid e-mail address";}s:19:"sErrorEmailForgiven";a:1:{s:5:"value";s:40:"This email address is already registered";}s:19:"sErrorEmailNotFound";a:1:{s:5:"value";s:33:"This e-mail address was not found";}s:11:"sErrorerror";a:1:{s:5:"value";s:21:"An error has occurred";}s:23:"sErrorForgotMailUnknown";a:1:{s:5:"value";s:28:"This mail address is unknown";}s:10:"sErrorhome";a:1:{s:5:"value";s:4:"Home";}s:11:"sErrorLogin";a:1:{s:5:"value";s:50:"Your access data could not be assigned to any user";}s:23:"sErrorMerchantNotActive";a:1:{s:5:"value";s:73:"You are not registered as a reseller or your account has not approved yet";}s:29:"sErrormoreinterestingarticles";a:1:{s:5:"value";s:17:"You may also like";}s:22:"sErrororderwascanceled";a:1:{s:5:"value";s:22:"The order was canceled";}s:14:"sErrorPassword";a:1:{s:5:"value";s:57:"Please choose a password consisting at least 6 characters";}s:21:"sErrorShippingAddress";a:1:{s:5:"value";s:40:"Please fill out all fields marked in red";}s:27:"sErrorthisarticleisnolonger";a:1:{s:5:"value";s:34:"The product has been discontinued!";}s:12:"sErrorUnknow";a:1:{s:5:"value";s:29:"An unknown error has occurred";}s:16:"sErrorValidEmail";a:1:{s:5:"value";s:35:"Please enter a valid e-mail address";}s:13:"sIndexaccount";a:1:{s:5:"value";s:10:"My Account";}s:16:"sIndexactivation";a:1:{s:5:"value";s:284:"3. Activation: As soon as both satisfactory forms are given to us and provided that your signatures match, we switch you for plays from 18 freely. Afterwards we immediately send you a confirmation email. Then you can quite simply order the USK 18 title comfortably about the web shop.";}s:25:"sIndexallpricesexcludevat";a:1:{s:5:"value";s:28:"* All prices exclude VAT and";}s:25:"sIndexandpossibledelivery";a:1:{s:5:"value";s:50:"and possibly shipping fees unless otherwise stated";}s:12:"sIndexappear";a:1:{s:5:"value";s:9:"Released:";}s:13:"sIndexarticle";a:1:{s:5:"value";s:10:"article(s)";}s:19:"sIndexarticlesfound";a:1:{s:5:"value";s:18:" article(s) found!";}s:24:"sIndexavailabledownloads";a:1:{s:5:"value";s:20:"Available downloads:";}s:16:"sIndexbacktohome";a:1:{s:5:"value";s:12:"back to home";}s:12:"sIndexbasket";a:1:{s:5:"value";s:16:"My shopping cart";}s:25:"sIndexcertifiedonlineshop";a:1:{s:5:"value";s:113:"Trusted Shops certified online shop with money-back guarantee. Click on the seal in order to verify the validity.";}s:26:"sIndexchangebillingaddress";a:1:{s:5:"value";s:22:"Change billing address";}s:27:"sIndexchangedeliveryaddress";a:1:{s:5:"value";s:23:"Change delivery address";}s:19:"sIndexchangepayment";a:1:{s:5:"value";s:24:"Change method of payment";}s:19:"sIndexclientaccount";a:1:{s:5:"value";s:16:"Customer account";}s:26:"sIndexcompareupto5articles";a:1:{s:5:"value";s:42:"You can compare up to 5 items in one step!";}s:15:"sIndexcopyright";a:1:{s:5:"value";s:51:"Copyright © 2008 shopware.ag - All rights reserved.";}s:11:"sIndexcover";a:1:{s:5:"value";s:6:"Cover:";}s:14:"sIndexcurrency";a:1:{s:5:"value";s:9:"Currency:";}s:14:"sIndexdownload";a:1:{s:5:"value";s:8:"Download";}s:13:"sIndexenglish";a:1:{s:5:"value";s:7:"English";}s:11:"sIndexextra";a:1:{s:5:"value";s:7:"Extras:";}s:28:"sIndexforreasonofinformation";a:1:{s:5:"value";s:477:"For reasons of information we display in our online shop also games which own no gift suitable for young people. However, there is a way for customers, who have already completed the eighteenth year,  to purchase these games. Now you can order with us also quite simply USK 18 games above the postal dispatch way. To fulfil the requirements of the protection of children and young people-sedate you must be personalised in addition simply by Postident. The way is quite simply:";}s:12:"sIndexfrench";a:1:{s:5:"value";s:6:"French";}s:10:"sIndexfrom";a:1:{s:5:"value";s:4:"from";}s:12:"sIndexgerman";a:1:{s:5:"value";s:6:"German";}s:11:"sIndexhello";a:1:{s:5:"value";s:5:"Hello";}s:10:"sIndexhome";a:1:{s:5:"value";s:4:"Home";}s:20:"sIndexhowcaniacquire";a:1:{s:5:"value";s:68:"How can i acquire the games which are restricted only from 18 years?";}s:14:"sIndexlanguage";a:1:{s:5:"value";s:9:"Language:";}s:12:"sIndexlogout";a:1:{s:5:"value";s:6:"Logout";}s:14:"sIndexmybasket";a:1:{s:5:"value";s:12:"Show my cart";}s:24:"sIndexmyinstantdownloads";a:1:{s:5:"value";s:20:"My instant downloads";}s:14:"sIndexmyorders";a:1:{s:5:"value";s:9:"My orders";}s:22:"sIndexnoagerestriction";a:1:{s:5:"value";s:23:"Without age restriction";}s:13:"sIndexnotepad";a:1:{s:5:"value";s:9:"Favorites";}s:18:"sIndexonthepicture";a:1:{s:5:"value";s:16:"On this picture:";}s:17:"sIndexordernumber";a:1:{s:5:"value";s:10:"Order no.:";}s:18:"sIndexourcommentto";a:1:{s:5:"value";s:13:"Our review on";}s:14:"sIndexoverview";a:1:{s:5:"value";s:8:"Overview";}s:16:"sIndexpagenumber";a:1:{s:5:"value";s:11:"Pagenumber:";}s:26:"sIndexpossiblydeliveryfees";a:1:{s:5:"value";s:50:"and possibly shipping fees unless otherwise stated";}s:15:"sIndexpostident";a:1:{s:5:"value";s:171:"2. POSTIDENT: The German post provides a POSTIDENT form in the confirmation of your majority. Please, sign this also. Then the German post sends both signed documents to .";}s:19:"sIndexpricesinclvat";a:1:{s:5:"value";s:27:"* All prices incl. VAT plus";}s:14:"sIndexprinting";a:1:{s:5:"value";s:6:"Print:";}s:25:"sIndexproductinformations";a:1:{s:5:"value";s:19:"Product information";}s:18:"sIndexrealizedwith";a:1:{s:5:"value";s:13:"realised with";}s:30:"sIndexrealizedwiththeshopsyste";a:1:{s:5:"value";s:57:"realized by shopware ag an the webshop-software Shopware ";}s:14:"sIndexreleased";a:1:{s:5:"value";s:19:"unrated (universal)";}s:25:"sIndexreleasedfrom12years";a:1:{s:5:"value";s:20:"Restricted 12 years+";}s:25:"sIndexreleasedfrom16years";a:1:{s:5:"value";s:20:"Restricted 16 years+";}s:25:"sIndexreleasedfrom18years";a:1:{s:5:"value";s:20:"Restricted 18 years+";}s:24:"sIndexreleasedfrom6years";a:1:{s:5:"value";s:19:"Restricted 6 years+";}s:12:"sIndexsearch";a:1:{s:5:"value";s:0:"";}s:14:"sIndexshipping";a:1:{s:5:"value";s:14:"Shipping rates";}s:14:"sIndexshopware";a:1:{s:5:"value";s:8:"Shopware";}s:17:"sIndexshownotepad";a:1:{s:5:"value";s:14:"Show favorites";}s:21:"sIndexsimilararticles";a:1:{s:5:"value";s:15:"Suggested Items";}s:10:"sIndexsite";a:1:{s:5:"value";s:4:"Page";}s:22:"sIndexsuitablearticles";a:1:{s:5:"value";s:16:"Suggested Items:";}s:27:"sIndexsystemrequirementsfor";a:1:{s:5:"value";s:22:"System requirement for";}s:8:"sIndexto";a:1:{s:5:"value";s:2:"To";}s:23:"sIndextrustedshopslabel";a:1:{s:5:"value";s:61:"Trusted shops stamp of quality - request validity check here!";}s:19:"sIndexviewmyaccount";a:1:{s:5:"value";s:16:"Visit my account";}s:19:"sIndexwelcometoyour";a:1:{s:5:"value";s:28:"and welcome to your personal";}s:10:"sIndexwere";a:1:{s:5:"value";s:4:"were";}s:16:"sIndexyouarehere";a:1:{s:5:"value";s:13:"You are here:";}s:19:"sIndexyouloadthepdf";a:1:{s:5:"value";s:222:"1. Load the PDF registration form in your customer area and print it out. Please, present this form together with your identity card or passport in a branch of the German post. Mark the appropriate field and sign the form.";}s:26:"sInfoEmailAlreadyRegiested";a:1:{s:5:"value";s:34:"You already receive our newsletter";}s:26:"sLoginalreadyhaveanaccount";a:1:{s:5:"value";s:25:"I am a returning customer";}s:15:"sLoginareyounew";a:1:{s:5:"value";s:27:"New customer? Start here at";}s:10:"sLoginback";a:1:{s:5:"value";s:4:"Back";}s:18:"sLogindealeraccess";a:1:{s:5:"value";s:16:"Reseller account";}s:11:"sLoginerror";a:1:{s:5:"value";s:22:"An error has occurred!";}s:24:"sLoginloginwithyouremail";a:1:{s:5:"value";s:50:"Please login using your eMail address and password";}s:18:"sLoginlostpassword";a:1:{s:5:"value";s:21:"Forgot your password?";}s:28:"sLoginlostpasswordhereyoucan";a:1:{s:5:"value";s:57:"Forgot your password? Here you can request a new password";}s:17:"sLoginnewcustomer";a:1:{s:5:"value";s:12:"New customer";}s:28:"sLoginnewpasswordhasbeensent";a:1:{s:5:"value";s:38:"Your new password has been sent to you";}s:15:"sLoginnoproblem";a:1:{s:5:"value";s:91:"No problem, ordering from us is easy and secure. The registration takes only a few moments.";}s:14:"sLoginpassword";a:1:{s:5:"value";s:14:"Your password:";}s:17:"sLoginregisternow";a:1:{s:5:"value";s:12:"Register now";}s:16:"sLoginstep1login";a:1:{s:5:"value";s:26:"Step1 - Login/Registration";}s:27:"sLoginwewillsendyouanewpass";a:1:{s:5:"value";s:94:"We will send you a new, randomly generated password. This can be changed in the customer area.";}s:21:"sLoginyouremailadress";a:1:{s:5:"value";s:20:"Your e-mail address:";}s:27:"sOrderprocessacceptourterms";a:1:{s:5:"value";s:46:"Please accept our general terms and conditions";}s:19:"sOrderprocessamount";a:1:{s:5:"value";s:8:"Quantity";}s:20:"sOrderprocessarticle";a:1:{s:5:"value";s:7:"Article";}s:26:"sOrderprocessbillingadress";a:1:{s:5:"value";s:72:"You can change billing address, shipping address and payment method now.";}s:27:"sOrderprocessbillingadress1";a:1:{s:5:"value";s:15:"Billing address";}s:19:"sOrderprocesschange";a:1:{s:5:"value";s:6:"Change";}s:25:"sOrderprocesschangebasket";a:1:{s:5:"value";s:20:"Change shopping cart";}s:30:"sOrderprocesschangeyourpayment";a:1:{s:5:"value";s:122:"Please change your payment method. The purchase of instant downloads is currently not possible with your selected payment!";}s:22:"sOrderprocessclickhere";a:1:{s:5:"value";s:44:"Trusted Shops stamp of quality - click here.";}s:20:"sOrderprocesscomment";a:1:{s:5:"value";s:11:"Annotation:";}s:20:"sOrderprocesscompany";a:1:{s:5:"value";s:7:"Company";}s:28:"sOrderprocessdeliveryaddress";a:1:{s:5:"value";s:16:"Shipping address";}s:21:"sOrderprocessdispatch";a:1:{s:5:"value";s:16:"Shipping method:";}s:25:"sOrderprocessdoesnotreach";a:1:{s:5:"value";s:16:"not reached yet!";}s:28:"sOrderprocessenteradditional";a:1:{s:5:"value";s:57:"Please, give here additional information about your order";}s:30:"sOrderprocessforwardingexpense";a:1:{s:5:"value";s:15:"Shipping costs:";}s:25:"sOrderprocessforyourorder";a:1:{s:5:"value";s:28:"Thank you for your order at ";}s:17:"sOrderprocessfree";a:1:{s:5:"value";s:4:"FREE";}s:26:"sOrderprocessimportantinfo";a:1:{s:5:"value";s:45:"Important information to the shipping country";}s:30:"sOrderprocessinformationsabout";a:1:{s:5:"value";s:30:"Informations about your order:";}s:27:"sOrderprocessmakethepayment";a:1:{s:5:"value";s:15:"Please pay now:";}s:30:"sOrderprocessminimumordervalue";a:1:{s:5:"value";s:46:"Attention. You have the minimum order value of";}s:15:"sOrderprocessmr";a:1:{s:5:"value";s:2:"Mr";}s:15:"sOrderprocessms";a:1:{s:5:"value";s:2:"Ms";}s:21:"sOrderprocessnettotal";a:1:{s:5:"value";s:10:"Net total:";}s:24:"sOrderprocessordernumber";a:1:{s:5:"value";s:13:"Order number:";}s:30:"sOrderprocessperorderonevouche";a:1:{s:5:"value";s:41:"Per order max. one voucher can be cashed.";}s:24:"sOrderprocesspleasecheck";a:1:{s:5:"value";s:50:"Please, check your order again, before sending it.";}s:18:"sOrderprocessprice";a:1:{s:5:"value";s:5:"Price";}s:18:"sOrderprocessprint";a:1:{s:5:"value";s:5:"Print";}s:27:"sOrderprocessprintorderconf";a:1:{s:5:"value";s:33:"Print out order confirmation now!";}s:29:"sOrderprocessrecommendtoprint";a:1:{s:5:"value";s:55:"We recommend to print out the order confirmation below.";}s:23:"sOrderprocessrevocation";a:1:{s:5:"value";s:18:"Right of withdrawl";}s:26:"sOrderprocesssameappliesto";a:1:{s:5:"value";s:42:"The same applies to the selected articles.";}s:28:"sOrderprocessselectedpayment";a:1:{s:5:"value";s:22:"Choosen payment method";}s:30:"sOrderprocessspecifythetransfe";a:1:{s:5:"value";s:60:"Please, give by the referral the following intended purpose:";}s:25:"sOrderprocesstotalinclvat";a:1:{s:5:"value";s:16:"Total incl. VAT:";}s:23:"sOrderprocesstotalprice";a:1:{s:5:"value";s:5:"Total";}s:29:"sOrderprocesstransactionumber";a:1:{s:5:"value";s:19:"Transaction number:";}s:30:"sOrderprocesstrustedshopmember";a:1:{s:5:"value";s:155:"As a member of Trusted Shops, we offer a\n     additional money-back guarantee. We take all\n     Cost of this warranty, you only need to be\n     registered.";}s:26:"sOrderprocessvouchernumber";a:1:{s:5:"value";s:15:"Voucher number:";}s:27:"sOrderprocesswehaveprovided";a:1:{s:5:"value";s:48:"We have sent you an order confirmation by eMail.";}s:28:"sOrderprocessyourvouchercode";a:1:{s:5:"value";s:62:"Please, enter your voucher code here and click on the "arrow".";}s:21:"sPaymentaccountnumber";a:1:{s:5:"value";s:16:"Account number*:";}s:22:"sPaymentbankcodenumber";a:1:{s:5:"value";s:17:"Bank code number:";}s:28:"sPaymentchooseyourcreditcard";a:1:{s:5:"value";s:26:"Choose your credit card *:";}s:24:"sPaymentcreditcardnumber";a:1:{s:5:"value";s:26:"Your credit card number *:";}s:25:"sPaymentcurrentlyselected";a:1:{s:5:"value";s:18:"Currently selected";}s:26:"sPaymentcurrentlyselected1";a:1:{s:5:"value";s:18:"Currently Selected";}s:11:"sPaymentEsd";a:1:{s:5:"value";s:14:"online banking";}s:23:"sPaymentmarkedfieldsare";a:1:{s:5:"value";s:43:"Please fill in all required address fields.";}s:13:"sPaymentmonth";a:1:{s:5:"value";s:5:"Month";}s:24:"sPaymentnameofcardholder";a:1:{s:5:"value";s:20:"Name of cardholder*:";}s:16:"sPaymentshipping";a:1:{s:5:"value";s:25:"Shipping rates & policies";}s:18:"sPaymentvaliduntil";a:1:{s:5:"value";s:14:"Valid until *:";}s:12:"sPaymentyear";a:1:{s:5:"value";s:4:"Year";}s:16:"sPaymentyourbank";a:1:{s:5:"value";s:11:"Your Bank*:";}s:22:"sPaymentyourcreditcard";a:1:{s:5:"value";s:15:"Your creditcard";}s:19:"sRegisteraccessdata";a:1:{s:5:"value";s:10:"Your Login";}s:25:"sRegisterafterregistering";a:1:{s:5:"value";s:79:"Once your account is approved, you will see your reseller prices in this shop.\n";}s:30:"sRegisteralreadyhaveatraderacc";a:1:{s:5:"value";s:36:"You have already a reseller account?";}s:13:"sRegisterback";a:1:{s:5:"value";s:4:"Back";}s:18:"sRegisterbirthdate";a:1:{s:5:"value";s:10:"Birthdate:";}s:19:"sRegistercharacters";a:1:{s:5:"value";s:11:"Characters.";}s:19:"sRegistercityandzip";a:1:{s:5:"value";s:22:"Postal Code and City*:";}s:25:"sRegisterclickheretologin";a:1:{s:5:"value";s:20:"Click here to log in";}s:16:"sRegistercompany";a:1:{s:5:"value";s:8:"Company:";}s:22:"sRegisterconsiderupper";a:1:{s:5:"value";s:31:"Consider upper and lower case. ";}s:16:"sRegistercountry";a:1:{s:5:"value";s:9:"Country*:";}s:19:"sRegisterdepartment";a:1:{s:5:"value";s:11:"Department:";}s:29:"sRegisterenterdeliveryaddress";a:1:{s:5:"value";s:29:"Enter a delivery address here";}s:22:"sRegistererroroccurred";a:1:{s:5:"value";s:22:"An error has occurred!";}s:12:"sRegisterfax";a:1:{s:5:"value";s:4:"Fax:";}s:21:"sRegisterfieldsmarked";a:1:{s:5:"value";s:18:"* required fields.";}s:18:"sRegisterfirstname";a:1:{s:5:"value";s:13:"First name *:";}s:22:"sRegisterforavatexempt";a:1:{s:5:"value";s:89:"Enter a VAT number to remove tax for orders being delivered to a country outside the EU. ";}s:23:"sRegisterfreetextfields";a:1:{s:5:"value";s:44:"Additional Info: (for example: mobile phone)";}s:20:"sRegisterinthefuture";a:1:{s:5:"value";s:59:"Please set up your account so that you can see your orders.";}s:17:"sRegisterlastname";a:1:{s:5:"value";s:11:"Last name*:";}s:11:"sRegistermr";a:1:{s:5:"value";s:3:"Mr.";}s:11:"sRegisterms";a:1:{s:5:"value";s:3:"Ms.";}s:13:"sRegisternext";a:1:{s:5:"value";s:4:"Next";}s:26:"sRegisternocustomeraccount";a:1:{s:5:"value";s:30:"Do not open a customer account";}s:29:"sRegisteronthisfollowingpages";a:1:{s:5:"value";s:0:"";}s:14:"sRegisterphone";a:1:{s:5:"value";s:7:"Phone*:";}s:21:"sRegisterpleasechoose";a:1:{s:5:"value";s:14:"Please choose:";}s:21:"sRegisterpleaseselect";a:1:{s:5:"value";s:17:"Please choose...\n";}s:27:"sRegisterrepeatyourpassword";a:1:{s:5:"value";s:22:"Repeat your password*:";}s:19:"sRegisterrevocation";a:1:{s:5:"value";s:18:"Right of withdrawl";}s:13:"sRegistersave";a:1:{s:5:"value";s:4:"Save";}s:22:"sRegisterselectpayment";a:1:{s:5:"value";s:43:"Please choose your preferred payment method";}s:29:"sRegistersendusyourtradeproof";a:1:{s:5:"value";s:28:"Fax your VAT number to us at";}s:30:"sRegistersendusyourtradeproofb";a:1:{s:5:"value";s:151:"Send your VAT number by fax to +49 2555 99 75 0 99.\nIf you are already a registered re-seller, you can skip this part. You don´t have to send it again.";}s:25:"sRegisterseperatedelivery";a:1:{s:5:"value";s:50:"I would like to enter a different delivery address";}s:30:"sRegistershippingaddressdiffer";a:1:{s:5:"value";s:56:"Your shipping address differs from your billing address.";}s:24:"sRegisterstreetandnumber";a:1:{s:5:"value";s:16:"Street and No.*:";}s:28:"sRegistersubscribenewsletter";a:1:{s:5:"value";s:26:"Sign up for our newsletter";}s:14:"sRegistertitle";a:1:{s:5:"value";s:7:"Title*:";}s:27:"sRegistertraderregistration";a:1:{s:5:"value";s:21:"Reseller Registration";}s:14:"sRegistervatid";a:1:{s:5:"value";s:11:"VAT number:";}s:16:"sRegisterwecheck";a:1:{s:5:"value";s:49:"After verification your account will be approved.";}s:27:"sRegisterwecheckyouastrader";a:1:{s:5:"value";s:212:"After verification your account will be approved. After clearing you get informations per eMail.\nFrom now on, you´ll see your special reseller prices directly, displayed on the product-detail- and overview-pages.";}s:24:"sRegisteryouraccountdata";a:1:{s:5:"value";s:17:"Your billing data";}s:18:"sRegisteryouremail";a:1:{s:5:"value";s:20:"Your eMail-address*:";}s:21:"sRegisteryourpassword";a:1:{s:5:"value";s:15:"Your password*:";}s:27:"sRegisteryourpasswordatlast";a:1:{s:5:"value";s:33:"Your password needs a minimum of ";}s:19:"sSearchafterfilters";a:1:{s:5:"value";s:9:"by filter";}s:20:"sSearchallcategories";a:1:{s:5:"value";s:19:"Show all categories";}s:17:"sSearchallfilters";a:1:{s:5:"value";s:11:"All filters";}s:22:"sSearchallmanufacturer";a:1:{s:5:"value";s:17:"All Manufacturers";}s:16:"sSearchallprices";a:1:{s:5:"value";s:10:"All Prices";}s:20:"sSearcharticlesfound";a:1:{s:5:"value";s:18:" article(s) found!";}s:22:"sSearcharticlesperpage";a:1:{s:5:"value";s:18:"Articles per page:";}s:13:"sSearchbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:21:"sSearchbymanufacturer";a:1:{s:5:"value";s:15:"by manufacturer";}s:14:"sSearchbyprice";a:1:{s:5:"value";s:8:"by price";}s:14:"sSearchelected";a:1:{s:5:"value";s:8:"Choosen:";}s:19:"sSearchhighestprice";a:1:{s:5:"value";s:13:"Highest price";}s:16:"sSearchitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:18:"sSearchlowestprice";a:1:{s:5:"value";s:12:"Lowest price";}s:15:"sSearchnextpage";a:1:{s:5:"value";s:9:"Next page";}s:22:"sSearchnoarticlesfound";a:1:{s:5:"value";s:42:"Your search did not match to any articles!";}s:18:"sSearchonepageback";a:1:{s:5:"value";s:13:"One page back";}s:25:"sSearchothermanufacturers";a:1:{s:5:"value";s:20:"Other manufacturers:";}s:17:"sSearchpopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:18:"sSearchreleasedate";a:1:{s:5:"value";s:12:"Release date";}s:16:"sSearchrelevance";a:1:{s:5:"value";s:9:"Relevance";}s:23:"sSearchsearchcategories";a:1:{s:5:"value";s:20:"Search by categories";}s:19:"sSearchsearchresult";a:1:{s:5:"value";s:16:"Search result(s)";}s:25:"sSearchsearchtermtooshort";a:1:{s:5:"value";s:37:"The entered search term is too short.";}s:14:"sSearchshowall";a:1:{s:5:"value";s:8:"Show all";}s:11:"sSearchsort";a:1:{s:5:"value";s:5:"Sort:";}s:9:"sSearchto";a:1:{s:5:"value";s:2:"to";}s:29:"sSearchunfortunatelytherewere";a:1:{s:5:"value";s:38:"unfortunately no entries were found to";}s:11:"sSearchwere";a:1:{s:5:"value";s:4:"were";}s:24:"sSupportallmanufacturers";a:1:{s:5:"value";s:22:"List all manufacturers";}s:22:"sSupportarticleperpage";a:1:{s:5:"value";s:18:"Articles per page:";}s:12:"sSupportback";a:1:{s:5:"value";s:4:"Back";}s:14:"sSupportbrowse";a:1:{s:5:"value";s:7:"Browse:";}s:23:"sSupportenterthenumbers";a:1:{s:5:"value";s:52:"Please enter the numbers into the following text box";}s:21:"sSupportentrynotfound";a:1:{s:5:"value";s:15:"No Entry found.";}s:24:"sSupportfieldsmarketwith";a:1:{s:5:"value";s:43:"Please fill in all required address fields.";}s:20:"sSupporthighestprice";a:1:{s:5:"value";s:13:"Highest price";}s:17:"sSupportitemtitle";a:1:{s:5:"value";s:19:"Article description";}s:19:"sSupportlowestprice";a:1:{s:5:"value";s:12:"Lowest price";}s:16:"sSupportnextpage";a:1:{s:5:"value";s:16:"One page forward";}s:19:"sSupportonepageback";a:1:{s:5:"value";s:13:"One page back";}s:18:"sSupportpopularity";a:1:{s:5:"value";s:11:"Bestselling";}s:19:"sSupportreleasedate";a:1:{s:5:"value";s:12:"Release date";}s:12:"sSupportsend";a:1:{s:5:"value";s:4:"Send";}s:12:"sSupportsort";a:1:{s:5:"value";s:8:"sort by:";}s:21:"sVoucherAlreadyCashed";a:1:{s:5:"value";s:53:"This voucher was already cashed with a previous order";}s:23:"sVoucherBoundToSupplier";a:1:{s:5:"value";s:56:"This voucher is valid only for products from {sSupplier}";}s:21:"sVoucherMinimumCharge";a:1:{s:5:"value";s:64:"The minimum turnover for this voucher amounts {sMinimumCharge} ?";}s:16:"sVoucherNotFound";a:1:{s:5:"value";s:51:"Voucher could not be found or is not valid any more";}s:23:"sVoucherOnlyOnePerOrder";a:1:{s:5:"value";s:40:"Per order only one voucher can be cashed";}s:26:"sVoucherWrongCustomergroup";a:1:{s:5:"value";s:52:"This coupon is not available for your customer group";}s:25:"sArticleinformationsabout";a:1:{s:5:"value";s:47:"Information about restricted 18 years+-articles";}s:27:"sArticlethevoucherautomatic";a:1:{s:5:"value";s:209:"* The voucher will be delivered automatically to you by eMail after registration and the first order of your friend. You have to be registered with the choosen email address in the shop to receive the voucher.";}s:18:"sBasketrecalculate";a:1:{s:5:"value";s:11:"Recalculate";}s:11:"sLoginlogin";a:1:{s:5:"value";s:5:"Login";}s:25:"sOrderprocesssendordernow";a:1:{s:5:"value";s:14:"Send order now";}s:28:"sOrderprocessforthemoneyback";a:1:{s:5:"value";s:41:"Registration for the money-back guarantee";}s:17:"sSearchcategories";a:1:{s:5:"value";s:11:"Categories:";}s:19:"sSearchmanufacturer";a:1:{s:5:"value";s:13:"Manufacturer:";}s:21:"sSearchshowallresults";a:1:{s:5:"value";s:16:"Show all results";}s:21:"sSearchnosearchengine";a:1:{s:5:"value";s:24:"No search engine support";}s:27:"sSupportfilloutallredfields";a:1:{s:5:"value";s:38:"Please fill out all red marked fields.";}s:12:"sArticlesend";a:1:{s:5:"value";s:4:"Send";}s:14:"sContact_right";a:1:{s:5:"value";s:72:"<strong>Demoshop<br />\n</strong><br />\nAdd your contact information here";}s:12:"sBankContact";a:1:{s:5:"value";s:71:"<strong>\nOur bank:\n</strong>\nVolksbank Musterstadt\nBIN:\nAccount number:";}s:11:"sArticleof1";a:1:{s:5:"value";s:2:"of";}s:25:"sBasketshippingdifference";a:1:{s:5:"value";s:49:"Order #1 #2 to get the whole order shipping free!";}s:19:"sRegister_advantage";a:1:{s:5:"value";s:218:"<h2>My advantages</h2>\n<ul>\n<li>faster shopping</li>\n<li>Save your user data and settings</li>\n<li>Have a look at your orders including shipping information</li>\n<li>Administrate your newsletter subscription</li>\n</ul>";}s:20:"sArticlePricePerUnit";a:1:{s:5:"value";s:14:"Price per unit";}s:18:"sArticleLastViewed";a:1:{s:5:"value";s:11:"Last viewed";}s:16:"sBasketLessStock";a:1:{s:5:"value";s:57:"Unfortunately there are not enough articles left in stock";}s:20:"sBasketLessStockRest";a:1:{s:5:"value";s:76:"Unfortunately there are not enough articles left in stock (x of y selctable)";}s:24:"sBasketPremiumDifference";a:1:{s:5:"value";s:36:"You almost reached this premium! Add";}s:19:"sAGBTextPaymentform";a:1:{s:5:"value";s:42:"I read the general terms and conditions...";}s:14:"sBasketInquiry";a:1:{s:5:"value";s:19:"Solicit a quotation";}s:21:"sArticleCompareDetail";a:1:{s:5:"value";s:15:"Compare article";}}', '1', 'en');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_unadjusted`
--

CREATE TABLE IF NOT EXISTS `s_core_unadjusted` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `table` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `s_core_unadjusted`
--

INSERT INTO `s_core_unadjusted` (`id`, `name`, `value`, `table`) VALUES
(1, 'sSHOPNAME', 'Shopware 3.0 Demo', 's_core_config');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_units`
--

CREATE TABLE IF NOT EXISTS `s_core_units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_core_units`
--

INSERT INTO `s_core_units` (`id`, `unit`, `description`) VALUES
(1, 'l', 'Liter'),
(2, 'g', 'Gramm'),
(8, 'Paket(e)', 'Paket(e)'),
(5, 'lfm', 'lfm'),
(6, 'kg', 'Kilogramm'),
(9, 'Stck.', 'Stück');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_variants`
--

CREATE TABLE IF NOT EXISTS `s_core_variants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `selection` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=11 ;

--
-- Daten für Tabelle `s_core_variants`
--

INSERT INTO `s_core_variants` (`id`, `selection`) VALUES
(1, 'Farbe'),
(2, 'Gr??e'),
(3, 'Ausstattung'),
(4, 'test'),
(5, 'Kriterium'),
(6, 'externe Antenne'),
(7, 'Lizenzen'),
(8, 'halbjährliches Update'),
(9, 'vierteljährliches Update'),
(10, 'monatliches Update');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_core_viewports`
--

CREATE TABLE IF NOT EXISTS `s_core_viewports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `viewport` varchar(255) NOT NULL,
  `viewport_file` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`viewport`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=54 ;

--
-- Daten für Tabelle `s_core_viewports`
--

INSERT INTO `s_core_viewports` (`id`, `viewport`, `viewport_file`, `description`) VALUES
(1, 'cat', 's_cat.php', 'Kategorielisten'),
(2, 'detail', 's_detail.php', 'Artikeldetailseite'),
(3, 'custom', 's_custom.php', 'Statische Seiten'),
(4, 'basket', 's_basket.php', 'Warenkorb'),
(5, 'login', 's_login.php', 'Login'),
(34, 'newsletter', 's_newsletter.php', 'Newsletter'),
(7, 'register2', 's_register2.php', 'Rechnungsadresse'),
(8, 'register3', 's_register3.php', 'Registrierung Zahlungsart'),
(9, 'register2shipping', 's_register2shipping.php', 'Lieferanschrift'),
(10, 'sale', 's_sale.php', 'Bestellabschluss'),
(11, 'crossselling', 's_crossselling.php', 'Empfehlungen'),
(12, 'tellafriend', 's_tellafriend.php', 'Artikel weiterempfehlen'),
(13, 'admin', 's_admin.php', 'Ihr Kundenkonto'),
(14, 'orders', 's_orders.php', 'Ihre Bestellungen'),
(15, 'logout', 's_logout.php', 'Vielen Dank für Ihren Besuch'),
(16, 'search', 's_search.php', 'Suche'),
(17, 'password', 's_password.php', 'Passwort vergessen'),
(18, 'content', 's_content.php', 'Dynamische Inhalte'),
(20, 'note', 's_note.php', 'Merkzettel'),
(21, 'searchFuzzy', 's_searchFuzzy.php', 'Suche'),
(22, 'sitemap', 's_sitemap.php', 'Sitemap'),
(23, 'cheaper', 's_cheaper.php', 'Artikel günstiger gesehen'),
(24, 'registerFC', 's_registerFC.php', 'Registrierung Start'),
(27, 'campaign', 's_campaign.php', 'Aktion'),
(28, 'support', 's_support.php', 'Support'),
(30, 'rma', 's_rma.php', 'rma'),
(35, 'ajax', 's_ajax.php', 'Ajax-Funktionen'),
(36, 'paypalexpressGA', 's_paypalexpressGA.php', 'Paypal Express Guest Account'),
(37, 'paypalexpressTXNPending', 's_paypalexpressTXNPending.php', 'Paypal Express Order Pending Page'),
(38, 'paypalexpressGAReg', 's_paypalexpressGAReg.php', 'Paypal Express Guest Account Registration'),
(39, 'paypalexpressAPIError', 's_paypalexpressAPIError.php', 'Paypal Express API Error'),
(40, 'ticket', 's_ticket.php', 'Ticket System Formular'),
(41, 'ticketview', 's_ticketview.php', 'Ticket Supportverwaltung'),
(42, 'ticketdirect', 's_ticketdirect.php', 'Direkte Ticketanwort'),
(43, 'moneybookers_success', 's_moneybookers_success.php', 'Moneybookers Dankeseite'),
(44, 'moneybookers_fail', 's_moneybookers_fail.php', 'Moneybookers Fehlerseite'),
(45, 'moneybookers_iframe', 's_moneybookers_iframe.php', 'Moneybookers IFrameseite'),
(46, 'hanseatic_success', 's_hanseatic_success.php', 'Hanseatic Dankeseite'),
(47, 'hanseatic_fail', 's_hanseatic_fail.php', 'Hanseatic Fehlerseite'),
(48, 'hanseatic_iframe', 's_hanseatic_iframe.php', 'Hanseatic IFrameseite'),
(49, 'heidelpay_success', 's_heidelpay_success.php', 'Heidelpay Dankeseite'),
(50, 'heidelpay_cancel', 's_heidelpay_cancel.php', 'Heidelpay Abbruchseite'),
(51, 'heidelpay_fail', 's_heidelpay_fail.php', 'Heidelpay Fehlerseite'),
(52, 'heidelpay_iframe', 's_heidelpay_iframe.php', 'Heidelpay IFrameseite'),
(53, 'newsletterListing', 's_newsletterListing.php', 'Newsletter Archiv');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_crontab`
--

CREATE TABLE IF NOT EXISTS `s_crontab` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `elementID` int(11) DEFAULT NULL,
  `data` text NOT NULL,
  `next` datetime DEFAULT NULL,
  `start` datetime DEFAULT NULL,
  `interval` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  `end` datetime DEFAULT NULL,
  `inform_template` varchar(255) NOT NULL,
  `inform_mail` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `action` (`action`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Daten für Tabelle `s_crontab`
--

INSERT INTO `s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`) VALUES
(1, 'Geburtstagsgruß', 'birthday', NULL, '', '2010-10-16 23:42:58', '2010-10-16 12:26:44', 86400, 1, '2010-10-16 12:26:44', '', ''),
(2, 'Aufräumen', 'clearing', NULL, '', '2010-10-16 12:34:38', '2010-10-16 12:34:32', 10, 1, '2010-10-16 12:34:32', '', ''),
(3, 'Lagerbestand Warnung', 'article_stock', NULL, 'a:2:{s:5:"count";i:64;s:17:"articledetailsIDs";a:64:{i:0;i:2;i:1;i:3;i:2;i:4;i:3;i:5;i:4;i:6;i:5;i:8;i:6;i:9;i:7;i:10;i:8;i:14;i:9;i:16;i:10;i:17;i:11;i:19;i:12;i:20;i:13;i:42;i:14;i:51;i:15;i:52;i:16;i:53;i:17;i:55;i:18;i:73;i:19;i:76;i:20;i:106;i:21;i:107;i:22;i:121;i:23;i:141;i:24;i:152;i:25;i:153;i:26;i:157;i:27;i:250;i:28;i:450;i:29;i:538;i:30;i:687;i:31;i:708;i:32;i:1049;i:33;i:1142;i:34;i:1151;i:35;i:1185;i:36;i:1186;i:37;i:1247;i:38;i:1395;i:39;i:1413;i:40;i:1420;i:41;i:1594;i:42;i:1648;i:43;i:1977;i:44;i:2194;i:45;i:2285;i:46;i:2374;i:47;i:2647;i:48;i:2662;i:49;i:2682;i:50;i:2732;i:51;i:2888;i:52;i:2889;i:53;i:2892;i:54;i:2928;i:55;i:3598;i:56;i:3624;i:57;i:3663;i:58;i:3827;i:59;i:3859;i:60;i:5957;i:61;i:6199;i:62;i:6596;i:63;i:6664;}}', '2010-10-16 12:34:33', '2010-10-16 12:34:31', 5, 1, '2010-10-16 12:34:32', 'sARTICLESTOCK', '{$sConfig.sMAIL}'),
(4, 'Übersetzungs-Tabelle', 'translation', NULL, '', '2010-10-16 12:34:38', '2010-10-16 12:34:32', 10, 1, '2010-10-16 12:34:32', '', ''),
(5, 'Suche', 'search', NULL, '', '2010-10-16 12:34:38', '2010-10-16 12:34:32', 10, 1, '2010-10-16 12:34:32', '', ''),
(6, 'eMail-Benachrichtigung', 'notification', NULL, '', '2010-10-17 00:20:28', '2010-10-16 12:26:44', 86400, 1, '2010-10-16 12:26:44', '', ''),
(7, 'Artikelbewertung per eMail', 'article_comment', NULL, '', '2010-10-16 12:35:18', '2010-10-16 12:34:32', 120, 1, '2010-10-16 12:34:32', '', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_banners`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_banners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(60) NOT NULL,
  `valid_from` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `valid_to` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `img` varchar(100) NOT NULL,
  `link` varchar(255) NOT NULL,
  `link_target` varchar(255) NOT NULL,
  `categoryID` int(11) NOT NULL DEFAULT '0',
  `extension` varchar(25) NOT NULL,
  `liveshoppingID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_banners`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_lastarticles`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_lastarticles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `img` varchar(255) NOT NULL,
  `name` varchar(120) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `sessionID` varchar(60) NOT NULL,
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `userID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `sessionID` (`sessionID`),
  KEY `userID` (`userID`),
  KEY `time` (`time`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_emarketing_lastarticles`
--

INSERT INTO `s_emarketing_lastarticles` (`id`, `img`, `name`, `articleID`, `sessionID`, `time`, `userID`) VALUES
(1, '', 'test', 1, '4tj0emcvatt9c1im33mlej65a7', '2010-10-18 02:13:33', 0),
(2, '', 'test', 1, '6lltps7bqcudea4e2e1govb595', '2010-10-18 10:34:29', 0);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_partner`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_partner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idcode` varchar(255) NOT NULL,
  `datum` date NOT NULL,
  `company` varchar(255) NOT NULL,
  `contact` varchar(255) NOT NULL,
  `street` varchar(255) NOT NULL,
  `streetnumber` varchar(35) NOT NULL,
  `zipcode` varchar(15) NOT NULL,
  `city` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `fax` varchar(50) NOT NULL,
  `country` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `web` varchar(255) NOT NULL,
  `profil` text NOT NULL,
  `fix` double NOT NULL,
  `percent` double NOT NULL,
  `cookielifetime` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_partner`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotions`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `category` int(11) NOT NULL DEFAULT '0',
  `mode` varchar(40) NOT NULL,
  `ordernumber` varchar(255) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  `link_target` varchar(50) NOT NULL,
  `valid_from` date NOT NULL DEFAULT '0000-00-00',
  `valid_to` date NOT NULL DEFAULT '0000-00-00',
  `position` int(11) NOT NULL DEFAULT '0',
  `img` varchar(255) NOT NULL,
  `liveshoppingID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_articles`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_articles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `type` varchar(30) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_articles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_banner`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_banner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `linkTarget` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_banner`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_containers`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_containers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `container` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_containers`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_html`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `headline` varchar(255) NOT NULL,
  `html` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_html`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_links`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_links` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `description` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_links`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_main`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_main` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `positionGroup` varchar(50) NOT NULL,
  `datum` date NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `image` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `linktarget` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `parentID` (`parentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_main`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_promotion_positions`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_promotion_positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `promotionID` int(11) NOT NULL DEFAULT '0',
  `containerID` int(11) NOT NULL DEFAULT '0',
  `position` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_promotion_positions`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_referer`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `referer` text NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_referer`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_searchbanner`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_searchbanner` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `keywords` varchar(255) NOT NULL,
  `img` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_searchbanner`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_tellafriend`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_tellafriend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `recipient` varchar(50) NOT NULL,
  `sender` int(11) NOT NULL DEFAULT '0',
  `confirmed` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_tellafriend`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_vouchers`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `vouchercode` varchar(100) NOT NULL,
  `numberofunits` int(11) NOT NULL DEFAULT '0',
  `value` double NOT NULL DEFAULT '0',
  `minimumcharge` double NOT NULL DEFAULT '0',
  `shippingfree` int(1) NOT NULL DEFAULT '0',
  `bindtosupplier` int(11) NOT NULL DEFAULT '0',
  `valid_from` date NOT NULL DEFAULT '0000-00-00',
  `valid_to` date NOT NULL DEFAULT '0000-00-00',
  `ordercode` varchar(100) NOT NULL,
  `modus` int(1) NOT NULL DEFAULT '0',
  `percental` int(1) NOT NULL,
  `numorder` int(11) NOT NULL,
  `customergroup` varchar(15) NOT NULL,
  `restrictarticles` text NOT NULL,
  `strict` int(1) NOT NULL,
  `subshopID` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_vouchers`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_vouchers_cashed`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_vouchers_cashed` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `voucherID` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_vouchers_cashed`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_emarketing_voucher_codes`
--

CREATE TABLE IF NOT EXISTS `s_emarketing_voucher_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `code` varchar(255) NOT NULL,
  `cashed` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_emarketing_voucher_codes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export`
--

CREATE TABLE IF NOT EXISTS `s_export` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `last_export` datetime NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `hash` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `show` int(1) NOT NULL DEFAULT '1',
  `count_articles` int(11) NOT NULL,
  `expiry` datetime NOT NULL,
  `interval` int(11) NOT NULL,
  `inform_template` varchar(255) NOT NULL,
  `inform_mail` varchar(255) NOT NULL,
  `formatID` int(11) NOT NULL DEFAULT '1',
  `last_change` datetime NOT NULL,
  `filename` varchar(255) NOT NULL,
  `encodingID` int(11) NOT NULL DEFAULT '1',
  `categoryID` int(11) DEFAULT NULL,
  `currencyID` int(11) DEFAULT NULL,
  `customergroupID` int(11) DEFAULT NULL,
  `partnerID` varchar(255) DEFAULT NULL,
  `languageID` int(11) DEFAULT NULL,
  `active_filter` int(1) NOT NULL DEFAULT '1',
  `image_filter` int(1) NOT NULL DEFAULT '0',
  `stockmin_filter` int(1) NOT NULL DEFAULT '0',
  `instock_filter` int(11) NOT NULL,
  `price_filter` double NOT NULL,
  `own_filter` text NOT NULL,
  `header` longtext NOT NULL,
  `body` longtext NOT NULL,
  `footer` longtext NOT NULL,
  `count_filter` int(11) NOT NULL,
  `multishopID` int(11) DEFAULT NULL,
  `variant_export` int(11) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=18 ;

--
-- Daten für Tabelle `s_export`
--

INSERT INTO `s_export` (`id`, `name`, `image`, `last_export`, `active`, `hash`, `link`, `show`, `count_articles`, `expiry`, `interval`, `inform_template`, `inform_mail`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`) VALUES
(1, 'Google Produktsuche', 'google.gif', '2010-09-06 17:57:40', 1, '22f80e4e5c49b860b0bfada60256f036', 'http://www.google.de/products', 1, 49, '2000-01-01 00:00:00', 3456, '', '', 2, '0000-00-00 00:00:00', 'export.txt', 1, 3, 1, 1, 'sks', NULL, 0, 0, 0, 0, 0, '', '{strip}\nid{#S#}\ntitel{#S#}\nbeschreibung{#S#}\nlink{#S#}\nbild_url{#S#}\nean{#S#}\ngewicht{#S#}\nmarke{#S#}\nmpn{#S#}\nzustand{#S#}\nproduktart{#S#}\npreis{#S#}\nversand{#S#}\nstandort{#S#}\nwährung\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape|htmlentities}{#S#}\n{$sArticle.description_long|strip_tags|html_entity_decode|trim|regex_replace:"#[^\\wöäüÖÄÜß\\.%&-+ ]#i":""|strip|truncate:500:"...":true|htmlentities|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.image|image:4}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{if $sArticle.weight}{$sArticle.weight|escape:"number"}{" kg"}{/if}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\nNeu{#S#}\n{$sArticle.articleID|category:" > "|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\nDE::DHL:{$sArticle|@shippingcost:"prepayment":"de"},AT::DHL:{$sArticle|@shippingcost:"prepayment":"at"}{#S#}\n{#S#}\n{$sCurrency.currency}\n{/strip}{#L#}', '', 0, 6, 1),
(2, 'Kelkoo', 'kelkoo.gif', '2000-01-01 00:00:00', 0, 'add27ed763bc2f9d21911e38ca258fca', 'http://www.kelkoo.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'kelkoo.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nurl{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nofferid{#S#}\nimage{#S#}\navailability{#S#}\ndeliverycost\n{/strip}{#L#}', '{strip}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{$sArticle.name|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.image|image:5|escape}{#S#}\n{if $sArticle.instock}001{else}002{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(3, 'billiger.de', 'billiger.de.gif', '2010-09-27 20:43:46', 1, 'bee7602b78c0ad4d999e23a0d29a679f', 'http://www.billiger.de/', 1, 80, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'billiger.csv', 1, 3, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nid{#S#}\nhersteller{#S#}\nmodell_nr{#S#}\nname{#S#}\nkategorie{#S#}\npreis{#S#}\nbeschreibung{#S#}\nbild_klein{#S#}\nbild_gross{#S#}\nlink{#S#}\nlieferzeit{#S#}\nlieferkosten{#S#}\nwaehrung{#S#}\naufbauservice{#S#}\n24_Std_service{#S#}\nEAN{#S#}\nASIN{#S#}\nISBN{#S#}\nPZN{#S#}\nISMN{#S#}\nEPC{#S#}\nVIN{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.articleID|category:">"|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle.image|image:3}|\n{$sArticle.image|image:5}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sCurrency.currency}|\n|\n|\n{$sArticle.attr6|replace:"|":""}|\n|\n|\n|\n|\n|\n|\n{/strip}{#L#}', '', 0, 1, 1),
(4, 'Idealo', 'idealo.gif', '2010-09-06 16:24:01', 1, 'ac31eedf4b8991acdae41156b353f025', 'http://www.idealo.de/', 1, 77, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'idealo.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nKategorie|\nHersteller|\nProduktbezeichnung|\nHersteller-Artikelnummer|\nEAN|\nPZN|\nISBN|\nPreis|\nVersandkosten Nachnahme|\nVersandkosten Vorkasse|\nVersandkosten Bankeinzug|\nDeeplink|\nLieferzeit|\nArtikelnummer|\nLink Produktbild|\nProdukt Kurztext|\n{/strip}{#L#}', '{strip}\n{$sArticle.articleID|category:">"|escape}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|replace:"|":""}|\n{$sArticle.suppliernumber|replace:"|":""}|\n{$sArticle.attr6|escape}|\n|\n|\n{$sArticle.price|escape:"number"}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.ordernumber|escape}|\n{$sArticle.image|image:5}{#S#}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{/strip}{#L#}', '', 0, 1, 1),
(5, 'Yatego', 'yatego.gif', '2000-01-01 00:00:00', 0, '29128766af3d47813dff6f547e086423', 'http://www.yatego.com/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'yatego.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nforeign_id{#S#}\narticle_nr{#S#}\ntitle{#S#}\ncategories{#S#}\nlong_desc{#S#}\npicture{#S#}\nurl{#S#}\ndelivery_surcharge{#S#}\nprice\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(6, 'schottenland.de', 'schottenland.gif', '2000-01-01 00:00:00', 0, 'ccb3f8a1623ca4b65842143416f38ab9', 'http://www.schottenland.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'schottenland.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nHersteller|\nProduktbezeichnung|\nProduktbeschreibung|\nPreis|\nVerfügbarkeit|\nEAN|\nHersteller AN|\nDeeplink|\nArtikelnummer|\nDAN_Ingram|\nVersandkosten Nachnahme|\nVersandkosten Vorkasse|\nVersandkosten Kreditkarte|\nVersandkosten Bankeinzug\n{/strip}{#L#}', '{strip}\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.suppliernumber|replace:"|":""}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.ordernumber|replace:"|":""}|\n|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"credituos":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n{/strip}{#L#}', '', 0, 1, 1),
(7, 'guenstiger.de', 'guenstiger.gif', '2000-01-01 00:00:00', 0, '4d9a7abb1b77b30640145f6cdec96f7c', 'http://www.guenstiger.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'guenstiger.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|replace:"|":""}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.image|image:2}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.weight|replace:"|":""}\n{/strip}{#L#}', '', 0, 1, 1),
(8, 'geizhals.at', 'geizhals.gif', '2000-01-01 00:00:00', 0, '644dc3b029a3f98abd4ef00b86dedae8', 'http://www.geizhals.at/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'geizhals.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nID{#S#}\nHersteller{#S#}\nArtikelbezeichnung{#S#}\nKategorie{#S#}\nBeschreibungsfeld{#S#}\nBild{#S#}\nUrl{#S#}\nLagerstandl{#S#}\nVersandkosten{#S#}\nVersandkostenNachname{#S#}\nPreis{#S#}\nEAN{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(9, 'Ciao', 'ciao.gif', '2000-01-01 00:00:00', 0, '56ff60fe1d387fa40155c1479e010cc1', 'http://www.ciao.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'ciao.csv', 1, NULL, 1, 1, '', NULL, 0, 1, 0, 0, 0, '', '{strip}\nOffer ID{#S#}\nBrand{#S#}\nProduct Name{#S#}\nCategory{#S#}\nDescription{#S#}\nImage URL{#S#}\nProduct URL{#S#}\nDelivery{#S#}\nShippingCost{#S#}\nPrice{#S#}\nProduct ID{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(10, 'Pangora', 'pangora.gif', '2000-01-01 00:00:00', 0, '6db98b3e621a8957a7910da264ed3845', 'http://www.pangora.com/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'pangora.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\noffer-id{#S#}\nmfname{#S#}\nlabel{#S#}\nmerchant-category{#S#}\ndescription{#S#}\nimage-url{#S#}\noffer-url{#S#}\nships-in{#S#}\nrelease-date{#S#}\ndelivery-charge{#S#}\nprices	old-prices{#S#}\nproduct-id{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.image|image:3|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.releasedate|escape}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{#S#}\n{/strip}{#L#}\n\n', '', 0, 1, 1),
(11, 'Shopping.com', 'shopping_com.gif', '2000-01-01 00:00:00', 0, '98d1feb9a02bfe65650bb70c23555a15', 'http://www.shopping.com/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'shopping.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nMPN|\nEAN|\nHersteller|\nProduktname|\nProduktbeschreibung|\nPreis|\nProdukt-URL|\nProduktbild-URL|\nKategorie|\nVerfügbar|\nVerfügbarkeitsdetails|\nVersandkosten\n{/strip}{#L#}', '{strip}\n|\n{$sArticle.attr6}|\n{$sArticle.supplier}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode}|\n{$sArticle.price|escape:"number"}|\n{$sArticle.articleID|link:$sArticle.name}|\n{$sArticle.image|image:4}|\n{$sArticle.articleID|category:">"}|\n{if $sArticle.instock}Ja{else}Nein{/if}|\n{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}\n{/strip}{#L#}', '', 0, 1, 1),
(12, 'Hitmeister', 'hitmeister.gif', '2000-01-01 00:00:00', 0, 'fa43a250792de037164bf682e5d7e240', 'http://www.hitmeister.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'hitmeister.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nean{#S#}\ncondition{#S#}\nprice{#S#}\ncomment{#S#}\noffer_id{#S#}\nlocation{#S#}\ncount{#S#}\ndelivery_time{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.attr6|escape}{#S#}\n100{#S#}\n{$sArticle.price*100}{#S#}\n{#S#}\n{$sArticle.ordernumber|escape}{#S#}\n{#S#}\n{#S#}\n{if $sArticle.instock}b{else}d{/if}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(13, 'evendi.de', 'evendi_de.gif', '2000-01-01 00:00:00', 0, '8fe9073ec646030e8787f01b0295081e', 'http://www.evendi.de/', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'evendi.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nEindeutige Händler-Artikelnummer{#S#}\nPreis in Euro{#S#}\nKategorie{#S#}\nProduktbezeichnung{#S#}\nProduktbeschreibung{#S#}\nLink auf Detailseite{#S#}\nLieferzeit{#S#}\nEAN-Nummer{#S#}\nHersteller-Artikelnummer{#S#}\nLink auf Produktbild{#S#}\nHersteller{#S#}\nVersandVorkasse{#S#}\nVersandNachnahme{#S#}\nVersandLastschrift{#S#}\nVersandKreditkarte{#S#}\nVersandRechnung{#S#}\nVersandPayPal\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{if $sArticle.instock}1-3 Werktage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}{#S#}\n{$sArticle.attr6|escape}{#S#}\n{$sArticle.suppliernumber|escape}{#S#}\n{$sArticle.image|image:2}{#S#}\n{$sArticle.supplier|escape}{#S#}\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{#S#}\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{$sArticle|@shippingcost:"paypal":"de":"Deutsche Post Standard"|escape:"number"}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(14, 'affili.net', '', '2010-09-29 21:56:05', 1, '3cdabcb378cb15e0c5058a5e692eddba', '', 1, 80, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'affilinet.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nart_number{#S#}\ncategory{#S#}\ntitle{#S#}\ndescription{#S#}\nprice{#S#}\nimg_url{#S#}\ndeeplink1{#S#}\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber}{#S#}\n{$sArticle.articleID|category:">"|escape}{#S#}\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}{#S#}\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|escape}{#S#}\n{$sArticle.price|escape:"number"}{#S#}\n{$sArticle.image|image:5|escape}{#S#}\n{$sArticle.articleID|link:$sArticle.name|escape}{#S#}\n{/strip}{#L#}', '', 0, 1, 1),
(15, 'Google Produktsuche XML', '', '2000-01-01 00:00:00', 0, '0c71f16710080ef3d7bc3e25e3503a46', '', 1, 0, '2000-01-01 00:00:00', 0, '', '', 3, '2008-09-27 09:52:17', 'export.xml', 2, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '<?xml version="1.0" encoding="UTF-8" ?>\n\n<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n	<atom:link href="http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}" rel="self" type="application/rss+xml" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>test</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>', '<item> \n	<title>{$sArticle.name|strip_tags|strip|truncate:80:"...":true|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name|escape}</link>\n	<description>{$sArticle.description_long|strip_tags|regex_replace:"/[^wöäüÖÄÜß .?!,&:%;-\\"'']/i":""|trim|truncate:900:"..."|escape}</description>\n	<category>{$sArticle.articleID|category:" > "|escape}</category>\n	{if $sArticle.changed}<pubDate>{$sArticle.changed|date_format:"%a, %d %b %Y %T %Z"}</pubDate>{/if}\n	<g:bild_url>{$sArticle.image|image:4}</g:bild_url>\n{*<g:verfallsdatum>2006-12-20</g:verfallsdatum>*}\n	<g:preis>{$sArticle.price|format:"number"}</g:preis>\n{*<g:preisart>ab</g:preisart>*}\n{*	<g:währung>{$sCurrency.currency}</g:währung>*}\n{*	<g:zahlungsmethode>Barzahlung;Scheck;Visa;MasterCard;AmericanExpress;Lastschrift</g:zahlungsmethode>*}\n{*<g:menge>20</g:menge>*}\n	<g:marke>{$sArticle.supplier|escape}</g:marke>\n	<g:ean>{$sArticle.attr6|escape}</g:ean>\n{*<g:hersteller>{$sArticle.supplier|escape}</g:hersteller>*}\n{*<g:hersteller_kennung>834</g:hersteller_kennung>*}\n{*<g:speicher>512</g:speicher>*}\n{*<g:prozessorgeschwindigkeit>2</g:prozessorgeschwindigkeit>*}\n	<g:modellnummer>{$sArticle.suppliernumber|escape}</g:modellnummer>\n{*<g:größe>14x14x3</g:größe>*}\n	<g:gewicht>2</g:gewicht>\n	<g:zustand>neu</g:zustand>\n{*<g:farbe>schwarz</g:farbe>*}\n	<g:produktart>{$sArticle.articleID|category:"/"|escape}</g:produktart>\n</item>', '</channel>\n</rss>', 0, 1, 1),
(16, 'preissuchmaschine.de', '', '2000-01-01 00:00:00', 0, '42201304b55b12802795d6b95bf326a9', '', 1, 0, '2000-01-01 00:00:00', 0, '', '', 1, '0000-00-00 00:00:00', 'preissuchmaschine.csv', 1, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '{strip}\nBestellnummer|\nHersteller|\nBezeichnung|\nPreis|\nLieferzeit|\nProduktLink|\nFotoLink|\nBeschreibung|\nVersandNachnahme|\nVersandKreditkarte|\nVersandLastschrift|\nVersandBankeinzug|\nVersandRechnung|\nVersandVorkasse|\nEANCode|\nGewicht\n{/strip}{#L#}', '{strip}\n{$sArticle.ordernumber|replace:"|":""}|\n{$sArticle.supplier|replace:"|":""}|\n{$sArticle.name|strip_tags|strip|truncate:80:"...":true|replace:"|":""}|\n{$sArticle.price|escape:"number"}|\n{if $sArticle.instock}2 Tage{elseif $sArticle.shippingtime}{$sArticle.shippingtime} Tage{else}10 Tage{/if}|\n{$sArticle.articleID|link:$sArticle.name|replace:"|":""}|\n{$sArticle.image|image:2}|\n{$sArticle.description_long|strip_tags|strip|trim|truncate:900:"...":true|html_entity_decode|replace:"|":""}|\n{$sArticle|@shippingcost:"cash":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"debit":"de":"Deutsche Post Standard"|escape:"number"}|\n|\n{$sArticle|@shippingcost:"invoice":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle|@shippingcost:"prepayment":"de":"Deutsche Post Standard"|escape:"number"}|\n{$sArticle.attr6|replace:"|":""}|\n{$sArticle.weight|replace:"|":""}\n{/strip}{#L#}', '', 0, 1, 1),
(17, 'RSS Feed-Template', '', '2000-01-01 00:00:00', 0, 'bb4ae3de155fd3638a0c4888d1ed892e', '', 1, 0, '2000-01-01 00:00:00', 0, '', '', 3, '0000-00-00 00:00:00', 'export.xml', 2, NULL, 1, 1, '', NULL, 0, 0, 0, 0, 0, '', '<?xml version="1.0" encoding="UTF-8" ?>\n<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">\n<channel>\n	<atom:link href="http://{$sConfig.sBASEPATH}/engine/connectors/export/{$sSettings.id}/{$sSettings.hash}/{$sSettings.filename}" rel="self" type="application/rss+xml" />\n	<title>{$sConfig.sSHOPNAME}</title>\n	<description>Shopbeschreibung ...</description>\n	<link>http://{$sConfig.sBASEPATH}</link>\n	<language>{$sLanguage.isocode}-{$sLanguage.isocode}</language>\n	<image>\n		<url>http://{$sConfig.sBASEPATH}/templates/0/de/media/img/default/store/logo.gif</url>\n		<title>{$sConfig.sSHOPNAME}</title>\n		<link>http://{$sConfig.sBASEPATH}</link>\n	</image>{#L#}', '<item> \n	<title>{$sArticle.name|strip_tags|htmlspecialchars_decode|strip|escape}</title>\n	<guid>{$sArticle.articleID|link:$sArticle.name|escape}</guid>\n	<link>{$sArticle.articleID|link:$sArticle.name}</link>\n	<description>{if $sArticle.image}\n		<a href="{$sArticle.articleID|link:$sArticle.name}" style="border:0 none;">\n			<img src="{$sArticle.image|image:3}" align="right" style="padding: 0pt 0pt 12px 12px; float: right;" />\n		</a>\n{/if}\n		{$sArticle.description_long|strip_tags|regex_replace:"/[^\\wöäüÖÄÜß .?!,&:%;\\-\\"'']/i":""|trim|truncate:900:"..."|escape}\n	</description>\n	<category>{$sArticle.articleID|category:">"|htmlspecialchars_decode|escape}</category>\n{if $sArticle.changed} 	{assign var="sArticleChanged" value=$sArticle.changed|strtotime}<pubDate>{"r"|date:$sArticleChanged}</pubDate>{"rn"}{/if}\n</item>{#L#}', '</channel>\n</rss>', 0, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_articles`
--

CREATE TABLE IF NOT EXISTS `s_export_articles` (
  `feedID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_export_articles`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_categories`
--

CREATE TABLE IF NOT EXISTS `s_export_categories` (
  `feedID` int(11) NOT NULL,
  `categoryID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_export_categories`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_settings`
--

CREATE TABLE IF NOT EXISTS `s_export_settings` (
  `name` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  `feedID` int(11) NOT NULL,
  PRIMARY KEY (`name`,`feedID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_export_settings`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_export_suppliers`
--

CREATE TABLE IF NOT EXISTS `s_export_suppliers` (
  `feedID` int(11) NOT NULL,
  `supplierID` int(11) NOT NULL,
  PRIMARY KEY (`feedID`,`supplierID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_export_suppliers`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter`
--

CREATE TABLE IF NOT EXISTS `s_filter` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `position` int(11) NOT NULL,
  `comparable` int(1) NOT NULL,
  `sortmode` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_filter`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_options`
--

CREATE TABLE IF NOT EXISTS `s_filter_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `filterable` int(1) NOT NULL,
  `default` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_filter_options`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_relations`
--

CREATE TABLE IF NOT EXISTS `s_filter_relations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `groupID` (`groupID`,`optionID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_filter_relations`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_filter_values`
--

CREATE TABLE IF NOT EXISTS `s_filter_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `optionID_2` (`optionID`,`value`),
  KEY `optionID` (`optionID`,`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_filter_values`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_help`
--

CREATE TABLE IF NOT EXISTS `s_help` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent` int(11) NOT NULL DEFAULT '1',
  `description` text,
  `position` int(11) DEFAULT '0',
  `alias` int(11) NOT NULL DEFAULT '0',
  `metakeywords` text NOT NULL,
  `metadescription` text NOT NULL,
  `cmsheadline` varchar(255) NOT NULL,
  `cmstext` text NOT NULL,
  `template` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=145 ;

--
-- Daten für Tabelle `s_help`
--

INSERT INTO `s_help` (`id`, `parent`, `description`, `position`, `alias`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`) VALUES
(105, 103, 'Kategorien', 3, 0, 'Kategorien Kategorieverwaltung Strukturierung', '<p><br></p>', 'Kategorien', '<p>Mit Hilfe der Kategorieverwaltung kann das gesamte Artikelangebot sinnvoll strukturiert werden. Sie können dabei beliebig viele Haupt- und Unterkategorien anlegen, diese mit Überschriften und Texten versehen oder jede Kategorie auch individuell für Suchmaschinen optimieren.</p><p>Wenn in Shopware noch keine Kategorien angelegt wurden und der Kategorie-Baum somit leer ist, müssen zunächst Hauptkategorien angelegt werden. Klicken Sie dazu auf "Shopware" und geben dann bei "Neue Hauptkategorie" eine sinnvolle Bezeichnung ein. Durch Bestätigen über "Anlegen" wird die neue Kategorie angelegt. Hauptkategorien bilden das Grundgerüst in Shopware und werden auch zentral auf der Startseite in der Storefront (in allen Standardtemplates) visualisiert.</p><p>Ist das Grundgerüst gebaut, können Sie dann unter den Hauptkategorien entsprechende Unterkategorien anlegen und auch darunter wieder z.B. einen weiteren Kategorie-Baum aufbauen.</p><p>Sobald entsprechende Artikel den Kategorien zugeordnet sind, wird ebenfalls angezeigt, wie viele Artikel mit den jeweiligen Kategorien bereits verknüpft sind.</p><p><b>Weitere Daten:</b><br>Je nach Template können Sie über "Template-Auswahl" je Kategorie eine eigene Visualisierung von Artikeln einstellen. Hierüber lässt sich beispielsweise steuern, ob Artikel in den Übersichten der Storefront z.B. untereinander oder zu zweit/dritt nebeneinander angezeigt werden sollen. Durch unterschiedliche Einstellungen kann man so die Optik nach eigenen Wünschen einstellen.</p><p>Durch die "Meta-Keywords" und "Meta-Description" lassen sich die Kategorien optimal für das Auffinden in Suchmaschinen konfigurieren. Die Keywords sind durch ein Leerzeichen getrennt einzugeben und bei der Description (Beschreibung) sind normale Texte erlaubt. Diese Informationen werden durch Shopware dann automatisch an die korrekte Stelle in den HTML-Quelltext gesetzt.</p><p>Einzelne Überschriften und Texte für Kategorien können auch in der Storefront als Beschreibung für eine Kategorie ausgegeben werden. Machen Sie dazu dann entsprechende Eingaben in den dafür vorgesehen Feldern.</p><p>Durch "Speichern" werden dann abschließend alle Angaben übernommen.<br></p><p></p>', ''),
(26, 103, 'Artikel neu', 1, 0, 'Produkt Artikel anlegen neu eintragen', '<p><br></p>', 'Artikel neu', '<p>\r\n\r\n<b>Hersteller</b><br>\r\nTragen Sie in diesem Feld einen Hersteller ein, dem der jeweilige Artikel zugeordnet werden soll. Verfügbare Hersteller werden direkt vorgeschlagen und können so ausgewählt werden.  Sollte ein Hersteller noch nicht existieren, wird dieser automatisch angelegt und kann zu einem späteren Zeitpunkt bearbeitet und z.B. mit einem Logo versehen werden ( -&gt; Artikel / Hersteller).<br>\r\n<br><b>Artikel-Bezeichnung</b><br>Hier wird eine kurze Beschreibung erwartet, die den jeweiligen Artikel definiert. Dieser Text wird dann bei den Auflistungen von Artikeln in der Storefront angezeigt.</p><p><b>Beschreibung</b><br>Hier haben Sie die Möglichkeit, den Artikel ausführlich zu beschreiben und den Text entsprechend zu formatieren. Dazu stehen ihnen verschiedene Werkzeuge zur Verfügung, die Sie auch aus einer normalen Textverarbeitung kennen. Falls der Hersteller bereits ausführliche Informationen bietet, können Sie diese auch per Copy &amp; Paste von der Homepage des Herstellers übernehmen und gegebenenfalls noch auf Ihre Bedürfnisse anpassen.</p><p><b>Hinweis:</b> Achten Sie bitte darauf, dass bestimmte HTML-Formatierungen, die Sie evtl. von fremden Portalen per Copy &amp; Paste in Shopware einpflegen, die Ansicht der Artikel-Detailseite negativ beeinflussen können. </p><p><b>Lieferzeit (In Tagen)</b><br>An dieser Stelle können Sie die Lieferzeit für den Artikel in Tagen angeben. Die Lieferzeit wird in der Storefront ausgegeben, wenn der Lagerbestand &lt;=0 ist. </p><p><b>Lagerbestand</b><br>Tragen Sie hier den Lagerbestand von Artikeln ein. Ist der Lagerbestand &gt;=1, so wird ein Artikel als "sofort lieferbar" in der Storefront visualisiert. Der Lagerbestand wird bei Bestellungen automatisch abgetragen und bei Stornierungen wieder addiert. </p><p><b>Gewicht</b><br>Tragen Sie das Gewicht eines Artikels in kg ein. Diese Angabe wird für die Berechnung der Versandkosten nach Gewicht benötigt.</p><p><b>MWST</b><br>Bestimmen Sie den jeweiligen Umsatzsteuersatz für einen Artikel.<br> </p><p><b>Erscheinungsdatum</b><br>Das Erscheinungsdatum kann bei Artikeln verwendet werden, die erst in Zukunft lieferbar sind. Das hier entweder manuell oder über den Kalender eingetragene Datum wird mit auf der Artikeldetailseite ausgegeben und ermöglicht Kunden somit, Artikel vorzubestellen.<br><br><b>Artikel hervorheben</b><br>Über diese Option lassen sich Artikel in der Storefront besonders kennzeichnen. Standardmäßig werden diese Artikel als "Tipp" angezeigt.</p><p><b>Aktiv</b><br>Diese Checkbox ist beim Anlegen neuer Artikel automatisch gesetzt. Diese Artikel sind somit aktiv und werden in der Storefront angezeigt. Sie können über diese Funktion steuern, ob Sie Artikel den Shopbesuchern zur Verfügung stellen wollen oder nicht. </p><p><b>Versandkostenfrei<br></b>Wenn Sie diese Checkbox aktivieren wird dieser Artikel (und auch weitere, die in den Warenkorb gelegt werden) ohne Versandkosten berechnet.</p><p><b>Erweiterte Preisangaben</b><br>Diese Felder sind optional. Wenn Sie die Bestelleinheit nicht verändern, ist der Standardwert "Stück". Hier können Sie z.B. auch definieren, dass sich ein Preis auf "Meter" beziehen soll. Weiterhin können Sie dann beispielsweise auch eine Mindestabnahme eintragen, die mindestens bestellt werden muss (z.B. 3 Meter). Die Staffelung ist dann sinnvoll, falls Sie nur z. B. 3, 6, 9 Meter verkaufen. In diesem Szenario wäre die Staffel somit "3". Die Maximalabnahme ist notwendig, wenn nicht mehr als eine bestimmte Menge bestellt werden kann oder darf. Unter "Preis bezieht sich auf" legen Sie fest, auf welche Menge sich der angelegte Preis bezieht. Wenn wir jetzt einmal bei unserem "Meter-Beispiel" bleiben und Sie hier 3 eintragen, wird in der Storefront der Preis für 3 Meter angezeigt. Möchten Sie z.B. zusätzlich einen Referenzpreis von 1 Meter anzeigen, so geben Sie 1 bei Referenzpreis ein</p><p><b>Hinweis: </b>Zusätzliche Bestelleinheiten können in den Grundeinstellungen angelegt werden.</p><p><b>Bestellnummer</b><br>Die Bestellnummer dient dazu, den Artikel für Sie eindeutig zu identifizieren. Für jeden Artikel muss eine individuelle Bestellnummer vergeben werden.</p><p><b>Herstellernummer</b><br>Wahlweise können Sie an dieser Stelle noch die Artikelnummer des Herstellers eintragen. Für den Fall, dass der Kunde sich z.B. auf der Internetseite vom Hersteller über den Artikel informieren möchte oder sich bereits informiert hat, hat er so eine eindeutige Zuordnungsmöglichkeit.</p><p><b>Kurzbeschreibung</b><br>Die Kurzbeschreibung wird in der Storefront auf allen Artikel-Übersichtsseiten angezeigt. Wird hier keine Eingabe vorgenommen, so wird die normale Artikel-Beschreibung verwendet und automatisch für die Übersichten gekürzt.</p><p><b>Keywords</b><br>Hier können zum Artikel passende „Schlüsselwörter“ hinterlegt werden. Mehrere Begriffe sind durch eine Leertaste getrennt einzugeben. Keywords sind nicht nur wichtig für die Indizierung in Suchmaschinen, sondern auch von großer Bedeutung bei Einsatz des Moduls "Intelligente Suche".Ist das Modul lizenziert, so werden die Keywords auch bei einer Suchanfrage berücksichtigt. Das Eingabefeld der Keywords ist auf 256 Zeichen beschränkt.</p><p><b>Freitext Varianten</b><br>Wenn Sie Artikel einpflegen wollen, die sich in Form, Größe, Farbe, etc. unterscheiden, können Sie hier den entsprechenden Unterschied definieren. Diese Information wird dann mit auf der Shop-Detailseite angezeigt. Weiterhin gibt es in diesem Zusammenhang eine komfortable und schnelle Möglichkeit, Artikel-Varianten anzulegen. Eine genaue Erklärung dazu finden Sie in der Beschreibung zu Artikel-Varianten. </p><p><b>Preise Shopkunden</b><br>Definieren Sie hier den Preis für den jeweiligen Artikel. Zudem besteht die Option, einen so genannten "Pseudo Preis" anzugeben. Dieser Preis wird dann durchgestrichen zusätzlich zu dem normalen Preis in der Storefront angezeigt. Ebenfalls kann der Einkaufspreis mit eingetragen werden. Dieser dient für statistische Auswertungen in Ihrer Shopware.<br>Eine zusätzliche Möglichkeit ist die Festlegung von Staffelpreisen. Klicken Sie dazu unterhalb von Staffeln auf "beliebig". In diesem Eingabefeld definieren Sie den Preis bis zu einer gewissen Abnahmemenge. Bestätigen Sie die Eingabe mit der "Tabulator-Taste". Automatisch werden für die nächste Staffel weitere Eingabefelder zur Verfügung gestellt. Dieser Vorgang kann beliebig fortgesetzt werden und alle Preisstaffeln werden in der Storefront angezeigt. Zusätzlich wird ein Artikel dann mit einem "Preis ab" der günstigsten Maximalstaffel dargestellt.</p><p><b>Hinweis: </b>Sie können wahlweise, je nach Definition in den Shopware Grundeinstellungen, Preise entweder Brutto oder Netto eingeben. Zudem bietet Shopware auch die individuelle Preisangabe oder Rabbattierung für unterschiedliche Käuferschichten, z.B. Endkunde/Händler. Hierzu benötigen Sie das Modul "Kundengruppen", dass Shopware B2B und B2C fähig macht und dabei beliebig viele Kundengruppen unterstützt. Detaillierte Informationen finden Sie in den Beschreibungen der Module.</p><p><b>Zusatzfelder</b> <br>Standardmäßig sind hier schon einige Felder vordefiniert. Eine Erweiterung auf bis zu 20 Felder (Attribute) ist in den Grundeinstellungen möglich. Artikel-Attribute können bei Bedarf Artikel tiefer klassifizieren und zusätzlich in der Storefront ausgegeben werden.</p><p><b>Änderungen speichern</b><br>Wenn Sie die Eingabe aller notwendigen Daten vorgenommen haben, betätigen Sie den Button "Änderungen speichern". Dadurch werden die gesamten Eingaben gespeichert und die weiteren Registerkarten werden aktiviert. So können Sie z. B. unmittelbar mit der Kategoriezuordnung fortfahren, Bilder zuordnen, etc.<br></p>', ''),
(40, 103, 'Übersicht', 2, 0, 'Übersicht Artikelübersicht', '<p><br></p>', 'Übersicht', '<p>\r\n\r\nDie Artikel Übersicht ist ähnlich angelegt wie die Kategorienzuordnung. Durch Klicken auf\r\neine Kategorie werden alle Artikel angezeigt, die dieser Kategorie bereits zugeordnet\r\nsind. Über das Bearbeiten-Symbol gelangen Sie zur Artikel-Bearbeiten-Maske. Dort können Sie,\r\nwie bei einem neuen Artikel, alle Einstellungen ändern und Bilder uploaden etc.\r\nZusätzlich können Artikel auch unmittelbar gelöscht werden. In der Übesicht wird zudem angezeigt, ob Artikel aktiv oder inaktiv sind.</p><p><b>Artikel kopieren:<br></b>Über die Artikelübersicht können Sie auch sehr komfortabel Artikel kopieren/duplizieren. Klicken Sie dazu einfach auf das "Diskettensymbol". Wenn Sie den Dialog dann bestätigen, wird eine 1zu1-Kopie des gewählten Artikels angelegt. Die Bestellnummern werden dabei automatisch durch Shopware erweitert und müssen dan gegebenfalls angepasst werden, ähnlich wie andere Unterscheidungskriterien der Artikel.<br></p><p><b>HINWEIS:</b>\r\nUmbenennen von Kategorien mit einem Doppelklick.<br>\r\n\r\n\r\n\r\n</p>', ''),
(27, 1, 'Inhalte', 2, 0, '', '', '', 'Shopware bietet Ihnen eine optimale Unterst&uuml;tzung bei der Vermarktung der Artikel Ihres Online-Shops. Allein die Darstellung von Artikeln in einem Shop reicht bei der gro&szlig;en Konkurrenz von Angeboten selten aus. Zus&auml;tzliche Ma&szlig;nahmen m&uuml;ssen ergriffen werden, um Ihr Angebot zu verbreiten und interessant zu gestalten. Shopware bietet als professionelle Shopl&ouml;sung diese Unterst&uuml;tzung. Ihre Kundenbasis kann regelm&auml;&szlig;ig &uuml;ber Newsletter informiert und z.B. durch Gutscheine langfristig an Ihr Angebot gebunden werden. Mit Bannern lassen sich Aktionen auf der Homepage Ihres Online-Shops bewerben und Pr&auml;mienartikel k&ouml;nnen f&uuml;r treue Kunden ausgelobt werden. Die Marketing Tools von Shopware unterst&uuml;tzen Sie bei allen Aktionen rund um die Vermarktung Ihrer Artikel und werden sicherlich schon bald zu den effektivsten Instrumenten in Ihrer eigenen Shopware geh&ouml;ren.', ''),
(28, 27, 'Banner', 1, 0, 'Banner Bannergrafik', '<p><br></p>', 'Banner', '<p>Hier können Banner in Shopware eingestellt werden. Über das Tree-Menü auf der linken Seite wählen Sie die gewünschte Kategorie aus, in der Banner später in der Storefront angezeigt werden sollen. <br>Als erstes wird eine "Kategoriebanner-Bezeichnung" erwartet und anschließend kann das Ziel mit einem Hyperlink definiert werden. Weiterhin besteht die Möglichkeit, entweder auf eine Seite in Shopware zu verlinken (z.B. Artikel-Detailseite) oder auf eine externe Quelle zu verweisen (z.B. Herstellerseite). Bei einem externen Ziel wird der Link in einem neuen Fenster geöffnet. </p><p>Die Anzeige von Bannern kann auch zeitlich eingegrenzt werden. Geben Sie dafür im Format "DD.MM.JJJJ" einen Start- und Endzeitpunkt ein oder wählen Sie über die unterstützende Kalenderfunktion das entsprechende Datum direkt aus. Diese Zeitangaben sind optional. Werden die Einträge nicht gefüllt, sind Banner ab sofort und auf unbestimmte Zeit gültig.<br>Zum Abschluss wird die eigentliche Grafik über "Durchsuchen" ausgewählt und durch Klicken auf "Banner speichern" überprüft und eingestellt.<br>Werden in einer Kategorie mehrere Banner eingepflegt, rotieren diese automatisch in der Storefront.<br> </p><p><b>Hinweis:</b> Unterstützte Dateiformate: gif, jpg und png. Grafiken werden nicht skaliert, daher ist ein Upload in der korrekten Breite, je nach Template, notwendig. </p>', ''),
(29, 27, 'Einkaufswelten', 2, 0, 'Einkaufswelt Angebote Promotion', '<p><br></p>', 'Einkaufswelten', '<p>Über die Einkaufswelten steuern Sie die gezielte und zugleich dynamische Bewerbung von Artikeln auf der Startseite und beliebigen Kategorie-Übersichten in Shopware. Die Platzierung der Artikel oder wahlweise auch das Einsetzen von Bannern mit entsprechendem Hyperlink lässt dabei für jede Einkaufswelt individuell konfigurieren.<br></p><p></p><p>Wählen Sie als erstes die gewünschte Promotion-Art aus. Es stehen folgende Optionen zur Verfügung:<br> </p><p><b>Fester Artikel</b><br>Wenn ein bestimmter Artikel in Szene gesetzt werden soll, ist diese Auswahl optimal. Unter "Artikel-Bestellnummer" ist dabei die Bestellnummer  des Artikels anzugeben. </p><p><b>Zufälliger Artikel</b><br>Hier wird dynamisch ein zufälliger Artikel aus dem gesamten Artikelstamm in die Promotion gesetzt, der ständig wechselt.  </p><p><b>Neuheit </b><br>Neue Artikel werden automatisch mit in die Promotion übernommen. </p><p><b>Top-Artikel </b><br>Hier werden Artikel angezeigt, die sich im Shop am besten verkaufen. Bei dieser Auswahl wird immer ein Artikel der aktuellen Top 10 Verkäufe in die Promotion gesetzt. </p><p><b>Eigenes Bild</b></p><p>Diese Option bietet sich an, um ein Bild/Banner mit in die Promotion aufzunehmen. Das Bild wird dann optional mit einem festen Artikel, einer Kategorie/Unterkategorie oder einer anderen Seite verlinkt. Bei dieser Auswahl tauchen automatisch weitere Eingabefelder auf.<br>Zunächst muss der Pfad des Bildes angegeben werden und im nächsten Feld dann der Hyperlink. Weiterhin kann angegeben werden, ob sich dieser Link im gleichen Fenster (Auswahl Shopware) oder in einem neuen Fenster öffnen soll.<br>zudem kann ein Zeitraum definiert werden, in der die Promotion aktiv sein soll. Dazu besteht auch die Möglichkeit, über das Icon für den Kalender automatisch das Datum einzufügen. </p><p>Nach Abschluss jeder neuen Artikel-Promotion wird mit dem Button "Promotion anlegen" die jeweilige Promotion gespeichert. Grundsätzlich werden auch immer alle bereits angelegten Promotions angezeigt, die jederzeit geändert oder auch gelöscht  werden können. </p><p><b>HINWEIS: </b><br>Die Position der einzelnen Promotions ist frei per Drag &amp; Drop wählbar. Nach der Positionierung muss anschließend auf "Positionen speichern" geklickt werden.</p><p><br>Bei einem Upload eigener Bilder muss auf die entsprechenden Maße der Grafik geachtet werden, da diese nicht automatisch skaliert werden. Die Maße sind hierbei abhängig vom eingesetzten Template.<br></p>', ''),
(32, 26, 'Konfigurator', 4, 0, 'Artikel Konfigurator', '<p><br></p>', 'Artikel-Konfigurator', '<div><strong>Modulbeschreibung<br></strong>Der Artikel-Konfigurator unterstützt \r\nSie beim Anlegen von mehrdimensionalen Varianten. Diese sind notwendig, wenn \r\nsich ein Artikel in mehr als einer Eigenschaft von seinen Varianten \r\nunterscheidet. Dies kann beispielsweise ein PC-System sein - Auswahl 100GB \r\nFestplatte, 120GB oder 160GB. Arbeitsspeicher 512MB oder 1024MB oder Artikel, \r\ndie sich gleichzeitig in Größe und Farbe unterscheiden, usw. </div>\r\n<div>&nbsp;</div>\r\n<div>\r\n<ul><li>Individuelle Bestellnummer und Preis für jede Varianten-Kombination </li><li>Unterteilung in Optionsgruppen (z.B. Größe, Farbe etc.) </li><li>Definition von Kombinationen die ausverkauft sind <br>(z.B. Farbe Blau, \r\nGröße L) </li><li>Zusätzlich Auswahl von optionalen Artikeln, die zusammen mit diesem Artikel \r\ngekauft werden können (z.B. passende Tastatur zum individuell zusammengestellten \r\nPC) </li></ul></div>\r\n<p><b>Zubehör-Gruppen</b> <br>Legen Sie zunächst eine Zubehör Gruppe (z.B. \r\nZubehör) mit einer sinnvollen Beschreibung (z.B. Gleich&nbsp;mit einem Klick mit \r\nbestellen!) an.</p>\r\n<p><b>Zubehör-Optionen</b> <br>Zubehörartikel, die Sie zuordnen möchten, müssen \r\nShopware bereits angelegt sein. Wählen Sie im Pulldown-Menü die gewünschte \r\nGruppe aus, die unter "Zubehör-Gruppen" angelegt wurde. Nun tragen Sie unter \r\n"Zubehör-Optionen" die gewünschte Artikel-Bezeichnung und die dazugehörige \r\nBestellnummer ein. Der Artikel kann dann in der Storefront einfach über eine \r\nCheckbox in einem Step zusammen mit dem Konfigurator-Artikel in den Warenkorb \r\ngelegt werden.</p>\r\n<p><b>Konfigurator-Gruppen</b> <br>Hier können alle wesentlichen \r\nUnterscheidungsmerkmale angelegt werden, die dann auch in der Storefront \r\nauswählbar sein sollen. Für einen PC z. B. legen Sie Festplatten, \r\nArbeitsspeicher, Gehäuse, Betriebssysteme etc. als Gruppen an. Diesen Gruppen \r\nordnen Sie im zweiten Schritt die verfügbaren Artikel zu. </p>\r\n<p><b>Konfigurator-Optionen</b> <br>Definieren Sie hier die genauen&nbsp;Unterschiede \r\nfür die&nbsp;unter "Konfigurator-Gruppen" angelegten&nbsp;Positionen&nbsp;. Wenn bei einem z.B. \r\ndie Gruppe "Festplatte" ist, können hier die verfügbaren Größen angelegt werden. \r\nSo kann der Shopkunde seinen PC in der Storefront individuell konfigurieren. \r\n</p>\r\n<p><b>Preiseingabe-Matrix</b> <br>In dieser Matrix werden alle möglichen \r\nKombination angezeigt. Nun müssen noch Bestellnummer und die Preise ergänzt \r\nwerden. Über Vorauswahl können Sie eine Kombination aktivieren, welche im Shop \r\nals erstes angezeigt wird. Über die Aktiv-Checkbox können einige Konfigurationen \r\naktiviert bzw. deaktiviert werden. </p>', ''),
(30, 27, 'Prämienartikel', 4, 0, 'Prämienartikel Gratis Artikel Zusatzartikel', '<p><br></p>', 'Prämienartikel', '<p>Der Prämienartikel ist ein kostenloser Zusatzartikel, den Sie Kunden anbieten können, sobald eine Bestellung einen bestimmten Bestellwert überschreitet.<br>der Prämienartikel eignet sich sehr gut zur Kaufförderung. Er soll den Kunden dazu bewegen, bei einem niedrigeren Bestellwert noch einen zusätzlichen Artikel in den Warenkorb zu legen, um sich damit seinen Prämienartikel zu sichern. Prämienartikel werden standardmäßig auf der Warenkorbseite in der Storefront angezeigt.<br> </p><p>Für eine neue Prämie geben Sie in das Eingabefeld "Mindestumsatz" den Bestellwert ein, ab dem der Kunde einen kostenlosen Zusatzartikel erhalten soll. Im darunter liegenden Feld definieren Sie eine "Artikelnummer-Warenwirtschaft" (sofern Sie hier über eine Anbindung verfügen) und "Artikelnummer-Shop" für den Prämienartikel. Durch Kick auf "Prämie speichern" wird der Prämien-Artikel aktiv. hier können Sie auch Prämien selbstverständlich bei Bedarf wieder löschen. </p><p>Generell ist es auch möglich, mehrere Prämienartikel für unterschiedliche Bestellwerte zu konfigurieren. Hier wird dann immer automatisch der nächste drunter liegende Pramienartikel in Bezug auf den Bestellwert mit in den Warenkorb gelegt.<br></p>', ''),
(31, 1, 'Module', 6, 0, '', '<p><br></p>', '', '<p><br></p>', ''),
(33, 27, 'Preissuchmaschinen', 3, 0, 'Preisportal Preissuchmaschine Google Günstiger billiger.de Yatego Idealo Kelkoo', '<p><br></p>', 'Preissuchmaschinen', '<p>Unter dem Punkt "Inhalte / Preissuchmaschinen" lassen sich schnell und einfach alle Einstellungen vornehmen, über die im Anschluss optimierte CSV-Dateien für Preissuchmaschinen generiert werden. Diese lassen sich dann on the fly in das entsprechende Preissuchmaschinen-Portal importieren.  </p><p><b>Übersicht Preissuchmaschinen</b><br>Nähere Informationen erhält man durch Tooltips, die bei Mouseover über Icons und Tabellenköpfe am Cursor erscheinen.  </p><p><b>CSV-Link: </b><br>Der CSV-Link ist der Pfad zur CSV-Datei, die für das jeweilige Preisportal formatiert ist. Diese Datei ändert sich je nach Einstellungen für den Export von Artikeln zu Preissuchmaschinen. Portale können auch komplett für den Export gesperrt werden. Dazu reicht ein Klick auf den grünen Haken in der Übersicht der Portale.</p><p>Bei Mouseover über das Kalenderblatt erscheint im Tooltip das Datum, wann die CSV-Datei letztmalig aufgerufen bzw. erstellt wurde.</p><p>Möchten Sie ein bereits genutztes Portal z.B. unmittelbar vor dem Zugriff durch Preissuchmaschinen wieder schützen, reicht ein Klick auf das Schlüssel-Icon. Hierdurch wird der CSV-Link mit einem neuen Schlüssel generiert und der bisherige funktioniert somit nicht mehr.<br> </p><p>Um an einem Portal Änderungen vorzunehmen, z. B. einige Kategorien, Artikel oder Wörter aus dem gesamten Artikelstamm zu blocken, genügt ein Klick auf Editieren (Stift-Icon). Nun kann mit den unteren Abschnitten weiter gearbeitet werden. </p><p><b>Allgemeine Einstellungen:</b><br>Tragen Sie bei Geblockte Wörter (durch Komma getrennt) die Buchstabenfolge ein, die Artikel mit gleichlautendem Namen (Teilnamen) in der Artikel-Bezeichnung automatisch für den Export blockieren.<br>Ebenso können ganze Hersteller geblockt werden. Einfach per Drag &amp; Drop den gewünschten Hersteller in das leere Feld (rechte) ziehen und mit "Speichern" bestätigen. </p><p><b>Auswahl Kategorien: </b><br>Hier können gesamte Kategorien und/oder Unterkategorien für den Export in die CSV-Datei gesperrt werden. Alle Kategorien samt Unterkategorien sind einsehbar und aufgebaut wie ein Stammbaum. So ist es möglich, eine einzelne Unterkategorie zu blocken. Jede Kategorie, bei der ein Haken gesetzt wird, wird für den Export ausgeschlossen. Auch hier muss die Auswahl durch "Speichern" bestätigt werden.<br> </p><p><b>Auswahl Artikel:<br></b>Hier können Sie nach verschiedenen Auswahkriterien Artikel suchen. Wenn Sie die gewünschten Artikel gefunden haben, die Sie blockieren wollen, klicken Sie in der tabellarischen Übersicht auf "Optionen" beim entsprechenden Artikel. Dadurch wird der Artikel in die Liste der zu blockenden Artikel aufgenommen. Durch einen erneuten Klick wird dieser wieder aus der Liste entfernt. Auch hier müssen Sie anschließend unter dem Abschnitt "geblockte Artikel" den Button "Speichern" betätigen.  </p>', ''),
(34, 27, 'Shopinhalte', 5, 0, '', '', '', '', ''),
(35, 34, 'Statische Inhalte', 1, 0, 'Statische Inhalte AGB Hilfe Über uns', '<p><br></p>', 'Statische Inhalte', '<p>Unter statische Inhalte fallen z. B. die AGB-, Datenschutz-, Über uns- und Hilfe-Seiten. Vorhandene Standardvorgaben/Seiten werden angezeigt und können schnell auf die eigenen Bedürfnisse abgestimmt werden. </p><p>Für eine neue Seite wählen Sie "Neue Seite". Es öffnet sich die Maske "Content bearbeiten".</p><p><u><b>Die ersten 6 Inputfelder sind optional. Hier ist das Einbinden von Templates möglich!</b></u></p><p><u><b></b></u><br><b>Template 1 bis 3 Variable:</b></p><p><b></b><u>sContainer</u> = Wird diese Variable gesetzt, so wird der Inhalt aus dem unteren Editor nicht mehr ausgegeben. Es wird das Template geladen, welches im Feld "Pfad" hinterlegt ist.</p><p><u>sContainerRight </u>= Bei dieser Variable wird das Template in die rechte Spalte geladen. Sinnvoll wenn z. B. Kontaktdaten immer angezeigt werden sollen.</p><p><br></p><p><b>Template 1 bis 3 Pfad:</b></p><p><u>/contact/contact.tpl</u> = Beispielangabe (Pfad im Templateordner)</p><p><br></p><p>Schließen Sie den Vorgang mit "Speichern" ab. Wählen Sie den gerade erstellten Eintrag zum Bearbeiten erneut aus und Sie bekommen den einzubindenden HTML-Code angezeigt. Diesen können Sie z. B. in Ihrer index.tpl ergänzen.<br></p><p><br></p>', ''),
(36, 34, 'Dynamische Inhalte', 2, 0, 'Inhalte dynamische News Aktuelles', '<p><br></p>', 'Dynamische Inhalte', '<p>Shopware bietet zum Pflegen von dynamischen Inhalten komfortable Content Management\r\nFunktionen. Somit lassen sich diverse Rubriken (Presse, Aktuelles, etc.) in der Storefront einfach\r\nund ohne HTML-Kenntnisse anpassen. Zusätzliche Upload-Funkionen bieten\r\ndie Möglichkeit, Grafiken einzubetten und/oder Dateien zum Download für Kunden bereitzustellen. <br></p><p> </p><p>Wählen Sie die gewünschte Gruppe und klicken auf "Aktualisieren". Alle in der gewählten Gruppe vorhanden Einträge werden aufgeführt. Hier können Einträge gelöscht oder bearbeitet werden. Mit "Neuer Eintrag" wird eine leere Maske geöffnet, in der alle relevanten Eingaben vorgenommen werden können. Klicken Sie zum Abschluss auf "Speichern". Nun befindet sich der Eintrag in der zuvor ausgewählten Gruppe und wird je nach Template unmittelbar in der Storefront angezeigt.<br></p>', ''),
(37, 34, 'Formulare', 3, 0, 'Support Kontakt Formular', '<p><br></p>', 'Formulare', '<p><b>Modulbeschreibung</b><br>Der Formulargenerator ist in Verbindung mit dem RMA-Management (unter Kunden / Service zu finden) das\r\nperfekte Baukastensystem, um individuelle Kontakt-, Support und\r\nServiceformulare für die Storefront zu generieren. Formulare können\r\ndabei ganz nach individuellen Anforderungen des Shopbetreibers im\r\nShopware-Backend zusammen-geklickt werden. Shopware generiert dann\r\nentsprechende Links, die auf einfache Art und Weise in die\r\nStorefront integrierbar sind und danach automatisch im Look &amp; Feel\r\ndes eingesetzten Templates erscheinen. In Kombination mit dem RMA-\r\nManagement für die Rücksendung von Waren, die entweder defekt sind oder\r\nim Rahmen des allgemeinen Rückgaberechts zurückgeschickt werden, stellt\r\nShopware zusätzliche Funktionen bereit. So können beispielsweise\r\nReklamationen angenommen und abgelehnt werden und der Kunde erhält\r\nautomatisch eine durch Shopware vergebene RMA-Nummer, die die weitere\r\nBearbeitung und letztendlich die Identifizierung von zurück gesendeten\r\nWaren enorm erleichtert. </p><ul class="lst_bullet" style="margin: 0pt 0pt 0pt 15px;" mce_style="margin: 0pt 0pt 0pt 15px;"><li>Formulargenerator zum Erstellen beliebiger Eingabefelder für die Storefront,   je nach Anforderungen des Shopbetreibers</li><li>Eingabefelder\r\nkönnen als Pflichtfelder oder optionale Eingaben erstellt werden, inkl.\r\nAusgabe von konfigurierbaren Meldungen, wenn Pflichtfelder nicht\r\nausgefüllt werden</li><li>Pulldown-Menüs, Checkboxen oder Radio-Buttons können zusätzlich zu reinen   Textfeldern angelegt werden</li><li>Jedes\r\nerstellte Formular kann an unterschiedliche eMail-Adressen (auch\r\ngleichzeitig an mehrere Adressen) verschickt werden, z.B. je nach\r\nZuständigkeiten von Mitarbeitern</li><li>Formulare erscheinen als eigene HTML-Seiten im Look &amp; Feel des   Webshops</li><li>Reklamationen werden vom Handling technisch und funktionell komplett in   Shopware integriert</li></ul><p></p><p><b>Funktionsweise</b><b>:</b></p><p><b>Name: </b>Variablen-Bezeichnung z.B. comment für Kommentar (Diese Bezeichnung kann mit {sVars.comment} im eMail-Template eingebunden und somit der Inhalt mit versendet werden.) Sollen in einer Zeile zwei Felder stehen, z. B. PLZ und Ort, so muss die Eingabe mit einem Semikolon getrennt werden (zipcode;city). </p><p><b>Bezeichnung:</b> Diese Bezeichnung wird vor dem jeweiligen Inputfeld in der Storefront angezeigt. </p><p><b>Typ:</b> Auswahl zwischen Eingabe Feld, zwei Eingabefelder, Radio-Button, Auswahlfeld, Textfeld, Checkbox und eMail. </p><p><b>Aussehen:</b>[Normal],[PLZ und Ort] und [Straße und Nr] wählbar</p><p><b> Optionen: </b>Werte die in Auswahlfeldern, Checkboxen und Radio-Buttons wählbar sind. Eingabe durch ein Semikolon getrennt. </p><p><b>Kommentar:</b> Hinterlegen eines Beschreibungstexts oder Kommentars, die in der Storefront zum jeweiligen Feld angezeigt werden. </p><p><b>Fehlermeldung:</b> Bei fehlender Eingabe, wird diese Meldung nach dem Sendeversuch angezeigt. </p><p><b>Eingabe erforderlich:</b> Pflichtfeld ja oder nein</p><p><b>Besonderheiten in Verbindung mit RMA-Funktionen (Kunden / Service):</b><br>Die Formulare werden auch über den Formular - Generator angelegt, nur statt einer eMail wird bei eMail-Template der notwendige \r\nSQL - Code für das Einfügen in die service_tabelle eingefügt. </p>\r\n\r\n<div>Der sieht dann z.B. so aus:</div>\r\n<div> </div>\r\n<div>INSERT INTO s_user_service<br>(clientnumber, email, billingnumber, \r\narticles, description, description2, \r\ndescription3,<br>description4,date,type)</div>\r\n<div><br>VALUES \r\n(<br>   ''{$kdnr}'',<br>   ''{$email}'',<br>   ''{$rechnung}'',<br>   ''{$artikel}'',<br>   ''{$info}'',<br>   '''',<br>   '''',<br>   '''',<br>   ''{$date}'',<br>2<br>  )</div>\r\n<div> </div>\r\n<div>Das Feld type (in diesem Fall 2) muss dem Service - Typ entsprechen, hierzu gibt </div>\r\n<div>es Einstellungsmöglichkeiten für Sservicetypes unter Textvorlagen.<br></div>\r\n<div> </div>\r\n\r\n\r\n<div>Die anderen Felder füllt man mit den Smarty - Feldbezeichnungen, die ganz normal über den Formulargenerator eingetragen werden können. Der Rest geht dann automatisch, Shopware verschickt dann keine eMail, \r\nsondern speichert das Formular in der Datenbank.</div><p> </p>', ''),
(70, 1, 'Marketing', 5, 0, '', '', '', '', ''),
(38, 27, 'Datenaustausch', 6, 0, 'Datenaustausch Import Export Artikeldaten CSV', '<p><br></p>', 'Datenaustausch', '<p>Der Datenaustausch bietet verschiedene Optionen um Shopinhalte aus Shopware zu exportieren und importieren. Diese Dateien können mit einem Editor oder in einer Tabellenkalkulation geöffnet und/oder weiter bearbeitet werden.</p><p>Export:</p><ul><li>Newsletter-Empfänger</li><li>Artikel-Stammdaten</li><li>Kategorien-Rohdaten</li><li>Bestellungen-Rohdaten</li><li>Alle nicht vorrätigen Artikel </li></ul><p> Import:<br> </p><ul><li>Lagerbestände</li></ul><br/>Shopware bietet zudem über verschiedene Scripte diverse Anbindungen an ERP-Systeme und stellt darüber komfortable Anbindungsmöglichkeiten zur Verfügung. Weitere Informationen bekommen Sie direkt bei Hersteller von <a target="_blank" title="Hamann-Media GmbH" mce_href="http://www.shopware2.de" href="http://www.shopware2.de">Shopware</a>.<br>', ''),
(41, 26, 'Kategorien', 1, 0, 'Kategorie Kategorien Unterteilung Einteilung', '<p><br></p>', 'Kategorien', '<p>Wählen Sie die Registerkarte "Kategorien", um mit der Kategoriezuweisung für den jeweiligen Artikel zu beginnen. Markieren Sie im Archive-Baum dazu eine Kategorie und klicken auf "Diese Kategorie zuordnen". Diesen Vorgang können Sie für die Zuordnung weiterer Kategorien beliebig oft wiederholen. Bereits zugeordnete Kategorien bei einem Artikel werden automatisch in der Übersicht angezeigt. Hier können Sie auch Kategoriezuordnungen wieder aufheben.</p><p><br><b>Hinweis:</b> Einem Artikel können beliebig viele Kategorien zugeordnet werden. Achten Sie aber auf eine sinnvolle Kategoriezuweisung, da auch z.B. bei der Suche in der Storefront jede Kategoriezuweisung ausgewertet wird.<br></p>', ''),
(42, 26, 'Bilder', 2, 0, 'Bilder Artikelbilder Produktbilder ', '<p><br></p>', 'Bilder', '<p>Über das Register "Bilder" können einem Artikel Grafiken zugeordnet werden. Klicken Sie dazu auf "<können sie="" ihrem="" neuen="" artikel="" bilder="" zuordnen.="" Über="" den="" punkt="">Bilder auswählen". Wählen Sie über den Explorer dann entsprechende Dateien aus. Sie können wahlweise auch mehrere Dateien gleichzeitig markieren und und für den Upload auswählen. Wichtig ist aber, dass alle Dateien im *.JPG-Format bereitstehen. Nach Auswahl der Bilder wird der Bildupload gestartet. Artikelbilder werden automatisch auf die richtigen Größen skaliert. Nach erfolgreichem Upload erscheinen diese rechts in der Übersicht. Standardmäßig wird ein Vorschaubild für einen Artikel vorgeschlagen, dass in der Storefront als Hauptbild in den Übersichten und auf der Artikel-Detailseite verwendet wird. </können>Mit einem Doppelklick auf ein Bild können Sie jede gewünschte Grafik zum Vorschaubild machen. Alle weiteren Artikelbilder werden als Thumbnails nur auf der Detailseite in der Storefront abgebildet.</p><p><b>Hinweis:</b> Um den Bildupload nutzen zu können, muss ein Flash-Plugin (ab Version 9.0 r47) für den Browser installiert sein.<br> </p>\r\n\r\n<p><b>Klicken Sie <a href="../../../engine/vendor/swfupload/player.exe" mce_href="/shopware2/engine/vendor/swfupload/player.exe">hier</a> um den aktuellen Flashplayer herunterzuladen.\r\nStarten Sie bitte nach dem Download die Installation - danach können Sie\r\nden Bild-Upload nutzen.</b></p>', ''),
(43, 26, 'Varianten', 3, 0, 'Varianten Artikel-Varianten Unterscheidungen Merkmale', '<p><br></p>', 'Varianten', '<p>Das Anlegen von Artikel-Varianten ist eine zentrale Funktion, ähnliche Artikel komfortabel und schnell in Shopware einzustellen, ohne dabei immer einen kompletten Artikel anlegen zu müssen. Über die Registerkarte "Varianten" können alle ähnlichen Artikel zu einem Hauptartikel angelegt werden, die sich ausschließlich in einem Merkmal unterscheiden. Das kann z.B. die Form, Größe, Farbe, Ausstattung, etc. sein.</p><p>Zum Hinzufügen einer neuen Variante muss zunächst das zentrale Unterscheidungsmerkmal in den Stammdaten des Hauptartikels eingetragen werden. Das Eingabefeld dafür lautet "Freitext-Varianten". Der Hauptartikel bildet damit zugleich auch die 1. Artikel-Variante. Danach kann im Register "Varianten" eine neue Variante hinzugefügt werden. Hier finden Sie bekannte Eingaben, wie aus den Haupt-Artikeldaten, die Sie bei den Varianten vornehmen können.</p><p><b>Hinweis:</b> Möchten Sie Artikel-Varianten in Verbindung mit Shopware nutzen, die sich in mehr als einem Kriterium unterscheiden, so ist das optionale Modul Artikel-Konfigurator hier das perfekte Werkzeug dafür.<br></p>', ''),
(45, 26, 'Links', 5, 0, 'Links Link Hyperlink Herstellerlink', '<p><br></p>', 'Links', '<p>Einfügen von passenden, weiterführenden Hyperlinks zum Artikel (optional). </p><p>Entweder zu anderen Seiten im Shop oder zu externen Seiten (Hersteller, Portale, Foren etc.).<br></p>', ''),
(46, 26, 'Downloads', 6, 0, 'Downloads Datenblätter Anleitungen zip rar', '<p><br></p>', 'Downloads', '<p>Hier können optional Downloads, wie Datenblätter, Bedienanleitungen, Testversionen, definiert werden.</p><p>Jedes Dateiformat wird akzeptiert.<br></p>', ''),
(47, 26, 'Cross-Selling', 7, 0, 'Cross-Selling', '<p><br></p>', 'Cross-Selling', '<p>Um dem Kunden das Einkaufen so einfach und komfortabel wie möglich zu gestalten, können unter Cross-Selling direkte Verknüpfungen zu anderen Artikeln eingerichtet werden. Sie können „Ähnliche Artikel“ und „Artikel-Zubehör“ vorschlagen. Dazu muss nur die gewünschte Bestellnummer des Artikels eingetragen werden und mit "Hinzufügen" bestätigt werden. Zugeordnete Artikel werden in der Tabelle angezeigt und können jeder Zeit geändert oder gelöscht werden.\r\n\r\n</p><p><b>Hinweis: </b>Werden keine „Ähnlichen Artikel“ hinterlegt, so schlägt Shopware automatisch aus der gleichen Kategorie Artikel in der Storefront vor. </p>', ''),
(50, 1, 'Kunden', 3, 0, '', '', '', '', ''),
(51, 50, 'Übersicht', 1, 0, 'Kunden Übersicht Kundenübersicht', '<p><br></p>', 'Übersicht', '<p>Im Kundenbereich verwalten Sie alle Daten zu Kunden, die Sie z. B. durch eine Registrierung oder beim Kauf eines Artikels erhalten haben. Diese Kundeninformationen nutzen Sie auf der einen Seite für die Ausführung und Lieferung der Bestellung, sie können aber auch für Newsletter und Marketingfunktionen genutzt werden.</p><p><b>Anzeigen, Bearbeiten von Kunden</b><br>Beim Aufruf "Kunden/Übersicht" erhalten Sie als erstes einen Überblick über die aktuellsten Kunden , sortiert nach Registrierdatum. Weiterhin wird in Klammern die Anzahl der Bestellungen hinter dem Namen und die jeweils zugeordnete Kundengruppe angezeigt. </p><p>Mit Klick auf einen Buchstaben werden in der Tabelle alle Kunden angezeigt, deren Name oder Firma mit dem entsprechenden Buchstaben beginnen. Auch die Anzahl der vorhanden Datensätze wird visualisiert. Zusätzlich ist eine Suche über das Inputfeld möglich. Bei dieser Live-Suche werden sofort Treffer in der Tabelle angezeigt.<br></p><p><b>Hinweis:</b> Durch Klicken auf den Tabellenkopf, z. B. PLZ, wird die Tabelle dementsprechend sortiert.</p><p>Über das Info-Icon gelangen Sie in das jeweilige Kundenkonto. Hier können Stammdaten eingesehen und geändert werden, sowie Kunden deaktiviert, gelöscht oder einer Kundengruppe zugewiesen werden.</p><p><b>Hinweis:</b> Gelöschte Konten können nicht wiederhergestellt werden!</p><p>Im Register "Bestellungen" finden Sie eine Übersicht über alle getätigten Bestellungen des jeweiligen Kunden. Über das Info-Icon können weitere Details abgerufen werden (siehe Bestellungen).</p><p>Unter "Umsatz" öffnet sich eine Statistik, die anschaulich die Umsätze und zusätzlich den Gesamtumsatz des jeweiligen Kunden anzeigt.<br></p>', ''),
(52, 50, 'Bestellungen', 2, 0, 'Bestellungen Bestellpositionen Belege', '<p><br></p>', 'Bestellungen', '<p>Shopware bietet eine Reihe von Möglichkeiten für das schnelle und übersichtliche Management aller eingehenden Bestellungen.<br></p><p>Unter "Inhalte / Bestellungen" können alle weiteren Einstellungen vorgenommen werden, die für die Bearbeitung der Kundenaufträge notwendig sind. Mit einem Doppelklick auf Bestellstatus oder Zahlstatus in der gewünschten Zeile, öffnet sich ein Pulldown Menü, in dem ein Status definiert werden kann. Weiterhin kann die Ansicht nach Status, auch in Kombination, gefiltert werden. Für die Detailansicht der Bestellung klicken Sie auf das Info-Icon.</p><p><b>Allgemeine Daten:</b><br>Einsehen der hinterlegten Rechnungsadresse, Lieferadresse, Zahlungsart etc.. Hier haben Sie die Möglichkeit, den Bestellstatus anzupassen und Kommentare zu hinterlegen, welche den Kunden später in der Bestellübersicht (Storefront) angezeigt werden.</p><p><b>Bestellpositionen:</b><br>Alle Artikel der aktuellen Bestellung werden hier aufgelistet. Nachträglich können Anzahl-, Preis- und Statusänderungen vorgenommen werden. Eine Veränderung der Anzahl/Menge wird automatisch beim Lagerbestand berücksichtigt.</p><p><b>Belege:</b><br>Shopware hat standardmäßig eine Belegerzeugung integriert. So lassen sich im Handumdrehen Rechnungen, Lieferscheine, Stornierungen und Gutschriften im PDF-Format erzeugen. Erstellte Belege können in Shopware heruntergeladen und geöffnet werden. Dies geschieht durch Klick auf das Acrobat-Icon unter "Vorhandene Belege".<br>Mit Klick auf "Artikel nachträglich hinzufügen" erhalten Sie eine zusätzliche, leere Zeile unter den Bestellpositionen. So können Sie einen weiteren Artikel oder einen Hinweis hinzufügen. Mit "Reset" können Sie jederzeit den Ursprungszustand der Bestellpositionen wiederherstellen.<br>Sind Ihre Angaben vollständig, so können Sie das gewählte PDF-Dokument mit "Beleg erstellen" erzeugen. Sobald der Erstellprozess abgeschlossen ist, wird der Beleg in der Tabelle sichtbar.<br>Wurde von ausländischen Kunden eine Umsatzsteuer-Id. hinterlegt, kann nach erfolgreicher Überprüfung durch den Shopbetreiber, eine umsatzsteuerbefreite Rechnung erstellt werden. Hierzu muss ein Haken in der Checkbox "Umsatzsteuerbefreit" gesetzt werden. Das so erzeugte PDF weist dann keine MwSt. mehr aus, sondern nur noch Netto-Beträge.</p><p><b>Hinweis: </b>Bitte beachten Sie, dass sich nachträgliche Änderungen der Bestellmenge unterhalb von "Belege" nicht mehr auf den Lagerbestand auswirken. Nehmen Sie entsprechende Mengenänderungen also bei den "Bestellpositionen" vor, damit Lagerbestände berücksichtigt werden. Generell werden für stornierte Artikel die Versandkosten nicht neu berechnet. </p>', ''),
(106, 103, 'Suchen', 4, 0, 'Suchen Artikelsuche query', '<p><br></p>', 'Suchen', '<p>Die Artikel Suchfunktion ist ein wichtiges Werkzeug für das Auffinden \r\nangelegter Artikel in Shopware. Sie können hier nach verschiedenen Kriterien \r\neinen Artikel ausfindig machen. Wählen Sie dazu einfach die entsprechenden \r\nSuchkriterien aus bzw. geben zusätzlich Ihren Suchbegriff ein. Nach Klicken auf \r\n"Suchen" werden die Ergebnisse unten aufgelistet.</p><p>Diese Artikelsuche stellt folgende Suchoptionen bereit:</p><ul><li>Suche nach Hersteller</li><li>Suche nach Bestellnummer</li><li>Suche nach Artikelbezeichnung</li><li>Suche nach nicht vorrätigen Artikeln</li><li>Suche nach Artikeln ohne Kategorie-Zuordnung</li><li>Suche nach Artikeln ohne Bilder<br></li></ul>\r\n<p>Aus der Suche heraus können Sie zudem Artikel bearbeiten/löschen, sehen wie \r\nhoch der Lagerbestand ist und ob ein Artikel aktiv bzw. inaktiv ist. Inaktive \r\nArtikel finden Sie beispielsweise nur in der Administration und nicht offiziell \r\nin der Storefront.</p><p><b>sQuery</b><br>Über den Reiter "sQuery" können Sie zusätzliche Abfragen nutzen und selbst \r\nzusammenstellen, um sich Artikel bestimmten Eigenschaften gefiltert anzeigen zu \r\nlassen bzw. danach zu suchen.</p>\r\nSie können entweder auf bestehende Abfragen zurückgreifen und sich dann über \r\n"Abfrage durchführen" die entsprechenden Ergebnisse anzeigen lassen, oder selber \r\neine individuelle Abfrage bauen. Dazu sollten Sie sich aber mit der \r\nDatenbank-Struktur von Shopware (SQL) auskennen.', ''),
(53, 1, 'Einstellungen', 4, 0, '', '', '', '', ''),
(65, 53, 'Benutzerverwaltung', 2, 0, 'Benutzerverwaltung Benutzer User Admin', '<p><br></p>', 'Benutzerverwaltung', '<p></p><p>Über die Benutzerverwaltung steuern Sie den Zugriff für autorisierte Personen für die Administration von Shopware. <br>Ein neuer Benutzer kann unter "Einstellungen / Benutzerverwaltung" angelegt werden. Füllen Sie hier bitte alle Felder aus und bestätigen mit "Speichern". Der neue Benutzer ist sofort aktiv und kann sich mit den vergebenen Zugangsdaten einloggen.</p><p><b>Hinweis:</b> Ein Benutzer-Account kann nicht zur gleichen Zeit von mehreren Personen genutzt werden. Legen Sie für die gleichzeitige Administration von Shopware dazu bitte entsprechend viele Benutzer-Accounts an.<br></p>', ''),
(66, 53, 'Versandkosten', 3, 0, 'Versand Versandkosten', '<p><br></p>', 'Versandkosten', '<p>Unter &quot;Einstellungen / Versandkosten&quot; haben  Sie die Möglichkeit, eine Vorgabe für die Berechnung der Versandkosten zu  definieren. Tragen Sie die für Sie relevanten Kosten in die Eingabefelder ein  und bestätigen die Eingabe mit &quot;Speichern&quot;. Zusätzlich können für  jede Zahlungsart Zuschläge definiert werden, dazu folgen Sie einfach dem Link  beim entsprechenden Hinweis dazu.</p>\r\n<p>Unter „Versandarten-Einstellungen“ können Sie angelegte Versandarten bearbeiten oder auch neue hinzufügen. Um eine neue Versandart zu definieren, tragen Sie die Bezeichnung unter „oder neu“ ein. Mit Position, z.B. 3, wird die jeweilige Versandart im Pulldown Menü in der dritten Zeile als Auswahlmöglichkeit angeboten. Mit der Checkbox „Gültig für Versandkostenfrei“ können Sie entscheiden, ob die normale Regelung für "Versandkostenfrei ab" des jeweiligen Landes oder Zone greifen soll. Der Beschreibungstext wird im Shop standardmäßig auf der Warenkorbseite, sowie der Bestellabschlussseite angezeigt. In der darauffolgenden Auswahlbox wird festgelegt, für welche Länder die Versandart zur Verfügung steht. Mehrere Länder können mit gleichzeitig gedrückter strg-Taste markiert werden. </p>\r\n<p>Hinweis: Zonen-Einstellungen und länderspezifische Einstellungen beziehen sich immer auf die aktive, gewählte Versandart.</p>\r\n<p>Bei &quot;Zonen-Einstellungen&quot; können den Gruppen  &quot;Deutschland&quot;, &quot;Europa&quot; und &quot;Welt&quot;  unterschiedliche Versandkosten zugeordnet werden. Hier können Sie auch „Versandkostenfrei  ab“ definieren und dem Kunden somit ab einer bestimmten Einkaufswert keine  Versandkosten mehr berechnet werden. Des weiteren können Versandkosten-Staffeln  &quot;Versandkosten nach Gewicht (kg)&quot; hinterlegt werden. Durch Eingabe  eines optionalen Faktors lassen sich die Versandkosten so auch automatisch nach  Gewicht anpassen. </p>\r\n<p>Für jedes Land können optional länderspezifische  Einstellungen vorgenommen werden. Nachdem im Pulldown Menü ein Land gewählt  wurde, sind die Einstellungsmöglichkeiten identisch wie bei  &quot;Zonen-Einstellungen&quot;. </p>', ''),
(67, 53, 'Zahlarten', 4, 0, 'Zahlungsart Zahlart', '<p><br></p>', 'Zahlarten', '<p>Unter diese Einstellungen lassen sich die in Shopware verwendeten \r\nZahlungsarten bearbeiten, aktivieren oder deaktivieren.<br></p>\r\n<p>Bezeichnung, Template, Class und Table können definiert werden. Die \r\nBezeichnung wird im Shop ausgegeben, z. B. Nachnahme zzgl. 2,00€ \r\nNachnahmegebühr. Zusätzlich kann hier ein Aufschlag für Versandkosten angegeben \r\nwerden. </p>\r\n<p>Über das Feld "Position" bestimmen Sie die Reihenfolge, in welcher die \r\nZahlungsarten in der Storefront angezeigt werden. </p>\r\n<p>Unter "Aktiv" können Zahlungsarten aktiviert / deaktiviert werden (1=aktiv, \r\n0=inaktiv). </p>\r\n<p>Darunter finden Sie die Möglichkeit, Zahlungsarten für ESD-Produkte \r\n(Download-Artikel) zu aktivieren bzw. zu deaktivieren. </p>\r\n<p>Weiterhin können Sie angeben, ob das hinterlegte Template in einem \r\nInlineframe angezeigt werden soll (1=aktiv, 0=inaktiv). Dieses wird z. B. bei \r\nKreditkarte oder Lastschrift verwendet.<br></p>\r\n<p>Im Feld "Für Neukunden sperren" definieren Sie, ob die jeweilige Zahlungsart \r\nbereits Neukunden zur Verfügung stehen soll. Ist diese Einstellung aktiv (1), so \r\nwird diese Zahlungsart erst ab der 2. Bestellung des Kunden zur Verfügung \r\ngestellt.</p>\r\n<p><b>Hinweis: </b>Mit dem Modul "Riskmanagement" können Sie eine zusätzliche \r\neffektive Sicherheit gegen Zahlungsausfälle bei kritischen Zahlungsarten \r\nerzielen. Über ein komplexes Regelwerk werden so Zahlungsarten dynamisch an die \r\njeweiligen Shopkunden angepasst. Bestimmte Zahlungsarten können dadurch auch \r\nautomatisch gesperrt werden, wenn z.B. 1. Bestellung aus dem Ausland, \r\nBestellwert &gt; X, Bestellung aus Kategorie XY, etc..</p>', '');
INSERT INTO `s_help` (`id`, `parent`, `description`, `position`, `alias`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`) VALUES
(68, 53, 'Textvorlagen', 5, 0, 'Registrierung eMail Bestell-eMail', '<p><br></p>', 'Textvorlagen', '<div id="TABLEDIV">\r\n<p>Unter "Einstellungen / Textvorlagen" können alle eMail-Vorlagen angepasst und \r\nindividualisiert werden. Die benötigten Vorlagen sind abhängig von den \r\nlizenzierten Module.</p>\r\n<p><b>Folgende Vorlagen werden standardmäßig benötigt:</b><br></p>\r\n<ul><li>sORDER = Bestellbestätigung<br></li><li>sPASSWORD = Passwort vergessen<br></li><li>sREGISTERCONFIRMATION = Registrierbestätigung<br></li><li>sTELLAFRIEND = Artikel weiterempfehlen</li></ul>\r\n<p><br>&nbsp;</p>\r\n<p><u><b>Beispiel Variablen:</b></u><br>Mit Verwendung der Variablen wird die \r\neMail personalisiert (z. B. Name und Nachname) Die Variablen werden automatisch \r\ndurch die abgefragten Inhalte ersetzt.<br></p>\r\n<p>{sShop} = Name Ihres Shops<br>{sShopURL} = Adresse zu Ihrem Shop<br></p>\r\n<p><br>&nbsp;</p>\r\n<hr>\r\n\r\n<p><u><b>Anwendungsbeispiel Bestellbestätigung:</b></u></p>\r\n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br><br>vielen \r\nDank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrderNumber}) am \r\n{$sOrderDay} um {$sOrderTime}.<br>Informationen zu Ihrer \r\nBestellung:<br><br>{$sTable}<br><br>Versandkosten: \r\n{$sShippingCosts}<br>Gesamtkosten Netto: {$sAmountNet}<br>Gesamtkosten Brutto: \r\n{$sAmount}<br><br>Gewählte Zahlungsart: \r\n{$additional.payment.description}<br><br>{if $additional.payment.name == \r\n"prepayment"}<br>Die Ware wird umgehend nach Geldeingang verschickt.<br>Unsere \r\nBankverbindung:<br>Konto: ###<br>BLZ: ###<br>{/if}<br><br>{if $sComment}<br>Ihr \r\nKommentar:<br>{$sComment}<br>{/if}<br>Rechnungsadresse:<br>{$billingaddress.company}<br>{$billingaddress.firstname} \r\n{$billingaddress.lastname}<br>{$billingaddress.street} \r\n{$billingaddress.streetnumber}<br>{$billingaddress.zipcode} \r\n{$billingaddress.city}<br>{$billingaddress.phone}<br>{$additional.country.countryname}<br><br>Lieferadresse:<br>{$shippingaddress.company}<br>{$shippingaddress.firstname} \r\n{$shippingaddress.lastname}<br>{$shippingaddress.street} \r\n{$shippingaddress.streetnumber}<br>{$shippingaddress.zipcode} \r\n{$shippingaddress.city}<br>{$additional.country.countryname}<br><br>{if \r\n$billingaddress.ustid}<br>Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br>Bei \r\nerfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br>bestellen, erhalten \r\nSie Ihre Ware umsatzsteuerbefreit.<br>{/if}</p>\r\n<p><br></p>\r\n<hr>\r\n\r\n<p><u><b>Anwendungsbeispiel Weiterempfehlung:</b></u></p>\r\n<p>Hallo,<br><br>{sName} hat für Sie bei {sShop} ein interessantes Produkt \r\ngefunden, dass Sie sich anschauen \r\nsollten:<br><br>{sArticle}<br>{sLink}<br><br>{sComment}<br><br>Bis zum nächsten \r\nMal und mit freundlichen Gruessen,</p>\r\n<hr>\r\n\r\n<p><u><b><br>Anwendungsbeispiel Passwort vergessen:</b></u><br></p>\r\n<p>Hallo,<br><br>ihre Zugangsdaten zu {sShopURL} lauten wie folgt:<br>Benutzer: \r\n{sMail}<br>Passwort: {sPassword}<br></p></div>', ''),
(69, 70, 'Aktionsmodul', 5, 0, 'Werbebanner Banner Aktionen Aktionsmodul', '<p><br></p>', 'Aktionsmodul', '<p><b>Modulbeschreibung<br></b>Speziell für individuelle Aktionen und \r\nAngebote bietet das Aktionsmodul die Möglichkeit, innerhalb von Shopware \r\nkomplett individuelle HTML-Seiten zu generieren, die weit über normale \r\nKategoriendarstellungen und Bannerfunktionen hinausgehen. Der Clou dabei ist, \r\ndass der Shopbetreiber sich nicht mit der Programmierung von Internetseiten \r\nauskennen muss. Shopware liefert hierzu ein Baukastensystem, in dem der \r\nShopbetreiber sich einfach die Elemente per Drag &amp; Drop aus dem Portfolio \r\nvon Shopware zusammenbaut. Das ganze geschieht natürlich komplett im Look &amp; \r\nFeel des gesamten Online-Angebots, dadurch erhält der Shop eine ganz persönliche \r\nNote.</p>\r\n<div>\r\n<ul class="lst_bullet" style="margin: 0px 0px 0px 15px;" mce_style="margin: 0px 0px 0px 15px;"><li>Aktionsseiten lassen sich an beliebigen Stellen im Shop integrieren \r\n</li><li>Jede Aktionsseite kann individuell und in beliebiger Reihenfolge aus \r\nTextblöcken, Bannern, Artikel-Zusammenstellungen und Link-Zusammenstellungen \r\nbestehen \r\n</li><li>Drag &amp; Drop Funktionen für die einfache Erstellung innerhalb weniger \r\nMinuten </li></ul></div>\r\n<p><b>Funktionsweise<br></b>Markieren sie die gewünschte Kategorie, in \r\nder die Aktion später in der Storefront angezeigt werden soll und \r\nklicken dann auf den Button zum Bearbeiten. Vergeben Sie einen sinnvollen Namen für \r\neine neue Aktion, definieren die Position und wählen optional eine Zeitraum \r\nfür die Gültigkeit der Aktion.<br>Laden Sie dann über "Durchsuchen" die \r\ngewünschte Grafik für die Aktion hoch, die je nach Template schon im korrekten \r\nFormat/Größe vorliegt.</p>\r\n<p>Jetzt gibt es 2 mögliche Aktions-Varianten:</p>\r\n<ol><li>Nutzung des Direktlinks<br>Hier können Sie eine URL angeben, die entweder \r\nz.B. auf eine Kategorie, Artikeldetailseite in Shopware oder auch auf ein \r\nexterne Adresse verweist. Das Linkziel intern/extern muss dazu entweder auf \r\nShopware oder extern eingestellt werden. Die Eingabe eines Direktlinks \r\ndeaktiviert dabei die Nutzung der Container-Funktion.</li><li>Nutzung von Containern<br>Dieses Werkzeug stellt die ein einfaches \r\nBaukastensystem bereit, um in Shopware komplett nach individuellen Wünschen \r\neigene HTML-Seiten zu kreieren. Ihnen stehen dazu Container für HTML-Texte, \r\nBanner, Artikel-Gruppen und Link-Container bereit. Alle Container können dabei \r\nbeliebig verschachtelt werden.<br> </li><ul><li>HTML-Text<br>Wählen Sie diese Option, um eigene Texte in eine Aktion \r\neinzufügen.</li><li>Banner<br>Über Banner kann ähnlich wie bei dem Direktlink eine Grafik hoch \r\ngeladen und mit einem Hyperlink versehen werden.</li><li>Artikel-Gruppe<br>Definieren Sie hier Artikel, die über diese Aktion \r\nbeworben werden sollen. Die Vorgehensweise und Möglichkeiten sind hier im \r\nPrinzip identisch wie bei den Einkaufswelten.</li><li>Link-Gruppe<br>Über die Link Gruppe können Sie interessante Links/URL´s mit \r\nder Aktion verlinken. Das Linkziel kann auch hier wiederum intern oder extern \r\nsein.</li></ul></ol>\r\n<div>Die angelegten Aktionen bzw. Container werden automatisch als Tree-Menü auf \r\nder linken Seite - ähnlich wie Kategoriebäume - zusammengebaut und können per \r\nDrag &amp; Drop verschoben werden, je nach gewünschter Anzeige für die \r\nStorefront.</div>', ''),
(71, 70, 'Gutscheine', 3, 0, 'Gutschein', '<p><br></p>', 'Gutscheine', '<p>Um einen neuen Gutschein anzulegen klicken Sie auf "Neuer Gutschein". Geben \r\nSie unter "Name" zunächst eine Bezeichnung für den Gutschein ein. Bei "Code" \r\ndefinieren Sie einen Code aus beliebigen Zahlen und/oder Buchstaben. Dieser Code \r\nist anschließend der Gutschein-Code, den Kunden in Shopware einlösen können. \r\n</p>\r\n<p>Unter "Maximal Einlösbar" kann optional noch eine Zahl eingesetzt werden, wie \r\nviele Gutscheine von Kunden insgesamt max. eingelöst werden können. </p>\r\n<p>Unter "Wert" legen Sie einen Betrag in EUR fest, den der Kunde bei \r\nBestellungen als Rabatt bekommen soll. Der "Mindestumsatz" bestimmt den \r\nminimalen Betrag, ab wann ein Gutschein bei einer Bestellung eingelöst werden \r\nkann.</p>\r\n<p>Eine weitere Möglichkeit ist, die Bestellung durch einen Gutschein als \r\n"Versandkostenfrei" auszuliefern.</p>\r\n<p>Falls ein Gutschein nur für einen gewissen Zeitraum einlösbar sein soll, \r\nkönnen Sie im weiteren Verlauf einen Gültigkeits-Zeitraum festlegen. </p>\r\n<p>Das Feld "Bestell-Nr." ist ein optionales Feld, um dem Gutschein auch eine \r\nBestellnummer zuzuweisen. Diese Bestellnummer wird dann mit an die \r\nWarenwirtschaft (sofern vorhanden) übergeben, damit der Gutschein dort korrekt \r\nverbucht werden kann.</p>\r\n<p>Zum Ende brauchen Sie nur noch auf "Speichern" klicken und die Daten werden \r\nübernommen. </p>\r\n<p><b>Hinweis:</b> Der Gutschein für "Artikel weiterempfehlen und Gutschein \r\nkassieren", wird hier ebenfalls gepflegt. (Vorausgesetztes Modul: Vouchers \r\nAdvanced)<br></p>', ''),
(72, 70, 'Übersicht', 1, 0, 'Statistik Umsatz Übersicht Bestellungen ', '<p><br></p>', 'Übersicht', '<p>Die Übersicht liefert einen zentralen Überblick über die Umsätze und wichtigsten Kennzahlen des aktuellen Monats, optional auch für individuell definierte Zeiträume.  </p>', ''),
(73, 70, 'Statistiken', 2, 0, 'Statistiken Umsatz Charts Diagramme Auswertung', '<p><br></p>', 'Statistiken', '<p>Übersicht Statistiken:</p><p><b>Hinweis:</b> Für die meisten grafischen Auswertungen steht auch eine tabellarische Form zur Verfügung. Zusätzlich ist der Download einer CSV-Datei möglich.<br></p><ul><li>Abgebrochene Warenkörbe<br>Übersicht über Besucher, Bestellungen, Klicks und abgebrochene Warenkörbe. Der angezeigte Zeitraum ist frei definierbar.<br><br></li><li>Conversion Rate<br>Die Conversionrate setzt zusammen aus:  [(Visits / Bestellungen) x 100]<br>Über Pulldown Menü ist jeweils ein 8-Wochen-Überblick wählbar.<br><br></li><li>Umsatz / Besucher<br>Umsatz, Visits und Hits im 8-Wochen-Überblick<br><br></li><li>Umsatz nach Kategorien<br>Hier können Umsätze aller Kategorien und Unterkategorien eingesehen werden.<br><br></li><li>Umsatz nach Kalenderwochen<br><br></li><li>Umsatz nach Monaten<br>Jahresüberblick (12 Monate)<br><br></li><li>Umsatz mit Gutscheinen<br>Übersicht der verwendeten Gutscheine und dem damit verbundenen Umsatz. Der Zeitraum ist frei wählbar.<br><br></li><li>Kunden nach Umsatz<br>Auflistung der Kunden mit getätigtem Umsatz in einem frei definierbarem Zeitraum.<br><br></li><li>Länder<br>Aus welchen Ländern sind Bestellungen eingegangen? Mit Mouseover erhalten Sie die dazugehörigen Prozentangaben.<br><br></li><li>Referer<br>Von welcher Seite sind die Besucher auf Ihren Shop gelangt? Durch Klick auf einen Balken bekommen Sie detailliertere Angaben. Mit einem erneuten Klick gelangen Sie direkt auf die Seite, von der der Besucher gekommen ist.<br><br></li><li>Google Keywords<br>Besucher sind mit aufgeführten Keywords, über Google auf Ihrem Shop gelangt. Der angezeigte Zeitraum ist frei definierbar.<br><br></li><li>Suche<br>Diese Statistik gibt Aufschluss über die Suchbegriffe. Welche Begriffe wurde am häufigsten verwendet.<br><br></li><li>Article View / Sales<br>Hier erkennen Sie, welche Artikel das meiste Interesse beim Besucher geweckt haben und wie viele Artikel verkauft wurden. Hieraus ergibt sich das Scoring. [(Bestellungen / Aufrufe) x 100]<br><br></li><li>Article Sales<br>Auflistung aller Artikel samt Aufrufe und Verkäufe<br><br></li><li>Article View<br></li></ul>', ''),
(74, 70, 'Adwords-Generator', 6, 0, 'Adwords google', '<p><br></p>', 'Adwords-Generator', '<p>Der Google Adwords-Generator stellt ein nützliches Werkzeug bereit, um \r\nWerbekampagnen schneller in Google Adwords einzustellen. </p><p>Markieren Sie dazu \r\neinfach über denn Kategoriebaum die gewünschte Kategorie, die Sie einstellen \r\nwollen, definieren den aktiven Zeitraum und eine maximale CPC-Rate und klicken \r\nSie anschließend auf "Export". Shopware erstellt jetzt automatisch aufgrund \r\nIhrer eingestellte Optionen einen&nbsp;CSV-Datenexport &nbsp;inkl. der jeweiligen \r\nArtikeldaten aus der gewählten Kategorie. Dieser Datenexport kann anschließend \r\ndafür verwendet werden, die gewünschten Kampagnen per Copy &amp; Past in Google \r\nAdwords zu portieren. Der Export enthält dabei alle relevanten Daten, die zum \r\nEinstellen notwendig sind.</p>', ''),
(76, 70, 'Partnerprogramm', 4, 0, 'Partnerprogramm Affiliate Partner ', '<p><br></p>', 'Partnerprogramm', '<p>Nutzen Sie dieses Marketinginstrument, um Artikel aus Shopware auch auf \r\nanderen Plattformen im Internet zu präsentieren. Schaffen Sie die notwendige \r\nAttraktivität bei der Platzierung Ihrer Artikel auf fremden Portalen, indem \r\nSie Partner unmittelbar am Umsatz beteiligen. Durch das gezielte Werben mit \r\nArtikeln auf anderen Internetseiten steigern Sie die Besucherzahl und den \r\nVerkauf und damit die Effizienz des Webshops. </p>\r\n<ul class="lst_bullet" style="margin: 0px 0px 0px 15px;" mce_style="margin: 0px 0px 0px 15px;"><li>Jeder Partner erhält einen individuellen Code </li><li>Automatisches Generieren von Hyperlinks (Link zum Artikel + Code) </li><li>Tracking aller Umsätze durch Shopware </li><li>Berechnung Provisionen an Partner </li></ul>\r\n<p>Ein potentieller Partner kann sich z. B. über ein Formular, welches unter \r\n"Shopinhalte / Formulare" erstellt werden kann, mit Ihnen in Verbindung \r\nsetzen.</p>\r\n<p>Mit Klick auf "Neuen Partner anlegen" können Sie alle erforderlichen Daten \r\nfür einen neuen Partner eingeben.</p>\r\n<p><b>Maske Partnerdetails</b> (Pflichtfelder)<b>:</b></p>\r\n<p><u>Tracking-Code:</u> Dieser muss eindeutig und ohne Leer- oder Sonderzeichen \r\nhinterlegt werden, z. B. musterfirma. Hieraus wird dann folgender Link erzeugt: \r\nhttp://www.ihrshop.de/shopware.dll/sPartner,musterfirma/</p>\r\n<p><u>Firma:</u> Name der Unternehmung / Firma</p>\r\n<p><u>Provision in %:</u> Eintragen des Prozentsatzes für die Umsatzbeteiligung. \r\nBezieht sich auf den Netto-Gesamtwert (ohne Versandkosten) bei einer Bestellung. \r\nJe nach Partner können unterschiedliche Prozentsätze definiert werden</p>\r\n<p><u>Gültigkeit Cookie (optional):</u> Bei der Gültigkeit kann festgelegt \r\nwerden, wie lange der Partner verprovisioniert wird. 86400 = ein Tag, 0 = eine \r\nBestellung. Die Eingabe erfolgt in Sekunden.</p>\r\n<p>Anschließend kann in der Tabelle durch Klick auf das "Statistik-Icon" die \r\nAuswertung aufgerufen werden. Zusätzlich zu den verprovisionierten Beträgen wird auch der Partner-Link angezeigt, den der Partner dann z.B. über ein \r\nentsprechendes Banner auf seiner Webseite mit Shopware verknüpfen kann. Jeder \r\nShopbesucher, der über diesen Link auf den Webshop kommt wird dann automatisch \r\ngetrackt und bei Bestellungen werden entsprechende Provisionen beim Partner \r\ngutgeschrieben. Sofern die Gültigkeit der Cookies auf einen längeren Zeitrahmen \r\neingestellt ist, wird der Partner sogar auch verprovisioniert, wenn der Kunde in \r\ndem eingestellten Zeitraum erneut den Shop besucht und eine Bestellung tätigt. \r\nDabei ist dann unabhängig, ob er über den Partner Link kommt oder evtl. sogar \r\ndirekt den Shop aufruft.</p>', ''),
(77, 50, 'abgebrochene Bestellungen', 4, 0, '', '<p><br></p>', 'Abgebrochene Bestellungen', '<p>Die abgebrochenen Bestellungen geben eine Übersicht über Kunden, die ein oder \r\nmehrere Artikel bereits im Warenkorb hatten, die Bestellung aber nicht \r\nabgeschlossenen haben. Über das Info Icon kann man sich zudem anschauen, welche \r\nArtikel genau im Warenkorb lagen. Diese Informationen kann der Shopbetreiber \r\ndann unter Hilfestellung für die Optimierung bestimmter Artikel \r\nund/oder Artikelpreise verwenden.</p>', ''),
(78, 50, 'Service', 5, 0, 'rma Rückgabe defekt', '<p><br></p>', 'Service', '<p>Das RMA-Management (Service) kann effektiv in Verbindung mit dem \r\nFormulargenerator (unter Inhalte / Shopinhalte / Formulare zu finden) eingesetzt \r\nwerden, um jegliche Rücksendungen von Waren so optimal wie nur möglich zu \r\nbearbeiten. </p><p>Wenn die nötigen Formulare über den Formulargenerator angelegt sind, \r\nkönnen Kunden komfortabel über die Storefront Formulare (z.B. für Reklamationen \r\noder im Rahmen des Widerrufsrechts) ausfüllen. Sie erhalten dann durch Shopware \r\nautomatisch eine so genannte RMA-Nummer, die Sie dann beim Rücksenden von Ware \r\nbenutzen können. Der Shopbetreiber bekommt dann hier unter "Service" eine \r\nÜbersicht und kann z.B. auch gewisse nicht berechtigte Reklamationen ablehnen. \r\nGenerell wird durch den Einsatz&nbsp;des RMA-Managements der gesamte Prozess von \r\nRücksendungen für den Shopbetreiber und auch für den Shopkunden stark optimiert \r\nund vor allem beschleunigt.</p>', ''),
(102, 31, 'Kundengruppen', 5, 0, 'Händler Händlerbereich Kundengruppen B2B B2C', '<p><br></p>', 'Händlerbereich / Kundengruppen', '<div><strong>Modulbeschreibung<br></strong>Wenn Sie mehr als eine Käuferschicht \r\nbedienen wollen, ist dieses Modul genau das Richtige! Definieren Sie beliebig \r\nviele Kundengruppen und statten diese mit eigenen Preisen, Staffeln oder \r\nRabatten aus. Ihre Shopware ist somit für B2B und B2C optimal \r\naufgestellt.<br></div>\r\n<ul><li>Individuelle Preise je Kundengruppe \r\n</li><li>Rabatte und Staffeln je Kundengruppe \r\n</li><li>Kunden lassen sich individuell Kundengruppen zuordnen </li><li>Brutto-Netto-Betrieb je Kundengruppe und automatische Visualisierung in der \r\nStorefront</li></ul>\r\n<div><br><strong>Funktionsweise<br></strong>Standardmäßig ist bereits eine \r\nKundengruppe "Shopkunden" angelegt, die in&nbsp;jeder Shopware Core Version enthalten \r\nist.</div>\r\n<div>Klicken Sie auf "Neue Kundengruppe", um Shopware um eine weitere \r\nKundengruppe zu ergänzen. Vergeben Sie zunächst eine Bezeichnung, die später \r\nauch zugehörigen Kunden nach Login in der Storefront angezeigt wird.&nbsp;Das gilt \r\nebenso für die Anzeige von Brutto- und Nettopreisen in der Storefront, die \r\nindividuell eingestellt werden kann. Ist z.B. in der Gruppe "Shopkunden" der \r\nBruttobetrieb eingestellt, so werden alle Preise inkl. Mehrwertsteuer angezeigt. \r\nLoggt sich nun ein Kunde in den Kundenbereich ein, der einer Kundengruppe \r\nangehört, die auf Nettobetrieb in der Storefront steht, so switcht der gesamte \r\nShop automatisch von Brutto- in Nettobetrieb um.</div>\r\n<div>&nbsp;</div>\r\n<div>Des weiteren habe Sie grundsätzlich 2 Möglichkeiten, die neu angelegte \r\nKundengruppe zu klassifizieren:</div>\r\n<ol><li>Eigene Preise je Artikel<br>Ist dieser Modus eingestellt, so&nbsp;werden bei \r\njedem Artikel in der Artikeleingabe auch eigene Preise verlangt, die in der \r\nAdministration je nach Voreinstellung entweder Brutto oder Netto eingegeben \r\nwerden können. Wenn Sie nachträglich eine Kundengruppe mit eigenen Preisen in \r\nShopware einfügen und das gesamte Artikelsortiment schon besteht, müssen die \r\nPreise für eine neue Kundengruppe nach gepflegt werden. Dafür wird die \r\nPreismatrix unter "Artikel / Neu" durch Shopware erweitert.</li><li>Globaler Rabatt<br>Hier können Sie jederzeit problemlos neue Kundengruppen \r\nmit festen Rabatten auf alle Preise der Gruppe "Shopkunden" anlegen. Die Preise \r\nwerden durch Shopware dann automatisch umgerechnet und können nach Login&nbsp;in der \r\nStorefront&nbsp;wiederum nach Einstellung Brutto oder Netto angezeigt werden. \r\nShopware zeigt eingeloggten Kunden dann bereits die reduzierten Preise \r\nan.</li></ol>\r\n<div>Auch können Sie für jede Kundengruppe Warenkorb-Rabatt-Staffeln anlegen, \r\ndie ab einem gewissen Warenkorb-Wert automatisch aktiv werden. Es zählt dann \r\nimmer die höchst eingestellte Rabattstaffel, die im Warenkorb erreicht, als \r\nBerechnungsgrundlage für einen Warenkorbrabatt.</div>\r\n<div>\r\n<p><b>Hinweis:</b> Haben Sie einer Kundengruppe bereits Kunden zugeordnet, so \r\nkönnen Sie diese zwar bearbeiten, aber nicht mehr löschen!<br></p></div>', ''),
(103, 1, 'Artikel', 1, 0, '', '', '', '', ''),
(107, 103, 'Hersteller', 5, 0, 'Hersteller Lieferanten', '<p><br></p>', 'Hersteller', '<p>Hier werden alle Hersteller des gesamten Produktsortiments verwaltet. Klicken sie auf "Neuer Hersteller", um neue Eingaben vorzunehmen. Optional \r\nbesteht ebenfalls die Möglichkeit, auch ein Herstellerlogo hochzuladen und eine \r\nHersteller-Webseite anzugeben. Diese Angaben können später sinnvoll für die \r\nStorefront genutzt werden.</p>\r\n<p><b>Hinweis: </b>Bitte achten Sie beim Hinterlegen der Logos auf \r\nTemplate-spezifische Vorgaben hinsichtlich der Größe von Hersteller-Grafiken. \r\nDie Grafiken werden nicht wie beim Bildupload automatisch skaliert!</p>', ''),
(109, 31, 'Tag-Wolke', 3, 0, 'Tag-Navigation Tag-Wolke Tagwolke', '<p><br></p>', 'Tag-Navigation', '<p><b>Modulbeschreibung<br></b>Im Zeitalter von Web 2.0 stellt Shopware eine moderne Tag-Navigation bereit, \r\ndie sich als alternative zur herkömmlichen Navigation an den Kauf- und \r\nSurfinteressen des Shopbesuchers orientiert. Shopware analysiert dabei \r\nautomatisch das Surfverhalten der Besucher und generiert selbstständig \r\nVorschläge für interessante Artikel.</p>\r\n<ul class="lst_bullet" style="margin: 0px 0px 0px 15px;" mce_style="margin: 0px 0px 0px 15px;"><li>Tag-Navigation auf Start- und Unterseiten \r\n</li><li>Automatische Aufbereitung der für den Besucher interessanten Artikel / \r\nThemen</li></ul><p><b>Funktionsweise:<br></b>Für die Tag-Wolke können (fast) beliebige Zahlenwerte eingegeben\r\nwerden, max. 4 Kategorien heißt z.B., dass in der Storefront 4\r\nKategorien ausgegeben werden. Das Gleiche gilt dann für 4 Artikel, 4\r\nSuchanfragen, etc.<br><br>\r\nDer "Name der Standardklasse" ist die niedrigste grafische Darstellung,\r\ndie angezeigt wird also "tag0". Anzahl der Stufen "4" bedeutet, dass es\r\ninsgesamt 5 Stufen (mit der Standardklasse) gibt, also tag0 bis tag4.\r\nJe höher die Stufe, desto größer wird der Begriff angezeigt...<br></p><p><br></p>', ''),
(141, 50, 'ePayment', 3, 0, 'ePayment UOS Lastschrift Kreditkarte', '<p><br></p>', 'ePayment', '<p>Hier erhalten Sie einen Überblick über alle Zahlungen, die über die \r\nePayment-Schnittstelle (Lastschrift, Kreditkarte, etc.) abgewickelt/gebucht \r\nwurden, in einem von Ihnen definierten Zeitraum. Geplatzte Lastschriften können \r\nSie z.B. finden, indem Sie nach dem Bezahlstatus "Inkasso" filtern. Alternativ \r\nkönnen Sie auch nach einer bestimmten Transaktionsnummer gezielt suchen. Alle \r\nErgebnisse werden automatisch unten in der Tabelle angezeigt.<br></p>\r\n<p>Über das Info-Icon gelangen Sie dann bei Bedarf in die jeweilige Bestellung \r\ndes Kunden.</p>', ''),
(137, 53, 'Riskmanagement', 6, 0, 'Riskmanagement Zahlungsausfälle vermeiden Sicherheit', '<p><br></p>', 'Riskmanagement', '<p><b>Modulbeschreibung:</b><br>Das Riskmanagement stellt ein effektives Regelwerk bereit, um\r\nZahlungsausfälle im Webshop bestmöglich zu minimieren. Alle gesammelten\r\nInformationen über Shopkunden - Produkte aus bestimmten Kategorien im\r\nWarenkorb, Bestellwert, Zahlungsausfälle in der Vergangenheit,\r\ngewünschte Zahlungsart, Auswertungen kritischer Zahlungsarten, etc. -\r\nwerden analysiert. Nach vollständiger Auswertung und Kategorisierung\r\ndes Shopkunden, leitet Shopware entsprechende Maßnahmen für kritische\r\nKunden ein und/oder gibt zusätzlich Hinweise an den Shopbetreiber. Ein\r\nnegatives Ergebnis bedeutet dabei nicht, mit dem jeweiligen Shopkunden\r\nkeine Geschäfte zu machen, sondern z.B. die bereitstehenden\r\nZahlungsarten im Hintergrund dynamisch an ihn anzupassen. Alle\r\nMaßnahmen können dabei automatisch und/oder durch den Shopbetreiber\r\nmanuell eingeleitet werden. Speziell bei Lastschriften oder\r\nKreditkartenzahlungen können so teure Rückabwicklungsprozesse nahezu\r\nausgeschlossen werden.</p>\r\n		  <ul class="lst_bullet" style="margin: 0pt 0pt 0pt 15px;" mce_style="margin: 0pt 0pt 0pt 15px;"><li>Auswertung geplatzter Bestellungen (Lastschrift, Kreditkarte), um Muster   für die häufigsten Storno-Merkmale zu erstellen</li><li>Regelassistent, um für jede "kritische" Zahlungsart Regeln und\r\nentsprechende Maßnahmen einzuleiten, die die Bereitstellung für\r\nShopkunden beeinflussen. Ein Beispiel für Lastschrift wäre z.B.:\r\nGesperrt falls &gt; Kunde IST Erstkunde UND Bestellwert &gt; 100 €\r\nODER Artikel aus der Kategorie X ODER Kunden- Herkunftsland NICHT\r\nGLEICH Deutschland</li><li>Nachträgliches Ändern von Zahlungsarten nach Bestelleingang möglich</li></ul><p><br></p>\r\n\r\n<p><b>Funktionsweise:<br></b>Wählen Sie über das Pulldown-Menü die Zahlungsart aus, für die Sie ein oder \r\nmehrere Regeln anlegen wollen und klicken auf (Auswählen). Zahlungsarten mit \r\nbereits angelegten Regeln werden später rot markiert. Hier können Sie dann \r\nbeliebig viele Regeln definieren, die für diese Zahlungsart gelten sollen. \r\nSobald eine Regel greift, wird die Zahlungsart deaktiviert! Jede Regel kann aus \r\nzwei Bedingungen bestehen, die dann beide gegeben sein müssen, damit die Regel \r\nzutrifft!</p>\r\n<div> </div>\r\n<div>Verfügbare Bedingungen:</div>\r\n<div> </div>\r\n<div><ul><li>Bestellwert&gt;=Trifft zu, wenn der Gesamtbestellwert größer oder gleich einem Betrag X \r\nist, Sample: z.b. nur Lastschrift bis 300,00 €</li></ul></div>\r\n\r\n\r\n\r\n<div>\r\n<div><ul><li>Bestellwert&lt;=Trifft zu, wenn der Gesamtbestellwert kleiner oder gleich einem Betrag X \r\nist, Sample: z.b. Rechnung erst ab einem Auftragswert von mind. 1000,00 €</li></ul></div>\r\n\r\n\r\n\r\n<div><ul><li>Kundengruppe IST Trifft zu, wenn der Benutzer der Kundengruppe X zugeordnet ist - normale \r\nShopkunden haben EK, die anderen Kundengruppen - Ids findet man in den \r\nKundengruppen-Einstellungen, Sample: Rechnung für Endkunden sperren &gt; Kundengruppe IST EK</li></ul></div>\r\n\r\n\r\n\r\n<div>\r\n<div><ul><li>Kundengruppe IST NICHT Trifft zu, wenn der Benutzer NICHT der Kundengruppe X zugeordnet ist - \r\nnormale Shopkunden haben EK, die anderen Kundengruppen - Ids findet man in den \r\nKundengruppen-Einstellungen, Sample: Lastschrift z.B. nur für Händler und für keine der anderen \r\nKundengruppe &gt; Kundengruppe IST NICHT H<br>\r\nDie ID für die gewünschte Kundengruppe finden Sie unter &quot;Einstellungen / Shop-Einstellungen / Module / Kundengruppen&quot;.</li></ul>\r\n</div>\r\n\r\n\r\n\r\n<div><ul><li>Neukunde IST WAHR Trifft zu, wenn der Kunde sich gerade angemeldet hat, Sample: Lastschrift für Neukunden sperren</li></ul></div>\r\n\r\n\r\n\r\n<div><ul>\r\n  <li>Zone IST Trifft zu, wenn der Kunde im Liefergebiet X sich befindet - mögliche \r\nOptionen, deutschland, europa, welt, Sample: Lastschrift für Europa sperren &gt; Zone IST europa</li>\r\n</ul></div>\r\n\r\n\r\n\r\n\r\n<div><ul><li>Zone IST NICHT Trifft zu, wenn der Kunde sich NICHT in Liefergebiet X befindet, Sample: Lastschrift nur für Deutschland &gt; Zone IST NICHT Deutschland</li></ul></div>\r\n\r\n<div><ul><li>Land IST Trifft zu, wenn der Kunde sich in Land X befindet, Sample: Lastschrift nur für Deutschland &gt; Land IST Deutschland</li></ul></div>\r\n\r\n<div><ul><li>Land IST NICHT Trifft zu, wenn der Kunde sich NICHT in Land X befindet, Sample: Lastschrift nur für Deutschland &gt; Land IST NICHT Deutschland<br/>\r\nHier ist der 2-stellige ISO-Code einzutragen. Diesen finden Sie in der Eingabemaske des jeweiligen Landes unter "Einstellungen / Shop-Einstellungen / System / Länderauswahl".</li>\r\n</ul></div>\r\n\r\n\r\n\r\n<div><ul><li>Bestellpositionen &gt;= x Trifft zu wenn die Bestellung aus mehr oder gleich X Positionen \r\nbesteht, Sample: Bestellung besteht aus mehr oder aus 2 Positionen &gt; Bestellpositionen &gt;= 2</li></ul></div>\r\n\r\n\r\n\r\n\r\n<div><ul><li>Inkasso ist WAHR Trifft zu, wenn eine vorherige Bestellung des Kunden den Status Inkasso \r\nhat, dann automatisch Zahlungsart sperren</li></ul></div>\r\n\r\n\r\n<div><ul><li>Keine Bestellung vor mind. x Tagen Trifft zu, wenn keine Bestellung gefunden wurde, die mindestens X Tage \r\nzurück liegt, Sample: Lastschrift erst nach 1 Bestellung freischalten, die mind. 7 Tage \r\nzurückliegt (um den Geldfluss abzuwarten) &gt; Keine Bestellung vor mind. X Tagen - 7</li></ul></div>\r\n\r\n\r\n\r\n\r\n<div><ul><li>Anzahl Bestellungen &lt;= X Trifft zu, wenn die Anzahl der vom Kunden getätigten Bestellungen kleiner \r\noder gleich X ist, Sample: Lastschrift erst nach der 5 Bestellung erlauben &gt; Anzahl Bestellungen &lt;= 4</li></ul></div>\r\n\r\n\r\n\r\n\r\n<div><ul><li>Artikel aus Kategorie X Trifft zu, wenn die Bestellung Artikel aus der Kategorie mit der ID X \r\nenthält, Sample: Lastschrift sperren, wenn Artikel aus der Kategorie Games (z.B. ID \r\n4) in der Bestellung sind &gt; Artikel aus Kategorie 4</li></ul></div>\r\n\r\n\r\n\r\n\r\n<div><ul><li>Postleitzahl IST X Trifft zu, wenn die PLZ des Kunden X ist, Sample: Lastschrift Immer sperren, wenn Kunde aus PLZ 48624 bestellt, oder \r\ndas als Lieferanschrift angegeben hat. Z.B. sinnvoll, wenn man einen Kunden hat, der immer wieder unter falschem \r\nNamen per Lastschrift bestellt, dann kann man global die PLZ für Lastschrift \r\nsperren</li></ul></div></div></div>\r\n\r\n', ''),
(138, 53, 'Shopcache leeren', 1, 0, 'Shopcache Cache cachen', '<p><br></p>', 'Shopcache leeren', '<p>Mit "Shopcache leeren" werden die temporär angelegten Dateien (Shopcache bzw. Puffer) des Webshops entfernt. Somit werden alle  Änderungen in der Administration, wie z. B. ein geänderter Artikel, sofort in der Storefront sichtbar.</p><p><b>Hinweis:</b> Die automatischen Caching-Zeiten können unter "Einstellungen / Grundeinstellungen / System / Performance" konfiguriert werden.<br></p>', ''),
(139, 103, 'Bewertungen', 6, 0, 'Bewertungen Artikelbewertung Rezensionen Kundenezensionen', '<p><br></p>', 'Bewertungen', '<p>Geben Sie Besuchern die Möglichkeit, Artikel zu bewerten und damit\r\nerweiterte Artikel-Informationen bereitzustellen von der alle Kunden\r\nprofitieren. Bewertungen können für jeden Artikel abgegeben werden. Über "Artikel / Bewertungen" sind alle Bewertungen einzusehen und können ggf. gelöscht werden.</p><p>Neu eingehende Bewertungen sind freizuschalten. Erst im Anschluss werden diese in der Storefront visualisiert. Je nach Template kann das Erscheinungsbild variieren (z. B. eine Punkte- oder Sternchen-Darstellung). Dem Besucher stehen 10 Bewertungsstufen zur Verfügung.</p><p><b>Hinweis: </b>Optional kann das Freischalten von Bewertungen auch deaktiviert werden, so dass diese unmittelbar in der Storefront sichtbar werden.<br></p>', ''),
(140, 26, 'ESD', 8, 0, 'Downloadartikel esd Electronic Software Distribution', '<p><br></p>', 'ESD (Electronic Software Distribution)', '<p><b>Modulbeschreibung:</b><br>Electronic Software Distribution (ESD) hilft \r\nIhnen bei dem Vertrieb von reinen Software Produkten. Diese Produkte werden \r\nonline bestellt, bezahlt und zum Download bereit gestellt. Neben Software können \r\nauch andere digitalen Artikel wie E-Books, Musik (mp3), Videos, Computerspiele \r\netc. auf ESD-Basis angeboten werden.</p>\r\n<p><b>Funktionsweise:</b><br>Klicken Sie dazu in den Artikel-Stammdaten auf den \r\nReiter "ESD" und neue ESD-Version anlegen. Über das Pulldown Menü wählen Sie den \r\ngewünschten Artikel. Bei einem Musikalbum kann z.B. als Hauptartikel das ganze \r\nAlbum hinterlegt werden und über Varianten (Reiter "Varianten"), kann zusätzlich \r\njeder Song einzeln abgelegt werden. So ist gewährleistet, das der Kunde nicht \r\ndas gesamte Album, sondern auch nur seine Lieblingstracks downloaden kann.</p>\r\n<p>Weiterhin kann optional mit Seriennummern gearbeitet werden. Klicken Sie \r\nhierzu auf "Seriennummern verwalten". In dem angezeigten Textfeld kann nun pro \r\nZeile jeweils eine Nummern eingetragen werden. Diese bekommt der Kunde inkl. \r\nDownloadlink nach Bestellung &nbsp;in seiner Bestellübersicht (Kundenkonto) im \r\nWebshop angezeigt.</p>\r\n<p><b>Hinweis:</b> Sind Seriennummer vergeben, so kann in der Übersicht durch \r\nKlick auf das Schlüssel-Icon nach Seriennummern gesucht werden. Zusätzlich sehen \r\nSie freie und vergebene Nummern mit Zuordnung zum jeweiligen Käufer.</p>', ''),
(143, 27, 'Filebrowser', 0, 0, '', '<p>\r\n</p>', 'Filebrowser', '<p>\r\n</p><div>Der Shopware Filebrowser eignet sich perfekt dafür, um zusätzlich Dateien (z.B. \r\nGrafiken) auf einfache Art und Weise in den Webshop zu integrieren. Alle Dateien \r\nkönnen dann entsprechend verlinkt und als zusätzliche Elemente für die \r\nStorefront eingesetzt werden. Das alles geht dabei völlig ohne jegliche \r\nHMTL-Kenntnisse.</div>\r\n<div><br>Mit dem in Shopware Datei-Browser können beliebige Dateien hoch geladen \r\nwerden. Wählen Sie zum Hinzufügen einer Datei das gewünschte Verzeichnis, in dem \r\ndie Daten abgelegt werden sollen. Standardmäßig wird hierfür das Verzeichnis \r\n"uploads" vorgesehen, Sie haben aber auch die Möglichkeit, \r\neigene&nbsp;Ordnerstrukturen anzulegen.&nbsp;Oben rechts im Dateibrowser bekommen Sie zu \r\nden jeweiligen Verzeichnissen automatisch eine Information, welche Rechte für \r\ndiese Verzeichnis gelten. Stehen die erforderlich Rechte zur Verfügung, kann \r\nüber "Durchsuchen" die gewünschte Datei ausgewählt werden. Achten Sie bei der \r\n"Bezeichnung" darauf, die Dateiendung mit anzugeben.</div>\r\n<div>&nbsp;</div>\r\n<div>Nach dem Upload wählen Sie wiederum das verwendete Verzeichnis und die \r\nsoeben hochgeladene Datei aus. Nun werden zusätzlich Dateigröße, URL, Preview \r\nangezeigt und zudem steht Ihnen hier auch eine Löschen-Funktion bereit. Die \r\nangezeigte Datei-URL kann per Cut &amp; Paste an beliebiger Stelle im Webshop \r\neingebunden werden, z.B. über die Bild-Funktion in der Artikelbeschreibung. \r\nEbenso können Sie auf diese Weise ein Firmenlogo hochladen, falls Sie die \r\nPDF-Belegerzeugung in Shopware nutzen möchten.<br></div>', ''),
(144, 70, 'Campaigns', 0, 0, '', '<p><br></p>', 'Campaigns', '<div><br></div><div><b>Modulbeschreibung</b><br>Mit Campaigns wird der Versand von \r\nprofessioneller eMail-Werbung zum Kinderspiel. Per Drag &amp; Drop können Sie \r\nNewsletter zusammenstellen, die dann im perfekten Shop-Layout an die von Ihnen \r\ngewünschten Empfangsgruppen versendet werden.<br></div>\r\n<div>Jeder Newsletter wird in Shopware genau erfasst und kann dezidiert \r\nausgewertet werden. So werden automatisch Diagramme generiert, die einen \r\nAufschluss über Read-,Click- und Conversion-Rate geben und einen bequemen \r\nVergleich des Erfolges der verschiedenen Mailings ermöglichen.</div>\r\n<div> </div>\r\n<div>Sie können für jeden Newsletter genau definieren, an welche Empfangsgruppen \r\ndieser geschickt werden soll. Hierbei werden automatisch alle im Shop \r\nhinterlegten Kundengruppen angezeigt. Desweiteren können eigene Empfangsgruppen \r\ndefiniert werden (z.B. Adressen Messe XY) und per Import mit eMail-Adressen \r\nbestückt werden.<br></div>\r\n<div>Der Newsletter selbst lässt sich bequem per Drag &amp; Drop \r\nzusammenstellen. Artikel können zum Beispiel einfach durch die Eingabe der \r\nBestellnummer in den Newsletter integriert werden. Shopware erstellt den \r\nNewsletter voll-automatisch in Ihrem Shop-Layout. HTML-Kenntnisse sind also \r\nnicht notwendig. Nach dem Versand stehen Ihnen eine Vielzahl von \r\nAuswertungsmöglichkeiten zur Verfügung um den Erfolg der Mailings messen zu \r\nkönnen.</div>\r\n<ul><li>Keine HTML-Kenntnisse zum Erstellen notwendig \r\n</li><li>Integrierte Vorschau-Funktion \r\n</li><li>Versand an beliebige Empfangsgruppen \r\n</li><li>Import für eMail-Adressen \r\n</li><li>Auswertung Umsatz je Newsletter \r\n</li><li>Auswertung Lese- und Klick-Rate \r\n</li><li>Personalisierte Newsletter möglich</li></ul>\r\n<div><br></div><div><b>Funktionsweise</b></div>\r\n<div>Im Campaigns Startfenster erhalten sie eine Übersicht über erstellte und \r\nversendete Newsletter, jeweils mit aktuellem Versandstatus und erweiterten \r\nInformationen. Sie sehen in der Übersicht z.B. schon genau, an wie viele \r\nEmpfänger ein Newsletter geht bzw. versendet wurde und bekommen automatisch eine \r\nÜbersicht, wie oft ein Newsletter gelesen wurde, wie viele Kunden durch den \r\nNewsletter auf den Webshop geleitet wurden und sogar wie viel Umsatz speziell \r\ndurch einen Newsletter generiert wurde.</div>\r\n<div> </div>\r\n<div>Unter dem Reiter „Auswertung“ erscheint ein Diagramm, welches die \r\nversendeten Newsletter samt Klicks, Views und Umsatz vergleicht. und über die \r\nEinstellungen könne Sie verschiedene eMail-Adressen hinterlegen, unter welchem \r\nAbsender ein Newsletter versendet werden soll, z.B. <a href="mailto:newsletter@ihrshop.de" mce_href="">newsletter@ihrshop.de</a>.</div>\r\n<div> </div>\r\n<div>Unter „Import“ können Sie gezielt nach angelegten eMail-Adressen im Webshop \r\nsuchen und bei Bedarf auch eMail-Adressen aus Empfängergruppen löschen. Des \r\nweiteren kann eine neue Empfänger Gruppe erstellt werden. Ebenfalls besteht die \r\nMöglichkeit, neue oder bestehende Gruppen auszuwählen und eMail-Adressen über \r\neinen CSV-Import in die jeweilige Gruppe zu importieren. Das Format der \r\nCSV-Datei muss folgendermaßen aufgebaut \r\nsein:<br>eMail1<br>eMail2<br>eMail3<br>Also je eine Adresse pro Zeile.<br>Beim \r\nImport wird automatisch überprüft, ob eMail-Adressen schon in der Datenbank \r\nvorhanden sind. Auch fehlerhafte Adressen werden visualisiert und nicht mit \r\nimportiert.</div><div><br></div>\r\n<div> </div>\r\n\r\n<div> </div>\r\n<div><b>Neuen Newsletter erstellen<br></b>Im ersten Step können Sie \r\ngenerelle Grundeinstellungen vornehmen, z. B. den Namen des Newsletters, \r\nAbsenders oder auch die Empfängergruppen definieren. Diese Einstellungen können \r\nim weiteren Verlauf auch noch wieder angepasst bzw. geändert werden. Um mit der \r\nErstellung fortzufahren, klicken Sie bitte auf „Speichern“.</div><div>Der Newsletter kann frei nach Ihren Wünschen aus 5 unterschiedlichen \r\nContainertypen aufgebaut werden. Diese können auch mehrfach verschachtelt \r\nwerden. Nach Erstellung eines Containers gelangen Sie durch Klick auf den \r\nNewsletternamen (Hauptordner im Treemenü) wieder auf die Hauptseite und können \r\nweitere Container definieren.</div><div><br></div>\r\n<div> </div>\r\n<div><b>Container-Optionen</b><br>Banner:  Einpflegen eines Banners \r\ninkl. Überschrift und einer Verlinkung (Link extern oder in Shopware)<br>Text:  \r\nAnlegen von Überschrift und eines nach Belieben gestaltbaren Textes über den \r\nWYSIWYG-Editors.<br>Artikel-Gruppe: Zuordnen von Artikeln. Zur Auswahl: \r\nZufällig,  Topseller, Neuheit oder fester Artikel. Bei einem fest zugeordneten \r\nArtikel wird zusätzlich die Bestellnummer aus dem Shop benötigt.<br>Link-Gruppe: \r\nZuordnung von Hyperlinks mit Namen. Diese können auf Seiten im Shop oder zu \r\nexternen Seiten verweisen.</div>\r\n<div> </div>\r\n<div>Hinweis: Die Positionen der Container können per Drag&amp;Drop frei \r\nbestimmt werden. Des weiteren befindet sich im Fuße des Treemenüs der Tabreiter \r\n„Optionen“.  Mit dem Punkt „Neu laden“ wird die gesamte Maske von Campaigns \r\naktualisiert. Mit „Vorschau“ verschaffen Sie sich einen ersten Eindruck. So \r\nkönnen Änderungen am Newsletter live betrachtet werden. Unter „Testmail“  können \r\nSie eine beliebige eMail-Adresse eintragen und so vorab einen Newsletter \r\nerhalten. <br></div>', '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order`
--

CREATE TABLE IF NOT EXISTS `s_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ordernumber` varchar(30) DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `invoice_amount` double NOT NULL DEFAULT '0',
  `invoice_amount_net` double NOT NULL,
  `invoice_shipping` double NOT NULL DEFAULT '0',
  `invoice_shipping_net` double NOT NULL,
  `ordertime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `status` int(11) NOT NULL DEFAULT '0',
  `cleared` int(11) NOT NULL DEFAULT '0',
  `paymentID` int(11) NOT NULL DEFAULT '0',
  `transactionID` varchar(255) NOT NULL,
  `comment` text NOT NULL,
  `customercomment` text NOT NULL,
  `internalcomment` text NOT NULL,
  `net` int(1) NOT NULL,
  `taxfree` int(11) NOT NULL,
  `partnerID` varchar(255) NOT NULL,
  `temporaryID` varchar(255) NOT NULL,
  `referer` text NOT NULL,
  `cleareddate` datetime NOT NULL,
  `trackingcode` varchar(255) NOT NULL,
  `language` varchar(10) NOT NULL,
  `dispatchID` int(11) NOT NULL,
  `currency` varchar(5) NOT NULL,
  `currencyFactor` double NOT NULL,
  `subshopID` int(11) NOT NULL,
  `o_attr1` varchar(255) NOT NULL,
  `o_attr2` varchar(255) NOT NULL,
  `o_attr3` varchar(255) NOT NULL,
  `o_attr4` varchar(255) NOT NULL,
  `o_attr5` varchar(255) NOT NULL,
  `o_attr6` varchar(255) NOT NULL,
  `remote_addr` varchar(255) NOT NULL,
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
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_basket`
--

CREATE TABLE IF NOT EXISTS `s_order_basket` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(70) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) NOT NULL,
  `shippingfree` int(1) NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `price` double NOT NULL DEFAULT '0',
  `netprice` double NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modus` int(11) NOT NULL DEFAULT '0',
  `esdarticle` int(1) NOT NULL,
  `partnerID` varchar(45) NOT NULL,
  `lastviewport` varchar(255) NOT NULL,
  `useragent` varchar(255) NOT NULL,
  `config` text NOT NULL,
  `currencyFactor` double NOT NULL,
  `ob_attr1` varchar(255) NOT NULL,
  `ob_attr2` varchar(255) NOT NULL,
  `ob_attr3` varchar(255) NOT NULL,
  `ob_attr4` varchar(255) NOT NULL,
  `ob_attr5` varchar(255) NOT NULL,
  `ob_attr6` varchar(255) NOT NULL,
  `liveshoppingID` int(11) NOT NULL,
  `bundleID` int(11) unsigned NOT NULL,
  `bundle_join_ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessionID` (`sessionID`),
  KEY `articleID` (`articleID`),
  KEY `datum` (`datum`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_order_basket`
--

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`, `ob_attr1`, `ob_attr2`, `ob_attr3`, `ob_attr4`, `ob_attr5`, `ob_attr6`, `liveshoppingID`, `bundleID`, `bundle_join_ordernumber`) VALUES
(1, '4tj0emcvatt9c1im33mlej65a7', 0, 'test', 1, 'SW10033', 0, 1, 1, 0.8403361345, '2010-10-18 02:17:17', 0, 0, '', 'registerFC', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10 ( .NET CLR 3.5.30729; .NET4.0E)', '', 1, '', '', '', '', '', '', 0, 0, ''),
(2, '6lltps7bqcudea4e2e1govb595', 0, 'test', 1, 'SW10033', 0, 1, 1, 0.8403361345, '2010-10-18 10:34:36', 0, 0, '', 'register', 'Mozilla/5.0 (Windows; U; Windows NT 6.0; de; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10 ( .NET CLR 3.5.30729; .NET4.0E)', '', 1, '', '', '', '', '', '', 0, 0, '');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_billingaddress`
--

CREATE TABLE IF NOT EXISTS `s_order_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL,
  `company` varchar(255) NOT NULL,
  `department` varchar(35) NOT NULL,
  `salutation` varchar(30) NOT NULL,
  `customernumber` varchar(30) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `street` varchar(100) NOT NULL,
  `streetnumber` varchar(6) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `city` varchar(70) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `fax` varchar(40) NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `ustid` varchar(50) NOT NULL,
  `text1` varchar(255) NOT NULL,
  `text2` varchar(255) NOT NULL,
  `text3` varchar(255) NOT NULL,
  `text4` varchar(255) NOT NULL,
  `text5` varchar(255) NOT NULL,
  `text6` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userid` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_billingaddress`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_comparisons`
--

CREATE TABLE IF NOT EXISTS `s_order_comparisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sessionID` varchar(70) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`),
  KEY `articleID` (`articleID`),
  KEY `sessionID` (`sessionID`),
  KEY `datum` (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_comparisons`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_details`
--

CREATE TABLE IF NOT EXISTS `s_order_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(40) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `articleordernumber` varchar(30) NOT NULL,
  `price` double NOT NULL DEFAULT '0',
  `quantity` int(11) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '0',
  `shipped` int(11) NOT NULL DEFAULT '0',
  `shippedgroup` int(11) NOT NULL DEFAULT '0',
  `releasedate` date NOT NULL DEFAULT '0000-00-00',
  `modus` int(11) NOT NULL,
  `esdarticle` int(1) NOT NULL,
  `taxID` int(11) NOT NULL,
  `config` text NOT NULL,
  `od_attr1` varchar(255) NOT NULL,
  `od_attr2` varchar(255) NOT NULL,
  `od_attr3` varchar(255) NOT NULL,
  `od_attr4` varchar(255) NOT NULL,
  `od_attr5` varchar(255) NOT NULL,
  `od_attr6` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `orderID` (`orderID`),
  KEY `articleID` (`articleID`),
  KEY `ordernumber` (`ordernumber`),
  KEY `articleordernumber` (`articleordernumber`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_details`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_documents`
--

CREATE TABLE IF NOT EXISTS `s_order_documents` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `type` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `orderID` int(11) unsigned NOT NULL,
  `amount` double NOT NULL,
  `docID` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `orderID` (`orderID`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_documents`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_esd`
--

CREATE TABLE IF NOT EXISTS `s_order_esd` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `serialID` int(255) NOT NULL DEFAULT '0',
  `esdID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL DEFAULT '0',
  `orderdetailsID` int(11) NOT NULL DEFAULT '0',
  `datum` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_esd`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_notes`
--

CREATE TABLE IF NOT EXISTS `s_order_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sUniqueID` varchar(70) NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `articlename` varchar(255) NOT NULL,
  `articleID` int(11) NOT NULL DEFAULT '0',
  `ordernumber` varchar(30) NOT NULL,
  `datum` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_notes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_order_number`
--

CREATE TABLE IF NOT EXISTS `s_order_number` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `number` int(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `desc` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=929 ;

--
-- Daten für Tabelle `s_order_number`
--

INSERT INTO `s_order_number` (`id`, `number`, `name`, `desc`) VALUES
(920, 20000, 'invoice', 'Bestellungen'),
(1, 20000, 'user', 'Kunden'),
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

CREATE TABLE IF NOT EXISTS `s_order_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `orderID` int(11) NOT NULL,
  `company` varchar(255) NOT NULL,
  `department` varchar(35) NOT NULL,
  `salutation` varchar(30) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `street` varchar(100) NOT NULL,
  `streetnumber` varchar(6) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `city` varchar(70) NOT NULL,
  `countryID` int(11) NOT NULL,
  `text1` varchar(255) NOT NULL,
  `text2` varchar(255) NOT NULL,
  `text3` varchar(255) NOT NULL,
  `text4` varchar(255) NOT NULL,
  `text5` varchar(255) NOT NULL,
  `text6` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_order_shippingaddress`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_coupons`
--

CREATE TABLE IF NOT EXISTS `s_plugin_coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `voucherID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID_2` (`voucherID`,`articleID`),
  KEY `voucherID` (`voucherID`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_plugin_coupons`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_coupons_codes`
--

CREATE TABLE IF NOT EXISTS `s_plugin_coupons_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `couponID` int(11) NOT NULL,
  `orderID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `codeID` int(11) NOT NULL,
  `stateID` int(11) NOT NULL,
  `pdf` varchar(255) NOT NULL,
  `pdfdate` datetime NOT NULL,
  `senddate` datetime NOT NULL,
  `cashdate` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_plugin_coupons_codes`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_plugin_recommendations`
--

CREATE TABLE IF NOT EXISTS `s_plugin_recommendations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `categoryID` int(11) NOT NULL,
  `banner_active` int(1) NOT NULL,
  `new_active` int(1) NOT NULL,
  `bought_active` int(1) NOT NULL,
  `supplier_active` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `categoryID_2` (`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_plugin_recommendations`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch`
--

CREATE TABLE IF NOT EXISTS `s_premium_dispatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `description` text NOT NULL,
  `comment` varchar(255) NOT NULL,
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
  `bind_sql` text,
  `status_link` text,
  `calculation_sql` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=10 ;

--
-- Daten für Tabelle `s_premium_dispatch`
--

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
(9, 'Normal Versand', 0, '', 'immer 3 &euro;', 1, 0, 1, 3, 0, 1000.00, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '<a href="http://nolp.dhl.de/nextt-online-public/set_identcodes.do?lang=de&zip=&idc={$offerPosition.trackingcode}" onclick="return !window.open(this.href, ''popup'', ''width=500,height=600,left=20,top=20'');" target="_blank">hier</a>', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_categories`
--

CREATE TABLE IF NOT EXISTS `s_premium_dispatch_categories` (
  `dispatchID` int(11) unsigned NOT NULL,
  `categoryID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`categoryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_premium_dispatch_categories`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_countries`
--

CREATE TABLE IF NOT EXISTS `s_premium_dispatch_countries` (
  `dispatchID` int(11) NOT NULL,
  `countryID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`countryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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

CREATE TABLE IF NOT EXISTS `s_premium_dispatch_holidays` (
  `dispatchID` int(11) unsigned NOT NULL,
  `holidayID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`dispatchID`,`holidayID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;

--
-- Daten für Tabelle `s_premium_dispatch_holidays`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_dispatch_paymentmeans`
--

CREATE TABLE IF NOT EXISTS `s_premium_dispatch_paymentmeans` (
  `dispatchID` int(11) NOT NULL,
  `paymentID` int(11) NOT NULL,
  PRIMARY KEY (`dispatchID`,`paymentID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_premium_dispatch_paymentmeans`
--

INSERT INTO `s_premium_dispatch_paymentmeans` (`dispatchID`, `paymentID`) VALUES
(9, 2),
(9, 3),
(9, 4),
(9, 5),
(9, 17),
(9, 18),
(9, 19),
(9, 20),
(9, 21),
(9, 28),
(9, 29),
(9, 30),
(9, 31);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_premium_holidays`
--

CREATE TABLE IF NOT EXISTS `s_premium_holidays` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `calculation` varchar(255) COLLATE latin1_german1_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci AUTO_INCREMENT=24 ;

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

CREATE TABLE IF NOT EXISTS `s_premium_shippingcosts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `from` decimal(10,3) unsigned NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `factor` decimal(10,2) NOT NULL,
  `dispatchID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from` (`from`,`dispatchID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=235 ;

--
-- Daten für Tabelle `s_premium_shippingcosts`
--

INSERT INTO `s_premium_shippingcosts` (`id`, `from`, `value`, `factor`, `dispatchID`) VALUES
(234, 0.000, 3.00, 0.00, 9);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_fields`
--

CREATE TABLE IF NOT EXISTS `s_search_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL,
  `relevance` int(11) NOT NULL,
  `field` varchar(255) CHARACTER SET latin1 COLLATE latin1_german1_ci NOT NULL,
  `tableID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`tableID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

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

CREATE TABLE IF NOT EXISTS `s_search_index` (
  `keywordID` int(11) NOT NULL,
  `fieldID` int(11) NOT NULL,
  `elementID` int(11) NOT NULL,
  PRIMARY KEY (`keywordID`,`fieldID`,`elementID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_search_index`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_keywords`
--

CREATE TABLE IF NOT EXISTS `s_search_keywords` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `keyword` varchar(255) NOT NULL,
  `soundex` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `keyword` (`keyword`),
  KEY `soundex` (`soundex`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_search_keywords`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_search_tables`
--

CREATE TABLE IF NOT EXISTS `s_search_tables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `table` varchar(255) NOT NULL,
  `referenz_table` varchar(255) DEFAULT NULL,
  `foreign_key` varchar(255) DEFAULT NULL,
  `where` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

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
-- Tabellenstruktur für Tabelle `s_shippingcosts`
--

CREATE TABLE IF NOT EXISTS `s_shippingcosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` double NOT NULL,
  `to` double NOT NULL DEFAULT '0',
  `shippingcosts` double NOT NULL DEFAULT '0',
  `factor` double NOT NULL,
  `area` varchar(30) NOT NULL DEFAULT '1',
  `countryID` int(11) NOT NULL,
  `typeID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `from` (`from`,`area`,`countryID`,`typeID`),
  KEY `artint` (`to`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=20 ;

--
-- Daten für Tabelle `s_shippingcosts`
--

INSERT INTO `s_shippingcosts` (`id`, `from`, `to`, `shippingcosts`, `factor`, `area`, `countryID`, `typeID`) VALUES
(19, 0, 0, 5, 0, 'deutschland', 0, 6),
(8, 0, 0, 0, 0, 'europa', 0, 6),
(9, 0, 0, 0, 0, 'welt', 0, 6);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_shippingcosts_areas`
--

CREATE TABLE IF NOT EXISTS `s_shippingcosts_areas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active` int(1) NOT NULL,
  `position` int(11) NOT NULL,
  `shippingfree` double NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Daten für Tabelle `s_shippingcosts_areas`
--

INSERT INTO `s_shippingcosts_areas` (`id`, `name`, `description`, `active`, `position`, `shippingfree`) VALUES
(1, 'deutschland', 'Deutschland', 1, 1, 1000),
(2, 'europa', 'Europa', 1, 2, 1000),
(3, 'welt', 'Welt', 1, 3, 1000);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_shippingcosts_dispatch`
--

CREATE TABLE IF NOT EXISTS `s_shippingcosts_dispatch` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `active` int(1) NOT NULL,
  `position` int(11) NOT NULL,
  `shippingfree` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Daten für Tabelle `s_shippingcosts_dispatch`
--

INSERT INTO `s_shippingcosts_dispatch` (`id`, `name`, `description`, `active`, `position`, `shippingfree`) VALUES
(6, 'Versandart 1', '', 1, 1, 1);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_shippingcosts_dispatch_countries`
--

CREATE TABLE IF NOT EXISTS `s_shippingcosts_dispatch_countries` (
  `typeID` int(11) NOT NULL,
  `countryID` int(11) NOT NULL,
  PRIMARY KEY (`typeID`,`countryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Daten für Tabelle `s_shippingcosts_dispatch_countries`
--

INSERT INTO `s_shippingcosts_dispatch_countries` (`typeID`, `countryID`) VALUES
(6, 2),
(6, 23);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_currentusers`
--

CREATE TABLE IF NOT EXISTS `s_statistics_currentusers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) NOT NULL,
  `page` varchar(70) NOT NULL,
  `time` datetime DEFAULT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_statistics_currentusers`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_pool`
--

CREATE TABLE IF NOT EXISTS `s_statistics_pool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `remoteaddr` varchar(255) NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_statistics_pool`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_referer`
--

CREATE TABLE IF NOT EXISTS `s_statistics_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `referer` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_statistics_referer`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_search`
--

CREATE TABLE IF NOT EXISTS `s_statistics_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_statistics_search`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_statistics_visitors`
--

CREATE TABLE IF NOT EXISTS `s_statistics_visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `pageimpressions` int(11) NOT NULL DEFAULT '0',
  `uniquevisits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_statistics_visitors`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_ticket_support`
--

CREATE TABLE IF NOT EXISTS `s_ticket_support` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `uniqueID` varchar(32) NOT NULL,
  `userID` int(10) NOT NULL,
  `employeeID` int(5) NOT NULL,
  `ticket_typeID` int(10) NOT NULL,
  `statusID` int(5) NOT NULL DEFAULT '1',
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `receipt` datetime NOT NULL,
  `last_contact` datetime NOT NULL,
  `additional` text NOT NULL,
  `isocode` varchar(3) NOT NULL DEFAULT 'de',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_ticket_support`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_ticket_support_history`
--

CREATE TABLE IF NOT EXISTS `s_ticket_support_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `ticketID` int(10) NOT NULL,
  `swUser` varchar(100) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `receipt` datetime NOT NULL,
  `support_type` enum('manage','direct') NOT NULL,
  `receiver` varchar(200) NOT NULL,
  `direction` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_ticket_support_history`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_ticket_support_mails`
--

CREATE TABLE IF NOT EXISTS `s_ticket_support_mails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `frommail` varchar(255) NOT NULL,
  `fromname` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `contentHTML` text NOT NULL,
  `ishtml` int(11) NOT NULL,
  `attachment` varchar(255) NOT NULL,
  `sys_dependent` tinyint(1) NOT NULL DEFAULT '0',
  `isocode` varchar(3) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=68 ;

--
-- Daten für Tabelle `s_ticket_support_mails`
--

INSERT INTO `s_ticket_support_mails` (`id`, `name`, `description`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `sys_dependent`, `isocode`) VALUES
(1, 'sSTRAIGHTANSWER', 'Kundenbenachrichtigung - registriert', 'info@example.com', 'Shopware Demo Auto', 'Antwort zu Ihrem Ticket {sTicketID}', 'Hallo,\r\n  \r\nin Ihrem "Mein-Konto"-Bereich liegt eine Anwort auf Ihr Ticket {sTicketID} in der Supportverwaltung bereit. \r\n\r\nMit freundlichen Grüßen,\r\n \r\nIhr Team von Shopware2.de', '', 0, '', 1, 'de'),
(3, 'sSTANDARD', 'Standardvorlage', 'info@example.com', 'Max Mustermann', 'Antwort zu Ihrem Ticket {sTicketID}', 'Standardvorlage \r\nAntwort auf das Ticket {sTicketID}', '\r\n', 0, '', 1, 'de'),
(2, 'sSTRAIGHTANSWER_UNREG', 'Kundenbenachrichtigung - unregistriert', 'info@example.com', 'Shopware Demo Auto', 'Antwort zu Ihrem Ticket {sTicketID}', '', 'Hallo,\r\n<br>  \r\nunter folgendem Link liegt eine Anwort auf Ihr Ticket {sTicketID} bereit. Hier haben Sie auch die Möglichkeit, auf diese zu reagieren.\r\n<br>\r\n<a href="{sTicketDirectUrl}">Ticketantwort einsehen</a>\r\n<br>\r\n<br>\r\nMit freundlichen Grüßen,<br>\r\n<br>\r\nIhr Team von Shopware2.de', 1, '', 1, 'de'),
(62, '', 'RMA Defekt - abgelehnt', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung wegen Defekt wurde abgelehnt', ' Rücksendung wegen Defekt \r\n=========================\r\n\r\nHallo,\r\n\r\nleider müssen wir die Reklamation ablehnen, aus folgendem Grund: \r\n\r\n\r\n  \r\nViele Grüße\r\nShopware2.de', '', 0, '', 0, 'de'),
(63, '', 'RMA Rückgaberecht - akzeptiert', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung gemäß Rückgaberecht wurde akzeptiert', 'R Ü C K S E N D U N G   W E G E N  N I C H T G E F A L L E N S\r\n(gemäß 14 tägiges Widerrufsrecht nach Erhalt der Ware)\r\n==================================================\r\n\r\nHallo,\r\n\r\nbitte senden Sie den von Ihnen beschriebenen Artikel per Post, mit einer Rechnungskopie und unter Angabe des Grundes ausreichend frankiert zurück.\r\n\r\nBitte haben Sie Verständnis dafür das unfreie Sendungen nicht angenommen werden können.\r\n\r\nBitte senden Sie das Produkt innerhalb der nächsten 7 Tage originalverpackt inkl. allem Zubehör an uns zurück\r\nsowie ohne Gebrauchsspuren. Sollte sich die zurückgesendete Ware in einem Zustand befinden, in dem sie nicht\r\nals Neuware verkauft werden kann, müssen Sie mit einem Abzug für diese Wertminderung bei der Gutschrift rechnen.\r\n\r\n\r\nIhre Rücksendenummer lautet RMA{sTicketID} (Bitte deutlich auf das Paket schreiben)\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, '', 0, 'de'),
(61, '', 'RMA Defekt - akzeptiert', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung wegen Defekt wurde akzeptiert', ' Rücksendung wegen Defekt \r\n=========================\r\n\r\nHallo,\r\nbitte senden Sie den von Ihnen beschriebenen Artikel \r\nper Post ausreichend frankiert zurück.\r\n\r\nUNFREIE SENDUNGEN KÖNNEN \r\nLEIDER NICHT ANGENOMMEN WERDEN.\r\n\r\nWir werden Ihnen bei berechtigter Reklamation die \r\nRücksendekosten gutschreiben.\r\n\r\nNach Überprüfung des Defektes durch \r\nunsere Techniker wird entschieden,  ob eine kurzfristige \r\nReparatur möglich ist, oder ob Sie einen Austausch\r\nfür den defekten Artikel erhalten. Danach wird der \r\nArtikel von uns zum Hersteller zur Reparatur bzw. zum \r\nAustausch geschickt. Im Anschluß an eine Reparatur \r\nwird der Artikel nochmals von uns getestet und Ihnen danach \r\nsofort zugestellt. Dauert eine Reparatur wider Erwarten zu lange, \r\nwird mit Ihnen abgestimmt, ob doch ein Austausch von \r\nuns vorgenommen wird.\r\n\r\nWir bitten Sie um Verständnis, dass wir nur bei tat-\r\nsächlich vorhandenen Mängeln einen Austausch \r\noder eine für Sie kostenlose Reparatur vornehmen können. \r\nSollte hingegen kein Mangel vorliegen, so behalten wir uns vor, \r\nunseren Überprüfungsaufwand und die Rück-\r\nsendekosten an Sie in Rechnung zu stellen.\r\n\r\nIhre Rücksendenummer lautet RMA{sTicketID} \r\n(Bitte deutlich auf das Paket schreiben) \r\n\r\nWenn Sie bereits eine ausführliche Fehlerbeschreibung in das\r\nOnline-Service-Formular geschrieben haben, müssen Sie der\r\nRücksendung keine Fehlerbeschreibung mehr beilegen.\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, '', 0, 'de'),
(64, '', 'RMA Rückgaberecht - abgelehnt', 'info@example.com', 'Shopware Demo', 'Ihre Rücksendung gemäß Rückgaberecht wurde abgelehnt', 'Rücksendung wegen Widerrufsrecht\r\n======================================\r\n\r\nHallo,\r\n\r\nleider müssen wir Ihren Widerruf ablehnen, aus folgendem Grund: \r\n\r\n\r\n\r\nViele Grüße\r\nShopware2.de', '', 0, '', 0, 'de'),
(65, 'sTICKETNOTIFYMAILNEW', 'Benachrichtigung - Neues Ticket', 'info@example.com', 'Mein Absendername', 'Es liegt ein neues Ticket vor', 'Es liegt ein neues Ticket für Sie bereit. Die TicketID lautet: {sTicketID}', '', 0, '', 1, 'de'),
(66, 'sTICKETNOTIFYMAILANS', 'Benachrichtigung - Beantwortetes Ticket', 'info@example.com', 'Mein Absendername', 'Es liegt eine Ticketantwort vor', 'Es liegt eine Antwort für das Ticket {sTicketID} für Sie bereit.', '', 0, '', 1, 'de'),
(67, 'sTICKETNOTIFYMAILCOSTUMER', 'Bestätigung - Kunde', 'info@example.com', 'Mein Absendername', 'Ihre Anfrage {sTicketID}', 'Vielen Dank für Ihre Anfrage mit der Nummer {sTicketID}. Wir werden uns in Kürze mit Ihnen in Verbindung setzen.', '', 0, '', 1, 'de');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_ticket_support_status`
--

CREATE TABLE IF NOT EXISTS `s_ticket_support_status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `description` varchar(50) NOT NULL,
  `responsible` tinyint(4) NOT NULL,
  `closed` tinyint(4) NOT NULL,
  `color` varchar(7) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Daten für Tabelle `s_ticket_support_status`
--

INSERT INTO `s_ticket_support_status` (`id`, `description`, `responsible`, `closed`, `color`) VALUES
(1, 'offen', 0, 0, '#ae0000'),
(2, 'in Bearbeitung', 0, 0, '0'),
(3, 'bearbeitet', 1, 0, '#0e9600'),
(4, 'Abgeschlossen', 0, 1, '0');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_ticket_support_types`
--

CREATE TABLE IF NOT EXISTS `s_ticket_support_types` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `gridcolor` varchar(7) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Daten für Tabelle `s_ticket_support_types`
--

INSERT INTO `s_ticket_support_types` (`id`, `name`, `gridcolor`) VALUES
(1, 'Support-Ticket', '#aeadae'),
(2, 'RMA', '#ffcc00');

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user`
--

CREATE TABLE IF NOT EXISTS `s_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `password` varchar(100) NOT NULL,
  `email` varchar(70) NOT NULL,
  `active` int(1) NOT NULL DEFAULT '0',
  `accountmode` int(11) NOT NULL,
  `confirmationkey` varchar(100) NOT NULL,
  `paymentID` int(11) NOT NULL DEFAULT '0',
  `firstlogin` date NOT NULL DEFAULT '0000-00-00',
  `lastlogin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `sessionID` varchar(255) NOT NULL,
  `newsletter` int(1) NOT NULL DEFAULT '0',
  `validation` varchar(255) NOT NULL DEFAULT '0',
  `affiliate` int(10) NOT NULL DEFAULT '0',
  `customergroup` varchar(15) NOT NULL,
  `paymentpreset` int(11) NOT NULL,
  `language` varchar(10) NOT NULL,
  `subshopID` int(11) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `pricegroupID` int(11) unsigned DEFAULT NULL,
  `internalcomment` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `sessionID` (`sessionID`),
  KEY `firstlogin` (`firstlogin`),
  KEY `lastlogin` (`lastlogin`),
  KEY `pricegroupID` (`pricegroupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_user`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_billingaddress`
--

CREATE TABLE IF NOT EXISTS `s_user_billingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) NOT NULL,
  `department` varchar(35) NOT NULL,
  `salutation` varchar(30) NOT NULL,
  `customernumber` varchar(30) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `street` varchar(100) NOT NULL,
  `streetnumber` varchar(6) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `city` varchar(70) NOT NULL,
  `phone` varchar(40) NOT NULL,
  `fax` varchar(40) NOT NULL,
  `countryID` int(11) NOT NULL DEFAULT '0',
  `ustid` varchar(50) NOT NULL,
  `text1` varchar(255) NOT NULL,
  `text2` varchar(255) NOT NULL,
  `text3` varchar(255) NOT NULL,
  `text4` varchar(255) NOT NULL,
  `text5` varchar(255) NOT NULL,
  `text6` varchar(255) NOT NULL,
  `birthday` date NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_user_billingaddress`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_creditcard`
--

CREATE TABLE IF NOT EXISTS `s_user_creditcard` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `holder` varchar(255) NOT NULL,
  `kind` varchar(255) NOT NULL,
  `number` varchar(255) NOT NULL,
  `checkvalue` varchar(5) NOT NULL,
  `expirationdate` date NOT NULL DEFAULT '0000-00-00',
  PRIMARY KEY (`id`),
  KEY `userid` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_user_creditcard`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_debit`
--

CREATE TABLE IF NOT EXISTS `s_user_debit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `account` varchar(30) NOT NULL,
  `bankcode` varchar(30) NOT NULL,
  `bankname` varchar(255) NOT NULL,
  `bankholder` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_user_debit`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_service`
--

CREATE TABLE IF NOT EXISTS `s_user_service` (
  `id` int(9) NOT NULL AUTO_INCREMENT,
  `clientnumber` varchar(255) NOT NULL DEFAULT '0',
  `email` varchar(50) NOT NULL,
  `billingnumber` varchar(255) NOT NULL DEFAULT '0',
  `articles` text NOT NULL,
  `description` text NOT NULL,
  `description2` text NOT NULL,
  `description3` text NOT NULL,
  `description4` text NOT NULL,
  `number` varchar(255) NOT NULL DEFAULT '0',
  `done` int(1) NOT NULL DEFAULT '0',
  `supplier` int(10) NOT NULL DEFAULT '0',
  `accepted` int(1) NOT NULL DEFAULT '0',
  `date` date NOT NULL DEFAULT '0000-00-00',
  `released` date NOT NULL DEFAULT '0000-00-00',
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Daten für Tabelle `s_user_service`
--


-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `s_user_shippingaddress`
--

CREATE TABLE IF NOT EXISTS `s_user_shippingaddress` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL DEFAULT '0',
  `company` varchar(255) NOT NULL,
  `department` varchar(35) NOT NULL,
  `salutation` varchar(30) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `street` varchar(100) NOT NULL,
  `streetnumber` varchar(6) NOT NULL,
  `zipcode` varchar(10) NOT NULL,
  `city` varchar(70) NOT NULL,
  `countryID` int(11) NOT NULL,
  `text1` varchar(255) NOT NULL,
  `text2` varchar(255) NOT NULL,
  `text3` varchar(255) NOT NULL,
  `text4` varchar(255) NOT NULL,
  `text5` varchar(255) NOT NULL,
  `text6` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



UPDATE `s_core_config` SET `value` = '5' WHERE `name` = 'sCHARTRANGE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '100' WHERE `name` = 'sMAXPURCHASE' LIMIT 1 ;
UPDATE `s_core_config` SET `value` = '0' WHERE `name` = 'sLASTARTICLESTHUMB' LIMIT 1 ;

UPDATE `s_core_config` SET `value` = '' WHERE `name` IN ('sHOST', 'sBASEPATH');
UPDATE `s_core_auth` SET `window_size` = '', `lastlogin` = '2000-01-01 00:00:00';

TRUNCATE TABLE `s_core_licences`;
TRUNCATE TABLE `s_core_log`;
TRUNCATE TABLE `s_core_sessions`;
TRUNCATE TABLE `s_order_basket`;
TRUNCATE TABLE `s_statistics_pool`;
TRUNCATE TABLE `s_statistics_visitors`;
TRUNCATE TABLE `s_statistics_search`;
TRUNCATE TABLE `s_statistics_currentusers`;
TRUNCATE TABLE `s_statistics_referer`;
TRUNCATE TABLE `s_emarketing_lastarticles`;

UPDATE `s_export`
SET `last_export` = '2000-01-01 00:00:00',
	`active` = '0',
	`hash` = MD5(RAND()),
	`count_articles` = '0',
	`expiry` = '2000-01-01 00:00:00',
	`categoryID` = NULL ,
	`partnerID` = '',
	`active_filter` = '0',
	`count_filter` = '0';

UPDATE `s_core_plugins`
SET `installation_date` = '2010-10-18 00:00:00', `update_date` = '2010-10-18 00:00:00'
WHERE `installation_date` IS NOT NULL;

UPDATE `s_core_snippets` SET `shopID` = '1';

# 351 Changes
DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sSERVICE%';
DELETE FROM `s_core_config_mails` WHERE `name` LIKE 'sCHEAPER';

INSERT IGNORE INTO `s_cms_support` (`id`, `name`, `text`, `email`, `email_template`, `email_subject`, `text2`, `ticket_typeID`, `isocode`) VALUES
(10, 'R�ckgabe', '<h2>Hier k&ouml;nnen Sie Informationen zur R&uuml;ckgabe einstellen...</h2>', 'info@example.de', 'R�ckgabe - Shopware Demoshop\r\n \r\nKundennummer: {sVars.kdnr}\r\neMail: {sVars.email}\r\n \r\nRechnungsnummer: {sVars.rechnung}\r\nArtikelnummer: {sVars.artikel}\r\n \r\nKommentar:\r\n \r\n{sVars.info}', 'R�ckgabe', '<p>Formular erfolgreich versandt.</p>', 0, 'de');

INSERT IGNORE INTO `s_cms_support_fields` (`id`, `error_msg`, `name`, `note`, `typ`, `required`, `supportID`, `label`, `class`, `value`, `vtyp`, `added`, `position`, `ticket_task`) VALUES
(60, '', 'kdnr', '', 'text', 1, 10, 'KdNr.(siehe Rechnung)', 'normal', '', '', '2007-11-06 17:31:38', 1, ''),
(61, '', 'email', '', 'text', 1, 10, 'eMail-Adresse', 'normal', '', '', '2007-11-06 17:31:51', 2, ''),
(62, '', 'rechnung', '', 'text', 1, 10, 'Rechnungsnummer', 'normal', '', '', '2007-11-06 17:32:02', 3, ''),
(63, '', 'artikel', '', 'textarea', 1, 10, 'Artikelnummer(n)', 'normal', '', '', '2007-11-06 17:32:17', 4, ''),
(64, '', 'info', '', 'textarea', 0, 10, 'Kommentar', 'normal', '', '', '2007-11-06 17:32:42', 5, '');

UPDATE `s_core_snippets` SET `value` = '{link file=''frontend/_resources/favicon.ico''}' WHERE `value` = '{link file=''resources/favicon.ico''}';
DELETE FROM `s_core_snippets` WHERE `namespace` LIKE 'templates/_default/%';
DELETE FROM `s_core_config_groups` WHERE `name` = 'Debugging';

# Crontab Changes
UPDATE `s_crontab` SET `interval` = 86400 WHERE `interval` IN (1, 10, 100);

# Snippet Changes
UPDATE `s_core_snippets` SET `value` = 'Ich habe die <a href="{url controller=custom sCustom=4 forceSecure}" title="AGB"><span style="text-decoration:underline;">AGB</span></a> Ihres Shops gelesen und bin mit deren Geltung einverstanden.' WHERE `name` = 'ConfirmTerms';
UPDATE `s_core_snippets` SET `value` = 'Nachdem Sie die erste Bestellung durchgef�hrt haben, k�nnen Sie hier auf vorherige Rechnungsadressen zugreifen.' WHERE `name` = 'SelectBillingInfoEmpty';

INSERT IGNORE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/box_article', 1, 1, 'Star', '*', '2010-12-08 02:51:26', '2010-12-08 02:51:26'),
('frontend/listing/box_article', 1, 1, 'reducedPrice', 'Statt: ', '2010-12-08 02:52:32', '2010-12-08 02:52:32');

UPDATE `s_core_snippets` SET `value` = 'Pr�fen und Bestellen' WHERE `value` = 'Bestellung abschlie¤en';

# Seo Changes
UPDATE `s_core_config` SET `value` = CONCAT(`value`, ',search,account,checkout,register') WHERE `name` = 'sSEOVIEWPORTBLACKLIST' AND `value` NOT LIKE '%checkout%';

# PayPal Changes
UPDATE `s_core_config` SET `multilanguage` = '1' WHERE `name` IN ('sXPRESS', 'sPaypalLogo');

# Max. Suppliers Config
SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Kategorien / Listen');
INSERT IGNORE INTO `s_core_config` (`group`,`name`,`value`,`description`)
VALUES (@parent, 'sMAXSUPPLIERSCATEGORY', '30', 'Max. Anzahl Hersteller in Sidebar');

# Plugin Changes
CREATE TABLE IF NOT EXISTS `s_core_plugin_configs_copy` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `value` text NOT NULL,
  `pluginID` int(11) unsigned NOT NULL,
  `localeID` int(11) unsigned NOT NULL,
  `shopID` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`pluginID`,`localeID`,`shopID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `s_core_plugin_configs_copy` (`name`, `value`, `pluginID`, `localeID`, `shopID`)
SELECT `name`, `value`, `pluginID`, `localeID`, `shopID`
FROM `s_core_plugin_configs`
ORDER BY `pluginID`, `shopID`, `name`, `id` DESC;

DROP TABLE IF EXISTS `s_core_plugin_configs`;
RENAME TABLE `s_core_plugin_configs_copy` TO `s_core_plugin_configs`;

# Filter Changes
CREATE TABLE IF NOT EXISTS `s_filter_values_copy` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `optionID` (`optionID`,`articleID`,`value`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `s_filter_values_copy` (`id`, `groupID`, `optionID`, `articleID`, `value`)
SELECT `id`, `groupID`, `optionID`, `articleID`, `value`
FROM `s_filter_values`;

DROP TABLE IF EXISTS `s_filter_values`;
RENAME TABLE `s_filter_values_copy` TO `s_filter_values`;

# Table Changes

ALTER TABLE `s_crontab` ADD `pluginID` INT( 11 ) UNSIGNED NULL;

UPDATE `s_cms_static` SET `link` = REPLACE(`link`,'&sFid,','&sFid=');
DELETE FROM `s_core_rewrite_urls` WHERE `path` = '';