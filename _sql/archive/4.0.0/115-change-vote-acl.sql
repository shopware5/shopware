UPDATE s_core_acl_privileges SET name = 'accept' WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'vote') AND name = 'answer';

-- //@UNDO

UPDATE s_core_acl_privileges SET name = 'answer' WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'vote') AND name = 'accept';

-- //