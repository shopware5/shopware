-- //

INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(NULL, 'Backend', 'StoreApi', 'StoreApi', 'Default', NULL, NULL, 1, '2012-08-19 14:34:36', '2012-08-19 14:34:46', '2012-08-19 14:34:46', '2012-08-21 09:59:19', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, NULL),
(NULL, 'Core', 'PluginManager', 'PluginManager', 'Default', NULL, NULL, 1, '2012-08-19 14:34:36', '2012-08-19 14:34:59', '2012-08-19 14:34:59', '2012-08-21 09:59:19', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 0, 0, NULL, NULL);

UPDATE s_core_plugins SET active = 1, installation_date = '2012-08-19 14:34:46'
WHERE name = 'StoreApi' OR name = 'PluginManager';

INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_PluginManager', 0, 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetPluginController', (SELECT id FROM s_core_plugins WHERE name = 'PluginManager'), 0),
(NULL, 'Enlight_Controller_Dispatcher_ControllerPath_Backend_Store', 0, 'Shopware_Plugins_Core_PluginManager_Bootstrap::onGetStoreController', (SELECT id FROM s_core_plugins WHERE name = 'PluginManager'), 0);

INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_StoreApi', 0, 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onInitResourceStoreApi', (SELECT id FROM s_core_plugins WHERE name = 'StoreApi'), 0),
(NULL, 'Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_StoreApi_Bootstrap::onPreDispatch', (SELECT id FROM s_core_plugins WHERE name = 'StoreApi'), 0);

-- //@UNDO


-- //