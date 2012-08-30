-- change acl customer privilege from view to read //
UPDATE `s_core_acl_privileges` SET name = 'read' WHERE `name` = 'view' AND `resourceID` = (SELECT id FROM s_core_acl_resources WHERE name = 'customer');
-- //@UNDO
UPDATE `s_core_acl_privileges` SET name = 'view' WHERE `name` = 'read' AND `resourceID` = (SELECT id FROM s_core_acl_resources WHERE name = 'customer');
