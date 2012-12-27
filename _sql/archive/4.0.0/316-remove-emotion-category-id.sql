-- //

ALTER TABLE `s_emotion` DROP `categoryID`;

-- //@UNDO

ALTER TABLE  `s_emotion` ADD  `categoryID` INT NOT NULL;


--