
DELETE FROM `s_core_snippets` WHERE `name` = 'NoteInfoDate' AND shopID = '1' AND localeID = '1';

-- //@UNDO

-- we do need to undo this cause it will be generated correctly automatically