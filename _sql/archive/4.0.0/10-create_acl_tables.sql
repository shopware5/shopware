-- //
CREATE TABLE IF NOT EXISTS `s_core_acl_user` (
  `resourceID` int(11) NOT NULL,
  `userID` int(11) NOT NULL,
  `privilegeID` int(11) NOT NULL,
  UNIQUE KEY `resourceID` (`resourceID`,`userID`,`privilegeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `s_core_acl_groups` (
  `resourceID` int(11) NOT NULL,
  `groupID` int(11) NOT NULL,
  `privilegeID` int(11) NOT NULL,
  UNIQUE KEY `resourceID` (`resourceID`,`groupID`,`privilegeID`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE `s_core_acl_resources` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 255 ) NOT NULL
) ENGINE = InnoDB;

CREATE TABLE `s_core_acl_privileges` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`resourceID` INT NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL
) ENGINE = InnoDB;
-- //@UNDO
DROP TABLE s_core_acl_user;
DROP TABLE s_core_acl_groups;
DROP TABLE s_core_acl_resources;
DROP TABLE s_core_acl_privileges;
-- //
