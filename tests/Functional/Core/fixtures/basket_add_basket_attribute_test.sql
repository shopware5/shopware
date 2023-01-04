INSERT INTO `s_order_basket` (`sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
    ('BasketAddBasketAttributesTestSessionId',	0,	'discountName',	0,	'SWOrderNumber',	0,	1,	10.99,	9.24,	19,	'2023-01-04 10:44:26',	1,	0,	'',	'',	'',	'',	1),
    ('BasketAddBasketAttributesTestSessionId',	0,	'discountName',	0,	'OtherSWOrderNumber',	0,	1,	1.99,	0.924,	19,	'2023-01-04 10:44:26',	0,	0,	'',	'',	'',	'',	1);

INSERT INTO `s_core_customergroups_discounts` (`groupID`, `basketdiscount`, `basketdiscountstart`) VALUES (	1,	5,	10);
