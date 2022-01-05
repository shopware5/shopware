INSERT INTO `s_user` (`id`, `password`, `encoder`, `email`, `active`, `accountmode`, `confirmationkey`, `paymentID`,
                      `doubleOptinRegister`, `doubleOptinEmailSentDate`, `doubleOptinConfirmDate`, `firstlogin`,
                      `lastlogin`, `sessionID`, `newsletter`, `validation`, `affiliate`, `customergroup`,
                      `paymentpreset`, `language`, `subshopID`, `referer`, `pricegroupID`, `internalcomment`,
                      `failedlogins`, `lockeduntil`, `default_billing_address_id`, `default_shipping_address_id`,
                      `title`, `salutation`, `firstname`, `lastname`, `birthday`, `customernumber`, `login_token`,
                      `changed`, `password_change_date`, `register_opt_in_id`)
VALUES (:customerId, '$2y$10$f8dUOFLOd0QOkzrqffu4EOXexhv..LVe72TwaOem0KXV2AyEjODvK', 'bcrypt', 'test@shopware.com', 1, 0, '', 5,
        0, NULL, NULL, '2021-12-30', '2021-12-30 11:28:33', 'ppvfk1jhcno42d801k3o8e0j94', 0, '', 0, 'EK', 0, '1', 1, '',
        NULL, '', 0, NULL, 5, 5, NULL, 'mr', 'Bruce', 'Wayne', NULL, '20005', 'b7d6a2a8-f8bb-40b5-af7e-3feac81bc23a.1',
        '2021-12-30 11:27:51', '2021-12-30 11:27:51', NULL);
