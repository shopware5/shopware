-- //

ALTER TABLE `s_blog` CHANGE `authorID` `author_id` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `s_blog` CHANGE `categoryID` `category_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `s_blog_media` CHANGE `blogID` `blog_id` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_blog_media` CHANGE `mediaID` `media_id` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_blog_tags` CHANGE `blogID` `blog_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL;

-- //@UNDO

ALTER TABLE `s_blog` CHANGE `author_id` `authorID` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `s_blog` CHANGE `category_id` `categoryID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE `s_blog_media` CHANGE `blog_id` `blogID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_blog_media` CHANGE `media_id` `mediaID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_blog_tags` CHANGE `blog_id` `blogID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;

-- //
