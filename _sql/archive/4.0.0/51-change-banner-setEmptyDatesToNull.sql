-- //
UPDATE `s_emarketing_banners` SET `valid_from` = NULL WHERE `valid_from` = '0000-00-00 00:00:00';
UPDATE `s_emarketing_banners` SET `valid_to` = NULL WHERE `valid_to` = '0000-00-00 00:00:00';

-- //@UNDO
UPDATE `s_emarketing_banners` SET `valid_from` = '0000-00-00 00:00:00' WHERE `valid_from` IS NULL;
UPDATE `s_emarketing_banners` SET `valid_to`   = '0000-00-00 00:00:00' WHERE `valid_to` IS NULL;