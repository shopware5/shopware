-- //

ALTER TABLE  `s_search_index` ADD INDEX  `clean_up_index` (  `keywordID` ,  `fieldID` );
ALTER TABLE  `s_search_fields` ADD INDEX (  `tableID` );

-- //@UNDO

-- //
