-- //
INSERT INTO s_core_acl_resources (name) VALUES ('category');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'category'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'category'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'category'), 'update');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'category'), 'delete');

UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'category') WHERE name = 'Kategorien';

-- //@UNDO

UPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'category');
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'category');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'category');
DELETE FROM s_core_acl_resources WHERE name = 'category';

-- //
