-- //

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 80, 'Recommendation', 'Artikelempfehlung', NULL, 8, 1, NULL);
SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Recommendation');

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'alsoBoughtShow', 'b:1;', 'Anzeigen der Kunden-kauften-auch-Liste', NULL, 'checkbox', 1, 1, 1, NULL, NULL, NULL),
(NULL, @parent, 'alsoBoughtPerPage', 'i:4;', 'Anzahl an Artikel pro Seite in der Liste', NULL, 'number', 1, 2, 1, NULL, NULL, NULL),
(NULL, @parent, 'alsoBoughtMaxPages', 'i:10;', 'Maximale Anzahl von Seiten in der Liste', NULL, 'number', 1, 3, 1, NULL, NULL, NULL),
(NULL, @parent, 'similarViewedShow', 'b:1;', 'Anzeigen der Kunden-schauten-sich-auch-an-Liste', NULL, 'checkbox', 1, 5, 1, NULL, NULL, NULL),
(NULL, @parent, 'similarViewedPerPage', 'i:4;', 'Anzahl an Artikel pro Seite in der Liste', NULL, 'number', 1, 6, 1, NULL, NULL, NULL),
(NULL, @parent, 'similarViewedMaxPages', 'i:10;', 'Maximale Anzahl von Seiten in der Liste', NULL, 'number', 1, 7, 1, NULL, NULL, NULL);

-- //@UNDO

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Recommendation');
DELETE FROM s_core_config_elements WHERE form_id=@parent;
DELETE FROM s_core_config_forms WHERE id=@parent;

-- //
