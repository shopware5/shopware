# CHANGELOG for Shopware 5.7.x

This changelog references changes done in Shopware 5.7 patch versions.

[View all changes from v5.6.1...v5.7.0](https://github.com/shopware/shopware/compare/v5.6.1...v5.7.0)

### Breaks

* Replaced `psh` and `ant` with an `Makefile`. See updated README.md for installation workflow.
* Changed min PHP version to 7.3
* Changed min Elasticsearch version to 7
* Added new required methods `saveCustomUrls` and `saveExcludedUrls` to interface `Shopware\Bundle\SitemapBundle\ConfigHandler\ConfigHandlerInterface`
* Changed Symfony version to 4.4
* Changed Slugify version to 3.2
* Changed Doctrine ORM version to 2.7.3
* Changed Doctrine Cache version to 1.10.2
* Changed Doctrine Common version to 3.0.2
* Changed Doctrine Persistence version to 2.0.0
* Changed Guzzle version to 7.0.1
* Changed Monolog version to 2.0.1
* Changed FPDF version to 1.8.2
* Changed FPDI version to 2.2.0
* Changed mPDF version to 8.0.7
* Migrated Zend components to new Laminas

### Additions

* Added Symfony session to `Request` object
* Added new user interface for the sitemap configuration. It's available in the backend performance module
* Added `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` to save and read the sitemap configuration from the database
* Added new doctrine model `Shopware\Models\Emotion\LandingPage`, which extends from `Shopware\Models\Emotion\Emotion`. It's needed to search for landing pages only using the backend store `Shopware.store.Search`
* Added new doctrine models `Shopware\Models\Sitemap\CustomUrl` and `Shopware\Models\Sitemap\ExcludeUrl`
* Added new ExtJS component `Shopware.grid.Searchable`. Using it you can search for different entities in a single grid, such as products, categories, blogs, etc. Have a look at the new sitemap UI to see what it looks like
* Added `Shopware-Listing-Total` header to ajax listing loading
* Added database transaction around plugin uninstall, activate and deactivate
* Added support for MySQL 8 `sql_require_primary_key`
* Added `attribute` to users listing in API

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
* Removed plugin `Debug`

### Deprecations

* Deprecated the class `Shopware\Bundle\SitemapBundle\ConfigHandler\File`. It will be removed in Shopware 5.8. Use `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` instead.
* Deprecated getting plugin config from `Shopware_Components_Config` without plugin namespace, use `SwagTestPlugin:MyConfigName` instead
* Deprecated the class `\Shopware\Components\Plugin\DBALConfigReader`. It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
* Deprecated the class `\Shopware\Components\Plugin\CachedConfigReader`. It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
