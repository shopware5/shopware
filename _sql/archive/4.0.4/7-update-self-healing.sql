

-- //

DELETE FROM s_core_plugins WHERE name = 'SelfHealing';

INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `refresh_date`, `author`, `copyright`, `license`, `version`, `support`, `changes`, `link`, `store_version`, `store_date`, `capability_update`, `capability_install`, `capability_enable`, `update_source`, `update_version`) VALUES
(NULL, 'Core', 'SelfHealing', 'SelfHealing', 'Default', NULL, NULL, 1, '2012-10-16 12:13:54', '2012-10-16 14:07:23', '2012-10-16 14:07:23', '2012-10-16 14:07:23', 'shopware AG', 'Copyright © 2012, shopware AG', NULL, '1.0.0', NULL, NULL, NULL, NULL, NULL, 1, 1, 1, NULL, NULL);

SET @parent = (SELECT id FROM s_core_plugins WHERE name='SelfHealing');

DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';
DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100),
(NULL, 'Enlight_Controller_Front_PostDispatch', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100),
(NULL, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Core_SelfHealing_Bootstrap::onDispatchEvent', @parent, 100);

-- //@UNDO

DELETE FROM s_core_plugins WHERE name = 'SelfHealing';
DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';
DELETE FROM s_core_subscribes WHERE listener LIKE 'Shopware_Plugins_Core_SelfHealing_Bootstrap%';
-- //


