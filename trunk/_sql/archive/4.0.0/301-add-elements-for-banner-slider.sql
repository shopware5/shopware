-- //

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider');
INSERT INTO  `s_library_component_field`
(`id` ,`componentID` ,`name` ,`x_type` ,`value_type` ,`field_label` ,`support_text` ,`help_title` ,`help_text` ,`store` ,`display_field` ,`value_field`)
VALUES
(NULL ,  @parent,  'banner_slider_title',  'textfield',  '',  'Überschrift',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_navigation',  'checkbox',  '',  'Navigation anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_arrows',  'checkbox',  '',  'Pfeile anzeigen',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_numbers',  'checkbox',  '',  'Nummern ausgeben',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_scrollspeed',  'numberfield',  '',  'Scroll-Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_rotation',  'checkbox',  '',  'Automatisch rotieren',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider_scrollspeed',  'numberfield',  '',  'Rotations Geschwindigkeit',  '',  '',  '',  '',  '',  ''),
(NULL ,  @parent,  'banner_slider',  'hidden',  '',  '',  '',  '',  '',  '',  '',  '');

-- //@UNDO

SET @parent = (SELECT id FROM `s_library_component` WHERE `x_type`='emotion-components-banner-slider');
DELETE FROM `s_library_component` WHERE `componentID` = @parent ;

--