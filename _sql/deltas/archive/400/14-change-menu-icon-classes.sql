-- //

/** Article */
UPDATE `s_core_menu` SET `class` = 'sprite-inbox--plus' WHERE `s_core_menu`.`name` LIKE 'Neu';
UPDATE `s_core_menu` SET `class` = 'sprite-ui-scroll-pane-list' WHERE `s_core_menu`.`name` LIKE '&Uuml;bersicht' AND `s_core_menu`.`onclick` LIKE "loadSkeleton('articlesfast');";
UPDATE `s_core_menu` SET `class` = 'sprite-blue-folders-stack' WHERE `s_core_menu`.`name` LIKE 'Kategorien';
UPDATE `s_core_menu` SET `class` = 'sprite-property-blue' WHERE `s_core_menu`.`name` LIKE 'Eigenschaften';
UPDATE `s_core_menu` SET `class` = 'sprite-truck' WHERE `s_core_menu`.`name` LIKE 'Hersteller';
UPDATE `s_core_menu` SET `class` = 'sprite-balloon' WHERE `s_core_menu`.`name` LIKE 'Bewertungen';

/** Content */
UPDATE `s_core_menu` SET `class` = 'sprite-documents' WHERE `s_core_menu`.`name` LIKE 'Shopseiten';
UPDATE `s_core_menu` SET `class` = 'sprite-application-text' WHERE `s_core_menu`.`name` LIKE 'Feeds';
UPDATE `s_core_menu` SET `class` = 'sprite-application-blog' WHERE `s_core_menu`.`name` LIKE 'Blog';
UPDATE `s_core_menu` SET `class` = 'sprite-application-form' WHERE `s_core_menu`.`name` LIKE 'Formulare';
UPDATE `s_core_menu` SET `class` = 'sprite-arrow-circle-double-135' WHERE `s_core_menu`.`name` LIKE 'Import/Export';
UPDATE `s_core_menu` SET `class` = 'sprite-disk-return-black' WHERE `s_core_menu`.`name` LIKE 'Datei-Archiv';

/** Customers */
UPDATE `s_core_menu` SET `class` = 'sprite-user--plus' WHERE `s_core_menu`.`name` LIKE 'Anlegen';
UPDATE `s_core_menu` SET `class` = 'sprite-ui-scroll-pane-detail' WHERE `s_core_menu`.`name` LIKE 'Kundenliste';
UPDATE `s_core_menu` SET `class` = 'sprite-sticky-notes-pin' WHERE `s_core_menu`.`name` LIKE 'Bestellungen';
UPDATE `s_core_menu` SET `class` = 'sprite-money-coin' WHERE `s_core_menu`.`name` LIKE 'Zahlungen';
UPDATE `s_core_menu` SET `class` = 'sprite-user--coins' WHERE `s_core_menu`.`name` LIKE 'Kundenspezifische Preise';
UPDATE `s_core_menu` SET `class` = 'sprite-ticket--pencil' WHERE `s_core_menu`.`name` LIKE 'Ticket-System';

/** Setttings */
UPDATE `s_core_menu` SET `class` = 'sprite-bin-full' WHERE `s_core_menu`.`name` LIKE 'Shopcache leeren';
UPDATE `s_core_menu` SET `class` = 'sprite-wrench-screwdriver' WHERE `s_core_menu`.`name` LIKE 'Grundeinstellungen';
UPDATE `s_core_menu` SET `class` = 'sprite-block' WHERE `s_core_menu`.`name` LIKE 'Plugins';
UPDATE `s_core_menu` SET `class` = 'sprite-information' WHERE `s_core_menu`.`name` LIKE 'Systeminfo';
UPDATE `s_core_menu` SET `class` = 'sprite-user-silhouette' WHERE `s_core_menu`.`name` LIKE 'Benutzerverwaltung';
UPDATE `s_core_menu` SET `class` = 'sprite-cards-stack' WHERE `s_core_menu`.`name` LIKE 'Logfile';
UPDATE `s_core_menu` SET `class` = 'sprite-suit' WHERE `s_core_menu`.`name` LIKE 'Business Essentials';
UPDATE `s_core_menu` SET `class` = 'sprite-envelope--arrow' WHERE `s_core_menu`.`name` LIKE 'Versandkosten';
UPDATE `s_core_menu` SET `class` = 'sprite-credit-cards' WHERE `s_core_menu`.`name` LIKE 'Zahlungsarten';
UPDATE `s_core_menu` SET `class` = 'sprite-mail--pencil' WHERE `s_core_menu`.`name` LIKE 'eMail-Vorlagen';
UPDATE `s_core_menu` SET `class` = 'sprite-layout-hf-3-mix' WHERE `s_core_menu`.`name` LIKE 'Templateauswahl';
UPDATE `s_core_menu` SET `class` = 'sprite-exclamation-diamond' WHERE `s_core_menu`.`name` LIKE 'Riskmanagement';
UPDATE `s_core_menu` SET `class` = 'sprite-edit' WHERE `s_core_menu`.`name` LIKE 'Textbausteine';

/** Marketing */
UPDATE `s_core_menu` SET `class` = 'sprite-chart' WHERE `s_core_menu`.`name` LIKE 'Auswertungen';
UPDATE `s_core_menu` SET `class` = 'sprite-picture' WHERE `s_core_menu`.`name` LIKE 'Banner';
UPDATE `s_core_menu` SET `class` = 'sprite-pin' WHERE `s_core_menu`.`name` LIKE 'Einkaufswelten';
UPDATE `s_core_menu` SET `class` = 'sprite-star' WHERE `s_core_menu`.`name` LIKE 'Pr&auml;mienartikel';
UPDATE `s_core_menu` SET `class` = 'sprite-mail-open-image' WHERE `s_core_menu`.`name` LIKE 'Gutscheine';
UPDATE `s_core_menu` SET `class` = 'sprite-megaphone' WHERE `s_core_menu`.`name` LIKE 'Aktionen';
UPDATE `s_core_menu` SET `class` = 'sprite-folder-export' WHERE `s_core_menu`.`name` LIKE 'Produktexporte';
UPDATE `s_core_menu` SET `class` = 'sprite-xfn-colleague' WHERE `s_core_menu`.`name` LIKE 'Partnerprogramm';
UPDATE `s_core_menu` SET `class` = 'sprite-mails-stack' WHERE `s_core_menu`.`name` LIKE 'Newsletter (Campaigns)';

/** Analysis */
UPDATE `s_core_menu` SET `class` = 'sprite-report-paper' WHERE `s_core_menu`.`name` LIKE '&Uuml;bersicht' AND `s_core_menu`.`onclick` LIKE "loadSkeleton('overview');";
UPDATE `s_core_menu` SET `class` = 'sprite-chart' WHERE `s_core_menu`.`name` LIKE 'Statistiken / Diagramme';
UPDATE `s_core_menu` SET `class` = 'sprite-chart-down-color' WHERE `s_core_menu`.`name` LIKE 'Abbruch-Analyse';
UPDATE `s_core_menu` SET `class` = 'sprite-mail-send' WHERE `s_core_menu`.`name` LIKE 'E-Mail Benachrichtigung';

-- //@UNDO

/** Article */
UPDATE `s_core_menu` SET `class` = 'ico2 package_add' WHERE `s_core_menu`.`name` LIKE '&Uuml;bersicht' AND `s_core_menu`.`onclick` LIKE "loadSkeleton('articlesfast');";
UPDATE `s_core_menu` SET `class` = 'ico2 table_arrow' WHERE `s_core_menu`.`name` LIKE '&Uuml;bersicht';
UPDATE `s_core_menu` SET `class` = 'ico2 folders_stack' WHERE `s_core_menu`.`name` LIKE 'Kategorien';
UPDATE `s_core_menu` SET `class` = 'ico2 databases_pencil' WHERE `s_core_menu`.`name` LIKE 'Eigenschaften';
UPDATE `s_core_menu` SET `class` = 'ico2 lorry' WHERE `s_core_menu`.`name` LIKE 'Hersteller';
UPDATE `s_core_menu` SET `class` = 'ico2 bubble01' WHERE `s_core_menu`.`name` LIKE 'Bewertungen';

/** Content */
UPDATE `s_core_menu` SET `class` = 'ico2 documents' WHERE `s_core_menu`.`name` LIKE 'Shopseiten';
UPDATE `s_core_menu` SET `class` = 'ico2 layout1' WHERE `s_core_menu`.`name` LIKE 'Feeds';
UPDATE `s_core_menu` SET `class` = 'ico2 layout1' WHERE `s_core_menu`.`name` LIKE 'Blog';
UPDATE `s_core_menu` SET `class` = 'ico2 table02' WHERE `s_core_menu`.`name` LIKE 'Formulare';
UPDATE `s_core_menu` SET `class` = 'ico2 arrow_circle_double_135' WHERE `s_core_menu`.`name` LIKE 'Import/Export';
UPDATE `s_core_menu` SET `class` = 'sprite-application-text' WHERE `s_core_menu`.`name` LIKE 'Datei-Archiv';

/** Customers */
UPDATE `s_core_menu` SET `class` = 'ico2 user_add' WHERE `s_core_menu`.`name` LIKE 'Anlegen';
UPDATE `s_core_menu` SET `class` = 'ico2 card_address' WHERE `s_core_menu`.`name` LIKE 'Kundenliste';
UPDATE `s_core_menu` SET `class` = 'ico2 sticky_notes_pin' WHERE `s_core_menu`.`name` LIKE 'Bestellungen';
UPDATE `s_core_menu` SET `class` = 'ico2 date2' WHERE `s_core_menu`.`name` LIKE 'Zahlungen';
UPDATE `s_core_menu` SET `class` = 'ico2 card_address' WHERE `s_core_menu`.`name` LIKE 'Kundenspezifische Preise';
UPDATE `s_core_menu` SET `class` = 'ico2 sticky_notes_pin' WHERE `s_core_menu`.`name` LIKE 'Ticket-System';

/** Setttings */
UPDATE `s_core_menu` SET `class` = 'ico2 bin' WHERE `s_core_menu`.`name` LIKE 'Shopcache leeren';
UPDATE `s_core_menu` SET `class` = 'ico2 computer' WHERE `s_core_menu`.`name` LIKE 'Grundeinstellungen';
UPDATE `s_core_menu` SET `class` = 'ico2 bricks' WHERE `s_core_menu`.`name` LIKE 'Plugins';
UPDATE `s_core_menu` SET `class` = 'ico2 information_frame' WHERE `s_core_menu`.`name` LIKE 'Systeminfo';
UPDATE `s_core_menu` SET `class` = 'ico2 status_online' WHERE `s_core_menu`.`name` LIKE 'Benutzerverwaltung';
UPDATE `s_core_menu` SET `class` = 'ico2 cards' WHERE `s_core_menu`.`name` LIKE 'Logfile';
UPDATE `s_core_menu` SET `class` = 'ico2 suit' WHERE `s_core_menu`.`name` LIKE 'Business Essentials';
UPDATE `s_core_menu` SET `class` = 'ico2 envelope_arrow' WHERE `s_core_menu`.`name` LIKE 'Versandkosten';
UPDATE `s_core_menu` SET `class` = 'ico2 creditcards' WHERE `s_core_menu`.`name` LIKE 'Zahlungsarten';
UPDATE `s_core_menu` SET `class` = 'ico2 mail_pencil' WHERE `s_core_menu`.`name` LIKE 'eMail-Vorlagen';
UPDATE `s_core_menu` SET `class` = 'ico2 layout_header_footer_2' WHERE `s_core_menu`.`name` LIKE 'Templateauswahl';
UPDATE `s_core_menu` SET `class` = 'ico2 bulb_off' WHERE `s_core_menu`.`name` LIKE 'Riskmanagement';
UPDATE `s_core_menu` SET `class` = 'ico2 plugin' WHERE `s_core_menu`.`name` LIKE 'Textbausteine';

/** Marketing */
UPDATE `s_core_menu` SET `class` = 'ico2 chart_pie1' WHERE `s_core_menu`.`name` LIKE 'Auswertungen';
UPDATE `s_core_menu` SET `class` = 'ico2 image' WHERE `s_core_menu`.`name` LIKE 'Banner';
UPDATE `s_core_menu` SET `class` = 'ico2 pin' WHERE `s_core_menu`.`name` LIKE 'Einkaufswelten';
UPDATE `s_core_menu` SET `class` = 'ico2 star' WHERE `s_core_menu`.`name` LIKE 'Pr&auml;mienartikel';
UPDATE `s_core_menu` SET `class` = 'ico2 email_open_image' WHERE `s_core_menu`.`name` LIKE 'Gutscheine';
UPDATE `s_core_menu` SET `class` = 'ico2 aktion' WHERE `s_core_menu`.`name` LIKE 'Aktionen';
UPDATE `s_core_menu` SET `class` = 'ico2 folder_open_image' WHERE `s_core_menu`.`name` LIKE 'Produktexporte';
UPDATE `s_core_menu` SET `class` = 'ico2 arrow_leftright_blue' WHERE `s_core_menu`.`name` LIKE 'Partnerprogramm';
UPDATE `s_core_menu` SET `class` = 'ico2 mails_stack' WHERE `s_core_menu`.`name` LIKE 'Newsletter (Campaigns)';

/** Analysis */
UPDATE `s_core_menu` SET `class` = 'ico2 table_arrow' WHERE `s_core_menu`.`name` LIKE '&Uuml;bersicht' AND `s_core_menu`.`onclick` LIKE "loadSkeleton('overview');";
UPDATE `s_core_menu` SET `class` = 'ico2 chart_curve1' WHERE `s_core_menu`.`name` LIKE 'Statistiken / Diagramme';
UPDATE `s_core_menu` SET `class` = 'cross' WHERE `s_core_menu`.`name` LIKE 'Abbruch-Analyse';
UPDATE `s_core_menu` SET `class` = 'ico2 table_arrow' WHERE `s_core_menu`.`name` LIKE 'E-Mail Benachrichtigung';

-- //