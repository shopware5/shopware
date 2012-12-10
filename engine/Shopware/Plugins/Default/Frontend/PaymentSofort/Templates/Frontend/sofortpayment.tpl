{*
 * Copyright (c) 2012 SOFORT AG
 *
 * $Date: 2012-07-09 11:10:01 +0200 (Mon, 09 Jul 2012) $
 * @version Shopware SOFORT AG Multipay 1.1.0 $Id: sofortpayment.tpl 4656 2012-07-09 09:10:01Z dehn $
 * @author SOFORT AG http://www.sofort.com (integration@sofort.com)
 *
*}
<!-- Attach some JavaScript functionality to head -->
{extends file="frontend/index/header.tpl"}
{block name="frontend_index_header_javascript" append}
<script type="text/javascript">
$(document).ready(function(){
	$('#vorkassebysofort_dhw').hide();
	$('#sofortrechnung_dhw').hide();
	$('#lastschriftbysofort_dhw').hide();
});

function showDWH(type) {
	if(type == 'sv') {
		$('#vorkassebysofort_dhw').slideToggle();
		$('[name=vorkassebysofort_dhw]').attr('checked', true);
	} else if(type == 'sr') {
		$('#sofortrechnung_dhw').slideToggle();
		$('[name=sofortrechnung_dhw]').attr('checked', true);
	} else if(type == 'ls') {
		$('#lastschriftbysofort_dhw').slideToggle();
		$('[name=lastschriftbysofort_dhw]').attr('checked', true);
	}
}
</script>
{/block}

<!-- Extend the payment fieldsets -->
{extends file="frontend/register/payment_fieldset.tpl"}
{block name='frontend_register_payment_fieldset_input_radio'}
	<div class="grid_5 first">
		<input type="radio" name="register[payment]" class="radio" value="{$payment_mean.id}" id="payment_mean{$payment_mean.id}"{if $payment_mean.id eq $form_data.payment or (!$form_data && !$smarty.foreach.register_payment_mean.index)} checked="checked"{/if} /> <label class="description" for="payment_mean{$payment_mean.id}">{$payment_mean.description}</label>
	</div>
{/block}
{block name='frontend_register_payment_fieldset_description'}
	{if $sofortPaymentMeans[$payment_mean.id].name eq 'sofortlastschrift_multipay'}
		{include file="sofortlastschrift.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'lastschriftbysofort_multipay'}
		{include file="lastschriftbysofort.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'vorkassebysofort_multipay'}
		{include file="vorkassebysofort.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'sofortrechnung_multipay'}
		{include file="sofortrechnung.tpl"}
	{elseif $sofortPaymentMeans[$payment_mean.id].name eq 'sofortueberweisung_multipay'}
		{include file="sofortueberweisung.tpl"}
	{else}
		<div class="grid_10 last">
			{$payment_mean.additionaldescription}
		</div>
	{/if}
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