<div class="table_foot">
	{block name='frontend_checkout_cart_footer_tax_information'}{/block}

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
	
	{* Aggregation *}
	<div id="aggregation" class="grid_2">
		
		{* Basket sum *}
		{block name='frontend_checkout_cart_footer_basket_sum'}
		<p class="textright">
			<strong>{$sBasket.Amount|currency}*</strong>
		</p>
		{/block}
		
		{* Shipping costs *}
		{block name='frontend_checkout_cart_footer_shipping_costs'}
			<div class="border">
				<p class="textright">
					<strong>{$sShippingcosts|currency}*</strong>
				</p>
			</div>
		{/block}
		
		{* Total sum *}
		{block name='frontend_checkout_cart_footer_total_sum'}
		<div class="totalamount border">
			<p class="textright">
				<strong>
					{if $sAmountWithTax && $sUserData.additional.charge_vat}{$sAmountWithTax|currency}{else}{$sAmount|currency}{/if}
				</strong>
			</p>
		</div>
		{/block}


		{* Total net *}
		{block name='frontend_checkout_cart_footer_total_net'}
		{if $sUserData.additional.charge_vat}
		<div class="tax">
		<p class="textright">
			<strong>{$sAmountNet|currency}</strong>
		</p>
		</div>
		{/if}
		{/block}
		
		{* Total tax *}
		{block name='frontend_checkout_cart_footer_tax_rates'}
		{if $sUserData.additional.charge_vat}
			{foreach $sBasket.sTaxRates as $rate=>$value}
			<div>
				<p class="textright">
					<strong>{$value|currency}</strong>
				</p>
			</div>
			{/foreach}
		{/if}
		{/block}
	</div>
	<div class="clear">&nbsp;</div>
</div>