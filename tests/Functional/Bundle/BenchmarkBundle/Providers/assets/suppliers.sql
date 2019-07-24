SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles;
DELETE FROM s_articles_categories;

INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`, `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`, `configurator_set_id`) VALUES
    (1, 1, 'Example product 1', '', '', NULL, '2017-10-05', 1, 1, 0, 0, '', '', '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 1, NULL, NULL, NULL),
    (2, 2, 'Example product 2', '', '', NULL, '2017-10-05', 1, 1, 5, 1, '', '', '2017-10-10 10:04:49', NULL, 0, 3, 1, 0, 1, '', 0, 2, NULL, NULL, NULL),
    (3, 2, 'Example product 3', '', '', NULL, '2017-10-05', 1, 1, 10, 0, '', '', '2017-10-10 10:04:32', NULL, 0, 3, 0, 0, 0, '', 0, 3, NULL, NULL, NULL),
    (4, 3, 'Example product 4', '', '', NULL, '2017-10-05', 1, 1, 15, 1, '', '', '2017-12-07 12:56:07', NULL, 0, 4, 0, 0, 0, '', 0, 39, NULL, NULL, 6),
    (5, 4, 'Example product 5', '', '', NULL, '2017-10-05', 1, 1, 20, 0, '', '', '2017-10-10 09:42:07', NULL, 0, 1, 0, 0, 0, '', 0, 23, NULL, NULL, 3);

INSERT INTO `s_articles_categories` (`articleID`, `categoryID`) VALUES
    (1, 2),
    (2, 2),
    (3, 3),
    (4, 3),
    (5, 2);

SET FOREIGN_KEY_CHECKS=1;
