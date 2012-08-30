-- //

INSERT INTO `s_library_component` (`id` ,`name` ,`x_type` ,`convert_function` ,`description` ,`template` ,`cls` ,`pluginID`)
VALUES (NULL ,  'Blog-Artikel',  'emotion-components-blog',  'getBlogEntry',  '',  'component_category_blog',  'category-teaser-blog', NULL);

-- //@UNDO

DELETE FROM `s_library_component` WHERE `template` = 'component_category_blog';

-- //