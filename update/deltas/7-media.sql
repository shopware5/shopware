UPDATE s_articles_supplier
SET img = CONCAT('media/image/', img)
WHERE img NOT LIKE '%/%' AND img != '';

UPDATE s_articles_downloads
SET filename = CONCAT('media/unknown/', filename)
WHERE filename NOT LIKE '%/%';

UPDATE s_emarketing_banners
SET img = CONCAT('media/image/', img)
WHERE img NOT LIKE '%/%' AND img != '';

UPDATE s_emarketing_promotions
SET img = CONCAT('media/image/', img)
WHERE img NOT LIKE '%/%' AND img != '';

UPDATE s_emarketing_promotion_main
SET image = CONCAT('media/image/', image)
WHERE image NOT LIKE '%/%' AND image != '';

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -1 as albumID, img as name, i.description,
  CONCAT('media/image/', img, '.', i.extension) as `path`,
  'IMAGE' as `type`, i.extension,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_img i
LEFT JOIN s_media m
ON m.name = img
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -6 as albumID, SUBSTRING_INDEX(REPLACE(filename, 'media/unknown/', ''), '.', 1) as name, d.description,
  filename as `path`,
  'UNKNOWN' as `type`, SUBSTRING_INDEX(filename, '.', -1) as `extension`,
  d.size as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_downloads d
LEFT JOIN s_media m
ON m.path = filename
WHERE filename LIKE 'media/unknown/%'
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -12 as albumID,  SUBSTRING_INDEX(REPLACE(img, 'media/image/', ''), '.', 1) as name, s.name as description,
  img as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_supplier s
LEFT JOIN s_media m
ON m.path = img
WHERE img != ''
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -2 as albumID,  SUBSTRING_INDEX(REPLACE(img, 'media/image/', ''), '.', 1) as name, b.description,
  img as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_emarketing_banners b
LEFT JOIN s_media m
ON m.path = img
WHERE img != ''
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -2 as albumID,  SUBSTRING_INDEX(REPLACE(img, 'media/image/', ''), '.', 1) as name, b.description,
  img as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_emarketing_promotions b
LEFT JOIN s_media m
ON m.path = img
WHERE img != ''
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -2 as albumID,  SUBSTRING_INDEX(REPLACE(image, 'media/image/', ''), '.', 1) as name, b.description,
  image as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(image, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, datum as `created`
FROM s_emarketing_promotion_main b
LEFT JOIN s_media m
ON m.path = image
WHERE image != ''
AND m.id IS NULL;

UPDATE `s_emarketing_banners` SET `valid_from` = NULL WHERE `valid_from` = '0000-00-00 00:00:00';
UPDATE `s_emarketing_banners` SET `valid_to` = NULL WHERE `valid_to` = '0000-00-00 00:00:00';
