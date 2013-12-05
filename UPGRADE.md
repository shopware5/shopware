# Shopware Upgrade Information
In this document you will find a changelog of the important changes related to the code base of shopware.

## 4.2.0

* A userland implementaion of [`array_column()`](http://php.net/array_column) has been included.
* Deprecated class `sTicketSystem` has been removed.
* Doctrine has been updated to version 2.4. See: https://github.com/doctrine/doctrine2/blob/2.4/UPGRADE.md
* Break: `Doctrine\ORM\Query::setParamters()` has changed. See: https://github.com/doctrine/doctrine2/blob/2.4/UPGRADE.md
* `Shopware\Components\Model\QueryBuilder::setParameters()` provides old behavior.
* Break: `Shopware_Plugins_Frontend_RouterOld_Bootstrap::onAssemble` event and implementation removed
* Update Zend Framework to version 1.12.3 (latest stable)

## 4.1.4

* New method `\Shopware\Components\Model\ModelManager::createPaginator($query)`.
 * This method should be used instead of `new \Doctrine\ORM\Tools\Pagination\Paginator($query)`.
 * As of SW 4.2 `$paginator->setUseOutputWalkers(false)` will be set here.
* New methods for calculating the basepricedata have been integrated in `/engine/core/class/sArticles.php`
 * `calculateCheapestBasePriceData` | This methods returns always the basepricedata of the cheapest variant. This is used in the listing views.
 * `getCheapestVariant` | This method is used by the method `calculateCheapestBasePriceData` to get the purchaseunit and the referenceunit of the cheapest variant.
 * `calculateReferencePrice` | This method does the basic calculation to get the right referenceprice.


### Deprecations
* The subquery in `$priceForBasePrice` used in the method `sGetArticlesByCategory` of the class `/engine/core/class/sArticles.php` is marked deprecated, because the query variable `priceForBasePrice` is no longer in use. Please do not use it anymore.

## 4.1.3

* Add configuration `Always display item short descriptions in listing views`.
* `Shopware_Components_Plugin_Bootstrap::assertVersionGreaterThen()` is now an alias to  `Shopware_Components_Plugin_Bootstrap::assertMinimumVersion()` and returns always `true` if run on an development/git Version of Shopware
* Added a new method `getDefault()` in `engine/Shopware/Models/Shop/Repository.php` which returns just the default shop without calling `fixActiv()`.
* Removed the unused `downloadAction()` in `engine/Shopware/Controllers/Backend/Plugin.php`

### Deprecations
* `decompressFile()` in `/engine/Shopware/Controllers/Backend/Plugin.php`
* `decompressFile()` in `/engine/Shopware/Plugins/Default/Core/PluginManager/Controllers/Backend/PluginManager.php`

You should use the decompressFile method in the CommunityStore component instead


## 4.1.1 / 4.1.2

With Shopware 4.1.1 we have fixed a bug that appeared during certain constellations in the customer registration process.
Submitting the registration formular empty, from time to time a fatal error was displayed.

For further information have a look at the following wiki article:

- GER: <http://wiki.shopware.de/_detail_1342.html>
- ENG: <http://en.wiki.shopware.de/_detail_1398.html>
