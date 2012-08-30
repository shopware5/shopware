-- //

ALTER TABLE `s_core_engine_elements` CHANGE `group` `groupID` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `domvalue` `default` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `domtype` `type` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `domdescription` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `required` `required` INT( 1 ) NOT NULL DEFAULT '0',
CHANGE `databasefield` `name` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `domclass` `layout` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `availablebyvariants` `variantable` INT( 1 ) UNSIGNED NOT NULL ,
CHANGE `help` `help` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `multilanguage` `translatable` INT( 1 ) UNSIGNED NOT NULL;

UPDATE `s_core_engine_elements`
SET `default` = NULL, `label` = 'Artikelbezeichnung', `layout` = NULL, `help` = NULL
WHERE `name` = 'name';
UPDATE `s_core_engine_elements`
SET `default` = NULL, `layout` = NULL, `help` = NULL
WHERE `name` = 'supplierId';
UPDATE `s_core_engine_elements`
SET `default` = NULL, `layout` = NULL, `help` = NULL
WHERE `name` = 'number';
UPDATE `s_core_engine_elements`
SET `layout` = NULL
WHERE `name` = 'active';
UPDATE `s_core_engine_elements`
SET `default` = NULL, `label` = 'Preisgruppe aktiv', `layout` = NULL
WHERE `name` = 'priceGroupActive';
UPDATE `s_core_engine_elements`
SET `default` = NULL, `layout` = NULL, `help` = NULL
WHERE `name` = 'priceGroupId';
UPDATE `s_core_engine_elements` SET `default` = NULL
WHERE `name` = 'filterGroupId';
UPDATE `s_core_engine_elements`
SET `default` = NULL, `layout` = NULL, `help` = NULL
WHERE `name` = 'template';

INSERT INTO `s_core_engine_elements` (`groupID`, `default`, `type`, `store`, `label`, `required`, `position`, `name`, `layout`, `variantable`, `help`, `translatable`) VALUES
(10, NULL, 'price', NULL, NULL, 1, 2, 'price', NULL, 0, NULL, 0);

ALTER TABLE `s_core_engine_groups`
CHANGE `group` `label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `availablebyvariants` `variantable` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
ADD `name` VARCHAR( 255 ) NOT NULL AFTER `id`;

UPDATE `s_core_engine_groups` SET `name` = 'basic' WHERE `label` = 'Stammdaten';
UPDATE `s_core_engine_groups` SET `name` = 'description' WHERE `label` = 'Beschreibung';
UPDATE `s_core_engine_groups` SET `name` = 'advanced' WHERE `label` = 'Einstellungen';
UPDATE `s_core_engine_groups` SET `name` = 'additional' WHERE `label` = 'Zusatzfelder';
UPDATE `s_core_engine_groups` SET `name` = 'reference_price' WHERE `label` = 'Grundpreisberechnung';
UPDATE `s_core_engine_groups` SET `name` = 'price' WHERE `label` = 'Kundengruppen';

DELETE FROM `s_core_engine_groups` WHERE `label` = 'Hauptartikel-Daten';

UPDATE `s_core_engine_elements` SET `groupID` = '2' WHERE `name` IN ('keywords', 'description');
UPDATE `s_core_engine_elements` SET `groupID` = '3' WHERE `name` IN ('supplierNumber', 'additionalText');

-- Add group layout

ALTER TABLE `s_core_engine_groups` ADD `layout` VARCHAR( 255 ) NULL AFTER `label`;
UPDATE `s_core_engine_groups` SET `layout` = 'column' WHERE `name` = 'basic';
UPDATE `s_core_engine_groups` SET `layout` = 'column' WHERE `name` = 'advanced';

INSERT INTO `s_core_engine_groups` (`name`, `label`, `layout`, `variantable`, `position`) VALUES
('property', 'Eigenschaften', NULL, 0, 5);


INSERT INTO `s_core_engine_elements` (`groupID`, `default`, `type`, `store`, `label`, `required`, `position`, `name`, `layout`, `variantable`, `help`, `translatable`) VALUES
((SELECT `id` FROM `s_core_engine_groups` WHERE `name` LIKE 'property'), NULL, 'property', NULL, NULL, 1, 2, 'property', NULL, 0, NULL, 0);

UPDATE `s_core_engine_elements`
SET `groupID` = (SELECT `id` FROM `s_core_engine_groups` WHERE `name` LIKE 'property')
WHERE `name` = 'filterGroupId';

UPDATE `s_core_engine_elements` SET `store` = 'detail.Template' WHERE `name` = 'template';

UPDATE `s_core_engine_groups` SET `position` = '5' WHERE `name` = 'advanced';
UPDATE `s_core_engine_groups` SET `label` = 'Preise und Kundengruppen', `position` = '3' WHERE `name` = 'price';
UPDATE `s_core_engine_groups` SET `position` = '6' WHERE `name` = 'property';
UPDATE `s_core_engine_groups` SET `position` = '7' WHERE `name` = 'additional';
UPDATE `s_core_engine_groups` SET `position` = '4' WHERE `name` = 'reference_price';

-- //@UNDO

ALTER TABLE `s_core_engine_elements` CHANGE `groupID` `group`  INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `default` `domvalue` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `type` `domtype` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `label` `domdescription` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `name` `databasefield` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
CHANGE `layout` `domclass` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `variantable` `availablebyvariants` INT( 1 ) UNSIGNED NOT NULL ,
CHANGE `translatable` `multilanguage` INT( 1 ) UNSIGNED NOT NULL;

DELETE FROM `s_core_engine_elements` WHERE `domtype` = 'price';

ALTER TABLE `s_core_engine_groups`
CHANGE `label` `group` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL ,
CHANGE `variantable` `availablebyvariants` INT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
DROP `name`;

INSERT INTO `s_core_engine_groups` (`id`, `group`, `availablebyvariants`, `position`) VALUES
(5, 'Hauptartikel-Daten', 1, 5);

UPDATE `s_core_engine_elements` SET `group` = '5' WHERE `databasefield` IN ('keywords', 'description');
UPDATE `s_core_engine_elements` SET `group` = '5' WHERE `databasefield` IN ('supplierNumber', 'additionalText');

ALTER TABLE `s_core_engine_groups` DROP `layout`;
DELETE FROM `s_core_engine_groups` WHERE `group` = 'Eigenschaften';
DELETE FROM `s_core_engine_elements` WHERE `domtype` = 'property';
UPDATE `s_core_engine_elements` SET `group` = 1 WHERE `databasefield` = 'filterGroupId';

--