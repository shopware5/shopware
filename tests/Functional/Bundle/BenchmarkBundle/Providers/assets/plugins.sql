SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_core_plugins;

INSERT INTO `s_core_plugins` (`namespace`, `name`, `label`, `version`, `update_version`, `author`, `source`, `description`, `translations`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `copyright`, `license`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `capability_secure_uninstall`, `in_safe_mode`) VALUES
    ('ShopwarePlugins', 'SwagExample1', 'Swag Example 1', '1.0.0', NULL, 'some company', 'Local', NULL, NULL, NULL, 0, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
    ('ShopwarePlugins', 'SwagExample2', 'Swag Example 2', '1.0.0', NULL, 'shopware AG', 'Local', NULL, NULL, NULL, 0, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
    ('ShopwarePlugins', 'SwagExample3', 'Swag Example 3', '1.0.0', '1.0.1', 'shopware AG', 'Local', NULL, NULL, NULL, 1, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
    ('ShopwarePlugins', 'SwagExample4', 'Swag Example 4', '1.0.0', '1.1.0', 'shopware AG', 'Local', NULL, NULL, NULL, 1, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
    ('ShopwarePlugins', 'SwagExample5', 'Swag Example 5', '1.0.0', '0.1.0', 'shopware AG', 'Local', NULL, NULL, NULL, 0, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0),
    ('ShopwarePlugins', 'SwagExample6', 'Swag Example 6', '1.0.0', '1.0.0', 'some company', 'Local', NULL, NULL, NULL, 0, '2018-03-12 09:30:00', NULL, NULL, '2018-03-12 09:35:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, 0, 0);

SET FOREIGN_KEY_CHECKS=1;
