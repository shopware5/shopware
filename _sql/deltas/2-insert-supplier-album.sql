-- //

INSERT INTO `s_media_album` (`id`, `name`, `parentID`, `position`) VALUES
(-12, 'Hersteller', NULL, 12);

-- //@UNDO

DELETE FROM s_media_album WHERE id = -12;

-- //
