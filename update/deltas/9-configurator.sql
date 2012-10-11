DELETE d FROM s_articles_details d, backup_s_articles_groups g
WHERE g.groupID = 1
AND g.articleID = d.articleID
AND d.kind = 1;

UPDATE s_articles_details d, backup_s_articles_groups_value v
SET d.ordernumber = v.ordernumber, d.active = v.active, d.instock = v.instock
WHERE v.articleID = d.articleID
AND d.kind = 1
AND v.standard = 1;

ALTER TABLE `s_article_configurator_groups` ADD INDEX ( `name` );

INSERT IGNORE INTO `s_articles_details` (`articleID`, `ordernumber`, `kind`, `active`, `instock`)
SELECT `articleID`, `ordernumber`, IF(`standard` = 1, 1, 2) as `kind`, `active`, `instock`
FROM backup_s_articles_groups_value;

INSERT IGNORE INTO `s_article_configurator_sets` (`name`, `public`, `type`)
SELECT CONCAT('Set-', d.ordernumber) as name, 0 as public, IFNULL(s.type, 1) as type
FROM backup_s_articles_groups g, s_articles_details d
LEFT JOIN backup_s_articles_groups_settings s
ON s.articleID = d.articleID
AND s.type < 3
LEFT JOIN s_article_configurator_sets c
ON c.name = CONCAT('Set-', d.ordernumber)
WHERE g.groupID = 1
AND g.articleID = d.articleID
AND d.kind = 1
AND c.id IS NULL;

UPDATE s_articles a, s_articles_details d, s_article_configurator_sets s
SET a.configurator_set_id = s.id
WHERE a.main_detail_id = d.id
AND s.name = CONCAT('Set-', d.ordernumber);

INSERT INTO `s_article_configurator_groups` (`name`, `description`, `position`)
SELECT STRAIGHT_JOIN CONCAT(articleID, '-', groupID, '-', groupname) as name, groupdescription as description, groupposition as position
FROM backup_s_articles_groups s
LEFT JOIN s_article_configurator_groups t
ON t.name = CONCAT(articleID, '-', groupID, '-', groupname)
WHERE t.id IS NULL
ORDER BY articleID, groupID, position;

INSERT IGNORE INTO `s_article_configurator_options` (`group_id`, `name`, `position`)
SELECT STRAIGHT_JOIN g.id as group_id, o.optionname as name, o.optionposition as position
FROM backup_s_articles_groups_option o
JOIN s_article_configurator_groups g
ON g.name LIKE CONCAT(articleID, '-', groupID, '-%')
LEFT JOIN s_article_configurator_options c
ON c.name = o.optionname AND c.group_id=g.id
WHERE c.id IS NULL;

INSERT IGNORE INTO `s_article_configurator_set_group_relations` (`set_id`, `group_id`)
SELECT STRAIGHT_JOIN s.id, g.id
FROM s_articles a, s_article_configurator_sets s, s_article_configurator_groups g
WHERE a.configurator_set_id = s.id
AND g.name LIKE CONCAT(a.id, '-%');

INSERT IGNORE INTO `s_article_configurator_set_option_relations` (`set_id`, `option_id`)
SELECT STRAIGHT_JOIN r.set_id, o.id as option_id
FROM s_articles a, s_article_configurator_sets s, s_article_configurator_groups g,
  s_article_configurator_set_group_relations r, s_article_configurator_options o
WHERE a.configurator_set_id = s.id
AND g.name LIKE CONCAT(a.id, '-%')
AND g.id = r.group_id
AND o.group_id = r.group_id;

INSERT IGNORE INTO `s_article_configurator_option_relations` (`article_id`, `option_id`)
SELECT d.id, o.id FROM backup_s_articles_groups_value v, s_articles_details d, s_articles a,
backup_s_articles_groups_option b, s_article_configurator_options o, s_article_configurator_set_option_relations r
WHERE v.ordernumber = d.ordernumber
AND a.id = d.articleID
AND b.optionID IN (v.attr1, v.attr2, v.attr3, v.attr4, v.attr5, v.attr6, v.attr7, v.attr8, v.attr9, v.attr10)
AND o.name = b.optionname
AND r.option_id = o.id
AND r.set_id = a.configurator_set_id;

INSERT IGNORE INTO `s_articles_prices` (`pricegroup`, `from`, `to`, `articleID`, `articledetailsID`, `price`)
SELECT p.groupkey as pricegroup, 1 as `from`, 'beliebig' as `to`, d.articleID , d.id as articledetailsID, p.price
FROM backup_s_articles_groups_prices p
JOIN backup_s_articles_groups_value v
ON p.valueID = v.valueID
JOIN s_articles_details d
ON d.ordernumber = v.ordernumber
LEFT JOIN s_articles_prices t
ON t.pricegroup = p.groupkey
AND t.from = 1
AND t.articledetailsID = d.id
WHERE p.price != 0
AND t.id IS NULL;

UPDATE `s_article_configurator_groups`
SET `name` = SUBSTRING_INDEX(name, '-', -1)
WHERE `name` LIKE '%-%-%';
