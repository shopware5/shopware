SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles_categories;

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
    (5, 2),
    (6, 2),
    (7, 4),
    (8, 4),
    (9, 5),
    (1, 5),
    (10, 6),
    (11, 6);

SET FOREIGN_KEY_CHECKS=1;
