SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_core_paymentmeans;
DELETE FROM s_core_paymentmeans_subshops;

INSERT INTO `s_core_paymentmeans` (`id`, `name`, `description`, `debit_percent`, `surcharge`, `surchargestring`, `template`, `class`, `table`, `hide`, `additionaldescription`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`, `mobile_inactive`) VALUES
    (1, 'example1', 'Example 1', 0, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (2, 'example2', 'Example 2', 1, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (3, 'example3', 'Example 3', 0, 2, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (4, 'example4', 'Example 4', 0, 0, 'DE:4;AE:-1', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (5, 'example5', 'Example 5', 0, 0, 'DE:-4', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    (6, 'example6', 'Example 6', -5, 0, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (7, 'example7', 'Example 7', 0, -6, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (8, 'example8', 'Example 8', -7, -8, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (9, 'example9', 'Example 9', 9, 10, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (10, 'example10', 'Example 10', 9, 10, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    (11, 'example11', 'Example 11', 9, 10, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0);

INSERT INTO `s_core_paymentmeans_subshops` (`paymentID`, `subshopID`) VALUES
    (4, 1),
    (10, 2),
    (11, 2);

SET FOREIGN_KEY_CHECKS=1;
