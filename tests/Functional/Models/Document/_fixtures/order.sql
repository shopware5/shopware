INSERT INTO `s_emarketing_vouchers` (`id`, `description`, `vouchercode`, `numberofunits`, `value`, `minimumcharge`, `shippingfree`, `bindtosupplier`, `valid_from`, `valid_to`, `ordercode`, `modus`, `percental`, `numorder`, `customergroup`, `restrictarticles`, `strict`, `subshopID`, `taxconfig`, `customer_stream_ids`) VALUES
(50001,	'percentage',	'percentage',	1,	25,	5,	0,	NULL,	NULL,	NULL,	'percentage',	0,	1,	1,	NULL,	'',	0,	NULL,	'auto',	NULL);

INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `invoice_shipping_tax_rate`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`, `is_proportional_calculation`, `changed`) VALUES
(60001,	'20003',	1,	19.91,	16.6,	4.95,	4.13,	20,	'2021-07-16 11:45:56',	0,	17,	5,	'',	'',	'',	'',	0,	0,	'',	'',	'',	NULL,	'',	'1',	17,	'EUR',	1,	1,	'172.18.0.0',	'desktop',	0,	'2021-07-16 11:45:56');

INSERT INTO `s_order_attributes` (`id`, `orderID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
(50001,	60001,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`, `articleDetailID`) VALUES
(2090001,	60001,	'20003',	178,	'SW10178',	19.95,	1,	'Strandtuch \"Ibiza\"',	0,	0,	0,	'0000-00-00',	0,	0,	5,	20,	'',	'',	'',	'Stück',	407),
(2100001,	60001,	'20003',	50001,	'percentage',	-4.9875,	1,	'Gutschein 25 %',	0,	0,	0,	'0000-00-00',	2,	0,	0,	20,	'',	'',	'',	'',	0);

INSERT INTO `s_order_details_attributes` (`id`, `detailID`, `attribute1`, `attribute2`, `attribute3`, `attribute4`, `attribute5`, `attribute6`) VALUES
(120001,	2090001,	'',	NULL,	NULL,	NULL,	NULL,	NULL),
(130001,	2100001,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
(300001,	1,	60001,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_billingaddress_attributes` (`id`, `billingID`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
(300001,	300001,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
(300001,	1,	60001,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	'');

INSERT INTO `s_order_shippingaddress_attributes` (`id`, `shippingID`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
(300001,	300001,	NULL,	NULL,	NULL,	NULL,	NULL,	NULL);

