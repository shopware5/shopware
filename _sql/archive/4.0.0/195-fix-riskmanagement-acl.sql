-- Add acl resources and privileges for resource risk_management //

UPDATE  s_core_acl_resources SET name='riskmanagement' WHERE name='risk_management';

-- //@UNDO

UPDATE  s_core_acl_resources SET name='risk_management' WHERE name='riskmanagement';

-- //