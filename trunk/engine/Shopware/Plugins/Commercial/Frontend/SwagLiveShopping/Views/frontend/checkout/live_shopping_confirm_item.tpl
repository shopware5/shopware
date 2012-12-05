{block name='frontend_checkout_cart_item_image' prepend}
	{if $sBasketItem.swagLiveShoppingId > 0}
		<span class="checkout_item_live_shopping"><span>&nbsp;</span></span>
	{/if}
{/block}

{* Article amount *}
{block name='frontend_checkout_cart_item_quantity'}
    <div class="grid_1">
        {if $sBasketItem.swagLiveShoppingId > 0}
            {$sBasketItem.quantity}
        {else}
             {$smarty.block.parent}
        {/if}
    </div>
{/block}
