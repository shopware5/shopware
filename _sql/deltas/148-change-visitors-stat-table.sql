ALTER TABLE `s_statistics_visitors` ADD `shopID` INT NOT NULL AFTER `id`;
UPDATE s_statistics_visitors SET shopID = (
SELECT id FROM s_core_multilanguage WHERE `default` = 1
);
-- //@UNDO
ALTER TABLE `s_statistics_visitors` DROP `shopID`;