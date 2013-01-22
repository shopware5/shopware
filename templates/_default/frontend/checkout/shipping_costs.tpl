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

    {foreach $sCountryList as $country}
       {if $country.states}
           <div {if $country.id != $sCountry.id}class="hidden"{/if}>
               <p></p>
               <p><label for="country_{$country.id}_states">{se name='RegisterBillingLabelState'}Bundesstaat:{/se} </label></p>

               <select {if $country.id != $sCountry.id}disabled="disabled"{/if} name="sState" id="country_{$country.id}_states" class="auto_submit text">
               <option value="" selected="selected">{s name='StateSelection'}Bitte wählen:{/s}</option>
                   {foreach from=$country.states item=state}
                       <option value="{$state.id}" {if $state.id eq $sState.id}selected="selected"{/if}>{$state.name}</option>
                   {/foreach}
               </select>
           </div>
       {/if}
   {/foreach}
</div>
{/block}

{* Payment method *}
{block name='frontend_checkout_shipping_costs_payment'}
<div class="basket_payment">
	<p>
		<label for="basket_payment_list">{se name="ShippingLabelPayment"}{/se}</label>
	</p>

	{foreach from=$sPayments item=payment}
		<div id="basket_payment_list">
			<input id="basket_payment{$payment.id}" type="radio" name="sPayment" value="{$payment.id}" {if $payment.id eq $sPayment.id}checked="checked"{/if} class="auto_submit" />
			<label for="basket_payment{$payment.id}">{$payment.description}</label>
		</div>
	{/foreach}
</div>
{/block}

{* Dispatch method *}
{block name='frontend_checkout_shipping_costs_dispatch'}
<div class="basket_dispatch">
	<p>
		<label for="basket_dispatch_list">{se name="ShipppingLabelDispatch"}{/se}</label>
	</p>
	{if $sDispatches}
	{foreach from=$sDispatches item=dispatch}
		<div>
			<input id="basket_dispatch{$dispatch.id}" type="radio" value="{$dispatch.id}" name="sDispatch" {if $dispatch.id eq $sDispatch.id}checked="checked"{/if} class="auto_submit">
			<label for="basket_dispatch{$dispatch.id}">{$dispatch.name}</label>
		</div>
	{/foreach}
	{/if}
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
