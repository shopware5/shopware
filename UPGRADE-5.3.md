# CHANGELOG for Shopware 5.3.x

This changelog references changes done in Shopware 5.3 patch versions.

## 5.3.0

[View all changes from v5.2.6...v5.3.0](https://github.com/shopware/shopware/compare/v5.2.6...v5.3.0)

* The Forms.php variable {$sShopname} was removed, use {sShopname} in your form templates instead.
* Fixed theme path for new plugins from `/resources` into `/Resources`
* Added `Shopware.grid.Panel` configuration `displayProgressOnSingleDelete` to hide progress window on single delete action.
* Added config flag `displayOnlySubShopVotes` to display only shop assigned article votes.
* Fixed sorting of `Shopware.listing.FilterPanel` fields.
* Add field `expression` parameter in `Shopware.listing.FilterPanel` to allow define own query expressions
* Added `Shopware.model.Container` config `splitFields` to configure field set column layout.
* Removed article vote module files
    * `themes/Backend/ExtJs/backend/vote/view/vote/detail.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/edit.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/infopanel.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/list.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/toolbar.js`
    * `themes/Backend/ExtJs/backend/vote/view/vote/window.js`
    * `themes/Backend/ExtJs/backend/vote/controller/vote.js`
    * `themes/Backend/ExtJs/backend/vote/controller/vote.js`
* `s_articles_vote.answer_date` is now nullable
* Removed legacy import / export module
* Deprecated `Shopware_Components_Convert_Csv`
* Deprecated `Shopware_Components_Convert_Xml`
* Deprecated `Shopware_Components_Convert_Excel`
* Removed database columns `s_emarketing_lastarticles`.`articleName` and `s_emarketing_lastarticles`.`img`.
* Removed plugin `LastArticle`, use `shopware.components.last_articles_subscriber` instead.
* Changed `LastArticle` plugin config elements to be prefixed with `lastarticles_`. This includes `show`, `controller` and `time`.
* Removed session key `sLastArticle`
* Removed `sLastActiveArticle` from basket
* Removed block `frontend_checkout_actions_link_last`
* Removed snippet `frontend/checkout/actions/CheckoutActionsLinkLast`
* Captchas are now configurable via backend
* You can now create own captcha modules. See `Shopware\Components\Captcha`.
* Removed `<meta name="fragment" content="!">` and `hasEscapedFragment`