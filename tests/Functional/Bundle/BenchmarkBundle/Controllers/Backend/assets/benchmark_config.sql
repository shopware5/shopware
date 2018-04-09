SET FOREIGN_KEY_CHECKS=0;
DELETE FROM s_benchmark_config;

INSERT INTO `s_benchmark_config` (`active`, `last_sent`, `last_received`, `last_order_id`, `orders_batch_size`, `business`, `terms_accepted`, `cached_template`)
VALUES
    (NULL, '1990-01-01 00:00:00', '1990-01-01 00:00:00', 0, 1000, NULL, 0, NULL);

SET FOREIGN_KEY_CHECKS=1;
