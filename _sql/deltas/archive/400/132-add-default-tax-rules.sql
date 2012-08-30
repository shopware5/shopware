TRUNCATE TABLE s_core_tax_groups;
TRUNCATE TABLE s_core_tax_rules;

INSERT INTO `s_core_tax_groups` (`id`, `name`) VALUES
(1, 'Sample Group High-Tax'),
(2, 'Sample Group Low-Tax');

INSERT INTO `s_core_tax_rules` (`id`, `areaID`, `countryID`, `stateID`, `groupID`, `tax`, `name`, `active`) VALUES
(1, 0, 0, 0, 1, 19, 'Default rule', 1),
(2, 0, 0, 0, 2, 7, 'Default rule', 1),
(4, 3, 19, 0, 1, 20, 'Österreich', 1),
(5, 3, 19, 0, 2, 10, 'Österreich', 1);
-- //@UNDO
TRUNCATE TABLE s_core_tax_groups;