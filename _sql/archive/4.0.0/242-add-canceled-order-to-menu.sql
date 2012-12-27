UPDATE `s_core_menu` SET `name` = 'Abbruch-Analyse', `onclick` = '', `controller`='CanceledOrder' WHERE  `name` LIKE 'Abbruch-Analyse%';

-- //@UNDO

UPDATE `s_core_menu` SET `name` = 'Abbruch-Analyse*', `onclick` = 'loadSkeleton(''orderscanceled'')' WHERE  `name` LIKE 'Abbruch-Analyse%';
