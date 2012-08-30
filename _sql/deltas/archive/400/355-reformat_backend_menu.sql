-- //

DELETE FROM `s_core_menu` WHERE `name` = 'Feeds*';
DELETE FROM `s_core_menu` WHERE `name` = 'Ticket-System*';
DELETE FROM `s_core_menu` WHERE `name` = 'Plugins*';
DELETE FROM `s_core_menu` WHERE `name` = 'Debug-Men&uuml;';
DELETE FROM `s_core_menu` WHERE `name` = 'Aktionen*';
DELETE FROM `s_core_menu` WHERE `name` = 'Beta-FAQ';

DELETE FROM `s_core_menu` WHERE `name` = 'Paypal*';
DELETE FROM `s_core_menu` WHERE `name` = 'Saferpay*';
DELETE FROM `s_core_menu` WHERE `name` = 'ClickPay*';

DELETE FROM `s_core_menu` WHERE `name` = 'Styling Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'Block Messages Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'Code Mirror Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'Article Suggest Search Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'TinyMCE Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'Desktop-Switcher Demo';
DELETE FROM `s_core_menu` WHERE `name` = 'Base Store Demo';

INSERT INTO `s_core_menu` ( `id`,`parent` ,`hyperlink` ,`name` ,`onclick` ,`style` ,`class` ,`position` ,`active` ,`pluginID` ,`resourceID` ,`controller` ,`shortcut` ,`action`)
VALUES (NULL , '40', '', 'Hilfe', NULL , NULL , 'sprite-lifebuoy', '0', '1', NULL , NULL , NULL , NULL , NULL);
SET @parent = (SELECT id FROM s_core_menu WHERE name='Hilfe');

INSERT INTO `s_core_menu` ( `id`,`parent` ,`hyperlink` ,`name` ,`onclick` ,`style` ,`class` ,`position` ,`active` ,`pluginID` ,`resourceID` ,`controller` ,`shortcut` ,`action`)
VALUES (NULL , 40, '', 'Feedback senden', NULL , NULL , 'sprite-briefcase--arrow', '0', '1', NULL , NULL , 'BetaFeedback' , NULL , 'Index');

UPDATE `s_core_menu` SET `position` = '1' WHERE `name` = 'Tastaturk&uuml;rzel';
UPDATE `s_core_menu` SET `position` = '2' WHERE `name` = 'Über Shopware';

UPDATE `s_core_menu` SET `parent` = @parent WHERE `name` = 'Zum Forum';
UPDATE `s_core_menu` SET `parent` = @parent WHERE `name` = 'Onlinehilfe aufrufen';

UPDATE `s_core_menu` SET `name` = 'Kundenspezifische Preise', `onclick` = '', `controller`='PriceGroup' WHERE `name` = 'Kundenspezifische Preise*';

UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Kategorien';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Hersteller';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Banner';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Einkaufswelten';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Gutscheine';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Pr&auml;mienartikel';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Produktexporte';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Shopseiten';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Kundenliste';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Bestellungen';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Benutzerverwaltung';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Versandkosten';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'eMail-Vorlagen';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Übersicht';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Statistiken / Diagramme';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Über Shopware';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Import/Export';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Bewertungen';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Partnerprogramm';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Formulare';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Newsletter';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Abbruch-Analyse';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Riskmanagement';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Systeminfo';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Medienverwaltung';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Übersicht';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Logfile';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Eigenschaften';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Kundenspezifische Preise';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'E-Mail Benachrichtigung';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Blog';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Textbausteine';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Plugin Manager';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Tastaturk&uuml;rzel';
UPDATE`s_core_menu` SET `action` = 'Index' WHERE `name` = 'Grundeinstellungen';



UPDATE `s_core_menu` SET `controller` = 'AnalysisMenu' WHERE `name` = 'Auswertungen';
UPDATE `s_core_menu` SET `controller` = 'ShortCutMenu' WHERE `name` = 'Tastaturk&uuml;rzel';
UPDATE `s_core_menu` SET `controller` = 'HelpMenu' WHERE `name` = 'Hilfe';


-- //@UNDO


-- //