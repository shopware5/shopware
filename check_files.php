<?php
$text = '
Hello {$billingaddress.firstname} {$billingaddress.lastname},
 
Thank you for your order with Shopware Demoshop (Nummer: {$sOrderNumber}) on {$sOrderDay} at {$sOrderTime}.
Information on your order:
 
Pos. Art.No.              Quantities         Price        Total
{foreach item=details key=position from=$sOrderDetails}
{$position+1|fill:4} {$details.ordernumber|fill:20} {$details.quantity|fill:6} {$details.price|padding:8} EUR {$details.amount|padding:8} EUR
{$details.articlename|wordwrap:49|indent:5}
{/foreach}
 
Shipping costs: {$sShippingCosts}
Total net: {$sAmountNet}
{if !$sNet}
Total gross: {$sAmount}
{/if}
 
Selected payment type: {$additional.payment.description}
{$additional.payment.additionaldescription}
{if $additional.payment.name == "debit"}
Your bank connection:
Account number: {$sPaymentTable.account}
BIN:{$sPaymentTable.bankcode}
We will withdraw the money from your bank account within the next days.
{/if}
{if $additional.payment.name == "prepayment"}
 
Our bank connection:
Account: ###
BIN: ###
{/if}
 
{if $sComment}
Your comment:
{$sComment}
{/if}
 
Billing address:
{$billingaddress.company}
{$billingaddress.firstname} {$billingaddress.lastname}
{$billingaddress.street} {$billingaddress.streetnumber}
{$billingaddress.zipcode} {$billingaddress.city}
{$billingaddress.phone}
{$additional.country.countryname}
 
Shipping address:
{$shippingaddress.company}
{$shippingaddress.firstname} {$shippingaddress.lastname}
{$shippingaddress.street} {$shippingaddress.streetnumber}
{$shippingaddress.zipcode} {$shippingaddress.city}
{$additional.country.countryname}
 
{if $billingaddress.ustid}
Your VAT-ID: {$billingaddress.ustid}
In case of a successful order and if you are based in one of the EU countries, you will receive your goods exempt from turnover tax. 
{/if}
 
 
In case of further questions, do not hesitate to contact us. 
 
We wish you a pleasant day.
 
Enter your contact details here.
';
//echo str_replace("\r","",$text);

echo str_replace("\n","\\n",$text);
