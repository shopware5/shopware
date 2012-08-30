-- //
ALTER TABLE `s_media_album` CHANGE `parentID` `parentID` INT( 11 ) NULL;
ALTER TABLE `s_media` ADD `memory_size` DECIMAL( 14, 2 ) NOT NULL AFTER `extension`;

-- //@UNDO
ALTER TABLE `s_media_album` CHANGE `parentID` `parentID` INT( 11 ) NOT NULL;
ALTER TABLE `s_media` DROP `memory_size` ;
-- //
