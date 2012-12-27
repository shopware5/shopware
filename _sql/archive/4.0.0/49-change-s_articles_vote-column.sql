ALTER TABLE `s_articles_vote` CHANGE `answer_datum` `answer_date` DATETIME NOT NULL;

-- //@UNDO

ALTER TABLE `s_articles_vote` CHANGE `answer_date` `answer_datum` DATETIME NOT NULL;