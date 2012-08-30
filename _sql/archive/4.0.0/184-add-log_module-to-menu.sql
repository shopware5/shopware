UPDATE s_core_menu SET name='Logfile', onclick='', controller='Log' WHERE name='Logfile*';

-- //@UNDO

UPDATE s_core_menu SET name='Logfile*', onclick='loadSkeleton(\'authlog\')', controller='Logfile' WHERE name='Logfile';