-- //
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'productfeed') WHERE name = 'Produktexporte';
UPDATE s_core_menu SET resourceID = (SELECT id FROM s_core_acl_resources WHERE name = 'voucher') WHERE name = 'Gutscheine';
INSERT INTO s_core_acl_privileges (resourceID,name) VALUES ( (SELECT id FROM s_core_acl_resources WHERE name = 'productfeed'), 'generate');

-- //@UNDO
UPDATE s_core_menu SET resourceID = NULL WHERE name = 'Produktexporte';
UPDATE s_core_menu SET resourceID = NULL WHERE name = 'Gutscheine';
--