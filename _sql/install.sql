-- Install.sql for Shopware

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
UPDATE `s_core_multilanguage` SET `switchCurrencies` = '1' WHERE `s_core_multilanguage`.`locale` = 1 AND `s_core_multilanguage`.`parentID` = 3;

/**
 * @ticket 5324
 * @author s.pohl
 * @since 3.5.4 - 2011/05/25
 */
REPLACE INTO `s_core_config_mails` VALUES(NULL, 'sORDER', 'info@example.com', 'Shopware 3.0 Demo', 'Ihre Bestellung im Demoshop', 'Hallo {$billingaddress.firstname} {$billingaddress.lastname},\r\n \r\nvielen Dank fuer Ihre Bestellung im Shopware Demoshop (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\nInformationen zu Ihrer Bestellung:\r\n \r\nPos. Art.Nr.              Menge         Preis        Summe\r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\r\n{$details.articlename|wordwrap:49|indent:5}\r\n{/foreach}\r\n \r\nVersandkosten: {$sShippingCosts}\r\nGesamtkosten Netto: {$sAmountNet}\r\n{if !$sNet}\r\nGesamtkosten Brutto: {$sAmount}\r\n{/if}\r\n \r\nGew�hlte Zahlungsart: {$additional.payment.description}\r\n{$additional.payment.additionaldescription}\r\n{if $additional.payment.name == "debit"}\r\nIhre Bankverbindung:\r\nKontonr: {$sPaymentTable.account}\r\nBLZ:{$sPaymentTable.bankcode}\r\nWir ziehen den Betrag in den n�chsten Tagen von Ihrem Konto ein.\r\n{/if}\r\n{if $additional.payment.name == "prepayment"}\r\n \r\nUnsere Bankverbindung:\r\nKonto: ###\r\nBLZ: ###\r\n{/if}\r\n \r\n{if $sComment}\r\nIhr Kommentar:\r\n{$sComment}\r\n{/if}\r\n \r\nRechnungsadresse:\r\n{$billingaddress.company}\r\n{$billingaddress.firstname} {$billingaddress.lastname}\r\n{$billingaddress.street} {$billingaddress.streetnumber}\r\n{$billingaddress.zipcode} {$billingaddress.city}\r\n{$billingaddress.phone}\r\n{$additional.country.countryname}\r\n \r\nLieferadresse:\r\n{$shippingaddress.company}\r\n{$shippingaddress.firstname} {$shippingaddress.lastname}\r\n{$shippingaddress.street} {$shippingaddress.streetnumber}\r\n{$shippingaddress.zipcode} {$shippingaddress.city}\r\n{$additional.country.countryname}\r\n \r\n{if $billingaddress.ustid}\r\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\r\nBei erfolgreicher Pr�fung und sofern Sie aus dem EU-Ausland\r\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\r\n{/if}\r\n \r\n \r\nF�r R�ckfragen stehen wir Ihnen jederzeit gerne zur Verf�gung. Sie erreichen uns wie folgt:\r\n \r\nWir w�nschen Ihnen noch einen sch�nen Tag.\r\n \r\nFirma: ###\r\nAdresse: ###\r\nTelefon: ###\r\neMail: ###\r\nURL: ###\r\nGesch�ftsf�hrer: ###\r\nRegistriergericht: ###\r\n\r\n## Bei Bestellbest�tigungen muss die Widerrufsbelehrung mitgeschickt werden. ###', '<div style="font-family:arial; font-size:12px;">\r\n<img src="http://www.shopwaredemo.de/eMail_logo.jpg" alt="Logo" />\r\n \r\n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\r\n \r\nvielen Dank fuer Ihre Bestellung bei {$sConfig.sSHOPNAME} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.\r\n<br/>\r\n<br/>\r\n<strong>Informationen zu Ihrer Bestellung:</strong></p>\r\n  <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:10px;">\r\n    <tr>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>\r\n      <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Summe</strong></td>\r\n    </tr>\r\n \r\n    {foreach item=details key=position from=$sOrderDetails}\r\n    <tr>\r\n      <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.1}<img src="{$details.image.src.1}" alt="{$details.articlename}" />{else} {/if}</td>\r\n      <td>{$position+1|fill:4} </td>\r\n      <td>{$details.ordernumber|fill:20}</td>\r\n      <td>{$details.quantity|fill:6}</td>\r\n      <td>{$details.price|padding:8}{$sCurrency}</td>\r\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\r\n    </tr>\r\n    <tr>\r\n      <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>\r\n    </tr>\r\n    {/foreach}\r\n \r\n  </table>\r\n \r\n<p>\r\n  <br/>\r\n  <br/>\r\n    Versandkosten: {$sShippingCosts}<br/>\r\n    Gesamtkosten Netto: {$sAmountNet}<br/>\r\n    {if !$sNet}\r\n    Gesamtkosten Brutto: {$sAmount}<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    <strong>Gew�hlte Zahlungsart:</strong> {$additional.payment.description}<br/>\r\n    {$additional.payment.additionaldescription}\r\n    {if $additional.payment.name == "debit"}\r\n    Ihre Bankverbindung:<br/>\r\n    Kontonr: {$sPaymentTable.account}<br/>\r\n    BLZ:{$sPaymentTable.bankcode}<br/>\r\n    Wir ziehen den Betrag in den n�chsten Tagen von Ihrem Konto ein.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    {if $additional.payment.name == "prepayment"}\r\n    Unsere Bankverbindung:<br/>\r\n    Konto: ###<br/>\r\n    BLZ: ###<br/>\r\n    {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Gew�hlte Versandart:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\r\n</p>\r\n<p>\r\n  {if $sComment}\r\n    <strong>Ihr Kommentar:</strong><br/>\r\n    {$sComment}<br/>\r\n  {/if} \r\n  <br/>\r\n  <br/>\r\n    <strong>Rechnungsadresse:</strong><br/>\r\n    {$billingaddress.company}<br/>\r\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\r\n    {$billingaddress.street} {$billingaddress.streetnumber}<br/>\r\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\r\n    {$billingaddress.phone}<br/>\r\n    {$additional.country.countryname}<br/>\r\n  <br/>\r\n  <br/>\r\n    <strong>Lieferadresse:</strong><br/>\r\n    {$shippingaddress.company}<br/>\r\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\r\n    {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>\r\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\r\n    {$additional.countryShipping.countryname}<br/>\r\n  <br/>\r\n    {if $billingaddress.ustid}\r\n    Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\r\n    Bei erfolgreicher Pr�fung und sofern Sie aus dem EU-Ausland<br/>\r\n    bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\r\n    {/if}\r\n  <br/>\r\n  <br/>\r\n    F�r R�ckfragen stehen wir Ihnen jederzeit gerne zur Verf�gung. Sie erreichen uns wie folgt: <br/>\r\n    <br/>\r\n    Mit freundlichen Gr��en,<br/>\r\n    Ihr Team von {$sConfig.sSHOPNAME}<br/>\r\n  <br/>\r\n  <br/>\r\n    Firma: ###<br/>\r\n    Adresse: ###<br/>\r\n    Telefon: ###<br/>\r\n    eMail: ###<br/>\r\n    URL: ###<br/>\r\n    Gesch�ftsf�hrer: ###<br/>\r\n    Registriergericht: ###\r\n  <br/>\r\n  <br/>\r\n    ## Bei Bestellbest�tigungen muss die Widerrufsbelehrung mitgeschickt werden. ###\r\n</p>\r\n</div>', 1, 1, '1.png;test.pdf/2.png;test2.pdf');

/**
 * @ticket 5124
 * @author h.lohaus
 * @since 3.5.4 - 2011/06/08
 */
 /*
DELETE FROM `s_core_config` WHERE `name` LIKE 'sCLICKPAY%';
DELETE FROM `s_core_config_groups` WHERE `name` LIKE 'ClickPay';
DELETE FROM `s_core_config_text` WHERE `name` LIKE 'sClickPay%';
DELETE FROM `s_core_menu` WHERE `name` LIKE '%ClickPay%';
DELETE FROM `s_core_paymentmeans` WHERE `name` LIKE 'clickpay_%';
DROP TABLE IF EXISTS `eos_risk_results`;
*/

/**
 * @author st.hamann
 * @since 3.5.4 - 2011/06/08
 */
UPDATE `s_core_config` SET value = '' WHERE name = 'sACCOUNTID';
/*
UPDATE `s_core_config` SET value = '0' WHERE name = 'sROUTERURLCACHE';
INSERT IGNORE INTO `s_core_menu` (`id`, `parent`, `hyperlink`, `name`, `onclick`, `style`, `class`, `position`, `active`, `pluginID`) VALUES
(NULL, 40, '', 'Shopware ID registrieren', 'window.open(''http://account.shopware.de'',''Shopware'',''width=800,height=550,scrollbars=yes'')', 'background-position: 5px 5px', 'ico2 book_open', -1, 1, NULL);
*/

/**
 * @author h.lohaus
 * @since 3.5.6 - 2011/01/04
 */
UPDATE `s_core_plugins` SET `active` = '0' WHERE `name` = 'RouterOld';

/**
 * @author Heiner Lohaus
 * @since 4.0.0 - 2012/01/30
 */
UPDATE `s_core_snippets`
SET value=REPLACE(value, '\')', '')
WHERE value LIKE '%$this%';
UPDATE `s_core_snippets`
SET value=REPLACE(value, '$this->config(\'', 'config name=')
WHERE value LIKE '%$this%';
UPDATE `s_core_snippets`
SET value=REPLACE(value, 'config name=sARTICLESOUTPUTNETTO == true', '{config name=articlesOutputNetto}')
WHERE value LIKE '%sARTICLESOUTPUTNETTO%';
