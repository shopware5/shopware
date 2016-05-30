# Shopware Upgrade Information
In this document you will find a changelog of the important changes related to the code base of Shopware.

## 5.2.0 DEV
* Increased minimum required PHP version to PHP >= 5.6.4.
* Added CSRF protection to frontend and backend which is enabled by default.
    * OptOut by implementing `Shopware\Components\CSRFWhitelistAware` interface
    * Added `X-CSRF-Token` to every ajax request
    * Added `__csrf_token` to every html form in frontend
    * Added `__csrf_token` param to every ExtJS form submit via override in `ExtJs/overrides/Ext.form.Base.js`
    * Added `csrfProtection` config options to disable CSRF protection
    * Special thanks to: [ltepner](https://github.com/ltepner)
    * See: https://developers.shopware.com/developers-guide/csrf-protection/
* Updated Symfony Components to version 2.8 LTS
* Replaced polyfill provided by `indigophp/hash-compat` with `symfony/polyfill-php56`
* Added polyfill for `random_bytes()` and `random_int()` via `paragonie/random_compat`
* Removed `client_check` and `referer_check` from the config in favor of the CSRF protection.
* Removed session variables `__SW_REFERER` and `__SW_CLIENT`
* Added AdvancedMenu feature to configure menu opening delay on mouse hover
* Added the ability to add custom CSS classes to emotion elements in the backend.
    * Multiple classnames can be added by separating them with whitespaces.
    * Added new `css_class` column to the `s_emotion_elements` table.
* Removed deprecated columns `s_filter_values.value_numeric` and `s_filter_options.default`
* Updated `monolog/monolog` to version 1.17.2
* Added HTML code widget for the shopping worlds which lets the user enter actual Smarty & JavaScript code which will be included like it is
    * The Smarty code has access to all globally available Smarty variables
* Added the following fields to status emails:
    * `billing_additional_address_line1`
    * `billing_additional_address_line2`
    * `shipping_additional_address_line1`
    * `shipping_additional_address_line2`
* Replaced `bower` with `npm` to manage the frontend dependencies
    * The dependencies can now be installed using the command: `npm install && npm run build`
    * Removed the file `vendors/less/open-sans-fontface/open-sans.less`. It's now located under `public/src/less/_components/fonts.less`
* Deprecated `Shopware_Bootstrap` and `Enlight_Bootstrap` commonly accesed by `Shopware()->Bootstrap()`.
* Removed deprecated methods and variables:
    * `sArticle.sVoteAverange` in product listings
    * `sNote.sVoteAverange` in note listing
    * `blog.media.path`
    * Removed methods Shopware\Models\Menu\Repository::addItem() and Shopware\Models\Menu\Repository::save()
    * `file` property of banner mappings
    * Removed method sOrder::sManageEsdOrder()
    * `sBanner.img` variable
    * emotion category teaser `image` property
* Removed the following events:
    * `sArticles::sGetCheapestPrice::replace`
    * `sArticles::sGetCheapestPrice::after`
    * `sArticles::sCalculatingPrice::replace`
    * `sArticles::sCalculatingPrice::replace`
    * `sArticles::getArticleListingCover::replace`
    * `sArticles::getArticleListingCover::after`
    * `sArticles::calculateCheapestBasePriceData::replace`
    * `sArticles::calculateCheapestBasePriceData::after`
    * `sArticles::sGetArticleProperties::replace`
    * `sArticles::sGetArticleProperties::after`
    * `sArticles::sGetArticlePictures::replace`
    * `sArticles::sGetArticlePictures::after`
    * `sArticles::sGetPricegroupDiscount::replace`
    * `sArticles::sGetPricegroupDiscount::after`
    * `sArticles::sGetUnit::replace`
    * `sArticles::sGetUnit::after`
    * `sArticles::sGetArticlesAverangeVote::replace`
    * `sArticles::sGetArticlesAverangeVote::after`
    * `sArticles::sGetArticlesVotes::replace`
    * `sArticles::sGetArticlesVotes::after`
    * `Shopware_Modules_Articles_GetPromotionById_FilterResult`
    * `Shopware_Modules_Articles_GetArticleById_FilterArticle`
* The following article arrays are now indexed by their order number
    * top seller
    * emotion slider data
    * recommendation data (also bought and also viewed)
    * similar and related articles
* Removed deprecated table `s_user_debit`
    * `\Shopware\Models\Customer\Customer::$debit`
    * `\Shopware\Models\Customer\Customer::getDebit()`
    * `\Shopware\Models\Customer\Customer::setDebit()`
* Added new configuration field to the emotion banner widget for link target.
* Added composer dependency for Symfony Form and implemented FormBundle
* Changed constructor of `\Shopware\Components\Theme\PathResolver`
* Changed constructor of `\Shopware_Components_Snippet_Manager`
* Changed constructor of `\Shopware\Bundle\PluginInstallerBundle\Service\DownloadService`
* Changed signature of `Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface::getSelection`, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\SearchBundleDBAL\PriceHelper::getSelection` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface::getList`, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface::get`, now expects `ProductContextInterface` instead of `ShopContextInterface` and `ListProduct` instead of `BaseProduct`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService::getList` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService::get` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface` and `ListProduct` instead of `BaseProduct`
* Deprecated methods now use `trigger_error` of type `E_USER_DEPRECATED`
* Changed default error_reporting to `E_ALL & ~E_USER_DEPRECATED`
* Deprecated Class `Enlight_Application`
* Deprecated `Enlight_Application::Instance()` and `Enlight()`, use `Shopware()` instead
* Deprecated `Shopware\Kernel::getShopware()`
* Deprecated `Shopware::App()` / `Shopware()->App()`
* Deprecated `Shopware::Environment()` / `Shopware()->Environment()`
* Deprecated `Shopware::OldPath()` / `Shopware()->OldPath()`
* Deprecated `Shopware::setEventManager()` / `Shopware()->setEventManager()`
* Deprecated `Enlight_Application::CorePath()` / `Shopware()->CorePath()`
* Deprecated `Enlight_Application::Path()` / `Shopware()->Path()`
* Deprecated `Enlight_Application::ComponentsPath()` / `Shopware()->ComponentsPath()`
* Deprecated `Enlight_Application::DS()`
* Removed `Enlight_Application::setOptions()`
* Removed `Enlight_Application::getOptions()`
* Removed `Enlight_Application::getOption()`
* Removed `Enlight_Application::setPhpSettings()`
* Removed `Enlight_Application::setIncludePaths()`
* Removed `Enlight_Application::__callStatic()`
* Removed the following models
    * `Shopware.apps.Customer.view.detail.Billing`
    * `Shopware.apps.Customer.view.detail.Shipping`
* Removed fax field form billing addresses
* Updated `ongr/elasticsearch-dsl` to v2.0.0, see https://github.com/ongr-io/ElasticsearchDSL/blob/master/CHANGELOG.md#v200-2016-03-03 for BC breaks.
* Renamed block 'frontend_blog_bookmarks_deliciosus' to 'frontend_blog_bookmarks_delicious'
* Deprecated `\Shopware\Models\Article\Element`
* Removed the following templates including their snippets and blocks
    * `frontend/account/billing.tpl`
    * `frontend/account/billing_checkout.tpl`
    * `frontend/account/content_right.tpl`
    * `frontend/account/select_address.tpl`
    * `frontend/account/select_billing.tpl`
    * `frontend/account/select_billing_checkout.tpl`
    * `frontend/account/select_shipping.tpl`
    * `frontend/account/select_shipping_checkout.tpl`
    * `frontend/account/shipping.tpl`
    * `frontend/account/shipping_checkout.tpl`
    * `frontend/checkout/cart_left.tpl`
    * `frontend/checkout/confirm_left.tpl`
* Removed `sAdmin::sGetPreviousAddresses()`
* Removed `sAdmin::sUpdateBilling()`
* Removed `sAdmin::sUpdateShipping()`
* Removed `sAdmin::sValidateStep1()`
* Removed `sAdmin::sValidateStep2()`
* Removed `sAdmin::sValidateStep2ShippingAddress()`
* Removed `billingAction()` in `Controllers/Frontend/Account.php`
* Removed `shippingAction()` in `Controllers/Frontend/Account.php`
* Removed `saveBillingAction()` in `Controllers/Frontend/Account.php`
* Removed `saveShippingAction()` in `Controllers/Frontend/Account.php`
* Removed `selectBillingAction()` in `Controllers/Frontend/Account.php`
* Removed `selectShippingAction()` in `Controllers/Frontend/Account.php`
* Moved block `frontend_checkout_confirm_left_billing_address` outside panel body
* Moved block `frontend_checkout_confirm_left_shipping_address` outside panel body
* Removed the following backend models including their smarty blocks
    * `Shopware.apps.Supplier.model.Attribute`
    * `Shopware.apps.Customer.model.BillingAttributes`
    * `Shopware.apps.Customer.model.ShippingAttributes`
    * `Shopware.apps.Customer.model.Attribute`
    * `Shopware.apps.Blog.model.Attribute`
    * `Shopware.apps.Form.model.Attribute`
    * `Shopware.apps.MediaManager.model.Attribute`
    * `Shopware.apps.Property.model.Attribute`
    * `Shopware.apps.Config.model.form.Attribute`
    * `Shopware.apps.Voucher.model.Attribute`
    * `Shopware.apps.Emotion.model.Attribute`
    * `Shopware.apps.Banner.model.Attribute`
    * `Shopware.apps.Order.model.Attribute`
    * `Shopware.apps.Order.model.BillingAttribute`
    * `Shopware.apps.Order.model.PositionAttribute`
    * `Shopware.apps.Order.model.ReceiptAttribute`
    * `Shopware.apps.Order.model.ShippingAttribute`
* The following repository methods no longer select attributes or have been removed entirely
    * `\Shopware\Models\Article\Repository::getSupplierQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getCustomerDetailQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getShippingAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getShippingAttributesQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getBillingAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getBillingAttributesQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getAttributesQueryBuilder()`
    * `\Shopware\Models\Blog\Repository::getBackedDetailQueryBuilder()`
    * `\Shopware\Models\Emotion\Repository::getEmotionDetailQueryBuilder()`
    * `\Shopware\Models\ProductFeed\Repository::getDetailQueryBuilder()`
    * `\Shopware\Models\Banner\Repository::getBannerMainQuery()`
    * `\Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder()`
    * `\Shopware\Models\Order\Repository::getBackendAdditionalOrderDataQuery()`
* Removed attribute associations from the following backend models
    * `Shopware.apps.Supplier.model.Supplier`
    * `Shopware.apps.Customer.model.Customer`
    * `Shopware.apps.Blog.model.Detail`
    * `Shopware.apps.Form.model.Form`
    * `Shopware.apps.Property.model.Set`
    * `Shopware.apps.MediaManager.model.Media`
    * `Shopware.apps.Emotion.model.Emotion`
    * `Shopware.apps.Config.model.form.Country`
    * `Shopware.apps.Banner.model.BannerDetail`
    * `Shopware.apps.Voucher.model.Detail`
    * `Shopware.apps.Order.model.Receipt`
    * `Shopware.apps.Order.model.Position`
    * `Shopware.apps.Order.model.Order`
* Removed the following backend files:
    * `themes/Backend/ExtJs/backend/blog/view/blog/detail/sidebar/attributes.js`
    * `themes/Backend/ExtJs/backend/config/store/form/attribute.js`
    * `themes/Backend/ExtJs/backend/config/view/form/attribute.js`
    * `themes/Backend/ExtJs/backend/config/model/form/attribute.js`
* Changed position of `Shopware.apps.Customer.view.detail.Billing` fields
* Changed position of `Shopware.apps.Customer.view.detail.Shipping` fields
* Fixed Shopware.form.plugin.Translation, the plugin can now be used in multiple forms at the same time.
    * Removed `clear`, `onOpenTranslationWindow`, `getFieldValues` and `onGetTranslatableFields` function
* `\Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface` requires now a provided `ShopContextInterface`
* Categories of `Shopware\Components\Api\Resource\Article::getArticleCategories($articleId)` are no longer indexed by category id
* Moved `<form>` element in checkout confirm outside the agreement box to wrap around address and payment boxes
* Removed smarty variable `sCategoryInfo` in listing and blog controllers. Use `sCategoryContent` instead.
* Added creation of custom `__construct()` method to `Shopware\Components\Model\Generator`, which initializes any default values of properties when generating attribute models
* Removed `sAdmin::sUpdateAccount()`
* Removed `saveAccount()` in `Controllers/Frontend/Account.php`
* Moved field `birthday` from billing address to customer
* Added validation of order number to `Shopware\Components\Api\Resource\Variant::prepareData()` to respond with meaningful error message for duplicate order numbers
* Added service `shopware.number_range_manager` for safely retrieving the next number of a number range (`s_order_number`)
* Changed the following methods to use the `shopware.number_range_manager` service for retrieving the next number of a range:
    * `sAdmin::assignCustomerNumber()`
    * `sOrder::sGetOrderNumber()`
    * `Shopware_Components_Document::saveDocument()`
* HttpCache: Added possibility to add multiple, comma separated proxy URLs
* API cache endpoint: Changed batchDelete in a way, that multiple cache types can be invalidated
* Removed `landingPageTeaser` and `landingPageBlock` fields from emotion shopping worlds.
* Removed unnecessary method `getCampaignByCategoryQuery()` from `Models/Emotion/Repository.php`.
* Removed template blocks for campaign boxes corresponding to the removed emotion fields.
    * `frontend_index_left_campaigns_top`
    * `frontend_index_left_campaigns_middle`
    * `frontend_index_left_campaigns_bottom`
    * `frontend_blog_index_campaign_top`
    * `frontend_blog_index_campaign_middle`
    * `frontend_blog_index_campaign_bottom`
* Removed unnecessary template file for campaign boxes `frontend/campaign/box.tpl`.
* Removed third party jQuery plugin dependency `masonry`.
* Deprecated `initMasonryGrid` method and `plugin/swEmotion/onInitMasonryGrid` event in `jquery.emotion.js`
* Removed shopping world mode `masonry`. The fallback is the new mode `fluid`.
* Replaced old LESS mixin `createColumnSizes` for new grid mixins `createGrid` and `createColumns` in `_components/emotion.less`.
* Added new blocks to `widgets/emotion/index.tpl` for better overriding of the configuration.
    * `widgets/emotion/index/config`
    * `widgets/emotion/index/attributes`
    * `widgets/emotion/index/element/config`
* Changed markup and styling on checkout confirm and finish page
* Support arbitrary namespaces for doctrine entities instead of the `Shopware\CustomModels` namespace.
* Deprecated `Shopware()->Models()->__call()`
* Removed unused database fields `s_core_config_elements.filters`, `s_core_config_elements.validators`, `s_core_config_forms.scope`
* Removed deprecated `\Shopware\Models\Menu\Repository::save()` and `\Shopware\Models\Menu\Repository::addItem()`
* Removed event `Shopware_Modules_Order_SaveOrderAttributes_FilterSQL`
* Removed event `Shopware_Modules_Order_SaveOrderAttributes_FilterDetailsSQL`
* Removed event `Shopware_Modules_Order_SaveBillingAttributes_FilterSQL`
* Removed event `Shopware_Modules_Order_SaveBillingAttributes_FilterArray`
* Removed event `Shopware_Modules_Admin_SaveRegisterShippingAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterShippingAttributes_Return`
* Removed event `Shopware_Modules_Admin_SaveRegisterBillingAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterBillingAttributes_Return`
* Removed event `Shopware_Modules_Admin_SaveRegisterMainDataAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterMainDataAttributes_Return`
* The filter event `Shopware_Modules_Order_SaveBilling_FilterArray` now contains an associative array instead of one with numeric keys.
* The filter event `Shopware_Modules_Order_SaveBilling_FilterSQL` now uses named parameters in the query instead of question marks.
* The filter event `Shopware_Modules_Order_SaveShipping_FilterArray` now contains an associative array instead of one with numeric keys.
* The filter event `Shopware_Modules_Order_SaveShipping_FilterSQL` now uses named parameters in the query instead of question marks.
* Moved `s_articles_prices.baseprice` to `s_articles_details.purchaseprice`
    * Added new database field `s_articles_details.purchaseprice`.
    * Added property `purchasePrice` to `Shopware\Models\Article\Detail`.
    * Removed property `basePrice` of `Shopware\Models\Article\Price`.
    * Removed methods `Shopware\Models\Article\Price::getBasePrice()` and `Shopware\Models\Article\Price::setBasePrice()`.
    * Deprecated database field `s_articles_prices.baseprice`. All data is left intact but this field is not used in shopware anymore and will be dropped in a future version.
    * Removed property `basePrice` of `Shopware\Models\Article\Configurator\Template\Price`.
    * Removed database field `s_article_configurator_template_prices.baseprice`.
* Removed unused class `Shopware_Components_Menu_Item` and `Shopware_Components_Menu_SaveHandler_DbTable`
* Removed database fields
    * `s_core_menu.hyperlink`
    * `s_core_menu.style`
    * `s_core_menu.resourceID`
* Removed method `Shopware\Models\Menu\Menu::setStyle()` and `Shopware\Models\Menu\Menu::getStyle()`
* Removed class `Shopware_Models_Payment`
* Removed class `Shopware_Models_PaymentManager`
* Removed `Shopware_Plugins_Frontend_Payment_Bootstrap`, Service: `Shopware()->Payments()`
* Removed following methods:
    * \Shopware_Controllers_Frontend_Register::saveRegister
    * \Shopware_Controllers_Frontend_Register::personalAction
    * \Shopware_Controllers_Frontend_Register::savePersonalAction
    * \Shopware_Controllers_Frontend_Register::billingAction
    * \Shopware_Controllers_Frontend_Register::saveBillingAction
    * \Shopware_Controllers_Frontend_Register::shippingAction
    * \Shopware_Controllers_Frontend_Register::saveShippingAction
    * \Shopware_Controllers_Frontend_Register::paymentAction
    * \Shopware_Controllers_Frontend_Register::savePaymentAction
    * \Shopware_Controllers_Frontend_Register::validatePersonal
    * \Shopware_Controllers_Frontend_Register::setRegisterData
    * \Shopware_Controllers_Frontend_Register::validateBilling
    * \Shopware_Controllers_Frontend_Register::validateShipping
    * \Shopware_Controllers_Frontend_Register::validatePayment
    * \sAdmin::sSaveRegisterMainData
    * \sAdmin::sSaveRegisterNewsletter
    * \sAdmin::sSaveRegisterBilling
    * \sAdmin::sSaveRegisterShipping
    * \sAdmin::sSaveRegister
    * \sAdmin::validateRegistrationFields
    * \sAdmin::assignCustomerNumber
    * \sAdmin::logRegistrationMailException
* Removed the following events:
    * Shopware_Modules_Admin_SaveRegisterMainData_FilterSql
    * Shopware_Modules_Admin_SaveRegisterMainData_Return
    * Shopware_Modules_Admin_SaveRegisterMainData_Return
    * Shopware_Modules_Admin_SaveRegisterBilling_FilterSql
    * Shopware_Modules_Admin_SaveRegisterBilling_Return
    * Shopware_Modules_Admin_SaveRegisterShipping_FilterSql
    * Shopware_Modules_Admin_SaveRegisterShipping_Return
    * Shopware_Modules_Admin_SaveRegister_Start
    * Shopware_Modules_Admin_SaveRegister_GetCustomerNumber
    * Shopware_Modules_Admin_SaveRegister_FilterNeededFields
    * Shopware_Modules_Admin_SaveRegister_FilterErrors
* Shopware_Modules_Admin_SaveRegister_Successful contains no more subject
* Changed following registration templates
    * frontend/register/index.tpl
    * frontend/register/shipping_fieldset.tpl
    * frontend/register/personal_fieldset.tpl
    * frontend/register/error_messages.tpl
    * frontend/register/billing_fieldset.tpl
* Moved s_user_billingaddress.customernumber to s_user table
* Removed \Shopware\Models\Customer\Billing::number property
* Removed method `Shopware\Bundle\PluginInstallerBundle\Service\InstallerService::getPluginBootstrap()`
* Changed Shopware\Components\Model\ModelManager::addAttribute() to allow using empty string and boolean as default value when adding attribute fields
* Removed s_categories.noviewselect
* Removed \Shopware\Models\Category\Category::$noViewSelect
* Removed \Shopware\Bundle\StoreFrontBundle\Struct\Category::$allowViewSelect
* Include the departments, salutations, cities and countries in the address comparison of the backend order details
* Display the departments in the backend order details overview
* Added new API resource 'Country' and respective REST API controller 'countries'
* Renamed input fields in `themes/Frontend/Bare/frontend/account/reset_password.tpl` with surrounding `password[]`
* Removed method `\Shopware_Controllers_Frontend_Account::validatePasswordResetForm()`
* Removed method `\Shopware_Controllers_Frontend_Account::resetPassword()`
* Changed structure of `billing` and `shipping` to `\Shopware\Models\Customer\Address` in `\Shopware\Components\Api\Resource\Customer`
* Added filter events to all convert functions in `LegacyStructConverter`

## 5.1.6
* The interface `Enlight_Components_Cron_Adapter` in `engine/Library/Enlight/Components/Cron/Adapter.php` got a new method `getJobByAction`. For default implementation see `engine/Library/Enlight/Components/Cron/Adapter/DBAL.php`.

## 5.1.5
* The smarty variable `sCategoryInfo` in Listing and Blog controllers is now deprecated and will be removed soon. Use `sCategoryContent` instead, it's a drop in replacement.

## 5.1.4
* Customer logout will now regenerate the session id and clear the customers basket.
* Added `IsNew` condition for product streams
* Added `SimilarProducts` condition
* Deprecated Method `Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface::getListByCategory` will be removed in shopware version 5.3
* Deprecated Method `Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface::getByCategory` will be removed in shopware version 5.3
* Added method `\Shopware\Models\Article\Repository::getSupplierListQueryBuilder()` to make the query builder extensible
* Added index on `s_article_img_mapping_rules`.`mapping_id` and `s_article_img_mapping_rules`.`option_id`
* Fixed `AND` search logic for search terms which not exist in the s_articles table.
* Added order and payment state constants in `\Shopware\Models\Order\Status`
* change email validation to a simple regex: `/^.+\@\S+\.\S+$/`. You can implement your own email validation by implementing the `EmailValidatorInterface`.
* Optimized header lookups for `x-shopware-cache-id` will improve HTTP-Cache invalidation performance. Old behaviour can be restored by setting `lookup_optimization` to false
* Moved the `div` element in block `frontend_index_left_switches` below `ul` element for W3C compatability in `themes/Frontend/Bare/frontend/index/sidebar.tpl`.
* Added css rule in order to remove bottom border from last child of `.emotion--html > .html--content` so there is no scrollbar when only whitespace would overlap parent div
* Enabled product streams for parent categories
* Disabled the automatic detection of similar products for install customers. Enabling this option may decrease the shop performance.
* Fixed the `removeListener` method in `Enlight_Event_Subscriber_Config`, `Enlight_Event_Subscriber_Array` and `Enlight_Event_EventManager`
* Removed `engine/Shopware/Bundle/SearchBundleES/SimilarProductsService.php`
* Added the possibility to configure the file and directory permissions for the `Local` CDN adapter.

## 5.1.3
* Switch Grunt to relativeUrls to unify the paths to less.php
* Deprecated `Enlight_Application::getOption()` and `Enlight_Application::getOptions`
* Renamed smarty block from `rontend_index_start` to `frontend_index_start` in `themes/Frontend/Bare/frontend/sitemap/index.tpl`
* Added new global snippets before (`priceDiscountLabel`) and after (`priceDiscountInfo`) all pseudo prices for the possibility to provide more detailed information
* Removed old snippet `reducedPrice` in `order_item_details.tpl` and `compare/col.tpl`.
* Introduced smarty blocks for footer headlines in `themes/Frontend/Bare/frontend/index/footer-navigation.tpl`. New blocks:
    * `frontend_index_footer_column_service_hotline_headline`
    * `frontend_index_footer_column_service_menu_headline`
    * `frontend_index_footer_column_information_menu_headline`
    * `frontend_index_footer_column_newsletter_headline`
* Removed out-of-stock variant selection due to problems

## 5.1.2
* Out-of-stock variants on the detail page are now selectable
* `ProductNumberService::getAvailableNumber()` now returns the provided product variant to allow deep linking of out-of-stock variants
* Added new configuration property to shopware themes, to configure the inheritance position before or after plugins.
* Added new smarty blocks `frontend_index_left_menu_entries` and `frontend_index_left_menu_container` to `index/sites-navigation.tpl`.
* Removed usage of the deprecated `Doctrine\Common\Annotations\FileCacheReader`. Removed methods:
    * `\Shopware\Components\CacheManager::getDoctrineFileCacheInfo()`
    * `\Shopware\Components\Model\Configuration::setFileCacheDir()`
    * `\Shopware\Components\Model\Configuration::getFileCacheDir()`
* Added `timed_delivery` column in `s_campaigns_mailings` for automatic newsletter delivering.
* Added new Smarty blocks to the `index/index.tpl` file
    * `frontend_index_body_classes`
    * `frontend_index_page_wrap`
    * `frontend_index_header_navigation`
    * `frontend_index_container_ajax_cart`
    * `frontend_index_content_main`
* Removed `extendsTemplate()` method in the AdvancedMenu plugin. Now the template uses the normal `{extends}` action.
* Moved template file of the AdvancedMenu plugin from the plugin directory to the normal index directory.
* Moved content for the AdvancedMenu to separate include file.
* Added new Smarty block for extending the complete AdvancedMenu template `frontend_plugins_advanced_menu_outer`.
* Removed obsolete config options `displayFiltersOnDetailPage` and `propertySorting`
* Add sub shop validation in \Shopware\Bundle\StoreFrontBundle\Service\Core\ListProductService
* Add getProductsCategories function to \Shopware\Bundle\StoreFrontBundle\Service\CategoryServiceInterface and \Shopware\Bundle\StoreFrontBundle\Gateway\CategoryGatewayInterface which returns all categories of the provided products.
* Removed duplicate content in `frontend/detail/data.tpl` for block prices
* Marked unnecessary block `frontend_detail_data_price_info` as deprecated
* Added seo title for landing pages
* Added config option to force the selection of a payment method in checkout
* Deprecated table column `s_filter_values.value_numeric`
* `\Shopware\Bundle\SearchBundleDBAL\PriceHelper::getSelection()` now requires a `ProductContext` instead of a `ShopContext`
* Changed type of `\Shopware\Models\Article\Detail::$active` to `boolean` (was `integer`)
* Emotions can be assigned to multiple categories. Author: Christiansen <t.christiansen@reply.de> via GitHub.
* Allow clearing caches after plugin update
* Added Event `Shopware_Modules_Admin_Execute_Risk_Rule_[RuleName]` to provide checks for custom risk rules
* Added sValidation parameter to the frontend/register/login.tpl url to redirect to same login page in case the login validation failed.
* Default media albums are now editable in their name. The negative ids are still compatible and fix for development checks and assignments.
* Add constants for default album ids in \Shopware\Models\Media\Album
* Replaced `FuzzyLikeThisFieldQuery` with `MultiMatchQuery` in ES Product Search implementation. Should now be compatible to versions >= 1.6
* Removed deprecated Google Analytics config form and s_core_plugins entry.
* Removed config option `fuzzysearchresultsperpage`, use `articlesPerPage` instead.

## 5.1.1
* Added new smarty block `frontend_detail_index_tabs_cross_selling` in the detail/ajax.tpl to prevent problems with custom themes
* Renamed block `backend/order/view/detail/communication` in `backend/order/view/detail/configuration.js` to `backend/order/view/detail/configuration`. The name was duplicated in another file and was renamed to match the correct file.

## 5.1.0
* Added event `Shopware_Plugin_Collect_MediaXTypes` to collect media related x_type fields for which the value needs to be normalized
* Updated Behat to v3.0 and other related libraries

## 5.1.0 RC3
* Activated media fallback by default so that old media paths get resolved to the new location

## 5.1.0 RC2
* Update ongr/elasticsearch-dsl to version 1.0.0-RC1
* Update elasticsearch/elasticsearch to version 2.0.0
    * See: https://www.elastic.co/guide/en/elasticsearch/reference/2.0/breaking-changes-2.0.html
* The MediaBackend and PathNormalizer have been moved into the MediaService
* The media live migration is now enabled by default
* Added new Smarty block `frontend_index_header_javascript_tracking` for tracking codes which are required to be included into the "head" section of the document
* Upgraded mPDF to version 6.0
* Removed `Zend_Json_Server` related classes

## 5.1.0 RC1
* Removed unused classes `Shopware_Components_Test_MailListener` and `Shopware_Components_Test_TicketListener`
* Removed unused snippets in `snippets/backend/article/view/main.ini`:
    * `detail/sidebar/options/article_options`
    * `detail/sidebar/options/article_preview`
    * `detail/sidebar/options/columns/name`
    * `detail/sidebar/options/delete`
    * `detail/sidebar/options/drop_zone`
    * `detail/sidebar/options/duplicate`
    * `detail/sidebar/options/image_field_set`
    * `detail/sidebar/options/rapid_categorization`
    * `detail/sidebar/options/select_category`
    * `detail/sidebar/options/selected_categories`
    * `detail/sidebar/options/shop`
    * `detail/sidebar/options/title`
    * `detail/sidebar/options/tooltip`
    * `detail/sidebar/options/translate`
    * `detail/sidebar/options/upload_button`
* Removed `Shopware.apps.Article.view.detail.sidebar.Link` backend component and created two new components instead:
    * `Shopware.apps.Article.view.resources.Downloads`
    * `Shopware.apps.Article.view.resources.Links`
* Removed unused file: `themes/Backend/ExtJs/backend/article/view/detail/sidebar/link.js`
* Removed `article-sidebar-link` object in `Shopware.apps.Article.view.detail.Sidebar`
* Changed views in `Shopware.apps.Article`:
    * Removed `detail.sidebar.Link`
    * Added `resources.Links`
    * Added `resources.Downloads`
* Changed events in `Shopware.apps.Article.controller.Detail`:
    * Removed `article-detail-window article-sidebar-link` event alias
    * Added `article-detail-window article-resources-links` event alias
    * Added `article-detail-window article-resources-downloads` event alias
* Changed grid reconfigures in `Shopware.apps.Article.controller.Detail`:
    * Removed `mainWindow.down('article-sidebar article-sidebar-link grid[name=link-listing]').reconfigure(article.getLink());`
    * Removed ` mainWindow.down('article-sidebar article-sidebar-link grid[name=download-listing]').reconfigure(article.getDownload());`
    * Added `mainWindow.down('article-resources-links grid[name=link-listing]').reconfigure(article.getLink());`
    * Added `mainWindow.down('article-resources-links grid[name=link-listing]').reconfigure(article.getLink());`
* Removed unused snippets in `snippets/backend/article/view/main.ini`:
    * `detail/sidebar/links/download/button`
    * `detail/sidebar/links/download/field_set`
    * `detail/sidebar/links/download/grid/delete`
    * `detail/sidebar/links/download/grid/edit`
    * `detail/sidebar/links/download/grid/title`
    * `detail/sidebar/links/download/link`
    * `detail/sidebar/links/download/name`
    * `detail/sidebar/links/download/notice`
    * `detail/sidebar/links/link/button`
    * `detail/sidebar/links/link/field_set`
    * `detail/sidebar/links/link/grid/delete`
    * `detail/sidebar/links/link/grid/edit`
    * `detail/sidebar/links/link/grid/external`
    * `detail/sidebar/links/link/grid/title`
    * `detail/sidebar/links/link/link`
    * `detail/sidebar/links/link/name`
    * `detail/sidebar/links/link/notice`
    * `detail/sidebar/links/title`
* Removed `article-detail-window article-sidebar-option` event listeners in `Shopware.apps.Article.controller.Media`
* Removed `onSidebarMediaUpload` method in `Shopware.apps.Article.controller.Media`
* Removed `addCategory: me.onAddCategory` event listener in `Shopware.apps.Article.controller.Detail`
* Removed `onAddCategory` method in `Shopware.apps.Article.controller.Detail`
* Changed event listener alias from `article-detail-window article-sidebar-option` to `article-detail-window article-actions-toolbar` in `Shopware.apps.Article.controller.Detail`
* Removed `article-sidebar-option` object in `Shopware.apps.Article.view.detail.Sidebar`
* Removed `detail.sidebar.Option` from `views` array in `Shopware.apps.Article`
* Removed `Shopware.apps.Article.view.detail.sidebar.Option` backend component
* Removed `onTranslate` method from `Shopware.apps.Article.controller.Detail`
* Removed unused snippets in `snippets/backend/article/view/main.ini`:
    * `detail/sidebar/accessory/article_number`
    * `detail/sidebar/accessory/article_search`
    * `detail/sidebar/accessory/assignment_box`
    * `detail/sidebar/accessory/assignment_field`
    * `detail/sidebar/accessory/bundle_box`
    * `detail/sidebar/accessory/bundle_field`
    * `detail/sidebar/accessory/delete`
    * `detail/sidebar/accessory/edit`
    * `detail/sidebar/accessory/name`
    * `detail/sidebar/accessory/number`
* Removed all unprefixed jQuery plugin events of the core plugins that were deprecated in the 5.0.2 Release.
* Removed the jQuery plugin events and added new prefixed values in these core plugins:
    * `jquery.lightbox.js`
    * `jquery.loading-indicator.js`
    * `jquery.modal.js`
    * `jquery.advancedMenu.js`
* Move directory `logs/` to `var/log/` and `cache/` to `var/cache`
* The property selection for an product is now a dedicated tab.
    * The `beforeedit` event will now be triggered on the `article-detail-window grid[name=property-grid]`
    * The property selection was built from scratch to provide a better user experience but the method names haven't changed
    * The store `Shopware.apps.Article.store.PropertyValue` and the associated model `Shopware.apps.Article.model.PropertyValue` are not available anymore
    * Property values are now creates on demand with an AJAX request
    * The selection of property values is now on select to allow for a faster usage of the component
* Added library [beberlei/assert](https://github.com/beberlei/assert) for low-level validation.
* Changed returning array keys to `ordernumber` in `\sArticles::sGetArticlesByCategory` which introduces an BC break for plugins hooking into the following methods:
    * `\Shopware_Controllers_Frontend_Listing::manufacturerAction`
    * `\Shopware_Controllers_Frontend_Listing::indexAction`
    * `\Shopware_Controllers_Widgets_Listing::ajaxListingAction`
    * `\sArticles::sGetArticlesByCategory`
* Added Escaper component to escape output data, dependent on the context in which the data will be used
    * Added library [zendframework/zend-escaper](https://github.com/zendframework/zend-escaper)
    * New interface: `\Shopware\Components\Escaper\EscaperInterface`
    * Default implementation: `\Shopware\Components\Escaper\Escaper`, uses `Zend\Escaper`
    * Available in DI-Container: `shopware.escaper`
    * Smarty Modifiers:
        * escapeHtml
        * escapeHtmlAttr
        * escapeJs
        * escapeUrl
        * escapeCss
* The following basic settings values are deprecated and will be removed in Shopware 5.2. Their corresponding snippet values from the `backend/static/discounts_surcharges` namespace should be used instead:
    * `discountname`
    * `paymentSurchargeAbsolute`
    * `paymentsurchargeadd`
    * `paymentsurchargedev`
    * `shippingdiscountname`
    * `surchargename`
    * `vouchername`
* Added the ability to change variants using an AJAX call, therefore the page won't reload anymore. The configuration can be enabled / disabled in theme config, the default value is `true`, therefore the variants will be loaded via AJAX by default.
    * Plugin developers which supports variants needs to change their plugin accordingly. They can subscribe to the event `plugin/swAjaxVariant/onRequestData` to update their plugin.
    * If your plugin modifies the product page using Smarty, you're good to go.
* Deprecated pre-installed import / export module in favor of the new import / export plugin, which is for free now
* Changed the way in which order and payment status translations are handled:
    * Added `Shopware\Models\Order\Status::name`. Its value should be matched to the corresponding snippet name in one of the `backend/base/model/order_status/*` namespaces
    * Deprecated `Shopware\Models\Order\Status::description`
    * `Shopware.apps.Base.model.OrderStatus` and `Shopware.apps.Base.model.PaymentStatus` ExtJs translations are now done using the `name` instead of the `id`.
* Deprecated `Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail::getSourceSet` since it should be placed in a hydrator or view
* Introducing the `MediaBundle` to support huge amounts of media items and add support for CDN's (Content Delivery Network)
	* Added library [thephpleague/flysystem](https://github.com/thephpleague/flysystem) to switch the underlying filesystem.
	* Media directory structure has been changed
		* Paths in `s_media` are now virtual paths, meaning that the files will no longer be accessible with the given path.
		* A MediaBackend decides how and where media files are getting stored (e.g. /media/image/blue_shoes_size37.jpg could be /media/image/e0/77/f8/blue_shoes_size37.jpg)
		* A MediaService handles file operations and generation of urls
		* A MediaPathNormalizer removes all unrelevant parts of a string to get a coherent syntax like `media/image/blue_shoes_size37.jpg`
		* A live migration, which is disabled by default, migrates media files to the new filesystem and format as they get requested
		* The store front, product feed and api endpoints have already been updated to make use of the underlying filesystem.
	* Added `sw:media` cli commands to easily manage your new media system
		* `sw:media:migrate` migrates from one filesystem to another
		    * E.g. use `sw:media:migrate --from=local --to=aws` to migrate all media items to Amazon S3
* Added `sw:media:cleanup` cli command to find all unused media and place them in a new album called Trash
    * Optional: `sw:media:cleanup --delete` to find all unused media and remove them automatically
* Added event `Shopware_Collect_MediaPositions` to collect more tables to scan for unused images. You should return a ArrayCollection of MediaPosition instances.
* Added configuration option 'errorHandler.throwOnRecoverableError'. When set to true errors of type `E_RECOVERABLE_ERROR` will result in an exception. This is useful to test PHP 7 compatiblity.
* Fixed duplicated jQuery event in `jquery.listing-actions.js` from `plugin/listingActions/onApplyUrlParams` to `plugin/swListingActions/onGetListingUrl`
* Removed previously deprecated `Enlight_Components_Adodb`
    * Container Key `AdoDb` / Shopware()->Adodb()
    * `sSystem::$sDB_CONNECTION`
    * `Shopware_Plugins_Core_System_Bootstrap::onInitResourceAdodb()`
    * `Enlight_Components_Adodb`
    * `Enlight_Components_Adodb_Statement`
* Removed previously deprecated search classes:
    * `Shopware_Components_Search`
    * `Shopware_Components_Search_Adapter_Default`
    * `Shopware_Components_Search_Adapter_Abstract`
    * `Shopware_Components_Search_Result_Default`
    * `Shopware_Components_Search_Result_Abstract`
    * `Shopware_Components_Search_Result_Interface`
* Removed previously deprecated API `Shopware()->Api()`
    * `Shopware_Plugins_Core_Api_Bootstrap`
    * `sCsvConvert`
    * `sShopwareExport`
    * `sShopwareImport`
    * `sMappingConvert`
    * `sXmlConvert`
* Removed previously deprecated table `s_core_multilanguage`
    * Table: `s_core_multilanguage`
    * `Shopware_Plugins_Core_System_Bootstrap::getSingleShopData()`
    * `sSystem::$sSubShop`
* Removed previously deprecated Plugin Bootstrap methods
    * `Shopware_Components_Plugin_Bootstrap::deleteForm()`
    * `Shopware_Components_Plugin_Bootstrap::deleteConfig()`
    * `Shopware_Components_Plugin_Bootstrap::createHook()`
    * `Shopware_Components_Plugin_Bootstrap::subscribeCron()`
    * `Shopware_Components_Plugin_Bootstrap::createEvent()`
* Removed previously deprecated or unused classes / methods
    * `sSystem::E_CORE_WARNING()`
    * `sSystem::$sExtractor`
    * `sSystem::$sPathArticleFiles`
    * `sCore::rewriteLink()`
    * `Shopware_Bootstrap::run()`
    * `Shopware_Controllers_Backend_ExtJs::setAclResourceName()`
    * `Shopware\Models\Property\Repository::getGroupsQueryBuilder`
    * `Shopware\Models\Property\Repository::getGroupsQuery`
    * `Shopware_Plugins_Frontend_CronRefresh_Bootstrap::onCronJobTranslation()`
    * `Shopware_Components_DummyPlugin_Bootstrap`

## 5.0.4
* Change file extension of `Shopware_Components_Convert_Excel::generateXML` to .xls
* Fixed jsonrenderer for backend order batchprocessing

## 5.0.3
* The variant API resource now supports the getList method. It will return all variants with prices and attributes. You can optionally calculate the gross price by using the "considerTaxInput" parameter.
* The getList method of the articles API resource now returns additionally the attributes of an article.
* Change event argument property `name` to `vouchername` in `Shopware_Modules_Basket_AddVoucher_FilterSql` in `sBasket.php` due to reserved word.
* Support for batch user deleting in Backend UserManager
* Added `createMediaField` to Emotion Component Model
* Added open graph and twitter meta tags to a new block `frontend_index_header_meta_tags_opengraph` in the `header.tpl`

## 5.0.2
* Method `createMenuItem` in plugin bootstrap now results in an duplicate error when passing an existing label with the same parent
* Removed `Shopware_Controllers_Backend_Order::getStatisticAction` and statistics in the order backend module.
* It's no longer possible to have spaces in article numbers. Existing articles with spaces in their numbers will still work, but the article cannot be changed without fixing the number.
* Change structure of `build-database` target in `build/build.xml` to allow a more fine grained build process.
* Introduce new configuration option `snippet.showSnippetPlaceholder`. Empty snippets are now hidden by default. If `showSnippetPlaceholder` is `true` snippet placeholders will be shown.
* Removed table `s_emarketing_vouchers_cashed`.
* 'Shopware.form.field.ArticleSearch' search using the "variants" option is deprecated. Use "configurator" to load configurator articles or "searchVariants" to load article variants with the correct additional text and ordernumber.
* Added column `added` to the table `s_campaigns_mailaddresses` which holds the date of the newsletter registration. It will be displayed in the newsletter administration under "Recipients" as the "Double-Opt-In date" column.
* Removed the expert layout and the corresponding mail form inside the batch processing window of the order backend module.
* Added support for attributes in backend module site
* Added a lot more jQuery plugin events.
* Marked some jQuery plugin events as deprecated which will be removed in the version 5.1. They were replaced with more conventional names:
    * plugin/collapseCart/afterRemoveArticle    => plugin/swCollapseCart/onRemoveArticleFinished
    * plugin/collapseCart/afterLoadCart         => plugin/swCollapseCart/onLoadCartFinished
    * plugin/collapseCart/onMouseLeave          => plugin/swCollapseCart/onMouseLeave
    * plugin/collapseCart/onCloseButton         => plugin/swCollapseCart/onCloseButton
    * plugin/collapseCart/onRemoveArticle       => plugin/swCollapseCart/onRemoveArticle
    * plugin/collapseCart/onMenuOpen            => plugin/swCollapseCart/onMenuOpen
    * plugin/collapseCart/onLoadCart            => plugin/swCollapseCart/onLoadCart
    * plugin/collapseCart/onCloseMenu           => plugin/swCollapseCart/onCloseMenu
    * plugin/collapsePanel/onOpen               => plugin/swCollapsePanel/onOpen
    * plugin/collapsePanel/onClose              => plugin/swCollapsePanel/onClose
    * plugin/filterComponent/onChange           => plugin/swFilterComponent/onChange
    * plugin/emotionLoader/loadEmotion          => plugin/swEmotionLoader/onLoadEmotion
    * plugin/emotionLoader/initEmotion          => plugin/swEmotionLoader/onInitEmotion
    * plugin/emotionLoader/showEmotion          => plugin/swEmotionLoader/onShowEmotion
    * plugin/emotionLoader/hideEmotion          => plugin/swEmotionLoader/onHideEmotion
    * plugin/emotionLoader/showFallbackContent  => plugin/swEmotionLoader/onShowFallbackContent
    * plugin/emotionLoader/hideFallbackContent  => plugin/swEmotionLoader/onHideFallbackContent
    * plugin/emotion/initElements               => plugin/swEmotion/onInitElements
    * plugin/emotion/initFullscreen             => plugin/swEmotion/onInitFullscreen
    * plugin/emotion/removeFullscreen           => plugin/swEmotion/onRemoveFullscreen
    * plugin/emotion/initMasonryGrid            => plugin/swEmotion/onInitMasonryGrid
    * plugin/emotion/initScaleGrid              => plugin/swEmotion/onInitScaleGrid
    * plugin/emotion/registerEvents             => plugin/swEmotion/onRegisterEvents
    * plugin/imageSlider/updateTransform        => plugin/swImageSlider/onUpdateTransform
    * plugin/imageSlider/slide                  => plugin/swImageSlider/onSlide
    * plugin/imageSlider/slideNext              => plugin/swImageSlider/onSlideNext
    * plugin/imageSlider/slidePrev              => plugin/swImageSlider/onSlidePrev
    * plugin/menuScroller/updateResize          => plugin/swMenuScroller/onUpdateResize
    * plugin/offcanvasMenu/beforeOpenMenu       => plugin/swOffcanvasMenu/onBeforeOpenMenu
    * plugin/offCanvasMenu/openMenu             => plugin/swOffcanvasMenu/onOpenMenu
    * plugin/offCanvasMenu/closeMenu            => plugin/swOffcanvasMenu/onCloseMenu
    * plugin/-PLUGIN_NAME-/init                 => plugin/-PLUGIN_NAME-/onInit (PluginBase)
    * plugin/-PLUGIN_NAME-/destroy              => plugin/-PLUGIN_NAME-/onDestroy (PluginBase)
    * plugin/-PLUGIN_NAME-/on                   => plugin/-PLUGIN_NAME-/onRegisterEvent (PluginBase)
    * plugin/-PLUGIN_NAME-/off                  => plugin/-PLUGIN_NAME-/onRemoveEvent (PluginBase)
    * plugin/productSlider/trackItems           => plugin/swProductSlider/onTrackItems
    * plugin/productSlider/trackArrows          => plugin/swProductSlider/onTrackArrows
    * plugin/productSlider/itemsLoaded          => plugin/swProductSlider/onLoadItemsSuccess
    * plugin/productSlider/loadItems            => plugin/swProductSlider/onLoadItems
    * plugin/productSlider/createContainer      => plugin/swProductSlider/onCreateContainer
    * plugin/productSlider/createArrows         => plugin/swProductSlider/onCreateArrows
    * plugin/productSlider/slideNext            => plugin/swProductSlider/onSlideNext
    * plugin/productSlider/slidePrev            => plugin/swProductSlider/onSlidePrev
    * plugin/productSlider/slideToElement       => plugin/swProductSlider/onSlideToElement
    * plugin/productSlider/slide                => plugin/swProductSlider/onSlide
    * plugin/productSlider/autoSlide            => plugin/swProductSlider/onAutoSlide
    * plugin/productSlider/stopAutoSlide        => plugin/swProductSlider/onStopAutoSlide
    * plugin/productSlider/scrollNext           => plugin/swProductSlider/onScrollNext
    * plugin/productSlider/scrollPrev           => plugin/swProductSlider/onScrollPrev
    * plugin/productSlider/autoScroll           => plugin/swProductSlider/onAutoScroll
    * plugin/productSlider/stopAutoScroll       => plugin/swProductSlider/onStopAutoScroll
    * plugin/productSlider/buffer               => plugin/swProductSlider/onBuffer
    * plugin/rangeSlider/changeMin              => plugin/swRangeSlider/onSetMin
    * plugin/rangeSlider/changeMax              => plugin/swRangeSlider/onSetMax
    * plugin/rangeSlider/reset                  => plugin/swRangeSlider/onReset
    * plugin/rangeSlider/onChange               => plugin/swRangeSlider/onEndDrag
* Added new validation rules for snippets
    * Use `bin/console sw:snippets:validate <your-plugin-snippets-path>` to check the validity of your snippets.
    * Defining a snippet value in multiple lines is deprecated.
    * All snippet values that don't pass the validation should be refactored.
* The method `getSeoArticleQuery` in `sRewriteTable.php` was changed to select the translations for the article attributes.

## 5.0.1
* Create `sw:theme:dump:configuration` command to generate watch files for theme compiling
* Rename \Shopware\Components\Theme\Compiler::preCompile to \Shopware\Components\Theme\Compiler::compile
* Change the following \Shopware\Components\Theme\Compiler functions visibility to private:
    * compilePluginCss
    * clearThemeCache
    * buildConfig
    * compilePluginLess
    * compilePluginCss
    * compressPluginJavascript
    * clearDirectory
    * createThemeJavascriptFile
* Changed \Shopware\Components\Theme\PathResolver functions: getJsFilePaths and getCssFilePaths
    * Renamed to singular naming, getJsFilePath and getCssFilePath
    * Returning directly the `default` file path
* Add themes/Gruntfile.js for local compiling.
* \Shopware\Bundle\SearchBundle\Condition\HasPriceCondition marked as deprecated.
* Add \Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory::createBaseCriteria function to create a default criteria with all validation conditions.
* Moved the mixins `primary-gradient`, `secondary-gradient` and `white-gradient` back to the Responsive theme
    * We moved the variable declaration of `remScaleFactor` to the Bare theme.
    * If you have created a custom theme based on the Bare theme and used the mentioned mixins, you need to modify the used variables in the mixin to get it working.
    * The `icon-element` mixin can now be found in the Responsive theme as well.

## 5.0.0
* \sArticles::sGetProductByOrdernumber result is now equals with the \sArticles::sGetPromotionById result.
* Add console command `sw:refresh:search:index` to manually regenerate the search index. The optional parameter `--clear-table` can be used to clear the index tables before regenerating the data again.
* Remove `price` field override in AjaxSearch controller.
* Include `frontend/listing/product-box/product-price.tpl` template in ajax search to display product prices.

## 5.0.0 RC3
* \Shopware\Bundle\SearchBundleDBAL\ConditionHandler\HasPriceConditionHandler now joins the prices as a 1:1 association for a performance improvement.
* sCategories::sGetCategoryContent function returns no more the category articleCount. Variable is unused.
* sCategories::sGetCategoryIdByArticleId function use now the s_articles_categories table.
* Add __redirect parameter in frontend language switcher. Each language switcher requires now an additionally post parameter to redirect to the new shop `<input type="hidden" name="__redirect" value="1">`

## 5.0.0 RC2
* SEO URL generation variable "statistic" has been translated and corrected to "static"
* Theme config elements can now define, over the attributes array, if they are less compatible. Example: `attributes => ['lessCompatible' => false]`, default is set to true.
* Implement plugin bootstrap helper functions: addHttpCacheRoute and removeHttpCacheRoute, to add and remove http cache routes.
* Refactor getRandomArticle function of sArticles. Shopware_Modules_Articles_GetPromotionById_FilterSqlRandom event removed.
* `Mark VAT ID number as required` moved to `Login / Registration` in `Basic Settings`. All other VAT ID validation options were removed. If you need VAT ID validation functionalities, please use the VAT ID Validation plugin available on the store.
    * `sAdmin::sValidateVat()` removed
* Removed supplier description on article detail page to prevent duplicated content for google remote crawling
* Fix duplicate name parameter for backend extjs stores inside the config module. Repository class name sent before as `name` parameter. Now the stores uses `_repositoryClass` as parameter.
* Removed shopware_storefront.product_gateway (\Shopware\Bundle\StoreFrontBundle\Gateway\ProductGatewayInterface).
* \Shopware\Bundle\StoreFrontBundle\Service\Core\ProductService uses now the ListProductService to load the product data and converts the product structs by loaded list products.
* Removed `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ProductHydrator::hydrateProduct` function.
* Removed \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct::STATE_TRANSLATED constant.
* Removed Service `guzzle_http_client`, use `guzzle_http_client_factory` instead.
* Added support for Bundle of CA Root Certificates. See: http://curl.haxx.se/docs/caextract.html.
* Removed `setField` and `setMode` function in \Shopware\Bundle\SearchBundle\Facet\ProductAttributeFacet.
* Removed unnecessary theme variable prefix for less compiler. Each theme config variable prefixed with "theme" . ucfirst($key) which generates @themeBrandPrimary. This variables were remapped inside responsive theme.

## 5.0.0 RC1
* New orders will no longer set `s_order.transactionID` automatically from POST data. 3rd party plugins can still use this value as before.
* Fix translation API, rename all `localeId` references to `shopId`. Create / update / delete with `localeId` are still supported as legacy.
* `\Shopware\Models\Translation\Translation` now correctly relates to `Shop` model instead of `Locale`.
* widgets/recommendations - boughtAction & viewedAction calls no more the sGetPromotionById function.
* Added emotion positioning number for ordering emotions by position number if there are more emotions on one page
* Replaced `closeOverlay` with `openOverlay` option in the loading indicator to improve the simplicity.
* Removed overlay options in the modal box and loading indicator jQuery plugin.
* Overlay jQuery plugin now only provides the closeOnClick, onClick and onClose options. To style the overlay, use the corresponding less file.
* Removed unused methods:
    * `Shopware_Controllers_Backend_Config::getTemplateListAction`
    * `Shopware_Controllers_Backend_Config::refreshTemplateList`
    * `Shopware_Controllers_Backend_Config::saveTemplateAction`
* Removed unused files:
    * `themes/Backend/ExtJs/backend/config/view/form/template.js`
    * `themes/Backend/ExtJs/backend/config/view/template/detail.js`
    * `themes/Backend/ExtJs/backend/config/view/template/preview.js`
    * `themes/Backend/ExtJs/backend/config/view/template/view.js`
    * `themes/Backend/ExtJs/backend/config/store/form/template.js`
    * `themes/Backend/ExtJs/backend/config/store/model/template.js`
    * `themes/Backend/ExtJs/backend/config/store/controller/template.js`
* Removed classes:
    * `ConfigIframe.php` backend controller
    * `Viewport.php` frontend controller
* Removed template files:
    * `backend\index\iframe.tpl`
* Removed commands `sw:store:download:update` and `sw:store:licenseplugin`.
* Added `sw:store:download` command to download install and updates of plugins.
* Added `sw:store:list:integrated` command to list all shopware 5 integrated plugins.
* Shopware.model.Container provides now the raw record value as id parameter to the searchAssociationAction to request the whole record on form load.
* Added way to early exit the dispatch.
    * After `Enlight_Controller_Front_RouteShutdown` a response containing a redirect will not enter the dispatch loop.
* `HttpCache` plugin is no longer handled by the Plugin manager. Use the `Performance` window to enable/configure the Http cache instead
* \Shopware\Models\Emotion\Repository::getListQuery function replaced by getListingQuery.

## 5.0.0 Beta 2
* Rename shopware_searchdbal.product_number_search to shopware_search.product_number_search. Use shopware_search.product_number_search service for number searchs.
* Remove aliases from bundle services. Example: list_product_service is now directly set to the old list_product_service_core
* Extend ProductAttributeFacet with different FacetResult properties, to allow full FacetResult configuration over the facet.
* Out of stock articles and variants are now not included in the product feed if the `Do not show on sale products that are out of stock ` option is enabled
* IonCube Loader version requirement bumped to 4.6.0 or higher
* Refactored routing component
    * Removed classes:
        * `Enlight_Controller_Router_Default`
        * `Enlight_Controller_Router_EventArgs`
    * Removed events:
        * `Enlight_Controller_Router_FilterAssembleParams`
        * `Enlight_Controller_Router_FilterUrl`
        * `Enlight_Controller_Router_Assemble`
    * Removed methods:
        * `Shopware_Plugins_Core_Router_Bootstrap::onFilterAssemble`
        * `Shopware_Plugins_Core_Router_Bootstrap::onFilterUrl`
        * `Shopware_Plugins_Core_Router_Bootstrap::onAssemble`
        * `Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onAfterSendResponse`
        * `Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onRoute`
        * `Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::onAssemble`
        * `Shopware_Plugins_Frontend_RouterRewrite_Bootstrap::sRewriteQuery`
* Shopware.grid.Panel executes now a local store search if no read url configured in the assigned store.
* Shopware.grid.Panel has now the RowEditing plugin inside the local variable rowEditor.

## 5.0.0 Beta 1
* Deprecated classes:
    * `Zend_Rest`
    * `Zend_Http`
    * `Enlight_Components_Adodb` (also accessed as `Shopware()->Adodb()` or `$system->sDB_CONNECTION`) will be removed in SW 5.1
    * `Shopware_Components_Search_Adapter_Default` is now deprecated, use `\Shopware\Bundle\SearchBundle\ProductNumberSearch`
    * `Zend_Validate_EmailAddress`
* Deprecated methods/variables:
    * `Shopware_Controllers_Frontend_Account::ajaxLoginAction()` is deprecated
    * `Shopware_Controllers_Frontend_Account::loginAction()` usage to load a login page is deprecated. Use `Shopware_Controllers_Frontend_Register::indexAction()` instead for both registration and login
    * `sSystem::sSubShop`
    * `sExport::sGetMultishop()`
    * `sExport::sLanguage`
    * `sExport::sMultishop`
* Deprecated configuration variables from `Basic settings`:
    * `basketHeaderColor`
    * `basketHeaderFontColor`
    * `basketTableColor`
    * `detailModal`
    * `paymentEditingInCheckoutPage`
    * `showbundlemainarticle`
* Deprecated tables/columns:
    * `s_core_multilanguage`. Table will be removed in SW 5.1. Previously unused fields `mainID`, `flagstorefront`, `flagbackend`, `separate_numbers`, `scoped_registration` and `navigation` are no longer loaded from the database
* Removed classes:
    * `Enlight_Components_Currency`
    * `Enlight_Components_Form` and subclasses
    * `Enlight_Components_Locale`
    * `Enlight_Components_Menu` and subclasses
    * `Enlight_Components_Site` and subclasses
    * `Enlight_Components_Test_Constraint_ArrayCount`
    * `Enlight_Components_Test_Database_TestCase`
    * `Enlight_Components_Test_Selenium_TestCase`
    * `Enlight_Components_Test_TestSuite`
    * `Enlight_Extensions_Benchmark_Bootstrap`
    * `Enlight_Extensions_Debug_Bootstrap`
    * `Enlight_Extensions_ErrorHandler_Bootstrap`
    * `Enlight_Extensions_Log_Bootstrap`
    * `Enlight_Extensions_Router_Bootstrap`
    * `Enlight_Extensions_RouterSymfony_Bootstrap`
    * `Enlight_Extensions_Site_Bootstrap`
    * `Enlight_Components_Log` (also accessed as `Shopware->Log()`)
* Removed methods/variables:
    * `sArticles::sGetAllArticlesInCategory()`
    * `sSystem::sSubShops`
    * `sSystem::sLanguageData`. Please use `Shopware()->Shop()` instead
    * `sSystem::sLanguage`. Please use `Shopware()->Shop()->getId()` instead
    * `Shopware_Plugins_Core_ControllerBase_Bootstrap::getLanguages()`
    * `Shopware_Plugins_Core_ControllerBase_Bootstrap::getCurrencies()`
    * `sExport::sGetLanguage()`
    * `Shopware_Controllers_Backend_Article::getConfiguratorPriceSurchargeRepository()`
    * `Shopware_Controllers_Backend_Article::saveConfiguratorPriceSurchargeAction()`
    * `Shopware_Controllers_Backend_Article::deleteConfiguratorPriceSurchargeAction()`
    * `Shopware_Controllers_Backend_Article::getArticlePriceSurcharges()`
    * `Shopware_Controllers_Backend_Article::getSurchargeByOptionId()`
    * `sArticles::sGetArticlesAverangeVote`
    * `sArticles::getCategoryFilters`
    * `sArticles::getFilterSortMode`
    * `sArticles::addFilterTranslation`
    * `sArticles::sGetArticleConfigTranslation`
    * `sArticles::sGetArticlesByName`
    * `sArticles::sGetConfiguratorImage`
    * `sArticles::sCheckIfConfig`
    * `sArticles::getCheapestVariant`
    * `sArticles::calculateCheapestBasePriceData`
    * `sArticles::displayFiltersOnArticleDetailPage`
    * `sArticles::getFilterQuery`
    * `sArticles::addArticleCountSelect`
    * `sArticles::addActiveFilterCondition`
    * `sArticles::displayFilterArticleCount`
    * `sArticles::sGetLastArticles`
    * `sArticles::sGetCategoryProperties`
    * `sArticles::sGetArticlesVotes`
    * `Enlight_Controller_Front::returnResponse()`
    * `Shopware_Plugins_Core_Cron_Bootstrap::onAfterSendResponse()`
    * `\Shopware\Models\User\User::setAdmin()`
    * `\Shopware\Models\User\User::getAdmin()`
    * `\Shopware\Models\User\User::setSalted()`
    * `\Shopware\Models\User\User::getSalted()`
    * `\Shopware\Models\Banner\Banner::setLiveShoppingId()`
    * `\Shopware\Models\Banner\Banner::getLiveShoppingId()`
    * `sArticles::getPromotionNumberByMode('premium')`
    * `sArticles::sGetPromotions()`
    * `sMarketing::sCampaignsGetDetail()`
    * `sMarketing::sCampaignsGetList()`
    * `\Shopware\Models\Plugin\Plugin::isDummy()`
    * `\Shopware\Models\Plugin\Plugin::disableDummy()`
    * Removed `sArticles::getPromotionNumberByMode('image')` and `sArticles::getPromotionNumberByMode('gfx')` support
* Removed events:
    * `Shopware_Modules_Articles_GetFilterQuery`
    * `Shopware_Modules_Article_GetFilterSortMode`
    * `Shopware_Modules_Article_GetCategoryFilters`
    * `Enlight_Controller_Front_SendResponse`
    * `Enlight_Controller_Front_AfterSendResponse`
    * `Shopware_Modules_Articles_sGetProductByOrdernumber_FilterSql`
    * `Shopware_Modules_Articles_GetPromotions_FilterSQL`
* Removed Smarty vars:
    * `$sArticle.sNavigation` for product detail page
* Removed configuration variables from `Basic settings`:
    * `useDefaultControllerAlways`
    * `articlelimit`
    * `configcustomfields`
    * `configmaxcombinations`
    * `displayFilterArticleCount`
    * `ignoreshippingfreeforsurcharges`
    * `liveinstock`
    * `mailer_encoding`
    * `redirectDownload`
    * `redirectnotfound`
    * `seorelcanonical`
    * `seoremovewhitespaces`
    * `taxNumber`
    * `deactivateNoInstock`
* Removed database table/columns:
    * `s_core_rewrite`
    * `s_cms_groups`
    * `s_core_auth.admin`
    * `s_core_auth.salted`
    * `s_order_basket.liveshoppingID`
    * `s_order_basket.liveshoppingID`
    * `s_order_basket.liveshoppingID`
    * `s_emarketing_banners.liveshoppingID`
    * `s_core_sessions.expireref`
    * `s_core_sessions.created`
    * `s_core_sessions_backend.created`
    * `s_emarketing_promotions*`
    * `s_core_plugins.capability_dummy`
    * `s_articles_details.impressions`
* The new Shopware core selects all required data for `sGetArticleById`, `sGetPromotionById` and `sGetArticlesByCategory`. The following events and internal functions are no longer used in these functions:
    * `sGetPromotionById` events
        * `Shopware_Modules_Articles_GetPromotionById_FilterSql`
    * `sGetPromotionById` functions
        * `sGetTranslation`
        * `sGetArticleProperties`
        * `sGetCheapestPrice`
        * `sCalculatingPrice`
        * `calculateCheapestBasePriceData`
        * `getArticleListingCover`
    * `sGetAritcleById` events
        * `Shopware_Modules_Articles_GetArticleById_FilterSQL`
    * `sGetAritcleById` functions
        * `sGetTranslation`
        * `sGetPricegroupDiscount`
        * `sGetPromotionById` (for similar and related products)
        * `sCheckIfEsd`
        * `sGetPricegroupDiscount`
        * `sCalculatingPrice`
        * `sGetCheapestPrice`
        * `sGetArticleConfig`
        * `calculateReferencePrice`
        * `sGetArticlePictures`
        * `sGetArticlesVotes`
        * `sGetArticlesAverangeVote`
        * `sGetArticleProperties`
    * `sGetArticlesByCategory` events
        * `Shopware_Modules_Articles_sGetArticlesByCategory_FilterSql`
        * `Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopStart`
        * `Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd`
    * `sGetArticlesByCategory` functions
        * `sGetSupplierById`
        * `sGetCheapestPrice`
        * `sCalculatingPrice`
        * `calculateCheapestBasePriceData`
* Removed plugin `Shopware_Plugins_Frontend_RouterOld_Bootstrap`
* Moved `engine/core/class/*` to `engine/Shopware/Core/*`
* Merged `_default` template into the `_emotion` template
* Removed the template directory `_default` and all its dependencies
* Added the ability to show campaign banners in blog categories
* Refactored the template structure of the compare functionality. The plugin now uses based on a widget.
    * Added new block `frontend_listing_box_article_actions_compare` in the `listing/box_article.tpl`
* Removed support for flash banners. The associated template block `frontend_listing_swf_banner` is marked as deprecated
* Removed the template files for the feed functionality, which was marked as deprecated in SW 3.5
* Add new optional address fields to the register account and checkout process
* Added global messages template component to display e.g. error or success messages
* Added global css classes for different device viewports
* The registration and checkout workflows have been redesigned for the new template
* New jQuery plugin helper which provides all the basic operations every jQuery plugin needs to do
* Added several javascript libraries that enhance the supported features of the IE 8 and above
* Added `controller_action` and `controller_name` smarty functions that return the correspondent variable values
* The sitemap.xml uses now a smarty template
    * Added `Turnover by device type` in the backend statistics module
    * Added device type details to `Impressions` and `Visitors` in the backend statistics module
* Added `secureUninstall` method and capability for plugins. When 'secureUninstall'  capability is set, the user will be asked to select one of the uninstall methods:
    * (new) `Bootstrap::secureUninstall()` should be remove only non-user data
    * (old) `Bootstrap::uninstall()` current logic, should remove plugin and user data
* The `ArticleList` was merged with the former `MultiEdit` plugin. Plugins hooking the `ArticleList` Controller or extending the `ArticleList` backend module should be reviewed
* When using `selection` configurator type, shipping estimations will only be displayed when the user selects a value for all groups
* It's no longer possible to disable variant support for article that still have variants
* Added a new Theme Manager 2.0 with the possibility to create custom themes from the backend
    * Themes now support specific snippets that are used exclusively in the theme to which they belong
    * Shop configuration no longer contains the template selection.
* The snippet module in the backend now supports editing multiple translations for a single snippet at once
* Forms: elements of type `text2` now support `;` as a separator between labels for the first and second field:
    * Responsive template: labels are used separately as `placeholder` attribute for each `input` element
    * legacy templates: `;` is replaced with a `/` and used in a single `label` element (old behaviour)
* `street number` fields were removed from interfaces and database
    * Existing values were merged into the `street` field
    * `street` fields were enlarged to 255 chars to accommodate this.
    * The API still accepts `street number` values on write operations. The values are internally merged into the `street` field. This is legacy support, and will be removed in the future.
    * Read operations on the API no longer return a `street number` field.
* The configuration for the thumbnail size of the product images in the "last seen products" module no longer affects the responsive template. The size now changes by screen size.
* Changed behavior of the `selection` configurator. Configurator options which have none available product variant disabled now in the select-tag. The new snippet `DetailConfigValueNotAvailable` can be used to append additional text after the value name.
* Variant's `additional text` field is now automatically generated using the configurator group options. This can be optionally disabled.
* The `sBasket::sGetNotes` function has been refactored with the new Shopware service classes and no longer calls the `sGetPromotionById` function.
* The article slider now supports sorting by price (asc and desc) and category filtering
    * `Shopware_Controllers_Widgets_Emotion::emotionTopSellerAction` and `Shopware_Controllers_Widgets_Emotion::emotionNewcomerAction` are now deprecated and should be replaced by `Shopware_Controllers_Widgets_Emotion::emotionArticleSliderAction`
* Removed `table` and `table_factory` from container.
* The old table configurator was removed and replaced by the new image configurator in the emotion and responsive template.
* Template inheritance using `{extends file="[default]backend/..."}` is no longer supported and should be replaced by `{extends file="parent:backend/..."}`
* Added [Guzzle](https://github.com/guzzle/guzzle).
    * Added HTTP client `Shopware\Components\HttpClient\HttpClientInterface`.
    * Can be fetched from the container using the key `http_client`.
    * Deprecated Zend Framework components `Zend_Rest` and `Zend_Http` will be removed in the next minor release.
* Increased minimum required PHP version to PHP >= 5.4.0.
* Increased minimum required MySQL version to MySQl >= 5.5.0.
* When duplicating articles in the backend, attributes and translations will also be copied
* When applying main data to variants, translations will also be overwritten, if selected
* It is now possible to rename variant configurator options
* It is now possible to add translations to configurator templates, which will then be used when generating variants
* Removed legacy `excuteParent` method alias from generated hook proxy files
* Restructured cache directories. The whole `/cache` directory should be writable now
* Added two new settings to handle 404 responses:
    * `PageNotFoundDestination` extends the previous behaviour by adding support for Shopping worlds pages
    * `PageNotFoundCode` added to configure the HTTP error code when requesting non-existent pages
* Removed `Trusted Shops` from the basic settings. Functionality can now be found in `Trusted Shops Excellence` plugin
* Added `sArticles::getProductNavigation`, product navigation is rendered asynchronous via ajax call to `\Shopware_Controllers_Widgets_Listing::productNavigationAction`
* Add `isFamilyFriendly` core setting to enable or disable the correspondent meta tag.
* Add new SEO fields to the forms module.
* Add new SEO templates in the core settings for the form and the site data.
* Added `Theme cache warm up` modal window and functionality:
    * On cache clear
    * On performance settings
    * On theme change
    * On theme settings change
    * On plugin install, by adding `theme` to the optional caches array returned in `install()`
* Added `http cache warmer` modal window in the performance module and console command `sw:warm:http:cache`
* Deprecate Legacy API `Shopware->Api()`, will be removed in SW 5.1
* Removed unused `/backend/document` templates and several unused `Shopware_Controllers_Backend_Document` actions, methods and variables
* Performance recommendations now accept a `warning` state (state was converted from boolean to integer)
* Removed support for `engine/Shopware/Configs/Custom.php`
    * Use `config.php` or `config_$environment.php` e.g. `config_production.php`
* The MailTemplates now have global header and footer fields in configuration -> storefront -> email settings
    * Header for Plaintext
    * Header for HTML
    * Footer for Plaintext
    * Footer for HTML
* Refactored price surcharge for variants
    * `s_article_configurator_price_surcharges` database table was fully restructured and renamed to `s_article_configurator_price_variations`. Existing data is migrated on update
    * Existing related ExtJs classes and events removed
    * Existing price variation backend controller actions and methods removed
    * `Shopware\Models\Article\Configurator\PriceSurcharged` replaced by `Shopware\Models\Article\Configurator\PriceVariation`
* Replace `orderbydefault` configuration by `defaultListingSorting`. The `orderbydefault` configuration worked with a plain sql input which is no longer possible. The `defaultListingSorting` contains now one of the default `sSort` parameters of a listing.
* Add configuration for each listing facet, which allows to disable each facet.
* Move performance filter configuration into the category navigation item.
* Uniform the sorting identifier in the search and listing. Search relevance id changed from 6 to 7 and search rating sorting changed from 2 to 7.
* Generated listing links in the `sGetArticlesByCategory` function removed. The listing parameters are build now over a html form.
    * `sNumberPages` value removed
    * `categoryParams` value removed
    * `sPerPage` contains now the page limit
    * `sPages` value removed
* The listing filters are now selected in the `sArticles::getListingFacets` and assigned to the template as structs.
* Replaced "evaluation" sorting of the search result with the listing "popularity" sorting.
* The search filters are now selected in the `getFacets` function of the frontend search controller.
* The search filters are now assigned as structs to the template.
* `Shopware_Components_Search_Adapter_Default` is now deprecated, use `\Shopware\Bundle\SearchBundle\ProductNumberSearch`.
    * The search term is handled in the `SearchTermConditionHandler`.
    * This handler can be overwritten by custom handler. Custom handlers can be registered with the `Shopware_Search_Gateway_DBAL_Collect_Condition_Handlers` event.
* sGetArticleById result no longer contains the sConfiguratorSelection property. sConfiguratorSelection previously contained the selected variant data, which can now be accessed directly in the first level of the sGetArticleById result.
* sConfigurator class exist no more. The configurator data can now selected over the Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService.php. To modify the configurator data you can use the sGetArticleById events.
* `sCategories::sGetCategories` no longer returns the articleCount and the position of each category. Categories always sorted by the position and filtered by the active flag.
* Removed config option `front.returnResponse`, which was hardcoded to `true` since SW 4.2
* Added global JavaScript StateManager Singleton to handle different states based on registered breakpoints.
* Added new default states to the state manager
    * `xs` that ranges from 0 to 479 pixels viewport width
    * `s`  that ranges from 480 to 767 pixels viewport width
    * `m` that ranges from 768 to 1023 pixels viewport width
    * `l`  that ranges from 1024 to 1259 pixels viewport width
    * `xl` that ranges from 1260 to 5160 pixels viewport width
* Moved `frontend/detail/similar.tpl` to `frontend/detail/tabs/similar.tpl`
* Removed `frontend/checkout/ajax_add_article_slider_item.tpl`
* Removed `frontend/listing/box_crossselling.tpl`
* Removed `widgets/recommendation/item.tpl`
* Added `frontend/listing/product-box/box--product-slider.tpl`
    * This file should be used as an product slider item template
* Following template files include the new product slider template `frontend/listing/product-box/box--product-slider.tpl`
    * `frontend/checkout/ajax_add_article_slider.tpl` includes it instead of `frontend/checkout/ajax_add_article_slider_item.tpl`
    * `frontend/detail/tabs/related.tpl` includes it instead of `frontend/listing/box_article.tpl`
    * `widgets/recommendation/bought.tpl` includes it instead of `widgets/recommendation/item.tpl`
    * `widgets/recommendation/viewed.tpl` includes it instead of `widgets/recommendation/item.tpl`
    * `widgets/emotion/slide_articles.tpl` includes it instead of its own implementation
* Block named `frontend_detail_index_similar_viewed_slider` is now in the `widgets/recommendation/viewed.tpl` instead of `frontend/detail/index.tpl`
* Block named `frontend_detail_index_also_bought_slider` is now in the `widgets/recommendation/bought.tpl` instead of `frontend/detail/index.tpl`
* Renamed `ENV` to `SHOPWARE_ENV` to avoid accidentally set `ENV` variable, please update your .htaccess if you use a custom envirenment or you are using the staging plugin
* Removed Facebook Plugin from core (`Shopware_Plugins_Frontend_Facebook_Bootstrap`). Will be released as plugin on Github.
* Removed Google Plugin from core (`Shopware_Plugins_Frontend_Google_Bootstrap`). Will be released as plugin on Github.
* All downloaded dummy plugins are now installed in the `engine/Shopware/Plugins/Community` directory.
* Install, update, uninstall function of a plugin supports now a "message" return parameter which allows to display different messages.
* New commands: `sw:cron:list` and `sw:cron:run`
* Running cronjobs using `php shopware.php backend/cron` is not recommended and should be seen as deprecated
* `sVoteAverange` and `averange` properties of article and blog data structures have been renamed to fix the typo in their names.
    * Old versions are kept for compatibility reasons, but are deprecated and will be removed
    * Please notice that the new variable might not always have the same value (10 based vs 5 based ratings)
* Added VRRL Plugin to Core. Service articles can be identified by article attributes. The field can be configured by general settings
* Support text which is assigned to an checkbox element in the emotion world module will now be transformed to a box label
* Added category selection for blog emotion widget
* Changed default sorting of pictures in the backend's Media Manager. Newer pictures are now displayed first.
* `widgets/campaign` is now included in the HTTP cache's default configuration
* Email validation is now done using the `egulias/email-validator` library.
* Removed `frontend/detail/ajax.tpl`
* Added `frontend/detail/product_quick_view.tpl`
* Added `\Shopware\Controllers\Frontend\Detail::productQuickViewAction` to retrieve a detail template with minimal information
* Added configuration `showEsd` to show/hide the ESD-Downloads in the customer accounts menu. (default = true)
* Article image album sizes have been changed to match the requirements of the new template (only new installations)
* Removed `src` property of article images. Each images contains now a `thumbnails` property which all thumbnails.
        * `src` property is restored for old templates.
* Default value for controllers in which to display tag clouds no longer includes homepage.
* `sSelfCanonical` is deprecated. Use the `canonicalParams` array instead
* Change array structure of thumbnail images in emotions, product detail pages, product listings, blog pages.
* Enable and disable function of a plugin bootstrap can now return same parameter as install, uninstall.
* Added automatic APC detection for the general cache.

## 4.3.6
* Backport ESI security patch from Symfony Upstream (http://symfony.com/blog/cve-2015-2308-esi-code-injection).

## 4.3.5
* Additional checks for the auto update module in preparation for Shopware 5.

## 4.3.3
* The config option `showException` now only applies to frontend errors. Backend errors will always display the exception details.
* New event `Shopware_Modules_Basket_AddArticle_CheckBasketForArticle` in class sBasket
* The `Google Analytics` plugin is deprecated and will be removed in the next release. Please use the new `Google Services` plugin instead, available on the community store.
* Removed event `Shopware_Modules_Order_SaveOrder_FilterSQL`
* New event `Shopware_Modules_Order_SaveOrder_FilterParams`
* Implemented the `Enlight_Controller_Request_Request` interface. Please typehint to this class instead to `Enlight_Controller_Request_RequestHttp`
* New config option `trustedProxies`
* New event `Shopware_Controllers_Frontend_Forms_commitForm_Mail`
* Changed default value of `$checkProxy` to false in \Enlight_Controller_Request_Request::getClientIp($checkProxy = false).
    * The correct client ip is automatically obtained if the `trustedProxies` option is configured properly.
* Deprecated event `Shopware_Plugins_HttpCache_ShouldNotCache`
* New config option `httpCache.cache_cookies`

## 4.3.1

* Fixed name used as reference when setting attributes of an order document.
* Added new event `Shopware_Modules_Articles_sGetArticlesByCategory_FilterCountSql`
* `Forgotten password` feature now takes into account the configured minimum password length when generating new passwords
* Create an attributes entity when creating an order document using the Document component and check for an `attributes` array in the document config, whose key/value pairs will be set as the document's attributes
* Customer reviews backend module was improved to better handle reviews with large texts
* Auto update module now also reports main shop and subshops languages
* Maintenance mode options can now be configured by subshop
* Error notification via email was improved and now additionally includes environment and request information
* Minor occurrences of `metadescription` and `metakeywords` have been uniformized to `metaDescription` and `metaKeywords`
* It's now possible to filter payment methods by subshops
* `/widgets` and `/listing` added to `robots.txt`
* Calling certain widget urls without the required parameters will no longer trigger a server error (returns 404 instead)
* `Overview` and `Statistics` backend modules were adjusted to have matching data and differentiate between new users and new customers.
* `Shopping worlds` pages without assigned categories now support SEO urls
* The query passed in the `Shopware_Modules_Basket_GetBasket_FilterSQL` event will no longer include `s_core_units` join and fields
* The config option `showException` is `false` by default (`engine/Shopware/Configs/Default.php`)
    * Exceptions will no longer be shown in the store front
    * Exceptions are logged in a logfile since 4.2.0 (/logs)
    * The old behaviour can be restored by setting `'front' => array('showException' => true)` in the projects `config.php`
* Hiding the country field for shipping addresses will also hide the state field. The option label in the backend was adjusted to better describe this behaviour.

## 4.3.0

* Removed `location` header in responses for all REST-API PUT routes (e.g. PUT /api/customers/{id}).
* Removed deprecated Zend Framework components:
    * `Zend_Amf`
    * `Zend_Application`
    * `Zend_Barcode`
    * `Zend_Cloud`
    * `Zend_CodeGenerator`
    * `Zend_Console`
    * `Zend_Gdata`
    * `Zend_Markup`
    * `Zend_Measure`
    * `Zend_Memory`
    * `Zend_Pdf`
    * `Zend_Reflection`
    * `Zend_Search`
    * `Zend_Serializer`
    * `Zend_Tag`
    * `Zend_Test`
    * `Zend_Tool`
    * `Zend_EventManager`
    * `Zend_Feed`
    * `Zend_Dojo`
    * `Zend_Mobile`
    * `Zend_Queue`
    * `Zend_Captcha`
    * `Zend_Service`
* Removed the following core classes deprecated and/or unused methods
    * `sArticles::sGetArticleAccessories`
    * `sArticles::sCreateTranslationTable`
    * `sArticles::sGetLiveShopping`
    * `sArticles::sGetArticleBundlesByArticleID`
    * `sArticles::sGetArticleBundleByID`
    * `sArticles::sGetBundleBasketDiscount`
    * `sSystem::sPreProcess`
    * `sSystem::sInitMailer`
    * `sSystem::sGetTranslation`
    * `sSystem::sInitAdo`
    * `sSystem::sTranslateConfig`
    * `sSystem::sInitConfig`
    * `sSystem::sInitSmarty`
    * `sSystem::sInitSession`
    * `sSystem::sCallHookPoint`
    * `sSystem::sLoadHookPoints`
    * `sSystem::sInitFactory`
    * `sSystem::sCheckLicense`
    * `sSystem::E_CORE_ERROR`
    * `sCms::sGetDynamicContentByGroup`
    * `sCms::sGetDynamicContentById`
    * `sCms::sGetDynamicGroupName`
    * `sAdmin::sGetDispatch`
    * `sAdmin::sGetDispatches`
    * `sAdmin::sGetShippingcosts`
    * `sAdmin::sCheckTaxID`
    * `sCore::sCustomRenderer`
    * `sBasket::sCountArticles`
    * `sBasket::sGetBasketWeight`
* Removed the following core classes deprecated and/or unused variables
    * `sSystem::sDB_HOST`
    * `sSystem::sDB_USER`
    * `sSystem::sDB_PASSWORD`
    * `sSystem::sDB_DATABASE`
    * `sSystem::sDB_CONNECTOR`
    * `sSystem::sDEBUG`
    * `sSystem::sBENCHRESULTS`
    * `sSystem::sBENCHMARK`
    * `sSystem::sPathMedia`
    * `sSystem::sBasePath`
    * `sSystem::sBasefile`
    * `sSystem::sLicenseData`
    * `sSystem::sCurrencyData`
    * `sSystem::sPathCmsFiles`
    * `sSystem::sPathCmsImg`
    * `sBasket::sBASKET`
* `sCore::sBuildLink()` second argument removed (dead code)
* `sCore` no longer returns `null` when calling not implemented functions
* `sNewsletter` core class removed
* `Shopware_Controllers_Frontend_Content` legacy controller removed
* `templates/_default/frontend/content` legacy template files removed
* `s_cms_content` legacy database table removed
* Removed functions `simpledom_load_file()` and `simpledom_load_string()`
* Removed class `SimpleDOM` and `Shopware_Components_Xml_SimpleXml`
* Add new product feed modifier `articleImages` and `property`
* Create a new product export cronjob to export all active product feeds
* Implement new article association for new seo categories. The seo categories can be assigned over the array key seoCategories in the article api resource.
* Access to GET, POST and COOKIES through sSystem is deprecated.
    * The current arrays have been replaced with wrappers objects to the global variables
    * This might introduce breaks in some scenarios (eg.: when using array functions like array_merge)
* Plugin configuration: Stores of `select` and `combo` elements can now be translated
* Dynamically injecting variables into sSystem is no longer supported
* Removed `Shopware\Models\Widget\View::label` variable, getter and setter, and correspondent `s_core_widget_views::label` database column
* Deprecated `Shopware\Models\Widget\Widget::label` variable, getter and setter, and correspondent `s_core_widgets::label` database column
* Removed deprecated widget settings from the config module. Active widgets and their positions will now be saved automatically.
* Removed desktop switcher from the `Shopware.container.Viewport` base component.

## 4.2.2

* Remove old payment dummy plugins out of the core: PaymentSofort and PigmbhRatePAYPayment
* The tell a friend feature is now disabled by default, due to legal requirements. This will affect new and existing installations. You can enable/re-enable it using a new configuration option in the backend settings menu.
* [REST API] Add thumbnail generation to article and variant create and update actions
* Deprecation: The Database Column impressions in s_articles_details in now deprecated. Please use the s_statistics_article_impression table.
* `Shopware_Components_Plugin_Bootstrap` now has a `addFormTranslations()` method to facilitate translations creation for forms.
* Removed view variables `sOrders` and `sNotes` from `/engine/Shopware/Controllers/Frontend/Account.php` index action
* The methods `sGetOpenOrderData` and `sGetDownloads` in `/engine/core/class/sAdmin.php` will now return a different array structure and will accept new optional parameters to provide a pager functionality
* Added X-Sendfile support for ESD downloads. `redirectDownload` configuration variable is now deprecated, `esdDownloadStrategy` should be used instead
* Deprecation: `/engine/Shopware/Models/Payment/Repository.php:` `getPaymentsQuery` and `getPaymentsQueryBuilder` use `getActivePaymentsQuery` and `getActivePaymentsQueryBuilder` instead.

## 4.2.0

* Add new metaTitle field to the Blog
* Add new metaTitle field to the Article
* Removed unused class `Services_JSON`, was located at `/engine/core/ajax/json.php`.
* The subquery in `$priceForBasePrice` used in `sArticles::sGetArticlesByCategory` has been removed.
* A userland implementaion of [`array_column()`](http://php.net/array_column) has been included.
* Deprecated class `sTicketSystem` has been removed.
* Doctrine has been updated to version 2.4. See: https://github.com/doctrine/doctrine2/blob/2.4/UPGRADE.md
* Break: `Doctrine\ORM\Query::setParamters()` has changed. See: https://github.com/doctrine/doctrine2/blob/2.4/UPGRADE.md
* `Shopware\Components\Model\QueryBuilder::setParameters()` provides old behavior.
* Break: `Shopware_Plugins_Frontend_RouterOld_Bootstrap::onAssemble` event and implementation removed
* Update Zend Framework to version 1.12.3 (latest stable)
* Deprecation: Several unused Zend Framework components and classes are now deprecated. Refer to the full upgrade guide for details
* Break: Custom article attributes of type `Time` are now always saved using the german format. Only affects values inserted in non-german backends
* Removed the sSetLastArticle in sArticles.php. Was deprecated through setLastArticleById in the Shopware_Plugins_Frontend_LastArticles_Bootstrap plugin.
* Implement new options in the article resource. "considerTaxInput" allows to get the variant prices considering the article tax. "language" allows to get a whole translated article array. The "language" parameter can contain a sub shop id or a language iso like en_GB.
* `s_core_debit` table is now deprecated. `s_core_payment_data` and `s_core_payment_instance` should be used instead.
* core payment classes were removed. Existing references in the core to those classes now use the core PaymentMethods module implementation. Refer to the module for details on how to implement payment method logic
* Break: PaymentMethods core plugin components and templates had their performance improved, resulting in potential breaks for extensions
* - getCurrentPaymentData() was removed and should be replaced with getCurrentPaymentDataAsArray(), which returns the same information but in an array format
* Break: some payment snippets had their namespaces changed to comply with recent changes in snippet handling
* Break: customer detail editing in the backend: field names and field container structured to add support for additional payment methods. As such, debit.js view and detail controller have some breaks
* Ext.editorLang variable is no longer used and is being deprecated.
* Deprecation (REST API): 'debit' info in /api/customers/{id} is deprecated. Use 'paymentData' instead
* Break: Removed the Shopware.apps.Analytics.view.table.Conversion, Shopware.apps.Analytics.view.toolbar.Source and Shopware.apps.Analytics.view.toolbar.Shop file which now defined in the analytics/view/main/toolbar.js file.
* Removed unused class `Shopware_Components_Subscriber`, was located at `/engine/Shopware/Components/Subscriber.php`.
* Deprecation: Enlight's assertArrayCount() and assertArrayNotCount() are deprecated. Use phpunit's assertCount() instead

## 4.1.4

* New method `\Shopware\Components\Model\ModelManager::createPaginator($query)`.
 * This method should be used instead of `new \Doctrine\ORM\Tools\Pagination\Paginator($query)`.
 * As of SW 4.2 `$paginator->setUseOutputWalkers(false)` will be set here.
* New methods for calculating the basepricedata have been integrated in `/engine/core/class/sArticles.php`
 * `calculateCheapestBasePriceData` | This methods returns always the basepricedata of the cheapest variant. This is used in the listing views.
 * `getCheapestVariant` | This method is used by the method `calculateCheapestBasePriceData` to get the purchaseunit and the referenceunit of the cheapest variant.
 * `calculateReferencePrice` | This method does the basic calculation to get the right referenceprice.
* New PaymentMethods core plugin including refactored Debit and new SEPA payment methods.
* New `Shopware\Models\Customer\PaymentData` model to store customer's payment information.
* New `Shopware\Models\Payment\PaymentInstance` model to store payments information for individual orders.

### Deprecations
* The subquery in `$priceForBasePrice` used in the method `sGetArticlesByCategory` of the class `/engine/core/class/sArticles.php` is marked deprecated, because the query variable `priceForBasePrice` is no longer in use. Please do not use it anymore.

## 4.1.3

* Add configuration `Always display item short descriptions in listing views`.
* `Shopware_Components_Plugin_Bootstrap::assertVersionGreaterThen()` is now an alias to  `Shopware_Components_Plugin_Bootstrap::assertMinimumVersion()` and returns always `true` if run on an development/git Version of Shopware
* Added a new method `getDefault()` in `engine/Shopware/Models/Shop/Repository.php` which returns just the default shop without calling `fixActiv()`.
* Removed the unused `downloadAction()` in `engine/Shopware/Controllers/Backend/Plugin.php`

### Deprecations
* `decompressFile()` in `/engine/Shopware/Controllers/Backend/Plugin.php`
* `decompressFile()` in `/engine/Shopware/Plugins/Default/Core/PluginManager/Controllers/Backend/PluginManager.php`

You should use the decompressFile method in the CommunityStore component instead


## 4.1.1 / 4.1.2

With Shopware 4.1.1 we have fixed a bug that appeared during certain constellations in the customer registration process.
Submitting the registration formular empty, from time to time a fatal error was displayed.

For further information have a look at the following wiki article:

- GER: <http://wiki.shopware.de/_detail_1342.html>
- ENG: <http://en.wiki.shopware.de/_detail_1398.html>
