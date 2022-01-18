# CHANGELOG for Shopware 5.7.x

This changelog references changes done in Shopware 5.7 patch versions.

## 5.7.8

[View all changes from v5.7.7...v5.7.8](https://github.com/shopware/shopware/compare/v5.7.7...v5.7.8)

### Deprecations

### Additions

### Changes

* Update `doctrine/common` to version 3.2.1
* Update `doctrine/orm` to version 2.11.0

### Removals

## 5.7.7

[View all changes from v5.7.6...v5.7.7](https://github.com/shopware/shopware/compare/v5.7.6...v5.7.7)

### Deprecations

* Deprecated `\Shopware_Controllers_Frontend_Checkout::getTaxRates`, it will be removed in the next minor version v5.8.
Use `TaxAggregator::taxSum` instead.

### Additions

* Added `\Shopware\Components\Cart\TaxAggregatorInterface`
* Added `\Shopware\Components\Cart\TaxAggregator` as a default implementation, extracting the tax aggregation logic from the checkout controller
* Added a new component to the update process. The `.htaccess`-file now contains a section dedicated to the Shopware core.
* Added new polyfill dependencies which were indirect dependencies before
  * `symfony/polyfill-php80` version 1.23.1
  * `symfony/polyfill-php81` version 1.23.0

### Changes

* Changed `\Shopware_Controllers_Frontend_Checkout::getTaxRates`, this method uses the `TaxAggregator::taxSum` now
* Changed `\Shopware_Models_Document_Order::processOrder`, this method uses the `TaxAggregator::shippingCostsTaxSum` method now
* Changed `\Shopware_Models_Document_Order::processPositions`, this method uses the `TaxAggregator::positionsTaxSum` method now
* Updated `league/flysystem` to version 1.1.6
* Updated `symfony/config` to version 4.4.34
* Updated `symfony/console` to version 4.4.34
* Updated `symfony/dependency-injection` to version 4.4.34
* Updated `symfony/expression-language` to version 4.4.34
* Updated `symfony/form` to version 4.4.34
* Updated `symfony/http-foundation` to version 4.4.34
* Updated `symfony/http-kernel` to version 4.4.34
* Updated `symfony/process` to version 4.4.34
* Updated `symfony/serializer` to version 5.3.12
* Updated `symfony/validator` to version 4.4.34
* Updated several indirect dependencies

### Removals

* Removed deprecated composer dependency `symfony/class-loader`. Use Composer ClassLoader instead

### Session validation

With v5.7.7 the session validation was adjusted, so that sessions created prior
to the latest password change of a customer account can't be used to login with
said account. This also means, that upon a password change, all existing
sessions for a given customer account are automatically considered invalid.

All sessions created prior to v5.7.7 are lacking the timestamp of the latest
password change and are therefore not considered valid anymore. **After an
upgrade to v5.7.7, all customers who have a session in the given shop, will need
to log in again.**

## 5.7.6

[View all changes from v5.7.5...v5.7.6](https://github.com/shopware/shopware/compare/v5.7.5...v5.7.6)

### Additions

* Added a new CSP directive to the default `.htaccess`

## 5.7.5

[View all changes from v5.7.4...v5.7.5](https://github.com/shopware/shopware/compare/v5.7.4...v5.7.5)

## 5.7.4

[View all changes from v5.7.3...v5.7.4](https://github.com/shopware/shopware/compare/v5.7.3...v5.7.4)

### Deprecations

* Deprecated `ajaxValidateEmailAction`. It will be removed in Shopware 5.8 with no replacement.

### Additions

* Added filter event `Shopware_Controllers_Order_OpenPdf_FilterName` to `Shopware_Controllers_Backend_Order::openPdfAction()`
* Added new composer dependency `psr/http-message`
* Added new parameter `rowIndex` to `Shopware_Modules_Export_ExportResult_Filter_Fixed` event

### Breaks

* In case you have extended the `frontend_listing_actions_filter` block to override the "include" of the button template,
please extend the `frontend_listing_actions_filter_include` block from now on instead.

### Changes

* Changed `themes/Frontend/Bare/frontend/listing/listing_actions.tpl` to remove a duplicate name entry
* Updated TinyMCE to version 3.5.12
* Updated `bcremer/line-reader` to version 1.1.0
* Updated `beberlei/assert` to version 3.3.1
* Updated `beberlei/doctrineextensions` to version 1.3.0
* Updated `doctrine/cache` to version 1.12.1
* Updated `doctrine/collections` to version 1.6.8
* Updated `doctrine/common` to version 3.1.2
* Updated `doctrine/dbal` to version 2.13.4
* Updated `doctrine/orm` to version 2.9.5
* Updated `doctrine/persistence` to version 2.2.2
* Updated `guzzlehttp/guzzle` to version 7.3.0
* Updated `guzzlehttp/psr7` to version 1.8.2
* Updated `laminas/laminas-code` to version 4.4.3
* Updated `.aminas/laminas-escaper` to version 2.9.0
* Updated `mpdf/mpdf` to version 8.0.13
* Updated `ocramius/proxy-manager` to version 2.13.0
* Updated `ongr/elasticsearch-dsl` to version 7.2.2
* Updated `setasign/fpdf` to version 1.8.4
* Updated `setasign/fpdi` to version 2.3.6
* Updated `symfony/serializer` to version 5.3.8
* Updated `friends-of-behat/mink-extension` to version 2.5.0
* Updated `sensiolabs/behat-page-object-extension` to version 2.3.3
* Changed several Doctrine types to better match the database type or to improve understanding their purpose
  * \Shopware\Models\Article\Configurator\PriceVariation::$variation
  * \Shopware\Models\Article\Detail::$purchasePrice
  * \Shopware\Models\Article\Price::$percent
  * \Shopware\Models\Blog\Comment::$points
  * \Shopware\Models\Country\Country::$taxFree
  * \Shopware\Models\Country\Country::$taxFreeUstId
  * \Shopware\Models\Country\Country::$taxFreeUstIdChecked
  * \Shopware\Models\Emotion\Emotion::$active
  * \Shopware\Models\Emotion\Emotion::$fullscreen
  * \Shopware\Models\Emotion\Emotion::$isLandingPage
  * \Shopware\Models\Newsletter\ContainerType\Article::$position
  * \Shopware\Models\Order\Order::$invoiceShippingTaxRate
  * \Shopware\Models\Premium\Premium::$startPrice
  * \Shopware\Models\Tax\Rule::$tax

### Removals

* Removed unused composer dependency `php-http/message`

## 5.7.3

[View all changes from v5.7.2...v5.7.3](https://github.com/shopware/shopware/compare/v5.7.2...v5.7.3)

### Changes

* Updated `wikimedia/less.php` to 3.1.0

### Removals

* Removed password hash from session
* Removed xml support for the snippet importer

## 5.7.2

[View all changes from v5.7.1...v5.7.2](https://github.com/shopware/shopware/compare/v5.7.1...v5.7.2)

### Changes

* Updated `league/flysystem` to 1.1.4

## 5.7.1

[View all changes from v5.7.0...v5.7.1](https://github.com/shopware/shopware/compare/v5.7.0...v5.7.1)

### Additions

* Added service alias from `Template` to `template`
* Added service alias from `Loader` to `loader`

### Changes

* Changed the visibility of services from tags `shopware_emotion.component_handler`, `criteria_request_handler` and `sitemap_url_provider` to public
* Changed following columns type from `date` to `datetime`
  * `s_order_basket.datum`
  * `s_order_comparisons.datum`
  * `s_order_notes.datum`

## 5.7.0

[View all changes from v5.6.10...v5.7.0](https://github.com/shopware/shopware/compare/v5.6.10...v5.7.0)

### Breaks

* Do not use the `count()` smarty function in your templates anymore, since this will break with PHP version > 8.0. Use `|count` modifier instead!
* Replaced `psh` and `ant` with an `Makefile`. See updated README.md for installation workflow.
* Changed min PHP version to 7.4
* Changed min Elasticsearch version to 7
* Added new required methods `saveCustomUrls` and `saveExcludedUrls` to interface `Shopware\Bundle\SitemapBundle\ConfigHandler\ConfigHandlerInterface`
* Changed Symfony version to 4.4
* Changed Slugify version to 3.2
* Changed Doctrine ORM version to 2.7.3
* Changed Doctrine Cache version to 1.10.2
* Changed Doctrine Common version to 3.0.2
* Changed Doctrine Persistence version to 2.0.0
* Changed Guzzle version to 7.1
* Changed Monolog version to 2
* Changed FPDF version to 1.8.2
* Changed FPDI version to 2.2.0
* Changed mPDF version to 8.0.7
* Migrated Zend components to new Laminas
* Elasticsearch indices doesn't use anymore types

### Additions

* Added Symfony session to `Request` object
* Added new user interface for the sitemap configuration. It's available in the backend performance module
* Added `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` to save and read the sitemap configuration from the database
* Added new doctrine model `Shopware\Models\Emotion\LandingPage`, which extends from `Shopware\Models\Emotion\Emotion`.
It's needed to search for landing pages only using the backend store `Shopware.store.Search`
* Added new doctrine models `Shopware\Models\Sitemap\CustomUrl` and `Shopware\Models\Sitemap\ExcludeUrl`
* Added new ExtJS component `Shopware.grid.Searchable`.
Using it you can search for different entities in a single grid, such as products, categories, blogs, etc.
Have a look at the new sitemap UI to see what it looks like
* Added `Shopware-Listing-Total` header to ajax listing loading
* Added database transaction around plugin uninstall, activate and deactivate
* Added support for MySQL 8 `sql_require_primary_key`
* Added `attribute` to users listing in API
* Added new blocks `document_index_head_logo` and `document_index_head_wrapper` to `themes/Frontend/Bare/documents/index.tpl`
* Added `unmapped_type` to `integer` in `engine/Shopware/Bundle/SearchBundleES/SortingHandler/ManualSortingHandler.php`
* Added a notice to registration form when a shipment blocked country has been selected

### Changes

* Changed `Shopware\Models\Order\Order` and `Shopware\Models\Order\Detail` models by extracting business logic into:
    * `Shopware\Bundle\OrderBundle\Service\StockService`
    * `Shopware\Bundle\OrderBundle\Service\CalculationService`
    * `Shopware\Bundle\OrderBundle\Subscriber\ProductStockSubscriber`
    * `Shopware\Bundle\OrderBundle\Subscriber\OrderRecalculationSubscriber`
* Changed `Enlight_Components_Session_Namespace` to extend from `Symfony\Component\HttpFoundation\Session\Session`
* Changed the default config for smarty `compileCheck` to false
* Changed following columns to nullable
    * `s_order_details.releasedate`
    * `s_core_auth.lastlogin`
    * `s_campaigns_logs.datum`
    * `s_emarketing_banners.valid_from`
    * `s_emarketing_banners.valid_to`
    * `s_emarketing_lastarticles.time`
    * `s_emarketing_tellafriend.datum`
    * `s_order_basket.datum`
    * `s_order_comparisons.datum`
    * `s_order_notes.datum`
    * `s_statistics_pool.datum`
    * `s_statistics_referer.datum`
    * `s_statistics_visitors.datum`
    * `s_user.firstlogin`
    * `s_user.lastlogin`
* Changed response from `Shopware_Controllers_Widgets_Listing` from JSON to HTML
* Changed emotion component names to allow translations using snippets
    * `Artikel` => `product`
    * `Kategorie-Teaser` => `category_teaser`
    * `Blog-Artikel` => `blog_article`
    * `Banner` => `banner`
    * `Banner-Slider` => `banner_slider`
    * `Youtube-Video` => `youtube`
    * `Hersteller-Slider` => `manufacturer_slider`
    * `Artikel-Slider` => `product_slider`
    * `HTML-Element` => `html_element`
    * `iFrame-Element` => `iframe`
    * `HTML5 Video-Element` => `html_video`
    * `Code Element` => `code_element`
* Changed the search to not consider keywords which match 90% of all variants 
* Changed `\Shopware\Bundle\ESIndexingBundle\Product\ProductProvider` to set `hasStock` based on instock like DBAL implementation
* Changed `\Shopware_Controllers_Backend_ProductStream::loadPreviewAction` to return formatted prices
* Changed `sw:plugin:activate` exit code from 1 to 0, when it's already installed.
* Changed `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway::get` it accepts now only integers as id
* Changed `sw:es:index:populate` to accept multiple shop ids with `--shopId={1,2}`
* Changed `\Shopware\Bundle\ESIndexingBundle\Product\ProductProvider` to consider cheapest price configuration
* Changed `\Shopware\Bundle\PluginInstallerBundle\Service\PluginInstaller` to remove also menu translations

### Removals

* Removed following classes:
    * `Enlight_Components_Session`
    * `Enlight_Components_Session_SaveHandler_DbTable`
    * `Zend_Session`
    * `Zend_Session_Namespace`
    * `Zend_Session_Abstract`
    * `Zend_Session_Exception`
    * `Zend_Session_SaveHandler_DbTable`
    * `Zend_Session_SaveHandler_Exception`
    * `Zend_Session_SaveHandler_Interface`
    * `Zend_Session_Validator_Abstract`
    * `Zend_Session_Validator_HttpUserAgent`
    * `Zend_Session_Validator_Interface`
    * `Shopware\Components\Log\Handler\ChromePhpHandler`
    * `Shopware\Components\Log\Handler\FirePHPHandler`
    * `\Shopware_Plugins_Core_Debug_Bootstrap`
    * `\Shopware\Plugin\Debug\Components\CollectorInterface`
    * `\Shopware\Plugin\Debug\Components\ControllerCollector`
    * `\Shopware\Plugin\Debug\Components\DatabaseCollector`
    * `\Shopware\Plugin\Debug\Components\DbalCollector`
    * `\Shopware\Plugin\Debug\Components\ErrorCollector`
    * `\Shopware\Plugin\Debug\Components\EventCollector`
    * `\Shopware\Plugin\Debug\Components\ExceptionCollector`
    * `\Shopware\Plugin\Debug\Components\TemplateCollector`
    * `\Shopware\Plugin\Debug\Components\TemplateVarCollector`
    * `\Shopware\Plugin\Debug\Components\Utils`
    * `\Shopware\Components\Api\Resource\ApiProgressHelper`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContext`
    * `\Shopware\Components\OpenSSLEncryption`
    * `\Shopware\Bundle\SearchBundleES\DependencyInjection\Factory\ProductNumberSearchFactory`
* Removed method `\Shopware\Bundle\EsBackendBundle\EsBackendIndexer::buildAlias` use `\Shopware\Bundle\EsBackendBundle\IndexFactoryInterface::createIndexConfiguration` instead
* Removed method `\Shopware\Bundle\SearchBundleES\DependencyInjection\Factory\ProductNumberSearchFactory::registerHandlerCollection`, use DI Tag `shopware_search_es.search_handler` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryAll`, use `\Shopware\Components\Model\ModelRepository::findAll` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryAll`, use `\Shopware\Components\Model\ModelRepository::findAll` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryBy`, use `\Shopware\Components\Model\ModelRepository::findBy` instead
* Removed following interfaces:
    * `\Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface`
    * `\Shopware\Bundle\ESIndexingBundle\Property\PropertyProviderInterface`
    * `\Shopware\Bundle\ESIndexingBundle\EsSearchInterface`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContextInterface`
* Removed from class `\Shopware\Components\HttpCache\CacheWarmer` following methods:
    * `callUrls`
    * `getSEOURLByViewPortCount`
    * `getAllSEOUrlCount`
    * `getAllSEOUrls`
    * `getSEOUrlByViewPort`
    * `prepareUrl`
    * `getShopDataById`
* Removed following methods from class `\Shopware_Controllers_Backend_Search`:
    * `getArticles` 
    * `getCustomers` 
    * `getOrders` 
* Removed referenced value from magic getter in session
* Removed the assignment of all request parameters to the view in `Shopware_Controllers_Widgets_Listing::productsAction`
* Removed duplicate ExtJs classes and added alias to new class:
    * `Shopware.apps.Config.view.element.Boolean`
    * `Shopware.apps.Config.view.element.Button`
    * `Shopware.apps.Config.view.element.Color`
    * `Shopware.apps.Config.view.element.Date`
    * `Shopware.apps.Config.view.element.DateTime`
    * `Shopware.apps.Config.view.element.Html`
    * `Shopware.apps.Config.view.element.Interval`
    * `Shopware.apps.Config.view.element.Number`
    * `Shopware.apps.Config.view.element.ProductBoxLayoutSelect`
    * `Shopware.apps.Config.view.element.Select`
    * `Shopware.apps.Config.view.element.SelectTree`
    * `Shopware.apps.Config.view.element.Text`
    * `Shopware.apps.Config.view.element.TextArea`
    * `Shopware.apps.Config.view.element.Time`
* Removed following unused dependencies
    * `egulias/email-validator`
    * `symfony/translation`
    * `php-http/curl-client`
    * `psr/link`
    * `symfony/polyfill-ctype`
    * `symfony/polyfill-iconv`
    * `symfony/polyfill-iconv`
    * `symfony/polyfill-php56`
    * `symfony/polyfill-php70`
    * `symfony/polyfill-php71`
    * `symfony/polyfill-php72`
* Removed field `size` from `Shopware\Models\Article\Download`. Use media_service to get the correct file size
* Removed plugin `Debug`

### Deprecations

* Deprecated the class `Shopware\Bundle\SitemapBundle\ConfigHandler\File`.
It will be removed in Shopware 5.8. Use `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` instead.
* Deprecated getting plugin config from `Shopware_Components_Config` without plugin namespace, use `SwagTestPlugin:MyConfigName` instead
* Deprecated the class `\Shopware\Components\Plugin\DBALConfigReader`.
It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
* Deprecated the class `\Shopware\Components\Plugin\CachedConfigReader`.
It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
