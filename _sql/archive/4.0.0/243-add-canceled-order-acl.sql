-- Add acl resources and privileges for resource banner //

INSERT INTO s_core_acl_resources (name) VALUES ('canceledorder');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'canceledorder'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'canceledorder'), 'read');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'canceledorder') WHERE name = 'Abbruch-Analyse';

-- //@UNDO

DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'canceledorder');
DELETE FROM s_core_acl_resources WHERE name = 'canceledorder';

-- //