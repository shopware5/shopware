-- //

UPDATE `s_core_config_elements` SET `name` = 'premiumshippingnoorder' WHERE `name` = 'premiumshippiungnoorder';

REPLACE INTO`s_core_snippets` (`namespace` , `shopID` , `localeID` , `name` , `value` , `created` , `updated` ) VALUES
('frontend/checkout/shipping_costs', '1', '1', 'RegisterBillingLabelState', 'Bundesstaat:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/shipping_costs', '1', '2', 'RegisterBillingLabelState', 'State', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/tabs', '1', '1', 'DetailTabsAccessories', 'Zubehör', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/tabs', '1', '2', 'DetailTabsAccessories', 'Accessories', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/shipping_costs', '1', '1', 'StateSelection', 'Bitte wählen:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/shipping_costs', '1', '2', 'StateSelection', 'Please select:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/recommendation/blocks_detail', '1', '2', 'DetailViewedArticlesSlider', 'Customers also viewed:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/facebook/blocks_detail', '1', '1', 'facebookTabTitle', 'Facebook-Kommentare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/facebook/blocks_detail', '1', '2', 'facebookTabTitle', 'Facebook comments', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

-- //@UNDO


-- //