INSERT INTO s_core_acl_resources (name) VALUES ('systeminfo');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'systeminfo'), 'read');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'systeminfo') WHERE name = 'Systeminfo';

-- //@UNDO

DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'systeminfo');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'systeminfo');
UPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'systeminfo');
DELETE FROM s_core_acl_resources WHERE name = 'systeminfo';

-- //