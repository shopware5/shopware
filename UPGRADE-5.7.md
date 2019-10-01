# CHANGELOG for Shopware 5.7.x

This changelog references changes done in Shopware 5.7 patch versions.

[View all changes from v5.6.1...v5.7.0](https://github.com/shopware/shopware/compare/v5.6.1...v5.7.0)

### Breaks

* Added new required methods `saveCustomUrls` and `saveExcludedUrls` to interface `Shopware\Bundle\SitemapBundle\ConfigHandler\ConfigHandlerInterface`

### Additions

* Added Symfony session to `Request` object
* Added new user interface for the sitemap configuration. It's available in the backend performance module
* Added `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` to save and read the sitemap configuration from the database
* Added new doctrine model `Shopware\Models\Emotion\LandingPage`, which extends from `Shopware\Models\Emotion\Emotion`. It's needed to search for landing pages only using the backend store `Shopware.store.Search`
* Added new doctrine models `Shopware\Models\Sitemap\CustomUrl` and `Shopware\Models\Sitemap\ExcludeUrl`
* Added new ExtJS component `Shopware.grid.Searchable`. Using it you can search for different entities in a single grid, such as products, categories, blogs, etc. Have a look at the new sitemap UI to see what it looks like

### Changes

* Changed `Shopware\Models\Order\Order` and `Shopware\Models\Order\Detail` models by extracting business logic into:
    * `Shopware\Bundle\OrderBundle\Service\StockService`
    * `Shopware\Bundle\OrderBundle\Service\CalculationService`
    * `Shopware\Bundle\OrderBundle\Subscriber\ProductStockSubscriber`
    * `Shopware\Bundle\OrderBundle\Subscriber\OrderRecalculationSubscriber`
* Changed `Enlight_Components_Session_Namespace` to extend from `Symfony\Component\HttpFoundation\Session\Session`
* Changed the default config for smarty `compileCheck` to false

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

### Deprecations

* Deprecated the class `Shopware\Bundle\SitemapBundle\ConfigHandler\File`. It will be removed in Shopware 5.8. Use `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` instead.
