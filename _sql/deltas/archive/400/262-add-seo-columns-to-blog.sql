-- //

ALTER TABLE `s_blog` ADD `seo_keywords` VARCHAR( 255 ) NULL , ADD `seo_description` VARCHAR( 150 ) NULL;

UPDATE `s_core_menu` SET `name` = 'Blog', `onclick` = '' WHERE `controller` = 'blog';



-- //@UNDO

ALTER TABLE `s_blog` DROP `seo_keywords`, DROP `seo_description`;

UPDATE `s_core_menu` SET `name` = 'Blog*', `onclick` = '' WHERE `controller` = 'Blog';

--