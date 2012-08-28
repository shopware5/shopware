/**
 * Insert sql queries for shopware 3.5.5
 */
 
SET NAMES 'latin1';

/**
 * @ticket 5716 (internal)
 * @ticket 100485 (external)
 * @author s.pohl
 * @since 3.5.5 - 2011/07/27
 */
UPDATE `s_core_snippets` SET `value` = 'Vielen Dank. Wir haben Ihnen eine Bestätigungsemail gesendet. Klicken Sie auf den enthaltenen Link um Ihre Anmeldung zu bestätigen.' WHERE `s_core_snippets`.`localeID` = 1 AND `s_core_snippets`.`name` LIKE 'sMailConfirmation';

/*
 * @ticket 5780 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/02
 */
ALTER TABLE `s_core_translations` CHANGE `objectkey` `objectkey` INT( 11 ) UNSIGNED NOT NULL;

/*
 * No Ticket - Update version info
 * @author st.hamann
 * @since 3.5.5 - 2011/08/08
 */
UPDATE `s_core_config` SET `value` = '3.5.5' WHERE `name` = 'sVERSION';
INSERT IGNORE INTO `s_core_config` (`group`, `name`, `value`)
VALUES (0, 'sREVISION', '7151')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

/*
 * @ticket 5867 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/12
 */
ALTER TABLE `s_emarketing_lastarticles` ADD `shopID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_emarketing_lastarticles`
	CHANGE `articleID` `articleID` INT( 11 ) UNSIGNED NOT NULL,
	CHANGE `userID` `userID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_emarketing_lastarticles` DROP INDEX sessionID;
ALTER TABLE `s_emarketing_lastarticles` DROP INDEX articleID;
ALTER TABLE `s_emarketing_lastarticles` ADD UNIQUE (
	`articleID`,
	`sessionID`,
	`shopID`
);

/*
 * @ticket 5867 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/16
 */
ALTER TABLE `s_articles` ADD INDEX `changetime` ( `changetime` );

/*
 * @ticket 5857 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/08/30
 */
INSERT IGNORE INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
('Backend', 'Check', 'Systeminfo', 'Default', '', '', 1, '2010-10-18 00:00:00', '2010-10-18 00:00:00', '2010-10-18 00:00:00', 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');
SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `label` = 'Systeminfo');
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Dispatcher_ControllerPath_Backend_Check', 0, 'Shopware_Plugins_Backend_Check_Bootstrap::onGetControllerPathBackend', @parent, 0);
UPDATE `s_core_menu` SET `onclick` = 'openAction(\'check\');', `pluginID` = @parent WHERE `name` = 'Systeminfo';

/*
 * @ticket 5418 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/09/05
 */
SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'USt-IdNr. Überprüfung');
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sVATCHECKCONFIRMATION', '0', 'Amtliche Bestätigungsmitteilung bei der erweiterten Überprüfung anfordern', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKVALIDRESPONSE', 'A, D', 'Gültige Ergebnisse bei der erweiterten Überprüfung', 0, 0, '', 0, '');

/*
 * @ticket 6026 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/09/08
 */
ALTER TABLE `s_core_subscribes` CHANGE `position` `position` INT( 11 ) NOT NULL;

/*
 * @ticket 5938 (internal)
 * @author h.lohaus 
 * @since 3.5.5 - 2011/09/14
 */
INSERT IGNORE INTO `s_core_plugins` (`namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `checkversion`, `checkdate`, `support`, `changes`, `link`) VALUES
('Backend', 'BusinessEssentials', 'Business Essentials', 'Default', '', '', 1, '2010-09-14 00:00:00', '2010-09-14 00:00:00', '2010-09-14 00:00:00', 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', '', '0000-00-00', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');
SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `label` = 'Business Essentials');
INSERT IGNORE INTO `s_core_subscribes` (`subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
('Enlight_Controller_Front_PreDispatch', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onPreDispatchFrontend', @parent, 10),
('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PrivateRegister', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onGetControllerPathPrivateRegister', @parent, 0),
('Enlight_Controller_Dispatcher_ControllerPath_Frontend_PrivateLogin', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onGetControllerPathPrivateLogin', @parent, 0),
('Enlight_Controller_Action_PostDispatch', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onPostDispatchFrontend', @parent, 0),
('Enlight_Controller_Action_PostDispatch_Backend_Index', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onPostDispatch', @parent, 0),
('Enlight_Controller_Dispatcher_ControllerPath_Backend_BusinessEssentialsUnlock', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onGetControllerPathUnlock', @parent, 0),
('Enlight_Controller_Dispatcher_ControllerPath_Backend_BusinessEssentials', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onGetControllerPath', @parent, 0),
('Shopware_Controllers_Frontend_Register_CustomerGroupRegister', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onStartRegisterCheckGroup', @parent, 0),
('Shopware_Modules_Admin_SaveRegisterMainData_FilterSql', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onFinishRegistrationFilterGroupField', @parent, 0),
('Enlight_Controller_Action_PostDispatch_Frontend_Register', 0, 'Shopware_Plugins_Backend_BusinessEssentials_Bootstrap::onStartRegisterController', @parent, 0);
SET @menu_parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Einstellungen');
INSERT IGNORE INTO `s_core_menu` (`parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`) VALUES
(@menu_parent, '', 'Business Essentials', 'openAction(''business_essentials'');', 'background-position: 5px 5px;', 'ico2 suit', -1, 1, @parent);
CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_cgsettings` (
  `customergroup` varchar(10) NOT NULL,
  `allowregister` tinyint(1) NOT NULL,
  `requireunlock` tinyint(1) NOT NULL,
  `assigngroupbeforeunlock` varchar(10) NOT NULL,
  `registertemplate` varchar(255) NOT NULL,
  `emailtemplatedeny` varchar(255) NOT NULL,
  `emailtemplateallow` varchar(255) NOT NULL,
  PRIMARY KEY (`customergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_tpl_config` (
  `customergroup` varchar(255) NOT NULL,
  `fieldkey` varchar(255) NOT NULL,
  `fieldvalue` varchar(255) NOT NULL,
  PRIMARY KEY (`customergroup`,`fieldkey`)
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_tpl_variables` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
 ) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 CREATE TABLE IF NOT EXISTS `s_core_plugins_b2b_private` (
  `customergroup` varchar(25) NOT NULL,
  `activatelogin` tinyint(1) NOT NULL,
  `redirectlogin` varchar(255) NOT NULL,
  `redirectregistration` varchar(255) NOT NULL,
  `registerlink` tinyint(1) NOT NULL,
  `registergroup` varchar(50) NOT NULL,
  `unlockafterregister` tinyint(1) NOT NULL,
  `templatelogin` varchar(50) NOT NULL,
  `templateafterlogin` varchar(50) NOT NULL,
  PRIMARY KEY (`customergroup`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
