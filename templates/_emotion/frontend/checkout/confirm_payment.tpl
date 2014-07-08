
{if $sRegisterFinished}
<div class="space"></div>
<form name="" method="POST" action="{url controller=account action=savePayment sTarget='checkout'}" class="payment">
<div class="payment_method">
	<h3 class="underline">{s name='CheckoutPaymentHeadline'}Zahlungsart{/s}</h3>
	
	{foreach from=$sPayments item=payment_mean name=register_payment_mean}
		<div class="grid_15 method">
			{block name='frontend_checkout_payment_fieldset_input_radio'}
            {if !{config name='IgnoreAGB'}}
                <input type="hidden" class="agb-checkbox" name="sAGB" value="{if $sAGBChecked}1{else}0{/if}" />
            {/if}
			<input type="hidden" name="sourceCheckoutConfirm" value="1" />
			<div class="grid_5 first">
				<input type="radio" name="register[payment]" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sPayment.id} checked="checked"{/if} />
				<label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
			</div>
			{/block}
			
			{block name='frontend_checkout_payment_fieldset_description'}
			<div class="grid_10 last">
				{include file="string:{$payment_mean.additionaldescription}"}
			</div>
			{/block}
			
			{block name='frontend_checkout_payment_fieldset_template'}
			<div class="payment_logo_{$payment_mean.name}"></div>
			{if "frontend/plugins/payment/show_`$payment_mean.template`"|template_exists && !{config name=paymentEditingInCheckoutPage}}
				<div class="space">&nbsp;</div>
				<div class="grid_10 bankdata">
					{if $payment_mean.id eq $sPayment.id}
						{include file="frontend/plugins/payment/show_`$payment_mean.template`" form_data=$sPayment.data}
					{/if}
				</div>
            {elseif "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
                <div class="space">&nbsp;</div>
                <div class="grid_10 bankdata">
                    {if $payment_mean.id eq $sPayment.id}
                        {include file="frontend/plugins/payment/`$payment_mean.template`" form_data=$sPayment.data}
                    {/if}
                </div>
			{/if}
			{/block}
		</div>
	{/foreach}

    {if {config name=paymentEditingInCheckoutPage}}
        <div class="doublespace"></div>
        <input type="submit" value="{s namespace='frontend/checkout/confirm_left' name='ConfirmLinkChangePayment'}Ändern{/s}" class="button-middle small" />
    {/if}
    <div class="clear">&nbsp;</div>
</div>
</form>
{/if}
