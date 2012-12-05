-- //

DROP TABLE IF EXISTS s_emotion_attributes_new;
DROP TABLE IF EXISTS s_emotion_attributes_backup;

-- Copy structure and data to new table, this does _not_ copy the foreign keys, that's exacly what we want
CREATE TABLE s_emotion_attributes_new LIKE s_emotion_attributes;
INSERT INTO s_emotion_attributes_new SELECT * FROM s_emotion_attributes;

RENAME TABLE s_emotion_attributes TO s_emotion_attributes_backup, s_emotion_attributes_new TO s_emotion_attributes;

-- Add missing foreign key
ALTER TABLE `s_emotion_attributes` ADD FOREIGN KEY ( `emotionID` ) REFERENCES `s_emotion` (
        `id`
) ON DELETE CASCADE ON UPDATE NO ACTION ;

DROP TABLE s_emotion_attributes_backup;

-- //@UNDO

-- //



