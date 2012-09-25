-- //

ALTER TABLE `s_article_configurator_options` DROP INDEX `group_id`,
ADD UNIQUE (`group_id`, `name`);

ALTER TABLE `s_article_configurator_sets` DROP INDEX `name`,
ADD UNIQUE `name` ( `name` );

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

-- //@UNDO


-- //