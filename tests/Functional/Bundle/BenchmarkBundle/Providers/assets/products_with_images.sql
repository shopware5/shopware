SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles_img;
DELETE FROM s_articles;

INSERT INTO `s_articles` (`id`, `supplierID`, `name`, `description`, `description_long`, `shippingtime`, `datum`, `active`, `taxID`, `pseudosales`, `topseller`, `metaTitle`, `keywords`, `changetime`, `pricegroupID`, `pricegroupActive`, `filtergroupID`, `laststock`, `crossbundlelook`, `notification`, `template`, `mode`, `main_detail_id`, `available_from`, `available_to`, `configurator_set_id`) VALUES
    (1, 1, 'Example product 1', '', 'Example description 1', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 1, NULL, NULL, NULL),
    (2, 1, 'Example product 2', '', 'Example description 2', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 2, NULL, NULL, NULL),
    (3, 1, 'Example product 3', '', 'Example description 3', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 3, NULL, NULL, NULL),
    (4, 1, 'Example product 4', '', 'Example description 4', NULL, '2017-10-05', 1, 1, 0, 0, NULL, NULL, '2017-10-10 09:44:42', NULL, 0, 3, 0, 0, 0, '', 0, 4, NULL, NULL, NULL);

INSERT INTO `s_articles_img` (`articleID`, `img`, `main`, `description`, `position`, `width`, `height`, `relations`, `extension`, `parent_id`, `article_detail_id`, `media_id`) VALUES
    (1, 'Example Img 1', 1, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (1, 'Example Img 2', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 3', 1, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 4', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 5', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 6', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 7', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 8', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (4, 'Example Img 9', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (1, 'Example Img 10', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (1, 'Example Img 11', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1),
    (1, 'Example Img 12', 2, '', 1, 0, 0, '', 'jpg', NULL, NULL, 1);

SET FOREIGN_KEY_CHECKS=1;
