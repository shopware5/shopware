-- //

ALTER TABLE `s_user` ADD `partnerID` INT( 11 ) unsigned  NULL;

-- //@UNDO

ALTER TABLE `s_user` DROP `partnerID`;

-- //
