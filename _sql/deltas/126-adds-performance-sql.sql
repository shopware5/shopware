-- //


INSERT IGNORE INTO `s_core_config_elements`
  (`name`, `value`, `label`, `description`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`)
VALUES
('topSellerActive', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerValidationTime', 'i:100;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerRefreshStrategy', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('topSellerPseudoSales', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('seoRefreshStrategy', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('searchRefreshStrategy', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('showSupplierInCategories', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, ''),
('propertySorting', 'i:1;', '', '', '', 1, 0, 0, NULL, NULL, '');


-- //@UNDO

-- //
