UPDATE s_articles a, s_articles_details d
SET a.main_detail_id = d.id
WHERE d.articleID = a.id
AND d.kind = 1;

INSERT IGNORE INTO `s_article_configurator_sets` (`name`, `public`, `type`)
SELECT CONCAT('Set-', d.ordernumber) as name, 0 as public, 0 as type
FROM s_articles_details v, s_articles_details d
LEFT JOIN s_article_configurator_sets s
ON s.name = CONCAT('Set-', d.ordernumber)
WHERE d.kind = 1
AND v.articleID = d.articleID
AND v.kind = 2
AND s.id IS NULL
GROUP BY d.id;

UPDATE s_articles a, s_articles_details d, s_article_configurator_sets s
SET a.configurator_set_id = s.id
WHERE a.main_detail_id = d.id
AND s.name = CONCAT('Set-', d.ordernumber);

INSERT IGNORE INTO `s_article_configurator_groups` (`id`, `name`, `description`, `position`) VALUES
(1, 'Variante', '', 1);

INSERT IGNORE INTO `s_article_configurator_options` (`group_id`, `name`, `position`)
SELECT 1 as group_id, d.additionaltext as name, d.position
FROM s_articles a, s_articles_details d
WHERE a.configurator_set_id IS NOT NULL
AND d.articleID = a.id;

INSERT IGNORE INTO `s_article_configurator_set_group_relations` (`set_id`, `group_id`)
SELECT id, 1
FROM `s_article_configurator_sets`
WHERE `name` LIKE 'Set-%';

INSERT IGNORE INTO `s_article_configurator_set_option_relations` (`set_id`, `option_id`)
SELECT s.id as set_id, o.id as option_id
FROM s_article_configurator_sets s, s_articles a,
  s_articles_details d, s_article_configurator_options o
WHERE s.name LIKE 'Set-%'
AND a.configurator_set_id = s.id
AND d.articleID = a.id
AND o.group_id = 1
AND o.name = d.additionaltext;

INSERT IGNORE INTO `s_article_configurator_option_relations` (`article_id`, `option_id`)
SELECT d.id as article_id, o.id as option_id
FROM s_article_configurator_sets s, s_articles a,
  s_articles_details d, s_article_configurator_options o
WHERE s.name LIKE 'Set-%'
AND a.configurator_set_id = s.id
AND d.articleID = a.id
AND o.group_id = 1
AND o.name = d.additionaltext;

UPDATE s_articles_details d
SET d.active =1
WHERE d.kind =2
AND d.additionaltext IS NOT NULL;

INSERT INTO s_articles_img (parent_id, article_detail_id)
SELECT i.id, d.id
FROM s_articles_img i
JOIN s_articles_details d
ON i.relations != ''
AND i.relations = d.ordernumber
LEFT JOIN s_articles_img r
ON r.parent_id = i.id
AND d.id = r.article_detail_id
WHERE i.articleID IS NOT NULL
AND r.id IS NULL;

INSERT INTO s_article_img_mappings (image_id)
SELECT i.parent_id FROM s_articles_img i
LEFT JOIN s_article_img_mappings m
ON m.image_id = i.parent_id
WHERE i.parent_id IS NOT NULL
AND m.id IS NULL;

INSERT INTO s_article_img_mapping_rules (mapping_id, option_id)
SELECT m.id, r.option_id FROM s_articles_img i
JOIN s_article_img_mappings m
ON m.image_id = i.parent_id
JOIN s_article_configurator_option_relations r
ON r.article_id = i.article_detail_id
LEFT JOIN s_article_img_mapping_rules s
ON s.mapping_id = m.id AND s.option_id = r.option_id
WHERE i.parent_id IS NOT NULL
AND s.id IS NULL;

UPDATE s_articles_img i, s_articles_details d
SET i.relations = ''
WHERE i.relations = d.ordernumber
AND i.relations != '';
