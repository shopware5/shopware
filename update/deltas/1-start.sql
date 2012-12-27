ALTER TABLE `s_core_states` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_core_subscribes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subscribe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) unsigned NOT NULL,
  `listener` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `pluginID` int(11) unsigned DEFAULT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subscribe` (`subscribe`,`type`,`listener`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('1', 'Enlight_Bootstrap_InitResource_Auth', '0', 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAuth', '36', '0'),
('2', 'Enlight_Controller_Action_PreDispatch', '0', 'Shopware_Plugins_Backend_Auth_Bootstrap::onPreDispatchBackend', '36', '0'),
('3', 'Enlight_Bootstrap_InitResource_Menu', '0', 'Shopware_Plugins_Backend_Menu_Bootstrap::onInitResourceMenu', '37', '0'),
('4', 'Enlight_Bootstrap_InitResource_Api', '0', 'Shopware_Plugins_Core_Api_Bootstrap::onInitResourceApi', '17', '0'),
('5', 'Enlight_Controller_Action_PostDispatch', '0', 'Shopware_Plugins_Core_ControllerBase_Bootstrap::onPostDispatch', '15', '100'),
('6', 'Enlight_Controller_Front_StartDispatch', '0', 'Shopware_Plugins_Core_ErrorHandler_Bootstrap::onStartDispatch', '2', '0'),
('7', 'Enlight_Bootstrap_InitResource_Log', '0', 'Shopware_Plugins_Core_Log_Bootstrap::onInitResourceLog', '1', '0'),
('8', 'Enlight_Controller_Front_RouteStartup', '0', 'Shopware_Plugins_Core_Log_Bootstrap::onRouteStartup', '1', '0'),
('9', 'Enlight_Plugins_ViewRenderer_FilterRender', '0', 'Shopware_Plugins_Core_PostFilter_Bootstrap::onFilterRender', '13', '0'),
('10', 'Enlight_Controller_Front_RouteStartup', '0', 'Shopware_Plugins_Core_Router_Bootstrap::onRouteStartup', '8', '0'),
('11', 'Enlight_Controller_Front_RouteShutdown', '0', 'Shopware_Plugins_Core_Router_Bootstrap::onRouteShutdown', '8', '0'),
('12', 'Enlight_Controller_Router_FilterAssembleParams', '0', 'Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble', '8', '0'),
('13', 'Enlight_Controller_Router_FilterUrl', '0', 'Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl', '8', '0'),
('14', 'Enlight_Controller_Router_Assemble', '0', 'Shopware_Plugins_Core_Router_Bootstrap::onAssemble', '8', '100'),
('15', 'Enlight_Controller_Front_PreDispatch', '0', 'Shopware_Plugins_Core_Shop_Bootstrap::onPreDispatch', '12', '0'),
('16', 'Enlight_Bootstrap_InitResource_Shop', '0', 'Shopware_Plugins_Core_Shop_Bootstrap::onInitResourceShop', '12', '0'),
('17', 'Enlight_Bootstrap_InitResource_System', '0', 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceSystem', '10', '0'),
('18', 'Enlight_Bootstrap_InitResource_Modules', '0', 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceModules', '10', '0'),
('19', 'Enlight_Bootstrap_InitResource_Adodb', '0', 'Shopware_Plugins_Core_System_Bootstrap::onInitResourceAdodb', '10', '0'),
('21', 'Enlight_Controller_Front_PreDispatch', '0', 'Shopware_Plugins_Core_ViewportForward_Bootstrap::onPreDispatch', '11', '10'),
('23', 'Enlight_Controller_Action_PostDispatch', '0', 'Shopware_Plugins_Frontend_Compare_Bootstrap::onPostDispatch', '20', '0'),
('24', 'Enlight_Controller_Front_DispatchLoopShutdown', '0', 'Shopware_Plugins_Frontend_Statistics_Bootstrap::onDispatchLoopShutdown', '31', '0'),
('25', 'Enlight_Plugins_ViewRenderer_FilterRender', '0', 'Shopware_Plugins_Frontend_Seo_Bootstrap::onFilterRender', '22', '0'),
('26', 'Enlight_Controller_Action_PostDispatch', '0', 'Shopware_Plugins_Frontend_Seo_Bootstrap::onPostDispatch', '22', '0'),
('27', 'Enlight_Controller_Action_PostDispatch', '0', 'Shopware_Plugins_Frontend_TagCloud_Bootstrap::onPostDispatch', '34', '0'),
('30', 'Enlight_Controller_Front_StartDispatch', '0', 'Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onStartDispatch', '19', '0'),
('31', 'Enlight_Controller_Router_Route', '0', 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onRoute', '24', '10'),
('32', 'Enlight_Controller_Router_Assemble', '0', 'Shopware_Plugins_Frontend_RouterOld_Bootstrap::onAssemble', '24', '10'),
('37', 'Enlight_Controller_Action_PostDispatch', '0', 'Shopware_Plugins_Frontend_LastArticles_Bootstrap::onPostDispatch', '23', '0'),
('38', 'Enlight_Controller_Front_RouteShutdown', '0', 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onRouteShutdown', '35', '0'),
('40', 'Enlight_Bootstrap_InitResource_Payments', '0', 'Shopware_Plugins_Frontend_Payment_Bootstrap::onInitResourcePayments', '39', '0'),
('41', 'Enlight_Controller_Dispatcher_ControllerPath_Backend_Check', '0', 'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend', '40', '0'),
('52', 'Enlight_Bootstrap_InitResource_BackendSession', '0', 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession', '36', '0'),
('53', 'Enlight_Bootstrap_InitResource_Acl', '0', 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', '36', '0'),
('55', 'Enlight_Controller_Action_PreDispatch', '0', 'Shopware_Plugins_Backend_Locale_Bootstrap::onPreDispatchBackend', '43', '0'),
('56', 'Enlight_Controller_Front_DispatchLoopStartup', '0', 'Shopware_Plugins_Core_RestApi_Bootstrap::onDispatchLoopStartup', '44', '0'),
('57', 'Enlight_Controller_Front_PreDispatch', '0', 'Shopware_Plugins_Core_RestApi_Bootstrap::onFrontPreDispatch', '44', '0'),
('58', 'Enlight_Bootstrap_InitResource_Auth', '0', 'Shopware_Plugins_Core_RestApi_Bootstrap::onInitResourceAuth', '44', '0'),
('59', 'Enlight_Controller_Front_DispatchLoopShutdown', '0', 'Shopware_Plugins_Core_Log_Bootstrap::onDispatchLoopShutdown', '1', '500'),
('60', 'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager', '0', 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetPluginController', '46', '0'),
('61', 'Enlight_Controller_Dispatcher_ControllerPath_Backend_Store', '0', 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetStoreController', '46', '0'),
('62', 'Enlight_Bootstrap_InitResource_StoreApi', '0', 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onInitResourceStoreApi', '45', '0'),
('63', 'Enlight_Controller_Action_PreDispatch', '0', 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onPreDispatch', '45', '0');
DROP TABLE IF EXISTS `backup_s_core_subscribes`;
RENAME TABLE `s_core_subscribes` TO `backup_s_core_subscribes`;
RENAME TABLE `new_s_core_subscribes` TO `s_core_subscribes`;

CREATE TABLE IF NOT EXISTS `new_s_core_tax` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax` decimal(10,2) NOT NULL,
  `description` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_core_tax` (`id`, `tax`, `description`)
SELECT `id`, `tax`, `description` FROM `s_core_tax`;
DROP TABLE IF EXISTS `s_core_tax`;
RENAME TABLE `new_s_core_tax` TO `s_core_tax`;

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
  KEY `areaID` (`areaID`)
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `basename` (`template`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_core_templates` (`id`, `template`, `name`, `description`, `author`, `license`, `esi`, `style_support`, `emotion`, `version`, `plugin_id`) VALUES
('4', 'orange', 'Orange', NULL, 'shopware AG', 'AGPL', '0', '0', '0', '1', NULL),
('11', 'emotion_orange', 'Emotion Orange', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('14', 'emotion_turquoise', 'Emotion Turquoise', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('15', 'emotion_brown', 'Emotion Brown', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('16', 'emotion_gray', 'Emotion Gray', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('17', 'emotion_red', 'Emotion Red', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('18', 'emotion_blue', 'Emotion Blue', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('19', 'emotion_green', 'Emotion Green', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('20', 'emotion_black', 'Emotion Black', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL),
('21', 'emotion_pink', 'Emotion Pink', NULL, 'shopware AG', 'AGPL', '1', '0', '1', '2', NULL);

ALTER TABLE `s_core_translations` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_core_units` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_core_widget_views`;
CREATE TABLE `s_core_widget_views` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `widget_id` int(11) unsigned NOT NULL,
  `auth_id` int(11) unsigned NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `column` int(11) unsigned NOT NULL,
  `position` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `widget_id` (`widget_id`,`auth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `s_core_widgets`;
CREATE TABLE `s_core_widgets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_core_widgets` (`id`, `name`, `label`) VALUES
('1', 'swag-sales-widget', 'Umsatz Heute und Gestern'),
('2', 'swag-upload-widget', 'Drag and Drop Upload'),
('3', 'swag-visitors-customers-widget', 'Besucher online'),
('4', 'swag-last-orders-widget', 'Letzte Bestellungen'),
('5', 'swag-notice-widget', 'Notizzettel'),
('6', 'swag-merchant-widget', 'Händlerfreischaltung');

CREATE TABLE IF NOT EXISTS `new_s_crontab` (
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_crontab` (`id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`, `pluginID`)
SELECT `id`, `name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`, `pluginID` FROM `s_crontab`;
DROP TABLE IF EXISTS `s_crontab`;
RENAME TABLE `new_s_crontab` TO `s_crontab`;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_banners` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_banners` (`id`, `description`, `valid_from`, `valid_to`, `img`, `link`, `link_target`, `categoryID`, `extension`, `liveshoppingID`)
SELECT `id`, `description`, `valid_from`, `valid_to`, `img`, `link`, `link_target`, `categoryID`, `extension`, `liveshoppingID` FROM `s_emarketing_banners`;
DROP TABLE IF EXISTS `s_emarketing_banners`;
RENAME TABLE `new_s_emarketing_banners` TO `s_emarketing_banners`;

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

ALTER TABLE `s_emarketing_lastarticles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_partner` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_partner` (`id`, `idcode`, `datum`, `company`, `contact`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `country`, `email`, `web`, `profil`, `fix`, `percent`, `cookielifetime`, `active`)
SELECT `id`, `idcode`, `datum`, `company`, `contact`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `country`, `email`, `web`, `profil`, `fix`, `percent`, `cookielifetime`, `active` FROM `s_emarketing_partner`;
DROP TABLE IF EXISTS `s_emarketing_partner`;
RENAME TABLE `new_s_emarketing_partner` TO `s_emarketing_partner`;

ALTER TABLE `s_emarketing_promotion_articles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_emarketing_promotion_banner` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_emarketing_promotion_containers` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_promotion_html` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL DEFAULT '0',
  `headline` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_promotion_html` (`id`, `parentID`, `headline`, `html`)
SELECT `id`, `parentID`, `headline`, `html` FROM `s_emarketing_promotion_html`;
DROP TABLE IF EXISTS `s_emarketing_promotion_html`;
RENAME TABLE `new_s_emarketing_promotion_html` TO `s_emarketing_promotion_html`;

ALTER TABLE `s_emarketing_promotion_links` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_emarketing_promotion_main` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_emarketing_promotion_positions` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_emarketing_promotions` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `date` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_referer` (`id`, `userID`, `referer`, `date`)
SELECT `id`, `userID`, `referer`, `date` FROM `s_emarketing_referer`;
DROP TABLE IF EXISTS `s_emarketing_referer`;
RENAME TABLE `new_s_emarketing_referer` TO `s_emarketing_referer`;

ALTER TABLE `s_emarketing_tellafriend` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_voucher_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) DEFAULT NULL,
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cashed` int(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_voucher_codes` (`id`, `voucherID`, `userID`, `code`, `cashed`)
SELECT `id`, `voucherID`, `userID`, `code`, `cashed` FROM `s_emarketing_voucher_codes`;
DROP TABLE IF EXISTS `s_emarketing_voucher_codes`;
RENAME TABLE `new_s_emarketing_voucher_codes` TO `s_emarketing_voucher_codes`;

CREATE TABLE IF NOT EXISTS `new_s_emarketing_vouchers` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_emarketing_vouchers` (`id`, `description`, `vouchercode`, `numberofunits`, `value`, `minimumcharge`, `shippingfree`, `bindtosupplier`, `valid_from`, `valid_to`, `ordercode`, `modus`, `percental`, `numorder`, `customergroup`, `restrictarticles`, `strict`, `subshopID`, `taxconfig`)
SELECT `id`, `description`, `vouchercode`, `numberofunits`, `value`, `minimumcharge`, `shippingfree`, `bindtosupplier`, `valid_from`, `valid_to`, `ordercode`, `modus`, `percental`, `numorder`, `customergroup`, `restrictarticles`, `strict`, `subshopID`, `taxconfig` FROM `s_emarketing_vouchers`;
DROP TABLE IF EXISTS `s_emarketing_vouchers`;
RENAME TABLE `new_s_emarketing_vouchers` TO `s_emarketing_vouchers`;

DROP TABLE IF EXISTS `s_emarketing_vouchers_attributes`;
CREATE TABLE `s_emarketing_vouchers_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucherID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `voucherID` (`voucherID`),
  CONSTRAINT `s_emarketing_vouchers_attributes_ibfk_1` FOREIGN KEY (`voucherID`) REFERENCES `s_emarketing_vouchers` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_emarketing_vouchers_cashed` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_emotion`;
CREATE TABLE `s_emotion` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `s_emotion_attributes`;
CREATE TABLE `s_emotion_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emotionID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `emotionID` (`emotionID`),
  CONSTRAINT `s_emotion_attributes_ibfk_1` FOREIGN KEY (`emotionID`) REFERENCES `s_emotion_backup` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
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
  PRIMARY KEY (`id`)
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

DROP TABLE IF EXISTS `s_emotion_grid`;
CREATE TABLE `s_emotion_grid` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `cols` int(11) NOT NULL,
  `rows` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_emotion_grid` (`id`, `name`, `cols`, `rows`, `width`, `height`) VALUES
('1', 'first-grid', '4', '10', '150', '150');

CREATE TABLE IF NOT EXISTS `new_s_export` (
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
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_export` (`id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export`)
SELECT `id`, `name`, `last_export`, `active`, `hash`, `show`, `count_articles`, `expiry`, `interval`, `formatID`, `last_change`, `filename`, `encodingID`, `categoryID`, `currencyID`, `customergroupID`, `partnerID`, `languageID`, `active_filter`, `image_filter`, `stockmin_filter`, `instock_filter`, `price_filter`, `own_filter`, `header`, `body`, `footer`, `count_filter`, `multishopID`, `variant_export` FROM `s_export`;
DROP TABLE IF EXISTS `s_export`;
RENAME TABLE `new_s_export` TO `s_export`;

ALTER TABLE `s_export_articles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_export_attributes`;
CREATE TABLE `s_export_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exportID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `exportID` (`exportID`),
  CONSTRAINT `s_export_attributes_ibfk_1` FOREIGN KEY (`exportID`) REFERENCES `s_export` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_export_categories` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_export_suppliers` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_filter` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_filter_articles`;
CREATE TABLE `s_filter_articles` (
  `articleID` int(10) unsigned NOT NULL,
  `valueID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`articleID`,`valueID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `s_filter_attributes`;
CREATE TABLE `s_filter_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filterID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filterID` (`filterID`),
  CONSTRAINT `s_filter_attributes_ibfk_1` FOREIGN KEY (`filterID`) REFERENCES `s_filter` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_filter_options` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_filter_relations` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_filter_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `optionID` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `optionID` (`optionID`,`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_filter_values` (`id`, `optionID`, `value`)
SELECT `id`, `optionID`, `value` FROM `s_filter_values`;
DROP TABLE IF EXISTS `backup_s_filter_values`;
RENAME TABLE `s_filter_values` TO `backup_s_filter_values`;
RENAME TABLE `new_s_filter_values` TO `s_filter_values`;

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
('2', 'HTML-Element', '', NULL, '', 'component_html', 'html-text-element', NULL),
('3', 'Banner', 'emotion-components-banner', 'getBannerMappingLinks', '', 'component_banner', 'banner-element', NULL),
('4', 'Artikel', 'emotion-components-article', 'getArticle', '', 'component_article', 'article-element', NULL),
('5', 'Kategorie-Teaser', 'emotion-components-category-teaser', 'getCategoryTeaser', '', 'component_category_teaser', 'category-teaser-element', NULL),
('6', 'Blog-Artikel', 'emotion-components-blog', 'getBlogEntry', '', 'component_blog', 'blog-element', NULL),
('7', 'Banner-Slider', 'emotion-components-banner-slider', 'getBannerSlider', '', 'component_banner_slider', 'banner-slider-element', NULL),
('8', 'Youtube-Video', '', NULL, '', 'component_youtube', 'youtube-element', NULL),
('9', 'iFrame-Element', '', NULL, '', 'component_iframe', 'iframe-element', NULL),
('10', 'Hersteller-Slider', 'emotion-components-manufacturer-slider', 'getManufacturerSlider', '', 'component_manufacturer_slider', 'manufacturer-slider-element', NULL),
('11', 'Artikel-Slider', 'emotion-components-article-slider', 'getArticleSlider', '', 'component_article_slider', 'article-slider-element', NULL);

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
  `default_value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `allow_blank` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`, `default_value`, `allow_blank`) VALUES
('3', '3', 'file', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', '0'),
('4', '2', 'text', 'tinymce', '', 'Text', 'Anzuzeigender Text', 'HTML-Text', 'Geben Sie hier den Text ein der im Element angezeigt werden soll.', '', '', '', '', '0'),
('5', '4', 'article', 'emotion-components-fields-article', '', 'Artikelsuche', 'Der anzuzeigende Artikel', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', '', '', '', '', '0'),
('6', '2', 'cms_title', 'textfield', '', 'Titel', '', '', '', '', '', '', '', '0'),
('7', '3', 'bannerMapping', 'hidden', 'json', '', '', '', '', '', '', '', '', '0'),
('8', '4', 'article_type', 'emotion-components-fields-article-type', '', 'Typ des Artikels', '', '', '', '', '', '', '', '0'),
('9', '5', 'image_type', 'emotion-components-fields-category-image-type', '', 'Typ des Bildes', '', '', '', '', '', '', '', '0'),
('10', '5', 'image', 'mediaselectionfield', '', 'Bild', '', '', '', '', '', '', '', '0'),
('11', '5', 'category_selection', 'emotion-components-fields-category-selection', '', '', '', '', '', '', '', '', '', '1'),
('12', '6', 'entry_amount', 'numberfield', '', 'Anzahl', '', '', '', '', '', '', '', '0'),
('13', '7', 'banner_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', '0'),
('15', '7', 'banner_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', '0'),
('16', '7', 'banner_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', '0'),
('17', '7', 'banner_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', '0'),
('18', '7', 'banner_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', '0'),
('19', '7', 'banner_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', '0'),
('20', '7', 'banner_slider', 'hidden', 'json', '', '', '', '', '', '', '', '', '0'),
('22', '8', 'video_id', 'textfield', '', 'Youtube-Video ID', '', '', '', '', '', '', '', '0'),
('23', '8', 'video_hd', 'checkbox', '', 'HD-Video verwenden', '', '', '', '', '', '', '', '0'),
('24', '9', 'iframe_url', 'textfield', '', 'URL', '', '', '', '', '', '', '', '0'),
('25', '10', 'manufacturer_type', 'emotion-components-fields-manufacturer-type', '', '', '', '', '', '', '', '', '', '0'),
('26', '10', 'manufacturer_category', 'emotion-components-fields-category-selection', '', '', '', '', '', '', '', '', '', '1'),
('27', '10', 'selected_manufacturers', 'hidden', 'json', '', '', '', '', '', '', '', '', '0'),
('28', '10', 'manufacturer_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', '0'),
('30', '10', 'manufacturer_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', '0'),
('31', '10', 'manufacturer_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', '0'),
('32', '10', 'manufacturer_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', '0'),
('33', '10', 'manufacturer_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', '0'),
('34', '10', 'manufacturer_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', '0'),
('36', '11', 'article_slider_type', 'emotion-components-fields-article-slider-type', '', '', '', '', '', '', '', '', '', '0'),
('37', '11', 'selected_articles', 'hidden', 'json', '', '', '', '', '', '', '', '', '0'),
('38', '11', 'article_slider_max_number', 'numberfield', '', 'max. Anzahl', '', '', '', '', '', '', '', '0'),
('39', '11', 'article_slider_title', 'textfield', '', 'Überschrift', '', '', '', '', '', '', '', '0'),
('41', '11', 'article_slider_arrows', 'checkbox', '', 'Pfeile anzeigen', '', '', '', '', '', '', '', '0'),
('42', '11', 'article_slider_numbers', 'checkbox', '', 'Nummern ausgeben', '', '', '', '', '', '', '', '0'),
('43', '11', 'article_slider_scrollspeed', 'numberfield', '', 'Scroll-Geschwindigkeit', '', '', '', '', '', '', '', '0'),
('44', '11', 'article_slider_rotation', 'checkbox', '', 'Automatisch rotieren', '', '', '', '', '', '', '', '0'),
('45', '11', 'article_slider_rotatespeed', 'numberfield', '', 'Rotations Geschwindigkeit', '', '', '', '', '', '', '5000', '0'),
('47', '3', 'link', 'textfield', '', 'Link', '', '', '', '', '', '', '', '0');

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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
('-12', 'Hersteller', NULL, '12'),
('-11', 'Blog', NULL, '3'),
('-10', 'Unsortiert', NULL, '7'),
('-9', 'Sonstiges', '-6', '3'),
('-8', 'Musik', '-6', '2'),
('-7', 'Video', '-6', '1'),
('-6', 'Dateien', NULL, '6'),
('-5', 'Newsletter', NULL, '4'),
('-4', 'Aktionen', NULL, '5'),
('-3', 'Einkaufswelten', NULL, '3'),
('-2', 'Banner', NULL, '1'),
('-1', 'Artikel', NULL, '2');

DROP TABLE IF EXISTS `s_media_album_settings`;
CREATE TABLE `s_media_album_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `albumID` int(11) NOT NULL,
  `create_thumbnails` int(11) NOT NULL,
  `thumbnail_size` text COLLATE utf8_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `albumID` (`albumID`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `s_media_album_settings` (`id`, `albumID`, `create_thumbnails`, `thumbnail_size`, `icon`) VALUES
('1', '-10', '0', '', 'sprite-blue-folder'),
('2', '-9', '0', '', 'sprite-blue-folder'),
('3', '-8', '0', '', 'sprite-blue-folder'),
('4', '-7', '0', '', 'sprite-blue-folder'),
('5', '-6', '0', '', 'sprite-blue-folder'),
('6', '-5', '0', '', 'sprite-blue-folder'),
('7', '-4', '0', '', 'sprite-blue-folder'),
('8', '-3', '0', '', 'sprite-blue-folder'),
('9', '-2', '0', '', 'sprite-blue-folder'),
('10', '-1', '1', '30x30;57x57;105x105;140x140;285x255;720x600', 'sprite-blue-folder'),
('11', '-11', '1', '57x57;140x140;285x255;720x600', 'sprite-blue-folder'),
('12', '-12', '0', '', 'sprite-blue-folder');

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

CREATE TABLE IF NOT EXISTS `new_s_order` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`)
SELECT `id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr` FROM `s_order`;
DROP TABLE IF EXISTS `backup_s_order`;
RENAME TABLE `s_order` TO `backup_s_order`;
RENAME TABLE `new_s_order` TO `s_order`;

DROP TABLE IF EXISTS `s_order_attributes`;
CREATE TABLE `s_order_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `orderID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orderID` (`orderID`),
  CONSTRAINT `s_order_attributes_ibfk_1` FOREIGN KEY (`orderID`) REFERENCES `s_order` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_order_basket` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`, `liveshoppingID`, `bundleID`, `bundle_join_ordernumber`)
SELECT `id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`, `liveshoppingID`, `bundleID`, `bundle_join_ordernumber` FROM `s_order_basket`;
DROP TABLE IF EXISTS `backup_s_order_basket`;
RENAME TABLE `s_order_basket` TO `backup_s_order_basket`;
RENAME TABLE `new_s_order_basket` TO `s_order_basket`;

DROP TABLE IF EXISTS `s_order_basket_attributes`;
CREATE TABLE `s_order_basket_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `basketID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `basketID` (`basketID`),
  CONSTRAINT `s_order_basket_attributes_ibfk_2` FOREIGN KEY (`basketID`) REFERENCES `s_order_basket` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_order_billingaddress` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid`)
SELECT `id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid` FROM `s_order_billingaddress`;
DROP TABLE IF EXISTS `backup_s_order_billingaddress`;
RENAME TABLE `s_order_billingaddress` TO `backup_s_order_billingaddress`;
RENAME TABLE `new_s_order_billingaddress` TO `s_order_billingaddress`;

DROP TABLE IF EXISTS `s_order_billingaddress_attributes`;
CREATE TABLE `s_order_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`),
  CONSTRAINT `s_order_billingaddress_attributes_ibfk_2` FOREIGN KEY (`billingID`) REFERENCES `s_order_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_order_comparisons` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_order_details` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `config`)
SELECT `id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `config` FROM `s_order_details`;
DROP TABLE IF EXISTS `backup_s_order_details`;
RENAME TABLE `s_order_details` TO `backup_s_order_details`;
RENAME TABLE `new_s_order_details` TO `s_order_details`;

DROP TABLE IF EXISTS `s_order_details_attributes`;
CREATE TABLE `s_order_details_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `detailID` int(11) DEFAULT NULL,
  `attribute1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `attribute6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detailID` (`detailID`),
  CONSTRAINT `s_order_details_attributes_ibfk_1` FOREIGN KEY (`detailID`) REFERENCES `s_order_details` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_order_documents` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_order_documents_attributes`;
CREATE TABLE `s_order_documents_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `documentID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `documentID` (`documentID`),
  CONSTRAINT `s_order_documents_attributes_ibfk_1` FOREIGN KEY (`documentID`) REFERENCES `s_order_documents` (`ID`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_order_esd` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

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

ALTER TABLE `s_order_notes` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_order_number` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_order_shippingaddress` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `countryID`)
SELECT `id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `countryID` FROM `s_order_shippingaddress`;
DROP TABLE IF EXISTS `backup_s_order_shippingaddress`;
RENAME TABLE `s_order_shippingaddress` TO `backup_s_order_shippingaddress`;
RENAME TABLE `new_s_order_shippingaddress` TO `s_order_shippingaddress`;

DROP TABLE IF EXISTS `s_order_shippingaddress_attributes`;
CREATE TABLE `s_order_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`),
  CONSTRAINT `s_order_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_order_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_plugin_recommendations` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `s_plugin_widgets_notes`;
CREATE TABLE `s_plugin_widgets_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) NOT NULL,
  `notes` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_premium_dispatch` (
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
INSERT IGNORE INTO `new_s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`)
SELECT `id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql` FROM `s_premium_dispatch`;
DROP TABLE IF EXISTS `s_premium_dispatch`;
RENAME TABLE `new_s_premium_dispatch` TO `s_premium_dispatch`;

DROP TABLE IF EXISTS `s_premium_dispatch_attributes`;
CREATE TABLE `s_premium_dispatch_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dispatchID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dispatchID` (`dispatchID`),
  CONSTRAINT `s_premium_dispatch_attributes_ibfk_1` FOREIGN KEY (`dispatchID`) REFERENCES `s_premium_dispatch` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_premium_dispatch_categories` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_premium_dispatch_countries` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_premium_dispatch_holidays` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_premium_dispatch_paymentmeans` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_premium_holidays` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_premium_shippingcosts` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_search_fields` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `relevance` int(11) NOT NULL,
  `field` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tableID` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `field` (`field`,`tableID`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_search_fields` (`id`, `name`, `relevance`, `field`, `tableID`)
SELECT `id`, `name`, `relevance`, `field`, `tableID` FROM `s_search_fields`;
DROP TABLE IF EXISTS `s_search_fields`;
RENAME TABLE `new_s_search_fields` TO `s_search_fields`;

ALTER TABLE `s_search_index` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_search_keywords` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_search_tables` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_statistics_currentusers` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

ALTER TABLE `s_statistics_pool` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_statistics_referer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `referer` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_statistics_referer` (`id`, `datum`, `referer`)
SELECT `id`, `datum`, `referer` FROM `s_statistics_referer`;
DROP TABLE IF EXISTS `s_statistics_referer`;
RENAME TABLE `new_s_statistics_referer` TO `s_statistics_referer`;

CREATE TABLE IF NOT EXISTS `new_s_statistics_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `searchterm` varchar(255) CHARACTER SET latin1 NOT NULL,
  `results` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `searchterm` (`searchterm`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
INSERT IGNORE INTO `new_s_statistics_search` (`id`, `datum`, `searchterm`, `results`)
SELECT `id`, `datum`, `searchterm`, `results` FROM `s_statistics_search`;
DROP TABLE IF EXISTS `s_statistics_search`;
RENAME TABLE `new_s_statistics_search` TO `s_statistics_search`;

CREATE TABLE IF NOT EXISTS `new_s_statistics_visitors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shopID` int(11) NOT NULL,
  `datum` date NOT NULL DEFAULT '0000-00-00',
  `pageimpressions` int(11) NOT NULL DEFAULT '0',
  `uniquevisits` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `datum` (`datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_statistics_visitors` (`id`, `datum`, `pageimpressions`, `uniquevisits`)
SELECT `id`, `datum`, `pageimpressions`, `uniquevisits` FROM `s_statistics_visitors`;
DROP TABLE IF EXISTS `s_statistics_visitors`;
RENAME TABLE `new_s_statistics_visitors` TO `s_statistics_visitors`;

CREATE TABLE IF NOT EXISTS `new_s_ticket_support` (
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
  `shop_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT IGNORE INTO `new_s_ticket_support` (`id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`, `isocode`)
SELECT `id`, `uniqueID`, `userID`, `employeeID`, `ticket_typeID`, `statusID`, `email`, `subject`, `message`, `receipt`, `last_contact`, `additional`, `isocode` FROM `s_ticket_support`;
DROP TABLE IF EXISTS `s_ticket_support`;
RENAME TABLE `new_s_ticket_support` TO `s_ticket_support`;

CREATE TABLE IF NOT EXISTS `new_s_ticket_support_mails` (
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
  `shop_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
INSERT IGNORE INTO `new_s_ticket_support_mails` (`id`, `name`, `description`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `sys_dependent`, `isocode`)
SELECT `id`, `name`, `description`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `attachment`, `sys_dependent`, `isocode` FROM `s_ticket_support_mails`;
DROP TABLE IF EXISTS `s_ticket_support_mails`;
RENAME TABLE `new_s_ticket_support_mails` TO `s_ticket_support_mails`;

CREATE TABLE IF NOT EXISTS `new_s_user` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_user` (`id`, `password`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`)
SELECT `id`, `password`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil` FROM `s_user`;
DROP TABLE IF EXISTS `s_user`;
RENAME TABLE `new_s_user` TO `s_user`;

DROP TABLE IF EXISTS `s_user_attributes`;
CREATE TABLE `s_user_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`),
  CONSTRAINT `s_user_attributes_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `s_user` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_user_billingaddress` (
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
  `stateID` int(11) DEFAULT NULL,
  `ustid` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `birthday` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_user_billingaddress` (`id`, `userID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid`, `birthday`)
SELECT `id`, `userID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid`, `birthday` FROM `s_user_billingaddress`;
DROP TABLE IF EXISTS `backup_s_user_billingaddress`;
RENAME TABLE `s_user_billingaddress` TO `backup_s_user_billingaddress`;
RENAME TABLE `new_s_user_billingaddress` TO `s_user_billingaddress`;

DROP TABLE IF EXISTS `s_user_billingaddress_attributes`;
CREATE TABLE `s_user_billingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `billingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `billingID` (`billingID`),
  CONSTRAINT `s_user_billingaddress_attributes_ibfk_1` FOREIGN KEY (`billingID`) REFERENCES `s_user_billingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

ALTER TABLE `s_user_debit` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8, COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `new_s_user_shippingaddress` (
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
  `stateID` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `userID` (`userID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
INSERT IGNORE INTO `new_s_user_shippingaddress` (`id`, `userID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `countryID`)
SELECT `id`, `userID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `countryID` FROM `s_user_shippingaddress`;
DROP TABLE IF EXISTS `backup_s_user_shippingaddress`;
RENAME TABLE `s_user_shippingaddress` TO `backup_s_user_shippingaddress`;
RENAME TABLE `new_s_user_shippingaddress` TO `s_user_shippingaddress`;

DROP TABLE IF EXISTS `s_user_shippingaddress_attributes`;
CREATE TABLE `s_user_shippingaddress_attributes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shippingID` int(11) DEFAULT NULL,
  `text1` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text2` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text3` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text4` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text5` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `text6` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `shippingID` (`shippingID`),
  CONSTRAINT `s_user_shippingaddress_attributes_ibfk_1` FOREIGN KEY (`shippingID`) REFERENCES `s_user_shippingaddress` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `cb_events`;

DROP TABLE IF EXISTS `cb_orders`;

DROP TABLE IF EXISTS `eos_reserved_orders`;

DROP TABLE IF EXISTS `paypal_orders`;

DROP TABLE IF EXISTS `backup_s_articles_groups`;
RENAME TABLE `s_articles_groups` TO `backup_s_articles_groups`;

DROP TABLE IF EXISTS `backup_s_articles_groups_option`;
RENAME TABLE `s_articles_groups_option` TO `backup_s_articles_groups_option`;

DROP TABLE IF EXISTS `backup_s_articles_groups_prices`;
RENAME TABLE `s_articles_groups_prices` TO `backup_s_articles_groups_prices`;

DROP TABLE IF EXISTS `backup_s_articles_groups_settings`;
RENAME TABLE `s_articles_groups_settings` TO `backup_s_articles_groups_settings`;

DROP TABLE IF EXISTS `s_articles_groups_templates`;

DROP TABLE IF EXISTS `backup_s_articles_groups_value`;
RENAME TABLE `s_articles_groups_value` TO `backup_s_articles_groups_value`;

DROP TABLE IF EXISTS `s_core_auth_files`;

DROP TABLE IF EXISTS `s_core_checklist`;

DROP TABLE IF EXISTS `backup_s_core_config`;
RENAME TABLE `s_core_config` TO `backup_s_core_config`;

DROP TABLE IF EXISTS `s_core_config_groups`;

DROP TABLE IF EXISTS `s_core_config_text`;

DROP TABLE IF EXISTS `s_core_config_text_groups`;

DROP TABLE IF EXISTS `s_core_engine_fieldsets`;

DROP TABLE IF EXISTS `s_core_engine_queries`;

DROP TABLE IF EXISTS `s_core_engine_values`;

DROP TABLE IF EXISTS `s_core_hookpoints`;

DROP TABLE IF EXISTS `s_core_im`;

DROP TABLE IF EXISTS `backup_s_core_licences`;
RENAME TABLE `s_core_licences` TO `backup_s_core_licences`;

DROP TABLE IF EXISTS `s_core_modules`;

DROP TABLE IF EXISTS `backup_s_core_plugin_configs`;
RENAME TABLE `s_core_plugin_configs` TO `backup_s_core_plugin_configs`;

DROP TABLE IF EXISTS `backup_s_core_plugin_elements`;
RENAME TABLE `s_core_plugin_elements` TO `backup_s_core_plugin_elements`;

DROP TABLE IF EXISTS `s_core_queries`;

DROP TABLE IF EXISTS `s_core_statistics`;

DROP TABLE IF EXISTS `s_core_unadjusted`;

DROP TABLE IF EXISTS `s_core_variants`;

DROP TABLE IF EXISTS `s_core_viewports`;

DROP TABLE IF EXISTS `s_emarketing_searchbanner`;

DROP TABLE IF EXISTS `s_export_settings`;

DROP TABLE IF EXISTS `s_help`;

DROP TABLE IF EXISTS `s_plugin_benchmark_log`;

DROP TABLE IF EXISTS `s_shippingcosts`;

DROP TABLE IF EXISTS `s_shippingcosts_areas`;

DROP TABLE IF EXISTS `s_shippingcosts_dispatch`;

DROP TABLE IF EXISTS `s_shippingcosts_dispatch_countries`;

DROP TABLE IF EXISTS `s_user_creditcard`;

DROP TABLE IF EXISTS `s_user_service`;

DROP TABLE IF EXISTS `saferpay_orders`;
