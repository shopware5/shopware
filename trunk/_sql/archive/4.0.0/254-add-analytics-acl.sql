-- //
INSERT INTO s_core_acl_resources (name) VALUES ('analytics');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'analytics'), 'read');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'analytics') WHERE name = 'Statistiken / Diagramme';

-- //@UNDO
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'analytics' Limit 1);
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'analytics' Limit 1);
UPDATE s_core_menu SET resourceID = 0 WHERE name = 'Statistiken / Diagramme';
DELETE FROM s_core_acl_resources WHERE name = 'analytics';

--