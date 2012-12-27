-- //
-- Update field description / types:

UPDATE `s_core_engine_elements` SET `domtype` = 'html' WHERE `domtype`='wysiwyg';
UPDATE `s_core_engine_elements` SET `domtype` = 'weight' WHERE `databasefield` LIKE '%weight%';
UPDATE `s_core_engine_elements` SET `domdescription` = '', `help` = '' WHERE `databasefield`='description_long';

-- Update field names:

UPDATE `s_core_engine_elements` SET `databasefield` = 'supplierId' WHERE `databasefield` LIKE 'supplierId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'shippingTime' WHERE `databasefield` LIKE 'shippingTime';
UPDATE `s_core_engine_elements` SET `databasefield` = 'shippingFree' WHERE `databasefield` LIKE 'shippingFree';
UPDATE `s_core_engine_elements` SET `databasefield` = 'releaseDate' WHERE `databasefield` LIKE 'releaseDate';

UPDATE `s_core_engine_elements` SET `databasefield` = 'descriptionLong' WHERE `databasefield` = 'description_long';
UPDATE `s_core_engine_elements` SET `databasefield` = 'highlight' WHERE `databasefield` = 'topseller';
UPDATE `s_core_engine_elements` SET `databasefield` = 'taxId' WHERE `databasefield` LIKE 'taxId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'minPurchase' WHERE `databasefield` LIKE 'minPurchase';
UPDATE `s_core_engine_elements` SET `databasefield` = 'purchaseSteps' WHERE `databasefield` LIKE 'purchaseSteps';
UPDATE `s_core_engine_elements` SET `databasefield` = 'maxPurchase' WHERE `databasefield` LIKE 'maxPurchase';
UPDATE `s_core_engine_elements` SET `databasefield` = 'unitId' WHERE `databasefield` LIKE 'unitId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'purchaseUnit' WHERE `databasefield` LIKE 'purchaseUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'referenceUnit' WHERE `databasefield` LIKE 'referenceUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'added' WHERE `databasefield` = 'datum';

UPDATE `s_core_engine_elements` SET `databasefield` = 'priceGroupActive' WHERE `databasefield` LIKE 'priceGroupActive';
UPDATE `s_core_engine_elements` SET `databasefield` = 'priceGroupId' WHERE `databasefield` LIKE 'priceGroupId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'pseudoSales' WHERE `databasefield` LIKE 'pseudoSales';
UPDATE `s_core_engine_elements` SET `databasefield` = 'filterGroupId' WHERE `databasefield` LIKE 'filterGroupId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'lastStock' WHERE `databasefield` LIKE 'lastStock';
UPDATE `s_core_engine_elements` SET `databasefield` = 'packUnit' WHERE `databasefield` LIKE 'packUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'changed' WHERE `databasefield` = 'changetime';

UPDATE `s_core_engine_elements` SET `databasefield` = 'number' WHERE `databasefield` LIKE 'ordernumber';
UPDATE `s_core_engine_elements` SET `databasefield` = 'additionalText' WHERE `databasefield` LIKE '%additionalText%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'inStock' WHERE `databasefield` LIKE '%inStock%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'stockMin' WHERE `databasefield` LIKE '%stockMin%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'weight' WHERE `databasefield` LIKE '%weight%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'supplierNumber' WHERE `databasefield` LIKE '%supplierNumber%';

UPDATE `s_core_engine_elements` SET `domtype` = 'select' WHERE `databasefield` LIKE 'supplierId';
UPDATE `s_core_engine_elements` SET `domtype` = 'number' WHERE `databasefield` IN (
  'inStock', 'stockMin', 'minPurchase', 'purchaseSteps', 'maxPurchase', 'purchaseUnit', 'referenceUnit', 'pseudoSales'
);

-- Add main detail id field

ALTER TABLE `s_articles` ADD `main_detailID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
UPDATE `s_articles` a, `s_articles_details` d SET `main_detailID` = d.id WHERE a.id = d.articleID AND d.kind = 1;
ALTER TABLE `s_articles` CHANGE `main_detailID` `main_detailID` INT( 11 ) UNSIGNED NOT NULL;
ALTER TABLE `s_articles` ADD UNIQUE (
  `main_detailID`
);
ALTER TABLE `s_articles` CHANGE `main_detailID` `main_detail_id` INT( 11 ) UNSIGNED NOT NULL;

-- Update detail field types

ALTER TABLE `s_articles_details` CHANGE `suppliernumber` `suppliernumber` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `additionaltext` `additionaltext` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL ,
CHANGE `weight` `weight` DECIMAL( 10, 3 ) NULL DEFAULT NULL;
UPDATE `s_articles_details` SET `weight` = NULL WHERE `weight`=0;
ALTER TABLE `s_articles_details` CHANGE `stockmin` `stockmin` INT( 11 ) UNSIGNED NULL;
UPDATE `s_articles_details` SET `stockmin` = NULL WHERE `stockmin`=0;
UPDATE `s_articles_details` SET `additionaltext` = NULL WHERE `additionaltext`='';

ALTER TABLE `s_articles_details` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `articleID` `articleID` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `active` `active` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0',
CHANGE `stockmin` `stockmin` INT( 11 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `weight` `weight` DECIMAL( 10, 3 ) UNSIGNED NULL DEFAULT NULL ,
CHANGE `position` `position` INT( 11 ) UNSIGNED NOT NULL;

-- Delete unused fields / elements:

DELETE FROM `s_core_engine_elements` WHERE `group` = 0;
DROP TABLE `s_core_variants`, `s_core_engine_fieldsets`, `s_core_engine_values`;

ALTER TABLE `s_core_engine_elements`
--  DROP `domname`,
  DROP `version`;

ALTER TABLE `s_articles` DROP `variantID`, DROP `free`;
ALTER TABLE `s_articles_details`
  DROP `esd`;

-- //@UNDO

UPDATE `s_core_engine_elements` SET `domtype` = 'wysiwyg' WHERE `domtype`='html';
UPDATE `s_core_engine_elements` SET `domtype` = 'text' WHERE `databasefield` LIKE '%weight%';

UPDATE `s_core_engine_elements` SET `databasefield` = 'supplierID' WHERE `databasefield` LIKE 'supplierId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'shippingtime' WHERE `databasefield` LIKE 'shippingTime';
UPDATE `s_core_engine_elements` SET `databasefield` = 'shippingfree' WHERE `databasefield` LIKE 'shippingFree';
UPDATE `s_core_engine_elements` SET `databasefield` = 'releasedate' WHERE `databasefield` LIKE 'releaseDate';

UPDATE `s_core_engine_elements` SET `databasefield` = 'description_long' WHERE `databasefield` = 'descriptionLong';
UPDATE `s_core_engine_elements` SET `databasefield` = 'topseller' WHERE `databasefield` = 'highlight';
UPDATE `s_core_engine_elements` SET `databasefield` = 'taxID' WHERE `databasefield` LIKE 'taxId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'minpurchase' WHERE `databasefield` LIKE 'minPurchase';
UPDATE `s_core_engine_elements` SET `databasefield` = 'purchasesteps' WHERE `databasefield` LIKE 'purchaseSteps';
UPDATE `s_core_engine_elements` SET `databasefield` = 'maxpurchase' WHERE `databasefield` LIKE 'maxPurchase';
UPDATE `s_core_engine_elements` SET `databasefield` = 'unitID' WHERE `databasefield` LIKE 'unitId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'purchaseunit' WHERE `databasefield` LIKE 'purchaseUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'referenceunit' WHERE `databasefield` LIKE 'referenceUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'datum' WHERE `databasefield` = 'added';

UPDATE `s_core_engine_elements` SET `databasefield` = 'pricegroupActive' WHERE `databasefield` LIKE 'priceGroupActive';
UPDATE `s_core_engine_elements` SET `databasefield` = 'pricegroupID' WHERE `databasefield` LIKE 'priceGroupId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'pseudosales' WHERE `databasefield` LIKE 'pseudoSales';
UPDATE `s_core_engine_elements` SET `databasefield` = 'filtergroupID' WHERE `databasefield` LIKE 'filterGroupId';
UPDATE `s_core_engine_elements` SET `databasefield` = 'laststock' WHERE `databasefield` LIKE 'lastStock';
UPDATE `s_core_engine_elements` SET `databasefield` = 'packunit' WHERE `databasefield` LIKE 'packUnit';
UPDATE `s_core_engine_elements` SET `databasefield` = 'changed' WHERE `databasefield` = 'changetime';

UPDATE `s_core_engine_elements` SET `databasefield` = 'ordernumber' WHERE `databasefield` LIKE 'number';
UPDATE `s_core_engine_elements` SET `databasefield` = 'additionaltext' WHERE `databasefield` LIKE '%additionalText%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'instock' WHERE `databasefield` LIKE '%inStock%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'stockmin' WHERE `databasefield` LIKE '%stockMin%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'weight' WHERE `databasefield` LIKE '%weight%';
UPDATE `s_core_engine_elements` SET `databasefield` = 'suppliernumber' WHERE `databasefield` LIKE '%supplierNumber%';

UPDATE `s_core_engine_elements` SET `domtype` = 'text' WHERE `databasefield` LIKE 'supplierId';
UPDATE `s_core_engine_elements` SET `domtype` = 'text' WHERE `domtype` = 'number';

ALTER TABLE `s_articles` DROP `main_detail_id`;

ALTER TABLE `s_articles` ADD `free` INT( 1 ) NOT NULL ,
ADD `variantID` INT( 11 ) NOT NULL;

ALTER TABLE `s_articles_details` ADD `esd` INT( 1 ) NOT NULL;

-- //
