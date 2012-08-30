UPDATE `s_core_menu` SET `name` = 'Import/Export', `onclick` = '', `controller`='ImportExport' WHERE  `name` LIKE 'Import/Export%';

-- //@UNDO

UPDATE `s_core_menu` SET `name` = 'Abbruch-Analyse*', `onclick` = 'loadSkeleton(''orderscanceled'')' WHERE  `name` LIKE 'Abbruch-Analyse%';
