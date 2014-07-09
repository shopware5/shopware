<div class="payment_method">
    <h3 class="headingbox_dark largesize">{s namespace='frontend/checkout/shipping_payment' name='ChangePaymentTitle'}{/s}</h3>

	{foreach $sPayments as $payment_mean}
		<div class="grid_15 {if $payment_mean@last}method_last{else}method{/if}">
			{block name='frontend_checkout_payment_fieldset_input_radio'}
                <div class="grid_5 first">
                    <input type="radio" name="payment" class="radio auto_submit" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $sFormData.payment or (!$sFormData && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} />
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
                {if "frontend/plugins/payment/`$payment_mean.template`"|template_exists}
                    <div class="grid_8 bankdata">
                        {include file="frontend/plugins/payment/`$payment_mean.template`" form_data=$sFormData error_flags=$sErrorFlag payment_means=$sPayments}
                    </div>
                {/if}
			{/block}
		</div>
	{/foreach}
	<div class="space">&nbsp;</div>
</div>