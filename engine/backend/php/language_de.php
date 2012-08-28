<?php
// Shopware 2.1
// German language file
/***********************	progress:	status:	
modules/vouchers:			95%		work / calendar.js
modules/userunlock:			100%		untested
modules/userdetails:		100%		work
modules/systeminfo:			100%		work
modules/templatepreview:	100%		work
modules/user				100%		work
modules/transactions		95%		work / calendar.js
modules/uosreserveorder		100%		work
modules/templates			100%		work
modules/supplier			100%		work
modules/statistics			100%
modules/articles			100%
modules/browser				100%
modules/import
modules/import_xml
modules/instock
modules/live
modules/mailcampaigns
/*
Allgemein
*/
$sLang = array();
$sLang["index.php"]["dashboard"] = "Dashboard";
$sLang["index.php"]["current_online"] = "Aktuell online";
$sLang["index.php"]["account"] = "Ihr Shopware-Account";
/*
modules/vouchers
|_vouchers.php
*/
$sLang["vouchers"]["voucher_del"] = "Gutschein wurde gelöscht";
$sLang["vouchers"]["voucher_del_fail"] = "Gutschein konnte nicht gelöscht werden";
$sLang["vouchers"]["voucher_changesave"] = "&Auml;nderung wurde gespeichert";
$sLang["vouchers"]["voucher_del_confirm1"] = "Soll der Gutschein";
$sLang["vouchers"]["voucher_del_confirm2"] = "wirklich gel&ouml;scht werden?";
$sLang["vouchers"]["voucher_name"] = "Gutschein-Name";
$sLang["vouchers"]["voucher_voucher_code"] = "Gutschein-Code";
$sLang["vouchers"]["voucher_count"] = "Anzahl Gutscheine";
$sLang["vouchers"]["voucher_ordernumber"] = "Bestellnummer";
$sLang["vouchers"]["voucher_add_success"] = "Gutschein erfolgreich hinzugefügt!";
$sLang["vouchers"]["voucher_voucher_worth"] = "Gutschein-Wert";
$sLang["vouchers"]["voucher_code_number"] = "Erzeuge %s Gutschein-Codes";
$sLang["vouchers"]["voucher_save_error"] = "Beim Speichern ist ein Fehler aufgetreten!";
$sLang["vouchers"]["voucher_edit_success"] = "Gutschein erfolgreich geändert!";
$sLang["vouchers"]["voucher_fill"] = "Bitte füllen Sie folgende Felder aus";
$sLang["vouchers"]["voucher_modus"] = "Hinweise für Gutschein-Modus";
$sLang["vouchers"]["voucher_modus_general"] = "Modus - Allgemein gültig";
$sLang["vouchers"]["voucher_modus_code"] = "Es wird ein einheitlicher Gutschein-Code bereitgestellt";
$sLang["vouchers"]["voucher_modus_individual"] = "Modus - Individuelle Gutscheincodes";
$sLang["vouchers"]["voucher_modus_query"] = "Es werden soviele individuelle Gutschein-Codes erzeugt, wie Sie unter &quot;Stückzahl&quot; angeben. Jeder Kunde erhält also seinen eigenen, individuellen Code. Sie können eine Liste aller hinterlegten Codes im CSV-Format abrufen";
$sLang["vouchers"]["voucher_nocodeavailable"] = "Keine freien Codes mehr verfügbar!!!";
$sLang["vouchers"]["voucher_clickhere"] = "Klicken Sie %s hier %s um weitere %s Codes anzulegen";
$sLang["vouchers"]["voucher_generate_code"] = "|_ Erzeuge %s Gutschein-Codes";
$sLang["vouchers"]["voucher_msg_individual"] = "Dieser Gutschein wurde mit individuellen Codes angelegt. (%s von %s frei)";
$sLang["vouchers"]["voucher_excel_list"] = "Excel-Liste mit erzeugten Codes";
$sLang["vouchers"]["voucher_vouchers"] = "Gutscheine";
$sLang["vouchers"]["voucher_titel"] = "Bezeichnung:";
$sLang["vouchers"]["voucher_general_available"] = "Allgemein gültig:";
$sLang["vouchers"]["voucher_individual_vouchercodes"] = "Individuelle Gutscheincodes:";
$sLang["vouchers"]["voucher_piececount"] = "St&uuml;ckzahl:";
$sLang["vouchers"]["voucher_absolute_deduction"] = "Absoluter Abzug:";
$sLang["vouchers"]["voucher_proportional_deduction"] = "Prozentualer Abzug:";
$sLang["vouchers"]["voucher_worth"] = "Wert:";
$sLang["vouchers"]["voucher_least_turnover"] = "Mindestumsatz:";
$sLang["vouchers"]["voucher_redeemable_per_customer"] = "Einlösbar je Kunde:";
$sLang["vouchers"]["voucher_freeforward"] = "Versandkostenfrei:";
$sLang["vouchers"]["voucher_limited_manufacturers"] = "Beschr&auml;nkt auf Hersteller:";
$sLang["vouchers"]["voucher_no_manufacturer"] = "kein Hersteller";
$sLang["vouchers"]["voucher_valid_from"] = "G&uuml;ltig von:";
$sLang["vouchers"]["voucher_valid_until"] = "G&uuml;ltig bis:";
$sLang["vouchers"]["voucher_order_number"] = "Bestell-Nr.:";
$sLang["vouchers"]["voucher_save_voucher"] = "Gutschein speichern";
$sLang["vouchers"]["voucher_code"] = "Code";
$sLang["vouchers"]["voucher_cashed"] = "Eingelöst";
$sLang["vouchers"]["voucher_from"] = "von";
$sLang["vouchers"]["voucher_until"] = "bis";
$sLang["vouchers"]["voucher_options"] = "Optionen";
/*
modules/vouchers
|_skeleton.php
*/
$sLang["vouchers"]["skeleton_voucher"] = "Gutscheine";
$sLang["vouchers"]["skeleton_new_voucher"] = "Neuer Gutschein";
/*
modules/vouchers/js
|_calendar.js
*/
$sLang["vouchers"]["calendar_day_short"] = "'So1', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'";
$sLang["vouchers"]["calendar_day_med"] = "'Son1', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam'";
$sLang["vouchers"]["calendar_day_long"] = "'Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'";
$sLang["vouchers"]["calendar_month_short"] = "'Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'";
$sLang["vouchers"]["calendar_month_med"] = "'Jan', 'Feb', 'Mar', 'Apr', 'Mai', 'Juni', 'Juli', 'Aug', 'Sept', 'Okt', 'Nov', 'Dez'";
$sLang["vouchers"]["calendar_month_long"] = "'Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'";
/*
modules/userunlock
|_skeleton.php
*/
$sLang["userunlock"]["skeleton_customers_unlock"] = "Kunden freischalten";
/*
modules/userunlock
|_list.php
*/
$sLang["userunlock"]["list_customer_not_assigned"] = "Der Kunde wurde nicht zugeordnet";
$sLang["userunlock"]["list_customerlist"] = "Shopware Kundenliste";
$sLang["userunlock"]["list_the_customer"] = "Soll der Kunde";
$sLang["userunlock"]["list_customer_team"] = "wirklich der Kundengruppe";
$sLang["userunlock"]["list_assigned"] = "zugeordnet werden?";
$sLang["userunlock"]["list_rejected"] = "wirklich abgewiesen werden?";
$sLang["userunlock"]["list_status"] = "Status";
$sLang["userunlock"]["list_no_customer"] = "Keine Kunden gefunden";
$sLang["userunlock"]["list_reg_date"] = "Reg.Datum";
$sLang["userunlock"]["list_company"] = "Firma";
$sLang["userunlock"]["list_customer"] = "Kunde / Bestellungen";
$sLang["userunlock"]["list_postcode"] = "PLZ";
$sLang["userunlock"]["list_city"] = "Ort";
$sLang["userunlock"]["list_customer_group"] = "Kundengruppe";
$sLang["userunlock"]["list_status"] = "Status";
$sLang["userunlock"]["list_sError"] = "eMail-Template nicht gefunden";
$sLang["userunlock"]["list_sInform"] = "Der Kunde wurde zugeordnet und benachrichtigt";
/*
modules/userdetails
|_main.php
*/
$sLang["userdetails"]["main _no_user"] = "No user given";
$sLang["userdetails"]["main_user_deleted"] = "Benutzer wurde gelöscht";
$sLang["userdetails"]["main_change_payment"] = "Zahlungsart wurde geändert";
$sLang["userdetails"]["main_account_lock"] = "Konto wurde gesperrt";
$sLang["userdetails"]["main_account_unlock"] = "Konto wurde wieder freigeschaltet";
$sLang["userdetails"]["main_could_not_fetch"] = "Could not fetch maindata";
$sLang["userdetails"]["main_emailadress_changed"] = "eMail-Adresse wurde geändert";
$sLang["userdetails"]["main_emailadress_unavailable"] = "Diese eMail-Adresse ist bereits vergeben";
$sLang["userdetails"]["main_enter_valid_emailadress"] = "Bitte geben Sie eine gültige Mailadresse ein";
$sLang["userdetails"]["main_customergroup_changed"] = "Kundengruppe wurde geändert";
$sLang["userdetails"]["main_cant_change_customergroup"] = "Kundengruppe konnte nicht geändert werden";
$sLang["userdetails"]["main_new_password_least_six_characters"] = "Das neue Password muss aus mindestens 6 Zeichen bestehen";
$sLang["userdetails"]["main_type_your_password_two_times"] = "Bitte geben Sie das neue Password zur Bestätigung zweimal ein";
$sLang["userdetails"]["main_changes_saved"] = "Änderungen gespeichert";
$sLang["userdetails"]["main_changes_not_saved"] = "Änderungen konnten nicht gespeichert werden";
$sLang["userdetails"]["main_could_not_fetch_billing-adress"] = "Could not fetch billing-address";
$sLang["userdetails"]["main_search"] = "Suche";
$sLang["userdetails"]["main_should_the_customer"] = "Soll der Kunde";
$sLang["userdetails"]["main_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["userdetails"]["main_please_enter_email"] = "Bitte geben Sie eine eMail-Adresse ein";
$sLang["userdetails"]["main_data"] = "Stammdaten";
$sLang["userdetails"]["main_Properties"] = "Konto - Eigenschaften";
$sLang["userdetails"]["main_emailadress"] = "eMail-Adresse:";
$sLang["userdetails"]["main_change_password"] = "Passwort ändern:";
$sLang["userdetails"]["main_change_password_confirm"] = "Passwort ändern Bestätigung:";
$sLang["userdetails"]["main_customergroup"] = "Kundengruppe:";
$sLang["userdetails"]["main_customernummber"] = "Kundennummer:";
$sLang["userdetails"]["main_customeraccount"] = "KEIN KUNDENKONTO! SCHNELLBESTELLUNG";
$sLang["userdetails"]["main_delet_account"] = "Konto löschen";
$sLang["userdetails"]["main_disable_account"] = "Konto deaktivieren";
$sLang["userdetails"]["main_enable_account"] = "Konto aktivieren";
$sLang["userdetails"]["main_registrated_since"] = "Angemeldet seit:";
$sLang["userdetails"]["main_last_login"] = "Letzter Login:";
$sLang["userdetails"]["main_orders_since_registration"] = "Bestellungen seit Anmeldung:";
$sLang["userdetails"]["main_Turnover_since_registration"] = "Umsatz seit Anmeldung:";
$sLang["userdetails"]["main_Paymentfailures_since_registration"] = "Zahlungsausfälle seit Anmeldung:";
$sLang["userdetails"]["main_Payment_Address"] = "Rechnungsadresse";
$sLang["userdetails"]["main_key_data"] = "Stammdaten";
$sLang["userdetails"]["main_Title"] = "Anrede:";
$sLang["userdetails"]["main_mr"] = "Herr";
$sLang["userdetails"]["main_ms"] = "Frau";
$sLang["userdetails"]["main_company"] = "Firma";
$sLang["userdetails"]["main_company_1"] = "Firma:";
$sLang["userdetails"]["main_Department"] = "Abteilung:";
$sLang["userdetails"]["main_firstname"] = "Vorname:";
$sLang["userdetails"]["main_lastname"] = "Nachname:";
$sLang["userdetails"]["main_street"] = "Strasse:";
$sLang["userdetails"]["main_house"] = "Hausnummer:";
$sLang["userdetails"]["main_Postal_Code"] = "PLZ:";
$sLang["userdetails"]["main_city"] = "Ort:";
$sLang["userdetails"]["main_phone"] = "Telefon:";
$sLang["userdetails"]["main_fax"] = "Fax:";
$sLang["userdetails"]["main_tax"] = "Ust.Id:";
$sLang["userdetails"]["main_country"] = "Land:";
$sLang["userdetails"]["main_free_text"] = "Freitext-Felder";
$sLang["userdetails"]["main_field_1"] = "Feld 1:";
$sLang["userdetails"]["main_field_2"] = "Feld 2:";
$sLang["userdetails"]["main_field_3"] = "Feld 3:";
$sLang["userdetails"]["main_field_4"] = "Feld 4:";
$sLang["userdetails"]["main_field_5"] = "Feld 5:";
$sLang["userdetails"]["main_field_6"] = "Feld 6:";
$sLang["userdetails"]["main_Address"] = "Lieferadresse";
$sLang["userdetails"]["main_Payment"] = "Zahlungsart";
$sLang["userdetails"]["main_selected_Payment"] = "Gewählte Zahlungsart:";
/*
modules/userdetails
|_details.php
*/
$sLang["userdetails"]["details_number"] = "Nummer";
$sLang["userdetails"]["details_Manufacturer"] = "Hersteller";
$sLang["userdetails"]["details_Article"] = "Artikel";
$sLang["userdetails"]["details_price"] = "Preis";
$sLang["userdetails"]["details_active"] = "Aktiv";
$sLang["userdetails"]["details_date"] = "Datum";
$sLang["userdetails"]["details_ordernumber"] = "Bestellnummer";
$sLang["userdetails"]["details_Contract_value"] = "Auftragswert";
$sLang["userdetails"]["details_status"] = "Status";
/*
modules/userdetails
|_orders.php
*/
$sLang["userdetails"]["orders_no_orders"] = "Noch keine Bestellungen";
$sLang["userdetails"]["orders_date"] = "Datum";
$sLang["userdetails"]["orders_ordernumber"] = "Bestellnr.";
$sLang["userdetails"]["orders_orderstatus"] = "Bestellstatus";
$sLang["userdetails"]["orders_payment_status"] = "Zahlstatus";
$sLang["userdetails"]["orders_amount"] = "Gesamtbetrag";
/*
modules/userdetails
|_skeleton.php
*/
$sLang["userdetails"]["skeleton_user_not_found"] = "Fehler: Benutzer nicht gefunden";
$sLang["userdetails"]["skeleton_customer_login"] = "Kundenkonto";
$sLang["userdetails"]["skeleton_KeyData"] = "Stammdaten";
$sLang["userdetails"]["skeleton_orders"] = "Bestellungen";
$sLang["userdetails"]["skeleton_Turnover"] = "Umsatz";
$sLang["userdetails"]["skeleton_save_changes"] = "&Auml;nderungen speichern";
/*
modules/userdetails
|_statistics.php
*/
$sLang["userdetails"]["statistics_search"] = "Suche";
$sLang["userdetails"]["statistics_Total_sales_since_registration"] = "Gesamtumsatz seit Anmeldung:";
/*
modules/useradd
|_main.php
*/
$sLang["useradd"]["window_title_add_user"] = "Kunde anlegen";

$sLang["useradd"]["btn_menu_add_addition_user"] = "Weiteren Kunden anlegen";
$sLang["useradd"]["btn_menu_open_useraccount"] = "Kundenkonto öffnen";
$sLang["useradd"]["btn_menu_order_with_user"] = "Bestellung mit dem Kundenkonto durchführen";

$sLang["useradd"]["skeleton_KeyData"] = "Stammdaten";
$sLang["useradd"]["billing_address"] = "Rechnungsadresse";
$sLang["useradd"]["delivery_address"] = "Lieferadresse";
$sLang["useradd"]["kind_of_payment"] = "Zahlungsart";

$sLang["useradd"]["mail_address"] = "eMail-Adresse:";
$sLang["useradd"]["password"] = "Passwort:";
$sLang["useradd"]["list_customergroup"] = "Kundengruppe:";
$sLang["useradd"]["list_shop"] = "Shop:";
$sLang["useradd"]["title"] = "Anrede:";
$sLang["useradd"]["mister"] = "Herr";
$sLang["useradd"]["miss"] = "Frau";
$sLang["useradd"]["company"] = "Firma";
$sLang["useradd"]["department"] = "Abteilung:";
$sLang["useradd"]["firstname"] = "Vorname:";
$sLang["useradd"]["lastname"] = "Nachname:";
$sLang["useradd"]["street"] = "Strasse:";
$sLang["useradd"]["house_no"] = "Hausnummer:";
$sLang["useradd"]["postal_Code"] = "PLZ:";
$sLang["useradd"]["city"] = "Ort:";
$sLang["useradd"]["phone"] = "Telefon:";
$sLang["useradd"]["fax"] = "Fax:";
$sLang["useradd"]["tax"] = "Ust.Id:";
$sLang["useradd"]["country"] = "Land:";

$sLang["useradd"]["pay_surname"] = "Nachname:";
$sLang["useradd"]["pay_credit_card"] = "Kreditkarte UOS:";
$sLang["useradd"]["pay_debit_advice"] = "Lastschrift:";
$sLang["useradd"]["pay_debit_advice_uos"] = "Lastschrift UOS:";
$sLang["useradd"]["pay_giro_account"] = "Girokonto:";
$sLang["useradd"]["pay_calculation"] = "Rechnung:";
$sLang["useradd"]["pay_calculation_uos"] = "Rechnung UOS:";
$sLang["useradd"]["pay_ipayment"] = "iPayment:";
$sLang["useradd"]["pay_paypal"] = "PayPal:";
$sLang["useradd"]["pay_prepayment"] = "Vorkasse UOS:";
$sLang["useradd"]["pay_immedi_assignment"] = "Sofort-Überweisung:";
$sLang["useradd"]["pay_united_transfer"] = "United Transfer:";
$sLang["useradd"]["pay_united_transfer_giro_account"] = "United Transfer Direkt Giropay:";
$sLang["useradd"]["pay_united_transfer_credit_card"] = "United Transfer Direkt Kreditkarte:";
$sLang["useradd"]["pay_ut_direct_debit_advice"] = "UT Direkt Lastschrift:";
$sLang["useradd"]["pay_united_transfer_prepayment"] = "United Transfer Direkt Vorkasse:";

$sLang["useradd"]["account"] = "Kontonummer:";
$sLang["useradd"]["bankcode"] = "BLZ:";
$sLang["useradd"]["bankname"] = "Bankname:";
$sLang["useradd"]["bankholder"] = "Inhaber:";

$sLang["useradd"]["add_user"] = "Kunde anlegen";

$sLang["useradd"]["magic_key_autocomplete"] = "Daten aus der Rechnungsadresse übernehmen";
/*
modules/systeminfo
|_sSystemCheck.php
*/
$sLang["systeminfo"]["sSystemCheck_system_check"] = "System-Check-Tool";
$sLang["systeminfo"]["sSystemCheck_name"] = "Name";
$sLang["systeminfo"]["sSystemCheck_needed"] = "Benötigt";
$sLang["systeminfo"]["sSystemCheck_available"] = "Vorhanden";
$sLang["systeminfo"]["sSystemCheck_status"] = "Status";
$sLang["systeminfo"]["sSystemCheck_directory"] = "Verzeichnis";
$sLang["systeminfo"]["sSystemCheck_function"] = "Funktion";
$sLang["systeminfo"]["sSystemCheck_browser"] = "Browser";
$sLang["systeminfo"]["sSystemCheck_firefox"] = "Firefox";
$sLang["systeminfo"]["sSystemCheck_safari"] = "Safari";
$sLang["systeminfo"]["sSystemCheck_javascript"] = "Javascript";
$sLang["systeminfo"]["sSystemCheck_flash"] = "Flash";
$sLang["systeminfo"]["sSystemCheck_cookies"] = "Cookies";
$sLang["systeminfo"]["sSystemCheck_not_installed"] = "not installed";
$sLang["systeminfo"]["sSystemCheck_not_enabled"] = "not enabled";
$sLang["systeminfo"]["sSystemCheck_not_passed"] = "not passed";
$sLang["systeminfo"]["sSystemCheck_not_found"] = "not found";
$sLang["systeminfo"]["sSystemCheck_not_readable"] = "not readable";
$sLang["systeminfo"]["sSystemCheck_not_writeable"] = "not writable";
/*
modules/systeminfo
|_skeleton.php
*/
$sLang["systeminfo"]["skeleton_systeminfo"] = "Systeminfo";
/*
modules/templatepreview
|_skeleton.php
*/
$sLang["templatepreview"]["skeleton_preview"] = "Vorschau Template";
/*
modules/user
|_skeleton.php
*/
$sLang["user"]["skeleton_customerlist"] = "Kundenliste";
/*
modules/user
|list.php
$sLang["user"]["list_customerlist"] = "Shopware Kundenliste";
$sLang["user"]["list_status"] = "Status";
$sLang["user"]["list_no_customer"] = "Keine Kunden gefunden";
$sLang["user"]["list_customer_number"] = "K.Nr.";
$sLang["user"]["list_registration_date"] = "Reg.Datum";
$sLang["user"]["list_company"] = "Firma";
$sLang["user"]["list_name"] = "Kunde / Bestellungen";
$sLang["user"]["list_zip"] = "PLZ";
$sLang["user"]["list_city"] = "Ort";
$sLang["user"]["list_customergroup"] = "Kundengruppe";
$sLang["user"]["list_result"] = "Ergebnisse";
$sLang["user"]["list_until"] = "bis";
$sLang["user"]["list_from"] = "von";
$sLang["user"]["list_customer"] = "Kunden";
$sLang["user"]["list_site"] = "Seite";
$sLang["user"]["list_back"] = "Zurück";
$sLang["user"]["list_forward"] = "Vor";
*/
/*
modules/user
|user.php
*/
$sLang["user"]["user_confirmation"] = "Bestätigung";
$sLang["user"]["user_delete_agree"] = "Sollen die markierten Artikel wirklich gelöscht werden?";
$sLang["user"]["user_tab_head"] = "Kundendaten";
$sLang["user"]["user_user_list"] = "User-List";
$sLang["user"]["user_customer_number"] = "Kundennummer";
$sLang["user"]["user_data"] = "Datum";
$sLang["user"]["user_company"] = "Firma";
$sLang["user"]["user_firstname"] = "Vorname";
$sLang["user"]["user_lastname"] = "Nachname";
$sLang["user"]["user_zip"] = "PLZ";
$sLang["user"]["user_city"] = "Ort";
$sLang["user"]["user_ordercount"] = "Anz. Bestellungen";
$sLang["user"]["user_total"] = "Ges. Umsatz";
$sLang["user"]["user_options"] = "Optionen";
$sLang["user"]["user_customer_overview"] = "Kunden - Übersicht";
$sLang["user"]["user_customer"] = "Kunden:";
$sLang["user"]["user_customer_total"] = "Gesamt:";
$sLang["user"]["user_no_customer_view"] = "Keine Kunden in Ansicht";
$sLang["user"]["user_search"] = "Suche:";
$sLang["user"]["user_test"] = "Test";
$sLang["user"]["user_refresh"] = "Aktualisieren";
$sLang["user"]["user_filters"] = "Filtern nach Kundengruppe";
$sLang["user"]["user_show_all"] = "Alle anzeigen";
/*
modules/user
|_details.php
*/
$sLang["user"]["details_number"] = "Nummer";
$sLang["user"]["details_manufacturer"] = "Hersteller";
$sLang["user"]["details_article"] = "Artikel";
$sLang["user"]["details_price"] = "Preis";
$sLang["user"]["details_date"] = "Datum";
$sLang["user"]["details_ordernumber"] = "Bestellnummer";
$sLang["user"]["details_orderworth"] = "Auftragswert";
$sLang["user"]["details_status"] = "Status";
$sLang["user"]["details_no_Data"] = "Keine Daten";
$sLang["user"]["details_in_constuction"] = "In Bearbeitung";
$sLang["user"]["details_active"] = "Aktiv";
/*
modules/user
|_skeleton.php
*/
$sLang["user"]["skeleton_customerlist"] = "Kundenliste";
/*
modules/transactions
|_skeleton.php
*/
$sLang["transactions"]["skeleton_epayment"] = "ePayment";
/*
modules/transactions
|_transactions.php
*/
$sLang["transactions"]["transactions_date"] = "Datum";
$sLang["transactions"]["transactions_ordernumber"] = "Bestellnr";
$sLang["transactions"]["transactions_transaction"] = "Transaktion";
$sLang["transactions"]["transactions_status"] = "Bestellstatus";
$sLang["transactions"]["transactions_payment_status"] = "Zahlstatus";
$sLang["transactions"]["transactions_payment_total"] = "Gesamtbetrag";
$sLang["transactions"]["transactions_costumer"] = "Kunde";
$sLang["transactions"]["transactions_evaluation_from"] = "Auswertung von:";
$sLang["transactions"]["transactions_evaluation_until"] = "Auswertung bis:";
$sLang["transactions"]["transactions_status"] = "Status:";
$sLang["transactions"]["transactions_paymentstatus"] = "Bezahlstatus:";
$sLang["transactions"]["transactions_showall"] = "Alle anzeigen";
$sLang["transactions"]["transactions_search"] = "Suche (Nr./Transaktion)";
$sLang["transactions"]["transactions_refresh"] = "Ansicht aktualisieren";
$sLang["transactions"]["transactions_tip"] = "Hinweis: Durch Doppelklick auf den Status lässt sich dieser ändern";
$sLang["transactions"]["transactions_total_period"] = "Gesamtumsatz in Zeitraum:";
$sLang["transactions"]["transactions_number_of_orders"] = "Anzahl Bestellungen: ";
$sLang["transactions"]["transactions_order_status"] = "Der Status der Bestellung";
$sLang["transactions"]["transactions_order_status_has"] = "wurde auf";
$sLang["transactions"]["transactions_order_status_changed"] = "geändert!";
$sLang["transactions"]["transactions_order_status_no_refresh"] = "Status konnte nicht aktualisiert werden";
$sLang["transactions"]["transactions_order_cant_load"] = "Beschreibung konnte nicht geladen werden";
$sLang["transactions"]["transactions_order_cant_find_id"] = "Bestell-ID konnte nicht ermittelt werden";
$sLang["transactions"]["transactions_cant_refresh_status"] = "Status konnte nicht aktualisiert werden";
/*
modules/uosreserveorder
|_skeleton.php
*/
$sLang["uosreserveorder"]["skeleton_Payments_reserved"] = "Reservierte Zahlungen";
/*
modules/uosreserveorder
|_transactions.php
*/
$sLang["uosreserveorder"]["transactions_reorder"] = "Reorder TreePanel";
$sLang["uosreserveorder"]["transactions_status"] = "Status";
$sLang["uosreserveorder"]["transactions_no_orders_found"] = "Keine Bestellungen gefunden";
$sLang["uosreserveorder"]["transactions_date"] = "Datum";
$sLang["uosreserveorder"]["transactions_ordernumber"] = "Bestellnr.";
$sLang["uosreserveorder"]["transactions_Action"] = "Transaktion.";
$sLang["uosreserveorder"]["transactions_order_status"] = "Bestellstatus";
$sLang["uosreserveorder"]["transactions_payment_status"] = "Zahlstatus";
$sLang["uosreserveorder"]["transactions_total"] = "Gesamtbetrag";
$sLang["uosreserveorder"]["transactions_customer"] = "Kunde";
$sLang["uosreserveorder"]["transactions_options"] = "Optionen";
$sLang["uosreserveorder"]["transactions_KK_free"] = "KK freigeben";
$sLang["uosreserveorder"]["transactions_LA_free"] = "LA freigeben";
$sLang["uosreserveorder"]["transactions_Already_booked"] = "Gebucht";
$sLang["uosreserveorder"]["transactions_Period_end"] = "Frist abgelaufen";
$sLang["uosreserveorder"]["transactions_Evaluation_of"] = "Auswertung von:";
$sLang["uosreserveorder"]["transactions_Evaluation_until"] = "Auswertung bis:";
$sLang["uosreserveorder"]["transactions_Booking_Status"] = "Buchungsstatus:";
$sLang["uosreserveorder"]["transactions_show_all"] = "Alle anzeigen";
$sLang["uosreserveorder"]["transactions_open_bookings"] = "Offene Buchungen";
$sLang["uosreserveorder"]["transactions_Completed_bookings"] = "Abgeschlossene Buchungen";
$sLang["uosreserveorder"]["transactions_status_1"] = "Status:";
$sLang["uosreserveorder"]["transactions_status_payment"] = "Bezahlstatus:";
$sLang["uosreserveorder"]["transactions_search"] = "Suche (Nr./Transaktion)";
$sLang["uosreserveorder"]["transactions_refresh_view"] = "Ansicht aktualisieren";
$sLang["uosreserveorder"]["transactions_attention"] = "Hinweis: Durch Doppelklick auf den Status lässt sich dieser ändern";
$sLang["uosreserveorder"]["transactions_total_in_period"] = "Gesamtumsatz in Zeitraum:";
$sLang["uosreserveorder"]["transactions_count_of_orders"] = "Anzahl Bestellungen:";
$sLang["uosreserveorder"]["transactions_cant_load_Description"] = "Beschreibung konnte nicht geladen werden";
$sLang["uosreserveorder"]["transactions_cant_load_orderID"] = "Bestell-ID konnte nicht ermittelt werden";
$sLang["uosreserveorder"]["transactions_cant_refresh_status"] = "Status konnte nicht aktualisiert werden";
$sLang["uosreserveorder"]["transactions_status_order"] = "Der Status der Bestellung";
$sLang["uosreserveorder"]["transactions_has_left"] = "wurde auf";
$sLang["uosreserveorder"]["transactions_changed"] = "geändert!";
/*
modules/uosreserveorder/action
|_skeleton.php
*/
$sLang["uosreserveorder"]["action_skeleton_reserved_booking"] = "Reservierte Buchung";
/*
modules/uosreserveorder/action
|_transactions.php
*/
//52
$sLang["uosreserveorder"]["action_transaction_too_high"] = "Der Betrag ist zu hoch!";
//55
$sLang["uosreserveorder"]["action_transaction_Invalid_procedures"] = "Ungültiges Zahlverfahren!";
//153
$sLang["uosreserveorder"]["action_transaction_error_booking"] = "Es ist ein Fehler bei der Buchung aufgetreten";
$sLang["uosreserveorder"]["action_transaction_booking"] = "Der Betrag wurde gebucht";
$sLang["uosreserveorder"]["action_transaction_customer"] = "Kunde:";
$sLang["uosreserveorder"]["action_transaction_order_date"] = "Bestelldatum:";
$sLang["uosreserveorder"]["action_transaction_order_number"] = "Bestellnummer:";
$sLang["uosreserveorder"]["action_transaction_Transaction_number"] = "Transaktionsnr.:";
$sLang["uosreserveorder"]["action_transaction_total"] = "Gesamtbetrag:";
$sLang["uosreserveorder"]["action_transaction_payment"] = "Zahlverfahren:";
$sLang["uosreserveorder"]["action_transaction_Booking_already_done"] = "Buchung wurde durchgef&uuml;hrt!";
$sLang["uosreserveorder"]["action_transaction_max_amount"] = "Max. zu buchender Betrag:";
$sLang["uosreserveorder"]["action_transaction_Unique_Booking"] = "Einmalige Buchung:";
$sLang["uosreserveorder"]["action_transaction_New_reserve"] = "Restbetrag neu reservieren:";
$sLang["uosreserveorder"]["action_transaction_delete_Booking"] = "Buchung l&ouml;schen:";
$sLang["uosreserveorder"]["action_transaction_no_order_found"] = "Keine Bestellung gefunden!";
/*
modules/templates
|_skeleton.php
*/
$sLang["templates"]["skeleton_templatebrowser"] = "Templateauswahl";
/*
modules/templates
|_templates.php
*/
$sLang["templates"]["templates_template_changed"] = "Das Standardtemplate wurde geändert";
$sLang["templates"]["templates_set_active"] = "wirklich aktiv gesetzt werden?";
$sLang["templates"]["templates_template_selection"] = "Template-Auswahl";
$sLang["templates"]["templates_select_template"] = "Wählen Sie hier Ihr gewünschtes Template aus";
$sLang["templates"]["templates_every_template"] = "Jedes dieser Templates kann individuell angepasst werden!";
$sLang["templates"]["templates_more_informations"] = "Weitere Informationen";
$sLang["templates"]["templates_tip"] = "Hinweis:";
$sLang["templates"]["templates_solid_template"] = "Diesem Shop wurde ein festes Template zugewiesen. Bitte sprechen Sie Ihren Partner bei Änderungswünschen an.";
$sLang["templates"]["templates_tips"] = "Hinweise:";
$sLang["templates"]["templates_click_on_link"] = "Klicken Sie auf den &quot;Vorschau-Link&quot; um Ihren Shop ausgebiebig mit dem jeweiligem Template testen zu können";
$sLang["templates"]["templates_click_on_end"] = "Wichtig: Klicken Sie auf &quot;Vorschau beenden&quot;, um wieder Ihr Standard-Shoptemplate zu sehen.";
$sLang["templates"]["templates_preview_end"] = "Vorschau beenden";
$sLang["templates"]["templates_please_note"] = "Bitte beachten Sie, das Banner und andere Grafiken unter Umständen für das neue Template angepasst werden müssen";
$sLang["templates"]["templates_preview"] = "Vorschau";
$sLang["templates"]["templates_select"] = "Auswählen";
$sLang["templates"]["templates_template"] = "Template:";
$sLang["templates"]["templates_selection"] = "Ausgewählt";
/*
modules/supplier
|_skeleton.php
*/
$sLang["templates"]["skeleton_supplieradministration"] = "Hersteller";
$sLang["templates"]["skeleton_save_changes"] = "&Auml;nderungen speichern";
$sLang["templates"]["skeleton_new_supplier"] = "Neuer Hersteller";
/*
modules/supplier
|_hersteller.php
*/
$sLang["supplier"]["hersteller_upload_error"] = "Fehler bei Upload";
$sLang["supplier"]["hersteller_save_successfull"] = "Hersteller wurde erfolgreich gespeichert";
$sLang["supplier"]["hersteller_save_error"] = "Hersteller konnte NICHT angelegt werden";
$sLang["supplier"]["hersteller_save_enter_supplier"] = "Bitte geben Sie den Namen des Herstellers ein";
$sLang["supplier"]["hersteller_supplier_deleted"] = "Hersteller wurde gelöscht";
$sLang["supplier"]["hersteller_supplier_not_deleted"] = "Hersteller konnte nicht gelöscht werden";
$sLang["supplier"]["hersteller_hamann-media"] = "shopware AG";
$sLang["supplier"]["hersteller_2007_hamann"] = "2009, shopware AG";
$sLang["supplier"]["hersteller_eBusiness"] = "shopware AG - eBusiness-Spezialist aus dem Muensterland";
$sLang["supplier"]["hersteller_eMail"] = "info@shopware.ag";
//159
$sLang["supplier"]["hersteller_this_supplier"] = "Diesem Hersteller sind";
//161
$sLang["supplier"]["hersteller_articles_assigned"] = "Artikel zugeordnet. Löschen erst nach Aufheben der Zuordnung möglich!";
$sLang["supplier"]["hersteller_the_supplier"] = "Soll der Hersteller";
$sLang["supplier"]["hersteller_really_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["supplier"]["hersteller_supplier"] = "Supplier";
$sLang["supplier"]["hersteller_new_supplier"] = "Neuer Hersteller";
$sLang["supplier"]["hersteller_edit_supplier"] = "Hersteller bearbeiten";
$sLang["supplier"]["hersteller_supplier_name"] = "Herstellername:";
$sLang["supplier"]["hersteller_supplier_picture"] = "Herstellerlogo:";
$sLang["supplier"]["hersteller_supplier_homepage"] = "Hersteller-Webseite:";
$sLang["supplier"]["hersteller_supplier_1"] = "Hersteller";
$sLang["supplier"]["hersteller_logo"] = "Logo";
$sLang["supplier"]["hersteller_options"] = "Optionen";
/*
modules/statistics
|_skeleton.php
*/
$sLang["statistics"]["skeleton_stat"] = "Statistiken / Diagramme";
/*
modules/statistics
|_statistics.php
*/
$sLang["statistics"]["statistics_stat"] = "core.statistics";
/*
modules/statistics
|_charts.php
*/
$sLang["statistics"]["charts_no_licence"] = "NICHT LIZENZIERT";
$sLang["statistics"]["charts_additional_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["statistics"]["charts_display_no_charts"] = "In der NICHT lizenzierten Version werden keine grafischen Charts angezeigt";
$sLang["statistics"]["charts_more_informations"] = "Weitere Informationen:";
$sLang["statistics"]["charts_module_Features"] = "Modul-Vorstellung";
$sLang["statistics"]["charts_module_buy"] = "Modul mieten/kaufen";
$sLang["statistics"]["charts_please_wait"] = "Bitte w&auml;hlen...";
$sLang["statistics"]["charts_today"] = "Heute";
$sLang["statistics"]["charts_this_week"] = "Diese Woche";
$sLang["statistics"]["charts_this_month"] = "Diesen Monat";
$sLang["statistics"]["charts_last_seven_days"] = "Letzten 7 Tage";
$sLang["statistics"]["charts_last_14_days"] = "Letzten 14 Tage";
$sLang["statistics"]["charts_last_30_days"] = "Letzten 30 Tage";
$sLang["statistics"]["charts_kw"] = "Kalenderwochen";
$sLang["statistics"]["charts_valid_from"] = "Gültig von:";
$sLang["statistics"]["charts_refresh"] = "Aktualisieren";
$sLang["statistics"]["charts_until"] = "bis";
$sLang["statistics"]["charts_Calendar"] = "Kalenderwoche";
$sLang["statistics"]["charts_and"] = "und";
$sLang["statistics"]["charts_array"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["charts_no_data"] = "F&uuml;r diese Darstellung liegen noch keine Daten vor.";
$sLang["statistics"]["charts_tip"] = "Hinweise:";
$sLang["statistics"]["charts_chart_Representation"] = "Chart-Darstellung";
$sLang["statistics"]["charts_display_tables"] = "Tabellen-Darstellung";
$sLang["statistics"]["charts_download_excelfile"] = "Exceldatei downloaden";
$sLang["statistics"]["charts_valid_until"] = "Gültig bis:";
/*
modules/statistics/data
|_amount.php
*/

$sLang["statistics"]["amount_array"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["amount_until"] = "bis";
$sLang["statistics"]["amount_Calendar"] = "Kalenderwoche";
$sLang["statistics"]["amount_turnover"] = "Umsatz";
$sLang["statistics"]["amount_hits"] = "Hits";
$sLang["statistics"]["amount_visits"] = "Visits";
$sLang["statistics"]["amount_week"] = "Woche";
$sLang["statistics"]["amount_array_1"] = 
		array(
			array("header"=>"Woche","name"=>"Woche","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz","name"=>"Umsatz","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Hits","name"=>"Hits","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Visits","name"=>"Visits","sortable"=>"false","width"=>150,"summaryType"=>"sum")
		);
/*
modules/statistics/data
|_amount_cat.php
*/
$sLang["statistics"]["amount_cat_array_month_short"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
/*
modules/statistics/data
|_amount_cat2.php
*/
$sLang["statistics"]["amount_cat2_array_monath_short"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
/*
modules/statistics/data
|_amount_daytime.php
*/
$sLang["statistics"]["amount_daytime_month"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["amount_daytime_day"] = array("","Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");
$sLang["statistics"]["amount_daytime_from"] = "von";
$sLang["statistics"]["amount_daytime_until"] = "bis";
$sLang["statistics"]["amount_daytime_time"] = "Uhrzeit";
$sLang["statistics"]["amount_daytime_turnover"] = "Umsatz";
$sLang["statistics"]["amount_daytime_turnover_time"] = "Umsatz nach Uhrzeit";
/*
modules/statistics/data
|_amount_month.php
*/
$sLang["statistics"]["amount_month_month_Short"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["amount_month_turnover_month"] = "Umsatz nach Monaten";
/*
modules/statistics/data
|_amount_supplier.php
*/
$sLang["statistics"]["amount_supplier"] = "Umsatz nach Herstellern";
/*
modules/statistics/data
|_amount_partner.php
*/
$sLang["statistics"]["amount_partner_partner_turnover"] = "Partner nach Umsatz";
$sLang["statistics"]["amount_partner_turnover"] = "Umsatz";
/*
modules/statistics/data
|_amount_user.php
*/
$sLang["statistics"]["amount_user_header"] = array(
		array("header"=>"ID","name"=>"id","sortable"=>"false","width"=>150),
			array("header"=>"Kundennr.","name"=>"Kundennr.","sortable"=>"false","width"=>150),
			array("header"=>"Kunde","name"=>"Name","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz","name"=>"Umsatz","sortable"=>"false","width"=>150)
);
/*
modules/statistics/data
|_amount_week.php
*/
$sLang["statistics"]["amount_week_month"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["amount_week_Turnover"] = "Umsatz nach Kalenderwochen";
/*
modules/statistics/data
|_amount_weekday.php
*/
$sLang["statistics"]["amount_weekday_month"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["amount_weekday_days"] = array("","Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");	
$sLang["statistics"]["amount_weekday_turnover_days"] = "Umsatz nach Wochentagen";
$sLang["statistics"]["amount_weekday_turnover"] = "Umsatz";
/*
modules/statistics/data
|_article.views.sales.php
*/
$sLang["statistics"]["article.views.sales_header"] = 
	array(
		array("header"=>"Bestellnr.","name"=>"ordernumber","sortable"=>"false","width"=>150),
		array("header"=>"Namen","name"=>"name","sortable"=>"false","width"=>150),
		array("header"=>"Scoring","name"=>"Scoring","sortable"=>"false","width"=>150),
		array("header"=>"Aufrufe","name"=>"impressions","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
		array("header"=>"Bestellungen","name"=>"sales","sortable"=>"false","width"=>150,"summaryType"=>"sum")
	);
/*
modules/statistics/data
|_basket.php
*/
$sLang["statistics"]["basket_month"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["basket_headers"] = 
		array(
			array("header"=>"Datum","name"=>"Datum","sortable"=>"false","width"=>150,"date"=>true),
			array("header"=>"Bestellungen","name"=>"Bestellungen","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Basket","name"=>"Basket","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Hits","name"=>"Hits","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Visits","name"=>"Visits","sortable"=>"false","width"=>150,"summaryType"=>"sum")
		);
/*
modules/statistics/data
|_basket_relative.php
*/
$sLang["statistics"]["basket_relative_month"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["basket_relative_header"] = array(
			array("header"=>"Datum","name"=>"Datum2","sortable"=>"false","width"=>150,"date"=>true),
			array("header"=>"Rate","name"=>"Rate","sortable"=>"false","width"=>150),
			array("header"=>utf8_encode("Warenkörbe"),"name"=>"Warenkoerbe","sortable"=>"false","width"=>150,"summaryType"=>"sum"),
			array("header"=>"Visits","name"=>"Visits","sortable"=>"false","width"=>150,"summaryType"=>"sum")
		);
/*
modules/statistics/data
|_condata.php
*/
$sLang["statistics"]["condata_month"] = array("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["condata_order"] = "Bestellungen";
$sLang["statistics"]["condata_basket"] = "Abgr. Warenkörbe";
$sLang["statistics"]["condata_new_customer"] = "Neukunden";
$sLang["statistics"]["condata_new_hit"] = "Hits";
$sLang["statistics"]["condata_order_basket_newcustomer"] = "Bestellungen/Warenkörbe/Neukunden";
$sLang["statistics"]["condata_hits_visits"] = "Hits/Visits";
$sLang["statistics"]["condata_visits"] = "Visits";
$sLang["statistics"]["condata_conversion_data"] = "Conversion Data";
/*
modules/statistics/data
|_conversion.php
*/
$sLang["statistics"]["conversion_until"] = "bis";
$sLang["statistics"]["conversion_week"] = "Kalenderwoche";
$sLang["statistics"]["conversion_array"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["conversion_order_conversion_rate"] = "Order Conversion Rate";
/*
modules/statistics/data
|_forecast.php
*/
$sLang["statistics"]["forecast_this_week"] = "Diese Woche";
$sLang["statistics"]["forecast_last_week"] = "Letzte Woche";
$sLang["statistics"]["forecast_this_month"] = "Diesen Monat";
$sLang["statistics"]["forecast_last_month"] = "Letzten Monat";
$sLang["statistics"]["forecast_last_year_same_month"] = "Letztes Jahr selber Monat";
$sLang["statistics"]["forecast_Forecast_month"] = "Prognose für diesen Monat";
$sLang["statistics"]["forecast_Forecast_week"] = "Prognose für diese Woche";
/*
modules/statistics/data
|_new_old_user.php
*/
$sLang["statistics"]["new_old_user_header"] = array(
		array("header"=>"Kalenderwoche","name"=>"Woche","sortable"=>"false","width"=>150),
		array("header"=>"Bestellungen","name"=>"Bestellungen","sortable"=>"false","width"=>150),
		array("header"=>"Neukunden","name"=>"Anteil Neukunden","sortable"=>"false","width"=>150),
		array("header"=>"Stammkunden","name"=>"Anteil Stammkunden","sortable"=>"false","width"=>150)
	);
$sLang["statistics"]["new_old_user_Loyalty"] = "Stammkunden";
$sLang["statistics"]["new_old_user_new_customer"] = "Neukunden";
$sLang["statistics"]["new_old_user_share_new_customer"] = "Anteil Neu-/Stammkunden";
$sLang["statistics"]["new_old_user_until"] = "bis";
$sLang["statistics"]["new_old_user_Week"] = "Kalenderwoche";
/*
modules/statistics/data
|_order_user.php
*/
$sLang["statistics"]["order_user_month"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
/*
modules/statistics/data
|_referer.php
*/
$sLang["statistics"]["referer_month"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["referer_badword"] = array(
	"www", "http", "ab", "die", "der", "und", "in", "zu", "den", "das", "nicht", "von", "sie", "ist", "des", "sich", "mit", "dem", "dass", "er", "es", "ein", "ich", "auf", "so", "eine", "auch", "als", "an", "nach", "wie", "im", "für", "einen", "um", "werden", "mehr", "zum", "aus", "ihrem", "style", "oder", "neue", "spieler", "können", "wird", "sind", "ihre", "einem", "of", "du", "sind", "einer", "über", "alle", "neuen", "bei", "durch", "kann", "hat", "nur", "noch", "zur", "gegen", "bis", "aber", "haben", "vor", "seine", "ihren", "jetzt", "ihr", "dir", "etc", "bzw", "nach", "deine", "the", "warum", "machen", "0"
);
$sLang["statistics"]["referer_Partner"] = "Partner:";
$sLang["statistics"]["referer_Partner_1"] = "Partner";
$sLang["statistics"]["referer_goog1e_adwords"] = "Google Adwords";
$sLang["statistics"]["referer_goog1e_search"] = "Google Suche";
$sLang["statistics"]["referer_goog1e_Product_Search"] = "Google Produktsuche";
$sLang["statistics"]["referer_Views_on_referer"] = "Aufrufe über Referer";
$sLang["statistics"]["referer_Direct_calls"] = "Direktaufrufe";
$sLang["statistics"]["referer_Total_visitors"] = "Besucher Insgesamt";
/*
modules/statistics/data
|_referer_google.php
*/
$sLang["statistics"]["referer_google_month"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["referer_google_header"] = array(
			array("header"=>"Request","name"=>"request","sortable"=>"false","width"=>150),
			array("header"=>"Anzahl","name"=>"count","sortable"=>"false","width"=>150)
		);
/*
modules/statistics/data
|_referer_user_old.php
*/
$sLang["statistics"]["referer_user_old_header"] = array(
			array("header"=>"Host","name"=>"Host","sortable"=>"false","width"=>150),
			array("header"=>"Ges. Umsatz","name"=>"Umsatz","sortable"=>"false","width"=>150),
			array("header"=>"Lead-Wert","name"=>"Umsatz/Bestellungen","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz Neuk.","name"=>"Umsatz Neukunden","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz Altk.","name"=>"Umsatz Altkunden","sortable"=>"false","width"=>150),
			array("header"=>"Bestellungen","name"=>"Bestellungen","sortable"=>"false","width"=>150),
			array("header"=>"Neukunden","name"=>"Neukunden","sortable"=>"false","width"=>150),
			array("header"=>"Altkunden","name"=>"Altkunden","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz/Neuk.","name"=>"Umsatz/Neukunden","sortable"=>"false","width"=>150),
			array("header"=>"Umsatz/Altk.","name"=>"Umsatz/Altkunden","sortable"=>"false","width"=>150),
			//array("header"=>"Optionen","name"=>"Optionen","sortable"=>"false","width"=>150)
		);
/*
modules/statistics/data
|_search.php
*/
$sLang["statistics"]["search_month"] = array("Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
$sLang["statistics"]["search_search"] = "Suche";
$sLang["statistics"]["search_count"] = "Anzahl";
$sLang["statistics"]["search_Search_Results"] = "Suchergebnisse";
/*
modules/statistics/data
|_voucher_amount.php
*/
$sLang["statistics"]["voucher_amount_header"] = array(
		array("header"=>"Datum","name"=>"Datum","sortable"=>"false","width"=>150,"date"=>true),
		array("header"=>"Gutscheincode","name"=>"Gutscheincode","sortable"=>"false","width"=>150),
		array("header"=>"Umsatz","name"=>"Umsatz","sortable"=>"false","width"=>150),
		array("header"=>"Gutscheinwert","name"=>"Gutscheinwert","sortable"=>"false","width"=>150),
		array("header"=>utf8_encode("Einkäufe"),"name"=>"Einkäufe","sortable"=>"false","width"=>150)
	);
/*
modules/articles
|_artikeln1.inc.php
*/
$sLang["articles"]["artikeln1_please_enter_price"] = "Bitte geben Sie einen Preis für die Gruppe \"Shopkunden\" ein";
$sLang["articles"]["artikeln1_the_ordernumber"] = "Die Bestellnummer";
$sLang["articles"]["artikeln1_already_taken"] = "ist bereits vergeben";
$sLang["articles"]["artikeln1_error_cant_save_article"] = "Fehler: Artikel konnte nicht angelegt werden! (Modus: $mode)";
$sLang["articles"]["artikeln1_error_cant_save_subarticle"] = "Fehler: Sub-Artikel konnte nicht angelegt werden! (Modus: $mode)";
$sLang["articles"]["artikeln1_error_cant_add_article_price"] = "Fehler! Dem Artikel konnten keine Preise hinzugefügt werden!";
$sLang["articles"]["artikeln1_error_cant_add_Attributes"] = "Attribute konnte nicht gespeichert werden";
$sLang["articles"]["artikeln1_error_cant_find_article"] = "Artikel konnte nicht gefunden werden";
$sLang["articles"]["artikeln1_error_cant_find_price_for_group"] = "Keine Preise für Gruppe";
$sLang["articles"]["artikeln1_error_deposited"] = "hinterlegt!";
$sLang["articles"]["artikeln1_error_no_price"] = "Es wurden noch keine Preise für die Gruppe:";
$sLang["articles"]["artikeln1_error_deposited"] = "hinterlegt! Der Artikel wird in dieser Gruppe erst angezeigt, wenn Preise hinterlegt wurden";
$sLang["articles"]["artikeln1_error_no_price"] = "deposited";
$sLang["articles"]["artikeln1_error_cant_save_article_1"] = "Artikel konnte nicht gespeichert werden";
$sLang["articles"]["artikeln1_data_saved"] = "Stammdaten gespeichert";
$sLang["articles"]["artikeln1_save_Variant"] = "Variante speichern";
$sLang["articles"]["artikeln1_save_data"] = "Stammdaten speichern";
$sLang["articles"]["artikeln1_Season"] = "Staffeln";
$sLang["articles"]["artikeln1_Selling_price"] = "Verkaufspreis";
$sLang["articles"]["artikeln1_Percent_discount"] = "Prozentrabatt";
$sLang["articles"]["artikeln1_pseudo_price"] = "Pseudopreis";
$sLang["articles"]["artikeln1_purchase_price"] = "Einkaufspreis";
/*
modules/articles
|_artikeln3.inc.php
*/
$sLang["articles"]["artikeln3_No_article_referenced"] = "No article referenced";
$sLang["articles"]["artikeln3_upload_picture"] = "Bilder hochladen:";
$sLang["articles"]["artikeln3_Please_arrange"] = "Bitte ordnen Sie dem Artikel";
$sLang["articles"]["artikeln3_Please_arrange_1"] = "Bilder zu.";
$sLang["articles"]["artikeln3_article_not_found"] = "Article not found";
$sLang["articles"]["artikeln3_choose_picture"] = "Bilder auswählen";
$sLang["articles"]["artikeln3_need_flash"] = "Der Shopware Bildupload benötigt Macromedia Flash!";
$sLang["articles"]["artikeln3_click"] = "Klicken Sie";
$sLang["articles"]["artikeln3_here"] = "hier";
$sLang["articles"]["artikeln3_download_flash_player"] = "um den aktuellen Flashplayer herunterzuladen.";
$sLang["articles"]["artikeln3_please_start_instalation_afer_download"] = "Starten Sie bitte nach dem Download die Installation - danach können Sie den Bild-Upload nutzen.";
$sLang["articles"]["artikeln3_tip"] = "Hinweis:";
$sLang["articles"]["artikeln3_user_doubleclick"] = "Benutzen Sie einen Doppelklick auf das Bild, um es als Vorschaubild zu markieren.";
$sLang["articles"]["artikeln3_assigned_pictures"] = "Diesem Artikel sind %s Bilder zugeordnet";
$sLang["articles"]["artikeln3_assigned_no_pictures"] = "Diesem Artikel sind keine Bilder zugeordnet";
$sLang["articles"]["artikeln3_realy_want_to_delete"] = "Soll das markierte Bild wirklich gel&ouml;scht werden?";
$sLang["articles"]["artikeln3_preview_changed"] = "Vorschaubild geändert";
$sLang["articles"]["artikeln3_preview"] = "Vorschaubild";
$sLang["articles"]["artikeln3_delete_picture"] = "Bild löschen";
/*
modules/articles
|_artikelv1.inc.php
*/
$sLang["articles"]["artikelv1_No_article"] = "No article";
$sLang["articles"]["artikelv1_fill_out_article"] = "Sie haben ein Variantenkriterium gewählt, bitte füllen Sie das Feld Zusatztext im Bereich Hauptartikel aus";
$sLang["articles"]["artikelv1_error_article_not_added"] = "Fehler: Artikel konnte nicht angelegt werden!";
$sLang["articles"]["artikelv1_error_mysql"] = "MySQL-Fehler";
$sLang["articles"]["artikelv1_error_subarticle_not_added"] = "Fehler: Sub-Artikel konnte nicht angelegt werden!";
$sLang["articles"]["artikelv1_error_price_not_added"] = "Fehler! Dem Artikel konnten keine Preise hinzugefügt werden!";
$sLang["articles"]["artikelv1_error_attribute_not_added"] = "Attribute konnte nicht gespeichert werden";
$sLang["articles"]["artikelv1_error_article_not_found"] = "Artikel konnte nicht gefunden werden";
$sLang["articles"]["artikelv1_error_cant_import"] = "Fehler: Preise für Artikel %s (Gruppe: %s) konnte nicht importiert werden!";
$sLang["articles"]["artikelv1_set_standart"] = "Setzte Standardwerte";
$sLang["articles"]["artikelv1_net"] = "Netto";
$sLang["articles"]["artikelv1_gross"] = "Brutto";
$sLang["articles"]["artikelv1_enter"] = "Eingabe %s-Preise";
$sLang["articles"]["artikelv1_cant_save_article"] = "Artikel konnte nicht gespeichert werden";
$sLang["articles"]["artikelv1_variant_saved"] = "Variante gespeichert";
$sLang["articles"]["artikelv1_save"] = "Speichern";
$sLang["articles"]["artikelv1_stagger"] = "Staffeln";
$sLang["articles"]["artikelv1_Selling_price"] = "Verkaufspreis";
$sLang["articles"]["artikelv1_Pseudo_price"] = "Prozentrabatt";
$sLang["articles"]["artikelv1_Purchase_price"] = "Einkaufspreis";
/*
modules/articles
|_categoryrelations.php
*/
$sLang["articles"]["categoryrelations_Category"] = "Kategorie zuordnen";
$sLang["articles"]["categoryrelations_no_Category_selected"] = "Keine Kategorie ausgewählt";
$sLang["articles"]["categoryrelations_article_not_found"] = "Article not found";
$sLang["articles"]["categoryrelations_please_select_category"] = "Bitte wählen Sie die Kategorien, in die der Artikel";
$sLang["articles"]["categoryrelations_to_be_set"] = "eingestellt werden soll";
$sLang["articles"]["categoryrelations_selected_category"] = "Gew&auml;hlte Kategorie:";
$sLang["articles"]["categoryrelations_Assigning_to_category"] = "Diese Kategorie zuordnen";
$sLang["articles"]["categoryrelations_Assigning_to_category_delete"] = "Kategoriezuordnung wurde gelöscht";
$sLang["articles"]["categoryrelations_Assigning_to_category_cant_delete"] = "Kategoriezuordnung konnte nicht gelöscht werden";
$sLang["articles"]["categoryrelations_already_assigned"] = "Diese Kategorie wurde bereits zugeordnet";
$sLang["articles"]["categoryrelations_Category_assignment_has_been_added"] = "Kategoriezuordnung wurde hinzugefügt";
$sLang["articles"]["categoryrelations_nothing_found"] = "Nothing found";
$sLang["articles"]["categoryrelations_Already_assigned_categories"] = "Bereits zugeordnete Kategorien:";
$sLang["articles"]["categoryrelations_assignment"] = "Soll die Zuordnung";
$sLang["articles"]["categoryrelations_assignment_delete"] = "wirklich gel&ouml;scht werden?";
/*
modules/articles
|_class_articles.php
*/
$sLang["articles"]["class_articles_invalid_price"] = "Der von Ihnen eingegebene Verkaufspreis (Gruppe: %s, Zeile: %s) ist kein gültiger Preis";
$sLang["articles"]["class_articles_wrong_price"] = "Falsche Preisangabe!";
$sLang["articles"]["class_articles_error"] = "Fehler:";
$sLang["articles"]["class_articles_tip"] = "Hinweis:";
$sLang["articles"]["class_please_fill_out"] = "Bitte füllen Sie folgende Felder aus:";
/*
modules/articles
|_config.php
*/
$sLang["articles"]["config_configurator_Group"] = "Soll die Konfigurator-Gruppe";
$sLang["articles"]["config_want_to_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["config_configurator_option"] = "Soll die Konfigurator-Option";
$sLang["articles"]["config_Accessories_group"] = "Soll die Zubeh&ouml;r-Gruppe";
$sLang["articles"]["config_Accessories_option"] = "Soll die Zubeh&ouml;r-Option";
$sLang["articles"]["config_delete_template"] = "Soll das ausgewählte Template wirklich gelöscht werden?";
$sLang["articles"]["config_regard"] = "Achtung! Bitte löschen Sie zunächst alle normalen Varianten, um mehrdimensionale Artikelvarianten nutzen zu können";
$sLang["articles"]["config_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["articles"]["config_any_retrofit"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["articles"]["config_not_be_saved"] = "In der NICHT lizenzierten Version können die Einträge nicht gespeichert werden";
$sLang["articles"]["config_more_informations"] = "Weitere Informationen:";
$sLang["articles"]["config_Module_Features"] = "Modul-Vorstellung";
$sLang["articles"]["config_Module_rent"] = "Modul mieten/kaufen";
$sLang["articles"]["config_tip"] = "Hinweis:";
$sLang["articles"]["config_support_you"] = "Der Artikel-Konfigurator unterstützt Sie beim Anlegen von mehrdimensionalen Varianten. Diese sind notwendig, wenn sich ein Artikel in mehr als einer Eigenschaft von seinen Varianten unterscheidet. Dies kann beispielsweise die Größe und Farbe eines Artikels sein.";
$sLang["articles"]["config_Accessories_Groups"] = "Zubehör - Gruppen";
$sLang["articles"]["config_Successful"] = "Erfolgreich";
$sLang["articles"]["config_Accessories_group_deleted"] = "Zubehör-Gruppe wurde gelöscht";
$sLang["articles"]["config_Accessories_options_successful"] = "Zubehör-Option wurde angelegt";
$sLang["articles"]["config_error"] = "Fehler";
$sLang["articles"]["config_error_Accessories_group_deleted"] = "Zubehör-Gruppe konnte nicht gelöscht werden";
$sLang["articles"]["config_enter_name_for_accessorie_group"] = "Bitte geben Sie einen Namen für die Zubehör-Gruppe ein";
$sLang["articles"]["config_Accessories_Group_was_created"] = "Zubehör-Gruppe wurde angelegt";
$sLang["articles"]["config_Accessories_Group_save_failed"] = "Zubehör-Gruppe konnte nicht gespeichert werden";
$sLang["articles"]["config_new_Accessories_Group"] = "Neue Zubehör-Gruppe anlegen";
$sLang["articles"]["config_Accessories_Group_as_article"] = "Zubehör-Artikel müssen in Shopware als Artikel angelegt sein";
$sLang["articles"]["config_Grouptitle"] = "Bezeichnung für Gruppe:";
$sLang["articles"]["config_Description"] = "Beschreibung";
$sLang["articles"]["config_add"] = "Hinzuf&uuml;gen";
$sLang["articles"]["config_accessorie_option"] = "Zubehör - Optionen";
$sLang["articles"]["config_accessorie_option_deleted"] = "Zubehör-Option wurde gelöscht";
$sLang["articles"]["config_accessorie_option_delete_failed"] = "Zubehör-Option konnte nicht gelöscht werden";
$sLang["articles"]["config_select_accessorie_group"] = "Bitte wählen Sie eine Zubehörgruppe aus";
$sLang["articles"]["config_enter_accessorie_option"] = "Bitte geben Sie eine Zubehör-Optionsbezeichnung ein";
$sLang["articles"]["config_accessorie_option_save_failed"] = "Zubehör-Option konnte nicht gespeichert werden";
$sLang["articles"]["config_enter_new_accessorie_option"] = "Neue Zubehör Option anlegen:";
$sLang["articles"]["config_accessorie"] = "Zubehör-Artikel müssen in Shopware als Artikel angelegt sein";
$sLang["articles"]["config_accessorie_group_1"] = "Zubehör-Gruppe";
$sLang["articles"]["config_accessorie_option_name"] = "Name für Zubehör-Option";
$sLang["articles"]["config_ordernumber"] = "Bestellnummer";
$sLang["articles"]["config_add"] = "Hinzuf&uuml;gen";
$sLang["articles"]["config_Configurator_Templates"] = "Konfigurator - Templates";
$sLang["articles"]["config_existing_templates"] = "Laden Sie existierende Templates für Gruppen und Optionen";
$sLang["articles"]["config_save_options_for_template"] = "Sie können die Gruppen und Optionen jedes Artikels speichern und als Vorlage für neue Artikel verwenden";
$sLang["articles"]["config_regard_load_template"] = "Achtung! Sobald Sie den Button &quot;Template Laden&quot; betätigen, werden alle bestehenden Konfigurator-Eingaben zu diesem Artikel überschrieben!";
$sLang["articles"]["config_template_deleted"] = "Template wurde gelöscht";
$sLang["articles"]["config_loading_template"] = "Lade Template";
$sLang["articles"]["config_template_loading_failed"] = "Template konnte NICHT geladen werden";
$sLang["articles"]["config_sql_error"] = "SQL - Fehler";
$sLang["articles"]["config_template_loaded"] = "Template wurde geladen";
$sLang["articles"]["config_price_matrix"] = "Erzeuge Preis-Matrix";
$sLang["articles"]["config_select_template"] = "Auswahl Template:";
$sLang["articles"]["config_option"] = "Optionen:";
$sLang["articles"]["config_please_select"] = "Bitte wählen";
$sLang["articles"]["config_load_template"] = "Template Laden";
$sLang["articles"]["config_missing_Group_assignment"] = "Dieser Artikel hat noch keine Gruppen/Optionszuordnung und kann somit nicht als Vorlage verwendet werden";
$sLang["articles"]["config_template_added_successfully"] = "Template wurde hinzugefügt";
$sLang["articles"]["config_template_add_failed"] = "Template konnte NICHT hinzugefügt werden";
$sLang["articles"]["config_Configurator_Groups"] = "Konfigurator - Gruppen";
$sLang["articles"]["config_Group_delted"] = "Gruppe wurde gelöscht";
$sLang["articles"]["config_delete_Group_failed"] = "Gruppe konnte nicht gelöscht werden";
$sLang["articles"]["config_enter_Group_article"] = "Bitte geben Sie einen Gruppen Artikel ein";
$sLang["articles"]["config_group_added"] = "Gruppe wurde angelegt";
$sLang["articles"]["config_add_group_failed"] = "Gruppe konnte nicht gespeichert werden";
$sLang["articles"]["config_New_Configurator_group"] = "Neue Konfigurator-Gruppe anlegen:";
$sLang["articles"]["config_Configurator_group_information"] = "Eine Konfigurator-Gruppe kann z.B. die Farbe oder die Größe sein. Die genauen Werte, wie z.B. Farbe Schwarz oder Weiss, legen Sie in den Konfigurator-Optionen fest.";
$sLang["articles"]["config_group_name"] = "Bezeichnung für Gruppe";
$sLang["articles"]["config_Description"] = "Beschreibung";
$sLang["articles"]["config_Configurator_Options"] = "Konfigurator - Optionen";
$sLang["articles"]["config_Option_deleted"] = "Option wurde gelöscht";
$sLang["articles"]["config_delete_Option_failed"] = "Option konnte nicht gelöscht werden";
$sLang["articles"]["config_select_article"] = "Bitte wählen Sie einen Artikel aus";
$sLang["articles"]["config_enter_article_option"] = "Bitte geben Sie eine Artikel Option ein";
$sLang["articles"]["config_matrix_error"] = "< Fehler in der Erzeugung der Matrix >";
$sLang["articles"]["config_option_added"] = "Option wurde angelegt";
$sLang["articles"]["config_add_option_failed"] = "Option konnte nicht gespeichert werden";
$sLang["articles"]["config_Configurator_groups_options"] = "Konfigurator-Gruppen Optionen:";
$sLang["articles"]["config_group"] = "Gruppe:";
$sLang["articles"]["config_new_worth_group"] = "Neuer Wert für diese Gruppe:";
$sLang["articles"]["config_Configurator_Price_input"] = "Konfigurator - Preiseingabe";
$sLang["articles"]["config_Configuratorgroups_price_input"] = "Konfigurator-Gruppen Preiseingabe:";
$sLang["articles"]["config_stock"] = "Lagerbestand";
$sLang["articles"]["config_Preselection"] = "Vorauswahl";
$sLang["articles"]["config_active"] = "Aktiv";
$sLang["articles"]["config_no_article"] = "No Article";
$sLang["articles"]["config_save_template"] = "Template Speichern";
$sLang["articles"]["config_save_article_as_template"] = "Diesen Artikel als Template speichern";
$sLang["articles"]["config_Configurator_Attitudes"] = "Konfigurator - Einstellungen";
$sLang["articles"]["config_Type_of_Configurator"] = "Art des Konfigurators:";
$sLang["articles"]["config_Surcharge"] = "Aufpreis";
$sLang["articles"]["config_Selection"] = "Auswahl";
$sLang["articles"]["config_Table"] = "Tabelle";
$sLang["articles"]["config_Sorting_the_default_value"] = "Sortierung um den Standardwert zu ermitteln:";
$sLang["articles"]["config_possible_values"] = "mögliche Werte: active, price, ordernumber, instock, standard";
$sLang["articles"]["config_Sort_groups"] = "Sortierung der Gruppen:";
$sLang["articles"]["config_possible_values_1"] = "mögliche Werte: groupID, groupname, groupposition";
$sLang["articles"]["config_sort_options"] = "Sortierung der Optionen:";
$sLang["articles"]["config_possible_values_2"] = "mögliche Werte: optionactive, optionname, optioninstock, optionposition, user_selected, selected";
$sLang["articles"]["config_Stock_note"] = "Lagerbestand beachten:";
$sLang["articles"]["config_save"] = "Speichern";
$sLang["articles"]["config_position"] = "Position";
$sLang["articles"]["config_error"] = "Fehler";
/*
modules/articles
|_amountmonth.php
*/
$sLang["articles"]["amountmonth_Sales_by_months"] = "Umsatz nach Monaten";
/*
modules/articles
|_cross.php
*/
$sLang["articles"]["cross_Link_has_been_successfully_saved"] = "Verknüpfung wurde erfolgreich gespeichert";
$sLang["articles"]["cross_Link_save_failed"] = "Verknüpfung konnte NICHT angelegt werden";
$sLang["articles"]["cross_article_not_found"] = "Artikel nicht gefunden";
$sLang["articles"]["cross_link_deleted"] = "Verknüpfung wurde gelöscht";
$sLang["articles"]["cross_link_delet_failed"] = "Verknüpfung konnte nicht gelöscht werden";
$sLang["articles"]["cross_Similar_article_has_been_successfully_saved"] = "Ähnlicher Artikel wurde erfolgreich gespeichert";
$sLang["articles"]["cross_Similar_article_save_failed"] = "Ähnlicher Artikel konnte NICHT angelegt werden";
$sLang["articles"]["cross_Similar_article_deleted"] = "Ähnlicher Artikel wurde gelöscht";
$sLang["articles"]["cross_Similar_article_cant_be_deleted"] = "Ähnlicher Artikel konnte nicht gelöscht werden";
$sLang["articles"]["cross_link_with"] = "Soll die Verknüpfung mit";
$sLang["articles"]["cross_want_to_Delete"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["cross_Similar_article_link_with"] = "Soll der ähnliche Artikel";
$sLang["articles"]["cross_tip"] = "Hinweis:";
$sLang["articles"]["cross_Here_you_have_the_possibility"] = "Hier haben Sie die Möglichkeit, den Artikel mit anderen Artikeln zu verknüpfen. Die verknüpften Artikel werden automatisch auf der Artikeldetailseite angezeigt.";
$sLang["articles"]["cross_Accessories-Article"] = "Zubehör-Artikel";
$sLang["articles"]["cross_add_link"] = "Neue Verknüpfung anlegen";
$sLang["articles"]["cross_ordernumber"] = "Bestellnummer:";
$sLang["articles"]["cross_add"] = "Hinzuf&uuml;gen";
$sLang["articles"]["cross_articlename"] = "Artikelname";
$sLang["articles"]["cross_options"] = "Optionen";
$sLang["articles"]["cross_similar_articles"] = "Ähnliche Artikel";
/*
modules/articles
|_downloads.php
*/
$sLang["articles"]["downloads_no_article"] = "No Article";
$sLang["articles"]["downloads_upload_failed"] = "Datei konnte nicht hochgeladen werden";
$sLang["articles"]["downloads_select_file"] = "Bitte wählen Sie eine Datei";
$sLang["articles"]["downloads_download_successfully"] = "Download wurde erfolgreich gespeichert";
$sLang["articles"]["downloads_add_download_failed"] = "Download konnte NICHT angelegt werden";
$sLang["articles"]["downloads_enter_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["articles"]["downloads_download_deleted"] = "Download wurde gelöscht";
$sLang["articles"]["downloads_delete_download_failed"] = "Download konnte nicht gelöscht werden";
$sLang["articles"]["downloads_links"] = "Links";
$sLang["articles"]["downloads_the_download"] = "Soll der Download";
$sLang["articles"]["downloads_really_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["downloads_new_download"] = "Neuer Download";
$sLang["articles"]["downloads_edit_download"] = "Download bearbeiten";
$sLang["articles"]["downloads_title"] = "Bezeichnung:";
$sLang["articles"]["downloads_file_upload"] = "Datei-Upload:";
$sLang["articles"]["downloads_information"] = "Informationen:";
$sLang["articles"]["downloads_filesize"] = "Dateigröße:";
$sLang["articles"]["downloads_Megabyte"] = "MB";
$sLang["articles"]["downloads_download_files"] = "Datei herunterladen";
$sLang["articles"]["downloads_save"] = "Speichern";
$sLang["articles"]["downloads_new_Attachment"] = "Neuer Datei-Anhang(Download)";
$sLang["articles"]["downloads_add_a_download"] = "Fügen Sie optional einen Download hinzu (z.B. eine PDF-Datei).";
$sLang["articles"]["downloads_info"] = "Info";
$sLang["articles"]["downloads_options"] = "Optionen";
/*
modules/articles
|_esd.php
*/
$sLang["articles"]["esd_no_article"] = "No Article";
$sLang["articles"]["esd_Creating_esd_failed"] = "ESD-Version konnte nicht angelegt werden";
$sLang["articles"]["esd_Creating_esd_successfull"] = "ESD-Version wurde angelegt";
$sLang["articles"]["esd_new_serial_numbers_imported"] = "neue Seriennummern importiert";
$sLang["articles"]["esd_deleting_esd"] = "ESD-Version wurde gelöscht";
$sLang["articles"]["esd_deleting_esd_failed"] = "ESD-Version konnte nicht gelöscht werden";
$sLang["articles"]["esd_link"] = "Links";
$sLang["articles"]["esd_the_ESD_version"] = "Soll die ESD-Version";
$sLang["articles"]["esd_really_detele"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["esd_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["articles"]["esd_This_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit ";
$sLang["articles"]["esd_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können keine ESD - Artikel angelegt werden";
$sLang["articles"]["esd_more_informations"] = "Weitere Informationen:";
$sLang["articles"]["esd_Module_Features"] = "Modul-Vorstellung";
$sLang["articles"]["esd_Module_rent"] = "Modul mieten/kaufen";
$sLang["articles"]["esd_shopware_esd-Module"] = "Shopware ESD-Modul";
$sLang["articles"]["esd_sell_digital_products"] = "Verkaufen Sie digitale Produkte, wie Software-Downloads, Musik, Videos und Bilder bequem direkt aus Ihrem Shop heraus. Die Auslieferung erfolgt als gesicherter Download.";
$sLang["articles"]["esd_no_more_esd-Modules"] = "Keine weiteren ESD-Versionen möglich";
$sLang["articles"]["esd_already_all_variants_Article_ESD_data_assigned"] = "Es wurden bereits allen Artikel-Varianten ESD-Daten zugeordnet";
$sLang["articles"]["esd_create_ESD_version"] = "ESD-Version anlegen";
$sLang["articles"]["esd_Choice_article"] = "Auswahl Artikel:";
$sLang["articles"]["esd_upload"] = "Upload";
$sLang["articles"]["esd_upload_of_esd"] = "Der Upload von ESD - Artikeln erfordert Macromedia Flash!";
$sLang["articles"]["esd_download_flash_player"] = "Bitte downloaden Sie den aktuellen Flashplayer unter www.adobe.com";
$sLang["articles"]["esd_manage_serialnumbers"] = "Seriennummern verwalten";
$sLang["articles"]["esd_new_serialnumber"] = "Neue Seriennummern (LF getrennt)";
$sLang["articles"]["esd_information"] = "Informationen:";
$sLang["articles"]["esd_available_serialnumbers"] = "Verfügbare Seriennummern:";
$sLang["articles"]["esd_Subcontracted_serialnumbers"] = "Vergebene Seriennummern:";
$sLang["articles"]["esd_download_file"] = "Datei herunterladen";
$sLang["articles"]["esd_new_esd-version_added"] = "neue ESD-Version anlegen";
$sLang["articles"]["esd_Already-based versions"] = "Bereits angelegte ESD-Versionen:";
/*
modules/articles
|_links.php
*/
$sLang["articles"]["links_link_saved"] = "Link wurde erfolgreich gespeichert";
$sLang["articles"]["links_link_save_failed"] = "Link konnte NICHT angelegt werden";
$sLang["articles"]["links_enter_title_and_link"] = "Bitte geben Sie Bezeichnung und Link ein";
$sLang["articles"]["links_link_deleted"] = "Link wurde gelöscht";
$sLang["articles"]["links_cant_Delete_link"] = "Link konnte nicht gelöscht werden";
$sLang["articles"]["links_link"] = "Links";
$sLang["articles"]["links_should_the_link"] = "Soll der Link";
$sLang["articles"]["links_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["links_new_link"] = "Neuer Link";
$sLang["articles"]["links_edit_link"] = "Link bearbeiten";
$sLang["articles"]["links_link_title"] = "Link-Bezeichnung:";
$sLang["articles"]["links_link_URL"] = "Link-URL (inkl. http://) :";
$sLang["articles"]["links_link_target"] = "Link-Ziel:";
$sLang["articles"]["links_shopware"] = "Shopware";
$sLang["articles"]["links_extern"] = "Extern";
$sLang["articles"]["links_save"] = "Speichern";
$sLang["articles"]["links_Add_optional"] = "Fügen Sie optional weitere Verweise (z.B. zum Hersteller) dem Artikel hinzu.";
$sLang["articles"]["links_info"] = "Info";
$sLang["articles"]["links_link_1"] = "Link";
$sLang["articles"]["links_options"] = "Optionen";
/*
modules/articles
|_skeleton.php
*/
$sLang["articles"]["skeleton_new_article"] = "Neuer Artikel";
$sLang["articles"]["skeleton_article"] = "Artikel";
$sLang["articles"]["skeleton_edit"] = "bearbeiten";
$sLang["articles"]["skeleton_data"] = "Stammdaten";
$sLang["articles"]["skeleton_Categories"] = "Kategorien";
$sLang["articles"]["skeleton_pictures"] = "Bilder";
$sLang["articles"]["skeleton_Variants"] = "Varianten";
$sLang["articles"]["skeleton_Configurator"] = "Konfigurator";
$sLang["articles"]["skeleton_links"] = "Links";
$sLang["articles"]["skeleton_downloads"] = "Downloads";
$sLang["articles"]["skeleton_Cross-Selling"] = "Cross-Selling";
$sLang["articles"]["skeleton_esd"] = "ESD";
$sLang["articles"]["skeleton_Statistics"] = "Statistiken";
$sLang["articles"]["skeleton_Save_Changes"] = "&Auml;nderungen speichern";
/*
modules/articles
|_statistics.php
*/
$sLang["articles"]["statistics_Total_sales"] = "Gesamtumsatz:";
$sLang["articles"]["statistics_Number_of_sales"] = "Anzahl Verkäufe:";
$sLang["articles"]["statistics_Number_of_requests"] = "Anzahl Zugriffe (in den letzten 30 Tagen):";
$sLang["articles"]["statistics_euro"] = "€";
/*
modules/articles
|_varianten.php
*/
$sLang["articles"]["varianten_no_article"] = "No Article";
$sLang["articles"]["varianten_variant_deleted"] = "Variante wurde gelöscht";
$sLang["articles"]["varianten_variant_delete_failed"] = "Variante konnte nicht gelöscht werden";
$sLang["articles"]["varianten_links"] = "Links";
$sLang["articles"]["varianten_should_the_Variant"] = "Soll die Variante";
$sLang["articles"]["varianten_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["articles"]["varianten_Regard_delete_multidimensional_variants"] = "Achtung! Bitte löschen Sie zunächst alle mehrdimensionalen Varianten, um die normalen Artikelvarianten nutzen zu können";
$sLang["articles"]["varianten_Regard_enter_Data_first"] = "Achtung! Bitte geben Sie im Reiter Stammdaten zunächst eine Varianten-Bezeichnung für den Hauptartikel ein"; 
$sLang["articles"]["varianten_add_new_variant"] = "Neue Variante anlegen";
$sLang["articles"]["varianten_Variants_are_intended_to"] = "Varianten dienen dazu ähnliche Artikel komfortabel und schnell in Shopware einzustellen, ohne dabei jeweils einen komplett eigenen Artikel anlegen zu müssen. Die Varianten dürfen sich hierbei ausschließlich in einem Merkmal unterscheiden. Das kann z.B. die Form, Größe, Farbe, Ausstattung, etc. sein.";
$sLang["articles"]["varianten_drag_drop"] = "Sie können die Position der Varianten per Drag & Drop über das Feld Info ändern";
$sLang["articles"]["varianten_new"] = "Neue Variante";
$sLang["articles"]["varianten_variant"] = "Variante";
$sLang["articles"]["varianten_options"] = "Optionen";
$sLang["articles"]["varianten_Save_positions"] = "Positionen speichern";
$sLang["articles"]["varianten_data"] = "Stammartikel";
/*
modules/articles
|_upload.php
*/
$sLang["articles"]["upload_no_articles_reference"] = "No article reference";
$sLang["articles"]["upload_it_is_not_a_directory"] = "it´s not a directory";
$sLang["articles"]["upload_could_not_load_simagesizes"] = "Could not load sIMAGESIZES";
$sLang["articles"]["upload_failure"] = "FAILURE";
$sLang["articles"]["upload_ok"] = "okay";
/*
modules/browser
|_browser.php
*/
$sLang["browser"]["browser_root"] = "Stammverzeichnis";
$sLang["browser"]["browser_options"] = "Einstellungen";
/*
modules/browser
|_browser.php
*/
$sLang["browser"]["options_Should_the_file"] = "Soll die Datei";
$sLang["browser"]["options_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["browser"]["options_upload_complete"] = "Upload abgeschlossen";
$sLang["browser"]["options_upload_failed"] = "Upload fehlgeschlagen";
$sLang["browser"]["options_Directory_was_created"] = "Verzeichnis wurde erstellt";
$sLang["browser"]["options_Could_not_be_created"] = "Verzeichnis konnte nicht erstellt werden";
$sLang["browser"]["options_file_Successfully_deleted"] = "Datei erfolgreich gelöscht";
$sLang["browser"]["options_Could_not_be_deleted"] = "Datei konnte nicht gelöscht werden";
$sLang["browser"]["options_informations_about"] = "Informationen über";
$sLang["browser"]["options_Access_rights"] = "Zugriffsrechte:";
$sLang["browser"]["options_write_protection"] = "Objekt schreibgeschützt";
$sLang["browser"]["options_Full_access"] = "Voller Zugriff möglich";
$sLang["browser"]["options_file_size"] = "Datei-Größe:";
$sLang["browser"]["options_url"] = "Datei - URL (für Verlinkungen):";
$sLang["browser"]["options_preview"] = "Preview:";
$sLang["browser"]["options_download_file"] = "Datei downloaden";
$sLang["browser"]["options_file_options"] = "Datei-Optionen:";
$sLang["browser"]["options_delete_file"] = "Datei Löschen";
$sLang["browser"]["options_upload_file"] = "Datei Upload:";
$sLang["browser"]["options_file"] = "Datei:";   
$sLang["browser"]["options_filetitle"] = "Bezeichnung:";
$sLang["browser"]["options_save"] = "Speichern";
$sLang["browser"]["options_new_Directory"] = "Neues Verzeichnis:";
$sLang["browser"]["options_Directoryname"] = "Verzeichnisname:";
$sLang["browser"]["options_create_directory"] = "Verzeichnis erstellen";
/*
modules/browser
|_skeleton.php
*/
$sLang["browser"]["options_shopware_filebrowser"] = "Datei-Archiv";
/*
modules/import
|_import.php
*/
$sLang["import"]["import_software_API"] = "Shopware API";
$sLang["import"]["import_with_the_software_API"] = "Mit der Shopware API können Sie eigene Importe und Exporte in beliebigen Formaten erstellen</strong><br />
Für den Zugriff auf die API benötigen Sie einen FTP-Zugang zu Ihrem Shop, den
Sie von uns oder Ihrem Partner erhalten können. Für die Anpassung der Scripte
an Ihre Bedürfnisse sind PHP-Kenntnisse notwendig.
Mit Hilfe der API können Sie XML und CSV Dateien in beliebigen Formaten importieren und exportieren.
Es existieren Beispiele für den Import von Artikeldaten, Kategoriedaten und Lagerbeständen";
$sLang["import"]["import_Available_imports_exports"] = "Verfügbare Importe/Exporte:";
$sLang["import"]["import_Import_stocks"] = "Import Lagerbestände";
$sLang["import"]["import_Call"] = "Aufrufen";
$sLang["import"]["import_import_export_article"] = "Import/Export Artikel/Kategorien";
$sLang["import"]["import_xml"] = "XML";
$sLang["import"]["import_csv"] = "CSV";
$sLang["import"]["import_export_newsletter"] = "Export Newsletter-Empfänger";
$sLang["import"]["import_export"] = "Exportieren";
$sLang["import"]["import_export_articledata"] = "Export Artikel-Stammdaten";
$sLang["import"]["import_export_order_rawdata"] = "Export Bestellungen - Rohdaten";
$sLang["import"]["import_export_Categories_rawdata"] = "Export Kategorien - Rohdaten";
$sLang["import"]["import_export_All_non_stock"] = "Export aller nicht vorrätigen Artikel";
/*
modules/import
|_skeleton.php
*/
$sLang["import"]["skeleton_import_export"] = "Datenaustausch";
/*
modules/import_xml
|_import.articles.php
*/
$sLang["import_xml"]["import_articles_import"] = "Import";
$sLang["import_xml"]["import_articles_importsuccessful"] = "Import war Erfolgreich";
$sLang["import_xml"]["import_articles_were"] = "Es wurden";
$sLang["import_xml"]["import_articles_import_updated"] = "Artikel importiert/geupdatet";
$sLang["import_xml"]["import_articles_import_updated_category"] = "Kategorien importiert/geupdatet";
$sLang["import_xml"]["import_articles_back"] = "Zurück";
/*
modules/import_xml
|_skeleton.php
*/
$sLang["import_xml"]["skeleton_import"] = "XML - Import";
/*
modules/import_xml
|_index.php
*/
$sLang["import_xml"]["index_standard_xml_import_export"] = "Standard XML - Import / Export";
$sLang["import_xml"]["index_export"] = "Export";
$sLang["import_xml"]["index_all_articledata"] = "alle Artikeldaten";
$sLang["import_xml"]["index_to_export"] = "Exportieren";
$sLang["import_xml"]["index_Data_Only"] = "nur Stammdaten";
$sLang["import_xml"]["index_categorie_Only"] = "nur Kategorien";
$sLang["import_xml"]["index_import"] = "Import";
$sLang["import_xml"]["index_delete_old_categories"] = "alte Kategorien löschen";
$sLang["import_xml"]["index_delete_not_included_categories"] = "nicht enthaltene Kategorien löschen";
$sLang["import_xml"]["index_delete_empty_categories"] = "leere Kategorien löschen";
$sLang["import_xml"]["index_dont_delete_categories"] = "keine Kategorien löschen";
$sLang["import_xml"]["index_delete_old_articles"] = "alte Artikel löschen";
$sLang["import_xml"]["index_Delete_not_included"] = "nicht enthaltene Artikel löschen";
$sLang["import_xml"]["index_dont_delete_article"] = "keine Artikel löschen";
$sLang["import_xml"]["index_import_article_picures"] = "Artikel-Bilder importieren";
$sLang["import_xml"]["index_xml_file"] = "XML-Datei";
$sLang["import_xml"]["index_to_import"] = "Importieren";
/*
modules/instock
|_lager.php
*/
$sLang["instock"]["lager_supplier"] = "Supplier";
$sLang["instock"]["lager_csv_import"] = "CSV Datei Lager-Import";
$sLang["instock"]["lager_sample"] = "Beispieldatei / Vorlage herunterladen";
$sLang["instock"]["lager_stock"] = "Lagerbestände in Datei";
$sLang["instock"]["lager_stock_article"] = "Lagerbestand Artikel";
$sLang["instock"]["lager_import"] = "IMPORTIERT";
$sLang["instock"]["lager_article_not_in_system"] = "Artikel nicht im System";
/*
modules/instock
|_skeleton.php
*/
$sLang["instock"]["skeleton_stock_import"] = "Lager-Import";
/*
modules/live
|_skeleton.php
*/
$sLang["live"]["skeleton_live_view"] = "Live-Ansicht";
/*
modules/mailcampaigns
|_articledetails.php
*/
$sLang["mailcampaigns"]["articledetails_articles_deleted"] = "Artikel wurde gelöscht";
$sLang["mailcampaigns"]["articledetails_cant_be_deleted"] = "Artikel konnte nicht gelöscht werden";
$sLang["mailcampaigns"]["articledetails_article_not_found"] = "Artikel nicht gefunden";
$sLang["mailcampaigns"]["articledetails_enter_ordernumber"] = "Bitte geben Sie die Bestellnummer des Artikels ein";
$sLang["mailcampaigns"]["articledetails_cant_find_ordernumber"] = "Es konnte kein Artikel mit der Bestellnummer";
$sLang["mailcampaigns"]["articledetails_cant_find"] = "gefunden werden";
$sLang["mailcampaigns"]["articledetails_cant_save_article"] = "Artikel konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["articledetails_article_updated"] = "Artikel wurde aktualisiert";
$sLang["mailcampaigns"]["articledetails_article_creaded"] = "Artikel wurde angelegt";
$sLang["mailcampaigns"]["articledetails_article_not_found"] = "Artikel nicht gefunden";
$sLang["mailcampaigns"]["articledetails_want_do_delete"] = "Soll dieser Artikel wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["articledetails_edit_article"] = "Artikel bearbeiten";
$sLang["mailcampaigns"]["articledetails_ordernumber"] = "Bestellnummer (Fester Artikel):";
$sLang["mailcampaigns"]["articledetails_typ_solid_Article"] = "(für Typ fester Artikel)";
$sLang["mailcampaigns"]["articledetails_typ"] = "Typ:";
$sLang["mailcampaigns"]["articledetails_randomly"] = "Zufällig";
$sLang["mailcampaigns"]["articledetails_topseller"] = "Topseller";
$sLang["mailcampaigns"]["articledetails_new"] = "Neuheit";
$sLang["mailcampaigns"]["articledetails_solid_Article"] = "fester Artikel";
$sLang["mailcampaigns"]["articledetails_save_article"] = "Artikel speichern";
$sLang["mailcampaigns"]["articledetails_delete_article"] = "Artikel löschen";
/*
modules/mailcampaigns
|_skeleton.php
*/
$sLang["mailcampaigns"]["skeleton_shopware_campaigns"] = "Newsletter (Campaigns)";
$sLang["mailcampaigns"]["skeleton_overview"] = "&Uuml;bersicht";
$sLang["mailcampaigns"]["skeleton_Evaluation"] = "Auswertung";
$sLang["mailcampaigns"]["skeleton_options"] = "Einstellungen";
$sLang["mailcampaigns"]["skeleton_import"] = "Import";
/*
modules/mailcampaigns
|_articlesedit.php
*/
$sLang["mailcampaigns"]["articlesedit_articlegroup_deleted"] = "Artikelgruppe wurde gelöscht";
$sLang["mailcampaigns"]["articlesedit_articlegroup_not_found"] = "Artikelgruppe nicht gefunden";
$sLang["mailcampaigns"]["articlesedit_enter_ordernumber_for_article"] = "Bitte geben Sie die Bestellnummer des Artikels ein";
$sLang["mailcampaigns"]["articlesedit_cant_find_article"] = "Es konnte kein Artikel mit der Bestellnummer";
$sLang["mailcampaigns"]["articlesedit_cant_find"] = "gefunden werden";
$sLang["mailcampaigns"]["articlesedit_Random"] = "Zufall";
$sLang["mailcampaigns"]["articlesedit_topseller"] = "Topseller";
$sLang["mailcampaigns"]["articlesedit_new"] = "Neuheit";
$sLang["mailcampaigns"]["articlesedit_cant_save_article"] = "Artikel konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["articlesedit_article_updated"] = "Artikel wurde aktualisiert";
$sLang["mailcampaigns"]["articlesedit_article_added"] = "Artikel wurde angelegt";
$sLang["mailcampaigns"]["articlesedit_want_to_delete_articlegroup"] = "Soll diese Artikelgruppe wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["articlesedit_articlegroup_options"] = "Artikelgruppe Eigenschaften";
$sLang["mailcampaigns"]["articlesedit_title"] = "Überschrift:";
$sLang["mailcampaigns"]["articlesedit_save"] = "Speichern";
$sLang["mailcampaigns"]["articlesedit_delete_articlegroup"] = "Artikelgruppe löschen";
$sLang["mailcampaigns"]["articlesedit_add_article_to_group"] = "Artikel in Gruppe einfügen";
$sLang["mailcampaigns"]["articlesedit_ordernumber"] = "Bestellnummer:";
$sLang["mailcampaigns"]["articlesedit_typ_solid_article"] = "(für Typ fester Artikel)";
$sLang["mailcampaigns"]["articlesedit_typ"] = "Typ:";
$sLang["mailcampaigns"]["articlesedit_random"] = "Zufällig";
$sLang["mailcampaigns"]["articlesedit_topseller"] = "Topseller";
$sLang["mailcampaigns"]["articlesedit_new"] = "Neuheit";
$sLang["mailcampaigns"]["articlesedit_solid_article"] = "fester Artikel";
$sLang["mailcampaigns"]["articlesedit_add_article_to_group"] = "Artikel in Gruppe einfügen";
/*
modules/mailcampaigns
|_banneredit.php
*/
$sLang["mailcampaigns"]["articlesedit_banner_deleted"] = "Banner wurde gelöscht";
$sLang["mailcampaigns"]["articlesedit_wrong_file"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["mailcampaigns"]["articlesedit_error"] = "Fehler bei Upload";
$sLang["mailcampaigns"]["articlesedit_enter_titel"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["mailcampaigns"]["articlesedit_banner_save_failed"] = "Banner konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["articlesedit_banner_updated"] = "Banner wurde aktualisiert";
$sLang["mailcampaigns"]["articlesedit_banner_added"] = "Banner wurde angelegt";
$sLang["mailcampaigns"]["articlesedit_realy_want_to_delete"] = "Soll das Banner wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["articlesedit_edit_banner"] = "Banner bearbeiten";
$sLang["mailcampaigns"]["articlesedit_banner_title"] = "Überschrift des Banners:";
$sLang["mailcampaigns"]["articlesedit_picture"] = "Bild:";
$sLang["mailcampaigns"]["articlesedit_directlink"] = "Direktlink:";
$sLang["mailcampaigns"]["articlesedit_link_target"] = "Link-Ziel:";
$sLang["mailcampaigns"]["articlesedit_shopware"] = "Shopware";
$sLang["mailcampaigns"]["articlesedit_extern"] = "Extern";
$sLang["mailcampaigns"]["articlesedit_save"] = "Speichern";
$sLang["mailcampaigns"]["articlesedit_delete_banner"] = "Banner löschen";
/*
modules/mailcampaigns
|_campaigns.php
*/
$sLang["mailcampaigns"]["campaigns_newsletter_not_found"] = "Newsletter nicht gefunden";
$sLang["mailcampaigns"]["campaigns_serverconnection_failed"] = "Verbindung zum Server nicht möglich";
$sLang["mailcampaigns"]["campaigns_element_moved"] = "Element wurde verschoben";
$sLang["mailcampaigns"]["campaigns_mailing"] = "Mailing";
$sLang["mailcampaigns"]["campaigns_Settings"] = "Einstellungen";
$sLang["mailcampaigns"]["campaigns_shopware_campaigns"] = "Shopware Campaigns";
$sLang["mailcampaigns"]["campaigns_options_1"] = "Optionen";
$sLang["mailcampaigns"]["campaigns_reload"] = "Neu laden";
$sLang["mailcampaigns"]["campaigns_preview"] = "Vorschau";
$sLang["mailcampaigns"]["campaigns_testmail"] = "Testmail";
$sLang["mailcampaigns"]["campaigns_send_testmail"] = "Testnewsletter verschicken";
$sLang["mailcampaigns"]["campaigns_testmail_send"] = "Testmail wurde verschickt";
$sLang["mailcampaigns"]["campaigns_reload"] = "Reload";
/*
modules/mailcampaigns
|_campaigns2.php
*/
$sLang["mailcampaigns"]["campaigns2_reorder_panel"] = "Reorder TreePanel";
$sLang["mailcampaigns"]["campaigns2_send_testnewsletter"] = "Testnewsletter verschicken";
$sLang["mailcampaigns"]["campaigns2_back_to_overview"] = "zurück zur Übersicht";
$sLang["mailcampaigns"]["campaigns2_reload"] = "Reload";
$sLang["mailcampaigns"]["campaigns2_preview"] = "Preview";
$sLang["mailcampaigns"]["campaigns2_testmail"] = "Testmail";
$sLang["mailcampaigns"]["campaigns2_testmail_send"] = "Testmail wurde verschickt";
/*
modules/mailcampaigns
|_campaignsedit.php
*/
$sLang["mailcampaigns"]["campaignsedit_Container_delete_failed"] = "Löschen der Container fehlgeschlagen";
$sLang["mailcampaigns"]["campaignsedit_action_delete_failed"] = "Löschen der Aktion fehlgeschlagen";
$sLang["mailcampaigns"]["campaignsedit_wrong_file"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["mailcampaigns"]["campaignsedit_upload_failed"] = "Fehler bei Upload";
$sLang["mailcampaigns"]["campaignsedit_enter_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["mailcampaigns"]["campaignsedit_action_cant_be_saved"] = "Aktion konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["campaignsedit_action_updated"] = "Aktion wurde aktualisiert";
$sLang["mailcampaigns"]["campaignsedit_action_added"] = "Aktion wurde angelegt";
$sLang["mailcampaigns"]["campaignsedit_action_not_found"] = "Aktion nicht gefunden";
$sLang["mailcampaigns"]["campaignsedit_Should_the_action"] = "Soll die Aktion";
$sLang["mailcampaigns"]["campaignsedit_realy_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["campaignsedit_action"] = "Aktion";
$sLang["mailcampaigns"]["campaignsedit_edit"] = "bearbeiten";
$sLang["mailcampaigns"]["campaignsedit_new_action"] = "Neue Aktion in";
$sLang["mailcampaigns"]["campaignsedit_add"] = "anlegen";
$sLang["mailcampaigns"]["campaignsedit_actionname"] = "Name der Aktion:";
$sLang["mailcampaigns"]["campaignsedit_action_link"] = "Link zur Aktion:";
$sLang["mailcampaigns"]["campaignsedit_position"] = "Position:";
$sLang["mailcampaigns"]["campaignsedit_valid_From"] = "Gültig von:";
$sLang["mailcampaigns"]["campaignsedit_valid_until"] = "Gültig bis:";
$sLang["mailcampaigns"]["campaignsedit_picture"] = "Bild:";
$sLang["mailcampaigns"]["campaignsedit_directlink"] = "Direktlink:";
$sLang["mailcampaigns"]["campaignsedit_Disabled_container"] = "(Deaktiviert Container)";
$sLang["mailcampaigns"]["campaignsedit_link_target"] = "Link-Ziel:";
$sLang["mailcampaigns"]["campaignsedit_shopware"] = "Shopware";
$sLang["mailcampaigns"]["campaignsedit_extern"] = "Extern";
$sLang["mailcampaigns"]["campaignsedit_activ"] = "Aktiv:";
$sLang["mailcampaigns"]["campaignsedit_yes"] = "Ja";
$sLang["mailcampaigns"]["campaignsedit_no"] = "Nein";
$sLang["mailcampaigns"]["campaignsedit_save"] = "Speichern";
$sLang["mailcampaigns"]["campaignsedit_delete_action"] = "Aktion löschen";
$sLang["mailcampaigns"]["campaignsedit_add_container"] = "Container hinzufügen";
$sLang["mailcampaigns"]["campaignsedit_new_container"] = "Neuer Container:";
$sLang["mailcampaigns"]["campaignsedit_please_choose"] = "Bitte wählen";
$sLang["mailcampaigns"]["campaignsedit_banner"] = "Banner";
$sLang["mailcampaigns"]["campaignsedit_html_text"] = "HTML-Text";
$sLang["mailcampaigns"]["campaignsedit_article-group"] = "Artikel-Gruppe";
$sLang["mailcampaigns"]["campaignsedit_link-group"] = "Link-Gruppe";
$sLang["mailcampaigns"]["campaignsedit_insert_container"] = "Container einfügen";
/*
modules/mailcampaigns
|_config.inc.php
*/
$sLang["mailcampaigns"]["config_inc_campaigns_start"] = "Campaigns start";
$sLang["mailcampaigns"]["config_inc_should_the_Recipient"] = "Soll der Empfänger";
$sLang["mailcampaigns"]["config_inc_realy_Deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["config_inc_Sender"] = "Absender";
$sLang["mailcampaigns"]["config_inc_edit"] = "bearbeiten";
$sLang["mailcampaigns"]["config_inc_add_new_sender"] = "Neuen Absender anlegen";
$sLang["mailcampaigns"]["config_inc_sender_name"] = "Absender-Name:";
$sLang["mailcampaigns"]["config_inc_sender_mail"] = "Absender-eMail:";
$sLang["mailcampaigns"]["config_inc_save"] = "Speichern";
$sLang["mailcampaigns"]["config_inc_email"] = "eMail";
$sLang["mailcampaigns"]["config_inc_sender"] = "Absender";
$sLang["mailcampaigns"]["config_inc_options"] = "Optionen";
/*
modules/mailcampaigns
|_getArticles.php
*/
$sLang["mailcampaigns"]["getArticles_campaigns"] = "Campaigns-Auswertung";
$sLang["mailcampaigns"]["getArticles_conversion_rate"] = "Conversion-Rate";
$sLang["mailcampaigns"]["getArticles_click_rate"] = "Click-Rate";
$sLang["mailcampaigns"]["getArticles_read_rate"] = "Read-Rate";
$sLang["mailcampaigns"]["getArticles_array"] = array(
		array("text"=>"Name",			"key"=>"Name","fixedWidth"=>true,"defaultWidth"=>"200px","numeric"=>true),
		array("text"=>"Datum",			"key"=>"Datum","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>htmlentities("Empfänger"),		"key"=>"Empfänger","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),	
		array("text"=>"Umsatz",			"key"=>"Umsatz","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>"Bestellungen",	"key"=>"Bestellungen","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>"Conversion-Rate","key"=>"Conversion-Rate","fixedWidth"=>true,"defaultWidth"=>"85px","numeric"=>true),
		array("text"=>"Click-Rate",		"key"=>"Click-Rate","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>"Read-Rate",		"key"=>"Read-Rate","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>"Gelesen",			"key"=>"Views","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true),
		array("text"=>"Geklickt",			"key"=>"Clicks","fixedWidth"=>true,"defaultWidth"=>"75px","numeric"=>true)
	);
/*
modules/mailcampaigns
|_import.inc.php
*/
$sLang["mailcampaigns"]["import_inc_email_deleted"] = "eMail wurde gelöscht";
$sLang["mailcampaigns"]["import_inc_campaigns_start"] = "Campaigns start";
$sLang["mailcampaigns"]["import_inc_If_the_recipient"] = "Soll der Empfänger";
$sLang["mailcampaigns"]["import_inc_realy_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["import_inc_looking_for_recipient"] = "Nach Empfänger suchen";
$sLang["mailcampaigns"]["import_inc_eMail"] = "eMail:";
$sLang["mailcampaigns"]["import_inc_search"] = "Suchen";
$sLang["mailcampaigns"]["import_inc_group"] = "Gruppe";
$sLang["mailcampaigns"]["import_inc_options"] = "Optionen";
$sLang["mailcampaigns"]["import_inc_add_recipient_to_group"] = "Empfänger in Gruppe importieren";
$sLang["mailcampaigns"]["import_inc_recipient_in_file"] = "Empfänger in Datei";
$sLang["mailcampaigns"]["import_inc_mail_in_row"] = "|_ Mail in row";
$sLang["mailcampaigns"]["import_inc_could_not_imported"] = "could not imported";
$sLang["mailcampaigns"]["import_inc_please_choose"] = "Bitte wählen";
$sLang["mailcampaigns"]["import_inc_csv_file"] = "CSV-Datei:";
$sLang["mailcampaigns"]["import_inc_format_csv_file"] = "Format CSV-Datei:";
$sLang["mailcampaigns"]["import_inc_email1"] = "email1";
$sLang["mailcampaigns"]["import_inc_email2"] = "email2";
$sLang["mailcampaigns"]["import_inc_email3"] = "email3";
$sLang["mailcampaigns"]["import_inc_import"] = "Importieren";
$sLang["mailcampaigns"]["import_inc_edit_groups"] = "Gruppen bearbeiten";
$sLang["mailcampaigns"]["import_inc_group"] = "Gruppe";
$sLang["mailcampaigns"]["import_inc_recipient"] = "Empfänger";
$sLang["mailcampaigns"]["import_inc_new_group"] = "Neue Gruppe";
/*
modules/mailcampaigns
|_linkdetails.php
*/
$sLang["mailcampaigns"]["linkdetails_link_deleted"] = "Link wurde gelöscht";
$sLang["mailcampaigns"]["linkdetails_link_not_found"] = "Link nicht gefunden";
$sLang["mailcampaigns"]["linkdetails_enter_title_for_link"] = "Bitte geben Sie eine Bezeichnung für den Link ein";
$sLang["mailcampaigns"]["linkdetails_enter_url_for_link"] = "Bitte geben Sie eine URL für den Link ein";
$sLang["mailcampaigns"]["linkdetails_link_saving_failed"] = "Link konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["linkdetails_link_updated"] = "Link wurde aktualisiert";
$sLang["mailcampaigns"]["linkdetails_link_added"] = "Link wurde angelegt";
$sLang["mailcampaigns"]["linkdetails_realy_delete"] = "Soll dieser Link wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["linkdetails_link_title"] = "Link-Bezeichnung:";
$sLang["mailcampaigns"]["linkdetails_direct_link"] = "Direktlink:";
$sLang["mailcampaigns"]["linkdetails_link_target"] = "Link-Ziel:";
$sLang["mailcampaigns"]["linkdetails_shopware"] = "Shopware";
$sLang["mailcampaigns"]["linkdetails_extern"] = "Extern";
$sLang["mailcampaigns"]["linkdetails_save_link"] = "Link speichern";
/*
modules/mailcampaigns
|_linksedit.php
*/
$sLang["mailcampaigns"]["linksedit_linkgroup_deleted"] = "Linkgruppe wurde gelöscht";
$sLang["mailcampaigns"]["linksedit_linkgroup_not_found"] = "Linkgruppe nicht gefunden";
$sLang["mailcampaigns"]["linksedit_enter_title_for_link"] = "Bitte geben Sie eine Bezeichnung für den Link ein";
$sLang["mailcampaigns"]["linksedit_enter_url_for_link"] = "Bitte geben Sie eine URL für den Link ein";
$sLang["mailcampaigns"]["linksedit_link_saving_failed"] = "Link konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["linksedit_link_updated"] = "Link wurde aktualisiert";
$sLang["mailcampaigns"]["linksedit_link_added"] = "Link wurde angelegt";
$sLang["mailcampaigns"]["linksedit_realy_delete"] = "Soll diese Linkgruppe wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["linksedit_linkgroup_settings"] = "Linkgruppe Eigenschaften";
$sLang["mailcampaigns"]["linksedit_title"] = "Überschrift:";
$sLang["mailcampaigns"]["linksedit_save"] = "Speichern";
$sLang["mailcampaigns"]["linksedit_delete_linkgroup"] = "Linkgruppe löschen";
$sLang["mailcampaigns"]["linksedit_add_link_to_group"] = "Link in Gruppe einfügen";
$sLang["mailcampaigns"]["linksedit_link_title"] = "Link-Bezeichnung:";
$sLang["mailcampaigns"]["linksedit_directlink"] = "Direktlink:";
$sLang["mailcampaigns"]["linksedit_link_target"] = "Link-Ziel:";
$sLang["mailcampaigns"]["linksedit_shopware"] = "Shopware";
$sLang["mailcampaigns"]["linksedit_extern"] = "Extern";
/*
modules/mailcampaigns
|_new.php
*/
$sLang["mailcampaigns"]["new_enter_title"] = "Bitte geben Sie einen Betreff ein";
$sLang["mailcampaigns"]["new_select_a_sender"] = "Bitte wählen Sie einen Absender aus";
$sLang["mailcampaigns"]["new_select_a_template"] = "Bitte wählen Sie ein Template aus";
$sLang["mailcampaigns"]["new_could_not_get_insert_id"] = "Could not get insert-id";
$sLang["mailcampaigns"]["new_cant_save_newsletter"] = "Newsletter konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["new_newsletter_updated"] = "Newsletter wurde aktualisiert";
$sLang["mailcampaigns"]["new_newsletter_update_failed"] = "Newsletter konnte nicht aktualisiert werden";
$sLang["mailcampaigns"]["new_campaign_not_found"] = "Campaign not found";
$sLang["mailcampaigns"]["new_should_the_action"] = "Soll die Aktion";
$sLang["mailcampaigns"]["new_realy_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["new_back_to_overview"] = "zurück zur Übersicht";
$sLang["mailcampaigns"]["new_reload"] = "Reload";
$sLang["mailcampaigns"]["new_newsletter_Recipient"] = "Newsletter - Empfänger";
$sLang["mailcampaigns"]["new_Customergroup"] = "Kundengruppen:";
$sLang["mailcampaigns"]["new_recipient"] = "Empfänger";
$sLang["mailcampaigns"]["new_own_Reception_groups"] = "Eigene Empfangsgruppen:";
$sLang["mailcampaigns"]["new_campaign"] = "Kampagne";
$sLang["mailcampaigns"]["new_edit"] = "bearbeiten";
$sLang["mailcampaigns"]["new_newsletter_settings"] = "Newsletter - Einstellungen";
$sLang["mailcampaigns"]["new_sender"] = "Absender:";
$sLang["mailcampaigns"]["new_template"] = "Template:";
$sLang["mailcampaigns"]["new_language"] = "Sprache:";
$sLang["mailcampaigns"]["new_date"] = "Datum:";
$sLang["mailcampaigns"]["new_title"] = "Betreff:";
$sLang["mailcampaigns"]["new_save"] = "Speichern";
$sLang["mailcampaigns"]["new_add_container"] = "Container hinzufügen";
$sLang["mailcampaigns"]["new_new_container"] = "Neuer Container:";
$sLang["mailcampaigns"]["new_please_select"] = "Bitte wählen";
$sLang["mailcampaigns"]["new_add_container"] = "Container einfügen";
$sLang["mailcampaigns"]["new_banner"] = "Banner";
$sLang["mailcampaigns"]["new_html_text"] = "HTML-Text";
$sLang["mailcampaigns"]["new_article_group"] = "Artikel-Gruppe";
$sLang["mailcampaigns"]["new_link_group"] = "Link-Gruppe";
$sLang["mailcampaigns"]["new_suggest"] = "Suggest";
/*
modules/mailcampaigns
|_start.php
*/
$sLang["mailcampaigns"]["start_delivery_started"] = "Versand wird gestartet";
$sLang["mailcampaigns"]["start_delivery_breake"] = "Versand wird pausiert";
$sLang["mailcampaigns"]["start_campaigns_start"] = "Campaigns start";
$sLang["mailcampaigns"]["start_should_the_campaign"] = "Soll die Kampagne";
$sLang["mailcampaigns"]["start_realy_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["start_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["mailcampaigns"]["start_This_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["mailcampaigns"]["start_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können keine Newsletter verschickt werden";
$sLang["mailcampaigns"]["start_more_informations"] = "Weitere Informationen:";
$sLang["mailcampaigns"]["start_Module_Features"] = "Modul-Vorstellung";
$sLang["mailcampaigns"]["start_rent_buy_Module"] = "Modul mieten/kaufen";
$sLang["mailcampaigns"]["start_shopware_campaigns"] = "Shopware Campaigns";
$sLang["mailcampaigns"]["start_the_newsletter_tool"] = "Das Newsletter-Tool für den Versand professioneller, intelligenter Werbung";
$sLang["mailcampaigns"]["start_new_newsletter"] = "Neuen Newsletter erstellen";
$sLang["mailcampaigns"]["start_status"] = "Status";
$sLang["mailcampaigns"]["start_no_newsletter_delivered"] = "Noch keine Newsletter verschickt";
$sLang["mailcampaigns"]["start_date"] = "Datum";
$sLang["mailcampaigns"]["start_title"] = "Betreff";
$sLang["mailcampaigns"]["start_Recipient"] = "Empfänger";
$sLang["mailcampaigns"]["start_readed"] = "Gelesen";
$sLang["mailcampaigns"]["start_clicked"] = "Geklickt";
$sLang["mailcampaigns"]["start_Turnover"] = "Umsatz";
$sLang["mailcampaigns"]["start_options"] = "Optionen";
$sLang["mailcampaigns"]["start_Shipping_pause"] = "Versand pausieren";
$sLang["mailcampaigns"]["start_click_here_to_Start_shipping"] = "Hier klicken, um Newsletter-Versand zu starten";
$sLang["mailcampaigns"]["start_shipping_complete"] = "Komplett versendet";
/*
modules/mailcampaigns
|_textedit.php
*/
$sLang["mailcampaigns"]["textedit_Text_element_was_deleted"] = "Textelement wurde gelöscht";
$sLang["mailcampaigns"]["textedit_enter_a_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["mailcampaigns"]["textedit_cant_save_text"] = "Text konnte nicht gespeichert werden";
$sLang["mailcampaigns"]["textedit_text_updated"] = "Text wurde aktualisiert";
$sLang["mailcampaigns"]["textedit_text_added"] = "Text wurde angelegt";
$sLang["mailcampaigns"]["textedit_really_be_deleted"] = "Soll dieses Textelement wirklich gel&ouml;scht werden?";
$sLang["mailcampaigns"]["textedit_edit_text"] = "Text bearbeiten";
$sLang["mailcampaigns"]["textedit_Heading"] = "Überschrift:";
$sLang["mailcampaigns"]["textedit_text"] = "Text:";
$sLang["mailcampaigns"]["textedit_save"] = "Speichern";
$sLang["mailcampaigns"]["textedit_delete_text"] = "Text löschen";
/*
modules/mailcampaigns
|_treeCampaign.php
*/
$sLang["mailcampaigns"]["treeCampaign_newsletter_not_found"] = "Newsletter nicht gefunden";
$sLang["mailcampaigns"]["treeCampaign_serverconnection_failed"] = "Verbindung zum Server nicht möglich";
$sLang["mailcampaigns"]["treeCampaign_element_moved"] = "Element wurde verschoben";
/*
modules/mailcampaignspreview
|_skeleton.php
*/
$sLang["mailcampaignspreview"]["skeleton_newsletter_preview"] = "Vorschau Newsletter";
/*
modules/partner
|_partner.php
*/
$sLang["partner"]["partner_partner_was_deleted"] = "Partner wurde gelöscht";
$sLang["partner"]["partner_please_fill_in_all_fields_marked_in_bold"] = "Bitte füllen Sie alle fett markierten Felder aus";
$sLang["partner"]["partner_the_tracking_code"] = "Der Tracking-Code";
$sLang["partner"]["partner_is_already_taken"] = "ist bereits vergeben";
$sLang["partner"]["partner_partner_not_found"] = "Partner konnte nicht gefunden werden";
$sLang["partner"]["partner_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["partner"]["partner_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["partner"]["partner_This_Module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["partner"]["partner_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können keine Partner angelegt werden";
$sLang["partner"]["partner_more_informations"] = "Weitere Informationen:";
$sLang["partner"]["partner_Module_Features"] = "Modul-Vorstellung";
$sLang["partner"]["partner_rent_buy_Module"] = "Modul mieten/kaufen";
$sLang["partner"]["partner_partner_Module"] = "Partner / Affiliate-Modul";
$sLang["partner"]["partner_here_you_can_administrate_partner"] = "Hier können Sie Partner verwalten, die über spezielle Links erfasst und am generierten Umsatz verprovisioniert werden können";
$sLang["partner"]["partner_should_the_partner"] = "Soll der Partner";
$sLang["partner"]["partner_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["partner"]["partner_Partnerdetails"] = "Partnerdetails";
$sLang["partner"]["partner_Number_Range"] = "Nummernkreis";
$sLang["partner"]["partner_array"] = array(
				"id"=>"hide",
				"idcode"=>"Tracking-Code",
				"datum"=>"Eingetragen seit",
				"company"=>"Firma",
				"contact"=>"Ansprechpartner",
				"street"=>"Strasse",
				"streetnumber"=>"Strassennummer",
				"zipcode"=>"PLZ",
				"city"=>"Stadt",
				"phone"=>"Telefon",
				"fax"=> "Telefax",
				"country"=>"Land",
				"email"=>"eMail",
				"web"=>"Internetseite",
				"fix"=>"hide",
				"percent"=>"Provision in %",
				"cookielifetime"=>"Gültigkeit Cookie (Sek.)",
				"active"=>"Aktiv"
				);
$sLang["partner"]["partner_yes"] = "Ja";
$sLang["partner"]["partner_no"] = "Nein";
$sLang["partner"]["partner_save"] = "Speichern";
$sLang["partner"]["partner_Evaluation"] = "Auswertung";
$sLang["partner"]["partner_partner_link"] = "Partner-Link:";
$sLang["partner"]["partner_month_year"] = "Monat / Jahr";
$sLang["partner"]["partner_Turnover"] = "Umsatz";
$sLang["partner"]["partner_Commission"] = "Provision";
$sLang["partner"]["partner_csv_export"] = "CSV - Export";
$sLang["partner"]["partner_No_transactions_available"] = "Noch keine Umsätze vorhanden";
$sLang["partner"]["partner_add_new_partner"] = "Neuen Partner anlegen";
$sLang["partner"]["partner_overview"] = "&Uuml;bersicht:";
$sLang["partner"]["partner_status"] = "Status";
$sLang["partner"]["partner_no_partner_registered"] = "Keine Partner eingetragen";
$sLang["partner"]["partner_Company"] = "Firma";
$sLang["partner"]["partner_Entered"] = "Eingetragen.";
$sLang["partner"]["partner_activ"] = "Aktiv";
$sLang["partner"]["partner_Annual_sales"] = "Jahresumsatz";
$sLang["partner"]["partner_Monthly_Sales"] = "Monatsumsatz";
$sLang["partner"]["partner_options"] = "Optionen";
/*
modules/adwords
|_skeleton.php
*/
$sLang["adwords"]["skeleton_Google_Adwords_Export"] = "Adwords-Generator";
/*
modules/adwords
|_summary.php
*/
$sLang["adwords"]["summary_articles_overview"] = "articles.overview";
$sLang["adwords"]["summary_categorie"] = "Kategorien";
$sLang["adwords"]["summary_shopware"] = "Shopware";
$sLang["adwords"]["summary_settings"] = "Einstellungen";
/*
modules/adwords
|_articles.php
*/
$sLang["adwords"]["articles_reorder_treepanel"] = "Reorder TreePanel";
$sLang["adwords"]["articles_categorie"] = "Kategorie:";
$sLang["adwords"]["articles_Export_of"] = "Export von:";
$sLang["adwords"]["articles_Export_until"] = "Export bis:";
$sLang["adwords"]["articles_max_cpc"] = "Max-CPC (in Euro):";
$sLang["adwords"]["articles_export"] = "Export";
/*
modules/cms
|_skeleton.php
*/
$sLang["cms"]["skeleton_content_managment"] = "Feeds";
/*
modules/cms
|_cms.php
*/
$sLang["cms"]["cms_group_added"] = "Gruppe hinzugefügt";
$sLang["cms"]["cms_article_deleted"] = "Artikel wurde gelöscht";
$sLang["cms"]["cms_enter_title"] = "Bitte geben Sie eine Überschrift ein";
$sLang["cms"]["cms_only_jpeg_accepted"] = "Fehler: Es werden ausschließlich JPEG-Bilder unterstützt";
$sLang["cms"]["cms_Entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["cms"]["cms_article_not_found"] = "Artikel konnte nicht gefunden werden";
$sLang["cms"]["cms_should_the_article"] = "Soll das Artikel";
$sLang["cms"]["cms_really_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["cms"]["cms_choose_the_group"] = "Auswahl der Gruppe";
$sLang["cms"]["cms_group"] = "Gruppe:";
$sLang["cms"]["cms_or_new"] = "oder neu:";
$sLang["cms"]["cms_update"] = "Aktualisieren";
$sLang["cms"]["cms_new_entry"] = "Neuer Eintrag";
$sLang["cms"]["cms_edit_content"] = "Content bearbeiten";
$sLang["cms"]["cms_title"] = "Überschrift:";
$sLang["cms"]["cms_text"] = "Text:";
$sLang["cms"]["cms_date"] = "Datum:";
$sLang["cms"]["cms_image_upload"] = "Bild-Upload:";
$sLang["cms"]["cms_file_upload"] = "Datei-Upload:";
$sLang["cms"]["cms_extern_link"] = "Externer Link:";
$sLang["cms"]["cms_save"] = "Speichern";
$sLang["cms"]["cms_save_edit"] = "Änderungen speichern";
$sLang["cms"]["cms_overview"] = "&Uuml;bersicht:";
/*
modules/cmsstatic
|_cms.php
*/
$sLang["cmsstatic"]["cms_site_deleted"] = "Seite wurde gelöscht";
$sLang["cmsstatic"]["cms_enter_title"] = "Bitte geben Sie eine Überschrift ein";
$sLang["cmsstatic"]["cms_enter_groupname"] = "Bitte geben Sie einen Gruppennamen ein";
$sLang["cmsstatic"]["cms_Entry_saved"] = "Eintrag wurde gespeichert";
$sLang["cmsstatic"]["cms_article_not_found"] = "Artikel konnte nicht gefunden werden";
$sLang["cmsstatic"]["cms_should_the_site"] = "Soll die Seite";
$sLang["cmsstatic"]["cms_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["cmsstatic"]["cms_delete_recrusiv"] = "Beachten Sie: Hierbei werden auch alle Unterknoten gelöscht!";
$sLang["cmsstatic"]["cms_no_selection"] = "Bitte wählen Sie zunächst die Seite an, die gelöscht werden soll.";
$sLang["cmsstatic"]["cms_edit_content"] = "Content bearbeiten";
$sLang["cmsstatic"]["cms_HTML_code_to_integrate_the_page"] = "HTML-Code zur Einbindung der Seite:";
$sLang["cmsstatic"]["cms_array"] = array("id"=>"hide",
		"tpl1variable"=>"Template-1 Variable",
		"tpl1path"=>"Template-1 Pfad",
		"tpl2variable"=>"Template-2 Variable",
		"tpl2path"=>"Template-2 Pfad",
		"tpl3variable"=>"Template-3 Variable",
		"tpl3path"=>"Template-3 Pfad",
		"description"=>"Bezeichnung",
		"html"=>"Inhalt"
		);
$sLang["cmsstatic"]["cms_save"] = "Speichern";
$sLang["cmsstatic"]["cms_added_site"] = "Angelegte Seiten:";
/*
modules/cmsstatic
|_cms.php
*/
$sLang["cmsstatic"]["skeleton_cms"] = "Shopseiten";
/*
modules/orderscanceled
|_skeleton.php
*/
$sLang["orderscanceled"]["skeleton_calceled_orders"] = "Abbruch-Analyse";
/*
modules/orderscanceled
|_orders.php
*/
$sLang["orderscanceled"]["orders_should_the_customer"] = "Soll dem Kunden";
$sLang["orderscanceled"]["orders_a_voucher_really"] = "wirklich ein Gutschein geschickt werden?";
$sLang["orderscanceled"]["orders_should_the_customer_1"] = "Soll der Kunde";
$sLang["orderscanceled"]["orders_really_be_questioned"] = "wirklich zum Bestellabbruch befragt werden?";
$sLang["orderscanceled"]["orders_aborted_appointment_of_customer"] = "Soll die abgebrochene Bestellung des Kunden";
$sLang["orderscanceled"]["orders_really_deleted"] = "wirklich aus der Liste entfernt werden?";
$sLang["orderscanceled"]["orders_confirm"] = "Bestätigung";
$sLang["orderscanceled"]["orders_marked_orders_really_deleted"] = "Sollen die markierten Bestellungen wirklich gelöscht werden?";
$sLang["orderscanceled"]["orders_show_all"] = "Alle anzeigen";
$sLang["orderscanceled"]["orders_send_voucher"] = "Gutschein schicken:";
$sLang["orderscanceled"]["orders_worth"] = "(Wert:";
$sLang["orderscanceled"]["orders_please_select"] = "Bitte wählen";
$sLang["orderscanceled"]["orders_Ask_why"] = "Grund erfragen";
$sLang["orderscanceled"]["orders_customer"] = "Kunde:";
$sLang["orderscanceled"]["orders_Phone"] = "Telefon:";
$sLang["orderscanceled"]["orders_email"] = "eMail:";
$sLang["orderscanceled"]["orders_Order_positions"] = "Bestellpositionen:";
$sLang["orderscanceled"]["orders_Time"] = "Zeitpunkt";
$sLang["orderscanceled"]["orders_action"] = "Aktion";
$sLang["orderscanceled"]["orders_Amount"] = "Betrag";
$sLang["orderscanceled"]["orders_Transaction"] = "Transaktion";
$sLang["orderscanceled"]["orders_Payment"] = "Zahlart";
$sLang["orderscanceled"]["orders_customer"] = "Kunde";
$sLang["orderscanceled"]["orders_orders"] = "Bestellungen";
$sLang["orderscanceled"]["orders_no_orders_found"] = "Keine Bestellungen gefunden";
$sLang["orderscanceled"]["orders_Number_of_Orders"] = "Anzahl Bestellungen";
$sLang["orderscanceled"]["orders_update"] = "Aktualisieren";
$sLang["orderscanceled"]["orders_mark_all_orders"] = "Alle Bestellungen markieren";
$sLang["orderscanceled"]["orders_delete_marked_orders"] = "Markierte Bestellungen löschen";
$sLang["orderscanceled"]["orders_canceled_orders"] = "Abgebrochene Bestellungen";
$sLang["orderscanceled"]["orders_overview"] = "Übersicht";
$sLang["orderscanceled"]["orders_date"] = "Datum";
$sLang["orderscanceled"]["orders_abandoned_shopping_carts"] = "Abgr. Warenkörbe";
$sLang["orderscanceled"]["orders_shopping_cart"] = "Ø Warenkorb";
$sLang["orderscanceled"]["orders_Visitor"] = "Besucher";
$sLang["orderscanceled"]["orders_Page_views"] = "Seitenaufrufe";
$sLang["orderscanceled"]["orders_article"] = "Artikel";
$sLang["orderscanceled"]["orders_order_number"] = "Bestellnummer";
$sLang["orderscanceled"]["orders_quantity"] = "Menge";
$sLang["orderscanceled"]["orders_Exit_Pages"] = "Ausstiegsseiten";
$sLang["orderscanceled"]["orders_percent"] = "Prozent";
$sLang["orderscanceled"]["orders_number"] = "Anzahl";
$sLang["orderscanceled"]["orders_viewport"] = "Viewport";
$sLang["orderscanceled"]["orders_exchange"] = "Umsatz";
$sLang["orderscanceled"]["orders_filter"] = "Filter";
$sLang["orderscanceled"]["orders_from"] = "Von";
$sLang["orderscanceled"]["orders_until"] = "Bis";
$sLang["orderscanceled"]["orders_method_of_payment"] = "Zahlart";
$sLang["orderscanceled"]["orders_please_select"] = "Bitte auswählen";
$sLang["orderscanceled"]["orders_filter"] = "Filtern";
$sLang["orderscanceled"]["orders_new_customer"] = "Neukunden";
$sLang["orderscanceled"]["orders_Impressions"] = "Impressions";
$sLang["orderscanceled"]["orders_description"] = "Bezeichnung";
$sLang["orderscanceled"]["orders_worth_1"] = "Wert";
$sLang["orderscanceled"]["orders_statistics"] = "Statistiken";
$sLang["orderscanceled"]["orders_abandoned_shopping_carts_1"] = "Abgebrochene Warenkörbe";
/*
modules/account
|_skeleton.php
*/
$sLang["filter"]["properties"] = "Eigenschaften";
/*
modules/account
|_skeleton.php
*/
$sLang["articlesfast"]["windowtitle"] = "Schnelleingabe";
/*
modules/search_price
|_skeleton.php
*/
$sLang["search_price"]["windowtitle"] = "Preisexporte";
/*
modules/account
|_skeleton.php
*/
$sLang["account"]["skeleton_your_shopware_account"] = "Shopware Account";
$sLang["account"]["skeleton_overview"] = "&Uuml;bersicht";
$sLang["account"]["skeleton_Modules_license"] = "Module lizenzieren";
$sLang["account"]["skeleton_systemupdate"] = "Systemupdates";
$sLang["account"]["skeleton_shopware_services"] = "Shopware Services";
$sLang["account"]["skeleton_support"] = "Support";
/*
modules/account
|_start.php
*/
$sLang["account"]["start_reorder_treepanel"] = "Reorder TreePanel";
$sLang["account"]["start_account_overview"] = "Konto &Uuml;bersicht";
$sLang["account"]["start_credit"] = "Guthaben: 250 SC";
$sLang["account"]["start_Last_Book"] = "Letzte Buchung: 22.02.2008";
$sLang["account"]["start_please_charge_your_account"] = "Bitte laden Sie Ihr Kundenkonto auf!";
$sLang["account"]["start_Account_recharge"] = "Konto aufladen";
$sLang["account"]["start_Here_you_can_rechange_your_credit_quickly_and_easily"] = "Hier können Sie schnell und einfach Ihr Guthaben aufladen";
$sLang["account"]["start_one_euro_corresponds_one_shopcoin"] = "1,00 € entspricht 1 SC (Shopware Coin)";
$sLang["account"]["start_after_a_click_on"] = "Nach Klick auf &quot;Guthaben aufladen&quot; werden Sie auf die Zahlungsseite
		weitergeleitet. Bitte geben Sie hier Ihre Bankverbindung / Kreditkartendaten ein
		und klicken Sie auf &quot;Weiter&quot;. Nach Überprüfung der Daten, wird Ihnen das Guthaben
		unmittelbar gutgeschrieben.";
$sLang["account"]["start_Amount"] = "Betrag";
$sLang["account"]["start_please_wait"] = "Bitte wählen...";
$sLang["account"]["start_fifty_euro"] = "50,00 &euro; = 50 SC";
$sLang["account"]["start_hundred_euro"] = "100,00 &euro; = 100 SC";
$sLang["account"]["start_twohundredfifty_euro"] = "250,00 &euro; = 250 SC";
$sLang["account"]["start_fivehundred_euro"] = "500,00 &euro; = 500 SC";
$sLang["account"]["start_method_of_payment"] = "Zahlungsart";
$sLang["account"]["start_please_select"] = "Bitte wählen...";
$sLang["account"]["start_debit"] = "Lastschrift";
$sLang["account"]["start_credit_card"] = "Kreditkarte";
$sLang["account"]["start_charge_credit"] = "Guthaben aufladen";
$sLang["account"]["start_Book_List"] = "Buchungsliste";
$sLang["account"]["start_mooTable"] = "Hier mooTable mit allen Buchungen";
/*
modules/account
|_modules.php
*/
$sLang["account"]["modules_active_modules"] = "Aktive Module";
$sLang["account"]["modules_module_title"] = "Modul-Bezeichnung";
$sLang["account"]["modules_Mode"] = "Modus";
$sLang["account"]["modules_options"] = "Optionen";
$sLang["account"]["modules_Rental"] = "Miete / Monat";
$sLang["account"]["modules_customergroup"] = "Kundengruppe";
$sLang["account"]["modules_Purchased"] = "Gekauft";
$sLang["account"]["modules_Articles_configurator"] = "Artikel - Konfigurator";
$sLang["account"]["modules_Rent"] = "Miete";
$sLang["account"]["modules_29_SC"] = "29,00 SC";
$sLang["account"]["modules_rent_buy_modules"] = "Module kaufen / mieten";
$sLang["account"]["modules_modules"] = "Modul";
$sLang["account"]["modules_please_select"] = "Bitte wählen...";
$sLang["account"]["modules_Action_Module"] = "Aktionsmodul";
$sLang["account"]["modules_Module_actions"] = "Modul: Aktionen";
$sLang["account"]["modules_with_the_actions_module"] = "Mit dem Aktionsmodul können Sie schnell und einfach eigene Werbekampagnen starten";
$sLang["account"]["modules_Price"] = "Kaufpreis: 495,00 &euro;";
$sLang["account"]["modules_or_rent"] = "oder für 29 SC / Monat mieten";
$sLang["account"]["modules_more_informations"] = "Weitere Informationen";
$sLang["account"]["modules_after_a_click_on"] = "Nach Klick auf &quot;Lizenz beantragen&quot; setzen wir uns unverzüglich mit Ihnen in Verbindung.";
$sLang["account"]["modules_Rental_costs_will_be"] = "Mietkosten werden über Ihr Shopware-Konto abgerechnet. Bei Kauf erhalten Sie eine Rechnung
mit einem Zahlungsziel von 14 Tagen.";
$sLang["account"]["modules_Minimum_term_for_rent"] = "Mindestlaufzeit bei Miete: 1 Monat";
$sLang["account"]["modules_buy_rent"] = "Kauf/Miete";
$sLang["account"]["modules_buy_for"] = "Kaufen für 495,00 &euro;";
$sLang["account"]["modules_rent_for"] = "Mieten für 25 SC / Monat";
$sLang["account"]["modules_Licence"] = "Lizenz beantragen";
/*
modules/account
|_services.php
*/
$sLang["account"]["modules_recorder_treepanel"] = "Reorder TreePanel";
$sLang["account"]["modules_Available_updates"] = "Verfügbare Updates";
$sLang["account"]["modules_in_future_you_will_find"] = "Hier finden Sie in Zukunft sinnvolle Zusatzdienstleistungen für den
Ausbau Ihres Online-Erfolgs";
/*
modules/account
|_support.php
*/
$sLang["account"]["support_reorder_treepanel"] = "Reorder TreePanel";
$sLang["account"]["support_support"] = "Support";
/*
modules/account
|_updates.php
*/
$sLang["account"]["updates_recorder_treepanel"] = "Reorder TreePanel";
$sLang["account"]["updates_Available_updates"] = "Verfügbare Updates";
$sLang["account"]["updates_shopware_version"] = "Ihre Shopware-Version: 2.01";
$sLang["account"]["updates_update_to_Version"] = "Update auf Shopware 2.03";
$sLang["account"]["updates_release_date"] = "Release-Datum: 15.03.2008";
$sLang["account"]["updates_updateprice"] = "Updatepreis: 100 SC";
$sLang["account"]["updates_changelist"] = "Liste mit Änderungen / neuen Funktionen";
$sLang["account"]["updates_after_a_click_on"] = "Nach Klick auf &quot;Update kaufen&quot; setzen wir uns unverzüglich mit Ihnen in Verbindung";
$sLang["account"]["updates_The_costs_are_about"] = "Die Kosten werden über Ihr Shopware-Konto abgerechnet.
Wir installieren das Update zunächst auf einem Test-System und bei Abnahme durch Sie bestimmen
wir gemeinsam den Zeitpunkt der Aktualisierung des Live-Systems.
Die Kosten für die Installation sind im Preis inbegriffen";
$sLang["account"]["updates_buy_update"] = "Update kaufen";
/*
modules/orderlist
|_skeleton.php
*/
$sLang["orderlist"]["skeleton_orders"] = "Bestellungen";
/*
modules/orderlist
|_skeleton.php
*/
$sLang["orderlist"]["orders_show_all"] = "Alle anzeigen";
$sLang["orderlist"]["orders_time"] = "Zeitpunkt";
$sLang["orderlist"]["orders_ordernumber"] = "Bestellnr.";
$sLang["orderlist"]["orders_Amount"] = "Betrag";
$sLang["orderlist"]["orders_Transaction"] = "Transaktion";
$sLang["orderlist"]["orders_Order_Status"] = "Bestellstatus";
$sLang["orderlist"]["orders_paymentstatus"] = "Zahlstatus";
$sLang["orderlist"]["orders_paymentdescription"] = "Zahlart";
$sLang["orderlist"]["orders_customer"] = "Kunde";
$sLang["orderlist"]["orders_options"] = "Optionen";
$sLang["orderlist"]["orders_orders"] = "Bestellungen";
$sLang["orderlist"]["orders_from"] = "von";
$sLang["orderlist"]["orders_no_orders_found"] = "Keine Bestellungen gefunden";
$sLang["orderlist"]["orders_numbers_of_orders"] = "Anzahl Bestellungen";
$sLang["orderlist"]["orders_update"] = "Aktualisieren";
$sLang["orderlist"]["orders_Order_Summary"] = "Bestell-Übersicht";
$sLang["orderlist"]["orders_The_status_of_the_order"] = "Der Status der Bestellung";
$sLang["orderlist"]["orders_has_left"] = "wurde auf";
$sLang["orderlist"]["orders_amended"] = "geändert!";
$sLang["orderlist"]["orders_Turnover"] = "Umsatz";
$sLang["orderlist"]["orders_Orders"] = "Bestellungen";
$sLang["orderlist"]["orders_filter"] = "Filter";
$sLang["orderlist"]["orders_from"] = "Von";
$sLang["orderlist"]["orders_until"] = "Bis";
$sLang["orderlist"]["orders_Please_select"] = "Bitte auswählen";
$sLang["orderlist"]["orders_Number_status"] = "Zahlstatus";
$sLang["orderlist"]["orders_payment"] = "Zahlart";
$sLang["orderlist"]["orders_filters"] = "Filtern";
$sLang["orderlist"]["orders_new_customer"] = "Neukunden";
$sLang["orderlist"]["orders_Visitors"] = "Besucher";
$sLang["orderlist"]["orders_Impressions"] = "Impressions";
$sLang["orderlist"]["orders_title"] = "Bezeichnung";
$sLang["orderlist"]["orders_Worth"] = "Wert";
$sLang["orderlist"]["orders_Statistics"] = "Statistiken";
$sLang["orderlist"]["orders_Subject"] = "Betreff:";
$sLang["orderlist"]["orders_Recipient"] = "Empfänger:";
$sLang["orderlist"]["orders_mail_send"] = "eMail wurde versendet!";

$sLang["orderlist"]["del_order_confirmtitle"] = "Löschvorgang";
$sLang["orderlist"]["del_order_confirmtxt_1"] = "Wollen Sie wirklich die Bestellung";
$sLang["orderlist"]["del_order_confirmtxt_2"] = "von";
$sLang["orderlist"]["del_order_confirmtxt_3"] = "löschen?";
$sLang["orderlist"]["del_order_acknowltxt_1"] = "Die Bestellung";
$sLang["orderlist"]["del_order_acknowltxt_2"] = "von";
$sLang["orderlist"]["del_order_acknowltxt_3"] = "wurde erfolgreich gelöscht!";
$sLang["orderlist"]["del_order_cancel"] = "Der Löschvorgang wurde abgebrochen!";
/*
modules/premiums
|_skeleton.php
*/
$sLang["premius"]["skeleton_premiumarticle"] = "Pr&auml;mienartikel";
/*
modules/premiums
|_premiums.php
*/
$sLang["premius"]["premiums_premiums"] = "Premiums";
$sLang["premius"]["premiums_premium_deleted"] = "Prämie wurde gelöscht";
$sLang["premius"]["premiums_Please_specify_a_minimum_order_value"] = "Bitte geben Sie einen Mindestbestellwert ein!";
$sLang["premius"]["premiums_Please_specify_a_article_number"] = "Bitte geben Sie eine Artikelnummer ein!";
$sLang["premius"]["premiums_Please_specify_a_article_number_Shop"] = "Bitte geben Sie eine Artikelnummer-Shop ein!";
$sLang["premius"]["premiums_premium_added"] = "Prämie hinzugefügt";
$sLang["premius"]["premiums_change_saved"] = "Änderungen gespeichert";
$sLang["premius"]["premiums_should_the_premium"] = "Soll die Prämie";
$sLang["premius"]["premiums_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["premius"]["premiums_new_premium"] = "Neue Prämie";
$sLang["premius"]["premiums_premiumarticle"] = "Pr&auml;mienartikel";
$sLang["premius"]["premiums_Minimum_Turnover"] = "Mindestumsatz:";
$sLang["premius"]["premiums_articlenumber"] = "Artikelnummer-Warenwirtschaft:";
$sLang["premius"]["premiums_articlenumber_shop"] = "Artikelnummer-Shop:";
$sLang["premius"]["premiums_save_premium"] = "Prämie speichern";
$sLang["premius"]["premiums_Order_value"] = "Bestellwert ab";
$sLang["premius"]["premiums_pseudo_articlenumber_shop"] = "Pseudo-Artikelnummer Shop";
$sLang["premius"]["premiums_options"] = "Optionen";
/*
modules/promotion
|_skeleton.php
*/
$sLang["promotion"]["skeleton_Shopping_worlds"] = "Einkaufswelten";
/*
modules/promotion
|_promotion.php
*/
$sLang["promotion"]["promotion_articles_overview"] = "articles.overview";
$sLang["promotion"]["promotion_Categories"] = "Kategorien";
$sLang["promotion"]["promotion_shopware"] = "Shopware";
$sLang["promotion"]["promotion_options"] = "Einstellungen";
/*
modules/promotion
|_promotion_inline.php
*/
$sLang["promotion"]["promotion_inline_No_category_selected"] = "Keine Kategorie ausgewählt";
$sLang["promotion"]["promotion_inline_start"] = "Startseite";
$sLang["promotion"]["promotion_inline_promotion_deleted"] = "Promotion wurde gelöscht";
$sLang["promotion"]["promotion_inline_promotion_cant_be_deleted"] = "Promotion konnte nicht gelöscht werden";
$sLang["promotion"]["promotion_inline_title"] = "Bezeichnung";
$sLang["promotion"]["promotion_inline_ordernumber"] = "Bestellnummer";
$sLang["promotion"]["promotion_inline_image"] = "Bild";
$sLang["promotion"]["promotion_inline_article_with_ordernumber"] = "Artikel mit der Bestellnummer";
$sLang["promotion"]["promotion_inline_not_found"] = "konnte nicht gefunden werden!";
$sLang["promotion"]["promotion_inline_wrong_fileformat"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["promotion"]["promotion_inline_error_during_upload"] = "Fehler bei Upload";
$sLang["promotion"]["promotion_inline_promotion_saved"] = "Promotion gespeichert";
$sLang["promotion"]["promotion_inline_error"] = "Promotion konnte nicht gespeichert werden";
$sLang["promotion"]["promotion_inline_should_the_promotion"] = "Soll die Promotion";
$sLang["promotion"]["promotion_inline_really_deleted"] = "wirklich gelöscht werden?";
$sLang["promotion"]["promotion_inline_please_fill_out"] = "Bitte füllen Sie folgende Felder aus";
$sLang["promotion"]["promotion_inline_Promotion_Art"] = "Promotion Art";
$sLang["promotion"]["promotion_inline_Fixed_Article"] = "Fester Artikel";
$sLang["promotion"]["promotion_inline_random_article"] = "Zufälliger Artikel";
$sLang["promotion"]["promotion_inline_new"] = "Neuheit";
$sLang["promotion"]["promotion_inline_top_article"] = "Top-Artikel";
$sLang["promotion"]["promotion_inline_own_picture"] = "Eigenes Bild";
$sLang["promotion"]["promotion_inline_title_1"] = "Bezeichnung:";
$sLang["promotion"]["promotion_inline_valid_from"] = "Gültig von:";
$sLang["promotion"]["promotion_inline_valid_until"] = "Gültig bis:";
$sLang["promotion"]["promotion_inline_options_for_own_image"] = "Optionen für &quot;Eigenes Bild&quot;";
$sLang["promotion"]["promotion_inline_own_image"] = "Eigenes Bild:";
$sLang["promotion"]["promotion_inline_link"] = "Link:";
$sLang["promotion"]["promotion_inline_link_target"] = "Link-Ziel:";
$sLang["promotion"]["promotion_inline_shopware"] = "Shopware";
$sLang["promotion"]["promotion_inline_extern"] = "Extern";
$sLang["promotion"]["promotion_inline_Defined_options_for_Article"] = "Optionen für definierten Artikel";
$sLang["promotion"]["promotion_inline_ordernumber"] = "Bestellnummer:";
$sLang["promotion"]["promotion_inline_add_promotion"] = "Promotion anlegen";
$sLang["promotion"]["promotion_inline_new_promotion"] = "Neue Promotion";
$sLang["promotion"]["promotion_inline_Already_associated_promotions"] = "Bereits zugeordnete Promotions dieser Kategorie:";
$sLang["promotion"]["promotion_inline_Position_changes"] = "Position ver&auml;ndern: Per Drag &amp; Drop auf Bezeichnung klicken!";
$sLang["promotion"]["promotion_inline_Position_saved"] = "Position speichern";
/*
modules/presetting
|_skeleton.php
*/
$sLang["presettings"]["skeleton_settings"] = "Grundeinstellungen";
/*
modules/presetting
|_settings.php
*/
$sLang["presettings"]["settings_articles_overview"] = "articles.overview";
$sLang["presettings"]["settings_settings"] = "Einstellungen";
$sLang["presettings"]["settings_shopware_configuration"] = "Shopware Konfiguration";
$sLang["presettings"]["settings_important"] = "Wichtig!";
$sLang["presettings"]["settings_Please_note_the_reference_texts_on_the_various_options"] = "Bitte beachten Sie die Hinweistexte zu den einzelnen Optionen";
/*
modules/presetting
|_api.php
*/
$sLang["presettings"]["api_shopware_api_Access"] = "Shopware API Zugriff";
$sLang["presettings"]["api_with_the_shopware_api"] = "Mit der Shopware API verfügen Sie über leistungsstarke Möglichkeiten
eigene Anbindungen und Schnittstellen zu realisieren.
Um den Zugriff auf die API freizuschalten - erzeugen Sie bitte
hier einen eindeutigen Schlüssel, den Sie für Ihre Schnittstellen / Programmierungen
verwenden.";
$sLang["presettings"]["api_shopware_developer_portal"] = "Zum Shopware Entwickler-Portal";
$sLang["presettings"]["api_api_key"] = "API - Schlüssel";
$sLang["presettings"]["api_api_key_1"] = "API - Schlüssel:";
$sLang["presettings"]["api_Generate_new_key"] = "Neuen Schlüssel generieren";
/*
modules/presetting
|_attributes.php
*/
$sLang["presettings"]["attributes_Article_attribute"] = "Artikel-Attribut";
$sLang["presettings"]["attributes_array"] = array(
		"id"=>"hide",
		"group"=>"hide",
		"domname"=>"Eindeutiger Name (attr[1] bis attr[20])",
		"domvalue"=>"Standardwert",
		"domtype"=>"Typ des Feldes (boolean, text, textarea) ",
		"domdescription"=>"Name des Feldes",
		"required"=>"Auswahl erzwingen",
		"position"=>"Position:",
		"databasefield"=>"Datenbank-Feld (attr1 bis attr20)",
		"domclass"=>"hide",
		"version"=>"hide",
		"availablebyvariants"=>"Eingabe für Varianten möglich",
		"help"=>"Hilfetext Eingabe"
		);
$sLang["presettings"]["attributes_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["attributes_Entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["attributes_not_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["attributes_should"] = "Soll";
$sLang["presettings"]["attributes_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["attributes_yes"] = "Ja";
$sLang["presettings"]["attributes_no"] = "Nein";
$sLang["presettings"]["attributes_save"] = "Speichern";
$sLang["presettings"]["attributes_Verfügbare_Datensätze"] = "Verfügbare Datensätze:";
$sLang["presettings"]["attributes_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
$sLang["presettings"]["attributes_For_example"] = "Beispiel für eindeutigen Namen attr[freie Nummer], Beispiel für Datenbank-Feld attr[freie Nummer]";
/*
modules/presetting
|_attributes.php
*/
$sLang["presettings"]["countries_country"] = "Land";
$sLang["presettings"]["countries_array"] = array(
		"id"=>"hide",
		"countryname"=>"Name des Landes",
		"countryiso"=>"ISO-Code (2-stellig)",
		"countryarea"=>"Lieferzonen Angabe (deutschland, europa, welt)",
		"countryen"=>"Englische Bezeichnung des Landes",
		"position"=>"Position des Landes in der Auswahlbox",
		"notice"=>"Beschreibungstext (evtl. Zölle etc.)",
		"shippingfree"=>"hide",
		"active"=>"Aktiv"
		);
$sLang["presettings"]["countries_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["countries_Entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["countries_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["countries_should"] = "Soll";
$sLang["presettings"]["countries_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["countries_Creating"] = "anlegen";
$sLang["presettings"]["countries_edit"] = "bearbeiten";
$sLang["presettings"]["countries_save"] = "Speichern";
$sLang["presettings"]["countries_added_countries"] = "Angelegte Länder:";
/*
modules/presetting
|_attributes.php
*/
$sLang["presettings"]["cronjobs_cronjobs"] = "Cronjobs";
$sLang["presettings"]["cronjobs_array"] = array(
		"id"=>"hide",
		"start"=>"hide",
		"end"=>"hide",
		"data"=>"hide",
		"name"=>"hide",
		"next"=>"N&auml;chste Ausf&uuml;hrung",
		"last"=>"Letzte Ausf&uuml;hrung",
		"active"=>"Aktiv",
		"inform_mail"=>"Empf&auml;nger eMail-Adresse",
		"inform_template"=>"eMail Template",
		"elementID"=>"hide",
		"action"=>"hide"
		);
$sLang["presettings"]["cronjobs_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["cronjobs_Entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["cronjobs_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["cronjobs_should"] = "Soll";
$sLang["presettings"]["cronjobs_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["cronjobs_create"] = "anlegen";
$sLang["presettings"]["cronjobs_edit"] = "bearbeiten";
$sLang["presettings"]["cronjobs_save"] = "Speichern";
$sLang["presettings"]["cronjobs_Existing_Cronjobs"] = "Vorhandene Cronjobs:";
/*
modules/presetting
|_attributes.php
*/
$sLang["presettings"]["currencies_Currency"] = "Währung";
$sLang["presettings"]["currencies_array"] = array(
	"id"=>"hide",
	"templatechar"=>"Symbol",
	"factor"=>"Faktor",
	"symbol_position"=>"Symbol-Position"
);	
$sLang["presettings"]["currencies_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["currencies_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["currencies_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["currencies_should"] = "Soll";
$sLang["presettings"]["currencies_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["currencies_create"] = "anlegen";
$sLang["presettings"]["currencies_edit"] = "bearbeiten";
$sLang["presettings"]["currencies_yes"] = "Ja";
$sLang["presettings"]["currencies_no"] = "Nein";
$sLang["presettings"]["currencies_save"] = "Speichern";
$sLang["presettings"]["currencies_PRICES_COULD_NOT_BE_UPDATED"] = "WARNUNG! KURSE KONNTEN NICHT AKTUALISIERT WERDEN!!!";
$sLang["presettings"]["currencies_Courses_updated"] = "Kurse aktualisiert";
$sLang["presettings"]["currencies_New_Currency"] = "Neue Währung";
$sLang["presettings"]["currencies_created_currencies"] = "Angelegte Währungen:";
$sLang["presettings"]["currencies_please_edit_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
$sLang["presettings"]["currencies_update_Courses"] = "Kurse aktualisieren";
/*
modules/presetting
|_customergroups.php
*/
$sLang["presettings"]["customergroups_customergroup_deleted"] = "Kundengruppe wurde gelöscht";
$sLang["presettings"]["customergroups_Please_define_a"] = "Bitte definieren Sie einen Mindermengenzuschlag";
$sLang["presettings"]["customergroups_enter_a_name_for_customergroup"] = "Bitte geben Sie einen Namen für die Kundengruppe ein";
$sLang["presettings"]["customergroups_Cart_rebate_could_not_be_inserted"] = "Warenkorb-Rabatt konnte nicht eingefügt werden - Start:";
$sLang["presettings"]["customergroups_rebate"] = "Rabatt:";
$sLang["presettings"]["customergroups_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["customergroups_customergroup_not_found"] = "Kundengruppe konnte nicht gefunden werden";
$sLang["presettings"]["customergroups_should_the_customergroup"] = "Soll die Kundengruppe";
$sLang["presettings"]["customergroups_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["customergroups_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["presettings"]["customergroups_This_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["presettings"]["customergroups_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können keine neuen Kundengruppen angelegt werden";
$sLang["presettings"]["customergroups_more_informations"] = "Weitere Informationen:";
$sLang["presettings"]["customergroups_Module_Features"] = "Modul-Vorstellung";
$sLang["presettings"]["customergroups_new_customergroup"] = "Neue Kundengruppe";
$sLang["presettings"]["customergroups_edit_customergroup"] = "Kundengruppe bearbeiten";
$sLang["presettings"]["customergroups_array"] = array("id"=>"hide",
		"groupkey"=>"Interne ID",
		"description"=>"Bezeichnung",
		"tax"=>"Bruttopreise in Storefront",
		"taxinput"=>"Eingabe Bruttopreise",
		"mode"=>"Kundengruppen-Modus",
		"discount"=>"Globaler Rabatt in %",
		"basketdiscount"=>"hide",
		"basketdiscountstart"=>"hide",
		"minimumorder"=>"Mindestbestellwert",
		"minimumordersurcharge"=>"Mindermengenzuschlag"
		);
$sLang["presettings"]["customergroups_yes"] = "Ja";
$sLang["presettings"]["customergroups_no"] = "Nein";
$sLang["presettings"]["customergroups_own_prices_per_Article"] = "Eigene Preise je Artikel";
$sLang["presettings"]["customergroups_Global_Discount"] = "Globaler Rabatt";
$sLang["presettings"]["customergroups_From_shopping_cart_value"] = "ab Warenkorb-Wert";
$sLang["presettings"]["customergroups_shopping_cart_discount"] = "Warenkorb-Rabatt in %";
$sLang["presettings"]["customergroups_save"] = "Speichern";
$sLang["presettings"]["customergroups_created_customergroups"] = "Angelegte Kundengruppen:";
$sLang["presettings"]["customergroups_Deleting_a_group_of_customers"] = "Das L&ouml;schen einer Kundengruppe, der bereits Kunden zugeordnet sind, ist nicht m&ouml;glich!";
/*
modules/presetting
|_documents.php
*/
$sLang["presettings"]["documents_array"] = array (
	"margin" => "Seitenabstand",
	"header" => "Briefkopf",
	"sender" => "Anschrift",
	"headline" => "Überschrift",
	"content_middle" => "Inhalt",
	"footer" => "Fußzeile"
);
$sLang["presettings"]["customergroups_save"] = "Speichern";
$sLang["presettings"]["customergroups_preview"] = "Vorschau:";
$sLang["presettings"]["customergroups_Show"] = "Zeigen:";
/*
modules/presetting
|_documents.php
*/
$sLang["presettings"]["factory_Factory"] = "Factory";
$sLang["presettings"]["factory_array"] = array(
		"id"=>"hide",
		"basename"=>"Name der Basis-Klasse",
		"basefile"=>"Datei Basis-Klasse",
		"inheritname"=>"Name der vererbten Klasse",
		"inheritfile"=>"Datei der vererbten Klasse",
		"viewport_file"=>"PHP-File",
		"description"=>"Bezeichnung"
		);
$sLang["presettings"]["factory_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["factory_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["factory_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["factory_should"] = "Soll";
$sLang["presettings"]["factory_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["factory_Creating"] = "Neue Factory anlegen";
$sLang["presettings"]["factory_edit"] = "bearbeiten";
$sLang["presettings"]["factory_yes"] = "Ja";
$sLang["presettings"]["factory_no"] = "Nein";
$sLang["presettings"]["factory_Available_records"] = "Verfügbare Datensätze:";
$sLang["presettings"]["factory_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
$sLang["presettings"]["factory_save"] = "Speichern";
/*
modules/presetting
|_licences.php
*/
$sLang["presettings"]["licences_licence"] = "Lizenz";
$sLang["presettings"]["licences_array"] = array(
		"id"=>"hide",
		"module"=>"Komponente",
		"hash"=>"Lizenznummer",
		"inactive"=>"Inaktiv schalten"
		);
$sLang["presettings"]["licences_deleted"] = "wurde gelöscht";
$sLang["presettings"]["licences_entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["licences_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["licences_should"] = "Soll";
$sLang["presettings"]["licences_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["licences_add"] = "hinzufügen";
$sLang["presettings"]["licences_Creating"] = "anlegen";
$sLang["presettings"]["licences_edit"] = "bearbeiten";
$sLang["presettings"]["licences_yes"] = "Ja";
$sLang["presettings"]["licences_no"] = "Nein";
$sLang["presettings"]["licences_save"] = "Speichern";
$sLang["presettings"]["licences_Available_records"] = "Verfügbare Datensätze:";
$sLang["presettings"]["licences_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
/*
modules/presetting
|_multilanguage.php
*/
$sLang["presettings"]["multilanguage_language"] = "Sprache";
$sLang["presettings"]["multilanguage_array"] = array(
		"id"=>"hide",
		"isocode"=>"Isocode (z.B. en)",
		"parentID"=>"ID der Stammkategorie",
		"flagstorefront"=>"hide",
		"flagbackend"=>"Grafik für Darstellung in Backend",
		"skipbackend"=>"Im Backend ausblenden?"
		);
$sLang["presettings"]["multilanguage_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["multilanguage_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["multilanguage_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["multilanguage_should"] = "Soll";
$sLang["presettings"]["multilanguage_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["multilanguage_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["presettings"]["multilanguage_this_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["presettings"]["multilanguage_With_the_module"] = "Mit dem Modul &quot;Mehrsprachfähigkeit&quot; können Sie Subshops in unterschiedlichen
Sprachen anbieten";
$sLang["presettings"]["multilanguage_informations_buy"] = "Informationen und Kauf";
$sLang["presettings"]["multilanguage_Creating"] = "anlegen";
$sLang["presettings"]["multilanguage_edit"] = "bearbeiten";
$sLang["presettings"]["multilanguage_save"] = "Speichern";
$sLang["presettings"]["multilanguage_created_languages"] = "Angelegte Sprachen:";
/*
modules/presetting
|_numbers.php
*/
$sLang["presettings"]["numbers_Number_Range"] = "Nummernkreis";
$sLang["presettings"]["numbers_array"] = array(
		"id"=>"hide",
		"name"=>"hide",
		"number"=>"Fortlaufende Nummer",
		"desc"=>"Name"
		);
$sLang["presettings"]["numbers_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["numbers_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["numbers_should"] = "Soll";
$sLang["presettings"]["numbers_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["numbers_create"] = "anlegen";
$sLang["presettings"]["numbers_edit"] = "bearbeiten";
$sLang["presettings"]["numbers_yes"] = "Ja";
$sLang["presettings"]["numbers_no"] = "Nein";
$sLang["presettings"]["numbers_save"] = "Speichern";
$sLang["presettings"]["numbers_Available_records"] = "Verfügbare Datensätze:";
$sLang["presettings"]["numbers_Available_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
/*
modules/presetting
|_orderstatemail.php
*/
$sLang["presettings"]["orderstatemail_fail"] = "FAIL";
$sLang["presettings"]["orderstatemail_Service_Administration"] = "Service Administration";
$sLang["presettings"]["orderstatemail_edit"] = "Bearbeiten";
$sLang["presettings"]["orderstatemail_Subject"] = "Betreff:";
$sLang["presettings"]["orderstatemail_sender"] = "Absender:";
$sLang["presettings"]["orderstatemail_Address"] = "Absenderadresse:";
$sLang["presettings"]["orderstatemail_email-text"] = "eMail-Text:";
$sLang["presettings"]["orderstatemail_activ"] = "Aktiv:";
$sLang["presettings"]["orderstatemail_no"] = "nein";
$sLang["presettings"]["orderstatemail_save"] = "Speichern";
$sLang["presettings"]["orderstatemail_paymentstatus"] = "Zahlstatus";
$sLang["presettings"]["orderstatemail_orderstatus"] = "Bestellstatus";
$sLang["presettings"]["orderstatemail_group"] = "Gruppe:";
/*
modules/presetting
|_pricegroup.php
*/
$sLang["presettings"]["pricegroup_pricegroup_deleted"] = "Preisgruppe wurde gelöscht";
$sLang["presettings"]["pricegroup_enter_a_name_for_the_customergroup"] = "Bitte geben Sie einen Namen für die Kundengruppe ein";
$sLang["presettings"]["pricegroup_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["pricegroup_pricegroup_not_found"] = "Preisgruppe konnte nicht gefunden werden";
$sLang["presettings"]["pricegroup_should_the_pricegroup"] = "Soll die Preisgruppe";
$sLang["presettings"]["pricegroup_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["pricegroup_new_pricegroup"] = "Neue Preisgruppe";
$sLang["presettings"]["pricegroup_edit_pricegroup"] = "Preisgruppe bearbeiten";
$sLang["presettings"]["pricegroup_array"] = array("id"=>"hide",
		"description"=>"Bezeichnung"
		);
$sLang["presettings"]["pricegroup_From_pieces"] = "Ab Stck.";
$sLang["presettings"]["pricegroup_save"] = "Speichern";
$sLang["presettings"]["pricegroup_created_pricegroups"] = "Angelegte Preisgruppen:";
$sLang["presettings"]["pricegroup_Deleting_a_price_group"] = "Das L&ouml;schen einer Preisgruppe, die bereits zugeordnete Artikel enth&auml;lt, ist nicht m&ouml;glich!";
/*
modules/presetting
|_settings2.php
*/
$sLang["presettings"]["pricegroup_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["presettings"]["pricegroup_settings_saved"] = "Einstellungen wurden gespeichert";
$sLang["presettings"]["pricegroup_more_informations"] = "Weitere Informationen zu United-Online-Services
	erhalten Sie über folgenden Link:";
$sLang["presettings"]["pricegroup_wiki_article"] = "Wiki-Artikel";
$sLang["presettings"]["pricegroup_unlock_access"] = "Jetzt Zugang freischalten";
$sLang["presettings"]["pricegroup_United_Transfer"] = "United Transfer - Ihr weltweites ePayment";
$sLang["presettings"]["pricegroup_United_Transfer_standart_or_direct"] = "United Transfer Standard oder Direkt - einfach individueller abrechnen.";
$sLang["presettings"]["pricegroup_select_the_standart_version"] = "Wählen Sie die Standard-Version und Sie steigern Ihr Käuferpotential um ein vielfaches durch den großen Kundenstamm registrierter User bei United Transfer.
	Entscheiden Sie sich für United Transfer Direkt und nutzen Sie so ein sicheres, schnelles Payment, bei dem eine Kundenanmeldung am System nicht notwendig ist.";
$sLang["presettings"]["pricegroup_The_range_of_services_covers"] = "Das Leistungsspektrum der Schnittstelle umfasst im Wesentlichen die Bereitstellung von weltweit akzeptierten Zahlungsmitteln (Kreditkarte, Lastschrift, giropay, Vorkasse) und im Hintergrund ablaufenden Scoring-Services (u. a. internationale Adressprüfung, Volljährigkeitskontrolle und Bonitätsabfragen).";
$sLang["presettings"]["pricegroup_Your_standard_benefits"] = "Ihre Standard-Vorteile";
$sLang["presettings"]["pricegroup_accepted_means_of_payment"] = "<li>4 (weltweit) akzeptierte Zahlungsmittel im eigenen Shop: Kreditkarte (VISA, MasterCard), Lastschrift (DE, AT), giropay und Vorkasse ohne weitere Verträge mit Dritten abschließen zu müssen </li>	
	<li>auch Neukunden können, nach ihrer Anmeldung am System, direkt Zahlungen durchführen </li>	
	<li>großer Bestandskunden-Pool steigert das Käuferpotential </li>	
	<li>Ihr Onlineshop wird auf dem United Transfer Kunden- und Händlerportal kostenlos in einem Katalog beworben</li>";
$sLang["presettings"]["pricegroup_Your_direct_benefits"] = "Ihre Direkt-Vorteile";
$sLang["presettings"]["pricegroup_all_available_means_of_payment"] = "<li>alle verfügbaren Zahlungsmittel (wie Standard) im eigenen Shop können individuell geschaltet werden (ein-/ ausblenden)</li>
	<li>Ihre Kunden können direkt Zahlungen durchführen, ohne Anmeldung bei United Transfer</li>";
$sLang["presettings"]["pricegroup_save"] = "Speichern";
/*
modules/presetting
|_snippets.php
*/
$sLang["presettings"]["snippets_Textbrick"] = "Textbaustein";
$sLang["presettings"]["snippets_array"] = array(
		"id"=>"hide",
		"group"=>"hide",
		"name"=>"Smarty - Name / Variable",
		"value"=>"Inhalt",
		"description"=>"hide"
		);
$sLang["presettings"]["snippets_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["snippets_entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["snippets_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["snippets_should"] = "Soll";
$sLang["presettings"]["snippets_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["snippets_create"] = "anlegen";
$sLang["presettings"]["snippets_edit"] = "bearbeiten";
$sLang["presettings"]["snippets_save"] = "Speichern";
$sLang["presettings"]["snippets_created_Textbricks"] = "Angelegte Textbausteine:";
/*
modules/presetting
|_tax.php
*/
$sLang["presettings"]["tax_tax_rate"] = "MwSt.Satz";
$sLang["presettings"]["tax_array"] = array(
		"id"=>"hide",
		"tax"=>"MwSt.Satz",
		"description"=>"Beschreibung:"
		);
$sLang["presettings"]["tax_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["tax_entry_was_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["tax_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["tax_should"] = "Soll";
$sLang["presettings"]["tax_really_delete"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["tax_create"] = "anlegen";
$sLang["presettings"]["tax_edit"] = "bearbeiten";
$sLang["presettings"]["tax_yes"] = "Ja";
$sLang["presettings"]["tax_no"] = "Nein";
$sLang["presettings"]["tax_save"] = "Speichern";
$sLang["presettings"]["tax_Available_records"] = "Verfügbare Datensätze:";
$sLang["presettings"]["tax_Available_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
/*
modules/presetting
|_trusted.php
*/
$sLang["presettings"]["trusted_please_enter_trusted-shop-id"] = "Bitte geben Sie eine gültige Trusted-Shops-ID ein";
$sLang["presettings"]["trusted_Your_store_with_Trusted_Shop_seal"] = "Ihr Shop mit Trusted Shops Gütesiegel";
$sLang["presettings"]["trusted_Trusted_Shop_is_the_hallmark_for_online-shops"] = "Trusted Shops ist das Gütesiegel für Online-Shops mit einer Geld-zurück-Garantie für Ihre Online-
Kunden. Bei einer Zertifizierung wird Ihr Shop umfassenden Sicherheits-Tests unterzogen. Diese
Prüfung mit mehr als 100 Einzel-Kriterien orientiert sich an den Forderungen der
Verbraucherschützer sowie dem nationalen und europäischen Recht.
Diese Shopsoftware erfüllt bereits einen Großteil
der Zertifizierungsanforderungen. Der Vorteil für
Sie: Sie können sich ohne großen Aufwand und zu
stark vergünstigten Konditionen zertifizieren
lassen!";
$sLang["presettings"]["trusted_Your_Benefits_from_trusted_shop"] = "Ihre Vorteile durch Trusted Shops:";
$sLang["presettings"]["trusted_Improve_your_shop_and_your_ordering_process"] = "1. Verbessern Sie Ihren Shop und Ihren Bestellprozess mit Erfahrungen aus über 5.000 Shop-Zertifizierungen<br />
2. Erhöhen Sie Ihre Umsätze durch eine bessere Konversionsrate. Steigern Sie das Vertrauen Ihrer Kunden mit Gütesiegel und Geld-zurück-Garantie<br />
3. Reduzieren Sie Ihr Abmahnungsrisiko und vermeiden Sie rechtliche Fehler durch Prävention mit bewährten Formulierungen und Abmahnungsradar<br />";
$sLang["presettings"]["trusted_What_does_your_Trusted_Shop"] = "Welche Leistungen bietet Ihnen Trusted Shops?";
$sLang["presettings"]["trusted_Certification_of_your_online_store"] = "<li>Zertifizierung Ihres Online-Shops mit individuellem Prüfungsprotokoll</li>
<li>E-Mail-Support durch Zertifizierungsabteilung während Prüfung</li>
<li>Trusted Shops Praxishandbuch mit 50 rechtssicheren Musterformulierungen</li>
<li>Geld-zurück-Garantie für Ihre Kunden</li>
<li>Mehrsprachiges Service-Center für Ihre Kunden</li>
<li> Streitschlichtung bei Problemfällen</li>
<li>Experten-Newsletter mit aktuellen Urteilen und Praxistipps</li>
<li>Mustershop und Expertenforen für rechtliche Fragen</li>
<li> Exklusive Preisvorteile (Payment, Hosting, Marketing etc.)</li>";
$sLang["presettings"]["trusted_The_Trusted_Shops_effect"] = "Der Trusted Shops Effekt";
$sLang["presettings"]["trusted_The_combination_of_audit"] = "Durch die Kombination von Prüfung, Geld-zurück-Garantie und Service entsteht für den
Verbraucher ein Rundum-sicher-Paket. Somit steigt die Kaufrate und Ihr Umsatz - ein Vorteil,
den sich bereits über 2.500 erfolgreiche Online-Shops zunutze machen. Damit ist Trusted Shops
laut Handelsblatt klarer Marktführer in Deutschland.
Weitere Informationen und Erfahrungen von zertifizierten Online-Shops finden Sie auf der Trusted
Shops Homepage unter www.trustedshops.de.
Nutzen Sie diese Chance und lassen Sie sich jetzt zum Sonderpreis zertifizieren.";
$sLang["presettings"]["trusted_more_informations"] = "Weitere Informationen";
$sLang["presettings"]["trusted_please_enter_yout_trusted-shops-id"] = "Bitte geben Sie hier Ihre Trusted-Shops-ID ein:";
$sLang["presettings"]["trusted_trusted-shops-id"] = "Trusted-Shops-ID:";
$sLang["presettings"]["trusted_save"] = "Speichern";
/*
modules/presetting
|_units.php
*/
$sLang["presettings"]["units_Price_unit"] = "Preiseinheit";
$sLang["presettings"]["units_array"] = array(
		"id"=>"hide",
		"unit"=>"Kurz-Bezeichnung",
		"description"=>"Lang-Bezeichnung:"
		);
$sLang["presettings"]["units_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["units_entry_deleted"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["units_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["units_should"] = "Soll";
$sLang["presettings"]["units_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["units_create"] = "anlegen";
$sLang["presettings"]["units_edit"] = "bearbeiten";
$sLang["presettings"]["units_save"] = "Speichern";
$sLang["presettings"]["units_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
/*
modules/presetting
|_viewport.php
*/
$sLang["presettings"]["viewport_Viewport"] = "Viewport";
$sLang["presettings"]["viewport_array"] = array(
		"id"=>"hide",
		"viewport"=>"Key für Aufruf",
		"viewport_file"=>"Viewport-Klasse",
		"description"=>"Bezeichnung"
		);
$sLang["presettings"]["viewport_was_deleted"] = "wurde gelöscht";
$sLang["presettings"]["viewport_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["presettings"]["viewport_cant_be_found"] = "konnte nicht gefunden werden";
$sLang["presettings"]["viewport_should"] = "Soll";
$sLang["presettings"]["viewport_really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["presettings"]["viewport_create"] = "anlegen";
$sLang["presettings"]["viewport_edit"] = "bearbeiten";
$sLang["presettings"]["viewport_save"] = "Speichern";
$sLang["presettings"]["viewport_Available_records"] = "Verfügbare Datensätze:";
$sLang["presettings"]["viewport_Please_change_this_data_only"] = "Bitte verändern Sie diese Daten nur, wenn Sie genau wissen was Sie tun!";
/*
modules/orders
|_skeleton.php
*/
$sLang["orders"]["skeleton_error_order_not_found"] = "Fehler: Bestellung nicht gefunden";
$sLang["orders"]["skeleton_order"] = "Bestellung";
/*
modules/orders
|_main.php
*/
$sLang["orders"]["please_remember"] = "Bitte beachten Sie";
$sLang["orders"]["please_remember_txt"] = "Nachträglich hinzugefügte Bestellpositionen haben keinen Einfluss auf den Shop-Lagerbestand";
$sLang["orders"]["main_no_order_given"] = "No order given";
$sLang["orders"]["main_order_not"] = "Order";
$sLang["orders"]["main_order_not_found"] = "not found";
$sLang["orders"]["main_Attention_assigned_user_was_deleted"] = "Achtung, zugeordneter Benutzer wurde gelöscht";
$sLang["orders"]["main_changes_saved"] = "Änderungen gespeichert";
$sLang["orders"]["main_changes_save_failed"] = "Änderungen konnten nicht gespeichert werden";
$sLang["orders"]["main_search"] = "Suche";
$sLang["orders"]["main_General_data"] = "Allgemeine Daten";
$sLang["orders"]["main_Billing_address"] = "Rechnungsadresse:";
$sLang["orders"]["main_Delivery_address"] = "Lieferadresse:";
$sLang["orders"]["main_payment"] = "Zahlungsart:";
$sLang["orders"]["main_payment_not_found"] = "Zahlungsart nicht gefunden";
$sLang["orders"]["main_orderdetails"] = "Bestelldetails:";
$sLang["orders"]["main_time"] = "Zeitpunkt:";
$sLang["orders"]["main_ordernumber"] = "Bestellnummer:";
$sLang["orders"]["main_Currency"] = "Währung:";
$sLang["orders"]["main_Total"] = "Gesamtsumme:";
$sLang["orders"]["main_Elected_Dispatch"] = "Gewählte Versandart:";
$sLang["orders"]["main_Dispatch_not_saved"] = "Versandart nicht gespeichert";
$sLang["orders"]["main_Paid_on"] = "Bezahlt am:";
$sLang["orders"]["main_Tracking-Code"] = "Tracking-Code:";
$sLang["orders"]["main_forwarding_charges"] = "Versandkosten:";
$sLang["orders"]["main_order_status"] = "Bestellstatus:";
$sLang["orders"]["main_payment_status"] = "Zahlstatus:";
$sLang["orders"]["main_your_comment"] = "Ihr Kommentar";
$sLang["orders"]["main_customer_comment"] = "Kundenkommentar";
$sLang["orders"]["main_save"] = "Speichern";
$sLang["orders"]["main_articlenumber"] = "Art-Nr.";
$sLang["orders"]["main_description"] = "Bezeichnung";
$sLang["orders"]["main_quantity"] = "Anzahl";
$sLang["orders"]["main_price"] = "Preis";
$sLang["orders"]["main_whole"] = "Gesamt";
$sLang["orders"]["main_status"] = "Status";
$sLang["orders"]["main_settings_saved"] = "Einstellungen wurden gespeichert!";
$sLang["orders"]["main_Document_Handling_locked_in_demo"] = "Beleghandling in Demo gesperrt";
$sLang["orders"]["main_date"] = "datum";
$sLang["orders"]["main_document"] = "beleg";
$sLang["orders"]["main_amount"] = "amount";
$sLang["orders"]["main_Order_positions"] = "Bestellpositionen";
$sLang["orders"]["main_edit_Order_positions"] = "Bestellpositionen bearbeiten:";
$sLang["orders"]["main_save"] = "Speichern";
$sLang["orders"]["main_date_1"] = "Datum";
$sLang["orders"]["main_document_1"] = "Beleg";
$sLang["orders"]["main_Amount"] = "Betrag";
$sLang["orders"]["main_document_not_exists"] = "kein Beleg vorhanden";
$sLang["orders"]["main_Document_is_created"] = "Beleg wird erstellt";
$sLang["orders"]["main_documents"] = "Belege";
$sLang["orders"]["main_Existing_documents"] = "Vorhandene Belege";
$sLang["orders"]["main_create_documents"] = "Belege erstellen:";
$sLang["orders"]["main_Selected_Date"] = "Angezeigtes Datum:";
$sLang["orders"]["main_invoice_number"] = "Rechnungsnummer:";
$sLang["orders"]["main_date_of_delivery"] = "Liefertermin:";
$sLang["orders"]["main_choice"] = "Auswahl:";
$sLang["orders"]["main_invoice"] = "Rechnung";
$sLang["orders"]["main_bill_of_delivery"] = "Lieferschein";
$sLang["orders"]["main_credit"] = "Gutschrift";
$sLang["orders"]["main_reversal"] = "Stornierung";
$sLang["orders"]["main_voucher"] = "Gutschein:";
$sLang["orders"]["main_no_voucher"] = "Kein Gutschein";
$sLang["orders"]["main_Customer_VAT_number"] = "Kunden-USt-IdNr.:";
$sLang["orders"]["main_Sales_Tax_Exempt"] = "Umsatzsteuerbefreit:";
$sLang["orders"]["main_create_document"] = "Beleg erstellen";
$sLang["orders"]["main_reset"] = "Reset";
$sLang["orders"]["main_add_Article_subsequently"] = "Artikel nachtr&auml;glich hinzufügen";
$sLang["orders"]["main_preview"] = "Vorschau";
/*
modules/orders
|_details.php
*/
$sLang["orders"]["details_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["orders"]["details_number"] = "Nummer";
$sLang["orders"]["details_Manufacturer"] = "Hersteller";
$sLang["orders"]["details_article"] = "Artikel";
$sLang["orders"]["details_price"] = "Preis";
$sLang["orders"]["details_active"] = "Aktiv";
$sLang["orders"]["details_date"] = "Datum";
$sLang["orders"]["details_ordernumber"] = "Bestellnummer";
$sLang["orders"]["details_Contract_value"] = "Auftragswert";
$sLang["orders"]["details_status"] = "Status";
$sLang["orders"]["details_no_data"] = "Keine Daten";
$sLang["orders"]["details_in_process"] = "In Bearbeitung";
$sLang["orders"]["details_id"] = "id";
$sLang["orders"]["details_ID_1"] = "ID";
$sLang["orders"]["details_Article_1"] = "Article";
$sLang["orders"]["details_Publisher"] = "Publisher";
$sLang["orders"]["details_Merriam"] = "Merriam";
/*
modules/orders
|_documents.php
*/
$sLang["orders"]["documents_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["orders"]["documents_date"] = "Datum";
$sLang["orders"]["documents_document"] = "Beleg";
$sLang["orders"]["documents_value"] = "Betrag";
$sLang["orders"]["documents_article_number"] = "Art-Nr.";
$sLang["orders"]["documents_title"] = "Bezeichnung";
$sLang["orders"]["documents_quantity"] = "Anzahl";
$sLang["orders"]["documents_price"] = "Preis";
$sLang["orders"]["documents_total"] = "Gesamt";
$sLang["orders"]["documents_creating_PDF"] = "PDF wird erstellt!";
$sLang["orders"]["documents_settings_reset"] = "Einstellungen wurden resetet!";
$sLang["orders"]["documents_date"] = "datum";
$sLang["orders"]["documents_document"] = "beleg";
$sLang["orders"]["documents_no_document_available"] = "kein Beleg vorhanden";
$sLang["orders"]["documents_amount"] = "amount";
$sLang["orders"]["documents_Document_Handling_locked_in_demo"] = "Beleghandling in Demo gesperrt";
$sLang["orders"]["documents_creating_document"] = "Beleg wird erstellt";
$sLang["orders"]["documents_Existing_document"] = "Vorhandene Belege";
$sLang["orders"]["documents_creating_documents"] = "Belege erstellen:";
$sLang["orders"]["documents_Selected_Date"] = "Angezeigter Datum:";
$sLang["orders"]["documents_invoice_number"] = "Rechnungsnummer:";
$sLang["orders"]["documents_day_of_delivery"] = "Liefertermin:";
$sLang["orders"]["documents_choice"] = "Auswahl:";
$sLang["orders"]["documents_invoice"] = "Rechnung";
$sLang["orders"]["documents_delivery_order"] = "Lieferschein";
$sLang["orders"]["documents_credit"] = "Gutschrift";
$sLang["orders"]["documents_create_document"] = "Beleg erstellen";
$sLang["orders"]["documents_reset"] = "Reset";
$sLang["orders"]["documents_add_Article_subsequently"] = "Artikel nachtr&auml;glich hinzufügen";
$sLang["orders"]["documents_Advanced_Settings"] = "Erweiterte Einstellungen:";
/*
modules/orders
|_orders.php
*/
$sLang["orders"]["orders_date"] = "Datum";
$sLang["orders"]["orders_document"] = "Beleg";
$sLang["orders"]["orders_amount"] = "Betrag";
$sLang["orders"]["orders_Document_Handling_locked_in_demo"] = "Beleghandling in Demo gesperrt";
$sLang["orders"]["orders_date_1"] = "datum";
$sLang["orders"]["orders_invoice"] = "Rechnung #0000000";
$sLang["orders"]["orders_delivery_order"] = "Lieferschein #0000000";
$sLang["orders"]["orders_Part_credit"] = "Teilgutschrift #0000000";
$sLang["orders"]["orders_order_number"] = "Bestellnr.";
$sLang["orders"]["orders_Order_Status"] = "Bestellstatus";
$sLang["orders"]["orders_Order_Value"] = "Bestellwert";
$sLang["orders"]["orders_Positions"] = "Positionen";
$sLang["orders"]["orders_settings"] = "Optionen";
$sLang["orders"]["orders_article"] = "Artikel";
$sLang["orders"]["orders_open"] = "Offen";
$sLang["orders"]["referer"] = "Herkunft";
/*
modules/auth
|_skeleton.php
*/
$sLang["auth"]["skeleton_User_Management"] = "Benutzerverwaltung";
/*
modules/auth
|_auth.php
*/
$sLang["auth"]["auth_overview"] = "&Uuml;bersicht";
$sLang["auth"]["auth_user"] = "Benutzer";
$sLang["auth"]["auth_new_user"] = "Neuer Benutzer";
$sLang["auth"]["auth_del_user"] = "Benutzer Löschen";
$sLang["auth"]["auth_details"] = "Details";
$sLang["auth"]["auth_validation"] = "Bestätigung";
$sLang["auth"]["auth_really_delete_marked_user"] = "Sollen der markierte Benutzer wirklich gelöscht werden?";
$sLang["auth"]["auth_saving_changes"] = "Saving changes...";
$sLang["auth"]["auth_warning"] = "Warning";
$sLang["auth"]["auth_oops"] = "Oops...";
$sLang["auth"]["auth_user"] = "Benutzer";
$sLang["auth"]["auth_deleted"] = "wurde gelöscht";
/*
modules/auth
|_auth_new.php
*/
$sLang["auth"]["auth_new_site_deleted"] = "Seite wurde gelöscht";
$sLang["auth"]["auth_new_Please_fill_in_all_fields"] = "Bitte füllen Sie alle Felder aus";
$sLang["auth"]["auth_new_user_saved"] = "Benutzer wurde gespeichert";
$sLang["auth"]["auth_new_user_created"] = "Benutzer wurde angelegt";
$sLang["auth"]["auth_new_user_not_found"] = "Benutzer konnte nicht gefunden werden";
$sLang["auth"]["auth_new_should_the_site"] = "Soll die Seite";
$sLang["auth"]["auth_new_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["auth"]["auth_new_user"] = "Benutzer";
$sLang["auth"]["auth_new_edit_last_online"] = "bearbeiten (Zuletzt online:";
$sLang["auth"]["auth_new_create_user"] = "Benutzer anlegen";
$sLang["auth"]["auth_new_active_1"] = "Aktiv";
$sLang["auth"]["auth_new_username"] = "Benutzername";
$sLang["auth"]["auth_new_password"] = "Passwort";
$sLang["auth"]["auth_new_name"] = "Name";
$sLang["auth"]["auth_new_email"] = "eMail";

$sLang["auth"]["auth_new_dont_show"] = "nicht zeigen";
$sLang["auth"]["auth_new_show"] = "zeigen";
$sLang["auth"]["auth_new_save"] = "Speichern";
$sLang["auth"]["auth_new_Module_access_rights"] = "Modul-Zugriffsrechte";
/*
modules/authlog
|_skeleton.php
*/
$sLang["authlog"]["skeleton_user_log"] = "Logfile";
/*
modules/authlog
|_transaction.php
*/
$sLang["authlog"]["transaction_user_list"] = "User-List";
$sLang["authlog"]["transaction_Confirmation"] = "Bestätigung";
$sLang["authlog"]["transaction_log_entries_really_deleted"] = "Sollen die markierten Log-Einträge wirklich gelöscht werden?";
$sLang["authlog"]["transaction_date"] = "Datum";
$sLang["authlog"]["transaction_user"] = "Benutzer";
$sLang["authlog"]["transaction_modul"] = "Modul";
$sLang["authlog"]["transaction_entry"] = "Eintrag";
$sLang["authlog"]["transaction_options"] = "Optionen";
$sLang["authlog"]["transaction_log_file_entry"] = "Log-File Einträge";
$sLang["authlog"]["transaction_log_entry"] = "Log-Einträge:";
$sLang["authlog"]["transaction_total"] = "Gesamt:";
$sLang["authlog"]["transaction_No_log_entrie_sin_View"] = "Keine Log-Einträge in Ansicht";
$sLang["authlog"]["transaction_mark_all"] = "Alle markieren";
$sLang["authlog"]["transaction_delete_marked_post"] = "Markierte Einträge löschen";
/*
modules/categories
|_skeleton.php
*/
$sLang["categories"]["skeleton_Category_management"] = "Kategorien";
/*
modules/categories
|_categories.php
*/
$sLang["categories"]["categories_shopware"] = "Shopware";
$sLang["categories"]["categories_settings"] = "Einstellungen";
$sLang["categories"]["categories_a_name_is_reqiored"] = "A name is required";
$sLang["categories"]["categories_connection_to_server_failed"] = "Verbindung zum Server nicht möglich";
$sLang["categories"]["categories_Category_has_been_renamed"] = "Kategorie wurde umbenannt";
$sLang["categories"]["categories_Category_has_been_moved"] = "Kategorie wurde verschoben";
$sLang["categories"]["categories_Categories"] = "Kategorien";
/*
modules/categories
|_categoryedit.php
*/
$sLang["categories"]["categoryedit_Category"] = "Kategorie";
$sLang["categories"]["categoryedit_has_been_created"] = "wurde angelegt";
$sLang["categories"]["categoryedit_category_cant_be_created"] = "Kategorie konnte nicht angelegt werden";
$sLang["categories"]["categoryedit_has_been_updated"] = "wurde aktualisiert";
$sLang["categories"]["categoryedit_Category_could_not_be_updated"] = "Kategorie konnte nicht aktualisiert werden";
$sLang["categories"]["categoryedit_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["categories"]["categoryedit_Should_the_category"] = "Soll die Kategorie";
$sLang["categories"]["categoryedit_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["categories"]["categoryedit_Categories_rename_move"] = "Kategorien umbenennen / verschieben";
$sLang["categories"]["categoryedit_To_rename_a_category"] = "Um eine Kategorie umzubenennen, klicken Sie doppelt auf die Kategorie und geben
Sie die neue Bezeichnung ein.";
$sLang["categories"]["categoryedit_To_add_a_category_to_move"] = "Um eine Kategorie zu verschieben, klicken sie die Kategorie an, halten Sie
die Maustaste gedrückt und ziehen Sie die Kategorie auf die gewünschte Position.";
$sLang["categories"]["categoryedit_Important_Note"] = "Wichtiger Hinweis:";
$sLang["categories"]["categoryedit_This_category_is_a_Systemcategory"] = "Diese Kategorie ist eine Systemkategorie. Um diese zu löschen, entfernen Sie zuerst alle
Multilanguage und Multishop-Zuordnungen in den Einstellungen";
$sLang["categories"]["categoryedit_Assigned_Article"] = "Zugeordnete Artikel:";
$sLang["categories"]["categoryedit_New_subcategory"] = "Neue Unterkategorie:";
$sLang["categories"]["categoryedit_After_creating_the_category"] = "Nach dem Anlegen der Kategorie können Sie weitere Einstellungen (z.B. Meta-Tags) vornehmen!";
$sLang["categories"]["categoryedit_creating"] = "Anlegen";
$sLang["categories"]["categoryedit_delete"] = "Löschen";
$sLang["categories"]["categoryedit_more_options"] = "Weitere Optionen:";
$sLang["categories"]["categoryedit_Meta-Keywords"] = "Meta-Keywords:";
$sLang["categories"]["categoryedit_Meta-Description"] = "Meta-Description:";
$sLang["categories"]["categoryedit_title"] = "Überschrift:";
$sLang["categories"]["categoryedit_Description"] = "Beschreibung:";
$sLang["categories"]["categoryedit_save"] = "Speichern";
$sLang["categories"]["categoryedit_create_new_category"] = "Neue Kategorie anlegen";
$sLang["categories"]["categoryedit_new_maincategory"] = "Neue Hauptkategorie:";
/*
modules/help_admin
|_skeleton.php
*/
$sLang["help_admin"]["skeleton_Shopware_onlinehelp"] = "Shopware Onlinehilfe";
/*
modules/help_admin
|_index.php
*/
$sLang["help_admin"]["index_Help_Administration"] = "Help Administration";
/*
modules/help_admin
|_categories.php
*/
$sLang["help_admin"]["categories_Double-click_Move"] = "Umbenennen: Doppelklick Verschieben:<br /> Drag & Drop";
/*
modules/help_admin
|_categoryedit.php
*/
$sLang["help_admin"]["categoryedit_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["help_admin"]["categoryedit_Content"] = "Inhalt:";
$sLang["help_admin"]["categoryedit_print"] = "Drucken";
$sLang["help_admin"]["categoryedit_Outlook_issue_invite"] = "Voreingestelltes Thema laden";
/*
modules/help_admin
|_detailsArt.php
*/
$sLang["help_admin"]["detailsArt_Help_Administration"] = "Help Administration";
$sLang["help_admin"]["detailsArt_create_article"] = "Artikel anlegen";
$sLang["help_admin"]["detailsArt_name"] = "Name:";
$sLang["help_admin"]["detailsArt_Description"] = "Beschreibung:";
$sLang["help_admin"]["detailsArt_activ"] = "Aktiv:";
$sLang["help_admin"]["detailsArt_dont_Show"] = "nicht zeigen";
$sLang["help_admin"]["detailsArt_Show"] = "zeigen";
$sLang["help_admin"]["detailsArt_save"] = "speichern";
$sLang["help_admin"]["detailsArt_create_tooltip"] = "Tooltip anlegen";
$sLang["help_admin"]["detailsArt_titel"] = "Titel:";
$sLang["help_admin"]["detailsArt_text"] = "Text:";
$sLang["help_admin"]["detailsArt_create"] = "anlegen";
$sLang["help_admin"]["detailsArt_delete_this_article"] = "Diesen Artikel löschen";
$sLang["help_admin"]["detailsArt_delete"] = "löschen";
/*
modules/help_admin
|_detailsCat.php
*/
$sLang["help_admin"]["detailsCat_Help_Administration"] = "Help Administration";
$sLang["help_admin"]["detailsCat_Edit_Category"] = "Kategorie bearbeiten";
$sLang["help_admin"]["detailsCat_name"] = "Name:";
$sLang["help_admin"]["detailsCat_Description"] = "Beschreibung:";
$sLang["help_admin"]["detailsCat_save"] = "Speichern";
$sLang["help_admin"]["detailsCat_delete_this_category"] = "Diese Kategorie löschen";
$sLang["help_admin"]["detailsCat_delete"] = "löschen";
$sLang["help_admin"]["detailsCat_create_article"] = "Artikel anlegen";
$sLang["help_admin"]["detailsCat_create"] = "anlegen";
$sLang["help_admin"]["detailsCat_create_subcategory"] = "Unterkategorie anlegen";
/*
modules/home
|_skeleton.php
*/
$sLang["home"]["skeleton_External_resource"] = "Externe Ressource";
/*
modules/mails
|_textvorlagen.php
*/
$sLang["mails"]["textvorlagen_site_deleted"] = "Seite wurde gelöscht";
$sLang["mails"]["textvorlagen_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["mails"]["textvorlagen_mail_not_found"] = "Mail konnte nicht gefunden werden";
$sLang["mails"]["textvorlagen_sould_the_site"] = "Soll die Seite";
$sLang["mails"]["textvorlagen_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["mails"]["textvorlagen_array"] = array("id"=>"hide",
		"name"=>"hide",
		"ishtml"=>"hide",
		"frommail"=>"Absender eMail",
		"fromname"=>"Absender Name",
		"subject"=>"Betreff",
		"content"=>"Text"
		);
$sLang["mails"]["textvorlagen_save"] = "Speichern";
$sLang["mails"]["textvorlagen_overview_Text_Templates"] = "&Uuml;bersicht Textvorlagen:";
/*
modules/mails
|_skeleton.php
*/
$sLang["mails"]["skeleton_Text_Templates"] = "eMail-Vorlagen";
/*
modules/overview
|_skeleton.php
*/
$sLang["overview"]["skeleton_Evaluation_Overview"] = "Auswertung - &Uuml;bersicht";
/*
modules/overview
|_overview.php
*/
$sLang["overview"]["overview_Evaluation_Overview"] = "Auswertung-&Uuml;bersicht";
$sLang["overview"]["overview_Evaluation_of"] = "Auswertung von:";
$sLang["overview"]["overview_Evaluation_until"] = "Auswertung bis:";
$sLang["overview"]["overview_date"] = "Datum";
$sLang["overview"]["overview_turnover"] = "Umsatz";
$sLang["overview"]["overview_orders"] = "Bestellungen";
$sLang["overview"]["overview_Order_Value"] = "Bestellwert";
$sLang["overview"]["overview_user_order"] = "User/Order";
$sLang["overview"]["overview_New_Customers"] = "Neukunden";
$sLang["overview"]["overview_Visitor"] = "Besucher";
$sLang["overview"]["overview_Page_Views"] = "Seitenzugriffe";
$sLang["overview"]["overview_within_the_period"] = "innerhalb des Zeitraums:";
$sLang["overview"]["overview_Total_sales"] = "Gesamtumsatz:";
$sLang["overview"]["overview_new_customers"] = "Neukunden:";
$sLang["overview"]["overview_orders"] = "Bestellungen:";
$sLang["overview"]["overview_Visitors"] = "Besucher:";
$sLang["overview"]["overview_site_views"] = "Seitenaufrufe:";
/*
modules/payment
|_skeleton.php
*/
$sLang["payment"]["skeleton_Payment_Methods"] = "Zahlungsarten";
/*
modules/payment
|_payment.php
*/
$sLang["payment"]["payment_site_deleted"] = "Seite wurde gelöscht";
$sLang["payment"]["payment_entry_saved"] = "Eintrag wurde gespeichert";
$sLang["payment"]["payment_mail_not_found"] = "Mail konnte nicht gefunden werden";
$sLang["payment"]["payment_should_the_site"] = "Soll die Seite";
$sLang["payment"]["payment_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["payment"]["payment_Overview_Payment_methods"] = "Übersicht Zahlarten";
$sLang["payment"]["payment_You_can_check_availability"] = "Sie können die Verfügbarkeit einzelner Zahlungsarten über
das Shopware Riskmanagement einschränken";
$sLang["payment"]["payment_Payment_methods"] = "Zahlungsarten";
$sLang["payment"]["payment_array"] = array("id"=>"hide",
		"name"=>"hide",
		"hide"=>"hide",
		"debit_percent"=>"Aufschlag/Abschlag % (für Abschlag - x)",
		"debit_fix"=>"hide",
		"description"=>"Bezeichnung",
		"template"=>"Template",
		"class"=>"Systemklasse",
		"table"=>"Datenbank-Tabelle",
		"additionaldescription"=>"Zusatzbeschreibung",
		"active"=>"Aktiv",
		"esdactive"=>"Gültig für ESD-Produkte",
		"embediframe"=>"URL für iFrame",
		"surcharge"=>"Pauschaler Aufschlag",
		"surchargestring"=>"Länderspezifischer Aufschlag",
		"hideprospect"=>"hide"
		);
$sLang["payment"]["payment_Please_use_the_following_format"] = "Bitte folgendes Format verwenden: DE:5.00;AT:10.00;GB:10.00";
$sLang["payment"]["payment_The_ISO_country_codes"] = "Die ISO-Codes der Länder können Sie unter Einstellungen > Länderauswahl einsehen.";
$sLang["payment"]["payment_save"] = "Speichern";
$sLang["payment"]["payment_Overview_Payment"] = "&Uuml;bersicht Zahlungsarten:";
/*
modules/risk
|_skeleton.php
*/
$sLang["risk"]["skeleton_risk_management"] = "Riskmanagement";
/*
modules/risk
|_cms.php
*/
$sLang["risk"]["cms_should_the_article"] = "Soll das Artikel";
$sLang["risk"]["cms_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["risk"]["cms_NOT_LICENSED"] = "NICHT LIZENZIERT";
$sLang["risk"]["cms_This_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["risk"]["cms_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können Sie maximal 1 Regel je Zahlart definieren";
$sLang["risk"]["cms_more_informations"] = "Weitere Informationen:";
$sLang["risk"]["cms_Module_Features"] = "Modul-Vorstellung";
$sLang["risk"]["cms_rent_buy_module"] = "Modul mieten/kaufen";
$sLang["risk"]["cms_Risk-Management"] = "Risk-Management";
$sLang["risk"]["cms_Here_you_can_define_rules"] = "Hier können Sie Regeln definieren, die Zahlungsarten mit
einem hohen Ausfallrisiko (z.B. Rechnung / Lastschrift)
nur unter bestimmten Voraussetzungen erlauben";
$sLang["risk"]["cms_select_payment"] = "Auswahl der Zahlungsart";
$sLang["risk"]["cms_select"] = "Auswählen";
$sLang["risk"]["cms_block_IF"] = "sperren WENN";

$sLang["risk"]["cms_please_select"] = "Bitte wählen...";
$sLang["risk"]["cms_AND"] = "UND";
$sLang["risk"]["cms_OR"] = "ODER";
$sLang["risk"]["cms_save"] = "Speichern";
/*
modules/rss
|_skeleton.php
*/
$sLang["rss"]["skeleton_rss-reader"] = "Shopware RSS-Reader";
/*
modules/salescampaigns
|_skeleton.php
*/
$sLang["salescampaigns"]["skeleton_Actions"] = "Aktionen";
/*
modules/salescampaigns
|_campaigns.php
*/
$sLang["salescampaigns"]["campaigns_startsite"] = "Startseite";
$sLang["salescampaigns"]["campaigns_Categories"] = "Kategorien";
$sLang["salescampaigns"]["campaigns_Actions"] = "Aktionen";
$sLang["salescampaigns"]["campaigns_serverconnection_failed"] = "Verbindung zum Server nicht möglich";
$sLang["salescampaigns"]["campaigns_Element_has_been_postponed"] = "Element wurde verschoben";
$sLang["salescampaigns"]["campaigns_settings"] = "Einstellungen";
$sLang["salescampaigns"]["campaigns_to_actionoverview"] = "zur Aktionsübersicht";
/*
modules/salescampaigns
|_campaigns2.php
*/
$sLang["salescampaigns"]["campaigns2_Reorder_TreePanel"] = "Reorder TreePanel";
$sLang["salescampaigns"]["campaigns2_move"] = "Verschieben:";
$sLang["salescampaigns"]["campaigns2_drag_n_drop"] = "Drag & Drop";
/*
modules/salescampaigns
|_campaignsedit.php
*/
$sLang["salescampaigns"]["campaignsedit_category_not_found"] = "Kategorie nicht gefunden";
$sLang["salescampaigns"]["campaignsedit_startsite"] = "Startseite";
$sLang["salescampaigns"]["campaignsedit_container_delete_failed"] = "Löschen der Container fehlgeschlagen";
$sLang["salescampaigns"]["campaignsedit_action_delete_failed"] = "Löschen der Aktion fehlgeschlagen";
$sLang["salescampaigns"]["campaignsedit_action_deleted"] = "Aktion wurde gelöscht";
$sLang["salescampaigns"]["campaignsedit_wrong_file_format"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["salescampaigns"]["campaignsedit_upload_failed"] = "Fehler bei Upload";
$sLang["salescampaigns"]["campaignsedit_please_enter_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["salescampaigns"]["campaignsedit_cant_save_action"] = "Aktion konnte nicht gespeichert werden";
$sLang["salescampaigns"]["campaignsedit_action_updated"] = "Aktion wurde aktualisiert";
$sLang["salescampaigns"]["campaignsedit_action_created"] = "Aktion wurde angelegt";
$sLang["salescampaigns"]["campaignsedit_action_not_found"] = "Aktion nicht gefunden";
$sLang["salescampaigns"]["campaignsedit_should_be_deleted"] = "wirklich gel&ouml;scht werden";
$sLang["salescampaigns"]["campaignsedit_not_licensed"] = "NICHT LIZENZIERT";
$sLang["salescampaigns"]["campaignsedit_this_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["salescampaigns"]["campaignsedit_In_the_unlicensed_version"] = "In der NICHT lizenzierten Version können Sie maximal 1 Startseiten - Aktion einstellen";
$sLang["salescampaigns"]["campaignsedit_more_informations"] = "Weitere Informationen:";
$sLang["salescampaigns"]["campaignsedit_Module_Features"] = "Modul-Vorstellung";
$sLang["salescampaigns"]["campaignsedit_Module_help"] = "Module Hilfe";
$sLang["salescampaigns"]["campaignsedit_action"] = "Aktion";
$sLang["salescampaigns"]["campaignsedit_edit"] = "bearbeiten";
$sLang["salescampaigns"]["campaignsedit_New_action_in"] = "Neue Aktion in";
$sLang["salescampaigns"]["campaignsedit_create"] = "anlegen";
$sLang["salescampaigns"]["campaignsedit_In_the_unlicensed_version"] = "In der nicht lizenzierten Version können keine neuen Aktionen angelegt werden";
$sLang["salescampaigns"]["campaignsedit_Name_of_the_Action"] = "Name der Aktion:";
$sLang["salescampaigns"]["campaignsedit_Link_to_the_Action"] = "Link zur Aktion:";
$sLang["salescampaigns"]["campaignsedit_open"] = "Öffnen";
$sLang["salescampaigns"]["campaignsedit_position"] = "Position:";
$sLang["salescampaigns"]["campaignsedit_valid_from"] = "Gültig von:";
$sLang["salescampaigns"]["campaignsedit_valid_until"] = "Gültig bis:";
$sLang["salescampaigns"]["campaignsedit_picture"] = "Bild:";
$sLang["salescampaigns"]["campaignsedit_directlink"] = "Direktlink:";
$sLang["salescampaigns"]["campaignsedit_Disabled_container"] = "(Deaktiviert Container)";
$sLang["salescampaigns"]["campaignsedit_link_target"] = "Link-Ziel:";
$sLang["salescampaigns"]["campaignsedit_shopware"] = "Shopware";
$sLang["salescampaigns"]["campaignsedit_extern"] = "Extern";
$sLang["salescampaigns"]["campaignsedit_activ"] = "Aktiv:";
$sLang["salescampaigns"]["campaignsedit_yes"] = "Ja";
$sLang["salescampaigns"]["campaignsedit_no"] = "Nein";
$sLang["salescampaigns"]["campaignsedit_save"] = "Speichern";
$sLang["salescampaigns"]["campaignsedit_delete_action"] = "Aktion löschen";
$sLang["salescampaigns"]["campaignsedit_add_container"] = "Container hinzufügen";
$sLang["salescampaigns"]["campaignsedit_new_container"] = "Neuer Container:";
$sLang["salescampaigns"]["campaignsedit_please_select"] = "Bitte wählen";
$sLang["salescampaigns"]["campaignsedit_banner"] = "Banner";
$sLang["salescampaigns"]["campaignsedit_html_text"] = "HTML-Text";
$sLang["salescampaigns"]["campaignsedit_articlegroup"] = "Artikel-Gruppe";
$sLang["salescampaigns"]["campaignsedit_linkgroup"] = "Link-Gruppe";
$sLang["salescampaigns"]["campaignsedit_Insert_container"] = "Container einfügen";
/*
modules/salescampaigns
|_linkdetails.php
*/
$sLang["salescampaigns"]["linkdetails_link_deleted"] = "Link wurde gelöscht";
$sLang["salescampaigns"]["linkdetails_link_not_found"] = "Link nicht gefunden";
$sLang["salescampaigns"]["linkdetails_enter_linktitle"] = "Bitte geben Sie eine Bezeichnung für den Link ein";
$sLang["salescampaigns"]["linkdetails_please_enter_URL"] = "Bitte geben Sie eine URL für den Link ein";
$sLang["salescampaigns"]["linkdetails_link_cant_be_saved"] = "Link konnte nicht gespeichert werden";
$sLang["salescampaigns"]["linkdetails_link_updated"] = "Link wurde aktualisiert";
$sLang["salescampaigns"]["linkdetails_link_created"] = "Link wurde angelegt";
$sLang["salescampaigns"]["linkdetails_link_should_this_link_really_be_deleted"] = "Soll dieser Link wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["linkdetails_edit_link"] = "Link bearbeiten";
$sLang["salescampaigns"]["linkdetails_linktitle"] = "Link-Bezeichnung:";
$sLang["salescampaigns"]["linkdetails_direktlink"] = "Direktlink:";
$sLang["salescampaigns"]["linkdetails_linktarget"] = "Link-Ziel:";
$sLang["salescampaigns"]["linkdetails_shopware"] = "Shopware";
$sLang["salescampaigns"]["linkdetails_extern"] = "Extern";
$sLang["salescampaigns"]["linkdetails_save_link"] = "Link speichern";
/*
modules/salescampaigns
|_linkedit.php
*/
$sLang["salescampaigns"]["linkedit_link_deleted"] = "Linkgruppe wurde gelöscht";
$sLang["salescampaigns"]["linkedit_linkgroup_not_found"] = "Linkgruppe nicht gefunden";
$sLang["salescampaigns"]["linkedit_please_enter_a_linktitle"] = "Bitte geben Sie eine Bezeichnung für den Link ein";
$sLang["salescampaigns"]["linkedit_please_enter_an_url"] = "Bitte geben Sie eine URL für den Link ein";
$sLang["salescampaigns"]["linkedit_link_cant_be_saved"] = "Link konnte nicht gespeichert werden";
$sLang["salescampaigns"]["linkedit_link_updated"] = "Link wurde aktualisiert";
$sLang["salescampaigns"]["linkedit_link_created"] = "Link wurde angelegt";
$sLang["salescampaigns"]["linkedit_should_this_linkgroup_really_be_deleted"] = "Soll diese Linkgruppe wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["linkedit_linkgroup_settings"] = "Linkgruppe Eigenschaften";
$sLang["salescampaigns"]["linkedit_overview"] = "Überschrift:";
$sLang["salescampaigns"]["linkedit_save"] = "Speichern";
$sLang["salescampaigns"]["linkedit_delete_linkgroup"] = "Linkgruppe löschen";
$sLang["salescampaigns"]["linkedit_add_link_to_group"] = "Link in Gruppe einfügen";
$sLang["salescampaigns"]["linkedit_linktitle"] = "Link-Bezeichnung:";
$sLang["salescampaigns"]["linkedit_directlink"] = "Direktlink:";
$sLang["salescampaigns"]["linkedit_link_target"] = "Link-Ziel:";
$sLang["salescampaigns"]["linkedit_shopware"] = "Shopware";
$sLang["salescampaigns"]["linkedit_extern"] = "Extern";
/*
modules/salescampaigns
|_start.php
*/
$sLang["salescampaigns"]["start_reorder_treePanel"] = "Reorder TreePanel";
$sLang["salescampaigns"]["start_not_licensed"] = "NICHT LIZENZIERT";
$sLang["salescampaigns"]["start_this_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["salescampaigns"]["start_in_the_unlicensed_version"] = "In der NICHT lizenzierten Version können Sie maximal 1 Startseiten - Aktion einstellen";
$sLang["salescampaigns"]["start_more_informations"] = "Weitere Informationen:";
$sLang["salescampaigns"]["start_module_features"] = "Modul-Vorstellung";
$sLang["salescampaigns"]["start_rent_buy_module"] = "Modul mieten/kaufen";
$sLang["salescampaigns"]["start_shopware_actions"] = "Shopware Aktionen";
$sLang["salescampaigns"]["start_the_tool_to_easily_create_individual_deals"] = "Das Tool zum einfachen Erstellen individueller Angebote, Inhalte und Landingpages";
$sLang["salescampaigns"]["start_please_select_the_category"] = "Bitte wählen Sie rechts die Kategorie, in der Sie Aktionen einstellen möchten.";
$sLang["salescampaigns"]["start_category_not_found"] = "Kategorie nicht gefunden";
$sLang["salescampaigns"]["start_edit"] = "bearbeiten";
/*
modules/salescampaigns
|_textedit.php
*/
$sLang["salescampaigns"]["textedit_text_deleted"] = "Text wurde gelöscht";
$sLang["salescampaigns"]["textedit_please_enter_a_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["salescampaigns"]["textedit_text_cant_be_saved"] = "Text konnte nicht gespeichert werden";
$sLang["salescampaigns"]["textedit_text_updated"] = "Text wurde aktualisiert";
$sLang["salescampaigns"]["textedit_text_created"] = "Text wurde angelegt";
$sLang["salescampaigns"]["textedit_should_this_text_really_be_deleted"] = "Soll dieses Textelement wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["textedit_edit_text"] = "Text bearbeiten";
$sLang["salescampaigns"]["textedit_heading"] = "Überschrift:";
$sLang["salescampaigns"]["textedit_text"] = "Text:";
$sLang["salescampaigns"]["textedit_save"] = "Speichern";
$sLang["salescampaigns"]["textedit_delete_text"] = "Text löschen";
/*
modules/salescampaigns
|_treeCampaign.php
*/
$sLang["salescampaigns"]["treecampaign_category_not_found"] = "Kategorie nicht gefunden";
$sLang["salescampaigns"]["treecampaign_serverconnection_failed"] = "Verbindung zum Server nicht möglich";
$sLang["salescampaigns"]["treecampaign_element_moved"] = "Element wurde verschoben";
/*
modules/salescampaigns
|_articledetails.php
*/
$sLang["salescampaigns"]["articledetails_article_deleted"] = "Artikel wurde gelöscht";
$sLang["salescampaigns"]["articledetails_article_cant_be_deleted"] = "Artikel konnte nicht gelöscht werden";
$sLang["salescampaigns"]["articledetails_article_not_found"] = "Artikel nicht gefunden";
$sLang["salescampaigns"]["articledetails_please_enter_ordernumber"] = "Bitte geben Sie die Bestellnummer des Artikels ein";
$sLang["salescampaigns"]["articledetails_here_was_no_article_with_the_ordernumber"] = "Es konnte kein Artikel mit der Bestellnummer";
$sLang["salescampaigns"]["articledetails_found"] = "gefunden werden";
$sLang["salescampaigns"]["articledetails_article_cant_be_saved"] = "Artikel konnte nicht gespeichert werden";
$sLang["salescampaigns"]["articledetails_article_updated"] = "Artikel wurde aktualisiert";
$sLang["salescampaigns"]["articledetails_article_created"] = "Artikel wurde angelegt";
$sLang["salescampaigns"]["articledetails_article_created"] = "Artikel nicht gefunden";
$sLang["salescampaigns"]["articledetails_should_this_article_really_be_deleted"] = "Soll dieser Artikel wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["articledetails_edit_article"] = "Artikel bearbeiten";
$sLang["salescampaigns"]["articledetails_ordernumber"] = "Bestellnummer (Fester Artikel):";
$sLang["salescampaigns"]["articledetails_picture_upload"] = "Bild-Upload (Bild mit Link):";
$sLang["salescampaigns"]["articledetails_target_link"] = "Link-Ziel (Bild mit Link):";
$sLang["salescampaigns"]["articledetails_target"] = "Ziel:";
$sLang["salescampaigns"]["articledetails_typ"] = "Typ:";
$sLang["salescampaigns"]["articledetails_random"] = "Zufällig";
$sLang["salescampaigns"]["articledetails_topseller"] = "Topseller";
$sLang["salescampaigns"]["articledetails_novelty"] = "Neuheit";
$sLang["salescampaigns"]["articledetails_solid_article"] = "fester Artikel";
$sLang["salescampaigns"]["articledetails_picture_with_link"] = "Bild mit Link";
$sLang["salescampaigns"]["articledetails_save_article"] = "Artikel speichern";
$sLang["salescampaigns"]["articledetails_delete_article"] = "Artikel löschen";
/*
modules/salescampaigns
|_articlesedit.php
*/
$sLang["salescampaigns"]["articlesedit_articlegroup_not_found"] = "Artikelgruppe nicht gefunden";
$sLang["salescampaigns"]["articlesedit_no_picture_defined"] = "Kein Bild definiert";
$sLang["salescampaigns"]["articlesedit_please_enter_the_ordernumber"] = "Bitte geben Sie die Bestellnummer des Artikels ein";
$sLang["salescampaigns"]["articlesedit_here_was_no_article_with_the_ordernumber"] = "Es konnte kein Artikel mit der Bestellnummer";
$sLang["salescampaigns"]["articlesedit_found"] = "gefunden werden";
$sLang["salescampaigns"]["articlesedit_article_cant_be_saved"] = "Artikel konnte nicht gespeichert werden";
$sLang["salescampaigns"]["articlesedit_article_updated"] = "Artikel wurde aktualisiert";
$sLang["salescampaigns"]["articlesedit_article_created"] = "Artikel wurde angelegt";
$sLang["salescampaigns"]["articlesedit_should_this_articlegroup_really_be_deleted"] = "Soll diese Artikelgruppe wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["articlesedit_articlegroup_settings"] = "Artikelgruppe Eigenschaften";
$sLang["salescampaigns"]["articlesedit_heading"] = "Überschrift:";
$sLang["salescampaigns"]["articlesedit_save"] = "Speichern";
$sLang["salescampaigns"]["articlesedit_delete_articlegroup"] = "Artikelgruppe löschen";
$sLang["salescampaigns"]["articlesedit_add_article_to_group"] = "Artikel in Gruppe einfügen";
$sLang["salescampaigns"]["articlesedit_ordernumber"] = "Bestellnummer (Fester Artikel):";
$sLang["salescampaigns"]["articlesedit_picture_upload"] = "Bild-Upload (Bild mit Link):";
$sLang["salescampaigns"]["articlesedit_linktarget"] = "Link-Ziel (Bild mit Link):";
$sLang["salescampaigns"]["articlesedit_target"] = "Ziel:";
$sLang["salescampaigns"]["articlesedit_typ"] = "Typ:";
$sLang["salescampaigns"]["articlesedit_random"] = "Zufällig";
$sLang["salescampaigns"]["articlesedit_topseller"] = "Topseller";
$sLang["salescampaigns"]["articlesedit_novelty"] = "Neuheit";
$sLang["salescampaigns"]["articlesedit_solid_article"] = "fester Artikel";
$sLang["salescampaigns"]["articlesedit_picture_with_link"] = "Bild mit Link";
$sLang["salescampaigns"]["articlesedit_add_article_to_group"] = "Artikel in Gruppe einfügen";
/*
modules/salescampaigns
|_banneredit.php
*/
$sLang["salescampaigns"]["banneredit_wrong_file_format"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["salescampaigns"]["banneredit_upload_failed"] = "Fehler bei Upload";
$sLang["salescampaigns"]["banneredit_banner_deleted"] = "Banner wurde gelöscht";
$sLang["salescampaigns"]["banneredit_please_enter_a_title"] = "Bitte geben Sie eine Bezeichnung ein";
$sLang["salescampaigns"]["banneredit_banner_cant_be_saved"] = "Banner konnte nicht gespeichert werden";
$sLang["salescampaigns"]["banneredit_banner_updated"] = "Banner wurde aktualisiert";
$sLang["salescampaigns"]["banneredit_banner_created"] = "Banner wurde angelegt";
$sLang["salescampaigns"]["banneredit_should_the_category"] = "Soll die Kategorie";
$sLang["salescampaigns"]["banneredit_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["salescampaigns"]["banneredit_edit_banner"] = "Banner bearbeiten";
$sLang["salescampaigns"]["banneredit_bannertitle"] = "Überschrift des Banners:";
$sLang["salescampaigns"]["banneredit_picture"] = "Bild:";
$sLang["salescampaigns"]["banneredit_directlink"] = "Direktlink:";
$sLang["salescampaigns"]["banneredit_linktarget"] = "Link-Ziel:";
$sLang["salescampaigns"]["banneredit_shopware"] = "Shopware";
$sLang["salescampaigns"]["banneredit_extern"] = "Extern";
$sLang["salescampaigns"]["banneredit_save"] = "Speichern";
$sLang["salescampaigns"]["banneredit_delete_banner"] = "Banner löschen";
/*
modules/banner
|_skeleton.php
*/
$sLang["banner"]["skeleton_banner"] = "Banner";
/*
modules/banner
|_banner.php
*/
$sLang["banner"]["banner_articles_overview"] = "articles.overview";
$sLang["banner"]["banner_categories"] = "Kategorien";
$sLang["banner"]["banner_settings"] = "Einstellungen";
$sLang["banner"]["banner_no_category_selected"] = "Keine Kategorie ausgewählt";
$sLang["banner"]["banner_home"] = "Startseite";
$sLang["banner"]["banner_banner_deleted"] = "Banner wurde gelöscht";
$sLang["banner"]["banner_banner_cant_be_deleted"] = "Banner konnte nicht gelöscht werden";
$sLang["banner"]["banner_banner_title"] = "Banner-Bezeichnung";
$sLang["banner"]["banner_banner_graphic"] = "Banner-Grafik";
$sLang["banner"]["banner_wrong_file_format"] = "Falsches Dateiformat (jpg,gif,png erlaubt)";
$sLang["banner"]["banner_error_during_upload"] = "Fehler bei Upload";
$sLang["banner"]["banner_saved"] = "Banner gespeichert";
$sLang["banner"]["banner_error"] = "Fehler";
$sLang["banner"]["banner_should_the_banner"] = "Soll das Banner";
$sLang["banner"]["banner_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["banner"]["banner_new_banner"] = "Neuen Banner anlegen";
$sLang["banner"]["banner_please_complete_the_following_fields"] = "Bitte füllen Sie folgende Felder aus";
$sLang["banner"]["banner_title"] = "Bezeichnung:";
$sLang["banner"]["banner_link"] = "Link:";
$sLang["banner"]["banner_linktarget"] = "Link Ziel:";
$sLang["banner"]["banner_shopware"] = "Shopware";
$sLang["banner"]["banner_extern"] = "Extern";
$sLang["banner"]["banner_valid_from"] = "G&uuml;ltig von:";
$sLang["banner"]["banner_valid_until"] = "G&uuml;ltig bis:";
$sLang["banner"]["banner_save_banner"] = "Banner speichern";
$sLang["banner"]["banner_note"] = "Hinweis:";
$sLang["banner"]["banner_please_make_sure"] = "Bitte achten Sie darauf, dass die Grafiken in der richtigen Gr&ouml;ße angelegt worden sind.";
$sLang["banner"]["banner_already_assigned_banner"] = "Bereits zugeordnete Banner dieser Kategorie:";
/*
modules/lizens
|_skeleton.php
*/
$sLang["license"]["skeleton_license"] = "Lizenz";
/*
modules/lizens
|_license.php
*/
$sLang["license"]["license_Payment_methods"] = "Zahlarten";
$sLang["license"]["license_shopware_license"] = "Shopware Lizenzen";
$sLang["license"]["license_You_can_easily_licensed_modules"] = "Sie können Ihre lizenzierten Module bequem unter Einstellungen -> Lizenzen
verwalten";
$sLang["license"]["license_more_informations"] = "Weitere Informationen:";
$sLang["license"]["license_modlue_overview"] = "Übersicht Module";
$sLang["license"]["license_license"] = "Lizenz";
$sLang["license"]["license_licensee"] = "Lizenznehmer:";
$sLang["license"]["license_license_number"] = "Lizenznummer:";
$sLang["license"]["license_modules"] = "Module:";
$sLang["license"]["license_error"] = "Fehler";
$sLang["license"]["license_an_error_has_occurred"] = "Ein Fehler ist aufgetreten!";
$sLang["license"]["license_please_fill_out_the_required_fields"] = "Bitte füllen Sie die Pflichtfelder aus.";
$sLang["license"]["license_first_name"] = "Vorname:";
$sLang["license"]["license_name"] = "Name:";
$sLang["license"]["license_telephone"] = "Telefon:";
$sLang["license"]["license_mail_address"] = "eMail-Adresse:";
$sLang["license"]["license_support_request"] = "Supportanfrage:";
$sLang["license"]["license_support_request_from"] = "Supportanfrage von";
$sLang["license"]["license_thank_you_for_your_request"] = "Vielen Dank für Ihre Anfrage";
$sLang["license"]["license_we_will_shortly_get_in_contact_with_you"] = "Wir werden uns in K&uuml;rze mit Ihnen in Verbindung setzen.";
$sLang["license"]["license_name_1"] = "Name: *";
$sLang["license"]["license_first_name_1"] = "Vorname: *";
$sLang["license"]["license_mail_address_1"] = "eMail-Adresse: *";
$sLang["license"]["license_your_support_request"] = "Ihre Supportanfrage: *";
$sLang["license"]["license_send_support_request"] = "Supportanfrage senden";
/*
modules/service
|_service.php
*/
$sLang["service"]["skeleton_Forms"] = "Service";
/*
modules/service
|_detailsCat.php
*/
$sLang["service"]["detailsCat_service_administration"] = "Service Administration";
$sLang["service"]["detailsCat_rma_number"] = "RMA-Nr.";
$sLang["service"]["detailsCat_customer_number_short"] = "Kd-Nr.";
$sLang["service"]["detailsCat_email"] = "eMail";
$sLang["service"]["detailsCat_invoice_number_short"] = "R-Nr.";
$sLang["service"]["detailsCat_from"] = "Vom";
$sLang["service"]["detailsCat_status"] = "Status";
$sLang["service"]["detailsCat_settings"] = "Optionen";
$sLang["service"]["detailsCat_not_licensed"] = "NICHT LIZENZIERT";
$sLang["service"]["detailsCat_this_module"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["service"]["detailsCat_more_informations"] = "Weitere Informationen:";
$sLang["service"]["detailsCat_module_features"] = "Modul-Vorstellung";
$sLang["service"]["detailsCat_rent_buy_module"] = "Modul mieten/kaufen";
$sLang["service"]["detailsCat_service_management"] = "Service-Verwaltung";
$sLang["service"]["detailsCat_here_you_see_incoming_service_request"] = "Hier sehen Sie eingehende Service-Anfragen und können diese 
bequem verwalten und auf eMail-Templates zur Bearbeitung zurückgreifen.";
$sLang["service"]["detailsCat_search"] = "Suchen";
$sLang["service"]["detailsCat_invoice_number"] = "Rechnungsnummer:";
$sLang["service"]["detailsCat_customer_number"] = "Kundennummer:";
$sLang["service"]["detailsCat_email_1"] = "eMail:";
$sLang["service"]["detailsCat_rma_number_1"] = "RMA-Nr.:";
$sLang["service"]["detailsCat_search_small"] = "suchen";
$sLang["service"]["detailsCat_no_requests_found"] = "Keine Anfragen gefunden!";
$sLang["service"]["detailsCat_there_were_no_requests_found"] = "Es wurden keinen Anfragen gefunden.";
/*
modules/service
|_getarticle.php
*/
$sLang["service"]["getarticle_not_processed"] = "nicht bearbeitet";
$sLang["service"]["getarticle_rejected_on"] = "Abgelehnt am";
$sLang["service"]["getarticle_accepted_on"] = "Akzeptiert am";
/*
modules/shipping
|_skeleton.php
*/
$sLang["shipping"]["skeleton_forwarding_expenses"] = "Versandkosten";
/*
modules/shipping
|_shipping.php
*/
$sLang["shipping"]["shipping_forwarding_expenses"] = "Versandkosten";
$sLang["shipping"]["shipping_should_the_dispatch"] = "Soll die Versandart";
$sLang["shipping"]["shipping_really_be_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["shipping"]["shipping_to_define_supplements_for_payment_methods"] = "Um Zuschläge für Zahlungsarten zu definieren, klicken Sie bitte";
$sLang["shipping"]["shipping_here"] = "hier";
$sLang["shipping"]["shipping_please_make_sure"] = "Bitte achten Sie darauf, die Versandkosten Brutto zu hinterlegen";
$sLang["shipping"]["shipping_shipment_settings"] = "Versandarten-Einstellungen";
$sLang["shipping"]["shipping_dispatch_selection"] = "Auswahl Versandart:";
$sLang["shipping"]["shipping_or_new"] = "oder neu:";
$sLang["shipping"]["shipping_title"] = "Bezeichnung:";
$sLang["shipping"]["shipping_position"] = "Position:";
$sLang["shipping"]["shipping_valid_for_free"] = "Gültig für Versandkostenfrei:";
$sLang["shipping"]["shipping_description"] = "Beschreibung:";
$sLang["shipping"]["shipping_country_selection"] = "Auswahl Länder:";
$sLang["shipping"]["shipping_save"] = "Speichern";
$sLang["shipping"]["shipping_delete_dispatch"] = "Versandart löschen";
$sLang["shipping"]["shipping_zone_settings"] = "Zonen-Einstellungen";
$sLang["shipping"]["shipping_dispatch"] = "Versandart:";
$sLang["shipping"]["shipping_selection_group"] = "Auswahl Gruppe:";
$sLang["shipping"]["shipping_free_shipping_from"] = "Versandkostenfrei ab:";
$sLang["shipping"]["shipping_optional"] = "Optional: Länderspezifische Einstellungen";
$sLang["shipping"]["shipping_locations"] = "Auswahl Land:";
$sLang["shipping"]["shipping_please_select"] = "Bitte wählen";
$sLang["shipping"]["shipping_delete_assignment"] = "Zuordnung löschen";
/*
modules/support
|_skeleton.php
*/
$sLang["support"]["shipping_support_forms"] = "Formulare";
/*
modules/support
|_detailsCat.php
*/
$sLang["support"]["detailscat_help_administration"] = "Help Administration";
$sLang["support"]["detailscat_should_the_field"] = "Soll das Feld";
$sLang["support"]["detailscat_really_be_deleted"] = "wirklich gelöscht werden?";
$sLang["support"]["detailscat_should_the_form"] = "Soll das Formular";
$sLang["support"]["detailscat_not_licensed"] = "NICHT LIZENZIERT";
$sLang["support"]["detailscat_this_modul"] = "Dieses Zusatzmodul können Sie bei Bedarf jederzeit nachrüsten";
$sLang["support"]["detailscat_more_informations"] = "Weitere Informationen:";
$sLang["support"]["detailscat_module_features"] = "Modul-Vorstellung";
$sLang["support"]["detailscat_buy_rent_module"] = "Modul mieten/kaufen";
$sLang["support"]["detailscat_form_overview"] = "Formular Übersicht";
$sLang["support"]["detailscat_name"] = "Name";
$sLang["support"]["detailscat_email"] = "eMail";
$sLang["support"]["detailscat_create_form"] = "Formular anlegen";
$sLang["support"]["detailscat_create"] = "anlegen";
/*
modules/support
|_detailsArt.php
*/
$sLang["support"]["detailsart_help_administration"] = "Help Administration";
$sLang["support"]["detailsart_should_the_field"] = "Soll das Feld";
$sLang["support"]["detailsart_really_be_deleted"] = "wirklich gelöscht werden?";
$sLang["support"]["detailsart_should_the_form"] = "Soll das Formular";
$sLang["support"]["detailsart_edit_form"] = "Formular bearbeiten";
$sLang["support"]["detailsart_name"] = "Name:";
$sLang["support"]["detailsart_email"] = "eMail:";
$sLang["support"]["detailsart_email_subject"] = "E-Mail-Betreff:";
$sLang["support"]["detailsart_form_head"] = "Formular - Kopf:";
$sLang["support"]["detailsart_form_confirmation"] = "Formular - Bestätigung:";
$sLang["support"]["detailsart_email_template"] = "eMail-Template:";
$sLang["support"]["detailsart_save"] = "speichern";
$sLang["support"]["detailsart_edit_field"] = "Felder bearbeiten";
$sLang["support"]["detailsart_with_two_input_fields"] = "Bei Zwei Eingabefeldern zwei Namen angeben und mit Semikolen (;) trennen.";
$sLang["support"]["detailsart_name_1"] = "Name";
$sLang["support"]["detailsart_title"] = "Bezeichnung";
$sLang["support"]["detailsart_typ"] = "Typ";
$sLang["support"]["detailsart_look"] = "Aussehen";
$sLang["support"]["detailsart_position"] = "Position";
$sLang["support"]["detailsart_values_in_the_selection_fields"] = "Werte die in Auswahlfelder, Checkboxen oder Radio-Buttons ausgewählt werden können. Die Werte mit Semikolons (;) trennen.";
$sLang["support"]["detailsart_options"] = "Optionen";
$sLang["support"]["detailsart_comment"] = "Kommentar";
$sLang["support"]["detailsart_error_message"] = "Fehler Meldung";
$sLang["support"]["detailsart_required"] = "Eingabe erforderlich?";
$sLang["support"]["detailsart_please_select"] = "Bitte wählen:";
$sLang["support"]["detailsart_field"] = "Eingabe Feld";
$sLang["support"]["detailsart_two_fields"] = "Zwei Eingabe Felder";
$sLang["support"]["detailsart_radio_button"] = "Radio-Button";
$sLang["support"]["detailsart_selection_field"] = "Auswahlfeld";
$sLang["support"]["detailsart_text_field"] = "Textfeld";
$sLang["support"]["detailsart_checkbox"] = "Checkbox";
$sLang["support"]["detailsart_email_1"] = "eMail";
$sLang["support"]["detailsart_normal"] = "Normal";
$sLang["support"]["detailsart_city_and_zip"] = "PLZ und Ort";
$sLang["support"]["detailsart_street_and_number"] = "Straße und Nr";
$sLang["support"]["detailsart_no"] = "nein";
$sLang["support"]["detailsart_yes"] = "ja";
$sLang["support"]["detailsart_edit"] = "bearbeiten";
$sLang["support"]["detailsart_delete_this_form"] = "Dieses Formular löschen";
$sLang["support"]["detailsart_delete"] = "löschen";
$sLang["support"]["detailsart_form_overview"] = "Formular Übersicht";
$sLang["support"]["detailsart_show"] = "zeigen";
/*
modules/vote
|_skeleton.php
*/
$sLang["vote"]["skeleton_reviews"] = "Bewertungen";
/*
modules/vote
|_transactions.php
*/
$sLang["vote"]["transactions_user_list"] = "User-List";
$sLang["vote"]["transactions_confirmation"] = "Bestätigung";
$sLang["vote"]["transactions_should_the_selected_reviews_really_be_deleted"] = "Sollen die markierten Bewertungen wirklich gelöscht werden?";
$sLang["vote"]["transactions_status"] = "Status";
$sLang["vote"]["transactions_date"] = "Datum";
$sLang["vote"]["transactions_article"] = "Artikel";
$sLang["vote"]["transactions_author"] = "Verfasser";
$sLang["vote"]["transactions_heading"] = "Überschrift";
$sLang["vote"]["transactions_points"] = "Punkte";
$sLang["vote"]["transactions_options"] = "Optionen";
$sLang["vote"]["transactions_article_reviews"] = "Artikelbewertungen";
$sLang["vote"]["transactions_reviews"] = "Bewertungen:";
$sLang["vote"]["transactions_total"] = "Gesamt:";
$sLang["vote"]["transactions_no_reviews_in_view"] = "Keine Bewertungen in Ansicht";
$sLang["vote"]["transactions_test"] = "Test";
$sLang["vote"]["transactions_update"] = "Aktualisieren";
$sLang["vote"]["transactions_mark_all"] = "Alle markieren";
$sLang["vote"]["transactions_delete_marked_reviews"] = "Markierte Bewertungen löschen";


$sLang["snippets"]["snippet_deleted"] = "Der Löschvorgang war erfolgreich!";
$sLang["snippets"]["snippet_tree_title"] = "Textbausteine";
$sLang["snippets"]["snippet_tree_add"] = "Hinzufügen";
$sLang["snippets"]["snippet_tree_delete"] = "Löschen";

$sLang["snippets"]["should_the_site"] = "Soll der Textbaustein";
$sLang["snippets"]["really_deleted"] = "wirklich gel&ouml;scht werden?";
$sLang["snippets"]["delete_recrusiv"] = "Beachten Sie: Hierbei werden auch alle Unterknoten gelöscht!";
$sLang["snippets"]["no_selection"] = "Bitte wählen Sie zunächst den Textbaustein / Gruppe aus, die gelöscht werden soll.";
/*
modules/snippets
|_index.php
*/
$sLang["snippets"]["enter_groupname"] = "Bitte geben Sie einen Gruppennamen ein!";
$sLang["snippets"]["enter_description"] = "Bitte geben Sie eine Beschreibung ein!";
$sLang["snippets"]["enter_smartyvar"] = "Bitte geben Sie eine Smarty Variable ein!";

$sLang["snippets"]["add_snippet_title"] = "Neuen Textbaustein hinzufügen";
$sLang["snippets"]["edit_snippet_title"] = "Textbaustein bearbeiten";

$sLang["snippets"]["group_label"] = "Gruppe:";
$sLang["snippets"]["group_add_label"] = "oder neu:";
$sLang["snippets"]["description_label"] = "Beschreibung:";
$sLang["snippets"]["smartyvar_label"] = "Smarty Variable:";
$sLang["snippets"]["content_label"] = "Inhalt:";

$sLang["snippets"]["save_btn"] = "Speichern";
$sLang["snippets"]["save_edit_btn"] = "Änderungen speichern";

/*
modules/paypalreserveorder
|_skeleton.php
*/
$sLang["paypalreserveorder"]["skeleton_Payments_reserved"] = "PayPal Zahlungen";
/*
modules/paypalreserveorder
|_transactions.php
*/
$sLang["paypalreserveorder"]["transactions_reorder"] = "Reorder TreePanel";
$sLang["paypalreserveorder"]["transactions_status"] = "Status";
$sLang["paypalreserveorder"]["transactions_no_orders_found"] = "Keine Bestellungen gefunden";
$sLang["paypalreserveorder"]["transactions_date"] = "Datum";
$sLang["paypalreserveorder"]["transactions_ordernumber"] = "Bestellnr.";
$sLang["paypalreserveorder"]["transactions_Action"] = "Transaktion.";
$sLang["paypalreserveorder"]["transactions_order_status"] = "Bestellstatus";
$sLang["paypalreserveorder"]["transactions_payment_status"] = "Zahlstatus";
$sLang["paypalreserveorder"]["transactions_total"] = "Gesamtbetrag";
$sLang["paypalreserveorder"]["transactions_customer"] = "Kunde";
$sLang["paypalreserveorder"]["transactions_options"] = "Optionen";
$sLang["paypalreserveorder"]["transactions_PP_free"] = "Buchung";
$sLang["paypalreserveorder"]["transactions_PP_refund"] = "Gutschrift";
$sLang["paypalreserveorder"]["transactions_Period_end"] = "Frist abgelaufen";
$sLang["paypalreserveorder"]["transactions_Evaluation_of"] = "Auswertung von:";
$sLang["paypalreserveorder"]["transactions_Evaluation_until"] = "Auswertung bis:";
$sLang["paypalreserveorder"]["transactions_Booking_Status"] = "Buchungsstatus:";
$sLang["paypalreserveorder"]["transactions_show_all"] = "Alle anzeigen";
$sLang["paypalreserveorder"]["transactions_open_bookings"] = "Offene Buchungen";
$sLang["paypalreserveorder"]["transactions_Completed_bookings"] = "Abgeschlossene Buchungen";
$sLang["paypalreserveorder"]["transactions_status_1"] = "Status:";
$sLang["paypalreserveorder"]["transactions_status_payment"] = "Bezahlstatus:";
$sLang["paypalreserveorder"]["transactions_search"] = "Suche (Nr./Transaktion)";
$sLang["paypalreserveorder"]["transactions_refresh_view"] = "Ansicht aktualisieren";
$sLang["paypalreserveorder"]["transactions_attention"] = "Hinweis: Durch Doppelklick auf den Status lässt sich dieser ändern. <br/ >PayPal empfiehlt grundsätzlich reservierte Zahlungen nach spätestens 3 Tagen freizugeben (belasten).";
$sLang["paypalreserveorder"]["transactions_total_in_period"] = "Gesamtumsatz in Zeitraum:";
$sLang["paypalreserveorder"]["transactions_count_of_orders"] = "Anzahl Bestellungen:";
$sLang["paypalreserveorder"]["transactions_cant_load_Description"] = "Beschreibung konnte nicht geladen werden";
$sLang["paypalreserveorder"]["transactions_cant_load_orderID"] = "Bestell-ID konnte nicht ermittelt werden";
$sLang["paypalreserveorder"]["transactions_cant_refresh_status"] = "Status konnte nicht aktualisiert werden";
$sLang["paypalreserveorder"]["transactions_status_order"] = "Der Status der Bestellung";
$sLang["paypalreserveorder"]["transactions_has_left"] = "wurde auf";
$sLang["paypalreserveorder"]["transactions_changed"] = "geändert!";
/*
modules/paypalreserveorder/action
|_skeleton.php
*/
$sLang["paypalreserveorder"]["action_skeleton_reserved_booking"] = "Reservierte Buchung";
/*
modules/paypalreserveorder/action
|_transactions.php
*/
//52
$sLang["paypalreserveorder"]["action_transaction_too_high"] = "Der Betrag ist zu hoch!";
//55
$sLang["paypalreserveorder"]["action_transaction_Invalid_procedures"] = "Ungültiges Zahlverfahren!";
//153
$sLang["paypalreserveorder"]["action_transaction_error_booking"] = "Es ist ein Fehler bei der Buchung aufgetreten";
$sLang["paypalreserveorder"]["action_transaction_error_errorcode"] = "Error Code";
$sLang["paypalreserveorder"]["action_transaction_error_short_message"] = "Fehler";
$sLang["paypalreserveorder"]["action_transaction_error_long_message"] = "Beschreibung";
$sLang["paypalreserveorder"]["action_transaction_customer"] = "Kunde:";
$sLang["paypalreserveorder"]["action_transaction_order_date"] = "Bestelldatum:";
$sLang["paypalreserveorder"]["action_transaction_order_number"] = "Bestellnummer:";
$sLang["paypalreserveorder"]["action_transaction_Transaction_number"] = "Transaktionsnr.:";
$sLang["paypalreserveorder"]["action_transaction_total"] = "Gesamtbetrag:";
$sLang["paypalreserveorder"]["action_transaction_payment"] = "Zahlverfahren:";
$sLang["paypalreserveorder"]["action_transaction_Booking_already_done"] = "Buchung wurde durchgef&uuml;hrt!";
$sLang["paypalreserveorder"]["action_transaction_max_amount"] = "Max. zu buchender Betrag:";
$sLang["paypalreserveorder"]["action_transaction_no_order_found"] = "Keine Bestellung gefunden!";

/*
modules/paypalrefund
|_skeleton.php
*/
$sLang["paypalreserveorder"]["action_skeleton_refund"] = "Gutschrift";
/*
modules/paypalrefund
|_transactions.php
*/
$sLang["paypalreserveorder"]["action_refund_done"] = "Gutschrift wurde durchgef&uuml;hrt!";
$sLang["paypalreserveorder"]["action_refund_total"] = "bisherige Gutschrift:";
$sLang["paypalreserveorder"]["action_refund_max_amount"] = "Max. Betrag:";
$sLang["paypalreserveorder"]["action_refund_comment"] = "Wiedergutschrift:";
$sLang["paypalreserveorder"]["action_refund_error"] = "Fehlerhafter Betrag:";

?>