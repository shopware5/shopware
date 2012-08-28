{block name='frontend_register_payment_fieldset_input_radio'}
{if $BillsafePaymentChange && $payment_mean.id == $form_data.payment}

{elseif $payment_mean.name == 'billsafe_invoice' && $BillsafePrevalidate && (!$BillsafePrevalidate->invoice->isAvailable || $BillsafePrevalidate->ack == 'ERROR')}
	<div class="grid_14 last notice">
        {if $BillsafeConfig.debug && $BillsafePrevalidate->errorList}
            {if $BillsafePrevalidate->errorList->message}
                [{$BillsafePrevalidate->errorList->code}] - {$BillsafePrevalidate->errorList->message|escape|nl2br}
            {/if}
        {elseif $BillsafePrevalidate->invoice->message}
            {$BillsafePrevalidate->invoice->message|escape}
        {/if}
	</div>
	<div class="space clear"></div>
	<div class="grid_5 first">
		<input type="radio" name="register[payment]" disabled="disabled" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" />
		<label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
	</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}

{block name='frontend_checkout_confirm_payment' prepend}
{if $sUserData.additional.payment.name == 'billsafe_invoice' && !$BillsafePrevalidate->invoice->isAvailable}
	{$sRegisterFinished = true}
{/if}
{/block}

{block name='frontend_register_payment_fieldset_description'}
{if !$BillsafePaymentChange || $payment_mean.id != $form_data.payment}
	{$smarty.block.parent}
{/if}
{/block}

{block name='frontend_checkout_payment_fieldset_input_radio'}
{if $BillsafeConfig->prevalidate && $payment_mean.name == 'billsafe_invoice' && !$BillsafePrevalidate->invoice->isAvailable}
	<div class="grid_14 last notice" style="margin: 0 0 0 40px;">
		{$BillsafePrevalidate->invoice->message|escape}
	</div>
	<div class="space clear"></div>
	<div class="grid_5 first">
		<input type="radio" name="register[payment]" disabled="disabled" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}" />
		<label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
	</div>
{else}
	{$smarty.block.parent}
{/if}
{/block}