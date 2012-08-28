-- //

INSERT INTO `s_library_component`
(`id` ,`name` ,`x_type` ,`convert_function` ,`description` ,`template` ,`cls` ,`pluginID`)
VALUES (NULL ,  'Kategorie-Teaser',  'emotion-components-category-teaser', NULL ,  '',  'component_category_teaser',  'category-teaser-element', NULL);

-- //@UNDO

DELETE FROM `s_library_component` WHERE `x_type` = 'emotion-components-category-teaser';

-- //