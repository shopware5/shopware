-- //

UPDATE  `s_library_component` SET  `cls` =  'banner-slider-element' WHERE  `x_type` = 'emotion-components-banner-slider';

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES (NULL, 'Hersteller-Slider', 'emotion-components-manufacturer-slider', 'getManufacturerSlider', '', 'component_manufacturer_slider', 'manufacturer-slider-element', NULL);

SET @parent = (SELECT id FROM `s_library_component` WHERE `template`='component_manufacturer_slider');
INSERT INTO  `s_library_component_field`
(`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES
(NULL, @parent, 'manufacturer_type', 'emotion-components-fields-manufacturer-type', '', '', '', '', '', '', '', ''),
(NULL, @parent, 'manufacturer_category', 'emotion-components-fields-category-selection', '', '', '', '', '', '', '', ''),
(NULL, @parent, 'selected_manufacturers', 'hidden', 'json', '', '', '', '', '', '', ''),
(NULL ,  @parent,  'manufacturer_slider_title',  'textfield',  '',  'Überschrift',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_navigation',  'checkbox',  '',  'Navigation anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_arrows',  'checkbox',  '',  'Pfeile anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_numbers',  'checkbox',  '',  'Nummern ausgeben',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_scrollspeed',  'numberfield',  '',  'Scroll-Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_rotation',  'checkbox',  '',  'Automatisch rotieren',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_scrollspeed',  'numberfield',  '',  'Rotations Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'manufacturer_slider_select',  'emotion-components-fields-category-slider-select',  '',  'Slider Typ',  '',  '',  '',  '',  '',  '');

-- //@UNDO


UPDATE  `s_library_component` SET  `cls` =  'banner-slider' WHERE  `x_type` = 'emotion-components-banner-slider';

SET @parent = (SELECT id FROM `s_library_component` WHERE `template`='component_manufacturer_slider');
DELETE FROM `s_library_component_field` WHERE `componentID` = @parent;
DELETE FROM `s_library_component` WHERE `x_type` = 'emotion-components-manufacturer-slider';

--