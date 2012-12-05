-- //

UPDATE  `s_library_component_field` SET  `allow_blank` =  '1' WHERE `x_type` = 'emotion-components-fields-category-selection';
UPDATE  `s_library_component_field` SET `allow_blank` = '1' WHERE `x_type` = 'emotion-components-fields-category-slider-select';

SET @bannerSlider = (SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-components-banner-slider');
UPDATE  `s_library_component_field` SET  `name` = 'banner_slider_rotatespeed' WHERE  `field_label` = 'Rotations Geschwindigkeit' AND `componentID` = @bannerSlider;

SET @manufacturerSlider = (SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-components-manufacturer-slider');
UPDATE  `s_library_component_field` SET  `name` = 'manufacturer_slider_rotatespeed' WHERE  `field_label` = 'Rotations Geschwindigkeit' AND `componentID` = @manufacturerSlider;

SET @articleSlider = (SELECT id FROM `s_library_component` WHERE `x_type` = 'emotion-components-article-slider');
UPDATE  `s_library_component_field` SET  `name` = 'article_slider_rotatespeed' WHERE  `field_label` = 'Rotations Geschwindigkeit' AND `componentID` = @articleSlider;

-- //@UNDO


-- //