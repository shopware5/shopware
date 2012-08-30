-- //
ALTER TABLE `s_emotion` ADD `active` INT( 1 ) NOT NULL AFTER `id`;
ALTER TABLE `s_emotion` ADD `is_landingpage` INT( 1 ) NOT NULL AFTER `userID`;
ALTER TABLE `s_emotion` ADD `landingpage_teaser` VARCHAR ( 255 ) NOT NULL AFTER `is_landingpage`;
ALTER TABLE `s_emotion` ADD `seo_keywords` VARCHAR( 255 ) NOT NULL AFTER `landingpage_teaser`;
ALTER TABLE `s_emotion` ADD `seo_description` TEXT NOT NULL AFTER `seo_keywords`;

UPDATE s_emotion SET active = 1;

-- //@UNDO
ALTER TABLE `s_emotion` DROP `active`;
ALTER TABLE `s_emotion` DROP `is_landingpage`;
ALTER TABLE `s_emotion` DROP `landingpage_teaser`;
ALTER TABLE `s_emotion` DROP `seo_keywords`;
ALTER TABLE `s_emotion` DROP `seo_description`;
--