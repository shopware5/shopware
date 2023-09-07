# CHANGELOG for Shopware 5.7.x

This changelog references changes done in Shopware 5.7 patch versions.

## 5.7.19

[View all changes from v5.7.18...v5.7.19](https://github.com/shopware5/shopware/compare/v5.7.18...v5.7.19)

### Changes

* Changed behaviour of the translation transfer while setting a product variant as the main variant
* Changed the test kernel, so PHPUnit tests do no longer ignore PHP warnings and notices and are failing instead

* Updated `cocur/slugify` to version 4.4.0
* Updated `doctrine/orm` to version 2.15.3
* Updated `google/cloud-storage` to version 1.33.0
* Updated `mpdf/mpdf` to version 8.2.0
* Updated `laminas/laminas-code` to version 4.12.0 for PHP 8.1 and newer
* Updated `phpunit/phpuit` to version 9.6.11
* Updated `setasign/fpdf` to version 1.8.6
* Updated `setasign/fpdi` to version 2.4.1
* Updated `symfony/serializer` to version 5.4.25
* Updated `voku/anti-xss` to version 4.1.42
* Updated `wikimedia/less.php` to version 4.1.1
* Updated several indirect dependencies
* Updated npm dependencies in `themes/package.json`
* Updated npm dependencies in `themes/Frontend/Responsive/package.json`

## 5.7.18

[View all changes from v5.7.17...v5.7.18](https://github.com/shopware5/shopware/compare/v5.7.17...v5.7.18)

### Additions

* Added new input field that does not allow values, that contain URLs

### Deprecations

* Deprecated `\Shopware\Bundle\SearchBundleDBAL\SearchBundleDBALSubscriber`, it will be removed with Shopware 5.8, because it is not used.

### Changes

* Changed newsletter registration so that it does not allow URLs as value for first and last name
* Changed customer registration so that it does not allow URLs as value for first and last name
* Updated `behat/behat` to version 3.13.0
* Updated `doctrine/dbal` to version 2.13.9
* Updated `doctrine/orm` to version 2.15.2
* Updated `doctrine/persistence` to version 3.2.0
* Updated `elasticsearch/elasticsearch` to version 7.17.2
* Updated `friendsofphp/proxy-manager-lts` to version 1.0.16
* Updated `google/cloud-storage` to version 1.31.2
* Updated `guzzlehttp/guzzle` to version 7.7.0
* Updated `guzzlehttp/psr7` to version 2.5.0
* Updated `laminas/laminas-code` to version 4.11.0 for PHP 8.1 and newer
* Updated `mpdf/mpdf` to version 8.1.6
* Updated `phpunit/phpunit` to version 9.6.8
* Updated `setasign/fpdi` to version 2.3.7
* Updated `symfony/serializer` to version 5.4.23
* Updated `voku/anti-xss` to version 4.1.41
* Updated `wikimedia/less.php` to version 4.1.0
* Updated several indirect dependencies

### Removals

* Removed the feedback popup at first login, as the feedback is no longer used.
* Removed the Shopware BI feature, as it will be shut down.

## 5.7.17

[View all changes from v5.7.16...v5.7.17](https://github.com/shopware5/shopware/compare/v5.7.16...v5.7.17)

### Additions

* Added version `4.8.0` to version constraint of `laminas/laminas-code` to allow installation on PHP 8.2
* Added new polyfill `symfony/polyfill-php82` to be able to use PHP 8.2 features

### Deprecations

* Deprecated `\Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface::getProductCombinations`, it will be removed in the next minor version v5.8.

### Changes

* Updated `bcremer/line-reader` to version 1.3.0
* Updated `behat/behat` to version 3.12.0
* Updated `cocur/slugify` to version 4.3.0
* Updated `doctrine/annotations` to version 1.14.3
* Updated `doctrine/collections` to version 1.8.0
* Updated `doctrine/common` to version 3.4.3
* Updated `doctrine/event-manager` to version 1.2.0
* Updated `doctrine/orm` to version 2.14.1
* Updated `doctrine/persistence` to version 3.1.3
* Updated `elasticsearch/elasticsearch` to version 7.17.1
* Updated `friendsofphp/proxy-manager-lts` to version 1.0.14
* Updated `google/cloud-storage` to version 1.30.1
* Updated `guzzlehttp/psr7` to version 2.4.3
* Updated `laminas/laminas-code` to version 4.7.1
* Updated `laminas/laminas-escaper` to version 2.12.0
* Updated `league/flysystem` to version 1.1.10
* Updated `monolog/monolog` to version 2.9.1
* Updated `mpdf/mpdf` to version 8.1.4
* Updated `phpunit/phpunit` to version 9.6.3
* Updated `sensiolabs/behat-page-object-extension` to version 2.3.7
* Updated `setasign/fpdf` to version 1.8.5
* Updated `symfony/console` to version 4.4.49
* Updated `symfony/dependency-injection` to version 4.4.49
* Updated `symfony/expression-language` to version 4.4.47
* Updated `symfony/form` to version 4.4.48
* Updated `symfony/http-foundation` to version 4.4.49
* Updated `symfony/http-kernel` to version 4.4.50
* Updated `symfony/polyfill-php80` to version 1.27.0
* Updated `symfony/polyfill-php81` to version 1.27.0
* Updated `symfony/serializer` to version 5.4.17
* Updated `symfony/validator` to version 4.4.48
* Updated `wikimedia/less.php` to version 3.2.0
* Updated several indirect dependencies
* Updated npm dependencies in `themes/package.json`
* Updated npm dependencies in `themes/Frontend/Responsive/package.json`

## 5.7.16

[View all changes from v5.7.15...v5.7.16](https://github.com/shopware5/shopware/compare/v5.7.15...v5.7.16)

### Additions

* Added new block `backend/mail_log/model/filter/fields` in `themes/Backend/ExtJs/backend/mail_log/model/filter.js` to be able to extend the model fields
* Added missing dependency `doctrine/inflector`, which was an indirect dependency before

### Changes

* Changed the following block names, because they were duplicated and could have caused errors

| file path                                                                                                 | old block name                                                                | new block name                                                                    |
|-----------------------------------------------------------------------------------------------------------|-------------------------------------------------------------------------------|-----------------------------------------------------------------------------------|
| themes/Backend/ExtJs/backend/analytics/view/table/partner_revenue.js                                      | backend/analytics/view/table/referrer_revenue                                 | backend/analytics/view/table/partner_revenue                                      |
| themes/Backend/ExtJs/backend/base/model/product_box_layout.js                                             | backend/base/model/product_box_layout                                         | backend/base/model/product_box_layout/fields                                      |
| themes/Backend/ExtJs/backend/config/model/main/value.js                                                   | backend/config/model/main/navigation/fields                                   | backend/config/model/main/value/fields                                            |
| themes/Backend/ExtJs/backend/config/view/custom_search/sorting/classes/product_number_sorting.js          | backend/config/view/custom_search/sorting/classes/product_name_sorting        | backend/config/view/custom_search/sorting/classes/product_number_sorting          |
| themes/Backend/ExtJs/backend/config/view/main/fieldset.js                                                 | backend/config/view/main/form                                                 | backend/config/view/main/fields_set                                               |
| themes/Backend/ExtJs/backend/customer/model/batch.js                                                      | backend/customer/model/customer                                               | backend/customer/model/batch                                                      |
| themes/Backend/ExtJs/backend/customer/model/batch.js                                                      | backend/customer/model/customer/fields                                        | backend/customer/model/batch/fields                                               |
| themes/Backend/ExtJs/backend/customer/view/customer_stream/conditions/not_registered_in_shop_condition.js | backend/customer/view/customer_stream/conditions/registered_in_shop_condition | backend/customer/view/customer_stream/conditions/not_registered_in_shop_condition |
| themes/Backend/ExtJs/backend/mail_log/model/filter.js                                                     | backend/performance/model/filter                                              | backend/mail_log/model/filter                                                     |
| themes/Backend/ExtJs/backend/media_manager/view/replace/grid.js                                           | backend/media_manager/view/replace/window                                     | backend/media_manager/view/replace/grid                                           |
| themes/Backend/ExtJs/backend/media_manager/view/replace/upload.js                                         | backend/media_manager/view/replace/row                                        | backend/media_manager/view/replace/upload                                         |
| themes/Backend/ExtJs/backend/newsletter_manager/model/container.js                                        | backend/newsletter_manager/model/sender/fields                                | backend/newsletter_manager/model/container/fields                                 |
| themes/Backend/ExtJs/backend/order/model/detail_batch.js                                                  | backend/order/model/batch                                                     | backend/order/model/detail_batch                                                  |
| themes/Backend/ExtJs/backend/performance/controller/direct.js                                             | backend/performance/controller/main                                           | backend/performance/controller/direct                                             |
| themes/Backend/ExtJs/backend/performance/model/seo.js                                                     | backend/performance/model/top_seller/fields                                   | backend/performance/model/seo/fields                                              |
| themes/Backend/ExtJs/backend/product_feed/model/shop.js                                                   | backend/product_feed/model/main                                               | backend/product_feed/model/shop                                                   |
| themes/Backend/ExtJs/backend/product_stream/view/condition_list/field/attribute_date_time.js              | backend/product_stream/view/condition_list/condition/attribute_date           | backend/product_stream/view/condition_list/condition/attribute_date_time          |
| themes/Backend/ExtJs/backend/shipping/store/tax.js                                                        | backend/shipping/store/country                                                | backend/shipping/store/tax                                                        |
| themes/Backend/ExtJs/backend/site/store/selected.js                                                       | backend/site/store/groups                                                     | backend/site/store/selected                                                       |
| themes/Backend/ExtJs/backend/snippet/view/main/translate_window.js                                        | backend/snippet/view/main/edit_form                                           | backend/snippet/view/main/translate_window                                        |
| themes/Backend/ExtJs/backend/systeminfo/controller/systeminfo.js                                          | backend/systeminfo/controller/main                                            | backend/systeminfo/controller/systeminfo                                          |
| themes/Backend/ExtJs/backend/translation/view/main/services.js                                            | backend/translation/view/main/window                                          | backend/translation/view/main/services                                            |
| themes/Backend/ExtJs/backend/user_manager/model/user_detail.js                                            | backend/user_manager/model/detail                                             | backend/user_manager/model/user_detail                                            |
| themes/Backend/ExtJs/backend/user_manager/model/user_detail.js                                            | backend/user_manager/model/detail/fields                                      | backend/user_manager/model/user_detail/fields                                     |
| themes/Frontend/Bare/frontend/blog/comment/form.tpl                                                       | frontend_blog_comments_input_captcha_placeholder                              | frontend_blog_comments_input_captcha_notice                                       |
| themes/Frontend/Bare/frontend/blog/comments.tpl                                                           | frontend_blog_comments_form                                                   | frontend_blog_comments_form_action                                                |
| themes/Frontend/Bare/frontend/checkout/finish.tpl                                                         | frontend_checkout_confirm_information_addresses_billing_panel_title           | frontend_checkout_finish_information_addresses_billing_panel_title                |
| themes/Frontend/Bare/frontend/compare/add_article.tpl                                                     | product_compare_error_title                                                   | product_compare_error_message                                                     |
| themes/Frontend/Bare/frontend/compare/col.tpl                                                             | frontend_listing_box_article_price_regulation                                 | frontend_compare_price_regulation                                                 |
| themes/Frontend/Bare/frontend/compare/col.tpl                                                             | frontend_listing_box_article_price_discount_before                            | frontend_compare_price_regulation_before                                          |
| themes/Frontend/Bare/frontend/compare/col.tpl                                                             | frontend_listing_box_article_price_discount_after                             | frontend_compare_price_regulation_after                                           |
| themes/Frontend/Bare/frontend/compare/col_description.tpl                                                 | frontend_compare_article_name                                                 | frontend_compare_article_name_header                                              |
| themes/Frontend/Bare/frontend/compare/col_description.tpl                                                 | frontend_compare_votings                                                      | frontend_compare_votings_header                                                   |
| themes/Frontend/Bare/frontend/compare/col_description.tpl                                                 | frontend_compare_description                                                  | frontend_compare_description_header                                               |
| themes/Frontend/Bare/frontend/compare/col_description.tpl                                                 | frontend_compare_price                                                        | frontend_compare_price_header                                                     |
| themes/Frontend/Bare/frontend/compare/col_description.tpl                                                 | frontend_compare_properties                                                   | frontend_compare_properties_header                                                |
| themes/Frontend/Bare/frontend/detail/data.tpl                                                             | frontend_detail_data_pseudo_price_discount_content                            | frontend_detail_data_regulation_price_content                                     |
| themes/Frontend/Bare/frontend/listing/product-box/box-minimal.tpl                                         | frontend_listing_box_article_price_discount_before                            | frontend_listing_box_article_price_regulation_before                              |
| themes/Frontend/Bare/frontend/listing/product-box/box-minimal.tpl                                         | frontend_listing_box_article_price_discount_after                             | frontend_listing_box_article_price_regulation_after                               |
| themes/Frontend/Bare/frontend/listing/product-box/product-price.tpl                                       | frontend_listing_box_article_price_discount_before                            | frontend_listing_box_article_price_regulation_before                              |
| themes/Frontend/Bare/frontend/listing/product-box/product-price.tpl                                       | frontend_listing_box_article_price_discount_after                             | frontend_listing_box_article_price_regulation_after                               |
| themes/Frontend/Bare/frontend/newsletter/detail.tpl                                                       | frontend_newsletter_listing_error_message                                     | frontend_newsletter_detail_error_message                                          |
| themes/Frontend/Bare/frontend/plugins/notification/index.tpl                                              | frontend_account_index_form_captcha                                           | frontend_detail_index_notification_captcha                                        |

* Changed the following jQuery event names, because they were duplicated and could have caused errors

| file path                                                                       | method                           | old event name                                      | new event name                                             |
|---------------------------------------------------------------------------------|----------------------------------|-----------------------------------------------------|------------------------------------------------------------|
| themes/Frontend/Responsive/frontend/_public/src/js/jquery.image-slider.js       | onThumbnailSlideMove             | plugin/swImageSlider/onThumbnailSlideTouch          | plugin/swImageSlider/onThumbnailSlideMove                  |
| themes/Frontend/Responsive/frontend/_public/src/js/jquery.infinite-scrolling.js | generateButton                   | plugin/swInfiniteScrolling/onLoadMore               | plugin/swInfiniteScrolling/onGenerateButton                |
| themes/Frontend/Responsive/frontend/_public/src/js/jquery.listing-actions.js    | setCategoryParamsFromTopLocation | plugin/swListingActions/onSetCategoryParamsFromData | plugin/swListingActions/onSetCategoryParamsFromTopLocation |
| themes/Frontend/Responsive/frontend/_public/src/js/jquery.listing-actions.js    | getLabelIcon                     | plugin/swListingActions/onCreateStarLabel           | plugin/swListingActions/onGetLabelIcon                     |

* Updated `cocur/slugify` to version 4.2.0
* Updated `doctrine/annotations` to version 1.13.3
* Updated `doctrine/collections` to version 1.7.3
* Updated `doctrine/common` to version 3.4.0
* Updated `doctrine/event-manager` to version 1.1.2
* Updated `doctrine/orm` to version 2.13.1
* Updated `doctrine/persistence` to version 2.5.4
* Updated `google/cloud-storage` to version 1.28.1
* Updated `guzzlehttp/guzzle` to version 7.5.0
* Updated `guzzlehttp/psr7` to version 2.4.1
* Updated `laminas/laminas-code` to version 4.6.0
* Updated `league/flysystem-aws-s3-v3` to version 1.0.30
* Updated `monolog/monolog` to version 2.8.0
* Updated `phpunit/phpunit` to version 9.5.23
* Updated `symfony/config` to version 4.4.44
* Updated `symfony/console` to version 4.4.45
* Updated `symfony/dependency-injection` to version 4.4.44
* Updated `symfony/expression-language` to version 4.4.44
* Updated `symfony/finder` to version 4.4.44
* Updated `symfony/form` to version 4.4.45
* Updated `symfony/http-foundation` to version 4.4.45
* Updated `symfony/http-kernel` to version 4.4.45
* Updated `symfony/options-resolver` to version 4.4.44
* Updated `symfony/process` to version 4.4.44
* Updated `symfony/serializer` to version 5.4.12
* Updated `symfony/validator` to version 4.4.45
* Updated several indirect dependencies

## 5.7.15

[View all changes from v5.7.14...v5.7.15](https://github.com/shopware5/shopware/compare/v5.7.14...v5.7.15)

## 5.7.14

[View all changes from v5.7.13...v5.7.14](https://github.com/shopware5/shopware/compare/v5.7.13...v5.7.14)

## 5.7.13

[View all changes from v5.7.12...v5.7.13](https://github.com/shopware5/shopware/compare/v5.7.12...v5.7.13)

## 5.7.12

[View all changes from v5.7.11...v5.7.12](https://github.com/shopware5/shopware/compare/v5.7.11...v5.7.12)

### Additions

* Added missing dependency `doctrine/annotations`, which was an indirect dependency before
* Added new optional parameter `dateTime` to `\Shopware\Components\Logger::addRecord` method to be compatible with parent `\Monolog\Logger::addRecord` method
* Added requirement `composer-runtime-api ^2.0` which was already added indirectly in version [5.7.8](#5.7.8)

### Changes

* Updated `bamarni/composer-bin-plugin` to version 1.5.0
* Updated `behat/mink` to version 1.10.0
* Updated `behat/mink-selenium2-driver` to version 1.6.0
* Updated `doctrine/cache` to version 1.13.0
* Updated `doctrine/common` to version 3.3.0
* Updated `doctrine/dbal` to version 2.13.8
* Updated `doctrine/orm` to version 2.12.3
* Updated `doctrine/persistence` to version 2.5.3
* Updated `elasticsearch/elasticsearch` to version 7.17.0
* Updated `friendsofphp/proxy-manager-lts` to version 1.0.12
* Updated `google/cloud-storage` to version 1.27.1
* Updated `guzzlehttp/guzzle` to version 7.4.5
* Updated `guzzlehttp/psr7` to version 2.4.0
* Updated `laminas/laminas-escaper` to version 2.10.0
* Updated `monolog/monolog` to version 2.7.0
* Updated `mpdf/mpdf` to version 8.1.1
* Updated `phpunit/phpunit` to version 9.5.21
* Updated `symfony/browser-kit` to version 4.4.37
* Updated `symfony/config` to version 4.4.42
* Updated `symfony/console` to version 4.4.42
* Updated `symfony/dependency-injection` to version 4.4.42
* Updated `symfony/dom-crawler` to version 4.4.42
* Updated `symfony/expression-language` to version 4.4.41
* Updated `symfony/filesystem` to version 4.4.42
* Updated `symfony/finder` to version 4.4.41
* Updated `symfony/form` to version 4.4.42
* Updated `symfony/http-foundation` to version 4.4.42
* Updated `symfony/http-kernel` to version 4.4.42
* Updated `symfony/options-resolver` to version 4.4.37
* Updated `symfony/polyfill-php80` to version 1.26.0
* Updated `symfony/polyfill-php81` to version 1.26.0
* Updated `symfony/process` to version 4.4.41
* Updated `symfony/serializer` to version 5.4.9
* Updated `symfony/validator` to version 4.4.41
* Updated `symfony/web-link` to version 4.4.37
* Updated several indirect dependencies

## 5.7.11

[View all changes from v5.7.10...v5.7.11](https://github.com/shopware5/shopware/compare/v5.7.10...v5.7.11)

## 5.7.10

[View all changes from v5.7.9...v5.7.10](https://github.com/shopware5/shopware/compare/v5.7.9...v5.7.10)

## 5.7.9

[View all changes from v5.7.8...v5.7.9](https://github.com/shopware5/shopware/compare/v5.7.8...v5.7.9)

### Changes

* Updated npm dependencies in `themes/package.json`
* Updated npm dependencies in `themes/Frontend/Responsive/package.json`


## 5.7.8

[View all changes from v5.7.7...v5.7.8](https://github.com/shopware5/shopware/compare/v5.7.7...v5.7.8)

### Additions

* Added missing dependency `google/cloud-storage`, which was an indirect dependency before
* Added missing dependency `psr/log`, which was an indirect dependency before
* Added missing dependency `psr/link`, which was an indirect dependency before
* Added missing dependency `doctrine/event-manager`, which was an indirect dependency before

### Changes

* Changed `\Zend_Db_Adapter_Abstract::insert` return type to native `int` type
* Changed `\Zend_Db_Adapter_Abstract::fetchAll` return type to native `array` type
* Updated `bcremer/line-reader` to version 1.2.0
* Updated `beberlei/assert` to version 3.3.2
* Updated `cocur/slugify` to version 4.1.0
* Updated `doctrine/common` to version 3.2.1
* Updated `doctrine/dbal` to version 2.13.7
* Updated `doctrine/orm` to version 2.11.0
* Updated `elasticsearch/elasticsearch` to version 7.16.0
* Updated `fig/link-util` to version 1.1.2
* Updated `guzzlehttp/guzzle` to version 7.4.1
* Updated `guzzlehttp/psr7` to version 2.2.1
* Updated `laminas/laminas-code` to version 4.5.1
* Updated `league/flysystem` to version 1.1.9
* Updated `league/flysystem-aws-s3-v3` to version 1.0.29
* Updated `monolog/monolog` to version 2.3.5
* Updated `mpdf/mpdf` to version 8.0.15
* Updated `symfony/config` to version 4.4.36
* Updated `symfony/console` to version 4.4.36
* Updated `symfony/dependency-injection` to version 4.4.36
* Updated `symfony/finder` to version 4.4.36
* Updated `symfony/form` to version 4.4.36
* Updated `symfony/http-foundation` to version 4.4.36
* Updated `symfony/http-kernel` to version 4.4.36
* Updated `symfony/polyfill-php80` to version 1.24.0
* Updated `symfony/polyfill-php81` to version 1.24.0
* Updated `symfony/process` to version 4.4.36
* Updated `symfony/serializer` to version 5.4.2
* Updated `symfony/validator` to version 4.4.36
* Updated `behat/behat` to version 3.10.0
* Updated `behat/gherkin` to version 4.9.0
* Updated `behat/mink` to version 1.9.0
* Updated `behat/mink-selenium2-driver` to version 1.5.0
* Updated `friends-of-behat/mink-extension` to version 2.6.1
* Updated `phpspec/prophecy` to version 1.15.0
* Updated `phpunit/phpunit` to version 9.5.11
* Updated `sensiolabs/behat-page-object-extension` to version 2.3.4
* Updated `symfony/dom-crawler` to version 4.4.36
* Updated several indirect dependencies

### Removals

* Removed unused dependency `composer/package-versions-deprecated`
* Removed unused dependency `ocramius/proxy-manager`
* Removed unused dependency `psr/http-message`. It is now an indirect dependency.

## 5.7.7

[View all changes from v5.7.6...v5.7.7](https://github.com/shopware5/shopware/compare/v5.7.6...v5.7.7)

### Deprecations

* Deprecated `\Shopware_Controllers_Frontend_Checkout::getTaxRates`, it will be removed in the next minor version v5.8.
Use `TaxAggregator::taxSum` instead.

### Additions

* Added `\Shopware\Components\Cart\TaxAggregatorInterface`
* Added `\Shopware\Components\Cart\TaxAggregator` as a default implementation, extracting the tax aggregation logic from the checkout controller
* Added a new component to the update process. The `.htaccess`-file now contains a section dedicated to the Shopware core.
* Added new polyfill dependencies which were indirect dependencies before
  * `symfony/polyfill-php80` version 1.23.1
  * `symfony/polyfill-php81` version 1.23.0

### Changes

* Changed `\Shopware_Controllers_Frontend_Checkout::getTaxRates`, this method uses the `TaxAggregator::taxSum` now
* Changed `\Shopware_Models_Document_Order::processOrder`, this method uses the `TaxAggregator::shippingCostsTaxSum` method now
* Changed `\Shopware_Models_Document_Order::processPositions`, this method uses the `TaxAggregator::positionsTaxSum` method now
* Updated `league/flysystem` to version 1.1.6
* Updated `symfony/config` to version 4.4.34
* Updated `symfony/console` to version 4.4.34
* Updated `symfony/dependency-injection` to version 4.4.34
* Updated `symfony/expression-language` to version 4.4.34
* Updated `symfony/form` to version 4.4.34
* Updated `symfony/http-foundation` to version 4.4.34
* Updated `symfony/http-kernel` to version 4.4.34
* Updated `symfony/process` to version 4.4.34
* Updated `symfony/serializer` to version 5.3.12
* Updated `symfony/validator` to version 4.4.34
* Updated several indirect dependencies

### Removals

* Removed deprecated composer dependency `symfony/class-loader`. Use Composer ClassLoader instead

### Session validation

With v5.7.7 the session validation was adjusted, so that sessions created prior
to the latest password change of a customer account can't be used to login with
said account. This also means, that upon a password change, all existing
sessions for a given customer account are automatically considered invalid.

All sessions created prior to v5.7.7 are lacking the timestamp of the latest
password change and are therefore not considered valid anymore. **After an
upgrade to v5.7.7, all customers who have a session in the given shop, will need
to log in again.**

## 5.7.6

[View all changes from v5.7.5...v5.7.6](https://github.com/shopware5/shopware/compare/v5.7.5...v5.7.6)

### Additions

* Added a new CSP directive to the default `.htaccess`

## 5.7.5

[View all changes from v5.7.4...v5.7.5](https://github.com/shopware5/shopware/compare/v5.7.4...v5.7.5)

## 5.7.4

[View all changes from v5.7.3...v5.7.4](https://github.com/shopware5/shopware/compare/v5.7.3...v5.7.4)

### Deprecations

* Deprecated `ajaxValidateEmailAction`. It will be removed in Shopware 5.8 with no replacement.

### Additions

* Added filter event `Shopware_Controllers_Order_OpenPdf_FilterName` to `Shopware_Controllers_Backend_Order::openPdfAction()`
* Added new composer dependency `psr/http-message`
* Added new parameter `rowIndex` to `Shopware_Modules_Export_ExportResult_Filter_Fixed` event

### Breaks

* In case you have extended the `frontend_listing_actions_filter` block to override the "include" of the button template,
please extend the `frontend_listing_actions_filter_include` block from now on instead.

### Changes

* Changed `themes/Frontend/Bare/frontend/listing/listing_actions.tpl` to remove a duplicate name entry
* Updated TinyMCE to version 3.5.12
* Updated `bcremer/line-reader` to version 1.1.0
* Updated `beberlei/assert` to version 3.3.1
* Updated `beberlei/doctrineextensions` to version 1.3.0
* Updated `doctrine/cache` to version 1.12.1
* Updated `doctrine/collections` to version 1.6.8
* Updated `doctrine/common` to version 3.1.2
* Updated `doctrine/dbal` to version 2.13.4
* Updated `doctrine/orm` to version 2.9.5
* Updated `doctrine/persistence` to version 2.2.2
* Updated `guzzlehttp/guzzle` to version 7.3.0
* Updated `guzzlehttp/psr7` to version 1.8.2
* Updated `laminas/laminas-code` to version 4.4.3
* Updated `.aminas/laminas-escaper` to version 2.9.0
* Updated `mpdf/mpdf` to version 8.0.13
* Updated `ocramius/proxy-manager` to version 2.13.0
* Updated `ongr/elasticsearch-dsl` to version 7.2.2
* Updated `setasign/fpdf` to version 1.8.4
* Updated `setasign/fpdi` to version 2.3.6
* Updated `symfony/serializer` to version 5.3.8
* Updated `friends-of-behat/mink-extension` to version 2.5.0
* Updated `sensiolabs/behat-page-object-extension` to version 2.3.3
* Changed several Doctrine types to better match the database type or to improve understanding their purpose
  * \Shopware\Models\Article\Configurator\PriceVariation::$variation
  * \Shopware\Models\Article\Detail::$purchasePrice
  * \Shopware\Models\Article\Price::$percent
  * \Shopware\Models\Blog\Comment::$points
  * \Shopware\Models\Country\Country::$taxFree
  * \Shopware\Models\Country\Country::$taxFreeUstId
  * \Shopware\Models\Country\Country::$taxFreeUstIdChecked
  * \Shopware\Models\Emotion\Emotion::$active
  * \Shopware\Models\Emotion\Emotion::$fullscreen
  * \Shopware\Models\Emotion\Emotion::$isLandingPage
  * \Shopware\Models\Newsletter\ContainerType\Article::$position
  * \Shopware\Models\Order\Order::$invoiceShippingTaxRate
  * \Shopware\Models\Premium\Premium::$startPrice
  * \Shopware\Models\Tax\Rule::$tax

### Removals

* Removed unused composer dependency `php-http/message`

## 5.7.3

[View all changes from v5.7.2...v5.7.3](https://github.com/shopware5/shopware/compare/v5.7.2...v5.7.3)

### Changes

* Updated `wikimedia/less.php` to 3.1.0

### Removals

* Removed password hash from session
* Removed xml support for the snippet importer

## 5.7.2

[View all changes from v5.7.1...v5.7.2](https://github.com/shopware5/shopware/compare/v5.7.1...v5.7.2)

### Changes

* Updated `league/flysystem` to 1.1.4

## 5.7.1

[View all changes from v5.7.0...v5.7.1](https://github.com/shopware5/shopware/compare/v5.7.0...v5.7.1)

### Additions

* Added service alias from `Template` to `template`
* Added service alias from `Loader` to `loader`

### Changes

* Changed the visibility of services from tags `shopware_emotion.component_handler`, `criteria_request_handler` and `sitemap_url_provider` to public
* Changed following columns type from `date` to `datetime`
  * `s_order_basket.datum`
  * `s_order_comparisons.datum`
  * `s_order_notes.datum`

## 5.7.0

[View all changes from v5.6.10...v5.7.0](https://github.com/shopware5/shopware/compare/v5.6.10...v5.7.0)

### Breaks

* Do not use the `count()` smarty function in your templates anymore, since this will break with PHP version > 8.0. Use `|count` modifier instead!
* Replaced `psh` and `ant` with an `Makefile`. See updated README.md for installation workflow.
* Changed min PHP version to 7.4
* Changed min Elasticsearch version to 7
* Added new required methods `saveCustomUrls` and `saveExcludedUrls` to interface `Shopware\Bundle\SitemapBundle\ConfigHandler\ConfigHandlerInterface`
* Changed Symfony version to 4.4
* Changed Slugify version to 3.2
* Changed Doctrine ORM version to 2.7.3
* Changed Doctrine Cache version to 1.10.2
* Changed Doctrine Common version to 3.0.2
* Changed Doctrine Persistence version to 2.0.0
* Changed Guzzle version to 7.1
* Changed Monolog version to 2
* Changed FPDF version to 1.8.2
* Changed FPDI version to 2.2.0
* Changed mPDF version to 8.0.7
* Migrated Zend components to new Laminas
* Elasticsearch indices doesn't use anymore types

### Additions

* Added Symfony session to `Request` object
* Added new user interface for the sitemap configuration. It's available in the backend performance module
* Added `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` to save and read the sitemap configuration from the database
* Added new doctrine model `Shopware\Models\Emotion\LandingPage`, which extends from `Shopware\Models\Emotion\Emotion`.
It's needed to search for landing pages only using the backend store `Shopware.store.Search`
* Added new doctrine models `Shopware\Models\Sitemap\CustomUrl` and `Shopware\Models\Sitemap\ExcludeUrl`
* Added new ExtJS component `Shopware.grid.Searchable`.
Using it you can search for different entities in a single grid, such as products, categories, blogs, etc.
Have a look at the new sitemap UI to see what it looks like
* Added `Shopware-Listing-Total` header to ajax listing loading
* Added database transaction around plugin uninstall, activate and deactivate
* Added support for MySQL 8 `sql_require_primary_key`
* Added `attribute` to users listing in API
* Added new blocks `document_index_head_logo` and `document_index_head_wrapper` to `themes/Frontend/Bare/documents/index.tpl`
* Added `unmapped_type` to `integer` in `engine/Shopware/Bundle/SearchBundleES/SortingHandler/ManualSortingHandler.php`
* Added a notice to registration form when a shipment blocked country has been selected

### Changes

* Changed `Shopware\Models\Order\Order` and `Shopware\Models\Order\Detail` models by extracting business logic into:
    * `Shopware\Bundle\OrderBundle\Service\StockService`
    * `Shopware\Bundle\OrderBundle\Service\CalculationService`
    * `Shopware\Bundle\OrderBundle\Subscriber\ProductStockSubscriber`
    * `Shopware\Bundle\OrderBundle\Subscriber\OrderRecalculationSubscriber`
* Changed `Enlight_Components_Session_Namespace` to extend from `Symfony\Component\HttpFoundation\Session\Session`
* Changed the default config for smarty `compileCheck` to false
* Changed following columns to nullable
    * `s_order_details.releasedate`
    * `s_core_auth.lastlogin`
    * `s_campaigns_logs.datum`
    * `s_emarketing_banners.valid_from`
    * `s_emarketing_banners.valid_to`
    * `s_emarketing_lastarticles.time`
    * `s_emarketing_tellafriend.datum`
    * `s_order_basket.datum`
    * `s_order_comparisons.datum`
    * `s_order_notes.datum`
    * `s_statistics_pool.datum`
    * `s_statistics_referer.datum`
    * `s_statistics_visitors.datum`
    * `s_user.firstlogin`
    * `s_user.lastlogin`
* Changed response from `Shopware_Controllers_Widgets_Listing` from JSON to HTML
* Changed emotion component names to allow translations using snippets
    * `Artikel` => `product`
    * `Kategorie-Teaser` => `category_teaser`
    * `Blog-Artikel` => `blog_article`
    * `Banner` => `banner`
    * `Banner-Slider` => `banner_slider`
    * `Youtube-Video` => `youtube`
    * `Hersteller-Slider` => `manufacturer_slider`
    * `Artikel-Slider` => `product_slider`
    * `HTML-Element` => `html_element`
    * `iFrame-Element` => `iframe`
    * `HTML5 Video-Element` => `html_video`
    * `Code Element` => `code_element`
* Changed the search to not consider keywords which match 90% of all variants 
* Changed `\Shopware\Bundle\ESIndexingBundle\Product\ProductProvider` to set `hasStock` based on instock like DBAL implementation
* Changed `\Shopware_Controllers_Backend_ProductStream::loadPreviewAction` to return formatted prices
* Changed `sw:plugin:activate` exit code from 1 to 0, when it's already installed.
* Changed `\Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\CategoryGateway::get` it accepts now only integers as id
* Changed `sw:es:index:populate` to accept multiple shop ids with `--shopId={1,2}`
* Changed `\Shopware\Bundle\ESIndexingBundle\Product\ProductProvider` to consider cheapest price configuration
* Changed `\Shopware\Bundle\PluginInstallerBundle\Service\PluginInstaller` to remove also menu translations

### Removals

* Removed following classes:
    * `Enlight_Components_Session`
    * `Enlight_Components_Session_SaveHandler_DbTable`
    * `Zend_Session`
    * `Zend_Session_Namespace`
    * `Zend_Session_Abstract`
    * `Zend_Session_Exception`
    * `Zend_Session_SaveHandler_DbTable`
    * `Zend_Session_SaveHandler_Exception`
    * `Zend_Session_SaveHandler_Interface`
    * `Zend_Session_Validator_Abstract`
    * `Zend_Session_Validator_HttpUserAgent`
    * `Zend_Session_Validator_Interface`
    * `Shopware\Components\Log\Handler\ChromePhpHandler`
    * `Shopware\Components\Log\Handler\FirePHPHandler`
    * `\Shopware_Plugins_Core_Debug_Bootstrap`
    * `\Shopware\Plugin\Debug\Components\CollectorInterface`
    * `\Shopware\Plugin\Debug\Components\ControllerCollector`
    * `\Shopware\Plugin\Debug\Components\DatabaseCollector`
    * `\Shopware\Plugin\Debug\Components\DbalCollector`
    * `\Shopware\Plugin\Debug\Components\ErrorCollector`
    * `\Shopware\Plugin\Debug\Components\EventCollector`
    * `\Shopware\Plugin\Debug\Components\ExceptionCollector`
    * `\Shopware\Plugin\Debug\Components\TemplateCollector`
    * `\Shopware\Plugin\Debug\Components\TemplateVarCollector`
    * `\Shopware\Plugin\Debug\Components\Utils`
    * `\Shopware\Components\Api\Resource\ApiProgressHelper`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContext`
    * `\Shopware\Components\OpenSSLEncryption`
    * `\Shopware\Bundle\SearchBundleES\DependencyInjection\Factory\ProductNumberSearchFactory`
* Removed method `\Shopware\Bundle\EsBackendBundle\EsBackendIndexer::buildAlias` use `\Shopware\Bundle\EsBackendBundle\IndexFactoryInterface::createIndexConfiguration` instead
* Removed method `\Shopware\Bundle\SearchBundleES\DependencyInjection\Factory\ProductNumberSearchFactory::registerHandlerCollection`, use DI Tag `shopware_search_es.search_handler` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryAll`, use `\Shopware\Components\Model\ModelRepository::findAll` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryAll`, use `\Shopware\Components\Model\ModelRepository::findAll` instead
* Removed method `\Shopware\Components\Model\ModelRepository::queryBy`, use `\Shopware\Components\Model\ModelRepository::findBy` instead
* Removed following interfaces:
    * `\Shopware\Bundle\ESIndexingBundle\Product\ProductProviderInterface`
    * `\Shopware\Bundle\ESIndexingBundle\Property\PropertyProviderInterface`
    * `\Shopware\Bundle\ESIndexingBundle\EsSearchInterface`
    * `\Shopware\Bundle\StoreFrontBundle\Struct\LocationContextInterface`
* Removed from class `\Shopware\Components\HttpCache\CacheWarmer` following methods:
    * `callUrls`
    * `getSEOURLByViewPortCount`
    * `getAllSEOUrlCount`
    * `getAllSEOUrls`
    * `getSEOUrlByViewPort`
    * `prepareUrl`
    * `getShopDataById`
* Removed following methods from class `\Shopware_Controllers_Backend_Search`:
    * `getArticles` 
    * `getCustomers` 
    * `getOrders` 
* Removed referenced value from magic getter in session
* Removed the assignment of all request parameters to the view in `Shopware_Controllers_Widgets_Listing::productsAction`
* Removed duplicate ExtJs classes and added alias to new class:
    * `Shopware.apps.Config.view.element.Boolean`
    * `Shopware.apps.Config.view.element.Button`
    * `Shopware.apps.Config.view.element.Color`
    * `Shopware.apps.Config.view.element.Date`
    * `Shopware.apps.Config.view.element.DateTime`
    * `Shopware.apps.Config.view.element.Html`
    * `Shopware.apps.Config.view.element.Interval`
    * `Shopware.apps.Config.view.element.Number`
    * `Shopware.apps.Config.view.element.ProductBoxLayoutSelect`
    * `Shopware.apps.Config.view.element.Select`
    * `Shopware.apps.Config.view.element.SelectTree`
    * `Shopware.apps.Config.view.element.Text`
    * `Shopware.apps.Config.view.element.TextArea`
    * `Shopware.apps.Config.view.element.Time`
* Removed following unused dependencies
    * `egulias/email-validator`
    * `symfony/translation`
    * `php-http/curl-client`
    * `psr/link`
    * `symfony/polyfill-ctype`
    * `symfony/polyfill-iconv`
    * `symfony/polyfill-iconv`
    * `symfony/polyfill-php56`
    * `symfony/polyfill-php70`
    * `symfony/polyfill-php71`
    * `symfony/polyfill-php72`
* Removed field `size` from `Shopware\Models\Article\Download`. Use media_service to get the correct file size
* Removed plugin `Debug`

### Deprecations

* Deprecated the class `Shopware\Bundle\SitemapBundle\ConfigHandler\File`.
It will be removed in Shopware 5.8. Use `Shopware\Bundle\SitemapBundle\ConfigHandler\Database` instead.
* Deprecated getting plugin config from `Shopware_Components_Config` without plugin namespace, use `SwagTestPlugin:MyConfigName` instead
* Deprecated the class `\Shopware\Components\Plugin\DBALConfigReader`.
It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
* Deprecated the class `\Shopware\Components\Plugin\CachedConfigReader`.
It will be removed in Shopware 5.9. Use `Shopware\Components\Plugin\Configuration\ReaderInterface` instead
