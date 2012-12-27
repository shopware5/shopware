-- //

DELETE FROM `s_core_engine_elements` WHERE `domtype` = 'price';
DELETE FROM `s_core_engine_groups` WHERE `group` = 'Preise';
SET @parent = (SELECT `id` FROM `s_core_engine_groups` WHERE `group` = 'Kundengruppen');
INSERT INTO `s_core_engine_elements` (`group`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `availablebyvariants`, `help`, `multilanguage`) VALUES
(@parent, '', 'customer_group', '', 0, 1, 'customerGroups', '', 0, 'Auswahl der aktiven Kundengruppen', 0);

ALTER TABLE `s_core_engine_elements` ADD `store` VARCHAR( 255 ) NULL AFTER `domtype`;
UPDATE `s_core_engine_elements` SET `domvalue` = '',
`store` = 'detail.Unit' WHERE `databasefield` = 'unitId';
UPDATE `s_core_engine_elements` SET `domvalue` = '',
`store` = 'detail.Tax' WHERE `databasefield` = 'taxId';
UPDATE `s_core_engine_elements` SET `domvalue` = '',
`store` = 'detail.Supplier' WHERE `databasefield` = 'supplierId';
UPDATE `s_core_engine_elements` SET `domvalue` = '',
`store` = 'detail.PriceGroup' WHERE `databasefield` = 'priceGroupId';
UPDATE `s_core_engine_elements` SET `domvalue` = '',
`store` = 'detail.FilterGroup' WHERE `databasefield` = 'filterGroupId';

-- //@UNDO

INSERT INTO `s_core_engine_elements` (`id`, `group`, `domvalue`, `domtype`, `domdescription`, `required`, `position`, `databasefield`, `domclass`, `availablebyvariants`, `help`, `multilanguage`) VALUES
(14, 6, '', 'price', '', 1, 0, '', '', 1, '', 0);
INSERT INTO `s_core_engine_groups` (`id`, `group`, `availablebyvariants`, `position`) VALUES
(6, 'Preise', 1, 3);
DELETE FROM `s_core_engine_elements` WHERE `domvalue` = 'customerGroups';
ALTER TABLE `s_core_engine_elements` DROP `store`;

-- //
