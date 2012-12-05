-- //

SET @formID = (SELECT id FROM s_core_config_forms WHERE name='MasterData');
INSERT INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@formID, 'company', 's:0:"";', 'Firma', NULL, 'textfield', 0, 0, 1, NULL, NULL, NULL);

-- //@UNDO

SET @formID = (SELECT id FROM s_core_config_forms WHERE name='MasterData');
DELETE FROM `s_core_config_elements` WHERE form_id = @formID AND label='Firma';

--