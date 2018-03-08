SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_core_paymentmeans;

INSERT INTO `s_core_paymentmeans` (`name`, `description`, `debit_percent`, `surcharge`, `surchargestring`, `template`, `class`, `table`, `hide`, `additionaldescription`, `position`, `active`, `esdactive`, `embediframe`, `hideprospect`, `action`, `pluginID`, `source`, `mobile_inactive`) VALUES
    ('example1', 'Example 1', 0, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    ('example2', 'Example 2', 1, 0, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    ('example3', 'Example 3', 0, 2, '', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    ('example4', 'Example 4', 0, 0, 'DE:4;AE:-1', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    ('example5', 'Example 5', 0, 0, 'DE:-4', 'example.tpl', '', '', 0, '', 4, 1, 0, '', 0, '', NULL, NULL, 0),
    ('example6', 'Example 6', -5, 0, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    ('example7', 'Example 7', 0, -6, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    ('example8', 'Example 8', -7, -8, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0),
    ('example9', 'Example 9', 9, 10, '', 'example.tpl', '', '', 0, '', 4, 0, 0, '', 0, '', NULL, NULL, 0);

SET FOREIGN_KEY_CHECKS=1;
