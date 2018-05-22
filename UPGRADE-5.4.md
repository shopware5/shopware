# CHANGELOG for Shopware 5.4.x

This changelog references changes done in Shopware 5.4 patch versions.

## 5.4.5

[View all changes from v5.4.4...v5.4.5](https://github.com/shopware/shopware/compare/v5.4.4...v5.4.5)

### Additions

* Added anonymization of IP addresses, activated by default

### Changes

* Changed xml files in `engine/Library/Zend/Locale/Data` to be more up-to-date

## 5.4.4

[View all changes from v5.4.3...v5.4.4](https://github.com/shopware/shopware/compare/v5.4.3...v5.4.4)

### Additions

* Added newsletter registration check
* Added Double-Opt-In information for newsletter registrations
  * Added database column `double_optin_confirmed` in `s_campaigns_mailaddresses` and `s_campaigns_maildata`
  * Added `Opt-In confirmed` column in backend recipients overview
* Added debug logs to deprecated methods which will be removed in 5.5. The using of deprecated methods will create debug logs, if Shopware is not running in production mode.
* Added possibility to config elements to override ``queryMode`` option
* Added workaround for disabled localStorage in browser
* Added the following arguments to `notify` event `Shopware_CronJob_RefreshSeoIndex_CreateRewriteTable`: 
    * `shopContext` – The context of the shop being processed
    * `cachedTime` – `\DateTime` instance used for the new entries
* Added Smarty block `frontend_register_back_to_shop_button` to `themes/Frontend/Bare/frontend/register/index.tpl`
* Added Smarty blocks to `themes/Frontend/Bare/frontend/listing/actions/action-filter-facets.tpl`:
  * `frontend_listing_actions`
  * `frontend_listing_actions_facet`
* Added filter event `TemplateMail_CreateMail_Available_Theme_Config` to allow extension of theme variables made available to the mail templates 
* Added new configuration to set shopware store timeout and connection_timeout
* Added JS, less and theme template files to md5 filecheck
* Added OpenSans woff2-Files to responsive fonts
* Added button to the customer backend module to unlock a locked customer

### Changes

* Changed Tinymce editor to resolve placeholder images on initialization
* Changed product notification to match the documented feature
* Changed AJAX search to respect the basic setting for direct number searches and show the same results as the normal search
* Changed information of backend recipients overview
  * `Double-Opt-In date` is now `Register date`, which doesn't display the Double-Opt-In confirmation date anymore
* Changed TemplateMail to work without shop context or shops without templates
* Changed Symfony version to v2.8.41
* Changed ReflectionHelper to work with Windows
* Changed `Unknown path` Smarty error to work with Windows
* Changed `Shopware\Recovery\UpdateFilePermissionChanger` to make it PHP 7 compatible
* Changed `manufacturerNumber` field type in elasticsearch to improve search for manufacturer numbers
* Changed default sorting of the customer list to the order of creation
* Changed behaviour of closeout condition to work with product streams
* Changed Font-Face settings to fix rendering if `OpenSans` is locally available
* Changed following classes to use interface instead class as typehint
    * `Shopware\Bundle\SearchBundleDBAL\ConditionHandler\HeightConditionHandler`
    * `Shopware\Bundle\SearchBundleDBAL\ConditionHandler\ImmediateDeliveryConditionHandler`
    * `Shopware\Bundle\SearchBundleDBAL\ConditionHandler\LengthConditionHandler`
    * `Shopware\Bundle\SearchBundleDBAL\ConditionHandler\WeightConditionHandler`
    * `Shopware\Bundle\SearchBundleDBAL\FacetHandler\CategoryFacetHandler`
    * `Shopware\Bundle\SearchBundleDBAL\FacetHandler\ImmediateDeliveryFacetHandler`
    * `Shopware\Bundle\SearchBundleDBAL\FacetHandler\ProductDimensionsFacetHandler`
    * `Shopware\Bundle\SearchBundleDBAL\ListingPriceSwitcher`
* Changed `Media` resource to fix a problem with file names set via API
* Changed behaviour of unchecked ToS-checkbox in checkout to hint the missing input, especially on iOS
* Changed privacy policy checkbox setting to automatically activate privacy policy text being displayed
* Changed PluginManager rangeDownloadAction and extractAction to no longer use the provided URL parameters

## 5.4.3

[View all changes from v5.4.2...v5.4.3](https://github.com/shopware/shopware/compare/v5.4.2...v5.4.3)

### Additions

* Added new events to `Shopware_Controllers_Widgets_Listing::listingCountAction` to modify view variables before the template is fetched
  * `Shopware_Controllers_Widgets_Listing_fetchListing_preFetch`
  * `Shopware_Controllers_Widgets_Listing_fetchPagination_preFetch`
* Added a new category for the basic setting containing privacy options
* Added possibility to enable/disable data protection information texts
* Added theme data to the mail context
* Added cron job for deleting canceled orders
* Added cron job for deleting accountless users

### Changes

* Changed input validation to fix possible authenticated SQLi vulnerability in the backend
* Changed .htaccess file to prohibit download of .env files
* Changed behaviour of media/temp files, which now will be deleted if they are uploaded
* Changed "Send E-Mails" checkbox in batch processing window of order to be enabled by default again
* Changed behaviour of inactive forms to act like any other missing page
* Changed API behaviour on update, when the lastStock parameter is set for a product its applied to its mainDetail aswell (like on creation)
* Changed newsletter recipient count to work correctly with customer streams
* Changed position of several privacy options to a new basic setting category "Privacy"
* Changed search indexer to make the keyword batch size configurable using the key `search.indexer.batchsize` in the `config.php` 

### Removals

* Removed now empty "Cookie hint" basic setting category in backend

## 5.4.2

[View all changes from v5.4.1...v5.4.2](https://github.com/shopware/shopware/compare/v5.4.1...v5.4.2)

### Additions

* Added possibility to enable/disable forms without having to delete them
* Added pagination to the attribute filter of the product stream configurator 
* Added `json` attribute for snippets in `Enlight_Components_Snippet_Resource`
  * You may now set the attribute `json='true'` on smarty snippets, the content of the snippet will then be encoded via `json_encode()`
    * Example: ```{s json='true' name='foo'}é"'#-_*+`{/s}``` will render as ```"\u00e9\"'#-_*+`"```
    * Safely constructing a JS object: ```{ "someProp": {s json='true' name='your/snippet'}{/s} }```

### Changes

* Changed password verification process for password protected actions in backend
* Changed behaviour of search indexer to allow product attribute search
* Changed hashing algorithm for product variant search join table aliases to prevent errors on 32bit systems
* Changed SMTP password input type in base config from plaintext to password
* Changed detail page behaviour with preselection variants which onsale-flag is active
* Changed note counting to fix an error which displays 0 notes when adding the first note
* Changed construction of ProductSearchResult-object to fix error in stream listing count

## 5.4.1

[View all changes from v5.4.0...v5.4.1](https://github.com/shopware/shopware/compare/v5.4.0...v5.4.1)

### Additions

* Added new optional parameter `$filterGroupIds` to `PropertyGatewayInterface.php` for proper property sorting in a listing
* Added variant filtering to selection product streams
* Added `queryMode` option to select- and comboboxes in `engine/Shopware/Controllers/Backend/Config.php`
* Added theme configuration information to newsletter templates
* Added Smarty blocks to `newsletter/index/index.tpl`:
  * `newsletter_index_start`
  * `newsletter_index_doctype`
  * `newsletter_index_html_attributes`
  * `newsletter_index_index_head`
  * `newsletter_index_body_attributes`
  * `newsletter_index_table`
  * `newsletter_index_table_inner`
  * `newsletter_index_table_inner_header`
  * `newsletter_index_table_inner_content`
  * `newsletter_index_table_inner_footer`
  * `newsletter_index_log`
* Added Smarty blocks to `newsletter/index/footer.tpl`:
  * `newsletter_footer_table_upper`
  * `newsletter_footer_table_navigation`
  * `newsletter_footer_table_tax_notice`
  * `newsletter_footer_table_lower`
  * `newsletter_footer_table_lower_links`
* Added Smarty blocks to `newsletter/index/header.tpl`:
  * `newsletter_header`
  * `newsletter_header_content`
  * `newsletter_header_content_logo`
  * `newsletter_header_content_title`
* Added view variable `sCategoryContent` to the `listingCountAction` of the controller `Shopware_Controllers_Widgets_Listing`

### Changes

* Changed Smarty to improve error message when a template extends a parent template and said parent does not exist
* Changed cloning of `Enlight_Template_Manager` object to preserve reference of the security policy to the instance  
* Changed newsletter logo from a static file to the active themes logo
* Changed condition in `sBasket::sGetAmountRestrictedArticles`, it now also checks for an empty `$articles` array
* Changed `ImmediateDeliveryConditionHandler` (DBAL and ES) and product indexing to improve variant filtering  

### Removals

* Removed deprecated blocks `frontend_detail_data_liveshopping` and `frontend_detail_data_price_info` from `frontend/detail/data.tpl`

## 5.4.0

[View all changes from v5.3.7...v5.4.0](https://github.com/shopware/shopware/compare/v5.3.7...v5.4.0)

### Additions

* Added database field `s_articles_details.laststock` to be able to define per variant if said variant is available when the stock is lower or equal to 0
* Added `lastStock` field to `\Shopware\Models\Article\Detail`
* Added database field `garbage_collectable TINYINT(1) DEFAULT 1` to table `s_media_album` to define if an album is to be considered by the `sw:media:cleanup` command. The flag can be toggled in the album settings.
* Added product box layout selection support for manufacturer listings
* Added destroy method to `swJumpToTab` jQuery plugin
* Added option to discard Less/Javascript files of extended themes. [More information](https://developers.shopware.com/designers-guide/theme-startup-guide/#theme.php)
* Added multi-select feature when assigning variant configurations to product images
* Added variant configuration information in the image information panel
* Added DIC parameters:
    - `shopware.release.version`
        The version of the Shopware installation (e.g. '5.4.0')
    - `shopware.release.version_text`
        The version_text of the Shopware installation (e.g. 'RC1')
    - `shopware.release.revision`
        The revision of the Shopware installation (e.g. '20180081547')
* Added new service in the DIC containing all parameters above 
    - `shopware.release`
        A new struct of type `\Shopware\Components\ShopwareReleaseStruct` containing all parameters above
* Added several paths to the DIC:
	- `shopware.plugin_directories.projectplugins` 
		Path to project specific plugins, see [Composer project](https://github.com/shopware/composer-project)
	- `shopware.template.templatedir`
		Path to the themes folder
	- `shopware.app.rootdir`
		Path to the root of your project
	- `shopware.app.downloadsdir`
		Path to the downloads folder
	- `shopware.app.documentsdir`
		Path to the generated documents folder
	- `shopware.web.webdir`
		Path to the web folder
	- `shopware.web.cachedir`
		Path to the web-cache folder 
	
	These paths are configurable in the `config.php`, see `engine/Shopware/Configs/Default.php` for defaults

* Added all additional article columns to product import/export
* Added backend config option `logMailLevel` to choose the minimum log level for sending e-mail notifications
* Added snippet `frontend/detail/data/DetailDataPriceInfo` in ajax cart template
* Added snippet `frontend/detail/DetailCommentAnonymousName` for anonymous product ratings
* Added block `frontend_checkout_ajax_cart_prices_info` in `frontend/checkout/ajax_cart.tpl`
* Added config `preLoadStoredEntry` to `Shopware.form.field.PagingComboBox` to be compatible with saving and loading entries from e.g. the second page.
* Added order attributes to return values of `OrderRepository::getDetails`
* Added option for batch updating plugins to plugin update command
* Added defaults for `ignored_url_parameters` setting of HTTP cache in `config.php`. See [Ignore some HTTP parameters](https://developers.shopware.com/developers-guide/http-cache/#ignore-some-http-parameters) for more information. 
* Added optional `id` parameter to `getTemplatesAction` in `engine/Shopware/Controllers/Backend/Emotion.php` to allow fetching of a single template
* Added new filter event `Shopware_Controllers_Backend_Emotion_Detail_Filter_Values` to `Shopware/Controllers/Backend/Emotion.php` to allow manipulation of elements

### Changes

* Changed the event selectors to make them configurable in the `swJumpToTab` jQuery plugin
* `\Shopware\Bundle\SearchBundle\ProductSearchResult::__construct` requires now the used Criteria and ShopContext object
* Changed route to POST to be more HTTP compliant
* Changed all writing actions to POST to be more HTTP compliant.
    * Checkout actions:
        - `finish`
    
    * Basket actions
        - `addArticle`
        - `addAccessories`
        - `addPremium`
        - `changeQuantity`
        - `deleteArticle`
        - `setAddress`
        - `ajaxAddArticle`
        - `ajaxAddArticleCart`
        - `ajaxDeleteArticle`
        - `ajaxDeleteArticleCart`
        
* Changed JSONP requests to JSON in the following Frontend controllers:
    * Controller List
        - Frontend/AjaxSearch.php
        - Frontend/Checkout.php
        - Frontend/Compare.php
        - Frontend/Note.php
        - Widgets/Listing.php
* Changed the paging in a listing so that using it while using the live filter reloading will now scroll to the top paging bar
* Changed name field in product ratings to be optional
* Changed loading of the themes/_private folder to be always executed
* Changed the `checkOrderStatus` method in `Shopware_Controllers_Backend_Order` to only send e-mails when necessary
* Changed `themes/Backend/ExtJs/backend/order/controller/batch.js` to inform the user about configuration errors
* Changed `themes/Backend/ExtJs/backend/order/view/batch/form.js` to allow for more precise form validation and better feedback to the user
* Changed rounding of prices to two decimal digits in `engine/Shopware/Bundle/StoreFrontBundle/Service/Core/PriceCalculator.php` and `engine/Shopware/Core/sArticles.php`
* Changed Mpdf version to 6.1.4 and added it to the autoloader, so a `require()` isn't necessary anymore
* Changed Symfony version to 2.8.34

### Removals

* Removed config option for maximum number of category pages
* Removed "Force http canonical url" setting in basic settings as it is obsolete
* Removed config option `template_security['enabled']` for toggling smarty security
* Removed config option `blogcategory` and `bloglimit`
* Removed the "Show more products" button beneath an emotion when the category itself has no products to be shown
* Removed support for separate SSL host and SSL path. Also the `Use SSL` and `Always SSL` options were merged.
    * Removed database fields
        - `s_core_shops.secure_host`
        - `s_core_shops.secure_base_path`
        - `s_core_shops.always_secure`
        
    * Removed methods
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::setSecureHost`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::getSecureHost`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::setSecurePath`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::getSecurePath`
        - `\Shopware\Components\Routing\Context::getSecureHost`
        - `\Shopware\Components\Routing\Context::setSecureHost`
        - `\Shopware\Components\Routing\Context::getSecureBaseUrl`
        - `\Shopware\Components\Routing\Context::setSecureBaseUrl`
        - `\Shopware\Components\Routing\Context::isAlwaysSecure`
        - `\Shopware\Components\Routing\Context::setAlwaysSecure`
        - `\Shopware\Models\Shop\Shop::getSecureHost`
        - `\Shopware\Models\Shop\Shop::setSecureHost`
        - `\Shopware\Models\Shop\Shop::getSecureBasePath`
        - `\Shopware\Models\Shop\Shop::setSecureBasePath`
        - `\Shopware\Models\Shop\Shop::getSecureBaseUrl`
        - `\Shopware\Models\Shop\Shop::setSecureBaseUrl`
        - `\Shopware\Models\Shop\Shop::getAlwaysSecure`
        - `\Shopware\Models\Shop\Shop::setAlwaysSecure`

    * Changed methods
        - `\Shopware\Components\Theme\PathResolver::formatPathToUrl`
           The method signature no longer contains the `isSecureRequest` parameter

### Deprecations

* Deprecated `forceSecure` and `sUseSSL` smarty flags. They are now without function.
* Deprecated constants `Shopware::VERSION`, `Shopware::VERSION_TEXT` and `Shopware::REVISION`, they will be removed in Shopware v5.6. This information can now be retrieved from the DIC.
    * New, alternative DIC parameters:
        - `shopware.release.version`
            The version of the Shopware installation (e.g. '5.4.0')
        - `shopware.release.version_text`
            The version_text of the Shopware installation (e.g. 'RC1')
        - `shopware.release.revision`
            The revision of the Shopware installation (e.g. '20180081547')
    * New, alternative DIC service:
        - `shopware.release`
            A new struct of type `\Shopware\Components\ShopwareReleaseStruct` containing all parameters above 
* Deprecated `lastStock` field in `\Shopware\Models\Article\Article` as the field has been moved to the variants. It will be removed in 6.0.
* Deprecated `laststock` column in `s_articles` since this field has been moved to the variants. It will be removed in 6.0
* Deprecated `articleId` column in `s_articles_attributes` table, it will be removed in Shopware version 5.5 as it isn't used anymore since version 5.2
* Deprecated SEO support for the following AJAX routes, see `themes/Frontend/Bare/frontend/index/index.tpl`:
    - `/checkout/ajaxCart`
    - `/register/index`
    - `/checkout/addArticle`
    - `/widgets/Listing/ajaxListing`
    - `/checkout/ajaxAmount`
    - `/address/ajaxSelection`
    - `/address/ajaxEditor`
* Deprecated `\Shopware\Models\Order\Document\Type`, use `\Shopware\Models\Document\Document` instead. The old document type will be removed with 5.5.
