-- //

SET @parent = (SELECT `id` FROM `s_core_config_forms` WHERE `label` = 'ESD');

INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(@parent, 'redirectDownload', 'b:0;', 'Auf Download-Datei direkt verlinken', NULL, 'boolean', 0, 0, 0, NULL, NULL, NULL);


SET @parent = (SELECT `id` FROM `s_core_config_elements` WHERE `name` = 'redirectDownload');

INSERT IGNORE INTO `s_core_config_element_translations` (`id` ,`element_id` ,`locale_id` ,`label` ,`description`)
VALUES (NULL , @parent, '2', 'Link to download file directly', NULL);

-- //@UNDO

-- //
