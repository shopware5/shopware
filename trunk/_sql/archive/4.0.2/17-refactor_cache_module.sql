-- //

DELETE FROM `s_core_menu` WHERE `name` = 'Proxy/Model-Cache';

DELETE FROM `s_core_menu` WHERE `name` = 'Konfiguration';

UPDATE `s_core_menu` SET `name` = 'Konfiguration + Template', `action` = 'Config', `shortcut` = 'STRG + ALT + X'  WHERE `name` = 'Textbausteine + Template';

UPDATE `s_core_menu` SET `action` = 'Frontend', `shortcut` = 'STRG + ALT + F' WHERE `name` = 'Artikel + Kategorien';



-- //@UNDO


-- //