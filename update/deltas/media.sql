UPDATE s_articles_supplier
SET img = CONCAT('media/image/', img)
WHERE img NOT LIKE '%/%' AND img != '';

UPDATE s_articles_downloads
SET filename = CONCAT('media/pdf/', filename)
WHERE filename NOT LIKE '%/%' AND filename LIKE '%.pdf';

UPDATE s_articles_downloads
SET filename = CONCAT('media/image/', filename)
WHERE filename NOT LIKE '%/%'
AND (filename LIKE '%.jpg'
OR filename LIKE '%.png');

UPDATE s_articles_downloads
SET filename = CONCAT('media/unknown/', filename)
WHERE filename NOT LIKE '%/%';

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -1 as albumID, img as name, `description`,
  CONCAT('media/image/', img, '.', extension) as `path`,
  'IMAGE' as `type`, `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_img;

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -6 as albumID,  SUBSTRING_INDEX(filename, '.', 1) as name, `description`,
  CONCAT('media/image/', filename) as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(filename, '.', -1) as `extension`,
  size as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_downloads
WHERE filename LIKE 'media/image/%');

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -6 as albumID,  SUBSTRING_INDEX(filename, '.', 1) as name, `description`,
  CONCAT('media/unknown/', filename) as `path`,
  'UNKNOWN' as `type`, SUBSTRING_INDEX(filename, '.', -1) as `extension`,
  size as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_downloads
WHERE filename LIKE 'media/unknown/%');

INSERT INTO `s_media` (`albumID`, `name`, `description`, `path`, `type`, `extension`, `file_size`, `userID`, `created`)
SELECT
  -12 as albumID,  SUBSTRING_INDEX(img, '.', 1) as name, name as `description`,
  CONCAT('media/image/', img) as `path`,
  'IMAGE' as `type`, SUBSTRING_INDEX(img, '.', -1) as `extension`,
  0 as `file_size`, 0 as `userID`, NOW() as `created`
FROM s_articles_supplier
WHERE img != '';
