# CHANGELOG for Shopware 5.5.x

This changelog references changes done in Shopware 5.5 patch versions.

[View all changes from v5.4.5...v5.5.0](https://github.com/shopware/shopware/compare/v5.4.5...v5.5.0)

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
* Added payment and dispatch translation for order status mails
* Added ability to translate shop pages. Please rename the key of the old groups ("gLeft", "gBottom" etc.) to "left", "bottom", "bottom2", "disabled" and translate the pages.
* Added snippets for locales in backend menus
* Added implementation of elasticsearch backend
    * Added EsBackend bundle to index and search products, customers and orders for the backend
    * Added new searcher, reader and repositories to AttributeBundle for implementing elasticsearch backend
        * `engine/Shopware/Bundle/AttributeBundle/Repository`
            * `Reader/OrderReader.php`
            * `Searcher/OrderSearcher.php`
            * `CustomerRepository.php`
            * `OrderRepository.php`
    * Added indexing and backlog sync command
        * `engine/Shopware/Bundle/EsBackend/Commands/IndexPopulateCommand.php`
        * `engine/Shopware/Bundle/EsBackend/Commands/SyncBacklogCommand.php`
    * Added new templates to `themes/Backend/ExtJs/backend/search/` to split the `index.tpl`
        * `articles.tpl`
        * `customers.tpl`
        * `orders.tpl`
    * Added new config parameters `write_backlog`, `enabled` and `backend` to `es` parameter
* Added ability to translate shop forms
* Added attributes to shop forms page
* Added hreflang support to translated pages
    * Added configuration to "Seo / Router" to disable href-lang or show only language instead of language and locale.
* Added new column `changed` with `DEFAULT NULL` to tables `s_order` and `s_user`
* Added checks for changes on products, customers and orders in backend while a user saves them to prevent an overwriting of changes made by someone else
* Added proportional calculation of tax positions
    * New configuration option in Basic Settings => Checkout, "Proportional calculation of tax positions", inactive by default
    * Added ``Shopware\Components\Cart\ProportionalTaxCalculator`` to calculate proportional taxes for the cart items
    * Added ``Shopware\Components\Cart\BasketHelper`` to to add items to the cart that need to be calculation in a proportional way
    * Added ``Shopware\Components\Cart\ProportionalCartMerger`` to merge proportional cart items into one cart item
    * For the proportional tax calculation to work with vouchers and modes of dispatch, be sure to set the mode of tax calculation to "auto detection" in their settings
    * Added new filter event to modify proportional vouchers ``Shopware_Modules_Basket_AddVoucher_VoucherPrices``
* Added new column ``invoice_shipping_tax_rate`` to s_order, to save exact dispatch shipping tax rate
* Added `Shopware\Components\DependencyInjection\LegacyPhpDumper` to support old container events such as ``Enlight_Bootstrap_InitResource_``
* Added new `sqli` privilege to product feed to restrict access on custom filters
* Added MySQL 8.0 support

### Changes

* Changed the execution model of `replace` hooks to prevent multiple calls of the hooked method, if more than one `replace` hook on the same method exists and all of them call `executeParent()` once
* Changed Symfony version to 3.4.11
* Changed jQuery version to 3.3.1
* Changed Slugify version to 3.1
* Changed the event `Shopware_Form_Builder` so that the `reference` contains the `BlockPrefix` of the Formtype, not the name
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
* Changed rebates and vouchers to also show their icon in the cart for values >1 €
* Changed basic settings option `Extended SQL query`, so users now need the `sql_rule` permission of `shipping` to edit it.
* Changed `s_order_documents` column `ID` to `id`
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
    
### Removals

* Removed tables `s_user_billingaddress_attributes` and `s_user_shippingaddress_attributes`
* Removed class `Shopware\Bundle\EmotionBundle\ComponentHandler\EventComponentHandler`
* Removed methods `getByCategory` and `getListByCategory` of interface `Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface`
* Removed `sSelfCanonical` of `Shopware\Components\Compatibility\LegacyStructConverter`
* Removed method `getSourceSet` of `Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail`
* Removed class `Shopware_Components_Convert_Csv`, `Shopware_Components_Convert_Excel` and `Shopware_Components_Convert_Xml`
* Removed methods `addAttribute` and `removeAttribute` of `Shopware\Components\Model\ModelManager`
* Removed methods `setParameters` and `addParameters` of `Shopware\Components\Model\QueryBuilder`
* Removed method `assertVersionGreaterThen` of `Shopware_Components_Plugin_Bootstrap`
* Removed method `getSnippet` of `Shopware_Components_Snippet_Manager`
* Removed class `Shopware\Models\Config\Element\Shopware_Components_Form`
* Removed method `confirmAction` of `Shopware_Controllers_Frontend_Newsletter`
* Removed methods `indexAction`, `listingAction`, `requestAction`, `detailAction`, `directAction`, `commitForm` of `Shopware_Controllers_Frontend_Ticket`
* Removed methods `emotionTopSellerAction` and `emotionNewcomerAction` of `Shopware_Controllers_Widgets_Emotion`
* Removed method `ajaxListingAction` of `Shopware_Controllers_Widgets_Listing`
* Removed method `sGetAffectedSuppliers` of `sArticles`
* Removed variables `$sLanguage` and `sMultishop` with method `sGetMultishop` of `sExport`
* Removed variables `o_attr_1`, `o_attr_2`, `o_attr_3`, `o_attr_4`, `o_attr_5`, `o_attr_6` of `sOrder`
* Removed method `sCreateRewriteTableSuppliers` of `sRewriteTable`
* Removed method `save` of `Shopware\Models\Config\Form`
* Removed class `Shopware\Models\Customer\Billing` and `Shopware\Models\Customer\BillingRepository`
* Removed variables `billing` and `shipping` with methods `getShipping`, `setShipping`, `getBilling`, `setBilling` of `Shopware\Models\Customer\Customer`
* Removed class `Shopware\Models\Customer\Shipping`
* Removed methods `getCategoryEmotionsQuery` and `getCategoryEmotionsQueryBuilder` of `Shopware\Models\Emotion\Repository`
* Removed method `onUpdate` of `Shopware\Models\Media\Settings`
* Removed class `Shopware\Models\Order\Document\Type`
* Removed constant `PAYMENT_STATE_THE_PAYMENT_HAS_BEEN_ORDERED_BY_HANSEATIC_BANK` of `Shopware\Models\Order\Status`
* Removed variable `description` with methods `setDescription` and `getDescription` of `Shopware\Models\Order\Status`
* Removed methods of `Shopware\Models\Payment\Payment`:
    * `setClass`
    * `getClass`
    * `setTable`
    * `getTable`
    * `setEmbedIFrame`
    * `getEmbedIFrame`
* Removed methods `getPaymentsQuery`, `getPaymentsQueryBuilder` of `Shopware\Models\Payment\Repository`
* Removed variable `label` and methods `getLabel` and `setLabel` of `Shopware\Models\Widget\Widget`
* Removed snippets `table/s_user_billingaddress_attributes` and `table/s_user_shippingaddress_attributes`
* Removed variable `preLoadStoredEntry` of `Shopware.form.field.PagingComboBox`
* Removed smarty block `frontend_blog_detail_comments`
* Removed smarty block `frontend_detail_data_block_prices_headline`
* Removed smarty block `frontend_detail_buy_variant`
* Removed smarty block `frontend_detail_data_price_info`
* Removed smarty block `frontend_detail_data_liveshopping`
* Removed smarty block `frontend_index_header_css_ie`
* Removed smarty block `frontend_index_ajax_seo_optimized`
* Removed smarty block `frontend_index_categories_left_ul`
* Removed smarty block `frontend_listing_box_article_actions_more`
* Removed smarty block `frontend_listing_box_article_actions_inline`
* Removed ExtJs models `Shopware.apps.Customer.model.Billing` and `Shopware.apps.Customer.model.Shipping`
* Removed following unnecessary CompilerPasses due Symfony `tagged` tag:
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
    * `Shopware\Bundle\EsBackend\Subscriber\ServiceSubscriber`
    * `Shopware\Bundle\ESIndexingBundle\Subscriber\ServiceSubscriber`

### Deprecations

* Deprecated `lastStock` field in `\Shopware\Models\Article\Article` as the field has been moved to the variants. It will be removed in 5.6
* Deprecated `laststock` column in `s_articles` since this field has been moved to the variants. It will be removed in 5.6
* Deprecated the translation workaround ("gLeft", "gBottom", "eLeft", "eBottom", etc.) for shop page groups. Please rename the key of the old groups ("gLeft", "gBottom" etc.) to "left", "bottom", "bottom2", "disabled" and translate the pages.

### Elasticsearch in backend

To activate elasticsearch in backend you have to enable the `es => backend => enabled` parameter in the `config.php` and start a indexation
of the backend entities with `sw:es:backend:index:populate`.
