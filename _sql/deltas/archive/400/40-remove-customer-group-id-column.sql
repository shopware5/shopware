--  //
ALTER TABLE `s_user` DROP `customer_group_id`;
-- //@UNDO
ALTER TABLE `s_user` ADD `customer_group_id` INT NOT NULL;
-- //