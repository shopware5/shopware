INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`,
                       `invoice_shipping_net`, `invoice_shipping_tax_rate`, `ordertime`, `status`, `cleared`,
                       `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`,
                       `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`,
                       `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`,
                       `is_proportional_calculation`, `changed`)
VALUES (390059, '30003', 1, 120.79, 101.51, 3.9, 3.28, 19, '2021-10-26 08:38:40', 0, 17, 5, '', '', '', '', 0, 0, '', '',
        '', NULL, '', '1', 9, 'EUR', 1, 1, '172.18.0.0', 'desktop', 0, '2021-10-26 08:38:40'),
       (390061, '30004', 1, 66.84, 56.17, 3.9, 3.28, 19, '2021-10-26 08:39:30', 0, 17, 5, '', '', '', '', 0, 0, '', '', '',
        NULL, '', '1', 9, 'EUR', 1, 1, '172.18.0.0', 'desktop', 0, '2021-10-26 08:39:30'),
       (390063, '30005', 1, 79.33, 66.67, 3.9, 3.28, 19, '2021-10-26 08:39:54', 0, 17, 5, '', '', '', '', 0, 0, '', '', '',
        NULL, '', '1', 9, 'EUR', 1, 1, '172.18.0.0', 'desktop', 0, '2021-10-26 08:39:54');

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
(39003,	1,	390059,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL),
(39004,	1,	390061,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL),
(39005,	1,	390063,	'Muster GmbH',	'',	'mr',	'20001',	'Max',	'Mustermann',	'Musterstr. 55',	'55555',	'Musterhausen',	'05555 / 555555',	2,	3,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
(39003,	1,	390059,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	''),
(39004,	1,	390061,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	''),
(39005,	1,	390063,	'shopware AG',	'',	'mr',	'Max',	'Mustermann',	'Mustermannstraße 92',	'48624',	'Schöppingen',	'',	2,	NULL,	'',	'',	'');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`, `articleDetailID`) VALUES
(3900210,	390059,	'30003',	213,	'SW10211',	39.95,	1,	'Surfbrett',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'Stück',	751),
(3900211,	390059,	'30003',	238,	'SW10229',	38.95,	1,	'Strandbag Sailor',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'',	793),
(3900212,	390059,	'30003',	173,	'SW10173',	39.99,	1,	'Strandkleid Flower Power',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'Stück',	402),
(3900213,	390059,	'30003',	0,	'SHIPPINGDISCOUNT',	-2,	1,	'Warenkorbrabatt',	0,	0,	0,	'0000-00-00',	4,	0,	0,	19,	'',	'',	'',	'',	NULL),
(3900217,	390061,	'30004',	178,	'SW10178',	19.95,	1,	'Strandtuch \"Ibiza\"',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'Stück',	407),
(3900218,	390061,	'30004',	179,	'SW10179.1',	44.99,	1,	'Strandtuch in mehreren Farben blau',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'',	409),
(3900219,	390061,	'30004',	0,	'SHIPPINGDISCOUNT',	-2,	1,	'Warenkorbrabatt',	0,	0,	0,	'0000-00-00',	4,	0,	0,	19,	'',	'',	'',	'',	NULL),
(3900224,	390063,	'30005',	170,	'SW10170',	39.95,	1,	'Sonnenbrille \"Red\"',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'',	394),
(3900225,	390063,	'30005',	165,	'SW10165',	19.99,	1,	'Sommerschal Light Red aus Seide',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'',	389),
(3900226,	390063,	'30005',	169,	'SW10169',	17.49,	1,	'Pilotenbrille Silver Sky',	0,	0,	0,	'0000-00-00',	0,	0,	1,	19,	'',	'',	'',	'',	393),
(3900227,	390063,	'30005',	0,	'SHIPPINGDISCOUNT',	-2,	1,	'Warenkorbrabatt',	0,	0,	0,	'0000-00-00',	4,	0,	0,	19,	'',	'',	'',	'',	NULL);
