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
class Migrations_Migration1436 extends Shopware\Components\Migrations\AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up($modus)
    {
        $this->updateTypeOfDefaultDocumentMailTemplate();

        $installationLanguage = 'empty';

        try {
            $installationLanguage = $this->connection->query('SELECT LOWER(TRIM(`name`)) FROM `s_core_countries_areas` WHERE `id` = 1')->fetchColumn(0);
        } catch (\Exception $ex) {
            // Empty on purpose
        }

        switch ($installationLanguage) {
            case 'deutschland':
                $this->createGermanDocumentMailTemplates();
                break;

            default:
                $this->createEnglishDocumentMailTemplates();
        }
    }

    /**
     * Move the current email template for sending document to the new template category.
     */
    private function updateTypeOfDefaultDocumentMailTemplate()
    {
        $this->addSql(
            'UPDATE `s_core_config_mails`
            SET `mailtype` = 4
            WHERE `name` = \'sORDERDOCUMENTS\''
        );
    }

    /**
     * Create new mail templates for the default document types
     */
    private function createEnglishDocumentMailTemplates()
    {
        $sql = <<<'SQL'
INSERT IGNORE INTO s_core_config_mails (
    stateId,
    name,
    frommail,
    fromname,
    subject,
    content,
    contentHTML,
    ishtml,
    attachment,
    mailtype,
    context,
    dirty
) VALUES (
    NULL,
    'document_invoice',
    '{config name=mail}',
    '{config name=shopName}',
    'Invoice for order {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_delivery_note',
    '{config name=mail}',
    '{config name=shopName}',
    'Delivery note for order {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your delivery note in PDF format.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your delivery note in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_credit',
    '{config name=mail}',
    '{config name=shopName}',
    'Credit note for order {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your credit note in PDF format.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your credit note in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_cancellation',
    '{config name=mail}',
    '{config name=shopName}',
    'Cancellation invoice for order {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your cancellation invoice in PDF format.

{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your cancellation invoice in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
);
SQL;

        $this->addSql($sql);
    }

    /**
     * Create new mail templates for the default document types
     */
    private function createGermanDocumentMailTemplates()
    {
        $sql = <<<'SQL'
INSERT IGNORE INTO s_core_config_mails (
    stateId,
    name,
    frommail,
    fromname,
    subject,
    content,
    contentHTML,
    ishtml,
    attachment,
    mailtype,
    context,
    dirty
) VALUES (
    NULL,
    'document_invoice',
    '{config name=mail}',
    '{config name=shopName}',
    'Rechnung zur Bestellung {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},

vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Rechnung zu Ihrer Bestellung als PDF.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Rechnung zu Ihrer Bestellung als PDF.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_delivery_note',
    '{config name=mail}',
    '{config name=shopName}',
    'Lieferschein zur Bestellung {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},

vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie den Lieferschein zu Ihrer Bestellung als PDF.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie den Lieferschein zu Ihrer Bestellung als PDF.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_credit',
    '{config name=mail}',
    '{config name=shopName}',
    'Gutschrift zur Bestellung {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},

vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Gutschrift zu Ihrer Bestellung als PDF.

{include file="string:{config name=emailfooterplain}"}',
        '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Gutschrift zu Ihrer Bestellung als PDF.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
), (
    NULL,
    'document_cancellation',
    '{config name=mail}',
    '{config name=shopName}',
    'Stornorechnung zur Bestellung {$orderNumber}',
    '{include file="string:{config name=emailheaderplain}"}

Hallo {$sUser.salutation|salutation} {$sUser.lastname},

vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Stornorechnung zu Ihrer Bestellung als PDF.
{include file="string:{config name=emailfooterplain}"}',
    '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hallo {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        vielen Dank für Ihre Bestellung bei {config name=shopName}. Im Anhang finden Sie die Stornorechnung zu Ihrer Bestellung als PDF.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',
    1,
    '',
    4,
    'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:12:"order_number";s:5:"20002";s:6:"userID";s:1:"1";s:10:"customerID";s:1:"1";s:14:"invoice_amount";s:6:"201.86";s:18:"invoice_amount_net";s:6:"169.63";s:16:"invoice_shipping";s:1:"0";s:20:"invoice_shipping_net";s:1:"0";s:9:"ordertime";s:19:"2012-08-31 08:51:46";s:6:"status";s:1:"7";s:8:"statusID";s:1:"7";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"4";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:0:"";s:11:"status_name";s:20:"completely_delivered";s:18:"status_description";s:0:"";s:19:"payment_description";s:0:"";s:20:"dispatch_description";s:0:"";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}s:13:"sOrderDetails";a:1:{i:0;a:20:{s:14:"orderdetailsID";s:3:"204";s:7:"orderID";s:2:"57";s:11:"ordernumber";s:5:"20002";s:9:"articleID";s:3:"197";s:18:"articleordernumber";s:7:"SW10196";s:5:"price";s:5:"34.99";s:8:"quantity";s:1:"2";s:7:"invoice";s:5:"69.98";s:4:"name";s:7:"Artikel";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"1";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"1";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";s:0:"";s:10:"attribute3";s:0:"";s:10:"attribute4";s:0:"";s:10:"attribute5";s:0:"";s:10:"attribute6";s:0:"";}}}s:5:"sUser";a:82:{s:15:"billing_company";s:11:"shopware AG";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20001";s:17:"billing_firstname";s:3:"Max";s:16:"billing_lastname";s:10:"Mustermann";s:14:"billing_street";s:20:"Mustermannstraße 92";s:32:"billing_additional_address_line1";N;s:32:"billing_additional_address_line2";N;s:15:"billing_zipcode";s:5:"48624";s:12:"billing_city";s:12:"Schöppingen";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";s:1:"3";s:15:"billing_country";s:11:"Deutschland";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:11:"deutschland";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"57";s:16:"shipping_company";s:11:"shopware AG";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:3:"Max";s:17:"shipping_lastname";s:10:"Mustermann";s:15:"shipping_street";s:20:"Mustermannstraße 92";s:33:"shipping_additional_address_line1";N;s:33:"shipping_additional_address_line2";N;s:16:"shipping_zipcode";s:5:"48624";s:13:"shipping_city";s:12:"Schöppingen";s:16:"shipping_stateID";s:1:"3";s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:11:"Deutschland";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:11:"deutschland";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"1";s:8:"password";s:0:"";s:7:"encoder";s:3:"md5";s:5:"email";s:16:"test@example.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2011-11-23";s:9:"lastlogin";s:19:"2012-01-04 14:12:05";s:9:"sessionID";s:26:"uiorqd755gaar8dn89ukp178c7";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"1";s:27:"default_shipping_address_id";s:1:"3";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:3:"Max";s:8:"lastname";s:10:"Mustermann";s:8:"birthday";N;s:11:"login_token";N;s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:16:"Standard Versand";s:11:"description";s:0:"";}}',
    0
);
SQL;

        $this->addSql($sql);
    }
}
