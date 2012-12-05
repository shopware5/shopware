-- //
INSERT INTO s_core_acl_resources (name) VALUES ('snippet');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'snippet'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'snippet'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'snippet'), 'update');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'snippet'), 'delete');

UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'snippet') WHERE name = 'Neue Templatebasis';

-- //@UNDO
UPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'snippet');
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'snippet');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'snippet');
DELETE FROM s_core_acl_resources WHERE name = 'snippet';
--
