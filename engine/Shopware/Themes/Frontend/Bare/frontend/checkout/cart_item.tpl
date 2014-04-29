{* Constants for the different basket item types *}
{$IS_PRODUCT = 0}
{$IS_PREMIUM_PRODUCT = 1}
{$IS_VOUCHER = 2}
{$IS_REBATE = 3}
{$IS_SURCHARGE_DISCOUNT = 4}

{if $sBasketItem.modus == $IS_PRODUCT}

    {* Product *}
    {include file="frontend/checkout/items/product.tpl"}

{elseif $sBasketItem.modus == $IS_PREMIUM_PRODUCT}

    {* Chosen premium products *}
    {include file="frontend/checkout/items/premium-product.tpl"}
{elseif $sBasketItem.modus == $IS_VOUCHER}

    {* Voucher *}
    {include file="frontend/checkout/items/voucher.tpl"}

{elseif $sBasketItem.modus == $IS_REBATE}

    {* Basket rebate *}
    {include file="frontend/checkout/items/rebate.tpl"}
{elseif $sBasketItem.modus == $IS_SURCHARGE_DISCOUNT}

    {* Surcharge / discount *}
    {include file="frontend/checkout/items/rebate.tpl"}
{else}

    {* Register your own mode selection *}
    {block name='frontend_checkout_cart_additional_type'}{/block}
{/if}