
TRUNCATE `s_search_index`;
TRUNCATE `s_search_keywords`;
TRUNCATE `s_statistics_currentusers`;
TRUNCATE `s_statistics_pool`;
TRUNCATE `s_statistics_referer`;
TRUNCATE `s_statistics_search`;
TRUNCATE `s_statistics_visitors`;


UPDATE s_user SET email = CONCAT('demoUser', id, '@shopware.de'),
                  password = 'a256a310bc1e5db755fd392c524028a8',
                  paymentID = (SELECT id FROM s_core_paymentmeans WHERE name='cash');

UPDATE  s_user_billingaddress SET
  firstname =  'Muster',
  lastname =  'Mann',
  street = 'Musterstrasse',
  streetnumber =  '1122',
  zipcode =  '123123';

UPDATE  s_user_shippingaddress SET
  firstname =  'Muster',
  lastname =  'Mann',
  street = 'Musterstrasse',
  streetnumber =  '1122',
  zipcode =  '123123';


UPDATE  s_order_billingaddress SET
  firstname =  'Muster',
  lastname =  'Mann',
  street = 'Musterstrasse',
  streetnumber =  '1122',
  zipcode =  '123123';


UPDATE  s_order_shippingaddress SET
  firstname =  'Muster',
  lastname =  'Mann',
  street = 'Musterstrasse',
  streetnumber =  '1122',
  zipcode =  '123123';

UPDATE s_user_debit SET
  account =  '1111111',
  bankcode =  '11111111',
  bankholder = 'Mustermann';


INSERT INTO `s_user` (`id`, `password`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`) VALUES
(NULL, 'a256a310bc1e5db755fd392c524028a8', 'test@example.com', 1, 0, '', 5, '2011-11-23', '2012-01-04 14:12:05', 'uiorqd755gaar8dn89ukp178c7', 0, '', 0, 'EK', 0, 1, 1, '', NULL, '', 0, '0000-00-00 00:00:00');

SET @customerId = (SELECT id FROM s_user WHERE email = 'test@example.com');

INSERT INTO `s_user_billingaddress` (`id`, `userID`, `company`, `department`, `salutation`, `customernumber`, `firstname`, `lastname`, `street`, `streetnumber`, `zipcode`, `city`, `phone`, `fax`, `countryID`, `ustid`, `birthday`) VALUES
(NULL, @customerId, 'Muster GmbH', '', 'mr', '20001', 'Max', 'Mustermann', 'Musterstr.', '55', '55555', 'Musterhausen', '05555 / 555555', '', 2, '', '0000-00-00');

DELETE FROM `s_cms_static` WHERE `link` LIKE '%sViewport=content%';

INSERT IGNORE INTO `s_core_auth` (`id`, `roleID`, `username`, `password`, `apiKey`, `localeID`, `sessionID`, `lastlogin`, `name`, `email`, `active`, `admin`, `salted`, `failedlogins`, `lockeduntil`, `extended_editor`, `disabled_cache`) VALUES
(NULL, 1, 'demo', '84c2ef7bb215395c80119636233765f0', NULL, 1, '', '2012-08-31 11:39:28', 'Demo-Admin', 'demo@demo.de', 1, 1, 1, 0, '0000-00-00 00:00:00', 0, 0);


