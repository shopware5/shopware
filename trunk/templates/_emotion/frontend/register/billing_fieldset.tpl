{extends file='parent:frontend/register/billing_fieldset.tpl'}

{* Alternative *}
{block name='frontend_register_billing_fieldset_different_shipping'}
	{if !$update}
		<div class="alt_shipping">
			<input name="register[billing][shippingAddress]" type="checkbox" id="register_billing_shippingAddress" value="1" class="chkbox" {if $form_data.shippingAddress}checked="checked"{/if} />
			<span>{s name='RegisterBillingLabelShipping'}{/s}</span>
			<div class="clear">&nbsp;</div>
		</div>
	{/if}
{/block}
