ALTER TABLE `s_article_configurator_set_relations` ADD INDEX ( `set_id` , `group_id` );

ALTER TABLE `s_article_configurator_dependencies` ADD `configurator_set_id` INT UNSIGNED NOT NULL AFTER `id` ,
ADD INDEX ( `configurator_set_id` );


CREATE TABLE IF NOT EXISTS `s_article_configurator_price_surcharges` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `configurator_set_id` int(10) unsigned NOT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `child_id` int(11) unsigned DEFAULT NULL,
  `surcharge` decimal(10,0) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `configurator_set_id` (`configurator_set_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- //@UNDO

DROP TABLE s_article_configurator_price_surcharges;
ALTER TABLE s_article_configurator_dependencies DROP configurator_set_id;
