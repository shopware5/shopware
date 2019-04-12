# CHANGELOG for Shopware 5.6.x

This changelog references changes done in Shopware 5.6 patch versions.

[View all changes from v5.5.10...v5.6.0](https://github.com/shopware/shopware/compare/v5.5.10...v5.6.0)

### Additions

* Added support for Shopping Worlds without AJAX requests, configurable in theme settings
* Added configuration to define the format of a valid order number. See [Custom validation of order numbers (SKU)](###Custom validation of order numbers (SKU)) for more details
* Added controller registration by DI-tag. See Controller See [Controller Registration using DI-Tag](###Controller Registration using DI-Tag) for more details
* Added autowiring of controller action parameters. See [Autowire of controller actions parameters](###Autowire of controller actions parameters) for more details* Added better ExtJS file auto-loading. See [Improved ExtJS auto-loading](###Improved ExtJS auto-loading) for more details
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
    * `shopware_mail.log_entry_builder`
    * `shopware_mail.log_entry_mail_builder`
    * `shopware_mail.log_service`
    * `shopware_mail.filter.administrative_mail_filter`
    * `shopware_mail.filter.newsletter_mail_filter`
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
* Added command `sw:es:backend:backlog:clear` to clear the backlog
* Added alias command `sw:es:backend:backlog:sync` to `sw:es:backend:sync`
* Added paging to more lists in the backend

### Changes

* Changed minimum required PHP version to 7.2.0
* Changed minimum required MySQL version to 5.7.0
* Changed doctrine/orm to 2.6.3
* Changed mpdf/mpdf to 7.1.9
* Changed elasticsearch/elasticsearch to 5.4.0
* Changed ongr/elasticsearch-dsl to 5.0.6
* Changed jQuery to 3.4.1
* Changed id of login password form in `frontend/account/login.tpl` from `passwort` to `password`
* Changed the generation of the Robots.txt. See [Improved Robots.txt](###Improved Robots.txt) for more details
* Changed plugin initialization to alphabetical by default
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
* Changed following tables 
    * `s_core_customergroups`
    * `s_article_configurator_template_prices`
    * `s_articles_prices`
    * `s_campaigns_mailings` to varchar limit of 15 for customer group key
* Changed backend customer login to start with a fresh session
* Changed `Shopware_Controllers_Backend_Application` to be an abstract class
* Changed `sExport::sGetArticleCategoryPath` to allow various attributes in category path
* Changed `Shopware_Controllers_Backend_Application` to be abstract
* Changed `Ext.ClassManager` to show better error messages on missing alias or class
* Changed `Shopware_Controllers_Backend_Application` to abstract
* Changed `Shopware_Controllers_Backend_ExtJs` to abstract
* Changed minimum Elasticsearch version to 6.0.0
* Changed internal validation of `Shopware\Bundle\StoreFrontBundle\Struct\Attribute`
* Changed the blog seo meta details to be saveable after being over the max length of the global max length
* Changed shipping calculation in off canvas to work correctly with country states
* Changed the input of filters in the backend to prevent grammar error 
* Changed `Shopware\Bundle\ESIndexingBundle\ShopIndexerInterface::index`. Added optional `$indexNames` argument
* Changed Bare Template to improve accessibility
* Changed product module split view mode to work correctly with properties
* Changed product search to work correctly on MySQL 8
* Changed last seen products productLimit to work correctly
* Changed product-feed export to refresh context while exporting multiple feeds
* Changed `Shopware\Models\Shop\Shop::registerResources` to extract the code in a new service `shopware.components.shop_registration_service`
* Changed the offcanvas basket to now display shipping costs also when the user is logged in
* Changed `\Shopware\Components\Privacy\PrivacyService` to run more efficiently
* Changed the error message if a customer enters an invalid birthday in the registration
* Changed article list in elasticsearch to consier show variants option
* Changed Hook generation to work correctly with `void` return type

### Removals

* Removed `s_articles_attributes`.`articleID` which was not set for new article variants anymore since Shopware 5.2.0
* Removed `Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\CompositeSynchronizerFactory`
* Removed `Shopware\Bundle\ESIndexingBundle\CompositeSynchronizer`
* Removed global `$Shopware` template variable
* Removed following classes, use `Shopware\Components\Plugin\XmlReader\*` instead
    * `Shopware\Components\Plugin\XmlPluginInfoReader`
    * `Shopware\Components\Plugin\XmlConfigDefinitionReader`
    * `Shopware\Components\Plugin\XmlCronjobReader`
    * `Shopware\Components\Plugin\XmlMenuReader`
* Removed `storeType` `php` from Plugin config.xml
* Removed the unspecific request params assignment to view in `Shopware_Controllers_Widgets_Listing::productsAction` and `Shopware_Controllers_Widgets_Listing::streamAction`. Use a *PostDispatchEvent to assign necessary variables in a plugin
* Removed voucher field from additional feature
* Removed following classes without replacement
    * `Shopware\Bundle\FormBundle\Extension\EnlightRequestExtension`
    * `Shopware\Bundle\FormBundle\EnlightRequestHandler`
* Removed checkbox show in all categories on the category filter detail page
* Removed category filter facet from filter listing in category settings 
* Removed category filter from category page in frontend
* Removed deprecated `Shopware_Controllers_Backend_Deprecated`
* Removed deprecated `Shopware` constants
    * Removed `Shopware::VERSION` use the DIC-Parameter `shopware.release.version` instead
    * Removed `Shopware::VERSION_TEXT` use the DIC-Parameter `shopware.release.version_text` instead
    * Removed `Shopware::REVISION` use the DIC-Parameter `shopwqare.release.REVISION` instead
* Removed deprecated `Kernel` constants
    * Removed `Kernel::VERSION` use the DIC-Parameter `shopware.release.version` instead
    * Removed `Kernel::VERSION_TEXT` use the DIC-Parameter `shopware.release.version_text` instead
    * Removed `Kernel::REVISION` use the DIC-Parameter `shopware.release.revision` instead
* Removed deprecated `Shopware_Controllers_Frontend_SitemapMobileXml`
* Removed deprecated `Shopware\Components\SitemapXMLRepository`
* Removed deprecated `$legacyGroups` in `Shopware\Components\SitemapXMLRepository`
* Removed deprecated older `Shopware\Models\Order\Document\Document`
* Removed deprecations of `Shopware\Components\Api\Resource\Article`
* Removed deprecations of `Shopware\Components\Api\Resource\Variant`
* Removed deprecated `Shopware_Components_Benchmark_Point`
* Removed deprecated `Shopware_Components_Benchmark_Container`
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
* Deprecated `Shopware\Models\Shop\Shop::registerResources`. It will be removed in 5.8, use `Shopware\Components\ShopRegistrationService` instead

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
<service id="swag_example.controller.frontend.test" class="SwagExample\Controller\Frontend\Test">
    <argument type="service" id="dbal_connection"/>
    <tag name="shopware.controller" module="frontend" controller="test"/>
</service>
```

##### Controller:

```php
<?php

namespace SwagExample\Controller\Frontend;

use Doctrine\DBAL\Connection;

class Test extends \Enlight_Controller_Action
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

* Services (e.g ListProductService $listProductService)
* $request (e.g Request $request)
* $requestParameters (e.g int $limit = 0  /action?limit=5)

### Enlight_Controller_Request_RequestHttp is now extending Symfony\Component\HttpFoundation\Request and Enlight_Controller_Response_ResponseHttp extends Symfony\Component\HttpFoundation\Response

The request and response instances in Shopware now extend from Symfony Request / Response.

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

If you are running a MariaDB database, you should prefix the `serverVersion` with `mariadb`- (e.g.: `mariadb-10.2.12`).

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
        $token = $this->get('shopware.components.cart.payment_token')->generate();
        
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
* table with s_custom prefix
* repository serivce with shopware.bundle.content_type.**type_name**

They are also accessible in template using new smarty function `fetchContent`

Example
```html
{fetchContent type=store assign=stores filter=[['property' => 'country', 'value' => 'Germany']]}

{foreach $stores as $store}
    {$store.name}
{/foreach}
```

The backend fields and titles can be translated using snippet namespace ``backend/customYOURTYPE/main``.
