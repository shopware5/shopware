{block name='frontend_checkout_cart_item_small_quantities_details'}
{if $sBasketItem.ordernumber !='sw-payment-absolute' && $sBasketItem.ordernumber !='sw-payment'}
    <div class="grid_6">
        <div class="basket_details">
            <strong class="title">{$sBasketItem.articlename}</strong>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
{else}
    {$smarty.block.parent}
{/if}
{/block}
{* Tax price *}
{block name='frontend_checkout_cart_item_small_quantites_tax_price'}{/block}
{block name='frontend_checkout_Cart_item_small_quantities_price'}
//{if $sBasketItem.ordernumber !='sw-payment-absolute' && $sBasketItem.ordernumber !='sw-payment'}
<div class="grid_3 push_5">
    <div class="textright">
        <strong>
            {if $sBasketItem.itemInfo}
                {$sBasketItem.itemInfo}
            {else}
                {$sBasketItem.price|currency} {block name='frontend_checkout_cart_tax_symbol'}*{/block}
            {/if}
        </strong>
    </div>
    <div class="clear">&nbsp;</div>
</div>
//{else}
{$smarty.block.parent}
{/if}
    {/block}