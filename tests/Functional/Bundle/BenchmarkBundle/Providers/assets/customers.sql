SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_user;
DELETE FROM s_user_addresses;

INSERT INTO `s_user` (`customernumber`, `default_billing_address_id`, `default_shipping_address_id`, `salutation`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `title`, `firstname`, `lastname`, `birthday`, `login_token`) VALUES
    ('20001', 1, 2, 'mr', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-01-01', NULL),
    ('20002', 1, 2, 'mrs', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-02-01', NULL),
    ('20003', 1, 2, 'ms', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1993-03-01', NULL),
    ('20004', 1, 4, 'herr', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1994-01-01', NULL),
    ('20005', 1, 4, 'frau', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1995-01-01', NULL),
    ('20006', 1, 1, 'ms', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', '1995-01-02', NULL),
    ('20007', 3, 4, 'mrs', 'example_password123', 'md5', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', '', 0, '', 0, 'EK', 0, '1', 1, '', NULL, '', 0, NULL, NULL, 'Max', 'Mustermann', NULL, NULL);

INSERT INTO `s_user_addresses` (`id`, `user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`) VALUES
    (1, 1, 'Muster GmbH', NULL, 'mr', NULL, 'Max', 'Mustermann', 'Musterstr. 55', '55555', 'Musterhausen', 2, NULL, NULL, NULL, NULL, NULL),
    (2, 1, 'shopware AG', NULL, 'mr', NULL, 'Max', 'Mustermann', 'Musterstr. 55', '55555', 'Musterhausen', 3, NULL, NULL, NULL, NULL, NULL),
    (3, 1, 'Muster GmbH', NULL, 'mrs', NULL, 'Max', 'Mustermann', 'Musterstr. 55', '55555', 'Musterhausen', 3, NULL, NULL, NULL, NULL, NULL),
    (4, 1, 'shopware AG', NULL, 'mrs', NULL, 'Max', 'Mustermann', 'Musterstr. 55', '55555', 'Musterhausen', 4, NULL, NULL, NULL, NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;
