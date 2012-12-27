-- //
ALTER TABLE `s_article_configurator_sets` DROP INDEX `name` ,
ADD INDEX `name` ( `name` );
-- //@UNDO
ALTER TABLE `s_article_configurator_sets` DROP INDEX `name` ,
ADD UNIQUE `name` ( `name` );
-- //