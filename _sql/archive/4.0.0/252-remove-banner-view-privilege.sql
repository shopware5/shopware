-- Remove view privilege for resource banner, its redundant  //

DELETE FROM s_core_acl_privileges WHERE resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'banner') AND `name` = 'view';

-- //@UNDO

-- //