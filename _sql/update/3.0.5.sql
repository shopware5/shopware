-- Hier alle Änderungen für 3.0.4.1 auf 3.0.5 --

CREATE TABLE IF NOT EXISTS `s_articles_bundles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `articleID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `rab_type` varchar(255) NOT NULL,
  `taxID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  `max_quantity_enable` INT( 11 ) unsigned NOT NULL,
  `max_quantity` int(11) unsigned NOT NULL,
  `valid_from` date NOT NULL,
  `valid_to` date NOT NULL,
  `datum` datetime NOT NULL,
  `customergroups` text NOT NULL,
  `sells` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_articles_bundles_articles` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `bundleID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bundleID_2` (`bundleID`,`ordernumber`),
  KEY `bundleID` (`bundleID`,`ordernumber`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_articles_bundles_prices` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `bundleID` int(11) unsigned NOT NULL,
  `customergroup` varchar(255) NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bundleID_2` (`bundleID`,`customergroup`),
  KEY `bundleID` (`bundleID`,`customergroup`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_articles_bundles_stint` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `bundleID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bundleID` (`bundleID`,`ordernumber`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


ALTER TABLE `s_order_basket` ADD `bundleID` INT( 11 ) unsigned NOT NULL;
ALTER TABLE `s_order_basket` ADD `bundle_join_ordernumber` VARCHAR( 255 ) NOT NULL ;

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Warenkorb / Artikeldetails');

INSERT IGNORE INTO `s_core_config` (`group`, `name`, `value`, `description` )
VALUES (@parent, 'sSHOWBUNDLEMAINARTICLE', 1, 'Hauptartikel im Bundle anzeigen');

ALTER TABLE `s_articles` ADD `crossbundlelook` INT( 1 ) unsigned NOT NULL;

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` = 'basket');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sBasketBundleDiscountText', 'BUNDLE RABATT', 'BUNDLE RABATT');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sBundleHeadline', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln:', 'Kaufen Sie diesen Artikel...');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sRelatedBundleHeadline', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln:', 'Kaufen Sie diesen Artikel...');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sBundleDiscountPrefix', 'Statt', 'Statt');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sBundleDiscountPostfix', '% Rabatt', '% Rabatt');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sBundlePriceForAll', 'Preis für alle:', 'Preis für alle:');

-- Ticket #XXXX  --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'System');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`) VALUES
(NULL, @parent, 'sCAPTCHACOLOR', '0,0,255', 'Schriftfarbe Captcha (R,G,B)', 1, 1, '', 0);

-- Ticket #2481 --

ALTER TABLE `s_premium_shippingcosts` CHANGE `factor` `factor` DECIMAL( 10, 2 ) NOT NULL;

UPDATE `s_premium_holidays` SET `calculation` = 'DATE(''12/24'')', `date` = '2000-01-01' WHERE `name` = 'Heiligabend';
UPDATE `s_premium_holidays` SET `calculation` = 'DATE(''12/25'')', `date` = '2000-01-01' WHERE `name` = '1. Weihnachtstag';
UPDATE `s_premium_holidays` SET `calculation` = 'DATE(''12/26'')', `date` = '2000-01-01' WHERE `name` = '2. Weihnachtstag (Stephanstag)';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Rabatte / Zuschläge');

INSERT IGNORE INTO `s_core_config` (`group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`) VALUES
(@parent, 'sSHIPPINGDISCOUNTNUMBER', 'SHIPPINGDISCOUNT', 'Abschlag-Versandregel (Bestellnummer)', 0, 0, '', 1),
(@parent, 'sSHIPPINGDISCOUNTNAME', 'Warenkorbrabatt', 'Abschlag-Versandregel (Bezeichnung)', 0, 0, '', 1);

-- Ticket #2599 --

ALTER TABLE `s_articles_groups_templates` CHANGE `object` `object` LONGTEXT NOT NULL;

-- Ticket #1742 --

ALTER TABLE `s_emarketing_banners` CHANGE `valid_from` `valid_from` DATETIME NOT NULL DEFAULT '0000-00-00';
ALTER TABLE `s_emarketing_banners` CHANGE `valid_to` `valid_to` DATETIME NOT NULL DEFAULT '0000-00-00';

-- Ticket #XXXX  --

UPDATE `s_core_config` SET `value` =  'cleanup : true, language: ''de'',skin : ''o2k7'',skin_variant : ''silver'', convert_urls : false, fullscreen_new_window: true, relative_urls : false, width: "100%", invalid_elements:''script,applet,iframe'', theme_advanced_resizng : true, theme_advanced_toolbar_location : ''top'', theme_advanced_toolbar_align : ''left'', theme_advanced_path_location : ''bottom'', theme_advanced_resizing : true, extended_valid_elements : "font[size],script[src|type],object[width|height|classid|codebase|ID|value],param[name|value],embed[name|src|type|wmode|width|height|style|allowScriptAccess|menu|quality|pluginspage]"'  WHERE `s_core_config`.`name` = 'sTINYMCEOPTIONS' LIMIT 1 ;

-- Ticket #2730 --

ALTER TABLE `s_core_config` ADD `fieldtype` VARCHAR( 255 ) NOT NULL;

UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSHOWLASTARTICLES';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sIMAGESIZES';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sORDERTABLE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSHOWCLOUD';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sOPTIMIZEURLS';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sUSESSL';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sCATEGORYTEMPLATES';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sCAMPAIGNSPOSITIONS';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sVOTEDISABLE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sVOTEUNLOCK';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSHOPWAREMANAGEDCUSTOMERNUMBERS';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sIGNOREAGB';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sCOUNTRYSHIPPING';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sHIDESTART';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sTEMPLATEDEBUG';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sNO_ORDER_MAIL';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sHIGHPERFCACHE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sBASKETSHIPPINGINFO';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sCMSPOSITIONS';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sBACKENDAUTOORDERNUMBER';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sUSEZOOMPLUS';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sIGNORESHIPPINGFREEFORSURCHARGES';
UPDATE s_core_config SET fieldtype = 'textarea' WHERE name = 'sBOTBLACKLIST';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sADODB_LOG';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sDONTGZIP';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSEND_CONFIRM_MAIL';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sTICKETNOTIFYEMAIL';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sTICKETEMAILMATCH';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sTICKETNOTIFYMAILCOSTUMER';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sPREMIUMSHIPPIUNGNOORDER';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sPREMIUMSHIPPIUNG';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sOPTINNEWSLETTER';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sOPTINVOTE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sTICKETSIDEBAR';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sACTDPRCHECK';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSETOFFLINE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sLIVEINSTOCK';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sDELETECACHEAFTERORDER';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sDEACTIVATENOINSTOCK';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sREALCACHE';
UPDATE s_core_config SET fieldtype = 'int' WHERE name = 'sSHOWBUNDLEMAINARTICLE';
	
-- Ticket #2631 --

ALTER TABLE `s_articles_img` ADD `extension` VARCHAR( 255 ) NOT NULL;
UPDATE s_articles_img SET extension = 'jpg' WHERE extension = '';

-- Ticket #2589 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Intelligente Suche');

INSERT IGNORE INTO `s_core_config` (`group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`) VALUES
(@parent, 'sFUZZYSEARCHPRICEFILTER', '5|10|20|50|100|300|600|1000|1500|2500|3500|5000', 'Auswahl Preisfilter', 0, 0, '', 0),
(@parent, 'sFUZZYSEARCHSELECTPERPAGE', '12|24|36|48', 'Auswahl Ergebnisse pro Seite', 0, 0, '', 0),
(@parent, 'sFUZZYSEARCHRESULTSPERPAGE', '12', 'Ergebnisse pro Seite', 0, 0, '', 0);

-- Ticket #2139 --

UPDATE `s_core_config_groups`
SET `description` = '<img src="../../connectors/clickandbuy/images/clickandbuy_logo1.gif" width="408" height="90"> <br>Mehr Geld verdienen und mehr Umsatz machen! <br>Sicher, schnell und einfach Zahlungen empfangen! <br>Kostenlose Anmeldung, Keine Grundgebühr! <br><a target="_blank" href="http://www.clickandbuy.com/DE/de/merchantportal/home.html"><strong>Mehr Infos zu ClickandBuy!</strong></a> <br><a target="_blank" href="../../connectors/clickandbuy/mreg.php"><strong>ClickandBuy Registrierung!</strong></a><br> <br /> <strong>Achtung! Diese Zahlungsart bietet keine Subshop-Unterstützung. Sie müssen die Zahlungart also per Risk-Management auf den zu verwendenen Shop beschränken!</strong>'
WHERE `name` = 'ClickandBuy' LIMIT 1;

-- Ticket #2695 --

CREATE TABLE IF NOT EXISTS `s_campaigns_maildata` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `email` varchar(255) NOT NULL,
  `groupID` int(11) unsigned NOT NULL,
  `salutation` varchar(255) default NULL,
  `title` varchar(255) default NULL,
  `firstname` varchar(255) default NULL,
  `lastname` varchar(255) default NULL,
  `street` varchar(255) default NULL,
  `streetnumber` varchar(255) default NULL,
  `zipcode` varchar(255) default NULL,
  `city` varchar(255) default NULL,
  `added` datetime NOT NULL,
  `deleted` datetime default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `email` (`email`,`groupID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Ticket  #2696 --

ALTER TABLE `s_articles` ADD `notification` INT( 1 ) unsigned NOT NULL COMMENT 'send notification';

INSERT IGNORE INTO `s_core_config_mails` (`id`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `htmlable`, `attachment`) VALUES
(NULL, 'sARTICLEAVAILABLE', 'info@example.com', 'Shopware 3.0 Demo', 'Ihr Artikel ist wieder verfügbar', 'Hallo, \r\n\r\nIhr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar. \r\n\r\n{$sArticleLink} \r\n\r\nViele Grüße Ihr Shopware 3.0 Demo Team ', '', 0, 0, ''),
(NULL, 'sACCEPTNOTIFICATION', 'info@example.com', 'Shopware 3.0 Demo', 'Bitte bestätigen Sie Ihre E-Mail-Benachrichtigung', 'Hallo, \r\n\r\nvielen Dank, dass Sie sich für die automatische e-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben. \r\nBitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link: \r\n\r\n{$sConfirmLink} \r\n\r\nViele Grüße Ihr Shopware 3.0 Demo Team ', '', 0, 0, '');

CREATE TABLE IF NOT EXISTS `s_articles_notification` (
  `id` INT(11) unsigned NOT NULL auto_increment,
  `ordernumber` VARCHAR(255) NOT NULL,
  `date` DATETIME NOT NULL,
  `mail` VARCHAR(255) NOT NULL,
  `send` INT(1) unsigned NOT NULL,
  `language` VARCHAR(255) NOT NULL,
  `shopLink` VARCHAR(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

SET @parent = (SELECT `id` FROM `s_core_engine_groups` WHERE `group` LIKE 'Einstellungen');

INSERT IGNORE INTO `s_core_engine_elements` (`id`, `group`, `domname`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `version`, `availablebyvariants`, `help`, `multilanguage`) 
VALUES (NULL, @parent, 'notification', '', 'boolean', 'eMail-Benachrichtigung, wenn nicht auf Lager', '0', '3', 'notification', '', '0', '0', 'Benachrichtigungsfunktion einblenden, wenn Artikel nicht verfügbar', '0');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` LIKE 'articles');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sRegisterForNotification', 'Benachrichtigen Sie mich, wenn der Artikel lieferbar ist.', 'Benachrichtigen Sie mich, wenn der Artikel wieder vorhanden ist.'),
(@parent, 'sRegisterForNotificationValid', 'Vielen Dank!\r\n\r\nWir haben Ihre Anfrage gespeichert!\r\nSie werden benachrichtigt sobald der Artikel wieder verfügbar ist.\r\n\r\n', 'Sie werden Benachrichtigt sobald der Artikel wieder verfügbar ist.'),
(@parent, 'sRegisterNotificationInValid', 'Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.', 'Bei der Validierung Ihrer E-Mail-Benachrichtigung ist ein Fehler aufgetreten.'),
(@parent, 'sArticleNotificationSend', 'Bestätigen Sie den Link der', 'Bestätigen Sie den Link der eMail die Sie gerade erhalten haben. Sie erhalten dann eine eMail sobald der Artikel wieder verfügbar ist');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sAlreadyForArticleRegistered', 'Für diesen Artikeln haben Sie sich bereits für eine Benachrichtigung registriert.', 'Für diesen Artikeln haben Sie sich bereits für eine Benachrichtigung registriert.');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES (NULL, '79', 'sDEACTIVATEBASKETONNOTIFICATION', '0', 'Warenkorb bei eMail-Benachrichtigung ausblenden', '0', '0', 'Warenkorb bei aktivierter eMail-Benachrichtigung und nicht vorhandenem Lagerbestand ausblenden', '0', 'int');

SET @parent = (SELECT `id` FROM `s_core_menu` WHERE `name` = 'Auswertungen');

INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `ul_properties`) VALUES
(NULL, @parent, '', 'E-Mail Benachrichtigung', 'loadSkeleton(''notificationStat'');', 'background-position: 5px 5px;', 'ico2 table_arrow', 4, 1, '');

INSERT IGNORE INTO `s_crontab` (`name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`) VALUES
('eMail-Benachrichtigung', 'notification', NULL, '', '2000-01-01 00:00:00', '2000-01-01 00:00:00', 86400, 0, '2000-01-01 00:00:00', '', '');

-- Ticket #2670 --

ALTER TABLE `s_core_auth` ADD `salted` INT( 1 ) unsigned NOT NULL;

-- Ticket #2559 --

DELETE FROM s_core_hookpoints WHERE `name` = 'shopware.php_licenseCheckAfter';

-- Ticket #XXXX --

UPDATE s_core_menu SET `onclick` =  "window.Growl('{release}<br />(c)2010-2011 shopware AG');" WHERE `name` = 'Über Shopware';
UPDATE s_core_menu SET `name` =  'Import/Export' WHERE `name` = 'Datenaustausch';

-- Ticket #2750 --

ALTER TABLE `s_order_details` ADD INDEX `articleordernumber` ( `articleordernumber` );

-- Ticket #2697 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` LIKE 'Artikel-Bewertungen');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sVOTESENDCALLING', '0', 'Automatische Erinnerung zur Artikelbewertung senden', 0, 0, 'Nach Kauf dem Benutzer an die Artikelbewertung via E-Mail erinnern', 0, 'int'),
(NULL, @parent, 'sVOTECALLINGTIME', '', 'Tage bis die Erinnerungs-E-Mail verschickt wird', 0, 0, 'Tage bis der Kunde via E-Mail an die Artikel-Bewertung erinnert wird', 0, '');

INSERT IGNORE INTO `s_core_config_mails` (`id`, `name`, `frommail`, `fromname`, `subject`, `content`, `contentHTML`, `ishtml`, `htmlable`, `attachment`) VALUES
(NULL, 'sARTICLECOMMENT', 'info@example.com', 'Shopware Demo', 'Artikel bewerten', '<p>Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n</p>\r\nSie haben bei uns vor einigen Tagen Produkte bei uns unter shopwareAG gekauft. Wir würden uns freuen, wenn Sie diese Produkte bewerten würden. So helfen Sie uns, unseren Service weiter zu steigern, und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen. \r\nÜbrigens, Sie müssen natürlich nicht jeden gekauften Artikel kommentieren, nehmen Sie einfach die wozu Sie Lust haben, wir freuen uns über jedes Feedback.\r\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\r\n<p>\r\n</p>\r\n<table>\r\n {foreach from=$sArticles item=sArticle key=key}\r\n{if !$sArticle.modus}\r\n <tr>\r\n  <td>{$sArticle.articleordernumber}</td>\r\n  <td>{$sArticle.name}</td>\r\n  <td>\r\n  <a href="{$sArticle.link}#bewertungen">link</a>\r\n  </td>\r\n </tr>\r\n{/if}\r\n {/foreach}\r\n</table>\r\n\r\n<p>\r\nMit freundlichen Grüßen,<br />\r\nIhr Team von shopwareAG\r\n</p>', '<p>Hallo {if $sUser.salutation eq "mr"}Herr{elseif $sUser.billing_salutation eq "ms"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n</p>\r\nSie haben bei uns vor einigen Tagen Produkte bei uns unter shopwareAG gekauft. Wir würden uns freuen, wenn Sie diese Produkte bewerten würden. So helfen Sie uns, unseren Service weiter zu steigern, und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen. \r\nÜbrigens, Sie müssen natürlich nicht jeden gekauften Artikel kommentieren, nehmen Sie einfach die wozu Sie Lust haben, wir freuen uns über jedes Feedback.\r\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\r\n<p>\r\n</p>\r\n<table>\r\n {foreach from=$sArticles item=sArticle key=key}\r\n{if !$sArticle.modus}\r\n <tr>\r\n  <td>{$sArticle.articleordernumber}</td>\r\n  <td>{$sArticle.name}</td>\r\n  <td>\r\n  <a href="{$sArticle.link}#bewertungen">link</a>\r\n  </td>\r\n </tr>\r\n{/if}\r\n {/foreach}\r\n</table>\r\n\r\n<p>\r\nMit freundlichen Grüßen,<br />\r\nIhr Team von shopwareAG\r\n</p>', 1, 1, '');

INSERT IGNORE INTO `s_crontab` (`name`, `action`, `elementID`, `data`, `next`, `start`, `interval`, `active`, `end`, `inform_template`, `inform_mail`) VALUES
('Artikelbewertung per eMail', 'article_comment', NULL, 'b:0;', '2000-01-01 00:00:00', '2000-01-01 00:00:00', 86400, 0, '2000-01-01 00:00:00', '', '');

-- Ticket #2679 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` LIKE 'Debugging');

UPDATE `s_core_config` SET `group` = @parent WHERE `name` = 'sADODB_LOG';

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sFIREPHP', '', 'Ip-Beschränkung für FirePHP', 0, 0, '', 0, '');

-- Ticket #2223 --

ALTER TABLE `s_filter` ADD `sortmode` INT( 1 ) NOT NULL;

-- Ticket #2745 --

SET @parent = (SELECT `id` FROM `s_core_menu` WHERE `name` LIKE 'Inhalte');

INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `ul_properties`) VALUES
(NULL, @parent, '', 'Blog', 'loadSkeleton(''blog'');', 'background-position: 5px 5px;', 'ico2 layout1', 1, 1, '');

SET @parent = (SELECT `id` FROM `s_core_engine_groups` WHERE `group` = 'Stammdaten');

INSERT IGNORE INTO `s_core_engine_elements` (`id`, `group`, `domname`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `version`, `availablebyvariants`, `help`, `multilanguage`) VALUES
(NULL, @parent, 'selectTemplate', 'Bitte wählen', 'select', 'Template', 0, 1, 'template', '', 0, 0, 'Template welches verwendet werden soll', 0);

ALTER TABLE `s_articles` ADD `template` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `s_categories` DROP `premium_exclude`;
ALTER TABLE `s_categories` ADD `blog` INT NOT NULL;
ALTER TABLE `s_articles` ADD `mode` INT NOT NULL;

INSERT IGNORE INTO `s_order_number` (`id`, `number`, `name`, `desc`) VALUES
(NULL, 100, 'blogordernumber', 'Blog - ID');

-- Ticket #2793 --

ALTER TABLE `s_order_basket` ADD `liveshoppingID` INT( 11 ) NOT NULL AFTER `ob_attr6`;

CREATE TABLE IF NOT EXISTS `s_articles_live` (
  `id` int(11) NOT NULL auto_increment,
  `articleID` int(11) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `active` int(1) unsigned NOT NULL,
  `rab_type` varchar(255) NOT NULL,
  `max_quantity_enable` INT( 1 ) unsigned NOT NULL,
  `max_quantity` int(11) unsigned NOT NULL,
  `valid_from` datetime NOT NULL,
  `valid_to` datetime NOT NULL,
  `datum` datetime NOT NULL,
  `customergroups` text NOT NULL,
  `sells` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `articleID` (`articleID`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_articles_live_prices` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `customergroup` varchar(255) NOT NULL,
  `price` double NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bundleID_2` (`liveshoppingID`,`customergroup`),
  KEY `bundleID` (`liveshoppingID`,`customergroup`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_articles_live_stint` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `ordernumber` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `bundleID` (`liveshoppingID`,`ordernumber`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- Ticket #2694 --

CREATE TABLE IF NOT EXISTS `s_core_rewrite_urls` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `org_path` varchar(255) NOT NULL,
  `path` varchar(255) NOT NULL,
  `main` int(1) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `path` (`path`,`subshopID`),
  KEY `org_path` (`org_path`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

INSERT IGNORE INTO `s_core_factory` (`id`, `description`, `basename`, `basefile`, `inheritname`, `inheritfile`) VALUES
(NULL, 'Router', 'sRouter', 'sRouter.php', '', '');

INSERT IGNORE INTO `s_core_config_groups` (`id`, `name`, `position`, `parent`, `file`, `description`) VALUES
(NULL, 'SEO', 12, 38, '', '');

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'SEO');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sUSEROUTER', '0', 'neue SEO-Urls verwenden', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sROUTERUSEMODREWRITE', '0', 'Mod_Rewrite nutzen', 0, 0, '', 0, 'int'),
(NULL, @parent, 'sROUTERTOLOWER', '0', 'Nur Kleinbuchstaben in den Urls nutzen', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sREDIRECTBASEFILE', '0', 'Startseite ohne Shopkernel in der Url nutzen', 0, 0, '', 0, 'int'),
(NULL, @parent, 'sREDIRECTNOTFOUND', '0', 'Bei nicht vorhandenen Kategorien/Artikel auf Startseite umleiten', 0, 0, '', 0, 'int'),
(NULL, @parent, 'sREDIRECTAFTERRENDER', '0', 'Auf die echte Url im Bestellprozess umleiten', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sSEOMETADESCRIPTION', '1', 'Meta-Description von Artikel/Kategorien aufbereiteten', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sROUTERREMOVECATEGORY', '0', 'KategorieID aus Url entfernen', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sSEOQUERYBLACKLIST', 'sPage,sPerPage,sSupplier,sFilterProperties,p,n,s,f', 'SEO-Nofollow Querys', 0, 0, '', 0, ''),
(NULL, @parent, 'sSEOVIEWPORTBLACKLIST', 'login,ticket,tellafriend,note,support,basket,admin,registerFC,newsletter,search', 'SEO-Nofollow Viewports', 0, 0, '', 0, '');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sSEOREMOVEWHITESPACES', '0', 'überflüssige Leerzeichen / Zeilenumbrüchen entfernen', 0, 0, '', 0, 'int'),
(NULL, @parent, 'sSEOREMOVECOMMENTS', '0', 'Html-Kommentare entfernen', 0, 0, '', 0, 'int');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sSEOQUERYALIAS', 'sSearch=q,\r\nsPage=p,\r\nsPerPage=n,\r\nsSupplier=s,\r\nsFilterProperties=f,\r\nsCategory=c,\r\nsCoreId=u,\r\nsTarget=t,\r\nsValidation=v', 'Query-Aliase', 0, 0, '', 0, 'textarea'),
(NULL, @parent, 'sSEOBACKLINKWHITELIST', 'www.shopware.de,\r\nwww.shopware.ag,\r\nwww.shopware-ag.de', 'SEO-Follow Backlinks', 0, 0, '', 1, 'textarea');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sSEORELCANONICAL', '0', 'SEO-Canonical-Tags nutzen', 0, 0, '', 1, 'int');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sROUTERLASTUPDATE', '', 'Datum des letzten Updates', 0, 0, '', 0, '');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sROUTERCACHE', '86400', 'SEO-Urls Cachezeit Tabelle', 0, 0, '', 0, ''),
(NULL, @parent, 'sROUTERURLCACHE', '86400', 'SEO-Urls Cachezeit Urls', 0, 0, '', 0, '');

-- Ticket #2777 --

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_countries` (
  `paymentID` int(11) unsigned NOT NULL,
  `countryID` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`paymentID`,`countryID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_core_paymentmeans_subshops` (
  `paymentID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`paymentID`,`subshopID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Ticket #2766 --

UPDATE `s_crontab` SET `active` = '0' WHERE `action` = 'birthday' AND 'end' NOT LIKE '2010-%';

-- Ticket #2748 --

INSERT IGNORE INTO `s_core_viewports` (`id`, `viewport`, `viewport_file`, `description`) VALUES
(NULL, 'newsletterListing', 's_newsletterListing.php', 'Newsletter Archiv');

ALTER TABLE `s_campaigns_mailings` ADD `publish` INT( 1 ) UNSIGNED NOT NULL;

-- Ticket #2789 --

ALTER TABLE `s_order_documents` CHANGE `orderID` `orderID` INT( 11 ) UNSIGNED NOT NULL;

-- Ticket #2675 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'eMail-Einstellungen');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`) VALUES
(NULL, @parent, 'sORDERSTATEMAILACK', '', 'Bestellstatus - Änderungen CC-Adresse', 0, 0, '', 0);

-- Ticket #2741 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Warenkorb / Artikeldetails');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sINSTOCKINFO', '1', 'Lagerbestands-Unterschreitung im Warenkorb anzeigen', 0, 0, '', 0, 'int');
	
-- Ticket #2668 --
	
ALTER TABLE s_emarketing_vouchers ADD strict INT( 1 ) NOT NULL;
ALTER TABLE s_emarketing_vouchers ADD subshopID INT( 1 ) NOT NULL;

-- Ticket #2793 --

ALTER TABLE `s_addon_premiums` ADD `subshopID` INT NOT NULL;

ALTER TABLE `s_articles_live` ADD `frontpage_display` INT( 1 ) NOT NULL ,
ADD `categories_display` INT( 1 ) NOT NULL;

ALTER TABLE `s_articles_live` ADD `typeID` INT( 1 ) NOT NULL AFTER `articleID`;
ALTER TABLE `s_articles_live_prices` ADD `endprice` DOUBLE NOT NULL;

-- Ticket #2744 --

ALTER TABLE `s_categories` ADD `showfiltergroups` INT NOT NULL;

-- Ticket #2745 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Kategorien / Listen');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sCATEGORYDETAILLINK', '0', 'Direkt auf Detailspringen, falls nur ein Artikel vorhanden ist', 0, 0, '', 0, 'int');

ALTER TABLE `s_categories` ADD `external` VARCHAR( 255 ) NOT NULL;

-- Ticket #2733 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Anmeldung / Registrierung');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sDOUBLEEMAILVALIDATION', '0', 'E-Mail Addresse muss zweimal eingegeben werden.', 0, 0, 'E-Mail Addresse muss zweimal eingegeben werden, um Tippfehler zu vermeiden.', 0, 'int');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` = 'register');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sErrorEmailNotEqual', 'Die eMail-Adressen stimmen nicht überein.', 'Die eMail-Adressen stimmen nicht überein.');
INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sRegisteryouremailconfirmation', 'Wiederholen Sie Ihre eMail-Adresse*:', 'Wiederholen Sie Ihre eMail-Adresse*:');

-- Ticket #2745 --

INSERT IGNORE INTO `s_core_engine_elements` (`id`, `group`, `domname`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `version`, `availablebyvariants`, `help`, `multilanguage`) VALUES
(NULL, 0, 'changetime', '', 'text', 'Einstelldatum', 0, 6, 'changetime', 'w100', 0, 0, 'Einstelldatum', 0);

UPDATE `s_core_config_groups` SET `name` = 'CMS-Funktionen' WHERE `name` = 'Shopseiten / Feeds';

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'CMS-Funktionen');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sBLOGCATEGORY', '3', 'Blog-Einträge aus Kategorie (ID) auf Startseite anzeigen', 0, 0, '', 1, 'text'),
(NULL, @parent, 'sBLOGLIMIT', '3', 'Anzahl Blog-Einträge auf Startseite', 0, 0, '', 1, 'text');

-- Ticket #2809 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Sofortüberweisung');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent , 'sNOTIFYKEY', '', 'Benachrichtungsschlüssel', 0, 0, '', 0, '');

-- Ticket #2800 --

ALTER TABLE `s_articles_groups_value` ADD `gv_attr1` VARCHAR( 255 ) NULL ,
ADD `gv_attr2` VARCHAR( 255 ) NULL ,
ADD `gv_attr3` VARCHAR( 255 ) NULL ,
ADD `gv_attr4` VARCHAR( 255 ) NULL ,
ADD `gv_attr5` VARCHAR( 255 ) NULL;

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Warenkorb / Artikeldetails');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES

(NULL, @parent, 'sCONFIGCUSTOMFIELDS', 'Freitext 1, Freitext 2', 'Konfigurator Freitextfelder', 0, 0, '', 0, '');

-- Ticket #2744 --

ALTER TABLE `s_categories` ADD `hidefilter` INT( 1 ) NOT NULL;

-- Ticket #2804 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Module');

INSERT IGNORE INTO `s_core_config_groups` (`id`, `name`, `position`, `parent`, `file`, `description`) VALUES
(NULL, 'USt-IdNr. Überprüfung', 11, @parent, '', '');

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'USt-IdNr. Überprüfung');

REPLACE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sVATCHECKENDABLED', '0', 'Modul aktivieren', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKADVANCEDNUMBER', '', 'Eigene USt-IdNr. für erweiterte Überprüfung', 0, 0, '', 0, ''),
(NULL, @parent, 'sVATCHECKADVANCED', '0', 'Erweiterte Überprüfung aktivieren', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKADVANCEDCOUNTRIES', 'AT', 'gültige Länder für erweiterte Überprüfung', 0, 0, '', 0, ''),
(NULL, @parent, 'sVATCHECKREQUIRED', '0', 'USt-IdNr. als Pflichtfeld markieren', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKDEBUG', '0', 'Erweiterte Fehlerausgabe aktivieren', 0, 0, '', 1, 'int'),
(NULL, @parent, 'sVATCHECKCONFIRMATION', '0', 'Amtliche Bestätigungsmitteilung aktivieren', 0, 0, '', 1, 'int');
SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description` = 'register');

INSERT IGNORE INTO `s_core_config_text` (`id`, `group`, `name`, `value`, `description`) VALUES
(NULL, @parent, 'sVatCheckErrorEmpty', 'Bitte geben Sie eine USt-IdNr. an.', ''),
(NULL, @parent, 'sVatCheckErrorDate', 'Die eingegebene USt-IdNr. ist ungültig. Sie ist erst ab dem %s gültig.', ''),
(NULL, @parent, 'sVatCheckErrorInvalid', 'Die eingegebene USt-IdNr. ist ungültig.', ''),
(NULL, @parent, 'sVatCheckErrorField', 'Das Feld %s passt nicht zur USt-IdNr.', ''),
(NULL, @parent, 'sVatCheckUnknownError', 'Es ist ein unerwarteter Fehler bei der Überprüfung der USt-IdNr. aufgetreten. Bitte kontaktieren Sie den Shopbetreiber. (Fehlercode: %d)', ''),
(NULL, @parent, 'sVatCheckErrorInfo', 'Bitte passen Sie die Rechnungsadresse/USt-IdNr. an oder lassen Sie das Feld für die USt-IdNr. leer.', ''),
(NULL, @parent, 'sVatCheckErrorFields', 'Firma,Ort,PLZ,Straße,Land', '');

-- Ticket #XXXX --

ALTER TABLE `s_emarketing_promotions` ADD `liveshoppingID` INT( 11 ) unsigned NOT NULL;

ALTER TABLE `s_emarketing_banners` ADD `liveshoppingID` INT( 11 ) unsigned NOT NULL;

-- Ticket #2801 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'SEO');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sROUTERARTICLETEMPLATE', '{sCategoryPath articleID=$sArticle.id}/{$sArticle.name}', 'SEO-Urls Artikel-Template', 0, 0, '', 1, ''),
(NULL, @parent, 'sROUTERCATEGORYTEMPLATE', '{sCategoryPath categoryID=$sCategory.id}', 'SEO-Urls Kategorie-Template', 0, 0, '', 1, '');


-- Ticket #2788 -- 

SET @parent = (SELECT MAX(`groupID`) FROM `s_core_config_text_groups`)+1;

INSERT IGNORE INTO `s_core_config_text_groups` (`groupID`, `description`) VALUES
(@parent , 'blog');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sBlogComments', 'Kommentare', 'Kommentare'),
(@parent, 'sBlogReadMore', 'Mehr lesen', 'Mehr lesen'),
(@parent, 'sBlogOlderArticles', 'Zurück', 'Zurück'),
(@parent, 'sBlogNewerArticles', 'Weiter', 'Weiter'),
(@parent, 'sBlogtoOverview', 'Zur &Uuml;bersicht', 'Zur &Uuml;bersicht'),
(@parent, 'sBlogCategoryAssignment', 'Kategoriezuordnung', 'Kategoriezuordnung'),
(@parent, 'sBlogToComments', 'Zu den Kommentaren des Artikels', 'Zu den Kommentaren des Artikels'),
(@parent, 'sBlogTags', 'Tags', 'Tags'),
(@parent, 'sBlogShowAllAuthors', 'Alle Autoren anzeigen', 'Alle Autoren anzeigen'),
(@parent, 'sBlogAuthors', 'Autoren', 'Autoren'),
(@parent, 'sBlogDate', 'Datum', 'Datum'),
(@parent, 'sBlogRSS', 'RSS-Feed', 'RSS-Feed'),
(@parent, 'sBlogAtom', 'Atom-Feed', 'Atom-Feed'),
(@parent, 'sBlogCategories', 'Kategorien', 'Kategorien'),
(@parent, 'sBlogNewInTheBlog', 'Neu in unserem Blog', 'Neu in unserem Blog');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='category');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sCategoryFilterTo', 'Filtern nach', 'Filtern nach');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='account');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sAccountRepeatOrder', 'Bestellung wiederholen', 'Bestellung wiederholen');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='ajax');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sCompareClose', 'schlie&szlig;en', 'schlie&szlig;en');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='articles');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sArticlesBundleSaveMoney', 'Sparen Sie jetzt mit unseren Bundle-Angeboten', 'Sparen Sie jetzt mit unseren Bundle-Angeboten'),
(@parent, 'sArticlesBundlePricesForAll', 'Preis für alle', 'Preis für alle'),
(@parent, 'sArticlesBundleInstead', 'Statt', 'Statt'),
(@parent, 'sArticlesBundleBuy', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln', 'Kaufen Sie diesen Artikel zusammen mit folgenden Artikeln'),
(@parent, 'sArticlesLiveshoppingAuctionEnd', 'Aktionsende', 'Aktionsende'),
(@parent, 'sArticlesLiveshoppingStartPrice', 'Startpreis', 'Startpreis'),
(@parent, 'sArticlesLiveshoppingActualPrice', 'Aktueller Preis', 'Aktueller Preis'),
(@parent, 'sArticlesLiveshoppingHours', 'Stunden', 'Stunden'),
(@parent, 'sArticlesLiveshoppingMinutes', 'Minuten', 'Minuten'),
(@parent, 'sArticlesLiveshoppingSeconds', 'Sekunden', 'Sekunden'),
(@parent, 'sArticlesLiveshoppingJust', 'Noch', 'Noch'),
(@parent, 'sArticlesLiveshoppingPiece', 'Stück', 'Stück'),
(@parent, 'sArticleLiveshoppingOfferEndsIn', 'Angebot endet in', 'Angebot endet in'),
(@parent, 'sArticlesLiveshoppingPriceFalling', 'Preis f&auml;llt um &euro;', 'Preis f&auml;llt um &euro;'),
(@parent, 'sArticlesLiveshoppingPriceRising', 'Preis steigt um &euro;', 'Preis steigt um &euro;'),
(@parent, 'sArticlesLiveshoppingPriceFallingPerMinute', 'Preis sinkt im Minutentakt um', 'Preis sinkt im Minutentakt um'),
(@parent, 'sArticlesLiveshoppingPriceRisingPerMinute', 'Preis steigt im Minutentakt um', 'Preis steigt im Minutentakt um'),
(@parent, 'sArticlesLiveshoppingSpecialOfferTill', 'Sonderangebot nur noch bis zum:', 'Sonderangebot nur noch bis zum:'),
(@parent, 'sArticleNotificationEmail', 'E-Mail', 'E-Mail'),
(@parent, 'sArticlesNotificationSignIn', 'Eintragen', 'Eintragen'),
(@parent, 'sArticlesShippingTill', 'Lieferung bis', 'Lieferung bis'),
(@parent, 'sArticlesOrderInNext', 'Bestellen Sie in den  \r\nnächsten', 'Bestellen Sie in den   nächsten'),
(@parent, 'sArticlesOrderInNextHours', 'Stunden und', 'Stunden und'),
(@parent, 'sArticlesOrderInAndChoose', 'und wählen Sie', 'und wählen Sie'),
(@parent, 'sArticlesOrderOvernight', 'Overnight-Express', 'Overnight-Express'),
(@parent, 'sArticlesOrderToCashDesk', 'an der Kasse', 'an der Kasse');

SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='custom');

INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sNewsletterNewWindow', 'Newsletter in neuem Fenster öffnen', 'Newsletter in neuem Fenster öffnen');

-- Ticket #XXXX --

CREATE TABLE IF NOT EXISTS `s_articles_live_shoprelations` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `liveshoppingID` int(11) unsigned NOT NULL,
  `subshopID` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `liveshoppingID` (`liveshoppingID`,`subshopID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Ticket #2833 --

UPDATE `s_core_config_groups` SET `description` = '<div style="display: block; border: 1px solid rgb(169, 169, 169); margin: 0pt 5px 20px; padding: 5px; background-color: rgb(255, 255, 255); font-size: 12px;">\r\n<p style="font-weight: bold; margin: 4px 0pt;">PayPal &ndash; Ihr Partner für Online-Zahlungen</p>\r\n<p>Als einer der führenden Anbieter von Online-Zahlungslösungen ist PayPal Ihr verlässlicher Partner für schnelle und sichere Zahlungen in Ihrem Online-Shop. Mit PayPal empfangen Sie Zahlungen online genauso einfach wie an der Ladenkasse. Denn Sie aktivieren PayPal in Minuten und bieten allein in Deutschland Nutzern von über 10 Millionen PayPal-Konten sofort alle gängigen Zahlungsmethoden an. Sie können Ihren Umsatz nachweislich um bis zu 16% steigern und profitieren von umfassendem Risikomanagement.</p>\r\n\r\n<p>Und: PayPal kostet Sie nur dann etwas, wenn Ihr Kunde damit bezahlt.</p>\r\n\r\n<p><a target="_blank" href="http://altfarm.mediaplex.com/ad/ck/3484-50686-12439-4?ID=1">Zur PayPal-Anmeldung</a></p>\r\n\r\n<p style="font-weight: bold; margin: 20px 0px 4px 0px;">Hinweis:</p> Wenn Sie den PayPal Zahlungsmodus auf reserviert ändern, können die reservierten Zahlungen später manuell über Kunden -&gt; Zahlungen -&gt; PayPal eingezogen werden.</div>' WHERE `name` = 'PayPal (Express)';

-- Ticket #XXXX --

UPDATE `s_core_config_text` SET `value` = 'Preis fällt um', `description` = 'Preis fällt um' WHERE `name` = 'sArticlesLiveshoppingPriceFalling' LIMIT 1 ;
UPDATE `s_core_config_text` SET `value` = 'Preis steigt um', `description` = 'Preis steigt um' WHERE `name` = 'sArticlesLiveshoppingPriceRising' LIMIT 1 ;

-- Ticket #2781 --

ALTER TABLE `s_filter_values` DROP INDEX `optionID` ,
ADD INDEX `optionID` ( `optionID` , `articleID` );

-- Ticket #2840 --

UPDATE `s_core_engine_elements` SET `multilanguage` = '1' WHERE `domname` = 'txtpackunit';

-- Ticket #2852 --

UPDATE `s_core_config` SET `multilanguage` = '1' WHERE `name` = 'sNEWSLETTERDEFAULTGROUP';

-- Ticket #2711 --

UPDATE `s_core_config` SET `description` =  'Installierte Zahlungsarten' WHERE `name` = 'sMONEYBOOKERS_INSTALLED_PAYMETHODSINSTALLED';

-- Ticket #2711 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Anmeldung / Registrierung');

INSERT IGNORE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sNEWSLETTEREXTENDEDFIELDS', '0', 'Erweiterte Felder in Newsletter-Registrierung abfragen', 0, 0, '', 1, 'int');

-- Ticket #XXXX --

ALTER TABLE `s_articles_live` ADD `ordernumber` VARCHAR( 255 ) NOT NULL AFTER `rab_type`;

-- Ticket #2745 --

SET @parent = (SELECT `id` FROM `s_core_config_groups` WHERE `name` = 'Warenkorb / Artikeldetails');

REPLACE INTO `s_core_config` (`id`, `group`, `name`, `value`, `description`, `required`, `warning`, `detailtext`, `multilanguage`, `fieldtype`) VALUES
(NULL, @parent, 'sDETAILTEMPLATES', ':Standard;../blog/details.tpl:Blog', 'Verfügbare Templates Detailseite', 0, 0, '', 0, 'textarea');

UPDATE `s_articles` SET `template` =  '../blog/details.tpl'  WHERE `template` = 'article_details_blog.tpl';

-- #2970 Weitere Liveshoppinganpassungen --
SET @parent = (SELECT `groupID` FROM `s_core_config_text_groups` WHERE `description`='articles');
INSERT IGNORE INTO `s_core_config_text` (`group`, `name`, `value`, `description`) VALUES
(@parent, 'sArticlesLiveshoppingOriginallyPrice', 'Ursprünglicher Preis:', 'Ursprünglicher Preis:'),
(@parent, 'sArticlesLiveshoppingYouSave', 'Sie sparen:', 'Sie sparen:');

-- Change Version --

UPDATE `s_core_config` SET `value` = '3.0.5' WHERE name = 'sVERSION';