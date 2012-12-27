UPDATE s_core_menu SET onclick='', controller='Customer', action='Detail', name='Anlegen' WHERE name='Anlegen*' AND class='sprite-user--plus';

-- //@UNDO

UPDATE s_core_menu SET onclick='loadSkeleton(''useradd'');', controller='AddUser', action=NULL, name='Anlegen*' WHERE name='Anlegen' AND class='sprite-user--plus';
