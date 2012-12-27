-- //
ALTER TABLE `s_article_configurator_sets` ADD `type` INT NOT NULL DEFAULT '0';
-- //@UNDO
ALTER TABLE `s_article_configurator_sets` DROP `type`;
-- //