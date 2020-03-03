# CHANGELOG for Shopware 5.6.x

This changelog references changes done in Shopware 5.6 patch versions.

## 5.6.6

[View all changes from v5.6.5...v5.6.6](https://github.com/shopware/shopware/compare/v5.6.5...v5.6.6)

### Changes

* Changed `\Shopware\Components\DependencyInjection\Compiler\PluginResourceCompilerPass` to work correctly with multiple plugins

## 5.6.5

[View all changes from v5.6.4...v5.6.5](https://github.com/shopware/shopware/compare/v5.6.4...v5.6.5)

### Additions

* Added PHP 7.4 support
* Added new option `metaOptions` to S3 Adapter to set S3 options
* Added a filter event 'Shopware_Modules_Basket_AddVoucher_FilterSqlParams' to `sBasket::sAddVoucher` to modify sql params
* Added a notify event 'Shopware_Modules_Basket_AddVoucher_Inserted' to `sBasket::sAddVoucher` to execute code after a voucher was inserted
* Added new product fields to product exports: `metaTitle`, `pseudosales`, `notification`, `available_from`, `available_to`, `pricegroupActive`, `pricegroupID`
* Added `intl` extension to required php extensions
* Added unique index to `s_attribute_configuration` with columns `table_name` and `column_name`
* Added new Argon2 password encoder
* Added attributes to the following API resources
    * `CustomerGroup`
    * `Media`
    * `Country`
* Added new config option to also show technical urls in hreflang
* Added new less function `swhash` to get file hash for cache busting
* Added new event `Shopware_Modules_Admin_SaveRegister_BeforeRegister` to cancel customer registration
* Added new property to plugin base class `$autoloadViews` which allows pre registration of template folder
    * Its still required on backend extensions to call `extendsTemplate`

### Changes

* The `\Shopware\Components\StateTranslatorService` works case insensitive now
* Changed `Shopware.apps.Emotion.view.components.Base` to properly handle a checkbox default value
* Changed `\Shopware_Controllers_Backend_Base::getPaymentsAction` to optionally list all payment methods
* Changed `\Shopware\Components\Cart\CartPersistService` to also persist cart item attributes
* Changed blog statistics to work also with active http cache
* Changed `\Shopware\Components\Routing\Router::assemble` and `\Shopware\Components\Routing\Router::match` default implementation handling arrays and encoded strings
    * `Shopware\Components\Routing\Generators\DefaultGenerator::generate` will encode arrays as strings with `http_build_query`; objects result in an user error in 5.6 and Exception in 5.7
    * `Shopware\Components\Routing\Matchers\DefaultMatcher::match` will try to convert encoded string values back to array representation
* Changed `Shopware.form.field.SingleSelection` to forward `enable` and `disable` calls

### Removals

 * Removed usage of column `baseprice` in `engine/Shopware/Bundle/SearchBundleDBAL/ListingPriceHelper.php` 
 
### Deprecations

* Deprecated following methods of class `\Shopware\Components\CacheManager`
    * `getCoreCache`
    * `getDirectoryInfo`
    * `encodeSize`

## 5.6.4

[View all changes from v5.6.3...v5.6.4](https://github.com/shopware/shopware/compare/v5.6.3...v5.6.4)

### Changes

* Changed status of SLT-cookie from 'Comfort' feature to 'Technically required' when feature is active

## 5.6.3

[View all changes from v5.6.2...v5.6.3](https://github.com/shopware/shopware/compare/v5.6.2...v5.6.3)

### Additions

* Added customer attribute risk rules to risk management
* Added interface `ArrayAccess` to `Shopware\Bundle\StoreFrontBundle\Struct\Attribute`
* Added `attribute` to struct data after being converted by the `Shopware\Components\Compatibility\LegacyStructConverter`
* Added method `clearBody` to `Enlight_Components_Mail` to clear the plain and html body
* Added interfaces for all services in AttributeBundle
* Added `AllowInvalidArrayType` to Doctrine to fix deserialization error on mail sending
* Added missing template for `\Shopware\Bundle\ContentTypeBundle\Field\ComboboxField`
* Added jQuery event `plugin/swCookiePermission/onAcceptButtonClick` and `plugin/swCookiePermission/onDeclineButtonClick`
* Added option `__options_details` to order api update to toggle order position replace mode 
* Added setter and is methods for json renderer `formatDateTime` variable.
* Added multiple Smarty blocks to `frontend/plugins/index/delivery_informations.tpl`
* Added new Smarty block `frontend_detail_index_data_pricespecification` to `frontend/detail/content/buy_container.tpl`
* Added new Smarty blocks `frontend_includes_emotion`, `frontend_includes_emotion_inner`, `frontend_includes_emotion_template` and `frontend_includes_emotion_template_inner` to `frontend/_includes/emotion.tpl`
* Added new Smarty blocks `frontend_index_left_categories_wrapper` and `frontend_index_left_subcategory_config` to `frontend/index/sidebar.tpl`
* Added `Shopware\Bundle\CookieBundle`, which takes care of the new cookie consent manager. If you're using cookies in your plugin, make sure
to read [this documentation](https://developers.shopware.com/developers-guide/cookie-consent-manager/)!


### Changes

* Changed aria-label in `themes/Frontend/Bare/frontend/_includes/privacy.tpl` to remove html tags
* Changed `sAdmin::sRiskATTRIS` to allow any product attribute
* Changed `Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\GraduatedPricesGateway` to consider minimum purchase quantity
* Changed `\Shopware_Controllers_Frontend_Address::handleExtraData` to split `sessionKey` correctly
* Changed `StateManager.getScrollBarSize` to lazily get the size of the scrollbar
* Changed missing german translation in `snippets/backend/article_list/main.ini`
* Changed the `\Shopware\Models\Shop\Repository::getBaseListQueryBuilder`, `\Shopware\Models\Shop\Repository::getBaseListQuery` and `\Shopware\Models\Shop\Repository::getMainListQueryBuilder` methods. Added a boolean `$orderByShopPositionAsDefault` that can be set to true to filter by shop position instead of shop name
* Changed the Smarty block `frontend_detail_data_delivery`, added availability meta tags for the pre selected configurator products for configurator types 'Selection' and 'Image' 
* Changed the path value of following cookies, so they correctly consider the shop's base path
  * `session-<shopId>`
  * `partner`
  * `sUniqueID`
* Changed `unitize` mixin to only output rem values
* Changed `Shopware\Models\Customer\Customer`, set correct return type for `getregisterOptInId`
* Changed the `TemplateMail_CreateMail_MailContext` filter to work correctly.
* Changed `ProductServiceInterface` to extend from `ListProductServiceInterface`
* Changed additionAddressLine1 in `themes/Frontend/Bare/frontend/register/shipping_fieldset.tpl` to fix a typo
* Changed `\sArticles::sGetArticlePictures` to correctly return image thumbnail URLs again

### Removals

* Removed the smarty blocks `frontend_blog_bookmarks_delicious` and `frontend_blog_bookmarks_digg` and their content from `themes/Frontend/Bare/frontend/blog/bookmarks.tpl
* Removed unnecessary `extendsAction` from `Shopware_Controllers_Backend_ExtJs`
* Removed 'p' parameter and its alias 'sPage' from "NoIndex queries" configuration
* Removed controller action `Shopware_Controllers_Backend_SwagUpdate::saveFtpAction`
* Removed ExtJS model `engine/Shopware/Plugins/Default/Backend/SwagUpdate/Views/backend/swag_update/model/ftp.js`
* Removed ExtJS view `engine/Shopware/Plugins/Default/Backend/SwagUpdate/Views/backend/swag_update/view/ftp.js`
* Removed ExtJS controller method `engine/Shopware/Plugins/Default/Backend/SwagUpdate/Views/backend/swag_update/controller/main.js::onSaveFtp`

### Deprecations

* Deprecated class `\Shopware\Components\OpenSSLEncryption`. It will be removed in 5.7, use own implementation instead
* Deprecated parameter `$orderByShopPositionAsDefault` for methods `getBaseListQuery`, `getBaseListQueryBuilder`and `getMainListQueryBuilder` in `engine/Shopware/Models/Shop/Repository.php`
* Deprecated following methods in class `\Shopware\Models\Partner\Repository`
  * `getCustomerForMappingQuery`
  * `getCustomerForMappingQueryBuilder`

## 5.6.2

[View all changes from v5.6.1...v5.6.2](https://github.com/shopware/shopware/compare/v5.6.1...v5.6.2)

### Additions

* Added a title to the base price declaration shown in the product box (`themes/Frontend/Bare/frontend/listing/product-box/product-price-unit.tpl`), so users may view the full text by hovering, in case the declaration was truncated
* Added a filter event 'Shopware_Plugins_AdvancedMenu_CacheKey' to `\Shopware_Plugins_Frontend_AdvancedMenu_Bootstrap::getAdvancedMenu`
* Added all global defined attachments to the order document mail sending
* Added example HSTS configuration in the htaccess file
* Added the same context variables to `sREGISTERCONFIRMATION` when the mail is sent after a DOI eMail compared to when it is sent directly
* Added some values to the context of eMails `sOPTINREGISTER` and `sOPTINREGISTERACCOUNTLESS` to include the same values as eMail `sREGISTERCONFIRMATION`, these are:
    * `customer_type` and `additional.customer_type`
    * `accountmode`
    * `email` and `sMAIL`
    * `street`
    * `zipcode`
    * `city`
    * `country`
    * `state`
* Added `db.timezone` section to `config.php` to configure a custom timezone for the database connection

### Changes

* Changed the base price declaration shown in the product box (`themes/Frontend/Bare/frontend/listing/product-box/product-price-unit.tpl`), so that an ellipsis is shown when the text is truncated
* Changed `Shopware_Controllers_Widgets_Listing::convertProductsResult` to consider use short description configuration
* Changed `Shopware\Models\Analytics\Repository::createAmountBuilder` to join with `left join` instead `inner join`
* Changed the block of the registration verification alert
* Changed the `x-robots` header to `x-robots-tag`
* Changed HTML minification to exclude `checkout` controller due to performance issues with many items in the cart
* Changed `Shopware\Models\Analytics\Repository::createAmountBuilder` to join with `left join` instead `inner join`
* Changed default value of `Shopware\Models\Article\Image::$main` to 2
* Changed the label of the config form `Service` to `Maintenance`
* Changed `s_mail_log` foreign keys to set null on delete
* Changed `Item by sales` to consider only products
* Changed systeminfo to consider mariadb installations
* Changed `Zend_Cache_Backend_Redis` to make it compatible with PhpRedis 5.0.0
* Changed `Listing` controller to prevent it from accessing categories of subshops
* Changed jquery plugins `ajax-product-navigation`, `infinite-scrolling` and `listing-actions` to work with invalid query strings
* Changed the context variables of eMail `sREGISTERCONFIRMATION` to contain the same variables when the mail is sent after a DOI eMail compared to when it is sent directly
* Changes how the IP of the client get's determined when an order is being stored
* Changed the context variables of eMails `sOPTINREGISTER` and `sOPTINREGISTERACCOUNTLESS` to include the same values as eMail `sREGISTERCONFIRMATION`, these are:
    * The already existing variables:
        * `sConfirmLink`
        * `firstname`
        * `lastname`
        * `salutation`
    * New are the variables:
        * `customer_type` and `additional.customer_type`
        * `accountmode`
        * `email` and `sMAIL`
        * `street`
        * `zipcode`
        * `city`
        * `country`
        * `state`
* Changed PHPStan to 0.11.16
* Changed a call to the `flatpickr`'s `formatDate()` method, so that it now reflects the current parameter order

### Removals

* Removed the `UNIQUE`-Constraint from `\Shopware\Models\Mail\Contact::$mailAddress`

## 5.6.1

[View all changes from v5.6.0...v5.6.1](https://github.com/shopware/shopware/compare/v5.6.0...v5.6.1)

### Additions

* Added new config checkbox for applying stock on chosen variants while applying standard data
* Added new smarty block `frontend_global_messages_icon_remove`
* Added new div class `is--content-type` to all content type pages
* Added fallback for missing widget label translation
* Added path `/tracking` to `robots.txt`

### Changes

* Changed the meta property `og:type` in the listing from `product` to `product.group`
* Changed password recovery form to also work with invalid customer objects
* Changed `Shopware\Bundle\StoreFrontBundle\Gateway\BlogGateway` to consider blog translations
* Changed the SEO meta tags in the blog listing
* Changed `Shopware_Components_Translation` to work with missing payment or dispatch entries
* Changed `sAdmin` to save cart after logout
* Changed `Shopware\Components\DependencyInjection\Compiler\LegacyApiResourcesPass` to work correctly
* Changed the calculation of the date in the affiliate marketing statistics
* Changed `Enlight_Controller_Response_ResponseHttp::isRedirect` to not consider http response code 201 
* Changed content type pages to display the off canvas menu in mobile view
* Changed `sAdmin::sGetCountryList` to use CountryService
* Changed the pagesize for the snippets module
* Changed the handling of browser notifications to support browsers without notifications
* Changed `themes/Frontend/Bare/frontend/_includes/emotion.tpl` to also work with emotion preview
* Changed dependency `beberlei/assert` to version 2.9.9 to fix issues with PHP 7.3

## 5.6.0

[View all changes from v5.5.10...v5.6.0](https://github.com/shopware/shopware/compare/v5.5.10...v5.6.0)

### Additions

* Added support for Shopping Worlds without AJAX requests, configurable in theme settings
* Added configuration to define the format of a valid order number. See [Custom validation of order numbers (SKU)](###Custom validation of order numbers (SKU)) for more details
* Added controller registration by DI-tag. See Controller See [Controller Registration using DI-Tag](###Controller Registration using DI-Tag) for more details
* Added autowiring of controller action parameters. See [Autowire of controller actions parameters](###Autowire of controller actions parameters) for more details
* Added better ExtJS file auto-loading. See [Improved ExtJS auto-loading](###Improved ExtJS auto-loading) for more details
* Added new `Shopware\Components\Cart\PaymentTokenService` to improve handling of payment callbacks. See [Payment Token](###Payment Token) for more details
* Added specific logger service for each plugin. See [Plugin specific logger](###Plugin specific logger) for more details
* Added support for HTTP2 server push. See [HTTP2 Server Push Support](###HTTP2 Server Push Support) for more details
* Added Symfony `RequestStack` to Enlight's request cycle
* Added new config option to allow restoring of old cart items
* Added new config option to enable sharing of session between language shops
* Added support for SVG files in the frontend
* Added means to translate document types, dispatch and payment methods
* Added definition signature `getAttributeRawField` to `Shopware\Bundle\ESIndexingBundle\TextMappingInterface` to reduce info request of
    Elasticsearch and added method implementation to TextMappings
    `Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES2::getAttributeRawField`
    `Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES5::getAttributeRawField`
    `Shopware\Bundle\ESIndexingBundle\TextMapping\TextMappingES6::getAttributeRawField`
* Added new privilege for Plugin Manager and Updater notifications
* Added product review widget
* Added plugin migrations
    * Migrations are loaded from folder `SwagTestPlugin/Resources/migrations`
    * The migration file can be generated using `./bin/console sw:generate:migration added-something-new -p SwagTestPlugin`
* Added new theme configuration to disable ajax loading for emotions
* Added signature `supports` to `Shopware\Bundle\ESIndexingBundle\SynchronizerInterface` to reduce wrong typed backlog sync request
    * Added method implementation to 
    `Shopware\Bundle\ESIndexingBundle\Property\PropertySynchronizer::supports`
    `Shopware\Bundle\ESIndexingBundle\Product\ProductSynchronizer::supports`
* Added service `\Doctrine\Common\Annotations\Reader` as `models.annotations_reader`
* Added `shopware.controller.blacklisted_controllers` parameter to the DI container to blacklist controllers for dispatching
* Added Doctrine's default table options to config, can now be modified
* Added configuration to show the voucher field on checkout confirm page
* Added information text to detail page of category filter 
* Added `string` type cast in return statement of method `sOrder::sGetOrdernumber`
* Added configuration to decide whether user basket should be cleared after logout or not
* Added the following new models and repositories to enable e-mail logging
    * `\Shopware\Models\Mail\Log`
    * `\Shopware\Models\Mail\Contact`
    * `\Shopware\Models\Mail\LogRepository`
* Added the following new services to enable e-mail logging
    * `Shopware\Bundle\MailBundle\Service\LogEntryBuilder`
    * `Shopware\Bundle\MailBundle\Service\LogEntryMailBuilder`
    * `Shopware\Bundle\MailBundle\Service\LogService`
    * `Shopware\Bundle\MailBundle\Service\Filter\AdministrativeMailFilter`
    * `Shopware\Bundle\MailBundle\Service\Filter\NewsletterMailFilter`
* Added the `MailLogCleanup` cron job which clears old entries from the e-mail log
* Added new basic settings in the mailer section
    * `mailLogActive`
    * `mailLogCleanupMaximumAgeInDays`
* Added the `associations` property to `Enlight_Components_Mail`
* Added option symbols for `{include file="frontend/_includes/rating.tpl"}` to hide rating symbols
* Added new controller `Shopware\Controllers\Backend\Logger`
* Added [Ace](https://ace.c9.io/) editor in the backend where `Codemirror` was used before: in email and shopping world templates, shipping cost calculation and product exports.
* Added server response tab to extjs error reporter to show errors in javascript code
* Added new backend module `mail_log`
* Added new backend controllers:
  * `MailLog`
  * `MailLogContact`
* Added function to rename or overwrite if esd file already exists
* Added `beberlei/DoctrineExtensions` as requirement.
* Added ExtJs developer mode, to provide better warnings and errors to developers
* Added `Enlight_Hook_Exception`. It will be thrown when the HookManger gets a class name which not implements `Enlight_Hook` in 5.8
* Added new events in `sBasket::getPricesForItemUpdates()`
* Added an option to use a datepicker for birthday instead of the single select-fields
* Added additional information to the address verification. You can now give more feedback during the form validation.
* Added `--index` option to `sw:es:index:populate`. It can be used to reindex single or multiple index. If it is not defined, every index will be reindexed.
    `bin/console sw:es:index:populate --index property`
    `bin/console sw:es:index:populate --index property --index product`
* Added the Product Number as a new method to sort your results
* Added support for Elasticsearch 7
* Added a last password change date to customers
* Added new foreign key to `s_order_details`, `orderID` now references `s_order`.`id`
* Added new config option to set batch size of backlog indexing for elasticsearch backend implementation
* Added listener for new orders, to add them in the elasticsearch backend backlog
* Added logging for exceptions that may occur during migrations
* Added command `sw:es:backend:backlog:clear` to clear the backlog
* Added alias command `sw:es:backend:backlog:sync` to `sw:es:backend:sync`
* Added paging to more lists in the backend
* Added a new configuration to a blog for limiting the blog to shops
* Added browser notifications in the backend, which are shown when a growl message is created and the current tab is inactive
* Added new block `frontend_listing_box_article_badges_inner` to `themes/Frontend/Bare/frontend/listing/product-box/product-badges.tpl`
* Added event `Shopware_Models_Order_Document_Filter_Config` to modify config settings for document creation
* Added `_config` property to class `Shopware_Models_Document_Order` to make it usable while model creation
* Added new parameter to prevent certain exceptions from cluttering your logs. See [Disable logging of specific exceptions](###Disable logging of specific exceptions) for more details
* Added a `Symfony\Component\HttpKernel\Bundle\Bundle` class to all Shopware bundles and moved all service.xml files to a corresponding `DependencyInjection` directory
* Added `es.index_configuration` and `es.backend.index_configuration` section to `config.php` to allow custom elasticsearch settings
* Added getter and setter to Config/Form model
* Added new event `Shopware_Controllers_Order_SendMail_Prepare` to `Shopware_Controllers_Backend_Order::sendMailAction`
* Added `Shopware\Components\CacheSubscriber` to clear config cache, when plugin config changes

### Changes

* Changed minimum required PHP version to 7.2.0
* Changed minimum required MySQL version to 5.7.0
* Changed minimum Elasticsearch version to 6.6.0
* Changed Symfony library to version 3.4.30
* Changed doctrine/orm to 2.6.3
* Changed mpdf/mpdf to 7.1.9
* Changed elasticsearch/elasticsearch to 6.7.1
* Changed ongr/elasticsearch-dsl to 6.0.3
* Changed jQuery to 3.4.1
* Changed PHPStan to 0.11.12
* Changed id of login password form in `frontend/account/login.tpl` from `passwort` to `password`
* Changed plugin initialization to alphabetical order by default
* Changed all DIC service ids to lowercase as a preparation for a switch to Symfony 4 in the future
* Changed the generation of the Robots.txt. See [Improved Robots.txt](###Improved Robots.txt) for more details
* Changed cookie `x-ua-device` to be `secure` if the shop runs on SSL
* Changed the following cart actions to redirect the request to allow customers to press reload:
    `Shopware_Controllers_Frontend_Checkout::addArticleAction`
    `Shopware_Controllers_Frontend_Checkout::addAccessoriesAction`
    `Shopware_Controllers_Frontend_Checkout::deleteArticleAction`
* Changed browser cache handling in backend to cache javascript `index` and `load` actions. Caching will be disabled when...
    * the template cache is disabled
    * `$this->Response()->setHeader('Cache-Control', 'private', true);` is used in the controller
* Changed mapping of Elasticsearch fields:
    * Fields previously handled as `notAnalyzedFields` are now treated as `keyword` fields, meaning they are only searchable completely, not in parts instead of not searchable. Examples of these fields are:
        * EAN
        * SKU
        * ZIP codes
        * transaction Ids
        * Phone numbers
        * .raw fields
        * and some more
* Changed the manufacturer image to appropriate thumbnails
* Changed `Shopware\Components\Plugin\CachedConfigReader` to cache into `Zend_Cache_Core`
* Changed `plugin.xsd` to make pluginName in `requiredPlugins` required
* Changed `Shopware\Components\DependencyInjection\Container` to trigger InitResource and AfterInitResource events for alias services, introduced by decorations
* Changed the `Regex`-Constraint on `\Shopware\Models\Article\Detail::$number` to a new `OrderNumber`-Constraint to be more configurable
* Changed interface `Shopware\Bundle\SearchBundleDBAL\VariantHelperInterface` to contain new method `joinVariants(QueryBuilder $query)` which was already a necessary part of the default implementation
* Changed `type` of `logMailAddress` config in `s_core_config_elements` to `textarea`
* Changed mail error handler to consider multiple recipient addresses
* Changed `Shopware_Controllers_Frontend_Note` forwards to redirects
* Changed display mode of voucher field on the shopping cart page into a configurable display mode
* Changed symfony form request handler to `Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationRequestHandler`
* Changed `.htaccess`-file to no longer contain references to PHP5
* Changed following tables to varchar limit of 15 for customer group key
    * `s_core_customergroups`
    * `s_article_configurator_template_prices`
    * `s_articles_prices`
    * `s_campaigns_mailings`
* Changed backend customer login to start with a fresh session
* Changed `Shopware_Controllers_Backend_Application` to be an abstract class
* Changed `sExport::sGetArticleCategoryPath` to allow various attributes in category path
* Changed `Shopware_Controllers_Backend_Application` to be abstract
* Changed `Ext.ClassManager` to show better error messages on missing alias or class
* Changed `Shopware_Controllers_Backend_Application` to abstract
* Changed `Shopware_Controllers_Backend_ExtJs` to abstract
* Changed internal validation of `Shopware\Bundle\StoreFrontBundle\Struct\Attribute`
* Changed the blog seo meta details to be saveable after being over the max length of the global max length
* Changed shipping calculation in off canvas to work correctly with country states
* Changed the input of filters in the backend to prevent grammar error 
* Changed `Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface::index`. Added optional `$indexNames` argument
* Changed Bare Template to improve accessibility by adding aria labels
* Changed product module split view mode to work correctly with properties
* Changed product search to work correctly on MySQL 8
* Changed last seen products productLimit to work correctly
* Changed product-feed export to refresh context while exporting multiple feeds
* Changed `Shopware\Models\Shop\Shop::registerResources` to extract the code in a new service `shopware.components.shop_registration_service`
* Changed the offcanvas basket to now display shipping costs also when the user is logged in
* Changed `\Shopware\Components\Privacy\PrivacyService` to run more efficiently
* Changed the logging to not have critical or error logging for CSRF-Tokens and 404-pages
* Changed the error message if a customer enters an invalid birthday in the registration
* Changed product list in Elasticsearch to consider show variants option
* Changed Hook generation to work correctly with `void` return type
* Changed column `s_order_documents.ID` to `s_order_documents.id` (if it wasn't already changed due to MySQL 8 being used)
* Changed padding on hidden cookie-permission banner
* Changed partner cookie durations slightly to make them more accurate. Added more available intervals
* Changed grunt configuration to also take the plugin directory `custom/project` into account
* Changed advanced menu javascript to throw less events
* Changed the ShopContext to be elegantly decoratable
* Changed method name `Shopware\Components\Plugin::getNamespace` to `Shopware\Components\Plugin::getPluginNamespace` to support `getNamespace` method from Symfony bundles.
* Changed `Shopware\Components\Plugin` class to extend from `Symfony\Component\HttpKernel\Bundle\Bundle`
* Changed `Shopware\Components\Plugin::registerCommands` parameter typehint from `Shopware\Components\Console\Application` to `Symfony\Component\Console\Application`
* Changed Elasticsearch backend implementation to use an index prefix
* Changed `sBasket::getAdditionalInfoForUpdateProduct` to add additional text fields for variants as well
* Changed the emotion detail window to be resizable
* Changed the generation of `.tmp` files for compiled theme cache
* Changed the `Shopware_Components_Config` to use Doctrine DBAL instead of Zend_Db
* Changed ajax-search to cancel ajax request, when submit button is pressed
* Changed elasticsearch backend backlog to write variant backlogs correctly
* Changed the attribute entity selection for `\Shopware\Models\Order\Detail`
* Changed the first parameter in `Shopware\Components\Model\ModelEntity:setOneToMany` to allow iterables

### Removals

* Removed the following classes without replacement
    * `Shopware\Bundle\FormBundle\Extension\EnlightRequestExtension`
    * `Shopware\Bundle\FormBundle\EnlightRequestHandler`
    * `Shopware\Components\Compatibility\LegacyDocumentIdConverter`
    * `Shopware\Components\Compatibility\MigrateMysql8Command`
    * `Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\CompositeSynchronizerFactory`
    * `Shopware\Bundle\ESIndexingBundle\CompositeSynchronizer`
* Removed the following deprecated classes
    * `Shopware_Controllers_Frontend_SitemapMobileXml`
    * `Shopware\Components\SitemapXMLRepository`
    * `Shopware_Components_Benchmark_Point`
    * `Shopware_Components_Benchmark_Container`
    * `Shopware_Controllers_Backend_Deprecated`
* Removed `s_articles_attributes`.`articleID` which was not set for new article variants anymore since Shopware 5.2.0
* Removed global `$Shopware` template variable
* Removed following classes, use `Shopware\Components\Plugin\XmlReader\*` instead
    * `Shopware\Components\Plugin\XmlPluginInfoReader`
    * `Shopware\Components\Plugin\XmlConfigDefinitionReader`
    * `Shopware\Components\Plugin\XmlCronjobReader`
    * `Shopware\Components\Plugin\XmlMenuReader`
* Removed `storeType php` from Plugin config.xml
* Removed the unspecific request params assignment to view in `Shopware_Controllers_Widgets_Listing::productsAction` and `Shopware_Controllers_Widgets_Listing::streamAction`. Use a *PostDispatchEvent to assign necessary variables in a plugin
* Removed voucher field from additional feature
* Removed checkbox show in all categories on the category filter detail page
* Removed category filter facet from filter listing in category settings 
* Removed category filter from category page in frontend
* Removed deprecated `Shopware` constants
    * Removed `Shopware::VERSION` use the DIC-Parameter `shopware.release.version` instead
    * Removed `Shopware::VERSION_TEXT` use the DIC-Parameter `shopware.release.version_text` instead
    * Removed `Shopware::REVISION` use the DIC-Parameter `shopwqare.release.REVISION` instead
* Removed deprecated `Kernel` constants
    * Removed `Kernel::VERSION` use the DIC-Parameter `shopware.release.version` instead
    * Removed `Kernel::VERSION_TEXT` use the DIC-Parameter `shopware.release.version_text` instead
    * Removed `Kernel::REVISION` use the DIC-Parameter `shopware.release.revision` instead
* Removed deprecated `$legacyGroups` in `Shopware\Components\SitemapXMLRepository`
* Removed deprecated older `Shopware\Models\Order\Document\Document`
* Removed deprecations of `Shopware\Components\Api\Resource\Article`
* Removed deprecations of `Shopware\Components\Api\Resource\Variant`
* Removed unused `Shopware\Bundle\SearchBundleES\DependencyInjection\CompilerPassSearchHandlerCompilerPass` which was not used at all
* Removed method `Enlight_Controller_Response_ResponseHttp::insert` 
* Removed method `Shopware\Kernel::transformEnlightResponseToSymfonyResponse` 
* Removed following methods from class `Enlight_Controller_Dispatcher_Default`
    * `addControllerDirectory`, use setModules instead
    * `addModuleDirectory`, use addModule instead
    * `setControllerDirectory`, use setModules instead
    * `getControllerDirectory`, use getModules instead
    * `removeControllerDirectory`, use setModules instead
* Removed the `CodeMirror` JavaScript editor, use `Ace` instead
* Removed `ext-all-debug.js`

### Deprecations

* Deprecated the implicit conversion of strings to `DateTime` objects in Doctrine entities, it will be removed in Shopware 5.7. Relying on this conversion will throw a deprecation warning till then, please only insert `DateTime` objects
* Deprecated `Shopware\Bundle\ESIndexingBundle\TextMappingInterface::getNotAnalyzedField`. It will be removed in 5.7, use the getKeywordField instead
* Deprecated `Shopware\Bundle\ESIndexingBundle\TextMappingInterface::getAttributeRawField`. It will be removed in 5.7, use the getKeywordField instead
* Deprecated `Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface`. It will be removed in 5.7, use the `Shopware\Bundle\ESIndexingBundle\ProviderInterface` instead
* Deprecated `Shopware\Bundle\ESIndexingBundle\Property\PropertyProviderInterface`. It will be removed in 5.7, use the `Shopware\Bundle\ESIndexingBundle\ProviderInterface` instead
* Deprecated `Shopware\Components\Model\ModelRepository::queryAll`. It will be removed in 5.7, use findBy([], null, $limit, $offset) instead
* Deprecated `Shopware\Components\Model\ModelRepository::queryBy`. It will be removed in 5.7, use findBy instead
* Deprecated `Shopware\Bundle\ESIndexingBundle\EsClientLogger`. Use `Shopware\Bundle\ESIndexingBundle\EsClient` instead.
* Deprecated `shopware_elastic_search.client.logger`. Use `shopware_elastic_search.client` instead.
* Deprecated `Shopware\Models\Article\Article::getAttributeRawField`. It will be removed in 5.7, , use `Shopware\Models\Article\Detail::getAttributeRawField `
* Deprecated `Shopware\Models\Article\Article::setLastStock`. It will be removed in 5.7, , use `Shopware\Models\Article\Detail::setLastStock`
* Deprecated `Shopware\Models\Article\Article::lastStock`. It will be removed in 5.8, use `Shopware\Models\Article\Detail::lastStock`
* Deprecated `EsSearch::addFilter`. Use `EsSearch::addQuery(BuilderInterface, BoolQuery::FILTER)` instead
* Deprecated `EsSearch::getFilters`. Use `EsSearch::getQueries(BuilderInterface, BoolQuery::FILTER)` instead
* Deprecated `Enlight_Controller_Response_ResponseHttp::setRawHeader`
* Deprecated `Enlight_Controller_Response_ResponseHttp::clearRawHeader`
* Deprecated `Enlight_Controller_Response_ResponseHttp::clearRawHeaders`
* Deprecated `Enlight_Controller_Response_ResponseHttp::outputBody`
* Deprecated `Shopware_Controllers_Backend_Log::createLogAction`. It will be removed in 5.7, use `\Shopware\Controllers\Backend\Logger::createLogAction` instead
* Deprecated `Enlight_Event_EventHandler`. It will be removed in 5.8, use `Enlight_Event_Handler_Default` or `SubscriberInterface::getSubscribedEvents` instead
* Deprecated `Shopware\Bundle\SearchBundle\ConditionInterface\RegisteredInShopCondition`. It will be removed in 5.7 without replacement
* Deprecated `Shopware\Bundle\BenchmarkBundle\Commands\ReceiveStatisticsCommand::MAX_BATCH_SIZE`. It will be removed in 5.7 without replacement
* Deprecated `Shopware\Bundle\SearchBundle\ConditionInterface\RegisteredInShopCondition`. It will be removed in 5.7 without replacement
* Deprecated `Shopware\Bundle\CustomerSearchBundleDBAL\ConditionHandler\RegisteredInShopConditionHandler`. It will be removed in 5.7 without replacement
* Deprecated `Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\CronJobSubscriber\CronJobProgressHelper`. It will be removed in 5.7. Use the `CronJobProgressHelper` instead.
* Deprecated `Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexerInterface::clearIndex`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\CustomerSearchBundleDBAL\Indexing\SearchIndexerInterface::cleanupIndex`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\EmotionBundle\Exception\ComponentHandlerNotFoundException`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\EsBackendBundle\EsAwareSearcher`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\ESIndexingBundle\Product\ProductQueryFactoryInterface::createPriceIdQuery`, `::createVoteIdQuery`, `::createVariantIdQuery`, `::createProductCategoryQuery`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\ESIndexingBundle\Product\ProductQueryFactory::createPriceIdQuery`, `::createVoteIdQuery`, `::createVariantIdQuery`, `::createProductCategoryQuery`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\ESIndexingBundle\Product\ProductQueryFactoryInterface::createPriceIdQuery`, `::createVoteIdQuery`, `::createVariantIdQuery`, `::createProductCategoryQuery`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\ESIndexingBundle\LastIdQuery::getQuery`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\PluginInstallerBundle\Context\PluginLicenceRequest`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\PluginInstallerBundle\Service\PluginStoreService::getPluginLicence`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\PluginInstallerBundle\Struct\SubscriptionStateStruct`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\SearchBundle\Condition\SimpleCondition`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\SearchBundle\Facet\SimpleFacet`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\SearchBundle\Sorting\SimpleSorting`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\StoreFrontBundle\Common\StructHelper`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Bundle\StoreFrontBundle\Struct\LocationContext`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Components\Auth\Adapter\Default::rehash`. It will be private in 5.7 without replacement.
* Deprecated `Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait`. It will be removed in 5.8 without replacement.
* Deprecated `Shopware\Components\DependencyInjection\ProxyFactory`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Components\Emotion\Preset\PresetInstallerInterface`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Components\Log\Handler\DoctrineDBALHandler`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware\Components\CsvIterator::setFieldmark`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware_Controllers_Backend_Article` `::getArticleCategories`, `::getArticleSimilars`, `::getArticleRelatedProductStreams`, `::getArticleRelated`, `::getArticleImages`, `::getArticleLinks`, `::getArticleDownloads`, `::getArticleCustomerGroups`, `::getArticleConfiguratorSet`, `::getArticleDependencies`, `::getFreeSerialCount`, `:getChartData:`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Blog` `::getCategoryRepository`, `::getArticleRepository`, `::getRepository`, `::getBlogCommentRepository`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Category` `::getRepository`, `::getCategoryComponent`, `::getPathByQuery`, `::saveDetail`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Login` `::getPlugin`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Newsletter` `::initMailing`, `::initTemplate`, `::getMailing`, `::getMailingDetails`, `::getMailingVoucher`, `::getMailingEmails`, `::getVoucherCode`, `::getMailingUserByEmail`, `::preFilter`, `::outputFilter`, `::altFilter`, `::trackFilter`, `::createHash`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_NewsletterManager`, `::getCampaignsRepository`, `::getPreviewNewslettersQuery`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_NewsletterManager`, `::getCampaignsRepository`, `::getPreviewNewslettersQuery`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Performance`, `::saveConfigData`, `::prepareDataForSaving`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Shipping`, `::deleteCostMatrix`, `::saveCostMatrix`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Supplier::getAllSupplier`. It will removed in 5.7, without any replacement.
* Deprecated `Shopware_Controllers_Frontend_Blog`, `::getDateFilterData`, `::getAuthorFilterData`, `::getTagsFilterData`, `::getCategoryBreadcrumb`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Frontend_Blog::acceptBlogCommentAction`.  It will be removed in 5.7 without replacement.
* Deprecated `Shopware_Controllers_Frontend_Checkout`, `::flagPaymentBlocked`, `::getUserData`, `::saveTemporaryOrder`, `::saveOrder`, `::getInstockInfo`, `::getAvailableStock`, `::getShippingCosts`, `::getBasket`, `::getTaxRates`, `::getSimilarShown`, `::getBoughtToo`, `::getMinimumCharge`, `::getDispatchNoOrder`, `::getPremiums`, `::getEsdNote`, `::getInquiry`, `::getInquiryLink`, `::getCountryList`, `::getDispatches`, `::getPayments`, `::getSelectedCountry`, `getSelectedState`, `::getSelectedPayment`, `::getSelectedDispatch` . It will protected in 5.8.
* Deprecated `Shopware_Controllers_Frontend_Forms::commitForm`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Frontend_Listing::getBreadcrumb`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Frontend_Tracking::$testRepository`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware_Controllers_Widgets_Campaign::getEmotions`. It will be removed in 5.7 without replacement.
* Deprecated `sArticles` `::sGetArticleTaxById`, `::sGetCheapestPrice`, `::sGetSupplierById`, `::sGetArticlesBySupplier`, `::sFillUpComparisonArticles`, `::sGetComparisonProperties`. It will be removed in 5.7 without replacement.
* Deprecated `sArticles::sFillUpComparisonArticles`. It will be removed in 5.7. Use the `sArticlesComparisons::sFillUpComparisonArticles` instead.
* Deprecated `sArticles::sGetComparisonProperties`. It will be removed in 5.7. Use the `sArticlesComparisons::sGetComparisonProperties` instead.
* Deprecated `sArticles::getArticleListingCover`. It will be removed in 5.7. Use the `sArticles::sGetArticlePictures` instead.
* Deprecated `sArticles::sGetTranslations`. It will be removed in 5.7. Use `sArticle::sGetTranslation` instead.
* Deprecated `sCategories::$sSYSTEM` It will be removed in 5.7 without replacement.
* Deprecated `sExport` `::$sDB`, `::$sApi`, `::$sPath`, `::$sTemplates`, `::sGetDispatch` It will be removed in 5.7 without replacement.
* Deprecated `sOrder::$paymentObject`. It will be removed in 5.7 without replacement.
* Deprecated `sRewriteTable::getData`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware_Components_AlsoBought::getOrderTime`. It will be removed in 5.7 without replacement.
* Deprecated `Shopware_Controllers_Backend_SimilarShown::SimilarShown`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_TopSeller::TopSeller`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Seo` `::SeoIndex`, `::RewriteTable`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Analytics::getShopRepository`. It will be private in 5.8.
* Deprecated `Shopware_Controllers_Backend_Snippet::prefixProperties`. It will be removed in 5.8 without replacement.
* Deprecated `Shopware_Controllers_Backend_Order::getDocumentRepository`. It will be removed in 5.8 without replacement.
* Deprecated `Shopware_Controllers_Backend_Article` `::$configuratorPriceVariationRepository`, `::$configuratorGroupRepository`, `::getConfiguratorGroupRepository`, `::getDependencyByOptionId`, `::getViolationFields`. It will be removed in 5.8 without an replacement
* Deprecated `Shopware_Components_Translation::filterText`. It will be removed in 5.7 without an replacement
* Deprecated `Shopware\Components\Api\Resource\ApiProgressHelper`. It will be removed in 5.7 without an replacement.
* Deprecated `Shopware\Components\Api\Resource\Cache::getRequest`. It will be removed in 5.7 without an replacement.
* Deprecated `Shopware\Components\Api\Resource\Article::isVariantImageExist`. It will be removed in 5.7 without an replacement. 
* Deprecated `Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\Hydrator::getFields`. It will be removed in 5.7 without an replacement.
* Deprecated `Shopware\Components\Model\Query\Mysql\IfElse` in 5.6, will be removed with 5.7. Please use `DoctrineExtensions\Query\Mysql\IfElse` instead.
* Deprecated `Shopware\Components\Model\Query\Mysql\DateFormat` in 5.6, will be removed with 5.7. Please use `DoctrineExtensions\Query\Mysql\DateFormat` instead.
* Deprecated `Shopware\Components\Model\Query\Mysql\IfNull` in 5.6, will be removed with 5.7. Please use `DoctrineExtensions\Query\Mysql\IfNull` instead.
* Deprecated `Shopware\Components\Model\Query\Mysql\RegExp` in 5.6, will be removed with 5.7. Please use `DoctrineExtensions\Query\Mysql\RegExp` instead.
* Deprecated `Shopware\Components\Model\Query\Mysql\Replace` in 5.6, will be removed with 5.7. Please use `DoctrineExtensions\Query\Mysql\Replace` instead.
* Deprecated `Shopware\Components\Model\DBAL\Types\DateTimeStringType` in 5.6, will be removed with 5.7. Please use `Doctrine\DBAL\Types\DateTimeType` instead.
* Deprecated `Shopware\Components\Model\DBAL\Types\AllowInvalidArrayType` in 5.6, will be removed with 5.7. Please use `Doctrine\DBAL\Types\ArrayType` instead.
* Deprecated `Shopware\Components\Model\DBAL\Types\DateTimeStringType` in 5.6, will be removed with 5.7. Please use `Doctrine\DBAL\Types\DateTimeType` instead.
* Deprecated `Shopware\Components\Api\Manager::getResource`. It will be removed in 5.8, inject resources instead
* Deprecated usage of `shopware.api` DI prefix without tag `shopware.api_resource`
* Deprecated `Shopware\Models\Shop\Shop::registerResources`. It will be removed in 5.8, use `Shopware\Components\ShopRegistrationService` instead.
* Deprecated `Shopware\Components\DependencyInjection\Compiler\LegacyApiResourcesPass`. It will be removed in 5.8, use the tag `shopware.api_resource` instead.

### Improved ExtJS auto-loading

Previous to Shopware 5.6, only ExtJS files from the `Shopware.apps.Base` were loaded globally, so you can use them globally in your apps. 
Additional to that, when loading up a module like e.g. "ProductStream", all files from `Shopware.apps.ProductStream` were loaded on top.
Using files from others apps, such as `Shopware.apps.Article` inside of the `Shopware.apps.ProductStream` application, required you to implement some special code in order to do so.
Also, when using an ExtJS store in your plugin configuration, you were only able to use stores from `Shopware.apps.Base`, none of the other stores.

We've improved our auto-loading of ExtJS files, so you don't have to worry about this anymore.
Instead of failing, because you used files of an application, that didn't get loaded yet, we're simply loading the missing application now.
This way you don't have to worry about using others applications files anymore, as well as worrying about which stores can be used
in your plugin configuration.

### Controller Registration using DI-Tag

Controllers can be now registered using the DI tag `shopware.controller`. This DI tag needs attributes `module` and `controller`. These controllers are also lazy-loaded.

#### Example:

##### DI:

```xml
<service id="SwagExample\Controller\Frontend\SwagTest">
    <argument type="service" id="dbal_connection"/>
    <tag name="shopware.controller" module="frontend" controller="swagTest"/>
</service>
```

##### Controller:

```php
<?php

namespace SwagExample\Controller\Frontend;

use Doctrine\DBAL\Connection;

class SwagTest extends \Enlight_Controller_Action
{
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        parent::__construct();
    }

    public function indexAction()
    {
        // Do something with $this->connection
    }
    
    public function detailAction(int $productNumber = null, ListProductServiceInterface $listProductService, ContextServiceInterface $contextService)
    {
        if (!$productNumber) {
            throw new \RuntimeException('No product number provided');
        }
        
        $this->View()->assign('product', $listProductService->getList([$productNumber], $contextService->getShopContext()));
    }
}
```

### Autowire of controller actions parameters

The new controllers tagged with `shopware.controller` tag, can now have parameters in action methods. Possible parameters are

* Services (e.g `ListProductService $listProductService`)
* $request (e.g `Request $request`)
* $requestParameters (e.g `int $limit = 0`, `/action?limit=5`)

### The request and response instances in Shopware now extend from Symfony's Request / Response

`Enlight_Controller_Request_RequestHttp` is now extending `Symfony\Component\HttpFoundation\Request` and `Enlight_Controller_Response_ResponseHttp` extends `Symfony\Component\HttpFoundation\Response`. This is a first move to switch to Symfony Controllers and Request/Responses completely in a future release. 

### Custom validation of order numbers (SKU)

Up to now, the validation of order numbers (or SKUs) was done in form of a Regex-Assertion in the Doctrine model at `Shopware\Models\Article\Detail::$number`. That solution was not flexible and didn't allow any modifications of said regex, let alone a complete custom implementation of a validation.
 
Now, a new constraint `Shopware\Components\Model\DBAL\Constraints\OrderNumber` is used instead, which is a wrapper around `\Shopware\Components\OrderNumberValidator\RegexOrderNumberValidator`.

This way you can either change the regex which is being used for validation by defining one yourself in the `config.php`:
```php
<?php
return [
    'product' => [
        'orderNumberRegex' => '/^[a-zA-Z0-9-_.]+$/' // This is the default
    ],
    'db' => [...],
]
``` 
Or you can create your own implementation of the underlying interface `Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface` and use it for the validation by simply decorating the current service with id `shopware.components.ordernumber_validator` and e.g. query some API.

### Definition of MySQL version in config

It is now possible to define the MySQL version being used in the `config.php` as part of the Doctrine default configuration.
The version can be determined by running the SQL query `SELECT version()`, the result needs to be provided in the `db.serverVersion` config:

```php

<?php
return [
     ...
     'db' => [
         ...
         'serverVersion' => '5.7.24',
     ],
];
```
Providing this value via config makes it unnecessary for Doctrine to figure the version out by itself, thus reducing the number of database calls Shopware makes per request by one.

If you are running a MariaDB database, you should suffix the `serverVersion` with `-MariaDB` (e.g.: `10.3.18-MariaDB`).

### Payment Token

Some internet security software packages open a new clean browser without cookies for payments.
After returning from the payment provider, the customer will be redirected to the home page, because the new browser instance does not contain the previous session.
For this reason there is now a service to generate a token, which can be added to the returning url (e.g `/payment_paypal/return?paymentId=test123&swPaymentToken=abc123def`).
This parameter will be resolved in the PreDispatch.
If the user is not logged in, but the URL contains a valid token, he will get back his former session and will be redirected to the original URL, but without the token

Example implementation:

```php
<?php

use \Shopware\Components\Cart\PaymentTokenService;

class MyPaymentController extends Controller {

    public function gatewayAction()
    {
        // Do some payment things
        $token = $this->get(\Shopware\Components\Cart\PaymentTokenService::class)->generate();
        
        $returnParamters = [
            'controller' => 'payment_paypal',
            'action' => 'return',
            PaymentTokenService::TYPE_PAYMENT_TOKEN => $token
        ];
        $returnLink = $this->router->assemble($returnParamters);
        
        $redirectUrl = $this->paymentProviderApi->createPayment($cart, $returnLink);
        
        $this->redirect($redirectUrl);
    }
}
```

### Replaced Codemirror with Ace-Editor

Codemirror has been replaced with Ace-Editor. For compatibility reason, Ace-Editor supports all xtypes / classes from Codemirror.
Following modes are available
    * css
    * html
    * javascript
    * json
    * less
    * mysql
    * php
    * sass
    * scss
    * smarty
    * sql
    * text
    * xml
    * xquery
    
## Improved Robots.txt

robots.txt shows now all links from all language shops.
To remove or add entries overwrite the blocks `frontend_robots_txt_disallows_output`, `frontend_robots_txt_allows_output` and call methods `setAllow`, `setDisallow`, `removeAllow`, `removeDisallow`

Example:

```smarty
{block name="frontend_robots_txt_disallows_output"}
    {$robotsTxt->removeDisallow('/ticket')}
    {$smarty.block.parent}
{/block}
```

### Plugin specific logger

There is a new logger service for each plugin.
The service id is a combination of the plugin's service prefix (lower camel case plugin name) and `.logger`.

For example: when a plugin's name is `SwagPlugin` the specific logger can be accessed via `swag_plugin.logger`.
This logger will now write into the logs directory using a rotating file pattern like the other logger services.

The settings for the logger can be configured using the DI parameters `swag_plugin.logger.level`(defaults to shopware default logging level) and `swag_plugin.logger.max_files` (defaults to 14 like other shopware loggers).
In this case the logger would write into a file like `var/log/swag_plugin_production-2019-03-06.log`.

Support for easier log message writing is enabled:

```php
<?php

$logger->fatal("An error is occured while requesting {module}/{controller}/{action}", $controller->Request()->getParams());
```

### Manual Sorting of products in categories

Products in a category can be now sorted "by hand". This specific sorting can also be created using the categories API resource.

They will be applied when the associated sorting has been selected in the storefront. Not manually sorted products will use the configured normal fallback sorting.

### HTTP2 Server Push Support

HTTP2 Server Push allows Shopware to push certain resources to the browser without it even requesting them. To do so, Shopware creates `Link`-headers for specified resources, informing Apache or Nginx to push these files to the browser. Server Push is supported since [Apache 2.4.18](https://httpd.apache.org/docs/2.4/mod/mod_http2.html#h2push) and [nginx 1.13.9](https://www.nginx.com/blog/nginx-1-13-9-http2-server-push/#http2_push).

These resources are only pushed on the very first request of a client. After that, the files should be cached in the browser and don't need to be transmitted anymore. The presence of a `session`-cookie is used to determine if a push is necessary.

The Smarty function `{preload}` is used to define in the template which resource are to be pushed and as what.

Example for CSS:
```html
<link href="{preload file={$stylesheetPath} as="style"}" media="all" rel="stylesheet" type="text/css" />
```

Example for Javascript:
```html
<script src="{preload file={link file='somefile.js'} as="script"}"></script>
```

Server Push can be enabled in the `Various` section of the `Cache/Performance` settings. Please do not enable Server Push support if you are using Google's Pagespeed module: It creates custom CSS and Javascript files for the browser, replacing the ones Shopware contains in the HTML. So pushing the original files to the browser leads to an unnecessary overhead.

### Improved ExtJs Error Reporter

Extjs error reporter shows now also the server response and lints the code to show errors.

### ExtJs Developer-Mode

ExtJs developer mode loads a developer-version file of ExtJs to provide code documentation, warnings and better error messages. This mode can be enabled using this snippet in the `config.php`

```php
'extjs' => [
    'developer_mode' => true
]
```

### Content Types

Content Types are something similar to attributes, but you can create your own simple entity with defined fields using using xml or the backend.

Example XML:

```xml
<?xml version="1.0" encoding="utf-8"?>
<contentTypes xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
              xsi:noNamespaceSchemaLocation="../../../engine/Shopware/Bundle/ContentTypeBundle/Resources/contenttypes.xsd">
    <types>
        <type>
            <typeName>store</typeName>
            <name>Stores</name>
            <fieldSets>
                <fieldSet>
                    <field name="name" type="text">
                        <label>Name</label>
                        <showListing>true</showListing>
                    </field>
                    <field name="address" type="text">
                        <label>Address</label>
                        <showListing>false</showListing>
                    </field>
                    <field name="country" type="text">
                        <label>Country</label>
                        <showListing>false</showListing>
                    </field>
                </fieldSet>
            </fieldSets>
        </type>
    </types>
</contentTypes>
```

Each type gets its own

* backend controller
* frontend controller, if enabled
* API controller for all CRUD operations (Custom**type_name** e.g. CustomStore)
* backend menu icon
* table with `s_custom_` prefix
* repository service with shopware.bundle.content_type.**type_name**

They are also accessible in template using new smarty function `fetchContent`

Example
```html
{fetchContent type=store assign=stores filter=[['property' => 'country', 'value' => 'Germany']]}

{foreach $stores as $store}
    {$store.name}
{/foreach}
```

The backend fields and titles can be translated using snippet namespace ``backend/customYOURTYPE/main``.

You can find more information and details (e.g. regarding available field types) in the [Developer Docs](https://developers.shopware.com/developers-guide/content-types/).

### FQCN as DIC ids

Starting with Shopware 5.6, new services will have their Fully Qualified Class Names (FQCN) as their service id in the DIC.

Classes that implement an interface will normally use that interface as their FQCN, though exemptions may occur if an
interface is implemented multiple times (e.g. the `SubscriberInterface`).

For the moment, all existing legacy service ids will be converted to lowercase and should be used in lowercase in plugins
as well. Since the Shopware DIC is case-insensitive, this is no problem in existing plugins or older Shopware installations.

This change is being made as a first arrangement for a future switch to Symfony 4, where the DIC is case sensitive and
FQCNs are the default.

### Disable logging of specific exceptions

Sometimes you are aware that exceptions occur, but you're not interested in them being logged since there is nothing you
can do about them and their occurring isn't harmful to the system or your business. One example might be a
`CSRFTokenValidationException` being thrown by visiting bots.

To prevent those exceptions from cluttering up your log, you are now able to disable logging of those exceptions. To do so,
add the class name of the exceptions your want to exempt from being logged to your `config.php`:

```php
'errorHandler' => [
    'ignoredExceptionClasses' => [
        \Shopware\Components\CSRFTokenValidationException::class,
        \Shopware\Components\Api\Exception\CustomValidationException::class
    ]
]
```
By default, these exceptions are now longer being logged:
```php
\Shopware\Components\Api\Exception\BatchInterfaceNotImplementedException::class,
\Shopware\Components\Api\Exception\CustomValidationException::class,
\Shopware\Components\Api\Exception\NotFoundException::class,
\Shopware\Components\Api\Exception\OrmException::class,
\Shopware\Components\Api\Exception\ParameterMissingException::class,
\Shopware\Components\Api\Exception\PrivilegeException::class,
\Shopware\Components\Api\Exception\ValidationException::class,
```
