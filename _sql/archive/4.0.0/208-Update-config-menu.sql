-- //

UPDATE `s_core_menu` SET `controller` = 'Config', `action` = 'Template' WHERE `controller` = 'Template';
UPDATE `s_core_menu` SET `controller` = 'Config', `action` = 'Country' WHERE `controller` = 'Countries';
UPDATE `s_core_menu` SET `controller` = 'Config', `action` = 'Tax' WHERE `controller` = 'Tax';

-- //@UNDO


-- //