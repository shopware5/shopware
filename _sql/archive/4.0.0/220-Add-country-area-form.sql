-- //

DELETE FROM `s_core_menu` WHERE `controller` = 'Config' AND `action` IN ('Country', 'Tax');
DELETE FROM `s_core_menu` WHERE `controller` LIKE 'OldSettings';

INSERT INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 77, 'CountryArea', 'Länder-Zonen', NULL, 51, 0, NULL);

INSERT IGNORE INTO `s_core_config_forms` (`id`, `parent_id`, `name`, `label`, `description`, `position`, `scope`, `plugin_id`) VALUES
(NULL, 78, 'Plugin', 'Plugins', NULL, 20, 0, NULL);

-- //@UNDO


-- //