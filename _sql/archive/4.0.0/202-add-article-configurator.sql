ALTER TABLE  `s_articles` ADD  `available_from` DATETIME NULL DEFAULT NULL ,
ADD  `available_to` DATETIME NULL DEFAULT NULL;

ALTER TABLE `s_articles` ADD `configurator_set_id` INT( 11 ) UNSIGNED NULL , ADD INDEX ( `configurator_set_id` );
ALTER TABLE `s_articles_details` ADD `unitID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `purchasesteps` int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `maxpurchase` int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `minpurchase` int(11) UNSIGNED DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `purchaseunit` decimal(10,3) UNSIGNED DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `referenceunit` decimal(10,3) UNSIGNED DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `packunit` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `releasedate` date DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD `shippingfree` int(1) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE `s_articles_details` ADD `shippingtime` varchar(11) COLLATE utf8_unicode_ci DEFAULT NULL;
ALTER TABLE `s_articles_details` ADD INDEX ( `releasedate` );

UPDATE s_articles_details, s_articles SET s_articles_details.unitID = s_articles.unitID,
    s_articles_details.purchasesteps = s_articles.purchasesteps,
    s_articles_details.maxpurchase = s_articles.maxpurchase,
    s_articles_details.minpurchase = s_articles.minpurchase,
    s_articles_details.purchaseunit = s_articles.purchaseunit,
    s_articles_details.referenceunit = s_articles.referenceunit,
    s_articles_details.packunit = s_articles.packunit,
    s_articles_details.releasedate = s_articles.releasedate,
    s_articles_details.shippingfree = s_articles.shippingfree,
    s_articles_details.shippingtime = s_articles.shippingtime
WHERE s_articles_details.articleID = s_articles.id;

UPDATE s_articles_details SET shippingtime = NULL WHERE shippingtime = '' OR  shippingtime = 0;
UPDATE s_articles_details SET packunit = NULL WHERE packunit = '';
UPDATE s_articles_details SET purchasesteps = NULL WHERE purchasesteps = '';
UPDATE s_articles_details SET maxpurchase = NULL WHERE maxpurchase = '';
UPDATE s_articles_details SET minpurchase = NULL WHERE minpurchase = '';
UPDATE s_articles_details SET purchaseunit = NULL WHERE purchaseunit = '';
UPDATE s_articles_details SET referenceunit = NULL WHERE referenceunit = '';

CREATE TABLE IF NOT EXISTS `s_article_configurator_dependencies` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `child_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


CREATE TABLE IF NOT EXISTS `s_article_configurator_groups` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5 ;

CREATE TABLE IF NOT EXISTS `s_article_configurator_options` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned DEFAULT NULL,
  `article_id` int(11) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `article_id` (`article_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=11 ;

CREATE TABLE IF NOT EXISTS `s_article_configurator_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) unsigned DEFAULT NULL,
  `option_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detail_id` (`detail_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `s_article_configurator_sets` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=3 ;

CREATE TABLE IF NOT EXISTS `s_article_configurator_set_relations` (
  `id` int(11) unsigned NOT NULL,
  `set_id` int(11) unsigned DEFAULT NULL,
  `group_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;



-- //@UNDO;
ALTER TABLE s_articles_details DROP INDEX releasedate;
ALTER TABLE `s_articles` DROP `configurator_set_id`;
ALTER TABLE `s_articles` DROP `available_from`;
ALTER TABLE `s_articles` DROP `available_to`;
ALTER TABLE `s_articles_details` DROP `unitID`;
ALTER TABLE `s_articles_details` DROP `purchasesteps`;
ALTER TABLE `s_articles_details` DROP `maxpurchase`;
ALTER TABLE `s_articles_details` DROP `minpurchase`;
ALTER TABLE `s_articles_details` DROP `purchaseunit`;
ALTER TABLE `s_articles_details` DROP `referenceunit`;
ALTER TABLE `s_articles_details` DROP `packunit`;
ALTER TABLE `s_articles_details` DROP `releasedate`;
ALTER TABLE `s_articles_details` DROP `shippingfree`;
ALTER TABLE `s_articles_details` DROP `shippingtime`;

DROP TABLE s_article_configurator_dependencies;
DROP TABLE s_article_configurator_groups;
DROP TABLE s_article_configurator_options;
DROP TABLE s_article_configurator_relations;
DROP TABLE s_article_configurator_sets;
DROP TABLE s_article_configurator_set_relations;
