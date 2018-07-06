{* Constants for the different basket item types *}
{$IS_PRODUCT = 0}
{$IS_PREMIUM_PRODUCT = 1}
{$IS_VOUCHER = 2}
{$IS_REBATE = 3}
{$IS_SURCHARGE_DISCOUNT = 4}
{$path = ''}

{if $sBasketItem.modus == $IS_PRODUCT}
    {block name="frontend_checkout_finish_item_product_wrapper"}
        {$path = 'frontend/checkout/finish_item_product.tpl'}
    {/block}
{elseif $sBasketItem.modus == $IS_PREMIUM_PRODUCT}
    {block name="frontend_checkout_finish_item_premium_product_wrapper"}
        {$path = 'frontend/checkout/finish_item_premium_product.tpl'}
    {/block}
{elseif $sBasketItem.modus == $IS_VOUCHER}
    {block name="frontend_checkout_finish_item_voucher_wrapper"}
        {$path = 'frontend/checkout/finish_item_voucher.tpl'}
    {/block}
{elseif $sBasketItem.modus == $IS_REBATE}
    {block name="frontend_checkout_finish_item_voucher_rebate_wrapper"}
        {$path = 'frontend/checkout/cart_item_rebate.tpl'}
    {/block}
{elseif $sBasketItem.modus == $IS_SURCHARGE_DISCOUNT}
    {block name="frontend_checkout_finish_item_discount_wrapper"}
        {$path = 'frontend/checkout/cart_item_surcharge_discount.tpl'}
    {/block}
{else}
    {* Register your own mode selection *}
    {block name="frontend_checkout_finish_item_additional_type_wrapper"}
        {block name='frontend_checkout_cart_item_additional_type'}{/block}
    {/block}
{/if}

{if $path != ''}
    {include file="$path" isLast=$isLast}
{/if}