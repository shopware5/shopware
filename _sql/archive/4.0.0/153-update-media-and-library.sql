UPDATE s_media_album_settings SET thumbnail_size = '30x30;57x57;105x105;140x140;285x255;720x600' WHERE albumID = -1;
UPDATE s_library_component SET x_type = 'emotion-components-banner' WHERE s_library_component.id =3;
-- //@UNDO
UPDATE s_media_album_settings SET thumbnail_size = '' WHERE albumID = -1;
UPDATE s_library_component SET x_type = '' WHERE s_library_component.id =3;