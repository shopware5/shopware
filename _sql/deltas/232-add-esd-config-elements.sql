-- //

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Product');

INSERT IGNORE INTO `s_core_config_forms` (`parent_id`, `name`, `label`) VALUES
(@parent, 'Esd', 'ESD');

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Esd');

INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'esdKey', 's:33:"552211cce724117c3178e3d22bec532ec";', 'ESD-Key', NULL, 'text', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //
