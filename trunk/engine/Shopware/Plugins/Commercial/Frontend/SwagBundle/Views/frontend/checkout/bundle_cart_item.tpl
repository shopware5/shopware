{block name='frontend_checkout_cart_item_image' prepend}
	{if $sBasketItem.attribute.bundleId > 0}
		<span class="item_bundle"><span>&nbsp;</span></span>
	{/if}
{/block}

{* Article amount *}
{block name='frontend_checkout_cart_item_quantity'}
<div class="grid_1">
	{if $sBasketItem.modus == 0}
	
	{if $sBasketItem.attribute.bundleId > 0}
		{$sBasketItem.quantity}
	{else}
	
		<select name="sQuantity" class="auto_submit">
		{section name="i" start=$sBasketItem.minpurchase loop=$sBasketItem.maxpurchase+1 step=$sBasketItem.purchasesteps}
			<option value="{$smarty.section.i.index}" {if $smarty.section.i.index==$sBasketItem.quantity}selected="selected"{/if}>
					{$smarty.section.i.index} 
			</option>
		{/section}
		</select>
		<input type="hidden" name="sArticle" value="{$sBasketItem.id}" />
	
	{/if}
	{else}
		&nbsp;
	{/if}
</div>
{/block}
