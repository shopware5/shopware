-- //

UPDATE `s_core_menu` SET `parent` = NULL WHERE `parent` IN ('', '0', 0);

-- //@UNDO

-- //