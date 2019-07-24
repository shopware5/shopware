SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_core_shops;
DELETE FROM s_categories;
DELETE FROM s_benchmark_config;

INSERT INTO `s_core_shops` (`id`, `main_id`, `name`, `title`, `position`, `host`, `base_path`, `base_url`, `hosts`, `secure`, `template_id`, `document_template_id`, `category_id`, `locale_id`, `currency_id`, `customer_group_id`, `fallback_id`, `customer_scope`, `default`, `active`) VALUES
    (1, NULL, 'Shop 1', NULL, 0, 'myShop.de', NULL, NULL, '', 0, 23, 23, 2, 1, 1, 1, NULL, 0, 1, 1),
    (2, NULL, 'Shop 2', NULL, 0, 'myShop.com', NULL, NULL, '', 0, 23, 23, 3, 2, 1, 1, NULL, 1, 0, 1);

INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`) VALUES
    (1, NULL, NULL, 'Root', 0, 0, 0, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, ''),
    (2, 1, NULL, 'Example Parent 1', 0, 0, 0, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, ''),
    (3, 1, NULL, 'Example Parent 2', 0, 0, 0, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, '');

INSERT INTO `s_benchmark_config` (`id`, `shop_id`, `active`, `last_sent`, `last_received`, `last_order_id`, `last_customer_id`, `last_product_id`, `last_updated_orders_date`, `batch_size`, `industry`, `type`, `response_token`, `cached_template`, `locked`)
VALUES (UNHEX(''), '1', '1', '1990-01-01 00:00:00', '1990-01-01 00:00:00', '0', '0', '0', '1970-01-01 00:00:00', '1000', '3', 'b2c', NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
