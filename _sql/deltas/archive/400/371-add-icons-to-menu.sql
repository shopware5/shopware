-- //

UPDATE `s_core_menu` SET class='sprite-blue-folders-stack' WHERE controller='Cache' AND action='Frontend';
UPDATE `s_core_menu` SET class='sprite-gear' WHERE controller='Cache' AND action='Config';

-- //@UNDO

UPDATE `s_core_menu` SET class='ico2 bin' WHERE controller='Cache' AND action='Config';
UPDATE `s_core_menu` SET class='ico2 bin' WHERE controller='Cache' AND action='Frontend';

-- //