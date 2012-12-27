ALTER TABLE s_library_component_field ADD value_type VARCHAR( 255 ) NOT NULL AFTER x_type;

ALTER TABLE s_emotion CHANGE valid_from valid_from DATETIME NULL DEFAULT NULL ,
CHANGE valid_to valid_to DATETIME NULL DEFAULT NULL ,
CHANGE create_date create_date DATETIME NULL DEFAULT NULL ,
CHANGE modified modified DATETIME NULL DEFAULT NULL;

UPDATE s_emotion 
	SET valid_from = NULL,
		valid_to = NULL,
		create_date = NULL,
		modified = NULL;

-- //@UNDO
		
ALTER TABLE s_emotion DROP value_type;

ALTER TABLE s_emotion CHANGE valid_from valid_from DATETIME NOT NULL ,
CHANGE valid_to valid_to DATETIME NOT NULL ,
CHANGE create_date create_date DATETIME NOT NULL ,
CHANGE modified modified DATETIME NOT NULL;

UPDATE s_emotion
	SET valid_from = '00-00-00 00:00:00',
		valid_to = '00-00-00 00:00:00',
		create_date = '00-00-00 00:00:00',
		modified = '00-00-00 00:00:00';	