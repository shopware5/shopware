SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_order;
DELETE FROM s_order_details;
DELETE FROM s_core_paymentmeans;
DELETE FROM s_premium_dispatch;
DELETE FROM s_premium_shippingcosts;
DELETE FROM s_user;
DELETE FROM s_order_billingaddress;
DELETE FROM s_order_shippingaddress;
DELETE FROM s_campaigns_mailaddresses;

INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `paymentID`, `dispatchID`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`, `changed`) VALUES
    (1, '20001', 1, 100.00, 90.00, 4, 8, 15, 0, '2012-08-30 10:15:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', 'mobile', '1993-01-01 00:00:00'),
    (2, '20002', 2, 200.00, 80.00, 1, 9, 0, 0, '2012-08-30 15:20:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'DKK', 1, 1, '', NULL, '1993-01-01 00:00:00'),
    (3, '20003', 3, 300.00, 70.00, 2, 6, 0, 0, '2012-08-30 15:25:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL, '1993-01-01 00:00:00'),
    (4, '20004', 3, 400.00, 60.00, 3, 9, 0, 0, '2012-08-30 15:30:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL, '1993-01-01 00:00:00'),
    (5, '20005', 4, 200.00, 50.00, 4, 9, 0, 0, '2012-08-30 15:35:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL, '1994-01-01 00:00:00'),
    (6, '20006', 2, 250.00, 85.00, 1, 7, 0, 0, '2012-08-30 15:40:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL, '1995-01-01 00:00:00'),
    (7, '20007', 5, 350.00, 75.00, 3, 8, 0, 0, '2012-08-30 15:45:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL, '1996-01-01 00:00:00'),
    (8, '20008', 6, 150.00, 10.00, 4, 7, 0, 0, '2012-08-30 15:50:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 2, '', NULL, '1997-01-01 00:00:00'),
    (9, '20009', 6, 120.00, 20.00, 4, 7, 0, 0, '2012-08-30 15:50:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 2, '', NULL, '1998-01-01 00:00:00');

INSERT INTO `s_order_details` (`orderID`, `ordernumber`, `articleID`, `articleordernumber`, `price`, `quantity`, `name`, `ean`, `unit`, `pack_unit`, `status`, `shipped`, `shippedgroup`, `releasedate`, `modus`, `esdarticle`, `taxID`, `tax_rate`, `config`) VALUES
    (1, '20001', 10, 'SW10010', 150.00, 1, 'Example product 1', 'example_ean', NULL, NULL, 0, 0, 0, '0000-00-00', 0, 1, 1, 19, ''),
    (1, '20001', 11, 'SW10011', 20, 4, 'Example product 2', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (2, '20002', 12, 'SW10012', 30, 1, 'Example product 3', NULL, 'liter', 'bottles', 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (3, '20003', 13, 'SW10013', 40, 3, 'Example product 4', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (4, '20004', 14, 'SW10014', 50, 5, 'Example product 5', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (4, '20004', 15, 'SW10015', 60, 7, 'Example product 6', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (4, '20004', 16, 'SW10016', 70, 1, 'Example product 7', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (5, '20005', 17, 'SW10017', 80, 2, 'Example product 8', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (5, '20005', 18, 'SW10018', 90, 1, 'Example product 9', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (6, '20006', 19, 'SW10019', 100, 6, 'Example product 10', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (6, '20006', 20, 'SW10020', 110, 9, 'Example product 11', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (6, '20006', 21, 'SW10021', 120, 1, 'Example product 12', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (7, '20007', 22, 'SW10022', 130, 2, 'Example product 13', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (7, '20007', 23, 'SW10023', 140, 4, 'Example product 14', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (7, '20007', 24, 'SW10024', 150, 5, 'Example product 15', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (7, '20007', 25, 'SW10025', 160, 2, 'Example product 16', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, ''),
    (8, '20008', 26, 'SW10026', 170, 1, 'Example product 17', NULL, NULL, NULL, 0, 0, 0, '0000-00-00', 0, 0, 1, 19, '');

UPDATE `s_benchmark_config` SET last_order_id=0, batch_size=5;

INSERT INTO `s_core_paymentmeans` (`id`, `name`, `description`, `debit_percent`, `surcharge`, `surchargestring`, `template`, `class`, `table`, `hide`, `additionaldescription`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`, `mobile_inactive`) VALUES
    (1, 'example1', 'Example 1', 0, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (2, 'example2', 'Example 2', 1, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (3, 'example3', 'Example 3', 0, 2, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (4, 'example4', 'Example 4', 0, 0, 'DE:4;AE:-1', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0);

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
    (6, 'Example dispatch 1', 0, 'Example description 1', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (7, 'Example dispatch 2', 1, 'Example description 1', '', 1, 0, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (8, 'Example dispatch 3', 3, 'Example description 3', '', 1, 0, 1, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (9, 'Example dispatch 4', 0, 'Example description 4', '', 1, 2, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL);

INSERT INTO `s_premium_shippingcosts` (`from`, `value`, `factor`, `dispatchID`) VALUES
    ('0.000', '15.00', '0.00', 6),
    ('5.000', '1.00', '0.00', 6),
    ('0.000', '13.00', '0.00', 7),
    ('0.000', '14.00', '0.00', 8),
    ('0.000', '10.00', '0.00', 9),
    ('5.000', '5.00', '0.00', 9),
    ('10.000', '2.50', '0.00', 9);

INSERT INTO `s_user` (`id`, `customernumber`, `default_billing_address_id`, `default_shipping_address_id`, `salutation`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `title`, `firstname`, `lastname`, `birthday`, `login_token`) VALUES
    (1, '20001', 1, 1, 'mr', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-01-01', NULL),
    (2, '20002', 2, 2, 'mrs', 'example_password123', 'md5', 'test2@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-02-01', NULL),
    (3, '20003', 3, 3, 'ms', 'example_password123', 'md5', 'test3@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-03-01', NULL),
    (4, '20004', 5, 5, 'mr', 'example_password123', 'md5', 'test4@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-04-01', NULL),
    (5, '20005', 7, 7, 'herr', 'example_password123', 'md5', 'test5@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-05-01', NULL),
    (6, '20006', 8, 8, 'mrs', 'example_password123', 'md5', 'test6@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-06-01', NULL);

INSERT INTO `s_order_billingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `ustid`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (1, 1, 1, '', '', 'mr', NULL, 'Foo 1', 'Bar 1', 'Example street 1', '00000', 'Example city 1', '012345 / 6789', 2, 3, '', NULL, NULL, NULL),
    (2, 2, 2, '', '', 'mr', NULL, 'Foo 2', 'Bar 2', 'Example street 2', '00000', 'Example city 2', '012345 / 6789', 3, 3, '', NULL, NULL, NULL),
    (3, 3, 3, '', '', 'mr', NULL, 'Foo 3', 'Bar 3', 'Example street 3', '00000', 'Example city 3', '012345 / 6789', 4, 3, '', NULL, NULL, NULL),
    (4, 3, 4, '', '', 'mr', NULL, 'Foo 4', 'Bar 4', 'Example street 4', '00000', 'Example city 4', '012345 / 6789', 4, 3, '', NULL, NULL, NULL),
    (5, 4, 5, '', '', 'mr', NULL, 'Foo 5', 'Bar 5', 'Example street 5', '00000', 'Example city 5', '012345 / 6789', 5, 3, '', NULL, NULL, NULL),
    (6, 2, 6, '', '', 'mr', NULL, 'Foo 6', 'Bar 6', 'Example street 6', '00000', 'Example city 6', '012345 / 6789', 3, 3, '', NULL, NULL, NULL),
    (7, 5, 7, '', '', 'mr', NULL, 'Foo 7', 'Bar 7', 'Example street 7', '00000', 'Example city 7', '012345 / 6789', 6, 3, '', NULL, NULL, NULL),
    (8, 6, 8, '', '', 'mr', NULL, 'Foo 8', 'Bar 8', 'Example street 8', '00000', 'Example city 8', '012345 / 6789', 7, 3, '', NULL, NULL, NULL);

INSERT INTO `s_order_shippingaddress` (`id`, `userID`, `orderID`, `company`, `department`, `salutation`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `phone`, `countryID`, `stateID`, `additional_address_line1`, `additional_address_line2`, `title`) VALUES
    (1, 1, 1, '', '', 'mr', 'Foo 1', 'Bar 1', 'Example street 1', '00000', 'Example city 1', '012345 / 6789', 10, 3, NULL, NULL, NULL),
    (2, 2, 2, '', '', 'mr', 'Foo 2', 'Bar 2', 'Example street 2', '00000', 'Example city 2', '012345 / 6789', 3, 3, NULL, NULL, NULL),
    (3, 3, 3, '', '', 'mr', 'Foo 3', 'Bar 3', 'Example street 3', '00000', 'Example city 3', '012345 / 6789', 4, 3, NULL, NULL, NULL),
    (4, 3, 4, '', '', 'mr', 'Foo 4', 'Bar 4', 'Example street 4', '00000', 'Example city 4', '012345 / 6789', 4, 3, NULL, NULL, NULL),
    (5, 4, 5, '', '', 'mr', 'Foo 5', 'Bar 5', 'Example street 5', '00000', 'Example city 5', '012345 / 6789', 5, 3, NULL, NULL, NULL),
    (6, 2, 6, '', '', 'mr', 'Foo 6', 'Bar 6', 'Example street 6', '00000', 'Example city 6', '012345 / 6789', 3, 3, NULL, NULL, NULL),
    (7, 5, 7, '', '', 'mr', 'Foo 7', 'Bar 7', 'Example street 7', '00000', 'Example city 7', '012345 / 6789', 6, 3, NULL, NULL, NULL),
    (8, 6, 8, '', '', 'mr', 'Foo 8', 'Bar 8', 'Example street 8', '00000', 'Example city 8', '012345 / 6789', 7, 3, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
