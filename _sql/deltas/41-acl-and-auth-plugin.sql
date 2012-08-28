--  //

SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `name` = 'Auth');
INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_BackendSession', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession', @parent, 0),
(NULL, 'Enlight_Bootstrap_InitResource_Acl', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', @parent, 0);

-- //@UNDO

DELETE FROM `s_core_subscribes` WHERE `listener` IN (
  'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceBackendSession',
  'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl'
);

-- //