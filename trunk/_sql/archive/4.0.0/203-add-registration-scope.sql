-- //
ALTER TABLE `s_core_multilanguage` ADD `scoped_registration` INT( 1 ) NULL AFTER `switchLanguages`;

-- //@UNDO

ALTER TABLE `s_core_multilanguage` DROP `scoped_registration`;

-- //