{block name='frontend_checkout_cart_item_image' prepend}
	{if $sBasketItem.attribute.bundleId > 0}
		<span class="item_bundle"><span>&nbsp;</span></span>
	{/if}
{/block}


{block name='frontend_checkout_cart_item_bundle_details'}
    <div class="{$checkoutBundleTemplate}">
        <div class="basket_details">
            <strong class="title">{s name='CartItemInfoBundle' namespace="frontend/checkout/cart_item"}{/s}</strong>
        </div>
        <div class="clear">&nbsp;</div>
    </div>
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
	{/if}
</div>
{/block}
