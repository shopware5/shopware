-- //
ALTER TABLE `s_blog_comments` ADD `points` DOUBLE NOT NULL;

-- //@UNDO

ALTER TABLE `s_blog_comments` DROP `points`;
--