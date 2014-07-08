# Shopware Upgrade Information
In this document you will find a changelog of the important changes related to the code base of Shopware.

## 4.4.0
* Merged `_default` template into the `_emotion` template
* Removed the template directory `_default` and all it's dependencies
* Added the ability to show campaign banners in blog categories
* Refactored the template structure of the compare functionality. The plugin now uses based on a widget.
* Removed support for flash banners. The associated template block `frontend_listing_swf_banner` is marked as deprecated
* Removed the template files for the feed functionality, which was marked as deprecated in SW 3.5
* Add new optional address fields to the register account and checkout process
* Added global messages template component to display e.g. error or success messages
* Added global css classes for different device viewports
* New checkout process:
    * `Shopware_Controllers_Frontend_Account::ajaxLoginAction` is deprecated
    * `Shopware_Controllers_Frontend_Account::loginAction` usage to load a login page is deprecated. Use `Shopware_Controllers_Frontend_Register::indexAction` instead for both registration and login
* New jQuery plugin helper which provides all the basic operations every jQuery plugin needs to do
* Added several javascript libraries that enhance the supported features of the IE 8 and above
* Added `controller_action` and `controller_name` smarty functions that return the correspondent variable values
* Added device type detection support. Supports external detection (ie. varnish, apache, recommended) or internal using 3rd party lib
    * Added Mobile Detect (http://mobiledetect.net/) library to composer dependencies for internal mobile detection.
    * Added `Enlight_Controller_Request_RequestHttp::getDeviceType()`
    * Added `device` Smarty function
* The sitemap.xml uses now a smarty template
    * Added `Turnover by device type` in the backend statistics module
    * Added device type details to `Impressions` and `Visitors` in the backend statistics module
* Added secureUninstall method for plugins. There will be a message box when capability 'secureUninstall' is set, which uninstall method should be used.
    * (new) Bootstrap::secureUninstall() -> should be used for removing only non-user data
    * (old) Bootstrap::uninstall() -> old logic
* The ArticleList was merged with the former MultiEdit plugin. Plugins hooking the ArticleList-Controller or extending the ArticleList backend module will most probably break
* When using `selection` configurator type, shipping estimations will only be displayed when the user selects a value for all groups
* It's no longer possible to disable variant support for article that still have variants
* Added a new Theme Manager 2.0 with the possibility to create custom themes from the backend
    * Themes now support specific snippets that are used exclusively in the theme to which they belong
* The snippet module in the backend now supports editing multiple translations for a single snippet at once
* Forms: elements of type `text2` now support `;` as a separator between labels for the first and second field:
    * Responsive template: labels are used separately as `placeholder` attribute for each `input` element
    * legacy templates: `;` is replaced with a `/` and used in a single `label` element (old behaviour)
* `street number` fields were removed from interfaces and database
    * Existing values were merged into the `street` field
    * `street` fields were enlarged to 255 chars to accommodate this.
    * The API still accepts `street number` values on write operations. The values are internally merged into the `street` field. This is legacy support, and will be removed in the future.
    * Read operations on the API no longer return a `street number` field.
* Shop configuration contains no more the template selection. The shop template selection is only available in the new theme manager 2.0.
* The configuration for the thumbnail size of the product images in the "last seen products" module takes no effect on the responsive template. The size now changes by screen size.
* The registration and checkout workflows have been redesigned for the new template
* Changed behavior of the `selection` configurator. Configurator options which have none available product variant disabled now in the select-tag. The new snippet `DetailConfigValueNotAvailable` can be used to append additional text after the value name.
* Variant's `additional text` field is now automatically generated using the configurator group options. This can be optionally disabled
* The sBasket::sGetNotes function is refactored with the new shopware service classes and calls no more the sGetPromotionById function.
* The article slider now supports sorting by price (asc and desc) and category filtering
* Removed `table` and `table_factory` from container.
* The old table configurator was removed and replaced by the new image configurator in the emotion and responsive template.
* Template inheritance using `{extends file="[default]backend/..."}` is no longer supported and should be replaced by `{extends file="parent:backend/..."}`
* Added [Guzzle](https://github.com/guzzle/guzzle).
* Added HTTP client `Shopware\Components\HttpClient\HttpClientInterface`.
    * Can be fetched from the container using the key `http_client`.
* Deprecated Zend Framework components `Zend_Rest` and `Zend_Http`.
    * Will be removed in the next minor release.
    * Use `http_client` from container instead.
* Increased minimum required PHP version to PHP >= 5.4.0.
* When duplicating articles in the backend, attributes and translations will also be copied
* When applying main data to variants, translations will also be overwritten, if selected
* It is now possible to rename variant configurator options

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
* sGetArticleById result no longer contains the sConfiguratorSelection property. sConfiguratorSelection previously contained the selected variant data, which can now be accessed directly in the first level of the sGetArticleById result.
* sConfigurator class exist no more. The configurator data can now selected over the Shopware\Bundle\StoreFrontBundle\Service\Core\ConfiguratorService.php. To modify the configurator data you can use the sGetArticleById events.
* The new shopware core selects all required data for sGetArticleById, sGetPromotionById and sGetArticlesByCategory. The following events and internal functions not used in these functions any more
    * sGetPromotionById events
        * Shopware_Modules_Articles_GetPromotionById_FilterSql
    * sGetPromotionById functions
        * sGetTranslation
        * sGetArticleProperties
        * sGetCheapestPrice
        * sCalculatingPrice
        * calculateCheapestBasePriceData
        * getArticleListingCover
    * sGetAritcleById events
        * Shopware_Modules_Articles_GetArticleById_FilterSQL
    * sGetAritcleById functions
        * sGetTranslation
        * sGetPricegroupDiscount
        * sGetPromotionById (for similar and related products)
        * sCheckIfEsd
        * sGetPricegroupDiscount
        * sCalculatingPrice
        * sGetCheapestPrice
        * sGetArticleConfig
        * calculateReferencePrice
        * sGetArticlePictures
        * sGetArticlesVotes
        * sGetArticlesAverangeVote
        * sGetArticleProperties
    * sGetArticlesByCategory events
        * Shopware_Modules_Articles_sGetArticlesByCategory_FilterSql
        * Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopStart
        * Shopware_Modules_Articles_sGetArticlesByCategory_FilterLoopEnd
    * sGetArticlesByCategory functions
        * sGetSupplierById
        * sGetCheapestPrice
        * sCalculatingPrice
        * calculateCheapestBasePriceData


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
