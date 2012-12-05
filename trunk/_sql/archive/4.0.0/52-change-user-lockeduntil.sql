ALTER TABLE `s_user` CHANGE `lockeduntil` `lockeduntil` DATETIME NULL;
-- //@UNDO
ALTER TABLE `s_user` CHANGE `lockeduntil` `lockeduntil` DATETIME NOT NULL;