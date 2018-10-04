# CHANGELOG for Shopware 5.5.x

This changelog references changes done in Shopware 5.5 patch versions.

## 5.5.2

[View all changes from v5.5.1...v5.5.2](https://github.com/shopware/shopware/compare/v5.5.1...v5.5.2)

### Changes

* Changed thumbnail variable on detail page for href-attribute from `sArticle.image.src.1` to `sArticle.image.source`
* Changed failed login behaviour by only increasing the failedlogin-count of active customer accounts, not guest accounts
* Changed product saving to handle invalid changetime dates
* Changed document template `themes/Frontend/Bare/documents/index.tpl` to also render a `department` if it is part of the address
* Changed `controllerAction` and `controllerName` Smarty functions to sanitize action and controller names
* Changed FormSynchronizer to consider sorting from config.xml
* Changed the instock column in the product variants list to be sortable

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

### Removals

* Removed trailing slash from LESS variable `@font-directory`

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
