SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_categories;
DELETE FROM s_articles_categories;

INSERT INTO `s_categories` (`id`, `parent`, `path`, `description`, `position`, `left`, `right`, `level`, `added`, `changed`, `metakeywords`, `metadescription`, `cmsheadline`, `cmstext`, `template`, `active`, `blog`, `external`, `hidefilter`, `hidetop`, `mediaID`, `product_box_layout`, `meta_title`, `stream_id`, `hide_sortings`, `sorting_ids`, `facet_ids`, `external_target`) VALUES
    (1, NULL, NULL, 'Root', 0, 1, 6, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, NULL, NULL, NULL, NULL, 1, 0, NULL, 0, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, ''),
    (2, 1, '|3|', 'Example 1', NULL, 0, 0, 0, '2012-08-27 22:28:52', '2012-08-27 22:28:52', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, ''),
    (3, 1, '|3|', 'Example 2', NULL, 0, 0, 0, '2017-10-05 14:56:02', '2017-10-05 14:56:02', NULL, '', '', '', NULL, 1, 0, '', 0, 0, NULL, NULL, '', NULL, 0, NULL, NULL, '');

INSERT INTO `s_articles_categories` (`articleID`, `categoryID`) VALUES
    (1, 1),
    (2, 1),
    (3, 1),
    (4, 1),
    (5, 1),
    (6, 1),
    (7, 1),
    (8, 2),
    (9, 2),
    (1, 2),
    (2, 3),
    (3, 3);

SET FOREIGN_KEY_CHECKS=1;
