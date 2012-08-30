DELETE
FROM `s_core_plugins`
WHERE `source` = 'Default'
AND `name` IN (
 'Modules', 'Acl', 'Template',
 'ViewportDispatcher', 'HttpCache', 'Locale'
);

DELETE sb
FROM `s_core_subscribes` sb
LEFT JOIN `s_core_plugins` p
ON p.id=pluginID
WHERE p.id IS NULL;

INSERT INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
('Core', 'HttpCache', 'HttpCache', 'Default', '', '', 1, NOW(), NOW(), NOW(), 'shopware AG', 'Copyright &copy; 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');

SET @parent = (SELECT id FROM s_core_plugins WHERE name='HttpCache');

INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Core_HttpCache_Bootstrap::onPreDispatch', @parent, 0);


INSERT INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
('Backend', 'Locale', 'Locale', 'Default', '', '', 1, NOW(), NOW(), NOW(), 'shopware AG', 'Copyright &copy; 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');

SET @parent = (SELECT id FROM s_core_plugins WHERE name='Locale');

INSERT INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Action_PreDispatch', 0, 'Shopware_Plugins_Backend_Locale_Bootstrap::onPreDispatchBackend', @parent, 0);

-- //@UNDO

