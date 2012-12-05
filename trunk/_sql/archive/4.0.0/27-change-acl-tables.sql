-- Related to SW-824 //
TRUNCATE TABLE s_core_acl_roles;

ALTER TABLE `s_core_acl_roles` DROP PRIMARY KEY;
ALTER TABLE `s_core_acl_roles` CHANGE `resourceID` `resourceID` INT( 11 ) NULL DEFAULT NULL;
ALTER TABLE `s_core_acl_roles` CHANGE `privilegeID` `privilegeID` INT( 11 ) NULL DEFAULT NULL;

ALTER TABLE `s_core_acl_roles` ADD UNIQUE (
`roleID` ,
`resourceID` ,
`privilegeID`
);

ALTER TABLE `s_core_acl_roles` ADD INDEX ( `resourceID` ) ;
ALTER TABLE `s_core_acl_roles` ADD INDEX ( `privilegeID` ) ;


INSERT INTO `s_core_acl_roles` (`roleID`, `resourceID`, `privilegeID`) VALUES
(1, NULL, NULL),
(2, 1, 1),
(3, 1, 1),
(3, 1, 2),
(4, 1, NULL);


-- //@UNDO
TRUNCATE TABLE s_core_acl_roles;

INSERT INTO `s_core_acl_roles` (`roleID`, `resourceID`, `privilegeID`) VALUES
(1, 0, 0),
(2, 1, 1),
(3, 1, 1),
(3, 1, 2);
-- //