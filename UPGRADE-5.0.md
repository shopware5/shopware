# CHANGELOG for Shopware 5.0.x

This changelog references changes done in Shopware 5.0 patch versions.

## 5.0.4 (2015-09-16)

[View all changes from v5.0.3...v5.0.4](https://github.com/shopware/shopware/compare/v5.0.3...v5.0.4)

* Change file extension of `Shopware_Components_Convert_Excel::generateXML` to .xls
* Fixed jsonrenderer for backend order batchprocessing
    
## 5.0.3 (2015-08-24)

[View all changes from v5.0.2...v5.0.3](https://github.com/shopware/shopware/compare/v5.0.2...v5.0.3)

* The variant API resource now supports the getList method. It will return all variants with prices and attributes. You can optionally calculate the gross price by using the "considerTaxInput" parameter.
* The getList method of the articles API resource now returns additionally the attributes of an article.
* Change event argument property `name` to `vouchername` in `Shopware_Modules_Basket_AddVoucher_FilterSql` in `sBasket.php` due to reserved word.
* Support for batch user deleting in Backend UserManager
* Added `createMediaField` to Emotion Component Model
* Added open graph and twitter meta tags to a new block `frontend_index_header_meta_tags_opengraph` in the `header.tpl`

## 5.0.2 (2015-07-20)

[View all changes from v5.0.1...v5.0.2](https://github.com/shopware/shopware/compare/v5.0.1...v5.0.2)

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

## 5.0.1 (2015-05-26)

[View all changes from v5.0.0...v5.0.1](https://github.com/shopware/shopware/compare/v5.0.0...v5.0.1)

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

## 5.0.0 (2015-04-27)

* \sArticles::sGetProductByOrdernumber result is now equals with the \sArticles::sGetPromotionById result.
* Add console command `sw:refresh:search:index` to manually regenerate the search index. The optional parameter `--clear-table` can be used to clear the index tables before regenerating the data again.
* Remove `price` field override in AjaxSearch controller.
* Include `frontend/listing/product-box/product-price.tpl` template in ajax search to display product prices.
* \Shopware\Bundle\SearchBundleDBAL\ConditionHandler\HasPriceConditionHandler now joins the prices as a 1:1 association for a performance improvement.
* sCategories::sGetCategoryContent function returns no more the category articleCount. Variable is unused.
* sCategories::sGetCategoryIdByArticleId function use now the s_articles_categories table.
* Add __redirect parameter in frontend language switcher. Each language switcher requires now an additionally post parameter to redirect to the new shop `<input type="hidden" name="__redirect" value="1">`
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
