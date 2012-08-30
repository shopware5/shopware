-- //

ALTER TABLE `s_core_shops` ADD `active` INT( 1 ) NOT NULL;
UPDATE `s_core_shops` SET `active` = '1' WHERE `id`!=2;

-- //@UNDO

ALTER TABLE `s_core_shops` DROP `active`;

-- //