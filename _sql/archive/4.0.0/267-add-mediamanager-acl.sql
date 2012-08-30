-- //
INSERT INTO s_core_acl_resources (name) VALUES ('mediamanager');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager'), 'upload');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager'), 'update');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager') WHERE name = 'Medienverwaltung';

-- //@UNDO
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager' Limit 1);
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'mediamanager' Limit 1);
UPDATE s_core_menu SET resourceID = 0 WHERE name = 'Medienverwaltung';
DELETE FROM s_core_acl_resources WHERE name = 'mediamanager';

--