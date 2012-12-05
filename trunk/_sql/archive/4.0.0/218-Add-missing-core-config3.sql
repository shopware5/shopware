-- //

SET @parent = (SELECT id FROM s_core_config_forms WHERE name='Core');

INSERT IGNORE INTO `s_core_config_elements` (`id`, `form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(NULL, @parent, 'baseFile', 's:12:"shopware.php";', 'Base-File', NULL, 'text', 1, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //