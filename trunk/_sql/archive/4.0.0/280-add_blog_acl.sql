-- //
INSERT INTO s_core_acl_resources (name) VALUES ('blog');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'blog'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'blog'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'blog'), 'update');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'blog'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'blog'), 'comments');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'blog') WHERE name = 'Blog';

-- //@UNDO
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'blog' Limit 1);
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'blog' Limit 1);
UPDATE s_core_menu SET resourceID = 0 WHERE name = 'blog';
DELETE FROM s_core_acl_resources WHERE name = 'blog';

--