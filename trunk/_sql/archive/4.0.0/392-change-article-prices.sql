-- //
ALTER TABLE  `s_articles_prices` CHANGE  `pseudoprice`  `pseudoprice` DOUBLE NULL DEFAULT NULL ,
CHANGE  `baseprice`  `baseprice` DOUBLE NULL DEFAULT NULL;


-- //@UNDO


ALTER TABLE  `s_articles_prices` CHANGE  `pseudoprice`  `pseudoprice` DECIMAL( 10, 2 ) NULL DEFAULT NULL ,
CHANGE  `baseprice`  `baseprice` DECIMAL( 10, 2 ) NULL DEFAULT NULL;

-- //
