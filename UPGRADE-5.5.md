# CHANGELOG for Shopware 5.5.x

This changelog references changes done in Shopware 5.5 patch versions.

## 5.5.10

[View all changes from v5.5.9...v5.5.10](https://github.com/shopware/shopware/compare/v5.5.9...v5.5.10)

### Additions

* Added support for environments using `open_basedir` for restricting access to the filesystem

### Changes

* Changed `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway::get` to support `int` and `array` parameters

### Deprecations

* Deprecated support for `array` parameters in `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway::get`, use `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway::getList` instead

## 5.5.9

[View all changes from v5.5.8...v5.5.9](https://github.com/shopware/shopware/compare/v5.5.8...v5.5.9)

### Additions

* Added option to "I am" select field in basic configuration
* Added paragraph to license.txt regarding the permission to relicense plugins
* Added cartItem to event `Shopware_Modules_Basket_UpdateArticle_Start`
* Added OPCache options `opcache.use_cwd` and `opcache.validate_root` to the system info's requirements tab
* Added more GTIN formats for itemprops in `frontent/detail/content/header.tpl`
* Added following events to `sBasket`
    * `Shopware_Modules_Basket_DeleteNote_Start`
    * `Shopware_Modules_Basket_UpdateArticle_FilterSqlDefaultParameters`
    * `Shopware_Modules_Basket_UpdateCartItems_Updated`
    * `Shopware_Modules_Basket_BasketCleared`
    * `Shopware_Modules_Basket_DeleteArticle_Start`
    * `Shopware_Modules_Basket_DeletedArticle`
    * `Shopware_Modules_Basket_getTaxesForUpdateProduct_FilterReturn`
* Added new blocks to `frontend/checkout/ajax_cart.tpl` for inactive links to checkout:
    * `frontend_checkout_ajax_cart_open_checkout_inner_unavailable` 
    * `frontend_checkout_ajax_cart_open_basket_unavailable`
* Added the following public methods to `Shopware\Models\Order\Repository`:
    * `getDetailsQueryBuilder`
    * `getPaymentsQueryBuilder`
    * `getDocumentsQueryBuilder`
* Added product number and open action to backend product stream module
* Added new column `pseudo percent` in article detail module
* Added new interface `ReflectionAwareInterface`

### Changes

* Changed meta-tag `viewport` to allow zooming on mobile devices
* Changed the usage of translation for shop pages in the side menu in mobile view
* Changed the `worstRating` for Google's struct data
* Changed `SnippetManager` to consider plugin directories and theme directories in `readFromIni` mode
* Changed `Checkout` controller, to resolve race condition problems on confirm page
* Changed the album selection in the media manager for the blog images to show all images
* Changed `Blog` and `Listing` controllers to throw an exception if a blog category-id is passed to the `Listing` controller or vice versa
* Changed `EsBackendIndexer` to improve support for unicode characters
* Changed `mediaselectionfield` to also work with enabled translation in tabs
* Changed RSS templates to contain correct language code
* Changed `BatchProcess` to fix an issue with `removeString` operator
* Changed the handling of custom-page attribute translations
* Changed the handling of the grouping for shop pages
* Changed custom-page attributes to be translated properly
* Changed shop page attributes to be translatable
* Changed Symfony library to version 3.4.27
* Changed buttons in empty off-canvas baskets to be disabled
* Changed button for mobile-view rating to work properly 
* Changed cache file permission definition in `\Enlight_Template_Manager`
* Changed Article and Category API endpoints to support translation of attributes with underscores
* Changed the order of the columns in article module
* Changed the listing for the category-teaser to also list main categories
* Changed template `register/personal_fieldset.tpl` to not show the 'No account' checkbox on signup form

### Deprecations

* Deprecated `sBasket::clearBasket`, use `sBasket::sDeleteBasket` instead
* Deprecated `Shopware\Models\Article\Download::getSize` and `Shopware\Models\Article\Download::setSize`. Use `Shopware\Models\Media\Media::getFileSize` and `Shopware\Models\Media\Media::setFileSize` instead
* Deprecated column `s_articles_downloads.size`. Use `s_media.file_size` instead

## 5.5.8

[View all changes from v5.5.7...v5.5.8](https://github.com/shopware/shopware/compare/v5.5.7...v5.5.8)

### Additions

* Added new config `alwaysShowMainFeatures` to show the template from `mainfeatures` in the whole checkout process
* Added configuration for displaying shipping costs pre calculation in off canvas shopping cart
* Added wrapping smarty blocks to `documents/index.tpl`
* Added condition for not displaying basic price if it is a product with graduated prices
* Added new block `frontend_account_order_item_detail_info_wrapper` to `account/order_item_details.tpl`
* Added missing attribute accessor methods to `CustomerStream` model
* Added rich-snippets for `priceValidUntil` , `url`, `image` and  `gtin13`
* Added new block `frontend_index_header_meta_tags_inner` to `frontend/index/header.tpl`
* Added preselected checkbox option to 'Deactivate No Customer Account' in basic configuration
* Added an upload compatibility check for plugins
* Added smarty function `http_build_query` to the list of allowed functions
* Added Elasticsearch mapping debug option to show all mapping data 

### Changes

* Changed HTTPCache to fix issues with the first request of a URL when debugging is active
* Changed url plugin to resolve base-url on CLI
* Changed variant switch to consider url parameters without value
* Changed `CategoryProvider` to skip excluded categories from sitemap 
* Changed `CookieSubscriber` to clear session value for `userInfo` after a login
* Changed article REST api to properly deal with configuration option positions
* Changed shipping costs pre calculation display configuration
* Changed `HreflangService` to only consider active subshops
* Changed availability filter for ES condition
* Changed `ProductListingVariationLoader::fetchAvailability` to consider only given variants
* Changed cachebuster parameter for images in backend to contain the datetime of the last change
* Changed `font-display` value to `swap` for improved font rendering performance
* Changed `CdnOptimizerService` to work with external filesystems
* Changed `sAdmin::sLogout` to refresh ShopContext on logout
* Changed ESD download to send headers only once, fixing issues in IE and Safari browsers
* Changed role selection in rule management to work with roles from the second page
* Changed backend log viewer to work properly
* Changed conversion of resource to service id with CamelCase-Name
* Changed duplicated variable assignment in `frontend/checkout/header.tpl`
* Changed blog meta description length from 150 to global used meta description length
* Changed deprecation on `Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface`. It was deprecated since 5.3.0, that deprecation got removed

### Deprecations

* Deprecated the class `Shopware_Components_Benchmark_Point`. It will be removed in 5.6 without replacement.
* Deprecated the class `Shopware_Components_Benchmark_Container`. It will be removed in 5.6 without replacement.
* Deprecated the class `Shopware_Controllers_Backend_Search::getArticles`. It will be removed in 5.7, use the ProductRepository instead.
* Deprecated the class `Shopware_Controllers_Backend_Search::getOrders`. It will be removed in 5.7, use the OrderRepository instead.
* Deprecated the class `Shopware_Controllers_Backend_Search::getCustomers`. It will be removed in 5.7, use the CustomerRepository instead.
* Deprecated the function `sArticles::sCheckIfEsd` as it is unused. It will be removed in 5.7 without replacement.

### Custom file extension whitelist

Shopware by default only allows a fixed list of known file extensions to be uploaded to the server using the MediaManger. If you need to upload files with an extension not in this internal list, you can now add necessary file extensions in your `config.php`:

```php
<?php
return [
    ...
    'media' => [
        'whitelist' => [
            'docx',
            'xslx'
        ],
    ]
];
```

### Debug Elasticsearch Mapping

Shopware adds a `_source` to reduce the size of the indices by default. By setting the `debug` option to true, you can get every field back by Elasticsearch.

```php
<?php
return [
    ...
    'es' => [
        'debug' => true,
    ]
];
```

For security reasons, some extensions like php, cgi, com, exe are not allowed in the whitelist.

## 5.5.7

[View all changes from v5.5.6...v5.5.7](https://github.com/shopware/shopware/compare/v5.5.6...v5.5.7)

### Additions

* Added a license synchronization button in Plugin Manager
* Added a standardized way to filter certain entities from being exported to the sitemap, or have custom URLs exported as well (see paragraph "Excluding URLs from sitemap and adding custom URLs" below)
* Added new blocks `frontend_listing_actions_paging_inner` to `listing/actions/action-pagination.tpl` and `frontend_listing_actions_sort_inner` to `listing/actions/action-sorting.tpl`
* Added `shopId` to the `Shopware_Controllers_Seo_filterCounts` Event
* Added `laststock` to "Apply Standard Data"
* Added blocks "frontend_index_header_css_screen_stylesheet" and `frontend_index_header_javascript_jquery_lib_file`
* Added button to order module detail window, to open customer directly
* Added new option to AdvancedMenu to improve performance when not using customer groups
* Added clearing of note entries, that are older than a year, by the "CronRefresh" cronjob

### Changes

* Changed the default value in column `s_core_auth.lockeduntil` to `2010-01-01 00:00:00`
* Changed order module sorting to consider sorting also when elasticsearch is activated
* Changed `StaticUrlProvider` to prevent duplicate URLs
* Changed minibasket to also update amount/number badge when the last product is being removed from offcanvas basket
* Changed libraries in Updater-app to most recent versions
* Changed initial BI teaser to only show after a few days
* Changed `jquery.datepicker.js` to initialize component value by element value on init 
* Changed return array of `\Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES5::getNotAnalyzedField` to fix warning thrown on indexing in an Elasticsearch 5 server
* Changed double cache testing in advanced menu
* Changed `ReflectionHelper` to improve support for Composer projects
* Changed filters in blog categories can now be hidden in mobile view
* Changed plugin CLI commands to clear relevant caches
* Changed jQuery preloader plugin to fix reset function
* Changed SQL query in risk management to avoid block nested loops
* Changed `GarbageCollector` to consider links
* Changed the type of the `docId` column in `s_order_documents` to VARCHAR, the models and backend module have been changed accordingly
* Changed pluginPath assignment in `\Shopware\Commands\PluginDeleteCommand::execute` to delete plugins located in "/custom/plugins/" correctly
* Changed the backend search for chars like 'ß'
* Changed following service ids to lower-case:
    * `Loader`
    * `Hooks`
    * `Modelconfig`
    * `EventSubscriber`
    * `OrderHistorySubscriber`
    * `CategorySubscriber`
    * `CategoryDuplicator`
    * `CategoryDenormalization`
    * `MediaSubscriber`

### Deprecations

* Deprecated the global `$Shopware` variable in templates. It will be removed in 5.6 without replacement.
* Deprecated the following classes:
    * `\Shopware\Components\Plugin\XmlMenuReader`
    * `\Shopware\Components\Plugin\XmlCronjobReader`
    * `\Shopware\Components\Plugin\XmlPluginInfoReader`
    * `\Shopware\Components\Plugin\XmlConfigDefinitionReader`
    They have been replaced in Shopware 5.6 with new implementations in the namespace [Shopware\Components\Plugin\XmlReader](https://github.com/shopware/shopware/tree/5.6/engine/Shopware/Components/Plugin/XmlReader)

### Removals

* Removed `appendSession` parameter from router due to possible security implications. Use the `OptinService` to store the session name and id internally and use the generated hash in combination with a ListenerService instead to restore the session where necessary
* Removed duplicate folder renaming in `PluginExtractor`

### Excluding URLs from sitemap and adding custom URLs

With Shopware 5.5.0, we added a better way to handle a lot of sitemap URLs. 
Unfortunately this way we also dropped the support for customizing the URLs being used in a sitemap.

With Shopware 5.5.7, we've reimplemented a way to deal with unwanted URLs, as well as a way to add custom URLs to your
generated sitemaps.

Just add the example configuration mentioned below into your `config.php` file.

```php
<?php
return [
    ...
    'sitemap' => [
        'excluded_urls' => [
            [
                // Possible resources:
                // - product (\Shopware\Models\Article\Article::class)
                // - campaign (\Shopware\Models\Emotion\Emotion::class)
                // - manufacturer (\Shopware\Models\Article\Supplier::class)
                // - blog (\Shopware\Models\Blog\Blog::class)
                // - category (\Shopware\Models\Category\Category::class)
                // - static (\Shopware\Models\Site\Site::class)
                'resource' => \Shopware\Models\Article\Article::class,

                // The ID for the entity mentioned above (e.g. 5 excludes the URL for the product with ID 5).
                // If left empty (or 0), the whole resource will be skipped for URL generation
                'identifier' => '',
                
                // The ID of the shop to which this exclusion applies. If left empty (or 0), this applies for every shop
                'shopId' => 0
            ]
        ],
        'custom_urls' => [
            [
                // The custom URL
                'url' => 'https://myCustomUrl.de',

                // Date in format Y-m-d H:i:s
                'lastMod' => '2019-01-01 00:00:00',

                // How frequently the page is likely to change
                // Possible values: always, hourly, daily, weekly, monthly, yearly, always
                'changeFreq' => 'weekly',

                // The priority of this URL relative to other URLs on your site
                // Must be a value between 0 and 1
                'priority' => 0.5,

                // The ID for the shop to which this custom URL applies. If left empty (or 0), this applies for every shop
                'shopId' => 2,
            ]
        ]
    ]
];
```

* Deprecated the unspecific request params assignment to view in `\Shopware_Controllers_Widgets_Listing::productsAction` and `\Shopware_Controllers_Widgets_Listing::streamAction`. Use a *PostDispatchEvent to assign necessary variables in a plugin. The assignment will be removed in 5.6

## 5.5.6

[View all changes from v5.5.5...v5.5.6](https://github.com/shopware/shopware/compare/v5.5.5...v5.5.6)

### Changes

* Changed the selector for the emotion-wrapper in `jquery.emotion.js` to fix an issue with emotions using the 'resize' layout
* Changed SQL in migration to fix possible issues

## 5.5.5

[View all changes from v5.5.4...v5.5.5](https://github.com/shopware/shopware/compare/v5.5.4...v5.5.5)

### Additions

* Added attributes to shop entities
* Added missing countries to list of available countries (table `s_core_countries`)
* Added new event `TemplateMail_CreateMail_MailContext` to `engine/Shopware/Components/TemplateMail.php`
* Added new event `Shopware_Modules_Basket_CheckBasketQuantities_ProductsQuantity`
* Added new config option to define Elasticsearch version
* Added internal locking to sitemap generation so the sitemap isn't generated multiple times in parallel
* Added event `plugin/swAjaxVariant/onHistoryChanged` in in order to listen to changes in browser history after variants did change
* Added blocks `frontend_account_order_item_pseudo_price_inner` and `frontend_account_order_item_pseudo_price_inner_value` to `frontend/account/order_item_details.tpl` to modify price if necessary
* Added filter event `Shopware_Modules_Basket_CheckBasketQuantities_ProductQuantities` in order to modify variables for each basket item
* Added `max_expansions` in config.php for the `phrase_prefixes` `name` and `number`
* Added aggregation size to product attribute range sliders
* Added product attribute parser for elasticsearch indexing to allow indexing of boolean values
* Added alias `shopware.api.customerstream` to service with id `shopware.api.customer_stream` to fix problem in cronjob
* Added new property `showIdColumn` to `Shopware.grid.Panel` to add the possibility of showing the id column
* Added `CustomEventPolyfill` for IE 11 on Windows 7
* Added article details to api variant endpoint
* Added `IN` and `NOT IN` operators for filters in backend's article overview
* Added a new event `onRequestDataCompleted` to jQuery plugin `swAjaxVariant`
* Added support for attribute html fields in the media garbage collector
* Added block 'backend/search/index/result' to the backend search result template
* Added loading of payment attributed to the frontend
* Added new console command `sw:refresh:topseller` to refresh the topsellers
* Added help text icon and support text icon to ExtJs HTML fields
* Added new template blocks `frontend_account_order_item_pseudo_price_inner` and `frontend_account_order_item_pseudo_price_inner_value` in `account/order_item_details.tpl`
* Added the interfaces to the classes in the StoreFrontBundle where they are missing
* Added `setTemplateName` and `getTemplateName` methods to `Enlight_Components_Mail`

### Changes

* Changed all occurences of snippets embedded in strings
    * The snippets content now gets assigned to a variable instead
    * This may lead to broken links or text, in case a snippet contains escaped quotemarks (`\"`), since escaping them is not necessary anymore
* Changed Symfony library to version 3.4.21
* Changed non-idempotent basket actions to forward the current request
* Changed user timeout to session `session.gc_maxlifetime`
* Changed itemprop `priceCurrency` to be available for products with price groups
* Changed `ajaxListing` action to `listingCount` action in Bare theme
* Changed backend filter for variants with laststock
* Changed namespace for plugins in custom/project directory to fix theme recognition in composer projects
* Changed calculation of conversion rate in backend widget
* Changed wrong exception instantiation in `\Shopware\Components\Thumbnail\Manager`
* Changed snippet namespace parsing to improve compatibility to windows installations 
* Changed Querybuilder to fix compatibility with PHP7 on cygwin
* Changed `\Shopware_Controllers_Backend_Base::addAdditionalTextForVariant` to show variant text on new order position
* Changed the jQuery swRegister plugin to use the correct input ids for input validation if they were changed by data attributes
* Changed `\Shopware_Controllers_Backend_Application::resolveExtJsData` to use constants instead of numbers in ORM relation mapping
* Changes category links in HTML sitemap to now also be translated
* Changed filter for `data-` attributes
* Changed display of translatable checkbox attributes in backend to show the fallback value if no translation is configured
* Changed sitemap export to contain the correct lastmod in sitemap entries of products
* Changed the internally stored last exported product ids to prevent duplicate exports of single products in the sitemap
* Changed the reset of the account number when saving a costumer
* Changed the overwrite protection time zone when saving an order detail or deleting one
* Changed Plugin Manager in backend to also show strikethrough prices of plugins

### Removals

* Removed unused `Shopware.data.ClassCache` ExtJs class

### Changed occurences of snippets embedded in strings

Wherever a snippet's content was embedded inside a string, it's value now gets assigned to a variable instead. This may lead to broken links or text, in case a snippet contains escaped quotemarks (`\"`), since escaping them is not necessary anymore.

Please check all snippets with escaped quotation marks after upgrading.

### Defining Elasticsearch version to reduce calls

By default, Shopware makes an `info` request to the Elasticsearch backend to be able to determine the version of Elasticsearch that is being used. In high load environments, this can create unnecessary additional load on all services due to the slight overhead these requests create.

Starting with Shopware 5.5.5, it is possible to define the version of Elasticsearch being used in the `config.php` like described below. Doing so will keep Shopware from making these `info` requests.

```php

<?php
return [
     ...
     'es' => [
         ...
         'version' => '5.6.5',
     ],
];
```

## 5.5.4

[View all changes from v5.5.3...v5.5.4](https://github.com/shopware/shopware/compare/v5.5.3...v5.5.4)

### Additions

* Added the following new blocks to `themes/Frontend/Bare/widgets/captcha/custom_captcha.tpl`
    * `frontend_widgets_captcha_custom_captcha_config`
    * `frontend_widgets_captcha_custom_captcha_honeypot`
* Added dependency to Symfony Expression Language, this allows using something like `container.initialized('shop') ? service('shop') : null` in `services.xml`
* Added `deleteDocument` ACL privilege to order
* Added new event `Shopware_Controllers_Backend_CustomerQuickView_listQuerySearchFields`
* Added forwarding to manufacturer listing for old supplier urls
* Added support for own product box layouts via an ExtJS Plugin
* Added AWS endpoint configuration
* Added product notification attributes
* Added `DISTINCT` for `priceListingQuery` to improve ES indexing performance with activated variant filter
* Added media optimizer overview to backend systeminfo
* Added `notify`-event `Shopware_Command_RebuildSeoIndexCommand_CreateRewriteTable` that is triggered after the SEO index is rebuilt via cli command `sw:rebuild:seo:index`
* Added privilege check for sensitive data of API's user endpoint
* Added functionality to restore expert mode status on snippet manager loading
* Added possibility to create a customized product box layout with own ExtJS plugin
* Added product information to `sARTICLEAVAILABLE` template
* Added country filter to customer module
* Added country entity to attributes multiselect
* Added changetime to ListProduct struct
* Added events indicating plugin-lifecycle changes to PluginInstaller
* Added grunt development task

### Changes

* Changed Symfony library to version 3.4.19
* Changed new countries created via programming interfaces default to allowing shipping to restore backwards compatibility to versions before Shopware 5.5.3.
* Changed `themes/Frontend/Bare/widgets/captcha/custom_captcha.tpl` to also include the honeypot captcha template if the corresponding option is active
* Changed `themes/Frontend/Responsive/frontend/_public/src/js/jquery.captcha.js`, so the plugin won't try to fetch the honeypot captcha template via AJAX
* Changed the following templates to include the `custom_captcha.tpl` template, if any other captcha method than "legacy" is active:
    * `themes/Frontend/Bare/frontend/blog/comment/form.tpl`
    * `themes/Frontend/Bare/frontend/detail/comment/form.tpl`
    * `themes/Frontend/Bare/frontend/newsletter/index.tpl`
    * `themes/Frontend/Bare/frontend/tellafriend/index.tpl`
* Changed seo product query to clarify select statement
* Changed document view to hide document boxes on new entry
* Changed `CronjobSynchronizer` to consider action names without Cronjob Prefix
* Changed `Enlight_Template_Manager` to allow overriding file permissions
* Changed API variant resource to compare configuration groups in lowercase
* Changed type annotation on a product's EAN field
* Changed ES backend `OrmBacklogSubscriber` to fix backlog sync
* Changed adding items to the cart to use laststock from variant
* Changed structured data properties on blog article page to meet the requirements
* Changed default groupKey to get correct subpages of custom pages in mobile menu
* Changed some mailer options to combo boxes to avoid wrong entries
* Changed CSV import of snippets to only remove one apostrophe from the beginning of a line 
* Changed field trackingcode to type "text" to be able to save multiple trackingcodes
* Changed open-sans file size
* Changed mailer config elements to combo box to simplify configuration
* Changed `Kernel::getRootDir` to use `dirname` instead of `realpath`
* Changed backend search to consider order documents
* Changed customer orders to translate order state
* Changed product preview combobox to show only active shops
* Changed EventManager to improve plugin phpunit capability
* Changed regex in media manager to filter all symbols
* Changed cart item information to use laststock from variant instead product
* Changed media normalizer to consider model objects
* Changed `clear_cache.sh` to use `rsync` if available for faster cache clearing

### Removals

* Removed IE history check from ajax variants
* Removed wrong css class from password reset page

## 5.5.3

[View all changes from v5.5.2...v5.5.3](https://github.com/shopware/shopware/compare/v5.5.2...v5.5.3)

### Additions

* Added shipping country restrictions
* Added ESD Article support for REST API
* Added where-Condition for ES SubPriceQuery to improve indexing speed with active variant filter
* Added filter event `Shopware_Components_Document_Render_FilterMpdfConfig` to be able to filter the mpdf config for document rendering
* Added the `renderer=html` GET-parameter to be able to debug documents
* Added button to unlock backend users in the detail view
* Added new payment api resource
* Added fileSize column to table view of media manager
* Added possibility to use boolean keywords when defining boolean attributes
* Added shop site as possible entities in attributes
* Added translation fallback for mails

### Changes

* Change order overwrite protection to fix false positive message
* Changed backend session loading to fix issues with incomplete locale object
* Changed model definition of order status to allow setting id
* Changed multi select entities in attributes to allow shop sites
* Changed "Save" on newsletter page is now being disabled on submit
* Changed Shopware RSS Feed to use php default stream context
* Changed topseller generation so it now also works when topseller are deactivated

## 5.5.2

[View all changes from v5.5.1...v5.5.2](https://github.com/shopware/shopware/compare/v5.5.1...v5.5.2)

### Additions

* Added the following events for newsletter un/subscription
    * filter:
        * `Shopware_Controllers_Frontend_Newsletter_sendMail_FilterVariables`
        * `Shopware_Modules_Admin_sendMail_FilterVariables`
    * notify:
        * `Shopware_Modules_Admin_Newsletter_Unsubscribe`
        * `Shopware_Modules_Admin_sUpdateNewsletter_Subscribe`
* Added possibility to edit index.max_result_window for ES via `config.php`
* Added new block `frontend_listing_box_article_image_attributes` in `listing/product-box/product-image.tpl`
* Added `Retry-After` header for maintenance mode
* Added new config flag **isCustomStore** for the `Shopware.apps.Base.view.element.Select` to create a store with a custom **"valueField"**.
* Added new smarty block `widgets/emotion/index/classes` to file `widgets/emotion/index.tpl`
* Added config `backward_compatibility.predictable_plugin_order` with default `false`. Enabling this loads plugins in alphabetical order instead of an undefined one.
* Added command `sw:es:backend:index:cleanup` to delete old ES backend indices
* Added numeric amounts for order details in the account orders action
* Added pagination to backend order filter shipping country and billing country
* Added numeric amounts for cart items in cart array structure
* Added new notify event `Shopware_CronJob_Notification_Product_QueryBuilder` to the notification plugin to be able to change the QueryBuilder
* Added new filter event `Shopware_Modules_Basket_InsertDiscount_FilterParams` to the basket discount functionality
* Added service `\Shopware\Components\StreamProtocolValidator` (service id: `shopware.components.stream_protocol_validator`) to validate stream protocols in URLs
* Added method `resetShop` to `Shopware/Components/Snippet/Manager.php` to reset the assigned shop
* Added event `Shopware_Modules_Admin_GetDispatchBasket_Calculation_QueryBuilder` to change the possible dispatches for the basket (SW-22646)
* Added confirmation dialog when deleting or overwriting a order document
* Added streamId translation of categories
* Added "not in" compare to product stream attributes
* Added information to systeminfo to show time difference between MySQL and PHP
* Added Polish language to installer
* Added amountNumeric and priceNumeric to order items in template
* Added numeric amounts for basket items
* Added column "active" for product feed-list in the backend
* Added mapping for `voteAverage.average` for elastic search indexing
* Added proper helpText to 'displayOnlySubShopVotes' configuration
* Added possibility to remove supplier images via REST API
* Added unit test for manufacturers api resource
* Added instance check for ES category facet
* Added random sorting option to emotion products slider
* Added support for configurable lengths of the meta description
* Added `getListQueryBuilder` method to `Shopware/Models/Order/Repository.php`
* Added plugin manager reloading on plugin update failure
* Added `CheapestPriceESGateway` to be able to use the price filter with ES
* Added new filter event `Shopware_Controllers_Article_CreateConfiguratorVariants_FilterData` to article backend controller to modify variant data during generation
* Added new property `pageShortParameter` in `jquery.infinite-scrolling.js` to add compatibility with a custom sPage query alias in the product listings

### Changes

* Changed thumbnail variable on detail page for href-attribute from `sArticle.image.src.1` to `sArticle.image.source`
* Changed failed login behaviour by only increasing the failedlogin-count of active customer accounts, not guest accounts
* Changed product saving to handle invalid changetime dates
* Changed document template `themes/Frontend/Bare/documents/index.tpl` to also render a `department` if it is part of the address
* Changed FormSynchronizer to consider sorting from config.xml
* Changed the `instock` column in the product variants list to be sortable
* Changed `emotion/components/component_banner_slider.tpl` to reset thumbnail URLs correctly
* Changed Symfony library to version 3.4.17
* Changed jQuery plugin `swRegister` to correctly use data attributes
* Changed VariantHelper to work also on search page
* Changed `controllerAction` and `controllerName` Smarty functions to sanitize action and controller names
* Changed session name validator to be more rigid
* Changed VariantFilter to work with different customer group then fallback customer group
* Changed `detailAction` method of `Emotion` backend controller to not add the shop base URL to media URLs again if it's already set
* Changed statistic call to also encode `&` in `referer` parameter
* Changed menu-scroller to hide scrollbar on IE 11
* Changed Media Resource of REST API to deal with uploads that contain a ftp file-path
* Changed product module preview combobox to preselect the default shop
* Changed API media resource to support `file://` paths
* Changed sub-albums to inherit thumbnail settings on creation
* Changed ajax variant jquery plugin have a own function for pushing history states
* Changed default value of required in config.xsd to match `XmlConfigDefinitionReader`
* Changed `JsonRequest` to use `getRawBody()` instead of `php://input`
* Changed `Shopware.apps.Voucher.model.Tax` to explicit define field types

### Removals

* Removed trailing slash from LESS variable `@font-directory`
* Removed private method `Shopware_Controllers_Widgets_Emotion::getEmotionsByCategoryId`
* Remove unused `ajax_validate_billing` case in `jquery.register.js`

## 5.5.1

[View all changes from v5.5.0...v5.5.1](https://github.com/shopware/shopware/compare/v5.5.0...v5.5.1)

### Changes

* Changed the loading of shopping worlds to fix issues with missing shopping worlds

## 5.5.0

[View all changes from v5.4.6...v5.5.0](https://github.com/shopware/shopware/compare/v5.4.6...v5.5.0)

### Additions

* Added unique identifier to `s_core_documents` for document types in order to create a unique, settable property for plugin developers and enabling risk free user editing of the name field
* Added new emotion component handlers:
    - `HtmlCodeComponentHandler`
    - `HtmlElementComponentHandler`
    - `IFrameComponentHandler`
    - `YoutubeComponentHandler`
* Added `mainDetail` to REST API call for retrieving a list of products
* Added struct `Shopware\Components\Cart\Struct\CartItemStruct` to represent items in the cart during calculation
* Added public function `sBasket::updateCartItems` to provide a new way of interacting with cart updates
* Added following templates to `themes/Frontend/Bare/frontend/checkout/`
    * `cart_item_premium_product.tpl`
    * `cart_item_product.tpl`
    * `cart_item_rebate.tpl`
    * `cart_item_surcharge_discount.tpl`
    * `cart_item_voucher.tpl`
    * `confirm_item_premium_product.tpl`
    * `confirm_item_product.tpl`
    * `confirm_item_rebate.tpl`
    * `confirm_item_surcharge_discount.tpl`
    * `confirm_item_voucher.tpl`
    * `finish_item_premium_product.tpl`
    * `finish_item_product.tpl`
    * `finish_item_voucher.tpl`
* Added ability to translate categories
* Added ability to translate shop forms
* Added ability to translate shop pages. Please rename the key of the old groups ("gLeft", "gBottom" etc.) to "left", "bottom", "bottom2", "disabled" and translate the pages.
* Added hreflang-tag support to translated pages
    * Added configuration to "Seo / Router" to disable href-lang or show only language instead of language and locale
    * Added configuration to "Seo / Router" to select a language shop for the x-default tag
* Added payment and dispatch translation for order status mails
* Added snippets for locales in backend menus
* Added implementation of elasticsearch backend
    * Added `EsBackendBundle` to index and search products, customers and orders for the backend
    * Added new searcher, reader and repositories to AttributeBundle for implementing elasticsearch backend
        * `engine/Shopware/Bundle/AttributeBundle/Repository`
            * `Reader/OrderReader.php`
            * `Searcher/OrderSearcher.php`
            * `CustomerRepository.php`
            * `OrderRepository.php`
    * Added indexing and backlog sync command
        * `engine/Shopware/Bundle/EsBackendBundle/Commands/IndexPopulateCommand.php`
        * `engine/Shopware/Bundle/EsBackendBundle/Commands/SyncBacklogCommand.php`
    * Added new templates to `themes/Backend/ExtJs/backend/search/` to split the `index.tpl`
        * `articles.tpl`
        * `customers.tpl`
        * `orders.tpl`
    * Added new config parameters `write_backlog`, `enabled` and `backend` to `es` parameter
* Added attributes to shop forms page
* Added new column `changed` with `DEFAULT NULL` to tables `s_order` and `s_user`
* Added checks for changes on products, customers and orders in backend while a user saves them to prevent an overwriting of changes made by someone else
* Added proportional calculation of tax positions
    * New configuration option in Basic Settings => Checkout, "Proportional calculation of tax positions", inactive by default
    * Added `Shopware\Components\Cart\ProportionalTaxCalculator` to calculate proportional taxes for the cart items
    * Added `Shopware\Components\Cart\BasketHelper` to to add items to the cart that need to be calculation in a proportional way
    * Added `Shopware\Components\Cart\ProportionalCartMerger` to merge proportional cart items into one cart item
    * For the proportional tax calculation to work with vouchers and modes of dispatch, be sure to set the mode of tax calculation to "auto detection" in their settings
    * Added new filter event to modify proportional vouchers `Shopware_Modules_Basket_AddVoucher_VoucherPrices`
* Added new column `invoice_shipping_tax_rate` to s_order, to save exact dispatch shipping tax rate
* Added `Shopware\Components\DependencyInjection\LegacyPhpDumper` to support old container events such as `Enlight_Bootstrap_InitResource_*`
* Added new column `articleDetailsID` to table `s_order_details`
* Added new `sqli` privilege to product feed to restrict access on custom filters
* Added MySQL 8.0 support. See paragraph [MySQL 8 workaround](#user-content-mysql-8-workaround) for details
* Added additional filesystem adapter implementations for services out of the box:
    * Amazon Web Services
    * Google Cloud Platform
* Added service `shopware.filesystem.public` and `shopware.filesystem.private` for file handling
    * Documents, ESD and Sitemap files can now also served from S3 or Google Cloud
    * Added service ``shopware.filesystem.public.url_generator`` for generating public urls
* Added automatic prefixed filesystem service registration for plugins
    * `plugin_name.filesystem.public`
    * `plugin_name.filesystem.private`
* Added sitemap splitting with entries over 50.000.
    * sitemaps can be now generated by cronjob, live or `sw:generate:sitemap` command
    * sitemaps will be now served from cache and compressed with gzip
    * Added new DI tag `sitemap_url_provider` to add custom sitemap url provider
    * Added new interface `Shopware\Bundle\SitemapBundle\UrlProviderInterface` for url providers
* Added new DIC parameter `shopware.es.batchsize` (configurable via `config.php`) to change the number of products that are send to elasticsearch in one batch
* Added confirm dialog when changing variant price with a price scale
* Added services and classes to support dynamic (time-based) cache invalidation via the HttpCache plugin
    * Added `InvalidationDateInterface` and implementations
        - `AbstractInvalidationDate`
        - `BlogDate`
        - `BlogListingDate`
        - `ListingDate`
        - `ListingDateFrontpage`
        - `ProductDetailDate`
    * Added services
        - `CacheRouteGenerationService`
        - `DefaultRouteService`
    * Added `CacheTimeServiceInterface` and implementations
        - `DefaultCacheTimeService`
        - `DynamicCacheTimeService`
    * Added `invalidation_date_provider` tag to the DIC
* Added parameter mode to Log module, to directly open the systemlogs tab
* Added possibility to set `sTarget` for Detail/ratingAction
* Added support in `config.php` to specify TrustedHeaderSet options for Symfony
* Added user attributes to PDF documents
* Added shop selection to emotions, to limit emotion to a specific shop
* Added block `frontend_account_order_item_status_value_custom` to `Bare/frontend/account/order_item.tpl`
* Added new smarty blocks `frontend_checkout_cart_item_rebate_name_wrapper`, `themes/Frontend/Bare/frontend/checkout/items/rebate.tpl` and `frontend_checkout_cart_item_rebate_tax_price_wrapper` to `themes/Frontend/Bare/frontend/checkout/items/rebate.tpl`
* Added event ``KernelEvents::TERMINATE``, which will be fired after the response has been sent, when the kernel terminates
* Added event `Shopware_Emotion_Collect_Emotion_Component_Handlers` to add component handler on legacy plugin system
* Added filter event `Legacy_Struct_Converter_Convert_Product_Price` to `Shopware\Components\Compatibility\LegacyStructConverter::convertProductPriceStruct`
* Added filter event `Shopware_Components_Document_Render_FilterHtml` to `Shopware_Components_Document::render`
* Added option to allow multiple documents of the same type for orders in `engine/Shopware/Components/Document.php`
* Added new notify event `Shopware_Modules_Admin_GetDispatchBasket_QueryBuilder` to the sAdmin which will be fired for change the sGetDispatchBasket query builder. 
* Added new notify event `Shopware_Modules_Basket_GetAmountArticles_QueryBuilder` to the sBasket which will be fired for change the sGetAmountArticles query builder.
* Added new notify event `Shopware_Modules_Admin_GetPremiumDispatches_QueryBuilder` to the sAdmin which will be fired for change the sGetPremiumDispatches query builder.
* Added support for multiple document type mail templates for every order document
    * Added `Shopware_Controllers_Backend_Order::getMailTemplatesAction`
    * Added `Shopware\Models\Mail\Mail::isDocumentMail`
    * Added `Shopware\Models\Mail\Repository::getMailsListQueryBuilder`

### Changes

* Changed the execution model of `replace` hooks to prevent multiple calls of the hooked method, if more than one `replace` hook on the same method exists and all of them call `executeParent()` once
* Changed Symfony version to 3.4.15
* Changed jQuery version to 3.3.1
* Changed Slugify version to 3.1
* Changed the event `Shopware_Form_Builder` so that the `reference` contains the `BlockPrefix` of the Formtype, not the name
* Changed `s_order_documents` column `ID` to `id` *on new installations of 5.5*. See paragraph [MySQL 8 workaround](#user-content-mysql-8-workaround) for details
* Changed REST API `articles` list call to include `mainDetail`
* Changed `themes/Frontend/Bare/frontend/checkout/cart_item.tpl` in which the following blocks are contained:
    * `frontend_checkout_cart_item_product`
    * `frontend_checkout_cart_item_premium_product`
    * `frontend_checkout_cart_item_voucher`
    * `frontend_checkout_cart_item_rebate`
    * `frontend_checkout_cart_item_surcharge_discount`
    All these blocks are moved to own template files to optimize include process. Please be aware that these changes make it necessary to change templates that extend `cart_item.tpl`.
* Changed `themes/Frontend/Bare/frontend/checkout/confirm_item.tpl` and `finish_item.tpl` to keep track of the earlier mentioned changes and additions to `cart_item.tpl` and to use Smarty Inheritance system correctly.
    Please check your templates when you extend `cart_item.tpl`. You now have to extend one of the added subtemplates.
* Changed `country_id` to `countryId` and `state_id` to `stateId` in `Shopware.apps.Customer.model.Address`
* Changed xml files in `engine/Library/Zend/Locale/Data` to be more up-to-date
* Changed rebates and vouchers to also show their icon in the cart for values > 1€
* Changed basic settings option `Extended SQL query`, so users now need the `sql_rule` permission of `shipping` to edit it.
* Changed following classes constructor to accept `IteratorAggregate` instead `array` for `tagged` services collections:
    * `Shopware\Bundle\AttributeBundle\Repository\Registry`
    * `Shopware\Bundle\CustomerSearchBundleDBAL\HandlerRegistry`
    * `Shopware\Bundle\EmotionBundle\Service\EmotionElementService`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\CompositeSynchronizerFactory`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\ShopIndexerFactory`
    * `Shopware\Bundle\MediaBundle\MediaServiceFactory`
    * `Shopware\Bundle\MediaBundle\OptimizerService`
    * `Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory`
    * `Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch`
    * `Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch`
    * `Shopware\Bundle\SearchBundleDBAL\QueryBuilderFactory`
    * `Shopware\Bundle\SearchBundleES\DependencyInjection\Factory\ProductNumberSearchFactory`
    * `Shopware\Components\Captcha\CaptchaRepository`
    * `Shopware\Components\DependencyInjection\Bridge\Router`
    * `Shopware\Components\Emotion\Preset\PresetDataSynchronizer`
* Changed `getRolesAction` in `Shopware/Controllers/Backend/UserManager` so it takes the `id` parameter into account. This is needed for the paging combobox to work properly.
* Changed elasticsearch indexing for ES6 compatibility:
    * Every type gets a own index
    * The `sw:es:analyze` and `sw:es:switch:alias` commands require a mapping type as a new argument.
    * Added a new mapping type argument to the constructor of the `ShopIndex`
    * Added a new mapping type argument to methods `createIndexConfiguration`, `createShopIndex` of the `IndexFactory` and the `IndexFactoryInterface`
    * Modified attributes mapping in the product mapping. Changed type of 'raw' field to keyword (>= 6).
    * Added method `supports` to the `\Shopware\Bundle\ESIndexingBundle\DataIndexerInterface` to be able to distinguish which index is for which mapping type
* Changed `ShopIndexer::createAlias`, it now deletes indexes that are named like the index alias
* Changed `Subscription expired` growl message, to hide it for a week
* Changed form controller to allow multiple receivers comma separated
* Changed behaviour of the `HttpCache` core plugin. The max-age for the cached content of shopping-worlds, blog-categories, product detail pages and blog detail pages is now set based on the activation date of the respective resource.
* Changed `Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\AddressHydrator` to correctly hydrate additionalAddressLine2
* Changed cache warmer behaviour by implementing a new procedure, which doesn't rely on SEO urls anymore, warms new url types and allows partial warming
    * Changed HttpCacheWarmer-Module in Backend to apply the new settings
    * Changed cache warmer to be extendable by implementing `Shopware\Components\HttpCache\UrlProvider\UrlProviderInterface` to your own UrlProvider
    * Changed sw:warm:http:cache CLI command by improving the printed information and adding new parameters:
    
       | Parameter             | Short | Description                                   |
       | --------------------- | ----- | --------------------------------------------- |
       | --category            | -j    | Warm up categories                            |
       | --emotion             | -o    | Warm up emotions                              |
       | --blog                | -g    | Warm up blog                                  |
       | --manufacturer        | -m    | Warm up manufacturer pages                    |
       | --static              | -t    | Warm up static pages                          |
       | --product             | -p    | Warm up products                              |
       | --variantswitch       | -d    | Warm up variant switch of configurators       |
       | --productwithnumber   | -z    | Warm up products with number parameter        |
       | --productwithcategory | -y    | Warm up products with category parameter      |
       | --extensions          | -x    | Warm up all URLs provided by other extensions |
* Changed visibility of `Shopware\Bundle\PluginInstallerBundle\Service\SubscriptionService::getPluginInformationFromApi()` to public
* Changed Double-Opt-In behaviour to redirect back into the checkout, if user registered from there
* Changed console.command tag CompilerPass to support lazy commands.
* Changed customer editing in backend to work also when customer is in optin mode
* Changed order mergeDocuments to send a valid Content-Type to fix downloads on some webserver configurations
* Changed voucher restrictions to allow product numbers shorter then 5 characters 
* Changed download strategies to work only on local adapter
* Changed installation process to generate a unique ESD key
* Changed blog categories to also redirect requests when being linked to an external site
* Changed event `Shopware_Modules_Admin_Login_Failure` to always contain parameter `email`

### Removals

* Removed class `Shopware\Models\Config\Element\Shopware_Components_Form`
* Removed class `Shopware_Components_Convert_Csv`, `Shopware_Components_Convert_Excel` and `Shopware_Components_Convert_Xml`
* Removed class `Shopware\Models\Customer\Billing` and `Shopware\Models\Customer\BillingRepository`
* Removed class `Shopware\Models\Customer\Shipping`
* Removed class `Shopware\Models\Order\Document\Type`
* Removed methods `getByCategory` and `getListByCategory` of interface `Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface`
* Removed methods `addAttribute` and `removeAttribute` of `Shopware\Components\Model\ModelManager`
* Removed methods `indexAction`, `listingAction`, `requestAction`, `detailAction`, `directAction`, `commitForm` of `Shopware_Controllers_Frontend_Ticket`
* Removed methods `emotionTopSellerAction` and `emotionNewcomerAction` of `Shopware_Controllers_Widgets_Emotion`
* Removed methods `getCategoryEmotionsQuery` and `getCategoryEmotionsQueryBuilder` of `Shopware\Models\Emotion\Repository`
* Removed methods `getPaymentsQuery`, `getPaymentsQueryBuilder` of `Shopware\Models\Payment\Repository`
* Removed method `getSourceSet` of `Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail`
* Removed method `assertVersionGreaterThen` of `Shopware_Components_Plugin_Bootstrap`
* Removed method `getSnippet` of `Shopware_Components_Snippet_Manager`
* Removed method `confirmAction` of `Shopware_Controllers_Frontend_Newsletter`
* Removed method `ajaxListingAction` of `Shopware_Controllers_Widgets_Listing`
* Removed method `sGetAffectedSuppliers` of `sArticles`
* Removed method `sCreateRewriteTableSuppliers` of `sRewriteTable`
* Removed method `save` of `Shopware\Models\Config\Form`
* Removed method `onUpdate` of `Shopware\Models\Media\Settings`
* Removed variables `billing` and `shipping` with methods `getShipping`, `setShipping`, `getBilling`, `setBilling` of `Shopware\Models\Customer\Customer`
* Removed variables `$sLanguage` and `sMultishop` with method `sGetMultishop` of `sExport`
* Removed variables `o_attr_1`, `o_attr_2`, `o_attr_3`, `o_attr_4`, `o_attr_5`, `o_attr_6` of `sOrder`
* Removed variable `label` and methods `getLabel` and `setLabel` of `Shopware\Models\Widget\Widget`
* Removed variable `preLoadStoredEntry` of `Shopware.form.field.PagingComboBox`
* Removed variable `description` with methods `setDescription` and `getDescription` of `Shopware\Models\Order\Status`
* Removed variable `sSelfCanonical` in `Shopware\Components\Compatibility\LegacyStructConverter`
* Removed constant `PAYMENT_STATE_THE_PAYMENT_HAS_BEEN_ORDERED_BY_HANSEATIC_BANK` of `Shopware\Models\Order\Status`
* Removed snippets `table/s_user_billingaddress_attributes` and `table/s_user_shippingaddress_attributes`
* Removed smarty block `frontend_blog_detail_comments` in `frontend/blog/detail.tpl`, use `frontend_blog_detail_comments_count` and `frontend_blog_detail_comments_list` instead.
* Removed smarty block `frontend_detail_data_block_prices_headline` in `frontend/detail/block_price.tpl`
* Removed smarty block `frontend_detail_buy_variant` in `frontend/detail/buy.tpl`
* Removed smarty block `frontend_index_header_css_ie` in `frontend/index/header.tpl`
* Removed smarty block `frontend_index_ajax_seo_optimized` in `frontend/index/index.tpl`
* Removed smarty block `frontend_index_categories_left_ul` in `frontend/index/sidebar-categories.tpl`
* Removed smarty block `frontend_listing_box_article_actions_more` in `frontend/listing/product-box/product-actions.tpl`
* Removed smarty block `frontend_listing_box_article_actions_inline` in `frontend/listing/product-box/product-actions.tpl`
* Removed ExtJs models `Shopware.apps.Customer.model.Billing` and `Shopware.apps.Customer.model.Shipping`
* Removed following unnecessary CompilerPasses due to Symfony 3.0 `tagged` attribute:
    * `Shopware\Bundle\AttributeBundle\DependencyInjection\Compiler\SearchRepositoryCompilerPass`
    * `Shopware\Bundle\CustomerSearchBundleDBAL\DependencyInjection\Compiler\HandlerRegistryCompilerPass`
    * `Shopware\Bundle\EmotionBundle\DependencyInjection\Compiler\EmotionComponentHandlerCompilerPass`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\DataIndexerCompilerPass`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\MappingCompilerPass`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SettingsCompilerPass`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\CompilerPass\SynchronizerCompilerPass`
    * `Shopware\Bundle\MediaBundle\DependencyInjection\Compiler\MediaAdapterCompilerPass`
    * `Shopware\Bundle\MediaBundle\DependencyInjection\Compiler\MediaOptimizerCompilerPass`
    * `Shopware\Bundle\SearchBundle\DependencyInjection\Compiler\CriteriaRequestHandlerCompilerPass`
    * `Shopware\Bundle\SearchBundleDBAL\DependencyInjection\Compiler\DBALCompilerPass`
    * `Shopware\Bundle\SearchBundleES\DependencyInjection\CompilerPass\SearchHandlerCompilerPass`
    * `Shopware\Components\DependencyInjection\Compiler\AddCaptchaCompilerPass`
    * `Shopware\Components\DependencyInjection\Compiler\EmotionPresetCompilerPass`
    * `Shopware\Components\DependencyInjection\Compiler\RouterCompilerPass`
* Removed following unnecessary Subscriber:
    * `Shopware\Bundle\EsBackendBundle\Subscriber\ServiceSubscriber`
    * `Shopware\Bundle\ESIndexingBundle\Subscriber\ServiceSubscriber`
* Removed event `Shopware_Controllers_Widgets_Emotion_AddElement`
* Removed Ioncube checks from PluginManager
* Removed `SwagLicense` dependency in plugin licenses

### Deprecations

* Deprecated `lastStock` field in `\Shopware\Models\Article\Article` as the field has been moved to the variants. It will be removed in 5.6
* Deprecated `laststock` column in `s_articles` since this field has been moved to the variants. It will be removed in 5.6
* Deprecated uppercase column `ID` in `s_order_documents`. It is lowercase `id` on new installations of 5.5 and will be renamed automatically if you are using MySQL 8 or in Shopware 5.6. See paragraph [MySQL 8 workaround](#user-content-mysql-8-workaround) for details
* Deprecated the translation workaround ("gLeft", "gBottom", "eLeft", "eBottom", etc.) for shop page groups. Please rename the key of the old groups ("gLeft", "gBottom" etc.) to "left", "bottom", "bottom2", "disabled" and translate the pages.
* Deprecated `Shopware_Controllers_Frontend_SitemapMobileXml` for mobile sitemaps. It will be removed in 5.6
* Deprecated `Shopware\Components\SitemapXMLRepository`. It will be removed in 5.6
* Deprecated `Shopware_Controllers_Frontend_SitemapXml` it redirects to sitemap_index.xml. Redirect will be removed with 6.0
* Deprecated `Shopware\Components\Plugin::registerCommands`. It will be removed in 5.7, use DI tag `console.command` instead

### Elasticsearch in backend

To activate elasticsearch in backend you have to enable the `es => backend => enabled` parameter in the `config.php` and start a indexation
of the backend entities with `sw:es:backend:index:populate`.

### MySQL 8 workaround

Due to a mixture of MySQL 8 and Doctrine constraints, the column `s_core_documents.ID` will be renamed to
`s_core_documents.id` on the fly if MySQL 8 is being used. To be able to do that, the service `\Shopware\Components\Compatibility\LegacyDocumentIdConverter`
was introduced, which is checked in the file `engine/Shopware/Models/Order/Document/Document.php` to determine if
a Doctrine model with uppercase or lowercase `id` needs to be used.

If you need to reference this column in your own model, we recommend to use the same workaround there. You can use the
same service (see above) with id `legacy_documentid_converter` for that.

The reason for this workaround is that MySQL 8 forces ids in foreign key constraints to be lower case.

This is a problem in current systems since we have an uppercase `ID` in table `s_order_documents`.
MySQL doesn't care if we use `ID` in the table and `id` in the constraint, but Doctrine needs both to be written 
in the same way. On new installations of Shopware 5.5 this is already the case, both are lowercase there.

So in order to support MySQL 8 on updates from older Shopware versions we need to change the case of the `id` column
in `s_order_documents`, which breaks support of blue/green deployments as older versions of Shopware (< 5.5) need
that column to be uppercase.

Since this change is only really necessary if you are using MySQL 8, it is only enforced when a MySQL 8 server is
detected. A downgrade to an older Shopware installation wouldn't be possible anyway in that case, as Shopware 5.4
does not support MySQL 8 yet.

If you want to make this migration offline, there is the command `sw:migrate:mysql8` to check if the migration was
executed and do so if you want.

The column `s_core_documents.id` will be lowercase from Shopware 5.6 forward.
