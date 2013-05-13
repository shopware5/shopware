

-- //

ALTER TABLE `s_articles_categories` ADD INDEX  `category_id_by_article_id` (  `articleID` ,  `id` );

-- //@UNDO

ALTER TABLE `s_articles_categories` DROP INDEX  `category_id_by_article_id`;

-- //

