-- Add acl resources and privileges for resource risk_management //

INSERT INTO s_core_acl_resources (name) VALUES ('risk_management');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'risk_management'), 'save');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'risk_management'), 'read');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'risk_management'), 'delete');

-- //@UNDO

DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'risk_management');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'risk_management');
DELETE FROM s_core_acl_resources WHERE name = 'risk_management';

-- //