-- //

ALTER TABLE `s_core_templates` ADD `version` INT( 11 ) UNSIGNED NOT NULL AFTER `emotion`;
UPDATE `s_core_templates` SET version = IF(template LIKE 'emotion_%', 2, 1);

-- //@UNDO

ALTER TABLE `s_core_templates` DROP `version`;

-- //