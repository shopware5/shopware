-- //

INSERT IGNORE INTO  `s_media_album_settings` (
    `id` ,
    `albumID` ,
    `create_thumbnails` ,
    `thumbnail_size` ,
    `icon`
)
VALUES (
    NULL ,  '-12',  '0',  '',  'sprite-blue-folder'
);


-- //@UNDO

DELETE FROM s_media_album_settings WHERE albumID = -12;

-- //
