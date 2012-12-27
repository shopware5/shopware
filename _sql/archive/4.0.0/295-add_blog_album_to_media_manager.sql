-- //

INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-11, 'Blog', NULL, 3);


INSERT INTO `s_media_album_settings` (`id`, `albumID`, `create_thumbnails`, `thumbnail_size`, `icon`) VALUES
(11, -11, 1, '57x57;140x140;285x255;720x600', 'sprite-blue-folder');



-- //@UNDO

DELETE FROM `s_media_album` WHERE `id` = '-11';

DELETE FROM `s_media_album_settings` WHERE `albumID` = '-11';

--