ALTER TABLE `s_core_menu` ADD `controller` VARCHAR( 255 ) NOT NULL ,
ADD `shortcut` VARCHAR( 255 ) NOT NULL ,
ADD `action` VARCHAR( 255 ) NULL;

UPDATE s_core_menu SET controller = 'Article' WHERE name = 'Artikel';
UPDATE s_core_menu SET controller = 'Article', shortcut = 'STRG + ALT + N', onclick = '', name='Anlegen' WHERE name = 'Neu';
UPDATE s_core_menu SET controller = 'Categories', onclick = '' WHERE name = 'Kategorien';
UPDATE s_core_menu SET controller = 'Supplier', onclick='' WHERE name = 'Hersteller';
UPDATE s_core_menu SET controller = 'Content' WHERE name = 'Inhalte';
UPDATE s_core_menu SET controller = 'Banner', onclick='', class='sprite-image-medium' WHERE name = 'Banner';
UPDATE s_core_menu SET controller = 'Emotion' WHERE name = 'Einkaufswelten*';
UPDATE s_core_menu SET controller = 'Voucher', onclick = '' WHERE name = 'Gutscheine';
UPDATE s_core_menu SET controller = 'Premium', onclick = '' WHERE name = 'Pr&auml;mienartikel';
UPDATE s_core_menu SET controller = 'Productfeeds', onclick = '' WHERE name = 'Produktexporte';
UPDATE s_core_menu SET controller = 'Site', onclick = '' WHERE name = 'Shopseiten';
UPDATE s_core_menu SET controller = 'Feeds' WHERE name = 'Feeds*';
UPDATE s_core_menu SET controller = 'Customer' WHERE name = 'Kunden';
UPDATE s_core_menu SET controller = 'Customer', onclick = '', shortcut = 'STRG + ALT + K' WHERE name = 'Kundenliste';
UPDATE s_core_menu SET controller = 'Order', onclick = '', shortcut = 'STRG + ALT + B' WHERE name = 'Bestellungen';
UPDATE s_core_menu SET controller = 'OldSettings', name = 'Alte Einstellungen*' WHERE name = 'Grundeinstellungen*';
UPDATE s_core_menu SET controller = 'UserManager', onclick = '' WHERE name = 'Benutzerverwaltung';
UPDATE s_core_menu SET controller = 'Shipping', onclick = '' WHERE name = 'Versandkosten';
UPDATE s_core_menu SET controller = 'Payment', onclick = '' WHERE name = 'Zahlungsarten';
UPDATE s_core_menu SET controller = 'Mail', onclick = '' WHERE name = 'eMail-Vorlagen';
UPDATE s_core_menu SET controller = 'Cache' WHERE name = 'Shopcache leeren*';
UPDATE s_core_menu SET controller = 'Marketing' WHERE name = 'Marketing';
UPDATE s_core_menu SET controller = 'Overview', onclick = '' WHERE class = 'sprite-report-paper';
UPDATE s_core_menu SET controller = 'Analytics', onclick = '' WHERE name = 'Statistiken / Diagramme';
UPDATE s_core_menu SET controller = 'Onlinehelp' WHERE name = 'Onlinehilfe aufrufen';
UPDATE s_core_menu SET controller = 'AboutShopware' WHERE name = 'Über Shopware';
UPDATE s_core_menu SET controller = 'Templates', class = 'sprite-application-icon-large' WHERE name = 'Templateauswahl*';
UPDATE s_core_menu SET controller = 'Import/Export' WHERE name = 'Import/Export*';
UPDATE s_core_menu SET controller = 'Vote', onclick = '' WHERE name = 'Bewertungen';
UPDATE s_core_menu SET controller = 'SalesCampaigns' WHERE name = 'Aktionen*';
UPDATE s_core_menu SET controller = 'Partner' WHERE name = 'Partnerprogramm*';
UPDATE s_core_menu SET controller = 'Form', onclick = '' WHERE name = 'Formulare';
UPDATE s_core_menu SET controller = 'Newsletter', class = 'sprite-paper-plane' WHERE name = 'Newsletter (Campaigns)*';
UPDATE s_core_menu SET controller = 'OrdersCanceled' WHERE name = 'Abbruch-Analyse*';
UPDATE s_core_menu SET controller = 'Riskmanagement', class='funnel--exclamation' WHERE name = 'Riskmanagement*';
UPDATE s_core_menu SET controller = 'Systeminfo', class='sprite-blueprint', onclick = '' WHERE name = 'Systeminfo';
UPDATE s_core_menu SET controller = 'MediaManager', onclick = '' WHERE name = 'Medienverwaltung';
UPDATE s_core_menu SET controller = 'Payments' WHERE name = 'Zahlungen';
UPDATE s_core_menu SET controller = 'ArticleList', onclick = '', shortcut = 'STRG + ALT + O' WHERE class = 'sprite-ui-scroll-pane-list';
UPDATE s_core_menu SET controller = 'Logfile' WHERE name = 'Logfile*';
UPDATE s_core_menu SET controller = 'Analytics', onclick = '' WHERE name = 'Auswertungen';
UPDATE s_core_menu SET controller = 'Filter' WHERE name = 'Eigenschaften*';
UPDATE s_core_menu SET controller = 'AddUser' WHERE name = 'Anlegen*';
UPDATE s_core_menu SET controller = 'Paypal' WHERE name = 'Paypal*';
UPDATE s_core_menu SET controller = 'Saferpay' WHERE name = 'Saferpay*';
UPDATE s_core_menu SET controller = 'TicketSystem' WHERE name = 'Ticket-System*';
UPDATE s_core_menu SET controller = 'UserPrice' WHERE name = 'Kundenspezifische Preise*';
UPDATE s_core_menu SET controller = 'Notifications' WHERE name = 'E-Mail Benachrichtigung*';
UPDATE s_core_menu SET controller = 'Blog' WHERE name = 'Blog*';
UPDATE s_core_menu SET controller = 'Forum', class = 'balloons-box' WHERE name = 'Zum Forum';
UPDATE s_core_menu SET controller = 'Plugins', class = 'sprite-application-block', shortcut = 'STRG + ALT + P' WHERE name = 'Plugins*';
SET @parent = (SELECT id FROM s_core_menu WHERE name='Shopcache leeren*');
UPDATE s_core_menu SET controller = 'Snippets', class='edit-shade' WHERE name = 'Textbausteine' AND parent = @parent;
UPDATE s_core_menu SET controller = 'Article + Categories' WHERE name = 'Artikel + Kategorien';
UPDATE s_core_menu SET controller = 'Configs' WHERE name = 'Konfiguration';
UPDATE s_core_menu SET controller = 'BusinessEssentials', class='sprite-user-business-gray-boss' WHERE name = 'Business Essentials*';
UPDATE s_core_menu SET controller = 'RegisterShopwareID' WHERE name = 'Shopware ID registrieren';
UPDATE s_core_menu SET controller = 'Snippet', class = 'edit-shade', onclick = '' WHERE name = 'Textbausteine' AND parent != @parent;
UPDATE s_core_menu SET controller = 'BetaFAQ' WHERE name = 'Beta-FAQ';
UPDATE s_core_menu SET controller = 'ClickPay' WHERE name = 'ClickPay*';
UPDATE s_core_menu SET controller = 'ClickPayCreditCheck' WHERE name = 'ClickPay Bonitätsüberprüfung*';

SET @parent = (SELECT id FROM s_core_menu WHERE name='Einstellungen');
INSERT INTO s_core_menu (parent, name, style, class, position, active, controller) VALUES  (@parent, 'Grundeinstellungen', 'background-position: 5px 5px;','sprite-wrench-screwdriver', -5, 1, 'NewSettings');

SET @parent = (SELECT id FROM s_core_menu WHERE name='Grundeinstellungen');
INSERT INTO s_core_menu (parent, name, style, class, position, active, controller) VALUES  (@parent, 'Länder', 'background-position: 5px 5px;','sprite-globe', 0, 1, 'Countries');
INSERT INTO s_core_menu (parent, name, style, class, position, active, controller) VALUES  (@parent, 'Steuerverwaltung', 'background-position: 5px 5px;','sprite-money-coin', -1, 1, 'Tax');



-- //@UNDO

ALTER TABLE `s_core_menu`
  DROP `controller`,
  DROP `shortcut`,
  DROP `action`;

UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Article'', { controller: ''Detail'', action: ''New'' });', name='Neu' WHERE name = 'Anlegen';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Supplier'');' WHERE name = 'Hersteller';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Banner'');', class = 'sprite-picture' WHERE name = 'Banner';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Voucher'');' WHERE name = 'Gutscheine';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Premium'');' WHERE name = 'Pr&auml;mienartikel';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.ProductFeed'');' WHERE name = 'Produktexporte';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Site'');' WHERE name = 'Shopseiten';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Customer'');' WHERE name = 'Kundenliste';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Order'');' WHERE name = 'Bestellungen';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.UserManager'');' WHERE name = 'Benutzerverwaltung';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Shipping'');' WHERE name = 'Versandkosten';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Payment'');' WHERE name = 'Zahlungsarten';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Mail'');' WHERE name = 'eMail-Vorlagen';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Overview'');' WHERE class = 'sprite-report-paper';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Analytics'');' WHERE name = 'Statistiken / Diagramme';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Vote'');' WHERE name = 'Bewertungen';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Form'');' WHERE name = 'Formulare';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Systeminfo'');', class='sprite-information' WHERE name = 'Systeminfo';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.MediaManager'')' WHERE name = 'Medienverwaltung';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.ArticleList'');' WHERE class = 'sprite-ui-scroll-pane-list';
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Analytics'');' WHERE name = 'Auswertungen';
SET @parent = (SELECT id FROM s_core_menu WHERE name='Shopcache leeren*');
UPDATE s_core_menu SET onclick = 'openNewModule(''Shopware.apps.Snippet'');', class='sprite-edit' WHERE name = 'Textbausteine' AND parent != @parent;

UPDATE s_core_menu SET class = 'sprite-block' WHERE name = 'Plugins*';
UPDATE s_core_menu SET class = 'sprite-suit' WHERE name = 'Business Essentials*';
UPDATE s_core_menu SET class = 'sprite-layout-hf-3-mix' WHERE name = 'Templateauswahl*';
UPDATE s_core_menu SET class = 'sprite-exclamation-diamond' WHERE name = 'Riskmanagement*';
UPDATE s_core_menu SET class = 'sprite-edit' WHERE name = 'Textbausteine' AND parent = @parent;
UPDATE s_core_menu SET class = 'sprite-mails-stack' WHERE name = 'Newsletter (Campaigns)*';
UPDATE s_core_menu SET class = 'sprite-documents' WHERE name = 'Zum Forum';

UPDATE s_core_menu SET name = 'Kategorien*', onclick = 'loadSkeleton(''categories'');' WHERE name = 'Kategorien';

UPDATE s_core_menu SET name = 'Grundeinstellungen*' WHERE name = 'Alte Einstellungen*';

DELETE FROM s_core_menu WHERE name = 'Grundeinstellungen';
DELETE FROM s_core_menu WHERE name = 'Länder';
DELETE FROM s_core_menu WHERE name = 'Steuerverwaltung';
