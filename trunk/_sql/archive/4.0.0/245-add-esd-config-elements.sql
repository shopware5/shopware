-- //

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Esd');

INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'esdMinSerials', 'i:5;', 'ESD-Min-Serials', NULL, 'text', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //
