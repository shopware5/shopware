{block name='frontend_checkout_finish_item_image' prepend}
	{if $sBasketItem.attribute.bundleId > 0}
		<span class="item_bundle"><span>&nbsp;</span></span>
	{/if}
{/block}