-- //

DELETE FROM `s_library_component_field` WHERE `x_type` = 'emotion-components-fields-category-slider-select';
DELETE FROM `s_library_component_field` WHERE `field_label` = 'Navigation anzeigen';
UPDATE `s_library_component_field` SET  `default_value` =  '5000' WHERE  `field_label` = 'Rotations Geschwindigkeit';

-- //@UNDO

--
