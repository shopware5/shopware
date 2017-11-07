-- s_core_paymentmeans --
UPDATE `s_core_paymentmeans` SET description = 'Debit', additionaldescription = '' WHERE name= 'debit';
UPDATE `s_core_paymentmeans` SET description = 'Cash', additionaldescription = '' WHERE name= 'cash';
UPDATE `s_core_paymentmeans` SET description = 'Invoice', additionaldescription = '' WHERE name= 'invoice';
UPDATE `s_core_paymentmeans` SET description = 'Prepayment', additionaldescription = '' WHERE name= 'prepayment';

-- s_campaigns_sender --
UPDATE `s_campaigns_sender` SET `name` = 'Newsletter sender' WHERE id = 1;

-- s_campaigns_groups --
UPDATE `s_campaigns_groups` SET `name` = 'Newsletter recipients' WHERE id = 1;

-- s_core_detail_states --
UPDATE s_core_detail_states SET description = 'Open' WHERE id = 0;
UPDATE s_core_detail_states SET description = 'In progress' WHERE id = 1;
UPDATE s_core_detail_states SET description = 'Cancelled' WHERE id = 2;
UPDATE s_core_detail_states SET description = 'Completed' WHERE id = 3;

-- s_premium_dispatch --
UPDATE s_premium_dispatch SET `name` = 'Standard shipping' WHERE id = 9;

-- s_core_documents --
UPDATE s_core_documents SET name = 'Invoice' WHERE template = 'index.tpl';
UPDATE s_core_documents SET name = 'Delivery note' WHERE template = 'index_ls.tpl';
UPDATE s_core_documents SET name = 'Credit note' WHERE template = 'index_gs.tpl';
UPDATE s_core_documents SET name = 'Cancellation invoice' WHERE template = 'index_sr.tpl';

-- s_core_customergroups --
UPDATE s_core_customergroups SET description = 'Default' WHERE `groupkey` = 'EK';
UPDATE s_core_customergroups SET description = 'B2B / Reseller ' WHERE `groupkey` = 'H';

-- s_core_countries --
UPDATE s_core_countries SET countryname = CONCAT(UPPER(SUBSTRING(countryen, 1,1)),LOWER(SUBSTRING(countryen, 2)));
UPDATE s_core_countries SET active = 1, areaID = 1 WHERE countryiso = 'GB';
UPDATE s_core_countries SET active = 0, areaID = 3 WHERE countryiso = 'DE';

-- s_core_countries_areas --
UPDATE s_core_countries_areas SET name = 'Great Britain' WHERE id = 1;
UPDATE s_core_countries_areas SET name = 'World' WHERE id = 2;
UPDATE s_core_countries_areas SET name = 'Europe' WHERE id = 3;

-- s_categories --
UPDATE s_categories SET `description` = 'English' WHERE `description` = 'Deutsch';

-- s_core_countries_states --
UPDATE s_core_countries_states SET `name` = 'Lower Saxony' WHERE id = 2;
UPDATE s_core_countries_states SET `name` = 'North Rhine-Westphalia' WHERE id = 3;
UPDATE s_core_countries_states SET `name` = 'Baden-Württemberg' WHERE id = 5;
UPDATE s_core_countries_states SET `name` = 'Bavaria' WHERE id = 6;
UPDATE s_core_countries_states SET `name` = 'Berlin' WHERE id = 7;
UPDATE s_core_countries_states SET `name` = 'Brandenburg' WHERE id = 8;
UPDATE s_core_countries_states SET `name` = 'Bremen' WHERE id = 9;
UPDATE s_core_countries_states SET `name` = 'Hamburg' WHERE id = 10;
UPDATE s_core_countries_states SET `name` = 'Hesse' WHERE id = 11;
UPDATE s_core_countries_states SET `name` = 'Mecklenburg-Western Pomerania' WHERE id = 12;
UPDATE s_core_countries_states SET `name` = 'Rhineland-Palatinate' WHERE id = 13;
UPDATE s_core_countries_states SET `name` = 'Saarland' WHERE id = 14;
UPDATE s_core_countries_states SET `name` = 'Saxony' WHERE id = 15;
UPDATE s_core_countries_states SET `name` = 'Saxony-Anhalt' WHERE id = 16;
UPDATE s_core_countries_states SET `name` = 'Schleswig-Holstein' WHERE id = 17;
UPDATE s_core_countries_states SET `name` = 'Thuringia' WHERE id = 18;
UPDATE s_core_countries_states SET `name` = 'Alabama' WHERE id = 20;
UPDATE s_core_countries_states SET `name` = 'Alaska' WHERE id = 21;
UPDATE s_core_countries_states SET `name` = 'Arizona' WHERE id = 22;
UPDATE s_core_countries_states SET `name` = 'Arkansas' WHERE id = 23;
UPDATE s_core_countries_states SET `name` = 'California' WHERE id = 24;
UPDATE s_core_countries_states SET `name` = 'Colorado' WHERE id = 25;
UPDATE s_core_countries_states SET `name` = 'Connecticut' WHERE id = 26;
UPDATE s_core_countries_states SET `name` = 'Delaware' WHERE id = 27;
UPDATE s_core_countries_states SET `name` = 'Florida' WHERE id = 28;
UPDATE s_core_countries_states SET `name` = 'Georgia' WHERE id = 29;
UPDATE s_core_countries_states SET `name` = 'Hawaii' WHERE id = 30;
UPDATE s_core_countries_states SET `name` = 'Idaho' WHERE id = 31;
UPDATE s_core_countries_states SET `name` = 'Illinois' WHERE id = 32;
UPDATE s_core_countries_states SET `name` = 'Indiana' WHERE id = 33;
UPDATE s_core_countries_states SET `name` = 'Iowa' WHERE id = 34;
UPDATE s_core_countries_states SET `name` = 'Kansas' WHERE id = 35;
UPDATE s_core_countries_states SET `name` = 'Kentucky' WHERE id = 36;
UPDATE s_core_countries_states SET `name` = 'Louisiana' WHERE id = 37;
UPDATE s_core_countries_states SET `name` = 'Maine' WHERE id = 38;
UPDATE s_core_countries_states SET `name` = 'Maryland' WHERE id = 39;
UPDATE s_core_countries_states SET `name` = 'Massachusetts' WHERE id = 40;
UPDATE s_core_countries_states SET `name` = 'Michigan' WHERE id = 41;
UPDATE s_core_countries_states SET `name` = 'Minnesota' WHERE id = 42;
UPDATE s_core_countries_states SET `name` = 'Mississippi' WHERE id = 43;
UPDATE s_core_countries_states SET `name` = 'Missouri' WHERE id = 44;
UPDATE s_core_countries_states SET `name` = 'Montana' WHERE id = 45;
UPDATE s_core_countries_states SET `name` = 'Nebraska' WHERE id = 46;
UPDATE s_core_countries_states SET `name` = 'Nevada' WHERE id = 47;
UPDATE s_core_countries_states SET `name` = 'New Hampshire' WHERE id = 48;
UPDATE s_core_countries_states SET `name` = 'New Jersey' WHERE id = 49;
UPDATE s_core_countries_states SET `name` = 'New Mexico' WHERE id = 50;
UPDATE s_core_countries_states SET `name` = 'New York' WHERE id = 51;
UPDATE s_core_countries_states SET `name` = 'North Carolina' WHERE id = 52;
UPDATE s_core_countries_states SET `name` = 'North Dakota' WHERE id = 53;
UPDATE s_core_countries_states SET `name` = 'Ohio' WHERE id = 54;
UPDATE s_core_countries_states SET `name` = 'Oklahoma' WHERE id = 55;
UPDATE s_core_countries_states SET `name` = 'Oregon' WHERE id = 56;
UPDATE s_core_countries_states SET `name` = 'Pennsylvania' WHERE id = 57;
UPDATE s_core_countries_states SET `name` = 'Rhode Iceland' WHERE id = 58;
UPDATE s_core_countries_states SET `name` = 'South Carolina' WHERE id = 59;
UPDATE s_core_countries_states SET `name` = 'South Dakota' WHERE id = 60;
UPDATE s_core_countries_states SET `name` = 'Tennessee' WHERE id = 61;
UPDATE s_core_countries_states SET `name` = 'Texas' WHERE id = 62;
UPDATE s_core_countries_states SET `name` = 'Utah' WHERE id = 63;
UPDATE s_core_countries_states SET `name` = 'Vermont' WHERE id = 64;
UPDATE s_core_countries_states SET `name` = 'Virginia' WHERE id = 65;
UPDATE s_core_countries_states SET `name` = 'Washington' WHERE id = 66;
UPDATE s_core_countries_states SET `name` = 'West Virginia' WHERE id = 67;
UPDATE s_core_countries_states SET `name` = 'Wisconsin' WHERE id = 68;
UPDATE s_core_countries_states SET `name` = 'Wyoming' WHERE id = 69;

-- s_core_config_mails --
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your registration at {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {$salutation} {$firstname} {$lastname},

thank you for your registration with our Shop.

You will gain access via the email address {$sMAIL}
and the password you have chosen.

You can have your password sent to you by email anytime.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$salutation} {$firstname} {$lastname},<br/>
        <br/>
        thank you for your registration with our Shop.<br/>
        <br/>
        You will gain access via the email address <strong>{$sMAIL}</strong><br/>
        and the password you have chosen.<br/>
        <br/>
        You can have your password sent to you by email anytime.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sREGISTERCONFIRMATION';

UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with the {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {if $billingaddress.salutation eq "mr"}Mr{elseif $billingaddress.salutation eq "ms"}Mrs{/if} {$billingaddress.firstname} {$billingaddress.lastname},

Thank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.
Information on your order:

Pos. Art.No.              Quantities         Price        Total
{foreach item=details key=position from=$sOrderDetails}
{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR
{$details.articlename|wordwrap:49|indent:5}
{/foreach}

Shipping costs: {$sShippingCosts}
Net total: {$sAmountNet}
{if !$sNet}
{foreach $sTaxRates as $rate => $value}
plus {$rate}% VAT {$value|currency}
{/foreach}
Total gross: {$sAmount}
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
{$billingaddress.phone}
{$additional.country.countryname}

Shipping address:
{$shippingaddress.company}
{$shippingaddress.firstname} {$shippingaddress.lastname}
{$shippingaddress.street} {$shippingaddress.streetnumber}
{if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}
{$additional.countryShipping.countryname}

{if $billingaddress.ustid}
Your VAT-ID: {$billingaddress.ustid}
In case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax.{/if}

If you have any questions, do not hesitate to contact us.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>Hello {if $billingaddress.salutation eq "mr"}Mr{elseif $billingaddress.salutation eq "ms"}Mrs{/if} {$billingaddress.firstname} {$billingaddress.lastname},<br/>
        <br/>
        Thank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.<br/>
        <br/>
        <strong>Information on your order:</strong></p><br/>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        <tr>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Article</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Pos.</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Art.No.</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Quantities</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Price</strong></td>
            <td bgcolor="#F7F7F2" style="border-bottom:1px solid #cccccc;"><strong>Total</strong></td>
        </tr>

        {foreach item=details key=position from=$sOrderDetails}
        <tr>
            <td rowspan="2" style="border-bottom:1px solid #cccccc;">{if $details.image.src.0 && $details.modus == 0}<img style="height: 57px;" height="57" src="{$details.image.src.0}" alt="{$details.articlename}" />{else} {/if}</td>
            <td>{$position+1|fill:4} </td>
            <td>{$details.ordernumber|fill:20}</td>
            <td>{$details.quantity|fill:6}</td>
            <td>{$details.price|padding:8}{$sCurrency}</td>
            <td>{$details.amount|padding:8} {$sCurrency}</td>
        </tr>
        <tr>
            <td colspan="5" style="border-bottom:1px solid #cccccc;">{$details.articlename|wordwrap:80|indent:4}</td>
        </tr>
        {/foreach}
        
    </table>

    <p>
        <br/>
        <br/>
        Shipping costs: {$sShippingCosts}<br/>
        Net total: {$sAmountNet}<br/>
        {if !$sNet}
        {foreach $sTaxRates as $rate => $value}
        plus {$rate}% VAT {$value|currency}<br/>
        {/foreach}
        <strong>Total gross: {$sAmount}</strong><br/>
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
        {$sDispatch.description}<br/>
        <br/>
    </p>
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
        {$billingaddress.phone}<br/>
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
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sORDER';

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
        <br/>
    </p>
    {if $sComment}
        <div style="border: 2px solid black; border-radius: 5px; padding: 5px;"><p>{$sComment}</p></div><br/>
    {/if}
    
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND';
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
        <br/>
    </p>
    <strong>{$sArticleName}</strong><br/>
    <p>
        Please provide new serial numbers immediately or deactivate the article.<br/>
        Please assign a serial number to the customer {$sMail} manually.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sNOSERIALS';
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
        <strong>Your voucher code is as follows: XXX</strong><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sVOUCHER';
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
        <br/>
        From now on, we will charge you the net purchase price.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHACCEPTED';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your trader account has not been accepted',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear customer,

thank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.

In case of further questions please do not hesitate to contact us via telephone, fax or email.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear customer,<br/>
		<br/>
        thank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.<br/>
        <br/>
        In case of further questions please do not hesitate to contact us via telephone, fax or email.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHREJECTED';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your aborted order process - Send us your feedback and get a voucher!',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear customer,

You have recently aborted an order process on {$sShop} - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.

Please tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher.

Thank you for your feedback.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear customer,<br/>
        <br/>
        You have recently aborted an order process on {$sShop} - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.<br/>
        <br/>
        Please tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher.<br/>
        <br/>
        Thank you for your feedback.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sCANCELEDQUESTION';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your aborted order process - Voucher code enclosed',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear customer,

You have recently aborted an order process on {$sShop} - today, we would like to give you a {$sVouchervalue} {if $sVoucherpercental == "1"}%{else}€{/if}-voucher - and therefore make it easier for you to decide for an order with {$sShop}.

Your voucher is valid for two months and can be redeemed by entering the code "{$sVouchercode}".

We would be pleased to accept your order!

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
         Dear customer,<br/>
         <br/>
         You have recently aborted an order process on {$sShop} - today, we would like to give you a 5{$sVouchervalue} {if $sVoucherpercental == "1"}%{else}€{/if}-voucher - and therefore make it easier for you to decide for an order with {$sShop}.<br/>
         <br/>
         Your voucher is valid for two months and can be redeemed by entering the code "<strong>{$sVouchercode}</strong>".<br/>
         <br/>
         We would be pleased to accept your order!<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sCANCELEDVOUCHER';


UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL9';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL10';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '1st reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

this is your first reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        this is your first reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL13';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Encashment of your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

You have now received 3 reminders for your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

You will receive shortly post from an encashment company!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        You have now received 3 reminders for your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You will receive shortly post from an encashment company!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL16';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '3rd reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

this is your third and last reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        this is your third and last reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL15';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = '2nd reminder for your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

this is your second reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!
The new payment status is as follows: {$sOrder.cleared_description}.

Please pay your invoice as fast as possible!

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        this is your second reminder of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"}!<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        Please pay your invoice as fast as possible!<br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL14';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is completely paid',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL12';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL17';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL18';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is delayed',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL19';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL20';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Stock level of {$sData.count} article{if $sData.count>1}s{/if} under minimum stock ',`content` = '
Hello,

the following articles have undershot the minimum stock:

Order number Name of article Stock/Minimum stock
{foreach from=$sJob.articles item=sArticle key=key}
{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin}
{/foreach}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        the following articles have undershot the minimum stock:<br/>
        <br/>
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
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sARTICLESTOCK';

UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Thank you for your newsletter subscription',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for your newsletter subscription at {config name=shopName}.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for your newsletter subscription at {config name=shopName}.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sNEWSLETTERCONFIRMATION';
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
        <br/>
        Please confirm your subscription by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sOPTINNEWSLETTER';
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
        <br/>
        Please confirm the evaluation by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sOPTINVOTE';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your article is available again',`content` = '
Hello,

your article with the order number {$sOrdernumber} is available again.

{$sArticleLink}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        your article with the order number {$sOrdernumber} is available again.<br/>
        <br/>
        <a href="{$sArticleLink}">{$sOrdernumber}</a><br/>
    </p>
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your e-mail notification',`content` = '{include file="string:{config name=emailheaderplain}"}

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
        <br/>
        Please confirm the notification by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sACCEPTNOTIFICATION';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'SEPA direct debit mandate',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {$paymentInstance.firstName} {$paymentInstance.lastName},

attached you will find the direct debit mandate form for your order {$paymentInstance.orderNumber}. Please return the completely filled out document by fax or email.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$paymentInstance.firstName} {$paymentInstance.lastName},<br/>
        <br/>
        attached you will find the direct debit mandate form for your order {$paymentInstance.orderNumber}. Please return the completely filled out document by fax or email.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 1 WHERE `s_core_config_mails`.`name` = 'sORDERSEPAAUTHORIZATION';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Password change - Password reset',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

there has been a request to reset you Password in the Shop {$sShop}.

Please confirm the link below to specify a new password.

{$sUrlReset}

This link is valid for the next 2 hours. After that you have to request a new confirmation link.

If you do not want to reset your password, please ignore this email. No changes will be made.

{config name=address}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        there has been a request to reset your password in the shop {$sShop}.
        Please confirm the link below to specify a new password.<br/>
        <br/>
        <a href="{$sUrlReset}">reset password</a><br/>
        <br/>
        This link is valid for the next 2 hours. After that you have to request a new confirmation link.
        If you do not want to reset your password, please ignore this email. No changes will be made.<br/>
        <br/>
        {config name=address}<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sCONFIRMPASSWORDCHANGE';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is in process',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL1';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order at {config name=shopName} is completed',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL2';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new payment status is as follows: {$sOrder.cleared_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new payment status is as follows: {$sOrder.cleared_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL11';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is ready for delivery',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL5';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.


Information on your order:
==================================
{foreach item=details key=position from=$sOrderDetails}
{$position+1|fill:3} {$details.articleordernumber|fill:10:" ":"..."} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:"%.2f"} {$sOrder.currency}
{/foreach}

Shipping costs: {$sOrder.invoice_shipping|string_format:"%.2f"} {$sOrder.currency}
Net total: {$sOrder.invoice_amount_net|string_format:"%.2f"} {$sOrder.currency}
Total amount incl. VAT: {$sOrder.invoice_amount|string_format:"%.2f"} {$sOrder.currency}

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
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
    
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL3';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL8';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is cancelled',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL4';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is partially delivered',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL6';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order with {config name=shopName} is delivered',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.
The new status is as follows: {$sOrder.status_description}.

You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
        <br/>
        the status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:"%d/%m/%Y"} has changed.<br/>
        <strong>The new status is as follows: {$sOrder.status_description}.</strong><br/>
        <br/>
        You can check the current status of your order on our website under "My account" - "My orders" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 3 WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL7';

UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Happy Birthday from {config name=shopName}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation} {$sUser.lastname},

we wish you all the best for your birthday.

For your personal anniversary we thought of something special and send you your own birthday code you can easily redeem in your next order.

Your personal birthday code is: {$sVoucher.code}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
	<p>Dear {$sUser.salutation} {$sUser.lastname},</p>
	<p><strong>we wish you all the best for your birthday. </strong>. For your personal anniversary we thought of something special and send you your own birthday code you can easily redeem in your next order in our <a href="{$sShopURL}" title="{$sShop}">online hop</a>.</p>
	<p><strong>Your personal birthday code is: <span style="text-decoration:underline;">{$sVoucher.code}</span></strong></p>

    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sBIRTHDAY';

UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Evaluate article',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {if $sUser.salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},

You bought some products in our store a few days ago, we would really appreciate it if you would rate these products.
Rating products helps us to improve our service and provide your opinion about these products to other customers.

Here you can find the links for rating the products you bought.

{foreach from=$sArticles item=sArticle key=key}
{if !$sArticle.modus}
{$sArticle.articleordernumber} - {$sArticle.name}: {$sArticle.link}
{/if}
{/foreach}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    Hello {if $sUser.salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},<br/>
    <br/>
    You bought some products in our store a few days ago, we would really appreciate it if you would rate these products.<br/>
    Rating products helps us to improve our service and provide your opinion about these products to other customers.<br/>
    <br/>
    Here you can find the links for rating the products you bought.<br/>
    <table width="80%" border="0" style="font-family:Arial, Helvetica, sans-serif; font-size:12px;">
        {foreach from=$sArticles item=sArticle key=key}
        {if !$sArticle.modus}
            <tr>
                <td>{$sArticle.articleordernumber}</td>
                <td>{$sArticle.name}</td>
                <td>
                    <a href="{$sArticle.link}">link</a>
                </td>
            </tr>
        {/if}
        {/foreach}
    </table>
    <br/><br/>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sARTICLECOMMENT';
UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Documents for order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello {$sUser.salutation|salutation} {$sUser.firstname} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this E-Mail you will find the documents for your order in PDF format.

We wish you a nice day.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello {$sUser.salutation|salutation} {$sUser.firstname} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this E-Mail you will find the documents for your order in PDF format.<br/>
        <br/>
        We wish you a nice day.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2 WHERE `s_core_config_mails`.`name` = 'sORDERDOCUMENTS';

-- s_cms_static_groups --
UPDATE s_cms_static_groups SET `name` = 'German left pane and service/support top' WHERE id = 1;
UPDATE s_cms_static_groups SET `name` = 'German bottom pane (Shop service)' WHERE id = 2;
UPDATE s_cms_static_groups SET `name` = 'German bottom pane (Information)' WHERE id = 3;
UPDATE s_cms_static_groups SET `name` = 'In progress' WHERE id = 4;
UPDATE s_cms_static_groups SET `name` = 'English left pane and service/support top' WHERE id = 7;
UPDATE s_cms_static_groups SET `name` = 'English bottom pane (Shop service)' WHERE id = 9;
UPDATE s_cms_static_groups SET `name` = 'English bottom pane (Information)' WHERE id = 10;

-- s_core_shop_pages --
INSERT INTO `s_core_shop_pages` (`shop_id`, `group_id`) VALUES
  (1, 7),
  (1, 9),
  (1, 10);

-- s_core_locales --
UPDATE s_core_locales SET language = 'German', territory = 'Germany' WHERE locale = 'de_DE';
UPDATE s_core_locales SET language = 'English', territory = 'United Kingdom' WHERE locale = 'en_GB';
UPDATE s_core_locales SET language = 'Afar', territory = 'Djibouti' WHERE locale = 'aa_DJ';
UPDATE s_core_locales SET language = 'Afar', territory = 'Eritrea' WHERE locale = 'aa_ER';
UPDATE s_core_locales SET language = 'Afar', territory = 'Ethiopia' WHERE locale = 'aa_ET';
UPDATE s_core_locales SET language = 'Afrikaans', territory = 'Namibia' WHERE locale = 'af_NA';
UPDATE s_core_locales SET language = 'Afrikaans', territory = 'South Africa' WHERE locale = 'af_ZA';
UPDATE s_core_locales SET language = 'Akan', territory = 'Ghana' WHERE locale = 'ak_GH';
UPDATE s_core_locales SET language = 'Amharic', territory = 'Ethiopia' WHERE locale = 'am_ET';
UPDATE s_core_locales SET language = 'Arabic', territory = 'United Arab Emirates' WHERE locale = 'ar_AE';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Bahrain' WHERE locale = 'ar_BH';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Algeria' WHERE locale = 'ar_DZ';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Egypt' WHERE locale = 'ar_EG';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Iraq' WHERE locale = 'ar_IQ';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Jordan' WHERE locale = 'ar_JO';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Kuwait' WHERE locale = 'ar_KW';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Lebanon' WHERE locale = 'ar_LB';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Libya' WHERE locale = 'ar_LY';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Morocco' WHERE locale = 'ar_MA';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Oman' WHERE locale = 'ar_OM';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Qatar' WHERE locale = 'ar_QA';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Saudi Arabia' WHERE locale = 'ar_SA';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Sudan' WHERE locale = 'ar_SD';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Syria' WHERE locale = 'ar_SY';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Tunisia' WHERE locale = 'ar_TN';
UPDATE s_core_locales SET language = 'Arabic', territory = 'Yemen' WHERE locale = 'ar_YE';
UPDATE s_core_locales SET language = 'Assamese', territory = 'India' WHERE locale = 'as_IN';
UPDATE s_core_locales SET language = 'Azerbaijani', territory = 'Azerbaijan' WHERE locale = 'az_AZ';
UPDATE s_core_locales SET language = 'Belarusian', territory = 'Belarus' WHERE locale = 'be_BY';
UPDATE s_core_locales SET language = 'Bulgarian', territory = 'Bulgaria' WHERE locale = 'bg_BG';
UPDATE s_core_locales SET language = 'Bengali', territory = 'Bangladesh' WHERE locale = 'bn_BD';
UPDATE s_core_locales SET language = 'Bengali', territory = 'India' WHERE locale = 'bn_IN';
UPDATE s_core_locales SET language = 'Tibetan', territory = 'China' WHERE locale = 'bo_CN';
UPDATE s_core_locales SET language = 'Tibetan', territory = 'India' WHERE locale = 'bo_IN';
UPDATE s_core_locales SET language = 'Bosnian', territory = 'Bosnia and Herzegovina' WHERE locale = 'bs_BA';
UPDATE s_core_locales SET language = 'Blin', territory = 'Eritrea' WHERE locale = 'byn_ER';
UPDATE s_core_locales SET language = 'Catalan', territory = 'Spain' WHERE locale = 'ca_ES';
UPDATE s_core_locales SET language = 'Atsam', territory = 'Nigeria' WHERE locale = 'cch_NG';
UPDATE s_core_locales SET language = 'Czech', territory = 'Czech Republic' WHERE locale = 'cs_CZ';
UPDATE s_core_locales SET language = 'Welsh', territory = 'United Kingdom' WHERE locale = 'cy_GB';
UPDATE s_core_locales SET language = 'Danish', territory = 'Denmark' WHERE locale = 'da_DK';
UPDATE s_core_locales SET language = 'German', territory = 'Austria' WHERE locale = 'de_AT';
UPDATE s_core_locales SET language = 'German', territory = 'Belgium' WHERE locale = 'de_BE';
UPDATE s_core_locales SET language = 'German', territory = 'Switzerland' WHERE locale = 'de_CH';
UPDATE s_core_locales SET language = 'German', territory = 'Liechtenstein' WHERE locale = 'de_LI';
UPDATE s_core_locales SET language = 'German', territory = 'Luxemburg' WHERE locale = 'de_LU';
UPDATE s_core_locales SET language = 'Maldivian', territory = 'Maldive Islands' WHERE locale = 'dv_MV';
UPDATE s_core_locales SET language = 'Bhutanese', territory = 'Bhutan' WHERE locale = 'dz_BT';
UPDATE s_core_locales SET language = 'Ewe', territory = 'Ghana' WHERE locale = 'ee_GH';
UPDATE s_core_locales SET language = 'Ewe', territory = 'Togo' WHERE locale = 'ee_TG';
UPDATE s_core_locales SET language = 'Greek', territory = 'Cyprus' WHERE locale = 'el_CY';
UPDATE s_core_locales SET language = 'Greek', territory = 'Greece' WHERE locale = 'el_GR';
UPDATE s_core_locales SET language = 'English', territory = 'American Samoa' WHERE locale = 'en_AS';
UPDATE s_core_locales SET language = 'English', territory = 'Australia' WHERE locale = 'en_AU';
UPDATE s_core_locales SET language = 'English', territory = 'Belgium' WHERE locale = 'en_BE';
UPDATE s_core_locales SET language = 'English', territory = 'Botswana' WHERE locale = 'en_BW';
UPDATE s_core_locales SET language = 'English', territory = 'Belize' WHERE locale = 'en_BZ';
UPDATE s_core_locales SET language = 'English', territory = 'Canada' WHERE locale = 'en_CA';
UPDATE s_core_locales SET language = 'English', territory = 'Guam' WHERE locale = 'en_GU';
UPDATE s_core_locales SET language = 'English', territory = 'Hong Kong' WHERE locale = 'en_HK';
UPDATE s_core_locales SET language = 'English', territory = 'Ireland' WHERE locale = 'en_IE';
UPDATE s_core_locales SET language = 'English', territory = 'India' WHERE locale = 'en_IN';
UPDATE s_core_locales SET language = 'English', territory = 'Jamaica' WHERE locale = 'en_JM';
UPDATE s_core_locales SET language = 'English', territory = 'Marshall Islands' WHERE locale = 'en_MH';
UPDATE s_core_locales SET language = 'English', territory = 'Northern Mariana Islands' WHERE locale = 'en_MP';
UPDATE s_core_locales SET language = 'English', territory = 'Malta' WHERE locale = 'en_MT';
UPDATE s_core_locales SET language = 'English', territory = 'Namibia' WHERE locale = 'en_NA';
UPDATE s_core_locales SET language = 'English', territory = 'New Zealand' WHERE locale = 'en_NZ';
UPDATE s_core_locales SET language = 'English', territory = 'Philippines' WHERE locale = 'en_PH';
UPDATE s_core_locales SET language = 'English', territory = 'Pakistan' WHERE locale = 'en_PK';
UPDATE s_core_locales SET language = 'English', territory = 'Singapore' WHERE locale = 'en_SG';
UPDATE s_core_locales SET language = 'English', territory = 'Trinidad and Tobago' WHERE locale = 'en_TT';
UPDATE s_core_locales SET language = 'English', territory = 'U.S. Minor Outlying Islands' WHERE locale = 'en_UM';
UPDATE s_core_locales SET language = 'English', territory = 'United States' WHERE locale = 'en_US';
UPDATE s_core_locales SET language = 'English', territory = 'American Virgin Islands' WHERE locale = 'en_VI';
UPDATE s_core_locales SET language = 'English', territory = 'South Africa' WHERE locale = 'en_ZA';
UPDATE s_core_locales SET language = 'English', territory = 'Zimbabwe' WHERE locale = 'en_ZW';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Argentina' WHERE locale = 'es_AR';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Bolivia' WHERE locale = 'es_BO';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Chile' WHERE locale = 'es_CL';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Colombia' WHERE locale = 'es_CO';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Costa Rica' WHERE locale = 'es_CR';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Dominican Republic' WHERE locale = 'es_DO';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Ecuador' WHERE locale = 'es_EC';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Spain' WHERE locale = 'es_ES';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Guatemala' WHERE locale = 'es_GT';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Honduras' WHERE locale = 'es_HN';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Mexico' WHERE locale = 'es_MX';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Nicaragua' WHERE locale = 'es_NI';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Panama' WHERE locale = 'es_PA';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Peru' WHERE locale = 'es_PE';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Puerto Rico' WHERE locale = 'es_PR';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Paraguay' WHERE locale = 'es_PY';
UPDATE s_core_locales SET language = 'Spanish', territory = 'El Salvador' WHERE locale = 'es_SV';
UPDATE s_core_locales SET language = 'Spanish', territory = 'United States' WHERE locale = 'es_US';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Uruguay' WHERE locale = 'es_UY';
UPDATE s_core_locales SET language = 'Spanish', territory = 'Venezuela' WHERE locale = 'es_VE';
UPDATE s_core_locales SET language = 'Estonian', territory = 'Estonia' WHERE locale = 'et_EE';
UPDATE s_core_locales SET language = 'Basque', territory = 'Spain' WHERE locale = 'eu_ES';
UPDATE s_core_locales SET language = 'Persian', territory = 'Afghanistan' WHERE locale = 'fa_AF';
UPDATE s_core_locales SET language = 'Persian', territory = 'Iran' WHERE locale = 'fa_IR';
UPDATE s_core_locales SET language = 'Finnish', territory = 'Finland' WHERE locale = 'fi_FI';
UPDATE s_core_locales SET language = 'Filipino', territory = 'Philippines' WHERE locale = 'fil_PH';
UPDATE s_core_locales SET language = 'Faeroese', territory = 'Faeroe Islands' WHERE locale = 'fo_FO';
UPDATE s_core_locales SET language = 'French', territory = 'Belgium' WHERE locale = 'fr_BE';
UPDATE s_core_locales SET language = 'French', territory = 'Canada' WHERE locale = 'fr_CA';
UPDATE s_core_locales SET language = 'French', territory = 'Switzerland' WHERE locale = 'fr_CH';
UPDATE s_core_locales SET language = 'French', territory = 'France' WHERE locale = 'fr_FR';
UPDATE s_core_locales SET language = 'French', territory = 'Luxembourg' WHERE locale = 'fr_LU';
UPDATE s_core_locales SET language = 'French', territory = 'Monaco' WHERE locale = 'fr_MC';
UPDATE s_core_locales SET language = 'French', territory = 'Senegal' WHERE locale = 'fr_SN';
UPDATE s_core_locales SET language = 'Friulian', territory = 'Italy' WHERE locale = 'fur_IT';
UPDATE s_core_locales SET language = 'Irish', territory = 'Ireland' WHERE locale = 'ga_IE';
UPDATE s_core_locales SET language = 'Ga', territory = 'Ghana' WHERE locale = 'gaa_GH';
UPDATE s_core_locales SET language = 'Geez', territory = 'Eritrea' WHERE locale = 'gez_ER';
UPDATE s_core_locales SET language = 'Geez', territory = 'Ethiopia' WHERE locale = 'gez_ET';
UPDATE s_core_locales SET language = 'Galician', territory = 'Spain' WHERE locale = 'gl_ES';
UPDATE s_core_locales SET language = 'Swiss German', territory = 'Switzerland' WHERE locale = 'gsw_CH';
UPDATE s_core_locales SET language = 'Gujarati', territory = 'India' WHERE locale = 'gu_IN';
UPDATE s_core_locales SET language = 'Manx', territory = 'United Kingdom' WHERE locale = 'gv_GB';
UPDATE s_core_locales SET language = 'Hausa', territory = 'Ghana' WHERE locale = 'ha_GH';
UPDATE s_core_locales SET language = 'Hausa', territory = 'Niger' WHERE locale = 'ha_NE';
UPDATE s_core_locales SET language = 'Hausa', territory = 'Nigeria' WHERE locale = 'ha_NG';
UPDATE s_core_locales SET language = 'Hausa', territory = 'Sudan' WHERE locale = 'ha_SD';
UPDATE s_core_locales SET language = 'Hawaiian', territory = 'United States' WHERE locale = 'haw_US';
UPDATE s_core_locales SET language = 'Hebrew', territory = 'Israel' WHERE locale = 'he_IL';
UPDATE s_core_locales SET language = 'Hindi', territory = 'India' WHERE locale = 'hi_IN';
UPDATE s_core_locales SET language = 'Croatian', territory = 'Croatia' WHERE locale = 'hr_HR';
UPDATE s_core_locales SET language = 'Hungarian', territory = 'Hungary' WHERE locale = 'hu_HU';
UPDATE s_core_locales SET language = 'Armenian', territory = 'Armenia' WHERE locale = 'hy_AM';
UPDATE s_core_locales SET language = 'Indonesian', territory = 'Indonesia' WHERE locale = 'id_ID';
UPDATE s_core_locales SET language = 'Igbo', territory = 'Nigeria' WHERE locale = 'ig_NG';
UPDATE s_core_locales SET language = 'Sichuan Yi', territory = 'China' WHERE locale = 'ii_CN';
UPDATE s_core_locales SET language = 'Icelandic', territory = 'Iceland' WHERE locale = 'is_IS';
UPDATE s_core_locales SET language = 'Italian', territory = 'Switzerland' WHERE locale = 'it_CH';
UPDATE s_core_locales SET language = 'Italian', territory = 'Italy' WHERE locale = 'it_IT';
UPDATE s_core_locales SET language = 'Japanese', territory = 'Japan' WHERE locale = 'ja_JP';
UPDATE s_core_locales SET language = 'Georgian', territory = 'Georgia' WHERE locale = 'ka_GE';
UPDATE s_core_locales SET language = 'Jju', territory = 'Nigeria' WHERE locale = 'kaj_NG';
UPDATE s_core_locales SET language = 'Kamba', territory = 'Kenya' WHERE locale = 'kam_KE';
UPDATE s_core_locales SET language = 'Tyap', territory = 'Nigeria' WHERE locale = 'kcg_NG';
UPDATE s_core_locales SET language = 'Koro', territory = 'Ivory Coast' WHERE locale = 'kfo_CI';
UPDATE s_core_locales SET language = 'Kazakh', territory = 'Kazakhstan' WHERE locale = 'kk_KZ';
UPDATE s_core_locales SET language = 'Greenlandic', territory = 'Greenland' WHERE locale = 'kl_GL';
UPDATE s_core_locales SET language = 'Cambodian', territory = 'Cambodia' WHERE locale = 'km_KH';
UPDATE s_core_locales SET language = 'Kannada', territory = 'India' WHERE locale = 'kn_IN';
UPDATE s_core_locales SET language = 'Korean', territory = 'Republic of Korea' WHERE locale = 'ko_KR';
UPDATE s_core_locales SET language = 'Konkani', territory = 'India' WHERE locale = 'kok_IN';
UPDATE s_core_locales SET language = 'Kpelle', territory = 'Guinea' WHERE locale = 'kpe_GN';
UPDATE s_core_locales SET language = 'Kpelle', territory = 'Liberia' WHERE locale = 'kpe_LR';
UPDATE s_core_locales SET language = 'Kurdish', territory = 'Iraq' WHERE locale = 'ku_IQ';
UPDATE s_core_locales SET language = 'Kurdish', territory = 'Iran' WHERE locale = 'ku_IR';
UPDATE s_core_locales SET language = 'Kurdish', territory = 'Syria' WHERE locale = 'ku_SY';
UPDATE s_core_locales SET language = 'Kurdish', territory = 'Turkey' WHERE locale = 'ku_TR';
UPDATE s_core_locales SET language = 'Cornish', territory = 'United Kingdom' WHERE locale = 'kw_GB';
UPDATE s_core_locales SET language = 'Kirghiz', territory = 'Kyrgyzstan' WHERE locale = 'ky_KG';
UPDATE s_core_locales SET language = 'Lingala', territory = 'Democratic Republic of the Congo' WHERE locale = 'ln_CD';
UPDATE s_core_locales SET language = 'Lingala', territory = 'Congo' WHERE locale = 'ln_CG';
UPDATE s_core_locales SET language = 'Lao', territory = 'Laos' WHERE locale = 'lo_LA';
UPDATE s_core_locales SET language = 'Lithuanian', territory = 'Lithuania' WHERE locale = 'lt_LT';
UPDATE s_core_locales SET language = 'Latvian', territory = 'Lettland' WHERE locale = 'lv_LV';
UPDATE s_core_locales SET language = 'Macedonian', territory = 'Macedonia' WHERE locale = 'mk_MK';
UPDATE s_core_locales SET language = 'Malayalam', territory = 'India' WHERE locale = 'ml_IN';
UPDATE s_core_locales SET language = 'Mongol', territory = 'China' WHERE locale = 'mn_CN';
UPDATE s_core_locales SET language = 'Mongol', territory = 'Mongolia' WHERE locale = 'mn_MN';
UPDATE s_core_locales SET language = 'Marathi', territory = 'India' WHERE locale = 'mr_IN';
UPDATE s_core_locales SET language = 'Malay', territory = 'Brunei Darussalam' WHERE locale = 'ms_BN';
UPDATE s_core_locales SET language = 'Malay', territory = 'Malaysia' WHERE locale = 'ms_MY';
UPDATE s_core_locales SET language = 'Maltese', territory = 'Malta' WHERE locale = 'mt_MT';
UPDATE s_core_locales SET language = 'Burmese', territory = 'Myanmar' WHERE locale = 'my_MM';
UPDATE s_core_locales SET language = 'Norwegian Bokmål', territory = 'Norway' WHERE locale = 'nb_NO';
UPDATE s_core_locales SET language = 'Low German', territory = 'Germany' WHERE locale = 'nds_DE';
UPDATE s_core_locales SET language = 'Nepalese', territory = 'India' WHERE locale = 'ne_IN';
UPDATE s_core_locales SET language = 'Nepalese', territory = 'Nepal' WHERE locale = 'ne_NP';
UPDATE s_core_locales SET language = 'Dutch', territory = 'Belgium' WHERE locale = 'nl_BE';
UPDATE s_core_locales SET language = 'Dutch', territory = 'Netherlands' WHERE locale = 'nl_NL';
UPDATE s_core_locales SET language = 'Norwegian Nynorsk', territory = 'Norway' WHERE locale = 'nn_NO';
UPDATE s_core_locales SET language = 'Southern Ndebele', territory = 'South Africa' WHERE locale = 'nr_ZA';
UPDATE s_core_locales SET language = 'Northern Sotho language', territory = 'South Africa' WHERE locale = 'nso_ZA';
UPDATE s_core_locales SET language = 'Nyanja', territory = 'Malawi' WHERE locale = 'ny_MW';
UPDATE s_core_locales SET language = 'Occitan', territory = 'France' WHERE locale = 'oc_FR';
UPDATE s_core_locales SET language = 'Oromo', territory = 'Ethiopia' WHERE locale = 'om_ET';
UPDATE s_core_locales SET language = 'Oromo', territory = 'Kenya' WHERE locale = 'om_KE';
UPDATE s_core_locales SET language = 'Orija', territory = 'India' WHERE locale = 'or_IN';
UPDATE s_core_locales SET language = 'Punjabi', territory = 'India' WHERE locale = 'pa_IN';
UPDATE s_core_locales SET language = 'Punjabi', territory = 'Pakistan' WHERE locale = 'pa_PK';
UPDATE s_core_locales SET language = 'Polish', territory = 'Poland' WHERE locale = 'pl_PL';
UPDATE s_core_locales SET language = 'Pashto', territory = 'Afghanistan' WHERE locale = 'ps_AF';
UPDATE s_core_locales SET language = 'Portuguese', territory = 'Brazil' WHERE locale = 'pt_BR';
UPDATE s_core_locales SET language = 'Portuguese', territory = 'Portugal' WHERE locale = 'pt_PT';
UPDATE s_core_locales SET language = 'Romanian', territory = 'Republic of Moldova' WHERE locale = 'ro_MD';
UPDATE s_core_locales SET language = 'Romanian', territory = 'Romania' WHERE locale = 'ro_RO';
UPDATE s_core_locales SET language = 'Russian', territory = 'Russian Federation' WHERE locale = 'ru_RU';
UPDATE s_core_locales SET language = 'Russian', territory = 'Ukraine' WHERE locale = 'ru_UA';
UPDATE s_core_locales SET language = 'Rwandan', territory = 'Rwanda' WHERE locale = 'rw_RW';
UPDATE s_core_locales SET language = 'Sanskrit', territory = 'India' WHERE locale = 'sa_IN';
UPDATE s_core_locales SET language = 'Northen Samian', territory = 'Finland' WHERE locale = 'se_FI';
UPDATE s_core_locales SET language = 'Northen Samian', territory = 'Norway' WHERE locale = 'se_NO';
UPDATE s_core_locales SET language = 'Serbo-Croat', territory = 'Bosnia and Herzegovina' WHERE locale = 'sh_BA';
UPDATE s_core_locales SET language = 'Serbo-Croat', territory = 'Serbia and Montenegro' WHERE locale = 'sh_CS';
UPDATE s_core_locales SET language = 'Serbo-Croat', territory = '' WHERE locale = 'sh_YU';
UPDATE s_core_locales SET language = 'Singhalese', territory = 'Sri Lanka' WHERE locale = 'si_LK';
UPDATE s_core_locales SET language = 'Sidamo', territory = 'Ethiopia' WHERE locale = 'sid_ET';
UPDATE s_core_locales SET language = 'Slovakian', territory = 'Slovakia' WHERE locale = 'sk_SK';
UPDATE s_core_locales SET language = 'Slovakian', territory = 'Slovenia' WHERE locale = 'sl_SI';
UPDATE s_core_locales SET language = 'Somali', territory = 'Djibouti' WHERE locale = 'so_DJ';
UPDATE s_core_locales SET language = 'Somali', territory = 'Ethiopia' WHERE locale = 'so_ET';
UPDATE s_core_locales SET language = 'Somali', territory = 'Kenya' WHERE locale = 'so_KE';
UPDATE s_core_locales SET language = 'Somali', territory = 'Somalia' WHERE locale = 'so_SO';
UPDATE s_core_locales SET language = 'Albanian', territory = 'Albania' WHERE locale = 'sq_AL';
UPDATE s_core_locales SET language = 'Serbian', territory = 'Bosnia and Herzegovina' WHERE locale = 'sr_BA';
UPDATE s_core_locales SET language = 'Serbian', territory = 'Serbia and Montenegro' WHERE locale = 'sr_CS';
UPDATE s_core_locales SET language = 'Serbian', territory = 'Montenegro' WHERE locale = 'sr_ME';
UPDATE s_core_locales SET language = 'Serbian', territory = 'Serbia' WHERE locale = 'sr_RS';
UPDATE s_core_locales SET language = 'Serbian', territory = '' WHERE locale = 'sr_YU';
UPDATE s_core_locales SET language = 'Swazi', territory = 'Swaziland' WHERE locale = 'ss_SZ';
UPDATE s_core_locales SET language = 'Swazi', territory = 'South Africa' WHERE locale = 'ss_ZA';
UPDATE s_core_locales SET language = 'Southern Sotho Language', territory = 'Lesotho' WHERE locale = 'st_LS';
UPDATE s_core_locales SET language = 'Southern Sotho Language', territory = 'South Africa' WHERE locale = 'st_ZA';
UPDATE s_core_locales SET language = 'Swedish', territory = 'Finland' WHERE locale = 'sv_FI';
UPDATE s_core_locales SET language = 'Swedish', territory = 'Sweden' WHERE locale = 'sv_SE';
UPDATE s_core_locales SET language = 'Swahili', territory = 'Kenya' WHERE locale = 'sw_KE';
UPDATE s_core_locales SET language = 'Swahili', territory = 'Tanzania' WHERE locale = 'sw_TZ';
UPDATE s_core_locales SET language = 'Syriac', territory = 'Syria' WHERE locale = 'syr_SY';
UPDATE s_core_locales SET language = 'Tamil', territory = 'India' WHERE locale = 'ta_IN';
UPDATE s_core_locales SET language = 'Telugu', territory = 'India' WHERE locale = 'te_IN';
UPDATE s_core_locales SET language = 'Tadjik', territory = 'Tajikistan' WHERE locale = 'tg_TJ';
UPDATE s_core_locales SET language = 'Thai', territory = 'Thailand' WHERE locale = 'th_TH';
UPDATE s_core_locales SET language = 'Tigrinya', territory = 'Eritrea' WHERE locale = 'ti_ER';
UPDATE s_core_locales SET language = 'Tigrinya', territory = 'Ethiopia' WHERE locale = 'ti_ET';
UPDATE s_core_locales SET language = 'Tigre', territory = 'Eritrea' WHERE locale = 'tig_ER';
UPDATE s_core_locales SET language = 'Tswana Language', territory = 'South Africa' WHERE locale = 'tn_ZA';
UPDATE s_core_locales SET language = 'Tongan', territory = 'Tonga' WHERE locale = 'to_TO';
UPDATE s_core_locales SET language = 'Turkish', territory = 'Turkey' WHERE locale = 'tr_TR';
UPDATE s_core_locales SET language = 'Tsonga', territory = 'South Africa' WHERE locale = 'ts_ZA';
UPDATE s_core_locales SET language = 'Tartar', territory = 'Russian Federation' WHERE locale = 'tt_RU';
UPDATE s_core_locales SET language = 'Uighur', territory = 'China' WHERE locale = 'ug_CN';
UPDATE s_core_locales SET language = 'Ukrainian', territory = 'Ukraine' WHERE locale = 'uk_UA';
UPDATE s_core_locales SET language = 'Urdu', territory = 'India' WHERE locale = 'ur_IN';
UPDATE s_core_locales SET language = 'Urdu', territory = 'Pakistan' WHERE locale = 'ur_PK';
UPDATE s_core_locales SET language = 'Uzbek', territory = 'Afghanistan' WHERE locale = 'uz_AF';
UPDATE s_core_locales SET language = 'Uzbek', territory = 'Uzbekistan' WHERE locale = 'uz_UZ';
UPDATE s_core_locales SET language = 'Venda Language', territory = 'South Africa' WHERE locale = 've_ZA';
UPDATE s_core_locales SET language = 'Vietnamese', territory = 'Vietnam' WHERE locale = 'vi_VN';
UPDATE s_core_locales SET language = 'Walamo Language', territory = 'Ethiopia' WHERE locale = 'wal_ET';
UPDATE s_core_locales SET language = 'Wolof', territory = 'Senegal' WHERE locale = 'wo_SN';
UPDATE s_core_locales SET language = 'Xhosa', territory = 'South Africa' WHERE locale = 'xh_ZA';
UPDATE s_core_locales SET language = 'Yoruba', territory = 'Nigeria' WHERE locale = 'yo_NG';
UPDATE s_core_locales SET language = 'Chinese', territory = 'China' WHERE locale = 'zh_CN';
UPDATE s_core_locales SET language = 'Chinese', territory = 'Hong Kong' WHERE locale = 'zh_HK';
UPDATE s_core_locales SET language = 'Chinese', territory = 'Macao' WHERE locale = 'zh_MO';
UPDATE s_core_locales SET language = 'Chinese', territory = 'Singapur' WHERE locale = 'zh_SG';
UPDATE s_core_locales SET language = 'Chinese', territory = 'Taiwan' WHERE locale = 'zh_TW';
UPDATE s_core_locales SET language = 'Zulu', territory = 'South Africa' WHERE locale = 'zu_ZA';

-- s_order_number --
UPDATE s_order_number SET `desc` = 'Customers' WHERE id = 1;
UPDATE s_order_number SET `desc` = 'Orders' WHERE id = 920;
UPDATE s_order_number SET `desc` = 'Packing list' WHERE id = 921;
UPDATE s_order_number SET `desc` = 'Credits' WHERE id = 922;
UPDATE s_order_number SET `desc` = 'Invoices' WHERE id = 924;
UPDATE s_order_number SET `desc` = 'Article order number' WHERE id = 925;

-- s_crontab --
UPDATE s_crontab SET `name` = 'Birthday wishes' WHERE id = 1;
UPDATE s_crontab SET `name` = 'Cleanup' WHERE id = 2;
UPDATE s_crontab SET `name` = 'Article stock warning' WHERE id = 3;
UPDATE s_crontab SET `name` = 'Search' WHERE id = 5;
UPDATE s_crontab SET `name` = 'Email notification' WHERE id = 6;
UPDATE s_crontab SET `name` = 'Article rating by email' WHERE id = 7;
UPDATE s_crontab SET `name` = 'Clear HTTP cache' WHERE id = 12;

-- s_core_units --
UPDATE s_core_units SET `description` = 'Liter' WHERE id = 1;
UPDATE s_core_units SET `description` = 'Gram' WHERE id = 2;
UPDATE s_core_units SET `description` = 'Linear meter(s)', `unit` = 'lm' WHERE id = 5;
UPDATE s_core_units SET `description` = 'Kilogram' WHERE id = 6;
UPDATE s_core_units SET `description` = 'Package(s)', `unit` = 'Package(s)' WHERE id = 8;
UPDATE s_core_units SET `description` = 'Unit', `unit` = 'unit' WHERE id = 9;

-- s_core_engine_elements --
UPDATE s_core_engine_elements SET `label` = 'Comment', `help` = 'Optional comment' WHERE id = 22;
UPDATE s_core_engine_elements SET `label` = 'Free text 1', `help` = 'Free text to display on the detail page' WHERE id = 33;
UPDATE s_core_engine_elements SET `label` = 'Free text 2', `help` = 'Free text to display on the detail page' WHERE id = 34;

-- s_core_config_elements --
UPDATE s_core_config_elements SET `value` = 's:226:"0,a,according,against,all,and,are,as,be,before,but,by,can,etc,for,from,has,have,he,her,his,I,in,it,itis,its,just,like,make,more,new,not,now,of,on,one,or,over,players,style,that,the,thewhy,to,well,who,will,with,yet,you,you,your";' WHERE id = 625;
UPDATE s_core_config_elements SET `value` = 's:7:"Voucher";' WHERE id = 614;
UPDATE s_core_config_elements SET `value` = 's:30:"Surcharge for small quantities";' WHERE id = 623;
UPDATE s_core_config_elements SET `value` = 's:15:"Basket discount";' WHERE id = 637;
UPDATE s_core_config_elements SET `value` = 's:15:"Basket discount";' WHERE id = 620;
UPDATE s_core_config_elements SET `value` = 's:21:"Surcharge for payment";' WHERE id = 897;
UPDATE s_core_config_elements SET `value` = 's:21:"Surcharge for payment";' WHERE id = 626;
UPDATE s_core_config_elements SET `value` = 's:21:"Deduction for payment";' WHERE id = 627;

UPDATE s_core_config_elements SET `value` = 's:48:"
Kind Regards,

Your {config name=shopName} team";' WHERE id = 995;
UPDATE s_core_config_elements SET `value` = 's:69:"<br/>
Kind Regards,<br/><br/>

Your {config name=shopName} team</div>";' WHERE id = 997;

-- s_campaigns_templates --
UPDATE s_campaigns_templates SET description = 'Default template' WHERE id = 1;
UPDATE s_campaigns_templates SET description = 'Reseller' WHERE id = 2;

-- s_core_engine_groups --
UPDATE s_core_engine_groups SET label = 'Basic data' WHERE id = 1;
UPDATE s_core_engine_groups SET label = 'Description' WHERE id = 2;
UPDATE s_core_engine_groups SET label = 'Settings' WHERE id = 3;
UPDATE s_core_engine_groups SET label = 'Additional fields' WHERE id = 7;
UPDATE s_core_engine_groups SET label = 'Reference price' WHERE id = 8;
UPDATE s_core_engine_groups SET label = 'Prices and customer groups' WHERE id = 10;
UPDATE s_core_engine_groups SET label = 'Property' WHERE id = 11;

-- s_attribute_configuration --
UPDATE s_attribute_configuration SET label = 'Comment', help_text = 'Optional comment' WHERE id = 1;
UPDATE s_attribute_configuration SET label = 'Free-text-1', help_text = 'Free text for display on detail page' WHERE id = 2;
UPDATE s_attribute_configuration SET label = 'Free-text-2', help_text = 'Free text for display on detail page' WHERE id = 3;

-- s_billing_template --
UPDATE s_billing_template SET `desc` = 'Margin top' WHERE id = 1;
UPDATE s_billing_template SET `desc` = 'Margin right' WHERE id = 2;
UPDATE s_billing_template SET `desc` = 'Margin bottom' WHERE id = 3;
UPDATE s_billing_template SET `desc` = 'Margin left' WHERE id = 4;
UPDATE s_billing_template SET `desc` = 'Logo height' WHERE id = 5;
UPDATE s_billing_template SET `desc` = 'Margin heading to address' WHERE id = 7;
UPDATE s_billing_template SET `desc` = 'Margin left (negative value possible)' WHERE id = 8;
UPDATE s_billing_template SET `desc` = 'Footer' WHERE id = 9;
UPDATE s_billing_template SET `desc` = 'Letterhead right', value = '<p><strong>Demo Ltd. </strong><br /> John Doe<br /> Street 3<br /> 00000 Somecity<br /> Phone: 01234 / 56789<br /> Fax: 01234 / 56780<br />info@demo.de<br />www.demo.com</p>' WHERE id = 13;
UPDATE s_billing_template SET `desc` = 'Sender', value = 'Demo Ltd. - Street 3 - 00000 Somecity' WHERE id = 14;
UPDATE s_billing_template SET `desc` = 'Margin left' WHERE id = 15;
UPDATE s_billing_template SET `desc` = 'Margin bottom' WHERE id = 16;
UPDATE s_billing_template SET `desc` = 'Number of positions shown' WHERE id = 17;
UPDATE s_billing_template SET `desc` = 'Content' WHERE id = 18;
UPDATE s_billing_template SET `desc` = 'Margin content to upper border' WHERE id = 19;
UPDATE s_billing_template SET `desc` = 'Logo top' WHERE id = 20;
UPDATE s_billing_template SET `desc` = 'Margin bottom to logo (negative value possible)' WHERE id = 21;
UPDATE s_billing_template SET `desc` = 'Margin right (negative value possible)' WHERE id = 22;
UPDATE s_billing_template SET `desc` = 'Margin right (negative value possible)' WHERE id = 22;

-- s_core_documents_box --
UPDATE s_core_documents_box SET `value` = '<p>Demo Ltd. - Street 3 - 00000 Somecity</p>' WHERE name = 'Header_Sender';
UPDATE s_core_documents_box SET `value` = '<p>The goods remain our property till the bill is payed in full.</p>' WHERE name = 'Content_Info';
UPDATE s_core_documents_box SET `value` = '<p><strong>Demo Ltd. </strong><br /> John Doe<br /> Street 3<br /> 00000 Somecity<br /> Phone: 01234 / 56789<br /> Fax: 01234 / 6780<br />info@demo.com<br />www.demo.com</p>' WHERE name = 'Header_Box_Right';

-- s_core_widgets --
UPDATE s_core_widgets SET label = 'Sales yesterday and today' WHERE id = 1;
UPDATE s_core_widgets SET label = 'Drag and drop upload' WHERE id = 2;
UPDATE s_core_widgets SET label = 'Visitors online' WHERE id = 3;
UPDATE s_core_widgets SET label = 'Latest orders' WHERE id = 4;
UPDATE s_core_widgets SET label = 'Notes' WHERE id = 5;
UPDATE s_core_widgets SET label = 'Reseller activation' WHERE id = 6;

-- s_search_fields --
UPDATE s_search_fields SET `name` = 'Category keywords' WHERE id = 1;
UPDATE s_search_fields SET `name` = 'Category headline' WHERE id = 2;
UPDATE s_search_fields SET `name` = 'Product name' WHERE id = 3;
UPDATE s_search_fields SET `name` = 'Product keywords' WHERE id = 4;
UPDATE s_search_fields SET `name` = 'Product order number' WHERE id = 5;
UPDATE s_search_fields SET `name` = 'Manufacturer name' WHERE id = 6;
UPDATE s_search_fields SET `name` = 'Product name translation' WHERE id = 7;
UPDATE s_search_fields SET `name` = 'Product keywords translation' WHERE id = 8;

-- s_search_custom_sorting --
UPDATE s_search_custom_sorting SET label = 'Release date' WHERE id = 1;
UPDATE s_search_custom_sorting SET label = 'Popularity' WHERE id = 2;
UPDATE s_search_custom_sorting SET label = 'Lowest price' WHERE id = 3;
UPDATE s_search_custom_sorting SET label = 'Highest price' WHERE id = 4;
UPDATE s_search_custom_sorting SET label = 'Article description' WHERE id = 5;
UPDATE s_search_custom_sorting SET label = 'Best results' WHERE id = 7;

-- s_search_custom_facet --
UPDATE s_search_custom_facet SET `name` = 'Categories', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\CategoryFacet":{"label":"Categories", "depth": "2"}}' WHERE id = 1;
UPDATE s_search_custom_facet SET `name` = 'Immediately available', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ImmediateDeliveryFacet":{"label":"Immediately available"}}' WHERE id = 2;
UPDATE s_search_custom_facet SET `name` = 'Manufacturer', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ManufacturerFacet":{"label":"Manufacturer"}}' WHERE id = 3;
UPDATE s_search_custom_facet SET `name` = 'Price', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\PriceFacet":{"label":"Price"}}' WHERE id = 4;
UPDATE s_search_custom_facet SET `name` = 'Properties' WHERE id = 5;
UPDATE s_search_custom_facet SET `name` = 'Shipping free', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\ShippingFreeFacet":{"label":"Shipping free"}}' WHERE id = 6;
UPDATE s_search_custom_facet SET `name` = 'Rating', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\VoteAverageFacet":{"label":"Rating"}}' WHERE id = 7;
UPDATE s_search_custom_facet SET `name` = 'Weight', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\WeightFacet":{"label":"Weight","suffix":"kg","digits":2}}' WHERE id = 8;
UPDATE s_search_custom_facet SET `name` = 'Width', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\WidthFacet":{"label":"Width","suffix":"cm","digits":2}}' WHERE id = 9;
UPDATE s_search_custom_facet SET `name` = 'Height', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\HeightFacet":{"label":"Height","suffix":"cm","digits":2}}' WHERE id = 10;
UPDATE s_search_custom_facet SET `name` = 'Length', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\LengthFacet":{"label":"Length","suffix":"cm","digits":2}}' WHERE id = 11;