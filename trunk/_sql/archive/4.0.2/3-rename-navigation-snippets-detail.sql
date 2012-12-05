-- //

UPDATE  `s_core_snippets` SET  `value` =  'Zur Übersicht' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Back to overview' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 2;

-- //@UNDO

UPDATE  `s_core_snippets` SET  `value` =  'Übersicht' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 1;
UPDATE  `s_core_snippets` SET  `value` =  'Overview' WHERE  `name` = 'DetailNavIndex' AND `localeID` = 2;

-- //
