-- //
ALTER TABLE `s_core_auth` CHANGE `lockeduntil` `lockeduntil` DATETIME NULL ;
-- //@UNDO
ALTER TABLE `s_core_auth` CHANGE `lockeduntil` `lockeduntil` DATETIME NOT NULL ;
-- //
