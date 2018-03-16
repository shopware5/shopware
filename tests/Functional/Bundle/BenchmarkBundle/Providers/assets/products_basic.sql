SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles;

INSERT INTO `s_articles` (`supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`, `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`, `configurator_set_id`) VALUES
    (1, 'Example product 1', '', 'Example description 1', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 1, NULL, NULL, NULL),
    (1, 'Example product 2', '', 'Example description 2', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 10:04:49', NULL, 0, 3, 1, 0, 1, '', 0, 2, NULL, NULL, NULL),
    (1, 'Example product 3', '', 'Example description 3', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 10:04:32', NULL, 0, 3, 0, 0, 0, '', 0, 3, NULL, NULL, NULL),
    (1, 'Example product 4', '', 'Example description 4', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-11-15 08:48:45', NULL, 0, 4, 0, 0, 0, '', 0, 4, NULL, NULL, NULL),
    (1, 'Example product 5', '', 'Example description 5', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-11-15 08:49:37', NULL, 0, 1, 0, 0, 0, '', 0, 5, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
