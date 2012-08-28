ALTER TABLE `s_user_billingaddress` CHANGE `birthday` `birthday` DATE NULL;

INSERT INTO s_core_acl_resources (name) VALUES ('order');

INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'order'), 'create');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'order'), 'update') ;
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'order'), 'delete');
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'order'), 'view');
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'order') WHERE name = 'Order';

-- //@UNDO
DELETE FROM s_core_acl_roles WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'order');
DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'order');
UPDATE s_core_menu SET resourceID = 0 WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'order');
DELETE FROM s_core_acl_resources WHERE name = 'order';


ALTER TABLE `s_user_billingaddress` CHANGE `birthday` `birthday` DATE NOT NULL;