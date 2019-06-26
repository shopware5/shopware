{block name='frontend_checkout_ajax_cart'}
    <div class="ajax--cart">
        {block name='frontend_checkout_ajax_cart_buttons_offcanvas'}
            <div class="buttons--off-canvas">
                {block name='frontend_checkout_ajax_cart_buttons_offcanvas_inner'}
                    <a href="#close-categories-menu" class="close--off-canvas">
                        <i class="icon--arrow-left"></i>
                        {s name="AjaxCartContinueShopping"}{/s}
                    </a>
                {/block}
            </div>
        {/block}

        {block name='frontend_checkout_ajax_cart_alert_box'}
            {if $theme.offcanvasCart}
                {if $basketInfoMessage}
                    <div class="alert is--info is--rounded is--hidden">
                        <div class="alert--icon">
                            <div class="icon--element icon--info"></div>
                        </div>
                        <div class="alert--content">{$basketInfoMessage}</div>
                    </div>
                {else}
                    <div class="alert is--success is--rounded is--hidden">
                        <div class="alert--icon">
                            <div class="icon--element icon--check"></div>
                        </div>
                        <div class="alert--content">{s name="AjaxCartSuccessText" namespace="frontend/checkout/ajax_cart"}{/s}</div>
                    </div>
                {/if}
            {/if}
        {/block}

        {block name='frontend_checkout_ajax_cart_product_removed_with_invalid_category'}
            {if $sInvalidCartItems}
                <div class="alert is--warning is--rounded">
                    <div class="alert--icon">
                        <div class="icon--element icon--info"></div>
                    </div>
                    <div class="alert--content">{s name="InvalidCategoryMessage" namespace="frontend/checkout/error_messages"}{/s}</div>
                </div>
            {/if}
        {/block}

        {block name='frontend_checkout_ajax_cart_product_removed_with_invalid_voucher'}
            {if $sBasketVoucherRemovedInCart}
                <div class="alert is--warning is--rounded has--close">
                    <a class="alert--close icon--cross" data-notification-message-close="true" data-link="{url removeMessage="voucher"}" title="{s name="Hide" namespace="frontend"}{/s}"></a>
                    <div class="alert--icon">
                        <div class="icon--element icon--info"></div>
                    </div>
                    <div class="alert--content">{s name="InvalidVoucherGotRemoved" namespace="frontend/checkout/error_messages"}{/s}</div>
                </div>
            {/if}
        {/block}

        {block name='frontend_checkout_ajax_cart_item_container'}
            <div class="item--container">
                {block name='frontend_checkout_ajax_cart_item_container_inner'}
                    {if $sBasket.content}
                        {foreach $sBasket.content as $sBasketItem}
                            {block name='frontend_checkout_ajax_cart_row'}
                                {include file="frontend/checkout/ajax_cart_item.tpl" basketItem=$sBasketItem}
                            {/block}
                        {/foreach}
                    {else}
                        {block name='frontend_checkout_ajax_cart_empty'}
                            <div class="cart--item is--empty">
                                {block name='frontend_checkout_ajax_cart_empty_inner'}
                                    <span class="cart--empty-text">{s name='AjaxCartInfoEmpty'}{/s}</span>
                                {/block}
                            </div>
                        {/block}
                    {/if}
                {/block}
            </div>
        {/block}

        {block name='frontend_checkout_ajax_cart_prices_container'}
            {if $sBasket.content}
                {if {config name=showShippingCostsOffCanvas} == 0}
                    {block name='frontend_checkout_ajax_cart_prices_container_without_shipping_costs'}
                        <div class="prices--container">
                            {block name='frontend_checkout_ajax_cart_prices_container_inner'}
                                <div class="prices--articles">
                                    <span class="prices--articles-text">{s name="AjaxCartTotalAmount"}{/s}</span>
                                    <span class="prices--articles-amount">{$sBasket.Amount|currency}</span>
                                </div>
                            {/block}
                            {block name='frontend_checkout_ajax_cart_prices_info'}
                                <p class="prices--tax">
                                    {s name="DetailDataPriceInfo" namespace="frontend/detail/data"}{/s}
                                </p>
                            {/block}
                        </div>
                    {/block}
                {else}
                    {block name='frontend_checkout_ajax_cart_prices_container_with_shipping_costs'}
                        <div class="prices--container">
                            {block name='frontend_checkout_ajax_cart_prices_container_inner'}
                                <div class="small--information">
                                    <span>{s name="AjaxCartTotalAmount"}{/s}</span>
                                    <span class="small--prices">{$sBasket.Amount|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}</span>
                                </div>
                            {/block}
                            {* Shipping costs & Shipping costs pre-calculation *}
                            {if {config name=showShippingCostsOffCanvas} == 1}
                                {block name='frontend_checkout_shipping_costs_country_trigger'}
                                    {if !$sUserLoggedIn && !$sUserData.additional.user.id}
                                        <a href="#show-hide--shipping-costs" class="table--shipping-costs-trigger">
                                            {s name='CheckoutFooterEstimatedShippingCosts' namespace="frontend/checkout/cart_footer"}{/s}
                                            <i class="icon--arrow-right"></i>
                                        </a>
                                    {else}
                                        <span class="table--shipping-costs-trigger-no-action">
                                            {s name='CheckoutFooterEstimatedShippingCosts' namespace="frontend/checkout/cart_footer"}{/s}:
                                        </span>
                                    {/if}
                                    <span class="small--information">
                                        <span class="small--prices"> {$sShippingcosts|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                                        </span>
                                    </span>
                                {/block}
                                {block name='frontend_checkout_shipping_costs_country_include'}
                                    {include file="frontend/checkout/shipping_costs.tpl" calculateShippingCosts=$showShippingCalculation}
                                {/block}
                            {/if}
                            {if {config name=showShippingCostsOffCanvas} == 2}
                                {block name='frontend_checkout_shipping_costs_country_include'}
                                    <div class="small--information">
                                        <span>{s name='CheckoutFooterEstimatedShippingCosts' namespace="frontend/checkout/cart_footer"}{/s}</span>
                                        <span class="small--prices"> {$sShippingcosts|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                                        </span>
                                    </div>
                                    {include file="frontend/checkout/shipping_costs.tpl" calculateShippingCosts=true}
                                {/block}
                            {/if}
                            {* Total sum *}
                            {block name='frontend_checkout_cart_footer_field_labels_total'}
                                <div class="prices--articles">
                                    <span class="prices--articles-text">{s name="CartFooterLabelTotal" namespace="frontend/checkout/cart_footer"}{/s}</span>
                                    <span class="prices--articles-amount">
                                        {$sAmount|currency}{s name="Star" namespace="frontend/listing/box_article"}{/s}
                                    </span>
                                </div>
                            {/block}
                            {block name='frontend_checkout_ajax_cart_prices_info'}
                                <p class="prices--tax">
                                    {s name="Star" namespace="frontend/listing/box_article"}{/s}{s name="AjaxDetailDataPriceInfo"}{/s}
                                </p>
                            {/block}
                        </div>
                    {/block}
                {/if}
            {/if}
        {/block}

        {* Basket link *}
        {block name='frontend_checkout_ajax_cart_button_container'}
            <div class="button--container">
                {block name='frontend_checkout_ajax_cart_button_container_inner'}
                    {block name='frontend_checkout_ajax_cart_open_checkout'}
                        {s name="AjaxCartLinkConfirm" assign="snippetAjaxCartLinkConfirm"}{/s}
                        {s name="AjaxCartLinkBasket" assign="snippetAjaxCartLinkBasket"}{/s}
                        {if $sBasket.content}
                            {* There is something in the basket *}
                            {if !($sDispatchNoOrder && !$sDispatches) && !$sInvalidCartItems}
                                {block name='frontend_checkout_ajax_cart_open_checkout_inner'}
                                    <a href="{if {config name=always_select_payment}}{url controller='checkout' action='shippingPayment'}{else}{url controller='checkout' action='confirm'}{/if}" class="btn is--primary button--checkout is--icon-right" title="{$snippetAjaxCartLinkConfirm|escape}">
                                        <i class="icon--arrow-right"></i>
                                        {s name='AjaxCartLinkConfirm'}{/s}
                                    </a>
                                {/block}
                            {else}
                                {block name='frontend_checkout_ajax_cart_open_checkout_inner_disabled'}
                                    {s name="AjaxCartLinkConfirm" assign="snippetAjaxCartLinkConfirm"}{/s}
                                    <span class="btn is--disabled is--primary button--checkout is--icon-right" title="{$snippetAjaxCartLinkConfirm|escape}">
                                        <i class="icon--arrow-right"></i>
                                        {s name='AjaxCartLinkConfirm'}{/s}
                                    </span>
                                {/block}
                            {/if}
                            {block name='frontend_checkout_ajax_cart_open_basket'}
                                <a href="{url controller='checkout' action='cart'}" class="btn button--open-basket is--icon-right" title="{$snippetAjaxCartLinkBasket|escape}">
                                    <i class="icon--arrow-right"></i>
                                    {s name='AjaxCartLinkBasket'}{/s}
                                </a>
                            {/block}
                        {else}
                            {* The Basket is empty *}
                            {block name='frontend_checkout_ajax_cart_open_checkout_inner_unavailable'}
                                <span class="btn is--primary button--checkout is--icon-right is--disabled" title="{$snippetAjaxCartLinkConfirm|escape}">
                                    <i class="icon--arrow-right"></i>
                                    {s name='AjaxCartLinkConfirm'}{/s}
                                </span>
                            {/block}
                            {block name='frontend_checkout_ajax_cart_open_basket_unavailable'}
                                <span class="btn button--open-basket is--icon-right is--disabled" title="{$snippetAjaxCartLinkBasket|escape}">
                                    <i class="icon--arrow-right"></i>
                                    {s name='AjaxCartLinkBasket'}{/s}
                                </span>
                            {/block}
                        {/if}
                    {/block}
                {/block}
            </div>
        {/block}
    </div>
{/block}
