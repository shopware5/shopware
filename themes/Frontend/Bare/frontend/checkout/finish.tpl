{extends file="frontend/index/index.tpl"}

{* Hide sidebar left *}
{block name='frontend_index_content_left'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide breadcrumb *}
{block name='frontend_index_breadcrumb'}{/block}

{* Hide shop navigation *}
{block name='frontend_index_shop_navigation'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Step box *}
{block name='frontend_index_navigation_categories_top'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Hide top bar *}
{block name='frontend_index_top_bar_container'}
    {if !$theme.checkoutHeader}
        {$smarty.block.parent}
    {/if}
{/block}

{* Footer *}
{block name='frontend_index_footer'}
    {if !$theme.checkoutFooter}
        {$smarty.block.parent}
    {else}
        {block name='frontend_index_checkout_finish_footer'}
            {include file="frontend/index/footer_minimal.tpl"}
        {/block}
    {/if}
{/block}

{* Back to the shop button *}
{block name='frontend_index_logo_trusted_shops'}
    {$smarty.block.parent}
    {if $theme.checkoutHeader}
        {s name="FinishButtonBackToShop" assign="snippetFinishButtonBackToShop"}{/s}
        <a href="{url controller='index'}"
           class="btn is--small btn--back-top-shop is--icon-left"
           title="{$snippetFinishButtonBackToShop|escape}">
            <i class="icon--arrow-left"></i>
            {s name="FinishButtonBackToShop"}{/s}
        </a>
    {/if}
{/block}

{* Main content *}
{block name="frontend_index_content"}
    <div class="content checkout--content finish--content">

        {* Finish teaser message *}
        {block name='frontend_checkout_finish_teaser'}
            <div class="finish--teaser panel has--border is--rounded">

                {block name='frontend_checkout_finish_teaser_title'}
                    <h2 class="panel--title teaser--title is--align-center">{s name="FinishHeaderThankYou"}{/s} {$sShopname|escapeHtml}!</h2>
                {/block}

                {block name='frontend_checkout_finish_teaser_content'}
                    <div class="panel--body is--wide is--align-center">
                        {if $confirmMailDeliveryFailed}
                            {s name="FinishInfoConfirmationMailFailed" assign="snippetFinishInfoConfirmationMailFailed"}{/s}
                            {include file="frontend/_includes/messages.tpl" type="error" content=$snippetFinishInfoConfirmationMailFailed}
                        {/if}

                        <p class="teaser--text">
                            {if !$confirmMailDeliveryFailed}
                                {s name="FinishInfoConfirmationMail"}{/s}
                                <br />
                            {/if}

                            {s name="FinishInfoPrintOrder"}{/s}
                        </p>

                        {block name='frontend_checkout_finish_teaser_actions'}
                            <p class="teaser--actions">

                                {strip}
                                {* Back to the shop button *}
                                {s name="FinishButtonBackToShop" assign="snippetFinishButtonBackToShop"}{/s}
                                <a href="{url controller='index'}" class="btn is--secondary teaser--btn-back is--icon-left" title="{$snippetFinishButtonBackToShop|escape}">
                                    <i class="icon--arrow-left"></i>&nbsp;{$snippetFinishButtonBackToShop|replace:' ':'&nbsp;'}
                                </a>

                                {* Print button *}
                                {s name="FinishLinkPrint" assign="snippetFinishLinkPrint"}{/s}
                                <a href="#" class="btn is--primary teaser--btn-print" onclick="self.print()" title="{$snippetFinishLinkPrint|escape}">
                                    {s name="FinishLinkPrint"}{/s}
                                </a>
                                {/strip}
                            </p>

                            {* Print notice *}
                            {block name='frontend_checkout_finish_teaser_print_notice'}
                                <p class="print--notice">
                                    {s name="FinishPrintNotice"}{/s}
                                </p>
                            {/block}
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}

        {block name='frontend_checkout_finish_information_wrapper'}
            <div class="panel--group block-group information--panel-wrapper finish--info" data-panel-auto-resizer="true">

                {block name='frontend_checkout_finish_information_addresses'}

                    {if $sAddresses.equal}

                        {* Equal Billing & Shipping *}
                        {block name='frontend_checkout_finish_information_addresses_equal'}
                            <div class="information--panel-item information--panel-address">

                                {block name='frontend_checkout_finish_information_addresses_equal_panel'}
                                    <div class="panel has--border is--rounded block information--panel finish--billing">

                                        {block name='frontend_checkout_finish_information_addresses_equal_panel_title'}
                                            <div class="panel--title is--underline">
                                                {s name='ConfirmAddressEqualTitle' namespace="frontend/checkout/confirm"}{/s}
                                            </div>
                                        {/block}

                                        {block name='frontend_checkout_finish_information_addresses_equal_panel_body'}
                                            <div class="panel--body is--wide">

                                                {block name='frontend_checkout_finish_information_addresses_equal_panel_billing'}
                                                    <div class="billing--panel">
                                                        {if $sAddresses.billing.company}
                                                            <span class="address--company is--bold">{$sAddresses.billing.company|escapeHtml}</span>{if $sAddresses.billing.department}<br /><span class="address--department is--bold">{$sAddresses.billing.department|escapeHtml}</span>{/if}
                                                            <br />
                                                        {/if}

                                                        <span class="address--salutation">{$sAddresses.billing.salutation|salutation}</span>
                                                        {if {config name="displayprofiletitle"}}
                                                            <span class="address--title">{$sAddresses.billing.title|escapeHtml}</span><br/>
                                                        {/if}
                                                        <span class="address--firstname">{$sAddresses.billing.firstname|escapeHtml}</span> <span class="address--lastname">{$sAddresses.billing.lastname|escapeHtml}</span><br />
                                                        <span class="address--street">{$sAddresses.billing.street|escapeHtml}</span><br />
                                                        {if $sAddresses.billing.additional_address_line1}<span class="address--additional-one">{$sAddresses.billing.additional_address_line1|escapeHtml}</span><br />{/if}
                                                        {if $sAddresses.billing.additional_address_line2}<span class="address--additional-two">{$sAddresses.billing.additional_address_line2|escapeHtml}</span><br />{/if}
                                                        {if {config name=showZipBeforeCity}}
                                                            <span class="address--zipcode">{$sAddresses.billing.zipcode|escapeHtml}</span> <span class="address--city">{$sAddresses.billing.city|escapeHtml}</span>
                                                        {else}
                                                            <span class="address--city">{$sAddresses.billing.city|escapeHtml}</span> <span class="address--zipcode">{$sAddresses.billing.zipcode|escapeHtml}</span>
                                                        {/if}<br />
                                                        {if $sAddresses.billing.state.name}<span class="address--statename">{$sAddresses.billing.state.name|escapeHtml}</span><br />{/if}
                                                        <span class="address--countryname">{$sAddresses.billing.country.name|escapeHtml}</span>
                                                    </div>
                                                {/block}
                                            </div>
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/block}

                    {else}

                        {* Separate Billing & Shipping *}
                        {block name='frontend_checkout_finish_information_addresses_billing'}
                            <div class="information--panel-item information--panel-item-billing">
                                {* Billing address *}
                                {block name='frontend_checkout_finish_information_addresses_billing_panel'}
                                    <div class="panel has--border block information--panel billing--panel finish--billing">

                                        {* Headline *}
                                        {block name='frontend_checkout_confirm_information_addresses_billing_panel_title'}
                                            <div class="panel--title is--underline">
                                                {s name="ConfirmHeaderBilling" namespace="frontend/checkout/confirm"}{/s}
                                            </div>
                                        {/block}

                                        {* Content *}
                                        {block name='frontend_checkout_finish_information_addresses_billing_panel_body'}
                                            <div class="panel--body is--wide">
                                                {if $sAddresses.billing.company}
                                                    <span class="address--company is--bold">{$sAddresses.billing.company|escapeHtml}</span>{if $sAddresses.billing.department}<br /><span class="address--department is--bold">{$sAddresses.billing.department|escapeHtml}</span>{/if}
                                                    <br />
                                                {/if}

                                                <span class="address--salutation">{$sAddresses.billing.salutation|salutation}</span>
                                                {if {config name="displayprofiletitle"}}
                                                    <span class="address--title">{$sAddresses.billing.title|escapeHtml}</span><br/>
                                                {/if}
                                                <span class="address--firstname">{$sAddresses.billing.firstname|escapeHtml}</span> <span class="address--lastname">{$sAddresses.billing.lastname|escapeHtml}</span><br />
                                                <span class="address--street">{$sAddresses.billing.street|escapeHtml}</span><br />
                                                {if $sAddresses.billing.additional_address_line1}<span class="address--additional-one">{$sAddresses.billing.additional_address_line1|escapeHtml}</span><br />{/if}
                                                {if $sAddresses.billing.additional_address_line2}<span class="address--additional-two">{$sAddresses.billing.additional_address_line2|escapeHtml}</span><br />{/if}
                                                {if {config name=showZipBeforeCity}}
                                                    <span class="address--zipcode">{$sAddresses.billing.zipcode|escapeHtml}</span> <span class="address--city">{$sAddresses.billing.city|escapeHtml}</span>
                                                {else}
                                                    <span class="address--city">{$sAddresses.billing.city|escapeHtml}</span> <span class="address--zipcode">{$sAddresses.billing.zipcode|escapeHtml}</span>
                                                {/if}<br />
                                                {if $sAddresses.billing.state.name|escapeHtml}<span class="address--statename">{$sAddresses.billing.state.name|escapeHtml}</span><br />{/if}
                                                <span class="address--countryname">{$sAddresses.billing.country.name|escapeHtml}</span>
                                            </div>
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/block}

                        {block name='frontend_checkout_finish_information_addresses_shipping'}
                            <div class="information--panel-item information--panel-item-shipping">
                                {block name='frontend_checkout_finish_information_addresses_shipping_panel'}
                                    <div class="panel has--border block information--panel shipping--panel finish--shipping">

                                        {* Headline *}
                                        {block name='frontend_checkout_finish_information_addresses_shipping_panel_title'}
                                            <div class="panel--title is--underline">
                                                {s name="ConfirmHeaderShipping" namespace="frontend/checkout/confirm"}{/s}
                                            </div>
                                        {/block}

                                        {* Content *}
                                        {block name='frontend_checkout_finish_information_addresses_shipping_panel_body'}
                                            <div class="panel--body is--wide">
                                                {if $sAddresses.shipping.company}
                                                    <span class="address--company is--bold">{$sAddresses.shipping.company|escapeHtml}</span>{if $sAddresses.shipping.department}<br /><span class="address--department is--bold">{$sAddresses.shipping.department|escapeHtml}</span>{/if}
                                                    <br />
                                                {/if}

                                                <span class="address--salutation">{$sAddresses.shipping.salutation|salutation}</span>
                                                {if {config name="displayprofiletitle"}}
                                                    <span class="address--title">{$sAddresses.shipping.title|escapeHtml}</span><br/>
                                                {/if}
                                                <span class="address--firstname">{$sAddresses.shipping.firstname|escapeHtml}</span> <span class="address--lastname">{$sAddresses.shipping.lastname|escapeHtml}</span><br />
                                                <span class="address--street">{$sAddresses.shipping.street|escapeHtml}</span><br />
                                                {if $sAddresses.shipping.additional_address_line1}<span class="address--additional-one">{$sAddresses.shipping.additional_address_line1|escapeHtml}</span><br />{/if}
                                                {if $sAddresses.shipping.additional_address_line2}<span class="address--additional-two">{$sAddresses.shipping.additional_address_line2|escapeHtml}</span><br />{/if}
                                                {if {config name=showZipBeforeCity}}
                                                    <span class="address--zipcode">{$sAddresses.shipping.zipcode|escapeHtml}</span> <span class="address--city">{$sAddresses.shipping.city|escapeHtml}</span>
                                                {else}
                                                    <span class="address--city">{$sAddresses.shipping.city|escapeHtml}</span> <span class="address--zipcode">{$sAddresses.shipping.zipcode|escapeHtml}</span>
                                                {/if}<br />
                                                {if $sAddresses.shipping.state.name}<span class="address--statename">{$sAddresses.shipping.state.name|escapeHtml}</span><br />{/if}
                                                <span class="address--countryname">{$sAddresses.shipping.country.name|escapeHtml}</span>
                                            </div>
                                        {/block}
                                    </div>
                                {/block}
                            </div>
                        {/block}
                    {/if}
                {/block}

                {* Payment method *}
                {block name='frontend_checkout_finish_information_payment'}
                    <div class="information--panel-item">
                        {block name='frontend_checkout_finish_payment_method_panel'}
                            <div class="panel has--border block information--panel payment--panel finish--details">

                                {block name='frontend_checkout_finish_left_payment_method_headline'}
                                    <div class="panel--title is--underline payment--title">
                                        {s name="FinishHeaderInformation"}{/s}
                                    </div>
                                {/block}

                                {block name='frontend_checkout_finish_left_payment_content'}
                                    <div class="panel--body is--wide payment--content">

                                        {* Invoice number *}
                                        {block name='frontend_checkout_finish_invoice_number'}
                                            {if $sOrderNumber}
                                                <strong>{s name="FinishInfoId"}{/s}</strong> {$sOrderNumber}<br />
                                            {/if}
                                        {/block}

                                        {* Transaction number *}
                                        {block name='frontend_checkout_finish_transaction_number'}
                                            {if $sTransactionumber}
                                                <strong>{s name="FinishInfoTransaction"}{/s}</strong> {$sTransactionumber}<br />
                                            {/if}
                                        {/block}

                                        {* Payment method *}
                                        {block name='frontend_checkout_finish_payment_method'}
                                            {if $sPayment.description}
                                                <strong>{s name="ConfirmHeaderPayment" namespace="frontend/checkout/confirm"}{/s}:</strong> {$sPayment.description}<br />
                                            {/if}
                                        {/block}

                                        {* Dispatch method *}
                                        {block name='frontend_checkout_finish_dispatch_method'}
                                            {if $sDispatch.name}
                                                <strong>{s name="CheckoutDispatchHeadline" namespace="frontend/checkout/confirm_dispatch"}{/s}:</strong> {$sDispatch.name}
                                            {/if}
                                        {/block}

                                    </div>
                                {/block}

                            </div>
                        {/block}
                    </div>
                {/block}
            </div>
        {/block}

        {block name='frontend_checkout_finish_items'}
            <div class="finish--table product--table">
                <div class="panel has--border">
                    <div class="panel--body is--rounded">

                        {* Table header *}
                        {block name='frontend_checkout_finish_table_header'}
                            {include file="frontend/checkout/finish_header.tpl"}
                        {/block}

                        {* Article items *}
                        {foreach $sBasket.content as $key => $sBasketItem}
                            {block name='frontend_checkout_finish_item'}
                                {include file='frontend/checkout/finish_item.tpl' isLast=$sBasketItem@last}
                            {/block}
                        {/foreach}

                        {* Table footer *}
                        {block name='frontend_checkout_finish_table_footer'}
                            {include file="frontend/checkout/finish_footer.tpl"}
                        {/block}
                    </div>
                </div>
            </div>
        {/block}
    </div>
{/block}
