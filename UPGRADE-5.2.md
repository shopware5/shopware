# CHANGELOG for Shopware 5.2.x

This changelog references changes done in Shopware 5.2 patch versions.

## 5.2.19
* Changed the loading of backend widgets to disable widgets of deactivated plugins
* Added new Event `Shopware_Modules_Admin_regenerateSessionId_Start` in sAdmin::regenerateSessionId
* Changed `convertCategory` method in `engine/Shopware/Core/sCategories.php` from private to public
* Added left join of `s_order_basket_attributes` in `sAdmin::sGetDispatchBasket()` and `sExport::sGetDispatchBasket()`
* Added event `blog-save-successfully` to `onSaveBlogArticle()` method in `themes/Backend/ExtJs/backend/blog/controller/blog.js`
* Added event `customer-address-save-successfully` to `onSaveCustomer()` method in `themes/Backend/ExtJs/backend/customer/controller/detail.js`
* Added event `customer-save-successfully` to  `onSaveCustomer()` method in `themes/Backend/ExtJs/backend/customer/controller/detail.js`
* Added event `site-save-successfully` to `onSaveSite()` method in `themes/Backend/ExtJs/backend/site/controller/form.js`
* Added event `supplier-save-successfully` to `onSupplierSave()` method in `themes/Backend/ExtJs/backend/supplier/controller/main.js`
* Added the possibility to add a Theme info tab. Add a folder with the name "info" to your theme folder. Add a html file to the folder with the required language iso like "en_EN.html". The HTML content of the file is the content of the tab.
* Changed internal loop variable name `positions` in `themes/Frontend/Bare/documents/index.tpl` to fix typo
* Added configuration option `ShopwarePlugins` to `plugin_directories` in the `engine/Shopware/Configs/Default.php` to make the path of the plugin system directory configurable
* Support for custom CSS files in themes added to Grunt tasks

## 5.2.17
[View all changes from v5.2.16...v5.2.17](https://github.com/shopware/shopware/compare/v5.2.16...v5.2.17)

* Deprecated Smarty modifier `rewrite`. Modifier will be removed in 5.3.0.
* Changed default `session.gc_divisor` to `200`. To decrease session garbage collection probability.
* Added console command `sw:session:cleanup` to cleanup expired sessions.
* Changed database field `s_core_sessions.expiry` to contain the timestamp when the session should expire, not the session lifetime.
* Changed database field `s_core_sessions_backend.expiry` to contain the timestamp when the session should expire, not the session lifetime.
* Added `$sAmountNumeric` and `$sAmountNetNumeric` to sOrder mail
* Added command `sw:media:optimize` to optimize media files without quality loss.
* Added new Smarty blocks to `documents/index.tpl`
    * `document_index_address`
    * `document_index_address_sender`
    * `document_index_address_base`
* Added `s_article_configurator_options_attributes` and `s_article_configurator_groups_attributes`
* Added new plugin config element type `button`
* Changed `\Enlight_Controller_Plugins_ViewRenderer_Bootstrap::setNoRender` resets now the view template to prevent `PostDispatchSecure` events
* Changed `\Shopware\Bundle\StoreFrontBundle\Service\Core\ManufacturerService::getList` fetch now the seo urls for each manufacturer
* Removed duplicated initialisation of `\Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface`.
* Added `_seo` parameter for smarty url plugin which allows to prevent s_core_rewrite_url query for none seo urls
* Removed unnecessary `/widget/index/menu` call in `themes/Frontend/Bare/frontend/index/topbar-navigation.tpl` and `themes/Frontend/Bare/frontend/index/footer_minimal.tpl`. The `widgets/index/menu.tpl` template is now included directly.
* Added `\Shopware\Components\Theme\PathResolver::getDirectoryByArray` function which allows to load theme inheritances without doctrine models
* Added api resources to dependency injection container

### Media Optimizer

The service `shopware_media.optimizer_service` optimizes files using external tools. Further external tools can be implemented using the interface `Shopware\Bundle\MediaBundle\Optimizer\OptimizerInterface` and the dependency injection tag `shopware_media.optimizer`.

### Api resources
The api resources are now available in the dependency injection container using the namespace `shopware.api`. It is now possible to add your own resources as services or decorate others in your plugins `services.xml`.
```
/** Register new resource as service */
<service id="shopware.api.example" class="SwagExampleApi\Components\Api\Resource\Example" />

/** Replace existing resource service */
<service 
    id="swag_example_plugin.article_api"
    class="SwagExampleApi\Components\Api\Resource\Article"
    decorates="shopware.api.article"
    public="false"
    shared="false">
</service>
```

## 5.2.15

[View all changes from v5.2.14...v5.2.15](https://github.com/shopware/shopware/compare/v5.2.14...v5.2.15)

* Fixed article api resource when creating a new article with new configurator options and an image mapping for this new options
* Added cronjob registration via `Resources/cronjob.xml` file
* Removed call to `strip_tags` in inputfilter on all request parameters.
    * Please make sure untrusted input is escaped in plugins 

## 5.2.14

[View all changes from v5.2.13...v5.2.14](https://github.com/shopware/shopware/compare/v5.2.13...v5.2.14)

### Add property "valueField" to the media field.
* The shopping world element "Media field" supports now to change the value field. All possible properties you can find in the file: `themes/Backend/ExtJs/backend/media_manager/model/media.js`

 Example:
 
```php
 $emotionElement->createMediaField([
     'name' => 'preview_image',
     'fieldLabel' => 'The preview image',
     'valueField' => 'virtualPath'
 ]);
 ```

## 5.2.13

[View all changes from v5.2.12...v5.2.13](https://github.com/shopware/shopware/compare/v5.2.12...v5.2.13)

* Changed duplicate smarty block from `frontend_checkout_confirm_information_addresses_equal_panel_shipping_select_address` to `frontend_checkout_confirm_information_addresses_equal_panel_shipping_add_address` in `frontend/checkout/confirm.tpl`
* Added interface `\Shopware\Bundle\ESIndexingBundle\TextMappingInterface` which handles text field mappings for different elastic search versions
* Added the missing requirement of the php function `parse_ini_file` to the system info and the installer
* Added `Shopware\Components\Plugin\PaymentInstaller` class to install payment methods in plugins
* Changed theme path for plugins of new plugin system from `/resources` to `/Resources`
* Changed parsing of JSON `POST`ed to the REST API to not remove top-level `NULL` values
* Changed frontendsession to a locking session handler
    * Added new configuration parameter `session.locking` which is `true` by default
    * The session handler can be overwritten by replacing the `session.save_handler`-Service. A instance of `\SessionHandlerInterface` has to be returned.
* Changed return value of `sArticles::sGetArticleById()` to provide an additional text if none is given and display the cover image by default when using the selection configurator
* Changed url parameter in last seen articles to deeplink to an article variant instead of the article
* Added console command `sw:rebuild:seo:index` to rebuild the SEO index on demand

### Autoloading of plugin resources

Plugin resources inside of the `PluginName/Resources/frontend` directory are now loaded automatically on theme compilation when using the new plugin system.

Example:

- `custom/plugins/SwagResourceTest/Resources/frontend/css/**.css`
- `custom/plugins/SwagResourceTest/Resources/frontend/js/**.js`
- `custom/plugins/SwagResourceTest/Resources/frontend/less/all.less`

## 5.2.12

[View all changes from v5.2.11...v5.2.12](https://github.com/shopware/shopware/compare/v5.2.11...v5.2.12)

## 5.2.11

[View all changes from v5.2.10...v5.2.11](https://github.com/shopware/shopware/compare/v5.2.10...v5.2.11)

* Added new Smarty block `frontend_robots_txt_allows` to `frontend/robots_txt/index.tpl`
* Added new Smarty block `frontend_account_order_item_availability` to `frontend/account/order_item_details.tpl`
* Added new rule for `/widgets/emotion` to robots.txt in `frontend/robots_txt/index.tpl`
* Added new blocks in `themes/Frontend/Bare/frontend/account/index.tpl`
* If shipping address equals billing address show notice instead of same address twice in account index
* Added container tag `shopware_media.adapter` to register new media adapters
* Added interface `Shopware\Bundle\MediaBundle\Adapters\AdapterFactoryInterface` to create new adapter factories
* Removed method `Shopware\Bundle\MediaBundle\Subscriber\ServiceSubscriber::createLocalAdapter()`
* Removed method `Shopware\Bundle\MediaBundle\Subscriber\ServiceSubscriber::createFtpAdapter()`
* Deprecated collect event `Shopware_Collect_MediaAdapter_*`, use container tag `shopware_media.adapter` instead. The event will be removed in 5.4.

### Custom stores in plugin configuration

It is now possible to define custom config stores directly inside your plugin's config.xml when using the new plugin system.

A custom config store is defined like this:

```
<store>
    <option>
        <value>1</value>
        <label lang="de">Deutscher Anzeigewert</label>
        <label lang="en">English display value</label>
    </option>
    <option>
        <value>two</value>
        <label lang="de">Deutscher Anzeigewert</label>
        <label>English display value (locale en via fallback)</label>
    </option>
    <option>
        <value>3</value>
        <label>Single display value (locale en via fallback)</label>
    </option>
</store>
```

There are two unique constraints:
* Inside a store, a value tag's value must only occur once
* Inside an option tag, a label tag's lang attribute value must only occur once

Additionally, the order is fixed. The value tag must be defined before the label tag(s).

There must be at least one option tag and inside each option tag where must be at least one value and one option tag. 

## 5.2.10

[View all changes from v5.2.9...v5.2.10](https://github.com/shopware/shopware/compare/v5.2.9...v5.2.10)

* Added optional `filter` option to `Shopware.apps.Base.view.element.Select`
* Set `remoteFilter` to `true` in several *base* stores:
    * `Shopware.apps.Base.store.Country`
    * `Shopware.apps.Base.store.CountryArea`
    * `Shopware.apps.Base.store.CountryState`
    * `Shopware.apps.Base.store.Currency`
    * `Shopware.apps.Base.store.CustomerGroup`
    * `Shopware.apps.Base.store.Dispatch`
    * `Shopware.apps.Base.store.Locale`
    * `Shopware.apps.Base.store.OrderStatus`
    * `Shopware.apps.Base.store.Payment`
    * `Shopware.apps.Base.store.PaymentStatus`
    * `Shopware.apps.Base.store.PositionStatus`
    * `Shopware.apps.Base.store.Tax`
* Replaced the default filter on `dispatches.active` added in `Shopware_Controllers_Backend_Base::getDispatchesAction()` by a default filter on `active` added in `Shopware.apps.Base.store.Dispatch`
* Refactored jQuery product slider plugin for sliding infinitely
* Added `initOnEvent` option to cross selling tabs on detail page for the combination of tabs with product sliders
* Added `Shopware\Components\Emotion\ComponentInstaller` class to install emotion components in plugins.
* Added `\Shopware\Components\Emotion\EmotionComponentViewSubscriber` to register emotion widget templates 
* Added `plugin_dir` and `plugin_name` container parameter for each plugin. Parameters are prefixed by `\Shopware\Components\Plugin::getContainerPrefix`
* Deprecated parameter `$checkProxy` from `Enlight_Controller_Request_Request::getClientIp()`

## 5.2.9

[View all changes from v5.2.8...v5.2.9](https://github.com/shopware/shopware/compare/v5.2.8...v5.2.9)

* `filtergroupID` column will be set to `null` in the `s_articles` table when deleting a property set 

## 5.2.8

[View all changes from v5.2.7...v5.2.8](https://github.com/shopware/shopware/compare/v5.2.7...v5.2.8)

* Fixed a PHP 7 fatal error in the SVG rendering of mPDF
* Added missing update of the order details' order number, when converting a cancelled order to a *normal* order in `Shopware_Controllers_Backend_CanceledOrder::convertOrderAction()`
* Added creation of `Shopware\Models\Attribute\OrderDetail` upon adding a new order position in the backend
* Added missing creation of `Shopware\Models\Attribute\Order` and `Shopware\Models\Attribute\OrderDetail` instances in `sOrder::sCreateTemporaryOrder()`

## 5.2.7

[View all changes from v5.2.6...v5.2.7](https://github.com/shopware/shopware/compare/v5.2.6...v5.2.7)

* Add support for third party post messages in the backend
* getOne function of customer api resource contains now the country and state data for billing and shipping address
* Changed `jquery.search::onKeyboardNavigation()` method to provide more extension possibilities.
* Deprecated the execution shopware.php via CLI. Please use the command line tool in `bin/console` instead.

## 5.2.6

[View all changes from v5.2.5...v5.2.6](https://github.com/shopware/shopware/compare/v5.2.5...v5.2.6)

* Changed visibility of sAdmin::loginUser to protected
* Added filter events to all convert functions in `LegacyStructConverter`
* Removed unused shipping free configuration in backend country form
* Added new smarty blocks in `engine/Shopware/Plugins/Default/Frontend/AdvancedMenu/Views/frontend/advanced_menu/index.tpl`
* Add order attribute select to \Shopware\Components\Api\Resource\Order::getList query
* \Shopware\Bundle\StoreFrontBundle\Struct\ListProduct::isPriceGroupActive proofs additionally if a price group exists
* Use user ID of the API key owner for media files if none is provided
* Fixed image configurator html ids for radio boxes
* Added notifyUntil event `Shopware_Modules_Basket_BeforeAddMinimumOrderSurcharge` to `sBasket::sInsertSurcharge` containing the surcharge.
* Added notifyUntil event `Shopware_Modules_Basket_BeforeAddOrderSurchargePercent` to `sBasket::sInsertSurchargePercent` containing the surcharge.
* Added notifyUntil event `Shopware_Modules_Basket_BeforeAddOrderDiscount` to `sBasket::sInsertDiscount` containing the discount.
* Replaced `grunt-contrib-watch` with `grunt-chokidar` for the grunt watch task.
* Deprecated "{$sShopname}" variable in Forms.php, please use "{sShopname}" in your mail form templates instead.
* Added `\Shopware\Bundle\SearchBundle\SearchTermPreProcessorInterface` interface which pre filters provided search terms
* Added `ShopPage` to Storefront Bundle
* Attributes of `forms` and `shop pages` are not translatable anymore
* Added new Smarty blocks to `frontend/detail/image.tpl`
    * `frontend_detail_image_default_image_slider_item`
    * `frontend_detail_images_image_slider_item`
* Updated `ongr/elasticsearch-dsl` to version 2.0.2
* Updated `phpunit/phpunit` to version 5.5
* Removed unused smarty block `frontend_checkout_cart_item_small_quantites_tax_price` in `themes/Frontend/Bare/frontend/checkout/confirm_item.tpl`
* Added method `Enlight_Controller_Response_ResponseTestCase::sendCookies` so it's consistent with `Enlight_Controller_Response_ResponseHttp`
* Added `\Shopware\Components\DependencyInjection\Compiler\TagReplaceTrait` class which centralized service constructor replacements with prioritized tagged services

## 5.2.5

[View all changes from v5.2.4...v5.2.5](https://github.com/shopware/shopware/compare/v5.2.4...v5.2.5)

* Fixed SEO URL generation for URLs containing dots and forward slashes

## 5.2.4

[View all changes from v5.2.3...v5.2.4](https://github.com/shopware/shopware/compare/v5.2.3...v5.2.4)

* Introduced new interface `Shopware\Components\Slug\SlugInterface` to generate URL safe versions of a string
    * Service id `shopware.slug`
    * The default implementation delegates to [`cocur/slugify`](https://github.com/cocur/slugify)
    * SEO url generation now uses this new service to rewrite urls
* Added new media file type for 3D model files. Supporting following files in the media manager: .dae, .obj, .fbx, .spx, .3ds, .3mf, .blend, .awd, .ply, .pcd, .stl, .skp
* `\Shopware\Bundle\AttributeBundle\CrudService::unifiedToSql()` now returns SQL type mapped to `string` if a given type is not mapped
* Changed http response code to `400` for CSRF exceptions
* Added `is--active` class to wishlist entry in the account sidebar
* Added interface `Shopware\Bundle\ESIndexingBundle\Product\ProductQueryFactoryInterface` and implemented it in its implementations
* Removed class `Shopware_Components_Check_System`. Use `Shopware\Components\Check\Requirements` instead (Service Id: `shopware.requirements`)
* Added mixin `.clear-form-button()` to remove the default browser styling of form buttons
* Changed action links which modify data to use forms with HTTP `POST`. This affects the following templates and plugins:
    * `themes/Frontend/Bare/frontend/checkout/items/premium-product.tpl`
    * `themes/Frontend/Bare/frontend/checkout/items/product.tpl`
    * `themes/Frontend/Bare/frontend/checkout/items/voucher.tpl`
    * `themes/Frontend/Bare/frontend/checkout/ajax_cart_item.tpl`
    * `themes/Frontend/Bare/frontend/compare/index.tpl`
    * `themes/Frontend/Bare/frontend/detail/actions.tpl`
    * `themes/Frontend/Bare/frontend/listing/product-box/product-actions.tpl`
    * `themes/Frontend/Bare/frontend/note/item.tpl`
    * `themes/Frontend/Responsive/frontend/_public/src/js/jquery.collapse-cart.js`
    * `themes/Frontend/Responsive/frontend/_public/src/js/jquery.product-compare-add.js`
    * `themes/Frontend/Responsive/frontend/_public/src/js/jquery.product-compare-menu.js`
* Added event `Shopware_SearchBundle_Create_Base_Criteria` in `Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory::createBaseCriteria()`
* Changed visiblility of service `snippet_resource` to public in DI Container
* Added JS and LESS directory path of new plugin system to gruntfile
* Deprecated css class `icon--brogress-1`, use `icon--progress-1` instead
* Updated [CodeMirror](https://github.com/codemirror/CodeMirror) to version 5.17.0
* Improved mode support for CodeMirror element
* Allow uploading file when creating `media` using the REST API
* Increased max length of `s_emarketing_banners.img` and `s_articles_supplier.img` to 255
* Added filter events for editing the collection of LESS and JS files before compiling
    * `Theme_Compiler_Collect_Less_Definitions_FilterResult`
    * `Theme_Compiler_Collect_Javascript_Files_FilterResult`
* Removed synchronizing of plugin information column `changes`
* Allow root menu elements for plugins. Added attribute `isRootMenu` in `menu.xml` Example: `<entry isRootMenu="true">`

## 5.2.3

[View all changes from v5.2.2...v5.2.3](https://github.com/shopware/shopware/compare/v5.2.2...v5.2.3)

* Updated `guzzlehttp/guzzle` to version 5.3.1 to mitigate [httproxy](https://httpoxy.org/) vulnerability
* Set timeouts from install/update/(secure) uninstall operations in plugin manager to 300 seconds
* Fix countries rest api response data and header
* Added exception to Shopware updater if `php-curl` is missing
* Add support for integer and array values to the `sw:plugin:config:set` cli command
* Deprecated the `$strong` optional parameter from the following methods, as the component ensures a cryptographically secure pseudo-random number generator is always used since Shopware 5.2.0
    * `Shopware\Components\Random::getBytes()`
    * `Shopware\Components\Random::getBoolean()`
    * `Shopware\Components\Random::getInteger()`
    * `Shopware\Components\Random::getFloat()`
    * `Shopware\Components\Random::getString()`
    * `Shopware\Components\Random::getAlphanumericString()`

## 5.2.2 (2016-07-13)

[View all changes from v5.2.0...v5.2.2](https://github.com/shopware/shopware/compare/v5.2.0...v5.2.2)

* Add support for Symfony `console.command` service tag to register commands directly inside service container

## 5.2.0 (2016-07-01)

[View all changes from v5.1.6...v5.2.0](https://github.com/shopware/shopware/compare/v5.1.6...v5.2.0)

* Increased minimum required PHP version to PHP >= 5.6.4.
* Added CSRF protection to frontend and backend which is enabled by default.
    * OptOut by implementing `Shopware\Components\CSRFWhitelistAware` interface
    * Added `X-CSRF-Token` to every ajax request
    * Added `__csrf_token` to every html form in frontend
    * Added `__csrf_token` param to every ExtJS form submit via override in `ExtJs/overrides/Ext.form.Base.js`
    * Added `csrfProtection` config options to disable CSRF protection
    * Special thanks to: [ltepner](https://github.com/ltepner)
    * See: https://developers.shopware.com/developers-guide/csrf-protection/
* Updated Symfony Components to version 2.8 LTS
* Replaced polyfill provided by `indigophp/hash-compat` with `symfony/polyfill-php56`
* Added polyfill for `random_bytes()` and `random_int()` via `paragonie/random_compat`
* Removed `client_check` and `referer_check` from the config in favor of the CSRF protection.
* Removed session variables `__SW_REFERER` and `__SW_CLIENT`
* Added AdvancedMenu feature to configure menu opening delay on mouse hover
* Added the ability to add custom CSS classes to emotion elements in the backend.
    * Multiple classnames can be added by separating them with whitespaces.
    * Added new `css_class` column to the `s_emotion_elements` table.
* Removed deprecated columns `s_filter_values.value_numeric` and `s_filter_options.default`
* Updated `monolog/monolog` to version 1.17.2
* Added HTML code widget for the shopping worlds which lets the user enter actual Smarty & JavaScript code which will be included like it is
    * The Smarty code has access to all globally available Smarty variables
* Added the following fields to status emails:
    * `billing_additional_address_line1`
    * `billing_additional_address_line2`
    * `shipping_additional_address_line1`
    * `shipping_additional_address_line2`
* Replaced `bower` with `npm` to manage the frontend dependencies
    * The dependencies can now be installed using the command: `npm install && npm run build`
    * Removed the file `vendors/less/open-sans-fontface/open-sans.less`. It's now located under `public/src/less/_components/fonts.less`
* Deprecated `Shopware_Bootstrap` and `Enlight_Bootstrap` commonly accesed by `Shopware()->Bootstrap()`.
* Removed deprecated methods and variables:
    * `sArticle.sVoteAverange` in product listings
    * `sNote.sVoteAverange` in note listing
    * `blog.media.path`
    * Removed methods Shopware\Models\Menu\Repository::addItem() and Shopware\Models\Menu\Repository::save()
    * `file` property of banner mappings
    * Removed method sOrder::sManageEsdOrder()
    * `sBanner.img` variable
    * emotion category teaser `image` property
* Removed the following events:
    * `sArticles::sGetCheapestPrice::replace`
    * `sArticles::sGetCheapestPrice::after`
    * `sArticles::sCalculatingPrice::replace`
    * `sArticles::sCalculatingPrice::replace`
    * `sArticles::getArticleListingCover::replace`
    * `sArticles::getArticleListingCover::after`
    * `sArticles::calculateCheapestBasePriceData::replace`
    * `sArticles::calculateCheapestBasePriceData::after`
    * `sArticles::sGetArticleProperties::replace`
    * `sArticles::sGetArticleProperties::after`
    * `sArticles::sGetArticlePictures::replace`
    * `sArticles::sGetArticlePictures::after`
    * `sArticles::sGetPricegroupDiscount::replace`
    * `sArticles::sGetPricegroupDiscount::after`
    * `sArticles::sGetUnit::replace`
    * `sArticles::sGetUnit::after`
    * `sArticles::sGetArticlesAverangeVote::replace`
    * `sArticles::sGetArticlesAverangeVote::after`
    * `sArticles::sGetArticlesVotes::replace`
    * `sArticles::sGetArticlesVotes::after`
    * `Shopware_Modules_Articles_GetPromotionById_FilterResult`
    * `Shopware_Modules_Articles_GetArticleById_FilterArticle`
* The following article arrays are now indexed by their order number
    * top seller
    * emotion slider data
    * recommendation data (also bought and also viewed)
    * similar and related articles
* Removed deprecated table `s_user_debit`
    * `\Shopware\Models\Customer\Customer::$debit`
    * `\Shopware\Models\Customer\Customer::getDebit()`
    * `\Shopware\Models\Customer\Customer::setDebit()`
* Added new configuration field to the emotion banner widget for link target.
* Added composer dependency for Symfony Form and implemented FormBundle
* Changed constructor of `\Shopware\Components\Theme\PathResolver`
* Changed constructor of `\Shopware_Components_Snippet_Manager`
* Changed constructor of `\Shopware\Bundle\PluginInstallerBundle\Service\DownloadService`
* Changed signature of `Shopware\Bundle\SearchBundleDBAL\PriceHelperInterface::getSelection`, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\SearchBundleDBAL\PriceHelper::getSelection` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface::getList`, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\CheapestPriceServiceInterface::get`, now expects `ProductContextInterface` instead of `ShopContextInterface` and `ListProduct` instead of `BaseProduct`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService::getList` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface`
* Changed signature of `Shopware\Bundle\StoreFrontBundle\Service\Core\CheapestPriceService::get` to match changed interface, now expects `ProductContextInterface` instead of `ShopContextInterface` and `ListProduct` instead of `BaseProduct`
* Deprecated methods now use `trigger_error` of type `E_USER_DEPRECATED`
* Changed default error_reporting to `E_ALL & ~E_USER_DEPRECATED`
* Deprecated Class `Enlight_Application`
* Deprecated `Enlight_Application::Instance()` and `Enlight()`, use `Shopware()` instead
* Deprecated `Shopware\Kernel::getShopware()`
* Deprecated `Shopware::App()` / `Shopware()->App()`
* Deprecated `Shopware::Environment()` / `Shopware()->Environment()`
* Deprecated `Shopware::OldPath()` / `Shopware()->OldPath()`
* Deprecated `Shopware::setEventManager()` / `Shopware()->setEventManager()`
* Deprecated `Enlight_Application::CorePath()` / `Shopware()->CorePath()`
* Deprecated `Enlight_Application::Path()` / `Shopware()->Path()`
* Deprecated `Enlight_Application::ComponentsPath()` / `Shopware()->ComponentsPath()`
* Deprecated `Enlight_Application::DS()`
* Removed `Enlight_Application::setOptions()`
* Removed `Enlight_Application::getOptions()`
* Removed `Enlight_Application::getOption()`
* Removed `Enlight_Application::setPhpSettings()`
* Removed `Enlight_Application::setIncludePaths()`
* Removed `Enlight_Application::__callStatic()`
* Removed the following models
    * `Shopware.apps.Customer.view.detail.Billing`
    * `Shopware.apps.Customer.view.detail.Shipping`
* Removed fax field form billing addresses
* Updated `ongr/elasticsearch-dsl` to v2.0.0, see https://github.com/ongr-io/ElasticsearchDSL/blob/master/CHANGELOG.md#v200-2016-03-03 for BC breaks.
* Renamed block 'frontend_blog_bookmarks_deliciosus' to 'frontend_blog_bookmarks_delicious'
* Deprecated `\Shopware\Models\Article\Element`
* Removed the following templates including their snippets and blocks
    * `frontend/account/billing.tpl`
    * `frontend/account/billing_checkout.tpl`
    * `frontend/account/content_right.tpl`
    * `frontend/account/select_address.tpl`
    * `frontend/account/select_billing.tpl`
    * `frontend/account/select_billing_checkout.tpl`
    * `frontend/account/select_shipping.tpl`
    * `frontend/account/select_shipping_checkout.tpl`
    * `frontend/account/shipping.tpl`
    * `frontend/account/shipping_checkout.tpl`
    * `frontend/checkout/cart_left.tpl`
    * `frontend/checkout/confirm_left.tpl`
* Removed `sAdmin::sGetPreviousAddresses()`
* Removed `sAdmin::sUpdateBilling()`
* Removed `sAdmin::sUpdateShipping()`
* Removed `sAdmin::sValidateStep1()`
* Removed `sAdmin::sValidateStep2()`
* Removed `sAdmin::sValidateStep2ShippingAddress()`
* Removed `billingAction()` in `Controllers/Frontend/Account.php`
* Removed `shippingAction()` in `Controllers/Frontend/Account.php`
* Removed `saveBillingAction()` in `Controllers/Frontend/Account.php`
* Removed `saveShippingAction()` in `Controllers/Frontend/Account.php`
* Removed `selectBillingAction()` in `Controllers/Frontend/Account.php`
* Removed `selectShippingAction()` in `Controllers/Frontend/Account.php`
* Moved block `frontend_checkout_confirm_left_billing_address` outside panel body
* Moved block `frontend_checkout_confirm_left_shipping_address` outside panel body
* Removed block `frontend_checkout_finish_info`, use `frontend_checkout_finish_information_wrapper` instead
* Removed the following backend models including their smarty blocks
    * `Shopware.apps.Supplier.model.Attribute`
    * `Shopware.apps.Customer.model.BillingAttributes`
    * `Shopware.apps.Customer.model.ShippingAttributes`
    * `Shopware.apps.Customer.model.Attribute`
    * `Shopware.apps.Blog.model.Attribute`
    * `Shopware.apps.Form.model.Attribute`
    * `Shopware.apps.MediaManager.model.Attribute`
    * `Shopware.apps.Property.model.Attribute`
    * `Shopware.apps.Config.model.form.Attribute`
    * `Shopware.apps.Voucher.model.Attribute`
    * `Shopware.apps.Emotion.model.Attribute`
    * `Shopware.apps.Banner.model.Attribute`
    * `Shopware.apps.Order.model.Attribute`
    * `Shopware.apps.Order.model.BillingAttribute`
    * `Shopware.apps.Order.model.PositionAttribute`
    * `Shopware.apps.Order.model.ReceiptAttribute`
    * `Shopware.apps.Order.model.ShippingAttribute`
    * `Shopware.apps.Category.model.Attribute`
    * `Shopware.apps.Mail.model.Attribute`
    * `Shopware.apps.Payment.model.Attribute`
    * `Shopware.apps.Shipping.model.Attribute`
    * `Shopware.apps.Site.model.Attribute`
    * `Shopware.apps.UserManager.model.Attribute`
* The following repository methods no longer select attributes or have been removed entirely
    * `\Shopware\Models\Article\Repository::getSupplierQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getCustomerDetailQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getShippingAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getShippingAttributesQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getBillingAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getBillingAttributesQueryBuilder()`
    * `\Shopware\Models\Customer\Repository::getAttributesQuery()`
    * `\Shopware\Models\Customer\Repository::getAttributesQueryBuilder()`
    * `\Shopware\Models\Blog\Repository::getBackedDetailQueryBuilder()`
    * `\Shopware\Models\Emotion\Repository::getEmotionDetailQueryBuilder()`
    * `\Shopware\Models\ProductFeed\Repository::getDetailQueryBuilder()`
    * `\Shopware\Models\Banner\Repository::getBannerMainQuery()`
    * `\Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder()`
    * `\Shopware\Models\Order\Repository::getBackendAdditionalOrderDataQuery()`
* Removed attribute associations from the following backend models
    * `Shopware.apps.Supplier.model.Supplier`
    * `Shopware.apps.Customer.model.Customer`
    * `Shopware.apps.Blog.model.Detail`
    * `Shopware.apps.Form.model.Form`
    * `Shopware.apps.Property.model.Set`
    * `Shopware.apps.MediaManager.model.Media`
    * `Shopware.apps.Emotion.model.Emotion`
    * `Shopware.apps.Config.model.form.Country`
    * `Shopware.apps.Banner.model.BannerDetail`
    * `Shopware.apps.Voucher.model.Detail`
    * `Shopware.apps.Order.model.Receipt`
    * `Shopware.apps.Order.model.Position`
    * `Shopware.apps.Order.model.Order`
    * `Shopware.apps.Category.model.Detail`
    * `Shopware.apps.Customer.model.Customer`
    * `Shopware.apps.Payment.model.Payment`
    * `Shopware.apps.Shipping.model.Dispatch`
    * `Shopware.apps.Site.model.Nodes`
    * `Shopware.apps.UserManager.model.User`
    * `Shopware.apps.UserManager.model.UserDetail`
* Removed the following backend files:
    * `themes/Backend/ExtJs/backend/blog/view/blog/detail/sidebar/attributes.js`
    * `themes/Backend/ExtJs/backend/config/store/form/attribute.js`
    * `themes/Backend/ExtJs/backend/config/view/form/attribute.js`
    * `themes/Backend/ExtJs/backend/config/model/form/attribute.js`
* Changed position of `Shopware.apps.Customer.view.detail.Billing` fields
* Changed position of `Shopware.apps.Customer.view.detail.Shipping` fields
* Fixed Shopware.form.plugin.Translation, the plugin can now be used in multiple forms at the same time.
    * Removed `clear`, `onOpenTranslationWindow`, `getFieldValues` and `onGetTranslatableFields` function
* `\Shopware\Bundle\StoreFrontBundle\Gateway\GraduatedPricesGatewayInterface` requires now a provided `ShopContextInterface`
* Categories of `Shopware\Components\Api\Resource\Article::getArticleCategories($articleId)` are no longer indexed by category id
* Moved `<form>` element in checkout confirm outside the agreement box to wrap around address and payment boxes
* Removed smarty variable `sCategoryInfo` in listing and blog controllers. Use `sCategoryContent` instead.
* Added creation of custom `__construct()` method to `Shopware\Components\Model\Generator`, which initializes any default values of properties when generating attribute models
* Removed `sAdmin::sUpdateAccount()`
* Removed `saveAccount()` in `Controllers/Frontend/Account.php`
* Moved field `birthday` from billing address to customer
* Added validation of order number to `Shopware\Components\Api\Resource\Variant::prepareData()` to respond with meaningful error message for duplicate order numbers
* Added service `shopware.number_range_manager` for safely retrieving the next number of a number range (`s_order_number`)
* Changed the following methods to use the `shopware.number_range_manager` service for retrieving the next number of a range:
    * `sAdmin::assignCustomerNumber()`
    * `sOrder::sGetOrderNumber()`
    * `Shopware_Components_Document::saveDocument()`
* HttpCache: Added possibility to add multiple, comma separated proxy URLs
* API cache endpoint: Changed batchDelete in a way, that multiple cache types can be invalidated
* Removed `landingPageTeaser` and `landingPageBlock` fields from emotion shopping worlds.
* Removed unnecessary method `getCampaignByCategoryQuery()` from `Models/Emotion/Repository.php`.
* Removed template blocks for campaign boxes corresponding to the removed emotion fields.
    * `frontend_index_left_campaigns_top`
    * `frontend_index_left_campaigns_middle`
    * `frontend_index_left_campaigns_bottom`
    * `frontend_blog_index_campaign_top`
    * `frontend_blog_index_campaign_middle`
    * `frontend_blog_index_campaign_bottom`
* Removed unnecessary template file for campaign boxes `frontend/campaign/box.tpl`.
* Removed third party jQuery plugin dependency `masonry`.
* Deprecated `initMasonryGrid` method and `plugin/swEmotion/onInitMasonryGrid` event in `jquery.emotion.js`
* Removed shopping world mode `masonry`. The fallback is the new mode `fluid`.
* Replaced old LESS mixin `createColumnSizes` for new grid mixins `createGrid` and `createColumns` in `_components/emotion.less`.
* Added new blocks to `widgets/emotion/index.tpl` for better overriding of the configuration.
    * `widgets/emotion/index/config`
    * `widgets/emotion/index/attributes`
    * `widgets/emotion/index/element/config`
* Changed markup and styling on checkout confirm and finish page
* Support arbitrary namespaces for doctrine entities instead of the `Shopware\CustomModels` namespace.
* Deprecated `Shopware()->Models()->__call()`
* Removed unused database fields `s_core_config_elements.filters`, `s_core_config_elements.validators`, `s_core_config_forms.scope`
* Removed deprecated `\Shopware\Models\Menu\Repository::save()` and `\Shopware\Models\Menu\Repository::addItem()`
* Removed event `Shopware_Modules_Order_SaveOrderAttributes_FilterSQL`
* Removed event `Shopware_Modules_Order_SaveOrderAttributes_FilterDetailsSQL`
* Removed event `Shopware_Modules_Order_SaveBillingAttributes_FilterSQL`
* Removed event `Shopware_Modules_Order_SaveBillingAttributes_FilterArray`
* Removed event `Shopware_Modules_Admin_SaveRegisterShippingAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterShippingAttributes_Return`
* Removed event `Shopware_Modules_Admin_SaveRegisterBillingAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterBillingAttributes_Return`
* Removed event `Shopware_Modules_Admin_SaveRegisterMainDataAttributes_FilterSql`
* Removed event `Shopware_Modules_Admin_SaveRegisterMainDataAttributes_Return`
* The filter event `Shopware_Modules_Order_SaveBilling_FilterArray` now contains an associative array instead of one with numeric keys.
* The filter event `Shopware_Modules_Order_SaveBilling_FilterSQL` now uses named parameters in the query instead of question marks.
* The filter event `Shopware_Modules_Order_SaveShipping_FilterArray` now contains an associative array instead of one with numeric keys.
* The filter event `Shopware_Modules_Order_SaveShipping_FilterSQL` now uses named parameters in the query instead of question marks.
* Moved `s_articles_prices.baseprice` to `s_articles_details.purchaseprice`
    * Added new database field `s_articles_details.purchaseprice`.
    * Added property `purchasePrice` to `Shopware\Models\Article\Detail`.
    * Removed property `basePrice` of `Shopware\Models\Article\Price`.
    * Removed methods `Shopware\Models\Article\Price::getBasePrice()` and `Shopware\Models\Article\Price::setBasePrice()`.
    * Deprecated database field `s_articles_prices.baseprice`. All data is left intact but this field is not used in shopware anymore and will be dropped in a future version.
    * Removed property `basePrice` of `Shopware\Models\Article\Configurator\Template\Price`.
    * Removed database field `s_article_configurator_template_prices.baseprice`.
* Removed unused class `Shopware_Components_Menu_Item` and `Shopware_Components_Menu_SaveHandler_DbTable`
* Removed database fields
    * `s_core_menu.hyperlink`
    * `s_core_menu.style`
    * `s_core_menu.resourceID`
* Removed method `Shopware\Models\Menu\Menu::setStyle()` and `Shopware\Models\Menu\Menu::getStyle()`
* Removed class `Shopware_Models_Payment`
* Removed class `Shopware_Models_PaymentManager`
* Removed `Shopware_Plugins_Frontend_Payment_Bootstrap`, Service: `Shopware()->Payments()`
* Removed following methods:
    * \Shopware_Controllers_Frontend_Register::saveRegister
    * \Shopware_Controllers_Frontend_Register::personalAction
    * \Shopware_Controllers_Frontend_Register::savePersonalAction
    * \Shopware_Controllers_Frontend_Register::billingAction
    * \Shopware_Controllers_Frontend_Register::saveBillingAction
    * \Shopware_Controllers_Frontend_Register::shippingAction
    * \Shopware_Controllers_Frontend_Register::saveShippingAction
    * \Shopware_Controllers_Frontend_Register::paymentAction
    * \Shopware_Controllers_Frontend_Register::savePaymentAction
    * \Shopware_Controllers_Frontend_Register::validatePersonal
    * \Shopware_Controllers_Frontend_Register::setRegisterData
    * \Shopware_Controllers_Frontend_Register::validateBilling
    * \Shopware_Controllers_Frontend_Register::validateShipping
    * \Shopware_Controllers_Frontend_Register::validatePayment
    * \sAdmin::sSaveRegisterMainData
    * \sAdmin::sSaveRegisterNewsletter
    * \sAdmin::sSaveRegisterBilling
    * \sAdmin::sSaveRegisterShipping
    * \sAdmin::sSaveRegister
    * \sAdmin::validateRegistrationFields
    * \sAdmin::assignCustomerNumber
    * \sAdmin::logRegistrationMailException
* Removed the following events:
    * Shopware_Modules_Admin_SaveRegisterMainData_FilterSql
    * Shopware_Modules_Admin_SaveRegisterMainData_Return
    * Shopware_Modules_Admin_SaveRegisterMainData_Return
    * Shopware_Modules_Admin_SaveRegisterBilling_FilterSql
    * Shopware_Modules_Admin_SaveRegisterBilling_Return
    * Shopware_Modules_Admin_SaveRegisterShipping_FilterSql
    * Shopware_Modules_Admin_SaveRegisterShipping_Return
    * Shopware_Modules_Admin_SaveRegister_Start
    * Shopware_Modules_Admin_SaveRegister_GetCustomerNumber
    * Shopware_Modules_Admin_SaveRegister_FilterNeededFields
    * Shopware_Modules_Admin_SaveRegister_FilterErrors
* Shopware_Modules_Admin_SaveRegister_Successful contains no more subject
* Changed following registration templates
    * frontend/register/index.tpl
    * frontend/register/shipping_fieldset.tpl
    * frontend/register/personal_fieldset.tpl
    * frontend/register/error_messages.tpl
    * frontend/register/billing_fieldset.tpl
* Moved s_user_billingaddress.customernumber to s_user table
* Removed \Shopware\Models\Customer\Billing::number property
* Removed method `Shopware\Bundle\PluginInstallerBundle\Service\InstallerService::getPluginBootstrap()`
* Changed Shopware\Components\Model\ModelManager::addAttribute() to allow using empty string and boolean as default value when adding attribute fields
* Removed s_categories.noviewselect
* Removed \Shopware\Models\Category\Category::$noViewSelect
* Removed \Shopware\Bundle\StoreFrontBundle\Struct\Category::$allowViewSelect
* Include the departments, salutations, cities and countries in the address comparison of the backend order details
* Display the departments in the backend order details overview
* Added new API resource 'Country' and respective REST API controller 'countries'
* Renamed input fields in `themes/Frontend/Bare/frontend/account/reset_password.tpl` with surrounding `password[]`
* Removed method `\Shopware_Controllers_Frontend_Account::validatePasswordResetForm()`
* Removed method `\Shopware_Controllers_Frontend_Account::resetPassword()`
* Added new shopping world type `rows` which is based on single rows.
* Changed structure of `billing` and `shipping` to `\Shopware\Models\Customer\Address` in `\Shopware\Components\Api\Resource\Customer`
* Replaced `buttons` with a toolbar in `dockedItems` in `Shopware.apps.Order.view.detail.Detail`
* Removed method `createButtons()` in `Shopware.apps.Order.view.detail.Detail`
* Fixed \Shopware\Bundle\SearchBundleES\ConditionHandler\VoteAverageConditionHandler vote average value
* Fixed \Shopware\Bundle\SearchBundleES\SortingHandler\ReleaseDateSortingHandler field usage
* Fixed \Shopware\Bundle\SearchBundleES\ConditionHandler\ProductAttributeConditionHandler null value handling, not null handling and string operations.
* Added attributes.core mapping in \Shopware\Bundle\ESIndexingBundle\Product\ProductMapping
* Fixed attribute assignment in \Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch
* Added all source values as attributes of each product in \Shopware\Bundle\SearchBundleES\ProductNumberSearch
* Added unified product slider template
    * Created template files
        * `themes/Frontend/Bare/frontend/_includes/product_slider.tpl`
        * `themes/Frontend/Bare/frontend/_includes/product_slider_item.tpl`
        * `themes/Frontend/Bare/frontend/_includes/product_slider_items.tpl`
    * Created template blocks
        * `frontend_common_product_slider_config`
        * `frontend_common_product_slider_component`
        * `frontend_common_product_slider_container`
        * `frontend_common_product_slider_items`
        * `frontend_common_product_slider_item_config`
        * `frontend_common_product_slider_item`
    * Removed template blocks
        * `checkout_ajax_add_cross_slider_item`
        * `frontend_detail_index_streams_slider_container`
        * `frontend_detail_index_similar_slider_item`
        * `widget_emotion_component_product_slider`
        * `widgets_listing_top_seller_slider_container`
        * `widgets_listing_top_seller_slider_container_inner`
        * `widgets_listing_top_seller_slider_container_include`
        * `frontend_detail_index_also_bought_slider_inner`
        * `frontend_detail_index_similar_viewed_slider_inner`
        * `frontend_widgets_slide_articles_item`
    * Removed template files
        * `themes/Frontend/Bare/widgets/emotion/slide_articles.tpl`
* Removed customer options in import export module which results in the removal of
    * Properties
        * `\Shopware_Controllers_Backend_ImportExport::$customerRepository`
    * Methods
        * `\Shopware_Controllers_Backend_ImportExport::getCustomerRepository()`
        * `\Shopware_Controllers_Backend_ImportExport::exportCustomersAction()`
        * `\Shopware_Controllers_Backend_ImportExport::importCustomers()`
        * `\Shopware_Controllers_Backend_ImportExport::saveCustomer()`
        * `\Shopware_Controllers_Backend_ImportExport::prepareCustomerData()`
* Removed unused controller endpoints `ajax_login` and `ajax_logout` in `themes/Frontend/Bare/frontend/index/index.tpl`
* \Shopware\Bundle\SearchBundleES\ConditionHandler\ProductAttributeConditionHandler requires now the \Shopware\Bundle\AttributeBundle\Service\CrudService as constructor dependency
* Merged \Shopware\Bundle\AttributeBundle\Service\CrudService create and update function
* Removed `$basket` from `sAdmin::sManageRisks($paymentID, $basket, $user)`
* Added new `\Shopware\Bundle\StoreFrontBundle\Service\VariantCoverServiceInterface` which allows to load variant covers without considering `forceMainImageInListing` parameter
* Removed wrong parameter usage of `Shopware\Models\Menu\Repository::findOneBy`, which allows to provide two strings as criteria instead of array.
* Updated composer dependency elasticsearch/elasticsearch to version 2.2.0
* Changed default labelWidth for emotion component fields in `Shopware.apps.Emotion.view.components.Base` to 170 pixels
* IonCube Loader version requirement bumped to 5.0 or higher
* PHP setting `display_errors` defaults to `off` now in `engine/Shopware/Configs/Default.php`
* Removed `\Shopware\Bundle\StoreFrontBundle\Struct\Context` class
* Deprecated following classes and functions:
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::getContext`
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::getProductContext`
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::getLocationContext`
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::initializeContext`
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::initializeLocationContext`
    * `\Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface::initializeProductContext`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContext`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\ProductContext`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContextInterface`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\ProductContextInterface`
* Added support for loading a new store instance by ID in the config combo box `Shopware.apps.Config.view.element.Select`
* Added attributes to interface `Enlight_Controller_Request_Request`. New methods:
    * `Enlight_Controller_Request_Request::getAttributes()`
    * `Enlight_Controller_Request_Request::getAttribute()`
    * `Enlight_Controller_Request_Request::setAttribute()`
    * `Enlight_Controller_Request_Request::unsetAttribute()`
* Fixed tax free for company configuration. If the delivery country contains the flag `taxfree_ustid`, the vat id of the shipping address is checked.
