-- //
-- Delete fields that are not longer needed
ALTER TABLE `s_core_auth` DROP `sidebar`;       -- int 1
ALTER TABLE `s_core_auth` DROP `window_height`; -- int 11
ALTER TABLE `s_core_auth` DROP `window_width`;  -- int 11
ALTER TABLE `s_core_auth` DROP `window_size`;   -- text
ALTER TABLE `s_core_auth` DROP `rights`;        -- text

-- Add new fields
ALTER TABLE `s_core_auth` ADD `groupID` INT (11) NOT NULL AFTER `id`;

-- Add new table to hold groups
CREATE TABLE IF NOT EXISTS `s_core_auth_groups` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`name` VARCHAR( 255 ) NOT NULL ,
`description` TEXT NOT NULL ,
`source` VARCHAR( 255 ) NOT NULL ,
`enabled` INT( 1 ) NOT NULL,
`admin` INT ( 1 ) NOT NULL
) ENGINE = InnoDB;

-- Insert pre-build groups
INSERT INTO `s_core_auth_groups` (`id` ,`name` ,`description` ,`source` ,`enabled`, `admin`)
VALUES (
NULL , 'Administrators', 'Default group that gains access to all shop functions', 'build-in', '1', '1'
);

-- Set all users to admins temporary
UPDATE s_core_auth SET groupID = 1;



-- //@UNDO

ALTER TABLE `s_core_auth` ADD `sidebar` INT ( 1 ) NOT NULL AFTER `active`;
ALTER TABLE `s_core_auth` ADD `window_height` INT ( 11 ) NOT NULL AFTER `sidebar`;
ALTER TABLE `s_core_auth` ADD `window_width` INT ( 11 ) NOT NULL AFTER `window_height`;
ALTER TABLE `s_core_auth` ADD `window_size` TEXT NOT NULL AFTER `window_width`;
ALTER TABLE `s_core_auth` ADD `rights` TEXT NOT NULL AFTER `admin`;

DROP TABLE IF EXISTS s_core_auth_groups;

ALTER TABLE `s_core_auth` DROP `groupID`;
-- //