ALTER TABLE s_emotion_element_value ADD emotionID INT NOT NULL AFTER id ;
-- //@UNDO
ALTER TABLE s_emotion_element_value DROP emotionID;
