SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_premium_dispatch;
DELETE FROM s_premium_shippingcosts;
DELETE FROM s_order;

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
    (1, 'Example dispatch 1', 0, 'Example description 1', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (2, 'Example dispatch 2', 1, 'Example description 1', '', 1, 0, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (3, 'Example dispatch 3', 3, 'Example description 3', '', 1, 0, 1, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (4, 'Example dispatch 4', 0, 'Example description 4', '', 1, 2, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (5, 'Example dispatch 5', 0, 'Example description 5', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (6, 'Example dispatch 6', 0, 'Example description 6', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL);

INSERT INTO `s_premium_shippingcosts` (`from`, `value`, `factor`, `dispatchID`) VALUES
    ('0.000', '15.00', '0.00', 1),
    ('5.000', '1.00', '0.00', 1),
    ('0.000', '13.00', '0.00', 2),
    ('0.000', '14.00', '0.00', 3),
    ('0.000', '10.00', '0.00', 4),
    ('5.000', '5.00', '0.00', 4),
    ('10.000', '2.50', '0.00', 4),
    ('0.000', '15.00', '0.00', 5),
    ('0.000', '15.00', '0.00', 6);

INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `paymentID`, `dispatchID`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
    (1, '20001', 1, 100.00, 90.00, 4, 1, 0, 0, '2012-08-30 10:15:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL),
    (2, '20002', 2, 200.00, 80.00, 1, 2, 0, 0, '2012-08-30 15:20:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL),
    (3, '20003', 3, 300.00, 70.00, 2, 3, 0, 0, '2012-08-30 15:25:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL),
    (4, '20004', 3, 400.00, 60.00, 3, 4, 0, 0, '2012-08-30 15:30:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL),
    (5, '20005', 4, 200.00, 50.00, 4, 2, 0, 0, '2012-08-30 15:35:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL),
    (6, '20006', 4, 120.00, 90.00, 4, 6, 0, 0, '2012-08-30 15:35:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL);

INSERT INTO `s_premium_dispatch_categories` (`dispatchID`, `categoryID`) VALUES
    (6, 2);

SET FOREIGN_KEY_CHECKS=1;
