UPDATE s_core_menu SET `name` = 'Kategorien', onclick = '', `controller` = 'Category' WHERE `s_core_menu`.`name` ='Kategorien*';

-- //@UNDO
UPDATE s_core_menu SET `name` = 'Kategorien*', onclick = 'loadSkeleton(''categories'');', controller = '' WHERE `s_core_menu`.`name` ='Kategorien';
