# CHANGELOG for Shopware 5.3.x

This changelog references changes done in Shopware 5.3 patch versions.

## 5.3.7

[View all changes from v5.3.6...v5.3.7](https://github.com/shopware/shopware/compare/v5.3.6...v5.3.7)

### Changes

* Changed input validation to fix non persistent XSS vulnerability in the frontend
* Changed timeout for store API requests

### Deprecations

* Deprecated `articleId` column in `s_articles_attributes` table, it will be removed in Shopware version 5.5 as it isn't used anymore since version 5.2

## 5.3.6

[View all changes from v5.3.5...v5.3.6](https://github.com/shopware/shopware/compare/v5.3.5...v5.3.6)

### Changes

* Changed logging of exceptions to not log 404 errors 

### Additions

* Added product sorting by stock facet

## 5.3.5

[View all changes from v5.3.4...v5.3.5](https://github.com/shopware/shopware/compare/v5.3.4...v5.3.5)

### Additions

* Added new filter event `Shopware_Modules_Order_SaveOrder_FilterAttributes` to `engine/Shopware/Core/sOrder.php` to allow modification of the Order's attributes
* Added new notify until event `Shopware_Plugins_HttpCache_ShouldNotInvalidateCache` to `engine/Shopware/Plugins/Default/Core/HttpCache/Bootstrap.php` to be notified when the cache for a model will be invalidated and be able to prevent it
* Added new service `Shopware\Components\HttpCache\CacheRouteInstaller` for creating cache routes
* Added smarty block `frontend_index_header_meta_description_twitter` for `twitter:description` meta tag
* Added smarty block `frontend_index_header_meta_description_og` for `og:description` meta tag
* Added EventEmitter to StateManager constructor
* Added translation for article `shippingtime` field
* Added feed-id parameter to sw:product:feeds:refresh to refresh only a specific product feed 
* Added sw:product:feeds:list command to show all product feeds
* Added possibility for the http cache component to ignore certain url parameters for the cache key. A set of default parameters will be provided in version 5.4
* Added new API endpoint /users which allows to manage the backend users. For more information, look [here](https://developers.shopware.com/developers-guide/rest-api/api-resource-user/) 
* Added generatePassword method to engine/Shopware/Components/Random.php which allows to generate cryptographically secure passwords
* Added UserName and UserEmail validator in /engine/Shopware/Components/Auth
* Added hidden `sordernumber` form fields to inquiry forms

### Changes

* Changed the cache ids for the listing back to a<id> instead of <id>
* Changed the captcha validation with double-opt-in
* Changed the `createValueListFacetResult` method of `ProductAttributeFacetHandler` to display translations of attribute values in facet list
* Changed the snippet export to specify the fallback language
* The tax id of the invoice address is now also used for deliveries to foreign countries if the invoice country and delivery country are marked to be tax free for companies and if the delivery address does not have a tax id

### Removals

* Removed ambiguous variant cache ids from the emotion pages (`a<variantId>`)

## 5.3.4

[View all changes from v5.3.3...v5.3.4](https://github.com/shopware/shopware/compare/v5.3.3...v5.3.4)

### Additions

* Added method `Shopware\Models\Shop\Repository::getById()`
* Added service `translation`
* Added smarty function `is_object` to the list of allowed functions
* Added grunt task support for child themes so that their tasks are run as well by the Shopware grunt
* Added dispatch attributes to frontend
* Added initialization check for the "disable" and "enable" form field functionality of the TinyMCE component
* Added infinite sliding option to product slider

### Changes

* Changed article attribute filter in API 
* Changed the pagination of the blog by adding the filters to the pagination links
* Changed method `\Shopware\Components\SitemapXMLRepository::readCategoryUrls` to only export public category links 
* Changed method visibility of `\Shopware_Controllers_Backend_Search::createEntitySearchQuery` from `private` to `public`
* Changed the `I am` select option for the registration: If it is deactivated in the backend, it effects only the registration
* Changed the update, delete and insert backlog from product number to the main detail product number by variant and price model
* Changed payment type text legacy for `the_payment_has_been_ordered_by_hanseatic_bank` to be more generic
* Changed the snippet read operation: Snippets with numeric names are now possible
* Changed the creation of Cronjobs to insert active Cronjobs by a `cronjob.xml`

### Removals

* Removed the default option `touchControls: true` from the instantiation of the `swImageSlider`

## 5.3.3

[View all changes from v5.3.2...v5.3.3](https://github.com/shopware/shopware/compare/v5.3.2...v5.3.3)

### Additions

* Added filtering of model fields with possibly insecure content
* Added new container parameter `active_plugins` which contains an array of installed and active plugins
* Added variables `image_small`, `image_large`, `image_original`, `link_rating_tab` to product rating reminder email
* Added SVG and WOFF files to the list of compressible mimetypes
* Added functionality to show the backend growl messages in each corner of the screen (configurable in basic settings)
* Added smarty block `backend/base/model/corner_position` for corner position configuration model
* Added smarty block `backend/base/store/corner_position` for corner position configuration store
* Added functionality to open the drop down menus of the main menu in the backend by using either mouse hover or mouse click (configurable in basic settings)

### Changes

* Updated CustomerStream interface to improve usability

## 5.3.2

[View all changes from v5.3.1...v5.3.2](https://github.com/shopware/shopware/compare/v5.3.1...v5.3.2)

### Additions

* Added basic auth support for rest api

### Changes

* Changed `engine/Library/Smarty/sysplugins/smarty_internal_write_file.php` in order to prevent race condition when creating directories concurrently
* Support namespaces for controllers. The event `Enlight_Controller_Dispatcher_ControllerPath_{module}_{controller}` now accepts a class name as return value to allow namespaces for controllers in plugins.
* Changed representation of empty `conditions` in `s_customer_streams` to NULL instead of `{}`

## 5.3.0

[View all changes from v5.2.27...v5.3.0](https://github.com/shopware/shopware/compare/v5.2.27...v5.3.0)

### Additions

* Added config element `displayOnlySubShopVotes` to display only shop assigned article votes
* Added parameter `displayProgressOnSingleDelete` to `Shopware.grid.Panel` to hide progress window on single delete action
* Added parameter `expression` in `Shopware.listing.FilterPanel` to allow define own query expressions
* Added parameter `splitFields` to `Shopware.model.Container` to configure fieldset column layout
* Added interface `Shopware\Components\Captcha\CaptchaInterface`
* Added method `Shopware\Models\Order\Repository::getList()`
* Added method `Shopware\Models\Order\Repository::search()`
* Added method `Shopware\Models\Order\Repository::getDocuments()`
* Added method `Shopware\Models\Order\Repository::getDetails()`
* Added method `Shopware\Models\Order\Repository::getPayments()`
* Added responsive helper css/less classes in `_mixins/visibility-helper.less`
* Added method `Shopware\Bundle\MediaBundle\MediaServiceInterface::getFilesystem()` for direct access to the media filesystem
* Added config element `liveMigration` to enable or disable the media live migration
* Added config element `displayListingBuyButton` to display listing buy button
* Added service `shopware_search.batch_product_search` and `shopware_search.batch_product_number_search` for optimized product queries
* Added support for callback methods and jQuery promises in `jQuery.overlay` and `jQuery.loadingIndicators`
* Added jQuery method `setLoading()` to apply a loading indicator to an element `$('selector').setLoading()`
* Added required attribute `data-facet-name` for filter elements
* Added type for the filter panels `value-list-single`
* Added smarty block `frontend_listing_filter_facet_multi_selection` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_flyout` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_title` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_icon` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_content` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_list` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_option` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_option_container` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_input` for unified filter panel
* Added smarty block `frontend_listing_filter_facet_multi_selection_label` for unified filter panel
* Added service `Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryDepthService` to select categories by the given depth
* Added jQuery event `plugin/swListing/fetchListing` which allows to load listings, facet data or listing counts
* Added config element `listingMode` to switch listing reload behavior
* Added jQuery event `action/fetchListing` which allows to load listings, facet data or listing counts
* Added property `path` to `Shopware\Bundle\StoreFrontBundle\Struct\Media` which reflects the virtual path
* Added service `Shopware\Bundle\StoreFrontBundle\Service\Core\BlogService` to fetch blog entries by id
* Added template `themes/Frontend/Bare/frontend/detail/content.tpl`
* Added template `themes/Frontend/Bare/frontend/detail/content/header.tpl`
* Added template `themes/Frontend/Bare/frontend/detail/content/buy_container.tpl`
* Added template `themes/Frontend/Bare/frontend/detail/content/tab_navigation.tpl`
* Added template `themes/Frontend/Bare/frontend/detail/content/tab_container.tpl`
* Added option to select variants in `Shopware.apps.Emotion.view.components.Article` and `Shopware.apps.Emotion.view.components.ArticleSlider`
* Added local path to `@font-face` integration of the Open Sans font
* Added smarty block `frontend_register_billing_fieldset_company_panel` for registration
* Added smarty block `frontend_register_billing_fieldset_company_title` for registration
* Added smarty block `frontend_register_billing_fieldset_company_body` for registration
* Added smarty block `frontend_register_billing_fieldset_panel` for registration
* Added smarty block `frontend_register_billing_fieldset_title` for registration
* Added smarty block `frontend_register_billing_fieldset_body` for registration
* Added smarty block `frontend_register_index_cgroup_header_title` for registration
* Added smarty block `frontend_register_index_cgroup_header_body` for registration
* Added smarty block `frontend_register_index_advantages_title` for registration
* Added smarty block `frontend_register_login_customer_title` for registration
* Added smarty block `frontend_register_personal_fieldset_panel` for registration
* Added smarty block `frontend_register_personal_fieldset_title` for registration
* Added smarty block `frontend_register_personal_fieldset_body` for registration
* Added smarty block `frontend_register_shipping_fieldset_panel` for registration
* Added smarty block `frontend_register_shipping_fieldset_title` for registration
* Added smarty block `frontend_register_shipping_fieldset_body` for registration
* Added global date picker component `frontend/_public/src/js/jquery.datepicker.js` to Responsive theme
* Added filter facets for date and datetime fields
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-date.tpl` for date and datetime facets
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-date-multi.tpl` for date and datetime facets
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-date-range.tpl` for date and datetime facets
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-datetime.tpl` for date and datetime facets
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-datetime-multi.tpl` for date and datetime facets
* Added template `themes/Frontend/Bare/frontend/listing/filter/facet-datetime-range.tpl` for date and datetime facets
* Added JavaScript method `document.asyncReady()` to register callbacks which fire after the main script was loaded asynchronously.
* Added missing dependency `jquery.event.move` to the `package.json` file.
* Added template switch for `listing/index.tpl` to `listing/customer_stream.tpl` in case that the category contains a shopping world which is restricted to customer streams
* Added database column `s_emarketing_vouchers.customer_stream_ids` to restrict vouchers to customer streams.
* Added database column `s_emotion.customer_stream_ids` to restrict shopping worlds to customer streams.
* Added database table `s_customer_streams` for a list of all existing streams (`Shopware\Models\CustomerStream\CustomerStream`)
* Added database table `s_customer_search_index` for an fast customer search
* Added database table `s_customer_streams_mapping` for mappings between customer and assigned streams
* Added bundle `Shopware\Bundle\CustomerSearchBundle` which defines how customers can be searched
* Added bundle `Shopware\Bundle\CustomerSearchBundleDBAL` which allows to search for customers using DBAL
* Added console command `sw:customer:search:index:populate` to generate customer stream search index
* Added console command `sw:customer:stream:index:populate` to generate customer stream mapping table
* Added flag `$hasCustomerStreamEmotion` in `frontend/home/index.tpl` to switch between emotions restricted to customer streams and those which are unrestricted
* Added route `/frontend/listing/layout` which loads the category page layout for customer streams. This route is called using `{action ...}` in case that the category contains an emotion with customer streams
* Added route `/frontend/listing/listing` which loads the category product listing. This route is called using `{action ...}` in case that the category contains an emotion with customer streams
* Added entity `Shopware\Models\CustomerStream\CustomerStream` for attribute single and multi selection.
* Added translations for attribute labels. See below for more information.
* Added database structure for new emotion preset feature:
    * `s_emotion_presets` - contains all installed presets
    * `s_emotion_preset_translations` - contains presets translations
* Added models for presets
    * `Shopware\Models\Emotion\Preset`
    * `Shopware\Models\Emotion\PresetTranslation`
* Added classes for handling emotion preset feature
    * `Shopware\Components\Emotion\EmotionImporter` - handle emotion imports
    * `Shopware\Components\Emotion\EmotionExporter` - handle emotion exports
    * `Shopware\Components\Emotion\Preset\EmotionToPresetDataTransformer` - transform emotion to preset
    * `Shopware\Components\Emotion\Preset\PresetDataSynchronizer` - uses component handlers to support import / export of emotions  
    * `Shopware\Components\Emotion\Preset\PresetInstaller` - installer for preset plugins
    * `Shopware\Components\Emotion\Preset\PresetLoader` - loads presets and refreshes preset data to match current database
    * `Shopware\Components\Emotion\Preset\PresetMetaDataInterface` - interface to use for preset plugin development
* Added API Resource for emotion presets `Shopware\Components\Api\Resource\EmotionPreset`
* Added backend controller for emotion presets `Shopware\Controllers\Backend\EmotionPresets`
* Added compiler pass to register emotion component handlers `Shopware\Components\DependencyInjection\Compiler\EmotionPresetCompilerPass`
* Added component handlers for asset import and export of shopping world elements
    * `Shopware\Components\Emotion\Preset\ComponentHandler\BannderComponentHandler`
    * `Shopware\Components\Emotion\Preset\ComponentHandler\BannerSliderComponentHandler`
    * `Shopware\Components\Emotion\Preset\ComponentHandler\CategoryTeaserComponentHandler`
    * `Shopware\Components\Emotion\Preset\ComponentHandler\Html5VideoComponentHandler`
* Added new ExtJs views for emotion presets under `themes\backend\emotion\view\preset`
* Added new service tag for registering emotion preset component handlers `shopware.emotion.preset_component_handler`
* Added actions to import and export shopping worlds in `Shopware_Controllers_Backend_Emotion`
* Added condition class `Shopware\Bundle\SearchBundle\Condition\WidthCondition`
* Added condition class `Shopware\Bundle\SearchBundle\Condition\HeightCondition`
* Added condition class `Shopware\Bundle\SearchBundle\Condition\LengthCondition`
* Added condition class `Shopware\Bundle\SearchBundle\Condition\WeightCondition`
* Added facet class `Shopware\Bundle\SearchBundle\Facet\CombinedConditionFacet`
* Added facet class `Shopware\Bundle\SearchBundle\Facet\WidthFacet`
* Added facet class `Shopware\Bundle\SearchBundle\Facet\HeightFacet`
* Added facet class `Shopware\Bundle\SearchBundle\Facet\LengthFacet`
* Added facet class `Shopware\Bundle\SearchBundle\Facet\WeightFacet`
* Added `Shopware\Bundle\SearchBundleDBAL\VariantHelper` which joins all variants for dbal search
* Added smarty blocks `frontend_checkout_shipping_payment_core_button_top` and `frontend_checkout_shipping_payment_core_button_top` for shipping
* Added new Interface for facet result template switch `Shopware\Bundle\SearchBundle\TemplateSwitchable`
* Added new service `Shopware\Bundle\MediaBundle\CdnOptimizerService` for optimizing remote images on CDNs
* Added option to control target attribute for external links in categories
    * Added database column `s_category.external_target`
    * Added property `externalTarget` in `Shopware\Bundle\StoreFrontBundle\Struct\Category`
    * Added property `externalTarget` in `Shopware\Models\Category\Category`
    * Added property `externalTarget` in `Shopware\themes\Backend\ExtJs\backend\category\model\detail`
    * Added translations for field labels and combo box options
* Added `selecttree` and `combotree` config elements for plugins
* Added backend configuration option for the newsletter to configure if a captcha is required to subscribe to the newsletter
* Added two new Smarty blocks for menu and menu item overwrite possibility to the account sidebar
* Added LiveReload mode for the default grunt which reloads your browser window automatically after the grunt compilation was successful
* Added `nofollow` attribute to all links in the block `frontend_account_menu` since these links are now visible in the frontend if the account dropdown menu is activated
* Added `type` parameter to `Shopware_Controllers_Widgets_Listing::productSliderAction` and `Shopware_Controllers_Widgets_Listing::productsAction` which allows to load product sliders or product boxes.
* Added new search builder class `Shopware\Components\Model\SearchBuilder`
* Added new search builder as __construct parameter in `Shopware\Bundle\AttributeBundle\Repository\Searcher\GenericSearcher`
* Added new `FunctionNode` for IF-ELSE statements in ORM query builder
* Added `/address` to robots.txt 
* Added snippet `DetailBuyActionAddName` in `snippets/frontend/detail/buy.ini`
* Added `Shopware\Components\Template\Security` class for all requests.
* Added whitelist for allowed php functions and php modifiers in smarty
    * template_security.php_modifiers
    * template_security.php_functions
* Added new option `showPagingToolbar` to `Shopware.DragAndDropSelector.js`. Default is `false`.
* Added proper expandability to the manual 'SEO URL generation' and the 'HttpCache Warmer' window in `themes/Backend/ExtJs/backend/performance/view/main/multi_request_tasks.js`
* Added new filter event `Shopware_Controllers_Performance_filterCounts` to `engine/Shopware/Controllers/Backend/Performance.php` to add custom count of the HttpCache warmer URLs
* Added new filter event `Shopware_Controllers_Seo_filterCounts` to `engine/Shopware/Plugins/Default/Core/RebuildIndex/Controllers/Seo.php` to add a custom SEO URL count
* Added new notify event `Shopware_CronJob_RefreshSeoIndex_CreateRewriteTable` to `engine/Shopware/Plugins/Default/Core/RebuildIndex/Bootstrap.php` to be notified when the SEO URLs are generated via cronjob
* Added new column `do_not_split` to table `s_search_fields`. Activate to store the values of this field as given into the search index. If not active, the default behaviour is used
* Added new service `shopware_storefront.price_calculator` which calculates the product price. Was formerly a private method in `shopware_storefront.price_calculation_service`
* Added service `shopware_media.extension_mapping` to provide a customizable whitelist for media file extensions and their type mapping

### Changes

* Updated `FPDF` to 1.8.1
* Updated `FPDI` to 1.6.1
* Updated `flatpickr` to 2.5.7
* Updated `jquery` to 2.2.4
* Updated `grunt` to 1.0.1
* Updated `grunt-contrib-clean` to 1.1.0
* Updated `grunt-contrib-copy` to 1.0.0
* Updated `Modernizr` to 3.5.0
* Changed theme path for new plugins from `/resources` into `/Resources`
* Changed sorting of `Shopware.listing.FilterPanel` fields
* Changed database column `s_articles_vote`.`answer_date` to allow `NULL` values
* Changed `LastArticle` plugin config elements `show`, `controller` and `time` to be prefixed with `lastarticles_`
* Changed product listings in shopping worlds to only be loaded if `showListing` is true
* Changed sql query in `sAdmin` queries which uses a sub query for address compatibility, following functions affected:
    * `sAdmin::sGetDispatchBasket`
    * `sAdmin::sGetPremiumDispatches`
    * `sAdmin::sGetPremiumDispatchSurcharge`
* Changed attribute type `string` mapping to mysql `TEXT` type. String and single selection data type supports no longer a sql default value.
* Changed `roundPretty` value for currency range filter
* Changed `CategoryFacet` behavior to generate each time a tree based on the system category with a configured category depth
* Changed facet templates `facet-radio`, `facet-media-list` and `facet-value-list` into one template
* Renamed parameter `data-count-ctrl` on `#filter` form to `data-listing-url`
* Changed removal version of method `Shopware\Components\Model\ModelManager::addAttribute` to 5.4
* Changed removal version of method `Shopware\Components\Model\ModelManager::removeAttribute` to 5.4
* Changed template `component_article_slider.tpl` to show provided products instead of always fetching them via ajax
* Changed emotion preview to not save the current state before showing preview
* Changed command `sw:thumbnail:cleanup` to search the filesystem to remove orphaned thumbnails
* Changed configuration `defaultListingSorting` from the performance module to basic settings in `categories / listings`
* Changed the jQuery plugin `src/js/jquery.selectbox-replacement.js` to be used only as a polyfill. Use the CSS-only version for select fields instead.
* Changed template filename from `frontend/forms/elements.tpl` to `frontend/forms/form-elements.tpl`
* Changed smarty block from `frontend_forms_index_elements` to `frontend_forms_index_form_elements`
* Changed smarty blocks from `frontend_forms_elements*` to `frontend_forms_form_elements*`
* Changed template file `themes/Frontend/Bare/frontend/detail/index.tpl` to split it into separated files
* Changed property `linkDetails` of `$sArticle`
* Changed the article url to also contain the order number of the product
* Changed the product selection to variant selection in `Shopware.apps.Emotion.view.components.BannerMapping`
* Changed the integration of `modernizr.js` and added it to the compressed main JavaScript files
* Changed the script tag for the generated JavaScript file for asynchronous loading, can be changed in theme configuration
* Changed the inline script for the statistics update to vanilla JavaScript
* Changed event name from `plugin/swAjaxProductNavigation/onSetProductState` to `plugin/swAjaxProductNavigation/onGetProductState`
* Changed behavior of the smarty rendering in forms fields comment. See below for more information
* Changed behavior of the tracking url rendering. See below for more information
* Changed database column `s_articles_details.instock` to allow `NULL` values and default to `0`
* Backend customer listing is now loaded in `Shopware_Controllers_Backend_CustomerQuickView`
* Refactored backend customer module. Please take a look into the different template files to see what has changed.
* Changed parameter order of `Shopware\Bundle\SearchBundle\FacetResult\RangeFacetResult::__construct()` and added `$suffix` and `$digits`.
* Changed selection fields in the voucher module. `shopware-form-field-single-selection` is now used instead of the native `combobox` xtype and `shopware-form-field-product-grid` is used for article restriction.
* Changed templates to support custom targets for category links
    * `themes/Frontend/Bare/frontend/index/main-navigation.tpl`
    * `themes/Frontend/Bare/frontend/index/sidebar-categories.tpl`
    * `themes/Frontend/Bare/frontend/sitemap/index.tpl`
    * `themes/Frontend/Bare/frontend/sitemap/recurse.tpl`
    * `engine/Shopware/Plugins/Default/Frontend/AdvancedMenu/Views/frontend/advanced_menu/index.tpl`
* Changed `engine\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\CategoryHydrator` to support the custom target property
* Changed `engine\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper` to support the custom target property
* Changed `engine\Shopware\Components\Compatibility\LegacyStructConverter` to support the custom target property
* Changed return values so the array keys are now the respective country/state IDs in `\Shopware\Bundle\StoreFrontBundle\Service\Core\LocationService::getCountries`
* Moved the removal of the whole cache folder after the removal of the `.js` and `.css` files for better handling of huge caches in the `clear_cache.sh` script
* Changed `Shopware_Controllers_Widgets_Listing::streamSliderAction` to `Shopware_Controllers_Widgets_Listing::streamAction`
* Changed `Shopware_Controllers_Widgets_Listing::productSliderAction` to `Shopware_Controllers_Widgets_Listing::productsAction`
* Changed snippet `DetailBuyActionAdd` in `snippets/frontend/detail/buy.ini`, it now contains <span> tags
* Changed snippet `ListingBuyActionAdd` in `snippets/frontend/listing/box_article.ini`, it now contains another <span> tag
* Merged `account/sidebar.tpl` and `account/sidebar_personal.tpl`
* Moved snippets from `account/sidebar_personal.ini` to `account/sidebar.ini`

### Removals

* Removed unused Zend Framework Components. See below for more information
* Removed support for Internet Explorer < 11
* Removed configuration option `sCOUNTRYSHIPPING`
* Removed variable `{$sShopname}` from forms, use `{sShopname}` instead
* Removed import / export module
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/detail.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/edit.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/infopanel.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/list.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/toolbar.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/view/vote/window.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/controller/vote.js`
* Removed ExtJS template `themes/Backend/ExtJs/backend/vote/controller/vote.js`
* Removed database column `s_emarketing_lastarticles`.`articleName`
* Removed database column `s_emarketing_lastarticles`.`img`
* Removed default plugin `LastArticle`, use `shopware.components.last_articles_subscriber` instead
* Removed session key `sLastArticle`
* Removed view variable `sLastActiveArticle` from basket
* Removed snippet `frontend/checkout/actions/CheckoutActionsLinkLast`
* Removed meta tag `fragment`
* Removed view variable `hasEscapedFragment`
* Removed method `Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder()`
* Removed method `Shopware\Models\Order\Repository::getBackendOrdersQuery()`
* Removed method `Shopware\Models\Order\Repository::getBackendAdditionalOrderDataQuery()`
* Removed jQuery plugin method `showFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin method `hideFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin event `plugin/swEmotionLoader/onShowFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin event `plugin/swEmotionLoader/onHideFallbackContent`in `jquery.emotion.js`
* Removed alias support from `Enlight_Controller_Request_Request` (`getAlias`, `getAliases`, `setAlias`)
* Removed method `Shopware\Components\Model\ModelManager::__call()`
* Removed class `Enlight_Bootstrap`
* Removed parameter `$checkProxy` from `Enlight_Controller_Request_Request::getClientIp()`
* Removed smarty block `frontend_search_category_filter`
* Removed template file `themes/Frontend/Bare/frontend/search/category-filter.tpl`
* Removed parameter `sCategory` from search controller `listing/ajaxCount` requests
* Removed smarty block `frontend_listing_filter_facet_media_list_flyout`
* Removed smarty block `frontend_listing_filter_facet_media_list_title`
* Removed smarty block `frontend_listing_filter_facet_media_list_icon`
* Removed smarty block `frontend_listing_filter_facet_media_list_content`
* Removed smarty block `frontend_listing_filter_facet_media_list_list`
* Removed smarty block `frontend_listing_filter_facet_media_list_option`
* Removed smarty block `frontend_listing_filter_facet_media_list_option_container`
* Removed smarty block `frontend_listing_filter_facet_media_list_input`
* Removed smarty block `frontend_listing_filter_facet_media_list_label`
* Removed smarty block `frontend_listing_filter_facet_radio_flyout`
* Removed smarty block `frontend_listing_filter_facet_radio_title`
* Removed smarty block `frontend_listing_filter_facet_radio_icon`
* Removed smarty block `frontend_listing_filter_facet_radio_content`
* Removed smarty block `frontend_listing_filter_facet_radio_list`
* Removed smarty block `frontend_listing_filter_facet_radio_option`
* Removed smarty block `frontend_listing_filter_facet_radio_option_container`
* Removed smarty block `frontend_listing_filter_facet_radio_input`
* Removed smarty block `frontend_listing_filter_facet_radio_label`
* Removed smarty block `frontend_listing_filter_facet_value_list_flyout`
* Removed smarty block `frontend_listing_filter_facet_value_list_title`
* Removed smarty block `frontend_listing_filter_facet_value_list_icon`
* Removed smarty block `frontend_listing_filter_facet_value_list_content`
* Removed smarty block `frontend_listing_filter_facet_value_list_list`
* Removed smarty block `frontend_listing_filter_facet_value_list_option`
* Removed smarty block `frontend_listing_filter_facet_value_list_option_container`
* Removed smarty block `frontend_listing_filter_facet_value_list_input`
* Removed smarty block `frontend_listing_filter_facet_value_list_label`
* Removed field `attributes.search.cheapest_price` from DBAL search query
* Removed field `attributes.search.average` from DBAL search query
* Removed join to `s_core_tax` in `Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch`
* Removed method `Shopware_Controllers_Widgets_Emotion::getEmotion()`
* Removed method `Shopware_Controllers_Widgets_Emotion::handleElement()`, use `Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface` instead
* Removed method `Shopware_Controllers_Widgets_Emotion::getRandomBlogEntry()`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBlogEntry()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BlogComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getCategoryTeaser()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\CategoryTeaserComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBannerMappingLinks()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BannerComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getManufacturerSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\ManufacturerSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBannerSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BannerSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getArticleSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getHtml5Video()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\Html5VideoComponentHandler`
* Removed LESS variable `@zindex-fancy-select`
* Removed the jQuery plugin `src/js/jquery.selectbox-replacement.js` completely
* Removed LESS variable `@zindex-fancy-select`
* Removed smarty block `frontend_listing_actions_sort_field_relevance`
* Removed smarty block `frontend_listing_actions_sort_field_release`
* Removed smarty block `frontend_listing_actions_sort_field_rating`
* Removed smarty block `frontend_listing_actions_sort_field_price_asc`
* Removed smarty block `frontend_listing_actions_sort_field_price_desc`
* Removed smarty block `frontend_listing_actions_sort_field_name`
* Removed route `/backend/performance/listingSortings`
* Removed constants of `\Shopware\Bundle\SearchBundle\CriteriaRequestHandler\CoreCriteriaRequestHandler` and `Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory`:
    * `SORTING_RELEASE_DATE`
    * `SORTING_POPULARITY`
    * `SORTING_CHEAPEST_PRICE`
    * `SORTING_HIGHEST_PRICE`
    * `SORTING_PRODUCT_NAME_ASC`
    * `SORTING_PRODUCT_NAME_DESC`
    * `SORTING_SEARCH_RANKING`
* Removed smarty modifier `rewrite`
* Removed less variable `@font-face` for `extra-bold` and `light` of the Open Sans font type
* Removed scrollbar styling on filter-panels (Selector: `.filter-panel--content`)
* Removed support for `.swf` file type in the banner module
* Removed smarty template block `frontend_listing_swf_banner` in `themes/Frontend/Bare/frontend/listing/banner.tpl`
* Removed the jQuery UI date picker integration in favour of a new global component
* Removed unused css class `.panel--list`
* Removed unused css class `.panel--arrow`
* Removed unused css class `.panel--tab-nav`
* Removed unused css class `.panel--filter-btn`
* Removed unused css class `.panel--filter-select`
* Removed unused css class `.js--mobile-tab-panel`
* Removed unused css class `.ribbon`
* Removed unused css class `.ribbon--content`
* Removed unused css class `.device--mobile`
* Removed unused css class `.device--tablet`
* Removed unused css class `.device--tablet-portrait`
* Removed unused css class `.device--tablet-landscape`
* Removed unused css class `.device--desktop`
* Removed `max-width` rule for `.filter--active` in `themes/Frontend/Responsive/frontend/_public/src/less/_components/filter-panel.less`
* Removed unused field `s_core_countries.shippingfree`
* Removed `__country_shippingfree` field in `Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper::getCountryFields`
* Removed `Shopware\Bundle\StoreFrontBundle\Struct\Country::setShippingFree` and `Shopware\Bundle\StoreFrontBundle\Struct\Country::isShippingFree`
* Removed `Shopware\Models\Country\Country::$shippingFree`, `Shopware\Models\Country\Country::setShippingFree` and `Shopware\Models\Country\Country::getShippingFree`
* Removed method `Shopware\Models\Customer\Repository::getListQueryBuilder`
* Removed method `Shopware\Models\Customer\Repository::getListQuery`
* Removed method `Shopware\Models\Customer\Repository::getBackendListCountedBuilder`
* Removed route `/backend/customer/getList`
* Removed ExtJS model `Shopware.apps.Customer.model.List`
* Removed ExtJS store `Shopware.apps.Customer.store.List`
* Removed obsolete ExtJS model `Shopware.apps.CanceledOrder.model.Customer`
* Removed less file `ie.less` from the Responsive Theme
* Removed vendor prefixes `-ms` and  `-o` from all mixins in the Bare and Responsive Theme
* Removed file `jquery.ie-fixes.js` from the Responsive Theme
* Removed polyfill `selectivizr` for CSS3 pseudo-classes & attribute selectors
* Removed polyfill `placeholder`
* Removed polyfill `Respond.js` for `min / max-width` media queries
* Removed polyfill `matchMedia` from `jquery.state-manager.js`. Use `Modernizr.mq()` for the same functionality
* Removed polyfill `requestAnimationFrame` from `jquery.state-manager.js`
* Removed polyfill `cancelAnimationFrame` from `jquery.state-manager.js`
* Removed cookie based polyfill `Storage` from `jquery.storage-manager.js`
* Removed method `hasLocalStorageSupport` from `jquery.storage-manager.js`. Use Modernizr to detect the feature
* Removed method `hasSessionStorageSupport` from `jquery.storage-manager.js`. Use Modernizr to detect the feature
* Removed `html5shiv`
* Removed `modernizr` option `Input attributes`
* Removed `modernizr` option `Form input types`
* Removed `modernizr` option `Border radius`
* Removed `modernizr` option `Box shadow`
* Removed `modernizr` option `CSS gradient`
* Removed `modernizr` option `CSS Transforms`
* Removed `modernizr` option `CSS Transforms 3D`
* Removed `modernizr` option `CSS Transitions`
* Removed event `Shopware_Plugins_HttpCache_ShouldNotCache`
* Removed `eval` from block `frontend_forms_index_headline` in `index.tpl` of `themes\Frontend\Bare\frontend\forms` for `$sSupport.text`
* Removed cleanupPlugins from `Shopware\Bundle\PluginInstallerBundle\Service`

### Deprecations

* Deprecated `Shopware_Components_Convert_Csv` without replacement, to be removed with 5.4
* Deprecated `Shopware_Components_Convert_Xml` without replacement, to be removed with 5.4
* Deprecated `Shopware_Components_Convert_Excel` without replacement, to be removed with 5.4
* Deprecated `\Shopware_Controllers_Widgets_Listing::ajaxListingAction`, use `\Shopware_Controllers_Widgets_Listing::listingCountAction` instead
* Deprecated method `sArticles::sGetAffectedSuppliers()` without replacement, to be removed with 5.5
* Deprecated `Shopware\Models\Article\Element`, to be removed with 6.0

### Zend Framework

A lot of zend framework components or their methods were unused. Here is a list of changes:

#### Removed Components

* `Zend_Controller`
* `Zend_Navigation`
* `Zend_View`
* `Zend_Form`
* `Zend_Paginator`
* `Zend_Crypt`
* `Zend_Oauth`
* `Zend_Dom`
* `Zend_Server`
* `Zend_Rest`
* `Zend_Stdlib`
* `Zend_Text`
* `Zend_Debug`
* `Zend_Registry`
* `Zend_XmlRpc`
* `Zend_Soap`
* `Zend_Service`
* `Zend_Filter_Compress`
* `Zend_Filter_Decompress`
* `Zend_Filter_Encrypt`
* `Zend_Filter_Decrypt`

#### Zend_Json

* Removed `Zend_Json_Decoder`
* Removed `Zend_Json_Encoder`
* Removed `Zend_Json_Expr`
* Option `enableJsonExprFinder`
* Removed property `Zend_Json::$useBuiltinEncoderDecoder`
* Removed property `Zend_Json::$maxRecursionDepthAllowed`
* Removed method `Zend_Json::fromXml()`

#### Zend_Loader

 * Removed `Zend_Loader_Autoloader`
 * Removed `Zend_Loader_ClassMapAutoloader`
 * Removed `Zend_Loader_StandardAutoloader`
 * Removed `Zend_Loader_Autoloader_Resource`
 * Removed method `Zend_Loader::autoload()`
 * Removed method `Zend_Loader::registerAutoload()`

#### Zend_DB

* Removed unused adapters like Db2, Mysqli, Oracle, Ibm, MsSql, Oci, PgSQL, Sqlsrv

#### Zend_Layout

* Removed `Zend_Log_Writer_Mail::setLayout()` config options `layout` and `layoutFormatter` `setLayoutFormatter()` `getLayoutFormatter()`

#### Zend_Inforcard

* Removed `Zend_Auth_Adapter_InfoCard`

#### Zend_OpenId

* Removed `Zend_Auth_Adapter_OpenId`

#### Zend_TimeSync

* Removed TimeSync support from `Zend_Date`

#### Zend_ProgressBar

* Removed ProgressBar support from `Zend_File_Transfer_Adapter_Http`

#### Zend_Ldap

* Removed `Zend_Auth_Adapter_Ldap`
* Removed `Zend_Validate_Ldap_Dn`

#### Zend_Wildfire
* Removed `Zend_Db_Profiler_Firebug`
* Removed `Zend_Log_Formatter_Firebug`
* Removed `Zend_Log_Writer_Firebug`

#### Zend_File

* Removed `Zend_Filter_File_*`
* Removed `Zend_Validate_File_*`

### Smarty Rendering

#### Form Fields

Only variables that were previously assigned to the view are rendered. In addition, smarty function calls are no longer executed.

##### Example

```
{sElement.name} // works

{sElement.name|currency} // works, but does not execute the currency function

{sElement.value[$key]|currency} // does not work
```

#### Tracking Code

Smarty rendering has been disable for this section. All variables have been removed with one exception. The variable `{$offerPosition.trackingcode}` is a placeholder now. To generate tracking urls, use the following pattern:

```
https://gls-group.eu/DE/de/paketverfolgung?match={$offerPosition.trackingcode}

<a href="https://gls-group.eu/DE/de/paketverfolgung?match={$offerPosition.trackingcode}" onclick="return !window.open(this.href, 'popup', 'width=500,height=600,left=20,top=20');" target="_blank">{$offerPosition.trackingcode}</a>
```

### Attribute label translations

Translations for different fields (help, support, label) can be configured via snippets.

**Example: `s_articles_attributes.attr1`**

| Field | Snippet name |
|-------|--------------|
| Snippet namespace         |  backend/attribute_columns |
| Snippet name label        |  s_articles_attributes_attr1_label |
| Snippet name support text |  s_articles_attributes_attr1_supportText |
| Snippet name help text    |  s_articles_attributes_attr1_helpText |

### Backend Components

You can now define the expression for the comparison in SQL. For example `>=` like seen below:

```javascript
Ext.define('Shopware.apps.Vote.view.list.extensions.Filter', {
    extend: 'Shopware.listing.FilterPanel',
    alias:  'widget.vote-listing-filter-panel',
    configure: function() {
        return {
            controller: 'Vote',
            model: 'Shopware.apps.Vote.model.Vote',
            fields: {
                points: {
                    expression: '>=',
                }
            }
        };
    }
});
```

### Captcha

Captchas are now configurable via backend and can be added using the `shopware.captcha` dependency injection container tag.

```xml
<service id="shopware.captcha.recaptcha" class="SwagReCaptcha\ReCaptcha">
    <argument type="service" id="guzzle_http_client_factory"/>
    <argument type="service" id="config"/>
    <tag name="shopware.captcha"/>
</service>
```

For more information, please refer to our [Captcha Documentation](https://developers.shopware.com/developers-guide/implementing-your-own-captcha/).

### Redis backend and doctrine cache
Redis can now be used as a cache provider for the backend and model caches. Here is an example:

```
    'model' => [
        'redisHost' => '127.0.0.1',
        'redisPort' => 6379,
        'redisDbIndex' => 0,
        'cacheProvider' => 'redis',
    ],

    'cache' => [
        'backend' => 'redis',
        'backendOptions' => [
            'servers' => array(
                array(
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'dbindex' => 0,
                ),
            ),
        ],
    ],
```

### Select field replacement

The replacement of the select field elements via JavaScript is deprecated and will be removed in a future release. You can create a styled select field with a simple CSS-only solution by adding a wrapper element.

```
<div class="select-field">
    <select>
        <option></option>
        <option></option>
    </select>
</div>
```

### Batch Product Search

The Batch Product Search service works with request and results. You can add multiple criteria's and/or product numbers to a request and resolve them in an optimized way. An optimizer groups multiple equal criteria's into one and performs the search.

```php
$criteria = new Critera();
$criteria->addCondition(new CategoryCondition([3]));
$criteria->limit(3);

$anotherCriteria = new Critera();
$anotherCriteria->addCondition(new CategoryCondition([3]));
$anotherCriteria->limit(5);

$request = new BatchProductNumberSearchRequest();
$request->setProductNumbers('numbers-1', ['SW10004', 'SW10006']);
$request->setCriteria('criteria-1', $criteria);
$request->setCriteria('criteria-2', $anotherCriteria);

$result = $this->container->get('shopware_search.batch_product_search')->search($request, $context);

$result->get('numbers-1'); // ['SW10004' => ListProduct, 'SW10006' => ListProduct]
$result->get('criteria-1'); // ['SW10006' => ListProduct, 'SW10007' => ListProduct, 'SW10008' => ListProduct]
$result->get('criteria-2'); // ['SW10009' => ListProduct, 'SW10010' => ListProduct, 'SW10011' => ListProduct, 'SW10012' => ListProduct, 'SW10013' => ListProduct]
```

### Partial facets

`\Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface` marked as deprecated and replaced by `\Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface`.
Each facet handler had to revert the provided criteria by their own to remove customer conditions. This behaviour is now handled in the `\Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch::createFacets`

Old implementation:
```
/**
 * @param FacetInterface $facet
 * @param Criteria $criteria
 * @param ShopContextInterface $context
 * @return BooleanFacetResult
 */
public function generateFacet(
    FacetInterface $facet,
    Criteria $criteria,
    ShopContextInterface $context
) {
    $reverted = clone $criteria;
    $reverted->resetConditions();
    $reverted->resetSorting();

    $query = $this->queryBuilderFactory->createQuery($reverted, $context);
    //...
}
```

New implementation:
```
public function generatePartialFacet(
    FacetInterface $facet,
    Criteria $reverted,
    Criteria $criteria,
    ShopContextInterface $context
) {
    $query = $this->queryBuilderFactory->createQuery($reverted, $context);
    //...
```

#### Elastic search
In the elastic search implementation the current filter behavior is controlled by the condition handlers. By adding an query as `post filter`, facets are not affected by this filter.
This behavior is checked over the `Criteria->hasBaseCondition` statement:
```
/**
 * @inheritdoc
 */
public function handle(
    CriteriaPartInterface $criteriaPart,
    Criteria $criteria,
    Search $search,
    ShopContextInterface $context
) {
    if ($criteria->hasBaseCondition($criteriaPart->getName())) {
        $search->addFilter(new TermQuery('active', 1));
    } else {
        $search->addPostFilter(new TermQuery('active', 1));
    }
}

```
This behavior is now controlled in the `\Shopware\Bundle\SearchBundleES\ProductNumberSearch`. To support the new filter mode, each condition handler has to implement the `\Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface`.
It is possible to implement this interface beside the original `\Shopware\Bundle\SearchBundleES\HandlerInterface`.
```
namespace Shopware\Bundle\SearchBundleES;
if (!interface_exists('\Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface')) {
    interface PartialConditionHandlerInterface { }
}

namespace Shopware\SwagBonusSystem\Bundle\SearchBundleES;

class BonusConditionHandler implements HandlerInterface, PartialConditionHandlerInterface
{
    const ES_FIELD = 'attributes.bonus_system.has_bonus';

    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof BonusCondition);
    }

    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addFilter(
            new TermQuery(self::ES_FIELD, 1)
        );
    }


    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addPostFilter(new TermQuery(self::ES_FIELD, 1));
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        if ($criteria->hasBaseCondition($criteriaPart->getName())) {
            $this->handleFilter($criteriaPart, $criteria, $search, $context);
        } else {
            $this->handlePostFilter($criteriaPart, $criteria, $search, $context);
        }
    }
}
```

### CookiePermission

Cookie permissions is now a part of shopware and you can configure it in the shop settings.

We implement a basic cookie permission hint. If you want to change the decision whether the item is displayed or not, overwrite the jQuery plugin in the `jquery.cookie-permission.js`.

### Shopping Worlds

Shopping World have been technically refactored from the ground up to improve the overall performance when adding several elements to a shopping world. It is now possible to export and import shopping worlds via the backend.
You can also convert shopping worlds to presets for reusability of configured shopping worlds. Please see Developer Docs article for further information.

#### ComponentHandler

The processing of elements has been changed from events to classes of component handler.

**Before: Subscribe to an event and process element data in the callback method**

```php
public static function getSubscribedEvents()
{
    return ['Shopware_Controllers_Widgets_Emotion_AddElement' => 'handleSideviewElement'];
}
```

**After: Create new class and tag it as `shopware_emotion.component_handler` in your `services.xml`**

```php
class SideviewComponentHandler implements ComponentHandlerInterface
{
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === 'emotion-component-sideview';
    }

    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        // do some prepare logic
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        // do some handle logic and fill data
        $element->getData()->set('key', 'value');
    }
}
```

#### Requesting items in ComponentHandler

To make use of the performance improvement, you have to split your logic into a prepare step and handle step. The prepare step collects product numbers or criteria objects which will be resolved across all elements at once. The handle step provides a collection with resolved products and can be filled into your element.

```php
public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
{
    $productNumber = $element->getConfig()->get('selected_product_number');
    $collection->getBatchRequest()->setProductNumbers('my-unique-request', [$productNumber]);
}

public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
{
    $product = current($collection->getBatchResult()->get('my-unique-request'));
    $element->getData()->set('product', $product);
}
```

Keep in mind to use a unique key for requesting and getting products. For best practise, use the element's id in your key (`$element->getId()`).
