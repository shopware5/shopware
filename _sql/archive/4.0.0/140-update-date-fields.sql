ALTER TABLE `s_articles` CHANGE `datum` `datum` DATE NULL DEFAULT NULL ,
CHANGE `releasedate` `releasedate` DATE NULL DEFAULT NULL;

UPDATE s_articles SET datum = NULL WHERE datum = '0000-00-0000-00-00';
UPDATE s_articles SET releasedate = NULL WHERE releasedate = '0000-00-0000-00-00';

-- //@UNDO

ALTER TABLE `s_articles` CHANGE `datum` `datum` DATE NOT NULL DEFAULT '0000-00-0000-00-00',
CHANGE `releasedate` `releasedate` DATE NOT NULL DEFAULT '0000-00-0000-00-00';
UPDATE s_articles SET datum = '0000-00-0000-00-00' WHERE datum IS NULL;
UPDATE s_articles SET releasedate = '0000-00-0000-00-00' WHERE releasedate IS NULL;