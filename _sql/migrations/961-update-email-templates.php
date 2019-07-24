<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

class Migrations_Migration961 extends Shopware\Components\Migrations\AbstractMigration
{
    public function up($modus)
    {
        $this->updateEmailFooterPlain();
        $this->updateEmailFooterHtml();
        $this->fixEmailDirtyFlag();
        $this->updateEmailTemplates();
    }

    private function updateEmailFooterPlain()
    {
        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET `value` = 's:63:"
Mit freundlichen Grüßen

Ihr Team von {config name=shopName}";' WHERE `name` = 'emailfooterplain' AND `value` = 's:64:"
Mit freundlichen Grüßen,

Ihr Team von {config name=shopName}";' AND `label` = 'E-Mail Footer Plaintext' AND ISNULL(`description`) AND `type` = 'textarea' AND `required` = 0 AND `position` = 0 AND `scope` = 1 AND ISNULL(`options`);
EOD;
        $this->addSql($sql);
    }

    private function updateEmailFooterHtml()
    {
        $sql = <<<'EOD'
UPDATE `s_core_config_elements` SET `value` = 's:84:"<br/>
Mit freundlichen Grüßen<br/><br/>

Ihr Team von {config name=shopName}</div>";' WHERE `name` = 'emailfooterhtml' AND `value` = 's:85:"<br/>
Mit freundlichen Grüßen,<br/><br/>

Ihr Team von {config name=shopName}</div>";' AND `label` = 'E-Mail Footer HTML' AND ISNULL(`description`) AND `type` = 'textarea' AND `required` = 0 AND `position` = 0 AND `scope` = 1 AND ISNULL(`options`);
EOD;
        $this->addSql($sql);
    }

    private function fixEmailDirtyFlag()
    {
        $sql = <<<'EOD'
UPDATE `s_core_config_mails` SET `dirty` = 0 WHERE `stateId` = 2 AND `name` = 'sORDERSTATEMAIL2' AND `frommail` = '{config name=mail}' AND `fromname` = '{config name=shopName}' AND `subject` = 'Statusänderung zur Bestellung bei {config name=shopName}' AND `content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_firstname} {$sUser.billing_lastname},

Der Status Ihrer Bestellung mit der Bestellnummer: {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:" %d-%m-%Y"} hat sich geändert. Der neue Status lautet nun {$sOrder.status_description}.

{include file="string:{config name=emailfooterplain}"}' AND `contentHTML` = '' AND `ishtml` = 0 AND `attachment` = '';
EOD;
        $this->addSql($sql);
    }

    private function updateEmailTemplates()
    {
        //German
        $sql = <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihre Anmeldung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}
        
Hallo {$salutation|salutation} {$lastname},

vielen Dank für Ihre Anmeldung in unserem Shop.
Sie erhalten Zugriff über Ihre E-Mail-Adresse {$sMAIL} und dem von Ihnen gewählten Kennwort.
Sie können Ihr Kennwort jederzeit nachträglich ändern.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
            {include file="string:{config name=emailheaderhtml}"}
            <br/><br/>
            <p>
                Hallo {$salutation|salutation} {$lastname},<br/>
                <br/>
                vielen Dank für Ihre Anmeldung in unserem Shop.<br/>
                Sie erhalten Zugriff über Ihre E-Mail-Adresse <strong>{$sMAIL}</strong> und dem von Ihnen gewählten Kennwort.<br/>
                Sie können Ihr Kennwort jederzeit nachträglich ändern.
            </p>
            {include file="string:{config name=emailfooterhtml}"}
        </div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:14:{s:5:"sMAIL";s:14:"xy@example.org";s:7:"sConfig";a:0:{}s:6:"street";s:15:"Musterstraße 1";s:7:"zipcode";s:5:"12345";s:4:"city";s:11:"Musterstadt";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:4:"Herr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:11:"accountmode";s:1:"0";s:5:"email";s:14:"xy@example.org";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}' WHERE `s_core_config_mails`.`name` = 'sREGISTERCONFIRMATION' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihre Anmeldung in unserem Shop%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihre Bestellung im {config name=shopName}',
        `content` = '{include file="string:{config name=emailheaderplain}"}
        
Hallo {$billingaddress.salutation|salutation} {$billingaddress.lastname},

vielen Dank für Ihre Bestellung im {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.
Informationen zu Ihrer Bestellung:

Pos.  Art.Nr.               Beschreibung                                      Menge       Preis       Summe
{foreach item=details key=position from=$sOrderDetails}
{{$position+1}|fill:4}  {$details.ordernumber|fill:20}  {$details.articlename|fill:49}  {$details.quantity|fill:6}  {$details.price|padding:8|currency|unescape:"htmlall"}      {$details.amount|padding:8|currency|unescape:"htmlall"}
{/foreach}

Versandkosten: {$sShippingCosts|currency|unescape:"htmlall"}
Gesamtkosten Netto: {$sAmountNet|currency|unescape:"htmlall"}
{if !$sNet}
{foreach $sTaxRates as $rate => $value}
zzgl. {$rate|number_format:0}% MwSt. {$value|currency|unescape:"htmlall"}
{/foreach}
Gesamtkosten Brutto: {$sAmount|currency|unescape:"htmlall"}
{/if}

Gewählte Zahlungsart: {$additional.payment.description}
{$additional.payment.additionaldescription}
{if $additional.payment.name == "debit"}
Ihre Bankverbindung:
Kontonr: {$sPaymentTable.account}
BLZ: {$sPaymentTable.bankcode}
Institut: {$sPaymentTable.bankname}
Kontoinhaber: {$sPaymentTable.bankholder}

Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.
{/if}
{if $additional.payment.name == "prepayment"}

Unsere Bankverbindung:
Konto: ###
BLZ: ###
{/if}


Gewählte Versandart: {$sDispatch.name}
{$sDispatch.description}

{if $sComment}
Ihr Kommentar:
{$sComment}
{/if}

Rechnungsadresse:
{$billingaddress.company}
{$billingaddress.firstname} {$billingaddress.lastname}
{$billingaddress.street} {$billingaddress.streetnumber}
{if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}\n
{$additional.country.countryname}

Lieferadresse:
{$shippingaddress.company}
{$shippingaddress.firstname} {$shippingaddress.lastname}
{$shippingaddress.street} {$shippingaddress.streetnumber}
{if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}\n
{$additional.countryShipping.countryname}

{if $billingaddress.ustid}
Ihre Umsatzsteuer-ID: {$billingaddress.ustid}
Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland
bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.
{/if}


Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',
        
        `contentHTML` = '<div style="font-family:arial; font-size:12px;">
            {include file="string:{config name=emailheaderhtml}"}
            <br/><br/>
            <p>Hallo {$billingaddress.salutation|salutation} {$billingaddress.lastname},<br/>
                <br/>
                vielen Dank für Ihre Bestellung bei {config name=shopName} (Nummer: {$sOrderNumber}) am {$sOrderDay} um {$sOrderTime}.<br/>
                <br/>
                <strong>Informationen zu Ihrer Bestellung:</strong></p><br/>
            <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
                <tr>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Bezeichnung</td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>
                    <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Summe</strong></td>
                </tr>

                {foreach item=details key=position from=$sOrderDetails}
                <tr>
                    <td style="border-bottom:1px solid #cccccc;">{$position+1|fill:4} </td>
                    <td style="border-bottom:1px solid #cccccc;">{if $details.image.src.0 && $details.modus == 0}<img style="height: 57px;" height="57" src="{$details.image.src.0}" alt="{$details.articlename}" />{else} {/if}</td>
                    <td style="border-bottom:1px solid #cccccc;">
                      {$details.articlename|wordwrap:80|indent:4}<br>
                      Artikel-Nr: {$details.ordernumber|fill:20}
                    </td>
                    <td style="border-bottom:1px solid #cccccc;">{$details.quantity|fill:6}</td>
                    <td style="border-bottom:1px solid #cccccc;">{$details.price|padding:8|currency}</td>
                    <td style="border-bottom:1px solid #cccccc;">{$details.amount|padding:8|currency}</td>
                </tr>
                {/foreach}

            </table>
        
            <p>
                <br/>
                <br/>
                Versandkosten: {$sShippingCosts|currency}<br/>
                Gesamtkosten Netto: {$sAmountNet|currency}<br/>
                {if !$sNet}
                {foreach $sTaxRates as $rate => $value}
                zzgl. {$rate|number_format:0}% MwSt. {$value|currency}<br/>
                {/foreach}
                <strong>Gesamtkosten Brutto: {$sAmount|currency}</strong><br/>
                {/if}
                <br/>
                <br/>
                <strong>Gewählte Zahlungsart:</strong> {$additional.payment.description}<br/>
                {$additional.payment.additionaldescription}
                {if $additional.payment.name == "debit"}
                Ihre Bankverbindung:<br/>
                Kontonr: {$sPaymentTable.account}<br/>
                BLZ: {$sPaymentTable.bankcode}<br/>
                Institut: {$sPaymentTable.bankname}<br/>
                Kontoinhaber: {$sPaymentTable.bankholder}<br/>
                <br/>
                Wir ziehen den Betrag in den nächsten Tagen von Ihrem Konto ein.<br/>
                {/if}
                <br/>
                <br/>
                {if $additional.payment.name == "prepayment"}
                Unsere Bankverbindung:<br/>
                Konto: ###<br/>
                BLZ: ###<br/>
                {/if}
                <br/>
                <br/>
                <strong>Gewählte Versandart:</strong> {$sDispatch.name}<br/>
                {$sDispatch.description}<br/>
            </p>
            <p>
                {if $sComment}
                <strong>Ihr Kommentar:</strong><br/>
                {$sComment}<br/>
                {/if}
                <br/>
                <br/>
                <strong>Rechnungsadresse:</strong><br/>
                {$billingaddress.company}<br/>
                {$billingaddress.firstname} {$billingaddress.lastname}<br/>
                {$billingaddress.street} {$billingaddress.streetnumber}<br/>
                {if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}<br/>
                {$additional.country.countryname}<br/>
                <br/>
                <br/>
                <strong>Lieferadresse:</strong><br/>
                {$shippingaddress.company}<br/>
                {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>
                {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>
                {if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}<br/>
                {$additional.countryShipping.countryname}<br/>
                <br/>
                {if $billingaddress.ustid}
                Ihre Umsatzsteuer-ID: {$billingaddress.ustid}<br/>
                Bei erfolgreicher Prüfung und sofern Sie aus dem EU-Ausland<br/>
                bestellen, erhalten Sie Ihre Ware umsatzsteuerbefreit.<br/>
                {/if}
                <br/>
                <br/>
                Für Rückfragen stehen wir Ihnen jederzeit gerne zur Verfügung.<br/>
                {include file="string:{config name=emailfooterhtml}"}
            </p>
        </div>',
        `ishtml` = 1,
        `attachment` = '',
        `mailtype` = 2,
        `context` = 'a:22:{s:13:"sOrderDetails";a:2:{i:0;a:54:{s:2:"id";s:3:"670";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:6:"userID";s:1:"0";s:11:"articlename";s:11:"ELASTIC CAP";s:9:"articleID";s:3:"152";s:11:"ordernumber";s:7:"SW10153";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:5:"29,95";s:8:"netprice";s:15:"25.168067226891";s:8:"tax_rate";s:2:"19";s:5:"datum";s:19:"2017-08-07 14:09:12";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:8:"register";s:9:"useragent";s:76:"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:54.0) Gecko/20100101 Firefox/54.0";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"packunit";s:0:"";s:12:"mainDetailId";s:3:"707";s:15:"articleDetailId";s:3:"708";s:11:"minpurchase";s:1:"1";s:5:"taxID";s:1:"1";s:7:"instock";s:2:"12";s:14:"suppliernumber";s:0:"";s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:9:"laststock";s:1:"0";s:12:"shippingtime";s:0:"";s:11:"releasedate";N;s:12:"sReleaseDate";N;s:3:"ean";s:0:"";s:8:"stockmin";s:1:"0";s:8:"ob_attr1";s:0:"";s:8:"ob_attr2";N;s:8:"ob_attr3";N;s:8:"ob_attr4";N;s:8:"ob_attr5";N;s:8:"ob_attr6";N;s:12:"shippinginfo";b:1;s:3:"esd";s:1:"0";s:18:"additional_details";a:94:{s:9:"articleID";i:152;s:16:"articleDetailsID";i:708;s:11:"ordernumber";s:9:"SW10152.1";s:9:"highlight";b:0;s:11:"description";s:0:"";s:16:"description_long";s:2404:"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p><p>Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue.</p>  <p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p> <p>Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla.</p> <p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede. Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi.</p>";s:3:"esd";b:0;s:11:"articleName";s:23:"WINDSTOPPER MÜTZE WARM";s:5:"taxID";i:1;s:3:"tax";i:19;s:7:"instock";i:12;s:11:"isAvailable";b:1;s:6:"weight";i:0;s:12:"shippingtime";N;s:16:"pricegroupActive";b:0;s:12:"pricegroupID";N;s:6:"length";i:0;s:6:"height";i:0;s:5:"width";i:0;s:9:"laststock";b:0;s:14:"additionaltext";s:0:"";s:5:"datum";s:10:"2015-02-05";s:5:"sales";i:0;s:13:"filtergroupID";i:8;s:17:"priceStartingFrom";N;s:18:"pseudopricePercent";N;s:15:"sVariantArticle";N;s:13:"sConfigurator";b:1;s:9:"metaTitle";s:0:"";s:12:"shippingfree";b:0;s:14:"suppliernumber";s:0:"";s:12:"notification";b:0;s:3:"ean";s:0:"";s:8:"keywords";s:0:"";s:12:"sReleasedate";s:0:"";s:8:"template";s:0:"";s:10:"attributes";a:2:{s:4:"core";a:23:{s:2:"id";s:3:"720";s:9:"articleID";s:3:"152";s:16:"articledetailsID";s:3:"708";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";}s:9:"marketing";a:4:{s:5:"isNew";b:0;s:11:"isTopSeller";b:0;s:10:"comingSoon";b:0;s:7:"storage";a:0:{}}}s:17:"allowBuyInListing";b:0;s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:12:"supplierName";s:8:"LÖFFLER";s:11:"supplierImg";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:10:"supplierID";i:10;s:19:"supplierDescription";s:1267:"<p>L&Ouml;FFLER ist anders. Denn anders als die meisten Mitbewerber hat sich L&Ouml;FFLER schon Anfang der 1990er Jahre entschieden, auch weiterhin in &Ouml;sterreich zu produzieren. Nat&uuml;rlich nach h&ouml;chsten ethischen und &ouml;kologischen Standards, wie sie nur in &Ouml;sterreich bzw. in der Europ&auml;ischen Union gelten. Mit gut ausgebildeten, kompetenten und motivierten Mitarbeiterinnen und Mitarbeitern.</p>  <p>Viele Sportswear-Konzerne haben im Streben nach h&ouml;chsten Gewinnmargen ihre Fertigung l&auml;ngst in Billiglohnl&auml;nder verlagert. Miserable Arbeitsbedingungen, Hungerl&ouml;hne und Kinderarbeit sind dort immer wieder an der Tagesordnung. H&ouml;chst fragw&uuml;rdig sind auch die Umweltzerst&ouml;rung durch r&uuml;cksichtslose Produktionsmethoden und die hohe Schadstoffbelastung der auf diese Weise hergestellten Textilien.</p> <p>70 Prozent aller Stoffe, die L&Ouml;FFLER verarbeitet, kommen aus der eigenen Strickerei in Ried im Innkreis. Das ist einzigartig - und eine wichtige Grundlage f&uuml;r die herausragende Qualit&auml;t, die Fair Sportswear von L&Ouml;FFLER auszeichnet.</p> <p>Weitere Informationen zu dem Hersteller finden Sie <a title="www.loeffler.at" href="http://www.loeffler.at/" target="_blank">hier</a>.</p>";s:19:"supplier_attributes";a:0:{}s:10:"newArticle";b:0;s:9:"sUpcoming";b:0;s:9:"topseller";b:0;s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"29,95";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";N;s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:29.949999999999999;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}s:5:"image";a:12:{s:2:"id";i:366;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:0:"";s:9:"extension";s:3:"jpg";s:4:"main";b:0;s:8:"parentId";N;s:5:"width";i:1492;s:6:"height";i:1500;s:10:"thumbnails";a:3:{i:0;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"200";s:9:"maxHeight";s:3:"200";s:10:"attributes";a:0:{}}i:1;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"600";s:9:"maxHeight";s:3:"600";s:10:"attributes";a:0:{}}i:2;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:4:"1280";s:9:"maxHeight";s:4:"1280";s:10:"attributes";a:0:{}}}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}s:6:"prices";a:1:{i:0;a:22:{s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"29,95";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";s:1:"0";s:18:"pseudopricePercent";N;s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:29.949999999999999;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}}}s:10:"linkBasket";s:42:"shopware.php?sViewport=basket&sAdd=SW10153";s:11:"linkDetails";s:42:"shopware.php?sViewport=detail&sArticle=152";s:11:"linkVariant";s:57:"shopware.php?sViewport=detail&sArticle=152&number=SW10153";s:11:"sProperties";a:3:{i:1;a:11:{s:2:"id";i:1;s:8:"optionID";i:1;s:4:"name";s:10:"Artikeltyp";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:16:"Bildkonfigurator";s:6:"values";a:1:{i:4;s:16:"Bildkonfigurator";}s:12:"isFilterable";b:1;s:7:"options";a:1:{i:0;a:3:{s:2:"id";i:4;s:4:"name";s:16:"Bildkonfigurator";s:10:"attributes";a:0:{}}}s:5:"media";a:0:{}s:10:"attributes";a:0:{}}i:3;a:11:{s:2:"id";i:3;s:8:"optionID";i:3;s:4:"name";s:8:"Material";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:20:"Polyester, Baumwolle";s:6:"values";a:2:{i:108;s:9:"Polyester";i:163;s:9:"Baumwolle";}s:12:"isFilterable";b:1;s:7:"options";a:2:{i:0;a:3:{s:2:"id";i:108;s:4:"name";s:9:"Polyester";s:10:"attributes";a:0:{}}i:1;a:3:{s:2:"id";i:163;s:4:"name";s:9:"Baumwolle";s:10:"attributes";a:0:{}}}s:5:"media";a:0:{}s:10:"attributes";a:0:{}}i:18;a:11:{s:2:"id";i:18;s:8:"optionID";i:18;s:4:"name";s:5:"Farbe";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:12:"Rot, Schwarz";s:6:"values";a:2:{i:166;s:3:"Rot";i:155;s:7:"Schwarz";}s:12:"isFilterable";b:1;s:7:"options";a:2:{i:0;a:3:{s:2:"id";i:166;s:4:"name";s:3:"Rot";s:10:"attributes";a:0:{}}i:1;a:3:{s:2:"id";i:155;s:4:"name";s:7:"Schwarz";s:10:"attributes";a:0:{}}}s:5:"media";a:2:{i:166;a:13:{s:7:"valueId";i:166;s:2:"id";i:355;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:3:"rot";s:9:"extension";s:3:"jpg";s:4:"main";N;s:8:"parentId";N;s:5:"width";i:40;s:6:"height";i:40;s:10:"thumbnails";a:0:{}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}i:155;a:13:{s:7:"valueId";i:155;s:2:"id";i:357;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:7:"schwarz";s:9:"extension";s:3:"jpg";s:4:"main";N;s:8:"parentId";N;s:5:"width";i:40;s:6:"height";i:40;s:10:"thumbnails";a:0:{}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}}s:10:"attributes";a:0:{}}}s:10:"properties";s:106:"Artikeltyp:&nbsp;Bildkonfigurator,&nbsp;Material:&nbsp;Polyester, Baumwolle,&nbsp;Farbe:&nbsp;Rot, Schwarz";}s:6:"amount";s:5:"29,95";s:9:"amountnet";s:5:"25,17";s:12:"priceNumeric";s:5:"29.95";s:5:"image";a:15:{s:2:"id";i:366;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:0:"";s:9:"extension";s:3:"jpg";s:4:"main";b:0;s:8:"parentId";N;s:5:"width";i:1492;s:6:"height";i:1500;s:10:"thumbnails";a:3:{i:0;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"200";s:9:"maxHeight";s:3:"200";s:10:"attributes";a:0:{}}i:1;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"600";s:9:"maxHeight";s:3:"600";s:10:"attributes";a:0:{}}i:2;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:4:"1280";s:9:"maxHeight";s:4:"1280";s:10:"attributes";a:0:{}}}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}s:3:"src";a:4:{s:8:"original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:0;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:1;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:2;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}s:5:"srchd";a:4:{s:8:"original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:0;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:1;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:2;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}s:3:"res";a:1:{s:8:"original";a:2:{s:5:"width";i:1500;s:6:"height";i:1492;}}}s:11:"linkDetails";s:42:"shopware.php?sViewport=detail&sArticle=152";s:10:"linkDelete";s:41:"shopware.php?sViewport=basket&sDelete=670";s:8:"linkNote";s:40:"shopware.php?sViewport=note&sAdd=SW10153";s:3:"tax";s:4:"4,78";s:13:"orderDetailId";s:3:"208";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:52:{s:2:"id";s:3:"673";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:6:"userID";s:1:"0";s:11:"articlename";s:15:"Warenkorbrabatt";s:9:"articleID";s:1:"0";s:11:"ordernumber";s:16:"SHIPPINGDISCOUNT";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:5:"-2,00";s:8:"netprice";s:5:"-1.68";s:8:"tax_rate";s:2:"19";s:5:"datum";s:19:"2017-08-07 14:09:20";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:0:"";s:9:"useragent";s:0:"";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"packunit";N;s:12:"mainDetailId";N;s:15:"articleDetailId";N;s:11:"minpurchase";i:1;s:5:"taxID";N;s:7:"instock";N;s:14:"suppliernumber";N;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:9:"laststock";N;s:12:"shippingtime";N;s:11:"releasedate";N;s:12:"sReleaseDate";N;s:3:"ean";N;s:8:"stockmin";N;s:8:"ob_attr1";N;s:8:"ob_attr2";N;s:8:"ob_attr3";N;s:8:"ob_attr4";N;s:8:"ob_attr5";N;s:8:"ob_attr6";N;s:12:"shippinginfo";b:0;s:3:"esd";s:1:"0";s:6:"amount";s:5:"-2,00";s:9:"amountnet";s:5:"-1,68";s:12:"priceNumeric";s:2:"-2";s:11:"linkDetails";s:40:"shopware.php?sViewport=detail&sArticle=0";s:10:"linkDelete";s:41:"shopware.php?sViewport=basket&sDelete=673";s:8:"linkNote";s:49:"shopware.php?sViewport=note&sAdd=SHIPPINGDISCOUNT";s:3:"tax";s:5:"-0,32";s:13:"orderDetailId";s:3:"209";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:14:"billingaddress";a:26:{s:2:"id";s:1:"5";s:7:"company";s:0:"";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:5:"title";s:0:"";s:8:"lastname";s:10:"Mustermann";s:6:"street";s:15:"Musterstraße 1";s:7:"zipcode";s:5:"12345";s:4:"city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:5:"vatId";s:0:"";s:22:"additionalAddressLine1";s:0:"";s:22:"additionalAddressLine2";s:0:"";s:9:"countryId";s:1:"2";s:7:"stateId";s:0:"";s:8:"customer";N;s:7:"country";N;s:5:"state";s:0:"";s:6:"userID";s:1:"3";s:9:"countryID";s:1:"2";s:7:"stateID";s:0:"";s:5:"ustid";s:0:"";s:24:"additional_address_line1";s:0:"";s:24:"additional_address_line2";s:0:"";s:10:"attributes";N;}s:15:"shippingaddress";a:26:{s:2:"id";s:1:"5";s:7:"company";s:0:"";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:5:"title";s:0:"";s:8:"lastname";s:10:"Mustermann";s:6:"street";s:15:"Musterstraße 1";s:7:"zipcode";s:5:"12345";s:4:"city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:5:"vatId";s:0:"";s:22:"additionalAddressLine1";s:0:"";s:22:"additionalAddressLine2";s:0:"";s:9:"countryId";s:1:"2";s:7:"stateId";s:0:"";s:8:"customer";N;s:7:"country";N;s:5:"state";s:0:"";s:6:"userID";s:1:"3";s:9:"countryID";s:1:"2";s:7:"stateID";s:0:"";s:5:"ustid";s:0:"";s:24:"additional_address_line1";s:0:"";s:24:"additional_address_line2";s:0:"";s:10:"attributes";N;}s:10:"additional";a:8:{s:7:"country";a:15:{s:2:"id";s:1:"2";s:11:"countryname";s:11:"Deutschland";s:10:"countryiso";s:2:"DE";s:6:"areaID";s:1:"1";s:9:"countryen";s:7:"GERMANY";s:8:"position";s:1:"1";s:6:"notice";s:0:"";s:7:"taxfree";s:1:"0";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"DEU";s:29:"display_state_in_registration";s:1:"0";s:27:"force_state_in_registration";s:1:"0";s:11:"countryarea";s:11:"deutschland";}s:5:"state";a:0:{}s:4:"user";a:33:{s:2:"id";s:1:"3";s:6:"userID";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:20";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";i:0;s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:14:"customernumber";s:5:"20005";s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";}s:15:"countryShipping";a:15:{s:2:"id";s:1:"2";s:11:"countryname";s:11:"Deutschland";s:10:"countryiso";s:2:"DE";s:6:"areaID";s:1:"1";s:9:"countryen";s:7:"GERMANY";s:8:"position";s:1:"1";s:6:"notice";s:0:"";s:7:"taxfree";s:1:"0";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"DEU";s:29:"display_state_in_registration";s:1:"0";s:27:"force_state_in_registration";s:1:"0";s:11:"countryarea";s:11:"deutschland";}s:13:"stateShipping";a:0:{}s:7:"payment";a:21:{s:2:"id";s:1:"5";s:4:"name";s:10:"prepayment";s:11:"description";s:8:"Vorkasse";s:8:"template";s:14:"prepayment.tpl";s:5:"class";s:14:"prepayment.php";s:5:"table";s:0:"";s:4:"hide";s:1:"0";s:21:"additionaldescription";s:108:"Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.";s:13:"debit_percent";s:1:"0";s:9:"surcharge";s:1:"0";s:15:"surchargestring";s:0:"";s:8:"position";s:1:"1";s:6:"active";s:1:"1";s:9:"esdactive";s:1:"0";s:11:"embediframe";s:0:"";s:12:"hideprospect";s:1:"0";s:6:"action";N;s:8:"pluginID";N;s:6:"source";N;s:15:"mobile_inactive";s:1:"0";s:10:"validation";a:0:{}}s:10:"charge_vat";b:1;s:8:"show_net";b:1;}s:9:"sTaxRates";a:1:{s:5:"19.00";d:5.0800000000000001;}s:14:"sShippingCosts";s:8:"3,90 EUR";s:7:"sAmount";s:9:"31,85 EUR";s:14:"sAmountNumeric";d:31.850000000000001;s:10:"sAmountNet";s:9:"26,77 EUR";s:17:"sAmountNetNumeric";d:26.77;s:12:"sOrderNumber";i:20003;s:9:"sOrderDay";s:10:"07.08.2017";s:10:"sOrderTime";s:5:"14:09";s:8:"sComment";s:0:"";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}s:9:"sCurrency";s:3:"EUR";s:9:"sLanguage";i:1;s:8:"sSubShop";i:1;s:4:"sEsd";N;s:4:"sNet";b:0;s:13:"sPaymentTable";a:0:{}s:9:"sDispatch";a:10:{s:2:"id";s:1:"9";s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";s:11:"calculation";s:1:"1";s:11:"status_link";s:0:"";s:21:"surcharge_calculation";s:1:"3";s:17:"bind_shippingfree";s:1:"0";s:12:"shippingfree";N;s:15:"tax_calculation";s:1:"0";s:21:"tax_calculation_value";N;}}' 
        WHERE `s_core_config_mails`.`name` = 'sORDER' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Informationen zu Ihrer Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '{$sName} empfiehlt Ihnen {$sArticle}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

{$sName} hat für Sie bei {$sShop} ein interessantes Produkt gefunden, das Sie sich anschauen sollten:

{$sArticle}
{$sLink}

{$sComment}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        {$sName} hat für Sie bei {$sShop} ein interessantes Produkt gefunden, das Sie sich anschauen sollten:<br/>
        <br/>
        <strong><a href="{$sLink}">{$sArticle}</a></strong><br/>
    </p>
    {if $sComment}
        <div style="border: 2px solid black; border-radius: 5px; padding: 5px;"><p>{$sComment}</p></div><br/>
    {/if}
    
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:4:{s:5:"sName";s:11:"Peter Meyer";s:8:"sArticle";s:10:"Blumenvase";s:5:"sLink";s:31:"http://shopware.example/test123";s:8:"sComment";s:36:"Hey Peter - das musst du dir ansehen";}'
WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%dass Sie sich anschauen sollten%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Achtung - keine freien Seriennummern für {$sArticleName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

es sind keine weiteren freien Seriennummern für den Artikel

{$sArticleName}

verfügbar. Bitte stelle umgehend neue Seriennummern ein oder deaktiviere den Artikel.
Außerdem weise dem Kunden {$sMail} bitte manuell eine Seriennummer zu.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        es sind keine weiteren freien Seriennummern für den Artikel<br/>
    </p>
    <strong>{$sArticleName}</strong><br/>
    <p>
        verfügbar. Bitte stelle umgehend neue Seriennummern ein oder deaktiviere den Artikel.<br/>
        Außerdem weise dem Kunden {$sMail} bitte manuell eine Seriennummer zu.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:2:{s:12:"sArticleName";s:20:"ESD Download Artikel";s:5:"sMail";s:23:"max.mustermann@mail.com";}' WHERE `s_core_config_mails`.`name` = 'sNOSERIALS' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%keine weiteren freien Seriennummern%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihr Gutschein',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$customer},

{$user} ist Ihrer Empfehlung gefolgt und hat soeben bei {$sShop} bestellt.
Wir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.

Ihr Gutschein-Code lautet: XXX

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$customer},<br/>
        <br/>
        {$user} ist Ihrer Empfehlung gefolgt und hat soeben bei {$sShop} bestellt.<br/>
        Wir schenken Ihnen deshalb einen X € Gutschein, den Sie bei Ihrer nächsten Bestellung einlösen können.<br/>
        <br/>
        <strong>Ihr Gutschein-Code lautet: XXX</strong>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:2:{s:8:"customer";s:11:"Peter Meyer";s:4:"user";s:11:"Hans Maiser";}'
WHERE `s_core_config_mails`.`name` = 'sVOUCHER' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%ist Ihrer Empfehlung gefolgt%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihr Händleraccount wurde freigeschaltet',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

Ihr Händleraccount bei {$sShop} wurde freigeschaltet.
Ab sofort kaufen Sie zum Netto-EK bei uns ein.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        Ihr Händleraccount bei {$sShop} wurde freigeschaltet.<br/>
        Ab sofort kaufen Sie zum Netto-EK bei uns ein.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = NULL
WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHACCEPTED' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ab sofort kaufen Sie zum Netto-EK bei uns ein%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihr Händleraccount wurde abgelehnt',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.
Bei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für Ihr Interesse an unseren Fachhandelspreisen. Leider liegt uns aber noch kein Gewerbenachweis vor bzw. leider können wir Sie nicht als Fachhändler anerkennen.<br/>
        Bei Rückfragen aller Art können Sie uns gerne telefonisch, per Fax oder per Mail diesbezüglich erreichen.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = NULL WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHREJECTED' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Leider liegt uns aber noch kein Gewerbenachweis vor%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihre abgebrochene Bestellung - Jetzt Feedback geben und Gutschein kassieren',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,
 
Sie haben vor kurzem Ihre Bestellung auf {$sShop} nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist. Bitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.
Vielen Dank für Ihre Unterstützung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        Sie haben vor kurzem Ihre Bestellung auf {$sShop} nicht bis zum Ende durchgeführt - wir sind stets bemüht unseren Kunden das Einkaufen in unserem Shop so angenehm wie möglich zu machen und würden deshalb gerne wissen, woran Ihr Einkauf bei uns gescheitert ist. Bitte lassen Sie uns doch den Grund für Ihren Bestellabbruch zukommen, Ihren Aufwand entschädigen wir Ihnen in jedem Fall mit einem 5,00 € Gutschein.<br/>
        Vielen Dank für Ihre Unterstützung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = NULL WHERE `s_core_config_mails`.`name` = 'sCANCELEDQUESTION' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihren Bestellabbruch zukommen%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihre abgebrochene Bestellung - Gutschein-Code anbei',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,
 
Sie haben vor kurzem Ihre Bestellung bei {$sShop} nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen {if $sVoucherpercental == "1"}{$sVouchervalue} %{else}{$sVouchervalue|currency|unescape:"htmlall"}{/if} Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung bei {$sShop} erleichtern. Ihr Gutschein ist 2 Monate gültig und kann mit dem Code "{$sVouchercode}" eingelöst werden. Wir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
         Hallo,<br/>
         <br/>
         Sie haben vor kurzem Ihre Bestellung bei {$sShop} nicht bis zum Ende durchgeführt - wir möchten Ihnen heute einen {if $sVoucherpercental == "1"}{$sVouchervalue} %{else}{$sVouchervalue|currency}{/if} Gutschein zukommen lassen - und Ihnen hiermit die Bestell-Entscheidung bei {$sShop} erleichtern. Ihr Gutschein ist 2 Monate gültig und kann mit dem Code "<strong>{$sVouchercode}</strong>" eingelöst werden. Wir würden uns freuen, Ihre Bestellung entgegen nehmen zu dürfen.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:5:{s:12:"sVouchercode";s:8:"23A7BCA4";s:13:"sVouchervalue";i:15;s:15:"sVouchervalidto";N;s:17:"sVouchervalidfrom";N;s:17:"sVoucherpercental";i:0;}' 

WHERE `s_core_config_mails`.`name` = 'sCANCELEDVOUCHER' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihnen hiermit die Bestell-Entscheidung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:1:"9";s:9:"clearedID";s:1:"9";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:18:"partially_invoiced";s:19:"cleared_description";s:30:"Teilweise in Rechnung gestellt";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL9' AND `s_core_config_mails`.`dirty` = 0 AND `subject` LIKE '%Statusänderung zur Bestellung bei%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"10";s:9:"clearedID";s:2:"10";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:19:"completely_invoiced";s:19:"cleared_description";s:29:"Komplett in Rechnung gestellt";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL10' AND `s_core_config_mails`.`dirty` = 0 AND `subject` LIKE '%Statusänderung zur Bestellung bei%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '1. Mahnung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

dies ist Ihre erste Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Bitte begleichen Sie schnellstmöglich Ihre Rechnung!

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        dies ist Ihre erste Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"13";s:9:"clearedID";s:2:"13";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"1st_reminder";s:19:"cleared_description";s:10:"1. Mahnung";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL13' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Inkasso der Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

Sie haben inzwischen 3 Mahnungen zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} erhalten!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Sie werden in Kürze Post von einem Inkasso Unternehmen erhalten!

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        Sie haben inzwischen 3 Mahnungen zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} erhalten!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        <strong>Sie werden in Kürze Post von einem Inkasso Unternehmen erhalten!</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"16";s:9:"clearedID";s:2:"16";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:10:"encashment";s:19:"cleared_description";s:7:"Inkasso";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL16' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '3. Mahnung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

dies ist Ihre dritte und letzte Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Bitte begleichen Sie schnellstmöglich Ihre Rechnung!

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        dies ist Ihre dritte und letzte Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"15";s:9:"clearedID";s:2:"15";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"3rd_reminder";s:19:"cleared_description";s:10:"3. Mahnung";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL15' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '2. Mahnung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

dies ist Ihre zweite Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Bitte begleichen Sie schnellstmöglich Ihre Rechnung!

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        dies ist Ihre zweite Mahnung zu Ihrer Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"}!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        <strong>Bitte begleichen Sie schnellstmöglich Ihre Rechnung!</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"14";s:9:"clearedID";s:2:"14";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"2nd_reminder";s:19:"cleared_description";s:10:"2. Mahnung";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL14' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} ist komplett bezahlt',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:16:"Komplett bezahlt";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL12' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL17' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"18";s:9:"clearedID";s:2:"18";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:8:"reserved";s:19:"cleared_description";s:10:"Reserviert";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL18' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Verzögerung der Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"19";s:9:"clearedID";s:2:"19";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:7:"delayed";s:19:"cleared_description";s:10:"Verzoegert";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL19' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Wiedergutschrift der Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"20";s:9:"clearedID";s:2:"20";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"re_crediting";s:19:"cleared_description";s:16:"Wiedergutschrift";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL20' AND `s_core_config_mails`.`dirty` = 0 AND `content` = '';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Lagerbestand von {$sData.count} Artikel{if $sData.count>1}n{/if} unter Mindestbestand ',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

folgende Artikel haben den Mindestbestand unterschritten:

Bestellnummer     Artikelname    Bestand/Mindestbestand
{foreach from=$sJob.articles item=sArticle key=key}
{$sArticle.ordernumber}       {$sArticle.name}        {$sArticle.instock}/{$sArticle.stockmin}
{/foreach}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        folgende Artikel haben den Mindestbestand unterschritten:<br/>
    </p>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Bestellnummer</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikelname</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Bestand/Mindestbestand</strong></td>
        </tr>
    
        {foreach from=$sJob.articles item=sArticle key=key}
            <tr>
                <td>{$sArticle.ordernumber}</td>
                <td>{$sArticle.name}</td>
                <td>{$sArticle.instock}/{$sArticle.stockmin}</td>
            </tr>
        {/foreach}
    </table>
    {include file="string:{config name=emailfooterhtml}"}
</div>
',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:2:{s:5:"sData";a:2:{s:5:"count";i:1;s:7:"numbers";a:1:{i:2;s:10:"SW10002841";}}s:4:"sJob";a:1:{s:8:"articles";a:1:{s:7:"SW10200";a:48:{s:11:"ordernumber";s:7:"SW10200";s:2:"id";s:3:"441";s:9:"articleID";s:3:"201";s:6:"unitID";N;s:4:"name";s:26:"Hervorgehobene Darstellung";s:11:"description";s:139:"Über diese Option lassen sich Artikel in der Storefront besonders kennzeichnen. Standardmäßig werden diese Artikel als "Tipp" angezeigt.";s:16:"description_long";s:172:"<p><span>&Uuml;ber diese Option lassen sich Artikel in der Storefront besonders kennzeichnen. Standardm&auml;&szlig;ig werden diese Artikel als "Tipp" angezeigt.</span></p>";s:12:"shippingtime";N;s:5:"added";s:10:"2012-07-16";s:9:"topseller";s:1:"1";s:8:"keywords";s:0:"";s:5:"taxID";s:1:"1";s:10:"supplierID";s:2:"14";s:7:"changed";s:19:"2012-08-30 16:17:44";s:16:"articledetailsID";s:3:"441";s:14:"suppliernumber";s:0:"";s:4:"kind";s:1:"1";s:14:"additionaltext";s:0:"";s:11:"impressions";s:1:"0";s:5:"sales";s:1:"0";s:6:"active";s:1:"1";s:7:"instock";s:1:"0";s:8:"stockmin";s:2:"96";s:6:"weight";s:5:"0.000";s:8:"position";s:1:"0";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:8:"supplier";s:7:"Example";s:4:"unit";N;s:3:"tax";s:5:"19.00";}}}}' WHERE `s_core_config_mails`.`name` = 'sARTICLESTOCK' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%folgende Artikel haben den Mindestbestand unterschritten%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Vielen Dank für Ihre Newsletter-Anmeldung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für Ihre Newsletter-Anmeldung bei {config name=shopName}.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:9:{s:27:"sUser.subscribeToNewsletter";s:1:"1";s:16:"sUser.newsletter";s:0:"";s:16:"sUser.salutation";s:4:"Herr";s:15:"sUser.firstname";s:3:"Max";s:14:"sUser.lastname";s:10:"Mustermann";s:12:"sUser.street";s:0:"";s:13:"sUser.zipcode";s:0:"";s:10:"sUser.city";s:0:"";s:15:"sUser.Speichern";s:0:"";}' WHERE `s_core_config_mails`.`name` = 'sNEWSLETTERCONFIRMATION' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihre Newsletter-Anmeldung bei%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bitte bestätigen Sie Ihre Newsletter-Anmeldung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter.
Bitte bestätigen Sie die Anmeldung über den nachfolgenden Link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für Ihre Anmeldung zu unserem regelmäßig erscheinenden Newsletter.<br/>
        Bitte bestätigen Sie die Anmeldung über den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Bestätigen</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:10:{s:12:"sConfirmLink";s:24:"http://shopware.example/";s:27:"sUser.subscribeToNewsletter";s:1:"1";s:16:"sUser.newsletter";s:0:"";s:16:"sUser.salutation";s:0:"";s:15:"sUser.firstname";s:0:"";s:14:"sUser.lastname";s:0:"";s:12:"sUser.street";s:0:"";s:13:"sUser.zipcode";s:0:"";s:10:"sUser.city";s:0:"";s:15:"sUser.Speichern";s:0:"";}' WHERE `s_core_config_mails`.`name` = 'sOPTINNEWSLETTER' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihre Anmeldung zu unserem%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bitte bestätigen Sie Ihre Artikel-Bewertung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank für die Bewertung des Artikels {$sArticle.articleName}.
Bitte bestätigen Sie die Bewertung über den nachfolgenden Link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank für die Bewertung des Artikels {$sArticle.articleName}.<br/>
        Bitte bestätigen Sie die Bewertung über nach den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Artikelbewertung bestätigen</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:2:{s:12:"sConfirmLink";s:133:"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=rating&sConfirmation=6avE5xLF22DTp8gNPaZ8KRUfJhflnvU9";s:8:"sArticle";a:1:{s:11:"articleName";s:24:"DIE ZEIT 5 Cowhide mokka";}}' WHERE `s_core_config_mails`.`name` = 'sOPTINVOTE' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%nach den nachfolgenden Link%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Ihr Artikel ist wieder verfügbar',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

Ihr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.

{$sArticleLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
   {include file="string:{config name=emailheaderhtml}"}
   <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        Ihr Artikel mit der Bestellnummer {$sOrdernumber} ist jetzt wieder verfügbar.<br/>
        <br/>
        <a href="{$sArticleLink}">{$sOrdernumber}</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:3:{s:12:"sArticleLink";s:70:"http://shopware.example/genusswelten/koestlichkeiten/272/spachtelmasse";s:12:"sOrdernumber";s:7:"SW10239";s:5:"sData";N;}' 
WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihr Artikel mit der Bestellnummer%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bitte bestätigen Sie Ihre E-Mail-Benachrichtigung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

vielen Dank, dass Sie sich für die automatische E-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.
Bitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo,<br/>
        <br/>
        vielen Dank, dass Sie sich für die automatische E-Mail Benachrichtigung für den Artikel {$sArticleName} eingetragen haben.<br/>
        Bitte bestätigen Sie die Benachrichtigung über den nachfolgenden Link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Bestätigen</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:2:{s:12:"sConfirmLink";s:177:"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=notifyConfirm&sNotificationConfirmation=j48FnwtKhMycfizOyYe0CtB0UKzgoeYG&sNotify=1&number=SW10165";s:12:"sArticleName";s:24:"DIE ZEIT 5 Cowhide mokka";}' WHERE `s_core_config_mails`.`name` = 'sACCEPTNOTIFICATION' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%die automatische E-Mail Benachrichtigung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'SEPA Lastschriftmandat',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo,

im Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$paymentInstance.firstName} {$paymentInstance.lastName},<br/>
        <br/>
        im Anhang finden Sie ein Lastschriftmandat zu Ihrer Bestellung {$paymentInstance.orderNumber}. Bitte senden Sie uns das komplett ausgefüllte Dokument per Fax oder Email zurück.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 1,`context` = 'a:1:{s:15:"paymentInstance";a:3:{s:9:"firstName";s:3:"Max";s:8:"lastName";s:10:"Mustermann";s:11:"orderNumber";s:5:"20003";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSEPAAUTHORIZATION' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%im Anhang finden Sie ein Lastschriftmandat%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Passwort vergessen - Passwort zurücksetzen',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$user.salutation|salutation} {$user.lastname},

im Shop {$sShop} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen. Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.

{$sUrlReset}

Dieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden. Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$user.salutation|salutation} {$user.lastname},<br/>
        <br/>
        im Shop {$sShop} wurde eine Anfrage gestellt, um Ihr Passwort zurück zu setzen.
        Bitte bestätigen Sie den unten stehenden Link, um ein neues Passwort zu definieren.<br/>
        <br/>
        <a href="{$sUrlReset}">Passwort zurücksetzen</a><br/>
        <br/>
        Dieser Link ist nur für die nächsten 2 Stunden gültig. Danach muss das Zurücksetzen des Passwortes erneut beantragt werden.
        Falls Sie Ihr Passwort nicht zurücksetzen möchten, ignorieren Sie diese E-Mail - es wird dann keine Änderung vorgenommen.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:4:{s:9:"sUrlReset";s:83:"http://shopware.example/account/resetPassword/hash/pdiR4nNSvvTYHQGxC0K2PxLk5QtQilXm";s:4:"sUrl";s:0:"";s:4:"sKey";s:0:"";s:4:"user";a:21:{s:11:"accountmode";s:1:"0";s:6:"active";s:1:"1";s:9:"affiliate";s:1:"0";s:8:"birthday";N;s:15:"confirmationkey";s:0:"";s:13:"customergroup";s:2:"EK";s:14:"customernumber";s:5:"20001";s:5:"email";s:16:"test@example.com";s:12:"failedlogins";s:1:"0";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:8:"language";s:1:"1";s:15:"internalcomment";s:0:"";s:11:"lockeduntil";N;s:9:"subshopID";s:1:"1";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:10:"newsletter";s:1:"0";s:10:"attributes";b:0;}}' WHERE `s_core_config_mails`.`name` = 'sCONFIRMPASSWORDCHANGE' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%wurde eine Anfrage gestellt, um Ihr Passwort%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} ist in Bearbeitung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"1";s:8:"statusID";s:1:"1";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:10:"in_process";s:18:"status_description";s:23:"In Bearbeitung (Wartet)";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL1' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Der Status Ihrer Bestellung mit der Bestellnummer%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} komplett abgeschlossen',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:9:"completed";s:18:"status_description";s:22:"Komplett abgeschlossen";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL2' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Der Status Ihrer Bestellung mit der Bestellnummer%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Zahlungsstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Zahlungsstatus: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:17:"Teilweise bezahlt";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL11' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Der Status Ihrer Bestellung mit der Bestellnummer%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} ist bereit zur Lieferung',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"5";s:8:"statusID";s:1:"5";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:18:"ready_for_delivery";s:18:"status_description";s:20:"Zur Lieferung bereit";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL5' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Der Status Ihrer Bestellung mit der Bestellnummer%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.


Informationen zu Ihrer Bestellung:
==================================
{foreach item=details key=position from=$sOrderDetails}
{$position+1|fill:3}      {$details.articleordernumber}     {$details.name|fill:30}     {$details.quantity} x {$details.price|string_format:"%.2f"} {$sOrder.currency}
{/foreach}

Versandkosten: {$sOrder.invoice_shipping|string_format:"%.2f"} {$sOrder.currency}
Netto-Gesamt: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sOrder.currency}
Gesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:"%.2f"} {$sOrder.currency}

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        <strong>Informationen zu Ihrer Bestellung:</strong></p><br/>
        <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
            <tr>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Artikel</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art-Nr.</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Menge</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Preis</strong></td>
            </tr>
            {foreach item=details key=position from=$sOrderDetails}
            <tr>
                <td>{$details.name|wordwrap:80|indent:4}</td>
                <td>{$position+1|fill:4} </td>
                <td>{$details.ordernumber|fill:20}</td>
                <td>{$details.quantity|fill:6}</td>
                <td>{$details.price|padding:8} {$sOrder.currency}</td>
            </tr>
            {/foreach}
        </table>
    <p>    
        <br/>
        Versandkosten: {$sOrder.invoice_shipping|string_format:"%.2f"} {$sOrder.currency}<br/>
        Netto-Gesamt: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sOrder.currency}<br/>
        Gesamtbetrag inkl. MwSt.: {$sOrder.invoice_amount|string_format:"%.2f"} {$sOrder.currency}<br/>
    	<br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"3";s:8:"statusID";s:1:"3";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:19:"partially_completed";s:18:"status_description";s:23:"Teilweise abgeschlossen";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL3' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Informationen zu Ihrer Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Statusänderung zur Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:18:"Klärung notwendig";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL8' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%der Bestellstatus für Ihre Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Stornierung der Bestellung bei {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"4";s:8:"statusID";s:1:"4";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:18:"cancelled_rejected";s:18:"status_description";s:21:"Storniert / Abgelehnt";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL4' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%der Bestellstatus für Ihre Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} wurde teilweise ausgeliefert',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"6";s:8:"statusID";s:1:"6";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:19:"partially_delivered";s:18:"status_description";s:22:"Teilweise ausgeliefert";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' 
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL6' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Bestellstatus für Ihre Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Bestellung bei {config name=shopName} wurde ausgeliefert',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!
Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.

Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        der Bestellstatus für Ihre Bestellung {$sOrder.ordernumber} vom {$sOrder.ordertime|date_format:"%d.%m.%Y"} hat sich geändert!<br/>
        <strong>Die Bestellung hat jetzt den Bestellstatus: {$sOrder.status_description}.</strong><br/>
        <br/>
        Den aktuellen Status Ihrer Bestellung können Sie auch jederzeit auf unserer Webseite im  Bereich "Mein Konto" - "Meine Bestellungen" abrufen. Sollten Sie allerdings den Kauf ohne Registrierung, also ohne Anlage eines Kundenkontos, gewählt haben, steht Ihnen diese Möglichkeit nicht zur Verfügung.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"3";s:10:"customerID";s:1:"3";s:14:"invoice_amount";s:5:"31.85";s:18:"invoice_amount_net";s:5:"26.77";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-08-07 14:09:26";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:5:"Offen";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:21:"Komplett ausgeliefert";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"208";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"152";s:18:"articleordernumber";s:9:"SW10152.1";s:5:"price";s:5:"29.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"29.95";s:4:"name";s:31:"WINDSTOPPER MÜTZE WARM Schwarz";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"209";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20005";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:15:"Musterstraße 1";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:5:"12345";s:12:"billing_city";s:11:"Musterstadt";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"59";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:15:"Musterstraße 1";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:5:"12345";s:13:"shipping_city";s:11:"Musterstadt";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"3";s:8:"password";s:60:"$2y$10$AzjwzOob83DJ2LG6yxXcBeghK9ciBB1zsK3UeBZADZCl10pTQN62W";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:14:"xy@example.org";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-08-07";s:9:"lastlogin";s:19:"2017-08-07 14:09:26";s:9:"sessionID";s:26:"hkkhfl82i1jejfvd2f0ucr6om4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"5";s:27:"default_shipping_address_id";s:1:"5";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"1239c089-6b2f-4461-9134-c02026970bff.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}' WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL7' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%der Bestellstatus für Ihre Bestellung%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Herzlichen Glückwunsch zum Geburtstag von {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},
 
Alles Gute zum Geburtstag. Zu Ihrem persönlichen Jubiläum haben wir uns etwas Besonderes ausgedacht, wir senden Ihnen hiermit einen Geburtstagscode über {if $sVoucher.value}{$sVoucher.value|currency|unescape:"htmlall"}{else}{$sVoucher.percental} %{/if}, den Sie bei Ihrer nächsten Bestellung in unserem Online-Shop: {$sShopURL} ganz einfach einlösen können.
 
Ihr persönlicher Geburtstags-Code lautet: {$sVoucher.code}
{if $sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig vom {$sVoucher.valid_from|date_format:"%d.%m.%Y"} bis zum {$sVoucher.valid_to|date_format:"%d.%m.%Y"}.{/if}
{if $sVoucher.valid_from && !$sVoucher.valid_to}Dieser Code ist gültig ab dem {$sVoucher.valid_from|date_format:"%d.%m.%Y"}.{/if}
{if !$sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig bis zum {$sVoucher.valid_to|date_format:"%d.%m.%Y"}.{/if}


{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
	<p>Hallo {$sUser.salutation|salutation} {$sUser.lastname},</p>
 	<p><strong>Alles Gute zum Geburtstag</strong>. Zu Ihrem persönlichen Jubiläum haben wir uns etwas Besonderes ausgedacht, wir senden Ihnen hiermit einen Geburtstagscode über {if $sVoucher.value}{$sVoucher.value|currency|unescape:"htmlall"}{else}{$sVoucher.percental} %{/if}, den Sie bei Ihrer nächsten Bestellung in unserem <a href="{$sShopURL}" title="{$sShop}">Online-Shop</a> ganz einfach einlösen können.</p>
 	<p><strong>Ihr persönlicher Geburtstags-Code lautet: <span style="text-decoration:underline;">{$sVoucher.code}</span></strong><br/>
 	{if $sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig vom {$sVoucher.valid_from|date_format:"%d.%m.%Y"} bis zum {$sVoucher.valid_to|date_format:"%d.%m.%Y"}.{/if}
 	{if $sVoucher.valid_from && !$sVoucher.valid_to}Dieser Code ist gültig ab dem {$sVoucher.valid_from|date_format:"%d.%m.%Y"}.{/if}
 	{if !$sVoucher.valid_from && $sVoucher.valid_to}Dieser Code ist gültig bis zum {$sVoucher.valid_to|date_format:"%d.%m.%Y"}.{/if}
</p>
 
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:3:{s:5:"sUser";a:28:{s:6:"userID";s:1:"1";s:7:"company";s:11:"Muster GmbH";s:10:"department";N;s:10:"salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:6:"street";s:13:"Musterstr. 55";s:7:"zipcode";s:5:"55555";s:4:"city";s:12:"Musterhausen";s:5:"phone";s:14:"05555 / 555555";s:9:"countryID";s:1:"2";s:5:"ustid";N;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;s:5:"email";s:16:"test@example.com";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:10:"newsletter";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";}s:8:"sVoucher";a:6:{s:13:"vouchercodeID";s:3:"201";s:4:"code";s:8:"0B818118";s:5:"value";s:1:"5";s:9:"percental";s:1:"0";s:8:"valid_to";s:10:"2017-12-31";s:10:"valid_from";s:10:"2017-10-22";}s:5:"sData";N;}' 
WHERE `s_core_config_mails`.`name` = 'sBIRTHDAY' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Ihnen alles Gute zum Geburtstag.%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Artikel bewerten',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

Sie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.
So helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.

Hier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.

Bestellnummer     Artikelname     Bewertungslink
{foreach from=$sArticles item=sArticle key=key}
{if !$sArticle.modus}
{$sArticle.articleordernumber}      {$sArticle.name}      {$sArticle.link_rating_tab}
{/if}
{/foreach}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    Hallo {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
    <br/>
    Sie haben bei uns vor einigen Tagen Artikel gekauft. Wir würden uns freuen, wenn Sie diese Artikel bewerten würden.<br/>
    So helfen Sie uns, unseren Service weiter zu steigern und Sie können auf diesem Weg anderen Interessenten direkt Ihre Meinung mitteilen.<br/>
    <br/>
    Hier finden Sie die Links zum Bewerten der von Ihnen gekauften Produkte.<br/>
    <br/>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Artikel</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Bestellnummer</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Artikelname</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Bewertungslink</td>
        </tr>
        {foreach from=$sArticles item=sArticle key=key}
        {if !$sArticle.modus}
            <tr>
                <td style="border-bottom:1px solid #cccccc;">
                  {if $sArticle.image_small && $sArticle.modus == 0}
                    <img style="height: 57px;" height="57" src="{$sArticle.image_small}" alt="{$sArticle.articlename}" />
                  {else}
                  {/if}
                </td>
                <td style="border-bottom:1px solid #cccccc;">{$sArticle.articleordernumber}</td>
                <td style="border-bottom:1px solid #cccccc;">{$sArticle.name}</td>
                <td style="border-bottom:1px solid #cccccc;">
                    <a href="{$sArticle.link_rating_tab}">Link</a>
                </td>
            </tr>
        {/if}
        {/foreach}
    </table>
    <br/><br/>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:4:{s:7:"sConfig";a:0:{}s:6:"sOrder";a:38:{s:2:"id";s:2:"59";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:12:"order_number";s:5:"20003";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"271.85";s:18:"invoice_amount_net";s:6:"228.45";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-09 11:41:41";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";s:19:"2017-10-09 00:00:00";s:12:"cleared_date";s:19:"2017-10-09 00:00:00";s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:19:"cleared_description";s:16:"Komplett bezahlt";s:18:"status_description";s:22:"Komplett abgeschlossen";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:16:"Standard Versand";s:20:"currency_description";s:4:"Euro";}s:5:"sUser";a:76:{s:7:"orderID";s:2:"59";s:15:"billing_company";s:11:"Muster GmbH";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:13:"Musterstr. 55";s:15:"billing_zipcode";s:5:"55555";s:12:"billing_city";s:12:"Musterhausen";s:5:"phone";s:14:"05555 / 555555";s:13:"billing_phone";s:14:"05555 / 555555";s:17:"billing_countryID";s:1:"2";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2017-10-09 11:41:41";s:9:"sessionID";s:26:"sh860bhb7plloqm4teo8s99tq0";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";s:38:"0626e2f8-db4a-41b3-b103-e9cece25f51a.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sArticles";a:1:{i:212;a:25:{s:14:"orderdetailsID";s:3:"212";s:7:"orderID";s:2:"59";s:11:"ordernumber";s:5:"20003";s:9:"articleID";s:3:"134";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"49.99";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"49.99";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:3:"esd";s:1:"0";s:9:"subshopID";s:1:"1";s:8:"language";s:1:"1";s:4:"link";s:42:"http://shopware.example/elastic-muetze-153";s:15:"link_rating_tab";s:57:"http://shopware.example/elastic-muetze-153?jumpTab=rating";s:11:"image_large";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"image_small";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:14:"image_original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}}}' WHERE `s_core_config_mails`.`name` = 'sARTICLECOMMENT' AND `s_core_config_mails`.`dirty` = 0 AND `content` LIKE '%Sie haben bei uns vor einigen Tagen Artikel gekauft%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Dokumente zur Bestellung {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},

vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie Dokumente zu Ihrer Bestellung als PDF.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie Dokumente zu Ihrer Bestellung als PDF.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERDOCUMENTS' AND `dirty` = 0 AND `content` LIKE '%Im Anhang finden Sie Dokumente zu Ihrer Bestellung als PDF%';
EOD;

        //English
        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `subject` = 'Your registration by {config name=shopName}', `content`='{include file="string:{config name=emailheaderplain}"}

Dear {$salutation|salutation} {$lastname},

thank you for your registration with our Shop.
You will gain access via the email address {$sMAIL} and the password you have chosen.
You can change your password at any time.

{include file="string:{config name=emailfooterplain}"}', contentHTML='<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$salutation|salutation} {$lastname},<br/>
        <br/>
        thank you for your registration with our Shop.<br/>
        You will gain access via the email address <strong>{$sMAIL}</strong> and the password you have chosen.<br/>
        You can change your password anytime.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>', `ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:14:{s:7:"sConfig";a:0:{}s:5:"sMAIL";s:12:"xyz@mail.com";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:2:"Mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:11:"accountmode";s:1:"0";s:5:"email";s:12:"xyz@mail.com";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name`="sREGISTERCONFIRMATION" AND `dirty` = 0 AND `content` LIKE '%thank you for your registration with our Shop%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order at the {config name=shopName}',
`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$billingaddress.salutation|salutation} {$billingaddress.lastname},

Thank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.
Information on your order:

Pos.  Art.No.               Description                                      Quantities       Price       Total
{foreach item=details key=position from=$sOrderDetails}
{{$position+1}|fill:4}  {$details.ordernumber|fill:20}  {$details.articlename|fill:49}  {$details.quantity|fill:6}  {$details.price|padding:8|currency|unescape:"htmlall"}     {$details.amount|padding:8|currency|unescape:"htmlall"}
{/foreach}

Shipping costs: {$sShippingCosts|currency|unescape:"htmlall"}
Net total: {$sAmountNet|currency|unescape:"htmlall"}
{if !$sNet}
{foreach $sTaxRates as $rate => $value}
plus {$rate|number_format:0}% VAT {$value|currency|unescape:"htmlall"}
{/foreach}
Total gross: {$sAmount|currency|unescape:"htmlall"}
{/if}

Selected payment type: {$additional.payment.description}
{$additional.payment.additionaldescription}
{if $additional.payment.name == "debit"}
Your bank connection:
Account number: {$sPaymentTable.account}
BIN:{$sPaymentTable.bankcode}
Bank name: {$sPaymentTable.bankname}
Bank holder: {$sPaymentTable.bankholder}

We will withdraw the money from your bank account within the next days.
{/if}
{if $additional.payment.name == "prepayment"}

Our bank connection:
Account: ###
BIN: ###
{/if}


Selected shipping type: {$sDispatch.name}
{$sDispatch.description}

{if $sComment}
Your comment:
{$sComment}
{/if}

Billing address:
{$billingaddress.company}
{$billingaddress.firstname} {$billingaddress.lastname}
{$billingaddress.street} {$billingaddress.streetnumber}
{if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}
{$additional.country.countryname}

Shipping address:
{$shippingaddress.company}
{$shippingaddress.firstname} {$shippingaddress.lastname}
{$shippingaddress.street} {$shippingaddress.streetnumber}
{if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}\n
{$additional.countryShipping.countryname}

{if $billingaddress.ustid}
Your VAT-ID: {$billingaddress.ustid}
In case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax.{/if}

If you have any questions, do not hesitate to contact us.

{include file="string:{config name=emailfooterplain}"}',

`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>Dear {$billingaddress.salutation|salutation} {$billingaddress.lastname},<br/>
        <br/>
        Thank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.<br/>
        <br/>
        <strong>Information on your order:</strong></p><br/>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Article</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Description</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Quantities</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Price</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Total</strong></td>
        </tr>

        {foreach item=details key=position from=$sOrderDetails}
        <tr>
            <td style="border-bottom:1px solid #cccccc;">{$position+1|fill:4} </td>
            <td style="border-bottom:1px solid #cccccc;">{if $details.image.src.0 && $details.modus == 0}<img style="height: 57px;" height="57" src="{$details.image.src.0}" alt="{$details.articlename}" />{else} {/if}</td>
            <td style="border-bottom:1px solid #cccccc;">
                {$details.articlename|wordwrap:80|indent:4}<br>
                Article-No: {$details.ordernumber|fill:20}
            </td>
            <td style="border-bottom:1px solid #cccccc;">{$details.quantity|fill:6}</td>
            <td style="border-bottom:1px solid #cccccc;">{$details.price|padding:8|currency}</td>
            <td style="border-bottom:1px solid #cccccc;">{$details.amount|padding:8|currency}</td>
        </tr>
        {/foreach}
        
    </table>

    <p>
        <br/>
        <br/>
        Shipping costs: {$sShippingCosts|currency}<br/>
        Net total: {$sAmountNet|currency}<br/>
        {if !$sNet}
        {foreach $sTaxRates as $rate => $value}
        plus {$rate|number_format:0}% VAT {$value|currency}<br/>
        {/foreach}
        <strong>Total gross: {$sAmount|currency}</strong><br/>
        {/if}
        <br/>
        <br/>
        <strong>Selected payment type:</strong> {$additional.payment.description}<br/>
        {$additional.payment.additionaldescription}
        {if $additional.payment.name == "debit"}
        Your bank connection:<br/>
        Account number: {$sPaymentTable.account}<br/>
        BIN: {$sPaymentTable.bankcode}<br/>
        Bank name: {$sPaymentTable.bankname}<br/>
        Bank holder: {$sPaymentTable.bankholder}<br/>
        <br/>
        We will withdraw the money from your bank account within the next days.<br/>
        {/if}
        <br/>
        <br/>
        {if $additional.payment.name == "prepayment"}
        Our bank connection:<br/>
        Account: ###<br/>
        BIN: ###<br/>
        {/if}
        <br/>
        <br/>
        <strong>Selected shipping type:</strong> {$sDispatch.name}<br/>
        {$sDispatch.description}
    </p>
    <br/>
    <p>
        {if $sComment}
        <strong>Your comment:</strong><br/>
        {$sComment}<br/>
        {/if}
        <br/>
        <br/>
        <strong>Billing address:</strong><br/>
        {$billingaddress.company}<br/>
        {$billingaddress.firstname} {$billingaddress.lastname}<br/>
        {$billingaddress.street} {$billingaddress.streetnumber}<br/>
        {if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}<br/>
        {$additional.country.countryname}<br/>
        <br/>
        <br/>
        <strong>Shipping address:</strong><br/>
        {$shippingaddress.company}<br/>
        {$shippingaddress.firstname} {$shippingaddress.lastname}<br/>
        {$shippingaddress.street} {$shippingaddress.streetnumber}<br/>
        {if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}<br/>
        {$additional.countryShipping.countryname}<br/>
        <br/>
        {if $billingaddress.ustid}
        Your VAT-ID: {$billingaddress.ustid}<br/>
        In case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax.<br/>
        {/if}
        <br/>
        <br/>
        If you have any questions, do not hesitate to contact us.<br/>
        {include file="string:{config name=emailfooterhtml}"}
    </p>
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:22:{s:13:"sOrderDetails";a:2:{i:0;a:54:{s:2:"id";s:3:"678";s:9:"sessionID";s:26:"340hhak8399eo3a0sk10jem2p0";s:6:"userID";s:1:"4";s:11:"articlename";s:11:"ELASTIC CAP";s:9:"articleID";s:3:"153";s:11:"ordernumber";s:7:"SW10153";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:5:"14,95";s:8:"netprice";s:15:"12.563025210084";s:8:"tax_rate";s:2:"19";s:5:"datum";s:19:"2017-10-27 11:41:01";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:5:"index";s:9:"useragent";s:105:"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"packunit";s:0:"";s:12:"mainDetailId";s:3:"709";s:15:"articleDetailId";s:3:"709";s:11:"minpurchase";s:1:"1";s:5:"taxID";s:1:"1";s:7:"instock";s:2:"16";s:14:"suppliernumber";s:0:"";s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:9:"laststock";s:1:"0";s:12:"shippingtime";s:0:"";s:11:"releasedate";N;s:12:"sReleaseDate";N;s:3:"ean";s:0:"";s:8:"stockmin";s:1:"0";s:8:"ob_attr1";s:0:"";s:8:"ob_attr2";N;s:8:"ob_attr3";N;s:8:"ob_attr4";N;s:8:"ob_attr5";N;s:8:"ob_attr6";N;s:12:"shippinginfo";b:1;s:3:"esd";s:1:"0";s:18:"additional_details";a:94:{s:9:"articleID";i:153;s:16:"articleDetailsID";i:709;s:11:"ordernumber";s:7:"SW10153";s:9:"highlight";b:1;s:11:"description";s:0:"";s:16:"description_long";s:2404:"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo.</p>
<p>Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue.</p>
<p>Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt.</p>
<p>Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui. Cras ultricies mi eu turpis hendrerit fringilla.</p>
<p>Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede. Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi.</p>";s:3:"esd";b:0;s:11:"articleName";s:11:"ELASTIC CAP";s:5:"taxID";i:1;s:3:"tax";i:19;s:7:"instock";i:16;s:11:"isAvailable";b:1;s:6:"weight";i:0;s:12:"shippingtime";N;s:16:"pricegroupActive";b:0;s:12:"pricegroupID";N;s:6:"length";i:0;s:6:"height";i:0;s:5:"width";i:0;s:9:"laststock";b:0;s:14:"additionaltext";s:0:"";s:5:"datum";s:10:"2015-02-05";s:5:"sales";i:1;s:13:"filtergroupID";i:8;s:17:"priceStartingFrom";N;s:18:"pseudopricePercent";N;s:15:"sVariantArticle";N;s:13:"sConfigurator";b:0;s:9:"metaTitle";s:0:"";s:12:"shippingfree";b:0;s:14:"suppliernumber";s:0:"";s:12:"notification";b:0;s:3:"ean";s:0:"";s:8:"keywords";s:0:"";s:12:"sReleasedate";s:0:"";s:8:"template";s:0:"";s:10:"attributes";a:2:{s:4:"core";a:23:{s:2:"id";s:3:"721";s:9:"articleID";s:3:"153";s:16:"articledetailsID";s:3:"709";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";}s:9:"marketing";a:4:{s:5:"isNew";b:0;s:11:"isTopSeller";b:0;s:10:"comingSoon";b:0;s:7:"storage";a:0:{}}}s:17:"allowBuyInListing";b:1;s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:12:"supplierName";s:8:"LÖFFLER";s:11:"supplierImg";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:10:"supplierID";i:10;s:19:"supplierDescription";s:1009:"<p>L&ouml;ffler is different. Unlike most competitors, L&ouml;ffler decided in the early 90s to continue production in Austria, following the highest ethical and environmental standards enforced by the EU. All of this was made possible thanks to well trained, competent and motivated employees.</p>
<p>Many sportswear companies have already shifted production to low-wage countries in pursuit of the highest profit margins, where miserable working conditions, starvation wages and child labor are ever-present. Highly problematic include the pollution and environmental degradation caused by these ruthless methods of textile production.</p>
<p>70% of all materials used by L&Ouml;FFLER come from our own knitting factory in Ried im Innkreis in Austria. This unique aspect is an important basis for the outstanding quality of L&Ouml;FFLER&rsquo;s Fair Sportswear.</p>
<p>More information on this manufacturer can be found <a title="www.loeffler.at" href="http://www.loeffler.at/" target="_blank">here</a>.</p>";s:19:"supplier_attributes";a:0:{}s:10:"newArticle";b:0;s:9:"sUpcoming";b:0;s:9:"topseller";b:0;s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"14,95";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";s:1:"0";s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:14.949999999999999;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}s:5:"image";a:12:{s:2:"id";i:367;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:0:"";s:9:"extension";s:3:"jpg";s:4:"main";b:1;s:8:"parentId";N;s:5:"width";i:2000;s:6:"height";i:1860;s:10:"thumbnails";a:3:{i:0;a:6:{s:6:"source";s:64:"https://shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"200";s:9:"maxHeight";s:3:"200";s:10:"attributes";a:0:{}}i:1;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"600";s:9:"maxHeight";s:3:"600";s:10:"attributes";a:0:{}}i:2;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:4:"1280";s:9:"maxHeight";s:4:"1280";s:10:"attributes";a:0:{}}}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}s:6:"prices";a:1:{i:0;a:22:{s:7:"valFrom";i:1;s:5:"valTo";N;s:4:"from";i:1;s:2:"to";N;s:5:"price";s:5:"14,95";s:11:"pseudoprice";s:1:"0";s:14:"referenceprice";s:1:"0";s:18:"pseudopricePercent";N;s:15:"has_pseudoprice";b:0;s:13:"price_numeric";d:14.949999999999999;s:19:"pseudoprice_numeric";i:0;s:16:"price_attributes";a:0:{}s:10:"pricegroup";s:2:"EK";s:11:"minpurchase";i:1;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:13:"referenceunit";N;s:8:"packunit";s:0:"";s:6:"unitID";N;s:5:"sUnit";a:2:{s:4:"unit";N;s:11:"description";N;}s:15:"unit_attributes";a:0:{}}}s:10:"linkBasket";s:42:"shopware.php?sViewport=basket&sAdd=SW10153";s:11:"linkDetails";s:42:"shopware.php?sViewport=detail&sArticle=153";s:11:"linkVariant";s:57:"shopware.php?sViewport=detail&sArticle=153&number=SW10153";s:11:"sProperties";a:3:{i:1;a:11:{s:2:"id";i:1;s:8:"optionID";i:1;s:4:"name";s:9:"Item type";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:14:"Highlight item";s:6:"values";a:1:{i:7;s:14:"Highlight item";}s:12:"isFilterable";b:1;s:7:"options";a:1:{i:0;a:3:{s:2:"id";i:7;s:4:"name";s:14:"Highlight item";s:10:"attributes";a:0:{}}}s:5:"media";a:0:{}s:10:"attributes";a:0:{}}i:3;a:11:{s:2:"id";i:3;s:8:"optionID";i:3;s:4:"name";s:8:"Material";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:17:"Polyamide, Cotton";s:6:"values";a:2:{i:308;s:9:"Polyamide";i:163;s:6:"Cotton";}s:12:"isFilterable";b:1;s:7:"options";a:2:{i:0;a:3:{s:2:"id";i:308;s:4:"name";s:9:"Polyamide";s:10:"attributes";a:0:{}}i:1;a:3:{s:2:"id";i:163;s:4:"name";s:6:"Cotton";s:10:"attributes";a:0:{}}}s:5:"media";a:0:{}s:10:"attributes";a:0:{}}i:18;a:11:{s:2:"id";i:18;s:8:"optionID";i:18;s:4:"name";s:5:"Color";s:7:"groupID";i:8;s:9:"groupName";s:7:"Fashion";s:5:"value";s:5:"Royal";s:6:"values";a:1:{i:293;s:5:"Royal";}s:12:"isFilterable";b:1;s:7:"options";a:1:{i:0;a:3:{s:2:"id";i:293;s:4:"name";s:5:"Royal";s:10:"attributes";a:0:{}}}s:5:"media";a:1:{i:293;a:13:{s:7:"valueId";i:293;s:2:"id";i:356;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:5:"royal";s:9:"extension";s:3:"jpg";s:4:"main";N;s:8:"parentId";N;s:5:"width";i:40;s:6:"height";i:40;s:10:"thumbnails";a:0:{}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}}}s:10:"attributes";a:0:{}}}s:10:"properties";s:93:"Item type:&nbsp;Highlight item,&nbsp;Material:&nbsp;Polyamide, Cotton,&nbsp;Color:&nbsp;Royal";}s:6:"amount";s:5:"14,95";s:9:"amountnet";s:5:"12,56";s:12:"priceNumeric";s:5:"14.95";s:5:"image";a:15:{s:2:"id";i:367;s:8:"position";N;s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"description";s:0:"";s:9:"extension";s:3:"jpg";s:4:"main";b:1;s:8:"parentId";N;s:5:"width";i:2000;s:6:"height";i:1860;s:10:"thumbnails";a:3:{i:0;a:6:{s:6:"source";s:64:"https://shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"200";s:9:"maxHeight";s:3:"200";s:10:"attributes";a:0:{}}i:1;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:3:"600";s:9:"maxHeight";s:3:"600";s:10:"attributes";a:0:{}}i:2;a:6:{s:6:"source";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:12:"retinaSource";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:9:"sourceSet";s:141:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg, https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg 2x";s:8:"maxWidth";s:4:"1280";s:9:"maxHeight";s:4:"1280";s:10:"attributes";a:0:{}}}s:10:"attributes";a:0:{}s:9:"attribute";a:0:{}s:3:"src";a:4:{s:8:"original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:0;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:1;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:2;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}s:5:"srchd";a:4:{s:8:"original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:0;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:1;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";i:2;s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}s:3:"res";a:1:{s:8:"original";a:2:{s:5:"width";i:1860;s:6:"height";i:2000;}}}s:11:"linkDetails";s:42:"shopware.php?sViewport=detail&sArticle=153";s:10:"linkDelete";s:41:"shopware.php?sViewport=basket&sDelete=678";s:8:"linkNote";s:40:"shopware.php?sViewport=note&sAdd=SW10153";s:3:"tax";s:4:"2,39";s:13:"orderDetailId";s:3:"218";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:52:{s:2:"id";s:3:"685";s:9:"sessionID";s:26:"340hhak8399eo3a0sk10jem2p0";s:6:"userID";s:1:"0";s:11:"articlename";s:15:"Warenkorbrabatt";s:9:"articleID";s:1:"0";s:11:"ordernumber";s:16:"SHIPPINGDISCOUNT";s:12:"shippingfree";s:1:"0";s:8:"quantity";s:1:"1";s:5:"price";s:5:"-2,00";s:8:"netprice";s:5:"-1.68";s:8:"tax_rate";s:2:"19";s:5:"datum";s:19:"2017-10-27 11:43:54";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:9:"partnerID";s:0:"";s:12:"lastviewport";s:0:"";s:9:"useragent";s:0:"";s:6:"config";s:0:"";s:14:"currencyFactor";s:1:"1";s:8:"packunit";N;s:12:"mainDetailId";N;s:15:"articleDetailId";N;s:11:"minpurchase";i:1;s:5:"taxID";N;s:7:"instock";N;s:14:"suppliernumber";N;s:11:"maxpurchase";s:3:"100";s:13:"purchasesteps";i:1;s:12:"purchaseunit";N;s:9:"laststock";N;s:12:"shippingtime";N;s:11:"releasedate";N;s:12:"sReleaseDate";N;s:3:"ean";N;s:8:"stockmin";N;s:8:"ob_attr1";N;s:8:"ob_attr2";N;s:8:"ob_attr3";N;s:8:"ob_attr4";N;s:8:"ob_attr5";N;s:8:"ob_attr6";N;s:12:"shippinginfo";b:0;s:3:"esd";s:1:"0";s:6:"amount";s:5:"-2,00";s:9:"amountnet";s:5:"-1,68";s:12:"priceNumeric";s:2:"-2";s:11:"linkDetails";s:40:"shopware.php?sViewport=detail&sArticle=0";s:10:"linkDelete";s:41:"shopware.php?sViewport=basket&sDelete=685";s:8:"linkNote";s:49:"shopware.php?sViewport=note&sAdd=SHIPPINGDISCOUNT";s:3:"tax";s:5:"-0,32";s:13:"orderDetailId";s:3:"219";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:14:"billingaddress";a:26:{s:2:"id";s:1:"6";s:7:"company";s:0:"";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:5:"title";s:0:"";s:8:"lastname";s:3:"Doe";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:5:"phone";s:0:"";s:5:"vatId";s:0:"";s:22:"additionalAddressLine1";s:0:"";s:22:"additionalAddressLine2";s:0:"";s:9:"countryId";s:1:"2";s:7:"stateId";s:0:"";s:8:"customer";N;s:7:"country";N;s:5:"state";s:0:"";s:6:"userID";s:1:"4";s:9:"countryID";s:1:"2";s:7:"stateID";s:0:"";s:5:"ustid";s:0:"";s:24:"additional_address_line1";s:0:"";s:24:"additional_address_line2";s:0:"";s:10:"attributes";N;}s:15:"shippingaddress";a:26:{s:2:"id";s:1:"6";s:7:"company";s:0:"";s:10:"department";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:5:"title";s:0:"";s:8:"lastname";s:3:"Doe";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:5:"phone";s:0:"";s:5:"vatId";s:0:"";s:22:"additionalAddressLine1";s:0:"";s:22:"additionalAddressLine2";s:0:"";s:9:"countryId";s:1:"2";s:7:"stateId";s:0:"";s:8:"customer";N;s:7:"country";N;s:5:"state";s:0:"";s:6:"userID";s:1:"4";s:9:"countryID";s:1:"2";s:7:"stateID";s:0:"";s:5:"ustid";s:0:"";s:24:"additional_address_line1";s:0:"";s:24:"additional_address_line2";s:0:"";s:10:"attributes";N;}s:10:"additional";a:8:{s:7:"country";a:15:{s:2:"id";s:1:"2";s:11:"countryname";s:11:"Deutschland";s:10:"countryiso";s:2:"DE";s:6:"areaID";s:1:"1";s:9:"countryen";s:7:"GERMANY";s:8:"position";s:1:"1";s:6:"notice";s:0:"";s:7:"taxfree";s:1:"0";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"DEU";s:29:"display_state_in_registration";s:1:"0";s:27:"force_state_in_registration";s:1:"0";s:11:"countryarea";s:11:"deutschland";}s:5:"state";a:0:{}s:4:"user";a:33:{s:2:"id";s:1:"4";s:6:"userID";s:1:"4";s:8:"password";s:60:"$2y$10$tqQbjQGLZdG.LE734dY0Curk2Hb0TW.N5qJFlAZsrCRZNXRWP4dfe";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-27";s:9:"lastlogin";s:19:"2017-10-27 11:43:53";s:9:"sessionID";s:26:"340hhak8399eo3a0sk10jem2p0";s:10:"newsletter";i:0;s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"2";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:14:"customernumber";s:5:"20006";s:11:"login_token";s:38:"5436c29f-a11a-403b-8e0a-b5675df2b985.1";}s:15:"countryShipping";a:15:{s:2:"id";s:1:"2";s:11:"countryname";s:11:"Deutschland";s:10:"countryiso";s:2:"DE";s:6:"areaID";s:1:"1";s:9:"countryen";s:7:"GERMANY";s:8:"position";s:1:"1";s:6:"notice";s:0:"";s:7:"taxfree";s:1:"0";s:13:"taxfree_ustid";s:1:"0";s:21:"taxfree_ustid_checked";s:1:"0";s:6:"active";s:1:"1";s:4:"iso3";s:3:"DEU";s:29:"display_state_in_registration";s:1:"0";s:27:"force_state_in_registration";s:1:"0";s:11:"countryarea";s:11:"deutschland";}s:13:"stateShipping";a:0:{}s:7:"payment";a:21:{s:2:"id";s:1:"5";s:4:"name";s:10:"prepayment";s:11:"description";s:8:"Vorkasse";s:8:"template";s:14:"prepayment.tpl";s:5:"class";s:14:"prepayment.php";s:5:"table";s:0:"";s:4:"hide";s:1:"0";s:21:"additionaldescription";s:108:"Sie zahlen einfach vorab und erhalten die Ware bequem und günstig bei Zahlungseingang nach Hause geliefert.";s:13:"debit_percent";s:1:"0";s:9:"surcharge";s:1:"0";s:15:"surchargestring";s:0:"";s:8:"position";s:1:"1";s:6:"active";s:1:"1";s:9:"esdactive";s:1:"0";s:11:"embediframe";s:0:"";s:12:"hideprospect";s:1:"0";s:6:"action";N;s:8:"pluginID";N;s:6:"source";N;s:15:"mobile_inactive";s:1:"0";s:10:"validation";a:0:{}}s:10:"charge_vat";b:1;s:8:"show_net";b:1;}s:9:"sTaxRates";a:1:{s:5:"19.00";d:2.6899999999999999;}s:14:"sShippingCosts";s:8:"3,90 EUR";s:7:"sAmount";s:9:"16,85 EUR";s:14:"sAmountNumeric";d:16.850000000000001;s:10:"sAmountNet";s:9:"14,16 EUR";s:17:"sAmountNetNumeric";d:14.16;s:12:"sOrderNumber";i:20005;s:9:"sOrderDay";s:10:"27.10.2017";s:10:"sOrderTime";s:5:"11:44";s:8:"sComment";s:0:"";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}s:9:"sCurrency";s:3:"EUR";s:9:"sLanguage";i:1;s:8:"sSubShop";i:1;s:4:"sEsd";N;s:4:"sNet";b:0;s:13:"sPaymentTable";a:0:{}s:9:"sDispatch";a:10:{s:2:"id";s:1:"9";s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";s:11:"calculation";s:1:"1";s:11:"status_link";s:0:"";s:21:"surcharge_calculation";s:1:"3";s:17:"bind_shippingfree";s:1:"0";s:12:"shippingfree";N;s:15:"tax_calculation";s:1:"0";s:21:"tax_calculation_value";N;}}'
WHERE `s_core_config_mails`.`name` = 'sORDER' AND `dirty` = 0 AND `content` LIKE '%Information on your order:%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '{$sName} recommends you {$sArticle}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

{$sName} has found an interesting product for you on {$sShop} that you should have a look at:

{$sArticle}
{$sLink}

{$sComment}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
		{$sName} has found an interesting product for you on {$sShop} that you should have a look at:<br/>
        <br/>
        <strong><a href="{$sLink}">{$sArticle}</a></strong><br/>
    </p>
    {if $sComment}
        <div style="border: 2px solid black; border-radius: 5px; padding: 5px;"><p>{$sComment}</p></div><br/>
    {/if}
    
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:5:{s:7:"sConfig";a:0:{}s:5:"sName";s:8:"John Doe";s:8:"sArticle";s:11:"Elastic Cap";s:5:"sLink";s:39:"http://shopware.example/elastic-cap-153";s:8:"sComment";s:26:"Hey Jane - this is amazing";}'
WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND' AND `dirty` = 0 AND `content` LIKE '%has found an interesting product for you on%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Attention - no free serial numbers for {$sArticleName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

there is no additional free serial numbers available for the article 

{$sArticleName}.

Please provide new serial numbers immediately or deactivate the article.
Please assign a serial number to the customer {$sMail} manually.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        there is no additional free serial numbers available for the article<br/>
    </p>
    <strong>{$sArticleName}</strong><br/>
    <p>
        Please provide new serial numbers immediately or deactivate the article.<br/>
        Please assign a serial number to the customer {$sMail} manually.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:3:{s:7:"sConfig";a:0:{}s:12:"sArticleName";s:20:"ESD Download Product";s:5:"sMail";s:17:"john.doe@mail.com";}'
WHERE `s_core_config_mails`.`name` = 'sNOSERIALS' AND `dirty` = 0 AND `content` LIKE '%there is no additional free serial numbers available for the article%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your voucher',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {$customer},

{$user} has followed your recommendation and just ordered at {$sShop}.
This is why we give you a X € voucher, which you can redeem with your next order.

Your voucher code is as follows: XXX

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$customer},<br/>
        <br/>
        {$user} has followed your recommendation and just ordered at {$sShop}.<br/>
        This is why we give you a X € voucher, which you can redeem with your next order.<br/>
        <br/>
        <strong>Your voucher code is as follows: XXX</strong>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:3:{s:7:"sConfig";a:0:{}s:8:"customer";s:8:"John Doe";s:4:"user";s:8:"Jane Doe";}'
WHERE `s_core_config_mails`.`name` = 'sVOUCHER' AND `dirty` = 0 AND `content` LIKE '%This is why we give you a X € voucher, which you can redeem with your next order.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your merchant account has been unlocked',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

your merchant account at {$sShop} has been unlocked.
From now on, we will charge you the net purchase price.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        your merchant account at {$sShop} has been unlocked.<br/>
        From now on, we will charge you the net purchase price.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2, `context` = NULL
WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHACCEPTED' AND `dirty` = 0 AND `content` LIKE '%From now on, we will charge you the net purchase price.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your trader account has not been accepted',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.
In case of further questions please do not hesitate to contact us via telephone, fax or email.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
		<br/>
        thank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.<br/>
        In case of further questions please do not hesitate to contact us via telephone, fax or email.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = NULL
WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHREJECTED' AND `dirty` = 0 AND `content` LIKE '%Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your aborted order process - Send us your feedback and get a voucher!',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

You have recently aborted an order process on {$sShop} - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.
Please tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher.
Thank you for your feedback.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        You have recently aborted an order process on {$sShop} - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.<br/>
        Please tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher.<br/>
        Thank you for your feedback.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = NULL
WHERE `s_core_config_mails`.`name` = 'sCANCELEDQUESTION' AND `dirty` = 0 AND `content` LIKE '%Please tell us the reason why you have aborted your order.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your aborted order process - Voucher code enclosed',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

You have recently aborted an order process on {$sShop} - today, we would like to give you a {if $sVoucherpercental == "1"}{$sVouchervalue} %{else}{$sVouchervalue|currency|unescape:"htmlall"}{/if}-voucher - and therefore make it easier for you to decide for an order with {$sShop}. Your voucher is valid for two months and can be redeemed by entering the code "{$sVouchercode}". We would be pleased to accept your order!

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
         Hello,<br/>
         <br/>
         You have recently aborted an order process on {$sShop} - today, we would like to give you a {if $sVoucherpercental == "1"}{$sVouchervalue} %{else}{$sVouchervalue|currency}{/if}-voucher - and therefore make it easier for you to decide for an order with {$sShop}. Your voucher is valid for two months and can be redeemed by entering the code "<strong>{$sVouchercode}</strong>". We would be pleased to accept your order!
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:5:{s:12:"sVouchercode";s:8:"23A7BDA0";s:13:"sVouchervalue";i:15;s:15:"sVouchervalidto";s:10:"2017-12-31";s:17:"sVouchervalidfrom";s:10:"2017-10-22";s:17:"sVoucherpercental";i:0;}'
WHERE `s_core_config_mails`.`name` = 'sCANCELEDVOUCHER' AND `dirty` = 0 AND `content` LIKE '%Your voucher is valid for two months and can be redeemed by entering the code%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:1:"9";s:9:"clearedID";s:1:"9";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:18:"partially_invoiced";s:19:"cleared_description";s:18:"Partially invoiced";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL9' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"10";s:9:"clearedID";s:2:"10";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:19:"completely_invoiced";s:19:"cleared_description";s:19:"Completely invoiced";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL10' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '1st reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

this is your first reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        this is your first reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"13";s:9:"clearedID";s:2:"13";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"1st_reminder";s:19:"cleared_description";s:11:"1. Reminder";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL13' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Encashment of your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

You have now received 3 reminders for your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

You will receive shortly post from an encashment company!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        You have now received 3 reminders for your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You will receive shortly post from an encashment company!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"16";s:9:"clearedID";s:2:"16";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:10:"encashment";s:19:"cleared_description";s:10:"Collection";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL16' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '3rd reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

this is your third and last reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        this is your third and last reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"15";s:9:"clearedID";s:2:"15";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"3rd_reminder";s:19:"cleared_description";s:11:"3. Reminder";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL15' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '2nd reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

this is your second reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        this is your second reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"14";s:9:"clearedID";s:2:"14";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"2";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"2nd_reminder";s:19:"cleared_description";s:11:"2. Reminder";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL14' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is completely paid',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"2";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:15:"Completely paid";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL12' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
       UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:4:"Open";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL17' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%'; 
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"18";s:9:"clearedID";s:2:"18";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"2";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:8:"reserved";s:19:"cleared_description";s:8:"Reserved";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL18' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is delayed',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"19";s:9:"clearedID";s:2:"19";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"2";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:7:"delayed";s:19:"cleared_description";s:7:"Delayed";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL19' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:12:"order_number";s:5:"20004";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"16.85";s:18:"invoice_amount_net";s:5:"14.16";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 08:49:06";s:6:"status";s:1:"0";s:8:"statusID";s:1:"0";s:7:"cleared";s:2:"20";s:9:"clearedID";s:2:"20";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"2";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:12:"re_crediting";s:19:"cleared_description";s:12:"Re-crediting";s:11:"status_name";s:4:"open";s:18:"status_description";s:4:"Open";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"214";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"215";s:7:"orderID";s:2:"62";s:11:"ordernumber";s:5:"20004";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"62";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 08:49:06";s:9:"sessionID";s:26:"e6b7aaevs8nvaobuejg2o3gpt4";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"62c86dd9-a98d-46e7-9a85-612afff5cb7f.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL20' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Stock level of {$sData.count} article{if $sData.count>1}s{/if} under minimum stock ',`content` = '{include file="string:{config name=emailheaderplain}"}
        
Hello,

the following articles have undershot the minimum stock:

Order number     Name of article    Stock/Minimum stock
{foreach from=$sJob.articles item=sArticle key=key}
{$sArticle.ordernumber}       {$sArticle.name}        {$sArticle.instock}/{$sArticle.stockmin}
{/foreach}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        the following articles have undershot the minimum stock:<br/>
    </p>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Order number</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Name of article</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Stock/Minimum stock</strong></td>
        </tr>
    
        {foreach from=$sJob.articles item=sArticle key=key}
            <tr>
              <td>{$sArticle.ordernumber}</td>
              <td>{$sArticle.name}</td>
              <td>{$sArticle.instock}/{$sArticle.stockmin}</td>
            </tr>
        {/foreach}
    </table>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:2:{s:5:"sData";a:2:{s:5:"count";i:1;s:7:"numbers";a:1:{i:0;s:7:"SW10166";}}s:4:"sJob";a:1:{s:8:"articles";a:1:{s:7:"SW10166";a:48:{s:11:"ordernumber";s:7:"SW10166";s:2:"id";s:3:"730";s:9:"articleID";s:3:"166";s:6:"unitID";N;s:4:"name";s:12:"DIE ZEIT 100";s:11:"description";s:0:"";s:16:"description_long";s:2984:"<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum.</p>
<p>Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc, quis gravida magna mi a libero. Fusce vulputate eleifend sapien. Vestibulum purus quam, scelerisque ut, mollis sed, nonummy id, metus. Nullam accumsan lorem in dui.</p>
<p>Cras ultricies mi eu turpis hendrerit fringilla. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; In ac dui quis mi consectetuer lacinia. Nam pretium turpis et arcu. Duis arcu tortor, suscipit eget, imperdiet nec, imperdiet iaculis, ipsum. Sed aliquam ultrices mauris. Integer ante arcu, accumsan a, consectetuer eget, posuere ut, mauris. Praesent adipiscing. Phasellus ullamcorper ipsum rutrum nunc. Nunc nonummy metus. Vestibulum volutpat pretium libero. Cras id dui. Aenean ut eros et nisl sagittis vestibulum. Nullam nulla eros, ultricies sit amet, nonummy id, imperdiet feugiat, pede. Sed lectus. Donec mollis hendrerit risus. Phasellus nec sem in justo pellentesque facilisis. Etiam imperdiet imperdiet orci. Nunc nec neque. Phasellus leo dolor, tempus non, auctor et, hendrerit quis, nisi. Curabitur ligula sapien, tincidunt non, euismod vitae, posuere imperdiet, leo. Maecenas malesuada. Praesent congue erat at massa. Sed cursus turpis vitae tortor. Donec posuere vulputate arcu. Phasellus accumsan cursus velit. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed aliquam, nisi quis porttitor congue, elit erat euismod orci, ac placerat dolor lectus quis orci. Phasellus consectetuer vestibulum elit. Aenean tellus metus, bibendum sed, posuere ac, mattis non, nunc. Vestibulum fringilla pede sit amet augue. In turpis. Pellentesque posuere.</p>";s:12:"shippingtime";N;s:5:"added";s:10:"2015-02-06";s:9:"topseller";s:1:"0";s:8:"keywords";s:0:"";s:5:"taxID";s:1:"1";s:10:"supplierID";s:1:"6";s:7:"changed";s:19:"2017-10-30 09:59:28";s:16:"articledetailsID";s:3:"730";s:14:"suppliernumber";s:0:"";s:4:"kind";s:1:"1";s:14:"additionaltext";s:0:"";s:11:"impressions";s:1:"1";s:5:"sales";s:2:"10";s:6:"active";s:1:"1";s:7:"instock";s:1:"1";s:8:"stockmin";s:2:"10";s:6:"weight";s:5:"0.000";s:8:"position";s:1:"0";s:5:"attr1";s:0:"";s:5:"attr2";s:0:"";s:5:"attr3";s:0:"";s:5:"attr4";s:0:"";s:5:"attr5";s:0:"";s:5:"attr6";s:0:"";s:5:"attr7";s:0:"";s:5:"attr8";s:0:"";s:5:"attr9";s:0:"";s:6:"attr10";s:0:"";s:6:"attr11";s:0:"";s:6:"attr12";s:0:"";s:6:"attr13";s:0:"";s:6:"attr14";s:0:"";s:6:"attr15";s:0:"";s:6:"attr16";s:0:"";s:6:"attr17";N;s:6:"attr18";s:0:"";s:6:"attr19";s:0:"";s:6:"attr20";s:0:"";s:8:"supplier";s:4:"BREE";s:4:"unit";N;s:3:"tax";s:5:"19.00";}}}}'
WHERE `s_core_config_mails`.`name` = 'sARTICLESTOCK' AND `dirty` = 0 AND `content` LIKE '%the following articles have undershot the minimum stock:%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Thank you for your newsletter subscription',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for your newsletter subscription at {config name=shopName}.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for your newsletter subscription at {config name=shopName}.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:4:{s:27:"sUser.subscribeToNewsletter";s:1:"1";s:5:"sUser";a:3:{s:21:"subscribeToNewsletter";s:1:"1";s:10:"newsletter";s:12:"xyz@mail.com";s:12:"__csrf_token";s:30:"x4qUmF06eE1ofN36m93WQPT9TiZjVM";}s:16:"sUser.newsletter";s:12:"xyz@mail.com";s:18:"sUser.__csrf_token";s:30:"x4qUmF06eE1ofN36m93WQPT9TiZjVM";}'
WHERE `s_core_config_mails`.`name` = 'sNEWSLETTERCONFIRMATION' AND `dirty` = 0 AND `content` LIKE '%thank you for your newsletter subscription at%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your newsletter subscription',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for signing up for our regularly published newsletter.
Please confirm your subscription by clicking the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for signing up for our regularly published newsletter.<br/>
        Please confirm your subscription by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:11:{s:7:"sConfig";a:0:{}s:12:"sConfirmLink";s:24:"http://shopware.example/";s:27:"sUser.subscribeToNewsletter";s:1:"1";s:16:"sUser.newsletter";s:0:"";s:16:"sUser.salutation";s:2:"mr";s:15:"sUser.firstname";s:4:"John";s:14:"sUser.lastname";s:3:"Doe";s:12:"sUser.street";s:17:"Example Street 11";s:13:"sUser.zipcode";s:5:"12345";s:10:"sUser.city";s:11:"Examplecity";s:15:"sUser.Speichern";s:0:"";}'
WHERE `s_core_config_mails`.`name` = 'sOPTINNEWSLETTER' AND `dirty` = 0 AND `content` LIKE '%Please confirm your subscription by clicking the following link:%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your article evaluation',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for evaluating the article {$sArticle.articleName}.
Please confirm the evaluation by clicking the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <p>
        Hello,<br/>
        <br/>
        thank you for evaluating the article {$sArticle.articleName}.<br/>
        Please confirm the evaluation by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:2:{s:12:"sConfirmLink";s:133:"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=rating&sConfirmation=6avE5xLF22DTp8gNPaZ8KRUfJhflnvU9";s:8:"sArticle";a:1:{s:11:"articleName";s:24:"DIE ZEIT 5 Cowhide mokka";}}'
WHERE `s_core_config_mails`.`name` = 'sOPTINVOTE' AND `dirty` = 0 AND `content` LIKE '%Please confirm the evaluation by clicking the following link:%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your article is available again',`content` = '{include file="string:{config name=emailheaderplain}"}
        
Hello,

your article with the order number {$sOrdernumber} is available again.

{$sArticleLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        your article with the order number {$sOrdernumber} is available again.<br/>
        <br/>
        <a href="{$sArticleLink}">{$sOrdernumber}</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:3:{s:12:"sArticleLink";s:70:"http://shopware.example/genusswelten/koestlichkeiten/272/spachtelmasse";s:12:"sOrdernumber";s:7:"SW10239";s:5:"sData";N;}'
WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE' AND `dirty` = 0 AND `content` LIKE '%your article with the order number {$sOrdernumber} is available again.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your email notification',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for signing up for the automatic email notification for the article {$sArticleName}.
Please confirm the notification by clicking the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for signing up for the automatic email notification for the article {$sArticleName}.<br/>
        Please confirm the notification by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:2:{s:12:"sConfirmLink";s:177:"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=notifyConfirm&sNotificationConfirmation=j48FnwtKhMycfizOyYe0CtB0UKzgoeYG&sNotify=1&number=SW10165";s:12:"sArticleName";s:24:"DIE ZEIT 5 Cowhide mokka";}'
WHERE `s_core_config_mails`.`name` = 'sACCEPTNOTIFICATION' AND `dirty` = 0 AND `content` LIKE '%Please confirm the notification by clicking the following link:%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'SEPA direct debit mandate',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

attached you will find the direct debit mandate form for your order {$paymentInstance.orderNumber}. Please return the completely filled out document by fax or email.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        attached you will find the direct debit mandate form for your order {$paymentInstance.orderNumber}. Please return the completely filled out document by fax or email.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 1,
`context` = 'a:2:{s:7:"sConfig";a:0:{}s:15:"paymentInstance";a:3:{s:9:"firstName";s:4:"John";s:8:"lastName";s:3:"Doe";s:11:"orderNumber";s:5:"20003";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSEPAAUTHORIZATION' AND `dirty` = 0 AND `content` LIKE '%attached you will find the direct debit mandate form for your order%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Password change - Password reset',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$user.salutation|salutation} {$user.lastname},

there has been a request to reset you Password in the Shop {$sShop}.
Please confirm the link below to specify a new password.

{$sUrlReset}

This link is valid for the next 2 hours. After that you have to request a new confirmation link.
If you do not want to reset your password, please ignore this email. No changes will be made.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$user.salutation|salutation} {$user.lastname},<br/>
        <br/>
        there has been a request to reset your password in the shop {$sShop}.<br/>
        Please confirm the link below to specify a new password.<br/>
        <br/>
        <a href="{$sUrlReset}">reset password</a><br/>
        <br/>
        This link is valid for the next 2 hours. After that you have to request a new confirmation link.<br/>
        If you do not want to reset your password, please ignore this email. No changes will be made.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:4:{s:9:"sUrlReset";s:83:"http://shopware.example/account/resetPassword/hash/pdiR4nNSvvTYHQGxC0K2PxLk5QtQilXm";s:4:"sUrl";s:45:"http://shopware.example/account/resetPassword";s:4:"sKey";s:32:"pdiR4nNSvvTYHQGxC0K2PxLk5QtQilXm";s:4:"user";a:21:{s:11:"accountmode";s:1:"0";s:6:"active";s:1:"1";s:9:"affiliate";s:1:"0";s:8:"birthday";N;s:15:"confirmationkey";s:0:"";s:13:"customergroup";s:2:"EK";s:14:"customernumber";s:5:"20006";s:5:"email";s:12:"xyz@mail.com";s:12:"failedlogins";s:1:"0";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:8:"language";s:1:"1";s:15:"internalcomment";s:0:"";s:11:"lockeduntil";N;s:9:"subshopID";s:1:"1";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:10:"newsletter";s:1:"0";s:10:"attributes";a:2:{s:2:"id";s:1:"2";s:6:"userID";s:1:"4";}}}'
WHERE `s_core_config_mails`.`name` = 'sCONFIRMPASSWORDCHANGE' AND `dirty` = 0 AND `content` LIKE '%Please confirm the link below to specify a new password.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is in process',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"1";s:8:"statusID";s:1:"1";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:4:"Open";s:11:"status_name";s:10:"in_process";s:18:"status_description";s:10:"In process";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL1' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order at {config name=shopName} is completed',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:4:"Open";s:11:"status_name";s:9:"completed";s:18:"status_description";s:9:"Completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL2' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:9:"completed";s:18:"status_description";s:9:"Completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL11'  AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is ready for delivery',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"5";s:8:"statusID";s:1:"5";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:18:"ready_for_delivery";s:18:"status_description";s:18:"Ready for delivery";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL5' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.


Information on your order:
==================================
{foreach item=details key=position from=$sOrderDetails}
{$position+1|fill:3}      {$details.articleordernumber}     {$details.name|fill:30}     {$details.quantity} x {$details.price|string_format:"%.2f"} {$sOrder.currency}
{/foreach}

Shipping costs: {$sOrder.invoice_shipping|string_format:"%.2f"} {$sOrder.currency}
Net total: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sOrder.currency}
Total amount incl. VAT: {$sOrder.invoice_amount|string_format:"%.2f"} {$sOrder.currency}

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        <strong>Information on your order:</strong></p><br/>
        <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
            <tr>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Article</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art.No.</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Quantities</strong></td>
                <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Price</strong></td>
            </tr>
            {foreach item=details key=position from=$sOrderDetails}
            <tr>
                <td>{$details.name|wordwrap:80|indent:4}</td>
                <td>{$position+1|fill:4} </td>
                <td>{$details.ordernumber|fill:20}</td>
                <td>{$details.quantity|fill:6}</td>
                <td>{$details.price|padding:8} {$sOrder.currency}</td>
            </tr>
            {/foreach}
        </table>
    <p>
        <br/>
        Shipping costs: {$sOrder.invoice_shipping|string_format:"%.2f"} {$sOrder.currency}<br/>
        Net total: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sOrder.currency}<br/>
        Total amount incl. VAT: {$sOrder.invoice_amount|string_format:"%.2f"} {$sOrder.currency}<br/>
        <br/>
    
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"3";s:8:"statusID";s:1:"3";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:19:"partially_completed";s:18:"status_description";s:19:"Partially completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL3' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"8";s:8:"statusID";s:1:"8";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:22:"clarification_required";s:18:"status_description";s:22:"Clarification required";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL8' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is cancelled',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"4";s:8:"statusID";s:1:"4";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:18:"cancelled_rejected";s:18:"status_description";s:18:"Cancelled/rejected";s:19:"payment_description";s:8:"Vorkasse";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL4' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is partially delivered',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:1:"2";s:11:"ordernumber";s:5:"20001";s:12:"order_number";s:5:"20001";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:5:"18.85";s:18:"invoice_amount_net";s:5:"15.84";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-11-03 09:30:46";s:6:"status";s:1:"6";s:8:"statusID";s:1:"6";s:7:"cleared";s:2:"17";s:9:"clearedID";s:2:"17";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:4:"open";s:19:"cleared_description";s:4:"Open";s:11:"status_name";s:19:"partially_delivered";s:18:"status_description";s:19:"Partially delivered";s:19:"payment_description";s:10:"Prepayment";s:20:"dispatch_description";s:17:"Standard shipping";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:1:"2";s:7:"orderID";s:1:"2";s:11:"ordernumber";s:5:"20001";s:9:"articleID";s:3:"153";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"14.95";s:8:"quantity";s:1:"1";s:7:"invoice";s:5:"14.95";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20003";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:2:"11";s:15:"billing_stateID";N;s:15:"billing_country";s:13:"Great britain";s:18:"billing_countryiso";s:2:"GB";s:19:"billing_countryarea";s:13:"Great Britain";s:17:"billing_countryen";s:13:"GREAT BRITAIN";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:1:"2";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:2:"11";s:16:"shipping_country";s:13:"Great britain";s:19:"shipping_countryiso";s:2:"GB";s:20:"shipping_countryarea";s:13:"Great Britain";s:18:"shipping_countryen";s:13:"GREAT BRITAIN";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:60:"$2y$10$t53rA5a4RF4s60OniC28UeOoj.gY.Fg4rdnUlW7dAWsBoLl2UzHC.";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-11-03";s:9:"lastlogin";s:19:"2017-11-03 09:30:46";s:9:"sessionID";s:26:"thh4eiahbd7tr2qk4u8hutu046";s:10:"newsletter";s:1:"0";s:10:"validation";s:0:"";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"1";s:5:"title";N;s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"91dbc52b-9458-4140-b559-ac57b0f8b47e.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard shipping";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL6' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is delivered',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.billing_salutation|salutation} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:12:"order_number";s:5:"20005";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:6:"1201.4";s:18:"invoice_amount_net";s:7:"1009.58";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-30 09:57:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"11";s:9:"clearedID";s:2:"11";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"2";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:14:"partially_paid";s:19:"cleared_description";s:14:"Partially paid";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:20:"Completely delivered";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"218";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:3:"123";s:18:"articleordernumber";s:7:"SW10123";s:5:"price";s:6:"119.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:6:"1199.5";s:4:"name";s:30:"SPEED-HOODY FZ white / cyan XS";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"219";s:7:"orderID";s:2:"64";s:11:"ordernumber";s:5:"20005";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"64";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";N;s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL7' AND `dirty` = 0 AND `content` LIKE '%But in case you have purchased without a registration or a customer account, you do not have this option.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Happy Birthday from {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

we wish you all the best for your birthday.

For your personal anniversary we thought of something special and send you your own birthday code over {if $sVoucher.value}{$sVoucher.value|currency|unescape:"htmlall"}{else}{$sVoucher.percental} %{/if} you can easily redeem in your next order in our online shop: {$sShopURL}.

Your personal birthday code is: {$sVoucher.code}
{if $sVoucher.valid_from && $sVoucher.valid_to}This code is valid from {$sVoucher.valid_from|date_format:"%d/%m/%Y"} to {$sVoucher.valid_to|date_format:"%d/%m/%Y"}.{/if}
{if $sVoucher.valid_from && !$sVoucher.valid_to}This code is valid from {$sVoucher.valid_from|date_format:"%d/%m/%Y"}.{/if}
{if !$sVoucher.valid_from && $sVoucher.valid_to}This code is valid to {$sVoucher.valid_to|date_format:"%d/%m/%Y"}.{/if}


{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
	<p>Dear {$sUser.salutation|salutation} {$sUser.lastname},</p>
	<p><strong>we wish you all the best for your birthday.</strong> For your personal anniversary we thought of something special and send you your own birthday code over {if $sVoucher.value}{$sVoucher.value|currency|unescape:"htmlall"}{else}{$sVoucher.percental} %{/if} you can easily redeem in your next order in our <a href="{$sShopURL}" title="{$sShop}">online shop</a>.</p>
	<p><strong>Your personal birthday code is: <span style="text-decoration:underline;">{$sVoucher.code}</span></strong><br/>
  {if $sVoucher.valid_from && $sVoucher.valid_to}This code is valid from {$sVoucher.valid_from|date_format:"%d/%m/%Y"} to {$sVoucher.valid_to|date_format:"%d/%m/%Y"}.{/if}
  {if $sVoucher.valid_from && !$sVoucher.valid_to}This code is valid from {$sVoucher.valid_from|date_format:"%d/%m/%Y"}.{/if}
  {if !$sVoucher.valid_from && $sVoucher.valid_to}This code is valid to {$sVoucher.valid_to|date_format:"%d/%m/%Y"}.{/if}
</p>

    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:3:{s:5:"sUser";a:28:{s:6:"userID";s:1:"4";s:7:"company";N;s:10:"department";N;s:10:"salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:5:"phone";N;s:9:"countryID";s:1:"2";s:5:"ustid";N;s:5:"text1";N;s:5:"text2";N;s:5:"text3";N;s:5:"text4";N;s:5:"text5";N;s:5:"text6";N;s:5:"email";s:12:"xyz@mail.com";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:10:"newsletter";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";}s:8:"sVoucher";a:6:{s:13:"vouchercodeID";s:3:"101";s:4:"code";s:8:"E4AD5D61";s:5:"value";s:2:"10";s:9:"percental";s:1:"0";s:8:"valid_to";s:10:"2017-12-31";s:10:"valid_from";s:10:"2017-10-22";}s:5:"sData";N;}'
WHERE `s_core_config_mails`.`name` = 'sBIRTHDAY' AND `dirty` = 0 AND `content` LIKE '%For your personal anniversary we thought of something special and send you your own birthday code you can easily redeem in your next order.%';
EOD;

        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Evaluate article',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.billing_lastname},

You bought some products in our store a few days ago, we would really appreciate it if you would rate these products.
Rating products helps us to improve our service and provide your opinion about these products to other customers.

Here you can find the links for rating the products you bought.

Art.No     Description     Rating link
{foreach from=$sArticles item=sArticle key=key}
{if !$sArticle.modus}
{$sArticle.articleordernumber}      {$sArticle.name}      {$sArticle.link_rating_tab}
{/if}
{/foreach}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    Dear {$sUser.salutation|salutation} {$sUser.billing_lastname},<br/>
    <br/>
    You bought some products in our store a few days ago, we would really appreciate it if you would rate these products.<br/>
    Rating products helps us to improve our service and provide your opinion about these products to other customers.<br/>
    <br/>
    Here you can find the links for rating the products you bought.<br/>
    <br/>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Article</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Art.No</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Description</td>
          <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;">Rating link</td>
        </tr>
        {foreach from=$sArticles item=sArticle key=key}
        {if !$sArticle.modus}
            <tr>
                <td style="border-bottom:1px solid #cccccc;">
                  {if $sArticle.image_small && $sArticle.modus == 0}
                    <img style="height: 57px;" height="57" src="{$sArticle.image_small}" alt="{$sArticle.articlename}" />
                  {else}
                  {/if}
                </td>
                <td style="border-bottom:1px solid #cccccc;">{$sArticle.articleordernumber}</td>
                <td style="border-bottom:1px solid #cccccc;">{$sArticle.name}</td>
                <td style="border-bottom:1px solid #cccccc;">
                    <a href="{$sArticle.link_rating_tab}">Link</a>
                </td>
            </tr>
        {/if}
        {/foreach}
    </table>
    <br/><br/>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:3:{s:6:"sOrder";a:38:{s:2:"id";s:2:"66";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:12:"order_number";s:5:"20006";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"701.4";s:18:"invoice_amount_net";s:6:"589.42";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-29 10:00:08";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:19:"cleared_description";s:16:"Komplett bezahlt";s:18:"status_description";s:9:"Completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";}s:5:"sUser";a:76:{s:7:"orderID";s:2:"66";s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";s:10:"2017-10-30";s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sArticles";a:1:{i:222;a:25:{s:14:"orderdetailsID";s:3:"222";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:9:"articleID";s:3:"166";s:18:"articleordernumber";s:7:"SW10153";s:5:"price";s:5:"69.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:5:"699.5";s:4:"name";s:11:"ELASTIC CAP";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:3:"esd";s:1:"0";s:9:"subshopID";s:1:"1";s:8:"language";s:1:"1";s:4:"link";s:42:"http://shopware.example/elastic-muetze-153";s:15:"link_rating_tab";s:57:"http://shopware.example/elastic-muetze-153?jumpTab=rating";s:11:"image_large";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:11:"image_small";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";s:14:"image_original";s:68:"https://www.shopwaredemo.de/media/image/e3/46/f9/SW10153_200x200.jpg";}}}'
WHERE `s_core_config_mails`.`name` = 'sARTICLECOMMENT' AND `dirty` = 0 AND `content` LIKE '%You bought some products in our store a few days ago, we would really appreciate it if you would rate these products.%';
EOD;

        $sql .= <<<'EOD'
    UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Order documents for your order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
`context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:12:"order_number";s:5:"20006";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"701.4";s:18:"invoice_amount_net";s:6:"589.42";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-29 10:00:08";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:15:"Completely paid";s:11:"status_name";s:9:"completed";s:18:"status_description";s:9:"Completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"222";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:9:"articleID";s:3:"166";s:18:"articleordernumber";s:7:"SW10166";s:5:"price";s:5:"69.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:5:"699.5";s:4:"name";s:12:"DIE ZEIT 100";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"223";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"66";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";s:10:"2017-10-30";s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERDOCUMENTS' AND `dirty` = 0 AND `content` LIKE '%In the attachments of this E-Mail you will find the documents for your order in PDF format.%';    
EOD;

        // Move sORDERSEPAAUTHORIZATION to system mails
        $sql .= <<<'EOD'
        UPDATE `s_core_config_mails` SET `s_core_config_mails`.`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sORDERSEPAAUTHORIZATION';
EOD;

        $this->addSql($sql);
    }
}
