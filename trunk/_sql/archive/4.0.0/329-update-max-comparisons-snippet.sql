-- //

UPDATE `s_core_snippets` SET `value` = 'Es können maximal {config name=maxComparisons} Artikel in einem Schritt verglichen werden'
WHERE `name` = 'CompareInfoMaxReached' AND localeID = 1;

UPDATE `s_core_snippets` SET `value` = 'You can compare a maximum of {config name=maxComparisons} items in a single step'
WHERE `name` = 'CompareInfoMaxReached' AND localeID = 2;

-- //@UNDO


UPDATE `s_core_snippets` SET `value` = 'Es können maximal 5 Artikel in einem Schritt verglichen werden'
WHERE `name` = 'CompareInfoMaxReached' AND localeID = 1;

UPDATE `s_core_snippets` SET `value` = 'You can compare a maximum of 5 items in a single step'
WHERE `name` = 'CompareInfoMaxReached' AND localeID = 2;


--
