-- Release.sql for Shopware 3.5.4

/*
 * @ticket 4847
 * @author h.lohaus 
 * @since 3.5.4 - 2011/03/22
 */

UPDATE `s_core_config` SET `description` = 'Method to send mail: ("mail", "smtp" or "file").' WHERE `name`='sMAILER_Mailer';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Mailer');
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sMAILER_Auth', '', 'Sets connection auth. Options are "", "plain",  "login" or "crammd5"', 0, 0, '', 1, '');

/*
 * @ticket 5258
 * @author h.lohaus 
 * @since 3.5.4 - 2011/03/30
 */
DELETE FROM `s_core_snippets` WHERE `namespace` LIKE '/%' OR `namespace` LIKE 'templates/%';
UPDATE `s_core_snippets` SET `shopID` = 1 WHERE `shopID` = 0;

INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'backend/index/menu', 1, 2, 'Alle schliessen', 'Close all', '2011-03-31 11:47:42', '2011-03-31 11:47:42'),
(NULL, 'backend/index/menu', 1, 2, 'Anlegen', 'New', '2011-03-31 11:48:05', '2011-03-31 11:48:56'),
(NULL, 'backend/index/menu', 1, 2, 'Artikel', 'Products', '2011-03-31 11:49:30', '2011-04-01 11:42:15'),
(NULL, 'backend/index/menu', 1, 2, 'Artikel + Kategorien', 'Products + Categories', '2011-03-31 11:50:05', '2011-03-31 11:50:05'),
(NULL, 'backend/index/menu', 1, 2, 'Einstellungen', 'Settings', '2011-03-31 11:50:26', '2011-03-31 11:50:26'),
(NULL, 'backend/snippet/skeleton', 1, 2, 'WindowTitle', 'Textbausteine', '2011-04-01 11:33:58', '2011-04-01 11:33:58'),
(NULL, 'backend/auth/login_panel', 1, 2, 'UserNameField', 'User', '2011-04-01 11:34:47', '2011-04-01 11:36:30'),
(NULL, 'backend/auth/login_panel', 1, 2, 'PasswordMessage', 'Please enter a password!', '2011-04-01 11:35:29', '2011-04-01 11:36:08'),
(NULL, 'backend/auth/login_panel', 1, 2, 'UserNameMessage', 'Please enter a user name!', '2011-04-01 11:35:57', '2011-04-01 11:36:28'),
(NULL, 'backend/index/index', 1, 2, 'SearchLabel', 'Search', '2011-04-01 11:37:50', '2011-04-01 11:39:30'),
(NULL, 'backend/index/index', 1, 2, 'AccountMissing', 'No account created!', '2011-04-01 11:38:03', '2011-04-01 11:39:25'),
(NULL, 'backend/index/index', 1, 2, 'UserLabel', 'User: {$UserName}', '2011-04-01 11:38:20', '2011-04-01 11:39:31'),
(NULL, 'backend/index/index', 1, 2, 'LiveViewLabel', 'Shop view', '2011-04-01 11:38:40', '2011-04-01 11:39:26'),
(NULL, 'backend/index/index', 1, 2, 'AccountBalance', 'Balance: {$Amount} SC', '2011-04-01 11:38:57', '2011-04-01 11:39:24'),
(NULL, 'backend/index/menu', 1, 2, 'Fenster', 'Window', '2011-04-01 11:39:53', '2011-04-01 11:40:07'),
(NULL, 'backend/index/menu', 1, 2, 'Inhalte', 'Content', '2011-04-01 11:40:43', '2011-04-01 11:40:47'),
(NULL, 'backend/index/menu', 1, 2, 'Hilfe', 'Help', '2011-04-01 11:41:03', '2011-04-01 11:41:08'),
(NULL, 'backend/index/menu', 1, 2, 'Kunden', 'Customers', '2011-04-01 11:41:58', '2011-04-01 11:42:04'),
(NULL, 'backend/auth/login_panel', 1, 2, 'LoginButton', 'Login', '2011-04-01 11:37:09', '2011-04-01 11:37:09'),
(NULL, 'backend/auth/login_panel', 1, 2, 'LocaleField', 'Language', '2011-04-01 11:37:32', '2011-04-01 11:37:32'),
(NULL, 'backend/auth/login_panel', 1, 2, 'PasswordField', 'Password', '2011-04-01 11:37:32', '2011-04-01 11:37:32');

/*
 * @ticket 4778
 * @author h.lohaus 
 * @since 3.5.4 - 2011/04/01
 */
ALTER TABLE `s_core_currencies` ADD `symbol_position` INT( 11 ) UNSIGNED NOT NULL AFTER `templatechar`;

/*
 * @ticket 5068
 * @author h.lohaus 
 * @since 3.5.4 - 2011/04/12
 */
UPDATE `s_core_menu` SET `style` = 'background-position: 5px 5px;' WHERE `name` = 'Textbausteine';
UPDATE `s_core_config` SET `value` = '3.5.4' WHERE `name` = 'sVERSION';

/*
 * @ticket 4836
 * @author st.hamann
 * @since 3.5.4 - 2011/05/18
 */
INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/account/password', 1, 1, 'PasswordSendAction', 'Passwort anfordern', '2011-05-17 11:47:42', '2011-05-17 11:47:42');

/*
 * @ticket 5125
 * @author h.lohaus 
 * @since 3.5.4 - 2011/05/18
 */
UPDATE `s_core_config_mails` SET `name` = TRIM(`name`);

/*
 * @ticket 5125
 * @author h.lohaus 
 * @since 3.5.4 - 2011/05/18
 */
DELETE FROM `s_core_subscribes` WHERE `listener` LIKE 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::%';
INSERT INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Controller_Front_RouteShutdown', 0, 'Shopware_Plugins_Frontend_InputFilter_Bootstrap::onRouteShutdown', 35, -100);
INSERT IGNORE INTO `s_core_plugin_configs` (`id`, `name`, `value`, `pluginID`, `localeID`, `shopID`) VALUES
(NULL, 'rfi_protection', 's:1:"1";', 35, 1, 1),
(NULL, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 35, 1, 1);
INSERT IGNORE INTO `s_core_plugin_elements` (`id`, `pluginID`, `name`, `value`, `label`, `description`, `type`, `required`, `order`, `scope`, `filters`, `validators`) VALUES
(NULL, 35, 'rfi_protection', 'i:1;', 'RemoteFileInclusion-Schutz aktivieren', '', 'Text', 0, 0, 0, NULL, NULL),
(NULL, 35, 'rfi_regex', 's:33:"\\.\\./|\\0|2\\.2250738585072011e-308";', 'RemoteFileInclusion-Filter', '', 'Text', 0, 0, 0, NULL, NULL);

/*
 * @ticket 4708
 * @author st.hamann
 * @since 3.5.4 - 2011/05/21
 */
ALTER TABLE `s_emarketing_vouchers` ADD `taxconfig` VARCHAR( 15 ) NOT NULL;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
INSERT INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/listing/box_article', 1, 1, 'ListingBoxArticleContent', 'Inhalt', '2011-05-24 10:31:14', '2011-05-24 10:31:47'),
(NULL, 'frontend/listing/box_article', 1, 1, 'ListingBoxBaseprice', 'Grundpreis', '2011-05-24 10:33:36', '2011-05-24 10:33:55'),
(NULL, 'frontend/note/item', 1, 1, 'NoteUnitPriceContent', 'Inhalt', '2011-05-24 11:25:13', '2011-05-24 11:26:33'),
(NULL, 'frontend/note/item', 1, 1, 'NoteUnitPriceBaseprice', 'Grundpreis', '2011-05-24 11:25:13', '2011-05-24 11:26:46'),
(NULL, 'frontend/compare/col', 1, 1, 'CompareContent', 'Inhalt', '2011-05-24 11:51:10', '2011-05-24 11:51:36'),
(NULL, 'frontend/compare/col', 1, 1, 'CompareBaseprice', 'Grundpreis', '2011-05-24 11:51:10', '2011-05-24 11:51:46'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoContent', 'Inhalt', '2011-05-24 13:11:55', '2011-05-24 13:51:56'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(NULL, 'frontend/account/order_item', 1, 1, 'OrderItemInfoCurrentPrice', 'Aktueller Einzelpreis', '2011-05-24 14:22:31', '2011-05-24 14:22:59'),
(NULL, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoBaseprice', 'Grundpreis', '2011-05-24 13:11:55', '2011-05-24 13:52:14'),
(NULL, 'frontend/plugins/recommendation/slide_articles', 1, 1, 'SlideArticleInfoContent', 'Inhalt', '2011-05-24 14:22:31', '2011-05-24 14:22:59');

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
UPDATE `s_core_snippets`
SET `value` = '- Bestellen Sie f&uuml;r weitere {$sShippingcostsDifference|currency} um Ihre Bestellung versandkostenfrei in {$sCountry.countryname} zu erhalten!' WHERE `s_core_snippets`.`name` LIKE 'CartInfoFreeShippingDifference' AND `s_core_snippets`.`localeID` = 1;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
UPDATE `s_core_snippets` SET `value` = '<a title="Mehr Informationen zu {config name=Shopname}" href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank"> {config name=Shopname} ist ein von Trusted Shops gepr&uuml;fter Onlineh&auml;ndler mit G&uuml;tesiegel und <a href="http://www.trustedshops.de/info/garantiebedingungen/" target="_blank">K&auml;uferschutz.</a> <a title="Mehr Informationen zu " href="http://www.trustedshops.de/profil/_{config name=TSID}.html" target="_blank">Mehr...</a> </a>' WHERE `s_core_snippets`.`name` LIKE 'WidgetsTrustedLogoText' AND `s_core_snippets`.`localeID` = 1;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/24
 */
INSERT IGNORE INTO `s_core_snippets` (`id`, `namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
(NULL, 'frontend/register/personal_fieldset', 1, 1, 'RegisterPersonalRequiredText', '* hierbei handelt es sich um ein Pflichtfeld', '2011-05-24 17:12:28', '2011-05-24 17:13:52');

/**
 * @ticket 4734
 * @author st.hamann
 * @since 3.5.4 - 2011/05/24
 */
ALTER TABLE `s_core_plugins` ADD `added` DATETIME NOT NULL AFTER `active`;
ALTER TABLE `s_core_plugin_elements` ADD `options` TEXT NOT NULL;

ALTER TABLE `s_core_plugins` ADD `checkversion` VARCHAR( 255 ) NOT NULL AFTER `version` ,
ADD `checkdate` DATE NOT NULL AFTER `checkversion`;

/**
 * @ticket 4766
 * @author h.lohaus
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_config` SET `value` = '1' WHERE `name` = 'sDISABLECACHE';

/**
 * @ticket 4354
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_snippets` SET `value` = 'Versandinfo:' WHERE `s_core_snippets`.`name` LIKE 'DispatchHeadNotice' AND `s_core_snippets`.`localeID` = 1;
UPDATE `s_core_snippets` SET `value` = 'Dispatch info:' WHERE `s_core_snippets`.`name` LIKE 'DispatchHeadNotice' AND `s_core_snippets`.`localeID` = 2;
UPDATE `s_core_snippets` SET `value` = 'Login' WHERE `s_core_snippets`.`name` LIKE 'AccountLoginTitle' AND `s_core_snippets`.`localeID` = 1;
UPDATE `s_core_snippets` SET `value` = 'Login' WHERE `s_core_snippets`.`name` LIKE 'AccountLoginTitle' AND `s_core_snippets`.`localeID` = 2;

/**
 * @ticket 4842
 * @author st.hamann
 * @since 3.5.4 - 2011/05/26
 */
INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, '35', 'sTAXAUTOMODE', '1', 'Steuer für Rabatte dynamisch feststellen', '0', '0', '', '1', 'int');

/**
 * @ticket 4226
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/03
 */
INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_Acl', 0, 'Shopware_Plugins_Backend_Auth_Bootstrap::onInitResourceAcl', 36, 0);

/**
 * @ticket 5089
 * @author st.hamann
 * @since 3.5.4 - 2011/06/04
 */
CREATE TABLE IF NOT EXISTS `s_plugin_benchmark_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `datum` datetime NOT NULL,
  `hash` varchar(255) NOT NULL,
  `query` text NOT NULL,
  `parameters` text NOT NULL,
  `time` float NOT NULL,
  `ipaddress` varchar(24) NOT NULL,
  `route` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `datum` (`datum`),
  KEY `session` (`session`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=418 ;

/**
 * @ticket 5427
 * @author st.hamann
 * @since 3.5.4 - 2011/06/06
 */
ALTER TABLE `s_core_auth` ADD `failedlogins` INT NOT NULL ,
ADD `lockeduntil` DATETIME NOT NULL;

ALTER TABLE `s_user` ADD `failedlogins` INT NOT NULL ,
ADD `lockeduntil` DATETIME NOT NULL;

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` = 'sonstige');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`, `created`, `modified`, `locale`, `namespace`) VALUES
(NULL, @parent, 'sErrorLoginLocked', 'Zu viele fehlgeschlagene Versuche. Ihr Account wurde vorübergehend deaktivert - bitte probieren Sie es in einigen Minuten erneut!', '', NULL, NULL, 'de_DE', 'Frontend');

/**
 * @ticket 5124
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/07
 */
ALTER TABLE `s_core_paymentmeans` ADD `action` VARCHAR( 255 ) NULL,
ADD `pluginID` INT( 11 ) UNSIGNED NULL;

INSERT IGNORE INTO `s_core_plugins` (`id`, `namespace`, `name`, `label`, `source`, `description`, `description_long`, `active`, `added`, `installation_date`, `update_date`, `autor`, `copyright`, `license`, `version`, `support`, `changes`, `link`) VALUES
(NULL, 'Frontend', 'Payment', 'Payment', 'Default', '', '', 1, '0000-00-00 00:00:00', '2011-05-11 14:06:17', '2011-05-11 14:06:17', 'shopware AG', 'Copyright © 2011, shopware AG', '', '1.0.0', 'http://wiki.shopware.de', '', 'http://www.shopware.de/');

SET @parent = (SELECT `id` FROM `s_core_plugins` WHERE `name` = 'Payment');

INSERT IGNORE INTO `s_core_subscribes` (`id`, `subscribe`, `type`, `listener`, `pluginID`, `position`) VALUES
(NULL, 'Enlight_Bootstrap_InitResource_Payments', 0, 'Shopware_Plugins_Frontend_Payment_Bootstrap::onInitResourcePayments', @parent, 0);
