# CHANGELOG for Shopware Next

This changelog references changes done in Shopware Next patch versions.

## Changes
   
* Changed `BackendSession` service name to `backend_session`

## Removals

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
        - `\Shopware_Controllers_Widgets_Listing::tagCloudAction`
        - `\sMarketing::sBuildTagCloud`

    * Removed plugins
        - `TagCloud`
        
    * Removed blocks
        - `frontend_listing_index_tagcloud` in file `themes/Frontend/Bare/frontend/listing/index.tpl`
        - `frontend_home_index_tagcloud` in file `themes/Frontend/Bare/frontend/home/index.tpl`

    * Deprecated `forceSecure` and `sUseSSL` smarty flags
    
* Removed Shopware_Plugins_Backend_Auth_Bootstrap 
    * Implementation moved to \Shopware\Components\Auth\BackendAuthSubscriber
 
* Removed `s_core_engine_elements` and `Shopware\Models\Article\Element` 