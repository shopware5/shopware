UPDATE `s_categories` SET `metakeywords` = NULL WHERE `metakeywords` = '';
UPDATE `s_categories` SET `metadescription` = NULL WHERE `metadescription` = '';
UPDATE `s_categories` SET `cmsheadline` = NULL WHERE `cmsheadline` = '';
UPDATE `s_categories` SET `cmstext` = NULL WHERE `cmstext` = '';
UPDATE `s_categories` SET `template` = NULL WHERE `template` = '';
UPDATE `s_categories` SET `ac_attr1` = NULL WHERE `ac_attr1` = '';
UPDATE `s_categories` SET `ac_attr2` = NULL WHERE `ac_attr2` = '';
UPDATE `s_categories` SET `ac_attr3` = NULL WHERE `ac_attr3` = '';
UPDATE `s_categories` SET `ac_attr4` = NULL WHERE `ac_attr4` = '';
UPDATE `s_categories` SET `ac_attr5` = NULL WHERE `ac_attr5` = '';
UPDATE `s_categories` SET `ac_attr6` = NULL WHERE `ac_attr6` = '';
UPDATE `s_categories` SET `external` = NULL WHERE `external` = '';
UPDATE `s_categories` SET `added` = NOW(), `changed` = NOW();

INSERT IGNORE INTO s_categories_attributes (categoryID, attribute1, attribute2, attribute3, attribute4, attribute5, attribute6)
SELECT id,
  IF(ac_attr1='', NULL, ac_attr1),
  IF(ac_attr2='', NULL, ac_attr2),
  IF(ac_attr3='', NULL, ac_attr3),
  IF(ac_attr4='', NULL, ac_attr4),
  IF(ac_attr5='', NULL, ac_attr5),
  IF(ac_attr6='', NULL, ac_attr6)
FROM backup_s_categories;

DELETE ac
FROM s_articles_categories ac, s_categories c
WHERE ac.categoryID = c.id
AND (SELECT 1 FROM s_categories WHERE parent = c.id LIMIT 1) IS NOT NULL;

INSERT INTO `s_categories` (
  `id`, `parent`, `description`, `position`, `active`, `left`, `right`
)
VALUES (
  1, NULL, 'Root', '0', 1, 1, 2
);