SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_user;
DELETE FROM s_user_addresses;
DELETE FROM s_order;

INSERT INTO `s_user` (`id`, `customernumber`, `default_billing_address_id`, `default_shipping_address_id`, `salutation`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `title`, `firstname`, `lastname`, `birthday`, `login_token`) VALUES
    (1, '20001', 1, 2, 'mr', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-01-01', NULL),
    (2, '20002', 1, 2, 'mrs', 'example_password123', 'md5', 'test2@example.com', 1, 1, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-02-01', NULL),
    (3, '20003', 1, 2, 'ms', 'example_password123', 'md5', 'test3@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-03-01', NULL),
    (4, '20004', 1, 4, 'herr', 'example_password123', 'md5', 'test4@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1994-01-01', NULL),
    (5, '20005', 1, 4, 'frau', 'example_password123', 'md5', 'test5@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1995-01-01', NULL),
    (6, '20006', 1, 1, 'ms', 'example_password123', 'md5', 'test6@example.com', 1, 0, '', 5, '2013-11-25', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1995-01-02', NULL),
    (7, '20007', 3, 4, 'mrs', 'example_password123', 'md5', 'test7@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', NULL, NULL),
    (8, '20008', 3, 4, 'mr', 'example_password123', 'md5', 'test8@example.com', 1, 0, '', 5, '2014-01-01', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 2, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', NULL, NULL);

INSERT INTO `s_campaigns_mailaddresses` (`customer`, `groupID`, `email`, `lastmailing`, `lastread`, `added`, `double_optin_confirmed`) VALUES
    (1, 0, 'test5@example.com', 0, 0, '2018-06-07 10:46:11', NULL);

INSERT INTO `s_order` (`ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `paymentID`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `dispatchID`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`, `changed`) VALUES
    ('20001', 4, 650, 600, 0, 0, '2012-08-30 10:15:54', 0, 17, 4, '', '', '', '', 1, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, 'x.x.x.x', NULL, '2018-06-05 11:46:25'),
    ('20002', 4, 100, 80, 0, 0, '2012-08-30 10:15:54', 0, 17, 4, '', '', '', '', 1, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, 'x.x.x.x', NULL, '2018-06-05 11:46:25'),
    ('20003', 5, 850, 800, 0, 0, '2012-08-30 10:15:54', 0, 17, 4, '', '', '', '', 1, 0, '', '', '', NULL, '', '1', 9, 'EUR', 1, 1, 'x.x.x.x', NULL, '2018-06-05 11:46:25');

SET FOREIGN_KEY_CHECKS=1;
