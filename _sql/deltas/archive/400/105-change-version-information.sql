-- //
SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
UPDATE `s_core_menu` SET `onclick` = "createShopwareVersionMessage();" WHERE `name` = "Über Shopware" AND parent = @help_parent;
UPDATE `s_core_config` SET `value` = '1745' WHERE `name` = 'sREVISION';

-- //@UNDO

SET @help_parent = (SELECT `id` FROM `s_core_menu` WHERE `class` LIKE 'ico question_frame');
UPDATE `s_core_menu` SET `onclick` = "window.Growl('{release}<br />(c)2010-2011 shopware AG');" WHERE `name` = "Über Shopware" AND parent = @help_parent;
UPDATE `s_core_config` SET `value` = '7151' WHERE `name` = 'sREVISION';

--