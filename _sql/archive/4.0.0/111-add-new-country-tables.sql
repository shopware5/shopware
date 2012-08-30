-- //
RENAME TABLE s_core_countries TO s_core_countries_backup ;

CREATE  TABLE IF NOT EXISTS s_core_country_areas (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NULL ,
  active INT NULL ,
  PRIMARY KEY (id) ,
  UNIQUE INDEX name_UNIQUE (name ASC) )
ENGINE = InnoDB;

INSERT INTO s_core_country_areas (name,active)
(SELECT DISTINCT countryarea AS name, 1 as active FROM s_core_countries_backup);

CREATE TABLE IF NOT EXISTS s_core_countries (
  id INT NOT NULL AUTO_INCREMENT,
  countryname VARCHAR(255) NULL ,
  countryiso VARCHAR(255) NULL ,
  areaID INT NULL ,
  countryen VARCHAR (255) NULL,
  position INT NULL ,
  notice TEXT NULL ,
  shippingfree INT NULL ,
  taxfree INT NULL ,
  taxfree_ustid INT NULL ,
  taxfree_ustid_checked INT NULL ,
  active INT NULL ,
  iso3 VARCHAR(45) NULL ,
  PRIMARY KEY (id) ,
  KEY  areaID (areaID)
) ENGINE = InnoDB;

INSERT INTO s_core_countries (
id, countryname,countryiso,areaID,countryen,position,notice,shippingfree,taxfree,taxfree_ustid,
taxfree_ustid_checked,active,iso3
) (
SELECT id, countryname,countryiso,(SELECT s_core_country_areas.id FROM s_core_country_areas WHERE name = countryarea) AS areaID,
 countryen, position, notice, shippingfree, taxfree,taxfree_ustid, taxfree_ustid_checked, active, iso3 FROM s_core_countries_backup
);

DROP TABLE s_core_countries_backup;

CREATE  TABLE IF NOT EXISTS s_core_tax_groups (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(255) NULL ,
  PRIMARY KEY (id) ,
  UNIQUE INDEX name_UNIQUE (name ASC) )
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS s_core_country_states (
  id INT NOT NULL AUTO_INCREMENT,
  countryID INT NULL ,
  name VARCHAR(255) NULL ,
  position INT NULL ,
  active INT NULL ,
  PRIMARY KEY (id) ,
  KEY  countryID (countryID)
)
ENGINE = InnoDB;

CREATE  TABLE IF NOT EXISTS s_core_tax_rules (
  id INT NOT NULL AUTO_INCREMENT,
  areaID INT NULL ,
  countryID INT NULL ,
  stateID INT NULL ,
  groupID INT NULL ,
  tax FLOAT NULL ,
  name VARCHAR(255) NULL ,
  active INT NULL ,
  PRIMARY KEY (id) ,
  KEY  groupID ( groupID),
  KEY  countryID (countryID),
  KEY  stateID (stateID),
  KEY  areaID ( areaID)
)
ENGINE = InnoDB;

-- //@UNDO

--