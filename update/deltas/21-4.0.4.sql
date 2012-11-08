-- 1-fix-emotion-foreign-key.sql

DROP TABLE IF EXISTS s_emotion_attributes_new;
CREATE TABLE s_emotion_attributes_new LIKE s_emotion_attributes;
INSERT INTO s_emotion_attributes_new SELECT * FROM s_emotion_attributes;
DROP TABLE IF EXISTS s_emotion_attributes;
RENAME TABLE s_emotion_attributes_new TO s_emotion_attributes;
ALTER TABLE `s_emotion_attributes` ADD FOREIGN KEY ( `emotionID` ) REFERENCES `s_emotion` (
 `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

-- 2-trim-links.sql

UPDATE `s_articles_information` SET `link` = TRIM(`link`) ;

-- 3-fix-blog-attributes.sql

DROP TABLE IF EXISTS s_blog_attributes_new;
CREATE TABLE s_blog_attributes_new LIKE s_blog_attributes;
INSERT INTO s_blog_attributes_new SELECT * FROM s_blog_attributes;
DROP TABLE IF EXISTS s_blog_attributes;
RENAME TABLE s_blog_attributes_new TO s_blog_attributes;
ALTER TABLE `s_blog_attributes` ADD FOREIGN KEY ( `blog_id` ) REFERENCES `s_blog` (
  `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

-- 4-fix-cronstock-mail.sql

UPDATE s_core_config_mails SET ishtml = 0 WHERE name = 'sARTICLESTOCK';

-- 5-fix-config-values-length.sql

ALTER TABLE `s_core_config_values` CHANGE `value` `value` LONGTEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;

-- 7-update-self-healing.sql

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

SET @parent = (SELECT id FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem');
DELETE FROM `s_core_config_values` WHERE `element_id` = @parent;
DELETE FROM `s_core_config_elements` WHERE `name` LIKE 'bonusSystem';

-- 11-improve-customer-incrementation.sql

UPDATE s_order_number n, s_user_billingaddress u
SET n.number = n.number+1
WHERE n.name = 'user'
AND n.number = u.customernumber;
