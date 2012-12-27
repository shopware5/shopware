-- //

ALTER TABLE `s_user` DROP `partnerID`;
ALTER TABLE `s_emarketing_partner` ADD `userID` INT( 11 ) UNSIGNED NULL;

-- //@UNDO

ALTER TABLE `s_user` ADD `partnerID` INT( 11 ) unsigned  NULL;
ALTER TABLE `s_emarketing_partner` DROP `userID`;

-- //
