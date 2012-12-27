INSERT INTO s_library_component_field (id, componentID, name, x_type, value_type, field_label, support_text, help_title, help_text) VALUES
(7, 3, 'banner-mapping', 'hidden', 'json', '', '', '', '');
-- //@UNDO

DELETE FROM s_library_component_field WHERE name = 'banner-mapping';