
TRUNCATE `s_search_index`;
TRUNCATE `s_search_keywords`;
TRUNCATE `s_statistics_currentusers`;
TRUNCATE `s_statistics_pool`;
TRUNCATE `s_statistics_referer`;
TRUNCATE `s_statistics_search`;
TRUNCATE `s_statistics_visitors`;
TRUNCATE `s_user`;
TRUNCATE `s_user_attributes`;
TRUNCATE `s_user_billingaddress`;
TRUNCATE `s_user_billingaddress_attributes`;
TRUNCATE `s_user_debit`;
TRUNCATE `s_user_shippingaddress`;
TRUNCATE `s_user_shippingaddress_attributes`;

INSERT INTO `s_user` (`id`, `password`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`) VALUES
(1, 'a256a310bc1e5db755fd392c524028a8', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', 'uiorqd755gaar8dn89ukp178c7', 0, '', 0, 'EK', 0, 1, 1, '', NULL, '', 0, '0000-00-00 00:00:00');

INSERT INTO `s_user_billingaddress` (`id`, `userID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid`, `birthday`) VALUES
(1, 1, 'Muster GmbH', '', 'mr', '20001', 'Max', 'Mustermann', 'Musterstr.', '55', '55555', 'Musterhausen', '05555 / 555555', '', 2, '', '0000-00-00');

DELETE FROM `s_cms_static` WHERE `link` LIKE '%sViewport=content%';
