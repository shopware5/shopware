# CHANGELOG for Shopware 5.4.x

This changelog references changes done in Shopware 5.4 patch versions.

[View all changes from v5.3.7...v5.4.0](https://github.com/shopware/shopware/compare/v5.3.7...v5.4.0)

### Additions

* Added database field `s_articles_details.laststock` to be able to define per variant if said variant is available when the stock is lower or equal to 0
* Added `lastStock` field to `\Shopware\Models\Article\Detail`
* Added product box layout selection support for manufacturer listings
* Added destroy method to `swJumpToTab` jQuery plugin
* Added option to discard Less/Javascript files of extended themes (more information: https://developers.shopware.com/designers-guide/theme-startup-guide/#theme.php)
* Added multi-select feature when assigning variant configurations to product images
* Added variant configuration information in the image information panel
* Added DIC parameters:
    - `shopware.release.version`
        The version of the Shopware installation (e.g. '5.4.0')
    - `shopware.release.version_text`
        The version_text of the Shopware installation (e.g. 'RC1')
    - `shopware.release.revision`
        The revision of the Shopware installation (e.g. '20180081547')
* Added new service in the DIC containing all parameters above 
    - `shopware.release`
        A new struct of type `\Shopware\Components\ShopwareReleaseStruct` containing all parameters above
* Added new SEO routes
    - `sViewport=register`:
        `/anmeldung` (DE) and `/signup` (EN)
    - `sViewport=checkout&sAction=cart`:
        `/warenkorb` (DE) and `/basket` (EN)
    - `sViewport=checkout&sAction=confirm`:
        `/bestellen` (DE) and `/order` (EN)
    - `sViewport=checkout&sAction=shippingPayment`:
        `/zahlungsart-und-versand` (DE) and `/payment-and-delivery` (EN)
    - `sViewport=checkout`:
        `/pruefen-und-bestellen` (DE) and `/check-and-order` (EN)
    - `sViewport=checkout&sAction=finish`:
        `/vielen-dank-fuer-ihre-bestellung` (DE) and `/thank-you-for-your-order` (EN)

* Added several paths to the DIC:
	- `shopware.plugin_directories.projectplugins` 
		Path to project specific plugins, see [Composer project](https://github.com/shopware/composer-project)
	- `shopware.template.templatedir`
		Path to the themes folder
	- `shopware.app.rootdir`
		Path to the root of your project
	- `shopware.app.downloadsdir`
		Path to the downloads folder
	- `shopware.app.documentsdir`
		Path to the generated documents folder
	- `shopware.web.webdir`
		Path to the web folder
	- `shopware.web.cachedir`
		Path to the web-cache folder 
	
	These paths are configurable in the `config.php`, see `engine/Shopware/Configs/Default.php` for defaults

### Changes

* Updated mPDF to v6.1.4 and included it via composer
* Made the event selectors configurable in the `swJumpToTab` jQuery plugin
* `\Shopware\Bundle\SearchBundle\ProductSearchResult::__construct` requires now the used Criteria and ShopContext object
* Changed route to POST to be more HTTP compliant
* Changed all writing actions to POST to be more HTTP compliant.
    * Checkout actions:
        - `finish`
    
    * Basket actions
        - `addArticle`
        - `addAccessories`
        - `addPremium`
        - `changeQuantity`
        - `deleteArticle`
        - `setAddress`
        - `ajaxAddArticle`
        - `ajaxAddArticleCart`
        - `ajaxDeleteArticle`
        - `ajaxDeleteArticleCart`
        
* Changed JSONP requests to JSON in the following Frontend controllers:
    * Controller List
        - Frontend/AjaxSearch.php
        - Frontend/Checkout.php
        - Frontend/Compare.php
        - Frontend/Note.php
        - Widgets/Listing.php

### Removals

* Removed config option for maximum number of category pages
* Removed "Force http canonical url" setting in basic settings as it is obsolete
* Removed config option `template_security['enabled']` for toggling smarty security
* Removed config option `blogcategory` and `bloglimit`
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

### Deprecations

* Deprecated `forceSecure` and `sUseSSL` smarty flags. They are now without function.
* Deprecated constants `Shopware::VERSION`, `Shopware::VERSION_TEXT` and `Shopware::REVISION`, they will be removed in Shopware v5.5. This information can now be retrieved from the DIC.
    * New, alternative DIC parameters:
        - `shopware.release.version`
            The version of the Shopware installation (e.g. '5.4.0')
        - `shopware.release.version_text`
            The version_text of the Shopware installation (e.g. 'RC1')
        - `shopware.release.revision`
            The revision of the Shopware installation (e.g. '20180081547')
    * New, alternative DIC service:
        - `shopware.release`
            A new struct of type `\Shopware\Components\ShopwareReleaseStruct` containing all parameters above 
* Deprecated `lastStock` field in `\Shopware\Models\Article\Article` as the field has been moved to the variants. It will be removed in 6.0.
* Deprecated `laststock` column in `s_articles` since this field has been moved to the variants. It will be removed in 6.0
* Deprecated `articleId` column in `s_articles_attributes` table, it will be removed in Shopware version 5.5 as it isn't used anymore since version 5.2
* Deprecated SEO support for the following AJAX routes, see `themes/Frontend/Bare/frontend/index/index.tpl`:
    - `/checkout/ajaxCart`
    - `/register/index`
    - `/checkout/addArticle`
    - `/widgets/Listing/ajaxListing`
    - `/checkout/ajaxAmount`
    - `/address/ajaxSelection`
    - `/address/ajaxEditor`
* Deprecated `\Shopware\Models\Order\Document\Type`, use `\Shopware\Models\Document\Document` instead. The old document type will be removed with 5.5.
