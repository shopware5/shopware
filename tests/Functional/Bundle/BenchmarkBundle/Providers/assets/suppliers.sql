SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles_supplier;

INSERT INTO `s_articles_supplier` (`name`, `img`, `link`, `description`, `meta_title`, `meta_description`, `meta_keywords`, `changed`) VALUES
    ('Example 1', '', '', '', '', '', '', '2017-10-05 14:59:03'),
    ('Example 2', '', '', '', '', '', '', '2017-10-05 14:59:13'),
    ('Example 3', '', '', '', '', '', '', '2017-10-05 14:59:40'),
    ('Example 4', '', '', '', '', '', '', '2017-10-05 14:59:40'),
    ('Example 5', '', '', '', '', '', '', '2017-10-05 14:59:40');

SET FOREIGN_KEY_CHECKS=1;
