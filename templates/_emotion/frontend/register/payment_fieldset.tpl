

<div class="payment_method">
	<h2 class="headingbox_dark largesize">{s name='RegisterPaymentHeadline'}{/s}</h2>
	{foreach from=$payment_means item=payment_mean name=register_payment_mean}
		<div class="grid_15 {if $smarty.foreach.register_payment_mean.last}method_last{else}method{/if}">
			{block name='frontend_register_payment_fieldset_input_radio'}
			<div class="grid_5 first">
			<input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
			</div>
			{/block}
			
			{block name='frontend_register_payment_fieldset_description'}
			<div class="grid_10 last">
                {include file="string:{$payment_mean.additionaldescription}"}
			</div>
			{/block}
			
			{block name='frontend_register_payment_fieldset_template'}
			<div class="payment_logo_{$payment_mean.name}"></div>
			{if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
				<div class="space">&nbsp;</div>
				<div class="grid_8 bankdata">
					{include file="frontend/plugins/payment/`$payment_mean.template`"}
				</div>
			{/if}
			{/block}
		</div>
	{/foreach}
	<div class="space">&nbsp;</div>
</div>
