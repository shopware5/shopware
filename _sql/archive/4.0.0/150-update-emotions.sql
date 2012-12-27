ALTER TABLE s_emotion ADD cell_height INT NOT NULL AFTER categoryID ,
ADD article_height INT NOT NULL AFTER cell_height ,
ADD container_width INT NOT NULL AFTER article_height;

ALTER TABLE s_emotion ADD rows INT NOT NULL AFTER container_width;
ALTER TABLE s_emotion CHANGE gridID cols INT( 11 ) NULL DEFAULT NULL ;

UPDATE s_emotion
	SET rows = 10,
		cols = 4,
		article_height = 2,
		container_width = 998,
		cell_height = 120,
		template = 'Standard';

-- //@UNDO

ALTER TABLE s_emotion
  DROP cell_height,
  DROP article_height,
  DROP rows,
  DROP container_width;
  
ALTER TABLE s_emotion CHANGE cols gridID INT( 11 ) NULL DEFAULT NULL;
