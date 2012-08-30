-- //
UPDATE `s_emarketing_banners` SET `img` = concat('images/banner/',img)  WHERE img NOT LIKE 'images/banner/%' AND img != '' AND img  NOT LIKE  'media/image/%';
-- //@UNDO
UPDATE `s_emarketing_banners` SET `img` = replace(img,'images/banner/','');