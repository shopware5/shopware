{namespace name="frontend/checkout/ajax_cart"}

{* Constants for the different basket item types *}
{$IS_PRODUCT = 0}
{$IS_PREMIUM_PRODUCT = 1}
{$IS_VOUCHER = 2}
{$IS_REBATE = 3}
{$IS_SURCHARGE_DISCOUNT = 4}

{if $sBasketItem.additional_details.sConfigurator}
    {$detailLink={url controller=detail sArticle=$sBasketItem.articleID number=$sBasketItem.ordernumber}}
{else}
    {$detailLink=$sBasketItem.linkDetails}
{/if}

<div class="cart--item{if $basketItem.modus == 1} is--premium-article{/if}">
    {* Article image *}
    {block name='frontend_checkout_ajax_cart_articleimage'}
        <div class="thumbnail--container{if $basketItem.image.thumbnails[0]} has--image{/if}">

            {* Real product *}
            {block name='frontend_checkout_ajax_cart_articleimage_product'}
                {if $basketItem.modus == $IS_PRODUCT || $basketItem.modus == $IS_PREMIUM_PRODUCT}
                    {$desc = $basketItem.articlename|escape}
                    {if $basketItem.additional_details.image.thumbnails}
                        {if $basketItem.additional_details.image.description}
                            {$desc = $basketItem.additional_details.image.description|escape}
                        {/if}
                        <img srcset="{$basketItem.additional_details.image.thumbnails[0].sourceSet}" alt="{$desc}" title="{$desc|truncate:160}" class="thumbnail--image" />

                    {elseif $basketItem.image.src.0}
                        <img src="{$basketItem.image.src.0}" alt="{$desc}" title="{$desc|truncate:160}" class="thumbnail--image" />
                    {/if}
                {/if}
            {/block}

            {* Premium article product badge *}
            {block name='frontend_checkout_ajax_cart_articleimage_badge_premium'}
                {if $basketItem.modus == $IS_PREMIUM_PRODUCT}
                    <span class="cart--badge">
                        <span class="badge--free">{s name='AjaxCartInfoFree'}{/s}</span>
                    </span>
                {/if}
            {/block}

            {* Cart surcharge discount *}
            {block name='frontend_checkout_ajax_cart_articleimage_badge_surcharge'}
                {if $basketItem.modus == $IS_SURCHARGE_DISCOUNT}
                    <div class="basket--badge">
                        {if $sBasketItem.price >= 0}
                            <i class="icon--arrow-right"></i>
                        {else}
                            <i class="icon--percent2"></i>
                        {/if}
                    </div>
                {/if}
            {/block}

            {* Voucher *}
            {block name='frontend_checkout_ajax_cart_articleimage_badge_voucher'}
                {if $basketItem.modus == $IS_VOUCHER}
                    <div class="basket--badge">
                        <i class="icon--coupon"></i>
                    </div>
                {/if}
            {/block}

            {* Rebate article *}
            {block name='frontend_checkout_ajax_cart_articleimage_badge_rebate'}
                {if $basketItem.modus == $IS_REBATE}
                    <div class="basket--badge">
                        {if $sBasketItem.price >= 0}
                            <i class="icon--arrow-right"></i>
                        {else}
                            <i class="icon--percent2"></i>
                        {/if}
                    </div>
                {/if}
            {/block}
        </div>
    {/block}

    {* Article actions *}
    {block name='frontend_checkout_ajax_cart_actions'}
        <div class="action--container">
            {$deleteUrl = {url controller="checkout" action="ajaxDeleteArticleCart" sDelete=$basketItem.id}}

            {if $basketItem.modus == 2}
                {$deleteUrl = {url controller="checkout" action="ajaxDeleteArticleCart" sDelete="voucher"}}
            {/if}

            {if $basketItem.modus != 4 && $basketItem.modus != 3}
                <form action="{$deleteUrl}" method="post">
                    <button type="submit" class="btn is--small action--remove" title="{s name="AjaxCartRemoveArticle"}{/s}">
                        <i class="icon--cross"></i>
                    </button>
                </form>
            {/if}
        </div>
    {/block}

    {* Article name *}
    {block name='frontend_checkout_ajax_cart_articlename'}
        {$useAnchor = ($basketItem.modus != 4 && $basketItem.modus != 2 && $basketItem.modus != 3)}
        {if $useAnchor}
            <a class="item--link" href="{$detailLink}" title="{$basketItem.articlename|escapeHtml}">
        {else}
            <div class="item--link">
        {/if}
            {block name="frontend_checkout_ajax_cart_articlename_quantity"}
                <span class="item--quantity">{$basketItem.quantity}{s name="AjaxCartQuantityTimes"}{/s}</span>
            {/block}
            {block name="frontend_checkout_ajax_cart_articlename_name"}
                <span class="item--name">
                    {if $theme.offcanvasCart}
                        {$basketItem.articlename|escapeHtml}
                    {else}
                        {$basketItem.articlename|truncate:28:"...":true|escapeHtml}
                    {/if}
                </span>
            {/block}
            {block name="frontend_checkout_ajax_cart_articlename_price"}
                <span class="item--price">{if $basketItem.amount}{$basketItem.amount|currency}{else}{s name="AjaxCartInfoFree"}{/s}{/if}{s name="Star"}{/s}</span>
            {/block}
        {if $useAnchor}
            </a>
        {else}
            </div>
        {/if}

        {block name='frontend_checkout_ajax_cart_essential_features'}
            {if {config name=alwaysShowMainFeatures}}
                <div class="product--essential-features">
                    {include file="string:{config name=mainfeatures}"}
                </div>
            {/if}
        {/block}
    {/block}
</div>
