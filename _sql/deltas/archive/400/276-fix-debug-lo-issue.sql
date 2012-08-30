-- //

SET @parent = (SELECT id FROM `s_core_plugins` WHERE `name`='Log');
INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_DispatchLoopShutdown', 0, 'Shopware_Plugins_Core_Log_Bootstrap::onDispatchLoopShutdown', @parent, 500);

-- //@UNDO

-- //