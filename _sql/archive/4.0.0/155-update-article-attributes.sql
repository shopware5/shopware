ALTER TABLE s_articles_attributes CHANGE attr17 attr17 DATE NULL DEFAULT NULL;
ALTER TABLE s_articles_attributes CHANGE articleID articleID INT( 11 ) NULL DEFAULT NULL ,
CHANGE articledetailsID articledetailsID INT( 11 ) NULL DEFAULT NULL;
UPDATE s_media_album_settings SET create_thumbnails = 1 WHERE albumID = -1;
-- //@UNDO
ALTER TABLE s_articles_attributes CHANGE attr17 attr17 DATE NOT NULL DEFAULT '0000-00-00';
UPDATE s_media_album_settings SET create_thumbnails = 0 WHERE albumID = -1;