-- //
INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-10, 'Unsortiert', NULL, 7),
(-9, 'Sonstiges', -6, 3),
(-8, 'Musik', -6, 2),
(-7, 'Video', -6, 1),
(-6, 'Dateien', NULL, 6),
(-5, 'Newsletter', NULL, 4),
(-4, 'Aktionen', NULL, 5),
(-3, 'Einkaufswelten', NULL, 3),
(-2, 'Banner', NULL, 1),
(-1, 'Artikel', NULL, 2);

INSERT INTO `s_media_album_settings` (`id`, `albumID`, `create_thumbnails`, `thumbnail_size`, `icon`) VALUES
(1, -10, 0, '', 'folder'),
(2, -9, 0, '', 'folder'),
(3, -8, 0, '', 'folder'),
(4, -7, 0, '', 'folder'),
(5, -6, 0, '', 'folder'),
(6, -5, 0, '', 'folder'),
(7, -4, 0, '', 'folder'),
(8, -3, 0, '', 'folder'),
(9, -2, 0, '', 'folder'),
(10, -1, 0, '', 'folder');

UPDATE s_media_album SET parentID = NULL WHERE parentID = 0;

-- //@UNDO
DELETE FROM s_media_album;
DELETE FROM s_media_album_settings;
-- //
