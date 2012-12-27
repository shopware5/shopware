-- //

-- Fix template relation
UPDATE `s_core_multilanguage` SET
`template` = REPLACE(`template`, 'templates/', ''),
`doc_template` = REPLACE(`doc_template`, 'templates/', '');

-- Drop unused fields > Use the config
ALTER TABLE `s_core_multilanguage`
  DROP `encoding`,
  DROP `inheritstyles`,
  DROP `text1`,
  DROP `text2`,
  DROP `text3`,
  DROP `text4`,
  DROP `text5`,
  DROP `text6`;

-- Fix table layout
ALTER TABLE `s_core_multilanguage` CHANGE `id` `id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT ,
CHANGE `locale` `locale` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `parentID` `parentID` INT( 11 ) UNSIGNED NOT NULL ,
CHANGE `skipbackend` `skipbackend` INT( 1 ) NOT NULL ,
CHANGE `fallback` `fallback` INT( 11 ) UNSIGNED NULL;

-- Add main shop field
ALTER TABLE `s_core_multilanguage` ADD `mainID` INT( 11 ) UNSIGNED NULL AFTER `id`;

-- //@UNDO

UPDATE `s_core_multilanguage` SET
`template` = CONCAT('templates/', `template`),
`doc_template` = CONCAT('templates/', `doc_template`);

ALTER TABLE `s_core_multilanguage`
ADD `encoding` VARCHAR( 255 ) NOT NULL,
ADD `inheritstyles` INT( 1 ) NOT NULL,
ADD `text1` VARCHAR( 255 ) NOT NULL,
ADD `text2` VARCHAR( 255 ) NOT NULL,
ADD `text3` VARCHAR( 255 ) NOT NULL,
ADD `text4` VARCHAR( 255 ) NOT NULL,
ADD `text5` VARCHAR( 255 ) NOT NULL,
ADD `text6` VARCHAR( 255 ) NOT NULL;

ALTER TABLE `s_core_multilanguage`
  DROP `mainID`;

-- //