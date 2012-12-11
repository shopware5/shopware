INSERT IGNORE INTO `s_blog` (`id`, `title`, `author_id`, `active`, `short_description`, `description`, `views`, `display_date`, `category_id`, `template`, `meta_keywords`, `meta_description`)
SELECT a.id, name, NULL, active, description, description_long, 0, IF(releasedate = '0000-00-00', changetime, releasedate) as display_date, ac.categoryID, template, keywords, description
FROM backup_s_articles a
LEFT JOIN s_articles_categories ac
ON ac.articleID = a.id
WHERE a.mode=1
GROUP BY a.id;

INSERT IGNORE INTO s_blog_media
SELECT NULL, b.id as blog_id, i.media_id, IF(i.main = 1, 1,0) as preview
FROM s_blog b, s_articles_img i
WHERE i.articleID = b.id;

INSERT IGNORE INTO s_blog_comments
SELECT NULL, articleID, name, headline, comment, datum, active, email, points
FROM s_articles_vote;

DELETE a, d, at, ac, c, i FROM s_articles a
LEFT JOIN s_articles_details d
ON d.articleID = a.id
LEFT JOIN s_articles_attributes at
ON at.articledetailsID = d.id
LEFT JOIN s_articles_categories ac
ON ac.articleID = a.id
LEFT JOIN s_articles_vote c
ON c.articleID = a.id
LEFT JOIN s_articles_img i
ON i.articleID = a.id
WHERE a.mode != 0;

UPDATE `s_categories` SET `external` = '' WHERE `blog` = 1;
