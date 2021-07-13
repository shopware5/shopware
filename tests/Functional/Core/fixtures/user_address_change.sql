INSERT INTO `s_user` (`id`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`, `doubleOptinRegister`, `doubleOptinEmailSentDate`, `doubleOptinConfirmDate`, `firstlogin`, `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`, `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`, `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`, `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`, `login_token`, `changed`, `password_change_date`, `register_opt_in_id`) VALUES
(3,	'$2y$10$Z9JAOaS72cvvMfFRS2ObNui8y0LDNy4JisrN/Pd.Vb9spH95LS2g.',	'bcrypt',	'unit@test.com',	1,	0,	'',	5,	0,	NULL,	NULL,	'2021-07-09',	'2021-07-09 05:12:31',	'f375fe1b4ad9c6f2458844226831463f',	0,	'',	0,	'EK',	0,	'1',	1,	'',	NULL,	'',	0,	NULL,	501,	701,	NULL,	'mr',	'Unit',	'Tester',	NULL,	'20005',	'06367cee-ad6c-4031-ab22-580584ef8cfc.1',	'2021-07-09 07:08:11',	'2021-07-09 07:08:11',	NULL);

INSERT INTO `s_user_addresses` (`id`, `user_id`, `company`, `department`, `salutation`, `title`, `firstname`, `lastname`, `street`, `zipcode`, `city`, `country_id`, `state_id`, `ustid`, `phone`, `additional_address_line1`, `additional_address_line2`) VALUES
(501,	3,	NULL,	NULL,	'mr',	NULL,	'Unit',	'Tester',	'FooBar, 7',	'12345',	'UnitTest',	2,	NULL,	NULL,	NULL,	NULL,	NULL),
(601,	3,	NULL,	NULL,	'mr',	NULL,	'Unit',	'Tester',	'FooBar, 42',	'12345',	'UnitTest',	23,	NULL,	NULL,	NULL,	NULL,	NULL),
(701,	3,	NULL,	NULL,	'mr',	NULL,	'Unit',	'Tester',	'FooBar, 12',	'12345',	'UnitTest',	21,	NULL,	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_user_addresses_attributes` (`id`, `address_id`, `text1`, `text2`, `text3`, `text4`, `text5`, `text6`) VALUES
(501,	501,	'Freitext1',	'Freitext2',	NULL,	NULL,	NULL,	NULL),
(511,	601,	'Freitext1',	'Freitext2',	NULL,	NULL,	NULL,	NULL),
(521,	701,	'Freitext1',	'Freitext2',	NULL,	NULL,	NULL,	NULL);

INSERT INTO `s_core_tax_rules` (`id`, `areaID`, `countryID`, `stateID`, `groupID`, `customer_groupID`, `tax`, `name`, `active`) VALUES
(1,	3,	23,	NULL,	1,	1,	20.00,	'Austria',	1),
(2,	3,	21,	NULL,	1,	1,	25.00,	'Netherlands',	1);
