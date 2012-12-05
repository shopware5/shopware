-- Set multishopID to 1 if the default ID does not exist //

UPDATE `s_export` SET `multishopID`=1 WHERE `multishopID` NOT IN (SELECT `id` FROM `s_core_shops`) AND `name`='Google Produktsuche';

-- //@UNDO

-- //
