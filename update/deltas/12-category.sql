TRUNCATE `s_categories`;

INSERT INTO `s_categories` (
  `id`, `parent`, `description`, `position`, `active`, `left`, `right`
)
VALUES (
  1, NULL, 'Root', '0', 1, 1, 2
);

INSERT IGNORE INTO s_categories (
  `id` , `parent` , `description` , `position` , `metakeywords`,
  `metadescription` , `cmsheadline` , `cmstext` , `template` , `noviewselect` ,
  `active` , `blog` , `showfiltergroups` ,
  `external` , `hidefilter` , `hidetop`,
  `added`, `changed`
)
SELECT
  `id` , `parent` , `description` , `position` ,
  IF(`metakeywords`='', NULL, `metakeywords`),
  IF(`metadescription`='', NULL, `metadescription`),
  IF(`cmsheadline`='', NULL, `cmsheadline`),
  IF(`cmstext`='', NULL, `cmstext`),
  IF(`template`='', NULL, `template`), `noviewselect` ,
  `active` , `blog` , `showfiltergroups` ,
  IF(`external`='', NULL, `external`), `hidefilter` , `hidetop`,
  NOW() as `added`, NOW() as `changed`
FROM `backup_s_categories`;

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
