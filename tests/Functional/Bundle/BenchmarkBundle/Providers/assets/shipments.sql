SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_premium_dispatch;

INSERT INTO `s_premium_dispatch` (`id`, `name`, `type`, `description`, `comment`, `active`, `position`, `calculation`, `surcharge_calculation`, `tax_calculation`, `shippingfree`, `multishopID`, `customergroupID`, `bind_shippingfree`, `bind_time_from`, `bind_time_to`, `bind_instock`, `bind_laststock`, `bind_weekday_from`, `bind_weekday_to`, `bind_weight_from`, `bind_weight_to`, `bind_price_from`, `bind_price_to`, `bind_sql`, `status_link`, `calculation_sql`) VALUES
    (1, 'Example dispatch 1', 0, 'Example description 1', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (2, 'Example dispatch 2', 1, 'Example description 1', '', 1, 0, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (3, 'Example dispatch 3', 3, 'Example description 3', '', 1, 0, 1, 0, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (4, 'Example dispatch 4', 0, 'Example description 4', '', 1, 2, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL),
    (5, 'Example dispatch 5', 0, 'Example description 5', '', 1, 1, 1, 3, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', NULL);

INSERT INTO `s_premium_shippingcosts` (`from`, `value`, `factor`, `dispatchID`) VALUES
    ('0.000', '15.00', '0.00', 1),
    ('5.000', '1.00', '0.00', 1),
    ('0.000', '13.00', '0.00', 2),
    ('0.000', '14.00', '0.00', 3),
    ('0.000', '10.00', '0.00', 4),
    ('5.000', '5.00', '0.00', 4),
    ('10.000', '2.50', '0.00', 4),
    ('0.000', '15.00', '0.00', 5);

SET FOREIGN_KEY_CHECKS=1;
