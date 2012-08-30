-- //
SET @parent = (SELECT id FROM s_core_menu WHERE name='Marketing');
UPDATE s_core_menu SET controller = NULL WHERE parent = @parent AND controller='Analytics';

-- //@UNDO

SET @parent = (SELECT id FROM s_core_menu WHERE name='Marketing');
UPDATE s_core_menu SET controller = 'Analytics' WHERE parent = @parent AND name='Auswertungen';

-- //