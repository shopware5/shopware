-- //
CREATE TABLE IF NOT EXISTS `s_filter_articles` (
  `articleID` int(10) unsigned NOT NULL,
  `valueID` int(10) unsigned NOT NULL,
  PRIMARY KEY (`articleID`,`valueID`)
);

INSERT INTO `s_filter_articles`
SELECT f1.`articleID`, MIN(f2.id) as `valueID`
FROM `s_filter_values` f1
LEFT JOIN `s_filter_values` f2
ON f2.value=f1.value
AND f2.optionID=f1.optionID
GROUP BY f1.value, f1.articleID;

DELETE fv
FROM `s_filter_values` fv
LEFT JOIN `s_filter_articles` fi
ON fi.valueID = fv.id
WHERE fi.valueID IS NULL;

ALTER TABLE `s_filter_values`
  DROP `groupID`,
  DROP `articleID`,
  DROP INDEX `optionID`,
  ADD UNIQUE `optionID` ( `optionID`, `value` );

-- //@UNDO

RENAME TABLE `s_filter_values` TO `s_filter_values_old` ;
CREATE TABLE IF NOT EXISTS `s_filter_values` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `groupID` int(11) NOT NULL,
  `optionID` int(11) NOT NULL,
  `articleID` int(11) NOT NULL,
  `value` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `groupID` (`groupID`),
  KEY `optionID` (`optionID`,`articleID`,`value`)
);

INSERT INTO `s_filter_values` (`groupID`, `optionID`, `articleID`, `value`)
SELECT fr.groupID, fv.optionID, fa.articleID, fv.value
FROM
  s_filter_values_old fv,
  s_filter_articles fa,
  s_articles a,
  s_filter_relations fr
WHERE fv.id=fa.valueID
AND fa.articleID=a.id
AND fr.groupID=a.filtergroupID
AND fr.optionID=fv.optionID;

DROP TABLE IF EXISTS `s_filter_articles`, `s_filter_values_old`;
