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
  -6 as albumID, SUBSTRING_INDEX(filename, '.', 1) as name, d.description,
  CONCAT('media/unknown/', filename) as `path`,
  'UNKNOWN' as `type`, SUBSTRING_INDEX(filename, '.', -1) as `extension`,
  d.size as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_downloads d
LEFT JOIN s_media m
ON m.name = SUBSTRING_INDEX(filename, '.', 1)
WHERE filename LIKE 'media/unknown/%'
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -12 as albumID,  SUBSTRING_INDEX(img, '.', 1) as name, s.name as description,
  CONCAT('media/image/', img) as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_supplier s
LEFT JOIN s_media m
ON m.name = SUBSTRING_INDEX(img, '.', 1)
WHERE img != ''
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -2 as albumID,  SUBSTRING_INDEX(img, '.', 1) as name, b.description,
  CONCAT('media/image/', img) as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_emarketing_banners b
LEFT JOIN s_media m
ON m.name = SUBSTRING_INDEX(img, '.', 1)
WHERE img != ''
AND m.id IS NULL;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -2 as albumID,  SUBSTRING_INDEX(img, '.', 1) as name, b.description,
  CONCAT('media/image/', img) as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_emarketing_promotions b
LEFT JOIN s_media m
ON m.name = SUBSTRING_INDEX(img, '.', 1)
WHERE img != ''
AND m.id IS NULL;
