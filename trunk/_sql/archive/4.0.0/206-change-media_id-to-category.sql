-- //

ALTER TABLE `s_categories` CHANGE `mediaID` `mediaID` INT( 11 ) UNSIGNED NULL DEFAULT NULL;

-- //@UNDO

ALTER TABLE `s_categories` CHANGE `mediaID` `mediaID` INT( 11 ) NULL;

-- //