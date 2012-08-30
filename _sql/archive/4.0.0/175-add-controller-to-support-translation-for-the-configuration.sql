UPDATE `s_core_menu`
SET `controller` = 'ConfigurationMenu'
WHERE `class` = 'ico2 wrench_screwdriver';

-- //@UNDO

UPDATE `s_core_menu`
SET `controller` = NULL
WHERE `class` = 'ico2 wrench_screwdriver';
