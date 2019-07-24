SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles;
DELETE FROM s_articles_details;
DELETE FROM s_articles_img;
DELETE FROM s_media;
DELETE FROM s_articles_categories;

INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`, `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`, `configurator_set_id`) VALUES
    (1, 3, 'Example product 1', '', '', NULL, '2017-10-05', 1, 1, 0, 0, '', '', '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 1, NULL, NULL, NULL),
    (2, 3, 'Example product 2', '', '', NULL, '2017-10-05', 1, 1, 5, 1, '', '', '2017-10-10 10:04:49', NULL, 0, 3, 1, 0, 1, '', 0, 2, NULL, NULL, NULL),
    (3, 3, 'Example product 3', '', '', NULL, '2017-10-05', 1, 1, 10, 0, '', '', '2017-10-10 10:04:32', NULL, 0, 3, 0, 0, 0, '', 0, 3, NULL, NULL, NULL),
    (4, 3, 'Example product 4', '', '', NULL, '2017-10-05', 1, 1, 15, 1, '', '', '2017-12-07 12:56:07', NULL, 0, 4, 0, 0, 0, '', 0, 39, NULL, NULL, 6),
    (5, 1, 'Example product 5', '', '', NULL, '2017-10-05', 1, 1, 20, 0, '', '', '2017-10-10 09:42:07', NULL, 0, 1, 0, 0, 0, '', 0, 23, NULL, NULL, 3);

INSERT INTO `s_articles_details` (`articleID`, `ordernumber`, `suppliernumber`, `kind`, `additionaltext`, `sales`, `active`, `instock`, `stockmin`, `laststock`, `weight`, `position`, `width`, `height`, `length`, `ean`, `unitID`, `purchasesteps`, `maxpurchase`, `minpurchase`, `purchaseunit`, `referenceunit`, `packunit`, `releasedate`, `shippingfree`, `shippingtime`, `purchaseprice`) VALUES
    (1, 'SW10001', '', 1, '', 0, 1, 2, 1, 0, 0, 0, NULL, NULL, NULL, '', 9, NULL, NULL, 1, 1.0000, 1.000, '', NULL, 0, '3', 0),
    (2, 'SW10002', '', 1, '', 0, 0, 4, 3, 0, 0, 0, 1.000, 1.000, 1.000, '', 9, NULL, NULL, 1, 1.0000, 1.000, '', NULL, 1, '7', 0),
    (3, 'SW10003', '', 1, '', 0, 1, 6, 5, 1, 0, 0, NULL, NULL, NULL, '', 9, NULL, NULL, 2, 1.0000, 1.000, '', NULL, 1, '0', 0),
    (4, 'SW10004', '', 1, '', 0, 1, 8, 7, 1, 0, 0, NULL, NULL, NULL, '', 9, NULL, 10, 1, 1.0000, 1.000, '', NULL, 0, '', 0),
    (5, 'SW10005', '', 1, '', 0, 0, 15, 20, 0, 0, 0, NULL, NULL, NULL, '', 9, 5, NULL, 1, 1.0000, 1.000, '', NULL, 1, '2', 0);

INSERT INTO `s_articles_img` (`articleID`, `img`, `main`, `description`, `position`, `width`, `height`, `relations`, `extension`, `parent_id`, `article_detail_id`, `media_id`) VALUES
    (1, 'mobile', 1, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (1, 'waschmaschine', 1, '', 1, 0, 0, '', 'jpg', NULL, NULL, 2);

INSERT INTO `s_media` (`id`, `albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `width`, `height`, `userID`, `created`) VALUES
    (1, -1, 'foo', '', 'media/image/foo.jpg', 'IMAGE', 'jpg', 12345, 1280, 1280, 50, '2017-10-05'),
    (2, -1, 'bar', '', 'media/image/bar.jpg', 'IMAGE', 'jpg', 54321, 1280, 1280, 50, '2017-10-05');

INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`) VALUES
    (4, 2, '|2|', 'Example 2', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', 1, 0, NULL, NULL, ''),
    (5, 2, '|2|', 'Example 3', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 0, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, ''),
    (6, 3, '|3|', 'Example 4', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 0, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, ''),
    (7, 5, '|2|5|', 'Example 5', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, ''),
    (8, 7, '|2|5|7|', 'Example 6', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, '');

INSERT INTO `s_articles_categories` (`articleID`, `categoryID`) VALUES
    (1, 2),
    (2, 2),
    (3, 2),
    (4, 2),
    (5, 2);

SET FOREIGN_KEY_CHECKS=1;
