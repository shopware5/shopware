# Shopware Upgrade Information
In this document you will find a changelog of the important changes related to the code base of shopware.

## 4.1.3 
* Added a new method `getDefault()` in `engine/Shopware/Models/Shop/Repository.php` which returns just the default shop                     without calling `fixActiv()`.

* Removed the unused `downloadAction()` in `engine/Shopware/Controllers/Backend/Plugin.php`

### Deprecations
* `decompressFile()` in `/engine/Shopware/Controllers/Backend/Plugin.php` 
* `decompressFile()` in `/engine/Shopware/Plugins/Default/Core/PluginManager/Controllers/Backend/PluginManager.php`

You should use the decompressFile method in the CommunityStore component instead

- - -


## 4.1.1 / 4.1.2

With Shopware 4.1.1 we have fixed a bug that appeared during certain constellations in the customer registration process. Submitting the registration formular empty, from time to time a fatal error was displayed.
For further informations have a look at the following wiki article:

GER: <http://wiki.shopware.de/_detail_1342.html>

ENG: <http://en.wiki.shopware.de/_detail_1398.html>

- - -

