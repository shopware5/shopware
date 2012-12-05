-- Add acl resources and privileges for resource newsletter //

INSERT INTO s_core_acl_resources (name) VALUES ('newslettermanager');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'newslettermanager'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'newslettermanager'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'newslettermanager'), 'write');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'newslettermanager') WHERE name = 'Newsletter' AND controller = 'NewsletterManager';

-- //@UNDO

DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'newslettermanager');
DELETE FROM s_core_acl_resources WHERE name = 'newslettermanager';

-- //