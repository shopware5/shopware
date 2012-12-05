ALTER TABLE `s_article_configurator_options` DROP `article_id`;
DROP TABLE s_article_configurator_relations;

CREATE TABLE IF NOT EXISTS `s_article_configurator_option_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `article_id` int(11) unsigned NOT NULL,
  `option_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `article_id` (`article_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

ALTER TABLE  `s_article_configurator_set_relations` CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT;

-- //@UNDO

ALTER TABLE `s_article_configurator_options` ADD `article_id` INT NOT NULL;
ALTER TABLE  `s_article_configurator_set_relations` CHANGE  `id`  `id` INT( 11 ) UNSIGNED NOT NULL;
DROP TABLE s_article_configurator_option_relations;

CREATE TABLE IF NOT EXISTS `s_article_configurator_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `detail_id` int(11) unsigned DEFAULT NULL,
  `option_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `detail_id` (`detail_id`,`option_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;