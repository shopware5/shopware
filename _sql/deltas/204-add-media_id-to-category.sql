-- //

ALTER TABLE `s_categories` ADD `mediaID` INT( 1 ) NOT NULL ;

-- //@UNDO

ALTER TABLE `s_categories` DROP `mediaID`;

-- //