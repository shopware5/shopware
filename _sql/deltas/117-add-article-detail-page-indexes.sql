

-- //

ALTER TABLE `s_articles_categories_ro` ADD INDEX  `category_id_by_article_id` (  `articleID` ,  `id` );

-- //@UNDO

ALTER TABLE `s_articles_categories_ro` DROP INDEX  `category_id_by_article_id`;

-- //

