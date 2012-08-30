-- //

SET @parent = (SELECT id FROM `s_core_plugins` WHERE `name`='Auth');

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 78, 'Auth', 'Backend', '', 0, 0, @parent);

SET @parent = (SELECT id FROM `s_core_config_forms` WHERE `name`='Auth');

INSERT INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'backendTimeout', 'i:7200;', 'Timeout', NULL, 'interval', 1, 0, 0, NULL, NULL, 'a:0:{}'),
(NULL, @parent, 'backendLocales', 'a:2:{i:0;i:1;i:1;i:2;}', 'Auswählbare Sprachen', NULL, 'select', 1, 0, 0, NULL, NULL, 'a:2:{s:5:"store";s:11:"base.Locale";s:11:"multiSelect";b:1;}');

-- //@UNDO

SET @parent = (SELECT id FROM `s_core_config_forms` WHERE `name`='Auth');
DELETE FROM `s_core_config_elements` WHERE form_id = @parent;
DELETE FROM `s_core_config_forms` WHERE id = @parent;

-- //