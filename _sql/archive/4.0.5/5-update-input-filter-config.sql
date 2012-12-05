
SET @parent = (SELECT f.id FROM s_core_config_forms f WHERE f.name = 'InputFilter');

DELETE e FROM s_core_config_elements e
WHERE e.form_id = @parent
AND e.name LIKE '%_regex';

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'own_filter', 'N;', 'Eigener Filter', NULL, 'textarea', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'rfi_protection', 'b:1;', 'RemoteFileInclusion-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'sql_protection', 'b:1;', 'SQL-Injection-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL),
(@parent, 'xss_protection', 'b:1;', 'XSS-Schutz aktivieren', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL);

-- //@UNDO

-- //
