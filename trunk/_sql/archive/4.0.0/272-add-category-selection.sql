-- //

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-category-teaser');
INSERT INTO  `s_library_component_field` (`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES
(NULL ,  '5',  'category_selection',  'emotion-components-fields-category-selection',  '',  '',  '',  '',  '',  '',  '',  '');

-- //@UNDO

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-category-teaser');
DELETE FROM `s_library_component_field` WHERE `x_type` = 'emotion-components-fields-category-selection';

-- //