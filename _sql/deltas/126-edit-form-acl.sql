SET @resource_id = (SELECT id FROM s_core_acl_resources WHERE name = 'form');
SET @old_priv_id = (SELECT id FROM s_core_acl_privileges WHERE resourceID = @resource_id AND name = 'update');

UPDATE s_core_acl_privileges SET name = 'createupdate' WHERE resourceID =  @resource_id AND name = 'create';
DELETE FROM s_core_acl_roles WHERE resourceID = @resource_id AND privilegeID = @old_priv_id;
DELETE FROM s_core_acl_privileges WHERE id = @old_priv_id;

-- //@UNDO