{* Constants for the different basket item types *}
{$IS_PRODUCT = constant('Shopware\Models\Article\Article::MODE_PRODUCT')}
{$IS_PREMIUM_PRODUCT = constant('Shopware\Models\Article\Article::MODE_PREMIUM_PRODUCT')}
{$IS_VOUCHER = constant('Shopware\Models\Article\Article::MODE_VOUCHER')}
{$IS_REBATE = constant('Shopware\Models\Article\Article::MODE_CUSTOMER_GROUP_DISCOUNT')}
{$IS_SURCHARGE_DISCOUNT = constant('Shopware\Models\Article\Article::MODE_PAYMENT_SURCHARGE_DISCOUNT')}

{if $sBasketItem.modus == $IS_PRODUCT}

    {* Product *}
    {block name='frontend_checkout_cart_item_product'}
        {include file="frontend/checkout/items/product.tpl" isLast=$isLast}
    {/block}
{elseif $sBasketItem.modus == $IS_PREMIUM_PRODUCT}

    {* Chosen premium products *}
    {block name='frontend_checkout_cart_item_premium_product'}
        {include file="frontend/checkout/items/premium-product.tpl" isLast=$isLast}
    {/block}
{elseif $sBasketItem.modus == $IS_VOUCHER}

    {* Voucher *}
    {block name='frontend_checkout_cart_item_voucher'}
        {include file="frontend/checkout/items/voucher.tpl" isLast=$isLast}
    {/block}
{elseif $sBasketItem.modus == $IS_REBATE}

    {* Basket rebate *}
    {block name='frontend_checkout_cart_item_rebate'}
        {include file="frontend/checkout/items/rebate.tpl" isLast=$isLast}
    {/block}
{elseif $sBasketItem.modus == $IS_SURCHARGE_DISCOUNT}

    {* Surcharge / discount *}
    {block name='frontend_checkout_cart_item_surcharge_discount'}
        {include file="frontend/checkout/items/rebate.tpl" isLast=$isLast}
    {/block}
{else}

    {* Register your own mode selection *}
    {block name='frontend_checkout_cart_item_additional_type'}{/block}
{/if}