# CHANGELOG for Shopware 5.1.x

This changelog references changes done in Shopware 5.1 patch versions.

## 5.1.6 (2016-05-23)

[View all changes from v5.1.5...v5.1.6](https://github.com/shopware/shopware/compare/v5.1.5...v5.1.6)

* The interface `Enlight_Components_Cron_Adapter` in `engine/Library/Enlight/Components/Cron/Adapter.php` got a new method `getJobByAction`. For default implementation see `engine/Library/Enlight/Components/Cron/Adapter/DBAL.php`.
* Fix a unserialize regression with PHP 5.6.21 and PHP 7.0.6.
    
## 5.1.5 (2016-04-11)

[View all changes from v5.1.4...v5.1.5](https://github.com/shopware/shopware/compare/v5.1.4...v5.1.5)

* The smarty variable `sCategoryInfo` in Listing and Blog controllers is now deprecated and will be removed soon. Use `sCategoryContent` instead, it's a drop in replacement.

## 5.1.4 (2016-03-22)

[View all changes from v5.1.3...v5.1.4](https://github.com/shopware/shopware/compare/v5.1.3...v5.1.4)

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
    
## 5.1.3 (2016-02-15)

[View all changes from v5.1.2...v5.1.3](https://github.com/shopware/shopware/compare/v5.1.2...v5.1.3)

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

## 5.1.2 (2016-01-12)

[View all changes from v5.1.1...v5.1.2](https://github.com/shopware/shopware/compare/v5.1.1...v5.1.2)

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
    
## 5.1.1 (2015-10-26)

[View all changes from v5.1.0...v5.1.1](https://github.com/shopware/shopware/compare/v5.1.0...v5.1.1)

* Added new smarty block `frontend_detail_index_tabs_cross_selling` in the detail/ajax.tpl to prevent problems with custom themes
* Renamed block `backend/order/view/detail/communication` in `backend/order/view/detail/configuration.js` to `backend/order/view/detail/configuration`. The name was duplicated in another file and was renamed to match the correct file.

## 5.1.0 (2015-10-19)

[View all changes from v5.0.4...v5.1.0](https://github.com/shopware/shopware/compare/v5.0.4...v5.1.0)

* Added event `Shopware_Plugin_Collect_MediaXTypes` to collect media related x_type fields for which the value needs to be normalized
* Updated Behat to v3.0 and other related libraries
* Activated media fallback by default so that old media paths get resolved to the new location
* Update ongr/elasticsearch-dsl to version 1.0.0-RC1
* Update elasticsearch/elasticsearch to version 2.0.0
    * See: https://www.elastic.co/guide/en/elasticsearch/reference/2.0/breaking-changes-2.0.html
* The MediaBackend and PathNormalizer have been moved into the MediaService
* The media live migration is now enabled by default
* Added new Smarty block `frontend_index_header_javascript_tracking` for tracking codes which are required to be included into the "head" section of the document
* Upgraded mPDF to version 6.0
* Removed `Zend_Json_Server` related classes
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
    * Container Key `AdoDb` / 🦄()->Adodb()
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
* Removed previously deprecated API `🦄()->Api()`
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