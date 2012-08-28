UPDATE `s_core_menu` set name = 'Eigenschaften', controller = 'Property', onclick = '' WHERE `onclick` = 'loadSkeleton(''filter'');';

-- //@UNDO

UPDATE `s_core_menu` set name = 'Eigenschaften*', controller = '', onclick = 'loadSkeleton(''filter'');' WHERE `controller` = 'Property';
