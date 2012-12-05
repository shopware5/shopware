-- //

ALTER TABLE  `s_articles_img` ADD  `media_id` INT UNSIGNED NULL DEFAULT NULL ,
ADD INDEX (  `media_id` );

UPDATE s_articles_img , s_media
    SET s_articles_img.media_id = s_media.id
WHERE s_media.name = s_articles_img.img;

-- //@UNDO

ALTER TABLE `s_articles_img`
  DROP `media_id`;

--