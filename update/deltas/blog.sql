INSERT INTO `s_blog` (`id`, `title`, `author_id`, `active`, `short_description`, `description`, `views`, `display_date`, `category_id`, `template`, `meta_keywords`, `meta_description`)
SELECT id, name, NULL, active, description, description_long, 0, releasedate, ac.categoryID, template, NULL, NULL
FROM s_articles a, s_articles_categories ac
WHERE a.mode=1
AND ac.articleID = a.id;

DELETE a, d, at, ac FROM s_articles a, s_articles_details d, s_articles_attributes at, s_articles_categories ac
WHERE a.mode != 0
AND d.articleID = a.id
AND at.articledetailsID = d.id
AND ac.articleID = a.id;