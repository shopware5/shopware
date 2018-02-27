# CHANGELOG for Shopware 5.5.x

This changelog references changes done in Shopware 5.5 patch versions.

[View all changes from v5.4.0...v5.5.0](https://github.com/shopware/shopware/compare/v5.4.0...v5.5.0)

### Additions

* Added unique identifier to `s_core_documents` for document types in order to create a unique, settable property for plugin developers and enabling risk free user editing of the name field
* Added new emotion component handlers:
    - `HtmlCodeComponentHandler`
    - `HtmlElementComponentHandler`
    - `IFrameComponentHandler`
    - `YoutubeComponentHandler`

### Changes

* Changed the execution model of `replace` hooks to prevent multiple calls of the hooked method, if more than one `replace` hook on the same method exists and all of them call `executeParent()` once
* Changed Symfony version to 3.4.4
* Changed the event `Shopware_Form_Builder` so that the `reference` contains the `BlockPrefix` of the Formtype, not the name

### Removals

* Removed tables `s_user_billingaddress_attributes` and `s_user_shippingaddress_attributes`
* Removed class `Shopware\Bundle\EmotionBundle\ComponentHandler\EventComponentHandler`
* Removed methods `getByCategory` and `getListByCategory` of interface `Shopware\Bundle\StoreFrontBundle\Gateway\SimilarProductsGatewayInterface`
* Removed `sSelfCanonical` of `Shopware\Components\Compatibility\LegacyStructConverter`
* Removed method `getSourceSet` of `Shopware\Bundle\StoreFrontBundle\Struct\Thumbnail`
* Removed class `Shopware_Components_Convert_Csv`, `Shopware_Components_Convert_Excel` and `Shopware_Components_Convert_Xml`
* Removed methods `addAttribute` and `removeAttribute` of `Shopware\Components\Model\ModelManager`
* Removed methods `setParameters` and `addParameters` of `Shopware\Components\Model\QueryBuilder`
* Removed method `assertVersionGreaterThen` of `Shopware_Components_Plugin_Bootstrap`
* Removed method `getSnippet` of `Shopware_Components_Snippet_Manager`
* Removed class `Shopware\Models\Config\Element\Shopware_Components_Form`
* Removed method `confirmAction` of `Shopware_Controllers_Frontend_Newsletter`
* Removed methods `indexAction`, `listingAction`, `requestAction`, `detailAction`, `directAction`, `commitForm` of `Shopware_Controllers_Frontend_Ticket`
* Removed methods `emotionTopSellerAction` and `emotionNewcomerAction` of `Shopware_Controllers_Widgets_Emotion`
* Removed method `ajaxListingAction` of `Shopware_Controllers_Widgets_Listing`
* Removed method `sGetAffectedSuppliers` of `sArticles`
* Removed variables `$sLanguage` and `sMultishop` with method `sGetMultishop` of `sExport`
* Removed variables `o_attr_1`, `o_attr_2`, `o_attr_3`, `o_attr_4`, `o_attr_5`, `o_attr_6` of `sOrder`
* Removed method `sCreateRewriteTableSuppliers` of `sRewriteTable`
* Removed method `save` of `Shopware\Models\Config\Form`
* Removed class `Shopware\Models\Customer\Billing` and `Shopware\Models\Customer\BillingRepository`
* Removed variables `billing` and `shipping` with methods `getShipping`, `setShipping`, `getBilling`, `setBilling` of `Shopware\Models\Customer\Customer`
* Removed class `Shopware\Models\Customer\Shipping`
* Removed methods `getCategoryEmotionsQuery` and `getCategoryEmotionsQueryBuilder` of `Shopware\Models\Emotion\Repository`
* Removed method `onUpdate` of `Shopware\Models\Media\Settings`
* Removed class `Shopware\Models\Order\Document\Type`
* Removed constant `PAYMENT_STATE_THE_PAYMENT_HAS_BEEN_ORDERED_BY_HANSEATIC_BANK` of `Shopware\Models\Order\Status`
* Removed variable `description` with methods `setDescription` and `getDescription` of `Shopware\Models\Order\Status`
* Removed variable `surchargeString` and methods of `Shopware\Models\Payment\Payment`:
    * `setClass`
    * `getClass`
    * `setTable`
    * `getTable`
    * `setSurchargeString`
    * `getSurchargeString`
    * `setEmbedIFrame`
    * `getEmbedIFrame`
* Removed methods `getPaymentsQuery`, `getPaymentsQueryBuilder` of `Shopware\Models\Payment\Repository`
* Removed variable `label` and methods `getLabel` and `setLabel` of `Shopware\Models\Widget\Widget`
* Removed snippets `table/s_user_billingaddress_attributes` and `table/s_user_shippingaddress_attributes`
* Removed variable `preLoadStoredEntry` of `Shopware.form.field.PagingComboBox`
* Removed smarty block `frontend_blog_detail_comments`
* Removed smarty block `frontend_detail_data_block_prices_headline`
* Removed smarty block `frontend_detail_buy_variant`
* Removed smarty block `frontend_detail_data_price_info`
* Removed smarty block `frontend_detail_data_liveshopping`
* Removed smarty block `frontend_index_header_css_ie`
* Removed smarty block `frontend_index_ajax_seo_optimized`
* Removed smarty block `frontend_index_categories_left_ul`
* Removed smarty block `frontend_listing_box_article_actions_more`
* Removed smarty block `frontend_listing_box_article_actions_inline`

### Deprecations

* Deprecated `lastStock` field in `\Shopware\Models\Article\Article` as the field has been moved to the variants. It will be removed in 5.6
* Deprecated `laststock` column in `s_articles` since this field has been moved to the variants. It will be removed in 5.6
