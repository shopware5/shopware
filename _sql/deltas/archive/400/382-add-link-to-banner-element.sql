-- //
SET @parent = (SELECT id FROM `s_library_component` WHERE `template`='component_banner');
INSERT INTO `s_library_component_field` (`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field` ,`default_value` ,`allow_blank`)
VALUES (NULL ,  @parent,  'link',  'textfield',  '',  'Link',  '',  '',  '',  '',  '',  '',  '',  0);

-- //@UNDO

--