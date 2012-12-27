-- //

UPDATE `s_core_menu` SET `shortcut` = 'STRG + ALT + T'
WHERE `controller` = 'Cache' AND `action` = 'Template';
UPDATE `s_core_menu` SET `shortcut` = 'STRG + ALT + X'
WHERE `controller` = 'Cache' AND `action` = 'Config';
UPDATE `s_core_menu` SET `shortcut` = 'STRG + ALT + F'
WHERE `controller` = 'Cache' AND `action` = 'Frontend';

-- //@UNDO

-- //