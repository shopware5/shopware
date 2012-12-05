-- Related to ticket SW-824 //

DELETE FROM s_core_subscribes WHERE subscribe = 'Enlight_Bootstrap_InitResource_Acl';

-- //@UNDO

INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_Acl', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', 36, 0);


-- //
