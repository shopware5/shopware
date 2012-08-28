-- //

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider');
INSERT INTO  `s_library_component_field`
(`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES
(NULL ,  @parent,  'slider_select',  'emotion-components-fields-category-slider-select',  '',  'Slider Typ',  '',  '',  '',  '',  '',  '');
UPDATE  `s_library_component_field` SET  `value_type` =  'json' WHERE  `name` = 'banner_slider';

-- //@UNDO

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider');
DELETE FROM `s_library_component_field` WHERE `x_type` = 'emotion-components-fields-category-slider-select' AND `componentID` = @parent;
UPDATE  `s_library_component_field` SET  `value_type` =  '' WHERE  `name` = 'banner_slider';

--