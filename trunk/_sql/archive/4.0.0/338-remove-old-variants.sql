-- //

DELETE d, attr, p
FROM `s_articles` a
INNER JOIN s_articles_details d ON a.id = d.articleID AND kind = 2
LEFT JOIN s_articles_attributes attr ON attr.articledetailsID = d.id
LEFT JOIN  s_articles_prices p ON p.articledetailsID = d.id
WHERE a.configurator_set_id IS NULL;

-- //@UNDO

--
