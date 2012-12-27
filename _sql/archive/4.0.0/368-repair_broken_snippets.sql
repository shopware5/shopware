-- //

DELETE FROM `s_core_snippets` WHERE `namespace` = '' AND `name` = 'CheckoutArticleNotFound';

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend', 1, 1, 'CheckoutArticleNotFound', 'Das Produkt konnte nicht gefunden werden.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend', 1, 2, 'CheckoutArticleNotFound', 'Article could not be found', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/listing_actions', 1, 2, 'ListingActionsSettingsTitle', 'View:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/viewlast', 1, 2, 'WidgetsRecentlyViewedHeadline', 'Recently viewed articles', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/delivery_informations', 1, 2, 'DetailDataInfoInstock', 'Ready to ship today; delivery time: approx. 1-3 working days', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/data', 1, 2, 'DetailDataId', 'Order number:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_item', 1, 2, 'CartItemInfoId', 'Article No.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_footer_left', 1, 2, 'CheckoutFooterIdLabelInline', 'Article No.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlineCutdown', 'Filter by:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlineSupplier', 'Manufacturer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 2, 'SearchLeftHeadlinePrice', 'Price', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/compare/index', 1, 2, 'ListingBoxLinkCompare', 'Compare', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 2, 'AjaxAddLabelOrdernumber', 'Article No.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/footer', 1, 2, 'IndexCopyright', 'Copyright ©  - All rights reserved', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('newsletter/index/footer', 1, 2, 'NewsletterFooterCopyright', 'Copyright ©  - All rights reserved', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/steps', 1, 2, 'CheckoutStepBasketText', 'Your shopping cart', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/index', 1, 2, 'RegisterTitle', 'Your address', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/steps', 1, 2, 'CheckoutStepConfirmText', 'Check and order', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/listing/listing_actions', 1, 1, 'ListingActionsSettingsTitle', 'Ansicht:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/viewlast', 1, 1, 'WidgetsRecentlyViewedHeadline', 'Zuletzt angeschaute Artikel', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/index/delivery_informations', 1, 1, 'DetailDataInfoInstock', 'Sofort versandfertig, Lieferzeit ca. 1-3 Werktage', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/data', 1, 1, 'DetailDataId', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_item', 1, 1, 'CartItemInfoId', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/cart_footer_left', 1, 1, 'CheckoutFooterIdLabelInline', 'Artikel-Nr.:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineCutdown', 'Filtern nach:', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlineSupplier', 'Hersteller', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/search/fuzzy_left', 1, 1, 'SearchLeftHeadlinePrice', 'Preis', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/plugins/compare/index', 1, 1, 'ListingBoxLinkCompare', 'Vergleichen', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/ajax_add_article', 1, 1, 'AjaxAddLabelOrdernumber', 'Artikel-Nr.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/footer', 1, 1, 'IndexCopyright', 'Copyright ©  - Alle Rechte vorbehalten', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('newsletter/index/footer', 1, 1, 'NewsletterFooterCopyright', 'Copyright ©  - Alle Rechte vorbehalten', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/steps', 1, 1, 'CheckoutStepBasketText', 'Ihr Warenkorb', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/index', 1, 1, 'RegisterTitle', 'Ihre Adresse', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/register/steps', 1, 1, 'CheckoutStepConfirmText', 'Prüfen und Bestellen', '2012-08-22 15:57:47', '2012-08-22 15:57:47');


REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('frontend/index/checkout_actions', 1, 1, 'IndexLinkService', 'Service/Hilfe', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/index', 1, 1, 'DetailFromNew', 'Hersteller', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/detail/index', 1, 2, 'DetailFromNew', 'Manufacturer', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 1, 'sFooterServiceHotline', 'Telefonische Unterst&uuml;tzung und Beratung unter:<br /><br /><strong style="font-size:19px;">0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 Uhr', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 2, 'sFooterServiceHotline', "Support and advice by telephone under::<br /><br /><strong style='font-size:19px;'>0180 - 000000</strong><br/>Mo-Fr, 09:00 - 17:00 o'clock", '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 1, 'sFooterShopNavi1', 'Shop Service', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 1, 'sFooterShopNavi2', 'Informationen', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 1, 'sFooterNewsletterHead', 'Newsletter', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 2, 'sFooterNewsletter', 'Sign up for the free demoshop newsletter and don''t miss out on any news or sales campaign any more!', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/index/menu_footer', 1, 1, 'sFooterNewsletter', 'Abonnieren Sie den kostenlosen DemoShop Newsletter und verpassen Sie keine Neuigkeit oder Aktion mehr aus dem DemoShop.', '2012-08-22 15:57:47', '2012-08-22 15:57:47');


REPLACE INTO `s_core_snippets` (`namespace`, `shopID`, `localeID`, `name`, `value`, `created`, `updated`) VALUES
('backend/activate/skeleton', 1, 2, 'WindowTitle', 'Clear cache', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/cache/skeleton', 1, 2, 'WindowTitle', 'Cache', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/index/index', 1, 2, 'IndexTitle', 'Shopware {config name=Version}  (Rev. 3650, 18.10.2010) - Backend (c)2010,2011 shopware AG', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/index/index', 1, 2, 'IndexTitle', 'Shopware {config name=Version}  (Rev. 3650, 18.10.2010) - Backend (c)2010,2011 shopware AG', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/license/skeleton', 1, 2, 'WindowTitle', 'Licenses ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugin/viewport', 1, 2, 'tree_titel', 'Plugins ', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsInfo', 'The voucher is valid until', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsCharge', 'Please note the minimum order value of {$coupon.minimumcharge|currency}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsText', 'You can easily redeem the voucher during the next checkout process in your shopping cart.', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/coupons/skeleton', 1, 2, 'WindowTitle', 'Coupon management', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/recommendation/skeleton', 1, 2, 'WindowTitle', 'Slider components', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('documents/index', 1, 2, 'DocumentIndexVoucher', 'For the next purchase, you receive a {$Document.voucher.value} {$Document.voucher.prefix} voucher', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('documents/index', 1, 2, 'DocumentIndexCurrency', '<br>Euro conversion factor: {$Order._currency.factor|replace:".":","}', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('frontend/checkout/finish_item', 1, 2, 'CartItemInfoFree', 'Free', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugins/coupons/pdf/index', 1, 2, 'PluginsBackendCouponsText', 'We hope you enjoy your visit of our online shop. For questions or problems please contact us under : Sample company/Sample street/ Sample town', '2012-08-22 15:57:47', '2012-08-22 15:57:47'),
('backend/plugin/skeleton', 1, 2, 'WindowTitle', 'Plugin Manager', '2012-08-22 15:57:47', '2012-08-22 15:57:47');

UPDATE `s_core_snippets` SET `namespace` = 'frontend/checkout/finish_item' WHERE namespace = 'frontend/checkout/finish_item	';


-- //@UNDO


-- //