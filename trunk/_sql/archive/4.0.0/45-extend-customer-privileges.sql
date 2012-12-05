INSERT INTO s_core_acl_privileges (resourceID, name) VALUES ((SELECT id FROM s_core_acl_resources WHERE name='customer'), 'detail');
INSERT INTO s_core_acl_privileges (resourceID, name) VALUES ((SELECT id FROM s_core_acl_resources WHERE name='customer'), 'perform_order');
-- //@UNDO
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name='customer') AND name ='perform_order';
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name='customer') AND name ='detail';

