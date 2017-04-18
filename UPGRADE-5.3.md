# CHANGELOG for Shopware 5.3.x

This changelog references changes done in Shopware 5.3 patch versions.

## 5.3.0

[View all changes from v5.2.9...v5.3.0](https://github.com/shopware/shopware/compare/v5.2.9...v5.3.0)

### Additions

* Added config element `displayOnlySubShopVotes` to display only shop assigned article votes
* Added parameter `displayProgressOnSingleDelete` to `Shopware.grid.Panel` to hide progress window on single delete action
* Added parameter `expression` in `Shopware.listing.FilterPanel` to allow define own query expressions
* Added parameter `splitFields` to `Shopware.model.Container` to configure fieldset column layout
* Added interface `Shopware\Components\Captcha\CaptchaInterface`
* Added method `Shopware\Models\Order\Repository::getList()`
* Added method `Shopware\Models\Order\Repository::search()`
* Added method `Shopware\Models\Order\Repository::getDocuments()`
* Added method `Shopware\Models\Order\Repository::getDetails()`
* Added method `Shopware\Models\Order\Repository::getPayments()`
* Added responsive helper css/less classes in `_mixins/visibility-helper.less`
* Added config element `liveMigration` to enable or disable the media live migration
* Added config element `displayListingBuyButton` to display listing buy button
* Added service `shopware_search.batch_product_search` and `shopware_search.batch_product_number_search` for optimized product queries
* Added support for callback methods and jQuery promises in `jQuery.overlay` and `jQuery.loadingIndicators`
* Added jQuery method `setLoading()` to apply a loading indicator to an element `$('selector').setLoading()`
* Added required attribute `data-facet-name` for filter elements
* Added new type for the filter panels `value-list-single`
* Added new Smarty blocks for the unified filter panel:
    * `frontend_listing_filter_facet_multi_selection`
    * `frontend_listing_filter_facet_multi_selection_flyout`
    * `frontend_listing_filter_facet_multi_selection_title`
    * `frontend_listing_filter_facet_multi_selection_icon`
    * `frontend_listing_filter_facet_multi_selection_content`
    * `frontend_listing_filter_facet_multi_selection_list`
    * `frontend_listing_filter_facet_multi_selection_option`
    * `frontend_listing_filter_facet_multi_selection_option_container`
    * `frontend_listing_filter_facet_multi_selection_input`
    * `frontend_listing_filter_facet_multi_selection_label`
* Added service `Shopware\Bundle\StoreFrontBundle\Service\Core\CategoryDepthService` to select categories by the given depth
* Added event `plugin/swListing/fetchListing` which allows to load listings, facet data or listing counts
* Added config element `listingMode` to switch listing reload behavior
* Added event `action/fetchListing` which allows to load listings, facet data or listing counts
* Added property `path` to `Shopware\Bundle\StoreFrontBundle\Struct\Media` which reflects the virtual path
* Added service `Shopware\Bundle\StoreFrontBundle\Service\Core\BlogService` to fetch blog entries by id
* Added filter event `Shopware_Core_HttpCache_CacheIdsFromController` in HttpCache to extend cache keys to be invalidated based on the controller 
* Added smarty function `convertEmotion` to convert an emotion struct to the legacy array structure
* Added method `Shopware\Bundle\MediaBundle\MediaServiceInterface::listContents()`
* Added method `Shopware\Bundle\MediaBundle\MediaService::listContents()`
* Added new template files:
    * `themes/Frontend/Bare/frontend/detail/content.tpl`
    * `themes/Frontend/Bare/frontend/detail/content/header.tpl`
    * `themes/Frontend/Bare/frontend/detail/content/buy_container.tpl`
    * `themes/Frontend/Bare/frontend/detail/content/tab_navigation.tpl`
    * `themes/Frontend/Bare/frontend/detail/content/tab_container.tpl`
* Added new option to select variants in `Shopware.apps.Emotion.view.components.Article` and `Shopware.apps.Emotion.view.components.ArticleSlider`
* Added local path to `@font-face` integration of the Open Sans font
* Added new Smarty blocks for registration:
    * `frontend_register_billing_fieldset_company_panel`
    * `frontend_register_billing_fieldset_company_title`
    * `frontend_register_billing_fieldset_company_body`
    * `frontend_register_billing_fieldset_panel`
    * `frontend_register_billing_fieldset_title`
    * `frontend_register_billing_fieldset_body`
    * `frontend_register_index_cgroup_header_title`
    * `frontend_register_index_cgroup_header_body`
    * `frontend_register_index_advantages_title`
    * `frontend_register_login_customer_title`
    * `frontend_register_personal_fieldset_panel`
    * `frontend_register_personal_fieldset_title`
    * `frontend_register_personal_fieldset_body`
    * `frontend_register_shipping_fieldset_panel`
    * `frontend_register_shipping_fieldset_title`
    * `frontend_register_shipping_fieldset_body`
* Added new global date picker component `frontend/_public/src/js/jquery.datepicker.js` to Responsive theme
* Added filter facets for date and datetime fields
    * `themes/Frontend/Bare/frontend/listing/filter/facet-date.tpl`
    * `themes/Frontend/Bare/frontend/listing/filter/facet-date-multi.tpl`
    * `themes/Frontend/Bare/frontend/listing/filter/facet-date-range.tpl`
    * `themes/Frontend/Bare/frontend/listing/filter/facet-datetime.tpl`
    * `themes/Frontend/Bare/frontend/listing/filter/facet-datetime-multi.tpl`
    * `themes/Frontend/Bare/frontend/listing/filter/facet-datetime-range.tpl`
* Added classes to `themes/Frontend/Responsive/frontend/_public/src/less/_components/filter-panel.less`
    * `.filter-panel--radio`
    * `.filter-panel--checkbox`
    * `.radio--state`
    * `.checkbox--state`
* Added new JavaScript method to register callbacks which fire after the main script was loaded asynchronously. Use `document.asyncReady()` to register your callback when using inline script.
* Added missing dependency `jquery.event.move` to the `package.json` file.

### Changes

* Changed theme path for new plugins from `/resources` into `/Resources`
* Changed sorting of `Shopware.listing.FilterPanel` fields
* Changed database column `s_articles_vote`.`answer_date` to allow `NULL` values
* Changed `LastArticle` plugin config elements `show`, `controller` and `time` to be prefixed with `lastarticles_`
* Changed product listings in shopping worlds to only be loaded if `showListing` is true
* Changed sql query in `sAdmin` queries which uses a sub query for address compatibility, following functions affected:
    * `sAdmin::sGetDispatchBasket`
    * `sAdmin::sGetPremiumDispatches`
    * `sAdmin::sGetPremiumDispatchSurcharge`
* Changed attribute type `string` mapping to mysql `TEXT` type. String and single selection data type supports no longer a sql default value.
* Changed `roundPretty` value for currency range filter
* Changed `CategoryFacet` behavior to generate each time a tree based on the system category with a configured category depth
* Changed facet templates `facet-radio`, `facet-media-list` and `facet-value-list` into one template
* Renamed parameter `data-count-ctrl` on `#filter` form to `data-listing-url`
* Changed removal version of method `Shopware\Components\Model\ModelManager::addAttribute` to 5.4
* Changed removal version of method `Shopware\Components\Model\ModelManager::removeAttribute` to 5.4
* Changed template `component_article_slider.tpl` to show provided products instead of always fetching them via ajax
* Changed emotion preview to not save the current state before showing preview 
* Changed command `sw:thumbnail:cleanup` to search the filesystem to remove orphaned thumbnails
* Moved `defaultListingSorting` configuration from performance module to base settings > `categories / listings`
* Changed the jQuery plugin `src/js/jquery.selectbox-replacement.js` to be used only as a polyfill. Use the CSS-only version for select fields instead.
* Removed template `frontend/forms/elements.tpl` to `frontend/forms/form-elements.tpl`
    * Renamed smarty block `frontend_forms_index_elements` to `frontend_forms_index_form_elements`
    * Renamed all smarty blocks `frontend_forms_elements*` to `frontend_forms_form_elements*`
* Changed template file `themes/Frontend/Bare/frontend/detail/index.tpl` to split it into separated files
* Changed the `linkDetails` property of the `sArticle` template variable. The URL also contains now the order number of the product
* Changed the product selection to variant selection in `Shopware.apps.Emotion.view.components.BannerMapping`
* Changed the integration of `modernizr.js` and added it to the compressed main JavaScript files
* Changed the script tag for the generated JavaScript file for asynchronous loading, can be changed in theme configuration
* Changed the inline script for the statistics update to vanilla JavaScript
* Changed event name in `jquery.ajax-product-navigation.js::getProductState()` from `plugin/swAjaxProductNavigation/onSetProductState` to `plugin/swAjaxProductNavigation/onGetProductState`
* Change behavior of the smarty rendering in forms fields comment.
    * Only variables that were previously assign to the view are rendered.
    * smarty function calls are no longer executed.
    * Only simple variables can be used.

    Example:

    {sElement.name} **works**

    {sElement.name|currency} **works but did not execute the currency function**

    {sElement.value[$key]|currency} **did not work**
* Change behavior of the tracking url rendering.
    * only the smarty variable **{$offerPosition.trackingcode}** is in use.
    * use now only the url of the tracking service like: https://gls-group.eu/DE/de/paketverfolgung?match={$offerPosition.trackingcode}
* Changed text colour and height of `.filter--active` in `themes/Frontend/Responsive/frontend/_public/src/less/_components/filter-panel.less`

```xml
<a href="https://gls-group.eu/DE/de/paketverfolgung?match={$offerPosition.trackingcode}" onclick="return !window.open(this.href, 'popup', 'width=500,height=600,left=20,top=20');" target="_blank">{$offerPosition.trackingcode}</a>
```

did not work anymore because the smarty rendering is off. The string {$offerPosition.trackingcode} is only a placeholder.

* Updated the following dependencies:
    * `flatpickr` 2.4.7 to 2.5.7
    * `jquery` 2.1.4 up to 2.2.4
    * `grunt` 0.4.5 up to 1.0.1
    * `grunt-contrib-clean` 0.7.0 up to 1.1.0
    * `grunt-contrib-copy` 0.8.2 up to 1.0.0

### Removals

* Removed configuration option `sCOUNTRYSHIPPING`
* Removed variable `{$sShopname}` from forms, use `{sShopname}` instead
* Removed import / export module
* Removed article vote module files
    * `themes/Backend/ExtJs/backend/vote/view/vote/detail.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/edit.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/infopanel.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/list.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/toolbar.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/window.js`
    * `themes/Backend/ExtJs/backend/vote/controller/vote.js`
    * `themes/Backend/ExtJs/backend/vote/controller/vote.js`
* Removed database column `s_emarketing_lastarticles`.`articleName`
* Removed database column `s_emarketing_lastarticles`.`img`
* Removed default plugin `LastArticle`, use `shopware.components.last_articles_subscriber` instead
* Removed session key `sLastArticle`
* Removed view variable `sLastActiveArticle` from basket
* Removed snippet `frontend/checkout/actions/CheckoutActionsLinkLast`
* Removed meta tag `fragment`
* Removed view variable `hasEscapedFragment`
* Removed method `Shopware\Models\Order\Repository::getBackendOrdersQueryBuilder()`
* Removed method `Shopware\Models\Order\Repository::getBackendOrdersQuery()`
* Removed method `Shopware\Models\Order\Repository::getBackendAdditionalOrderDataQuery()`
* Removed jQuery plugin method `showFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin method `hideFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin event `plugin/swEmotionLoader/onShowFallbackContent` in `jquery.emotion.js`
* Removed jQuery plugin event `plugin/swEmotionLoader/onHideFallbackContent`in `jquery.emotion.js`
* Removed alias support from `Enlight_Controller_Request_Request` (`getAlias`, `getAliases`, `setAlias`)
* Removed unused Zend Framework Components
    * Changes to `Zend_Json`
        * Removed `Zend_Json_Decoder`
        * Removed `Zend_Json_Encoder`
        * Removed `Zend_Json_Expr`
        * Option `enableJsonExprFinder`
        * Removed property `Zend_Json::$useBuiltinEncoderDecoder`
        * Removed property `Zend_Json::$maxRecursionDepthAllowed`
        * Removed method `Zend_Json::fromXml()`
    * Changes to `Zend_Loader`
         * Removed `Zend_Loader_Autoloader`
         * Removed `Zend_Loader_ClassMapAutoloader`
         * Removed `Zend_Loader_StandardAutoloader`
         * Removed `Zend_Loader_Autoloader_Resource`
         * Removed method `Zend_Loader::autoload()`
         * Removed method `Zend_Loader::registerAutoload()`
    * Changes to `Zend_DB` 
        * Removed unused adapters like Db2, Mysqli, Oracle, Ibm, MsSql, Oci, PgSQL, Sqlsrv
    * `Zend_Controller`
    * `Zend_Navigation`
    * `Zend_View`
    * `Zend_Form`
    * `Zend_Paginator`
    * `Zend_Crypt`
    * `Zend_Oauth`
    * `Zend_Dom`
    * `Zend_Server`
    * `Zend_Rest`
    * `Zend_Stdlib`
    * `Zend_Text`
    * `Zend_Debug`
    * `Zend_Registry`
    * `Zend_XmlRpc`
    * `Zend_Soap`
    * `Zend_Service`
    * `Zend_Filter_Compress`
    * `Zend_Filter_Decompress`
    * `Zend_Filter_Encrypt`
    * `Zend_Filter_Decrypt`
    * `Zend_Layout`
        * Also removed `Zend_Log_Writer_Mail::setLayout()` config options `layout` and `layoutFormatter` `setLayoutFormatter()` `getLayoutFormatter()`
    * `Zend_Infocard`
         * Also removed `Zend_Auth_Adapter_InfoCard`
    * `Zend_OpenId`
         * Also removed `Zend_Auth_Adapter_OpenId`
    * `Zend_TimeSync`
        * Also removed TimeSync support from `Zend_Date`
    * `Zend_ProgressBar` 
        * Also removed ProgressBar support from `Zend_File_Transfer_Adapter_Http`
    * `Zend_Ldap` 
        * Also removed `Zend_Auth_Adapter_Ldap` 
        * Also removed `Zend_Validate_Ldap_Dn`
    * `Zend_Wildfire`
        * Also removed `Zend_Db_Profiler_Firebug`
        * Also removed `Zend_Log_Formatter_Firebug`
        * Also removed `Zend_Log_Writer_Firebug`
    * `Zend_File`
        * Also removed `Zend_Filter_File_*`
        * Also removed `Zend_Validate_File_*`
* Removed method `Shopware\Components\Model\ModelManager::__call()`
* Removed class `Enlight_Bootstrap`
* Removed parameter `$checkProxy` from `Enlight_Controller_Request_Request::getClientIp()`
* Removed smarty block `frontend_search_category_filter`
* Removed template file `themes/Frontend/Bare/frontend/search/category-filter.tpl`
* Removed parameter `sCategory` from search controller `listing/ajaxCount` requests
* Removed Smarty blocks due to the unified filter panel. The following blocks were removed:
    * `frontend_listing_filter_facet_media_list_flyout`
    * `frontend_listing_filter_facet_media_list_title`
    * `frontend_listing_filter_facet_media_list_icon`
    * `frontend_listing_filter_facet_media_list_content`
    * `frontend_listing_filter_facet_media_list_list`
    * `frontend_listing_filter_facet_media_list_option`
    * `frontend_listing_filter_facet_media_list_option_container`
    * `frontend_listing_filter_facet_media_list_input`
    * `frontend_listing_filter_facet_media_list_label`
    * `frontend_listing_filter_facet_radio_flyout`
    * `frontend_listing_filter_facet_radio_title`
    * `frontend_listing_filter_facet_radio_icon`
    * `frontend_listing_filter_facet_radio_content`
    * `frontend_listing_filter_facet_radio_list`
    * `frontend_listing_filter_facet_radio_option`
    * `frontend_listing_filter_facet_radio_option_container`
    * `frontend_listing_filter_facet_radio_input`
    * `frontend_listing_filter_facet_radio_label`
    * `frontend_listing_filter_facet_value_list_flyout`
    * `frontend_listing_filter_facet_value_list_title`
    * `frontend_listing_filter_facet_value_list_icon`
    * `frontend_listing_filter_facet_value_list_content`
    * `frontend_listing_filter_facet_value_list_list`
    * `frontend_listing_filter_facet_value_list_option`
    * `frontend_listing_filter_facet_value_list_option_container`
    * `frontend_listing_filter_facet_value_list_input`
    * `frontend_listing_filter_facet_value_list_label`
* Removed field `attributes.search.cheapest_price` from DBAL search query
* Removed field `attributes.search.average` from DBAL search query
* Removed join to `s_core_tax` in `Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch`
* Removed method `Shopware_Controllers_Widgets_Emotion::getEmotion()`
* Removed method `Shopware_Controllers_Widgets_Emotion::handleElement()`, use `Shopware\Bundle\EmotionBundle\ComponentHandler\ComponentHandlerInterface` instead
* Removed method `Shopware_Controllers_Widgets_Emotion::getRandomBlogEntry()`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBlogEntry()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BlogComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getCategoryTeaser()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\CategoryTeaserComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBannerMappingLinks()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BannerComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getManufacturerSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\ManufacturerSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getBannerSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\BannerSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getArticleSlider()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\ArticleSliderComponentHandler`
* Removed method `Shopware_Controllers_Widgets_Emotion::getHtml5Video()`, has been replaced by `Shopware\Bundle\EmotionBundle\ComponentHandler\Html5VideoComponentHandler`
* Removed LESS variable `@zindex-fancy-select`
* Removed select field replacement via JavaScript
    * Removed the jQuery plugin `src/js/jquery.selectbox-replacement.js` completely
    * Removed LESS variable `@zindex-fancy-select`
* Removed smarty blocks:
    * `frontend_listing_actions_sort_field_relevance`
    * `frontend_listing_actions_sort_field_release`
    * `frontend_listing_actions_sort_field_rating`
    * `frontend_listing_actions_sort_field_price_asc`
    * `frontend_listing_actions_sort_field_price_desc`
    * `frontend_listing_actions_sort_field_name`
* Removed `\Shopware_Controllers_Backend_Performance::getListingSortingsAction`
* Removed constants of `\Shopware\Bundle\SearchBundle\CriteriaRequestHandler\CoreCriteriaRequestHandler` and `Shopware\Bundle\SearchBundle\StoreFrontCriteriaFactory`:
    * `SORTING_RELEASE_DATE`
    * `SORTING_POPULARITY`
    * `SORTING_CHEAPEST_PRICE`
    * `SORTING_HIGHEST_PRICE`
    * `SORTING_PRODUCT_NAME_ASC`
    * `SORTING_PRODUCT_NAME_DESC`
    * `SORTING_SEARCH_RANKING`
* Removed smarty modifier `rewrite`
* Removed unused `@font-face` for "extra-bold" and "light" of the Open Sans font type
* Removed scrollbar styling on filter-panels (Selector `.filter-panel--content`)
* Removed support for `.swf` file type in banner module
* Removed deprecated template block `frontend_listing_swf_banner` in `themes/Frontend/Bare/frontend/listing/banner.tpl`
* Removed the jQuery UI date picker integration for new global component
    * `themes/Responsive/frontend/_public/src/js/jquery.ui.datepicker.js`
* Removed unused styles of the Responsive theme
    * `.panel--list`
    * `.panel--arrow`
    * `.panel--tab-nav`
    * `.panel--filter-btn`
    * `.panel--filter-select`
    * `.js--mobile-tab-panel`
    * `.ribbon`
    * `.ribbon--content`
    * `.device--mobile`
    * `.device--tablet`
    * `.device--tablet-portrait`
    * `.device--tablet-landscape`
    * `.device--desktop`
* Removed `max-width` rule for `.filter--active` in `themes/Frontend/Responsive/frontend/_public/src/less/_components/filter-panel.less`
* Removed unused field `s_core_countries.shippingfree` 
* Removed `__country_shippingfree` field in `Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper::getCountryFields`
* Removed `Shopware\Bundle\StoreFrontBundle\Struct\Country::setShippingFree` and `Shopware\Bundle\StoreFrontBundle\Struct\Country::isShippingFree`
* Removed `Shopware\Models\Country\Country::$shippingFree`, `Shopware\Models\Country\Country::setShippingFree` and `Shopware\Models\Country\Country::getShippingFree`

### Deprecations

* Deprecated `Shopware_Components_Convert_Csv` without replacement, to be removed with 5.4
* Deprecated `Shopware_Components_Convert_Xml` without replacement, to be removed with 5.4
* Deprecated `Shopware_Components_Convert_Excel` without replacement, to be removed with 5.4
* Deprecated `\Shopware_Controllers_Widgets_Listing::ajaxListingAction`, use `\Shopware_Controllers_Widgets_Listing::listingCountAction` instead
* Deprecated method `sArticles::sGetAffectedSuppliers()` without replacement, to be removed with 5.5
* Deprecated `Shopware\Models\Article\Element`, to be removed with 6.0

### Backend Components

You can now define the expression for the comparison in SQL. For example `>=` like seen below:

```javascript
Ext.define('Shopware.apps.Vote.view.list.extensions.Filter', {
    extend: 'Shopware.listing.FilterPanel',
    alias:  'widget.vote-listing-filter-panel',
    configure: function() {
        return {
            controller: 'Vote',
            model: 'Shopware.apps.Vote.model.Vote',
            fields: {
                points: {
                    expression: '>=',
                }
            }
        };
    }
});
```

### Captcha

Captchas are now configurable via backend and can be added using the `captcha` dependency injection container tag.

```xml
<service id="shopware.captcha.recaptcha" class="SwagReCaptcha\ReCaptcha">
    <argument type="service" id="guzzle_http_client_factory"/>
    <argument type="service" id="config"/>
    <tag name="captcha"/>
</service>
```

For more information, please refer to our [Captcha Documentation](https://developers.shopware.com/developers-guide/implementing-your-own-captcha/).

### Select field replacement

The replacement of the select field elements via JavaScript is deprecated and will be removed in a future release. You can create a styled select field with a simple CSS-only solution by adding a wrapper element.

```
<div class="select-field">
    <select>
        <option></option>
        <option></option>
    </select>
</div>
```

### Batch Product Search

The Batch Product Search service works with request and results. You can add multiple criteria's and/or product numbers to a request and resolve them in an optimized way. An optimizer groups multiple equal criteria's into one and performs the search.

```php
$criteria = new Critera();
$criteria->addCondition(new CategoryCondition([3]));
$criteria->limit(3);

$anotherCriteria = new Critera();
$anotherCriteria->addCondition(new CategoryCondition([3]));
$anotherCriteria->limit(5);

$request = new BatchProductNumberSearchRequest();
$request->setProductNumbers('numbers-1', ['SW10004', 'SW10006']);
$request->setCriteria('criteria-1', $criteria);
$request->setCriteria('criteria-2', $anotherCriteria);

$result = $this->container->get('shopware_search.batch_product_search')->search($request, $context);

$result->get('numbers-1'); // ['SW10004' => ListProduct, 'SW10006' => ListProduct] 
$result->get('criteria-1'); // ['SW10006' => ListProduct, 'SW10007' => ListProduct, 'SW10008' => ListProduct] 
$result->get('criteria-2'); // ['SW10009' => ListProduct, 'SW10010' => ListProduct, 'SW10011' => ListProduct, 'SW10012' => ListProduct, 'SW10013' => ListProduct] 
```

### Partial facets

`\Shopware\Bundle\SearchBundleDBAL\FacetHandlerInterface` marked as deprecated and replaced by `\Shopware\Bundle\SearchBundleDBAL\PartialFacetHandlerInterface`.
Each facet handler had to revert the provided criteria by their own to remove customer conditions. This behaviour is now handled in the `\Shopware\Bundle\SearchBundleDBAL\ProductNumberSearch::createFacets`

Old implementation:
```
/**
 * @param FacetInterface $facet
 * @param Criteria $criteria
 * @param ShopContextInterface $context
 * @return BooleanFacetResult
 */
public function generateFacet(
    FacetInterface $facet,
    Criteria $criteria,
    ShopContextInterface $context
) {
    $reverted = clone $criteria;
    $reverted->resetConditions();
    $reverted->resetSorting();

    $query = $this->queryBuilderFactory->createQuery($reverted, $context);
    //...
}
```

New implementation:
```
public function generatePartialFacet(
    FacetInterface $facet,
    Criteria $reverted,
    Criteria $criteria,
    ShopContextInterface $context
) {
    $query = $this->queryBuilderFactory->createQuery($reverted, $context);
    //...
```

#### Elastic search
In the elastic search implementation the current filter behavior is controlled by the condition handlers. By adding an query as `post filter`, facets are not affected by this filter.
This behavior is checked over the `Criteria->hasBaseCondition` statement:
```
/**
 * @inheritdoc
 */
public function handle(
    CriteriaPartInterface $criteriaPart,
    Criteria $criteria,
    Search $search,
    ShopContextInterface $context
) {
    if ($criteria->hasBaseCondition($criteriaPart->getName())) {
        $search->addFilter(new TermQuery('active', 1));
    } else {
        $search->addPostFilter(new TermQuery('active', 1));
    }
}

```
This behavior is now controlled in the `\Shopware\Bundle\SearchBundleES\ProductNumberSearch`. To support the new filter mode, each condition handler has to implement the `\Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface`.
It is possible to implement this interface beside the original `\Shopware\Bundle\SearchBundleES\HandlerInterface`.
```
namespace Shopware\Bundle\SearchBundleES;
if (!interface_exists('\Shopware\Bundle\SearchBundleES\PartialConditionHandlerInterface')) {
    interface PartialConditionHandlerInterface { }
}

namespace Shopware\SwagBonusSystem\Bundle\SearchBundleES;

class BonusConditionHandler implements HandlerInterface, PartialConditionHandlerInterface
{
    const ES_FIELD = 'attributes.bonus_system.has_bonus';

    public function supports(CriteriaPartInterface $criteriaPart)
    {
        return ($criteriaPart instanceof BonusCondition);
    }

    public function handleFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addFilter(
            new TermQuery(self::ES_FIELD, 1)
        );
    }


    public function handlePostFilter(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        $search->addPostFilter(new TermQuery(self::ES_FIELD, 1));
    }

    public function handle(
        CriteriaPartInterface $criteriaPart,
        Criteria $criteria,
        Search $search,
        ShopContextInterface $context
    ) {
        if ($criteria->hasBaseCondition($criteriaPart->getName())) {
            $this->handleFilter($criteriaPart, $criteria, $search, $context);
        } else {
            $this->handlePostFilter($criteriaPart, $criteria, $search, $context);
        }
    }
}
```

### CookiePermission

Cookie permissions is now a part of shopware and you can configure it in the shop settings. 

We implement a basic cookie permission hint. If you want to change the decision whether the item is displayed or not, overwrite the jQuery plugin in the jquery.cookie-permission.js

### Shopping Worlds

Shopping World have been technically refactored from the ground up to improve the overall performance when adding several elements to a shopping world.

#### ComponentHandler

The processing of elements has been changed from events to classes of component handler.

**Before: Subscribe to an event and process element data in the callback method**

```php
public static function getSubscribedEvents()
{
    return ['Shopware_Controllers_Widgets_Emotion_AddElement' => 'handleSideviewElement'];
}
```

**After: Create new class and tag it as `shopware_emotion.component_handler` in your `services.xml`**

```php
class SideviewComponentHandler implements ComponentHandlerInterface
{
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === 'emotion-component-sideview';
    }

    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        // do some prepare logic
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        // do some handle logic and fill data
        $element->getData()->set('key', 'value');
    }
}
```

#### Requesting items in ComponentHandler

To make use of the performance improvement, you have to split your logic into a prepare step and handle step. The prepare step collects product numbers or criteria objects which will be resolved across all elements at once. The handle step provides a collection with resolved products and can be filled into your element.

```php
public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
{
    $productNumber = $element->getConfig()->get('selected_product_number');
    $collection->getBatchRequest()->setProductNumbers('my-unique-request', [$productNumber]);
}

public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
{
    $product = current($collection->getBatchResult()->get('my-unique-request));
    $element->getData()->set('product', $product);
}
```

Keep in mind to use a unique key for requesting and getting products. For best practise, use the element's id in your key (`$element->getId()`). 

#### View changes

In addition, the emotion template will now be populated with an `Shopware\Bundle\EmotionBundle\Struct\Emotion` object instead of an array. To recreate the old behaviour, you have to convert the emotion object to an array using a smarty function.

```
{convertEmotion assign=emotion emotion=$emotion}
```
