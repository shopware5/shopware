-- //

SET @resourceID = (SELECT id FROM s_core_acl_resources WHERE name='article');
DELETE FROM s_core_acl_privileges WHERE resourceID = @resourceID AND (name ='update' OR name='create');
INSERT INTO s_core_acl_privileges (resourceID, name) VALUES (@resourceID, 'save');

-- //@UNDO

SET @resourceID = (SELECT id FROM s_core_acl_resources WHERE name='article');
DELETE FROM s_core_acl_privileges WHERE resourceID = @resourceID AND name='save';
INSERT INTO s_core_acl_privileges (resourceID, name) VALUES (@resourceID, 'create');
INSERT INTO s_core_acl_privileges (resourceID, name) VALUES (@resourceID, 'update');

-- //