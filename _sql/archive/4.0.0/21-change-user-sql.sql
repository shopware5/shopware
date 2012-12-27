-- Related to ticket SW-824 //
DROP TABLE IF EXISTS s_core_acl_groups;
DROP TABLE IF EXISTS s_core_acl_user;
DROP TABLE IF EXISTS s_core_auth_groups;

CREATE TABLE IF NOT EXISTS `s_core_auth_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parentID` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `source` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL,
  `admin` int(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2;

INSERT INTO `s_core_auth_roles` (`id`, `parentID`, `name`, `description`, `source`, `enabled`, `admin`) VALUES
(1, 0, 'local_admins', 'Default group that gains access to all shop functions', 'build-in', 1, 1);

ALTER TABLE `s_core_auth` CHANGE `groupID` `roleID` INT( 11 ) NOT NULL;
ALTER TABLE `s_core_auth` ADD `localeID` INT NOT NULL AFTER `password`;

CREATE TABLE IF NOT EXISTS `s_core_acl_roles` (
  `roleID` int(11) NOT NULL,
  `resourceID` int(11)  NULL,
  `privilegeID` int(11)  NULL,
  PRIMARY KEY (`roleID`,`resourceID`,`privilegeID`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- Insert test data --
DELETE FROM s_core_acl_resources;
INSERT INTO `s_core_acl_resources` (`id`, `name`) VALUES
(1, 'debug_test');

INSERT INTO `s_core_acl_privileges` (`id`, `resourceID`, `name`) VALUES
(1, 1, 'create'),
(2, 1, 'read'),
(3, 1, 'update'),
(4, 1, 'delete');

INSERT INTO `s_core_acl_roles` (`roleID`, `resourceID`, `privilegeID`) VALUES
(1, 1, 1),
(1, 1, 2);

-- //@UNDO
ALTER TABLE `s_core_auth` CHANGE `roleID` `groupID` INT( 11 ) NOT NULL;
ALTER TABLE `s_core_auth` DROP `localeID`;
DROP TABLE s_core_auth_roles;
DROP TABLE s_core_acl_roles;
-- //
