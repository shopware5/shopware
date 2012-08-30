INSERT INTO s_core_acl_resources (name) VALUES ('overview');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'overview'), 'read');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'overview') WHERE name = '&Uuml;bersicht';

-- //@UNDO
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'overview');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'overview');
UPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = '&Uuml;bersicht');
DELETE FROM s_core_acl_resources WHERE name = 'overview';
-- //
