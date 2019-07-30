SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_emotion;
DELETE FROM s_emotion_shops;
DELETE FROM s_emotion_categories;
DELETE FROM s_emotion_element;
DELETE FROM s_library_component;

INSERT INTO `s_emotion` (`id`, `active`, `name`, `cols`, `cell_spacing`, `cell_height`, `article_height`, `rows`, `valid_from`, `valid_to`, `userID`, `show_listing`, `is_landingpage`, `seo_title`, `seo_keywords`, `seo_description`, `create_date`, `modified`, `template_id`, `device`, `fullscreen`, `mode`, `position`, `parent_id`, `preview_id`, `preview_secret`, `customer_stream_ids`, `replacement`) VALUES
    (1, 1, 'Example 1 Shop 1', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (2, 1, 'Example 2 Shop 1', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,2,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (3, 1, 'Example 3 Shop 1', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (4, 1, 'Example 4 Shop 2', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (5, 1, 'Example 5 Shop 2', 4, 10, 185, 2, 20, '2018-03-07 03:00:00', NULL, 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (6, 1, 'Example 6 Shop 1', 4, 10, 185, 2, 20, NULL, '2018-03-07 05:00:00', 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL);

INSERT INTO `s_emotion_shops` (`emotion_id`, `shop_id`) VALUES
    (2, 1),
    (3, 1),
    (4, 2);

INSERT INTO `s_emotion_categories` (`emotion_id`, `category_id`) VALUES
    (1, 2),
    (5, 3),
    (6, 4);

INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`) VALUES
    (4, 2, '|2|', 'Example 2', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', 1, 0, NULL, NULL, '');

INSERT INTO `s_emotion_element` (`emotionID`, `componentID`, `start_row`, `start_col`, `end_row`, `end_col`, `css_class`) VALUES
    (1, 500, 1, 1, 1, 1, ''),
    (1, 500, 1, 1, 1, 1, ''),
    (1, 500, 1, 1, 1, 1, ''),
    (1, 501, 1, 1, 1, 1, ''),
    (1, 501, 1, 1, 1, 1, ''),
    (4, 500, 1, 1, 1, 1, ''),
    (4, 500, 1, 1, 1, 1, ''),
    (5, 501, 1, 1, 1, 1, '');

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
    (500, 'Example element 1', 'example-element-1', NULL, '', 'example_html_1', 'html-text-example-1', NULL),
    (501, 'Example element 2', 'example-element-2', NULL, '', 'example_html_2', 'html-text-example-2', NULL);

SET FOREIGN_KEY_CHECKS=1;
