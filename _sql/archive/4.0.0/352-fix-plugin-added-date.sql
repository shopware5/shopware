UPDATE `s_core_plugins` SET `added` = NULL WHERE added = '0000-00-00 00:00:00';

-- //@UNDO

UPDATE `s_core_plugins` SET `added` = '0000-00-00 00:00:00' WHERE added IS NULL;


