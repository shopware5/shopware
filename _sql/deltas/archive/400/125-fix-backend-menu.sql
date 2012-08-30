-- //
ALTER TABLE `s_core_menu` CHANGE `parent` `parent` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `onclick` `onclick` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `style` `style` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `class` `class` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `pluginID` `pluginID` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `controller` `controller` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `shortcut` `shortcut` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `action` `action` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL;
-- ALTER TABLE `s_core_menu` DROP `resourceID`

UPDATE `s_core_menu` SET `parent` = NULL WHERE `parent` = '';
UPDATE `s_core_menu` SET `onclick` = NULL WHERE `onclick` = '';
UPDATE `s_core_menu` SET `class` = NULL WHERE `class` = '';
UPDATE `s_core_menu` SET `pluginID` = NULL WHERE `pluginID` = '';
UPDATE `s_core_menu` SET `shortcut` = NULL WHERE `shortcut` = '';
UPDATE `s_core_menu` SET `controller` = NULL WHERE `controller` = '';
UPDATE `s_core_menu` SET `style` = NULL;

UPDATE `s_core_menu` SET `onclick` = NULL, `name` = 'Shopcache leeren' WHERE `name` LIKE '%Shopcache%';

UPDATE `s_core_menu` SET `name` = 'Textbausteine + Template',
`onclick` = NULL ,
`controller` = 'Cache',
`shortcut` = 'STRG + SHIFT + T',
`action` = 'Template'
WHERE `onclick` LIKE 'deleteCache%snippets%';

UPDATE `s_core_menu` SET `onclick` = NULL,
`controller` = 'Cache',
`shortcut` = 'STRG + SHIFT + C',
`action` = 'Config'
WHERE `onclick` LIKE 'deleteCache%articles%';

UPDATE `s_core_menu` SET `onclick` = NULL,
`controller` = 'Cache',
`shortcut` = 'STRG + SHIFT + F',
`action` = 'Frontend'
WHERE `onclick` LIKE 'deleteCache%config%';

UPDATE `s_core_menu`
SET `controller` = 'Article',  `action` = 'Detail'
WHERE `name` LIKE 'Anlegen';

UPDATE `s_core_menu`
SET `name` = 'Übersicht',
`controller` = 'Article', `action` = 'List'
WHERE `name` LIKE '%bersicht';

-- //@UNDO

-- //