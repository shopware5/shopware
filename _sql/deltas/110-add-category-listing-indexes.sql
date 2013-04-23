ALTER TABLE  `s_core_tax` ADD INDEX  `tax`
( `tax` );

ALTER TABLE  `s_articles` ADD INDEX  `articles_by_category_sort_release`
( `datum` ,  `id` );

ALTER TABLE  `s_articles_details` ADD INDEX  `articles_by_category_sort_popularity`
( `sales` ,  `impressions` ,  `articleID` );

ALTER TABLE  `s_articles` ADD INDEX  `articles_by_category_sort_name` (  `name` ,  `id` );

ALTER TABLE  `s_core_tax_rules` ADD INDEX  `tax_rate_by_conditions`
( `customer_groupID` ,  `areaID` ,  `countryID` ,  `stateID` );

ALTER TABLE  `s_categories` ADD INDEX  `active_query_builder` 
(  `parent` ,  `position` ,  `id` );

ALTER TABLE  `s_categories` ADD INDEX (  `parent` );

ALTER TABLE  `s_articles_img` ADD INDEX  `variant_images_by_article_number` 
(  `articleID` ,  `main` ,  `position` );

ALTER TABLE  `s_cms_static` ADD INDEX  `get_menu` (  `position` ,  `description` );