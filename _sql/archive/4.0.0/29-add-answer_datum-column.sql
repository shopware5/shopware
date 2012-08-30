-- //
ALTER TABLE `s_articles_vote` ADD `answer_datum` DATETIME NOT NULL;
-- //@UNDO
ALTER TABLE `s_articles_vote` DROP `answer_datum`;
-- //
