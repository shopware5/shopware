-- //

ALTER TABLE  `s_emotion_attributes` DROP FOREIGN KEY  `s_emotion_attributes_ibfk_1` ;
ALTER TABLE  `s_emotion_attributes` ADD FOREIGN KEY (  `emotionID` ) REFERENCES  `s_emotion` (
`id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

-- //@UNDO

-- //



