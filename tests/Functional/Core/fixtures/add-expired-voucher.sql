UPDATE s_user SET password = 'a256a310bc1e5db755fd392c524028a8'
WHERE email LIKE 'test@example.com';

INSERT INTO `s_emarketing_vouchers` (`id`, `description`, `vouchercode`, `numberofunits`, `value`, `minimumcharge`, `shippingfree`, `bindtosupplier`, `valid_from`, `valid_to`, `ordercode`, `modus`, `percental`, `numorder`, `customergroup`, `restrictarticles`, `strict`, `subshopID`, `taxconfig`, `customer_stream_ids`) VALUES
(400042,	'unitTestVoucher',	'',	100,	5,	10,	0,	NULL,	'2021-07-01',	'2021-07-10',	'FOOBAR',	1,	0,	0,	NULL,	'',	0,	NULL,	'auto',	NULL);

INSERT INTO `s_emarketing_voucher_codes` (`id`, `voucherID`, `userID`, `code`, `cashed`) VALUES
(100042,	400042,	NULL,	'foobar01',	0);

INSERT INTO `s_order_basket` (`id`, `sessionID`, `userID`, `articlename`, `articleID`, `ordernumber`, `shippingfree`, `quantity`, `price`, `netprice`, `tax_rate`, `datum`, `modus`, `esdarticle`, `partnerID`, `lastviewport`, `useragent`, `config`, `currencyFactor`) VALUES
(871,	'sessionId',	1,	'Strandtuch \"Ibiza\"',	178,	'SW10178',	0,	2,	19.95,	16.764705882353,	19,	'2021-07-13 09:47:05',	0,	0,	'',	'',	'',	'',	1),
(872,	'sessionId',	1,	'Warenkorbrabatt',	0,	'SHIPPINGDISCOUNT',	0,	1,	-2,	-1.68,	19,	'2021-07-13 09:47:05',	4,	0,	'',	'',	'',	'',	1);
