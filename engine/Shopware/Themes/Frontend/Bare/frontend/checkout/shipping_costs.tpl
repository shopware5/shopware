<h2 class="headingbox largesize">{se name="ShippingHeader"}{/se}</h2>

<form id="recalcShipping" method="POST" action="{url action='calculateShippingCosts' sTargetAction=$sTargetAction}">

{* Delivery country *}
{block name='frontend_checkout_shipping_costs_country'}
	<div class="basket_country">
		<p>
			<label for="basket_country_list">{se name="ShippingLabelDeliveryCountry"}{/se}</label>
		</p>
		<select id="basket_country_list" name="sCountry" class="auto_submit">
			{foreach from=$sCountryList item=country}
				<option value="{$country.id}" {if $country.id eq $sCountry.id}selected{/if}>
					{$country.countryname}
				</option>
			{/foreach}
		</select>
	</div>

	{foreach $sCountryList as $country}
		{if $country.states}
			<div class="basket_country country_states {if $country.id != $sCountry.id}hidden{/if}">
				<p>
					<label for="country_{$country.id}_states">{se name='RegisterBillingLabelState'}{/se}</label>
				</p>

				<select {if $country.id != $sCountry.id}disabled="disabled"{/if} name="sState" id="country_{$country.id}_states" class="auto_submit text">
					<option value="" selected="selected">{s name='StateSelection'}{/s}</option>
					{foreach from=$country.states item=state}
						<option value="{$state.id}" {if $state.id eq $sState.id || $state.id eq $sState}selected="selected"{/if}>
							{$state.name}
						</option>
					{/foreach}
				</select>
			</div>
		{/if}
	{/foreach}
{/block}

{* Payment method *}
{block name='frontend_checkout_shipping_costs_payment'}
	<div class="basket_payment">
		<p>
			<label for="basket_payment_list">{se name="ShippingLabelPayment"}{/se}</label>
		</p>

		<select id="basket_payment_list" name="sPayment" class="auto_submit">
			{foreach from=$sPayments item=payment}
				<option value="{$payment.id}" {if $payment.id eq $sPayment.id}selected{/if}>
					{$payment.description}
				</option>
			{/foreach}
		</select>
	</div>
{/block}

{* Dispatch method *}
{block name='frontend_checkout_shipping_costs_dispatch'}
	<div class="basket_dispatch">
		<p>
			<label for="basket_dispatch_list">{se name="ShipppingLabelDispatch"}{/se}</label>
		</p>
		<select id="basket_dispatch_list" name="sDispatch" class="auto_submit">
		{if $sDispatches}
			{foreach from=$sDispatches item=dispatch}
				<option value="{$dispatch.id}" {if $dispatch.id eq $sDispatch.id}selected{/if}>
					{$dispatch.name}
				</option>
			{/foreach}
		{/if}
		</select>
	</div>
{/block}

</form>
{if $sDispatch.description}
<div class="basket_dispatch_description">
	<h3>{s name='DispatchHeadNotice'}{/s}</h3>
	<p>
		{$sDispatch.description}
	</p>
</div>
{/if}
