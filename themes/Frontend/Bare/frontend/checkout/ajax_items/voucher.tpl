{namespace name="frontend/checkout/ajax_cart"}

<div class="cart--item">
    {* image *}
    {block name='frontend_checkout_ajax_cart_cover'}
        <div class="thumbnail--container">

            {block name='frontend_checkout_ajax_cart_voucher_badge'}
                <div class="basket--badge">
                    <i class="icon--coupon"></i>
                </div>
            {/block}
        </div>
    {/block}

    {* actions *}
    {block name='frontend_checkout_ajax_cart_actions'}
        <div class="action--container">
            {$deleteUrl = {url action='removeLineItem' identifier=$lineItem.identifier sTargetAction=$sTargetAction}}

            <form action="{$deleteUrl}" method="post">
                <button type="submit" class="btn is--small action--remove" title="{s name="AjaxCartRemoveArticle"}{/s}">
                    <i class="icon--cross"></i>
                </button>
            </form>
        </div>
    {/block}

    {* label *}
    {block name='frontend_checkout_ajax_cart_articlename'}
        <div class="item--link">
            {block name="frontend_checkout_ajax_cart_articlename_quantity"}
                <span class="item--quantity">1x</span>
            {/block}

            {block name="frontend_checkout_ajax_cart_articlename_name"}
                <span class="item--name">
                    {if $theme.offcanvasCart}
                        {$lineItem.label|escapeHtml}
                    {else}
                        {$lineItem.label|truncate:28:"...":true|escapeHtml}
                    {/if}
                </span>
            {/block}

            {block name="frontend_checkout_ajax_cart_articlename_price"}
                <span class="item--price">{$lineItem.price.totalPrice|currency}</span>
            {/block}
        </div>
    {/block}
</div>