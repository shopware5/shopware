-- //
ALTER TABLE `s_articles_vote` ADD `answer` TEXT NOT NULL;
-- //@UNDO
ALTER TABLE `s_articles_vote` DROP `answer`;
-- //
