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

### Changes

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

* Changed `Shopware\Components\Plugin\CachedConfigReader` to cache into `Zend_Cache_Core`
* Changed the manufacturer image to appropriate thumbnails
* Changed `plugin.xsd` to make pluginName in `requiredPlugins` required

### Removals

* Removed `s_articles_attributes`.`articleID` which was not set for new article variants anymore since Shopware 5.2.0
* Removed `Shopware\Bundle\ESIndexingBundle\DependencyInjection\Factory\CompositeSynchronizerFactory.php`
* Removed `Shopware\Bundle\ESIndexingBundle\CompositeSynchronizer.php
* Removed following classes, use `Shopware\Components\Plugin\XmlReader\*` instead
    * `Shopware\Components\Plugin\XmlPluginInfoReader`
    * `Shopware\Components\Plugin\XmlConfigDefinitionReader`
    * `Shopware\Components\Plugin\XmlCronjobReader`
    * `Shopware\Components\Plugin\XmlMenuReader`
* Removed `storeType` `php` from Plugin config.xml`

### Deprecations

* Deprecated `Shopware\Bundle\ESIndexingBundle::getNotAnalyzedField`. It will be removed in 5.7, use the getKeywordField instead.
* Deprecated `Shopware\Bundle\ESIndexingBundle::getAttributeRawField`. It will be removed in 5.7, use the getKeywordField instead.


### Controller Registration using DI-Tag

Controllers can be now registered using di tag ``shopware.controller``. This di tag needs attributes `module` and `controller`. These controllers are also lazy-loaded and should extend from `Shopware\Components\Controller`.

Example:

DI:

```xml
<service id="swag_example.controller.frontend.test" class="SwagExample\Controller\Frontend\Test">
    <argument type="service" id="dbal_connection"/>
    <tag name="shopware.controller" module="frontend" controller="test"/>
</service>
```

Controller:

```php
<?php

namespace SwagExample\Controller\Frontend;

use Doctrine\DBAL\Connection;
use Shopware\Components\Controller;

class Test extends Controller
{
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct();
        $this->connection = $connection;
    }

    public function indexAction()
    {
        // Do something with $this->connection
    }
}
```
