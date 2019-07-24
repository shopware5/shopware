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
UPDATE s_core_countries SET `countryname` = 'Germany' WHERE id = 2;
UPDATE s_core_countries SET `countryname` = 'Arab Emirates' WHERE id = 3;
UPDATE s_core_countries SET `countryname` = 'Australia' WHERE id = 4;
UPDATE s_core_countries SET `countryname` = 'Belgium' WHERE id = 5;
UPDATE s_core_countries SET `countryname` = 'Denmark' WHERE id = 7;
UPDATE s_core_countries SET `countryname` = 'Finland' WHERE id = 8;
UPDATE s_core_countries SET `countryname` = 'France' WHERE id = 9;
UPDATE s_core_countries SET `countryname` = 'Greece' WHERE id = 10;
UPDATE s_core_countries SET `countryname` = 'Great Britain' WHERE id = 11;
UPDATE s_core_countries SET `countryname` = 'Ireland' WHERE id = 12;
UPDATE s_core_countries SET `countryname` = 'Iceland' WHERE id = 13;
UPDATE s_core_countries SET `countryname` = 'Italy' WHERE id = 14;
UPDATE s_core_countries SET `countryname` = 'Canada' WHERE id = 16;
UPDATE s_core_countries SET `countryname` = 'Luxembourg' WHERE id = 18;
UPDATE s_core_countries SET `countryname` = 'Netherlands' WHERE id = 21;
UPDATE s_core_countries SET `countryname` = 'Norway' WHERE id = 22;
UPDATE s_core_countries SET `countryname` = 'Austria' WHERE id = 23;
UPDATE s_core_countries SET `countryname` = 'Sweden' WHERE id = 25;
UPDATE s_core_countries SET `countryname` = 'Switzerland' WHERE id = 26;
UPDATE s_core_countries SET `countryname` = 'Spain' WHERE id = 27;
UPDATE s_core_countries SET `countryname` = 'Poland' WHERE id = 30;
UPDATE s_core_countries SET `countryname` = 'Hungary' WHERE id = 31;
UPDATE s_core_countries SET `countryname` = 'Turkey' WHERE id = 32;
UPDATE s_core_countries SET `countryname` = 'Czech Republic' WHERE id = 33;
UPDATE s_core_countries SET `countryname` = 'Slovakia' WHERE id = 34;
UPDATE s_core_countries SET `countryname` = 'Romania' WHERE id = 35;
UPDATE s_core_countries SET `countryname` = 'Brazil' WHERE id = 36;
UPDATE s_core_countries SET `countryname` = 'Bulgaria' WHERE id = 38;
UPDATE s_core_countries SET `countryname` = 'Estonia' WHERE id = 39;
UPDATE s_core_countries SET `countryname` = 'Croatia' WHERE id = 40;
UPDATE s_core_countries SET `countryname` = 'Lettland' WHERE id = 41;
UPDATE s_core_countries SET `countryname` = 'Lithuania' WHERE id = 42;
UPDATE s_core_countries SET `countryname` = 'Slovenia' WHERE id = 44;
UPDATE s_core_countries SET `countryname` = 'Cyprus' WHERE id = 45;
UPDATE s_core_countries SET `countryname` = 'Åland Islands' WHERE id = 47;
UPDATE s_core_countries SET `countryname` = 'Albania' WHERE id = 48;
UPDATE s_core_countries SET `countryname` = 'Algeria' WHERE id = 49;
UPDATE s_core_countries SET `countryname` = 'American Samoa' WHERE id = 50;
UPDATE s_core_countries SET `countryname` = 'Antarctica' WHERE id = 54;
UPDATE s_core_countries SET `countryname` = 'Antigua And Barbuda' WHERE id = 55;
UPDATE s_core_countries SET `countryname` = 'Argentina' WHERE id = 56;
UPDATE s_core_countries SET `countryname` = 'Armenia' WHERE id = 57;
UPDATE s_core_countries SET `countryname` = 'Azerbaijan' WHERE id = 59;
UPDATE s_core_countries SET `countryname` = 'Bangladesh' WHERE id = 62;
UPDATE s_core_countries SET `countryname` = 'Belarus' WHERE id = 64;
UPDATE s_core_countries SET `countryname` = 'Bolivia' WHERE id = 69;
UPDATE s_core_countries SET `countryname` = 'Bonaire, Sint Eustatius And Saba' WHERE id = 70;
UPDATE s_core_countries SET `countryname` = 'Bosnia And Herzegovina' WHERE id = 71;
UPDATE s_core_countries SET `countryname` = 'Bouvet Island' WHERE id = 73;
UPDATE s_core_countries SET `countryname` = 'British Indian Ocean Territory' WHERE id = 74;
UPDATE s_core_countries SET `countryname` = 'United States Minor Outlying Islands' WHERE id = 75;
UPDATE s_core_countries SET `countryname` = 'Virgin Islands (British)' WHERE id = 76;
UPDATE s_core_countries SET `countryname` = 'Virgin Islands (U.S.)' WHERE id = 77;
UPDATE s_core_countries SET `countryname` = 'Brunei Darussalam' WHERE id = 78;
UPDATE s_core_countries SET `countryname` = 'Cambodia' WHERE id = 81;
UPDATE s_core_countries SET `countryname` = 'Cameroon' WHERE id = 82;
UPDATE s_core_countries SET `countryname` = 'Cabo Verde' WHERE id = 83;
UPDATE s_core_countries SET `countryname` = 'Cayman Islands' WHERE id = 84;
UPDATE s_core_countries SET `countryname` = 'Central African Republic' WHERE id = 85;
UPDATE s_core_countries SET `countryname` = 'Chad' WHERE id = 86;
UPDATE s_core_countries SET `countryname` = 'Christmas Island' WHERE id = 89;
UPDATE s_core_countries SET `countryname` = 'Cocos (Keeling) Islands' WHERE id = 90;
UPDATE s_core_countries SET `countryname` = 'Colombia' WHERE id = 91;
UPDATE s_core_countries SET `countryname` = 'Comoros' WHERE id = 92;
UPDATE s_core_countries SET `countryname` = 'Congo' WHERE id = 93;
UPDATE s_core_countries SET `countryname` = 'Congo (Democratic Republic)' WHERE id = 94;
UPDATE s_core_countries SET `countryname` = 'Cook Islands' WHERE id = 95;
UPDATE s_core_countries SET `countryname` = 'Cuba' WHERE id = 97;
UPDATE s_core_countries SET `countryname` = 'Djibouti' WHERE id = 99;
UPDATE s_core_countries SET `countryname` = 'Dominican Republic' WHERE id = 101;
UPDATE s_core_countries SET `countryname` = 'Egypt' WHERE id = 103;
UPDATE s_core_countries SET `countryname` = 'Equatorial Guinea' WHERE id = 105;
UPDATE s_core_countries SET `countryname` = 'Ethiopia' WHERE id = 107;
UPDATE s_core_countries SET `countryname` = 'Falkland Islands (Malvinas)' WHERE id = 108;
UPDATE s_core_countries SET `countryname` = 'Faroe Islands' WHERE id = 109;
UPDATE s_core_countries SET `countryname` = 'Fiji' WHERE id = 110;
UPDATE s_core_countries SET `countryname` = 'French Guiana' WHERE id = 111;
UPDATE s_core_countries SET `countryname` = 'French Polynesia' WHERE id = 112;
UPDATE s_core_countries SET `countryname` = 'French Southern Territories' WHERE id = 113;
UPDATE s_core_countries SET `countryname` = 'Gabon' WHERE id = 114;
UPDATE s_core_countries SET `countryname` = 'Georgia' WHERE id = 116;
UPDATE s_core_countries SET `countryname` = 'Greenland' WHERE id = 119;
UPDATE s_core_countries SET `countryname` = 'Heard Island And McDonald Islands' WHERE id = 129;
UPDATE s_core_countries SET `countryname` = 'Holy SEE' WHERE id = 130;
UPDATE s_core_countries SET `countryname` = 'India' WHERE id = 133;
UPDATE s_core_countries SET `countryname` = 'Indonesia' WHERE id = 134;
UPDATE s_core_countries SET `countryname` = "Côte D'Ivoire" WHERE id = 135;
UPDATE s_core_countries SET `countryname` = 'Iraq' WHERE id = 137;
UPDATE s_core_countries SET `countryname` = 'Isle Of Man' WHERE id = 138;
UPDATE s_core_countries SET `countryname` = 'Jamaica' WHERE id = 139;
UPDATE s_core_countries SET `countryname` = 'Jordan' WHERE id = 141;
UPDATE s_core_countries SET `countryname` = 'Kazakhstan' WHERE id = 142;
UPDATE s_core_countries SET `countryname` = 'Kenya' WHERE id = 143;
UPDATE s_core_countries SET `countryname` = 'Kyrgyzstan' WHERE id = 146;
UPDATE s_core_countries SET `countryname` = 'Lebanon' WHERE id = 148;
UPDATE s_core_countries SET `countryname` = 'Libya' WHERE id = 151;
UPDATE s_core_countries SET `countryname` = 'Macedonia' WHERE id = 153;
UPDATE s_core_countries SET `countryname` = 'Madagascar' WHERE id = 154;
UPDATE s_core_countries SET `countryname` = 'Maldives' WHERE id = 157;
UPDATE s_core_countries SET `countryname` = 'Marshall Islands' WHERE id = 159;
UPDATE s_core_countries SET `countryname` = 'Mauritania' WHERE id = 161;
UPDATE s_core_countries SET `countryname` = 'Mexico' WHERE id = 164;
UPDATE s_core_countries SET `countryname` = 'Micronesia' WHERE id = 165;
UPDATE s_core_countries SET `countryname` = 'Moldova' WHERE id = 166;
UPDATE s_core_countries SET `countryname` = 'Mongolia' WHERE id = 168;
UPDATE s_core_countries SET `countryname` = 'Morocco' WHERE id = 171;
UPDATE s_core_countries SET `countryname` = 'Mozambique' WHERE id = 172;
UPDATE s_core_countries SET `countryname` = 'Nepal' WHERE id = 175;
UPDATE s_core_countries SET `countryname` = 'New Caledonia' WHERE id = 176;
UPDATE s_core_countries SET `countryname` = 'New Zealand' WHERE id = 177;
UPDATE s_core_countries SET `countryname` = 'Norfolk Island' WHERE id = 182;
UPDATE s_core_countries SET `countryname` = 'North Korea' WHERE id = 183;
UPDATE s_core_countries SET `countryname` = 'Northern Mariana Islands' WHERE id = 184;
UPDATE s_core_countries SET `countryname` = 'Palestine, State Of' WHERE id = 188;
UPDATE s_core_countries SET `countryname` = 'Papua New Guinea' WHERE id = 190;
UPDATE s_core_countries SET `countryname` = 'Philippines' WHERE id = 193;
UPDATE s_core_countries SET `countryname` = 'Qatar' WHERE id = 196;
UPDATE s_core_countries SET `countryname` = 'Russian Federation' WHERE id = 199;
UPDATE s_core_countries SET `countryname` = 'Rwanda' WHERE id = 200;
UPDATE s_core_countries SET `countryname` = 'Saint Helena' WHERE id = 202;
UPDATE s_core_countries SET `countryname` = 'Saint Kitts And Nevis' WHERE id = 203;
UPDATE s_core_countries SET `countryname` = 'Saint Pierre And Miquelon' WHERE id = 206;
UPDATE s_core_countries SET `countryname` = 'Saint Vincent And The Grenadines' WHERE id = 207;
UPDATE s_core_countries SET `countryname` = 'Sao Tome And Principe' WHERE id = 210;
UPDATE s_core_countries SET `countryname` = 'Saudi Arabia' WHERE id = 211;
UPDATE s_core_countries SET `countryname` = 'Serbia' WHERE id = 213 ;
UPDATE s_core_countries SET `countryname` = 'Seychelles' WHERE id = 214;
UPDATE s_core_countries SET `countryname` = 'Singapore' WHERE id = 216;
UPDATE s_core_countries SET `countryname` = 'Sint Maarten (Dutch Part)' WHERE id = 217;
UPDATE s_core_countries SET `countryname` = 'Solomon Islands' WHERE id = 218;
UPDATE s_core_countries SET `countryname` = 'South Africa' WHERE id = 220;
UPDATE s_core_countries SET `countryname` = 'South Georgia And The South Sandwich Islands' WHERE id = 221;
UPDATE s_core_countries SET `countryname` = 'South Korea' WHERE id = 222;
UPDATE s_core_countries SET `countryname` = 'South Sudan' WHERE id = 223;
UPDATE s_core_countries SET `countryname` = 'Svalbard And Jan Mayen' WHERE id = 227;
UPDATE s_core_countries SET `countryname` = 'Swaziland' WHERE id = 228;
UPDATE s_core_countries SET `countryname` = 'Syrian Arab Republic' WHERE id = 229;
UPDATE s_core_countries SET `countryname` = 'Tajikistan' WHERE id = 231;
UPDATE s_core_countries SET `countryname` = 'Tanzania' WHERE id = 232;
UPDATE s_core_countries SET `countryname` = 'Trinidad And Tobago' WHERE id = 238;
UPDATE s_core_countries SET `countryname` = 'Tunisia' WHERE id = 239;
UPDATE s_core_countries SET `countryname` = 'Turkmenistan' WHERE id = 240;
UPDATE s_core_countries SET `countryname` = 'Turks And Caicos Islands' WHERE id = 241;
UPDATE s_core_countries SET `countryname` = 'Uzbekistan' WHERE id = 246;
UPDATE s_core_countries SET `countryname` = 'Venezuela' WHERE id = 248;
UPDATE s_core_countries SET `countryname` = 'Wallis And Futuna' WHERE id = 250;
UPDATE s_core_countries SET `countryname` = 'Western Sahara' WHERE id = 251;
UPDATE s_core_countries SET `countryname` = 'Yemen' WHERE id = 252;
UPDATE s_core_countries SET `countryname` = 'Zambia' WHERE id = 253;
UPDATE s_core_countries SET `countryname` = 'Zimbabwe' WHERE id = 254;

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
UPDATE `s_core_config_mails` SET `subject` = 'Your registration by {config name=shopName}', `content`='{include file="string:{config name=emailheaderplain}"}

Dear {$salutation|salutation} {$lastname},

thank you for your registration with our Shop.
You will gain access via the email address {$sMAIL} and the password you have chosen.
You can change your password at any time.

{include file="string:{config name=emailfooterplain}"}', contentHTML='<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$salutation|salutation} {$firstname} {$lastname},<br/>
        <br/>
        thank you for your registration with our Shop.<br/>
        You will gain access via the email address <strong>{$sMAIL}</strong> and the password you have chosen.<br/>
        You can change your password anytime.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>', `ishtml` = 1,`attachment` = '',`mailtype` = 2,
  `context` = 'a:14:{s:7:"sConfig";a:0:{}s:5:"sMAIL";s:12:"xyz@mail.com";s:6:"street";s:16:"Examplestreet 11";s:7:"zipcode";s:4:"1234";s:4:"city";s:11:"Examplecity";s:7:"country";s:1:"2";s:5:"state";N;s:13:"customer_type";s:7:"private";s:10:"salutation";s:2:"Mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:11:"accountmode";s:1:"0";s:5:"email";s:12:"xyz@mail.com";s:10:"additional";a:1:{s:13:"customer_type";s:7:"private";}}'
WHERE `name`="sREGISTERCONFIRMATION";



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Your order at the {config name=shopName}',
  `content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$billingaddress.salutation|salutation} {$billingaddress.lastname},

Thank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.
Information on your order:

Pos.  Art.No.               Description                                      Quantities       Price       Total
{foreach item=details key=position from=$sOrderDetails}
{{$position+1}|fill:4}  {$details.ordernumber|fill:20}  {$details.articlename|fill:49}  {$details.quantity|fill:6}  {$details.price|padding:8|currency|unescape:"htmlall"}      {$details.amount|padding:8|currency|unescape:"htmlall"}
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
WHERE `s_core_config_mails`.`name` = 'sORDER';



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
WHERE `s_core_config_mails`.`name` = 'sTELLAFRIEND';



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
WHERE `s_core_config_mails`.`name` = 'sNOSERIALS';



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
WHERE `s_core_config_mails`.`name` = 'sVOUCHER';



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
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHACCEPTED';



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
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sCUSTOMERGROUPHREJECTED';



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
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sCANCELEDQUESTION';



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
WHERE `s_core_config_mails`.`name` = 'sCANCELEDVOUCHER';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL9';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL10';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL13';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL16';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL15';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL14';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL12';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL17';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL18';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL19';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL20';



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
WHERE `s_core_config_mails`.`name` = 'sARTICLESTOCK';



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
WHERE `s_core_config_mails`.`name` = 'sNEWSLETTERCONFIRMATION';



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
WHERE `s_core_config_mails`.`name` = 'sOPTINNEWSLETTER';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your article evaluation',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for evaluating the article {$sArticle.articleName}.
Please confirm your evaluation using the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <p>
        Hello,<br/>
        <br/>
        thank you for evaluating the article {$sArticle.articleName}.<br/>
        Please confirm your evaluation using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2,
  `context` = 'a:2:{s:12:"sConfirmLink";s:133:"http://shopware.example/craft-tradition/men/business-bags/165/die-zeit-5?action=rating&sConfirmation=6avE5xLF22DTp8gNPaZ8KRUfJhflnvU9";s:8:"sArticle";a:1:{s:11:"articleName";s:24:"DIE ZEIT 5 Cowhide mokka";}}'
WHERE `s_core_config_mails`.`name` = 'sOPTINVOTE';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your registration at {config name=shopName}',
  `content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for signing up at {$sShop}.
Please confirm your registration by clicking the following link:

{$sConfirmLink}

With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for signing up at {$sShop}.<br/>
        Please confirm your registration by clicking the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm registration</a><br/>
        <br/>
        With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sOPTINREGISTER';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your e-mail address for your order at {config name=shopName}',
  `content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

Please confirm your e-mail address using the following link:

{$sConfirmLink}

After the confirmation you will be directed your order overview, where you can check your order again and complete it.
With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        Please confirm your e-mail address using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm e-mail</a><br/>
        <br/>
        After the confirmation you will be directed your order overview, where you can check your order again and complete it.<br/>
        With this confirmation you also agree that we may send you further e-mails within the scope of the fulfilment of the contract.<br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sOPTINREGISTERACCOUNTLESS';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Please confirm your blog article evaluation',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello,

thank you for evaluating the blog article "{$sArticle.title}".
Please confirm your evaluation using the following link:

{$sConfirmLink}

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Hello,<br/>
        <br/>
        thank you for evaluating the blog article for "{$sArticle.title}".<br/>
        Please confirm your evaluation using the following link:<br/>
        <br/>
        <a href="{$sConfirmLink}">Confirm</a><br/>
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',`mailtype` = 2
WHERE `s_core_config_mails`.`name` = 'sOPTINBLOGCOMMENT';



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
WHERE `s_core_config_mails`.`name` = 'sARTICLEAVAILABLE';



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
WHERE `s_core_config_mails`.`name` = 'sACCEPTNOTIFICATION';



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
</div>',`ishtml` = 1,`attachment` = '',
  `context` = 'a:2:{s:7:"sConfig";a:0:{}s:15:"paymentInstance";a:3:{s:9:"firstName";s:4:"John";s:8:"lastName";s:3:"Doe";s:11:"orderNumber";s:5:"20003";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERSEPAAUTHORIZATION';



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
WHERE `s_core_config_mails`.`name` = 'sCONFIRMPASSWORDCHANGE';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL1';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL2';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL11' ;



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL5';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL3';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL8';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL4';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL6';



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
WHERE `s_core_config_mails`.`name` = 'sORDERSTATEMAIL7';



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
WHERE `s_core_config_mails`.`name` = 'sBIRTHDAY';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Evaluate article',`content` = '{include file="string:{config name=emailheaderplain}"}

Hello  {$sUser.salutation|salutation} {$sUser.billing_lastname},

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
    Hello  {$sUser.salutation|salutation} {$sUser.billing_lastname},<br/>
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
WHERE `s_core_config_mails`.`name` = 'sARTICLECOMMENT';



UPDATE `s_core_config_mails` SET `frommail` = '{config name=mail}',`fromname` = '{config name=shopName}',`subject` = 'Order documents for your order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find the your order documents in PDF format.

{include file="string:{config name=emailfooterplain}"}',`contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>',`ishtml` = 1,`attachment` = '',
  `context` = 'a:4:{s:6:"sOrder";a:40:{s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:12:"order_number";s:5:"20006";s:6:"userID";s:1:"4";s:10:"customerID";s:1:"4";s:14:"invoice_amount";s:5:"701.4";s:18:"invoice_amount_net";s:6:"589.42";s:16:"invoice_shipping";s:3:"3.9";s:20:"invoice_shipping_net";s:4:"3.28";s:9:"ordertime";s:19:"2017-10-29 10:00:08";s:6:"status";s:1:"2";s:8:"statusID";s:1:"2";s:7:"cleared";s:2:"12";s:9:"clearedID";s:2:"12";s:9:"paymentID";s:1:"5";s:13:"transactionID";s:0:"";s:7:"comment";s:0:"";s:15:"customercomment";s:0:"";s:3:"net";s:1:"0";s:5:"netto";s:1:"0";s:9:"partnerID";s:0:"";s:11:"temporaryID";s:0:"";s:7:"referer";s:0:"";s:11:"cleareddate";N;s:12:"cleared_date";N;s:12:"trackingcode";s:0:"";s:8:"language";s:1:"1";s:8:"currency";s:3:"EUR";s:14:"currencyFactor";s:1:"1";s:9:"subshopID";s:1:"1";s:10:"dispatchID";s:1:"9";s:10:"currencyID";s:1:"1";s:12:"cleared_name";s:15:"completely_paid";s:19:"cleared_description";s:15:"Completely paid";s:11:"status_name";s:9:"completed";s:18:"status_description";s:9:"Completed";s:19:"payment_description";s:18:"Payment in advance";s:20:"dispatch_description";s:17:"Standard delivery";s:20:"currency_description";s:4:"Euro";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}s:13:"sOrderDetails";a:2:{i:0;a:20:{s:14:"orderdetailsID";s:3:"222";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:9:"articleID";s:3:"166";s:18:"articleordernumber";s:7:"SW10166";s:5:"price";s:5:"69.95";s:8:"quantity";s:2:"10";s:7:"invoice";s:5:"699.5";s:4:"name";s:12:"DIE ZEIT 100";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"0";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"1";s:3:"tax";s:5:"19.00";s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";s:0:"";s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}i:1;a:20:{s:14:"orderdetailsID";s:3:"223";s:7:"orderID";s:2:"66";s:11:"ordernumber";s:5:"20006";s:9:"articleID";s:1:"0";s:18:"articleordernumber";s:16:"SHIPPINGDISCOUNT";s:5:"price";s:2:"-2";s:8:"quantity";s:1:"1";s:7:"invoice";s:2:"-2";s:4:"name";s:15:"Warenkorbrabatt";s:6:"status";s:1:"0";s:7:"shipped";s:1:"0";s:12:"shippedgroup";s:1:"0";s:11:"releasedate";s:10:"0000-00-00";s:5:"modus";s:1:"4";s:10:"esdarticle";s:1:"0";s:5:"taxID";s:1:"0";s:3:"tax";N;s:8:"tax_rate";s:2:"19";s:3:"esd";s:1:"0";s:10:"attributes";a:6:{s:10:"attribute1";N;s:10:"attribute2";N;s:10:"attribute3";N;s:10:"attribute4";N;s:10:"attribute5";N;s:10:"attribute6";N;}}}s:5:"sUser";a:82:{s:15:"billing_company";s:0:"";s:18:"billing_department";s:0:"";s:18:"billing_salutation";s:2:"mr";s:14:"customernumber";s:5:"20006";s:17:"billing_firstname";s:4:"John";s:16:"billing_lastname";s:3:"Doe";s:14:"billing_street";s:16:"Examplestreet 11";s:32:"billing_additional_address_line1";s:0:"";s:32:"billing_additional_address_line2";s:0:"";s:15:"billing_zipcode";s:4:"1234";s:12:"billing_city";s:11:"Examplecity";s:5:"phone";s:0:"";s:13:"billing_phone";s:0:"";s:17:"billing_countryID";s:1:"2";s:15:"billing_stateID";N;s:15:"billing_country";s:7:"Germany";s:18:"billing_countryiso";s:2:"DE";s:19:"billing_countryarea";s:7:"germany";s:17:"billing_countryen";s:7:"GERMANY";s:5:"ustid";s:0:"";s:13:"billing_text1";N;s:13:"billing_text2";N;s:13:"billing_text3";N;s:13:"billing_text4";N;s:13:"billing_text5";N;s:13:"billing_text6";N;s:7:"orderID";s:2:"66";s:16:"shipping_company";s:0:"";s:19:"shipping_department";s:0:"";s:19:"shipping_salutation";s:2:"mr";s:18:"shipping_firstname";s:4:"John";s:17:"shipping_lastname";s:3:"Doe";s:15:"shipping_street";s:16:"Examplestreet 11";s:33:"shipping_additional_address_line1";s:0:"";s:33:"shipping_additional_address_line2";s:0:"";s:16:"shipping_zipcode";s:4:"1234";s:13:"shipping_city";s:11:"Examplecity";s:16:"shipping_stateID";N;s:18:"shipping_countryID";s:1:"2";s:16:"shipping_country";s:7:"Germany";s:19:"shipping_countryiso";s:2:"DE";s:20:"shipping_countryarea";s:7:"germany";s:18:"shipping_countryen";s:7:"GERMANY";s:14:"shipping_text1";N;s:14:"shipping_text2";N;s:14:"shipping_text3";N;s:14:"shipping_text4";N;s:14:"shipping_text5";N;s:14:"shipping_text6";N;s:2:"id";s:1:"4";s:8:"password";s:60:"$2y$10$qcu486mGUDZ/qbUSJDi78uYwatm24dzQ/dCO79PVVP0MbGHJ0LLgq";s:7:"encoder";s:6:"bcrypt";s:5:"email";s:12:"xyz@mail.com";s:6:"active";s:1:"1";s:11:"accountmode";s:1:"0";s:15:"confirmationkey";s:0:"";s:9:"paymentID";s:1:"5";s:10:"firstlogin";s:10:"2017-10-30";s:9:"lastlogin";s:19:"2017-10-30 12:02:18";s:9:"sessionID";s:26:"mpmqj6qoro0pg3ua9u5hprh646";s:10:"newsletter";s:1:"0";s:10:"validation";s:1:"0";s:9:"affiliate";s:1:"0";s:13:"customergroup";s:2:"EK";s:13:"paymentpreset";s:1:"0";s:8:"language";s:1:"1";s:9:"subshopID";s:1:"1";s:7:"referer";s:0:"";s:12:"pricegroupID";N;s:15:"internalcomment";s:0:"";s:12:"failedlogins";s:1:"0";s:11:"lockeduntil";N;s:26:"default_billing_address_id";s:1:"6";s:27:"default_shipping_address_id";s:1:"6";s:5:"title";s:0:"";s:10:"salutation";s:2:"mr";s:9:"firstname";s:4:"John";s:8:"lastname";s:3:"Doe";s:8:"birthday";s:10:"2017-10-30";s:11:"login_token";s:38:"1f6f51a1-54c1-4db8-8b4f-fbe957f8b856.1";s:11:"preisgruppe";s:1:"1";s:11:"billing_net";s:1:"1";}s:9:"sDispatch";a:2:{s:4:"name";s:17:"Standard delivery";s:11:"description";s:0:"";}}'
WHERE `s_core_config_mails`.`name` = 'sORDERDOCUMENTS';


UPDATE `s_core_config_mails` SET  `subject` = 'Invoice for order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.

{include file="string:{config name=emailfooterplain}"}', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your order documents in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>' WHERE `s_core_config_mails`.`name` = 'document_invoice';


UPDATE `s_core_config_mails` SET  `subject` = 'Delivery note for order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your delivery note in PDF format.

{include file="string:{config name=emailfooterplain}"}', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your delivery note in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>' WHERE `s_core_config_mails`.`name` = 'document_delivery_note';


UPDATE `s_core_config_mails` SET  `subject` = 'Credit note for order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your credit note in PDF format.

{include file="string:{config name=emailfooterplain}"}', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find the your credit note in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>' WHERE `s_core_config_mails`.`name` = 'document_credit';

UPDATE `s_core_config_mails` SET  `subject` = 'Cancellation invoice for order {$orderNumber}',`content` = '{include file="string:{config name=emailheaderplain}"}

Dear {$sUser.salutation|salutation} {$sUser.lastname},

thank you for your order at {config name=shopName}. In the attachments of this email you will find your cancellation invoice in PDF format.

{include file="string:{config name=emailfooterplain}"', `contentHTML` = '<div style="font-family:arial; font-size:12px;">
    {include file="string:{config name=emailheaderhtml}"}
    <br/><br/>
    <p>
        Dear {$sUser.salutation|salutation} {$sUser.lastname},<br/>
        <br/>
        thank you for your order at {config name=shopName}. In the attachments of this email you will find your cancellation invoice in PDF format.
    </p>
    {include file="string:{config name=emailfooterhtml}"}
</div>' WHERE `s_core_config_mails`.`name` = 'document_cancellation';

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
UPDATE s_search_custom_facet SET `name` = 'Variants', `facet` = '{"Shopware\\\\Bundle\\\\SearchBundle\\\\Facet\\\\VariantFacet":{"groupIds":{}, "expandGroupIds":{}}}' WHERE id = 12;

-- s_core_translations --
UPDATE `s_core_translations` SET objectdata = 'a:1:{s:5:\"label\";s:8:\"Variants\";}' WHERE objecttype = 'custom_facet' AND objectlanguage = 1 AND dirty != 1;

-- s_media-album --
UPDATE `s_media_album` SET `name` = 'Recycle bin' WHERE `id` = -13;
UPDATE `s_media_album` SET `name` = 'Manufacturer' WHERE `id` = -12;
UPDATE `s_media_album` SET `name` = 'Blog' WHERE `id` = -11;
UPDATE `s_media_album` SET `name` = 'Unsorted' WHERE `id` = -10;
UPDATE `s_media_album` SET `name` = 'Other' WHERE `id` = -9;
UPDATE `s_media_album` SET `name` = 'Music' WHERE `id` = -8;
UPDATE `s_media_album` SET `name` = 'Video' WHERE `id` = -7;
UPDATE `s_media_album` SET `name` = 'Files' WHERE `id` = -6;
UPDATE `s_media_album` SET `name` = 'Newsletter' WHERE `id` = -5;
UPDATE `s_media_album` SET `name` = 'Campaigns' WHERE `id` = -4;
UPDATE `s_media_album` SET `name` = 'Shopping Worlds' WHERE `id` = -3;
UPDATE `s_media_album` SET `name` = 'Banner' WHERE `id` = -2;
UPDATE `s_media_album` SET `name` = 'Products' WHERE `id` = -1;

-- s_premium_holidays --
UPDATE `s_premium_holidays` SET `name` = 'New Year''s Day' WHERE `id` = '1';
UPDATE `s_premium_holidays` SET `name` = 'Epiphany' WHERE `id` = '3';
UPDATE `s_premium_holidays` SET `name` = 'Carnival Monday' WHERE `id` = '4';
UPDATE `s_premium_holidays` SET `name` = 'Saint Joseph''s Day' WHERE `id` = '5';
UPDATE `s_premium_holidays` SET `name` = 'Good Friday' WHERE `id` = '6';
UPDATE `s_premium_holidays` SET `name` = 'Easter Monday' WHERE `id` = '7';
UPDATE `s_premium_holidays` SET `name` = 'Labour Day' WHERE `id` = '8';
UPDATE `s_premium_holidays` SET `name` = 'Ascension Day' WHERE `id` = '9';
UPDATE `s_premium_holidays` SET `name` = 'Whitmonday' WHERE `id` = '10';
UPDATE `s_premium_holidays` SET `name` = 'Corpus Christi' WHERE `id` = '11';
UPDATE `s_premium_holidays` SET `name` = 'Assumption Day' WHERE `id` = '13';
UPDATE `s_premium_holidays` SET `name` = 'German Unification Day' WHERE `id` = '14';
UPDATE `s_premium_holidays` SET `name` = 'Austrian National Day' WHERE `id` = '15';
UPDATE `s_premium_holidays` SET `name` = 'Reformation Day' WHERE `id` = '16';
UPDATE `s_premium_holidays` SET `name` = 'All Hallows'' Day' WHERE `id` = '17';
UPDATE `s_premium_holidays` SET `name` = 'Day of Prayer and Repentance' WHERE `id` = '18';
UPDATE `s_premium_holidays` SET `name` = 'Feast of the Immaculate Conception' WHERE `id` = '19';
UPDATE `s_premium_holidays` SET `name` = 'Christmas Eve' WHERE `id` = '20';
UPDATE `s_premium_holidays` SET `name` = 'Christmas Day' WHERE `id` = '21';
UPDATE `s_premium_holidays` SET `name` = 'Boxing Day' WHERE `id` = '22';
UPDATE `s_premium_holidays` SET `name` = 'New Year''s Eve' WHERE `id` = '23';
