# CHANGELOG for Shopware 5.4.x

This changelog references changes done in Shopware 5.4 patch versions.

[View all changes from v5.3.2...v5.4.0](https://github.com/shopware/shopware/compare/v5.3.2...v5.4.0)

### Additions

* Added product box layout selection support for manufacturer listings

### Changes

* Updated mPDF to v6.1.4 and included it via composer

### Removals

* Removed config option for maximum number of category pages
* Removed config option `template_security['enabled']` for toggling smarty security
* Removed support for separate SSL host and SSL path. Also the `Use SSL` and `Always SSL` options were merged.
    * Removed database fields
        - `s_core_shops.secure_host`
        - `s_core_shops.secure_base_path`
        - `s_core_shops.always_secure`
        
    * Removed methods
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::setSecureHost`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::getSecureHost`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::setSecurePath`
        - `\Shopware\Bundle\StoreFrontBundle\Struct\Shop::getSecurePath`
        - `\Shopware\Components\Routing\Context::getSecureHost`
        - `\Shopware\Components\Routing\Context::setSecureHost`
        - `\Shopware\Components\Routing\Context::getSecureBaseUrl`
        - `\Shopware\Components\Routing\Context::setSecureBaseUrl`
        - `\Shopware\Components\Routing\Context::isAlwaysSecure`
        - `\Shopware\Components\Routing\Context::setAlwaysSecure`
        - `\Shopware\Models\Shop\Shop::getSecureHost`
        - `\Shopware\Models\Shop\Shop::setSecureHost`
        - `\Shopware\Models\Shop\Shop::getSecureBasePath`
        - `\Shopware\Models\Shop\Shop::setSecureBasePath`
        - `\Shopware\Models\Shop\Shop::getSecureBaseUrl`
        - `\Shopware\Models\Shop\Shop::setSecureBaseUrl`
        - `\Shopware\Models\Shop\Shop::getAlwaysSecure`
        - `\Shopware\Models\Shop\Shop::setAlwaysSecure`

    * Changed methods
        - `\Shopware\Components\Theme\PathResolver::formatPathToUrl`
           The method signature no longer contains the `isSecureRequest` parameter
        
    * Deprecated `forceSecure` and `sUseSSL` smarty flags
* Removed config option `blogcategory` and `bloglimit`

### Deprecations

* Deprecated `forceSecure` and `sUseSSL` smarty flags
