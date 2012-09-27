INSERT IGNORE INTO `s_blog` (`id`, `title`, `author_id`, `active`, `short_description`, `description`, `views`, `display_date`, `category_id`, `template`, `meta_keywords`, `meta_description`)
SELECT a.id, name, NULL, active, description, description_long, 0, releasedate, ac.categoryID, template, NULL, NULL
FROM backup_s_articles a, s_articles_categories ac
WHERE a.mode=1
AND ac.articleID = a.id

DELETE a, d, at, ac FROM s_articles a
LEFT JOIN s_articles_details d
ON d.articleID = a.id
LEFT JOIN s_articles_attributes at
ON at.articledetailsID = d.id
LEFT JOIN s_articles_categories ac
ON ac.articleID = a.id
WHERE a.mode != 0