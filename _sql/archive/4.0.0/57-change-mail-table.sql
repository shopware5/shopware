--  //
ALTER TABLE `s_core_config_mails` ADD `stateId` INT NULL DEFAULT NULL AFTER `id`;
ALTER TABLE `s_core_config_mails` ADD UNIQUE (`stateId`);
ALTER TABLE `s_core_config_mails` ADD FOREIGN KEY (`stateId`) REFERENCES `s_core_states` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;
UPDATE `s_core_config_mails` set stateID = SUBSTRING(`name` , 16) WHERE name LIKE "sORDERSTATEMAIL%";

ALTER TABLE `s_core_config_mails` ADD `mailtype` INT NOT NULL DEFAULT '1';
UPDATE `s_core_config_mails` set mailtype = 3 WHERE stateId IS NOT NULL;
UPDATE `s_core_config_mails` set mailtype = 2 WHERE name LIKE "s%" AND stateId IS NULL;

CREATE TABLE IF NOT EXISTS `s_core_config_mails_attachments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mailID` int(11) NOT NULL,
  `mediaID` int(11) NOT NULL,
  `shopID` int(11) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `mailID` (`mailID`,`mediaID`,`shopID`),
  KEY `mediaID` (`mediaID`),
  KEY `shopID` (`shopID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


-- //@UNDO
ALTER TABLE `s_core_config_mails` DROP `stateId`;
ALTER TABLE `s_core_config_mails` DROP `mailtype`;

DROP TABLE  `s_core_config_mails_attachments`;
-- //