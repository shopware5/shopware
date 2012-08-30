ALTER TABLE `s_core_acl_roles` ADD `id` INT NOT NULL AUTO_INCREMENT FIRST ,
ADD PRIMARY KEY ( `id` );

-- //@UNDO

ALTER TABLE `s_core_acl_roles` DROP `id`;