{extends file='parent:frontend/checkout/cart_footer.tpl'}

{block name='frontend_checkout_cart_footer_left'}{/block}

{* Field labels *}
{block name='frontend_checkout_cart_footer_field_labels'}
<div id="aggregation_left" class="grid_4">
	<p>
		<strong>{se name="CartFooterSum"}{/se}</strong>
	</p>
	<div class="border">
	<p>
		<strong>{se name="CartFooterShipping"}{/se}</strong>
	</p>
	</div>
	<div class="totalamount border">
		<p>
			<strong>{se name="CartFooterTotal"}{/se}</strong>
		</p>
	</div>
	{if $sUserData.additional.charge_vat}
	<div class="tax">
	<p>
		<strong>{se name="CartFooterTotalNet"}{/se}</strong>
	</p>
	</div>
	{foreach $sBasket.sTaxRates as $rate=>$value}
		<div>
		<p>
			<strong>{se name="CartFooterTotalTax"}{/se}</strong>
		</p>
	</div>
	{/foreach}
	{/if}
</div>
{/block}


{* Shipping costs *}
{block name='frontend_checkout_cart_footer_shipping_costs'}
<div class="border">
	<p class="textright">
		<strong>{$sShippingcosts|currency}*</strong>
	</p>
</div>
{/block}
