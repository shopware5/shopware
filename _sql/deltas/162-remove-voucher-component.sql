DELETE FROM s_library_component WHERE id = 1;
DELETE FROM s_library_component_field WHERE componentID = 1;

-- //@UNDO

INSERT INTO s_library_component (id, name, description, template, cls, pluginID) VALUES
(1, 'Gutschein', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam', 'component_voucher', 'voucher-element', NULL);

INSERT INTO s_library_component_field (id, componentID, name, x_type, field_label, support_text, help_title, help_text) VALUES
(1, 1, 'code', 'textfield', 'Gutschein Code', 'Geben sie hier den Gutschein Code ein', 'Lorem ipsum dolor', 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam');