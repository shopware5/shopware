/* Test content for SearchIndexerTest */

/* Clear tables */
DELETE FROM s_articles;
DELETE FROM s_articles_details;
DELETE FROM s_articles_categories;
DELETE FROM s_articles_supplier;
DELETE FROM s_articles_translations;
DELETE FROM s_categories;
DELETE FROM s_search_keywords;
DELETE FROM s_search_fields;

/* Set search index config */
INSERT INTO `s_search_fields` (`id`, `name`, `relevance`, `field`, `tableID`, `do_not_split`)
VALUES
	(1,'Kategorie-Überschrift',70,'description',2,0),
	(2,'Artikel-Name',400,'name',1,0),
	(3,'Artikel-Bestellnummer',50,'ordernumber',4,0),
	(4,'Hersteller-Name',45,'name',3,0),
	(5,'Artikel-Name Übersetzung',50,'name',5,0);

/* Insert some test suppliers */
INSERT INTO `s_articles_supplier` (`id`, `name`, `img`, `link`, `description`, `meta_title`, `meta_description`, `meta_keywords`, `changed`)
VALUES
	(1, 'Testsupplier 1', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(2, 'Testsupplier 2', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(3, 'Testsupplier 3', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(4, 'Testsupplier 4', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(5, 'Testsupplier 5', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(6, 'Testsupplier 6', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(7, 'Testsupplier 7', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(8, 'Testsupplier 8', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(9, 'Testsupplier 9', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(10, 'Testsupplier 10', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00'),
	(11, 'Examplesupplier', '', '', '', NULL, NULL, NULL, '2019-01-01 00:00:00');

/* Insert some test articles */
INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`, `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`, `configurator_set_id`)
VALUES
	(1, 1, 'Testarticle 1', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(2, 2, 'Testarticle 2', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(3, 3, 'Testarticle 3', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(4, 4, 'Testarticle 4', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(5, 5, 'Testarticle 5', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(6, 6, 'Testarticle 6', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(7, 7, 'Testarticle 7', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(8, 8, 'Testarticle 8', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(9, 9, 'Testarticle 9', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(10, 10, 'Testarticle 10', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL),
	(11, 11, 'Examplearticle', NULL, NULL, NULL, NULL, 1, NULL, 0, 0, NULL, NULL, '2019-01-01 00:00:00', NULL, 0, NULL, 0, 0, 0, '', 0, NULL, NULL, NULL, NULL);

/* Insert some test article details */
INSERT INTO `s_articles_details` (`id`, `articleID`, `ordernumber`, `suppliernumber`, `kind`, `additionaltext`, `sales`, `active`, `instock`, `stockmin`, `laststock`, `weight`, `position`, `width`, `height`, `length`, `ean`, `unitID`, `purchasesteps`, `maxpurchase`, `minpurchase`, `purchaseunit`, `referenceunit`, `packunit`, `releasedate`, `shippingfree`, `shippingtime`, `purchaseprice`)
VALUES
	(1, 1, 'testordernumber-1', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(2, 2, 'testordernumber-2', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(3, 3, 'testordernumber-3', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(4, 4, 'testordernumber-4', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(5, 5, 'testordernumber-5', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(6, 6, 'testordernumber-6', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(7, 7, 'testordernumber-7', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(8, 8, 'testordernumber-8', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(9, 9, 'testordernumber-9', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(10, 10, 'testordernumber-10', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0),
	(11, 11, 'exampleordernumber-11', NULL, 0, NULL, 0, 1, 0, NULL, 0, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, NULL, NULL, NULL, 0, NULL, 0);

/* Insert some test translations */
INSERT INTO `s_articles_translations` (`id`, `articleID`, `languageID`, `name`, `keywords`, `description`, `description_long`, `description_clear`, `shippingtime`)
VALUES
	(1, 1, 2, 'Foo', '', '', '', '', ''),
	(2, 2, 2, 'Foo', '', '', '', '', ''),
	(3, 3, 2, 'Foo', '', '', '', '', ''),
	(4, 4, 2, 'Foo', '', '', '', '', ''),
	(5, 5, 2, 'Foo', '', '', '', '', ''),
	(6, 6, 2, 'Bar', '', '', '', '', ''),
	(7, 7, 2, 'Bar', '', '', '', '', ''),
	(8, 8, 2, 'Bar', '', '', '', '', ''),
	(9, 9, 2, 'Bar', '', '', '', '', ''),
	(10, 10, 2, 'Bar', '', '', '', '', ''),
	(11, 11, 2, 'Bar', '', '', '', '', '');

/* Insert some test categories and link them to the article */
INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`, `shops`)
VALUES
	(1, NULL, NULL, 'Testcategory 1', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(2, NULL, NULL, 'Testcategory 2', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(3, NULL, NULL, 'Testcategory 3', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(4, NULL, NULL, 'Testcategory 4', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(5, NULL, NULL, 'Testcategory 5', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(6, NULL, NULL, 'Testcategory 6', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(7, NULL, NULL, 'Testcategory 7', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(8, NULL, NULL, 'Testcategory 8', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(9, NULL, NULL, 'Testcategory 9', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(10, NULL, NULL, 'Testcategory 10', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL),
	(11, NULL, NULL, 'Examplecategory', 0, 0, 0, 0, '2019-01-01 00:00:00', '2019-01-01 00:00:00', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, '', NULL);

INSERT INTO `s_articles_categories` (`id`, `articleID`, `categoryID`)
VALUES
	(1, 1, 1),
	(2, 2, 2),
	(3, 3, 3),
	(4, 4, 4),
	(5, 5, 5),
	(6, 6, 6),
	(7, 7, 7),
	(8, 8, 8),
	(9, 9, 9),
	(10, 10, 10),
	(11, 11, 11);
