-- //

INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`, `resourceID`, `controller`, `shortcut`, `action`) VALUES
(100, '', 'Base Store Demo', 'createBaseStoreDemo()', NULL, 'sprite-hard-hat', 0, 1, NULL, NULL, NULL, NULL, NULL);

-- //@UNDO

DELETE FROM `s_core_menu` WHERE name = 'Base Store Demo' AND onclick = 'createBaseStoreDemo()';

-- //
