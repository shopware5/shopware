-- ALTER  TABLE  `s_ticket_support`  ADD  `shop_id` INT( 11  )  UNSIGNED NOT  NULL;
-- ALTER  TABLE  `s_ticket_support`  ADD  INDEX (  `shop_id`  );

UPDATE s_ticket_support as t
LEFT JOIN backup_s_core_multilanguage as m
ON m.isocode = t.isocode
SET t.shop_id = m.id;

UPDATE s_ticket_support_mails as t
LEFT JOIN backup_s_core_multilanguage as m
ON m.isocode = t.isocode
SET t.shop_id = m.id;
