-- //
CREATE TABLE IF NOT EXISTS `s_plugin_widgets_notes` (
        `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `userID` INT NOT NULL ,
        `notes` TEXT NOT NULL
        ) ENGINE = MYISAM ;
-- //@UNDO
DROP TABLE s_plugin_widgets_notes;
-- //