-- //

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-blog');
INSERT INTO `s_library_component_field` (`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES (NULL ,  @parent,  'entry_amount',  'numberfield',  '',  'Anzahl',  '',  '',  '',  '',  '',  '');

-- //@UNDO

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-blog');
DELETE FROM `s_library_component_field` WHERE `name` = 'enty_amount' AND `componentID` = @parent;

-- //
