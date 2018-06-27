SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_benchmark_config;

INSERT INTO `s_benchmark_config` (`id`, `active`, `shop_id`, `last_sent`, `last_received`, `last_order_id`, `last_customer_id`, `last_product_id`,`batch_size`, `industry`, `type`, `cached_template`)
VALUES(UNHEX('F9B39136A66B4EA5B651A99483BA0F85'), 0, 1, "1990-01-01 00:00:00", "1990-01-01 00:00:00", 0, 0, 0, 1000, 0, '', NULL);

SET FOREIGN_KEY_CHECKS=1;
