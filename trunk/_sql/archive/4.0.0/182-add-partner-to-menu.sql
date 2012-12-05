UPDATE `s_core_menu` SET `name` = 'Partnerprogramm', `onclick` = '' WHERE  `name` LIKE 'Partnerprogramm%';

-- //@UNDO

UPDATE `s_core_menu` SET `name` = 'Partnerprogramm*', `onclick` = 'loadSkeleton(''partner'')' WHERE  `name` LIKE 'Partnerprogramm%';
