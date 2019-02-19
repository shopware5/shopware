# CHANGELOG for Shopware 5.6.x

This changelog references changes done in Shopware 5.6 patch versions.

[View all changes from v5.5.7...v5.6.0](https://github.com/shopware/shopware/compare/v5.5.7...v5.6.0)

### Additions

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
* Added signature `supports` to `Shopware\Bundle\ESIndexingBundle\SynchronizerInterface` to reduce wrong typed backlog sync request.
    * Added method implementation to 
    `Shopware\Bundle\ESIndexingBundle\Property\PropertySynchronizer::supports`
    `Shopware\Bundle\ESIndexingBundle\Product\ProductSynchronizer::supports`
* Added configuration to define the format of a valid order number 
* Added service `\Doctrine\Common\Annotations\Reader` as `models.annotations_reader`
* Added `shopware.controller.blacklisted_controllers` parameter to the DI container to blacklist controllers for dispatching
* Added default table options of Doctrine to config
* Added better ExtJS file auto-loading. See [Improved ExtJS auto-loading](###Improved ExtJS auto-loading) for more details

### Changes

* Increased minimum required PHP version to PHP >= 7.2.0.
* Changed id of login password form in `frontend/account/login.tpl` from `passwort` to `password`
* Changed cookie `x-ua-device` to be `secure` if the shop runs on SSL
* Changed the following cart actions to redirect the request to allow customers to press reload:
    `\Shopware_Controllers_Frontend_Checkout::addArticleAction`
    `\Shopware_Controllers_Frontend_Checkout::addAccessoriesAction`
    `\Shopware_Controllers_Frontend_Checkout::deleteArticleAction`
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
* Changed Doctrine orm version to 2.6.3
* Changed mpdf to 7.1.9
* Changed `type` of `logMailAddress` config in `s_core_config_elements` to `textarea`
* Changed mail error handler to consider multiple recipient addresses

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
* Removed the unspecific request params assignment to view in `\Shopware_Controllers_Widgets_Listing::productsAction` and `\Shopware_Controllers_Widgets_Listing::streamAction`. Use a *PostDispatchEvent to assign necessary variables in a plugin.

### Deprecations

* Deprecated `Shopware\Bundle\ESIndexingBundle::getNotAnalyzedField`. It will be removed in 5.7, use the getKeywordField instead.
* Deprecated `Shopware\Bundle\ESIndexingBundle::getAttributeRawField`. It will be removed in 5.7, use the getKeywordField instead.

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

Controllers can be now registered using the DI tag `shopware.controller`. This DI tag needs attributes `module` and `controller`. These controllers are also lazy-loaded and should extend from `Shopware\Components\Controller`.

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
use Shopware\Bundle\ControllerBundle\Controller;

class Test extends Controller
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
}
```

### Custom validation of order numbers (SKU)

Up to now, the validation of order numbers (or SKUs) was done in form of a Regex-Assertion in the Doctrine model at `\Shopware\Models\Article\Detail::$number`. That solution was not flexible and didn't allow any modifications of said regex, let alone a complete custom implementation of a validation.
 
Now, a new constraint `\Shopware\Components\Model\DBAL\Constraints\OrderNumber` is used instead, which is a wrapper around `\Shopware\Components\OrderNumberValidator\RegexOrderNumberValidator`.

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
Or you can create your own implementation of the underlying interface `\Shopware\Components\OrderNumberValidator\OrderNumberValidatorInterface` and use it for the validation by simply decorating the current service with id `shopware.components.ordernumber_validator` and e.g. query some API. 

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
