<?php
$pi_Klarna_lang = array();

/**
 *  General
 */
$pi_Klarna_lang['iso'] = "de";
$pi_Klarna_lang['currency'] = "EUR";
$pi_Klarna_lang['notSet'] = "Nicht angegeben";
$pi_Klarna_lang['klarna_href'] = "Mehr Infos zu den Klarna Zahlungsl&ouml;sungen";
$pi_Klarna_lang['paymenterror'] = "Ihre Zahlung wurde von Klarna abgelehnt.";
$pi_Klarna_lang['LeftSidebarHeader'] = "Flexibel bezahlen";


/**
 *  Multilanguage Extension
 */
$pi_Klarna_lang['houseExt'] = "House Extension:&nbsp;";
$pi_Klarna_lang['Norway']['total'] = "Insgesamt";
$pi_Klarna_lang['Norway']['ratetext'] = "NOK zu einem Jahreszins von";
$pi_Klarna_lang['SocialNr'] = "Sozialversicherungsnummer:&nbsp;";
//Country Names for errormessages as {_COUNTRYNAME_}
$pi_Klarna_lang['countryName']['DE'] = "Deutschland";
$pi_Klarna_lang['countryName']['NL'] = "den Niederlanden";
$pi_Klarna_lang['countryName']['FI'] = "Finnland";
$pi_Klarna_lang['countryName']['DK'] = "D&auml;nemark";
$pi_Klarna_lang['countryName']['NO'] = "Norwegen";
$pi_Klarna_lang['countryName']['SE'] = "Schweden";
//Href's for banner and logos
$pi_Klarna_lang['invoice']['ahref'] = "klarna.com/de/privat/unsere-dienstleistungen/klarna-rechnung";
$pi_Klarna_lang['rate']['ahref'] = "klarna.com/de/privat/unsere-dienstleistungen/klarna-ratenkauf";
$pi_Klarna_lang['both']['ahref'] = "klarna.com/de/privat/unsere-dienstleistungen";

/**
 *  Klarna Rechnung
 */
$pi_Klarna_lang['invoice']['name'] = "Klarna Rechnung";
$pi_Klarna_lang['invoice']['description'] = "Bezahlung via Rechnung innerhalb von 14 Tagen!";
$pi_Klarna_lang['invoice']['href'] = "Mehr Infos zum Klarna Rechnungskauf";
$pi_Klarna_lang['invoice']['addresserror'] = "Klarna Rechnung ist in {_COUNTRYNAME_} nur verf&uuml;gbar, wenn die Versandadresse gleich der Lieferadresse ist.";
$pi_Klarna_lang['invoice']['companyerror'] = "Klarna Rechnung ist zur Zeit nur f&uuml;r Privatpersonen verf&uuml;gbar.";
$pi_Klarna_lang['invoice']['birthdayerror'] = "Klarna Rechnung ist nur verf&uuml;gbar, wenn Sie Ihr Geburtsdatum angeben.";
$pi_Klarna_lang['invoice']['nlerror'] = "Klarna Rechnung ist bei einer Bestellung in die Niederlande nur verf&uuml;gbar, wenn Sie Ihre 'House Extension' angeben.";
$pi_Klarna_lang['invoice']['skanderror'] = "Klarna Rechnung ist in {_COUNTRYNAME_} nur verf&uuml;gbar, wenn Sie Ihre Sozialversicherungsnummer angeben.";
$pi_Klarna_lang['invoice']['mailtext'] = "Bezahlung via Rechnung innerhalb von 14 Tagen! <br /><br />"
                    ."Bitte beachten Sie, dass Ihre Rechnung mit separater E-Mail von unserem Partner Klarna versandt wird, "
                    ."sobald wir die Bestellung an Sie ausgeliefert haben.<br />In einigen, wenigen F&auml;llen kann es "
                    ."dazu kommen, dass die von Klarna gesendeten E-Mails geblockt bzw. als Spam deklariert werden.<br />"
                    ."Stellen Sie deshalb bitte sicher, dass Sie die Absender E-Mail-Adresse<b> "
                    ."&lt;kundenservice@klarna.de&gt;</b> nicht blockieren und &uuml;berpr&uuml;fen Sie nach "
                    ."einer Bestellung auf 'Klarna Rechnung' bitte auch Ihren Spam-Ordner in regelm&auml;&szlig;igen Abst&auml;nden.";

/**
 *  Klarna Rate
 */
$pi_Klarna_lang['rate']['name'] = "Klarna Ratenzahlung";
$pi_Klarna_lang['rate']['description'] = "Finanzieren Sie Ihre Bestellung in bequemen Raten!";
$pi_Klarna_lang['rate']['from_amount'] = "finanzieren ab";
$pi_Klarna_lang['rate']['from'] = "ab";
$pi_Klarna_lang['rate']['value_month'] = "EUR/Monat*";
$pi_Klarna_lang['rate']['read_more'] = "Lesen Sie mehr!";
$pi_Klarna_lang['rate']['href'] = "Mehr Infos zum Klarna Ratenkauf";
$pi_Klarna_lang['rate']['addresserror'] = "Klarna Ratenkauf ist in {_COUNTRYNAME_} nur verf&uuml;gbar, wenn die Versandadresse gleich der Lieferadresse ist.";
$pi_Klarna_lang['rate']['companyerror'] = "Klarna Ratenkauf ist zur Zeit nur f&uuml;r Privatpersonen verf&uuml;gbar.";
$pi_Klarna_lang['rate']['birthdayerror'] = "Klarna Ratenkauf ist nur verf&uuml;gbar, wenn Sie Ihr Geburtsdatum angeben.";
$pi_Klarna_lang['rate']['nlerror'] = "Klarna Ratenkauf ist bei einer Bestellung in die Niederlande nur verf&uuml;gbar, wenn Sie Ihre 'House Extension' angeben.";
$pi_Klarna_lang['rate']['skanderror'] = "Klarna Ratenkauf ist in in {_COUNTRYNAME_} nur verf&uuml;gbar, wenn Sie Ihre Sozialversicherungsnummer angeben.";
$pi_Klarna_lang['rate']['mailtext'] = "Finanzieren Sie Ihre Bestellung in bequemen Raten! <br /><br />"
                    ."Bitte beachten Sie, dass Ihre Rechnung mit separater E-Mail von unserem Partner Klarna "
                    ."versandt wird, sobald wir die Bestellung an Sie ausgeliefert haben.<br />"
                    ."In einigen, wenigen F&auml;llen kann es dazu kommen, dass die von Klarna gesendeten"
                    ."E-Mails geblockt bzw. als Spam deklariert werden.<br />Stellen Sie deshalb bitte sicher, "
                    ."dass Sie die Absender E-Mail-Adresse<b> &lt;kundenservice@klarna.de&gt;</b> nicht blockieren "
                    ."und &uuml;berpr&uuml;fen Sie nach einer Bestellung auf 'Klarna Rate' bitte auch Ihren "
                    ."Spam-Ordner in regelm&auml;&szlig;igen Abst&auml;nden.";
$pi_Klarna_lang['rate']['noPclass'] = "Es sind keine Ratenzahlungsmodalit&auml;ten f&uuml;r dieses Rechnungsland hinterlegt. Bitte kontaktieren Sie den H&auml;ndler oder w&auml;hlen Sie eine andere Zahlungsart.";

/**
 *  Birthday Form
 */
$pi_Klarna_lang['missingInfo'] = "Geben Sie hier die noch ben&ouml;tigten Daten ein:";
$pi_Klarna_lang['birthday'] = "Geburtsdatum(TT/MM/JJJJ):&nbsp;";
$pi_Klarna_lang['submit_value'] = "speichern";

/**
 *  Customer Orderview
 *
 *  templates/_default/frontend/account/order_item.tpl
 */
$pi_Klarna_lang['payment']['check'] = "Zahlung wird von Klarna gepr&uuml;ft.";
$pi_Klarna_lang['payment']['accepted'] = "Zahlung von Klarna akzeptiert.";
$pi_Klarna_lang['payment']['denied'] = "Zahlung von Klarna nicht akzeptiert.";
$pi_Klarna_lang['stats']['work'] = "Bestellung ist in Bearbeitung.";
$pi_Klarna_lang['stats']['open'] = "Bestellung wurde noch nicht bearbeitet.";
$pi_Klarna_lang['stats']['complete'] = "Bestellung wurde versendet.";
$pi_Klarna_lang['stats']['part'] = "Bestellung wurde teilweise versendet.";
$pi_Klarna_lang['stats']['cancel'] = "Bestellung abgebrochen.";
$pi_Klarna_lang['stats']['return'] = "Bestellung wurde komplett storniert.";
$pi_Klarna_lang['stats']['complete_return'] = "Bestellung wurde komplett retourniert.";
$pi_Klarna_lang['storno_href'] = "Bestellung stornieren.";
$pi_Klarna_lang['dispatch']['none'] = "Nicht angegeben";
$pi_Klarna_lang['order']['show'] = "Anzeigen";
/**
 *  Checkout Page
 *
 *  templates/_default/frontend/checkout/confirm.tpl
 */
$pi_Klarna_lang['agberror'] = "Bitte best&auml;tigen Sie unsere AGB.";
$pi_Klarna_lang['Payment_informations'] = "Rechnungsadresse, Lieferadresse und Zahlungsart k&ouml;nnen Sie jetzt noch &auml;ndern. Achten Sie allerdings darauf, dass Rechnungs- und Lieferadresse mit Ihrer Meldeaddresse &uuml;bereinstimmen m&uuml;ssen um mit Klarna zu bezahlen.";
$pi_Klarna_lang['Payment_informations_header'] = "Bitte &uuml;berpr&uuml;fen Sie Ihre Bestellung nochmals, bevor Sie sie senden.";
$pi_Klarna_lang['klarnaagb']['start'] = "Mit der &Uuml;bermittlung der f&uuml;r die Abwicklung der gew&auml;hlten Klarna Zahlungsmethode und einer Identit&auml;ts- und Bonit&auml;tspr&uuml;fung erforderlichen Daten an Klarna bin ich einverstanden. Meine ";
$pi_Klarna_lang['klarnaagb']['href'] = "Einwilligung";
$pi_Klarna_lang['klarnaagb']['end'] = "kann ich jederzeit mit Wirkung f&uuml;r die Zukunft widerrufen.";
$pi_Klarna_lang['agb']['error'] = "Bitte stimmen Sie der Daten&uuml;bermittlung an Klarna zu.";
$pi_Klarna_lang['agb']['start'] = "Ich habe die";
$pi_Klarna_lang['agb']['href'] = "AGB";
$pi_Klarna_lang['agb']['end'] = "Ihres Shops gelesen und bin mit deren Geltung einverstanden.";












