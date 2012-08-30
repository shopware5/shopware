-- //

ALTER TABLE `s_campaigns_mailings` CHANGE `datum` `datum` DATE NULL DEFAULT NULL,
	CHANGE `read` `read` INT( 11 ) NOT NULL DEFAULT '0',
	CHANGE `clicked` `clicked` INT( 11 ) NOT NULL DEFAULT '0';
UPDATE `s_campaigns_mailings` SET `datum` = NULL WHERE `datum` = '0000-00-00';

-- //@UNDO

--
