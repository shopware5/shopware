ALTER TABLE `s_order` CHANGE `cleareddate` `cleareddate` DATETIME NULL DEFAULT NULL;
UPDATE s_order SET cleareddate = NULL WHERE cleareddate = '0000-00-00 00:00:00';
-- //@UNDO
ALTER TABLE `s_order` CHANGE `cleareddate` `cleareddate` DATETIME NOT NULL;
UPDATE s_order SET cleareddate = '0000-00-00 00:00:00' WHERE cleareddate IS NULL;