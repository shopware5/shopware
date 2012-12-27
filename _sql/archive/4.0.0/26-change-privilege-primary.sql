-- Related to SW-824 //
ALTER TABLE `s_core_acl_privileges` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `id` , `resourceID` );

UPDATE `s_core_auth_roles` SET parentID = NULL WHERE parentID = 0;

-- //@UNDO
ALTER TABLE `s_core_acl_privileges` DROP PRIMARY KEY ,
ADD PRIMARY KEY ( `id` );
-- //
