INSERT INTO `s_library_component_field` (`id`, `componentID`, `name`, `x_type`, `value_type`, `field_label`, `support_text`, `help_title`, `help_text`) VALUES (NULL, '2', 'cms_title', 'textfield', '', 'Titel', '', '', '');

-- //@UNDO

DELETE FROM `s_library_component_field` WHERE `name` = 'cms_title';