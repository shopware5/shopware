SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_media_album_settings;

INSERT INTO `s_media_album_settings` (`albumID`, `create_thumbnails`, `thumbnail_size`, `icon`, `thumbnail_high_dpi`, `thumbnail_quality`, `thumbnail_high_dpi_quality`) VALUES
(1, 0, '100x100;200x200;400x400', 'sprite-blue-folder', 0, 90, 60),
(2, 0, '200x200', 'sprite-blue-folder', 0, 90, 60),
(3, 0, '300x300', 'sprite-blue-folder', 0, 90, 60),
(4, 0, '400x400', 'sprite-blue-folder', 0, 90, 60),
(5, 0, '500x500', 'sprite-blue-folder', 0, 90, 60),
(6, 0, '600x600', 'sprite-blue-folder', 0, 90, 60),
(7, 0, '1280x720', 'sprite-blue-folder', 0, 90, 60),
(8, 0, '700x700;200x200;300x300', 'sprite-blue-folder', 0, 90, 60);

SET FOREIGN_KEY_CHECKS=1;
