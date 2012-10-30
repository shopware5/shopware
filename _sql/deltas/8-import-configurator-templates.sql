
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
  KEY `articleID` (`template_id`)
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
  UNIQUE KEY `priceID` (`template_price_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


ALTER TABLE `s_article_configurator_templates_attributes`
  ADD CONSTRAINT `s_article_configurator_templates_attributes_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `s_article_configurator_templates` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;


ALTER TABLE `s_article_configurator_template_prices_attributes`
  ADD CONSTRAINT `s_article_configurator_template_prices_attributes_ibfk_1` FOREIGN KEY (`template_price_id`) REFERENCES `s_article_configurator_template_prices` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION;

-- //@UNDO

DROP TABLE IF EXISTS s_article_configurator_templates;
DROP TABLE IF EXISTS s_article_configurator_templates_attributes;
DROP TABLE IF EXISTS s_article_configurator_template_prices;
DROP TABLE IF EXISTS s_article_configurator_template_prices_attributes;

-- //


