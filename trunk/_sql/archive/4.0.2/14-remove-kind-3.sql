-- //

DELETE ad, at, ap
FROM s_articles_details ad
LEFT JOIN s_articles_attributes at
ON ad.id=at.articledetailsID
LEFT JOIN s_articles_prices ap
ON ad.id=ap.articledetailsID
WHERE ad.kind=3;

-- //@UNDO


-- //