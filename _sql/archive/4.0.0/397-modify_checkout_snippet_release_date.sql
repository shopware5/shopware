-- //

UPDATE `s_core_snippets` SET `value` = "Dieser Artikel erscheint am" WHERE `name`="DetailDataInfoShipping" AND `shopID` = 1 AND `localeID` = 1;
UPDATE `s_core_snippets` SET `value` = "This article will be released at" WHERE `name`="DetailDataInfoShipping" AND `shopID` = 1 AND `localeID` = 2;

-- //@UNDO

UPDATE `s_core_snippets` SET `value` = "Lieferbar ab" WHERE `name`="DetailDataInfoShipping" AND `shopID` = 1 AND `localeID` = 1;
UPDATE `s_core_snippets` SET `value` = "Available from" WHERE `name`="DetailDataInfoShipping" AND `shopID` = 1 AND `localeID` = 2;

-- //