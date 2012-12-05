-- //
RENAME TABLE `s_article_configurator_set_relations` TO `s_article_configurator_set_group_relations` ;
CREATE TABLE IF NOT EXISTS `s_article_configurator_set_option_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `set_id` int(11) unsigned DEFAULT NULL,
  `option_id` int(11) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;


-- //@UNDO

RENAME TABLE `s_article_configurator_set_group_relations` TO `s_article_configurator_set_relations` ;
DROP TABLE s_article_configurator_set_option_relations;

-- //