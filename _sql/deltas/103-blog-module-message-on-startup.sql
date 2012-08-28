-- //

UPDATE `s_core_menu` SET `onclick` = "createBetaMessage();" WHERE `class` = "sprite-application-blog";

-- //@UNDO

UPDATE `s_core_menu` SET `onclick` = "openNewModule('Shopware.apps.Site');" WHERE `class` = "sprite-application-blog";

--