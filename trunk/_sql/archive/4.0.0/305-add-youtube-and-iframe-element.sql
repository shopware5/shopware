-- //

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES (NULL, 'Youtube-Video', '', NULL, '', 'component_youtube', 'youtube-element', NULL), (NULL, 'iFrame-Element', '', NULL, '', 'component_iframe', 'iframe-element', NULL);

SET @youtube = (SELECT id FROM `s_library_component` WHERE `template`='component_youtube');
INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`) VALUES (NULL, @youtube, 'video_id', 'textfield', '', 'Youtube-Video ID', '', '', '', '', '', ''), (NULL, @youtube, 'video_hd', 'checkbox', '', 'HD-Video verwenden', '', '', '', '', '', '');

SET @iframe = (SELECT id FROM `s_library_component` WHERE `template`='component_iframe');
INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`, `store`, `display_field`, `value_field`) VALUES (NULL, @iframe, 'iframe_url', 'textfield', '', 'URL', '', '', '', '', '', '');

-- //@UNDO

SET @youtube = (SELECT id FROM `s_library_component` WHERE `template`='component_youtube');
DELETE FROM `s_library_component_field` WHERE `componentID` = @youtube;

SET @iframe = (SELECT id FROM `s_library_component` WHERE `template`='component_iframe');
DELETE FROM `s_library_component_field` WHERE `componentID` = @iframe;

DELETE FROM `s_library_component` WHERE `template` = 'component_youtube';
DELETE FROM `s_library_component` WHERE `template` = 'component_iframe';

--