SET FOREIGN_KEY_CHECKS=0;

INSERT INTO `s_benchmark_config` (`id`, `shop_id`, `active`, `last_sent`, `last_received`, `last_order_id`, `last_customer_id`, `last_product_id`, `batch_size`, `industry`, `type`, `response_token`, `cached_template`)
VALUES (UNHEX('F00'), '2', '1', '1990-01-01 00:00:00', '1990-01-01 00:00:00', '0', '0', '0', '1000', '3', 'b2c', NULL, NULL);

SET FOREIGN_KEY_CHECKS=1;