
{extends file='frontend/checkout/cart_header.tpl'}

{* Article tax *}
{block name='frontend_checkout_cart_header_price'}{/block}

{* Article tax *}
{block name='frontend_checkout_cart_header_tax'}
<div class="charge_vat grid_2">
{if $sUserData.additional.charge_vat && !$sUserData.additional.show_net}
	{se name='CheckoutColumnExcludeTax'}{/se}
{elseif $sUserData.additional.charge_vat}
	{se name='CheckoutColumnTax'}{/se}
{else}&nbsp;{/if}
</div>
{/block}

{block name='frontend_checkout_cart_header_total'}
<div class="grid_2">
	<div class="textright">
		{se name="CartColumnTotal"}{/se}
	</div>
</div>
{/block}
