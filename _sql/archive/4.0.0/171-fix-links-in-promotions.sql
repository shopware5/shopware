UPDATE `s_emarketing_promotions` set link = REPLACE( link, 'beispiel/', 'beispiele/' ) WHERE `link` LIKE 'beispiel/%';

-- //@UNDO

UPDATE `s_emarketing_promotions` set link = REPLACE( link, 'beispiele/', 'beispiel/' ) WHERE `link` LIKE 'beispiele/%';
