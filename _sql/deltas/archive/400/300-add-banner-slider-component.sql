-- //

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES (NULL, 'Banner-Slider', 'emotion-components-banner-slider', 'getBannerSlider', '', 'component_banner_slider', 'banner-slider', NULL);

-- //@UNDO

DELETE FROM `s_library_component` WHERE `x_type` = 'emotion-components-banner-slider';

--