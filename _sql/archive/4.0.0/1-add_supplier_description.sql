-- //

    ALTER TABLE `s_articles_supplier` ADD `description` LONGTEXT CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL;


-- //@UNDO

    ALTER TABLE `s_articles_supplier` DROP `description`;

-- //

