-- //

DELETE FROM `s_core_menu` WHERE name LIKE 'Templateauswahl';

-- //@UNDO

SET @parent = (SELECT id FROM s_core_menu WHERE name LIKE 'Einstellungen');
INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES
(NULL, @parent, '', 'Templateauswahl', NULL, NULL, 'sprite-application-icon-large', 0, 1, NULL, NULL, 'Config', NULL, 'Template');

--