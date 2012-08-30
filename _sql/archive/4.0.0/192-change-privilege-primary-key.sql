ALTER TABLE `s_core_acl_privileges` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `id` );

-- //@UNDO

ALTER TABLE `s_core_acl_privileges` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `id` , `resourceID` );

