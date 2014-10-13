<?php

class Migrations_Migration407 Extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->addConfigFields();

        if ($modus === \Shopware\Components\Migrations\AbstractMigration::MODUS_INSTALL) {
            $this->fixContextData();
            $this->updateTemplates();
        }
    }

    private function fixContextData()
    {
        $sql = <<<EOD
UPDATE `s_core_config_mails` SET
`context` = REPLACE (`context`, 's:8:"Banjimen"', 's:3:"Max"'),
`context` = REPLACE (`context`, 's:6:"Ercmer"', 's:10:"Mustermann"')
WHERE `context` LIKE '%Banjimen%'
OR `context` LIKE '%Ercmer%';
EOD;

        $this->addSql($sql);
    }

    private function addConfigFields()
    {
        $sql = <<<'EOD'
INSERT IGNORE INTO `s_core_config_elements` (`form_id`, `name`, `value`, `label`, `type`, `required`, `position`, `scope`, `filters`, `validators`, `options`) VALUES
(192, 'emailheaderplain', 's:0:"";', 'E-Mail Header Plaintext', 'textarea', 0, 0, 1, NULL, NULL, NULL),
(192, 'emailheaderhtml', 's:137:"<div>\n<img src=\"{\$sShopURL}/engine/Shopware/Themes/Frontend/Responsive/frontend/_public/src/img/logos/logo--tablet.png\" alt=\"Logo\"><br />";', 'E-Mail Header HTML', 'textarea', 0, 0, 1, NULL, NULL, NULL),
(192, 'emailfooterplain', 's:63:"Mit freundlichen Grüßen,\n\nIhr Team von {config name=shopName}";', 'E-Mail Footer Plaintext', 'textarea', 0, 0, 1, NULL, NULL, NULL),
(192, 'emailfooterhtml', 's:79:"Mit freundlichen Grüßen,<br/><br/>\n\nIhr Team von {config name=shopName}</div>";', 'E-Mail Footer HTML', 'textarea', 0, 0, 1, NULL, NULL, NULL);
EOD;

        $this->addSql($sql);
    }

    private function updateTemplates()
    {
        $this->updateTemplate(
            'sREGISTERCONFIRMATION',
            'Hallo {salutation} {firstname} {lastname},\n \nvielen Dank für Ihre Anmeldung in unserem Shop.\n \nSie erhalten Zugriff über Ihre E-Mail-Adresse {sMAIL}\nund dem von Ihnen gewählten Kennwort.\n \nSie können sich Ihr Kennwort jederzeit per E-Mail erneut zuschicken lassen.',
            '<div style=\"font-family:arial; font-size:12px;\">\n<p>\nHallo {salutation} {firstname} {lastname},<br/><br/>\n \nvielen Dank für Ihre Anmeldung in unserem Shop.<br/><br/>\n \nSie erhalten Zugriff über Ihre eMail-Adresse <strong>{sMAIL}</strong><br/>\nund dem von Ihnen gewählten Kennwort.<br/><br/>\n \nSie können sich Ihr Kennwort jederzeit per eMail erneut zuschicken lassen.\n</p>\n</div>'
        );

        $this->updateTemplate(
            'sORDER',
            'Hallo {$billingaddress.firstname} {$billingaddress.lastname},\n \nvielen Dank fuer Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay|date:\"DATE_MEDIUM\"} um {$sOrderTime|date:\"TIME_SHORT\"}.\nInformationen zu Ihrer Bestellung:\n \nPos. Art.Nr.              Menge         Preis        Summe\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\n{$details.articlename|wordwrap:49|indent:5}\n{/foreach}\n \nVersandkosten: {$sShippingCosts}\nGesamtkosten Netto: {$sAmountNet}\n{if !$sNet}\nGesamtkosten Brutto: {$sAmount}\n{/if}\n \nGewählte Zahlungsart: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == \"debit\"}\nIhre Bankverbindung:\nKontonr: {$sPaymentTable.account}\nBLZ:{$sPaymentTable.bankcode}\nWir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.\n{/if}\n{if $additional.payment.name == \"prepayment\"}\n \nUnsere Bankverbindung:\n{config name=bankAccount}\n{/if}\n \n{if $sComment}\nIhr Kommentar:\n{$sComment}\n{/if}\n \nRechnungsadresse:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street}\n{$billingaddress.zipcode} {$billingaddress.city}\n{$billingaddress.phone}\n{$additional.country.countryname}\n \nLieferadresse:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street}\n{$shippingaddress.zipcode} {$shippingaddress.city}\n{$additional.country.countryname}\n \n{if $billingaddress.ustid}\nIhre Umsatzsteuer-ID: {$billingaddress.ustid}\nBei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland\nbestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.\n{/if}\n \n \nFür Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. \n\nWir wünschen Ihnen noch einen schönen Tag.',
            '<div style=\"font-family:arial; font-size:12px;\">\n \n<p>Hallo {$billingaddress.firstname} {$billingaddress.lastname},<br/><br/>\n \nvielen Dank fuer Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay|date:\"DATE_MEDIUM\"} um {$sOrderTime|date:\"TIME_SHORT\"}.\n<br/>\n<br/>\n<strong>Informationen zu Ihrer Bestellung:</strong></p>\n  <table width=\"80%\" border=\"0\" style=\"font-family:Arial, Helvetica, sans-serif; font-size:10px;\">\n    <tr>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Artikel</strong></td>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Pos.</strong></td>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Art-Nr.</strong></td>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Menge</strong></td>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Preis</strong></td>\n      <td bgcolor=\"#F7F7F2\" style=\"border-bottom:1px solid #cccccc;\"><strong>Summe</strong></td>\n    </tr>\n \n    {foreach item=details key=position from=$sOrderDetails}\n    <tr>\n      <td rowspan=\"2\" style=\"border-bottom:1px solid #cccccc;\">{if $details.image.src.1}<img src=\"{$details.image.src.1}\" alt=\"{$details.articlename}\" />{else} {/if}</td>\n      <td>{$position+1|fill:4} </td>\n      <td>{$details.ordernumber|fill:20}</td>\n      <td>{$details.quantity|fill:6}</td>\n      <td>{$details.price|padding:8}{$sCurrency}</td>\n      <td>{$details.amount|padding:8} {$sCurrency}</td>\n    </tr>\n    <tr>\n      <td colspan=\"5\" style=\"border-bottom:1px solid #cccccc;\">{$details.articlename|wordwrap:80|indent:4}</td>\n    </tr>\n    {/foreach}\n \n  </table>\n \n<p>\n  <br/>\n  <br/>\n    Versandkosten: {$sShippingCosts}<br/>\n    Gesamtkosten Netto: {$sAmountNet}<br/>\n    {if !$sNet}\n    Gesamtkosten Brutto: {$sAmount}<br/>\n    {/if}\n  <br/>\n  <br/>\n    <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>\n    {$additional.payment.additionaldescription}\n    {if $additional.payment.name == \"debit\"}\n    Ihre Bankverbindung:<br/>\n    Kontonr: {$sPaymentTable.account}<br/>\n    BLZ:{$sPaymentTable.bankcode}<br/>\n    Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>\n    {/if}\n  <br/>\n  <br/>\n    {if $additional.payment.name == \"prepayment\"}\n    Unsere Bankverbindung:<br/>\n    {config name=bankAccount}\n    {/if} \n  <br/>\n  <br/>\n    <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>{$sDispatch.description}\n</p>\n<p>\n  {if $sComment}\n    <strong>Ihr Kommentar:</strong><br/>\n    {$sComment}<br/>\n  {/if} \n  <br/>\n  <br/>\n    <strong>Rechnungsadresse:</strong><br/>\n    {$billingaddress.company}<br/>\n    {$billingaddress.firstname} {$billingaddress.lastname}<br/>\n    {$billingaddress.street}<br/>\n    {$billingaddress.zipcode} {$billingaddress.city}<br/>\n    {$billingaddress.phone}<br/>\n    {$additional.country.countryname}<br/>\n  <br/>\n  <br/>\n    <strong>Lieferadresse:</strong><br/>\n    {$shippingaddress.company}<br/>\n    {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>\n    {$shippingaddress.street}<br/>\n    {$shippingaddress.zipcode} {$shippingaddress.city}<br/>\n    {$additional.countryShipping.countryname}<br/>\n  <br/>\n    {if $billingaddress.ustid}\n    Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>\n    Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>\n    bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>\n    {/if}\n  <br/>\n  <br/>\n    Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung. Sie erreichen uns wie folgt: <br/>{config name=address}\n</p>\n</div>'
        );

        $this->updateTemplate(
            'sTELLAFRIEND',
            'Hallo,\r\n\r\n{sName} hat für Sie bei {sShop} ein interessantes Produkt gefunden, dass Sie sich anschauen sollten:\r\n\r\n{sArticle}\r\n{sLink}\r\n\r\n{sComment}'
        );

        $this->updateTemplate(
            'sPASSWORD',
            'Hallo,\n\nIhre Zugangsdaten zu {sShopURL} lauten wie folgt:\nBenutzer: {sMail}\nPasswort: {sPassword}'
        );

        $this->updateTemplate(
            'sNOSERIALS',
            'Hallo,\r\n\r\nes sind keine weiteren freien Seriennummern für den Artikel {sArticleName} verfügbar. Bitte stellen Sie umgehend neue Seriennummern ein oder deaktivieren Sie den Artikel.'
        );

        $this->updateTemplate(
            'sVOUCHER',
            'Hallo {customer},\n\n{user} ist Ihrer Empfehlung gefolgt und hat so eben bei {sShop} bestellt.\nWir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.\n\nIhr Gutschein-Code lautet: XXX'
        );

        $this->updateTemplate(
            'sCUSTOMERGROUPHACCEPTED',
            'Hallo,\n\nIhr Händleraccount bei {$sShop} wurde freigeschaltet.\n\nAb sofort kaufen Sie zum Netto-EK bei uns ein.'
        );

        $this->updateTemplate(
            'sCUSTOMERGROUPHREJECTED',
            'Sehr geehrter Kunde,\n\nvielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.\n\nBei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL1',
            'Sehr geehrte{if $sUser.billing_salutation eq \"mr\"}r Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\" %d-%m-%Y\"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL2',
            'Sehr geehrte{if $sUser.billing_salutation eq \"mr\"}r Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\" %d-%m-%Y\"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.'
        );

        $this->updateTemplate(
            'sCANCELEDQUESTION',
            'Lieber Kunde,\r\n \r\nSie haben vor kurzem Ihre Bestellung auf {sShop} nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist.\r\n \r\nBitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.\r\n \r\nVielen Dank für Ihre Unterstützung.'
        );

        $this->updateTemplate(
            'sCANCELEDVOUCHER',
            'Lieber Kunde,\r\n \r\nSie haben vor kurzem Ihre Bestellung bei {sShop} nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen 5,00 € Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung bei {sShop} erleichtern.\r\n \r\nIhr Gutschein ist 2 Monate gültig und kann mit dem Code \"{$sVouchercode}\" eingelöst werden.\r\n\r\nWir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL11',
            'Sehr geehrte{if $sUser.billing_salutation eq \"mr\"}r Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nDer Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\" %d-%m-%Y\"} hat sich geändert. \n\nDer neue Status lautet nun {$sOrder.status_description}.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL5',
            'Sehr geehrte{if $sUser.billing_salutation eq \"mr\"}r Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} \n{$sUser.billing_firstname} {$sUser.billing_lastname},\n \nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\" %d.%m.%Y\"} \nhat sich geändert. Der neun Staus lautet nun {$sOrder.status_description}.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL8',
            'Hallo {if $sUser.billing_salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL3',
            'Sehr geehrte{if $sUser.billing_salutation eq \"mr\"}r Herr{elseif $sUser.billing_salutation eq \"ms\"} Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nDer Status Ihrer Bestellung mit der Bestellnummer {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:\" %d.%m.%Y\"} \nhat sich geändert. Der neue Staus lautet nun \"{$sOrder.status_description}\".\n \n \nInformationen zu Ihrer Bestellung:\n================================== \n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:3} {$details.articleordernumber|fill:10:\" \":\"...\"} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:\"%.2f\"} {$sConfig.sCURRENCY}\n{/foreach}\n \nVersandkosten: {$sOrder.invoice_shipping} {$sConfig.sCURRENCY}\nNetto-Gesamt: {$sOrder.invoice_amount_net|string_format:\"%.2f\"} {$sConfig.sCURRENCY}\nGesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:\"%.2f\"} {$sConfig.sCURRENCY}'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL4',
            'Hallo {if $sUser.billing_salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.'
        );

        $this->updateTemplate(
            'sORDERSTATEMAIL6',
            'Hallo {if $sUser.billing_salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n \nder Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} hat sich geändert!\nDie Bestellung hat jetzt den Status: {$sOrder.status_description}.\n\nDen aktuellen Status Ihrer Bestellung  können Sie  auch jederzeit auf unserer Webseite im  Bereich \"Mein Konto\" - \"Meine Bestellungen\" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.'
        );

        $this->updateTemplate(
            'sBIRTHDAY',
            'Hallo {if $sUser.salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.firstname} {$sUser.lastname},\n\nwir wünschen Ihnen alles Gute zum Geburtstag.'
        );
        $this->updateTemplate(
            'sARTICLESTOCK',
            'Hallo,\n\nfolgende Artikel haben den Mindestbestand unterschritten:\n\nBestellnummer Artikelname Bestand/Mindestbestand \n{foreach from=$sJob.articles item=sArticle key=key}\n{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin} \n{/foreach}\n'
        );

        $this->updateTemplate(
            'sNEWSLETTERCONFIRMATION',
            'Hallo,\n\nvielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.'
        );

        $this->updateTemplate(
            'sOPTINNEWSLETTER',
            'Hallo, \n\nvielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter. \n\nBitte bestätigen Sie die Anmeldung über den nachfolgenden Link: {$sConfirmLink}'
        );

        $this->updateTemplate(
            'sOPTINVOTE',
            'Hallo, \n\nvielen Dank für die Bewertung des Artikels {$sArticle.articleName}. \n\nBitte bestätigen Sie die Bewertung über nach den nachfolgenden Link: {$sConfirmLink}'
        );

        $this->updateTemplate(
            'sARTICLEAVAILABLE',
            'Hallo,\n\nIhr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.\n\n{$sArticleLink}'
        );

        $this->updateTemplate(
            'sACCEPTNOTIFICATION',
            'Hallo,\n\nvielen Dank, dass Sie sich für die automatische E-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.\n\nBitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link: \n\n{$sConfirmLink}'
        );

        $this->updateTemplate(
            'sARTICLECOMMENT',
            'Hallo {if $sUser.salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\n\nSie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>\nSo helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.\n\n\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\n\n{foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n{$sArticle.articleordernumber} {$sArticle.name} {$sArticle.link}\n{/if}\n{/foreach}',
            '<div>\nHallo {if $sUser.salutation eq \"mr\"}Herr{elseif $sUser.billing_salutation eq \"ms\"}Frau{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n<br/>\nSie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>\nSo helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.\n<br/><br/>\n\nHier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.\n\n<table>\n {foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n <tr>\n  <td>{$sArticle.articleordernumber}</td>\n  <td>{$sArticle.name}</td>\n  <td>\n  <a href=\"{$sArticle.link}\">link</a>\n  </td>\n </tr>\n{/if}\n {/foreach}\n</table></div>'
        );

        $this->updateTemplate(
            'sORDERSEPAAUTHORIZATION',
            'Hallo {$paymentInstance.firstName} {$paymentInstance.lastName}, im Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.',
            'Hallo {$paymentInstance.firstName} {$paymentInstance.lastName}, im Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.'
        );
    }

    private function updateTemplate($name, $content, $contentHtml = '')
    {
        $headerPlain = '{include file=\"string:{config name=emailheaderplain}\"}';
        $headerHtml = '{include file=\"string:{config name=emailheaderhtml}\"}';
        $footerPlain = '{include file=\"string:{config name=emailfooterplain}\"}';
        $footerHtml = '{include file=\"string:{config name=emailfooterhtml}\"}';

        $content = sprintf(
            '%s\r\n\r\n%s\r\n\r\n%s',
            $headerPlain,
            $content,
            $footerPlain
        );

        if (empty($contentHtml)) {
            $sql = <<<EOD
UPDATE `s_core_config_mails` SET `content` = "$content" WHERE `name` = "$name"
EOD;
            $this->addSql($sql);
            return;
        }

        $contentHtml = sprintf(
            '%s\r\n\r\n%s\r\n\r\n%s',
            $headerHtml,
            $contentHtml,
            $footerHtml
        );

        $sql = <<<EOD
UPDATE `s_core_config_mails` SET `content` = "$content", `contentHTML` = "$contentHtml" WHERE `name` = "$name"
EOD;
        $this->addSql($sql);
    }
}