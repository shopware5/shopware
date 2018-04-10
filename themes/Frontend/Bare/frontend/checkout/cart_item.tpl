{* Constants for the different basket item types *}
{$IS_PRODUCT = 0}
{$IS_PREMIUM_PRODUCT = 1}
{$IS_VOUCHER = 2}
{$IS_REBATE = 3}
{$IS_SURCHARGE_DISCOUNT = 4}
{$path = ''}

{if $sBasketItem.modus == $IS_PRODUCT}
    {$path = "frontend/checkout/cart_item_product.tpl"}
{elseif $sBasketItem.modus == $IS_PREMIUM_PRODUCT}
    {$path = "frontend/checkout/cart_item_premium_product.tpl"}
{elseif $sBasketItem.modus == $IS_VOUCHER}
    {$path = "frontend/checkout/cart_item_voucher.tpl"}
{elseif $sBasketItem.modus == $IS_REBATE}
    {$path = "frontend/checkout/cart_item_rebate.tpl"}
{elseif $sBasketItem.modus == $IS_SURCHARGE_DISCOUNT}
    {$path = "frontend/checkout/cart_item_surcharge_discount.tpl"}
{else}
    {* Register your own mode selection *}
    {block name='frontend_checkout_cart_item_additional_type'}{/block}
{/if}

{if $path != ''}
    {include $path}
{/if}