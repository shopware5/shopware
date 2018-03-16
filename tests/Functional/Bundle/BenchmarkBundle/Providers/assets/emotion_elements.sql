SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_emotion_element;
DELETE FROM s_library_component;

INSERT INTO `s_emotion_element` (`emotionID`, `componentID`, `start_row`, `start_col`, `end_row`, `end_col`, `css_class`) VALUES
    (1, 500, 1, 1, 1, 1, ''),
    (1, 500, 1, 1, 1, 1, ''),
    (1, 500, 1, 1, 1, 1, ''),
    (1, 501, 1, 1, 1, 1, ''),
    (1, 501, 1, 1, 1, 1, '');

INSERT INTO `s_library_component` (`id`, `name`, `x_type`, `convert_function`, `description`, `template`, `cls`, `pluginID`) VALUES
    (500, 'Example element 1', 'example-element-1', NULL, '', 'example_html_1', 'html-text-example-1', NULL),
    (501, 'Example element 2', 'example-element-2', NULL, '', 'example_html_2', 'html-text-example-2', NULL);

SET FOREIGN_KEY_CHECKS=1;
