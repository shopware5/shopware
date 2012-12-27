-- //

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Core');

INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'version', 's:5:"4.0.0";', 'Version', NULL, 'text', 1, 0, 0, NULL, NULL, NULL),
(NULL, @parent, 'revision', 's:4:"3024";', 'Revision', NULL, 'text', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //