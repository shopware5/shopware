INSERT INTO `s_filter_articles`
SELECT f1.`articleID`, f2.id as `valueID`
FROM `backup_s_filter_values` f1
LEFT JOIN `s_filter_values` f2
ON f2.value = f1.value
AND f2.optionID = f1.optionID;