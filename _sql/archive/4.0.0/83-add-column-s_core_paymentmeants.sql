ALTER TABLE `s_core_paymentmeans` ADD `source` INT NULL ;
-- //@UNDO
ALTER TABLE `s_core_paymentmeans` DROP `source`;
-- //