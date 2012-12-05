-- //

DELETE FROM `s_core_snippets` WHERE TRIM(`value`) =  '';
DELETE FROM `s_core_snippets` WHERE `shopID` !=1;

-- //@UNDO

--