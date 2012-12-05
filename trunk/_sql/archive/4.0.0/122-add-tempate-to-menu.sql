UPDATE `s_core_menu` SET `name` = 'Templateauswahl', `onclick` = '', `controller` = 'Template' WHERE name = 'Templateauswahl*';
-- //@UNDO
UPDATE `s_core_menu` SET `name` = 'Templateauswahl*', `onclick` = 'loadSkeleton(''templates'')', `controller` = 'Templates' WHERE name = 'Templateauswahl';
