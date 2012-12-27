-- //

ALTER TABLE `s_emarketing_partner` CHANGE `active` `active` INT( 1 ) NOT NULL DEFAULT '0';
ALTER TABLE `s_emarketing_partner` CHANGE `cookielifetime` `cookielifetime` INT( 11 ) NOT NULL DEFAULT '0';
ALTER TABLE `s_emarketing_partner` CHANGE `percent` `percent` DOUBLE NOT NULL DEFAULT '0';
ALTER TABLE `s_emarketing_partner` CHANGE `fix` `fix` DOUBLE NOT NULL DEFAULT '0';

-- //@UNDO


ALTER TABLE `s_emarketing_partner` CHANGE `active` `active` INT( 1 ) NOT NULL;
ALTER TABLE `s_emarketing_partner` CHANGE `cookielifetime` `cookielifetime` INT( 11 ) NOT NULL;
ALTER TABLE `s_emarketing_partner` CHANGE `percent` `percent` DOUBLE NOT NULL;
ALTER TABLE `s_emarketing_partner` CHANGE `fix` `fix` DOUBLE NOT NULL;

-- //