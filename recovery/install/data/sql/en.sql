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
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Order shipped in parts', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:\"%d-%m-%Y\"} has changed. The new status is as follows: {$sOrder.status_description}.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL11';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear{if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:\" %d-%m-%Y\"} has changed. The new status is as follows: {$sOrder.status_description}.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL1';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order {$sOrder.ordernumber} has changed!\nThe current status of your order is as follows: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under \"My account\" - \"My orders\" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL8';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order at {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear{if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:\" %d-%m-%Y\"} has changed. The new status is as follows: {$sOrder.status_description}.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL2';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe order status of your order {$sOrder.ordernumber} has changed!\nThe order now has the following status: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under \"My account\" - \"My orders\" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL4';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Status change', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:\" %d.%m.%Y\"}\nhas changed. The new status is as follows: \"{$sOrder.status_description}\".\n\n\nInformation on your order:\n==================================\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:3} {$details.articleordernumber|fill:10:\" \":\"...\"} {$details.name|fill:30} {$details.quantity} x {$details.price|string_format:\"%.2f\"} {$sConfig.sCURRENCY}\n{/foreach}\n\nShipping costs: {$sOrder.invoice_shipping} {$sConfig.sCURRENCY}\nNet total: {$sOrder.invoice_amount_net|string_format:\"%.2f\"} {$sConfig.sCURRENCY}\nTotal amount incl. VAT: {$sOrder.invoice_amount|string_format:\"%.2f\"} {$sConfig.sCURRENCY}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL3';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe order status of your order {$sOrder.ordernumber} has changed!\nYour order now has the following status: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under \"My account\" - \"My orders\" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\n\nBest regards,\nYour team of {config name=shopName}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL6';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe status of your order with order number {$sOrder.ordernumber} of {$sOrder.ordertime|date_format:\"%d.%m.%Y\"} has changed. The new status is as follows: {$sOrder.status_description}.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL5';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your registration has been successful', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {salutation} {firstname} {lastname},\n\nthank you for your registration with our Shop.\n\nYou will gain access via the email address {sMAIL}\nand the password you have chosen.\n\nYou can have your password sent to you by email anytime.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sREGISTERCONFIRMATION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with the demoshop', content = '{include file=\"string:{config name=emailheaderplain}\"}\n\nHello {$billingaddress.firstname} {$billingaddress.lastname},\n\nThank you for your order at {config name=shopName} (Number: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.\nInformation on your order:\n\nPos. Art.No.              Quantities         Price        Total\n{foreach item=details key=position from=$sOrderDetails}\n{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR\n{$details.articlename|wordwrap:49|indent:5}\n{/foreach}\n\nShipping costs: {$sShippingCosts}\nTotal net: {$sAmountNet}\n{if !$sNet}\nTotal gross: {$sAmount}\n{/if}\n\nSelected payment type: {$additional.payment.description}\n{$additional.payment.additionaldescription}\n{if $additional.payment.name == \"debit\"}\nYour bank connection:\nAccount number: {$sPaymentTable.account}\nBIN:{$sPaymentTable.bankcode}\nWe will withdraw the money from your bank account within the next days.\n{/if}\n{if $additional.payment.name == \"prepayment\"}\n\nOur bank connection:\nAccount: ###\nBIN: ###\n{/if}\n\n{if $sComment}\nYour comment:\n{$sComment}\n{/if}\n\nBilling address:\n{$billingaddress.company}\n{$billingaddress.firstname} {$billingaddress.lastname}\n{$billingaddress.street}\n{if {config name=showZipBeforeCity}}{$billingaddress.zipcode} {$billingaddress.city}{else}{$billingaddress.city} {$billingaddress.zipcode}{/if}\n{$billingaddress.phone}\n{$additional.country.countryname}\n\nShipping address:\n{$shippingaddress.company}\n{$shippingaddress.firstname} {$shippingaddress.lastname}\n{$shippingaddress.street}\n{if {config name=showZipBeforeCity}}{$shippingaddress.zipcode} {$shippingaddress.city}{else}{$shippingaddress.city} {$shippingaddress.zipcode}{/if}\n{$additional.countryShipping.countryname}{if $billingaddress.ustid}\n\n\nYour VAT-ID: {$billingaddress.ustid}\nIn case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax.{/if}\n\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDER';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = '{sName} recommends you {sArticle}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\n{sName} has found an interesting product for you on {sShop} that you should have a look at:\n\n{sArticle}\n{sLink}\n\n{sComment}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sTELLAFRIEND';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Forgot password - Your access data for {sShop}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nYour access data for {sShopURL} is as follows:\nUser: {sMail}\nPassword: {sPassword}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sPASSWORD';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Attention - no free serial numbers for {sArticleName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nThere is no additional free serial numbers available for the article {sArticleName}. Please provide new serial numbers immediately or deactivate the article. Please assign a serial number to the customer {sMail} manually.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sNOSERIALS';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your voucher', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {customer},\n\n{user} has followed your recommendation and just ordered at {config name=shopName}.\nThis is why we give you a X € voucher, which you can redeem with your next order.\n\nYour voucher code is as follows: XXX\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sVOUCHER';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your merchant account has been unlocked', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nyour merchant account {config name=shopName} has been unlocked.\n\nFrom now on, we will charge you the net purchase price.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sCUSTOMERGROUPHACCEPTED';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your trader account has not been accepted', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear customer,\n\nthank you for your interest in our trade prices. Unfortunately, we do not have a trading license yet so that we cannot accept you as a merchant.\n\nIn case of further questions please do not hesitate to contact us via telephone, fax or email.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sCUSTOMERGROUPHREJECTED';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your aborted order process - Send us your feedback and get a voucher!', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear customer,\n\nYou have recently aborted an order process on {sShop} - we are always working to make shopping with our shop as pleasant as possible. Therefore we would like to know why your order has failed.\n\nPlease tell us the reason why you have aborted your order. We will reward your additional effort by sending you a 5,00 €-voucher.\n\nThank you for your feedback.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sCANCELEDQUESTION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your aborted order process - Voucher code enclosed', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nDear customer,\n\nYou have recently aborted an order process on {sShop} - today, we would like to give you a 5,00 Euro-voucher - and therefore make it easier for you to decide for an order with Demoshop.de.\n\nYour voucher is valid for two months and can be redeemed by entering the code \"{$sVouchercode}\".\n\nWe would be pleased to accept your order!\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sCANCELEDVOUCHER';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Happy Birthday from {$sConfig.sSHOPNAME}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {if $sUser.salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.firstname} {$sUser.lastname}, we wish you a happy birthday.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sBIRTHDAY';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Stock level of {$sData.count} article{if $sData.count>1}s{/if} under minimum stock ', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\nthe following articles have undershot the minimum stock:\nOrder number Name of article Stock/Minimum stock\n{foreach from=$sJob.articles item=sArticle key=key}\n{$sArticle.ordernumber} {$sArticle.name} {$sArticle.instock}/{$sArticle.stockmin}\n{/foreach}\n\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sARTICLESTOCK';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Thank you for your newsletter subscription', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nthank you for your newsletter subscription at {config name=shopName}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sNEWSLETTERCONFIRMATION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Please confirm your newsletter subscription', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nthank you for signing up for our regularly published newsletter.\n\nPlease confirm your subscription by clicking the following link: {$sConfirmLink}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sOPTINNEWSLETTER';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Please confirm your article evaluation', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nthank you for evaluating the article{$sArticle.articleName}.\n\nPlease confirm the evaluation by clicking the following link: {$sConfirmLink}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sOPTINVOTE';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your article is available again', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nyour article with the order number {$sOrdernumber} is available again.\n\n{$sArticleLink}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sARTICLEAVAILABLE';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Please confirm your e-mail notification', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\n\nthank you for signing up for the automatic email notification for the article {$sArticleName}.\nPlease confirm the notification by clicking the following link:\n\n{$sConfirmLink}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sACCEPTNOTIFICATION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Evaluate article', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {if $sUser.salutation eq "mr"}Mr{elseif $sUser.billing_salutation eq "ms"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nYou bought some products in our store a few days ago. We would really appreciate it if you could rate these.<br/>\nYou help us to improve our service and can give your opinion to other customers.\n\nHere you can find the links for rating the products you bought.\n\n{foreach from=$sArticles item=sArticle key=key}\n{if !$sArticle.modus}\n{$sArticle.articleordernumber} {$sArticle.name} {$sArticle.link}\n{/if}\n{/foreach}\n\nKind regards,\n\nYour {config name=shopName} team.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sARTICLECOMMENT';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'SEPA direct debit mandate', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {$paymentInstance.firstName} {$paymentInstance.lastName}, attached you will find the direct debit mandate form for your order {$paymentInstance.orderNumber}. Please return the completely filled out document by fax or email.\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSEPAAUTHORIZATION';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = 'Your order with {config name=shopName}', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello {if $sUser.billing_salutation eq \"mr\"}Mr{elseif $sUser.billing_salutation eq \"ms\"}Mrs{/if} {$sUser.billing_firstname} {$sUser.billing_lastname},\n\nThe order status of your order {$sOrder.ordernumber} has changed!\nYour order now has the following status: {$sOrder.status_description}.\n\nYou can check the current status of your order on our website under \"My account\" - \"My orders\" anytime. But in case you have purchased without a registration or a customer account, you do not have this option.\n\nBest regards,\nYour team of {config name=shopName}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sORDERSTATEMAIL7';
UPDATE s_core_config_mails SET ishtml = 0, attachment = '', subject = '', content = '{include file=\"string:{config name=emailheaderplain}\"}\r\n\r\nHello,\r\n\r\nthere has been a request to reset you Password in the Shop {sShopURL}.\r\n\r\nPlease confirm the link below to specify a new password.\r\n\r\n{sUrlReset}\r\n\r\nThis link is valid for the next 2 hours. After that you have to request a new confirmation link.\r\n\r\nIf you do not want to reset your password, please ignore this email. No changes will be made.\r\n\r\n{config name=address}\r\n\r\n{include file=\"string:{config name=emailfooterplain}\"}', contentHTML = '' WHERE name = 'sCONFIRMPASSWORDCHANGE';

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
