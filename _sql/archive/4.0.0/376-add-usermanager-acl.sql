-- //

DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'user_manager');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'user_manager');
DELETE FROM s_core_acl_resources WHERE name = 'user_manager';

INSERT INTO s_core_acl_resources (name) VALUES ('usermanager');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager'), 'update');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager'), 'view');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager') WHERE controller = 'UserManager';

-- //@UNDO

DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'usermanager');
DELETE FROM s_core_acl_resources WHERE name = 'usermanager';

-- //