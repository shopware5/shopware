INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`,
                       `invoice_shipping_net`, `invoice_shipping_tax_rate`, `ordertime`, `status`, `cleared`,
                       `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`,
                       `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`,
                       `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`,
                       `is_proportional_calculation`, `changed`)
VALUES (:orderId, '20003', :customerId, 95.57, 79.65, 24.99, 20.83, 20, '2021-12-30 11:28:33', 0, 17, 5, '', '', '', '', 0, 0, '',
        '', '', NULL, '', '1', 16, 'EUR', 1, 1, '::', 'desktop', 0, '2021-12-30 11:28:33');

INSERT INTO `s_order_details` (`id`, `orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`,
                               `name`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`,
                               `taxID`, `tax_rate`, `config`, `ean`, `unit`, `pack_unit`, `articleDetailID`)
VALUES (:orderDetailId, :orderId, '20003', 141, 'SW10141', 70.58, 1, 'Fahrerhandschuh aus Peccary Leder', 0, 0, 0,
        '0000-00-00', 0, 0, 1, 20, '', '', '', '', 288);

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`,
                                      `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`,
                                      `countryID`, `stateID`, `ustid`, `additional_address_line1`,
                                      `additional_address_line2`, `title`)
VALUES (12345, :customerId, :orderId, '', '', 'mr', '20005', 'Bruce', 'Wayne', 'Test Street 123', '11111', 'Gotham', '', 5, NULL,
        NULL, NULL, NULL, NULL);

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`,
                                       `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`,
                                       `additional_address_line1`, `additional_address_line2`, `title`)
VALUES (12345, :customerId, :orderId, '', '', 'mr', 'Bruce', 'Wayne', 'Test Street 123', '11111', 'Gotham', '', 5, NULL, '', '', '');
