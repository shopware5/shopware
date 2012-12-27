-- //

CREATE TABLE `s_emotion_categories` (
`id` INT NOT NULL AUTO_INCREMENT,
`emotion_id` INT NOT NULL ,
`category_id` INT NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = InnoDB;

-- //@UNDO

DROP TABLE IF EXISTS s_emotion_categories;

--