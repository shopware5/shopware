SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_order;

INSERT INTO `s_order` (`id`, `ordernumber`, `userID`, `invoice_amount`, `invoice_amount_net`, `paymentID`, `dispatchID`, `invoice_shipping`, `invoice_shipping_net`, `ordertime`, `status`, `cleared`, `transactionID`, `comment`, `customercomment`, `internalcomment`, `net`, `taxfree`, `partnerID`, `temporaryID`, `referer`, `cleareddate`, `trackingcode`, `language`, `currency`, `currencyFactor`, `subshopID`, `remote_addr`, `deviceType`) VALUES
    (1, '20000', 1, 150.00, 10.00, 4, 7, 0, 0, '2012-08-30 15:50:00', 0, 0, '', '', '', '', 0, 0, '', '', '', NULL, '', '1', 'EUR', 1, 1, '', NULL);

SET FOREIGN_KEY_CHECKS=1;
