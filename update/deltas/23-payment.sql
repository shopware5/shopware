DELETE p FROM s_core_paymentmeans p
LEFT JOIN s_order o
ON o.paymentID = p.id
WHERE p.id > 5
AND o.id IS NULL;

UPDATE s_core_paymentmeans
SET embediframe = '', active = 0
WHERE embediframe != '';

UPDATE `s_core_paymentmeans`
SET `name` = 'paypal_old', `active` = 0
WHERE name = 'paypal'
AND (SELECT 1 FROM `s_core_paymentmeans` WHERE `name` = 'paypal_old') IS NULL

UPDATE `s_core_paymentmeans`
SET `name` = 'paypal', `template` = '', `class` = ''
WHERE name = 'paypalexpress';