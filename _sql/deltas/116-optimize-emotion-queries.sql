-- //


ALTER TABLE  `s_blog` ADD INDEX  `emotion_get_blog_entry` (  `display_date` );
ALTER TABLE `s_emotion_element` ADD INDEX  `get_emotion_elements`  (  `emotionID` ,  `start_row` ,  `start_col` );

-- //@UNDO

-- //