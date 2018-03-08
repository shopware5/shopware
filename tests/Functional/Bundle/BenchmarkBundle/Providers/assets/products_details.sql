SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_articles_details;

INSERT INTO `s_articles_details` (`articleID`, `ordernumber`, `suppliernumber`, `kind`, `additionaltext`, `sales`, `active`, `instock`, `stockmin`, `laststock`, `weight`, `position`, `width`, `height`, `length`, `ean`, `unitID`, `purchasesteps`, `maxpurchase`, `minpurchase`, `purchaseunit`, `referenceunit`, `packunit`, `releasedate`, `shippingfree`, `shippingtime`, `purchaseprice`) VALUES
    (1, 'SW10001', NULL, 1, NULL, 0, 1, 10, 2, 0, '0.170', 0, NULL, NULL, NULL, NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 0, '3', 0),
    (1, 'SW10002', NULL, 1, NULL, 0, 1, 0, 0, 0, '45.000', 0, '1.000', '1.000', '1.000', NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 1, '7', 0),
    (1, 'SW10003', NULL, 1, NULL, 0, 1, 0, 0, 0, '0.000', 0, NULL, NULL, NULL, NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 1, '0', 0),
    (1, 'SW10004', '', 2, '', 0, 1, 10, 0, 0, '0.000', 0, NULL, NULL, NULL, '', 9, NULL, NULL, 1, '1.0000', '1.000', '', NULL, 0, '', 0),
    (1, 'SW10005', '', 2, '', 0, 1, 10, 0, 0, '0.000', 0, NULL, NULL, NULL, '', 9, NULL, NULL, 1, '1.0000', '1.000', '', NULL, 0, '', 0),
    (3, 'SW10006', '', 1, '', 0, 1, 50, 3, 0, '0.150', 0, NULL, NULL, NULL, '', 9, 0, 0, 1, '1.0000', '1.000', '', NULL, 1, '', 0),
    (3, 'SW10007', NULL, 1, NULL, 0, 1, 50, 5, 0, '0.000', 0, NULL, NULL, NULL, NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 0, NULL, 0),
    (3, 'SW10008', NULL, 1, NULL, 0, 1, 100, 10, 0, '0.100', 0, NULL, NULL, NULL, NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 0, NULL, 0),
    (9, 'SW10009', NULL, 1, NULL, 0, 1, 100, 10, 0, '0.100', 0, NULL, NULL, NULL, NULL, 9, 0, 0, 1, '1.0000', '1.000', NULL, NULL, 0, NULL, 0);

SET FOREIGN_KEY_CHECKS=1;
