# CHANGELOG for Shopware 5.6.x

This changelog references changes done in Shopware 5.6 patch versions.

[View all changes from v5.5.2...v5.6.0](https://github.com/shopware/shopware/compare/v5.5.2...v5.6.0)

### Additions


### Changes

* Changed id of login password form in `frontend/account/login.tpl` from `passwort` to `password` 
* Changed the following cart actions to redirect the request to allow customers to press reload:
    `\Shopware_Controllers_Frontend_Checkout::addArticleAction`
    `\Shopware_Controllers_Frontend_Checkout::addAccessoriesAction`
    `\Shopware_Controllers_Frontend_Checkout::deleteArticleAction`

* Changed browser cache handling in backend to cache javascript `index` and `load` actions. Caching will be disabled when
  * the template cache is disabled
  * `$this->Response()->setHeader('Cache-Control', 'private', true);` is used in the controller

### Removals

* Removed `s_articles_attributes`.`articleID` which was not set for new article variants anymore since Shopware 5.2.0

### Deprecations


