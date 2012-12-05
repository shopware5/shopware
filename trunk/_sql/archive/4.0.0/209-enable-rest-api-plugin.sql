-- //

INSERT INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `checkversion`, `checkdate`, `support`, `changes`, `link`) VALUES
('Core', 'RestApi', 'RestApi', 'Default', '', '', 1, '2012-07-13 12:03:13', '2012-07-13 12:03:36', '2012-07-13 12:03:36', 'shopware AG', 'Copyright © 2012, shopware AG', '', '1.0.0', '', '0000-00-00', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');

SET @pluginId = (SELECT `id` FROM `s_core_plugins` WHERE `name` = 'RestApi');

INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Front_DispatchLoopStartup', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onDispatchLoopStartup', @pluginId, 0),
('Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onFrontPreDispatch', @pluginId, 0),
('Enlight_Bootstrap_InitResource_Auth', 0, 'Shopware_Plugins_Core_RestApi_Bootstrap::onInitResourceAuth', @pluginId, 0);

UPDATE `s_core_auth` SET `apiKey` = '89e495e7941cf9e40e6980d14a16bf023ccd4c91' WHERE `username` LIKE "demo";

-- //@UNDO

SET @pluginId = (SELECT `id` FROM `s_core_plugins` WHERE `name` = 'RestApi');
DELETE FROM `s_core_subscribes` WHERE `pluginID` = @pluginId;
DELETE FROM `s_core_plugins` WHERE `id` = @pluginId;
UPDATE `s_core_auth` SET `apiKey` = NULL WHERE `username` LIKE "demo";

-- //
