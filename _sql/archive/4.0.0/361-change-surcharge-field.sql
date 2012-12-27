ALTER TABLE  `s_article_configurator_price_surcharges` CHANGE  `surcharge`  `surcharge` DECIMAL( 10, 3 ) NOT NULL;

-- //@UNDO

ALTER TABLE  `s_article_configurator_price_surcharges` CHANGE  `surcharge`  `surcharge` DECIMAL( 10, 0 ) NOT NULL;