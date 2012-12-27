-- Paymentmeans --
UPDATE `s_core_paymentmeans` SET description = 'Debit',additionaldescription = '' WHERE name= 'debit';
UPDATE `s_core_paymentmeans` SET description = 'Cash',additionaldescription = '' WHERE name= 'cash';
UPDATE `s_core_paymentmeans` SET description = 'Invoice',additionaldescription = '' WHERE name= 'invoice';
UPDATE `s_core_paymentmeans` SET description = 'Prepayment',additionaldescription = '' WHERE name= 'prepayment';

-- Order / payment states --
UPDATE s_core_states SET description = 'Canceled' WHERE id = -1;
UPDATE s_core_states SET description = 'Open' WHERE id = 0;
UPDATE s_core_states SET description = 'In work' WHERE id = 1;
UPDATE s_core_states SET description = 'Fully completed' WHERE id = 2;
UPDATE s_core_states SET description = 'Partially completed' WHERE id = 3;
UPDATE s_core_states SET description = 'Canceled / Rejected' WHERE id = 4;
UPDATE s_core_states SET description = 'Ready for delivery' WHERE id = 5;
UPDATE s_core_states SET description = 'Partial delivered' WHERE id = 6;
UPDATE s_core_states SET description = 'Delivered completely' WHERE id = 7;
UPDATE s_core_states SET description = 'Clarification needed' WHERE id = 8;
UPDATE s_core_states SET description = 'Partially charged' WHERE id = 9;
UPDATE s_core_states SET description = 'Fully charged' WHERE id = 10;
UPDATE s_core_states SET description = 'Partly paid' WHERE id = 11;
UPDATE s_core_states SET description = 'Fully paid' WHERE id = 12;
UPDATE s_core_states SET description = 'First reminder' WHERE id = 13;
UPDATE s_core_states SET description = 'Second reminder' WHERE id = 14;
UPDATE s_core_states SET description = 'Third reminder' WHERE id = 15;
UPDATE s_core_states SET description = 'Collection' WHERE id = 16;
UPDATE s_core_states SET description = 'Open' WHERE id = 17;
UPDATE s_core_states SET description = 'Reserved' WHERE id = 18;
UPDATE s_core_states SET description = 'Delayed' WHERE id = 19;
UPDATE s_core_states SET description = 'Recredit' WHERE id = 20;
UPDATE s_core_states SET description = 'Verification necessary' WHERE id = 21;

-- s_core_documents --
UPDATE s_core_documents SET name = 'Invoice' WHERE template = 'index.tpl';
UPDATE s_core_documents SET name = 'Delivery note' WHERE template = 'index_ls.tpl';
UPDATE s_core_documents SET name = 'Credit note' WHERE template = 'index_gs.tpl';
UPDATE s_core_documents SET name = 'Cancellation invoice' WHERE template = 'index_sr.tpl';

-- s_core_customergroups --
UPDATE s_core_customergroups SET description = 'Default' WHERE `groupkey` = 'EK';

-- s_core_countries --
UPDATE s_core_countries SET countryname = CONCAT(UPPER(SUBSTRING(countryen, 1,1)),LOWER(SUBSTRING(countryen, 2)));

-- s_core_countries_areas --
UPDATE s_core_countries_areas SET name = 'My country' WHERE id = 1;
UPDATE s_core_countries_areas SET name = 'World' WHERE id = 2;
UPDATE s_core_countries_areas SET name = 'Europa' WHERE id = 3;

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
UPDATE s_core_config_mails SET ishtml = 0, attachment = '',subject = 'Your registration has been successful', content = 'Hello {salutation} {firstname} {lastname},\n\nThank you for your registration with our Shop.\n\nYou will gain access via the e-mail address {sMAIL} and the password you have chosen.\n\nYou can have your password sent to you by e-mail anytime.\n\nBest regards\n\nYour team of shopware AG', contentHTML = '' WHERE name = 'sREGISTERCONFIRMATION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '',subject = 'Your order with the demoshop', content = 'Hello {$billingaddress.firstname} {$billingaddress.lastname},\r\n \r\nThank you for your order with Shopware Demoshop (Nummer: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.\r\nInformation on your order:\r\n \r\nPos. Art.No.              Quantities         Price        Total\r\n{foreach item=details key=position from=$sOrderDetails}\r\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\r\n{$details.articlename|wordwrap:49|indent:5}\r\n{/foreach}\r\n \r\nShipping costs: {$sShippingCosts}\r\nTotal net: {$sAmountNet}\r\n{if !$sNet}\r\nTotal gross: {$sAmount}\r\n{/if}\r\n \r\nSelected payment type: {$additional.payment.description}\r\n{$additional.payment.additionaldescription}\r\n{if $additional.payment.name == "debit"}\r\nYour bank connection:\r\nAccount number: {$sPaymentTable.account}\r\nBIN:{$sPaymentTable.bankcode}\r\nWe will withdraw the money from your bank account within the next days.\r\n{/if}\r\n{if $additional.payment.name == "prepayment"}\r\n \r\nOur bank connection:\r\nAccount: ###\r\nBIN: ###\r\n{/if}\r\n \r\n{if $sComment}\r\nYour comment:\r\n{$sComment}\r\n{/if}\r\n \r\nBilling address:\r\n{$billingaddress.company}\r\n{$billingaddress.firstname} {$billingaddress.lastname}\r\n{$billingaddress.street} {$billingaddress.streetnumber}\r\n{$billingaddress.zipcode} {$billingaddress.city}\r\n{$billingaddress.phone}\r\n{$additional.country.countryname}\r\n \r\nShipping address:\r\n{$shippingaddress.company}\r\n{$shippingaddress.firstname} {$shippingaddress.lastname}\r\n{$shippingaddress.street} {$shippingaddress.streetnumber}\r\n{$shippingaddress.zipcode} {$shippingaddress.city}\r\n{$additional.country.countryname}\r\n \r\n{if $billingaddress.ustid}\r\nYour VAT-ID: {$billingaddress.ustid}\r\nIn case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax. \r\n{/if}\r\n \r\n \r\nIn case of further questions, do not hesitate to contact us. \r\n \r\nWe wish you a pleasant day.\r\n \r\nEnter your contact details here.', contentHTML = '' WHERE name = 'sORDER';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '',subject = 'Forgot password - Your access data for {sShop}', content = 'Hello,\r\n\r\nYour access data for {sShopURL} is as follows:\r\nUser: {sMail}\r\nPassword: {sPassword}\r\n\r\nBest regards\r\n\r\nContact details', contentHTML = '' WHERE name = 'sPASSWORD';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '',subject = 'Your order', content = 'Dear{if $sUser.billing_salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\r\n\r\nThe status of your order with order number{$sOrder.ordernumber} of {$sOrder.ordertime|date_format:" %d-%m-%Y"} has changed. The new status is as follows {$sOrder.status_description}.', contentHTML = '' WHERE name = 'sORDERSTATEMAIL1';
