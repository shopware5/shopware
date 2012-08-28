UPDATE s_user SET lockeduntil = NULL WHERE lockeduntil = '0000-00-00 00:00:00';
UPDATE s_user_billingaddress SET birthday = NULL WHERE birthday = '0000-00-00 00:00:00';
-- //@UNDO
UPDATE s_user SET lockeduntil = '0000-00-00 00:00:00' WHERE lockeduntil IS NULL;
UPDATE s_user_billingaddress SET birthday = '0000-00-00 00:00:00' WHERE birthday IS NULL;