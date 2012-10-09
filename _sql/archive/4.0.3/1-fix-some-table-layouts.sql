-- //

DROP TABLE IF EXISTS `s_core_plugin_configs`, `s_core_plugin_elements`, `s_core_engine_queries`, `s_core_licences`, `s_plugin_benchmark_log`;
ALTER TABLE `s_filter_articles` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_order_history` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;
ALTER TABLE `s_plugin_widgets_notes` ENGINE=InnoDB, CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;

-- //@UNDO

-- //