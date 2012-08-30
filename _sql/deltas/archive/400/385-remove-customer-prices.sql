-- //
DELETE FROM `s_core_menu` WHERE `name` = 'Kundenspezifische Preise';

-- //@UNDO

INSERT INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES (NULL, '20', '', 'Kundenspezifische Preise', NULL, NULL, 'sprite-user--coins', '0', '1', NULL, NULL, 'PriceGroup', NULL, 'Index');

--