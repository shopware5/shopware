-- //

SET @debug_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Einstellungen');
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@debug_parent, '', 'Debug-Men&uuml;', '', '', 'sprite-hard-hat', '0', '1', NULL , NULL);

SET @menu_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Debug-Men&uuml;');
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'Styling Demo', 'createStylingDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'Blog Messages Demo', 'createBlogMessagesDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'Code Mirror Demo', 'createCodeMirrorDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'Article Suggest Search Demo', 'createArticleSearchDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'TinyMCE Demo', 'createTinyMceDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);
INSERT INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style` , `class`, `position`, `active`, `pluginID`, `resourceID`)
VALUES (@menu_parent, '', 'Desktop-Switcher Demo', 'createDesktopSwitcherDemo()', '', 'sprite-hard-hat', '0', '1', NULL , NULL);

-- //@UNDO

DELETE FROM `s_core_menu` WHERE `name` LIKE 'Debug-Men&uuml;';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'Styling Demo';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'Blog Messages Demo';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'Code Mirror Demo';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'Article Suggest Search Demo';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'TinyMCE Demo';
DELETE FROM `s_core_menu` WHERE `name` LIKE 'Desktop-Switcher Demo';

--