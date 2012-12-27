-- //
INSERT INTO s_core_acl_resources (name) VALUES ('notification');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'notification'), 'read');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'notification') WHERE name = 'E-Mail Benachrichtigung';

-- //@UNDO

UPDATE s_core_menu SET resourceID = 0 WHERE 0 = (SELECT id FROM s_core_acl_resources WHERE name = 'notification');
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'notification');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'notification');
DELETE FROM s_core_acl_resources WHERE name = 'notification';

-- //
