ALTER TABLE `s_user` ADD `customer_group_id` INT( 11 ) NOT NULL AFTER `id`  ,
ADD INDEX ( `customer_group_id` );

UPDATE s_user, s_core_customergroups SET customer_group_id = s_core_customergroups.id
WHERE s_core_customergroups.groupkey = s_user.customergroup;


-- //@UNDO
ALTER TABLE s_user DROP COLUMN customer_group_id;