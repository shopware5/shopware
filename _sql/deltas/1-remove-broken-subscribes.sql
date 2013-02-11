-- SW-5202-remove-broken-subscribes

-- //

DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Core_Shop_Bootstrap::%';
DELETE FROM `s_core_subscribes` WHERE  `listener` LIKE  'Shopware_Plugins_Backend_Locale_Bootstrap::%';

-- //@UNDO

-- //


