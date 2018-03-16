SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_emotion;

INSERT INTO `s_emotion` (`active`, `name`, `cols`, `cell_spacing`, `cell_height`, `article_height`, `rows`, `valid_from`, `valid_to`, `userID`, `show_listing`, `is_landingpage`, `seo_title`, `seo_keywords`, `seo_description`, `create_date`, `modified`, `template_id`, `device`, `fullscreen`, `mode`, `position`, `parent_id`, `preview_id`, `preview_secret`, `customer_stream_ids`, `replacement`) VALUES
    (1, 'Example emotion 1', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (1, 'Example emotion 2', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,2,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (1, 'Example emotion 3', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (1, 'Example emotion 4', 4, 10, 185, 2, 20, NULL, NULL, 1, 0, 1, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (1, 'Example emotion 5', 4, 10, 185, 2, 20, '2018-03-07 03:00:00', NULL, 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,1,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL),
    (1, 'Example emotion 6', 4, 10, 185, 2, 20, NULL, '2018-03-07 05:00:00', 1, 0, 0, '', '', '', '2017-11-07 09:57:28', '2018-03-06 15:53:29', 1, '0,2,3,4', 0, 'fluid', 1, NULL, NULL, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
