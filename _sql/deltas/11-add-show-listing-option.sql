-- //

ALTER TABLE `s_emotion` ADD `show_listing` INT( 1 ) NOT NULL AFTER `userID`;

-- //@UNDO

ALTER TABLE `s_emotion` DROP `show_listing`;

-- //