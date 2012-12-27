DROP TABLE s_core_acl_roles;

CREATE TABLE IF NOT EXISTS s_core_acl_roles (
  id int(11) NOT NULL AUTO_INCREMENT,
  roleID int(11) NOT NULL,
  resourceID int(11) DEFAULT NULL,
  privilegeID int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY roleID (roleID,resourceID,privilegeID),
  KEY resourceID (resourceID),
  KEY privilegeID (privilegeID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

INSERT INTO s_core_acl_roles (roleID, resourceID, privilegeID) VALUES
(1, NULL, NULL),
(2, 1, 1),
(3, 1, 1),
(3, 1, 2),
(4, 1, NULL);

-- //@UNDO

DROP TABLE s_core_acl_roles;

CREATE TABLE IF NOT EXISTS s_core_acl_roles (
  roleID int(11) NOT NULL,
  resourceID int(11) DEFAULT NULL,
  privilegeID int(11) DEFAULT NULL,
  UNIQUE KEY roleID (roleID,resourceID,privilegeID),
  KEY resourceID (resourceID),
  KEY privilegeID (privilegeID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO s_core_acl_roles (roleID, resourceID, privilegeID) VALUES
(1, NULL, NULL),
(2, 1, 1),
(3, 1, 1),
(3, 1, 2),
(4, 1, NULL);
