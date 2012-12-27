-- //

UPDATE `s_articles_attributes` SET attr17 = NULL WHERE attr17 = '0000-00-00';

-- //@UNDO

UPDATE `s_articles_attributes` SET attr17 = '0000-00-00' WHERE attr17 IS NULL;

--