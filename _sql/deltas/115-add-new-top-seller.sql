-- //


INSERT INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(NULL, 'Core', 'MarketingAggregate', 'Shopware Marketing Aggregat Funktionen', 'Default', NULL, NULL, 1, '2013-04-30 14:19:13', '2013-04-30 14:26:48', '2013-04-30 14:26:48', '2013-04-30 14:26:51', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, 'http://www.shopware.de/', NULL, NULL, 1, 1, 1, NULL, NULL);

SET @pluginId = (SELECT id FROM s_core_plugins WHERE name = 'MarketingAggregate');




CREATE TABLE IF NOT EXISTS `s_articles_top_seller_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `sales` int(11) unsigned NOT NULL DEFAULT '0',
  `last_cleared` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_also_bought_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `related_article_id` int(11) NOT NULL,
  `sales` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id_2` (`article_id`,`related_article_id`),
  KEY `related_article_id` (`related_article_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_articles_similar_shown_ro` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `related_article_id` int(11) NOT NULL,
  `viewed` int(11) unsigned NOT NULL DEFAULT '0',
  `init_date` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `viewed` (`viewed`,`related_article_id`,`article_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


ALTER TABLE  `s_emarketing_lastarticles` ADD INDEX  `get_last_articles` (  `sessionID` ,  `time` );
ALTER TABLE  `s_articles` ADD INDEX  `product_newcomer` (  `active` ,  `datum` );


-- //@UNDO

-- //
