-- Related to SW-824 //
ALTER TABLE  `s_core_auth_roles` CHANGE  `parentID`  `parentID` INT( 11 ) NULL;
ALTER TABLE  `s_core_auth_roles` ADD UNIQUE (
`name`
);
-- Set all acl & auth related tables to innoDB / utf8
ALTER TABLE  `s_core_auth_roles` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `s_core_auth` ENGINE = INNODB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `s_core_acl_privileges` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `s_core_acl_resources` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE  `s_core_acl_roles` ENGINE = INNODB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- Add additional test-users for acl testings
INSERT INTO `s_core_auth_roles` (`id`, `parentID`, `name`, `description`, `source`, `enabled`, `admin`) VALUES
(2, 0, 'Test-Group1', 'Group that has restricted access ', 'test', 1, 0),
(3, 0, 'Test-Group2', 'Group that has restricted access ', 'test', 1, 0),
(4, 3, 'Test-Group3', 'Group that has restricted access ', 'test', 1, 0);

TRUNCATE TABLE s_core_acl_roles;

INSERT INTO `s_core_acl_roles` (`roleID`, `resourceID`, `privilegeID`) VALUES
(1, 0, 0),
(2, 1, 1),
(3, 1, 1),
(3, 1, 2);

ALTER TABLE  `s_core_acl_resources` ADD  `pluginID` INT NULL; -- Support resources & privileges in plugins
ALTER TABLE  `s_core_acl_privileges` ADD INDEX (  `resourceID` );
ALTER TABLE  `s_core_menu` ADD  `resourceID` INT NULL AFTER  `pluginID`; -- Handle relationship between menu items & resources

UPDATE s_core_auth SET roleID = 1; -- Make all users to admins

-- //@UNDO
ALTER TABLE  `s_core_auth_roles` CHANGE  `parentID`  `parentID` INT( 11 ) NOT NULL;
ALTER TABLE `s_core_auth_roles` DROP INDEX `name`;
-- //
