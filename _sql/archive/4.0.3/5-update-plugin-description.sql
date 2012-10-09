-- depends on SW-3950 - Change description of Heidelpay payment plugin

-- //

UPDATE `s_core_plugins` set description = REPLACE(description, 'als einziger BaFin-zertifizierter', 'als BaFin-zertifizierter') WHERE name LIKE "HeidelPayment" OR name LIKE "HeidelActions";

-- //@UNDO

-- //



